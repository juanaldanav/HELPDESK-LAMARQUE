<?php /** @var bool $sent */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recuperar contraseña · Soporte Lamarque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#006970',dark:'#004f54'}},fontFamily:{sans:['"Plus Jakarta Sans"','system-ui','sans-serif']}}}};</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-100 font-sans text-slate-800 grid place-items-center px-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="mx-auto w-16 h-16 rounded-2xl bg-brand grid place-items-center text-white text-3xl mb-3">☕</div>
      <h1 class="text-2xl font-extrabold tracking-tight">Recuperar contraseña</h1>
    </div>
    <?php if (!empty($sent)): ?>
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center">
        <div class="text-green-600 text-4xl mb-2">✓</div>
        <p class="text-slate-700">Si la cuenta existe, enviamos un enlace para restablecer tu contraseña al correo registrado.</p>
        <p class="text-slate-400 text-sm mt-2">Revisa tu bandeja. El enlace expira en 15 minutos.</p>
        <a href="<?= h(url('login')) ?>" class="inline-block mt-4 text-brand font-medium hover:underline">Volver a entrar</a>
      </div>
    <?php else: ?>
      <form method="post" action="<?= h(url('forgot')) ?>" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <?= csrf_field() ?>
        <p class="text-sm text-slate-500">Ingresa tu correo <strong>@lamarque.mx</strong> y te enviaremos un enlace para crear una nueva contraseña.</p>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
          <input type="email" name="email" required autofocus placeholder="sucursal@lamarque.mx"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none">
        </div>
        <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg py-2.5">Enviar enlace</button>
        <p class="text-center"><a href="<?= h(url('login')) ?>" class="text-sm text-slate-500 hover:underline">Volver</a></p>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
