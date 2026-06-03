<?php /** @var array $u @var array $counts @var array $recent */
$h = (int)date('G');
$saludo = $h < 12 ? 'Buenos días' : ($h < 19 ? 'Buenas tardes' : 'Buenas noches');
?>
<div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto stagger">

  <div class="mb-5">
    <p class="text-[13px] font-semibold text-muted"><?= $saludo ?></p>
    <h1 class="text-[26px] font-extrabold tracking-tight leading-tight"><?= h($u['entity_name']) ?></h1>
    <p class="text-muted text-[13.5px] mt-1">¿Algo no funciona? Repórtalo y le damos seguimiento.</p>
  </div>

  <a href="<?= h(url('ticket/new')) ?>" class="tap flex items-center gap-3 w-full bg-brand active:bg-brand-dark hover:bg-brand-dark text-white rounded-2xl p-4 mb-5 transition-colors">
    <span class="w-12 h-12 shrink-0 grid place-items-center rounded-xl bg-white/15">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
    </span>
    <span class="text-left leading-tight">
      <span class="block text-[17px] font-extrabold">Reportar un problema</span>
      <span class="block text-[12.5px] text-white/75">Crea un ticket en segundos</span>
    </span>
    <svg class="ml-auto" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
  </a>

  <div class="grid grid-cols-3 gap-2.5 mb-7">
    <a href="<?= h(url('tickets', ['status' => 1])) ?>" class="tap bg-surface rounded-card p-3.5 text-center">
      <div class="text-[30px] font-extrabold leading-none" style="color:var(--st-nuevo)"><?= (int)$counts['abiertos'] ?></div>
      <div class="text-[11px] font-bold text-muted mt-1.5">Abiertos</div>
    </a>
    <a href="<?= h(url('tickets', ['status' => 4])) ?>" class="tap bg-surface rounded-card p-3.5 text-center">
      <div class="text-[30px] font-extrabold leading-none" style="color:var(--st-espera)"><?= (int)$counts['espera'] ?></div>
      <div class="text-[11px] font-bold text-muted mt-1.5">En espera</div>
    </a>
    <a href="<?= h(url('tickets', ['status' => 6])) ?>" class="tap bg-surface rounded-card p-3.5 text-center">
      <div class="text-[30px] font-extrabold leading-none" style="color:var(--st-resuelto)"><?= (int)$counts['resueltos'] ?></div>
      <div class="text-[11px] font-bold text-muted mt-1.5">Resueltos</div>
    </a>
  </div>

  <div class="flex items-center justify-between mb-3">
    <h2 class="text-[13px] font-bold uppercase tracking-wide text-muted">Tickets recientes</h2>
    <a href="<?= h(url('tickets')) ?>" class="text-[12.5px] text-brand-dark font-bold">Ver todos</a>
  </div>

  <?php if (empty($recent)): ?>
    <div class="bg-surface rounded-card p-8 text-center text-muted font-semibold">Aún no hay tickets.</div>
  <?php else: ?>
    <div class="space-y-2.5 lg:grid lg:grid-cols-2 lg:gap-2.5 lg:space-y-0">
      <?php foreach ($recent as $t): ?>
        <a href="<?= h(url('ticket/view', ['id' => $t['id']])) ?>" class="tap block bg-surface rounded-card p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-bold text-[15px] text-ink leading-snug truncate"><?= h($t['name']) ?></div>
              <div class="text-[12.5px] text-muted mt-1 flex items-center gap-1.5">
                <span class="font-mono text-faint">#<?= (int)$t['id'] ?></span>
                <span class="w-1 h-1 rounded-full bg-faint/50"></span>
                <span class="truncate"><?= h($t['cat_name'] ?: 'General') ?></span>
              </div>
              <div class="text-[11.5px] text-faint mt-0.5"><?= fecha($t['date']) ?></div>
            </div>
            <?= status_chip((int)$t['status']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
