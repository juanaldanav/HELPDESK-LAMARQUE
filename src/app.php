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

function notify_assigned(int $ticketId, int $tecnicoId, ?string $fecha = null): void
{
    $t = q_one("SELECT name, entities_id FROM glpi_tickets WHERE id = :id", [':id' => $ticketId]);
    $to = user_email($tecnicoId);
    $extra = $fecha ? "<p>Fecha de atención programada: <b>" . h($fecha) . "</b></p>" : "";
    send_mail($to, "Nuevo ticket asignado #$ticketId",
        "<p>Se te asignó el ticket <b>#$ticketId</b>: " . h($t['name'] ?? '') . "</p><p>Sucursal: " . h(Users::entityName((int)($t['entities_id'] ?? 0))) . "</p>" . $extra . "<p>Entra al portal para atenderlo.</p>");
}

// Cuerpo de correo para PROVEEDOR EXTERNO con el detalle completo del ticket.
function prov_ticket_email(array $t): string
{
    $desc = html_entity_decode((string)$t['content'], ENT_QUOTES, 'UTF-8');
    $desc = strip_tags(preg_replace('~<br\s*/?>|</p>~i', "\n", $desc));
    $desc = preg_replace('~\[(foto|doc):[^\]]+\]~', '', $desc);
    $assets = linked_assets_full((int)$t['id']);
    $eq = '';
    foreach ($assets as $a) $eq .= h($a['name']) . ' (' . h($a['serial']) . ') ';
    return "<p>Solicitamos su servicio para el siguiente mantenimiento:</p>"
        . "<table style='font-size:13.5px;line-height:1.7'>"
        . "<tr><td style='color:#5d6f70;padding-right:14px'><b>Ticket</b></td><td>#" . (int)$t['id'] . "</td></tr>"
        . "<tr><td style='color:#5d6f70'><b>Sucursal</b></td><td>" . h($t['entity_name']) . "</td></tr>"
        . "<tr><td style='color:#5d6f70'><b>Categoría</b></td><td>" . h($t['cat_name'] ?: 'General') . "</td></tr>"
        . "<tr><td style='color:#5d6f70'><b>Urgencia</b></td><td>" . h(urgency_label((int)$t['urgency'])) . "</td></tr>"
        . "<tr><td style='color:#5d6f70'><b>Reportado</b></td><td>" . fecha($t['date']) . "</td></tr>"
        . ($eq ? "<tr><td style='color:#5d6f70'><b>Equipo</b></td><td>" . $eq . "</td></tr>" : "")
        . "</table>"
        . "<p style='background:#f0f5f5;padding:10px 14px;border-radius:8px'>" . nl2br(h(trim($desc))) . "</p>"
        . "<p>Al concluir, favor de enviar su hoja de servicio a <b>coordinación</b> (este correo) para integrarla al historial.</p>";
}

// Notifica un mantenimiento agendado (técnico + sucursal). Best-effort.
function notify_agendado(int $agendaId): void
{
    $a = Agenda::get($agendaId);
    if (!$a) return;
    $suc = Users::entityName((int)$a['entities_id']);
    $rango = $a['fecha'] . ($a['fecha_fin'] ? ' a ' . $a['fecha_fin'] : '');
    $body = "<p>Mantenimiento <b>" . h($a['tipo']) . "</b> programado.</p>"
        . "<p>Sucursal: <b>" . h($suc) . "</b><br>Fecha: <b>" . h($rango) . "</b></p>"
        . ($a['descripcion'] ? "<p>" . h($a['descripcion']) . "</p>" : "")
        . "<p>— Soporte Lamarque</p>";
    if (!empty($a['users_id_tecnico'])) {
        send_mail(user_email((int)$a['users_id_tecnico']), "Mantenimiento programado: " . $a['tipo'] . " — $suc", $body);
    }
    send_mail(entity_email((int)$a['entities_id']), "Mantenimiento programado en tu sucursal: " . $a['tipo'], $body);
}

