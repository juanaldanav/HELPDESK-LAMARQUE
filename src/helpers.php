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
// Sólidos AA del design-system (mismos en TODAS las pantallas). Ver design/design-system.md.
const STATUS_HEX = [1 => '#2563c9', 2 => '#b5610f', 3 => '#b5610f', 4 => '#5f7173', 5 => '#1d8a5a', 6 => '#46585a'];
function status_chip(int $s): string
{
    $hex = STATUS_HEX[$s] ?? '#5f7173';
    return '<span class="inline-block text-[11px] font-bold text-white px-2.5 py-1 rounded-full whitespace-nowrap" style="background:' . $hex . '">' . h(status_label($s)) . '</span>';
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
const URGENCY_HEX = [1 => '#d83a34', 2 => '#e07a1a', 3 => '#c79213', 4 => '#3b8aa6', 5 => '#9aa6a6'];
function urgency_chip(int $u): string
{
    $hex = URGENCY_HEX[$u] ?? '#9aa6a6';
    return '<span class="inline-flex items-center gap-1 text-[12px] font-bold" style="color:' . $hex . '">'
        . '<span class="w-1.5 h-1.5 rounded-full" style="background:' . $hex . '"></span>' . h(urgency_label($u)) . '</span>';
}

// Prioridad asignada por el SISTEMA según categoría (ITIL: el solicitante no la elige).
// Matriz por criticidad de la categoría. Dalia/agente puede ajustarla luego.
function urgency_for_category(int $catId): int
{
    if ($catId <= 0) return 3;
    $n = mb_strtolower((string) q_val("SELECT completename FROM glpi_itilcategories WHERE id = :c", [':c' => $catId]));
    if ($n === '') return 3;
    // Muy alta (1): paro de servicio vital
    if (str_contains($n, 'crítico') || str_contains($n, 'critico') || str_contains($n, 'sin agua')
        || str_contains($n, 'sin internet') || str_contains($n, 'red caída') || str_contains($n, 'fuga')) return 1;
    // Alta (2): correctivo (algo descompuesto)
    if (str_contains($n, 'correctivo') || str_contains($n, 'wansoft') || str_contains($n, 'kiosko')) return 2;
    // Baja (4): preventivo / estético / jardinería / limpieza
    if (str_contains($n, 'preventivo') || str_contains($n, 'jardiner') || str_contains($n, 'limpieza')
        || str_contains($n, 'pintura') || str_contains($n, 'fachada')) return 4;
    // Media (3): soporte TI, infraestructura general y demás
    return 3;
}

// ---- Iconos SVG inline del design-system (trazo 1.7-1.9, tipo Lucide) ----
function svg_icon(string $name, int $size = 15): string
{
    $paths = [
        // tipos de mantenimiento / interfaz
        'snow'     => '<path d="M12 2v20M2 12h20M5 5l14 14M19 5 5 19M12 5l-3 2 3 2 3-2-3-2M12 19l-3-2 3-2 3 2-3 2"/>',
        'drop'     => '<path d="M12 3s6 6.5 6 10.5a6 6 0 0 1-12 0C6 9.5 12 3 12 3Z"/>',
        'air'      => '<path d="M3 8h13a3 3 0 1 0-3-3M3 13h16a3 3 0 1 1-3 3M3 18h10a2.5 2.5 0 1 1-2.5 2.5"/>',
        'cup'      => '<path d="M4 9h13v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9Z"/><path d="M17 10h2.5a2.5 2.5 0 0 1 0 5H17M8 2.5c-.6.8-.6 1.7 0 2.5M12 2.5c-.6.8-.6 1.7 0 2.5"/>',
        'wrench'   => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.2L3 17.8 6.2 21l6.3-6.3a4 4 0 0 0 5.2-5.4l-2.5 2.5-2.3-.4-.4-2.3 2.5-2.5Z"/>',
        'building' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/>',
        'bolt'     => '<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/>',
        'camera'   => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3.2"/>',
        'check'    => '<path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/>',
        'back'     => '<path d="M15 18l-6-6 6-6"/>',
        'close'    => '<path d="M18 6 6 18M6 6l12 12"/>',
        'external' => '<path d="M10 14 21 3M15 3h6v6M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
        'mail'     => '<path d="M4 7l8 6 8-6"/><rect x="3" y="5" width="18" height="14" rx="2.5"/>',
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 9h18"/>',
        'home'     => '<path d="M3 11l9-8 9 8"/><path d="M5 9.5V21h14V9.5"/>',
        'list'     => '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'grid'     => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'print'    => '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/>',
        // 9 categorías de activo (design-system §2.1)
        'refrigeracion'         => '<rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M5 10h14M9 5.5v2M9 13v3.5"/>',
        'maquinariadecafe'      => '<path d="M5 3h14v4H5z"/><path d="M7 7v4a3 3 0 0 0 3 3h1a3 3 0 0 0 3-3V7"/><path d="M15 9h2.5a1.5 1.5 0 0 1 0 3H15"/><path d="M7 21h8M11 17v4"/>',
        'mobiliario'            => '<path d="M6 11V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4"/><path d="M4 11h16v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4Z"/><path d="M6 16v4M18 16v4"/>',
        'electronicayseguridad' => '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3Z"/><path d="M9 12l2 2 4-4.5"/>',
        'jardineria'            => '<path d="M4 21c0-8 6-14 16-15 0 10-6 15-16 15Z"/><path d="M5 21c3-6 7-9 11-10.5"/>',
        'televisione'           => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'utensilio'             => '<path d="M6 3v6a2 2 0 0 0 4 0V3M8 3v18"/><path d="M16 3c-1.6 1-2.2 2.8-2.2 5.2 0 2 .7 3 2.2 3.4V21"/>',
        'herramienta_soporte'   => '<rect x="3" y="8" width="18" height="11" rx="2"/><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'computer'              => '<rect x="3" y="4" width="13" height="9" rx="1.5"/><path d="M7 17h6M9.5 13v4"/><rect x="18" y="5" width="3" height="14" rx="1"/>',
    ];
    $p = $paths[$name] ?? $paths['wrench'];
    $sw = in_array($name, ['back', 'close', 'building'], true) ? '2.1' : '1.8';
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round">' . $p . '</svg>';
}

// Color propio por categoría de activo ("color adepto": jardinería verde, refrigeración azul, etc.)
const ASSET_CAT_COLOR = [
    'refrigeracion'         => '#2456c4',
    'maquinariadecafe'      => '#b5610f',
    'mobiliario'            => '#8b5e34',
    'electronicayseguridad' => '#5a4bd1',
    'jardineria'            => '#1d8a5a',
    'televisione'           => '#6d28d9',
    'utensilio'             => '#0e8091',
    'herramienta_soporte'   => '#5f7173',
    'computer'              => '#0c83c4',
];
function asset_color(string $typeKey): string
{
    return ASSET_CAT_COLOR[$typeKey] ?? '#006970';
}
// Icono de categoría de activo en su color, dentro de cuadro tintado del mismo color.
function asset_icon_box(string $typeKey, int $icon = 18, string $boxClasses = 'w-9 h-9 rounded-xl'): string
{
    $c = asset_color($typeKey);
    return '<span class="grid place-items-center shrink-0 ' . $boxClasses . '" style="background:' . $c . '1a;color:' . $c . '">'
        . svg_icon($typeKey, $icon) . '</span>';
}

// Estado de activo: punto + etiqueta (design-system §1.2)
const ASSET_STATE_HEX = [1 => '#1d8a5a', 2 => '#b5610f', 3 => '#d83a34', 4 => '#5f7173', 5 => '#9aa6a6'];
function asset_state_dot(int $stateId, string $label): string
{
    $hex = ASSET_STATE_HEX[$stateId] ?? '#9aa6a6';
    return '<span class="inline-flex items-center gap-1 text-[12px] font-bold" style="color:' . $hex . '">'
        . '<span class="w-1.5 h-1.5 rounded-full" style="background:' . $hex . '"></span>' . h($label) . '</span>';
}

// Icono según el nombre de la categoría ITIL / tipo de mantenimiento.
function icon_for_category(?string $cat, int $size = 15): string
{
    $c = mb_strtolower($cat ?? '');
    $name = 'wrench';
    if (str_contains($c, 'hielo')) $name = 'snow';
    elseif (str_contains($c, 'refriger') || str_contains($c, 'vitrina')) $name = 'refrigeracion';
    elseif (str_contains($c, 'agua') || str_contains($c, 'plomer') || str_contains($c, 'fuga') || str_contains($c, 'filtro')) $name = 'drop';
    elseif (str_contains($c, 'minisplit') || str_contains($c, 'a/c')) $name = 'air';
    elseif (str_contains($c, 'caf')) $name = 'maquinariadecafe';
    elseif (str_contains($c, 'mobiliario') || str_contains($c, 'carpinter')) $name = 'mobiliario';
    elseif (str_contains($c, 'cómputo') || str_contains($c, 'computo') || str_contains($c, 'pc') || str_contains($c, 'internet') || str_contains($c, 'wansoft') || str_contains($c, 'impresora') || str_contains($c, 'kiosko') || str_contains($c, 'ti ')) $name = 'computer';
    elseif (str_contains($c, 'cámara') || str_contains($c, 'alarma') || str_contains($c, 'segurid')) $name = 'electronicayseguridad';
    elseif (str_contains($c, 'electri') || str_contains($c, 'alumbrado')) $name = 'bolt';
    elseif (str_contains($c, 'jardiner')) $name = 'jardineria';
    return svg_icon($name, $size);
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
    // Contenido histórico de GLPI puede venir con HTML *encodeado* (&lt;p&gt;…):
    // 1) decodificar entidades  2) saltos por <br>/</p>  3) aplanar tags a texto.
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    $content = preg_replace('~<br\s*/?>|</p>~i', "\n", $content);
    $content = strip_tags($content);

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
