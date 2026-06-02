# Schema real de glpidb (verificado 2026-06-02 vía SSH)

Ground truth. Toda query se ancla aquí. Conexión: `127.0.0.1:3306`, db `glpidb`, user `glpiuser` (creds en `/root/.glpi_credentials` del servidor).

## Autenticación — `glpi_users`
- `id`, `name` (login), `password` = **bcrypt `$2y$`, 60 chars → `password_verify($pass, $hash)`**
- `is_active` (filtrar =1), `firstname`, `realname`, `entities_id` (default, no fiable para permiso)
- Email real está en `glpi_useremails` (users_id, email, is_default).

## Permiso / entidad — `glpi_profiles_users`
- `users_id`, `profiles_id`, `entities_id`, `is_recursive`
- Perfiles relevantes (`glpi_profiles`):
  - **9 = Sucursal** (interface helpdesk) → 1 entidad, is_recursive=0
  - **10 = Tecnico** (central) → entidad 0, is_recursive=1 (ve todo)
  - **11 = Admin Mejora Continua** (Dalia, central) → entidad 0, is_recursive=1
  - **4 = Super-Admin** (ti.admin)
- Mapeo login → entidad (sucursales):
  | login | entity_id | sucursal |
  |---|---|---|
  | administracion | 1 | OFC |
  | coordinador.planta | 2 | CDS |
  | chapultepec | 3 | CHR |
  | chapultepecdrive | 4 | CHD |
  | quintas | 5 | QNT |
  | vallealto | 6 | VAL |
  | guadalupe | 7 | GDL |
  | pedroinfante | 8 | PDI |
  | privanzas | 9 | PRV |
  | explanada | 10 | EXP |
  | tresrios | 11 | TRS |
  | cuatrorios | 12 | CRS |
  | primavera | 13 | PRM |
  | conquistadrive | 14 | CQD |
  | conquista | 15 | CQP |
  | campusdigital | 16 | CAM |
- Técnicos: alfonso=25, noe=26, armando=27. Dalia=23. ti.admin=24.

## Entidades — `glpi_entities`
- id 0 = raíz "Entidad principal". id 1..16 = sucursales. Campos: `name`, `completename`, `tag` (OFC, GDL, ...).

## Tickets — `glpi_tickets`
- `id`, `entities_id`, `name` (título), `content` (longtext HTML), `date` (apertura), `closedate`, `solvedate`, `date_mod`
- `status` int: **1=Nuevo, 2=En curso(asignado), 3=En curso(planificado), 4=En espera, 5=Resuelto, 6=Cerrado**
- `urgency` 1..5 (1=muy alta), `impact`, `priority`, `type` (1=Incidencia, 2=Solicitud)
- `itilcategories_id`, `users_id_recipient` (quien lo creó)
- Estado actual: 999 cerrados, 7 asignados, 7 en espera. ~1009 históricos.

## Actores del ticket — `glpi_tickets_users`
- `tickets_id`, `users_id`, `type`: **1=Solicitante, 2=Asignado(técnico), 3=Observador**, `use_notification`, `alternative_email`

## Hilo / chat — `glpi_itilfollowups`
- Polimórfico: `itemtype`='Ticket', `items_id`=ticket id
- `users_id`, `content` (HTML), `date`, `is_private` (0=visible solicitante), `timeline_position`

## Activo ↔ ticket — `glpi_items_tickets`
- `itemtype` (clase GLPI del activo), `items_id`, `tickets_id`

## Activos — 8 tablas GenericObject + `glpi_computers`
Cada genericobject: `id, entities_id, name, serial (=Folio LMQ), otherserial, states_id, manufacturers_id, locations_id, is_deleted, comment (text — puede guardar link Drive), is_helpdesk_visible`.
Mapa tipo (`glpi_plugin_genericobject_types`):
| type | tabla | itemtype (clase) | código |
|---|---|---|---|
| refrigeracion | glpi_plugin_genericobject_refrigeracions | PluginGenericobjectRefrigeracion | REF |
| maquinariadecafe | glpi_plugin_genericobject_maquinariadecafes | PluginGenericobjectMaquinariadecafe | MCF |
| mobiliario | glpi_plugin_genericobject_mobiliarios | PluginGenericobjectMobiliario | MOB |
| electronicayseguridad | glpi_plugin_genericobject_electronicayseguridads | PluginGenericobjectElectronicayseguridad | ESG |
| jardineria | glpi_plugin_genericobject_jardinerias | PluginGenericobjectJardineria | JRD |
| televisione | glpi_plugin_genericobject_televisiones | PluginGenericobjectTelevisione | TV |
| utensilio | glpi_plugin_genericobject_utensilios | PluginGenericobjectUtensilio | UTL |
| herramienta_soporte | glpi_plugin_genericobject_herramientasoportes | PluginGenericobjectHerramientasoporte | HRS |
- Cómputo/POS: `glpi_computers` (mismos campos base; 307 activos, +2778 is_deleted=1 que NO se tocan).

## Categorías ITIL — `glpi_itilcategories`
Árbol (parent = `itilcategories_id`, 0=raíz). Raíces: 1 Mtto Preventivo, 2 Mtto Correctivo, 3 Soporte TI, 4 Infraestructura Local, 5 General/Otro. 26 subcategorías (ids 6-31).

## Estados de activo — `glpi_states`
1=Activo, 2=En Reparación, 3=Baja, 4=En Bodega, 5=Pendiente.

## Fotos / documentos — `glpi_documents` + `glpi_documents_items`
- `glpi_documents`: archivo subido (filepath, filename, mime). `glpi_documents_items`: polimórfico (`documents_id, items_id, itemtype, entities_id, timeline_position`).
- Para MVP la subida de foto puede guardarse en `/var/lib/glpi/files/...` siguiendo el patrón GLPI, o simplificarse a carpeta propia `uploads/` referenciada en el followup. Decisión en plan.md.
