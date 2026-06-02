<?php
// Acceso a usuarios, perfiles y entidades.
class Users
{
    public static function findByLogin(string $name): ?array
    {
        return q_one(
            "SELECT id, name, password, is_active, firstname, realname
             FROM glpi_users WHERE name = :n LIMIT 1",
            [':n' => $name]
        );
    }

    // Perfil + entidad primaria del usuario. Prioriza perfiles operativos (9/10/11/4).
    public static function primaryProfileEntity(int $uid): ?array
    {
        return q_one(
            "SELECT profiles_id, entities_id, is_recursive
             FROM glpi_profiles_users
             WHERE users_id = :u
             ORDER BY FIELD(profiles_id, 11, 4, 10, 9) DESC, id ASC
             LIMIT 1",
            [':u' => $uid]
        );
    }

    public static function entityName(int $eid): string
    {
        $n = q_val("SELECT name FROM glpi_entities WHERE id = :e", [':e' => $eid]);
        return $n !== false ? $n : ('Entidad ' . $eid);
    }

    public static function displayName(int $uid): string
    {
        $u = q_one("SELECT name, firstname, realname FROM glpi_users WHERE id = :u", [':u' => $uid]);
        if (!$u) return '—';
        $d = trim(($u['firstname'] ?? '') . ' ' . ($u['realname'] ?? ''));
        return $d !== '' ? $d : $u['name'];
    }

    // Técnicos (perfil 10) activos.
    public static function tecnicos(): array
    {
        return q_all(
            "SELECT DISTINCT u.id, u.name,
                    TRIM(CONCAT(COALESCE(u.firstname,''),' ',COALESCE(u.realname,''))) AS display
             FROM glpi_profiles_users pu
             JOIN glpi_users u ON u.id = pu.users_id
             WHERE pu.profiles_id = 10 AND u.is_active = 1
             ORDER BY u.name"
        );
    }

    // Lista de sucursales (entidades 1..16)
    public static function sucursales(): array
    {
        return q_all("SELECT id, name, tag FROM glpi_entities WHERE id > 0 ORDER BY name");
    }
}
