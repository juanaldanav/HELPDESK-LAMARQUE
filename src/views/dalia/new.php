<?php /** @var array $cats @var array $sucursales */ ?>
<a href="<?= h(url('dalia/tickets')) ?>" class="tap inline-flex items-center gap-1 text-[14px] font-semibold text-muted -ml-1 px-1 py-1 rounded-xl">
  <?= svg_icon('back', 20) ?> Tickets
</a>
<h1 class="text-[24px] font-extrabold tracking-tight mt-2 mb-5">Levantar ticket</h1>

<form method="post" action="<?= h(url('dalia/create')) ?>" class="bg-surface rounded-card p-5 space-y-4 max-w-2xl" x-data="{u:3}">
  <?= csrf_field() ?>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal *</label>
    <select name="entity" required class="fld w-full">
      <option value="">Selecciona…</option>
      <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Asunto *</label>
    <input name="name" required maxlength="200" placeholder="Ej. La vitrina no enfría" class="fld w-full">
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Categoría *</label>
    <select name="category_id" required class="fld w-full">
      <option value="">Selecciona…</option>
      <?php foreach ($cats as $g): ?>
        <optgroup label="<?= h($g['name']) ?>">
          <option value="<?= (int)$g['id'] ?>"><?= h($g['name']) ?> (general)</option>
          <?php foreach ($g['items'] as $it): ?><option value="<?= (int)$it['id'] ?>"><?= h($it['name']) ?></option><?php endforeach; ?>
        </optgroup>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-2">Urgencia</label>
    <input type="hidden" name="urgency" :value="u">
    <div class="flex gap-1.5">
      <?php foreach (URGENCY as $v => $lbl): $hex = URGENCY_HEX[$v]; ?>
        <button type="button" @click="u=<?= $v ?>" class="tap flex-1 flex flex-col items-center gap-1 py-2.5 rounded-xl transition-colors"
                :class="u===<?= $v ?> ? 'text-white' : 'bg-canvas text-muted'" :style="u===<?= $v ?> ? 'background:<?= $hex ?>' : ''">
          <span class="w-2 h-2 rounded-full" :style="u===<?= $v ?> ? 'background:#fff' : 'background:<?= $hex ?>'"></span>
          <span class="text-[11px] font-bold"><?= h($lbl) ?></span>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
  <div>
    <label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Descripción *</label>
    <textarea name="content" required rows="4" placeholder="Describe el problema…" class="fld w-full resize-none"></textarea>
  </div>
  <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-xl py-3.5 transition-colors">Crear ticket</button>
  <p class="text-[12px] text-faint">Tras crearlo podrás asignar técnico interno o proveedor externo.</p>
</form>
