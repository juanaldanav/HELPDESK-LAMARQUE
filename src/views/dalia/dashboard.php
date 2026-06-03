<?php /** @var string $period @var array $kpi */ ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Dashboard</h1>
  <form method="get" class="flex items-center gap-2">
    <input type="hidden" name="r" value="dalia/dashboard">
    <label class="text-sm text-slate-500">Periodo</label>
    <input type="month" name="period" value="<?= h($period) ?>" onchange="this.form.submit()"
           class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
  </form>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <?php
    $cards = [
      ['Tickets del periodo', $kpi['total'], 'text-slate-900', 'bg-white'],
      ['Completados', $kpi['completados'] . ' · ' . $kpi['pct_completados'] . '%', 'text-green-700', 'bg-green-50'],
      ['Pendientes', $kpi['pendientes'], 'text-amber-700', 'bg-amber-50'],
      ['Correctivo / Preventivo', $kpi['correctivo'] . ' / ' . $kpi['preventivo'], 'text-blue-700', 'bg-blue-50'],
    ];
    foreach ($cards as $c): ?>
    <div class="rounded-2xl border border-slate-200 <?= $c[3] ?> p-5">
      <div class="text-xs font-medium text-slate-500 uppercase tracking-wide"><?= h($c[0]) ?></div>
      <div class="text-2xl font-extrabold mt-1 <?= $c[2] ?>"><?= h((string)$c[1]) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-2">
    <h2 class="font-bold text-slate-800 mb-3">Top sucursales por carga</h2>
    <?php if (empty($kpi['top_sucursales'])): ?>
      <p class="text-slate-400 text-sm">Sin datos en el periodo.</p>
    <?php else: ?><canvas id="chSuc" height="120"></canvas><?php endif; ?>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5">
    <h2 class="font-bold text-slate-800 mb-3">Correctivo vs Preventivo</h2>
    <?php if (($kpi['correctivo'] + $kpi['preventivo']) === 0): ?>
      <p class="text-slate-400 text-sm">Sin datos en el periodo.</p>
    <?php else: ?><canvas id="chCP" height="200"></canvas><?php endif; ?>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-3">
    <h2 class="font-bold text-slate-800 mb-3">Top categorías recurrentes</h2>
    <?php if (empty($kpi['top_categorias'])): ?>
      <p class="text-slate-400 text-sm">Sin datos en el periodo.</p>
    <?php else: ?><canvas id="chCat" height="90"></canvas><?php endif; ?>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-3">
    <h2 class="font-bold text-slate-800 mb-3">Atenciones por proveedor <span class="text-sm font-normal text-slate-400">(interno vs externos)</span></h2>
    <?php if (empty($kpi['top_proveedores'])): ?>
      <p class="text-slate-400 text-sm">Sin datos en el periodo.</p>
    <?php else: ?><canvas id="chProv" height="90"></canvas><?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const BRAND = '#006970';
function bar(id, labels, data, horizontal) {
  const el = document.getElementById(id); if (!el) return;
  new Chart(el, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: BRAND, borderRadius: 6 }] },
    options: { indexAxis: horizontal ? 'y' : 'x', plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }
  });
}
bar('chSuc',
  <?= json_encode(array_column($kpi['top_sucursales'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_sucursales'], 'c'))) ?>, true);
bar('chCat',
  <?= json_encode(array_column($kpi['top_categorias'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_categorias'], 'c'))) ?>, false);
bar('chProv',
  <?= json_encode(array_column($kpi['top_proveedores'], 'label')) ?>,
  <?= json_encode(array_map('intval', array_column($kpi['top_proveedores'], 'c'))) ?>, false);
(function(){
  const el = document.getElementById('chCP'); if (!el) return;
  new Chart(el, { type: 'doughnut',
    data: { labels: ['Correctivo', 'Preventivo'],
      datasets: [{ data: [<?= (int)$kpi['correctivo'] ?>, <?= (int)$kpi['preventivo'] ?>], backgroundColor: ['#dc2626', BRAND] }] },
    options: { plugins: { legend: { position: 'bottom' } } } });
})();
</script>
