<?php /** @var array $list */
$stkey = fn(int $s) => [1 => 'nuevo', 2 => 'curso', 3 => 'curso', 4 => 'espera', 5 => 'resuelto', 6 => 'cerrado'][$s] ?? 'nuevo';
$nCurso = count(array_filter($list, fn($t) => in_array((int)$t['status'], [2, 3], true)));
$nTotal = count($list);
$nUrg = count(array_filter($list, fn($t) => (int)$t['urgency'] <= 2));
?>
<div class="max-w-md md:max-w-2xl lg:max-w-5xl mx-auto" x-data="{ active:0, show(st){ if(this.active===1) return st==='curso'; return true; } }">

  <div class="mb-4">
    <h1 class="text-[26px] font-extrabold tracking-tight leading-tight">Mis tareas</h1>
    <p class="text-muted text-[14px] mt-0.5">
      <?= $nTotal ?> pendiente<?= $nTotal === 1 ? '' : 's' ?>
      <?php if ($nUrg): ?> · <span class="font-semibold text-brand-dark"><?= $nUrg ?> urgente<?= $nUrg === 1 ? '' : 's' ?></span><?php endif; ?>
    </p>
  </div>

  <!-- Filtro segmentado -->
  <div class="relative bg-brand-tint rounded-2xl p-1 flex mb-5 select-none max-w-md">
    <button @click="active=0" class="relative z-10 flex-1 h-9 text-[13px] font-bold rounded-xl flex items-center justify-center gap-1.5 transition-colors duration-200" :class="active===0 ? 'text-white' : 'text-brand-dark/65'">
      <span>Pendientes</span>
      <span class="text-[11px] font-bold px-1.5 rounded-full" :class="active===0 ? 'bg-white/20 text-white' : 'bg-brand/10 text-brand-dark/70'"><?= $nTotal ?></span>
    </button>
    <button @click="active=1" class="relative z-10 flex-1 h-9 text-[13px] font-bold rounded-xl flex items-center justify-center gap-1.5 transition-colors duration-200" :class="active===1 ? 'text-white' : 'text-brand-dark/65'">
      <span>En curso</span>
      <span class="text-[11px] font-bold px-1.5 rounded-full" :class="active===1 ? 'bg-white/20 text-white' : 'bg-brand/10 text-brand-dark/70'"><?= $nCurso ?></span>
    </button>
    <div class="absolute z-0 top-1 bottom-1 rounded-xl bg-brand transition-transform duration-200" :style="`left:4px; width:calc((100% - 8px)/2); transform:translateX(calc(${active} * 100%))`"></div>
  </div>

  <?php if (empty($list)): ?>
    <div class="bg-surface rounded-card p-10 text-center">
      <div class="w-12 h-12 mx-auto rounded-2xl bg-brand-tint text-brand grid place-items-center mb-3"><?= svg_icon('check', 24) ?></div>
      <p class="text-muted font-semibold">No tienes tareas pendientes.</p>
    </div>
  <?php else: ?>
    <div class="grid gap-2.5 lg:grid-cols-2 stagger">
      <?php foreach ($list as $t): $sk = $stkey((int)$t['status']); ?>
        <a href="<?= h(url('tec/detail', ['id' => $t['id']])) ?>" class="tap block bg-surface rounded-card overflow-hidden"
           data-st="<?= $sk ?>" x-show="show('<?= $sk ?>')" x-transition.opacity.duration.200ms>
          <div class="p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="text-[11px] font-bold uppercase tracking-wide text-brand-dark flex items-center gap-1">
                  <?= svg_icon('building', 13) ?><span><?= h($t['entity_name']) ?></span>
                </div>
                <div class="font-bold text-[15.5px] text-ink leading-snug mt-1 truncate">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></div>
              </div>
              <?= status_chip((int)$t['status']) ?>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-canvas">
              <div class="flex items-center gap-1.5 text-[12.5px] font-semibold text-muted min-w-0">
                <span class="w-6 h-6 grid place-items-center rounded-lg bg-brand-tint text-brand shrink-0"><?= icon_for_category($t['cat_name']) ?></span>
                <span class="truncate"><?= h($t['cat_name'] ?: 'General') ?></span>
              </div>
              <div class="flex items-center gap-2.5 shrink-0 pl-2">
                <?= urgency_chip((int)$t['urgency']) ?>
                <span class="text-[11.5px] text-faint"><?= fecha($t['date']) ?></span>
              </div>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= h(cfg('base_url')) ?>/sw.js').catch(() => {});
}
</script>
