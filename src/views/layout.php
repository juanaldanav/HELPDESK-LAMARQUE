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
  colors: { brand: { DEFAULT: '#006970', dark: '#004f54', light: '#e6f0f0' } },
  fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] }
} } };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
<?php if ($u): ?>
<header class="bg-brand text-white shadow-sm sticky top-0 z-20">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
    <a href="<?= h(url(home_route_for($u['role']))) ?>" class="flex items-center gap-2 font-bold tracking-tight">
      <span class="inline-grid place-items-center w-8 h-8 rounded-lg bg-white/15">☕</span>
      <span>Soporte Lamarque</span>
    </a>
    <nav class="flex items-center gap-1 text-sm">
      <?php if ($u['role'] === 'sucursal'): ?>
        <a href="<?= h(url('home')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Inicio</a>
        <a href="<?= h(url('tickets')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Mis tickets</a>
        <a href="<?= h(url('assets')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Mis activos</a>
      <?php elseif ($u['role'] === 'tecnico'): ?>
        <a href="<?= h(url('tec/home')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Mis tareas</a>
      <?php elseif ($u['role'] === 'dalia'): ?>
        <a href="<?= h(url('dalia/dashboard')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Dashboard</a>
        <a href="<?= h(url('dalia/tickets')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Tickets</a>
        <a href="<?= h(url('dalia/calendar')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Calendario</a>
        <a href="<?= h(url('dalia/assets')) ?>" class="px-3 py-1.5 rounded-lg hover:bg-white/10">Activos</a>
      <?php endif; ?>
      <span class="hidden sm:inline px-3 text-white/70 text-xs"><?= h($u['display']) ?><?= $u['role']==='sucursal' ? ' · '.h($u['entity_name']) : '' ?></span>
      <a href="<?= h(url('logout')) ?>" class="px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25">Salir</a>
    </nav>
  </div>
</header>
<?php endif; ?>
<main class="max-w-6xl mx-auto px-4 py-6"><?= $content ?></main>
</body>
</html>
