<?php /** @var array $cats @var ?string $active @var array $items @var string $q @var ?int $entity @var array $sucursales */ ?>
<h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-1">Activos · consolidado</h1>
<p class="text-slate-500 mb-4">Inventario de todas las sucursales. Filtra y da de baja equipos.</p>

<!-- Filtro sucursal + búsqueda -->
<form method="get" class="bg-white rounded-xl border border-slate-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
  <input type="hidden" name="r" value="dalia/assets">
  <input type="hidden" name="cat" value="<?= h($active) ?>">
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Sucursal</label>
    <select name="entity" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
      <option value="">Todas</option>
      <?php foreach ($sucursales as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (string)($entity ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="flex-1 min-w-48">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input type="search" name="q" value="<?= h($q) ?>" placeholder="Folio LMQ o nombre…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
  </div>
  <button class="bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg px-4 py-2">Filtrar</button>
</form>

<!-- Desglose por categoría -->
<div class="flex flex-wrap gap-2 mb-5">
  <?php foreach ($cats as $c): ?>
    <a href="<?= h(url('dalia/assets', array_filter(['cat' => $c['key'], 'entity' => $entity, 'q' => $q]))) ?>"
       class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border transition
              <?= $active === $c['key'] ? 'bg-brand text-white border-brand' : 'bg-white text-slate-700 border-slate-200 hover:border-brand/40' ?>">
      <?= h($c['label']) ?>
      <span class="inline-grid place-items-center min-w-5 h-5 px-1.5 rounded-full text-xs <?= $active === $c['key'] ? 'bg-white/20' : 'bg-slate-100 text-slate-600' ?>"><?= (int)$c['count'] ?></span>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
  <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">Sin activos con ese filtro.</div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php foreach ($items as $a): $baja = (int)$a['states_id'] === 3; ?>
      <div class="bg-white rounded-xl border <?= $baja ? 'border-red-200 opacity-70' : 'border-slate-200' ?> p-4 flex flex-col">
        <div class="flex items-start justify-between gap-2">
          <div class="font-semibold text-slate-800"><?= h($a['name']) ?></div>
          <span class="shrink-0 text-xs px-2 py-0.5 rounded-full <?= $baja ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' ?>"><?= h($a['state_name']) ?></span>
        </div>
        <div class="text-xs font-mono text-slate-500 mt-1"><?= h($a['serial']) ?></div>
        <div class="text-xs text-slate-400 mt-0.5"><?= h($a['entity_name'] ?? '—') ?></div>
        <div class="flex items-center gap-2 mt-2">
          <?php if (!empty($a['drive_url'])): ?>
            <a href="<?= h($a['drive_url']) ?>" target="_blank" class="text-xs px-2.5 py-1 rounded-lg bg-brand-light text-brand-dark font-medium hover:bg-brand/20">Ver referencia</a>
          <?php endif; ?>
          <?php if (!$baja): ?>
            <form method="post" action="<?= h(url('dalia/asset/baja')) ?>" class="ml-auto"
                  onsubmit="return confirm('¿Dar de baja este activo? Quedará en histórico marcado como Baja.');">
              <?= csrf_field() ?>
              <input type="hidden" name="type_key" value="<?= h($a['type_key']) ?>">
              <input type="hidden" name="asset_id" value="<?= (int)$a['id'] ?>">
              <input type="hidden" name="entity" value="<?= h((string)($entity ?? '')) ?>">
              <input type="hidden" name="cat" value="<?= h($active) ?>">
              <input type="hidden" name="q" value="<?= h($q) ?>">
              <button class="text-xs px-2.5 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-medium">Dar de baja</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="text-xs text-slate-400 mt-3">Mostrando hasta 500 por categoría.</p>
<?php endif; ?>
