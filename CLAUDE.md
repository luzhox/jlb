# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress theme boilerplate. Stack: PHP 8.x + ACF Pro (Flexible Content) + Vite v8 + Tailwind CSS v4 + SASS (Atomic Design) + Vanilla JS + GSAP + Swiper. Node ≥ 18.

The theme lives at `wp-content/themes/boilerplate-wordpress/` inside a Local (Flywheel) site at `/Users/luismorales/Local Sites/template-wp/`. Local URL: `http://jlb-school.local/`.

## Commands

```bash
npm install
npm run dev         # Vite dev server on localhost:5173 (HMR + PHP full-reload)
npm run build       # Production bundle to build/ (with hashed filenames + manifest)
npm run preview     # Vite preview of production bundle
npm run clean       # rm -rf build/*

npm run legacy:dev  # Webpack watch (legacy bundler, still available during migration)
npm run legacy:build
```

There is **no test suite** and **no lint script** configured. PHP linting relies on the IDE; the `add_filter`/`add_action` "unknown function" warnings are false positives (WP stubs not loaded).

### Dev server activation — auto-detección por hot file (recomendado)

`bp_is_vite_dev()` (`lib/vite.php`) decide dev vs prod así:

1. **Si `VITE_DEV_SERVER` está definida en `wp-config.php`** → manda su valor
   (`true` = dev, `false` = prod). Override explícito para forzar un modo.
2. **Si NO está definida** y `WP_ENVIRONMENT_TYPE === 'local'` → **auto-detección**:
   dev cuando Vite corre, detectado por el hot file `.vite-hot` que el dev server
   crea al arrancar (`npm run dev`) y borra al cerrar (`vite.config.js`). `npm run
   build` también lo limpia. Así el modo **sigue al dev server sin tocar
   wp-config**.

**Para usar la auto-detección** (config recomendada en local): deja en
`wp-config.php` solo
```php
define('WP_ENVIRONMENT_TYPE', 'local');
define('WP_DEBUG', true);
// (NO definas VITE_DEV_SERVER)
```
y reinicia `npm run dev` una vez (para que escriba el hot file con el plugin
nuevo). A partir de ahí: `npm run dev` → dev; cerrarlo o `npm run build` → prod.

Si en algún momento queda un `.vite-hot` huérfano (Vite cerró sin limpiar),
bórralo a mano o corre `npm run build`. Port 5173 es `strictPort: true`.

En producción (`WP_ENVIRONMENT_TYPE != 'local'`) nunca entra en dev salvo que
definas explícitamente la constante.

## Architecture

### Module loop — the central pattern

Every page rendered through `page.php` (and the dedicated `page-demo.php`) is composed via ACF Flexible Content. The contract:

1. ACF field group `group_bp_componentes` registers a Flexible Content field named `modules` on `post_type=page` and `post_type=post`. Defined programmatically in `inc/acf-modules.php` — **not** via `acf-json/`.
2. `the_modules_loop()` (`lib/helpers.php:29`) iterates `have_rows('modules')`, takes the layout name from `get_row_layout()`, converts underscores to hyphens (`mi_modulo` → `mi-modulo`), and `locate_template()`s `modules/<name>/<name>.php`.
3. Each module's PHP file reads its own `get_sub_field()` values and renders a `<section>`. Convention: if the primary field is empty, `return` early.

To add a module: create `modules/<slug>/<slug>.php`, then add a `'layouts'` entry in `inc/acf-modules.php` with `'name' => '<slug_with_underscores>'`. The hyphen↔underscore conversion is automatic in `the_modules_loop()`.

### ACF source-of-truth contract

Two filters in `inc/acf-modules.php` enforce that **PHP is the only place where the `modules` flexible field is defined**:

- `acf/settings/load_json` — strips theme/child `acf-json/` paths, so any exported JSON is ignored.
- `acf/load_field_groups` — drops any field group (other than `group_bp_componentes`) that contains a sub-field named `modules`.

**Why this exists** (load-bearing comment, do not remove without understanding): if a second group registers a field also named `modules` (common when an old DB has orphan groups like `group_5504bb5d9b343` from a previous install), ACF stores the field-key in `_modules` meta. The two groups race on save → after a refresh the editor reads a key that doesn't match the PHP group → flexible content appears empty. Symptom: "I saved a module, refreshed to add another, the previous one disappeared."

If you need to add another field group, give it a different field name. Never name a sub-field `modules`.

### Vite ↔ WordPress bridge

`lib/vite.php` exposes:

