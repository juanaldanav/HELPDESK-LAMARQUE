<?php /** @var array $cats */ ?>
<a href="<?= h(url('home')) ?>" class="text-sm text-slate-500 hover:text-slate-700">&larr; Volver</a>
<h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mt-2 mb-5">Reportar un problema</h1>

<form method="post" action="<?= h(url('ticket/create')) ?>" enctype="multipart/form-data"
      class="bg-white rounded-2xl border border-slate-200 p-5 space-y-5 max-w-2xl"
      x-data="assetPicker()">
  <?= csrf_field() ?>

  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">¿Qué pasó? *</label>
    <input name="name" required maxlength="200" placeholder="Ej. La vitrina no enfría"
           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none">
  </div>

  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Categoría *</label>
    <select name="category_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-brand outline-none">
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
    <label class="block text-sm font-medium text-slate-700 mb-1">Urgencia</label>
    <select name="urgency" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-brand outline-none">
      <option value="1">Muy alta — algo crítico parado</option>
      <option value="2">Alta</option>
      <option value="3" selected>Media</option>
      <option value="4">Baja</option>
      <option value="5">Muy baja</option>
    </select>
  </div>

  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Descripción *</label>
    <textarea name="content" required rows="4" placeholder="Describe el problema con detalle…"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none"></textarea>
  </div>

  <!-- Buscar activo -->
  <div class="relative">
    <label class="block text-sm font-medium text-slate-700 mb-1">Equipo afectado <span class="text-slate-400 font-normal">(opcional)</span></label>
    <template x-if="!selected">
      <input type="text" x-model="q" @input.debounce.300ms="search()" placeholder="Busca por folio LMQ o nombre…"
             class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-brand outline-none">
    </template>
    <template x-if="selected">
      <div class="flex items-center justify-between gap-2 rounded-lg border border-brand/40 bg-brand-light px-3 py-2.5">
        <span class="text-sm"><span class="font-mono" x-text="selected.serial"></span> — <span x-text="selected.name"></span></span>
        <button type="button" @click="clearSel()" class="text-slate-500 hover:text-red-600 text-sm">Quitar</button>
      </div>
    </template>
    <input type="hidden" name="asset_itemtype" :value="selected ? selected.itemtype : ''">
    <input type="hidden" name="asset_id" :value="selected ? selected.id : ''">
    <div x-show="results.length && !selected" class="absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-64 overflow-auto">
      <template x-for="r in results" :key="r.itemtype + r.id">
        <button type="button" @click="pick(r)" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-100 last:border-0">
          <div class="text-sm font-medium" x-text="r.name"></div>
          <div class="text-xs text-slate-500"><span class="font-mono" x-text="r.serial"></span> · <span x-text="r.type_label"></span></div>
        </button>
      </template>
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Fotos <span class="text-slate-400 font-normal">(opcional, hasta 5)</span></label>
    <input type="file" name="photos[]" accept="image/*" capture="environment" multiple
           class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-light file:text-brand-dark file:font-medium">
  </div>

  <button class="w-full bg-brand hover:bg-brand-dark text-white font-bold rounded-lg py-3 transition">Enviar reporte</button>
</form>

<script>
function assetPicker() {
  return {
    q: '', results: [], selected: null,
    search() {
      if (this.q.trim().length < 2) { this.results = []; return; }
      fetch('<?= h(url('asset/search')) ?>&q=' + encodeURIComponent(this.q))
        .then(r => r.json()).then(d => this.results = d).catch(() => this.results = []);
    },
    pick(r) { this.selected = r; this.results = []; this.q = ''; },
    clearSel() { this.selected = null; }
  };
}
</script>
