<?php /** @var array $gastos @var float $total @var string $month @var array $sucursales @var array $suppliers @var string $ok @var string $err */
$CLASE_LBL = ['fijo' => 'Fijo', 'variable' => 'Variable'];
$CLASE_COL = ['fijo' => '#5a4bd1', 'variable' => '#b5610f'];
?>
<div x-data="{ openNew:false }">

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Gastos</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Costos de mantenimiento — ligados a un ticket o gastos fijos sueltos.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="<?= h(url('dalia/expenses/export', ['month' => $month])) ?>" class="tap inline-flex items-center gap-2 bg-canvas hover:bg-faint/20 text-ink font-bold text-[13px] rounded-xl px-3.5 py-2.5 border border-faint/40">
      <?= svg_icon('download', 16) ?> Exportar
    </a>
    <button @click="openNew=true" class="tap inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-extrabold text-[13.5px] rounded-xl px-4 py-2.5 transition-colors">
      <?= svg_icon('plus', 16) ?> Registrar gasto
    </button>
  </div>
</div>

<?php if ($ok): ?>
  <div class="mb-4 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2"><?= svg_icon('check', 18) ?> <?= h($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="mb-4 font-semibold rounded-card px-4 py-3 text-[13.5px]" style="color:#b3261e;background:#fdecea"><?= h($err) ?></div>
<?php endif; ?>

<!-- Filtros + total -->
<form method="get" class="bg-surface rounded-card p-4 mb-4 flex flex-wrap items-end gap-3">
  <input type="hidden" name="r" value="dalia/expenses">
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Mes</label>
    <input type="month" name="month" value="<?= h($month) ?>" class="fld" style="padding:9px 12px;font-size:13px">
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal</label>
    <select name="entity" class="fld" style="padding:9px 12px;font-size:13px">
      <option value="">Todas</option>
      <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)$entity === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Clase</label>
    <select name="clase" class="fld" style="padding:9px 12px;font-size:13px">
      <option value="">Todas</option>
      <option value="fijo" <?= $clase === 'fijo' ? 'selected' : '' ?>>Fijo</option>
      <option value="variable" <?= $clase === 'variable' ? 'selected' : '' ?>>Variable</option>
    </select>
  </div>
  <button class="tap bg-canvas text-ink font-bold text-[13px] rounded-xl px-4 py-2.5 border border-faint/40">Filtrar</button>
  <div class="ml-auto text-right">
    <div class="text-[11px] font-bold uppercase tracking-wide text-muted">Total del filtro</div>
    <div class="text-[24px] font-extrabold text-brand"><?= money($total) ?></div>
  </div>
</form>

<!-- Lista -->
<?php if (empty($gastos)): ?>
  <div class="bg-surface rounded-card p-8 text-center text-muted text-[14px]">Sin gastos en este periodo. Usa “Registrar gasto”.</div>
<?php else: ?>
  <div class="bg-surface rounded-card overflow-hidden">
    <table class="w-full text-[13px]">
      <thead class="text-[11px] uppercase tracking-wide text-muted bg-canvas">
        <tr>
          <th class="text-left font-bold px-4 py-2.5">Fecha</th>
          <th class="text-left font-bold px-4 py-2.5">Concepto</th>
          <th class="text-left font-bold px-4 py-2.5 hidden md:table-cell">Sucursal</th>
          <th class="text-left font-bold px-4 py-2.5 hidden lg:table-cell">Proveedor</th>
          <th class="text-left font-bold px-4 py-2.5 hidden sm:table-cell">Clase</th>
          <th class="text-right font-bold px-4 py-2.5">Monto</th>
          <th class="px-4 py-2.5"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($gastos as $g): ?>
          <tr class="border-t border-faint/25">
            <td class="px-4 py-2.5 whitespace-nowrap text-muted"><?= h(date('d/M/Y', strtotime($g['fecha']))) ?></td>
            <td class="px-4 py-2.5">
              <div class="font-semibold text-ink"><?= h($g['concepto']) ?></div>
              <?php if (!empty($g['tickets_id'])): ?><a href="<?= h(url('dalia/view', ['id' => (int)$g['tickets_id']])) ?>" class="text-[11.5px] text-brand font-bold">Ticket #<?= (int)$g['tickets_id'] ?></a><?php endif; ?>
              <?php if (!empty($g['comprobante'])): ?> <a href="<?= h(url('img', ['p' => $g['comprobante']])) ?>" target="_blank" class="text-[11.5px] text-muted font-bold ml-1">· comprobante</a><?php endif; ?>
            </td>
            <td class="px-4 py-2.5 hidden md:table-cell text-muted"><?= h($g['entity_name'] ?? '—') ?></td>
            <td class="px-4 py-2.5 hidden lg:table-cell text-muted"><?= h($g['proveedor'] ?? '—') ?></td>
            <td class="px-4 py-2.5 hidden sm:table-cell">
              <span class="text-[11px] font-bold text-white px-2 py-0.5 rounded-full" style="background:<?= $CLASE_COL[$g['clase']] ?? '#5d6f70' ?>"><?= $CLASE_LBL[$g['clase']] ?? $g['clase'] ?></span>
            </td>
            <td class="px-4 py-2.5 text-right font-extrabold text-ink whitespace-nowrap"><?= money($g['monto']) ?></td>
            <td class="px-4 py-2.5 text-right">
              <form method="post" action="<?= h(url('dalia/expense/delete')) ?>" onsubmit="return confirm('¿Eliminar este gasto?');">
                <?= csrf_field() ?>
                <input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
                <button class="tap text-faint hover:text-[#d83a34]" title="Eliminar"><?= svg_icon('close', 15) ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<!-- Mantenimiento Wansoft (integración en desarrollo) -->
