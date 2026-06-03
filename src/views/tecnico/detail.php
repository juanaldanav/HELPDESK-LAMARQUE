<?php /** @var array $t @var array $thread @var array $assets @var bool $ok */
$open = is_open_status((int)$t['status']); ?>
<div class="max-w-md md:max-w-3xl lg:max-w-5xl mx-auto <?= $open ? 'pb-36 lg:pb-0' : '' ?>" x-data="{ sheet:false }">

  <a href="<?= h(url('tec/home')) ?>" class="tap inline-flex items-center gap-1 text-[14px] font-semibold text-muted -ml-1 px-1 py-1 rounded-xl">
    <?= svg_icon('back', 20) ?> Mis tareas
  </a>

  <?php if (!empty($ok)): ?>
    <div class="mt-3 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2">
      <?= svg_icon('check', 18) ?> Ticket cerrado y hoja de servicio registrada.
    </div>
  <?php endif; ?>

  <!-- Resumen -->
  <div class="mt-3">
    <div class="text-[11px] font-bold uppercase tracking-wide text-brand-dark flex items-center gap-1">
      <?= svg_icon('building', 13) ?> <?= h($t['entity_name']) ?>
    </div>
    <div class="flex items-start justify-between gap-3 mt-1.5">
      <h1 class="text-[22px] font-extrabold tracking-tight leading-tight">#<?= (int)$t['id'] ?> · <?= h($t['name']) ?></h1>
      <span class="shrink-0 mt-1"><?= status_chip((int)$t['status']) ?></span>
    </div>
    <div class="flex flex-wrap items-center gap-3 mt-2 text-[12.5px] font-semibold text-muted">
      <span class="flex items-center gap-1.5">
        <span class="w-5 h-5 grid place-items-center rounded-md bg-brand-tint text-brand"><?= icon_for_category($t['cat_name'], 12) ?></span>
        <?= h($t['cat_name'] ?: 'General') ?>
      </span>
      <?= urgency_chip((int)$t['urgency']) ?>
    </div>
  </div>

  <!-- Desktop: 2 columnas (actividad | sidebar). Móvil: una sola, sidebar arriba. -->
  <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start mt-5">

    <!-- Sidebar (en móvil va primero; en desktop a la derecha) -->
    <div class="space-y-4 lg:order-2 lg:sticky lg:top-20">
      <?php partial('linked_assets', ['assets' => $assets]); ?>
      <div class="bg-surface rounded-card p-4 text-[13px]">
        <div class="text-[10.5px] font-bold uppercase tracking-wide text-faint mb-2">Detalles</div>
        <div class="flex justify-between text-muted"><span>Abierto</span><span class="font-semibold text-ink"><?= fecha($t['date']) ?></span></div>
        <?php if (!empty($t['closedate']) && $t['closedate'] !== '0000-00-00 00:00:00'): ?>
          <div class="flex justify-between text-muted mt-1"><span>Cerrado</span><span class="font-semibold text-ink"><?= fecha($t['closedate']) ?></span></div>
        <?php endif; ?>
      </div>
      <?php if ($open): ?>
        <!-- CTA desktop (la barra fija es solo móvil) -->
        <button @click="sheet=true" type="button"
          class="tap hidden lg:flex w-full bg-brand active:bg-brand-dark hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-4 items-center justify-center gap-2 transition-colors">
          <?= svg_icon('check', 20) ?> Cerrar con hoja de servicio
        </button>
      <?php endif; ?>
    </div>

    <!-- Columna principal: actividad -->
    <div class="lg:col-span-2 lg:order-1 mt-5 lg:mt-0">
      <div class="mb-2 flex items-center gap-2">
        <h2 class="text-[12px] font-bold uppercase tracking-wide text-muted">Actividad</h2>
        <span class="h-px flex-1 bg-ink/5"></span>
      </div>

      <?php partial('timeline', ['thread' => $thread]); ?>

      <?php if ($open): ?>
        <!-- Compositor · tec/comment -->
        <form method="post" action="<?= h(url('tec/comment')) ?>" enctype="multipart/form-data" class="mt-5 bg-surface rounded-card p-3" x-data="{txt:'', foto:false}">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <textarea name="content" x-model="txt" rows="2" placeholder="Escribe una actualización…"
            class="w-full resize-none bg-transparent text-[14px] placeholder:text-faint outline-none px-1 pt-1"></textarea>
          <div class="flex items-center justify-between mt-1">
            <label class="tap cursor-pointer inline-flex items-center gap-1.5 text-[12.5px] font-semibold px-2 py-1.5 rounded-lg active:bg-canvas" :class="foto ? 'text-brand-dark' : 'text-muted'">
              <?= svg_icon('camera', 18) ?>
              <span x-text="foto ? 'Foto lista' : 'Foto'"></span>
              <input type="file" name="photo" accept="image/*" capture="environment" class="hidden" @change="foto = $event.target.files.length > 0">
            </label>
            <button type="submit" class="tap text-[13px] font-bold rounded-xl px-4 py-2 transition-colors"
                    :class="(txt.trim() || foto) ? 'bg-brand text-white' : 'bg-canvas text-faint'">Comentar</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($open): ?>
    <!-- Barra fija: abrir hoja de servicio (solo móvil) -->
    <div class="lg:hidden fixed left-0 right-0 bottom-0 z-20 px-4 pt-3 pb-5 bg-gradient-to-t from-canvas via-canvas to-transparent">
      <div class="max-w-md mx-auto">
        <button @click="sheet=true" type="button"
          class="tap w-full bg-brand active:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-4 flex items-center justify-center gap-2">
          <?= svg_icon('check', 20) ?> Cerrar con hoja de servicio
        </button>
      </div>
    </div>

    <!-- Bottom sheet · tec/close -->
    <div x-show="sheet" x-cloak class="fixed inset-0 z-30" @keydown.escape.window="sheet=false">
      <div x-show="sheet" x-transition.opacity.duration.200ms @click="sheet=false" class="absolute inset-0 bg-ink/45"></div>

      <form method="post" action="<?= h(url('tec/close')) ?>" enctype="multipart/form-data"
        x-show="sheet"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
        class="absolute bottom-0 left-0 right-0 max-h-[88%] flex flex-col bg-canvas rounded-t-[28px] overflow-hidden max-w-md mx-auto">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">

        <div class="shrink-0 pt-2.5 pb-3 px-5 bg-canvas">
          <div class="w-10 h-1.5 rounded-full bg-ink/15 mx-auto mb-3"></div>
          <div class="flex items-center justify-between">
            <h3 class="text-[18px] font-extrabold tracking-tight">Hoja de servicio</h3>
            <button @click="sheet=false" type="button" class="tap w-9 h-9 grid place-items-center rounded-xl bg-surface text-muted active:bg-brand-tint">
              <?= svg_icon('close', 18) ?>
            </button>
          </div>
          <p class="text-[12.5px] text-muted mt-0.5">Marca lo realizado y registra evidencia para cerrar #<?= (int)$t['id'] ?>.</p>
        </div>

        <div class="overflow-y-auto px-5 pb-4 space-y-5">
          <div class="bg-surface rounded-card divide-y divide-canvas overflow-hidden">
            <?php
              $checks = [
                'chk_limpieza'      => 'Limpieza interna / externa',
                'chk_cableado'      => 'Ajuste de cableado y conexiones',
                'chk_tornilleria'   => 'Ajuste de tornillería general',
                'chk_lubricacion'   => 'Lubricación de partes móviles',
                'chk_actualizacion' => 'Actualización / firmware',
                'chk_entregado'     => 'Equipo entregado en funcionamiento',
              ];
              foreach ($checks as $name => $label): ?>
              <label class="flex items-center justify-between gap-3 px-4 py-3.5 cursor-pointer">
                <span class="text-[14px] font-semibold text-ink"><?= h($label) ?></span>
                <input type="checkbox" name="<?= $name ?>" value="1" class="peer sr-only"><span class="sw shrink-0"></span>
              </label>
            <?php endforeach; ?>
          </div>

          <div>
            <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Observaciones / reporte</label>
            <textarea name="observaciones" rows="3" placeholder="Qué se hizo, refacciones usadas, pendientes…"
              class="w-full bg-surface rounded-card px-4 py-3 text-[14px] placeholder:text-faint outline-none focus:ring-2 focus:ring-brand resize-none"></textarea>
          </div>

          <div>
            <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Foto de evidencia</label>
            <label class="tap cursor-pointer flex items-center gap-3 bg-brand-tint rounded-card px-4 py-3.5 active:bg-brand-light" x-data="{foto:false}">
              <span class="w-11 h-11 shrink-0 grid place-items-center rounded-xl bg-brand text-white"><?= svg_icon('camera', 22) ?></span>
              <span class="leading-tight">
                <span class="block text-[14px] font-bold text-brand-dark" x-text="foto ? 'Foto lista ✓' : 'Tomar / subir foto'">Tomar / subir foto</span>
                <span class="block text-[12px] text-muted">Cámara o galería · evidencia del cierre</span>
              </span>
              <input type="file" name="photo" accept="image/*" capture="environment" class="hidden" @change="foto = $event.target.files.length > 0">
            </label>
          </div>
        </div>

        <div class="shrink-0 px-5 pt-3 pb-6 bg-canvas">
          <button type="submit" class="tap w-full bg-brand active:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-4">
            Cerrar ticket #<?= (int)$t['id'] ?>
          </button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="mt-5 bg-surface rounded-card px-4 py-3.5 flex items-center justify-between gap-3">
      <span class="text-[13.5px] font-semibold text-muted">Ticket cerrado.</span>
      <a href="<?= h(url('pdf', ['id' => $t['id']])) ?>" target="_blank" class="tap shrink-0 inline-flex items-center gap-1.5 text-[12.5px] font-bold px-3 py-2 rounded-xl bg-brand-tint text-brand-dark">
        <?= svg_icon('check', 15) ?> Hoja / PDF
      </a>
    </div>
  <?php endif; ?>
</div>
