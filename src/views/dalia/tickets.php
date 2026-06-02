<?php /** @var array $list @var array $f @var array $sucursales @var array $tecnicos @var array $cats */
$g = fn($k) => h($f[$k] ?? ''); ?>
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Tickets · consolidado</h1>
  <a href="<?= h(url('dalia/export', $f)) ?>" class="bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg px-4 py-2">Exportar CSV</a>
</div>

<form method="get" class="bg-white rounded-xl border border-slate-200 p-4 mb-4 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
  <input type="hidden" name="r" value="dalia/tickets">
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Sucursal</label>
    <select name="entity" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm bg-white">
      <option value="">Todas</option>
      <?php foreach ($sucursales as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (string)($f['entity'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Categoría</label>
    <select name="category" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm bg-white">
      <option value="">Todas</option>
      <?php foreach ($cats as $grp): ?>
        <optgroup label="<?= h($grp['name']) ?>">
          <?php foreach ($grp['items'] as $it): ?>
            <option value="<?= (int)$it['id'] ?>" <?= (string)($f['category'] ?? '') === (string)$it['id'] ? 'selected' : '' ?>><?= h($it['name']) ?></option>
          <?php endforeach; ?>
        </optgroup>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Técnico</label>
    <select name="tecnico" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm bg-white">
      <option value="">Todos</option>
      <?php foreach ($tecnicos as $tc): ?>
        <option value="<?= (int)$tc['id'] ?>" <?= (string)($f['tecnico'] ?? '') === (string)$tc['id'] ? 'selected' : '' ?>><?= h($tc['display'] ?: $tc['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm bg-white">
      <?php $st = [''=>'Todos','1'=>'Nuevo','2'=>'En curso','4'=>'En espera','5'=>'Resuelto','6'=>'Cerrado'];
      foreach ($st as $v=>$l): ?>
        <option value="<?= $v ?>" <?= (string)($f['status'] ?? '') === (string)$v ? 'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
    <input type="date" name="from" value="<?= $g('from') ?>" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm">
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
    <input type="date" name="to" value="<?= $g('to') ?>" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm">
  </div>
  <div class="sm:col-span-2 lg:col-span-4">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar título</label>
    <input type="search" name="q" value="<?= $g('q') ?>" class="w-full rounded-lg border border-slate-300 px-2 py-2 text-sm">
  </div>
  <div class="lg:col-span-2 flex gap-2">
    <button class="flex-1 bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg px-4 py-2">Filtrar</button>
    <a href="<?= h(url('dalia/tickets')) ?>" class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">Limpiar</a>
  </div>
</form>

<div class="text-sm text-slate-500 mb-2"><?= count($list) ?> resultado<?= count($list) === 1 ? '' : 's' ?></div>

<div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
      <tr>
        <th class="text-left px-3 py-2.5">#</th>
        <th class="text-left px-3 py-2.5">Sucursal</th>
        <th class="text-left px-3 py-2.5">Título</th>
        <th class="text-left px-3 py-2.5">Categoría</th>
        <th class="text-left px-3 py-2.5">Urgencia</th>
        <th class="text-left px-3 py-2.5">Técnico</th>
        <th class="text-left px-3 py-2.5">Estado</th>
        <th class="text-left px-3 py-2.5">Apertura</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($list as $t): ?>
        <tr class="hover:bg-slate-50 cursor-pointer" onclick="location.href='<?= h(url('dalia/view', ['id' => $t['id']])) ?>'">
          <td class="px-3 py-2.5 font-mono text-slate-500"><?= (int)$t['id'] ?></td>
          <td class="px-3 py-2.5 whitespace-nowrap"><?= h($t['entity_name']) ?></td>
          <td class="px-3 py-2.5 max-w-xs truncate font-medium text-slate-800"><?= h($t['name']) ?></td>
          <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap"><?= h($t['cat_name'] ?? '—') ?></td>
          <td class="px-3 py-2.5"><?= urgency_chip((int)$t['urgency']) ?></td>
          <td class="px-3 py-2.5 text-slate-600 whitespace-nowrap"><?= h($t['tecnico_name'] ?: '—') ?></td>
          <td class="px-3 py-2.5"><?= status_chip((int)$t['status']) ?></td>
          <td class="px-3 py-2.5 text-slate-500 whitespace-nowrap"><?= fecha($t['date']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($list)): ?>
        <tr><td colspan="8" class="px-3 py-8 text-center text-slate-400">Sin resultados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