- `bp_is_vite_dev()` — dev vs prod: si `VITE_DEV_SERVER` está definida, manda su valor; si no, en entorno `local` auto-detecta el hot file `.vite-hot` que escribe el dev server (ver "Dev server activation").
- `bp_vite_asset($entry)` — returns `http://localhost:5173/<entry>` in dev or `<theme>/build/<hashed-file>` from manifest in prod.
- `bp_vite_css($entry)` — empty in dev (HMR injects CSS), manifest-derived URL in prod.
- `bp_vite_module_tag($handle)` — `script_loader_tag` filter that adds `type="module" crossorigin` to a Vite-enqueued script.

`inc/libraries.php` is the single enqueue point. It branches on `bp_is_vite_dev()`:
- **Dev**: injects `@vite/client` in `<head>` and enqueues `src/main.js` from the dev server.
- **Prod**: reads `build/.vite/manifest.json` for the hashed JS/CSS. Falls back to legacy Webpack bundle (`build/js/main.js` + `build/css/main.css`) if no Vite manifest exists. This fallback exists because Vite/Webpack coexist during migration — `vite.config.js` has `emptyOutDir: false` for the same reason.

### File organisation

```
inc/                  # WP integrations — always loaded via functions.php
  acf-modules.php     # Flexible Content registration + source-of-truth filters
  libraries.php       # wp_enqueue (Vite + Webpack fallback)
  schema.php          # JSON-LD: Organization, WebSite, BreadcrumbList, Article
  seo.php             # Canonical, meta description, OG, Twitter Cards
  menus.php           # 4 menu locations: menu_principal, footer, redes, menu_secundario
  customizer.php, widgets.php, login.php, formats.php, etc.php

lib/
  helpers.php         # the_module(), get_module(), the_modules_loop()
  vite.php            # Vite asset helper (above)

modules/<slug>/<slug>.php   # 13 ACF Flexible Content modules — see README.md

src/                  # Vite entry tree
  main.js             # Sole bundled entry (rollupOptions.input.main)
  main.css            # Tailwind v4 + @theme tokens
  animations/         # GSAP setup, ScrollTrigger, data-gsap-* attribute scanner
  carousel.js, modals.js, menuMobile.js, scrollHeader.js, etc.

styles/sass/          # SASS — Atomic Design, compiled through Vite
  basics/             # _tokens, _generics, _inputs
  utilities/          # _variables (SASS↔CSS-var aliases), _mixins, _grid
  atoms/              # _button, _typo
  molecules/          # _card
  organisms/          # _navbar, _footer, _hero, _modules, _modal, _article, _blog-list

template-parts/       # PHP partials — Atomic Design mirror of SASS
  atoms/              # button.php, image.php  (called via get_template_part with $args)
  molecules/          # card.php, breadcrumb.php

build/                # Compiled output — committed for prod (.vite/manifest.json + js/, css/, assets/)
docs/                 # Design audits and technical docs (e.g. auditoria-boilerplate-2026.md)
acf-json/             # If present, ignored by the load_json filter — do not rely on it
```

### Tailwind v4 + SASS coexistence (known fragmentation)

Design tokens currently live in **three places**:
- `src/main.css` `@theme` block (Tailwind v4, the modern source).
- `styles/sass/basics/_tokens.scss` (CSS custom properties for the SASS pipeline).
- `styles/sass/utilities/_variables.scss` (SASS aliases like `$primary` → `var(--color-primary)`, with some camelCase legacy names).

When tokens drift between these, navbar/menu styles silently break (e.g. `--color-active-menu` defined in `@theme` but missing from `_tokens.scss` ⇒ `color: ;` invalid). When adding a new token, add it to `src/main.css` first; mirror to `_tokens.scss` only if SASS code references it.

**Critical layer rule for JLB styles (load-bearing):** every JLB partial `@import` in `styles/sass/style.scss` MUST live inside the `@layer components` block, **never** `@layer legacy`. The cascade-layer order (`legacy, theme, base, components, utilities`) puts Tailwind's reset (`@layer base`: `h1 { font-size: inherit }`, `* { border-width: 0 }`) above `legacy` — so a JLB partial imported in `legacy` gets its headings collapsed to body size and borders zeroed (the styles render "half-applied"). The `@layer components` block (with all the `_jlb-*` imports) sits above `base` and fixes this. When adding a new JLB section, add its `@import` to that block. Also note: **unlayered global rules beat any layer** (e.g. a global `figure { margin-bottom }`); to win, use `!important` inside the layer.

### GSAP scroll attributes

`src/animations/` scans the DOM for these data attributes (no JS-side wiring needed in templates):

```html
<div data-gsap="fade-up" data-gsap-delay="0.2" data-gsap-duration="1">…</div>
<!-- types: fade-up | fade-down | fade-left | fade-right | zoom-in | zoom-out -->

<div data-gsap-batch=".card">…</div>          <!-- stagger group -->
<div data-gsap-parallax data-gsap-speed="0.3"><img …></div>
<span data-gsap-counter>250</span>             <!-- counts 0 → text content -->
```

