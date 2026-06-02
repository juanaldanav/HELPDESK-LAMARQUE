<?php /** @var array $t @var array $thread @var array $assets @var bool $ok */ ?>
<a href="<?= h(url('tickets')) ?>" class="text-sm text-slate-500 hover:text-slate-700">&larr; Mis tickets</a>

<?php if (!empty($ok)): ?>
  <div class="mt-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
    ✓ Tu reporte fue recibido. Te notificaremos cuando un técnico lo atienda.
  </div>
<?php endif; ?>

<div class="mt-3 mb-5">
  <div class="flex items-start justify-between gap-3">
    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></h1>
    <?= status_chip((int)$t['status']) ?>
  </div>
  <div class="text-sm text-slate-500 mt-1">
    <?= h($t['cat_name'] ?? '—') ?> · <?= urgency_chip((int)$t['urgency']) ?>
    <?php if ($t['tecnico_name']): ?> · Técnico: <span class="font-medium text-slate-700"><?= h($t['tecnico_name']) ?></span><?php endif; ?>
  </div>
</div>

<div class="grid md:grid-cols-3 gap-5">
  <div class="md:col-span-2 space-y-4">
    <?php partial('timeline', ['thread' => $thread]); ?>

    <?php if (is_open_status((int)$t['status'])): ?>
      <form method="post" action="<?= h(url('ticket/reply')) ?>" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-4">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <label class="block text-sm font-medium text-slate-700 mb-1">Agregar comentario</label>
        <textarea name="content" rows="2" placeholder="Escribe un mensaje…" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand outline-none"></textarea>
        <div class="flex items-center justify-between mt-2">
          <input type="file" name="photo" accept="image/*" capture="environment" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand-light file:text-brand-dark">
          <button class="bg-brand hover:bg-brand-dark text-white text-sm font-semibold rounded-lg px-4 py-2">Enviar</button>
        </div>
      </form>
    <?php else: ?>
      <div class="bg-slate-100 rounded-xl px-4 py-3 text-sm text-slate-500 text-center">Este ticket está cerrado.</div>
    <?php endif; ?>
  </div>

  <div class="space-y-4">
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
