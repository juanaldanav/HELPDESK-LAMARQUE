<?php /** @var array $cats @var ?string $active @var array $items @var string $q */ ?>
<h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-1">Mis activos</h1>
<p class="text-slate-500 mb-4">Equipos registrados en tu sucursal, por categoría.</p>

<!-- Desglose por categoría -->
<div class="flex flex-wrap gap-2 mb-5">
  <?php foreach ($cats as $c): ?>
    <a href="<?= h(url('assets', ['cat' => $c['key']])) ?>"
       class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border transition
              <?= $active === $c['key'] ? 'bg-brand text-white border-brand' : 'bg-white text-slate-700 border-slate-200 hover:border-brand/40' ?>">
      <?= h($c['label']) ?>
      <span class="inline-grid place-items-center min-w-5 h-5 px-1.5 rounded-full text-xs
                   <?= $active === $c['key'] ? 'bg-white/20' : 'bg-slate-100 text-slate-600' ?>"><?= (int)$c['count'] ?></span>
    </a>
  <?php endforeach; ?>
</div>

<form method="get" class="mb-4">
  <input type="hidden" name="r" value="assets">
  <input type="hidden" name="cat" value="<?= h($active) ?>">
  <input type="search" name="q" value="<?= h($q) ?>" placeholder="Buscar por folio o nombre…"
         class="w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand outline-none">
</form>

<?php if (empty($items)): ?>
  <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">Sin activos en esta categoría.</div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php foreach ($items as $a): ?>
      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <div class="flex items-start justify-between gap-2">
          <div class="font-semibold text-slate-800"><?= h($a['name']) ?></div>
          <span class="shrink-0 text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= h($a['state_name']) ?></span>
        </div>
        <div class="text-xs font-mono text-slate-500 mt-1"><?= h($a['serial']) ?></div>
        <?php if (!empty($a['drive_url'])): ?>
          <a href="<?= h($a['drive_url']) ?>" target="_blank" class="inline-block mt-2 text-xs px-2.5 py-1 rounded-lg bg-brand-light text-brand-dark font-medium hover:bg-brand/20">Ver referencia</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