When adding new motion, prefer extending the attribute scanner over hand-writing GSAP per template.

### template-parts conventions

Atoms and molecules accept arrays via `get_template_part(..., null, $args)`:

```php
get_template_part('template-parts/atoms/button', null, [
    'label' => 'Ver más', 'url' => $url, 'variant' => 'primary', 'size' => '', 'target' => '_self',
]);

get_template_part('template-parts/atoms/image', null, [
    'image' => get_sub_field('imagen'),  // ACF image array
    'priority' => true,                   // fetchpriority="high" for LCP
    'cover' => true, 'class' => 'mi-clase',
]);
```

`card.php` is meant to be called inside a WP `while (have_posts())` loop and reads from the global post.

## Conventions and gotchas

- **Layout-name ↔ folder-name**: ACF layout `name` uses underscores (`mi_modulo`); the corresponding folder/file uses hyphens (`modules/mi-modulo/mi-modulo.php`). The loop converts automatically — pick the right form per side.
- **Module early-return**: every `modules/<x>/<x>.php` checks its primary field and returns silently if empty. Don't render empty `<section>`s.
- **No `acf-json/`**: even if the directory exists, it's filtered out. Don't add field groups there expecting them to load.
- **jQuery is still enqueued globally** (`wp_enqueue_script('jquery')` + `external: ['jquery']` in Vite config) because a few legacy vendors (Colorbox, AOS) depend on it. New code should be vanilla.
- **AOS is still enqueued** (`vendors/aos.js` + `styles/css/aos.css`) alongside GSAP — pre-existing animations may still use `data-aos`. Prefer `data-gsap` for new code.
- **Colorbox loads only on `is_singular()`** — keep it that way; it's heavy.
- **`page-demo.php`** is a showcase template that hardcodes ACF content for all 13 modules. Useful for visual QA when no real page has the modules populated. Assign it via Page Attributes → Template.
- **WordPress coding standards**: 2-space indentation (per `CONTRIBUTING.md`), escape all output (`esc_html`, `esc_url`, `esc_attr`), sanitize all input.
- **Untracked `index.html`** in the theme root is leftover and not part of the theme — don't ship it.

## Workflows automáticos (Claude Code)

El directorio `.claude/` versiona la configuración de equipo: agentes, slash commands y un hook PostToolUse. Está pensado para que cualquier persona del equipo —humana o IA— siga el mismo proceso. La configuración local sigue en `.claude/settings.local.json` (no la edites desde aquí).

### Agentes especializados (`.claude/agents/`)

- **`ui-senior`** — Diseño centrado en usuario, design systems, Figma, heurísticas, journey mapping. **Primera etapa** del workflow para requerimientos nuevos.
- **`frontend-lead`** — Arquitecto del boilerplate (módulos ACF, Vite, Tailwind v4, GSAP, template-parts, Atomic Design, Gutenberg). Invocar con `Agent({ subagent_type: "frontend-lead", ... })` o vía `/qa-modulo`, `/qa-release`, `/requerimiento`.
- **`qa-visual`** — QA visual con Playwright contra wp-admin del Local site jlb-school. Credenciales en `qa/.env` (gitignoreado). Toma screenshots + asserts sobre el DOM. Úsalo cuando el usuario diga "valida tú mismo", "verifica visualmente", o tras cambios de UI/admin. Ver scripts de referencia en `qa/playwright-*.mjs`.
- **`seo-manager`** — SEO técnico, Core Web Vitals, structured data, Search Console.
- **`wp-security`** — Auditor de seguridad (XSS, CSRF, SQLi, capabilities, uploads, headers, secrets). Invocar **siempre** antes de merge si el cambio toca PHP que maneja input usuario.

### Slash commands (`.claude/commands/`)

