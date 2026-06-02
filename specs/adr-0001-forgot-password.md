# ADR-0001 — Flujo seguro de "olvidé mi contraseña"

**Estado:** Aceptado · 2026-06-02

## Contexto
Las sucursales olvidan su contraseña y hoy dependen del admin. Necesitamos un auto-servicio como el que tenía GLPI, sin comprometer credenciales. La app v2 corre sobre `glpidb`; `glpi_users` ya tiene `password_forget_token char(40)` y `password_forget_token_date timestamp`.

## Decisión

1. **Identificador = correo `@lamarque.mx`.** El usuario ingresa su correo. Se busca en `glpi_useremails`. La respuesta es **siempre la misma** ("Si la cuenta existe, enviamos instrucciones") exista o no → no revela qué cuentas existen (anti-enumeración).

2. **Token: se guarda el hash, no el token.** Se genera `token = bin2hex(random_bytes(32))` (64 hex) que viaja **solo en el link del correo**. En DB se guarda `sha1($token)` (40 hex, cabe exacto en la columna). La búsqueda es por `password_forget_token = sha1(:t)` + `hash_equals`. Si la DB se filtra, el token usable nunca estuvo ahí. sha1 es aceptable aquí porque el token ya es aleatorio de alta entropía (no es una contraseña que adivinar).

3. **Expiración: 15 minutos.** `password_forget_token_date = NOW()` al solicitar; el reset solo procede si `TIMESTAMPDIFF(MINUTE, token_date, NOW()) <= 15`.

4. **Un solo uso.** Tras un reset exitoso se limpia `password_forget_token` y `password_forget_token_date` (NULL). Un segundo intento con el mismo link falla.

5. **Nueva contraseña.** `password_hash($pass, PASSWORD_DEFAULT)` → bcrypt `$2y$`, idéntico a lo que `login()` verifica. Mínimo 8 caracteres; se pide dos veces (confirmación).

6. **Correo.** Se envía con `send_mail()` (SMTP reutilizando credenciales de `glpi_configs`). En pruebas `smtp.redirect_to = sistemas@lamarque.mx` desvía TODOS los correos; el cuerpo indica el destinatario real. El link usa `url_base` de glpi_configs (`https://soporte.lamarque.mx`) + ruta de la app.

## Seguridad — no comprometer contraseñas
- La app password de Gmail vive **solo en `glpi_configs.smtp_passwd`**; el mailer la lee en runtime, nunca se escribe en archivos del repo ni en logs.
- El token usable nunca se persiste (solo su sha1).
- `hash_equals` evita timing attacks en la comparación.
- Respuesta fija evita enumeración de cuentas.
- HTTPS en todo el flujo (Let's Encrypt ya activo).

## Alternativas descartadas
- **Guardar token crudo (estilo GLPI nativo):** más simple e interoperable con GLPI, pero el token usable queda en DB dentro de la ventana → descartado por seguridad.
- **Ventana 24h (default GLPI):** más cómoda pero mayor superficie → 15 min es suficiente.

## Consecuencias
- Pendiente futuro: throttling por correo/IP para limitar abuso de solicitudes (no en MVP; mitigado por respuesta fija + expiración corta + single-use).
- El token crudo viaja en el query string del link → queda en logs de Apache (igual que GLPI). Mitigado por expiración 15 min + single-use. A futuro se podría pasar por POST/fragmento.
- **Verificado 2026-06-02:** 18 tests CLI verde (`tests/reset_test.php`), QA navegador OK, SMTP envía real (redirigido a sistemas@).
- El flujo no interopera con el "olvidé contraseña" nativo de GLPI (distinto formato de token), aceptable porque los usuarios usan la UI v2.
