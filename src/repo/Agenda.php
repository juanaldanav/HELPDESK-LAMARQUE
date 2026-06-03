<?php
// Agenda de mantenimientos (tabla propia lmq_agenda). Preventivos programados + correctivos con fecha.
class Agenda
{
    const TIPOS = [
        'Filtro de Agua',
        'Maquina de Hielo',
        'Refrigeradores y Vitrinas',
        'Minisplit A/C',
        'Café Marino',
        'Correctivo',
        'Otro',
    ];

    // Color sólido por tipo (hex del design-system §1.2 — usar en style="background:…").
    const TIPO_COLOR = [
        'Filtro de Agua'            => '#0c83c4',
        'Maquina de Hielo'          => '#0e8091',
        'Refrigeradores y Vitrinas' => '#2456c4',
        'Minisplit A/C'             => '#5a4bd1',
        'Café Marino'               => '#b5610f',
        'Correctivo'                => '#d83a34',
        'Otro'                      => '#5d6f70',
    ];

    // Icono por tipo de mantenimiento (svg_icon name)
    const TIPO_ICON = [
        'Filtro de Agua'            => 'drop',
        'Maquina de Hielo'          => 'snow',
        'Refrigeradores y Vitrinas' => 'refrigeracion',
        'Minisplit A/C'             => 'air',
        'Café Marino'               => 'maquinariadecafe',
        'Correctivo'                => 'wrench',
        'Otro'                      => 'wrench',
    ];

    public static function tipoColor(string $tipo): string
    {
        return self::TIPO_COLOR[$tipo] ?? '#5d6f70';
    }

    // Eventos cuyo rango toca el mes (y,m). Filtros opcionales: entity, tipo.
    public static function month(int $y, int $m, array $f = []): array
    {
        $first = sprintf('%04d-%02d-01', $y, $m);
        $last  = date('Y-m-t', strtotime($first));
        $where = ["(a.fecha <= :last AND COALESCE(a.fecha_fin, a.fecha) >= :first)"];
        $p = [':first' => $first, ':last' => $last];
        if (!empty($f['entity'])) { $where[] = "a.entities_id = :e"; $p[':e'] = (int)$f['entity']; }
        if (!empty($f['tipo']))   { $where[] = "a.tipo = :t";        $p[':t'] = $f['tipo']; }
        $rows = q_all(
            "SELECT a.*, e.name AS entity_name, e.tag AS entity_tag,
                    TRIM(CONCAT(COALESCE(u.firstname,''),' ',COALESCE(u.realname,''))) AS tecnico_name
             FROM lmq_agenda a
             LEFT JOIN glpi_entities e ON e.id = a.entities_id
             LEFT JOIN glpi_users u ON u.id = a.users_id_tecnico
             WHERE " . implode(' AND ', $where) . " AND a.estado <> 'cancelado'
             ORDER BY a.fecha ASC, a.entities_id ASC",
            $p
        );
        return $rows;
    }

    // Mantenimientos programados asignados a un técnico (para que le LLEGUEN en su app).
    public static function forTecnico(int $uid): array
    {
        return q_all(
            "SELECT a.*, e.name AS entity_name, e.tag AS entity_tag
             FROM lmq_agenda a
             LEFT JOIN glpi_entities e ON e.id = a.entities_id
             WHERE a.users_id_tecnico = :u AND a.estado = 'programado'
               AND COALESCE(a.fecha_fin, a.fecha) >= CURDATE()
             ORDER BY a.fecha ASC LIMIT 50",
            [':u' => $uid]
        );
    }

    public static function get(int $id): ?array
    {
        return q_one("SELECT * FROM lmq_agenda WHERE id = :id", [':id' => $id]);
    }

    // d: entity_id, tipo, fecha, fecha_fin?, tecnico_id?, tickets_id?, descripcion?, clase, creator_id
    public static function create(array $d): int
    {
        $tipo = in_array($d['tipo'] ?? '', self::TIPOS, true) ? $d['tipo'] : 'Otro';
        $st = db()->prepare(
            "INSERT INTO lmq_agenda
             (entities_id, tipo, clase, fecha, fecha_fin, users_id_tecnico, tickets_id, descripcion, estado, users_id_creator)
             VALUES (:e, :t, :c, :f, :ff, :tec, :tk, :d, 'programado', :cr)"
        );
        $st->execute([
            ':e'   => (int)$d['entity_id'],
            ':t'   => $tipo,
            ':c'   => ($d['clase'] ?? 'preventivo') === 'correctivo' ? 'correctivo' : 'preventivo',
            ':f'   => $d['fecha'],
            ':ff'  => $d['fecha_fin'] ?: null,
            ':tec' => (int)($d['tecnico_id'] ?? 0) ?: null,
            ':tk'  => (int)($d['tickets_id'] ?? 0) ?: null,
            ':d'   => $d['descripcion'] ?? null,
            ':cr'  => (int)($d['creator_id'] ?? 0) ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function setEstado(int $id, string $estado): void
    {
        if (!in_array($estado, ['programado', 'realizado', 'cancelado'], true)) return;
        db()->prepare("UPDATE lmq_agenda SET estado = :s WHERE id = :id")->execute([':s' => $estado, ':id' => $id]);
    }

    // Conteo de programados del mes (para chip del nav, opcional).
    public static function pendingCount(): int
    {
        return (int) q_val("SELECT COUNT(*) FROM lmq_agenda WHERE estado = 'programado' AND fecha >= CURDATE()");
    }
}
