# Tareas — MVP Soporte Lamarque v2

Orden demo-first. Cada tarea termina verificada contra DB real.

## T0 — Infra
- [ ] config.example.php + config.local.php (creds glpidb)
- [ ] src/db.php (PDO), src/bootstrap.php, src/helpers.php (h, csrf, mapas)
- [ ] src/auth.php (login bcrypt, sesión, role+entity desde profiles_users)
- [ ] src/repo/Users.php
- [ ] views/layout.php + login.php
- [ ] public/index.php router
- [ ] VERIFY: login real `guadalupe`/`dalia`/`alfonso` funciona, role+entity correctos

## T1 — Sucursal núcleo
- [ ] repo/Tickets.php (list scoped, get, create, counts)
- [ ] repo/Followups.php (thread, add, addPhoto)
- [ ] sucursal/home.php (resumen + botón reportar)
- [ ] sucursal/new.php + ticket/create (con activo opcional + foto)
- [ ] sucursal/thread.php + ticket/reply
- [ ] VERIFY: guadalupe crea ticket real con activo LMQ-GDL-REF-0005 + foto

## T2 — Dalia handoff
- [ ] repo/Tickets.php: list global + filtros, assign()
- [ ] dalia/tickets.php (tabla + filtros + CSV)
- [ ] dalia/assign (POST) → tickets_users type=2, status=2
- [ ] VERIFY: dalia ve ticket de T1 y lo asigna a alfonso

## T3 — Técnico cierre
- [ ] repo/Tickets.php: assignedTo(tecnico), close()
- [ ] tecnico/home.php (mis tareas) + manifest + sw.js
- [ ] tecnico/detail.php + tec/close (checklist hoja servicio)
- [ ] VERIFY: alfonso ve tarea, cierra con checklist+foto, status=6

## T4 — Sucursal activos + tickets
- [ ] repo/Assets.php (byEntityGrouped, search)
- [ ] sucursal/assets.php (categorías + conteo desglose + Drive link)
- [ ] sucursal/tickets.php (historial)
- [ ] VERIFY: guadalupe ve sus activos por categoría con conteos; aislamiento vs chapultepec

## T5 — Notificaciones
- [ ] src/mailer.php best-effort; enganchar en create/assign/close

## T6 — Dashboard Dalia
- [ ] dalia/dashboard.php (KPIs + Chart.js)

## T7 — Deploy + QA
- [ ] scp a /var/www/soporte-v2, Apache alias, chown
- [ ] Playwright: ejecutar 5 criterios de aceptación de spec.md
- [ ] Limpieza local + actualizar memorias/CLAUDE.md
