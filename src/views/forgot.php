<?php /** @var bool $sent */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#006970">
<title>Recuperar contraseña · Soporte Lamarque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: { brand:{DEFAULT:'#006970',dark:'#004f54',deep:'#003b3f',light:'#e3eded',tint:'#f0f5f5'}, canvas:'#f3f5f5', surface:'#ffffff', ink:'#0f1c1d', muted:'#5d6f70', faint:'#90a0a0' },
  fontFamily:{ sans:['"Plus Jakarta Sans"','system-ui','sans-serif'] }, borderRadius:{ card:'16px' }
}}};
</script>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{ -webkit-tap-highlight-color:transparent; }
  .fld{ width:100%; background:#f3f5f5; border-radius:14px; padding:13px 16px; font-size:15px; outline:none; transition:box-shadow .15s ease, background .15s ease; }
  .fld::placeholder{ color:#90a0a0; }
  .fld:focus{ background:#fff; box-shadow:0 0 0 2px #006970; }
</style>
</head>
<body class="h-full font-sans text-ink antialiased min-h-screen bg-canvas grid place-items-center px-5 py-10">
  <div class="w-full max-w-[380px]">
    <div class="flex flex-col items-center text-center mb-7">
      <img src="https://lamarque.mx/images/icons/lamarque.png" alt="Lamarque" class="h-9 w-auto object-contain mb-5">
      <div class="w-10 h-1 rounded-full bg-brand mb-4"></div>
    </div>

    <div class="bg-surface rounded-card p-6">
      <?php if (!empty($sent)): ?>
        <div class="text-center">
          <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-tint text-brand grid place-items-center mb-3">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l8 6 8-6"/><rect x="3" y="5" width="18" height="14" rx="2.5"/></svg>
          </div>
          <h1 class="text-[19px] font-extrabold tracking-tight">Revisa tu correo</h1>
          <p class="text-[13.5px] text-muted mt-2">Si la cuenta existe, enviamos un enlace para restablecer tu contraseña. Expira en 15 minutos.</p>
        </div>
      <?php else: ?>
        <h1 class="text-[20px] font-extrabold tracking-tight text-center">Recuperar contraseña</h1>
        <p class="text-[13px] text-muted text-center mt-1 mb-5">Ingresa tu correo <strong class="text-ink">@lamarque.mx</strong> y te enviaremos un enlace.</p>
        <form method="post" action="<?= h(url('forgot')) ?>" class="space-y-3.5">
          <?= csrf_field() ?>
          <div>
            <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Correo</label>
            <input type="email" name="email" required autofocus placeholder="sucursal@lamarque.mx" class="fld">
          </div>
          <button class="w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-3.5 transition-colors">Enviar enlace</button>
        </form>
      <?php endif; ?>
    </div>
    <p class="text-center mt-4"><a href="<?= h(url('login')) ?>" class="text-[13px] text-muted font-semibold hover:underline">&larr; Volver a entrar</a></p>

    <p class="text-center text-[11.5px] text-faint mt-7">Lamarque Repostería &amp; Café · Portal de mantenimiento</p>
  </div>
</body>
</html>
