<?php /** @var string|null $error */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar · Soporte Lamarque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#006970',dark:'#004f54'}},fontFamily:{sans:['"Plus Jakarta Sans"','system-ui','sans-serif']}}}};</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-100 font-sans text-slate-800 grid place-items-center px-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="mx-auto w-16 h-16 rounded-2xl bg-brand grid place-items-center text-white text-3xl mb-3">☕</div>
      <h1 class="text-2xl font-extrabold tracking-tight">Soporte Lamarque</h1>
      <p class="text-slate-500 text-sm mt-1">Portal de mantenimiento y activos</p>
    </div>
    <form method="post" action="<?= h(url('login')) ?>" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
      <?= csrf_field() ?>
      <?php if (!empty($error)): ?>
        <div class="bg-red-50 text-red-700 text-sm rounded-lg px-3 py-2 border border-red-200"><?= h($error) ?></div>
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
        <input name="name" autocomplete="username" autofocus required
               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none"
               placeholder="ej. guadalupe">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
        <input type="password" name="pass" autocomplete="current-password" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none"
               placeholder="••••••••">
      </div>
      <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg py-2.5 transition">Entrar</button>
      <p class="text-center"><a href="<?= h(url('forgot')) ?>" class="text-sm text-brand hover:underline">¿Olvidaste tu contraseña?</a></p>
    </form>
    <p class="text-center text-xs text-slate-400 mt-6">Lamarque Repostería &amp; Café</p>
  </div>
</body>
</html>
