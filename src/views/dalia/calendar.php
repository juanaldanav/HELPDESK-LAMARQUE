<?php
/** @var int $y @var int $m @var string $ym @var array $events @var array $fil @var array $sucursales @var array $tecnicos @var bool $ok */
$MESES = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$first = sprintf('%04d-%02d-01', $y, $m);
$daysInMonth = (int)date('t', strtotime($first));
$startDow = (int)date('N', strtotime($first)) - 1; // 0 = lunes
$prev = date('Y-m', strtotime($first . ' -1 month'));
$next = date('Y-m', strtotime($first . ' +1 month'));
$today = date('Y-m-d');

// Eventos por día (rangos recortados al mes)
$byDay = [];
foreach ($events as $ev) {
    $s = max($ev['fecha'], $first);
    $e = min($ev['fecha_fin'] ?: $ev['fecha'], date('Y-m-t', strtotime($first)));
    for ($d = $s; $d <= $e; $d = date('Y-m-d', strtotime($d . ' +1 day'))) {
        $byDay[(int)substr($d, 8, 2)][] = $ev;
    }
}
?>
<div x-data="{ openNew: false, sel: null }">

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <div class="flex items-center gap-2">
    <a href="<?= h(url('dalia/calendar', array_filter(['ym' => $prev, 'entity' => $fil['entity'], 'tipo' => $fil['tipo']]))) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-brand/40">&larr;</a>
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900"><?= $MESES[$m] ?> <?= $y ?></h1>
    <a href="<?= h(url('dalia/calendar', array_filter(['ym' => $next, 'entity' => $fil['entity'], 'tipo' => $fil['tipo']]))) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-brand/40">&rarr;</a>
  </div>
  <button @click="openNew = true" class="bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg px-4 py-2">＋ Agendar mantenimiento</button>
</div>

<?php if (!empty($ok)): ?>
  <div class="mb-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-2.5 text-sm">✓ Mantenimiento agendado y notificado.</div>
<?php endif; ?>

