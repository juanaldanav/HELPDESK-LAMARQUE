<?php /** @var array $thread — burbujas de chat del design-system. Mensaje propio = derecha turquesa. */
$me = current_user();
$ini = function (string $name): string {
    $p = preg_split('~\s+~', trim($name));
    $s = mb_strtoupper(mb_substr($p[0] ?? '', 0, 1));
    if (count($p) > 1) $s .= mb_strtoupper(mb_substr(end($p), 0, 1));
    return $s ?: '·';
};
?>
<div class="space-y-3.5 stagger">
  <?php foreach ($thread as $m):
      $own = $me && !$m['is_initial'] && (int)($m['users_id'] ?? 0) === (int)$me['id']; ?>

    <?php if ($m['is_initial']): ?>
      <!-- Reporte inicial: bloque de color, no borde -->
      <div class="bg-brand-tint rounded-card p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-brand text-white grid place-items-center text-[11px] font-bold"><?= h($ini($m['author'])) ?></span>
            <div class="leading-tight">
              <div class="text-[13px] font-bold text-ink"><?= h($m['author']) ?></div>
              <div class="text-[10.5px] font-bold uppercase tracking-wide text-brand">Reporte inicial</div>
            </div>
          </div>
          <span class="text-[11px] text-faint"><?= fecha($m['date']) ?></span>
        </div>
        <div class="text-[13.5px] text-ink/85 leading-relaxed"><?= msg_html($m['content']) ?></div>
      </div>

    <?php elseif ($own): ?>
      <!-- Mensaje propio (derecha, turquesa) -->
      <div class="flex flex-col items-end">
        <div class="flex items-center gap-2 mb-1 pr-1">
          <span class="text-[10.5px] text-faint"><?= fecha($m['date']) ?></span>
          <span class="text-[12.5px] font-bold text-brand-dark">Tú</span>
        </div>
        <div class="bg-brand text-white rounded-card rounded-tr-md p-3.5 text-[13.5px] leading-relaxed max-w-[85%] [&_a]:text-white"><?= msg_html($m['content']) ?></div>
      </div>

    <?php else: ?>
      <!-- Mensaje de otros (izquierda) -->
      <div class="flex gap-2.5">
        <span class="w-7 h-7 shrink-0 rounded-full bg-ink/10 text-ink/70 grid place-items-center text-[11px] font-bold"><?= h($ini($m['author'])) ?></span>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-[12.5px] font-bold text-ink"><?= h($m['author']) ?></span>
            <span class="text-[10.5px] text-faint"><?= fecha($m['date']) ?></span>
          </div>
          <div class="bg-surface rounded-card rounded-tl-md p-3.5 text-[13.5px] text-ink/85 leading-relaxed"><?= msg_html($m['content']) ?></div>
        </div>
      </div>
    <?php endif; ?>

  <?php endforeach; ?>
</div>
