<?php
// Utilidades compartidas: escape, csrf, mapas de estado/urgencia, render, rutas.

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function url(string $r, array $params = []): string
{
    $base = cfg('base_url');
    $qs = http_build_query(array_merge(['r' => $r], $params));
    return ($base === '' ? '' : $base) . '/?' . $qs;
}

function redirect(string $r, array $params = []): void
{
    header('Location: ' . url($r, $params));
    exit;
}

// Página de acceso denegado / no encontrado, con botón a inicio. Termina la ejecución.
function deny(string $msg, int $code = 403): void
{
    http_response_code($code);
    $home = current_user() ? url(home_route_for(current_user()['role'])) : url('login');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Sin acceso</title>';
    echo '<div style="font-family:\'Plus Jakarta Sans\',system-ui,sans-serif;max-width:420px;margin:16vh auto;text-align:center;padding:24px;color:#1e293b">';
    echo '<div style="width:56px;height:56px;border-radius:14px;background:#006970;color:#fff;display:grid;place-items:center;margin:0 auto 12px;font-size:26px">!</div>';
    echo '<h1 style="font-size:19px;margin:.4rem 0;font-weight:700">' . h($msg) . '</h1>';
    echo '<a href="' . h($home) . '" style="display:inline-block;margin-top:14px;background:#006970;color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none;font-weight:600">Volver al inicio</a></div>';
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('CSRF token inválido. Recarga la página.');
    }
}

// ---- Estado de ticket (glpi_tickets.status) ----
const TICKET_STATUS = [
    1 => 'Nuevo',
    2 => 'En curso',
    3 => 'En curso',
    4 => 'En espera',
    5 => 'Resuelto',
    6 => 'Cerrado',
];
function status_label(int $s): string { return TICKET_STATUS[$s] ?? '—'; }
function status_classes(int $s): string
{
    return [
        1 => 'bg-blue-100 text-blue-800',
        2 => 'bg-amber-100 text-amber-800',
        3 => 'bg-amber-100 text-amber-800',
        4 => 'bg-gray-200 text-gray-700',
        5 => 'bg-green-100 text-green-800',
        6 => 'bg-slate-200 text-slate-700',
    ][$s] ?? 'bg-gray-100 text-gray-700';
}
function status_chip(int $s): string
{
    return '<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium ' . status_classes($s) . '">' . h(status_label($s)) . '</span>';
}
function is_open_status(int $s): bool { return in_array($s, [1, 2, 3, 4], true); }
// Normaliza el filtro de status de un GET a un arreglo válido (solo 1..6) o vacío.
function status_filter($v): array
{
    if ($v === null || $v === '') return [];
    $i = (int)$v;
    return in_array($i, [1, 2, 3, 4, 5, 6], true) ? [$i] : [];
}

// ---- Urgencia (glpi_tickets.urgency) 1=muy alta .. 5=muy baja ----
const URGENCY = [1 => 'Muy alta', 2 => 'Alta', 3 => 'Media', 4 => 'Baja', 5 => 'Muy baja'];
function urgency_label(int $u): string { return URGENCY[$u] ?? '—'; }
function urgency_classes(int $u): string
{
    return [
        1 => 'bg-red-100 text-red-800',
        2 => 'bg-orange-100 text-orange-800',
        3 => 'bg-yellow-100 text-yellow-800',
        4 => 'bg-blue-100 text-blue-700',
        5 => 'bg-gray-100 text-gray-600',
    ][$u] ?? 'bg-gray-100 text-gray-700';
}
function urgency_chip(int $u): string
{
    return '<span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium ' . urgency_classes($u) . '">' . h(urgency_label($u)) . '</span>';
}

function fecha(?string $dt): string
{
    if (!$dt || $dt === '0000-00-00 00:00:00') return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/M/Y H:i', $ts) : h($dt);
}

// ---- Render seguro de un mensaje del hilo ----
// El contenido puede traer marcadores [foto:ruta]. Texto se escapa; fotos -> thumbnail.
function msg_html(string $content): string
{
    // Contenido histórico de GLPI puede venir con HTML: lo aplanamos a texto.
    $content = preg_replace('~<br\s*/?>~i', "\n", $content);
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

    $photos = [];
    $content = preg_replace_callback('~\[foto:([^\]]+)\]~', function ($m) use (&$photos) {
        $photos[] = $m[1];
        return '';
    }, $content);

    $out = nl2br(h(trim($content)));
    if ($photos) {
        $out .= '<div class="mt-2 flex flex-wrap gap-2">';
        foreach ($photos as $rel) {
            // Solo rutas seguras <ticketId>/<archivo>, sin traversal.
            if (!preg_match('~^\d+/[A-Za-z0-9._-]+$~', $rel) || strpos($rel, '..') !== false) continue;
            $src = url('img', ['p' => $rel]);
            $out .= '<a href="' . h($src) . '" target="_blank"><img src="' . h($src) . '" class="w-24 h-24 object-cover rounded-lg border border-slate-200"></a>';
        }
        $out .= '</div>';
    }
    return $out;
}

// Incluye un parcial reutilizable con sus variables.
function partial(string $name, array $vars = []): void
{
    extract($vars);
    include __DIR__ . '/views/partials/' . $name . '.php';
}

// ---- Render de vistas con layout ----
function render(string $view, array $vars = [], bool $with_layout = true): void
{
    $title = $vars['title'] ?? cfg('app_name');
    extract($vars);
    ob_start();
    include __DIR__ . '/views/' . $view . '.php';
    $content = ob_get_clean();
    if ($with_layout) {
        include __DIR__ . '/views/layout.php';
    } else {
        echo $content;
    }
}
