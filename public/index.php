<?php
// Front controller. Ruta simple vía ?r=
require __DIR__ . '/../src/bootstrap.php';

$r = $_GET['r'] ?? 'home';
$method = $_SERVER['REQUEST_METHOD'];

// ---- Helpers locales de subida de foto ----
function save_photo(?array $file, int $ticketId): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic'];
    $mime = mime_content_type($file['tmp_name']) ?: '';
    $ext = $allowed[$mime] ?? null;
    if (!$ext) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
    }
    $dir = rtrim(cfg('uploads_dir'), '/\\') . '/' . $ticketId;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fname = bin2hex(random_bytes(8)) . '.' . $ext;
    $rel = $ticketId . '/' . $fname;
    if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $fname)) {
        if (!@rename($file['tmp_name'], $dir . '/' . $fname)) return null; // fallback CLI/test
    }
    return $rel;
}

// Hasta $max fotos de un input multiple (photos[]). Devuelve rutas relativas guardadas.
function save_photos(?array $files, int $ticketId, int $max = 5): array
{
    if (!$files || !isset($files['name']) || !is_array($files['name'])) return [];
    $out = [];
    $n = min(count($files['name']), $max);
    for ($i = 0; $i < $n; $i++) {
        $one = [
            'name'     => $files['name'][$i] ?? '',
            'type'     => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $files['size'][$i] ?? 0,
        ];
        $rel = save_photo($one, $ticketId);
        if ($rel) $out[] = $rel;
    }
    return $out;
}

