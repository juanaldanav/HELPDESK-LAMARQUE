<?php
// Repositorio de tickets. Todas las lecturas para sucursal van limitadas por entidad (Art. 2).
class Tickets
{
    // ---- Conteos para el home de sucursal (scoped por entidad de sesión) ----
    public static function counts(): array
    {
        [$w, $p] = entity_scope('t.entities_id');
        $rows = q_all(
            "SELECT status, COUNT(*) c FROM glpi_tickets t WHERE t.is_deleted = 0 $w GROUP BY status",
            $p
        );
        $out = ['abiertos' => 0, 'espera' => 0, 'resueltos' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $s = (int)$r['status'];
            $c = (int)$r['c'];
            $out['total'] += $c;
            if (in_array($s, [1, 2, 3], true)) $out['abiertos'] += $c;
            elseif ($s === 4) $out['espera'] += $c;
            elseif (in_array($s, [5, 6], true)) $out['resueltos'] += $c;
        }
        return $out;
    }

    // ---- Listado flexible. $opts: scope_entity(bool auto), assigned_to, status[], entity, category, tecnico, from, to, search, limit ----
    public static function listing(array $opts = []): array
    {
        $where = ["t.is_deleted = 0"];
        $p = [];

        // Scope por rol salvo que se pida explícito lo contrario
        $u = current_user();
        if ($u && $u['role'] === 'sucursal') {
            $where[] = "t.entities_id = :eid";
            $p[':eid'] = $u['entity_id'];
        }

        if (!empty($opts['assigned_to'])) {
            $where[] = "EXISTS (SELECT 1 FROM glpi_tickets_users tu WHERE tu.tickets_id = t.id AND tu.type = 2 AND tu.users_id = :atid)";
            $p[':atid'] = (int)$opts['assigned_to'];
        }
        if (!empty($opts['status']) && is_array($opts['status'])) {
            $in = [];
            foreach (array_values($opts['status']) as $i => $s) { $k = ":st$i"; $in[] = $k; $p[$k] = (int)$s; }
            $where[] = "t.status IN (" . implode(',', $in) . ")";
        }
        if (!empty($opts['entity'])) { $where[] = "t.entities_id = :fent"; $p[':fent'] = (int)$opts['entity']; }
        if (!empty($opts['category'])) { $where[] = "t.itilcategories_id = :fcat"; $p[':fcat'] = (int)$opts['category']; }
        if (!empty($opts['tecnico'])) {
            $where[] = "EXISTS (SELECT 1 FROM glpi_tickets_users tu WHERE tu.tickets_id = t.id AND tu.type = 2 AND tu.users_id = :ftec)";
            $p[':ftec'] = (int)$opts['tecnico'];
        }
        if (!empty($opts['from'])) { $where[] = "t.date >= :ffrom"; $p[':ffrom'] = $opts['from'] . ' 00:00:00'; }
        if (!empty($opts['to']))   { $where[] = "t.date <= :fto";   $p[':fto']   = $opts['to'] . ' 23:59:59'; }
        if (!empty($opts['search'])) { $where[] = "t.name LIKE :fsq"; $p[':fsq'] = '%' . $opts['search'] . '%'; }

        $limit = isset($opts['limit']) ? max(1, (int)$opts['limit']) : 200;
        $sql = "SELECT t.id, t.name, t.status, t.urgency, t.date, t.closedate, t.solvedate,
                       t.itilcategories_id, t.entities_id,
                       c.completename AS cat_name,
                       e.name AS entity_name,
                       (SELECT GROUP_CONCAT(TRIM(CONCAT(COALESCE(au.firstname,''),' ',COALESCE(au.realname,''))) SEPARATOR ', ')
                          FROM glpi_tickets_users tu2 JOIN glpi_users au ON au.id = tu2.users_id
                         WHERE tu2.tickets_id = t.id AND tu2.type = 2) AS tecnico_name
                FROM glpi_tickets t
                LEFT JOIN glpi_itilcategories c ON c.id = t.itilcategories_id
                LEFT JOIN glpi_entities e ON e.id = t.entities_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.date DESC
                LIMIT $limit";
        return q_all($sql, $p);
    }

