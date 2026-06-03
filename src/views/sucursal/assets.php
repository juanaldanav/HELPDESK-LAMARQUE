<?php /** @var array $cats @var ?string $active @var array $items @var string $q */
$activeCat = null;
foreach ($cats as $c) if ($c['key'] === $active) { $activeCat = $c; break; }
?>
<div class="max-w-md md:max-w-3xl lg:max-w-5xl mx-auto">
  <h1 class="text-[26px] font-extrabold tracking-tight leading-tight mb-4">Mis activos</h1>

  <!-- Buscador -->
  <form method="get" class="relative mb-4">
    <input type="hidden" name="r" value="assets">
    <input type="hidden" name="cat" value="<?= h($active) ?>">
    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-faint pointer-events-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="search" name="q" value="<?= h($q) ?>" placeholder="Buscar por folio LMQ o nombre…" class="fld" style="padding-left:42px">
  </form>

  <!-- ⭐ Tarjetas de categoría (color propio por categoría) -->
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2.5 stagger mb-6">
    <?php foreach ($cats as $c):
        $on = $active === $c['key'];
        $col = asset_color($c['key']); ?>
      <a href="<?= h(url('assets', array_filter(['cat' => $c['key'], 'q' => $q]))) ?>"
         class="tap relative rounded-card p-3.5 text-left transition-colors duration-200 block"
         style="<?= $on ? 'background:' . $col : '' ?>" <?= $on ? '' : 'data-off' ?>>
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

  <!-- Encabezado dinámico -->
  <?php if ($activeCat): ?>
    <div class="flex items-center gap-2 mb-3">
      <h2 class="text-[15px] font-extrabold tracking-tight"><?= h($activeCat['label']) ?></h2>
      <span class="text-[12px] font-bold text-brand-dark bg-brand-tint px-2 py-0.5 rounded-full"><?= (int)$activeCat['count'] ?> equipos</span>
      <?php if ($q !== ''): ?><span class="text-[12px] text-muted">· filtro “<?= h($q) ?>”</span><?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Lista de activos -->
  <?php if (empty($items)): ?>
    <div class="bg-surface rounded-card p-8 text-center text-muted font-semibold">Sin activos en esta categoría<?= $q !== '' ? ' con ese filtro' : '' ?>.</div>
  <?php else: ?>
    <div class="space-y-2.5 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-2.5 md:space-y-0">
      <?php foreach ($items as $a): ?>
        <div class="bg-surface rounded-card p-4">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-bold text-[14.5px] text-ink leading-snug"><?= h($a['name']) ?></div>
              <div class="text-[12px] font-mono text-faint mt-1"><?= h($a['serial']) ?></div>
            </div>
            <span class="shrink-0"><?= asset_state_dot((int)$a['states_id'], $a['state_name']) ?></span>
          </div>
          <?php if (!empty($a['drive_url'])): ?>
            <a href="<?= h($a['drive_url']) ?>" target="_blank" class="tap inline-flex items-center gap-1.5 text-[12px] font-bold mt-3 text-brand-dark">
              <?= svg_icon('external', 13) ?> Ver referencia
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
