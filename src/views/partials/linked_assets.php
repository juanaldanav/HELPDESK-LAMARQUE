<?php /** @var array $assets — tarjeta de activo vinculado (design-system) */ if (!empty($assets)): ?>
<div class="bg-surface rounded-card p-4">
  <div class="text-[10.5px] font-bold uppercase tracking-wide text-faint mb-1">Activo vinculado</div>
  <div class="space-y-3">
    <?php foreach ($assets as $a): ?>
      <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
          <div class="font-bold text-ink text-[14.5px] truncate"><?= h($a['name']) ?></div>
          <div class="text-[12px] text-muted mt-0.5"><?= h($a['type_label']) ?> · <span class="font-mono text-faint"><?= h($a['serial']) ?></span> · <?= h($a['state_name']) ?></div>
        </div>
        <?php if (!empty($a['drive_url'])): ?>
          <a href="<?= h($a['drive_url']) ?>" target="_blank" class="tap shrink-0 inline-flex items-center gap-1 text-[12px] font-bold px-3 py-2 rounded-xl bg-brand-tint text-brand-dark">
            <?= svg_icon('external', 14) ?> Ref.
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
