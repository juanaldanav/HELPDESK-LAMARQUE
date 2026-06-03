<?php /** @var string|null $error */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#006970">
<title>Entrar · Soporte Lamarque</title>
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
      <h1 class="text-[20px] font-extrabold tracking-tight text-center">Bienvenido</h1>
      <p class="text-[13px] text-muted text-center mt-1 mb-5">Entra para reportar y dar seguimiento.</p>

      <form method="post" action="<?= h(url('login')) ?>" class="space-y-3.5">
        <?= csrf_field() ?>
        <?php if (!empty($error)): ?>
          <div class="flex items-center gap-2 bg-[#fdecea] text-[#b3261e] text-[13px] rounded-xl px-3.5 py-2.5">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
            <?= h($error) ?>
          </div>
        <?php endif; ?>
        <div>
          <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Usuario</label>
          <input name="name" autocomplete="username" autofocus required placeholder="ej. guadalupe" class="fld">
        </div>
        <div>
          <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Contraseña</label>
          <input type="password" name="pass" autocomplete="current-password" required placeholder="••••••••" class="fld">
        </div>
        <button class="w-full bg-brand active:bg-brand-dark hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-3.5 transition-colors mt-1">Entrar</button>
      </form>
    </div>
    <p class="text-center mt-4"><a href="<?= h(url('forgot')) ?>" class="text-[13px] text-brand-dark font-bold hover:underline">¿Olvidaste tu contraseña?</a></p>

    <p class="text-center text-[11.5px] text-faint mt-7">Lamarque Repostería &amp; Café · Portal de mantenimiento</p>
  </div>
</body>
</html>
