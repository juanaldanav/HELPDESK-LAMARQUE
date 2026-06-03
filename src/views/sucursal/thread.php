<?php /** @var array $t @var array $thread @var array $assets @var bool $ok */
$open = is_open_status((int)$t['status']); ?>
<div class="max-w-md md:max-w-3xl lg:max-w-5xl mx-auto <?= $open ? 'pb-24 md:pb-0' : '' ?>">

  <a href="<?= h(url('tickets')) ?>" class="tap inline-flex items-center gap-1 text-[14px] font-semibold text-muted -ml-1 px-1 py-1 rounded-xl">
    <?= svg_icon('back', 20) ?> Mis tickets
  </a>

  <?php if (!empty($ok)): ?>
    <div class="mt-3 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2">
      <?= svg_icon('check', 18) ?> Tu reporte fue recibido. Te notificaremos cuando un técnico lo atienda.
    </div>
  <?php endif; ?>

  <!-- Resumen -->
  <div class="mt-3">
    <div class="flex items-start justify-between gap-3">
      <h1 class="text-[21px] font-extrabold tracking-tight leading-tight"><?= h($t['name']) ?></h1>
      <span class="shrink-0 mt-0.5"><?= status_chip((int)$t['status']) ?></span>
    </div>
    <div class="flex items-center gap-2.5 mt-2 text-[12.5px] font-semibold text-muted flex-wrap">
      <span class="font-mono text-faint">#<?= (int)$t['id'] ?></span>
      <span class="flex items-center gap-1.5">
        <span class="w-5 h-5 grid place-items-center rounded-md bg-brand-tint text-brand"><?= icon_for_category($t['cat_name'], 12) ?></span>
        <?= h($t['cat_name'] ?: 'General') ?>
      </span>
    </div>
    <div class="flex items-center gap-2 mt-2 text-[12.5px]">
      <?= urgency_chip((int)$t['urgency']) ?>
      <?php if ($t['tecnico_name']): ?>
        <span class="w-1 h-1 rounded-full bg-faint/50"></span>
        <span class="text-muted">Técnico: <span class="font-bold text-ink"><?= h($t['tecnico_name']) ?></span></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start mt-5">
    <div class="space-y-4 lg:order-2 lg:sticky lg:top-20">
      <?php partial('linked_assets', ['assets' => $assets]); ?>
    </div>

    <div class="lg:col-span-2 lg:order-1 mt-5 lg:mt-0">
      <div class="mb-2 flex items-center gap-2">
        <h2 class="text-[12px] font-bold uppercase tracking-wide text-muted">Conversación</h2>
        <span class="h-px flex-1 bg-ink/5"></span>
      </div>

      <?php partial('timeline', ['thread' => $thread]); ?>

      <?php if (!$open): ?>
        <div class="mt-5 bg-surface rounded-card px-4 py-3.5 flex items-center justify-between gap-3">
          <span class="text-[13.5px] font-semibold text-muted">Este ticket está cerrado.</span>
          <a href="<?= h(url('pdf', ['id' => $t['id']])) ?>" target="_blank" class="tap shrink-0 inline-flex items-center gap-1.5 text-[12.5px] font-bold px-3 py-2 rounded-xl bg-brand-tint text-brand-dark">
            <?= svg_icon('print', 15) ?> Hoja / PDF
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($open): ?>
    <!-- Composer fijo (ticket/reply) -->
    <div class="fixed md:static left-0 right-0 bottom-0 md:bottom-auto z-20 bg-surface md:bg-transparent px-3 md:px-0 pt-2.5 md:pt-4 pb-4 md:pb-0" x-data="{txt:'', foto:false}">
      <form method="post" action="<?= h(url('ticket/reply')) ?>" enctype="multipart/form-data" class="flex items-end gap-2 max-w-md md:max-w-none mx-auto md:bg-surface md:rounded-card md:p-3">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <label class="tap cursor-pointer w-11 h-11 shrink-0 grid place-items-center rounded-xl active:bg-brand-light transition-colors" :class="foto ? 'bg-brand text-white' : 'bg-brand-tint text-brand'">
          <?= svg_icon('camera', 22) ?>
          <input type="file" name="photo" accept="image/*" capture="environment" class="hidden" @change="foto = $event.target.files.length > 0">
        </label>
        <textarea name="content" x-model="txt" rows="1" placeholder="Escribe un mensaje…"
          class="flex-1 bg-canvas rounded-2xl px-4 py-3 text-[14px] placeholder:text-faint outline-none resize-none max-h-24"></textarea>
        <button type="submit" class="tap w-11 h-11 shrink-0 grid place-items-center rounded-xl transition-colors" :class="(txt.trim() || foto) ? 'bg-brand text-white' : 'bg-canvas text-faint'">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/></svg>
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>
