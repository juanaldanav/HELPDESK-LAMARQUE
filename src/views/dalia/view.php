<?php /** @var array $t @var array $thread @var array $assets @var array $tecnicos @var bool $ok */ ?>
<a href="<?= h(url('dalia/tickets')) ?>" class="tap inline-flex items-center gap-1 text-[14px] font-semibold text-muted -ml-1 px-1 py-1 rounded-xl">
  <?= svg_icon('back', 20) ?> Tickets
</a>

<?php if (!empty($ok)): ?>
  <div class="mt-3 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2">
    <?= svg_icon('check', 18) ?> Ticket actualizado.
  </div>
<?php endif; ?>

<div class="flex flex-wrap items-start justify-between gap-3 mt-3 mb-6">
  <div>
    <div class="text-[11px] font-bold uppercase tracking-wide text-brand-dark flex items-center gap-1"><?= svg_icon('building', 13) ?> <?= h($t['entity_name']) ?></div>
    <div class="flex flex-wrap items-center gap-3 mt-1">
      <h1 class="text-[24px] font-extrabold tracking-tight">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></h1>
      <?= status_chip((int)$t['status']) ?>
    </div>
    <div class="flex items-center gap-2.5 mt-2 text-[13px] font-semibold text-muted">
      <span><?= h($t['cat_name'] ?: 'General') ?></span>
      <span class="w-1 h-1 rounded-full bg-faint/50"></span>
      <?= urgency_chip((int)$t['urgency']) ?>
    </div>
  </div>
  <a href="<?= h(url('pdf', ['id' => $t['id']])) ?>" target="_blank" class="tap inline-flex items-center gap-2 bg-surface text-ink text-[13px] font-bold rounded-xl px-4 py-2.5">
    <?= svg_icon('print', 16) ?> Imprimir / PDF
  </a>
</div>

<div class="grid lg:grid-cols-3 gap-5 items-start">
  <!-- Timeline + comentar -->
  <div class="lg:col-span-2">
    <?php partial('timeline', ['thread' => $thread]); ?>

    <form method="post" action="<?= h(url('dalia/comment')) ?>" enctype="multipart/form-data" class="mt-4 bg-surface rounded-card p-3" x-data="{txt:'', foto:false}">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
      <textarea name="content" x-model="txt" rows="2" placeholder="Escribe un comentario para la sucursal y el técnico…"
        class="w-full resize-none bg-transparent text-[14px] placeholder:text-faint outline-none px-1 pt-1"></textarea>
      <div class="flex items-center justify-between mt-1">
        <label class="tap cursor-pointer inline-flex items-center gap-1.5 text-[12.5px] font-semibold px-2 py-1.5 rounded-lg active:bg-canvas" :class="foto ? 'text-brand-dark' : 'text-muted'">
          <?= svg_icon('camera', 18) ?>
          <span x-text="foto ? 'Foto lista' : 'Foto'"></span>
          <input type="file" name="photo" accept="image/*" class="hidden" @change="foto = $event.target.files.length > 0">
        </label>
        <button type="submit" class="tap text-[13px] font-bold rounded-xl px-4 py-2 transition-colors"
                :class="(txt.trim() || foto) ? 'bg-brand text-white' : 'bg-canvas text-faint'">Comentar</button>
      </div>
    </form>
  </div>

  <!-- Sidebar -->
  <div class="space-y-4 lg:sticky lg:top-20">
    <form method="post" action="<?= h(url('dalia/assign')) ?>" class="bg-surface rounded-card overflow-hidden">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
      <div class="bg-brand text-white px-4 py-3"><h3 class="font-extrabold text-[14px]">Asignar / actualizar</h3></div>
      <div class="p-4 space-y-3.5">
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Técnico</label>
          <select name="tecnico" class="fld w-full">
            <option value="">— Sin cambio —</option>
            <?php foreach ($tecnicos as $tc): ?>
              <option value="<?= (int)$tc['id'] ?>"><?= h($tc['display'] ?: $tc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Urgencia</label>
          <select name="urgency" class="fld w-full">
            <?php foreach (URGENCY as $v => $l): ?>
              <option value="<?= $v ?>" <?= (int)$t['urgency'] === $v ? 'selected' : '' ?>><?= h($l) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Fecha de atención <span class="text-faint font-medium normal-case">(va al calendario)</span></label>
          <input type="date" name="fecha" class="fld w-full">
        </div>
        <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 mt-1 transition-colors">Guardar y notificar</button>
        <?php if ($t['tecnico_name']): ?>
          <p class="text-[12px] text-muted">Asignado actualmente a <span class="font-bold text-ink"><?= h($t['tecnico_name']) ?></span>.</p>
        <?php endif; ?>
      </div>
    </form>

    <?php partial('linked_assets', ['assets' => $assets]); ?>

    <div class="bg-surface rounded-card p-4">
      <div class="text-[10.5px] font-bold uppercase tracking-wide text-faint mb-2.5">Detalles</div>
      <dl class="space-y-2 text-[13px]">
        <div class="flex justify-between"><dt class="text-muted">Abierto</dt><dd class="font-semibold"><?= fecha($t['date']) ?></dd></div>
        <?php if (!empty($t['closedate']) && $t['closedate'] !== '0000-00-00 00:00:00'): ?>
          <div class="flex justify-between"><dt class="text-muted">Cerrado</dt><dd class="font-semibold"><?= fecha($t['closedate']) ?></dd></div>
        <?php endif; ?>
      </dl>
    </div>
  </div>
</div>
