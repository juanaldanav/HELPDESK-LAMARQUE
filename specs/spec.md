# Spec funcional — Soporte Lamarque v2 (MVP)

**Objetivo:** Interfaz a la medida del sistema de tickets de Lamarque, sobre `glpidb`. Reemplaza la UI corporativa de GLPI por una experiencia minimalista, móvil-primero, centrada en el ciclo de mantenimiento de las 16 sucursales.

**Demo objetivo (mañana):** ciclo completo con datos reales — sucursal crea ticket → Dalia asigna técnico → técnico cierra con hoja de servicio → estado visible para todos.

---

## Roles (derivados de `profiles_users`, ver schema.md)
- **Sucursal** (profile 9): ve y opera SOLO su entidad.
- **Técnico** (profile 10): ve tickets asignados a él (todas las entidades).
- **Dalia / Admin MC** (profile 11): ve todo, asigna, mide.
- **ti.admin** (profile 4): entra como Dalia (superset) en el MVP.

---

## RF-S — Sucursal (móvil/responsive)

- **RF-S1 Login.** `name` + password contra `glpi_users` (bcrypt). Sesión PHP. Logo Lamarque, pantalla limpia.
- **RF-S1b Olvidé contraseña.** Auto-servicio por correo @lamarque.mx → link de reset (token sha1, 15 min, single-use) → nueva contraseña bcrypt. Ver `adr-0001-forgot-password.md`. Rutas `?r=forgot` y `?r=reset&t=`.
- **RF-S2 Home.** Saludo con nombre de sucursal. Botón grande "Reportar un problema". Chips resumen: # abiertos, # en espera, # resueltos (de SU entidad). Acceso a "Mis activos" y "Mis tickets".
- **RF-S3 Nuevo ticket.** Título, descripción, categoría (árbol ITIL en select agrupado), urgencia, activo vinculado opcional (buscador por folio LMQ/nombre, filtrado a su entidad), adjuntar foto. Al enviar: crea `glpi_tickets` (status=1, entities_id=su entidad, users_id_recipient), `glpi_tickets_users` (type=1 solicitante), opcional `glpi_items_tickets`. Confirma "Tu reporte fue recibido".
- **RF-S4 Hilo del ticket (chat).** Timeline cronológico: descripción inicial + cada followup (autor, fecha, texto, fotos). Chip de estado sticky. Sucursal puede agregar comentario (`itilfollowups`, is_private=0). No edita el original.
- **RF-S5 Mis activos.** Activos de SU entidad agrupados por categoría con **conteo por categoría (desglose)**. Filtro por categoría (chips), buscador por folio/nombre. Tarjeta: nombre, folio LMQ, estado (chip color). Si `comment` tiene URL Drive → botón "Ver referencia".
- **RF-S6 Mis tickets.** Lista de tickets de su entidad: título, categoría, estado chip, fecha, técnico asignado. Filtro por estado.

## RF-T — Técnico (PWA móvil)

- **RF-T1 Mis tareas.** Tickets donde es asignado (`tickets_users.type=2`), ordenados por urgencia + fecha. Ítem: sucursal, título, chip urgencia, fecha.
- **RF-T2 Detalle.** Sucursal, categoría, descripción, activo vinculado (folio + link Drive si existe), hilo. Botones: comentar, adjuntar foto.
- **RF-T3 Cerrar + hoja de servicio.** Checklist (limpieza, cableado, tornillería, lubricación, actualización, entregado funcionando), observaciones, foto evidencia (≥1). Al cerrar: followup con la hoja, `solvedate`/`closedate`, status=5/6. Notifica a sucursal y Dalia. Genera hoja imprimible (HTML print → PDF).

## RF-D — Dalia / Admin MC (escritorio)

- **RF-D1 Dashboard.** KPIs del periodo: total tickets, % completados (cerrado+resuelto), pendientes reales (en espera/asignados), ratio correctivo vs preventivo, top 5 sucursales por carga, top 5 categorías. Filtro por mes.
- **RF-D2 Consolidado tickets.** Tabla todas las sucursales: No., sucursal, categoría, título, urgencia, técnico, estado, fecha apertura/cierre. Filtros: sucursal, categoría, técnico, estado, rango fechas. Exportar CSV. Click → hilo.
- **RF-D3 Asignar técnico.** Desde un ticket: elegir técnico (Alfonso/Noe/Armando) + urgencia. Guardar → escribe `tickets_users` (type=2), status→2, notifica al técnico por correo.
- **RF-D4 Activos globales.** (si da tiempo) inventario filtrable por sucursal/categoría/búsqueda.

## RF-N — Notificaciones
- **RF-N1** Crear ticket → acuse al solicitante.
- **RF-N2** Asignar → correo al técnico con detalle.
- **RF-N3** Cierre → correo al solicitante.
- Vía SMTP existente (`smtp.gmail.com:587`, `noreply@lamarque.mx`). Si SMTP falla, no bloquea la operación (best-effort, log).

---

## Fuera de alcance del MVP (fase 2)
- Calendario preventivo (D5), service worker offline real, dashboard drag-drop, gestión de consumibles por lote, baja de activos desde la UI, edición de catálogos.

## Criterios de aceptación de la demo
1. `guadalupe` entra, ve solo GDL, crea ticket con activo `LMQ-GDL-REF-0005` + foto.
2. `dalia` ve ese ticket en el consolidado, lo asigna a `alfonso`.
3. `alfonso` ve la tarea, la cierra con checklist + foto.
4. `guadalupe` ve el ticket cerrado con la hoja en su hilo.
5. `chapultepec` NO ve nada de GDL (aislamiento verificado).
