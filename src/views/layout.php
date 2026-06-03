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
  /* campos planos de la familia de acceso/forms */
  .fld{ width:100%; background:var(--canvas); border-radius:14px; padding:13px 16px; font-size:15px; outline:none; transition:box-shadow .15s ease, background .15s ease; }
  .fld::placeholder{ color:#90a0a0; }
  .fld:focus{ background:#fff; box-shadow:0 0 0 2px var(--brand); }
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
        <a href="<?= h(url('home')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Inicio</a>
        <a href="<?= h(url('tickets')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Tickets</a>
        <a href="<?= h(url('assets')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Activos</a>
      <?php elseif ($u['role'] === 'tecnico'): ?>
        <a href="<?= h(url('tec/home')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Mis tareas</a>
      <?php elseif ($u['role'] === 'dalia'): ?>
        <a href="<?= h(url('dalia/dashboard')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Dashboard</a>
        <a href="<?= h(url('dalia/tickets')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Tickets</a>
        <a href="<?= h(url('dalia/calendar')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Calendario</a>
        <a href="<?= h(url('dalia/assets')) ?>" class="px-3 py-1.5 rounded-xl font-semibold hover:bg-white/10">Activos</a>
      <?php endif; ?>
      <a href="<?= h(url('logout')) ?>" class="tap ml-1 px-3 py-1.5 rounded-xl bg-white/12 font-bold hover:bg-white/20">Salir</a>
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
<main class="max-w-6xl mx-auto px-4 py-6"><?= $content ?></main>
</body>
</html>
