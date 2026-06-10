<?php /** @var array $suppliers @var string $ok @var string $err */ ?>
<div x-data="{ openNew:false, openMerge:false, q:'' }">

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Proveedores</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Catálogo de proveedores externos autorizados. Edita, fusiona duplicados y exporta para depurar.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="<?= h(url('dalia/suppliers/export')) ?>" class="tap inline-flex items-center gap-2 bg-canvas hover:bg-faint/20 text-ink font-bold text-[13px] rounded-xl px-3.5 py-2.5 border border-faint/40">
      <?= svg_icon('download', 16) ?> Exportar catálogo
    </a>
    <button @click="openMerge=true" class="tap inline-flex items-center gap-2 bg-canvas hover:bg-faint/20 text-ink font-bold text-[13px] rounded-xl px-3.5 py-2.5 border border-faint/40">
      <?= svg_icon('merge', 16) ?> Fusionar duplicados
    </button>
    <button @click="openNew=true" class="tap inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-extrabold text-[13.5px] rounded-xl px-4 py-2.5 transition-colors">
      <?= svg_icon('plus', 16) ?> Nuevo proveedor
    </button>
  </div>
</div>

<div class="relative mb-4 max-w-md">
  <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-faint pointer-events-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
  <input type="search" x-model="q" placeholder="Buscar proveedor, correo o teléfono…" class="fld w-full" style="padding-left:42px">
</div>