- **`/requerimiento "<descripción>"`** — Workflow completo de 5 etapas para procesar un requerimiento nuevo. Pipeline: `ui-senior` (UX) → `frontend-lead` (implementación) → `qa-visual + seo-manager + wp-security` (reviews en paralelo) → consolidación con veredicto APTO / APTO CON CAMBIOS / NO APTO. Soporta saltar etapas según el tipo de cambio (bug fix solo backend, copy, etc.).
- **`/nuevo-modulo <slug-kebab> "<descripción>"`** — Scaffold completo de un módulo ACF: crea `modules/<slug>/<slug>.php` con el patrón canónico (salida temprana, escape, GSAP scanner, template-parts) y registra el layout en `inc/acf-modules.php` con la convención snake/kebab. Recuerda no exportar a `acf-json/`.
- **`/qa-modulo <slug>`** — Auditoría cruzada de un módulo existente. Lanza `frontend-lead` y `wp-security` en paralelo, valida invariantes (early return, escape, snake/kebab, template-parts) y produce un reporte con hallazgos clasificados (BLOQUEANTE / IMPORTANTE / SUGERENCIA / CRÍTICO / ALTO / MEDIO / BAJO).
- **`/qa-release`** — Pre-release sobre la rama actual vs `master`. Coordina los tres agentes (`frontend-lead`, `wp-security`, `seo-manager`) en paralelo, corre sanity checks (php -l, npm run build, manifest, secrets), y emite un reporte ejecutivo "APTO / APTO CON CAMBIOS / NO APTO". No abrir PR si hay BLOQUEANTES sin resolver.
- **`/correccion-visual <sección|URL> [+ link Figma] [+ síntoma]`** — Loop de corrección visual de una sección/módulo existente contra Figma (o un síntoma reportado). Orquesta `qa-visual` (diagnóstico: captura en vivo a 1440px sin admin-bar + ref Figma vía MCP) → `ui-senior` (criterio de aceptación + responsive) → `frontend-lead` (implementación) → `qa-visual` (re-validación + no-regresión), iterando hasta APTO. Incluye los gotchas del proyecto (cascada `@layer` vs Swiper, dev/build, datos ACF vs código, plays horneados). Para **arreglar** lo que ya existe; para construir algo nuevo usa `/requerimiento`.

### Hook automático

`.claude/hooks/wp-security-reminder.sh` se dispara como PostToolUse de `Edit`/`Write` cuando el path coincide con `modules/<slug>/<algo>.php`. Inyecta un recordatorio con el slug detectado para que se ejecute `/qa-modulo` o se invoque al agente `wp-security` antes de cerrar el cambio. Para cambios cosméticos (clases Tailwind, copys), el aviso puede ignorarse.

Registrado en `.claude/settings.json` (versionado). Para deshabilitarlo localmente, usa `.claude/settings.local.json` con `hooks: {}` (override).

### Flujo recomendado para un módulo nuevo

1. `/nuevo-modulo testimonios-grid "Grid de testimonios con foto y rol"`
2. Editar campos ACF y plantilla PHP. (El hook recordará invocar wp-security.)
3. Llenar datos en wp-admin, validar visualmente en `/demo/`.
4. `/qa-modulo testimonios-grid` antes de commitear.
5. Antes de merge a `master`: `/qa-release`.

### Flujo recomendado para un requerimiento completo

Para features que tocan UX + arquitectura + reviews:

1. `/requerimiento "Necesito un módulo X con campos Y, debe verse Z"`
2. El pipeline corre las 5 etapas y devuelve un veredicto consolidado.
3. Resolver bloqueantes (si los hay), re-correr.
4. Si APTO → commit + PR.

## Lecciones documentadas (`docs/`)

- **`acf-pro-strong-wrapper-bug.md`** — bug ACF Pro 6.8.1 + WP 6.9.4 (wrapper `<strong>` rompe collapse y drag) y el patch defensivo en `assets/admin/acf-collapse-patch.{js,css}`. Lee esto antes de tocar nada del editor ACF en este sitio.
- **`jlb-arquitectura-contenido.md`** — cómo se administra el contenido: home (Componentes de Página), menú, footer **y todas las páginas internas** (Admisión, Experiencias, Open Day, Nosotros, Blog, CPT Talleres, CPT Niveles) con sus plantillas, módulos (15 layouts JLB), el formato de detalle compartido (`template-parts/jlb-formato-detalle.php`), seeders por página y los gotchas de las sesiones 2026-05-24/25. Léelo antes de tocar cualquier página JLB.
- **`hubspot-open-day.md`** — integración del formulario Open Day con HubSpot (endpoint REST proxy `jlb/v1/open-day`, config administrable en Ajustes del sitio → Integraciones + constantes para secretos). Léelo antes de tocar el form o la integración.

## QA visual con Playwright (`qa/`)

Scripts E2E reproducibles que loguean en wp-admin con credenciales de `qa/.env` (gitignoreado):

```bash
node qa/playwright-acf-collapse.mjs   # valida plegado de módulos ACF
node qa/playwright-acf-drag.mjs       # valida drag-and-drop de módulos
```

Helper compartido en `qa/lib/wp-playwright.mjs` con `launchAdminSession()`, `screenshot()`, `openPageByTitle()`. Para escribir tests nuevos, **copia uno de los scripts existentes** — siguen el mismo patrón. El agente `qa-visual` está entrenado para usar esta librería.
