<?php
/** @var int $y @var int $m @var string $ym @var array $events @var array $fil @var array $sucursales @var array $tecnicos @var bool $ok */
$MESES = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$first = sprintf('%04d-%02d-01', $y, $m);
$daysInMonth = (int)date('t', strtotime($first));
$startDow = (int)date('N', strtotime($first)) - 1; // 0 = lunes
$prev = date('Y-m', strtotime($first . ' -1 month'));
$next = date('Y-m', strtotime($first . ' +1 month'));
$today = date('Y-m-d');

$byDay = [];
foreach ($events as $ev) {
    $s = max($ev['fecha'], $first);
    $e = min($ev['fecha_fin'] ?: $ev['fecha'], date('Y-m-t', strtotime($first)));
    for ($d = $s; $d <= $e; $d = date('Y-m-d', strtotime($d . ' +1 day'))) {
        $byDay[(int)substr($d, 8, 2)][] = $ev;
    }
}
?>
<div x-data="{ openNew: false, sel: null, newDate: '<?= h($ym) ?>-01' }">

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex items-center gap-2">
    <a href="<?= h(url('dalia/calendar', array_filter(['ym' => $prev, 'entity' => $fil['entity'], 'tipo' => $fil['tipo']]))) ?>" class="tap w-10 h-10 grid place-items-center rounded-xl bg-surface text-muted"><?= svg_icon('back', 18) ?></a>
    <h1 class="text-[27px] font-extrabold tracking-tight"><?= $MESES[$m] ?> <?= $y ?></h1>
    <a href="<?= h(url('dalia/calendar', array_filter(['ym' => $next, 'entity' => $fil['entity'], 'tipo' => $fil['tipo']]))) ?>" class="tap w-10 h-10 grid place-items-center rounded-xl bg-surface text-muted rotate-180"><?= svg_icon('back', 18) ?></a>
  </div>
  <button @click="openNew = true" class="tap inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-extrabold text-[13.5px] rounded-xl px-4 py-2.5 transition-colors">
    <?= svg_icon('plus', 16) ?> Agendar mantenimiento
  </button>
</div>

<?php if (!empty($ok)): ?>
  <div class="mb-4 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2">
    <?= svg_icon('check', 18) ?> Mantenimiento agendado y notificado.
  </div>
<?php endif; ?>

