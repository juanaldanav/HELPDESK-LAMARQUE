<?php
// Hilo del ticket = descripción inicial + glpi_itilfollowups (itemtype='Ticket').
class Followups
{
    // Timeline cronológico. Incluye el mensaje inicial del ticket como primer elemento.
    public static function thread(array $ticket): array
    {
        $items = [];
        $items[] = [
            'is_initial' => true,
            'author'     => $ticket['users_id_recipient'] ? Users::displayName((int)$ticket['users_id_recipient']) : 'Solicitante',
            'date'       => $ticket['date'],
            'content'    => $ticket['content'] ?? '',
        ];
        $rows = q_all(
            "SELECT f.id, f.users_id, f.content, f.date, f.is_private,
                    TRIM(CONCAT(COALESCE(u.firstname,''),' ',COALESCE(u.realname,''))) AS dn, u.name AS uname
             FROM glpi_itilfollowups f
             LEFT JOIN glpi_users u ON u.id = f.users_id
             WHERE f.itemtype = 'Ticket' AND f.items_id = :t
             ORDER BY f.date ASC",
            [':t' => (int)$ticket['id']]
        );
        foreach ($rows as $r) {
            $items[] = [
                'is_initial' => false,
                'author'     => trim($r['dn']) !== '' ? $r['dn'] : ($r['uname'] ?: 'Usuario'),
                'date'       => $r['date'],
                'content'    => $r['content'] ?? '',
                'is_private' => (int)$r['is_private'],
            ];
        }
        return $items;
    }

    public static function add(int $ticketId, int $userId, string $content, int $isPrivate = 0, int $timelinePos = 1): int
    {
        $st = db()->prepare(
            "INSERT INTO glpi_itilfollowups
             (itemtype, items_id, date, users_id, content, is_private, date_mod, date_creation, timeline_position)
             VALUES ('Ticket', :t, NOW(), :u, :c, :p, NOW(), NOW(), :tp)"
        );
        $st->execute([
            ':t'  => $ticketId,
            ':u'  => $userId,
            ':c'  => $content,
            ':p'  => $isPrivate,
            ':tp' => $timelinePos,
        ]);
        db()->prepare("UPDATE glpi_tickets SET date_mod = NOW() WHERE id = :t")->execute([':t' => $ticketId]);
        return (int)db()->lastInsertId();
    }
}
