# Arquitectura de contenido — Tema JLB

Cómo se administra el contenido del sitio Jean Le Boulch desde wp-admin.
Cubre la modularización del home (2026-05-19); las páginas internas, blog,
Open Day + HubSpot y el CPT Talleres (2026-05-24); y el CPT Niveles + la
página Nosotros con la línea de tiempo (2026-05-25).

> Para el detalle de cada página construida (ids, módulos, seeders, gotchas)
> ver la sección **[Páginas internas y plantillas](#páginas-internas-y-plantillas-2026-05-24)**.

## Flujo de datos

```
                  ┌────────────────────────────┐
                  │  wp-admin → Páginas → Inicio   │
                  │  Componentes de Página         │
                  │  (ACF Flexible Content)        │
                  └─────────────┬──────────────┘
                                │ get_field('modules')
                                ▼
                ┌────────────────────────────────┐
                │  front-page.php                │
                │  the_modules_loop()            │
                └─────────────┬──────────────────┘
                              │ por cada layout
                              ▼
            ┌──────────────────────────────────────┐
            │  modules/jlb-<slug>/jlb-<slug>.php   │
            │  lee get_sub_field() (o $args        │
            │  como fallback estático)             │
            └──────────────────────────────────────┘

       ┌─────────────────────────┐        ┌──────────────────────────┐
       │  wp-admin → Apariencia  │        │  wp-admin → Ajustes      │
       │  → Menús                │        │  del sitio (ACF Options) │
       └─────────────┬───────────┘        └─────────────┬────────────┘
                     ▼                                  ▼
       ┌────────────────────────┐         ┌──────────────────────────┐
       │  header-jlb.php        │         │  footer-jlb.php          │
       │  wp_get_nav_menu_items │         │  get_field(..., 'option')│
       │  ('menu_principal')    │         │  + helpers jlb_footer_*  │
       └────────────────────────┘         └──────────────────────────┘
```

## Tres lugares para administrar

### 1. Contenido del home (módulos por bloques)

**Dónde:** wp-admin → Páginas → "Inicio" → sección "Componentes de Página".

**Cómo funciona:**
- ACF Flexible Content (`name="modules"`) registrado programáticamente en
  `inc/acf-modules.php` con 6 layouts JLB (`jlb_hero`, `jlb_niveles`,
  `jlb_manifesto`, `jlb_experience`, `jlb_testimonio_padres`, `jlb_noticias`)
  más los 13 layouts del boilerplate original.
- `front-page.php` itera con `the_modules_loop()` si hay ACF poblado;
  cae al fallback estático con `get_template_part(..., $args)` si no.
- Cada `modules/jlb-<slug>/jlb-<slug>.php` lee con `get_sub_field()` cuando
  está en flexible loop, o desde `$args` cuando se invoca como template-part.
  Patrón dual ya implementado.

**Para agregar un módulo nuevo:** usa `/nuevo-modulo <slug> "<descripción>"`.

**No usar `acf-json/`** — el filtro `acf/settings/load_json` en
`inc/acf-modules.php:18` ignora exports JSON por diseño. Razón: histórico
de pérdida de datos cuando se mezclaba UI + PHP (ver el comentario
load-bearing en `inc/acf-modules.php:36`).

### 2. Menú de navegación

**Dónde:** wp-admin → Apariencia → Menús → "Menú principal JLB".

**Cómo funciona:**
- `inc/menus.php` registra 4 locations: `menu_principal`, `footer`, `redes`,
  `menu_secundario`.
- `header-jlb.php` lee con `wp_get_nav_menu_items()` desde
  `menu_principal`. Si no hay menú asignado, cae a un set estático del
  Figma (8 items con submenú "Niveles").
- Soporta submenús (un nivel) gracias a la jerarquía de
  `menu_item_parent`.

**Curado en vivo (importante):** los enlaces de los submenús "Niveles"
(→ `/niveles/<slug>/`) y "Talleres" (→ `/talleres/<slug>/`), y el item
"Nuestro colegio" (→ `/nosotros/`), se editaron directamente sobre el menú WP
(id 2) vía WP-CLI, **no** solo desde `bin/seed-jlb.php`. Si re-siembras el menú,
reflejá estos `_menu_item_url` o quedarán apuntando a placeholders (`#inicial`,
`#colegio`, …).

### 3. Footer + identidad

**Dónde:** wp-admin → Ajustes del sitio (ítem en sidebar, ícono customizer).

**Cómo funciona:**
- ACF Options Page (`acf_add_options_page` en `inc/footer-options.php`).
- Field group `group_jlb_site_settings` con 3 tabs:
  - **Identidad**: logo header, logo footer.
  - **Footer**: dirección, teléfonos (repeater con WhatsApp), redes
    (repeater), email.
  - **Barra inferior**: copy con `{year}` placeholder, legal links
    (repeater).
- `footer-jlb.php` lee con `get_field(..., 'option')` y los helpers
  `jlb_footer_get()` / `jlb_footer_copy()` (resuelve `{year}`).
- Fallback hardcodeado en cada campo del footer para que el sitio se vea
  bien aún sin poblar las opciones.

## Páginas internas y plantillas (2026-05-24 / 05-25)

Cada página se compone de **módulos ACF Flexible Content** (igual que el home) o
de un **CPT con plantilla fija** (Blog, Talleres, Niveles). Todas reutilizan el
header/footer JLB (`get_header('jlb')` / `get_footer('jlb')`).

| Página / formato | Tipo | Admin | Plantilla | Seeder | Módulos / secciones |
|---|---|---|---|---|---|
| **Admisión** (id 10) | Página (flex `modules`) | Páginas → Admisión → Componentes de Página | `page.php` | `bin/seed-admision.php` | `jlb_admision_hero`, `jlb_proceso`, `jlb_cuota`, `jlb_galeria`, `jlb_faq` |
| **Experiencias innovadoras** (id 112) | Página (flex) | Páginas → Experiencias → Componentes | `page.php` | `bin/seed-experiencias.php` | `jlb_admision_hero` (reuso) + `jlb_experiencias` |
| **Open Day** (id 12) | Página (flex) | Páginas → Open Day → Componentes | `page.php` | `bin/seed-openday.php` | `jlb_admision_hero` (eyebrow+video+logo) + `jlb_open_day_form` |
| **Nosotros** (id 176) | Página (flex) | Páginas → Nosotros → Componentes | `page.php` | `bin/seed-nosotros.php` | `jlb_admision_hero` (reuso) + `jlb_manifesto` (reuso) + `jlb_timeline` |
| **Blog** (listado, page_for_posts id 113) | Índice de entradas | Entradas (Posts) + Ajustes → Lectura | `home.php` | `bin/seed-blog.php` (8 posts) | `jlb-page-hero` + `jlb-post-card` |
| **Artículo** | Single de entrada | Entradas → editar | `single.php` | (mismo seeder) | tag, título, fecha, imagen, autor, contenido, relacionados |
| **Talleres** (CPT `taller`, `/talleres/<slug>/`) | CPT + single | Talleres (menú propio) | `single-taller.php` → `jlb-formato-detalle` | `bin/seed-talleres.php` (5) | hero (reuso) + plan (flat) + video + objetivos + galería (reuso) + testimonial (reuso `jlb_testimoniales`) |
| **Niveles** (CPT `nivel`, `/niveles/<slug>/`) | CPT + single | Niveles (menú propio) | `single-nivel.php` → `jlb-formato-detalle` | `bin/seed-niveles.php` (4) | hero (reuso) + plan (por grupos) + objetivos + galería + imagen full + testimonial — **sin video** |

### Módulos JLB nuevos de esta sesión

Registrados en `inc/acf-modules.php` (mismo patrón snake/kebab + dual-mode):
`jlb_admision_hero`, `jlb_proceso`, `jlb_cuota`, `jlb_galeria`, `jlb_faq`,
`jlb_experiencias`, `jlb_open_day_form`, `jlb_timeline`. Los CPT `taller` y
`nivel` tienen su propio field group en `inc/cpt-taller.php` / `inc/cpt-nivel.php`
(no son flexible content). Total: **15 layouts JLB** en el flexible field.

**Timeline (`jlb_timeline`):** línea de tiempo vertical (eje + año en KG con
gradiente + punto + tarjeta título/texto/imagen opcional), GSAP fade-up.
Repeater `hitos {anio, titulo, texto, imagen}`. SCSS `_jlb-timeline.scss`. Lo
usa la página Nosotros para la historia 1983–2025 del colegio.

### Formato de detalle compartido (Talleres + Niveles)

`single-taller.php` y `single-nivel.php` son **wrappers delgados** (`get_header →
while → get_template_part('template-parts/jlb-formato-detalle') → get_footer`).
El partial `template-parts/jlb-formato-detalle.php` renderiza el formato completo
y soporta las dos variantes de datos:

- **Plan flat** (talleres, campo `plan`) vs **plan por grupos** (niveles, repeater
  `plan_grupos` de etiqueta + cursos → bandas "Áreas Curriculares", "Talleres").
- **Video opcional** (talleres sí, niveles no).
- **Galería** + **imagen full-width** opcional (`gal_full`, solo niveles).
- Reúsa hero (`jlb_admision_hero`), galería (`jlb_galeria`) y testimonial
  (`jlb_testimoniales`). Estilos compartidos en `_jlb-taller.scss` (prefijo
  `.jlb-taller-*`; + `.jlb-taller-plan__grupos` y `.jlb-taller-galfull`).

**Hero reutilizable (`jlb_admision_hero`):** además de título/subtítulo/imagen/
botones, soporta `eyebrow`, `video_url` + `video_caption` (play sobre la imagen →
video-lightbox) y `titulo_imagen` (logo PNG en vez del título de texto). Lo usan
Admisión, Experiencias y Open Day vía el flex loop, y Talleres vía
`get_template_part('modules/jlb-admision-hero/jlb-admision-hero', null, $args)`.

### Open Day → HubSpot

El módulo `jlb_open_day_form` envía a HubSpot vía un **endpoint REST proxy**
(`/wp-json/jlb/v1/open-day`, ver `inc/hubspot.php`). Configuración:

- **Administrable:** wp-admin → **Ajustes del sitio → Integraciones — Open Day /
  HubSpot** (Portal ID, Form GUID, reCAPTCHA site key — no secretos).
- **Secretos (solo wp-config):** `JLB_HUBSPOT_TOKEN`, `JLB_RECAPTCHA_SECRET`.
- La constante en wp-config tiene prioridad sobre el panel.
- Detalle completo en **`docs/hubspot-open-day.md`**.

### Blog — autor del artículo

El locale corre en `en_US`, así que las fechas se formatean en español con los
helpers `jlb_fecha_larga()` / `jlb_mes_abbr_es()` (`lib/helpers.php`). El autor
del artículo sale de meta `_jlb_autor` / `_jlb_autor_rol` (con fallback a
`get_the_author()`), porque los posts sembrados por WP-CLI quedan con
`post_author = 0`.

### Gotchas aprendidos (clave para no romper estilos)

1. **Estilos JLB SIEMPRE en `@layer components`.** Todo `@import` de un parcial
   JLB en `styles/sass/style.scss` debe ir dentro del bloque `@layer components`,
   **nunca** en `@layer legacy`. El reset de Tailwind (`@layer base`:
   `h1{font-size:inherit}`, `*{border-width:0}`) le gana a `legacy` por orden de
   capas → si pones un parcial en legacy, los títulos colapsan a 16px y los
   bordes desaparecen.
2. **Utilidad `.sr-text` / `.sr-only`** ahora es global (en `_jlb-tokens.scss`,
   scope `.jlb-home-template`). Antes solo existía en el footer, por lo que
   cualquier texto sr-only fuera del footer se renderizaba visible.
3. **Scope por página:** `header-jlb.php` añade `jlb-page-<slug>` al body. Úsalo
   para personalizar módulos compartidos sin acoplarlos (p.ej. color del botón
   del hero por página).
4. **Screenshots de nodos Figma vienen sobre fondo BLANCO** (opaco). Para
   gráficos transparentes (logos, garabatos decorativos) usa el **asset raw**
   (transparente) o un SVG, no el screenshot del nodo.
5. **Plays "horneados":** `assets/figma-home/experiencias/exp-1/2/3.jpg` traen un
   play dibujado. No usarlos como hero/poster donde no quieras play (o se duplica
   con el play.svg).
6. **`figure { margin-bottom }` global sin capa** vence a reglas layered → si
   necesitas pegar dos figures usa `margin: 0 !important` dentro de la capa.



Script WP-CLI **idempotente** que crea todo desde cero:

```bash
# Desde Local → "Open site shell":
cd app/public/wp-content/themes/jlb
wp eval-file bin/seed-jlb.php
```

Qué hace:
1. Sube las 13 imágenes de `assets/figma-home/` a Media Library (sin
   duplicar — busca por meta `_jlb_seed_source`).
2. Crea (o actualiza) la página "Inicio" y la asigna como front-page
   (`show_on_front=page` + `page_on_front=<ID>`).
3. Pobla los 6 módulos ACF con el contenido del Figma.
4. Crea (o resetea) el menú "Menú principal JLB" con 8 items + submenú
   Niveles, y lo asigna a `menu_principal`.
5. Llena los campos de la Options Page del footer (dirección, teléfonos,
   redes, email, copy, legal links) + ambos logos.

**Idempotente** — corre cuantas veces quieras. Cada paso valida si ya existe
antes de crear.

## Patch wp-admin (collapse + drag ACF)

Por bug en ACF Pro 6.8.1 + WP 6.9.4 (wrapper `<strong>` inesperado en cada
layout), hay un patch defensivo en `assets/admin/acf-collapse-patch.{js,css}`
enqueueado por `inc/acf-admin-patches.php`. Detalle completo en
`docs/acf-pro-strong-wrapper-bug.md`.

## Workflow para nuevos requerimientos

Slash command `/requerimiento <descripción>` orquesta los 5 agentes en
pipeline:

```
ui-senior → frontend-lead → qa-visual + seo-manager + wp-security (paralelo)
                          → consolidación → veredicto APTO/APTO CON CAMBIOS/NO APTO
```

Ver `.claude/commands/requerimiento.md`.

## Archivos clave

```
inc/
  acf-modules.php           — Flexible Content + filtros source-of-truth
  footer-options.php        — Options Page + field group footer + helpers
  acf-admin-patches.php     — enqueue del patch para ACF Pro
  hubspot.php               — endpoint REST jlb/v1/open-day → HubSpot + panel ACF
  cpt-taller.php            — CPT `taller` + field group del formato
  cpt-nivel.php             — CPT `nivel` + field group (plan por grupos + gal_full)

modules/jlb-*/jlb-*.php     — 15 módulos JLB. Home: hero, niveles, manifesto,
                              experience, testimonio_padres, testimoniales,
                              noticias. Internas: jlb-admision-hero (reusable),
                              jlb-proceso, jlb-cuota, jlb-galeria, jlb-faq,
                              jlb-experiencias, jlb-open-day-form, jlb-timeline

lib/helpers.php             — the_modules_loop() + jlb_fecha_larga/_mes_abbr_es

assets/admin/
  acf-collapse-patch.js     — patch JS (3 capas)
  acf-collapse-patch.css    — patch CSS (oculta .acf-fields al colapsar)

front-page.php              — home: the_modules_loop() + fallback estático
page.php                    — páginas internas (flex modules)
home.php                    — índice del blog (listado de entradas)
single.php                  — artículo del blog
single-taller.php           — wrapper del CPT taller → jlb-formato-detalle
single-nivel.php            — wrapper del CPT nivel  → jlb-formato-detalle
header-jlb.php              — nav menu reader + body class jlb-page-<slug>
footer-jlb.php              — options reader

template-parts/
  jlb-formato-detalle.php    — formato compartido talleres/niveles (plan flat o
                               por grupos, video opcional, galería + full opcional)
template-parts/molecules/
  jlb-page-hero.php          — banner gradiente reutilizable (blog)
  jlb-post-card.php          — tarjeta de entrada del blog

src/
  jlbOpenDayForm.js          — validación + envío del form Open Day
  jlbImageLightbox.js        — zoom de imagen (data-jlb-zoom)
  jlbVideoLightbox.js        — lightbox de video (data-jlb-video)

bin/
  seed-jlb.php               — seeder home (idempotente)
  seed-admision.php          — página Admisión
  seed-experiencias.php      — página Experiencias
  seed-openday.php           — página Open Day
  seed-blog.php              — Blog (página + 8 posts)
  seed-talleres.php          — CPT Talleres (5 talleres)
  seed-niveles.php           — CPT Niveles (Inicial/Primaria/Secundaria/Bachillerato)
  seed-nosotros.php          — página Nosotros (id 176) + línea de tiempo

qa/
  .env                      — credenciales (gitignoreado)
  .env.example              — plantilla
  lib/wp-playwright.mjs     — helper Playwright (login + screenshots)
  playwright-acf-collapse.mjs  — test E2E plegado
  playwright-acf-drag.mjs      — test E2E drag

docs/
  acf-pro-strong-wrapper-bug.md   — post-mortem detallado del bug
  jlb-arquitectura-contenido.md   — este archivo
  hubspot-open-day.md             — integración HubSpot del form Open Day

.claude/
  agents/qa-visual.md         — agente QA visual con Playwright
  agents/frontend-lead.md     — agente arquitecto
  agents/wp-security.md       — agente auditor seguridad
  agents/seo-manager.md       — agente SEO
  agents/ui-senior.md         — agente UX/design
  commands/requerimiento.md   — workflow 5-etapas
  commands/nuevo-modulo.md    — scaffold de módulo ACF
  commands/qa-modulo.md       — auditoría de un módulo
  commands/qa-release.md      — auditoría pre-merge
  hooks/wp-security-reminder.sh — recordatorio al editar modules/<slug>/*.php
```
