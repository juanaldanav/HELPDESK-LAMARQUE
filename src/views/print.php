<?php /** @var array $t @var array $thread @var array $assets */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hoja de servicio · Ticket #<?= (int)$t['id'] ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @media print { .no-print { display: none !important; } @page { margin: 1.5cm; } }
  body { font-family: 'Segoe UI', system-ui, sans-serif; }
</style>
</head>
<body class="bg-white text-slate-800 p-8 max-w-3xl mx-auto">

  <div class="no-print mb-4 flex gap-2">
    <button onclick="window.print()" class="bg-[#006970] text-white font-semibold rounded-lg px-4 py-2">Imprimir / Guardar PDF</button>
    <a href="javascript:history.back()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600">Volver</a>
  </div>

  <div class="flex items-center justify-between border-b-2 border-[#006970] pb-3 mb-4">
    <div class="flex items-center gap-2">
      <span class="text-2xl">☕</span>
      <div>
        <div class="font-extrabold text-lg text-[#006970]">Soporte Lamarque</div>
        <div class="text-xs text-slate-500">Hoja de mantenimiento</div>
      </div>
    </div>
    <div class="text-right text-sm">
      <div class="font-bold">Ticket #<?= (int)$t['id'] ?></div>
      <div class="text-slate-500"><?= h($t['entity_name']) ?></div>
    </div>
  </div>

  <table class="w-full text-sm mb-4">
    <tbody>
      <tr><td class="py-1 pr-4 text-slate-500 align-top w-40">Asunto</td><td class="py-1 font-medium"><?= h($t['name']) ?></td></tr>
      <tr><td class="py-1 pr-4 text-slate-500 align-top">Categoría</td><td class="py-1"><?= h($t['cat_name'] ?? '—') ?></td></tr>
      <tr><td class="py-1 pr-4 text-slate-500 align-top">Estado</td><td class="py-1"><?= h(status_label((int)$t['status'])) ?></td></tr>
      <tr><td class="py-1 pr-4 text-slate-500 align-top">Técnico</td><td class="py-1"><?= h($t['tecnico_name'] ?: '—') ?></td></tr>
      <tr><td class="py-1 pr-4 text-slate-500 align-top">Apertura</td><td class="py-1"><?= fecha($t['date']) ?></td></tr>
      <?php if (!empty($t['closedate']) && $t['closedate'] !== '0000-00-00 00:00:00'): ?>
        <tr><td class="py-1 pr-4 text-slate-500 align-top">Cierre</td><td class="py-1"><?= fecha($t['closedate']) ?></td></tr>
      <?php endif; ?>
      <?php if (!empty($assets)): ?>
        <tr><td class="py-1 pr-4 text-slate-500 align-top">Equipo</td><td class="py-1">
          <?php foreach ($assets as $a): ?><?= h($a['name']) ?> (<span class="font-mono"><?= h($a['serial']) ?></span>)<br><?php endforeach; ?>
        </td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <h2 class="font-bold text-[#006970] border-b border-slate-200 pb-1 mb-2">Bitácora</h2>
  <div class="space-y-3 mb-8">
    <?php foreach ($thread as $m): ?>
      <div>
        <div class="text-xs text-slate-500"><strong><?= h($m['author']) ?></strong> · <?= fecha($m['date']) ?></div>
        <div class="text-sm whitespace-pre-line"><?= msg_html($m['content']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-2 gap-8 mt-12 pt-4 text-sm">
    <div class="border-t border-slate-400 pt-1 text-center text-slate-500">Firma del técnico</div>
    <div class="border-t border-slate-400 pt-1 text-center text-slate-500">Firma de la sucursal</div>
  </div>
</body>
</html>
