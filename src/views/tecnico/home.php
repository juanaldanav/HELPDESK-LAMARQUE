<?php /** @var array $list */ ?>
<div class="mb-5">
  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Mis tareas</h1>
  <p class="text-slate-500"><?= count($list) ?> ticket<?= count($list) === 1 ? '' : 's' ?> asignado<?= count($list) === 1 ? '' : 's' ?> pendiente<?= count($list) === 1 ? '' : 's' ?>.</p>
</div>

<?php if (empty($list)): ?>
  <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">No tienes tareas pendientes. 🎉</div>
<?php else: ?>
  <div class="space-y-2">
    <?php foreach ($list as $t): ?>
      <a href="<?= h(url('tec/detail', ['id' => $t['id']])) ?>" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-brand/40 transition">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-xs font-medium text-brand-dark"><?= h($t['entity_name']) ?></div>
            <div class="font-semibold text-slate-800 truncate mt-0.5">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></div>
            <div class="text-xs text-slate-500 mt-0.5"><?= h($t['cat_name'] ?? '—') ?> · <?= fecha($t['date']) ?></div>
          </div>
          <div class="flex flex-col items-end gap-1 shrink-0">
            <?= urgency_chip((int)$t['urgency']) ?>
            <?= status_chip((int)$t['status']) ?>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= h(cfg('base_url')) ?>/sw.js').catch(() => {});
}
</script>
