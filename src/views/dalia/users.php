<?php /** @var array $users @var array $sucursales @var array $me @var string $ok @var string $err */
$ROLE_OF = [9 => 'sucursal', 10 => 'tecnico', 11 => 'dalia'];
$ROLE_LBL = ['sucursal' => 'Sucursal', 'tecnico' => 'Técnico', 'dalia' => 'Mejora Continua'];
$ROLE_COL = ['sucursal' => '#0c83c4', 'tecnico' => '#b5610f', 'dalia' => '#006970'];
?>
<div x-data="{ openNew: false, newRole: 'sucursal', q: '' }">

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div>
    <h1 class="text-[27px] font-extrabold tracking-tight">Equipo</h1>
    <p class="text-muted text-[13.5px] mt-0.5">Roles, accesos, datos y contraseñas de los usuarios del portal.</p>
  </div>
  <button @click="openNew = true" class="tap inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white font-extrabold text-[13.5px] rounded-xl px-4 py-2.5 transition-colors">
    <?= svg_icon('plus', 16) ?> Nuevo usuario
  </button>
</div>

<!-- Buscador -->
<div class="relative mb-4 max-w-md">
  <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-faint pointer-events-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
  <input type="search" x-model="q" placeholder="Buscar por nombre, usuario o correo…" class="fld w-full" style="padding-left:42px">
</div>

