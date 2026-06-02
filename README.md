# Soporte Lamarque — Portal de tickets (v2)

Interfaz a la medida del sistema de mantenimiento de **Lamarque Repostería & Café** (16 sucursales). App PHP ligera sobre la base de datos de GLPI (`glpidb`), **sin** arrastrar el runtime de GLPI.

## Stack
PHP 8.2 + PDO · Tailwind CSS (CDN) · Alpine.js (CDN) · Chart.js. Sin build, sin Composer. Branding turquesa `#006970`, Plus Jakarta Sans.

## Roles
- **Sucursal** — crea/ve tickets de SU sucursal, ve sus activos por categoría. (móvil/responsive)
- **Técnico** — PWA móvil: tareas asignadas, hoja de servicio digital al cerrar.
- **Dalia / Admin** — consolidado de tickets, asignación, dashboard, inventario global, baja de activos.

## Estructura
```
public/        webroot (index.php = router ?r=, manifest, sw.js)
src/
  bootstrap.php, db.php, auth.php, mailer.php, helpers.php, app.php
  repo/        Users, Tickets, Followups, Assets, PasswordReset
  views/       login, layout, forgot, reset, print + sucursal/ tecnico/ dalia/ partials/
specs/         constitution, schema, spec, plan, tasks, ADRs (¡leer antes de programar!)
tests/         reset_test.php (CLI)
config.example.php   plantilla (copiar a config.local.php — NO versionado)
```

## Correr local
```bash
cp config.example.php config.local.php   # poner credenciales de glpidb
php -S localhost:8000 -t public           # base_url='' en config
```
En servidor: Apache `Alias /soporte-v2 -> public`.

## Seguridad
- `config.local.php` (credenciales DB) y `smoke.php` están en `.gitignore` — **no subir secretos**.
- Credenciales SMTP se leen de `glpi_configs` en runtime, nunca de archivos.
- Reseteo de contraseña: token sha1 en DB / crudo solo en link, 15 min, single-use, bcrypt. Ver `specs/adr-0001-forgot-password.md`.
- Aislamiento por sucursal aplicado en el servidor (cada query filtra por entidad).
