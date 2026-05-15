# Auditoría Técnica — Boilerplate WordPress
**Fecha:** 9 de mayo de 2026
**Tema:** boilerplate-wordpress (rama `dev`)
**Auditores:** Frontend Lead · UI Senior · SEO Manager
**Objetivo:** Identificar deuda técnica antes de usar la plantilla como base de nuevos proyectos

---

## Resumen ejecutivo

La plantilla tiene una arquitectura base funcional con un buen patrón de módulos ACF, Webpack 5 + SASS y separación clara de concerns en PHP. Sin embargo, acumula deuda técnica en tres áreas críticas que deben resolverse **antes** de derivar cualquier proyecto de cliente:

1. **Seguridad:** Múltiples outputs sin escapar (vectores XSS), código de ejemplo hardcodeado y assets sin sanitizar.
2. **Performance/SEO:** Build en modo desarrollo, todos los scripts bloqueando el render, imágenes LCP como CSS background, cero metadatos SEO.
3. **Accesibilidad:** Focus eliminiado globalmente, hamburger menu inaccesible, HTML inválido, imágenes sin alt.

Los hallazgos críticos y la mayoría de los importantes son de **esfuerzo bajo** (1-2 días para resolver todos los P0/P1). Los problemas de modernización de stack (Vite, Tailwind, Vanilla JS) se planean para v2.0.

---

## Índice

