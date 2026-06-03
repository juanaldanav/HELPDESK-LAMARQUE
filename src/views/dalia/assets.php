<?php /** @var array $cats @var ?string $active @var array $items @var string $q @var ?int $entity @var array $sucursales */
$activeCat = null;
foreach ($cats as $c) if ($c['key'] === $active) { $activeCat = $c; break; }
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Activos · consolidado</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Inventario de todas las sucursales. Filtra y da de baja equipos.</p>
  </div>
</div>

<form method="get" class="bg-surface rounded-card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <input type="hidden" name="r" value="dalia/assets">
  <input type="hidden" name="cat" value="<?= h($active) ?>">
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal</label>
    <select name="entity" onchange="this.form.submit()" class="fld" style="width:auto;min-width:220px">
      <option value="">Todas</option>
      <?php foreach ($sucursales as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (string)($entity ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="flex-1 min-w-56 relative">
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Buscar</label>
    <input type="search" name="q" value="<?= h($q) ?>" placeholder="Folio LMQ o nombre…" class="fld w-full">
  </div>
  <button class="tap bg-brand hover:bg-brand-dark text-white text-[13px] font-bold rounded-xl px-5 py-3 transition-colors">Filtrar</button>
</form>

<!-- ⭐ Tarjetas de categoría -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5 stagger mb-6">
  <?php foreach ($cats as $c):
      $on = $active === $c['key'];
      $col = asset_color($c['key']); ?>
    <a href="<?= h(url('dalia/assets', array_filter(['cat' => $c['key'], 'entity' => $entity, 'q' => $q]))) ?>"
       class="tap relative rounded-card p-3.5 text-left transition-colors duration-200 block"
       style="<?= $on ? 'background:' . $col : '' ?>">
      <?php if (!$on): ?><span class="absolute inset-0 rounded-card bg-surface"></span><?php endif; ?>
      <span class="relative block">
        <span class="flex items-start justify-between mb-3">
          <span class="w-10 h-10 grid place-items-center rounded-xl"
                style="<?= $on ? 'background:rgba(255,255,255,.15);color:#fff' : 'background:' . $col . '1a;color:' . $col ?>">
            <?= svg_icon($c['key'], 22) ?>
          </span>
          <span class="text-[26px] font-extrabold leading-none mt-0.5 <?= $on ? 'text-white' : 'text-ink' ?>"><?= (int)$c['count'] ?></span>
        </span>
        <span class="block text-[12.5px] font-bold leading-tight <?= $on ? 'text-white' : 'text-ink' ?>"><?= h($c['label']) ?></span>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($activeCat): ?>
  <div class="flex items-center gap-2 mb-3">
    <h2 class="text-[15px] font-extrabold tracking-tight"><?= h($activeCat['label']) ?></h2>
    <span class="text-[12px] font-bold text-brand-dark bg-brand-tint px-2 py-0.5 rounded-full"><?= (int)$activeCat['count'] ?> equipos</span>
  </div>
<?php endif; ?>

<?php if (empty($items)): ?>
  <div class="bg-surface rounded-card p-10 text-center text-faint font-semibold">Sin activos con ese filtro.</div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
    <?php foreach ($items as $a): $baja = (int)$a['states_id'] === 3; ?>
      <div class="bg-surface rounded-card p-4 <?= $baja ? 'opacity-60' : '' ?>" x-data="{ edit: false }">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <div x-show="!edit" class="font-bold text-[14.5px] text-ink leading-snug"><?= h($a['name']) ?></div>
            <form x-show="edit" x-cloak method="post" action="<?= h(url('dalia/asset/rename')) ?>" class="flex gap-1.5">
              <?= csrf_field() ?>
              <input type="hidden" name="type_key" value="<?= h($a['type_key']) ?>">
              <input type="hidden" name="asset_id" value="<?= (int)$a['id'] ?>">
              <input type="hidden" name="entity" value="<?= h((string)($entity ?? '')) ?>">
              <input type="hidden" name="cat" value="<?= h($active) ?>">
              <input type="hidden" name="q" value="<?= h($q) ?>">
              <input name="name" value="<?= h($a['name']) ?>" required maxlength="255" class="fld flex-1" style="padding:6px 10px;font-size:13px">
              <button class="tap text-[12px] font-bold px-2.5 py-1.5 rounded-lg bg-brand text-white shrink-0">OK</button>
            </form>
            <div class="text-[12px] font-mono text-faint mt-1"><?= h($a['serial']) ?></div>
            <div class="text-[11.5px] text-faint mt-0.5"><?= h($a['entity_name'] ?? '—') ?></div>
          </div>
          <span class="shrink-0"><?= asset_state_dot((int)$a['states_id'], $a['state_name']) ?></span>
        </div>
        <div class="flex items-center gap-3 mt-3">
          <?php if (!empty($a['drive_url'])): ?>
            <a href="<?= h($a['drive_url']) ?>" target="_blank" class="tap inline-flex items-center gap-1.5 text-[12px] font-bold text-brand-dark">
              <?= svg_icon('external', 13) ?> Ver referencia
            </a>
          <?php endif; ?>
          <button type="button" @click="edit = !edit" class="tap text-[12px] font-bold text-muted" x-text="edit ? 'Cancelar' : 'Renombrar'"></button>
          <?php if (!$baja): ?>
            <form method="post" action="<?= h(url('dalia/asset/baja')) ?>" class="ml-auto"
                  onsubmit="return confirm('¿Dar de baja este activo? Quedará en histórico marcado como Baja.');">
              <?= csrf_field() ?>
              <input type="hidden" name="type_key" value="<?= h($a['type_key']) ?>">
              <input type="hidden" name="asset_id" value="<?= (int)$a['id'] ?>">
              <input type="hidden" name="entity" value="<?= h((string)($entity ?? '')) ?>">
              <input type="hidden" name="cat" value="<?= h($active) ?>">
              <input type="hidden" name="q" value="<?= h($q) ?>">
              <button class="tap text-[12px] font-bold px-3 py-1.5 rounded-lg" style="color:#d83a34;background:#d83a341a">Dar de baja</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="text-[12px] text-faint mt-3">Mostrando hasta 500 por categoría.</p>
<?php endif; ?>