function notify_closed(int $ticketId, ?string $pdfPath = null): void
{
    $t = q_one("SELECT name, entities_id FROM glpi_tickets WHERE id = :id", [':id' => $ticketId]);
    $att = $pdfPath ? [[$pdfPath, 'hoja-servicio-' . $ticketId . '.pdf']] : [];
    $body = "<p>Tu ticket <b>#$ticketId</b> (" . h($t['name'] ?? '') . ") fue atendido y cerrado.</p>"
        . ($pdfPath ? "<p>Adjuntamos la <b>hoja de servicio en PDF</b> con el detalle del trabajo realizado.</p>" : "<p>Revisa la hoja de servicio en el portal.</p>");
    send_mail(entity_email((int)($t['entities_id'] ?? 0)), "Ticket #$ticketId resuelto", $body, $att);
    // Copia a Dalia (coordinación)
    send_mail(user_email(23), "Cierre de ticket #$ticketId — " . h($t['name'] ?? ''), $body, $att);
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
    // Pendientes REALES al día de hoy: abiertos sin importar el mes de apertura
    // (un ticket de mayo aún abierto en junio sigue pendiente). Aprovecha todo glpidb.
    $pendientes_hoy = (int) q_val("SELECT COUNT(*) FROM glpi_tickets WHERE is_deleted = 0 AND status IN (1,2,3,4)");

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

    // Atenciones por proveedor: sin link a proveedor = atendido Interno (requisito junta, sin costos)
    $topProv = q_all(
        "SELECT COALESCE(s.name, 'Interno') AS label, COUNT(DISTINCT t.id) AS c
         FROM glpi_tickets t
         LEFT JOIN glpi_suppliers_tickets st ON st.tickets_id = t.id
         LEFT JOIN glpi_suppliers s ON s.id = st.suppliers_id
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per
         GROUP BY s.id ORDER BY c DESC LIMIT 8", $p);

    // ---- Tiempos de atención (métricas que pidió Dalia) ----
    // Resolución = apertura → cierre (horas), sobre tickets abiertos en el periodo y ya cerrados.
    $resol = q_one(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, t.date, t.closedate)) AS avg_h,
                COUNT(*) AS n
         FROM glpi_tickets t
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per
           AND t.closedate IS NOT NULL AND t.closedate > '1970-01-01 00:00:00'", $p);
    // Primera respuesta = apertura → primer seguimiento (horas).
    $resp = q_val(
        "SELECT AVG(TIMESTAMPDIFF(HOUR, t.date, fr.first_date))
         FROM glpi_tickets t
         JOIN (SELECT items_id, MIN(date) AS first_date FROM glpi_itilfollowups
               WHERE itemtype = 'Ticket' GROUP BY items_id) fr ON fr.items_id = t.id
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per", $p);
    // Distribución del tiempo de resolución (para la gráfica de barras).
    $dist = ['<4 h' => 0, '4–24 h' => 0, '1–3 días' => 0, '+3 días' => 0];
    foreach (q_all(
        "SELECT TIMESTAMPDIFF(HOUR, t.date, t.closedate) AS h
         FROM glpi_tickets t
         WHERE t.is_deleted = 0 AND DATE_FORMAT(t.date,'%Y-%m') = :per
           AND t.closedate IS NOT NULL AND t.closedate > '1970-01-01 00:00:00'", $p) as $row) {
        $hh = (int)$row['h'];
        if ($hh < 4) $dist['<4 h']++;
        elseif ($hh < 24) $dist['4–24 h']++;
        elseif ($hh < 72) $dist['1–3 días']++;
        else $dist['+3 días']++;
    }

    return [
        'total'        => $total,
        'completados'  => $completados,
        'pendientes'   => $pendientes,
        'pct_completados' => $total ? round($completados * 100 / $total) : 0,
        'pendientes_hoy' => $pendientes_hoy,
        'preventivo'   => $roots[1] ?? 0,
        'correctivo'   => $roots[2] ?? 0,
        'top_sucursales' => $topSuc,
        'top_categorias' => $topCat,
        'top_proveedores' => $topProv,
        'tiempos' => [
            'avg_respuesta_h'  => $resp !== null ? round((float)$resp, 1) : null,
            'avg_resolucion_h' => isset($resol['avg_h']) && $resol['avg_h'] !== null ? round((float)$resol['avg_h'], 1) : null,
            'n_cerrados'       => (int)($resol['n'] ?? 0),
            'distribucion'     => $dist,
        ],
    ];
}

// Formatea horas a texto legible: 2.5 h, 18 h, 3.2 días.
function fmt_horas(?float $h): string
{
    if ($h === null) return '—';
    if ($h < 48) return rtrim(rtrim(number_format($h, 1), '0'), '.') . ' h';
    return rtrim(rtrim(number_format($h / 24, 1), '0'), '.') . ' días';
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
