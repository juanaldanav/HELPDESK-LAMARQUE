<?php /** @var array $cats */ ?>
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
    </div>

    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-2">Urgencia</label>
      <input type="hidden" name="urgency" :value="urgency">
      <div class="flex gap-1.5">
        <?php foreach (URGENCY as $v => $lbl): $hex = URGENCY_HEX[$v]; ?>
          <button type="button" @click="urgency=<?= $v ?>"
                  class="tap flex-1 flex flex-col items-center gap-1 py-2.5 rounded-xl transition-colors"
                  :class="urgency===<?= $v ?> ? 'text-white' : 'bg-canvas text-muted'"
                  :style="urgency===<?= $v ?> ? 'background:<?= $hex ?>' : ''">
            <span class="w-2 h-2 rounded-full" :style="urgency===<?= $v ?> ? 'background:#fff' : 'background:<?= $hex ?>'"></span>
            <span class="text-[11px] font-bold"><?= h($lbl) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Descripción *</label>
      <textarea name="content" required rows="4" placeholder="Describe el problema con detalle…" class="fld resize-none"></textarea>
    </div>

    <!-- Buscar activo -->
    <div class="relative">
      <label class="block text-[12px] font-bold uppercase tracking-wide text-muted mb-1.5">Equipo afectado <span class="text-faint font-medium normal-case">(opcional)</span></label>
      <template x-if="!selected">
        <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Busca por folio LMQ o nombre…" class="fld">
      </template>
      <template x-if="selected">
        <div class="flex items-center justify-between gap-2 rounded-2xl bg-brand-tint px-4 py-3">
          <span class="text-[13px] min-w-0"><span class="font-mono text-brand-dark" x-text="selected.serial"></span> · <span x-text="selected.name"></span></span>
          <button type="button" @click="clearSel()" class="tap text-[12px] font-bold text-muted shrink-0">Quitar</button>
        </div>
      </template>
      <input type="hidden" name="asset_itemtype" :value="selected ? selected.itemtype : ''">
      <input type="hidden" name="asset_id" :value="selected ? selected.id : ''">
      <div x-show="results.length && !selected" x-cloak class="absolute z-10 left-0 right-0 mt-1.5 bg-surface rounded-2xl overflow-hidden max-h-64 overflow-y-auto">
        <template x-for="r in results" :key="r.itemtype + r.id">
          <button type="button" @click="pick(r)" class="tap w-full text-left px-4 py-2.5 active:bg-brand-tint">
            <div class="text-[13.5px] font-bold text-ink" x-text="r.name"></div>
            <div class="text-[11.5px] text-muted"><span class="font-mono" x-text="r.serial"></span> · <span x-text="r.type_label"></span></div>
          </button>
        </template>
      </div>
    </div>

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
    urgency: 3, q: '', results: [], selected: null, fotos: [],
    search() {
      const s = this.q.trim();
      if (s.length < 2) { this.results = []; return; }
      fetch('<?= h(url('asset/search')) ?>&q=' + encodeURIComponent(s))
        .then(r => r.json()).then(d => this.results = d).catch(() => this.results = []);
    },
    pick(r) { this.selected = r; this.results = []; this.q = ''; },
    clearSel() { this.selected = null; },
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
