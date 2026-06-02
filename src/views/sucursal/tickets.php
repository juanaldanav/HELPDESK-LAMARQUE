<?php /** @var array $list @var string $fstatus */ ?>
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Mis tickets</h1>
  <a href="<?= h(url('ticket/new')) ?>" class="bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg px-4 py-2">＋ Reportar</a>
</div>

<form method="get" class="mb-4 flex flex-wrap gap-2 items-center">
  <input type="hidden" name="r" value="tickets">
  <?php $opts = [''=>'Todos', '1'=>'Nuevos', '2'=>'En curso', '4'=>'En espera', '5'=>'Resueltos', '6'=>'Cerrados']; ?>
  <select name="status" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
    <?php foreach ($opts as $v => $lbl): ?>
      <option value="<?= $v ?>" <?= (string)$fstatus === (string)$v ? 'selected' : '' ?>><?= $lbl ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (empty($list)): ?>
  <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">No hay tickets con ese filtro.</div>
<?php else: ?>
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden divide-y divide-slate-100">
    <?php foreach ($list as $t): ?>
      <a href="<?= h(url('ticket/view', ['id' => $t['id']])) ?>" class="flex items-center justify-between gap-3 p-4 hover:bg-slate-50">
        <div class="min-w-0">
          <div class="font-semibold text-slate-800 truncate">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></div>
          <div class="text-xs text-slate-500 mt-0.5"><?= h($t['cat_name'] ?? '—') ?> · <?= fecha($t['date']) ?><?= $t['tecnico_name'] ? ' · ' . h($t['tecnico_name']) : '' ?></div>
        </div>
        <?= status_chip((int)$t['status']) ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
