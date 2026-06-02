<?php /** @var array $assets */ if (!empty($assets)): ?>
<div class="bg-white rounded-xl border border-slate-200 p-4">
  <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Activo vinculado</h3>
  <div class="space-y-2">
    <?php foreach ($assets as $a): ?>
      <div class="flex items-center justify-between gap-3 text-sm">
        <div>
          <div class="font-medium text-slate-800"><?= h($a['name']) ?></div>
          <div class="text-slate-500 text-xs"><?= h($a['type_label']) ?> · <span class="font-mono"><?= h($a['serial']) ?></span> · <?= h($a['state_name']) ?></div>
        </div>
        <?php if (!empty($a['drive_url'])): ?>
          <a href="<?= h($a['drive_url']) ?>" target="_blank" class="shrink-0 text-xs px-2.5 py-1 rounded-lg bg-brand-light text-brand-dark font-medium hover:bg-brand/20">Ver referencia</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