    // ---- Un ticket con verificación de scope ----
    public static function get(int $id): ?array
    {
        $t = q_one(
            "SELECT t.*, c.completename AS cat_name, e.name AS entity_name
             FROM glpi_tickets t
             LEFT JOIN glpi_itilcategories c ON c.id = t.itilcategories_id
             LEFT JOIN glpi_entities e ON e.id = t.entities_id
             WHERE t.id = :id AND t.is_deleted = 0",
            [':id' => $id]
        );
        if (!$t) return null;
        $u = current_user();
        if ($u && $u['role'] === 'sucursal' && (int)$t['entities_id'] !== (int)$u['entity_id']) {
            return null; // aislamiento
        }
        $t['tecnico_name'] = q_val(
            "SELECT GROUP_CONCAT(TRIM(CONCAT(COALESCE(au.firstname,''),' ',COALESCE(au.realname,''))) SEPARATOR ', ')
               FROM glpi_tickets_users tu JOIN glpi_users au ON au.id = tu.users_id
              WHERE tu.tickets_id = :id AND tu.type = 2",
            [':id' => $id]
        ) ?: null;
        return $t;
    }

    // Activos vinculados a un ticket
    public static function linkedAssets(int $ticketId): array
    {
        return q_all(
            "SELECT itemtype, items_id FROM glpi_items_tickets WHERE tickets_id = :t",
            [':t' => $ticketId]
        );
    }

    // ---- Crear ticket ----
    // $d: entity_id, name, content, urgency, category_id, type, requester_id
    public static function create(array $d): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare(
                "INSERT INTO glpi_tickets
                 (entities_id, name, content, status, urgency, impact, priority, type,
                  itilcategories_id, users_id_recipient, date, date_creation, date_mod)
                 VALUES (:e, :n, :c, 1, :u, :u, :u, :tp, :cat, :rec, NOW(), NOW(), NOW())"
            );
            $st->execute([
                ':e'   => (int)$d['entity_id'],
                ':n'   => $d['name'],
                ':c'   => $d['content'],
                ':u'   => (int)($d['urgency'] ?? 3),
                ':tp'  => (int)($d['type'] ?? 1),
                ':cat' => (int)($d['category_id'] ?? 0),
                ':rec' => (int)($d['requester_id'] ?? 0),
            ]);
            $id = (int)$pdo->lastInsertId();

            // Solicitante (actor type=1)
            if (!empty($d['requester_id'])) {
                $pdo->prepare(
                    "INSERT INTO glpi_tickets_users (tickets_id, users_id, type, use_notification)
                     VALUES (:t, :u, 1, 1)"
                )->execute([':t' => $id, ':u' => (int)$d['requester_id']]);
            }

            // Activo vinculado opcional
            if (!empty($d['asset_itemtype']) && !empty($d['asset_id'])) {
                $pdo->prepare(
                    "INSERT INTO glpi_items_tickets (itemtype, items_id, tickets_id)
                     VALUES (:it, :iid, :t)"
                )->execute([
                    ':it'  => $d['asset_itemtype'],
                    ':iid' => (int)$d['asset_id'],
                    ':t'   => $id,
                ]);
            }
            $pdo->commit();
            return $id;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // ---- Asignar técnico (Dalia) ----
    public static function assign(int $ticketId, int $tecnicoId): void
    {
        $pdo = db();
        $exists = q_val(
            "SELECT id FROM glpi_tickets_users WHERE tickets_id = :t AND users_id = :u AND type = 2",
            [':t' => $ticketId, ':u' => $tecnicoId]
        );
        if (!$exists) {
            $pdo->prepare(
                "INSERT INTO glpi_tickets_users (tickets_id, users_id, type, use_notification)
                 VALUES (:t, :u, 2, 1)"
            )->execute([':t' => $ticketId, ':u' => $tecnicoId]);
        }
        // Pasa a En curso si estaba Nuevo
        $pdo->prepare(
            "UPDATE glpi_tickets SET status = IF(status = 1, 2, status), date_mod = NOW() WHERE id = :t"
        )->execute([':t' => $ticketId]);
    }

    public static function setUrgency(int $ticketId, int $urgency): void
    {
        db()->prepare("UPDATE glpi_tickets SET urgency = :u, priority = :u, date_mod = NOW() WHERE id = :t")
            ->execute([':u' => $urgency, ':t' => $ticketId]);
    }

    // ---- Cerrar ticket (técnico) ----
    public static function close(int $ticketId): void
    {
        db()->prepare(
            "UPDATE glpi_tickets
                SET status = 6, solvedate = NOW(), closedate = NOW(), date_mod = NOW()
              WHERE id = :t"
        )->execute([':t' => $ticketId]);
    }
}