<form method="get" class="flex flex-wrap gap-2 mb-4 items-center">
  <input type="hidden" name="r" value="dalia/calendar">
  <input type="month" name="ym" value="<?= h($ym) ?>" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm bg-white">
  <select name="entity" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm bg-white">
    <option value="">Todas las sucursales</option>
    <?php foreach ($sucursales as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= (string)($fil['entity'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="tipo" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm bg-white">
    <option value="">Todos los tipos</option>
    <?php foreach (Agenda::TIPOS as $t): ?>
      <option value="<?= h($t) ?>" <?= ($fil['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= h($t) ?></option>
    <?php endforeach; ?>
  </select>
  <span class="text-sm text-slate-500"><?= count($events) ?> evento<?= count($events) === 1 ? '' : 's' ?></span>
</form>

<!-- Leyenda -->
<div class="flex flex-wrap gap-3 mb-3 text-xs text-slate-600">
  <?php foreach (Agenda::TIPO_COLOR as $t => $c): ?>
    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full <?= $c ?>"></span><?= h($t) ?></span>
  <?php endforeach; ?>
</div>

<!-- Grid del mes -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="grid grid-cols-7 text-xs font-semibold uppercase tracking-wide text-slate-500 bg-slate-50">
    <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d): ?>
      <div class="px-2 py-2 text-center"><?= $d ?></div>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-slate-100">
    <?php for ($i = 0; $i < $startDow; $i++): ?><div class="bg-slate-50/50 min-h-24"></div><?php endfor; ?>
    <?php for ($day = 1; $day <= $daysInMonth; $day++):
        $dateStr = sprintf('%04d-%02d-%02d', $y, $m, $day);
        $isToday = $dateStr === $today; ?>
      <div class="min-h-24 p-1.5 <?= $isToday ? 'bg-brand-light/60' : '' ?>">
        <div class="text-xs font-semibold mb-1 <?= $isToday ? 'text-brand-dark' : 'text-slate-400' ?>"><?= $day ?></div>
        <div class="space-y-1">
          <?php foreach (($byDay[$day] ?? []) as $ev):
              $done = $ev['estado'] === 'realizado';
              $json = h(json_encode([
                  'id' => (int)$ev['id'], 'tipo' => $ev['tipo'], 'clase' => $ev['clase'],
                  'fecha' => $ev['fecha'], 'fecha_fin' => $ev['fecha_fin'],
                  'entity' => $ev['entity_name'], 'tag' => $ev['entity_tag'],
                  'tecnico' => $ev['tecnico_name'] ?: null, 'desc' => $ev['descripcion'],
                  'estado' => $ev['estado'], 'tickets_id' => $ev['tickets_id'] ? (int)$ev['tickets_id'] : null,
              ], JSON_UNESCAPED_UNICODE)); ?>
            <button @click='sel = JSON.parse($el.dataset.ev)' data-ev="<?= $json ?>"
                    class="w-full text-left text-[11px] leading-tight text-white rounded px-1.5 py-1 <?= Agenda::tipoColor($ev['tipo']) ?> <?= $done ? 'opacity-40 line-through' : 'hover:opacity-85' ?>">
              <span class="font-bold"><?= h($ev['entity_tag'] ?: mb_substr($ev['entity_name'], 0, 6)) ?></span>
              · <?= h(mb_substr($ev['tipo'], 0, 16)) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<!-- Detalle de evento -->
<template x-if="sel">
  <div class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,23,42,.45)" @click.self="sel = null">
    <div class="bg-white rounded-2xl w-full max-w-md p-5" x-transition>
      <div class="flex items-start justify-between gap-3">
        <h3 class="font-bold text-lg text-slate-900" x-text="sel.tipo"></h3>
        <button @click="sel = null" class="text-slate-400 hover:text-slate-700">✕</button>
      </div>
      <dl class="mt-3 space-y-1.5 text-sm text-slate-700">
        <div class="flex justify-between"><dt class="text-slate-500">Sucursal</dt><dd class="font-medium" x-text="sel.entity"></dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Fecha</dt><dd x-text="sel.fecha + (sel.fecha_fin ? ' → ' + sel.fecha_fin : '')"></dd></div>
        <div class="flex justify-between" x-show="sel.tecnico"><dt class="text-slate-500">Técnico</dt><dd x-text="sel.tecnico"></dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Clase</dt><dd x-text="sel.clase"></dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Estado</dt><dd class="font-medium" x-text="sel.estado"></dd></div>
        <div x-show="sel.desc"><dt class="text-slate-500">Notas</dt><dd x-text="sel.desc"></dd></div>
      </dl>
      <a x-show="sel.tickets_id" :href="'<?= h(url('dalia/view')) ?>&id=' + sel.tickets_id"
         class="inline-block mt-3 text-sm text-brand font-medium hover:underline">Ver ticket vinculado →</a>
      <div class="flex gap-2 mt-5">
        <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1" x-show="sel.estado !== 'realizado'">
          <?= csrf_field() ?>
          <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
          <input type="hidden" name="estado" value="realizado">
          <button class="w-full bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg py-2">✓ Marcar realizado</button>
        </form>
        <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1" x-show="sel.estado === 'realizado'">
          <?= csrf_field() ?>
          <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
          <input type="hidden" name="estado" value="programado">
          <button class="w-full border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg py-2 hover:bg-slate-50">Reabrir</button>
        </form>
        <form method="post" action="<?= h(url('dalia/agenda/estado')) ?>" class="flex-1"
              onsubmit="return confirm('¿Cancelar este mantenimiento programado?');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" :value="sel.id"><input type="hidden" name="ym" value="<?= h($ym) ?>">
          <input type="hidden" name="estado" value="cancelado">
          <button class="w-full border border-red-200 text-red-600 text-sm font-semibold rounded-lg py-2 hover:bg-red-50">Cancelar</button>
        </form>
      </div>
    </div>
  </div>
</template>

<!-- Modal agendar -->
<div x-show="openNew" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,23,42,.45)" @click.self="openNew = false">
  <form method="post" action="<?= h(url('dalia/agenda/create')) ?>" class="bg-white rounded-2xl w-full max-w-md p-5 space-y-3" x-transition>
    <?= csrf_field() ?>
    <div class="flex items-start justify-between">
      <h3 class="font-bold text-lg text-slate-900">Agendar mantenimiento</h3>
      <button type="button" @click="openNew = false" class="text-slate-400 hover:text-slate-700">✕</button>
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Sucursal *</label>
      <select name="entity" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
        <option value="">Selecciona…</option>
        <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Tipo *</label>
      <select name="tipo" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
        <?php foreach (Agenda::TIPOS as $t): if ($t === 'Correctivo') continue; ?>
          <option value="<?= h($t) ?>"><?= h($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha *</label>
        <input type="date" name="fecha" required value="<?= h($ym) ?>-01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Fin <span class="text-slate-400">(rango)</span></label>
        <input type="date" name="fecha_fin" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Técnico</label>
      <select name="tecnico" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
        <option value="">— Por definir —</option>
        <?php foreach ($tecnicos as $tc): ?><option value="<?= (int)$tc['id'] ?>"><?= h($tc['display'] ?: $tc['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
      <textarea name="descripcion" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Detalle del mantenimiento…"></textarea>
    </div>
    <button class="w-full bg-brand hover:bg-brand-dark text-white font-semibold rounded-lg py-2.5">Agendar y notificar</button>
  </form>
</div>

</div>
<style>[x-cloak]{display:none!important}</style>
