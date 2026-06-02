<?php /** @var array $thread */ ?>
<div class="space-y-3">
  <?php foreach ($thread as $m): ?>
    <div class="bg-white rounded-xl border <?= $m['is_initial'] ? 'border-brand/30' : 'border-slate-200' ?> p-4">
      <div class="flex items-center justify-between mb-1.5">
        <span class="text-sm font-semibold text-slate-700"><?= h($m['author']) ?>
          <?php if ($m['is_initial']): ?><span class="ml-1 text-xs font-normal text-brand">· reporte inicial</span><?php endif; ?>
        </span>
        <span class="text-xs text-slate-400"><?= fecha($m['date']) ?></span>
      </div>
      <div class="text-sm text-slate-700 leading-relaxed"><?= msg_html($m['content']) ?></div>
    </div>
  <?php endforeach; ?>
</div>
