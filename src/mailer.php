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

// Envuelve el contenido en la plantilla de marca (header turquesa + footer).
function mail_wrap(string $title, string $inner): string
{
    return '<div style="background:#f3f5f5;padding:24px 12px;font-family:\'Segoe UI\',Arial,sans-serif">'
        . '<div style="max-width:560px;margin:0 auto">'
        . '<div style="background:#006970;border-radius:14px 14px 0 0;padding:15px 22px">'
        . '<span style="color:#ffffff;font-weight:800;font-size:16px;letter-spacing:.2px">Soporte Lamarque</span></div>'
        . '<div style="background:#ffffff;border-radius:0 0 14px 14px;padding:22px;color:#1f2a2b;font-size:14px;line-height:1.55">'
        . '<h2 style="margin:0 0 12px;font-size:17px;color:#0f1c1d">' . htmlspecialchars($title, ENT_QUOTES) . '</h2>'
        . $inner
        . '</div>'
        . '<p style="text-align:center;color:#90a0a0;font-size:11.5px;margin-top:12px">'
        . 'Lamarque Repostería &amp; Café · Portal de mantenimiento<br>Mensaje automático — no responder.</p>'
        . '</div></div>';
}

function send_mail(?string $to, string $subject, string $html, array $attachments = []): bool
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

        $inner = $html;
        if ($redirect && $to && $to !== $redirect) {
            $inner = "<p style='background:#fff8e1;padding:9px 12px;border-radius:8px;font-size:12.5px;color:#7a5a00'>"
                . "<b>Modo pruebas:</b> destinatario real era <b>" . htmlspecialchars($to, ENT_QUOTES) . "</b>. "
                . "Reenviado a sistemas@lamarque.mx.</p>" . $html;
        }
        $mail->Body    = mail_wrap($subject, $inner);
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $html));
        foreach ($attachments as $att) {
            if (is_array($att) && is_file($att[0])) $mail->addAttachment($att[0], $att[1] ?? basename($att[0]));
            elseif (is_string($att) && is_file($att)) $mail->addAttachment($att);
        }
        $mail->send();
        return true;
    } catch (Throwable $e) {
        // No logear credenciales — solo el error de PHPMailer.
        error_log('[soporte-v2 mail] fallo envío: ' . $e->getMessage());
        return false;
    }
}