1. [Hallazgos Críticos (P0)](#hallazgos-críticos-p0)
2. [Hallazgos Importantes (P1/P2)](#hallazgos-importantes-p1p2)
3. [Oportunidades de Mejora (P3)](#oportunidades-de-mejora-p3)
4. [Auditoría Frontend Lead](#auditoría-frontend-lead)
5. [Auditoría UI Senior](#auditoría-ui-senior)
6. [Auditoría SEO Manager](#auditoría-seo-manager)
7. [Plan de Acción](#plan-de-acción)
8. [Impacto Esperado en Core Web Vitals](#impacto-esperado-en-core-web-vitals)

---

## Hallazgos Críticos (P0)

> Deben resolverse antes de derivar cualquier proyecto. La mayoría tienen esfuerzo bajo.

| # | Hallazgo | Archivos afectados | Esfuerzo |
|---|----------|--------------------|----------|
| C1 | **XSS — Outputs sin escapar** en múltiples módulos y templates | `hero.php:7`, `hero-blog.php:1-5`, `texto.php:7`, `menu.php:6`, `footer.php:5`, `404.php:5`, `index.php:11,32`, `single.php:7`, `customizer.php:79` | Bajo |
| C2 | **Código de ejemplo hardcodeado en producción** — `<a class="example">` con URL de terceros + `<script>` jQuery inline | `modules/hero/hero.php:55-62` | Bajo |
| C3 | **URL de cliente anterior en JS** — ruta de imagen hardcodeada en la plantilla base | `src/menuMobile.js:11` | Bajo |
| C4 | **Build en modo desarrollo** — `main.js` compilado con `eval-source-map` (416 KB), bloquea main thread | `build/js/main.js`, `webpack.config.js:86` | Bajo |
| C5 | **Scripts en `<head>` bloqueando render** — AOS, Colorbox y main.js con `false` como 5to argumento | `inc/libraries.php:39-56` | Bajo |
| C6 | **Cero metadatos SEO** — sin `meta description`, `canonical`, Open Graph ni Twitter Cards. `meta theme-color` hardcodeado del cliente anterior | `header.php` | Medio |
| C7 | **Imagen LCP como CSS background** — hero, hero-blog y single usan `style="background:url()"` inline; invisibles al preload scanner del navegador | `index.php:11`, `single.php:7`, `hero-blog.php:1`, `hero.php` | Medio |
| C8 | **Focus eliminado globalmente** — `* { outline: 0px; }` rompe navegación por teclado en todo el sitio; viola WCAG 2.1 AA criterio 2.4.7 | `styles/sass/basics/_generics.scss:17` | Bajo |
| C9 | **Hamburger menu inaccesible** — construido con `<div>` vacíos, sin `<button>`, `aria-label`, ni `aria-expanded`. `role="navegation"` tiene typo | `menu.php:9-15` | Bajo |
| C10 | **HTML inválido en 404** — `<body>` duplicado (header.php ya lo abre) + `<h2>` abre pero `</h1>` cierra | `404.php:2,5` | Bajo |

---

## Hallazgos Importantes (P1/P2)

| # | Hallazgo | Archivos afectados | Esfuerzo |
|---|----------|--------------------|----------|
| I1 | **Google Fonts bloqueante sin preconnect** — 8 pesos de fuente cargados, sin `<link rel="preconnect">` a googleapis ni gstatic | `inc/libraries.php:7` | Bajo |
| I2 | **`data-aos` en el H1** — AOS oculta el H1 de posts con `opacity:0` hasta que JS ejecuta → eleva LCP | `single.php:11` | Bajo |
| I3 | **Imágenes sin `width` y `height`** — slider de hero y logos sin dimensiones declaradas → CLS al cargar | `hero.php:11,17`, `menu.php:6`, `footer.php:5` | Bajo |
| I4 | **`alt` faltante en logos e imágenes de fondo** — viola WCAG 1.1.1 y penaliza SEO | `menu.php:6`, `footer.php:5`, `hero-blog.php`, `index.php`, `single.php` | Bajo |
| I5 | **Owl Carousel duplicado** — JS incluido en bundle Webpack + 2 archivos CSS enqueued por separado; cargado en todas las páginas sin condición | `src/index.js:2`, `inc/libraries.php:22-31` | Medio |
| I6 | **Bugs funcionales en JS** — `buildThresholdList()` itera solo una vez (threshold incorrecto); `inputFields.js` usa `.attr('value')` en lugar de `.val()`; `modals.js` cierra siempre el mismo modal | `src/intersectionObserver.js:4-9,64`, `src/inputFields.js:9-12`, `src/modals.js:5,10` | Medio |
| I7 | **`console.log` triple en producción** — tres logs en `menuMobile.js` + `drop_console: false` en webpack | `src/menuMobile.js:8,13,15`, `webpack.config.js:86` | Bajo |
| I8 | **Sin sistema de tokens de diseño** — sin CSS Custom Properties; 15+ tamaños de fuente distintos hardcodeados; sistema de grilla con proporciones arbitrarias (`col(6.7, 23)`) | `styles/sass/basics/` | Medio |
| I9 | **Sin structured data (Schema.org)** — cero JSON-LD en ningún template (Organization, Article, BreadcrumbList, WebSite) | Todos los templates | Alto |
| I10 | **Assets globales sin carga condicional** — AOS, Owl, Colorbox se cargan en todas las páginas aunque no se usen | `inc/libraries.php` | Medio |
| I11 | **Módulo `blog` en ACF sin archivo PHP** — `the_modules_loop()` falla silenciosamente al intentar renderizarlo | `acf-json/group_5504bb5d9b343.json`, `modules/` | Bajo |
| I12 | **`require` en lugar de `require_once`** — riesgo de redefinición fatal de funciones en temas hijo o con plugins | `functions.php:2-8` | Bajo |
| I13 | **Google Analytics sin sanitizar** — `echo get_option('my_google_analytics')` inyecta HTML crudo desde base de datos | `inc/customizer.php:79` | Bajo |
| I14 | **`add_theme_support('title-tag')` duplicado** — declarado en `functions.php` (fuera de hook) y en `inc/etc.php` (dentro de hook correcto) | `functions.php:12`, `inc/etc.php:3` | Bajo |
| I15 | **`@` error suppressor en producción** — `@get_sub_field()` silencia errores PHP reales | `modules/post/post.php:2` | Bajo |
| I16 | **npm packages con major versions desactualizadas** — `sass-loader` 13→16, `css-minimizer` 5→8, `browser-sync` 2→3, `webpack-cli` 5→7 entre otros | `package.json` | Medio |
| I17 | **Sin estructura Atomic Design** — SASS mezcla reset, variables, grid, menú y footer en `basics/`; no existen capas de átomos ni moléculas | `styles/sass/` | Medio |
| I18 | **Modal sin accesibilidad** — sin focus trap, sin `role="dialog"`, sin `aria-modal`, sin cierre con Escape, sin retorno de foco | `src/modals.js`, `styles/sass/components/_modal.scss` | Medio |

---

## Oportunidades de Mejora (P3)

| # | Mejora | Área | Esfuerzo |
|---|--------|------|----------|
| O1 | Implementar CSS Custom Properties como capa de tokens (`--color-primary`, `--spacing-4`, `--radius-md`) | UI / Frontend | Medio |
| O2 | Escala tipográfica unificada con `clamp()` para fluid typography responsive | UI | Medio |
| O3 | Refactorizar SASS con capas Atomic Design reales: tokens → atoms → molecules → organisms → templates | UI | Alto |
| O4 | Crear `template-parts/` con átomos y moléculas PHP reutilizables (button, card, image, breadcrumb, pagination) | Frontend / UI | Alto |
| O5 | Migrar imágenes LCP a `<img>` con `fetchpriority="high"` y demás a `loading="lazy"` | SEO / UI | Medio |
| O6 | Ampliar ACF con 8-10 módulos base estándar (CTA, testimonios, cards, acordeón, estadísticas, equipo, galería, formulario) | Frontend | Alto |
| O7 | Crear `inc/seo.php` con canonical dinámico, Open Graph y Twitter Cards sin dependencia de plugin | SEO | Medio |
| O8 | Crear `inc/schema.php` con JSON-LD: Organization, WebSite+SearchAction, Article, BreadcrumbList | SEO | Medio |
| O9 | Modal accesible: focus trap, `role="dialog"`, `aria-modal="true"`, cierre con Escape, retorno de foco | UI | Medio |
| O10 | Reemplazar Owl Carousel (abandonado desde 2018) por Swiper.js o Embla Carousel | Frontend | Alto |
| O11 | Plan de migración de jQuery a Vanilla JS por módulos | Frontend | Alto |
| O12 | Evaluar migración de Webpack → Vite + SASS → Tailwind CSS v4 para v2.0 | Frontend | Alto |
| O13 | Documentar guía de uso: módulos ACF, clases CSS, variables configurables, proceso para agregar módulos | Todos | Bajo |
| O14 | Agregar `function_exists()` checks en `lib/helpers.php` antes de llamar funciones ACF | Frontend | Bajo |
| O15 | Remover `<link rel="pingback">` — protocolo obsoleto que habilita vectores de DDoS reflection | SEO | Bajo |

---

## Auditoría Frontend Lead

### Hallazgos detallados

#### XSS — Outputs sin escapar (C1)

Todo output que va a HTML requiere la función de escape apropiada. Los siguientes archivos inyectan valores ACF o de opciones de WordPress directamente al DOM o a atributos HTML sin ningún escape:

```php
// hero-blog.php — bg en style="" sin esc_attr(), text (wysiwyg) sin wp_kses_post()
<section class="hero-blog" style="background:url(<?php the_sub_field('bg')?>)">
<div><?php the_sub_field('text') ?></div>

// hero.php — overlay en style="" sin esc_attr()
<div class="overlay" style="background-color:<?php the_sub_field('overlay') ?>">

// menu.php, footer.php, 404.php — URLs de get_theme_mod() sin esc_url()
<img src="<?php echo get_theme_mod('brand_img'); ?>">

// customizer.php — Google Analytics inyectado sin ningún sanitizador
echo get_option('my_google_analytics');
```

**Fix estándar:**
```php
// Texto plano
echo esc_html(get_sub_field('titulo'));

// URLs (src, href)
echo esc_url(get_theme_mod('brand_img'));

// Atributos HTML (style, class, data-)
echo esc_attr(get_sub_field('overlay'));

// HTML permitido (WYSIWYG)
echo wp_kses_post(get_sub_field('texto'));

// Google Analytics
wp_add_inline_script('theme-main', wp_kses(get_option('my_google_analytics'), ['script' => []]));
```

#### Bugs funcionales en JavaScript (I6)

**`intersectionObserver.js` — threshold incorrecto:**
```js
// ACTUAL — el bucle itera exactamente una vez, produciendo threshold [1, 0]
for (var i = 1.0; i <= 1.0; i++) { thresholds.push(i / steps); }

// FIX — iterar desde 0 hasta steps
for (let i = 0; i <= steps; i++) { thresholds.push(i / steps); }
```

**`inputFields.js` — lee atributo HTML en lugar del valor actual:**
```js
// ACTUAL — siempre devuelve "" si el input no tiene value="" hardcodeado en HTML
if ($(this).attr('value') !== '') { $(this).parent().addClass('active'); }

// FIX
if ($(this).val() !== '') { $(this).parent().addClass('active'); }
```

**`modals.js` — selector captura todos los .close y usa el primero:**
```js
// ACTUAL — cierra siempre el mismo modal
$('.close').on('click', function() {
    const modal = $(this).data('modal');
    // ...
});

// FIX — usar evento delegado con contexto correcto
$(document).on('click', '.close', function() {
    const modalId = $(this).data('modal');
    $('#' + modalId).hide();
});
```

#### Sistema de módulos ACF — gaps de escalabilidad (O6)

El sistema base funciona pero le faltan protecciones para escalar:

```php
// lib/helpers.php — versión actual sin validación
function the_module($module_name = '') {
    locate_template("/modules/$module_name/$module_name.php", true, false);
}

// lib/helpers.php — versión robusta
function the_module($module_name = '') {
    if (empty($module_name)) return false;

    $template = locate_template("/modules/$module_name/$module_name.php", false, false);

    if (!$template) {
        if (WP_DEBUG) {
            error_log("Boilerplate: módulo '$module_name' no encontrado en /modules/$module_name/");
        }
        return false;
    }

    load_template($template, false);
}

// ACF guard en the_modules_loop()
function the_modules_loop($modules_field = 'modules') {
    if (!function_exists('have_rows')) return;

    while (the_flexible_field($modules_field)) {
        $module_name = str_replace('_', '-', get_row_layout());
        the_module($module_name);
    }
}
```

---

## Auditoría UI Senior

### Hallazgos detallados

#### Focus global eliminado (C8)

```scss
// ACTUAL — _generics.scss:17
* { outline: 0px; }
button { outline: 0; }

// FIX — eliminar el reset y agregar focus-visible
*:focus { outline: none; }
*:focus-visible {
    outline: 2px solid var(--color-primary, #065A98);
    outline-offset: 2px;
    border-radius: 2px;
}
```

#### Hamburger menu inaccesible (C9)

```php
// ACTUAL — menu.php:9-13
<div class="hamburger">
    <div class="bun top"></div>
    <div class="meat"></div>
    <div class="bun bot"></div>
</div>

// FIX
<button
    class="hamburger"
    aria-label="Abrir menú de navegación"
    aria-expanded="false"
    aria-controls="main-navigation"
    type="button"
>
    <span class="hamburger__line" aria-hidden="true"></span>
    <span class="hamburger__line" aria-hidden="true"></span>
    <span class="hamburger__line" aria-hidden="true"></span>
</button>
```

```js
// menuMobile.js — actualizar aria-expanded al toggle
const hamburger = document.querySelector('.hamburger');
const nav = document.querySelector('#main-navigation');

hamburger.addEventListener('click', () => {
    const isOpen = hamburger.getAttribute('aria-expanded') === 'true';
    hamburger.setAttribute('aria-expanded', !isOpen);
    nav.classList.toggle('is-open');
    document.querySelector('header').classList.toggle('active');
});
```

#### Sistema de tokens de diseño faltante (I8)

```scss
// Agregar _tokens.scss en styles/sass/basics/
:root {
    // Colores
    --color-primary:       #065A98;
    --color-primary-hover: #0668ad;
    --color-gray-dark:     #383A3F;
    --color-gray-light:    #dbe3f2;
    --color-sandwich:      #98c9ec;
    --color-white:         #ffffff;

    // Tipografía
    --font-heading: 'Poppins', Arial, Helvetica, sans-serif;
    --font-body:    'Open Sans', Arial, Helvetica, sans-serif;

    // Escala tipográfica (fluid)
    --text-sm:   clamp(0.875rem, 1.5vw, 1rem);
    --text-base: clamp(1rem, 2vw, 1.125rem);
    --text-lg:   clamp(1.125rem, 2.5vw, 1.5rem);
    --text-xl:   clamp(1.5rem, 3vw, 2rem);
    --text-2xl:  clamp(2rem, 4vw, 3rem);
    --text-3xl:  clamp(2.5rem, 5vw, 4rem);

    // Espaciado (base 4px)
    --space-1:  4px;
    --space-2:  8px;
    --space-3:  12px;
    --space-4:  16px;
    --space-6:  24px;
    --space-8:  32px;
    --space-12: 48px;
    --space-16: 64px;
    --space-24: 96px;

    // Radios
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;

    // Sombras
    --shadow-sm: 0 1px 3px rgb(0 0 0 / 0.12);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --shadow-card: -0px 6px 20px rgb(0 0 0 / 0.25);

    // Breakpoints (como referencia — usar en media queries)
    --bp-mobile:  375px;
    --bp-tablet:  744px;
    --bp-desktop: 1240px;
    --bp-wide:    1440px;
}
```

#### Estructura Atomic Design propuesta (O3)

```
styles/sass/
├── tokens/
│   ├── _colors.scss
│   ├── _typography.scss
│   ├── _spacing.scss
│   └── _shadows.scss
├── atoms/
│   ├── _button.scss      (variantes: primary, secondary, ghost, danger, link)
│   ├── _input.scss       (variantes: default, focus, error, disabled)
│   ├── _label.scss
│   ├── _badge.scss
│   ├── _icon.scss
│   └── _image.scss
├── molecules/
│   ├── _form-group.scss  (label + input + helper + error)
│   ├── _card.scss        (imagen + contenido + footer)
│   ├── _pagination.scss
│   ├── _alert.scss
│   └── _breadcrumb.scss
├── organisms/
│   ├── _hero.scss
│   ├── _navbar.scss
│   ├── _footer.scss
│   ├── _article.scss
│   └── _modal.scss
├── templates/
│   ├── _page.scss
│   ├── _single.scss
│   └── _404.scss
└── utilities/
    ├── _grid.scss
    ├── _helpers.scss
    └── _mixins.scss
```

---

## Auditoría SEO Manager

### Hallazgos detallados

#### Metadatos SEO faltantes (C6)

```php
// inc/seo.php — crear este archivo y requerirlo en functions.php

add_action('wp_head', 'bp_seo_meta', 1);
function bp_seo_meta() {
    global $post;

    // Canonical
    $canonical = get_permalink();
    if (is_front_page()) $canonical = home_url('/');
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

    // Meta description
    $description = '';
    if (is_singular() && $post) {
        $description = get_the_excerpt();
    } elseif (is_front_page()) {
        $description = get_bloginfo('description');
    }
    if ($description) {
        echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($description)) . '">' . "\n";
    }

    // Open Graph
    $og_title = is_singular() ? get_the_title() : get_bloginfo('name');
    $og_image = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'large') : '';
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    if ($description) echo '<meta property="og:description" content="' . esc_attr(wp_strip_all_tags($description)) . '">' . "\n";
    if ($og_image) echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";

    // Twitter Cards
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '">' . "\n";
    if ($og_image) echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
}
```

#### Imagen LCP como CSS background (C7)

```php
// ACTUAL — hero-blog.php:1 — invisible al preload scanner
<section class="hero-blog" style="background:url(<?php the_sub_field('bg')?>)">

// FIX — migrar a <img> con fetchpriority para el primer módulo
<?php
$bg_image = get_sub_field('bg');
$is_first_module = (get_row_index() === 1);
?>
<section class="hero-blog">
    <?php if ($bg_image): ?>
        <img
            src="<?php echo esc_url($bg_image['url']); ?>"
            alt="<?php echo esc_attr($bg_image['alt']); ?>"
            width="<?php echo esc_attr($bg_image['width']); ?>"
            height="<?php echo esc_attr($bg_image['height']); ?>"
            class="hero-blog__bg"
            <?php echo $is_first_module ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
        />
    <?php endif; ?>
    <div class="hero-blog__content">
        <!-- contenido -->
    </div>
</section>
```

#### Structured data faltante (I9)

```php
// inc/schema.php — crear este archivo

add_action('wp_head', 'bp_schema_organization', 1);
function bp_schema_organization() {
    $logo = get_field('logo_principal', 'option');
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => get_bloginfo('name'),
        'url'      => home_url('/'),
        'logo'     => $logo ? [
            '@type'  => 'ImageObject',
            'url'    => esc_url($logo['url']),
            'width'  => $logo['width'],
            'height' => $logo['height'],
        ] : null,
        'sameAs' => array_filter([
            get_field('red_facebook', 'option'),
            get_field('red_instagram', 'option'),
            get_field('red_linkedin', 'option'),
        ]),
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}

add_action('wp_head', 'bp_schema_breadcrumb', 1);
function bp_schema_breadcrumb() {
    if (is_front_page()) return;

    $items = [[
        '@type'  => 'ListItem',
        'position' => 1,
        'name'   => 'Inicio',
        'item'   => home_url('/'),
    ]];

    if (is_singular()) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => get_the_title(),
        ];
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}
```

#### Preconnect y fuentes optimizadas (I1)

```php
// header.php — agregar ANTES de wp_head()
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

// inc/libraries.php — reducir pesos a los realmente usados
// ACTUAL: Open Sans 400;500;600;700 + Poppins 300;400;500;700 = 8 archivos
// FIX: cargar solo los pesos documentados en _variables.scss
wp_enqueue_style(
    'google-fonts',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap',
    [],
    null
);
```

---

## Plan de Acción

### Semana 1 — Seguridad, Rendimiento y HTML Válido (esfuerzo bajo, alto impacto)

**Día 1-2 — Seguridad:**
- [ ] Agregar `esc_url()`, `esc_attr()`, `wp_kses_post()` en todos los outputs sin escapar (C1)
- [ ] Eliminar bloque `<a class="example">` y `<script>` de `hero.php` (C2)
- [ ] Reemplazar URL hardcodeada de logo en `menuMobile.js` (C3)
- [ ] Reemplazar `require` por `require_once` en `functions.php` (I12)
- [ ] Eliminar `@` error suppressor en `post.php` (I15)
- [ ] Reemplazar Google Analytics sin sanitizar por `wp_add_inline_script()` (I13)

**Día 3 — Rendimiento base:**
- [ ] Cambiar `npm run prod` como proceso obligatorio, activar `drop_console: true` en webpack (C4)
- [ ] Cambiar `false` a `true` en los 3 `wp_enqueue_script` de `libraries.php` (C5)
- [ ] Agregar `<link rel="preconnect">` a Google Fonts en `header.php` (I1)
- [ ] Reducir pesos de fuentes a los necesarios (I1)
- [ ] Eliminar `console.log` en `menuMobile.js` (I7)

**Día 4 — Accesibilidad y HTML:**
- [ ] Reemplazar `* { outline: 0px }` con `:focus-visible` styles (C8)
- [ ] Reemplazar divs de hamburger por `<button>` con `aria-label` y `aria-expanded` (C9)
- [ ] Corregir `404.php`: eliminar `<body>` duplicado y cerrar `<h2>` correctamente (C10)
- [ ] Agregar `alt` y `width`/`height` a logos en `menu.php` y `footer.php` (I3, I4)
- [ ] Corregir typo `role="navegation"` → `role="navigation"` en `menu.php` (O15)
- [ ] Quitar `data-aos` del `<h1>` en `single.php` (I2)

**Día 5 — Fixes y limpieza:**
- [ ] Eliminar `add_theme_support('title-tag')` duplicado en `functions.php` (I14)
- [ ] Crear `modules/blog/blog.php` o eliminar el layout de ACF (I11)
- [ ] Corregir bugs en `intersectionObserver.js`, `inputFields.js` y `modals.js` (I6)
- [ ] Actualizar `menuMobile.js` con `aria-expanded` (C9)
- [ ] Remover `<link rel="pingback">` de `header.php` (O15)

### Semana 2 — SEO Foundation y Assets

- [ ] Crear `inc/seo.php` con canonical dinámico, meta description, Open Graph, Twitter Cards (C6)
- [ ] Migrar imágenes LCP de `background-image` a `<img>` + `fetchpriority="high"` (C7, O5)
- [ ] Agregar dimensiones a imágenes ACF en hero (I3)
- [ ] Crear `inc/schema.php` con Organization + Breadcrumb + Article JSON-LD (I9)
- [ ] Condicionar carga de Owl, AOS y Colorbox solo donde se usan (I10)
- [ ] Consolidar Owl Carousel en npm en lugar de duplicado en bundle + CSS suelto (I5)
- [ ] Actualizar packages npm (minor/patch primero, majors por separado) (I16)

### Sprint 3 — Design System y Módulos

- [ ] Crear `styles/sass/basics/_tokens.scss` con CSS Custom Properties (O1)
- [ ] Definir escala tipográfica con `clamp()` (O2)
- [ ] Comenzar refactor Atomic Design en SASS (O3)
- [ ] Crear `template-parts/atoms/button.php` y `template-parts/molecules/card.php` (O4)
- [ ] Agregar validación de existencia de módulo en `lib/helpers.php` (O14)
- [ ] Implementar modal accesible con focus trap y roles ARIA (O9)
- [ ] Agregar `meta theme-color` dinámico desde Customizer (eliminar el hardcodeado)

### v2.0 — Modernización del Stack

- [ ] Evaluar migración Webpack → Vite (O12)
- [ ] Evaluar migración SASS → Tailwind CSS v4 (O12)
- [ ] Reemplazar Owl Carousel por Swiper.js o Embla Carousel (O10)
- [ ] Migrar jQuery → Vanilla JS por módulos (O11)
- [ ] Ampliar ACF con 8-10 módulos base estándar (O6)
- [ ] Redactar guía de uso y documentación de componentes (O13)

---

## Impacto Esperado en Core Web Vitals

### Estado actual estimado (sin datos de campo reales)

| Métrica | Umbral Bueno | Estado estimado | Causa principal |
|---------|-------------|-----------------|-----------------|
| **LCP** | ≤ 2.5s | ❌ **>4s probable** | Build dev + scripts en head + imagen CSS background + Google Fonts bloqueante |
| **CLS** | ≤ 0.1 | ⚠️ **0.1-0.25** | Imágenes sin width/height + AOS con transform + fuentes sin swap |
| **INP** | <200ms | ⚠️ **Riesgo alto** | Bundle de 416KB con eval() + jQuery global + Owl Carousel |

### Mejora esperada al implementar P0+P1

| Métrica | Mejora esperada | Cambios que lo producen |
|---------|----------------|------------------------|
| **LCP** | -60% a -70% | Scripts al footer + imagen a `<img>` + preconnect fuentes + build producción |
| **CLS** | Reducción a <0.1 | Width/height en imágenes + fuentes con preconnect + quitar AOS del H1 |
| **INP** | Reducción a <200ms | Build prod sin eval() + scripts al footer (parsing diferido) |

---

## Métricas de referencia para validación post-fix

Usar estas herramientas después de implementar los cambios para confirmar mejora:

| Herramienta | URL | Qué validar |
|-------------|-----|-------------|
| PageSpeed Insights | pagespeed.web.dev | LCP, CLS, INP, FCP, TTFB |
| Rich Results Test | search.google.com/test/rich-results | Structured data válida |
| Mobile-Friendly Test | search.google.com/test/mobile-friendly | Accesibilidad móvil |
| WAVE Accessibility | wave.webaim.org | WCAG 2.1 AA |
| HTML Validator | validator.w3.org | HTML válido |
| Schema Validator | validator.schema.org | JSON-LD correcto |

---

*Auditoría generada con el equipo de agentes: Frontend Lead · UI Senior · SEO Manager*
*Proyecto: boilerplate-wordpress · Rama: dev · Fecha: 9 de mayo de 2026*
