<?php /** @var string $content @var string $title */ $u = current_user(); ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#006970">
<title><?= h($title ?? cfg('app_name')) ?></title>
<link rel="manifest" href="<?= h(cfg('base_url')) ?>/manifest.webmanifest">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: {
    brand: { DEFAULT: '#006970', dark: '#004f54', deep: '#003b3f', light: '#e3eded', tint: '#f0f5f5' },
    canvas: '#f3f5f5', surface: '#ffffff', ink: '#0f1c1d', muted: '#5d6f70', faint: '#90a0a0'
  },
  fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
  borderRadius: { card: '16px' }
} } };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  :root{
    --st-nuevo:#2563c9; --st-curso:#b5610f; --st-espera:#5f7173; --st-resuelto:#1d8a5a; --st-cerrado:#46585a;
    --u1:#d83a34; --u2:#e07a1a; --u3:#c79213; --u4:#3b8aa6; --u5:#9aa6a6;
    --brand:#006970; --canvas:#f3f5f5;
  }
  *{ -webkit-tap-highlight-color: transparent; }
  [x-cloak]{ display:none !important; }
  .tap{ transition: transform .12s ease; } .tap:active{ transform: scale(.97); }
  @media (prefers-reduced-motion: no-preference){
    .stagger>*{ opacity:0; transform:translateY(10px); animation:rise .42s cubic-bezier(.22,.61,.36,1) forwards; }
    .stagger>*:nth-child(1){animation-delay:.04s}.stagger>*:nth-child(2){animation-delay:.09s}
    .stagger>*:nth-child(3){animation-delay:.14s}.stagger>*:nth-child(4){animation-delay:.19s}
    .stagger>*:nth-child(5){animation-delay:.24s}.stagger>*:nth-child(6){animation-delay:.29s}
    .stagger>*:nth-child(7){animation-delay:.34s}.stagger>*:nth-child(8){animation-delay:.39s}
    @keyframes rise{ to{ opacity:1; transform:none; } }
  }
  /* toggle switch (checklist hoja de servicio) */
  .sw{ width:46px; height:28px; border-radius:999px; background:#d4dbdb; position:relative; transition:background .2s ease; }
  .sw::after{ content:''; position:absolute; top:3px; left:3px; width:22px; height:22px; border-radius:999px; background:#fff; transition:transform .2s cubic-bezier(.4,1.3,.6,1); }
  .peer:checked ~ .sw{ background:var(--brand); }
  .peer:checked ~ .sw::after{ transform:translateX(18px); }
  .peer:focus-visible ~ .sw{ outline:2px solid var(--brand); outline-offset:2px; }
  /* campos de la familia de acceso/forms — con contorno sutil del color de marca */
  .fld{ width:100%; background:var(--canvas); border:1px solid rgba(0,105,112,.18); border-radius:14px; padding:13px 16px; font-size:15px; outline:none; transition:box-shadow .15s ease, background .15s ease, border-color .15s ease; }
  .fld::placeholder{ color:#90a0a0; }
  .fld:focus{ background:#fff; border-color:var(--brand); box-shadow:0 0 0 2px var(--brand); }
  /* Contorno delgado del color del header para delimitar cada espacio (pedido del cliente) */
  .bg-surface{ border:1px solid rgba(0,105,112,.16); }
  .bg-brand-tint.rounded-card{ border:1px solid rgba(0,105,112,.22); }
</style>
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
<?php if ($u): ?>
<header class="bg-brand text-white sticky top-0 z-20">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
    <a href="<?= h(url(home_route_for($u['role']))) ?>" class="flex items-center gap-2.5 min-w-0">
      <span class="inline-flex items-center h-9 px-2.5 rounded-xl bg-white shrink-0">
        <img src="https://lamarque.mx/images/icons/lamarque.png" alt="Lamarque" class="h-4 w-auto object-contain">
      </span>
      <span class="hidden sm:flex items-center gap-1.5 text-[12px] font-bold text-white/85">
        <span class="w-1 h-1 rounded-full bg-white/45"></span><?php
          echo $u['role'] === 'tecnico' ? 'Técnico' : ($u['role'] === 'dalia' ? 'Mejora Continua' : h($u['entity_name']));
        ?>
      </span>
    </a>
    <nav class="flex items-center gap-1 text-sm">
      <?php if ($u['role'] === 'sucursal'): ?>
        <a href="<?= h(url('home')) ?>" class="hidden md:block px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Inicio</a>
        <a href="<?= h(url('tickets')) ?>" class="hidden md:block px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Tickets</a>
        <a href="<?= h(url('assets')) ?>" class="hidden md:block px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Activos</a>
      <?php elseif ($u['role'] === 'tecnico'): ?>
        <a href="<?= h(url('tec/home')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Mis tareas</a>
      <?php elseif ($u['role'] === 'dalia'):
          $cur = $_GET['r'] ?? 'dalia/dashboard';
          $links = [
              ['dalia/dashboard', 'Dashboard'],
              ['dalia/tickets',   'Tickets'],
              ['dalia/calendar',  'Calendario'],
              ['dalia/assets',    'Activos'],
              ['dalia/users',     'Equipo'],
              ['dalia/suppliers', 'Proveedores'],
          ];
          foreach ($links as [$route, $lbl]):
              $on = str_starts_with($cur, $route) || ($route === 'dalia/tickets' && $cur === 'dalia/view') || ($route === 'dalia/users' && str_starts_with($cur, 'dalia/user')) || ($route === 'dalia/suppliers' && str_starts_with($cur, 'dalia/supplier'));
      ?>
        <a href="<?= h(url($route)) ?>" class="px-3.5 py-2 rounded-xl font-bold <?= $on ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10' ?>"><?= $lbl ?></a>
      <?php endforeach; endif; ?>
      <a href="<?= h(url('account')) ?>" title="Mi cuenta" class="tap ml-1 w-9 h-9 grid place-items-center rounded-xl hover:bg-white/15" aria-label="Mi cuenta">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
      </a>
      <a href="<?= h(url('logout')) ?>" class="tap ml-0.5 px-3 py-1.5 rounded-xl bg-white/12 font-bold hover:bg-white/20">Salir</a>
      <span class="hidden md:grid w-9 h-9 ml-1 place-items-center rounded-xl bg-white text-brand-dark font-extrabold text-[12px]"><?php
        $ini = mb_strtoupper(mb_substr($u['display'], 0, 1));
        $parts = preg_split('~\s+~', trim($u['display']));
        if (count($parts) > 1) $ini .= mb_strtoupper(mb_substr(end($parts), 0, 1));
        echo h($ini);
      ?></span>
    </nav>
  </div>
</header>
<?php endif; ?>
<main class="max-w-6xl mx-auto px-4 py-6 <?= $u && $u['role'] === 'sucursal' ? 'pb-28 md:pb-6' : '' ?>"><?= $content ?></main>

<?php
// Tab bar solo en pantallas raíz; las de detalle (nuevo ticket, hilo) son "push" con su propia barra.
$rNow = $_GET['r'] ?? 'home';
$rootTabs = in_array($rNow, ['home', 'tickets', 'assets'], true);
if ($u && $u['role'] === 'sucursal' && $rootTabs):
    $r = $rNow;
    $tab = $r === 'tickets' ? 'tickets' : ($r === 'assets' ? 'assets' : 'home');
    $tabs = [
        ['home',    'Inicio',  url('home'),    '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9.5 21v-6h5v6"/>'],
        ['tickets', 'Tickets', url('tickets'), '<path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4Z"/><path d="M14 5v14"/>'],
        ['assets',  'Activos', url('assets'),  '<path d="M3.5 7 12 3l8.5 4-8.5 4Z"/><path d="M3.5 7v10l8.5 4 8.5-4V7"/><path d="M12 11v10"/>'],
    ];
?>
<!-- Tab bar inferior (solo móvil, rol sucursal) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-20 bg-surface px-2 pt-1.5 pb-4 flex items-stretch">
  <?php foreach ($tabs as [$key, $label, $href, $glyph]): $on = $tab === $key; ?>
    <a href="<?= h($href) ?>" class="tap flex-1 flex flex-col items-center justify-center gap-1 py-1 <?= $on ? 'text-brand' : 'text-faint' ?>">
      <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="<?= $on ? '2.1' : '1.8' ?>" stroke-linecap="round" stroke-linejoin="round"><?= $glyph ?></svg>
      <span class="text-[10.5px] font-bold tracking-tight"><?= $label ?></span>
    </a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>
<script>
// Los <select> nativos abren hacia arriba si no hay espacio abajo. Antes de que abra,
// subimos el select en el viewport para que siempre despliegue hacia abajo.
document.addEventListener('mousedown', function (e) {
  var s = e.target && e.target.closest ? e.target.closest('select') : null;
  if (!s) return;
  var r = s.getBoundingClientRect();
  if (window.innerHeight - r.bottom < 340) {
    window.scrollBy({ top: r.top - 110, behavior: 'instant' });
  }
}, true);
</script>
</body>
</html>
