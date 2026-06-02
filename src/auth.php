<?php
// Autenticación contra glpi_users (bcrypt) + scope por entidad desde glpi_profiles_users.

// profile_id -> rol interno de la app
function role_from_profile(int $profile): ?string
{
    switch ($profile) {
        case 9:  return 'sucursal';      // Self-service de sucursal
        case 10: return 'tecnico';
        case 11: return 'dalia';         // Admin Mejora Continua
        case 4:  return 'dalia';         // Super-Admin -> vista Dalia (superset)
        default: return null;            // Observer y otros: sin acceso al MVP
    }
}

function auth_login(string $name, string $pass): bool
{
    $u = Users::findByLogin($name);
    if (!$u || (int)$u['is_active'] !== 1) return false;
    if (!password_verify($pass, $u['password'])) return false;

    $pe = Users::primaryProfileEntity((int)$u['id']);
    if (!$pe) return false;
    $role = role_from_profile((int)$pe['profiles_id']);
    if ($role === null) return false;

    $entity_id = (int)$pe['entities_id'];
    $_SESSION['uid']         = (int)$u['id'];
    $_SESSION['uname']       = $u['name'];
    $_SESSION['display']     = trim(($u['firstname'] ?? '') . ' ' . ($u['realname'] ?? '')) ?: $u['name'];
    $_SESSION['role']        = $role;
    $_SESSION['entity_id']   = $entity_id;
    $_SESSION['entity_name'] = Users::entityName($entity_id);
    $_SESSION['recursive']   = (int)$pe['is_recursive'] === 1 || in_array($role, ['tecnico', 'dalia'], true);
    session_regenerate_id(true);
    return true;
}

function auth_logout(): void
{
    $_SESSION = [];
    session_destroy();
}

function current_user(): ?array
{
    if (empty($_SESSION['uid'])) return null;
    return [
        'id'          => $_SESSION['uid'],
        'name'        => $_SESSION['uname'],
        'display'     => $_SESSION['display'],
        'role'        => $_SESSION['role'],
        'entity_id'   => $_SESSION['entity_id'],
        'entity_name' => $_SESSION['entity_name'],
        'recursive'   => $_SESSION['recursive'],
    ];
}

function require_login(): array
{
    $u = current_user();
    if (!$u) redirect('login');
    return $u;
}

function require_role(array $roles): array
{
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) {
        deny('No tienes acceso a esta sección.', 403);
    }
    return $u;
}

// Devuelve fragmento WHERE + params para limitar a la entidad del usuario sucursal.
// Roles recursivos (técnico/dalia) no filtran por entidad.
function entity_scope(string $col = 't.entities_id'): array
{
    $u = current_user();
    if ($u && $u['role'] === 'sucursal') {
        return [' AND ' . $col . ' = :eid', [':eid' => $u['entity_id']]];
    }
    return ['', []];
}

// Ruta home según rol
function home_route_for(string $role): string
{
    return ['sucursal' => 'home', 'tecnico' => 'tec/home', 'dalia' => 'dalia/dashboard'][$role] ?? 'home';
}
