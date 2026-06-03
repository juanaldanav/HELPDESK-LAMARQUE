<?php /** @var array $list @var array $f @var array $sucursales @var array $tecnicos @var array $cats */
$g = fn($k) => h($f[$k] ?? ''); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Tickets · consolidado</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Todas las sucursales en una vista.</p>
  </div>
  <div class="flex gap-2">
    <a href="<?= h(url('dalia/new')) ?>" class="tap inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white text-[13px] font-bold rounded-xl px-4 py-2.5 transition-colors">
      <?= svg_icon('plus', 16) ?> Levantar ticket
    </a>
    <a href="<?= h(url('dalia/export', array_filter($f ?? []))) ?>" class="tap inline-flex items-center gap-2 bg-ink text-white text-[13px] font-bold rounded-xl px-4 py-2.5">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
      Exportar CSV
    </a>
  </div>
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

<?php
// base de filtros para enlaces de paginación (sin page)
$pg = array_filter([
    'entity' => $f['entity'] ?? '', 'category' => $f['category'] ?? '', 'tecnico' => $f['tecnico'] ?? '',
    'status' => $f['status'] ?? '', 'from' => $f['from'] ?? '', 'to' => $f['to'] ?? '', 'q' => $f['q'] ?? '', 'per' => $per,
], fn($v) => $v !== '' && $v !== null);
$desde = $total ? (($page - 1) * $per + 1) : 0;
$hasta = min($page * $per, $total);
?>
<div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
  <div class="text-[13px] text-muted font-semibold"><?= $total ?> resultado<?= $total === 1 ? '' : 's' ?><?= $total ? " · mostrando $desde–$hasta" : '' ?></div>
  <form method="get" class="flex items-center gap-2">
    <?php foreach ($pg as $k => $v) if ($k !== 'per') echo '<input type="hidden" name="' . h($k) . '" value="' . h($v) . '">'; ?>
    <input type="hidden" name="r" value="dalia/tickets">
    <label class="text-[12px] text-muted font-semibold">Mostrar</label>
    <select name="per" onchange="this.form.submit()" class="fld" style="width:auto;padding:6px 10px;font-size:13px">
      <?php foreach ([25, 50, 100] as $opt): ?><option value="<?= $opt ?>" <?= $per === $opt ? 'selected' : '' ?>><?= $opt ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<!-- Escritorio: tabla (columnas que se ocultan en pantallas chicas para no hacer scroll) -->
<div class="hidden md:block bg-surface rounded-card overflow-hidden">
  <table class="w-full text-left table-fixed">
    <thead>
      <tr class="text-[11px] uppercase tracking-wide text-faint">
        <th class="px-3 py-3 font-bold w-14">#</th>
        <th class="px-3 py-3 font-bold w-40">Sucursal</th>
        <th class="px-3 py-3 font-bold">Asunto</th>
        <th class="px-3 py-3 font-bold w-28">Urgencia</th>
        <th class="px-3 py-3 font-bold w-40 hidden xl:table-cell">Técnico</th>
        <th class="px-3 py-3 font-bold w-28">Estado</th>
        <th class="px-3 py-3 font-bold w-32 hidden lg:table-cell">Apertura</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $t): ?>
        <tr class="tap hover:bg-canvas cursor-pointer border-t border-canvas" onclick="location.href='<?= h(url('dalia/view', ['id' => $t['id']])) ?>'">
          <td class="px-3 py-3 font-mono text-faint text-[12.5px]"><?= (int)$t['id'] ?></td>
          <td class="px-3 py-3 font-semibold text-[13px] truncate"><?= h($t['entity_name']) ?></td>
          <td class="px-3 py-3 min-w-0">
            <div class="font-bold text-[13.5px] text-ink truncate"><?= h($t['name']) ?></div>
            <div class="text-[11.5px] text-muted truncate"><?= h($t['cat_name'] ?: 'General') ?></div>
          </td>
          <td class="px-3 py-3"><?= urgency_chip((int)$t['urgency']) ?></td>
          <td class="px-3 py-3 text-muted text-[13px] truncate hidden xl:table-cell"><?= h($t['tecnico_name'] ?: '—') ?></td>
          <td class="px-3 py-3"><?= status_chip((int)$t['status']) ?></td>
          <td class="px-3 py-3 text-faint text-[12.5px] whitespace-nowrap hidden lg:table-cell"><?= fecha($t['date']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($list)): ?>
        <tr><td colspan="7" class="px-4 py-10 text-center text-faint font-semibold">Sin resultados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Móvil: tarjetas -->
<div class="md:hidden space-y-2.5">
  <?php foreach ($list as $t): ?>
    <a href="<?= h(url('dalia/view', ['id' => $t['id']])) ?>" class="tap block bg-surface rounded-card p-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wide text-brand-dark truncate"><?= h($t['entity_name']) ?></div>
          <div class="font-bold text-[14.5px] text-ink leading-snug truncate mt-0.5">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></div>
          <div class="text-[12px] text-muted mt-0.5 truncate"><?= h($t['cat_name'] ?: 'General') ?><?= $t['tecnico_name'] ? ' · ' . h($t['tecnico_name']) : '' ?></div>
        </div>
        <?= status_chip((int)$t['status']) ?>
      </div>
      <div class="mt-2 flex items-center gap-2.5"><?= urgency_chip((int)$t['urgency']) ?><span class="text-[11.5px] text-faint"><?= fecha($t['date']) ?></span></div>
    </a>
  <?php endforeach; ?>
  <?php if (empty($list)): ?><div class="bg-surface rounded-card p-8 text-center text-faint font-semibold">Sin resultados.</div><?php endif; ?>
</div>

<!-- Paginación -->
<?php if ($pages > 1): ?>
  <div class="flex items-center justify-center gap-1.5 mt-4">
    <?php
      $mk = fn($p) => h(url('dalia/tickets', array_merge($pg, ['page' => $p])));
      $win = range(max(1, $page - 2), min($pages, $page + 2));
    ?>
    <?php if ($page > 1): ?><a href="<?= $mk($page - 1) ?>" class="tap px-3 py-2 rounded-lg bg-surface text-muted text-[13px] font-bold">‹</a><?php endif; ?>
    <?php if ($win[0] > 1): ?><a href="<?= $mk(1) ?>" class="tap px-3 py-2 rounded-lg bg-surface text-muted text-[13px] font-bold">1</a><span class="text-faint px-1">…</span><?php endif; ?>
    <?php foreach ($win as $p): ?>
      <a href="<?= $mk($p) ?>" class="tap px-3.5 py-2 rounded-lg text-[13px] font-bold <?= $p === $page ? 'bg-brand text-white' : 'bg-surface text-muted' ?>"><?= $p ?></a>
    <?php endforeach; ?>
    <?php if (end($win) < $pages): ?><span class="text-faint px-1">…</span><a href="<?= $mk($pages) ?>" class="tap px-3 py-2 rounded-lg bg-surface text-muted text-[13px] font-bold"><?= $pages ?></a><?php endif; ?>
    <?php if ($page < $pages): ?><a href="<?= $mk($page + 1) ?>" class="tap px-3 py-2 rounded-lg bg-surface text-muted text-[13px] font-bold">›</a><?php endif; ?>
  </div>
<?php endif; ?>
