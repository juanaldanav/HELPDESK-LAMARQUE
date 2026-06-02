<?php
// PDO singleton a glpidb.
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = cfg('db');
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true, // permite reusar named placeholders (ej. LIKE :q dos veces)
        ]);
    }
    return $pdo;
}

// Helpers cortos
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function q_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}
function q_one(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}
function q_val(string $sql, array $params = [])
{
    return q($sql, $params)->fetchColumn();
}
