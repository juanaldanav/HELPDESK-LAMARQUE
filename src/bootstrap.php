<?php
// Arranque de la app: config, sesión, dependencias.
error_reporting(E_ALL & ~E_DEPRECATED);

$__cfg_file = __DIR__ . '/../config.local.php';
if (!file_exists($__cfg_file)) {
    $__cfg_file = __DIR__ . '/../config.example.php';
}
$GLOBALS['__cfg'] = require $__cfg_file;

function cfg($key = null)
{
    $c = $GLOBALS['__cfg'];
    if ($key === null) return $c;
    return $c[$key] ?? null;
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mailer.php';
require __DIR__ . '/repo/Users.php';
require __DIR__ . '/repo/Tickets.php';
require __DIR__ . '/repo/Followups.php';
require __DIR__ . '/repo/Assets.php';
require __DIR__ . '/repo/PasswordReset.php';
require __DIR__ . '/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,   // solo HTTPS en producción; permite http en local
        'httponly' => true,     // no accesible desde JS
        'samesite' => 'Lax',
    ]);
    session_start();
}
