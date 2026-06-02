# Constitución — Soporte Lamarque v2

Principios NO negociables. Toda decisión técnica se valida contra esto. Si una tarea viola un artículo, se detiene y se reporta.

## Art. 1 — Reutilizar la robustez de datos de GLPI, no su runtime
La app v2 NO ejecuta el framework de GLPI. Se conecta directo a la base `glpidb` (MariaDB) por PDO. GLPI permanece instalado como fuente de verdad/respaldo, pero el día a día corre sobre la interfaz a la medida. Cero dependencias pesadas, cero build step.

## Art. 2 — Aislamiento absoluto por sucursal
Una sucursal NUNCA ve datos de otra. Toda query de tickets/activos para un usuario `Sucursal` filtra por `entities_id = <su entidad>`. El filtro se aplica en el servidor (repo), nunca confiando en el cliente. Dalia (Admin MC) y Técnico ven todas las entidades (recursivo desde raíz).

## Art. 3 — No alucinar el schema
Cada query usa nombres de tabla/columna verificados en `specs/schema.md`. Si un campo no está documentado ahí, se verifica contra la DB real antes de usarlo. No se inventan tablas ni columnas.

## Art. 4 — No romper GLPI ni los datos históricos
Despliegue en ruta aparte (`/soporte-v2`), Apache alias, sin tocar el vhost de GLPI. Las escrituras a `glpidb` usan las mismas convenciones que GLPI (status int, tipos de actor, timeline_position) para que ambos sistemas coexistan. Nada de `DROP`, `TRUNCATE` ni `DELETE` masivo. Los 2,778 computers `is_deleted=1` no se tocan.

## Art. 5 — Stack mínimo
PHP 8.2 nativo + PDO. Frontend: Tailwind CDN + Alpine.js CDN. Sin npm, sin webpack, sin Composer salvo PHPMailer (vendored si hace falta). Un archivo = una responsabilidad. El MVP entero debe caber en ~20 archivos.

## Art. 6 — Credenciales fuera del código
DB y SMTP en `config.local.php` (chmod 600, nunca en repo). El código lee de ahí. Nada de contraseñas hardcodeadas en vistas o repos.

## Art. 7 — Móvil primero en sucursal y técnico
La sucursal reporta desde el celular del local; el técnico trabaja desde su teléfono. Esas vistas son responsive/PWA. Dalia es escritorio.

## Art. 8 — Demo end-to-end por encima de features parciales
La prioridad del MVP es un ciclo completo demostrable: sucursal crea → Dalia asigna → técnico cierra → notificación. Antes de pulir cualquier pantalla, el loop debe funcionar con datos reales.
