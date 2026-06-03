<?php /** @var array $t @var array $thread @var array $assets @var array $tecnicos @var bool $ok */ ?>
<a href="<?= h(url('dalia/tickets')) ?>" class="text-sm text-slate-500 hover:text-slate-700">&larr; Tickets</a>

<?php if (!empty($ok)): ?>
  <div class="mt-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">✓ Ticket actualizado.</div>
<?php endif; ?>

<div class="mt-3 mb-5">
  <div class="text-xs font-medium text-brand-dark"><?= h($t['entity_name']) ?></div>
  <div class="flex items-start justify-between gap-3 mt-0.5">
    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></h1>
    <?= status_chip((int)$t['status']) ?>
  </div>
  <div class="text-sm text-slate-500 mt-1"><?= h($t['cat_name'] ?? '—') ?> · <?= urgency_chip((int)$t['urgency']) ?></div>
  <a href="<?= h(url('print', ['id' => $t['id']])) ?>" target="_blank" class="inline-block mt-2 text-sm px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">🖨️ Imprimir / PDF</a>
</div>

<div class="grid md:grid-cols-3 gap-5">
  <div class="md:col-span-2 space-y-4">
    <?php partial('timeline', ['thread' => $thread]); ?>
  </div>

  <div class="space-y-4">
    <!-- Asignar técnico -->
    <form method="post" action="<?= h(url('dalia/assign')) ?>" class="bg-white rounded-xl border-2 border-brand/30 p-4">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
      <h3 class="font-bold text-slate-800 mb-3">Asignar / actualizar</h3>
      <label class="block text-sm font-medium text-slate-700 mb-1">Técnico</label>
      <select name="tecnico" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white mb-3">
        <option value="">— Sin cambio —</option>
        <?php foreach ($tecnicos as $tc): ?>
          <option value="<?= (int)$tc['id'] ?>"><?= h($tc['display'] ?: $tc['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="block text-sm font-medium text-slate-700 mb-1">Urgencia</label>
      <select name="urgency" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white mb-3">
        <?php foreach (URGENCY as $v => $l): ?>
          <option value="<?= $v ?>" <?= (int)$t['urgency'] === $v ? 'selected' : '' ?>><?= h($l) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de atención <span class="text-slate-400 font-normal">(opcional — va al calendario)</span></label>
      <input type="date" name="fecha" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
      <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg py-2.5">Guardar y notificar</button>
      <?php if ($t['tecnico_name']): ?>
        <p class="text-xs text-slate-500 mt-2">Asignado actualmente a: <span class="font-medium"><?= h($t['tecnico_name']) ?></span></p>
      <?php endif; ?>
    </form>

    <?php partial('linked_assets', ['assets' => $assets]); ?>

    <div class="bg-white rounded-xl border border-slate-200 p-4 text-sm">
      <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Detalles</h3>
      <dl class="space-y-1 text-slate-600">
        <div class="flex justify-between"><dt>Abierto</dt><dd><?= fecha($t['date']) ?></dd></div>
        <?php if (!empty($t['closedate']) && $t['closedate'] !== '0000-00-00 00:00:00'): ?>
          <div class="flex justify-between"><dt>Cerrado</dt><dd><?= fecha($t['closedate']) ?></dd></div>
        <?php endif; ?>
      </dl>
    </div>
  </div>
</div>