<?php if ($ok): ?>
  <div class="mb-4 bg-brand-tint text-brand-dark font-semibold rounded-card px-4 py-3 text-[13.5px] flex items-center gap-2"><?= svg_icon('check', 18) ?> <?= h($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="mb-4 font-semibold rounded-card px-4 py-3 text-[13.5px]" style="color:#b3261e;background:#fdecea"><?= h($err) ?></div>
<?php endif; ?>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
  <?php foreach ($users as $usr):
      $role = $ROLE_OF[(int)$usr['profiles_id']] ?? 'sucursal';
      $isMe = (int)$usr['id'] === (int)$me['id'];
      $dn = trim(($usr['firstname'] ?? '') . ' ' . ($usr['realname'] ?? '')) ?: $usr['name'];
      $rc = $ROLE_COL[$role];
      $hay = mb_strtolower($dn . ' ' . $usr['name'] . ' ' . ($usr['email'] ?? '') . ' ' . $ROLE_LBL[$role]); ?>
    <div x-show="q==='' || <?= htmlspecialchars(json_encode($hay), ENT_QUOTES) ?>.includes(q.toLowerCase().trim())"
         class="bg-surface rounded-card p-4 <?= (int)$usr['is_active'] ? '' : 'opacity-60' ?>"
         x-data="{ role: '<?= $role ?>', pw: false, datos: false }">
      <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="w-10 h-10 shrink-0 grid place-items-center rounded-xl text-white font-extrabold text-[13px]" style="background:<?= $rc ?>"><?= h(mb_strtoupper(mb_substr($dn, 0, 1))) ?></span>
          <div class="leading-tight min-w-0">
            <div class="font-bold text-[14.5px] text-ink truncate"><?= h($dn) ?></div>
            <div class="text-[12px] text-muted truncate"><span class="font-mono"><?= h($usr['name']) ?></span><?= $usr['email'] ? ' · ' . h($usr['email']) : '' ?></div>
          </div>
        </div>
        <span class="shrink-0 text-[10.5px] font-bold text-white px-2 py-0.5 rounded-full" style="background:<?= $rc ?>"><?= $ROLE_LBL[$role] ?></span>
      </div>

      <?php if ($isMe): ?>
        <p class="text-[12px] text-faint font-semibold">Tu propia cuenta — cámbiala en “Mi cuenta”.</p>
      <?php else: ?>
        <form method="post" action="<?= h(url('dalia/user/save')) ?>" class="space-y-2.5">
          <?= csrf_field() ?>
          <input type="hidden" name="uid" value="<?= (int)$usr['id'] ?>">
          <div class="grid grid-cols-2 gap-2">
            <select name="role" x-model="role" class="fld" style="padding:9px 12px;font-size:13px">
              <option value="sucursal">Sucursal</option>
              <option value="tecnico">Técnico</option>
              <option value="dalia">Mejora Continua</option>
            </select>
            <select name="entity" class="fld" style="padding:9px 12px;font-size:13px" x-show="role === 'sucursal'" x-cloak>
              <?php foreach ($sucursales as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= (int)$usr['entities_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Datos editables (corrección de nombre/correo) -->
          <div x-show="datos" x-cloak class="space-y-2 pt-1">
            <div class="grid grid-cols-2 gap-2">
              <input name="firstname" value="<?= h($usr['firstname'] ?? '') ?>" placeholder="Nombre" class="fld" style="padding:9px 12px;font-size:13px">
              <input name="realname" value="<?= h($usr['realname'] ?? '') ?>" placeholder="Apellido" class="fld" style="padding:9px 12px;font-size:13px">
            </div>
            <input type="email" name="email" value="<?= h($usr['email'] ?? '') ?>" placeholder="correo@lamarque.mx" class="fld w-full" style="padding:9px 12px;font-size:13px">
          </div>
          <input x-show="!datos" type="hidden" name="firstname" value="<?= h($usr['firstname'] ?? '') ?>">
          <input x-show="!datos" type="hidden" name="realname" value="<?= h($usr['realname'] ?? '') ?>">
          <input x-show="!datos" type="hidden" name="email" value="<?= h($usr['email'] ?? '') ?>">

          <div class="flex items-center justify-between gap-2">
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" name="active" value="1" <?= (int)$usr['is_active'] ? 'checked' : '' ?> class="peer sr-only"><span class="sw" style="width:38px;height:23px"></span>
              <span class="text-[12.5px] font-semibold text-muted">Activo</span>
            </label>
            <div class="flex gap-1.5">
              <button type="button" @click="datos = !datos" class="tap text-[12px] font-bold px-2.5 py-2 rounded-lg bg-canvas text-muted">Datos</button>
              <button type="button" @click="pw = !pw" class="tap text-[12px] font-bold px-2.5 py-2 rounded-lg bg-canvas text-muted">Clave</button>
              <button class="tap text-[12px] font-bold px-3 py-2 rounded-lg bg-brand text-white">Guardar</button>
            </div>
          </div>
        </form>
        <form method="post" action="<?= h(url('dalia/user/password')) ?>" x-show="pw" x-cloak x-transition class="flex gap-2 mt-2.5">
          <?= csrf_field() ?>
          <input type="hidden" name="uid" value="<?= (int)$usr['id'] ?>">
          <input type="password" name="pass" minlength="8" required placeholder="Nueva contraseña (mín. 8)" class="fld flex-1" style="padding:9px 12px;font-size:13px">
          <button class="tap text-[12px] font-bold px-3 py-2 rounded-lg text-white" style="background:#b5610f">Restablecer</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal nuevo usuario -->
<div x-show="openNew" x-cloak class="fixed inset-0 z-30 grid place-items-center p-4" style="background:rgba(15,28,29,.5)" @click.self="openNew = false" @keydown.escape.window="openNew = false">
  <form method="post" action="<?= h(url('dalia/user/create')) ?>" class="bg-surface rounded-card w-full max-w-md overflow-hidden" x-transition>
    <?= csrf_field() ?>
    <div class="bg-brand text-white px-5 py-3.5 flex items-center justify-between">
      <h3 class="font-extrabold text-[15px]">Nuevo usuario</h3>
      <button type="button" @click="openNew = false" class="tap w-8 h-8 grid place-items-center rounded-lg bg-white/15"><?= svg_icon('close', 16) ?></button>
    </div>
    <div class="p-5 space-y-3.5">
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Login *</label>
          <input name="login" required pattern="[A-Za-z0-9._-]{3,40}" placeholder="ej. lasalle" class="fld w-full"></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Correo</label>
          <input type="email" name="email" placeholder="usuario@lamarque.mx" class="fld w-full"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Nombre</label>
          <input name="firstname" class="fld w-full"></div>
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Apellido</label>
          <input name="realname" class="fld w-full"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Rol *</label>
          <select name="role" x-model="newRole" class="fld w-full">
            <option value="sucursal">Sucursal</option>
            <option value="tecnico">Técnico</option>
            <option value="dalia">Mejora Continua</option>
          </select></div>
        <div x-show="newRole === 'sucursal'"><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Sucursal *</label>
          <select name="entity" class="fld w-full">
            <?php foreach ($sucursales as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <div><label class="block text-[11px] font-bold uppercase tracking-wide text-muted mb-1.5">Contraseña inicial * <span class="text-faint normal-case">(mín. 8)</span></label>
        <input type="password" name="pass" required minlength="8" class="fld w-full"></div>
      <button class="tap w-full bg-brand hover:bg-brand-dark text-white font-extrabold text-[14px] rounded-xl py-3 transition-colors">Crear usuario</button>
    </div>
  </form>
</div>

</div>
