<?php /** @var string $period @var array $kpi */ ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Dashboard</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Resumen operativo de mantenimiento.</p>
  </div>
  <form method="get" class="flex items-center gap-2">
    <input type="hidden" name="r" value="dalia/dashboard">
    <label class="text-[13px] font-semibold text-muted">Periodo</label>
    <input type="month" name="period" value="<?= h($period) ?>" onchange="this.form.submit()" class="fld" style="width:auto">
  </form>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6 stagger">
  <div class="bg-surface rounded-card p-5">
    <div class="text-[11px] font-bold uppercase tracking-wide text-muted">Tickets del periodo</div>
    <div class="text-[30px] font-extrabold mt-1.5 leading-none text-ink"><?= (int)$kpi['total'] ?></div>
  </div>
  <div class="bg-surface rounded-card p-5">
    <div class="text-[11px] font-bold uppercase tracking-wide text-muted">Completados</div>
    <div class="text-[30px] font-extrabold mt-1.5 leading-none" style="color:var(--st-resuelto)"><?= (int)$kpi['completados'] ?> · <?= (int)$kpi['pct_completados'] ?>%</div>
  </div>
  <div class="bg-surface rounded-card p-5">
    <div class="text-[11px] font-bold uppercase tracking-wide text-muted">Pendientes</div>
    <div class="text-[30px] font-extrabold mt-1.5 leading-none" style="color:var(--st-curso)"><?= (int)$kpi['pendientes'] ?></div>
  </div>
  <div class="bg-surface rounded-card p-5">
    <div class="text-[11px] font-bold uppercase tracking-wide text-muted">Correctivo / Preventivo</div>
    <div class="text-[30px] font-extrabold mt-1.5 leading-none" style="color:var(--st-nuevo)"><?= (int)$kpi['correctivo'] ?> / <?= (int)$kpi['preventivo'] ?></div>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 stagger">
  <div class="bg-surface rounded-card p-5 lg:col-span-2">
    <div class="flex items-baseline justify-between mb-4"><h2 class="font-extrabold text-[15px] tracking-tight">Top sucursales por carga</h2><span class="text-[12px] text-faint font-semibold">tickets</span></div>
    <?php if (empty($kpi['top_sucursales'])): ?><p class="text-faint text-sm">Sin datos en el periodo.</p><?php else: ?><canvas id="chSuc" height="150"></canvas><?php endif; ?>
  </div>
  <div class="bg-surface rounded-card p-5">
    <div class="flex items-baseline justify-between mb-4"><h2 class="font-extrabold text-[15px] tracking-tight">Correctivo vs Preventivo</h2></div>
    <?php if (($kpi['correctivo'] + $kpi['preventivo']) === 0): ?><p class="text-faint text-sm">Sin datos en el periodo.</p><?php else: ?><canvas id="chCP" height="220"></canvas><?php endif; ?>
  </div>
  <div class="bg-surface rounded-card p-5 lg:col-span-3">
    <div class="flex items-baseline justify-between mb-4"><h2 class="font-extrabold text-[15px] tracking-tight">Top categorías recurrentes</h2></div>
    <?php if (empty($kpi['top_categorias'])): ?><p class="text-faint text-sm">Sin datos en el periodo.</p><?php else: ?><canvas id="chCat" height="110"></canvas><?php endif; ?>
  </div>
  <div class="bg-surface rounded-card p-5 lg:col-span-3">
    <div class="flex items-baseline justify-between mb-4"><h2 class="font-extrabold text-[15px] tracking-tight">Atenciones por proveedor</h2><span class="text-[12px] text-faint font-semibold">interno vs externos</span></div>
    <?php if (empty($kpi['top_proveedores'])): ?><p class="text-faint text-sm">Sin datos en el periodo.</p><?php else: ?><canvas id="chProv" height="110"></canvas><?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const BRAND = '#006970', INK = '#0f1c1d', GRID = '#eceeee', FAINT = '#90a0a0';
Chart.defaults.font.family = '"Plus Jakarta Sans", system-ui, sans-serif';
Chart.defaults.font.weight = '600'; Chart.defaults.color = FAINT;
function bar(id, labels, data, horizontal, colors) {
  const el = document.getElementById(id); if (!el) return;
  new Chart(el, { type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: colors || BRAND, borderRadius: 7, maxBarThickness: 38 }] },
    options: { indexAxis: horizontal ? 'y' : 'x',
      plugins: { legend: { display: false }, tooltip: { backgroundColor: INK, padding: 10, cornerRadius: 10, titleFont: { weight: '700' } } },
      scales: { x: { grid: { display: horizontal, color: GRID }, border: { display: false }, ticks: { font: { size: 12 } } },
                y: { grid: { display: !horizontal, color: GRID }, border: { display: false }, ticks: { font: { size: 12 } } } } } });
}
bar('chSuc',
  <?= json_encode(array_column($kpi['top_sucursales'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_sucursales'], 'c'))) ?>, true);
bar('chCat',
  <?= json_encode(array_column($kpi['top_categorias'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_categorias'], 'c'))) ?>, false);
bar('chProv',
  <?= json_encode(array_column($kpi['top_proveedores'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_proveedores'], 'c'))) ?>, false,
  <?= json_encode(array_map(fn($p) => $p['label'] === 'Interno' ? '#006970' : '#3b8aa6', $kpi['top_proveedores'])) ?>);
(function () {
  const el = document.getElementById('chCP'); if (!el) return;
  new Chart(el, { type: 'doughnut',
    data: { labels: ['Correctivo', 'Preventivo'], datasets: [{ data: [<?= (int)$kpi['correctivo'] ?>, <?= (int)$kpi['preventivo'] ?>], backgroundColor: ['#d83a34', '#006970'], borderWidth: 0, hoverOffset: 6 }] },
    options: { cutout: '62%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12, weight: '700' } } }, tooltip: { backgroundColor: INK, padding: 10, cornerRadius: 10 } } } });
})();
</script>
