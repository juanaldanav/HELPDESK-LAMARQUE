<?php /** @var array $u @var bool $ok @var string $err */ ?>
<div class="max-w-md mx-auto">
  <h1 class="text-[24px] font-extrabold tracking-tight mb-1">Mi cuenta</h1>
  <p class="text-muted text-[13.5px] mb-5"><?= h($u['display']) ?> · <span class="font-mono"><?= h($u['name']) ?></span></p>

  <?php if ($ok): ?>
    <div class="mb-4 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2"><?= svg_icon('check', 18) ?> Tu contraseña se actualizó.</div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="mb-4 font-semibold rounded-card px-4 py-3 text-[13.5px]" style="color:#b3261e;background:#fdecea"><?= h($err) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= h(url('account/password')) ?>" class="bg-surface rounded-card p-5 space-y-3.5" x-data="{p:'',c:''}">
    <?= csrf_field() ?>
    <h2 class="font-extrabold text-[15px]">Cambiar contraseña</h2>
    <div>
      <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Contraseña actual</label>
      <input type="password" name="current" required autocomplete="current-password" class="fld w-full">
    </div>
    <div>
      <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Nueva contraseña</label>
      <input type="password" name="pass" x-model="p" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="fld w-full">
    </div>
    <div>
      <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Confirmar nueva contraseña</label>
      <input type="password" name="confirm" x-model="c" required minlength="8" autocomplete="new-password" class="fld w-full">
    </div>
    <p class="text-[12px] flex items-center gap-1.5" :class="c.length>=8 && p===c ? 'text-[#1d8a5a]' : 'text-faint'">
      <span x-text="c.length>=8 && p===c ? '✓ Las contraseñas coinciden' : 'Mínimo 8 caracteres, deben coincidir'"></span>
    </p>
    <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Guardar contraseña</button>
  </form>
</div>
