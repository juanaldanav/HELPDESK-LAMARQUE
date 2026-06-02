<?php
// Tests CLI del flujo de reseteo. Corre en el servidor (DB localhost):
//   sudo -u www-data php tests/reset_test.php
// Usa un usuario fixture desechable (__pwtest); nunca toca cuentas reales.
require __DIR__ . '/../src/bootstrap.php';

$GLOBALS['__cfg']['smtp']['enabled'] = false; // no enviar correo real en tests

$PASS = 0; $FAIL = 0;
function ok(string $name, bool $cond) {
    global $PASS, $FAIL;
    if ($cond) { $PASS++; echo "  PASS  $name\n"; }
    else { $FAIL++; echo "  FAIL  $name\n"; }
}

// ---- fixture ----
function make_fixture(): int
{
    teardown_fixture();
    db()->prepare("INSERT INTO glpi_users (name, password, is_active) VALUES ('__pwtest', :p, 1)")
        ->execute([':p' => password_hash('OldPass123', PASSWORD_DEFAULT)]);
    $uid = (int) db()->lastInsertId();
    db()->prepare("INSERT INTO glpi_useremails (users_id, email, is_default) VALUES (:u, '__pwtest@lamarque.mx', 1)")
        ->execute([':u' => $uid]);
    return $uid;
}
function teardown_fixture(): void
{
    $uid = q_val("SELECT id FROM glpi_users WHERE name = '__pwtest'");
    if ($uid) {
        db()->prepare("DELETE FROM glpi_useremails WHERE users_id = :u")->execute([':u' => $uid]);
        db()->prepare("DELETE FROM glpi_users WHERE id = :u")->execute([':u' => $uid]);
    }
}

try {
    // 1) tracer: token emitido verifica al usuario correcto
    $uid = make_fixture();
    $tok = PasswordReset::issueToken($uid);
    ok('token crudo mide 64 hex', strlen($tok) === 64 && ctype_xdigit($tok));
    $v = PasswordReset::verify($tok);
    ok('verify(token válido) devuelve el usuario', $v !== null && $v['id'] === $uid);

    // 2) token inválido -> null
    ok('verify(token inexistente) -> null', PasswordReset::verify('deadbeef') === null);
    ok('verify(vacío) -> null', PasswordReset::verify('') === null);

    // 3) DB guarda sha1, no el token crudo
    $stored = q_val("SELECT password_forget_token FROM glpi_users WHERE id = $uid");
    ok('DB guarda sha1(token), no el token crudo', $stored === sha1($tok) && $stored !== $tok);

    // 4) expiración: token con fecha de hace 20 min -> null
    db()->exec("UPDATE glpi_users SET password_forget_token_date = NOW() - INTERVAL 20 MINUTE WHERE id = $uid");
    ok('verify(token expirado >15min) -> null', PasswordReset::verify($tok) === null);

    // 5) reset exitoso cambia la contraseña a bcrypt y es de un solo uso
    $tok2 = PasswordReset::issueToken($uid);
    $r = PasswordReset::resetWithToken($tok2, 'NuevaClave99', 'NuevaClave99');
    ok('reset válido -> ok', $r['ok'] === true);
    $newhash = q_val("SELECT password FROM glpi_users WHERE id = $uid");
    ok('contraseña nueva verifica con bcrypt', password_verify('NuevaClave99', $newhash));
    ok('contraseña vieja ya no verifica', !password_verify('OldPass123', $newhash));
    ok('token limpiado tras reset (single-use)', q_val("SELECT password_forget_token FROM glpi_users WHERE id = $uid") === null);
    ok('reusar token tras reset -> falla', PasswordReset::resetWithToken($tok2, 'OtraClave99', 'OtraClave99')['ok'] === false);

    // 6) validaciones de contraseña
    $tok3 = PasswordReset::issueToken($uid);
    ok('rechaza contraseña corta (<8)', PasswordReset::resetWithToken($tok3, 'corta', 'corta')['ok'] === false);
    ok('token sigue válido tras intento fallido', PasswordReset::verify($tok3) !== null);
    $tok4 = PasswordReset::issueToken($uid);
    ok('rechaza confirmación que no coincide', PasswordReset::resetWithToken($tok4, 'ClaveBuena1', 'OtraDistinta1')['ok'] === false);

    // 7) request() no revela existencia (siempre true)
    ok('request(email inexistente) -> true', PasswordReset::request('noexiste@lamarque.mx') === true);
    ok('request(email real) -> true', PasswordReset::request('__pwtest@lamarque.mx') === true);
    ok('request(email real) emitió token (date no null)',
        q_val("SELECT password_forget_token_date FROM glpi_users WHERE id = $uid") !== null);
    ok('request(email inexistente) no creó cuentas', (int) q_val("SELECT COUNT(*) FROM glpi_users WHERE name='noexiste'") === 0);

} finally {
    teardown_fixture();
}

echo "\n== RESULTADO: $PASS passed, $FAIL failed ==\n";
exit($FAIL === 0 ? 0 : 1);