<?php if ($ok): ?>
  <div class="mb-4 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2"><?= svg_icon('check', 18) ?> <?= h($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="mb-4 font-semibold rounded-card px-4 py-3 text-[13.5px]" style="color:#b3261e;background:#fdecea"><?= h($err) ?></div>
<?php endif; ?>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
  <?php foreach ($suppliers as $s):
      $hay = mb_strtolower(($s['name'] ?? '') . ' ' . ($s['email'] ?? '') . ' ' . ($s['phonenumber'] ?? '')); ?>
    <div x-show="q==='' || <?= htmlspecialchars(json_encode($hay), ENT_QUOTES) ?>.includes(q.toLowerCase().trim())"
         class="bg-surface rounded-card p-4 <?= (int)$s['is_active'] ? '' : 'opacity-60' ?>"
         x-data="{ edit:false }">
      <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="w-10 h-10 shrink-0 grid place-items-center rounded-xl text-white font-extrabold text-[13px]" style="background:#b5610f"><?= h(mb_strtoupper(mb_substr(trim($s['name'] ?: '?'), 0, 1))) ?></span>
          <div class="leading-tight min-w-0">
            <div class="font-bold text-[14.5px] text-ink truncate"><?= h($s['name']) ?></div>
            <div class="text-[12px] text-muted truncate"><?= $s['email'] ? h($s['email']) : '<span class="text-faint">sin correo</span>' ?><?= $s['phonenumber'] ? ' · ' . h($s['phonenumber']) : '' ?></div>
          </div>
        </div>
        <span class="shrink-0 text-[10.5px] font-bold px-2 py-0.5 rounded-full <?= (int)$s['is_active'] ? 'text-white' : 'text-muted bg-canvas' ?>" <?= (int)$s['is_active'] ? 'style="background:#0e8091"' : '' ?>><?= (int)$s['is_active'] ? 'Autorizado' : 'No autoriz.' ?></span>
      </div>

      <div class="flex items-center justify-between gap-2">
        <span class="text-[12px] text-faint font-semibold"><?= (int)$s['usos'] ?> ticket<?= (int)$s['usos'] === 1 ? '' : 's' ?></span>
        <button type="button" @click="edit=!edit" class="tap text-[12px] font-bold px-3 py-2 rounded-lg bg-canvas text-muted">Editar</button>
      </div>

      <div x-show="edit" x-cloak x-transition class="mt-3 pt-3 border-t border-faint/30 space-y-2.5">
        <form method="post" action="<?= h(url('dalia/supplier/save')) ?>" class="space-y-2.5">
          <?= csrf_field() ?>
          <input type="hidden" name="sid" value="<?= (int)$s['id'] ?>">
          <input name="name" value="<?= h($s['name'] ?? '') ?>" placeholder="Nombre" required class="fld w-full" style="padding:9px 12px;font-size:13px">
          <input type="email" name="email" value="<?= h($s['email'] ?? '') ?>" placeholder="correo@proveedor.com" class="fld w-full" style="padding:9px 12px;font-size:13px">
          <input name="phone" value="<?= h($s['phonenumber'] ?? '') ?>" placeholder="Teléfono" class="fld w-full" style="padding:9px 12px;font-size:13px">
          <div class="flex items-center justify-between gap-2">
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" name="active" value="1" <?= (int)$s['is_active'] ? 'checked' : '' ?> class="peer sr-only"><span class="sw" style="width:38px;height:23px"></span>
              <span class="text-[12.5px] font-semibold text-muted">Autorizado</span>
            </label>
            <button class="tap text-[12px] font-bold px-3 py-2 rounded-lg bg-brand text-white">Guardar</button>
          </div>
        </form>
        <form method="post" action="<?= h(url('dalia/supplier/delete')) ?>" class="text-right"
              onsubmit="return confirm('¿Eliminar a <?= h(addslashes($s['name'] ?? '')) ?>? El histórico de sus tickets se conserva.');">
          <?= csrf_field() ?>
          <input type="hidden" name="sid" value="<?= (int)$s['id'] ?>">
          <button class="tap text-[11.5px] font-bold" style="color:#d83a34">Eliminar proveedor</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal nuevo proveedor -->
<div x-show="openNew" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="openNew=false" @keydown.escape.window="openNew=false">
  <form method="post" action="<?= h(url('dalia/supplier/create')) ?>" class="bg-surface rounded-card w-full max-w-md overflow-hidden" x-transition>
    <?= csrf_field() ?>
    <div class="bg-brand text-white px-5 py-3.5 flex items-center justify-between">
      <h3 class="font-extrabold text-[15px]">Nuevo proveedor</h3>
      <button type="button" @click="openNew=false" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
    </div>
    <div class="p-5 space-y-3.5">
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Nombre *</label>
        <input name="name" required placeholder="ej. Refrigeración del Pacífico" class="fld w-full"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Correo</label>
          <input type="email" name="email" placeholder="correo@proveedor.com" class="fld w-full"></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Teléfono</label>
          <input name="phone" placeholder="667…" class="fld w-full"></div>
      </div>
      <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="checkbox" name="active" value="1" checked class="peer sr-only"><span class="sw" style="width:38px;height:23px"></span>
        <span class="text-[13px] font-semibold text-muted">Autorizado</span>
      </label>
      <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Crear proveedor</button>
    </div>
  </form>
</div>

<!-- Modal fusionar duplicados -->
<div x-show="openMerge" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="openMerge=false" @keydown.escape.window="openMerge=false">
  <form method="post" action="<?= h(url('dalia/supplier/merge')) ?>" class="bg-surface rounded-card w-full max-w-md overflow-hidden" x-transition
        onsubmit="return confirm('Se moverán los tickets del proveedor duplicado al correcto y el duplicado se eliminará. ¿Continuar?');">
    <?= csrf_field() ?>
    <div class="bg-brand text-white px-5 py-3.5 flex items-center justify-between">
      <h3 class="font-extrabold text-[15px]">Fusionar duplicados</h3>
      <button type="button" @click="openMerge=false" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
    </div>
    <div class="p-5 space-y-3.5">
      <p class="text-[12.5px] text-muted">Une dos registros del mismo proveedor. Los tickets del duplicado pasan al correcto.</p>
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Duplicado (se elimina) *</label>
        <select name="from" required class="fld w-full">
          <option value="">Selecciona…</option>
          <?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?> · <?= (int)$s['usos'] ?> tk</option><?php endforeach; ?>
        </select></div>
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Correcto (se conserva) *</label>
        <select name="into" required class="fld w-full">
          <option value="">Selecciona…</option>
          <?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?> · <?= (int)$s['usos'] ?> tk</option><?php endforeach; ?>
        </select></div>
      <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Fusionar</button>
    </div>
  </form>
</div>

</div>
