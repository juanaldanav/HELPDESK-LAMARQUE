<?php
// Gastos de mantenimiento (tabla propia lmq_gastos).
// Dos usos: (a) costo de resolver un ticket (tickets_id != NULL),
//           (b) gasto suelto / mantenimiento fijo sin ticket (tickets_id = NULL).
class Gastos
{
    const CLASES = ['fijo' => 'Fijo', 'variable' => 'Variable'];

    // d: entity_id, tickets_id?, suppliers_id?, concepto, clase, tipo?, monto, fecha, nota?, comprobante?, creator_id
    public static function create(array $d): int
    {
        $clase = isset($d['clase']) && isset(self::CLASES[$d['clase']]) ? $d['clase'] : 'variable';
        $st = db()->prepare(
            "INSERT INTO lmq_gastos
             (tickets_id, entities_id, suppliers_id, concepto, clase, tipo, monto, fecha, nota, comprobante, users_id_creator)
             VALUES (:tk, :e, :sup, :c, :cl, :tp, :m, :f, :n, :cmp, :cr)"
        );
        $st->execute([
            ':tk'  => (int)($d['tickets_id'] ?? 0) ?: null,
            ':e'   => (int)($d['entity_id'] ?? 0),
            ':sup' => (int)($d['suppliers_id'] ?? 0) ?: null,
            ':c'   => mb_substr(trim($d['concepto'] ?? ''), 0, 255),
            ':cl'  => $clase,
            ':tp'  => $d['tipo'] ? mb_substr(trim($d['tipo']), 0, 40) : null,
            ':m'   => round((float)($d['monto'] ?? 0), 2),
            ':f'   => $d['fecha'],
            ':n'   => $d['nota'] ? mb_substr(trim($d['nota']), 0, 1000) : null,
            ':cmp' => $d['comprobante'] ?: null,
            ':cr'  => (int)($d['creator_id'] ?? 0) ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function get(int $id): ?array
    {
        return q_one("SELECT * FROM lmq_gastos WHERE id = :id", [':id' => $id]);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM lmq_gastos WHERE id = :id")->execute([':id' => $id]);
    }

    // Gastos ligados a un ticket + total.
    public static function forTicket(int $ticketId): array
    {
        return q_all(
            "SELECT g.*, s.name AS proveedor
             FROM lmq_gastos g LEFT JOIN glpi_suppliers s ON s.id = g.suppliers_id
             WHERE g.tickets_id = :t ORDER BY g.fecha DESC, g.id DESC",
            [':t' => $ticketId]
        );
    }

    public static function totalForTicket(int $ticketId): float
    {
        return (float) q_val("SELECT COALESCE(SUM(monto),0) FROM lmq_gastos WHERE tickets_id = :t", [':t' => $ticketId]);
    }

    // Listado con filtros: month 'YYYY-MM', entity, supplier, clase. Paginado.
    public static function listing(array $f = []): array
    {
        $where = ['1=1'];
        $p = [];
        if (!empty($f['month']))    { $where[] = "DATE_FORMAT(g.fecha,'%Y-%m') = :m"; $p[':m'] = $f['month']; }
        if (!empty($f['entity']))   { $where[] = "g.entities_id = :e";                 $p[':e'] = (int)$f['entity']; }
        if (!empty($f['supplier'])) { $where[] = "g.suppliers_id = :s";                $p[':s'] = (int)$f['supplier']; }
        if (!empty($f['clase']) && isset(self::CLASES[$f['clase']])) { $where[] = "g.clase = :cl"; $p[':cl'] = $f['clase']; }
        $limit  = (int)($f['limit'] ?? 100);
        $offset = (int)($f['offset'] ?? 0);
        return q_all(
            "SELECT g.*, e.name AS entity_name, s.name AS proveedor
             FROM lmq_gastos g
             LEFT JOIN glpi_entities e ON e.id = g.entities_id
             LEFT JOIN glpi_suppliers s ON s.id = g.suppliers_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY g.fecha DESC, g.id DESC
             LIMIT $offset, $limit",
            $p
        );
    }

    public static function totalFiltered(array $f = []): float
    {
        $where = ['1=1'];
        $p = [];
        if (!empty($f['month']))    { $where[] = "DATE_FORMAT(fecha,'%Y-%m') = :m"; $p[':m'] = $f['month']; }
        if (!empty($f['entity']))   { $where[] = "entities_id = :e";                $p[':e'] = (int)$f['entity']; }
        if (!empty($f['supplier'])) { $where[] = "suppliers_id = :s";               $p[':s'] = (int)$f['supplier']; }
        if (!empty($f['clase']) && isset(self::CLASES[$f['clase']])) { $where[] = "clase = :cl"; $p[':cl'] = $f['clase']; }
        return (float) q_val("SELECT COALESCE(SUM(monto),0) FROM lmq_gastos WHERE " . implode(' AND ', $where), $p);
    }

    // ---- Resúmenes para el dashboard (periodo 'YYYY-MM') ----
    public static function summary(string $period): array
    {
        $p = [':per' => $period];
        $total = (float) q_val("SELECT COALESCE(SUM(monto),0) FROM lmq_gastos WHERE DATE_FORMAT(fecha,'%Y-%m') = :per", $p);
        $porClase = q_all("SELECT clase, COALESCE(SUM(monto),0) AS monto FROM lmq_gastos WHERE DATE_FORMAT(fecha,'%Y-%m') = :per GROUP BY clase", $p);
        $porSucursal = q_all(
            "SELECT e.name AS label, COALESCE(SUM(g.monto),0) AS monto
             FROM lmq_gastos g LEFT JOIN glpi_entities e ON e.id = g.entities_id
             WHERE DATE_FORMAT(g.fecha,'%Y-%m') = :per
             GROUP BY g.entities_id ORDER BY monto DESC LIMIT 10", $p);
        $porProveedor = q_all(
            "SELECT COALESCE(s.name,'Sin proveedor') AS label, COALESCE(SUM(g.monto),0) AS monto
             FROM lmq_gastos g LEFT JOIN glpi_suppliers s ON s.id = g.suppliers_id
             WHERE DATE_FORMAT(g.fecha,'%Y-%m') = :per
             GROUP BY g.suppliers_id ORDER BY monto DESC LIMIT 10", $p);
        return [
            'total'        => $total,
            'por_clase'    => $porClase,
            'por_sucursal' => $porSucursal,
            'por_proveedor'=> $porProveedor,
        ];
    }
}
