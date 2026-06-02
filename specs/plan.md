# Plan técnico — Soporte Lamarque v2

## Stack
- PHP 8.2 nativo + PDO MySQL. Sesiones nativas.
- Frontend: Tailwind CSS (CDN play), Alpine.js (CDN). Chart.js (CDN) solo en dashboard Dalia.
- Sin Composer. Correo: `mail()` nativo via sendmail, o PHPMailer vendored si SMTP auth requerido (decisión en deploy: probar `mail()`, fallback PHPMailer).
- Branding: turquesa `#006970`, Plus Jakarta Sans, grid 8px (design system "Lamarque Asset Portal").

## Estructura de archivos
```
soporte-v2/
  specs/                 constitution, schema, spec, plan, tasks (este folder)
  config.example.php     plantilla (en repo)
  config.local.php       creds reales (chmod 600, NO repo)
  public/                webroot (Apache apunta aquí)
    index.php            front controller / router (?r=ruta)
    sw.js                service worker técnico (cache shell)
    manifest.webmanifest PWA técnico
    assets/
      logo.png
      app.js             helpers Alpine compartidos
  src/
    bootstrap.php        carga config, db, auth, helpers; arranca sesión
    db.php               PDO singleton
    auth.php             login(), logout(), current_user(), require_role(), entity_scope()
    helpers.php          h() escape, status maps, urgency maps, entity name, csrf
    mailer.php           send_mail() best-effort
    repo/
      Users.php          findByLogin, profile+entity de un user, tecnicos()
      Tickets.php        list (scoped), get, create, assign, close, counts
      Followups.php      thread(ticket), add(ticket,user,text), addPhoto
      Assets.php         byEntityGrouped, search(scope,q), get, allTypes
    views/
      layout.php         shell HTML (head, tailwind, nav segun rol)
      partials/          status_chip, urgency_chip, ticket_card, asset_card
      login.php
      sucursal/ home.php new.php thread.php assets.php tickets.php
      tecnico/  home.php detail.php
      dalia/    dashboard.php tickets.php
  uploads/               fotos subidas (fuera de public o servidas controladas)
```

## Routing (`public/index.php`)
`?r=` simple switch. Ej: `login`, `logout`, `home`, `ticket/new`, `ticket/create`(POST), `ticket/view&id=`, `ticket/reply`(POST), `assets`, `tickets`, `tec/home`, `tec/detail&id=`, `tec/close`(POST), `dalia/dashboard`, `dalia/tickets`, `dalia/assign`(POST). Tras login redirige por rol: Sucursal→home, Técnico→tec/home, Dalia→dalia/dashboard.

## Auth y scope (Art. 2)
- `login($name,$pass)`: SELECT por name+is_active=1, `password_verify`. Guarda en sesión: user id, name, display, role (profile), entity_id, is_recursive.
- `entity_scope()`: si role=Sucursal → `WHERE entities_id = :eid`. Si Técnico/Dalia → sin filtro de entidad (ven todo). Centralizado en repo.
- CSRF token en todos los POST.

## Fotos (MVP simplificado)
- Subir a `soporte-v2/uploads/<ticketid>/<uuid>.<ext>`. Registrar referencia en el `content` del followup como `[[img:ruta]]` o insertar `<img>` (HTML permitido en followup). Servir vía `?r=img&path=` validando que pertenece al ticket y al scope. Evita la complejidad de `glpi_documents` para el MVP; fase 2 migra al esquema GLPI.

## Escrituras a glpidb (compatibles GLPI — Art. 4)
- Crear ticket: INSERT glpi_tickets (entities_id, name, content, status=1, urgency, type=1, itilcategories_id, date=NOW(), date_creation=NOW(), date_mod=NOW(), users_id_recipient). Luego INSERT glpi_tickets_users (type=1, users_id=solicitante). Opcional glpi_items_tickets.
- Asignar: INSERT/UPDATE glpi_tickets_users (type=2, users_id=tecnico); UPDATE glpi_tickets SET status=2, date_mod=NOW().
- Comentar: INSERT glpi_itilfollowups (itemtype='Ticket', items_id, users_id, content, date=NOW(), is_private=0, timeline_position=1|4).
- Cerrar: INSERT followup (hoja), UPDATE glpi_tickets SET status=6, solvedate=NOW(), closedate=NOW().
- Todas con PDO prepared statements.

## Deploy
- `rsync`/`scp` a `/var/www/soporte-v2/`. Apache: `Alias /soporte-v2 /var/www/soporte-v2/public` + `<Directory>` AllowOverride. `chown www-data`. config.local.php con creds de `/root/.glpi_credentials`. No tocar vhost GLPI.
- Probar con Playwright MCP contra `https://soporte.lamarque.mx/soporte-v2/`.

## Orden de construcción (minimiza riesgo, demo-first)
1. infra (bootstrap, db, auth, helpers, layout, login) — verificar login real.
2. Sucursal: home + nuevo ticket + hilo (núcleo del loop).
3. Dalia: consolidado + asignar (cierra el handoff).
4. Técnico: tareas + cerrar con hoja.
5. Sucursal: activos por categoría + mis tickets.
6. Notificaciones best-effort.
7. Dashboard KPIs Dalia.
8. Deploy + QA + aislamiento.
