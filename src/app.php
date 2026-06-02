<?php
// Helpers de nivel app: categorías, activos vinculados, notificaciones, hoja de servicio, KPIs, export.

// Árbol de categorías agrupado (raíz -> hijos) para selects.
function categories_grouped(): array
{
    $rows = q_all("SELECT id, name, itilcategories_id FROM glpi_itilcategories ORDER BY itilcategories_id, name");
    $roots = [];
    $children = [];
    foreach ($rows as $r) {
        if ((int)$r['itilcategories_id'] === 0) $roots[(int)$r['id']] = ['id' => (int)$r['id'], 'name' => $r['name'], 'items' => []];
    }
    foreach ($rows as $r) {
        $pid = (int)$r['itilcategories_id'];
        if ($pid !== 0 && isset($roots[$pid])) {
            $roots[$pid]['items'][] = ['id' => (int)$r['id'], 'name' => $r['name']];
        }
    }
    return array_values($roots);
}

// Activos vinculados a un ticket, resueltos a sus datos.
function linked_assets_full(int $ticketId): array
{
    $out = [];
    foreach (Tickets::linkedAssets($ticketId) as $l) {
        $a = Assets::get($l['itemtype'], (int)$l['items_id']);
        if ($a) $out[] = $a;
    }
    return $out;
}

// ---- Hoja de servicio (texto del followup de cierre) ----
function build_service_sheet(array $post): string
{
    $checks = [
        'chk_limpieza'      => 'Limpieza interna/externa',
        'chk_cableado'      => 'Ajuste de cableado y conexiones',
        'chk_tornilleria'   => 'Ajuste de tornillería general',
        'chk_lubricacion'   => 'Lubricación de partes móviles',
        'chk_actualizacion' => 'Actualización / firmware',
        'chk_entregado'     => 'Equipo entregado en funcionamiento',
    ];
    $lines = ["🧾 HOJA DE SERVICIO"];
    foreach ($checks as $k => $label) {
        $mark = !empty($post[$k]) ? '☑' : '☐';
        $lines[] = "$mark $label";
    }
    $obs = trim($post['observaciones'] ?? '');
    if ($obs !== '') { $lines[] = ""; $lines[] = "Observaciones: " . $obs; }
    return implode("\n", $lines);
}

// ---- Notificaciones best-effort ----
function notify_new_ticket(int $ticketId, array $requester): void
{
    $to = entity_email((int)$requester['entity_id']) ?: user_email((int)$requester['id']);
    send_mail($to, "Recibimos tu reporte #$ticketId",
        "<p>Hola " . h($requester['entity_name']) . ",</p><p>Tu reporte <b>#$ticketId</b> fue recibido. Te avisaremos cuando un técnico lo atienda.</p><p>— Soporte Lamarque</p>");
}

function notify_assigned(int $ticketId, int $tecnicoId): void
{
    $t = q_one("SELECT name, entities_id FROM glpi_tickets WHERE id = :id", [':id' => $ticketId]);
    $to = user_email($tecnicoId);
    send_mail($to, "Nuevo ticket asignado #$ticketId",
        "<p>Se te asignó el ticket <b>#$ticketId</b>: " . h($t['name'] ?? '') . "</p><p>Sucursal: " . h(Users::entityName((int)($t['entities_id'] ?? 0))) . "</p><p>Entra al portal para atenderlo.</p>");
}

function notify_closed(int $ticketId): void
{
    $t = q_one("SELECT name, entities_id FROM glpi_tickets WHERE id = :id", [':id' => $ticketId]);
    $to = entity_email((int)($t['entities_id'] ?? 0));
    send_mail($to, "Ticket #$ticketId resuelto",
        "<p>Tu ticket <b>#$ticketId</b> (" . h($t['name'] ?? '') . ") fue marcado como resuelto.</p><p>Revisa la hoja de servicio en el portal.</p><p>— Soporte Lamarque</p>");
}

// ---- KPIs del dashboard de Dalia (periodo 'YYYY-MM') ----
function dashboard_kpis(string $period): array
{
    $p = [':per' => $period];
    $base = "FROM glpi_tickets t WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per";

    $total = (int) q_val("SELECT COUNT(*) $base", $p);
    $byStatus = [];
    foreach (q_all("SELECT status, COUNT(*) c $base GROUP BY status", $p) as $r) $byStatus[(int)$r['status']] = (int)$r['c'];
    $completados = ($byStatus[5] ?? 0) + ($byStatus[6] ?? 0);
    $pendientes  = ($byStatus[1] ?? 0) + ($byStatus[2] ?? 0) + ($byStatus[3] ?? 0) + ($byStatus[4] ?? 0);

    // Correctivo (raíz 2) vs Preventivo (raíz 1)
    $roots = [];
    foreach (q_all(
        "SELECT CASE WHEN c.itilcategories_id = 0 THEN c.id ELSE c.itilcategories_id END AS root_id, COUNT(*) c
         FROM glpi_tickets t JOIN glpi_itilcategories c ON c.id = t.itilcategories_id
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per
         GROUP BY root_id", $p) as $r) {
        $roots[(int)$r['root_id']] = (int)$r['c'];
    }

    $topSuc = q_all(
        "SELECT e.name AS label, COUNT(*) c
         FROM glpi_tickets t JOIN glpi_entities e ON e.id = t.entities_id
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per AND t.entities_id > 0
         GROUP BY t.entities_id ORDER BY c DESC LIMIT 5", $p);

    $topCat = q_all(
        "SELECT c.completename AS label, COUNT(*) c
         FROM glpi_tickets t JOIN glpi_itilcategories c ON c.id = t.itilcategories_id
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per
         GROUP BY t.itilcategories_id ORDER BY c DESC LIMIT 5", $p);

    return [
        'total'        => $total,
        'completados'  => $completados,
        'pendientes'   => $pendientes,
        'pct_completados' => $total ? round($completados * 100 / $total) : 0,
        'preventivo'   => $roots[1] ?? 0,
        'correctivo'   => $roots[2] ?? 0,
        'top_sucursales' => $topSuc,
        'top_categorias' => $topCat,
    ];
}

// ---- Export CSV (Dalia) ----
function export_tickets_csv(array $get): void
{
    $opts = [
        'entity'   => (int)($get['entity'] ?? 0) ?: null,
        'category' => (int)($get['category'] ?? 0) ?: null,
        'tecnico'  => (int)($get['tecnico'] ?? 0) ?: null,
        'status'   => status_filter($get['status'] ?? ''),
        'from'     => $get['from'] ?? null,
        'to'       => $get['to'] ?? null,
        'search'   => trim($get['q'] ?? '') ?: null,
        'limit'    => 5000,
    ];
    $rows = Tickets::listing($opts);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tickets_lamarque.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, "\xEF\xBB\xBF"); // BOM utf-8
    fputcsv($out, ['No.', 'Sucursal', 'Categoría', 'Título', 'Urgencia', 'Técnico', 'Estado', 'Apertura', 'Cierre'], ';');
    foreach ($rows as $t) {
        fputcsv($out, [
            $t['id'], $t['entity_name'], $t['cat_name'], $t['name'],
            urgency_label((int)$t['urgency']), $t['tecnico_name'] ?: '',
            status_label((int)$t['status']), $t['date'], $t['closedate'],
        ], ';');
    }
    fclose($out);
    exit;
}
