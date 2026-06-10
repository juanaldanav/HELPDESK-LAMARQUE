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
          <span x-text="foto ? 'Adjunto listo' : 'Foto / PDF'"></span>
          <input type="file" name="photo" accept="image/*,application/pdf" class="hidden" @change="foto = $event.target.files.length > 0">
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
      <div class="p-4 space-y-3.5" x-data="{ modo: '<?= $proveedor ? 'externo' : 'interno' ?>', nuevo: false }">
        <!-- Quién atiende: técnico interno o proveedor externo -->
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Atiende</label>
          <div class="flex gap-1.5 bg-brand-tint rounded-xl p-1 mb-2">
            <button type="button" @click="modo='interno'" class="tap flex-1 h-8 text-[12.5px] font-bold rounded-lg" :class="modo==='interno' ? 'bg-brand text-white' : 'text-brand-dark/70'">Técnico interno</button>
            <button type="button" @click="modo='externo'" class="tap flex-1 h-8 text-[12.5px] font-bold rounded-lg" :class="modo==='externo' ? 'bg-brand text-white' : 'text-brand-dark/70'">Proveedor externo</button>
          </div>
        </div>
        <div x-show="modo==='interno'">
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Técnico</label>
          <select name="tecnico" class="fld w-full">
            <option value="">— Sin cambio —</option>
            <?php foreach ($tecnicos as $tc): ?>
              <option value="<?= (int)$tc['id'] ?>"><?= h($tc['display'] ?: $tc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div x-show="modo==='externo'" x-cloak>
          <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Proveedor externo</label>
          <select name="proveedor" x-show="!nuevo" class="fld w-full mb-1.5">
            <option value="">— Selecciona —</option>
            <?php foreach ($proveedores as $pv): ?>
              <option value="<?= (int)$pv['id'] ?>" <?= $proveedor && (int)$proveedor['id'] === (int)$pv['id'] ? 'selected' : '' ?>><?= h($pv['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div x-show="nuevo" x-cloak class="space-y-1.5 mb-1.5">
            <input name="proveedor_nuevo" placeholder="Nombre del proveedor" class="fld w-full">
            <input name="proveedor_email" type="email" placeholder="Correo (opcional)" class="fld w-full">
            <input name="proveedor_tel" type="tel" placeholder="Teléfono (opcional)" class="fld w-full">
          </div>
          <button type="button" @click="nuevo=!nuevo" class="tap text-[12px] font-bold text-brand-dark" x-text="nuevo ? '← Elegir existente' : '+ Nuevo proveedor'"></button>
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
          <p class="text-[12px] text-muted">Técnico: <span class="font-bold text-ink"><?= h($t['tecnico_name']) ?></span></p>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($proveedor): ?>
      <!-- Contacto del proveedor asignado -->
      <div class="bg-surface rounded-card p-4">
        <div class="text-[10.5px] font-bold uppercase tracking-wide text-faint mb-2">Proveedor externo asignado</div>
        <div class="font-bold text-[14px] text-ink"><?= h($proveedor['name']) ?></div>
        <div class="flex flex-wrap gap-2 mt-2.5">
          <?php if (!empty($proveedor['phonenumber'])): ?>
            <a href="tel:<?= h(preg_replace('~[^0-9+]~', '', $proveedor['phonenumber'])) ?>" class="tap inline-flex items-center gap-1.5 text-[12px] font-bold px-3 py-2 rounded-xl bg-brand-tint text-brand-dark">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.13.96.36 1.9.7 2.8a2 2 0 0 1-.45 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.45c.9.34 1.84.57 2.8.7A2 2 0 0 1 22 16.9Z"/></svg>
              Llamar · <?= h($proveedor['phonenumber']) ?>
            </a>
          <?php endif; ?>
          <?php if (!empty($proveedor['email'])): ?>
            <a href="mailto:<?= h($proveedor['email']) ?>" class="tap inline-flex items-center gap-1.5 text-[12px] font-bold px-3 py-2 rounded-xl bg-brand-tint text-brand-dark">
              <?= svg_icon('mail', 14) ?> <?= h($proveedor['email']) ?>
            </a>
          <?php endif; ?>
        </div>
        <?php if (!empty($proveedor['email'])): ?>
          <form method="post" action="<?= h(url('dalia/notify_prov')) ?>" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="tap w-full bg-ink text-white text-[13px] font-extrabold rounded-xl py-2.5">Enviar ticket por correo al proveedor</button>
          </form>
        <?php endif; ?>
        <p class="text-[11.5px] text-faint mt-2.5">Cuando el proveedor mande su hoja de servicio (correo/WhatsApp), adjúntala como PDF o foto en un comentario: queda en el historial del mantenimiento.</p>
      </div>
    <?php endif; ?>

    <?php if (is_open_status((int)$t['status'])): ?>
      <form method="post" action="<?= h(url('dalia/close')) ?>" class="bg-surface rounded-card p-4" x-data="{open:false}"
            onsubmit="return confirm('¿Cerrar este ticket?');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <button type="button" @click="open=!open" class="w-full flex items-center justify-between font-bold text-[13.5px] text-ink">
          <span>Cerrar ticket (coordinación)</span><span x-text="open?'▲':'▼'"></span>
        </button>
        <div x-show="open" x-cloak class="mt-3 space-y-2">
          <textarea name="nota" rows="2" placeholder="Nota de cierre (qué se resolvió)…" class="fld w-full resize-none"></textarea>
          <button class="tap w-full text-white font-extrabold text-[13.5px] rounded-xl py-2.5" style="background:#1d8a5a">Cerrar y generar hoja</button>
          <p class="text-[11.5px] text-faint">Úsalo cuando lo atiende un proveedor externo o se resuelve sin técnico interno.</p>
        </div>
      </form>
    <?php endif; ?>

    <?php partial('linked_assets', ['assets' => $assets]); ?>

    <!-- Costo del mantenimiento -->
    <div class="bg-surface rounded-card p-4" x-data="{add:false}">
      <div class="flex items-center justify-between mb-2.5">
        <div class="text-[10.5px] font-bold uppercase tracking-wide text-faint">Costo del mantenimiento</div>
        <span class="text-[15px] font-extrabold text-brand"><?= money($gastos_total ?? 0) ?></span>
      </div>
      <?php if (!empty($gastos)): ?>
        <ul class="space-y-1.5 mb-2">
          <?php foreach ($gastos as $g): ?>
            <li class="flex items-center justify-between gap-2 text-[12.5px]">
              <span class="min-w-0 truncate text-ink"><?= h($g['concepto']) ?><?= $g['proveedor'] ? ' · ' . h($g['proveedor']) : '' ?>
                <?php if (!empty($g['comprobante'])): ?><a href="<?= h(url('img', ['p' => $g['comprobante']])) ?>" target="_blank" class="text-faint">·comp.</a><?php endif; ?>
              </span>
              <span class="flex items-center gap-1.5 shrink-0">
                <span class="font-bold"><?= money($g['monto']) ?></span>
                <form method="post" action="<?= h(url('dalia/expense/delete')) ?>" onsubmit="return confirm('¿Eliminar gasto?');">
                  <?= csrf_field() ?><input type="hidden" name="gid" value="<?= (int)$g['id'] ?>">
                  <button class="tap text-faint hover:text-[#d83a34]" title="Eliminar"><?= svg_icon('close', 13) ?></button>
                </form>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <button type="button" @click="add=!add" class="tap text-[12px] font-bold text-brand-dark"><span x-text="add ? '← Cancelar' : '+ Agregar costo'"></span></button>
      <form x-show="add" x-cloak method="post" action="<?= h(url('dalia/expense/create')) ?>" enctype="multipart/form-data" class="mt-2 space-y-2">
        <?= csrf_field() ?>
        <input type="hidden" name="tickets_id" value="<?= (int)$t['id'] ?>">
        <input name="concepto" required placeholder="Concepto (ej. mano de obra)" class="fld w-full" style="padding:8px 11px;font-size:13px">
        <div class="grid grid-cols-2 gap-2">
          <input name="monto" required inputmode="decimal" placeholder="Monto" class="fld w-full" style="padding:8px 11px;font-size:13px">
          <input type="date" name="fecha" required value="<?= h(date('Y-m-d')) ?>" class="fld w-full" style="padding:8px 11px;font-size:13px">
        </div>
        <select name="clase" class="fld w-full" style="padding:8px 11px;font-size:13px">
          <option value="variable">Variable (por reparación)</option>
          <option value="fijo">Fijo (recurrente)</option>
        </select>
        <input type="file" name="comprobante" accept="image/*,application/pdf" class="fld w-full" style="padding:6px 9px;font-size:12px">
        <button class="tap w-full bg-brand text-white font-extrabold text-[13px] rounded-xl py-2.5">Guardar costo</button>
      </form>
    </div>

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
