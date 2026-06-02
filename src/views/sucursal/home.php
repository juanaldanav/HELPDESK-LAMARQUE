<?php /** @var array $u @var array $counts @var array $recent */ ?>
<div class="mb-6">
  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Hola, <?= h($u['entity_name']) ?></h1>
  <p class="text-slate-500">¿Algo no está funcionando? Repórtalo y le damos seguimiento.</p>
</div>

<a href="<?= h(url('ticket/new')) ?>"
   class="flex items-center justify-center gap-2 w-full bg-brand hover:bg-brand-dark text-white font-bold text-lg rounded-2xl py-5 shadow-sm transition mb-6">
  <span class="text-2xl">＋</span> Reportar un problema
</a>

<div class="grid grid-cols-3 gap-3 mb-8">
  <?php
    $tiles = [
      ['Abiertos', $counts['abiertos'], 'text-blue-700', 'bg-blue-50'],
      ['En espera', $counts['espera'], 'text-amber-700', 'bg-amber-50'],
      ['Resueltos', $counts['resueltos'], 'text-green-700', 'bg-green-50'],
    ];
    foreach ($tiles as $t): ?>
    <div class="rounded-2xl <?= $t[3] ?> p-4 text-center">
      <div class="text-3xl font-extrabold <?= $t[2] ?>"><?= (int)$t[1] ?></div>
      <div class="text-xs font-medium text-slate-500 mt-1"><?= $t[0] ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="flex items-center justify-between mb-3">
  <h2 class="font-bold text-slate-800">Tickets recientes</h2>
  <a href="<?= h(url('tickets')) ?>" class="text-sm text-brand font-medium hover:underline">Ver todos</a>
</div>

<?php if (empty($recent)): ?>
  <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400">Aún no hay tickets.</div>
<?php else: ?>
  <div class="space-y-2">
    <?php foreach ($recent as $t): ?>
      <a href="<?= h(url('ticket/view', ['id' => $t['id']])) ?>" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-brand/40 transition">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="font-semibold text-slate-800 truncate">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></div>
            <div class="text-xs text-slate-500 mt-0.5"><?= h($t['cat_name'] ?? '—') ?> · <?= fecha($t['date']) ?></div>
          </div>
          <?= status_chip((int)$t['status']) ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
