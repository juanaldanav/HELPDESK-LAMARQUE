# Design Brief — Soporte Lamarque (rediseño de UI)

> **Prompt para IA de diseño (Claude / Stitch / v0).** Pégalo como instrucción y apunta la herramienta a este repo (`https://github.com/juanaldanav/HELPDESK-LAMARQUE`). Las vistas actuales están en `src/views/`. Quiero que las rediseñes manteniendo el stack y la lógica, mejorando solo la capa visual.

---

## Contexto
"Soporte Lamarque" es el portal de mantenimiento de una cadena de 16 cafeterías. Tres tipos de usuario: **Sucursal** (reporta fallas desde el celular del local), **Técnico** (atiende desde su celular, PWA), **Dalia/Admin** (escritorio: consolidado, métricas, inventario). El stack es PHP + **Tailwind CSS (CDN)** + **Alpine.js** + Chart.js, **sin build step**. El rediseño debe seguir usando Tailwind por clases utilitarias y Alpine para interacción; no introducir frameworks ni pasos de compilación.

## Objetivo
Subir el nivel visual a algo **moderno, limpio y fluido**, con identidad propia (no se vea "GLPI"). Reemplazar emojis por **iconos SVG** consistentes. Priorizar **mobile-first** en Sucursal y Técnico.

## Estilo visual — reglas duras (FLAT)
- **Flat, nada de skeuomorfismo.** PROHIBIDO: sombras (`box-shadow`/`drop-shadow`) detrás de tarjetas o divs, bordes en tarjetas, degradados pastel, colores pastel difusos.
- La jerarquía se logra con **espaciado, tipografía, peso y bloques de color sólido** — no con sombras ni bordes.
- Tarjetas = superficies planas diferenciadas por un fondo sólido sutilmente distinto del lienzo (p. ej. lienzo `#F7F8F8`, superficie `#FFFFFF` o un bloque de color), separadas por espacio, no por borde/sombra.
- Esquinas redondeadas moderadas y consistentes (un solo radio base, p. ej. 14–16px).
- Colores saturados y definidos, no lavados.

## Marca / tokens
- **Primary (turquesa):** `#006970`. Dark `#004F54`. Tints sólidos derivados (no pastel translúcido) para fondos de acento.
- **Tipografía:** Plus Jakarta Sans (400/500/600/700/800).
- **Grid:** base 8px.
- **Estados (ticket):** Nuevo, En curso, En espera, Resuelto, Cerrado — cada uno con un color sólido legible (no pastel). **Urgencia:** Muy alta→Muy baja.
- Entrega los tokens como un bloque de **CSS variables** + tabla en `.md`.

## Iconografía
- Sustituir TODOS los emojis (logo ☕, categorías, 🧾, 🖨️, 🔒, ✓) por **SVG inline** (set coherente, trazo uniforme tipo Lucide/Phosphor, mismo grosor). Entregar los SVG embebidos en el HTML, no como dependencia externa.
- Cada **categoría de activo** tiene su icono propio: Refrigeración, Maquinaria de Café, Mobiliario, Electrónica y Seguridad, Jardinería, Televisiones, Utensilios, Herramientas, Cómputo/POS.

## Componente estrella: tarjeta de categoría de activos (estilo GLPI mejorado)
En GLPI cada categoría era un cuadro con icono y la cantidad de activos. Replícalo mejor:
- **Cuadro plano por categoría** con: icono SVG grande, nombre de la categoría, y el **número de activos** (contador prominente).
- Cuadrícula responsiva (2 col en móvil, 3–4 en escritorio). Al tocar, filtra los activos de esa categoría.
- Estado activo/seleccionado claro (bloque de color primario), sin borde ni sombra.

## Pantallas a rediseñar (ver `src/views/`)
**Sucursal (móvil/responsive):** `login`, `forgot`, `reset`, `sucursal/home` (saludo + botón grande "Reportar" + 3 contadores + tickets recientes), `sucursal/new` (form + buscador de activo + foto), `sucursal/thread` (hilo tipo chat + estado), `sucursal/tickets` (lista), `sucursal/assets` (cuadros de categoría + grid de activos).
**Técnico (PWA móvil — PRIORIDAD):** `tecnico/home` (lista de tareas), `tecnico/detail` (detalle + hoja de servicio con checklist + foto + cerrar). Ver requisitos móviles abajo.
**Dalia (escritorio):** `dalia/dashboard` (KPIs + Chart.js), `dalia/tickets` (tabla + filtros), `dalia/view` (ticket + asignar), `dalia/assets` (inventario consolidado + baja).
**Compartido:** `layout` (nav superior), `partials/timeline`, `partials/linked_assets`, `print` (hoja imprimible).

## Requisitos móviles del Técnico (énfasis del cliente)
- **Consistencia y responsividad impecables** en celular: todo legible y operable con una mano.
- Targets táctiles ≥ 44px. Tipografía cómoda. Sin scroll horizontal.
- Lista de tareas con jerarquía clara (sucursal, asunto, urgencia, fecha) y chips de estado/urgencia legibles.
- Hoja de servicio: checklist con toggles grandes, subida de foto evidente, botón de "Cerrar" fijo/accesible.
- Sensación de **app nativa** (es PWA instalable): transiciones suaves, feedback al tocar.

## Animaciones / micro-interacciones
- **Fluidas y sobrias**, refuerzan la acción (no decorativas). Ej.: entrada escalonada de tarjetas, transición de estado de chips, ripple/scale sutil al tocar, deslizamiento del hilo, expandir/colapsar de la hoja de servicio.
- Respetar `prefers-reduced-motion`. Duraciones 150–250ms, easing natural. Implementables con Tailwind + Alpine `x-transition` / CSS, sin librerías pesadas.

## Accesibilidad
- Contraste AA mínimo. Foco visible. Etiquetas en formularios. Iconos con `aria-label` cuando comuniquen significado.

## Entregables (en este orden de preferencia)
1. **Mockups HTML autocontenidos** (un archivo por pantalla, o uno con todas), usando Tailwind CDN + Alpine, con los SVG embebidos y las animaciones funcionando — listos para copiar al proyecto.
2. **`design-system.md`**: tokens (CSS variables + tabla de color/tipografía/espaciado/radios), inventario de iconos SVG, y reglas de componentes (tarjeta de categoría, ticket card, chips, timeline, nav, botones).
3. Notas de mapeo: qué archivo de `src/views/` reemplaza cada mockup.

## Restricciones (resumen)
✅ Tailwind por clases · Alpine para interacción · SVG inline · flat · animaciones sutiles · mobile-first técnico/sucursal.
❌ Sombras detrás de divs · bordes en tarjetas · colores/degradados pastel · emojis · frameworks nuevos · build steps · cambiar la lógica PHP o las rutas (`?r=...`).
