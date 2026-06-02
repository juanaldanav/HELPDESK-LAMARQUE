<?php
// Envío de correo best-effort vía SMTP. Reutiliza PHPMailer del vendor de GLPI y las
// credenciales SMTP guardadas en glpi_configs (fuente única — NO duplicamos el secreto).
// No bloquea la operación si falla.

function user_email(int $uid): ?string
{
    $e = q_val("SELECT email FROM glpi_useremails WHERE users_id = :u AND is_default = 1 LIMIT 1", [':u' => $uid]);
    if ($e) return $e;
    $e = q_val("SELECT email FROM glpi_useremails WHERE users_id = :u LIMIT 1", [':u' => $uid]);
    return $e ?: null;
}

function entity_email(int $eid): ?string
{
    $e = q_val("SELECT email FROM glpi_entities WHERE id = :e", [':e' => $eid]);
    return $e ?: null;
}

// Lee credenciales SMTP desde glpi_configs (cache estático). Nunca expone la contraseña.
function smtp_settings(): array
{
    static $s = null;
    if ($s !== null) return $s;
    $rows = q_all("SELECT name, value FROM glpi_configs
                   WHERE name IN ('smtp_host','smtp_port','smtp_username','smtp_passwd','smtp_sender','smtp_mode')");
    $c = [];
    foreach ($rows as $r) $c[$r['name']] = $r['value'];
    $s = [
        'host' => $c['smtp_host'] ?? 'smtp.gmail.com',
        'port' => (int)($c['smtp_port'] ?? 587),
        'user' => $c['smtp_username'] ?? '',
        'pass' => $c['smtp_passwd'] ?? '',
        'from' => $c['smtp_sender'] ?: ($c['smtp_username'] ?? 'noreply@lamarque.mx'),
    ];
    return $s;
}

function send_mail(?string $to, string $subject, string $html): bool
{
    $cfg = cfg('smtp');
    if (empty($cfg['enabled'])) {
        error_log("[soporte-v2 mail-disabled] to={$to} subj={$subject}");
        return false;
    }
    $redirect = $cfg['redirect_to'] ?? null;
    $realTo = $redirect ?: $to;
    if (!$realTo) return false;

    $dir = $cfg['phpmailer_dir'] ?? '/var/www/glpi/vendor/phpmailer/phpmailer/src';
    if (!is_file("$dir/PHPMailer.php")) {
        error_log("[soporte-v2 mail] PHPMailer no encontrado en $dir");
        return false;
    }
    require_once "$dir/Exception.php";
    require_once "$dir/PHPMailer.php";
    require_once "$dir/SMTP.php";

    $s = smtp_settings();
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $s['host'];
        $mail->Port       = $s['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $s['user'];
        $mail->Password   = $s['pass'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($s['from'], $cfg['from_name'] ?? 'Soporte Lamarque');
        $mail->addAddress($realTo);
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $body = $html;
        if ($redirect && $to && $to !== $redirect) {
            $body = "<p style='background:#fffbe6;padding:8px;border-radius:6px;font-size:13px'>"
                . "↪ <b>Modo pruebas:</b> destinatario real era <b>" . htmlspecialchars($to, ENT_QUOTES) . "</b>. "
                . "Reenviado a sistemas@lamarque.mx.</p>" . $html;
        }
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $html));
        $mail->send();
        return true;
    } catch (Throwable $e) {
        // No logear credenciales — solo el error de PHPMailer.
        error_log('[soporte-v2 mail] fallo envío: ' . $e->getMessage());
        return false;
    }
}
