<?php /** @var string $token @var bool $valid @var ?string $error @var bool $done */ ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#006970">
<title>Nueva contraseña · Soporte Lamarque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: { brand:{DEFAULT:'#006970',dark:'#004f54',deep:'#003b3f',light:'#e3eded',tint:'#f0f5f5'}, canvas:'#f3f5f5', surface:'#ffffff', ink:'#0f1c1d', muted:'#5d6f70', faint:'#90a0a0' },
  fontFamily:{ sans:['"Plus Jakarta Sans"','system-ui','sans-serif'] }, borderRadius:{ card:'16px' }
}}};
</script>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  *{ -webkit-tap-highlight-color:transparent; } [x-cloak]{ display:none!important; }
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
      <?php if (!empty($done)): ?>
        <div class="text-center">
          <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-tint text-[#1d8a5a] grid place-items-center mb-3">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          </div>
          <h1 class="text-[19px] font-extrabold tracking-tight">Contraseña actualizada</h1>
          <p class="text-[13.5px] text-muted mt-2">Ya puedes entrar con tu nueva contraseña.</p>
          <a href="<?= h(url('login')) ?>" class="inline-block mt-5 w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-3.5 transition-colors">Entrar</a>
        </div>
      <?php elseif (empty($valid)): ?>
        <div class="text-center">
          <div class="w-14 h-14 mx-auto rounded-2xl bg-[#fdecea] text-[#b3261e] grid place-items-center mb-3">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
          </div>
          <h1 class="text-[19px] font-extrabold tracking-tight">Enlace inválido o expirado</h1>
          <p class="text-[13.5px] text-muted mt-2">Los enlaces duran 15 minutos. Solicita uno nuevo.</p>
          <a href="<?= h(url('forgot')) ?>" class="inline-block mt-5 text-[13px] text-brand-dark font-bold hover:underline">Solicitar uno nuevo</a>
        </div>
      <?php else: ?>
        <h1 class="text-[20px] font-extrabold tracking-tight text-center">Nueva contraseña</h1>
        <p class="text-[13px] text-muted text-center mt-1 mb-5">Crea una contraseña segura para tu cuenta.</p>
        <form method="post" action="<?= h(url('reset')) ?>" class="space-y-3.5" x-data="{p:'',c:''}">
          <?= csrf_field() ?>
          <input type="hidden" name="t" value="<?= h($token) ?>">
          <?php if (!empty($error)): ?>
            <div class="flex items-center gap-2 bg-[#fdecea] text-[#b3261e] text-[13px] rounded-xl px-3.5 py-2.5"><?= h($error) ?></div>
          <?php endif; ?>
          <div>
            <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Nueva contraseña</label>
            <input type="password" name="pass" x-model="p" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="fld">
          </div>
          <div>
            <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Confirmar contraseña</label>
            <input type="password" name="confirm" x-model="c" required minlength="8" autocomplete="new-password" placeholder="Repite la contraseña" class="fld">
          </div>
          <p class="text-[12px] flex items-center gap-1.5" :class="c.length>=8 && p===c ? 'text-[#1d8a5a]' : 'text-faint'">
            <span class="w-3.5 h-3.5 grid place-items-center"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span x-text="c.length>=8 && p===c ? 'Las contraseñas coinciden' : 'Mínimo 8 caracteres, deben coincidir'"></span>
          </p>
          <button class="w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-3.5 transition-colors mt-1">Guardar contraseña</button>
        </form>
      <?php endif; ?>
    </div>

    <p class="text-center text-[11.5px] text-faint mt-7">Lamarque Repostería &amp; Café · Portal de mantenimiento</p>
  </div>
</body>
</html>
