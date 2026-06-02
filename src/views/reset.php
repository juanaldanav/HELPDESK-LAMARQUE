<?php /** @var string $token @var bool $valid @var ?string $error @var bool $done */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nueva contraseña · Soporte Lamarque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#006970',dark:'#004f54'}},fontFamily:{sans:['"Plus Jakarta Sans"','system-ui','sans-serif']}}}};</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-100 font-sans text-slate-800 grid place-items-center px-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="mx-auto w-16 h-16 rounded-2xl bg-brand grid place-items-center text-white text-3xl mb-3">☕</div>
      <h1 class="text-2xl font-extrabold tracking-tight">Nueva contraseña</h1>
    </div>

    <?php if (!empty($done)): ?>
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
        <div class="text-green-600 text-4xl mb-2">✓</div>
        <p class="text-slate-700">Tu contraseña se actualizó correctamente.</p>
        <a href="<?= h(url('login')) ?>" class="inline-block mt-4 bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg px-5 py-2.5">Entrar</a>
      </div>
    <?php elseif (empty($valid)): ?>
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
        <div class="text-red-500 text-4xl mb-2">⚠</div>
        <p class="text-slate-700">Este enlace es inválido o expiró.</p>
        <a href="<?= h(url('forgot')) ?>" class="inline-block mt-4 text-brand font-medium hover:underline">Solicitar uno nuevo</a>
      </div>
    <?php else: ?>
      <form method="post" action="<?= h(url('reset')) ?>" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="t" value="<?= h($token) ?>">
        <?php if (!empty($error)): ?>
          <div class="bg-red-50 text-red-700 text-sm rounded-lg px-3 py-2 border border-red-200"><?= h($error) ?></div>
        <?php endif; ?>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Nueva contraseña</label>
          <input type="password" name="pass" required minlength="8" autocomplete="new-password"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand outline-none">
          <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar contraseña</label>
          <input type="password" name="confirm" required minlength="8" autocomplete="new-password"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand outline-none">
        </div>
        <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg py-2.5">Guardar contraseña</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