<div class="mt-5 rounded-card p-5 border border-dashed" style="border-color:rgba(0,105,112,.4);background:rgba(0,105,112,.04)">
  <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
    <h2 class="font-extrabold text-[15px] tracking-tight">Mantenimiento — Estado de Resultados (Wansoft)</h2>
    <span class="text-[10.5px] font-bold text-white px-2 py-0.5 rounded-full" style="background:#5a4bd1">EN DESARROLLO</span>
  </div>
  <p class="text-[13px] text-muted max-w-2xl">Jalará el total contable mensual de la línea <b>Mantenimiento</b> por sucursal directo del Estado de Resultados de Wansoft, para comparar el gasto real registrado en este portal contra la contabilidad. Endpoint identificado; pendiente de cablear la sincronización.</p>
  <button type="button" disabled class="mt-3 inline-flex items-center gap-2 bg-canvas text-faint font-bold text-[13px] rounded-xl px-4 py-2.5 border border-faint/40 cursor-not-allowed">
    <?= svg_icon('download', 16) ?> Sincronizar con Wansoft (próximamente)
  </button>
</div>

<!-- Modal registrar gasto -->
<div x-show="openNew" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="openNew=false" @keydown.escape.window="openNew=false">
  <form method="post" action="<?= h(url('dalia/expense/create')) ?>" enctype="multipart/form-data" class="bg-surface rounded-card w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto" x-transition>
    <?= csrf_field() ?>
    <div class="bg-brand text-white px-5 py-3.5 flex items-center justify-between sticky top-0">
      <h3 class="font-extrabold text-[15px]">Registrar gasto</h3>
      <button type="button" @click="openNew=false" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
    </div>
    <div class="p-5 space-y-3.5">
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Concepto *</label>
        <input name="concepto" required placeholder="ej. Recarga de gas refrigerante" class="fld w-full"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Monto (MXN) *</label>
          <input name="monto" required inputmode="decimal" placeholder="0.00" class="fld w-full"></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Fecha *</label>
          <input type="date" name="fecha" required value="<?= h(date('Y-m-d')) ?>" class="fld w-full"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal *</label>
          <select name="entity" required class="fld w-full">
            <option value="">Selecciona…</option>
            <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Clase *</label>
          <select name="clase" class="fld w-full">
            <option value="variable">Variable (por reparación)</option>
            <option value="fijo">Fijo (recurrente)</option>
          </select></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Proveedor</label>
          <select name="suppliers_id" class="fld w-full">
            <option value="">— Ninguno —</option>
            <?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Tipo</label>
          <input name="tipo" placeholder="preventivo / correctivo" class="fld w-full"></div>
      </div>
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Comprobante (foto o PDF)</label>
        <input type="file" name="comprobante" accept="image/*,application/pdf" class="fld w-full" style="padding:7px 10px"></div>
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Nota</label>
        <textarea name="nota" rows="2" placeholder="Detalle opcional…" class="fld w-full"></textarea></div>
      <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Guardar gasto</button>
    </div>
  </form>
</div>

</div>
