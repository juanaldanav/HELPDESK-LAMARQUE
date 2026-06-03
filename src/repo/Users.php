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

    // ---- Gestión de equipo (panel Dalia) ----
    // Usuarios operativos (roles 9/10/11) con su perfil primario, entidad y correo.
    public static function managed(): array
    {
        return q_all(
            "SELECT u.id, u.name, u.firstname, u.realname, u.is_active,
                    pu.profiles_id, pu.entities_id,
                    e.name AS entity_name,
                    (SELECT ue.email FROM glpi_useremails ue WHERE ue.users_id = u.id ORDER BY ue.is_default DESC LIMIT 1) AS email
             FROM glpi_users u
             JOIN glpi_profiles_users pu ON pu.id = (
                 SELECT pu2.id FROM glpi_profiles_users pu2
                 WHERE pu2.users_id = u.id
                 ORDER BY FIELD(pu2.profiles_id, 11, 4, 10, 9) DESC, pu2.id ASC LIMIT 1
             )
             LEFT JOIN glpi_entities e ON e.id = pu.entities_id
             WHERE pu.profiles_id IN (9, 10, 11) AND u.is_deleted = 0
             ORDER BY FIELD(pu.profiles_id, 11, 10, 9), u.name"
        );
    }

    // ¿El usuario es gestionable desde el panel de Dalia? Solo perfiles operativos (9/10/11)
    // y NUNCA un Super-Admin (4) — protege a ti.admin y cuentas de sistema de escalación.
    public static function isManaged(int $uid): bool
    {
        if ($uid <= 0) return false;
        $hasSuper = q_val("SELECT 1 FROM glpi_profiles_users WHERE users_id = :u AND profiles_id = 4 LIMIT 1", [':u' => $uid]);
        if ($hasSuper) return false;
        $hasOp = q_val("SELECT 1 FROM glpi_profiles_users WHERE users_id = :u AND profiles_id IN (9,10,11) LIMIT 1", [':u' => $uid]);
        return (bool) $hasOp;
    }

    // Cambia el rol operativo de un usuario. role: sucursal|tecnico|dalia. entityId solo aplica a sucursal.
    public static function setRole(int $uid, string $role, int $entityId = 0): bool
    {
        $map = [
            'sucursal' => ['profile' => 9,  'entity' => $entityId, 'rec' => 0],
            'tecnico'  => ['profile' => 10, 'entity' => 0,         'rec' => 1],
            'dalia'    => ['profile' => 11, 'entity' => 0,         'rec' => 1],
        ];
        if (!isset($map[$role])) return false;
        if ($role === 'sucursal' && $entityId <= 0) return false;
        $m = $map[$role];
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Solo toca los perfiles operativos; Super-Admin (4) y Observer (2) quedan intactos.
            $pdo->prepare("DELETE FROM glpi_profiles_users WHERE users_id = :u AND profiles_id IN (9,10,11)")
                ->execute([':u' => $uid]);
            $pdo->prepare(
                "INSERT INTO glpi_profiles_users (users_id, profiles_id, entities_id, is_recursive, is_dynamic)
                 VALUES (:u, :p, :e, :r, 0)"
            )->execute([':u' => $uid, ':p' => $m['profile'], ':e' => $m['entity'], ':r' => $m['rec']]);
            $pdo->commit();
            return true;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // Actualiza nombre y correo (corrección de datos mal extraídos). email '' = no tocar correo.
    public static function updateProfileData(int $uid, string $firstname, string $realname, string $email): array
    {
        $email = trim($email);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Correo inválido.'];
        db()->prepare("UPDATE glpi_users SET firstname = :f, realname = :r WHERE id = :u")
            ->execute([':f' => trim($firstname), ':r' => trim($realname), ':u' => $uid]);
        if ($email !== '') {
            $cur = q_val("SELECT id FROM glpi_useremails WHERE users_id = :u ORDER BY is_default DESC LIMIT 1", [':u' => $uid]);
            if ($cur) {
                db()->prepare("UPDATE glpi_useremails SET email = :e WHERE id = :id")->execute([':e' => $email, ':id' => $cur]);
            } else {
                db()->prepare("INSERT INTO glpi_useremails (users_id, email, is_default) VALUES (:u, :e, 1)")->execute([':u' => $uid, ':e' => $email]);
            }
        }
        return ['ok' => true];
    }

    // Cambio de contraseña propio: verifica la actual. Devuelve [ok, error?].
    public static function changeOwnPassword(int $uid, string $current, string $new, string $confirm): array
    {
        if (strlen($new) < 8) return ['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 8 caracteres.'];
        if ($new !== $confirm) return ['ok' => false, 'error' => 'Las contraseñas no coinciden.'];
        $hash = q_val("SELECT password FROM glpi_users WHERE id = :u", [':u' => $uid]);
        if (!$hash || !password_verify($current, $hash)) return ['ok' => false, 'error' => 'La contraseña actual es incorrecta.'];
        self::setPassword($uid, $new);
        return ['ok' => true];
    }

    public static function setActive(int $uid, bool $active): void
    {
        db()->prepare("UPDATE glpi_users SET is_active = :a WHERE id = :u")
            ->execute([':a' => $active ? 1 : 0, ':u' => $uid]);
    }

    // Eliminar (soft) un usuario gestionable: desactiva, marca is_deleted y quita perfiles operativos.
    // No borra físicamente (conserva el histórico de tickets/GLPI).
    public static function softDelete(int $uid): bool
    {
        if (!self::isManaged($uid)) return false;
        db()->prepare("UPDATE glpi_users SET is_active = 0, is_deleted = 1 WHERE id = :u")->execute([':u' => $uid]);
        db()->prepare("DELETE FROM glpi_profiles_users WHERE users_id = :u AND profiles_id IN (9,10,11)")->execute([':u' => $uid]);
        return true;
    }

    public static function setPassword(int $uid, string $pass): void
    {
        db()->prepare("UPDATE glpi_users SET password = :p, password_forget_token = NULL, password_forget_token_date = NULL WHERE id = :u")
            ->execute([':p' => password_hash($pass, PASSWORD_DEFAULT), ':u' => $uid]);
    }

    // Crea un usuario operativo nuevo. Devuelve [ok, error|id].
    public static function createManaged(string $login, string $firstname, string $realname, string $email, string $pass, string $role, int $entityId): array
    {
        $login = trim($login);
        if (!preg_match('~^[a-z0-9._-]{3,40}$~i', $login)) return ['ok' => false, 'error' => 'Login inválido (3-40 caracteres, letras/números/._-).'];
        if (q_val("SELECT id FROM glpi_users WHERE name = :n", [':n' => $login])) return ['ok' => false, 'error' => 'Ese login ya existe.'];
        if (strlen($pass) < 8) return ['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres.'];
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Correo inválido.'];

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                "INSERT INTO glpi_users (name, password, firstname, realname, is_active, entities_id)
                 VALUES (:n, :p, :f, :r, 1, 0)"
            )->execute([
                ':n' => $login, ':p' => password_hash($pass, PASSWORD_DEFAULT),
                ':f' => $firstname, ':r' => $realname,
            ]);
            $uid = (int)$pdo->lastInsertId();
            if ($email !== '') {
                $pdo->prepare("INSERT INTO glpi_useremails (users_id, email, is_default) VALUES (:u, :e, 1)")
                    ->execute([':u' => $uid, ':e' => $email]);
            }
            $pdo->commit();
            self::setRole($uid, $role, $entityId);
            return ['ok' => true, 'id' => $uid];
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }
}
