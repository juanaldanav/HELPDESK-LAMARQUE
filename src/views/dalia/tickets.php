<?php /** @var array $list @var array $f @var array $sucursales @var array $tecnicos @var array $cats */
$g = fn($k) => h($f[$k] ?? ''); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Tickets · consolidado</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Todas las sucursales en una vista.</p>
  </div>
  <a href="<?= h(url('dalia/export', array_filter($f ?? []))) ?>" class="tap inline-flex items-center gap-2 bg-ink text-white text-[13px] font-bold rounded-xl px-4 py-2.5">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
    Exportar CSV
  </a>
</div>

<form method="get" class="bg-surface rounded-card p-4 mb-4 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
  <input type="hidden" name="r" value="dalia/tickets">
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal</label>
    <select name="entity" class="fld w-full">
      <option value="">Todas</option>
      <?php foreach ($sucursales as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (string)($f['entity'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Categoría</label>
    <select name="category" class="fld w-full">
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
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Técnico</label>
    <select name="tecnico" class="fld w-full">
      <option value="">Todos</option>
      <?php foreach ($tecnicos as $tc): ?>
        <option value="<?= (int)$tc['id'] ?>" <?= (string)($f['tecnico'] ?? '') === (string)$tc['id'] ? 'selected' : '' ?>><?= h($tc['display'] ?: $tc['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Estado</label>
    <select name="status" class="fld w-full">
      <?php foreach (['' => 'Todos', '1' => 'Nuevo', '2' => 'En curso', '4' => 'En espera', '5' => 'Resuelto', '6' => 'Cerrado'] as $v => $l): ?>
        <option value="<?= $v ?>" <?= (string)($f['status'] ?? '') === (string)$v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Desde</label><input type="date" name="from" value="<?= $g('from') ?>" class="fld w-full"></div>
  <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Hasta</label><input type="date" name="to" value="<?= $g('to') ?>" class="fld w-full"></div>
  <div class="sm:col-span-2 lg:col-span-4">
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Buscar título</label>
    <input type="search" name="q" value="<?= $g('q') ?>" placeholder="Palabra clave…" class="fld w-full">
  </div>
  <div class="lg:col-span-2 flex gap-2 items-end">
    <button class="tap flex-1 bg-brand hover:bg-brand-dark text-white text-[13px] font-bold rounded-xl px-4 py-2.5 transition-colors">Filtrar</button>
    <a href="<?= h(url('dalia/tickets')) ?>" class="tap px-4 py-2.5 text-[13px] font-bold rounded-xl bg-canvas text-muted">Limpiar</a>
  </div>
</form>

<div class="text-[13px] text-muted font-semibold mb-2.5"><?= count($list) ?> resultado<?= count($list) === 1 ? '' : 's' ?></div>

<div class="bg-surface rounded-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead>
        <tr class="text-[11px] uppercase tracking-wide text-faint">
          <th class="px-4 py-3 font-bold">#</th><th class="px-4 py-3 font-bold">Sucursal</th>
          <th class="px-4 py-3 font-bold">Título</th><th class="px-4 py-3 font-bold">Categoría</th>
          <th class="px-4 py-3 font-bold">Urgencia</th><th class="px-4 py-3 font-bold">Técnico</th>
          <th class="px-4 py-3 font-bold">Estado</th><th class="px-4 py-3 font-bold">Apertura</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $t): ?>
          <tr class="tap hover:bg-canvas cursor-pointer border-t border-canvas" onclick="location.href='<?= h(url('dalia/view', ['id' => $t['id']])) ?>'">
            <td class="px-4 py-3 font-mono text-faint text-[12.5px]"><?= (int)$t['id'] ?></td>
            <td class="px-4 py-3 whitespace-nowrap font-semibold text-[13px]"><?= h($t['entity_name']) ?></td>
            <td class="px-4 py-3 max-w-[260px] truncate font-bold text-[13.5px] text-ink"><?= h($t['name']) ?></td>
            <td class="px-4 py-3 text-muted text-[13px] whitespace-nowrap"><?= h($t['cat_name'] ?: '—') ?></td>
            <td class="px-4 py-3"><?= urgency_chip((int)$t['urgency']) ?></td>
            <td class="px-4 py-3 text-muted text-[13px] whitespace-nowrap"><?= h($t['tecnico_name'] ?: '—') ?></td>
            <td class="px-4 py-3"><?= status_chip((int)$t['status']) ?></td>
            <td class="px-4 py-3 text-faint text-[12.5px] whitespace-nowrap"><?= fecha($t['date']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($list)): ?>
          <tr><td colspan="8" class="px-4 py-10 text-center text-faint font-semibold">Sin resultados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