<form method="get" class="flex flex-wrap gap-2 mb-4 items-center">
  <input type="hidden" name="r" value="dalia/calendar">
  <input type="month" name="ym" value="<?= h($ym) ?>" onchange="this.form.submit()" class="fld" style="width:auto">
  <select name="entity" onchange="this.form.submit()" class="fld" style="width:auto">
    <option value="">Todas las sucursales</option>
    <?php foreach ($sucursales as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= (string)($fil['entity'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="tipo" onchange="this.form.submit()" class="fld" style="width:auto">
    <option value="">Todos los tipos</option>
    <?php foreach (Agenda::TIPOS as $t): ?>
      <option value="<?= h($t) ?>" <?= ($fil['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= h($t) ?></option>
    <?php endforeach; ?>
  </select>
  <span class="text-[13px] text-muted font-semibold"><?= count($events) ?> evento<?= count($events) === 1 ? '' : 's' ?></span>
</form>

<!-- Leyenda -->
<div class="flex flex-wrap gap-x-4 gap-y-1.5 mb-3 text-[12px] font-semibold text-muted">
  <?php foreach (Agenda::TIPO_COLOR as $t => $c): ?>
    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:<?= $c ?>"></span><?= h($t) ?></span>
  <?php endforeach; ?>
</div>

<!-- Grid del mes -->
<div class="bg-surface rounded-card overflow-hidden">
  <div class="grid grid-cols-7 text-[11px] font-bold uppercase tracking-wide text-faint bg-canvas/60">
    <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d): ?>
      <div class="px-2 py-2.5 text-center"><?= $d ?></div>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-canvas">
    <?php for ($i = 0; $i < $startDow; $i++): ?><div class="bg-canvas/40 min-h-24"></div><?php endfor; ?>
    <?php for ($day = 1; $day <= $daysInMonth; $day++):
        $dateStr = sprintf('%04d-%02d-%02d', $y, $m, $day);
        $isToday = $dateStr === $today; ?>
      <div class="min-h-24 p-1.5 cursor-pointer hover:bg-brand-tint/40 transition-colors <?= $isToday ? 'bg-brand-tint' : '' ?>"
           @click="newDate='<?= $dateStr ?>'; openNew=true" title="Agendar el <?= $dateStr ?>">
        <?php if ($isToday): ?>
          <span class="inline-grid place-items-center w-6 h-6 rounded-full bg-brand text-white text-[11px] font-extrabold mb-1"><?= $day ?></span>
        <?php else: ?>
          <div class="text-[11.5px] font-bold mb-1 text-faint"><?= $day ?></div>
        <?php endif; ?>
        <div class="space-y-1">
          <?php foreach (($byDay[$day] ?? []) as $ev):
              $done = $ev['estado'] === 'realizado';
              $json = h(json_encode([
                  'id' => (int)$ev['id'], 'tipo' => $ev['tipo'], 'clase' => $ev['clase'],
                  'color' => Agenda::tipoColor($ev['tipo']),
                  'fecha' => $ev['fecha'], 'fecha_fin' => $ev['fecha_fin'],
                  'entity' => $ev['entity_name'], 'tag' => $ev['entity_tag'],
                  'tecnico' => $ev['tecnico_name'] ?: null, 'desc' => $ev['descripcion'],
                  'estado' => $ev['estado'], 'tickets_id' => $ev['tickets_id'] ? (int)$ev['tickets_id'] : null,
              ], JSON_UNESCAPED_UNICODE)); ?>
            <button @click.stop='sel = JSON.parse($el.dataset.ev)' data-ev="<?= $json ?>"
                    style="background:<?= Agenda::tipoColor($ev['tipo']) ?>"
                    class="tap w-full text-left text-[10.5px] font-semibold leading-tight text-white rounded-md px-1.5 py-1 <?= $done ? 'opacity-40 line-through' : 'hover:opacity-85' ?>">
              <span class="font-extrabold"><?= h($ev['entity_tag'] ?: mb_substr($ev['entity_name'], 0, 6)) ?></span>
              · <?= h(mb_substr($ev['tipo'], 0, 18)) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<!-- Detalle de evento -->
<template x-if="sel">
  <div class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="sel = null" @keydown.escape.window="sel = null">
    <div class="bg-surface rounded-card w-full max-w-md overflow-hidden" x-transition>
      <div class="px-5 py-3.5 text-white flex items-center justify-between" :style="`background:${sel.color}`">
        <h3 class="font-extrabold text-[15px]" x-text="sel.tipo"></h3>
        <button @click="sel = null" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
      </div>
      <div class="p-5">
        <dl class="space-y-2 text-[13.5px]">
          <div class="flex justify-between gap-3"><dt class="text-muted">Sucursal</dt><dd class="font-bold text-ink" x-text="sel.entity"></dd></div>
          <div class="flex justify-between gap-3"><dt class="text-muted">Fecha</dt><dd class="font-semibold" x-text="sel.fecha + (sel.fecha_fin ? ' → ' + sel.fecha_fin : '')"></dd></div>
          <div class="flex justify-between gap-3" x-show="sel.tecnico"><dt class="text-muted">Técnico</dt><dd class="font-semibold" x-text="sel.tecnico"></dd></div>
          <div class="flex justify-between gap-3"><dt class="text-muted">Clase</dt><dd class="font-semibold capitalize" x-text="sel.clase"></dd></div>
          <div class="flex justify-between gap-3"><dt class="text-muted">Estado</dt><dd class="font-bold capitalize" x-text="sel.estado"></dd></div>
          <div x-show="sel.desc"><dt class="text-muted mb-1">Notas</dt><dd class="text-ink/85" x-text="sel.desc"></dd></div>
        </dl>
        <a x-show="sel.tickets_id" :href="'<?= h(url('dalia/view')) ?>&id=' + sel.tickets_id"
           class="inline-flex items-center gap-1.5 mt-3 text-[13px] text-brand-dark font-bold"><?= svg_icon('external', 13) ?> Ver ticket vinculado</a>
        <div class="flex gap-2 mt-5">
          <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1" x-show="sel.estado !== 'realizado'">
            <?= csrf_field() ?>
            <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
            <input type="hidden" name="estado" value="realizado">
            <button class="tap w-full bg-brand hover:bg-brand-dark text-white text-[13px] font-extrabold rounded-xl py-2.5 transition-colors">Marcar realizado</button>
          </form>
          <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1" x-show="sel.estado === 'realizado'">
            <?= csrf_field() ?>
            <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
            <input type="hidden" name="estado" value="programado">
            <button class="tap w-full bg-canvas text-ink text-[13px] font-extrabold rounded-xl py-2.5">Reabrir</button>
          </form>
          <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1"
                onsubmit="return confirm('¿Cancelar este mantenimiento programado?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
            <input type="hidden" name="estado" value="cancelado">
            <button class="tap w-full text-[13px] font-extrabold rounded-xl py-2.5" style="color:#d83a34;background:#d83a341a">Cancelar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- Modal agendar -->
<div x-show="openNew" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="openNew = false" @keydown.escape.window="openNew = false">
  <form method="post" action="<?= h(url('dalia/agenda/create')) ?>" class="bg-surface rounded-card w-full max-w-md overflow-hidden" x-transition>
    <?= csrf_field() ?>
    <div class="bg-brand text-white px-5 py-3.5 flex items-center justify-between">
      <h3 class="font-extrabold text-[15px]">Agendar mantenimiento</h3>
      <button type="button" @click="openNew = false" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
    </div>
    <div class="p-5 space-y-3.5">
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal *</label>
        <select name="entity" required class="fld w-full">
          <option value="">Selecciona…</option>
          <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Tipo *</label>
        <select name="tipo" required class="fld w-full">
          <?php foreach (Agenda::TIPOS as $t): if ($t === 'Correctivo') continue; ?>
            <option value="<?= h($t) ?>"><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Fecha *</label>
          <input type="date" name="fecha" required x-model="newDate" class="fld w-full">
        </div>
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Fin <span class="text-faint normal-case">(rango)</span></label>
          <input type="date" name="fecha_fin" class="fld w-full">
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Técnico</label>
        <select name="tecnico" class="fld w-full">
          <option value="">— Por definir —</option>
          <?php foreach ($tecnicos as $tc): ?><option value="<?= (int)$tc['id'] ?>"><?= h($tc['display'] ?: $tc['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Notas</label>
        <textarea name="descripcion" rows="2" class="fld w-full resize-none" placeholder="Detalle del mantenimiento…"></textarea>
      </div>
      <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Agendar y notificar</button>
    </div>
  </form>
</div>

</div>
