<?php /** @var array $cats @var array $assetsGrouped */ ?>
<div class="max-w-md md:max-w-2xl mx-auto" x-data="nuevoTicket()">
  <a href="<?= h(url('home')) ?>" class="tap inline-flex items-center gap-1 text-[14px] font-semibold text-muted -ml-1 px-1 py-1 rounded-xl">
    <?= svg_icon('back', 20) ?> Inicio
  </a>

  <form method="post" action="<?= h(url('ticket/create')) ?>" enctype="multipart/form-data" class="pt-3 pb-28 space-y-5 stagger">
    <?= csrf_field() ?>
    <h1 class="text-[24px] font-extrabold tracking-tight leading-tight">Reportar un problema</h1>

    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">¿Qué pasó? *</label>
      <input name="name" required maxlength="200" placeholder="Ej. La vitrina no enfría" class="fld">
    </div>

    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Categoría *</label>
      <select name="category_id" required class="fld">
        <option value="">Selecciona…</option>
        <?php foreach ($cats as $g): ?>
          <optgroup label="<?= h($g['name']) ?>">
            <option value="<?= (int)$g['id'] ?>"><?= h($g['name']) ?> (general)</option>
            <?php foreach ($g['items'] as $it): ?>
              <option value="<?= (int)$it['id'] ?>"><?= h($it['name']) ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
      <p class="text-[11.5px] text-faint mt-1.5">La prioridad la asigna el sistema según la categoría; coordinación puede ajustarla.</p>
    </div>

    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Descripción *</label>
      <textarea name="content" required rows="4" placeholder="Describe el problema y cómo afecta la operación…" class="fld resize-none"></textarea>
    </div>

    <!-- Equipo afectado: dropdown por categoría -->
    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Equipo afectado <span class="text-faint font-medium normal-case">(opcional)</span></label>
      <select name="asset_pick" class="fld">
        <option value="">— Sin equipo específico —</option>
        <?php foreach ($assetsGrouped as $g): ?>
          <optgroup label="<?= h($g['label']) ?>">
            <?php foreach ($g['items'] as $a): ?>
              <option value="<?= h($g['class']) ?>:<?= (int)$a['id'] ?>"><?= h($a['name']) ?> — <?= h($a['serial']) ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Emergencia (excepcional, la valida coordinación) -->
    <label class="flex items-center gap-3 bg-surface rounded-card px-4 py-3.5 cursor-pointer">
      <input type="checkbox" name="emergencia" value="1" class="peer sr-only"><span class="sw shrink-0"></span>
      <span class="leading-tight">
        <span class="block text-[14px] font-bold" style="color:#d83a34">Es una emergencia</span>
        <span class="block text-[12px] text-muted">La operación está detenida (ej. sin luz, sin agua, equipo crítico caído). Coordinación lo validará.</span>
      </span>
    </label>

    <!-- Fotos hasta 5 con previews -->
    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-2">Fotos <span class="text-faint font-medium normal-case">(opcional, hasta 5)</span></label>
      <div class="flex flex-wrap gap-2.5">
        <template x-for="(f, i) in fotos" :key="i">
          <div class="w-[72px] h-[72px] rounded-xl relative overflow-hidden bg-canvas">
            <img :src="f.url" class="w-full h-full object-cover">
            <button type="button" @click="quitar(i)" class="absolute -top-0 -right-0 w-6 h-6 grid place-items-center rounded-bl-xl bg-ink/80 text-white">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
          </div>
        </template>
        <label x-show="fotos.length < 5" class="tap cursor-pointer w-[72px] h-[72px] rounded-xl bg-brand-tint grid place-items-center text-brand active:bg-brand-light">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
          <input type="file" name="photos[]" accept="image/*" capture="environment" multiple class="hidden" x-ref="inp" @change="agregar($event)">
        </label>
      </div>
      <p class="text-[11.5px] text-faint mt-2" x-text="fotos.length + ' de 5 · toca + para agregar desde cámara o galería'"></p>
    </div>

    <!-- Enviar (fija en móvil, normal en escritorio) -->
    <div class="fixed md:static left-0 right-0 bottom-0 z-20 px-4 md:px-0 pt-3 md:pt-0 pb-5 md:pb-0 bg-gradient-to-t from-canvas via-canvas to-transparent md:bg-none">
      <div class="max-w-md md:max-w-none mx-auto">
        <button type="submit" class="tap w-full bg-brand active:bg-brand-dark hover:bg-brand-dark text-white font-extrabold text-[15px] rounded-2xl py-4 flex items-center justify-center gap-2 transition-colors">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/></svg>
          Enviar reporte
        </button>
      </div>
    </div>
  </form>
</div>

<script>
function nuevoTicket() {
  return {
    fotos: [],
    agregar(e) {
      for (const f of e.target.files) {
        if (this.fotos.length >= 5) break;
        this.fotos.push({ file: f, url: URL.createObjectURL(f) });
      }
      this.sync();
    },
    quitar(i) { URL.revokeObjectURL(this.fotos[i].url); this.fotos.splice(i, 1); this.sync(); },
    sync() {
      const dt = new DataTransfer();
      this.fotos.forEach(f => dt.items.add(f.file));
      this.$refs.inp.files = dt.files;
    }
  };
}
</script>
