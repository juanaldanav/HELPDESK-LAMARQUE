<?php
// Reseteo de contraseña. Ver specs/adr-0001-forgot-password.md.
// Token crudo (64 hex) viaja solo en el link; en DB se guarda sha1(token). Expira 15 min, un solo uso.
class PasswordReset
{
    const EXP_MIN  = 15;
    const MIN_PASS = 8;

    // Emite un token para un usuario: guarda sha1 + fecha, devuelve el token crudo (para el link).
    public static function issueToken(int $uid): string
    {
        $token = bin2hex(random_bytes(32)); // 64 hex
        db()->prepare(
            "UPDATE glpi_users SET password_forget_token = :h, password_forget_token_date = NOW() WHERE id = :id"
        )->execute([':h' => sha1($token), ':id' => $uid]);
        return $token;
    }

    // Devuelve [id,name] si el token es válido y no expiró; null en cualquier otro caso.
    public static function verify(string $token): ?array
    {
        if ($token === '') return null;
        $h = sha1($token);
        $u = q_one(
            "SELECT id, name, password_forget_token,
                    TIMESTAMPDIFF(MINUTE, password_forget_token_date, NOW()) AS mins
             FROM glpi_users
             WHERE password_forget_token = :h AND is_active = 1 AND password_forget_token_date IS NOT NULL
             LIMIT 1",
            [':h' => $h]
        );
        if (!$u) return null;
        if (!hash_equals((string)$u['password_forget_token'], $h)) return null; // defensa timing
        if ($u['mins'] === null || (int)$u['mins'] > self::EXP_MIN) return null;
        return ['id' => (int)$u['id'], 'name' => $u['name']];
    }

    // Cambia la contraseña usando un token. Devuelve ['ok'=>bool,'error'=>?string].
    // Un solo uso: limpia el token al terminar. Valida largo mínimo.
    public static function resetWithToken(string $token, string $pass, string $confirm): array
    {
        $u = self::verify($token);
        if (!$u) return ['ok' => false, 'error' => 'El enlace es inválido o expiró. Solicita uno nuevo.'];
        if (strlen($pass) < self::MIN_PASS) return ['ok' => false, 'error' => 'La contraseña debe tener al menos ' . self::MIN_PASS . ' caracteres.'];
        if ($pass !== $confirm) return ['ok' => false, 'error' => 'Las contraseñas no coinciden.'];

        $hash = password_hash($pass, PASSWORD_DEFAULT); // bcrypt $2y$
        db()->prepare(
            "UPDATE glpi_users
                SET password = :p, password_forget_token = NULL, password_forget_token_date = NULL
              WHERE id = :id"
        )->execute([':p' => $hash, ':id' => $u['id']]);
        return ['ok' => true, 'error' => null];
    }

    // Solicita reseteo por correo. SIEMPRE devuelve true (no revela si la cuenta existe).
    // Si el correo corresponde a un usuario activo, emite token y envía el link.
    public static function request(string $email): bool
    {
        $email = trim($email);
        if ($email === '') return true;
        $row = q_one(
            "SELECT u.id FROM glpi_useremails ue
             JOIN glpi_users u ON u.id = ue.users_id
             WHERE ue.email = :e AND u.is_active = 1
             ORDER BY ue.is_default DESC LIMIT 1",
            [':e' => $email]
        );
        if ($row) {
            $token = self::issueToken((int)$row['id']);
            $base = rtrim((string) q_val("SELECT value FROM glpi_configs WHERE name = 'url_base'"), '/');
            $link = $base . cfg('base_url') . '/?r=reset&t=' . $token;
            send_mail(
                $email,
                'Restablecer tu contraseña — Soporte Lamarque',
                "<p>Recibimos una solicitud para restablecer tu contraseña.</p>"
                . "<p><a href=\"" . htmlspecialchars($link, ENT_QUOTES) . "\" "
                . "style=\"background:#006970;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none\">"
                . "Crear nueva contraseña</a></p>"
                . "<p>El enlace expira en " . self::EXP_MIN . " minutos. Si no fuiste tú, ignora este correo.</p>"
                . "<p style=\"color:#888;font-size:12px\">— Soporte Lamarque</p>"
            );
        }
        return true;
    }
}
