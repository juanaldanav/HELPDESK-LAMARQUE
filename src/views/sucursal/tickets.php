<?php /** @var array $list @var string $fstatus */
$filters = ['' => 'Todos', '1' => 'Nuevos', '2' => 'En curso', '4' => 'En espera', '5' => 'Resueltos', '6' => 'Cerrados'];
?>
<div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto">
  <h1 class="text-[26px] font-extrabold tracking-tight leading-tight mb-4">Mis tickets</h1>

  <!-- Filtro por estado (chips scrolleables) -->
  <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 mb-4" style="scrollbar-width:none">
    <?php foreach ($filters as $v => $lbl): $on = (string)$fstatus === (string)$v; ?>
      <a href="<?= h(url('tickets', $v === '' ? [] : ['status' => $v])) ?>"
         class="tap shrink-0 px-3.5 h-9 rounded-full text-[12.5px] font-bold inline-flex items-center <?= $on ? 'bg-brand text-white' : 'bg-surface text-muted' ?>"><?= $lbl ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($list)): ?>
    <div class="bg-surface rounded-card p-8 text-center text-muted font-semibold">No hay tickets con ese filtro.</div>
  <?php else: ?>
    <div class="space-y-2.5 lg:grid lg:grid-cols-2 lg:gap-2.5 lg:space-y-0 stagger">
      <?php foreach ($list as $t): ?>
        <a href="<?= h(url('ticket/view', ['id' => $t['id']])) ?>" class="tap block bg-surface rounded-card p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-bold text-[15px] text-ink leading-snug truncate"><?= h($t['name']) ?></div>
              <div class="text-[12.5px] text-muted mt-1 flex items-center gap-1.5 flex-wrap">
                <span class="font-mono text-faint">#<?= (int)$t['id'] ?></span>
                <span class="w-1 h-1 rounded-full bg-faint/50"></span>
                <span class="truncate"><?= h($t['cat_name'] ?: 'General') ?></span>
              </div>
              <div class="text-[11.5px] text-faint mt-0.5"><?= fecha($t['date']) ?><?= $t['tecnico_name'] ? ' · ' . h($t['tecnico_name']) : '' ?></div>
            </div>
            <?= status_chip((int)$t['status']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