function serve_image(string $rel): void
{
    // valida ruta: <ticketId>/<archivo> — sin traversal
    if (!preg_match('~^\d+/[A-Za-z0-9._-]+$~', $rel) || strpos($rel, '..') !== false) { http_response_code(400); exit; }
    $path = rtrim(cfg('uploads_dir'), '/\\') . '/' . $rel;
    if (!is_file($path)) { http_response_code(404); exit; }
    $mime = mime_content_type($path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
}

try {
    switch ($r) {

        // ---------- AUTH ----------
        case 'login':
            if ($method === 'POST') {
                csrf_check();
                if (auth_login(trim($_POST['name'] ?? ''), $_POST['pass'] ?? '')) {
                    redirect(home_route_for(current_user()['role']));
                }
                render('login', ['error' => 'Usuario o contraseña incorrectos, o sin acceso al portal.'], false);
            } else {
                if (current_user()) redirect(home_route_for(current_user()['role']));
                render('login', [], false);
            }
            break;

        case 'logout':
            auth_logout();
            redirect('login');
            break;

        case 'forgot':
            if ($method === 'POST') {
                csrf_check();
                PasswordReset::request(trim($_POST['email'] ?? ''));
                render('forgot', ['sent' => true], false);
            } else {
                render('forgot', [], false);
            }
            break;

        case 'reset':
            $token = $_POST['t'] ?? $_GET['t'] ?? '';
            if ($method === 'POST') {
                csrf_check();
                $res = PasswordReset::resetWithToken($token, $_POST['pass'] ?? '', $_POST['confirm'] ?? '');
                if ($res['ok']) {
                    render('reset', ['done' => true], false);
                } else {
                    render('reset', ['token' => $token, 'valid' => PasswordReset::verify($token) !== null, 'error' => $res['error']], false);
                }
            } else {
                render('reset', ['token' => $token, 'valid' => PasswordReset::verify($token) !== null], false);
            }
            break;

        case 'img':
            require_login();
            serve_image($_GET['p'] ?? '');
            break;

        // ---------- SUCURSAL ----------
        case 'home':
            $u = require_role(['sucursal']);
            $counts = Tickets::counts();
            $recent = Tickets::listing(['limit' => 6]);
            render('sucursal/home', ['title' => 'Inicio', 'u' => $u, 'counts' => $counts, 'recent' => $recent]);
            break;

        case 'tickets':
            $u = require_role(['sucursal']);
            $status = status_filter($_GET['status'] ?? '');
            $list = Tickets::listing(['status' => $status, 'limit' => 200]);
            render('sucursal/tickets', ['title' => 'Mis tickets', 'list' => $list, 'fstatus' => $_GET['status'] ?? '']);
            break;

        case 'assets':
            $u = require_role(['sucursal']);
            $cats = Assets::categoryCounts($u['entity_id']);
            $active = $_GET['cat'] ?? ($cats[0]['key'] ?? null);
            $search = trim($_GET['q'] ?? '');
            $items = $active ? Assets::items($active, $u['entity_id'], $search ?: null) : [];
            render('sucursal/assets', ['title' => 'Mis activos', 'cats' => $cats, 'active' => $active, 'items' => $items, 'q' => $search]);
            break;

        case 'asset/search':
            $u = require_role(['sucursal', 'dalia', 'tecnico']);
            header('Content-Type: application/json');
            $q = trim($_GET['q'] ?? '');
            if (strlen($q) < 2) { echo json_encode([]); break; }
            $scope = $u['role'] === 'sucursal' ? $u['entity_id'] : null;
            echo json_encode(Assets::search($scope, $q));
            break;

        case 'ticket/new':
            $u = require_role(['sucursal']);
            render('sucursal/new', ['title' => 'Reportar problema', 'cats' => categories_grouped()]);
            break;

        case 'ticket/create':
            $u = require_role(['sucursal']);
            csrf_check();
            $name = trim($_POST['name'] ?? '');
            $content = trim($_POST['content'] ?? '');
            if ($name === '' || $content === '') { redirect('ticket/new'); }
            $name = mb_substr($name, 0, 250);
            // Urgencia: clamp 1..5
            $urgency = (int)($_POST['urgency'] ?? 3);
            if ($urgency < 1 || $urgency > 5) $urgency = 3;
            // Categoría: debe existir, si no -> 0
            $cat = (int)($_POST['category_id'] ?? 0);
            if ($cat && !q_val("SELECT 1 FROM glpi_itilcategories WHERE id = :c", [':c' => $cat])) $cat = 0;
            // Activo vinculado: validar clase conocida Y que pertenezca a la entidad del usuario (aislamiento)
            $asset_itemtype = $_POST['asset_itemtype'] ?? '';
            $asset_id = (int)($_POST['asset_id'] ?? 0);
            if (!$asset_itemtype || !$asset_id || !Assets::existsInEntity($asset_itemtype, $asset_id, $u['entity_id'])) {
                $asset_itemtype = ''; $asset_id = 0;
            }
            $id = Tickets::create([
                'entity_id'      => $u['entity_id'],
                'name'           => $name,
                'content'        => $content,
                'urgency'        => $urgency,
                'type'           => 1,
                'category_id'    => $cat,
                'requester_id'   => $u['id'],
                'asset_itemtype' => $asset_itemtype ?: null,
                'asset_id'       => $asset_id ?: null,
            ]);
            // fotos iniciales (hasta 5) -> followup del solicitante
            $rels = save_photos($_FILES['photos'] ?? null, $id);
            if ($rels) {
                $msg = count($rels) === 1 ? 'Foto del reporte' : 'Fotos del reporte';
                foreach ($rels as $rel) $msg .= "\n[foto:$rel]";
                Followups::add($id, $u['id'], $msg, 0, 1);
            }
            notify_new_ticket($id, $u);
            redirect('ticket/view', ['id' => $id, 'ok' => 1]);
            break;

        case 'ticket/view':
            $u = require_role(['sucursal']);
            $t = Tickets::get((int)($_GET['id'] ?? 0));
            if (!$t) { deny('Este ticket no está disponible para tu cuenta.', 404); }
            $thread = Followups::thread($t);
            $assets = linked_assets_full($t['id']);
            render('sucursal/thread', ['title' => 'Ticket #' . $t['id'], 't' => $t, 'thread' => $thread, 'assets' => $assets, 'ok' => !empty($_GET['ok'])]);
            break;

        case 'ticket/reply':
            $u = require_role(['sucursal']);
            csrf_check();
            $id = (int)($_POST['id'] ?? 0);
            $t = Tickets::get($id);
            if (!$t) { http_response_code(404); exit; }
            $msg = trim($_POST['content'] ?? '');
            $rel = save_photo($_FILES['photo'] ?? null, $id);
            if ($rel) $msg .= "\n[foto:$rel]";
            if (trim($msg) !== '') Followups::add($id, $u['id'], $msg, 0, 1);
            redirect('ticket/view', ['id' => $id]);
            break;

        // ---------- TÉCNICO ----------
        case 'tec/home':
            $u = require_role(['tecnico']);
            $list = Tickets::listing(['assigned_to' => $u['id'], 'status' => [1, 2, 3, 4], 'limit' => 200]);
            render('tecnico/home', ['title' => 'Mis tareas', 'list' => $list]);
            break;

        case 'tec/detail':
            $u = require_role(['tecnico']);
            $t = Tickets::get((int)($_GET['id'] ?? 0));
            if (!$t) { deny('Este ticket no está disponible para tu cuenta.', 404); }
            $thread = Followups::thread($t);
            $assets = linked_assets_full($t['id']);
            render('tecnico/detail', ['title' => 'Tarea #' . $t['id'], 't' => $t, 'thread' => $thread, 'assets' => $assets, 'ok' => !empty($_GET['ok'])]);
            break;

        case 'tec/comment':
            $u = require_role(['tecnico']);
            csrf_check();
            $id = (int)($_POST['id'] ?? 0);
            $msg = trim($_POST['content'] ?? '');
            $rel = save_photo($_FILES['photo'] ?? null, $id);
            if ($rel) $msg .= "\n[foto:$rel]";
            if (trim($msg) !== '') Followups::add($id, $u['id'], $msg, 0, 4);
            redirect('tec/detail', ['id' => $id]);
            break;

        case 'tec/close':
            $u = require_role(['tecnico']);
            csrf_check();
            $id = (int)($_POST['id'] ?? 0);
            $t = Tickets::get($id);
            if (!$t) { http_response_code(404); exit; }
            $sheet = build_service_sheet($_POST);
            $rel = save_photo($_FILES['photo'] ?? null, $id);
            if ($rel) $sheet .= "\n[foto:$rel]";
            Followups::add($id, $u['id'], $sheet, 0, 4);
            Tickets::close($id);
            notify_closed($id);
            redirect('tec/detail', ['id' => $id, 'ok' => 1]);
            break;

        // ---------- DALIA ----------
        case 'dalia/dashboard':
            $u = require_role(['dalia']);
            $period = $_GET['period'] ?? date('Y-m');
            render('dalia/dashboard', ['title' => 'Dashboard', 'period' => $period, 'kpi' => dashboard_kpis($period)]);
            break;

        case 'dalia/tickets':
            $u = require_role(['dalia']);
            $opts = [
                'entity'   => (int)($_GET['entity'] ?? 0) ?: null,
                'category' => (int)($_GET['category'] ?? 0) ?: null,
                'tecnico'  => (int)($_GET['tecnico'] ?? 0) ?: null,
                'status'   => status_filter($_GET['status'] ?? ''),
                'from'     => $_GET['from'] ?? null,
                'to'       => $_GET['to'] ?? null,
                'search'   => trim($_GET['q'] ?? '') ?: null,
                'limit'    => 300,
            ];
            $list = Tickets::listing($opts);
            render('dalia/tickets', [
                'title' => 'Tickets', 'list' => $list, 'f' => $_GET,
                'sucursales' => Users::sucursales(), 'tecnicos' => Users::tecnicos(), 'cats' => categories_grouped(),
            ]);
            break;

        case 'dalia/view':
            $u = require_role(['dalia']);
            $t = Tickets::get((int)($_GET['id'] ?? 0));
            if (!$t) { deny('Este ticket no está disponible para tu cuenta.', 404); }
            $thread = Followups::thread($t);
            $assets = linked_assets_full($t['id']);
            render('dalia/view', ['title' => 'Ticket #' . $t['id'], 't' => $t, 'thread' => $thread, 'assets' => $assets, 'tecnicos' => Users::tecnicos(), 'ok' => !empty($_GET['ok'])]);
            break;

        case 'dalia/assign':
            $u = require_role(['dalia']);
            csrf_check();
            $id = (int)($_POST['id'] ?? 0);
            $tec = (int)($_POST['tecnico'] ?? 0);
            $urg = (int)($_POST['urgency'] ?? 0);
            if ($urg >= 1 && $urg <= 5) Tickets::setUrgency($id, $urg);
            $fechaAt = $_POST['fecha'] ?? '';
            if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $fechaAt)) $fechaAt = '';
            if ($tec) {
                Tickets::assign($id, $tec);
                notify_assigned($id, $tec, $fechaAt ?: null);
                // Fecha de atención -> evento correctivo en el calendario
                if ($fechaAt) {
                    $tk = q_one("SELECT name, entities_id FROM glpi_tickets WHERE id = :id", [':id' => $id]);
                    if ($tk) {
                        Agenda::create([
                            'entity_id'   => (int)$tk['entities_id'],
                            'tipo'        => 'Correctivo',
                            'clase'       => 'correctivo',
                            'fecha'       => $fechaAt,
                            'tecnico_id'  => $tec,
                            'tickets_id'  => $id,
                            'descripcion' => mb_substr('Ticket #' . $id . ' — ' . $tk['name'], 0, 400),
                            'creator_id'  => $u['id'],
                        ]);
                    }
                }
            }
            redirect('dalia/view', ['id' => $id, 'ok' => 1]);
            break;

        case 'dalia/export':
            $u = require_role(['dalia']);
            export_tickets_csv($_GET);
            break;

        case 'dalia/calendar':
            $u = require_role(['dalia']);
            $ym = $_GET['ym'] ?? date('Y-m');
            if (!preg_match('~^\d{4}-(0[1-9]|1[0-2])$~', $ym)) $ym = date('Y-m');
            [$y, $m] = array_map('intval', explode('-', $ym));
            $fil = [
                'entity' => (int)($_GET['entity'] ?? 0) ?: null,
                'tipo'   => in_array($_GET['tipo'] ?? '', Agenda::TIPOS, true) ? $_GET['tipo'] : null,
            ];
            render('dalia/calendar', [
                'title' => 'Calendario', 'y' => $y, 'm' => $m, 'ym' => $ym,
                'events' => Agenda::month($y, $m, array_filter($fil)),
                'fil' => $fil, 'sucursales' => Users::sucursales(), 'tecnicos' => Users::tecnicos(),
                'ok' => !empty($_GET['ok']),
            ]);
            break;

        case 'dalia/agenda/create':
            $u = require_role(['dalia']);
            csrf_check();
            $fecha = $_POST['fecha'] ?? '';
            $ffin  = $_POST['fecha_fin'] ?? '';
            if (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $fecha)) redirect('dalia/calendar');
            if ($ffin !== '' && (!preg_match('~^\d{4}-\d{2}-\d{2}$~', $ffin) || $ffin < $fecha)) $ffin = '';
            $aid = Agenda::create([
                'entity_id'   => (int)($_POST['entity'] ?? 0),
                'tipo'        => $_POST['tipo'] ?? 'Otro',
                'clase'       => 'preventivo',
                'fecha'       => $fecha,
                'fecha_fin'   => $ffin ?: null,
                'tecnico_id'  => (int)($_POST['tecnico'] ?? 0),
                'descripcion' => mb_substr(trim($_POST['descripcion'] ?? ''), 0, 400),
                'creator_id'  => $u['id'],
            ]);
            notify_agendado($aid);
            redirect('dalia/calendar', ['ym' => substr($fecha, 0, 7), 'ok' => 1]);
            break;

        case 'dalia/agenda/estado':
            $u = require_role(['dalia']);
            csrf_check();
            Agenda::setEstado((int)($_POST['id'] ?? 0), $_POST['estado'] ?? '');
            $ymBack = $_POST['ym'] ?? date('Y-m');
            if (!preg_match('~^\d{4}-\d{2}$~', $ymBack)) $ymBack = date('Y-m');
            redirect('dalia/calendar', ['ym' => $ymBack]);
            break;

        case 'dalia/assets':
            $u = require_role(['dalia']);
            $entity = (int)($_GET['entity'] ?? 0) ?: null;
            $cats = Assets::categoryCounts($entity);
            $active = $_GET['cat'] ?? ($cats[0]['key'] ?? null);
            $search = trim($_GET['q'] ?? '');
            $items = $active ? Assets::items($active, $entity, $search ?: null) : [];
            render('dalia/assets', [
                'title' => 'Activos', 'cats' => $cats, 'active' => $active,
                'items' => $items, 'q' => $search, 'entity' => $entity, 'sucursales' => Users::sucursales(),
            ]);
            break;

        case 'dalia/asset/baja':
            $u = require_role(['dalia']);
            csrf_check();
            Assets::baja($_POST['type_key'] ?? '', (int)($_POST['asset_id'] ?? 0));
            redirect('dalia/assets', array_filter([
                'entity' => $_POST['entity'] ?? '', 'cat' => $_POST['cat'] ?? '', 'q' => $_POST['q'] ?? '',
            ]));
            break;

        case 'print':
            $u = require_login();
            $t = Tickets::get((int)($_GET['id'] ?? 0)); // scope sucursal aplicado en get()
            if (!$t) { deny('Este ticket no está disponible para tu cuenta.', 404); }
            render('print', ['t' => $t, 'thread' => Followups::thread($t), 'assets' => linked_assets_full((int)$t['id'])], false);
            break;

        default:
            if (current_user()) redirect(home_route_for(current_user()['role']));
            redirect('login');
    }
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[soporte-v2] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    echo '<pre style="font-family:monospace;padding:1rem">Error interno. Revisa el log.';
    if (ini_get('display_errors')) echo "\n\n" . h($e->getMessage()) . "\n" . h($e->getFile()) . ':' . $e->getLine();
    echo '</pre>';
}
