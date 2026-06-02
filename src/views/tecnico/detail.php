<?php /** @var array $t @var array $thread @var array $assets @var bool $ok */
$open = is_open_status((int)$t['status']); ?>
<a href="<?= h(url('tec/home')) ?>" class="text-sm text-slate-500 hover:text-slate-700">&larr; Mis tareas</a>

<?php if (!empty($ok)): ?>
  <div class="mt-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">✓ Ticket cerrado y hoja de servicio registrada.</div>
<?php endif; ?>

<div class="mt-3 mb-5">
  <div class="text-xs font-medium text-brand-dark"><?= h($t['entity_name']) ?></div>
  <div class="flex items-start justify-between gap-3 mt-0.5">
    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></h1>
    <?= status_chip((int)$t['status']) ?>
  </div>
  <div class="text-sm text-slate-500 mt-1"><?= h($t['cat_name'] ?? '—') ?> · <?= urgency_chip((int)$t['urgency']) ?></div>
  <?php if (!$open): ?>
    <a href="<?= h(url('print', ['id' => $t['id']])) ?>" target="_blank" class="inline-block mt-2 text-sm px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">🖨️ Imprimir hoja / PDF</a>
  <?php endif; ?>
</div>

<div class="grid md:grid-cols-3 gap-5">
  <div class="md:col-span-2 space-y-4">
    <?php partial('timeline', ['thread' => $thread]); ?>

    <?php if ($open): ?>
      <!-- Comentar -->
      <form method="post" action="<?= h(url('tec/comment')) ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-4">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <label class="block text-sm font-medium text-slate-700 mb-1">Agregar comentario</label>
        <textarea name="content" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand outline-none"></textarea>
        <div class="flex items-center justify-between mt-2">
          <input type="file" name="photo" accept="image/*" capture="environment" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand-light file:text-brand-dark">
          <button class="bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg px-4 py-2">Comentar</button>
        </div>
      </form>

      <!-- Hoja de servicio / cerrar -->
      <form method="post" action="<?= h(url('tec/close')) ?>" enctype="multipart/form-data" class="bg-white rounded-xl border-2 border-brand/30 p-4" x-data="{open:false}">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <button type="button" @click="open=!open" class="w-full flex items-center justify-between font-bold text-slate-800">
          <span>🧾 Cerrar con hoja de servicio</span>
          <span x-text="open ? '▲' : '▼'"></span>
        </button>
        <div x-show="open" x-cloak class="mt-4 space-y-4">
          <div class="space-y-2">
            <?php
              $checks = [
                'chk_limpieza' => 'Limpieza interna/externa',
                'chk_cableado' => 'Ajuste de cableado y conexiones',
                'chk_tornilleria' => 'Ajuste de tornillería general',
                'chk_lubricacion' => 'Lubricación de partes móviles',
                'chk_actualizacion' => 'Actualización / firmware',
                'chk_entregado' => 'Equipo entregado en funcionamiento',
              ];
              foreach ($checks as $name => $label): ?>
              <label class="flex items-center gap-2.5 text-sm text-slate-700">
                <input type="checkbox" name="<?= $name ?>" value="1" class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                <?= h($label) ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones / reporte</label>
            <textarea name="observaciones" rows="3" placeholder="Qué se hizo, refacciones, pendientes…" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand outline-none"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Foto de evidencia</label>
            <input type="file" name="photo" accept="image/*" capture="environment" class="text-sm text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand-light file:text-brand-dark">
          </div>
          <button class="w-full bg-brand hover:bg-brand-dark text-white font-bold rounded-lg py-3">Cerrar ticket</button>
        </div>
      </form>
    <?php else: ?>
      <div class="bg-slate-100 rounded-xl px-4 py-3 text-sm text-slate-500 text-center">Ticket cerrado.</div>
    <?php endif; ?>
  </div>

  <div class="space-y-4">
    <?php partial('linked_assets', ['assets' => $assets]); ?>
  </div>
</div>
<style>[x-cloak]{display:none!important}</style>
