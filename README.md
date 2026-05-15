# Boilerplate WordPress

Plantilla base para proyectos WordPress con Vite, Tailwind CSS v4, GSAP y Swiper.
Usa ACF Pro para la construcción modular de páginas.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Build | **Vite v8** |
| CSS | **Tailwind CSS v4** + SASS (Atomic Design) |
| JS | **Vanilla JS** + GSAP + Swiper |
| CMS | WordPress + **ACF Pro** |
| PHP | 8.x · Node ≥ 18 |

---

## Inicio rápido

```bash
npm install

# Producción
npm run build

# Desarrollo (HMR)
# 1. wp-config.php: define('VITE_DEV_SERVER', true);
npm run dev
```

---

## Comandos

| Comando | Descripción |
|---------|-------------|
| `npm run dev` | Vite dev server (HMR localhost:5173) |
| `npm run build` | Bundle producción con hash |
| `npm run preview` | Preview del bundle |
| `npm run legacy:dev` | Webpack modo desarrollo |
| `npm run legacy:build` | Webpack modo producción |
| `npm run clean` | Limpiar `build/` |

---

## Configuración

En `wp-config.php` (local):

```php
define('WP_ENVIRONMENT_TYPE', 'local');
define('VITE_DEV_SERVER', true);   // activa Vite dev server
define('WP_DEBUG', true);
```

En producción: NO definir `VITE_DEV_SERVER`.

---

## Estructura

```
boilerplate-wordpress/
├── acf-json/              # Field groups versionados con Git
├── build/                 # Assets compilados
├── docs/                  # Auditorías y documentación técnica
├── inc/
│   ├── acf-modules.php    # Registro programático de módulos ACF
│   ├── libraries.php      # wp_enqueue (Vite + fallback Webpack)
│   ├── schema.php         # JSON-LD: Organization, Article, Breadcrumb
│   └── seo.php            # Canonical, meta, Open Graph
├── lib/
│   ├── helpers.php        # the_module(), the_modules_loop()
│   └── vite.php           # bp_vite_asset(), bp_is_vite_dev()
├── modules/               # Módulos ACF (12 disponibles)
├── src/                   # Fuentes JS
│   ├── animations/        # GSAP setup + scroll animations
│   ├── main.css           # Tailwind v4 + @theme tokens
│   └── main.js            # Entry Vite
├── styles/sass/           # SASS — Atomic Design
│   ├── utilities/         # Variables, mixins, grid, generics
│   ├── atoms/             # Botón, tipografía
│   ├── molecules/         # Card
│   ├── organisms/         # Navbar, footer, hero, módulos
│   └── vendors/           # Colorbox
├── template-parts/        # PHP reutilizables
│   ├── atoms/             # button.php, image.php
│   └── molecules/         # card.php, breadcrumb.php
└── vite.config.js
```

---

## Módulos ACF

Todos se configuran en **WP Admin → Página → Componentes de Página**.

| Módulo | Descripción |
|--------|-------------|
| `hero` | Slider con imagen desktop/mobile, overlay, texto, botón |
| `hero-blog` | Hero para páginas de blog |
| `cta` | Llamada a la acción con 2 botones, imagen de fondo |
| `testimonios` | Carrusel con foto, nombre, cargo, calificación |
| `cards-servicios` | Grid 2/3/4 col con ícono, título, descripción, botón |
| `acordeon` | FAQ accesible con aria-expanded |
| `estadisticas` | Contadores animados con GSAP |
| `equipo` | Grid con foto, bio, redes sociales |
| `galeria` | Galería de imágenes 2/3/4 col |
| `formulario` | Shortcode CF7/WPForms + imagen lateral |
| `blog` | Listado de posts con filtro por categoría |
| `texto` | Bloque WYSIWYG con imagen y color |

### Agregar un módulo nuevo

```bash
mkdir modules/mi-modulo
```

```php
<?php
// modules/mi-modulo/mi-modulo.php
$titulo = get_sub_field('titulo');
if (!$titulo) return;
?>
<section class="mi-modulo">
    <div class="container">
        <h2><?php echo esc_html($titulo); ?></h2>
    </div>
</section>
```

Agregar el layout en `inc/acf-modules.php` → array `'layouts'`:

```php
'mi_modulo' => array(
    'key'   => 'layout_bp_mi_modulo',
    'name'  => 'mi_modulo',
    'label' => 'Mi Módulo',
    'sub_fields' => array(
        array('key'=>'field_bp_mm_titulo','label'=>'Título','name'=>'titulo','type'=>'text'),
    ),
),
```

> ACF transforma `mi_modulo` → `mi-modulo` para encontrar el archivo PHP.

---

## SASS — Atomic Design

| Capa | Archivos |
|------|---------|
| `utilities/` | `_variables`, `_mixins`, `_grid`, `_generics` |
| `atoms/` | `_button` (primary/outline/ghost/sm/lg), `_typo` |
| `molecules/` | `_card` (default/featured/horizontal) |
| `organisms/` | `_navbar`, `_footer`, `_hero`, módulos nuevos |
| `vendors/` | `_colorbox` |

**Variables SASS:**
```scss
$primary · $primary-hover · $gray · $lightgray · $primary-text
```

**Mixins:**
```scss
@include border-radius(8px);
@include transition(.3s);
@include mobile  { }  // max-width: 744px
@include tablet  { }  // min-width: 745px
@include desktop { }  // min-width: 1240px
```

**CSS Custom Properties (tokens):**
```css
--color-primary   --color-primary-hover   --color-gray-dark
--font-heading    --font-body
--text-xs → --text-3xl    /* fluid con clamp() */
--space-1 → --space-24    /* base 4px */
--radius-sm → --radius-full
--shadow-sm → --shadow-card
--transition-fast / --transition-base / --transition-slow
```

---

## Animaciones GSAP

### Scroll animations (data-gsap)

```html
<div data-gsap="fade-up">Fade desde abajo</div>
<div data-gsap="fade-right" data-gsap-delay="0.2">Con delay</div>
<div data-gsap="zoom-in" data-gsap-duration="1">Zoom</div>
<!-- Tipos: fade-up | fade-down | fade-left | fade-right | zoom-in | zoom-out -->

<!-- Grupo con stagger -->
<div data-gsap-batch=".card">
    <div class="card">…</div>
    <div class="card">…</div>
</div>

<!-- Parallax -->
<div data-gsap-parallax data-gsap-speed="0.3"><img …></div>

<!-- Contador -->
<span data-gsap-counter>250</span>
```

### Uso en módulos custom

```js
import { gsap, ScrollTrigger } from './animations/gsap.js'

gsap.from('.elemento', {
    opacity: 0, y: 50, duration: 0.8,
    scrollTrigger: { trigger: '.seccion', start: 'top 80%' },
})
```

---

## template-parts

### button.php

```php
get_template_part('template-parts/atoms/button', null, [
    'label'   => 'Ver más',
    'url'     => get_permalink(),
    'variant' => 'primary',   // primary | outline | ghost
    'size'    => '',          // '' | sm | lg
    'target'  => '_self',
]);
```

### image.php

```php
get_template_part('template-parts/atoms/image', null, [
    'image'    => get_sub_field('imagen'),  // array ACF
    'priority' => true,   // fetchpriority="high" en imagen LCP
    'cover'    => true,   // object-fit: cover
    'class'    => 'mi-clase',
]);
```

### card.php

```php
// En loop WordPress:
while (have_posts()): the_post();
    get_template_part('template-parts/molecules/card');
endwhile;
```

### breadcrumb.php

```php
// Auto-construye desde contexto WP + Schema.org microdata
get_template_part('template-parts/molecules/breadcrumb');
```

---

## Carrusel Swiper

```html
<div class="swiper mi-carrusel">
    <div class="swiper-wrapper">
        <div class="swiper-slide">…</div>
    </div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</div>
```

```js
// src/carousel.js
createSwiper('.mi-carrusel', {
    loop: true,
    autoplay: { delay: 4000 },
    navigation: {
        nextEl: '.mi-carrusel .swiper-button-next',
        prevEl: '.mi-carrusel .swiper-button-prev',
    },
    breakpoints: { 744: { slidesPerView: 2 }, 1240: { slidesPerView: 3 } },
})
```

---

## SEO automático

El tema genera en `<head>`:
- Canonical URL · Meta description · Open Graph · Twitter Cards
- JSON-LD: `Organization`, `WebSite+SearchAction`, `BreadcrumbList`, `Article`

---

## Menús registrados

| Slug | Ubicación |
|------|-----------|
| `menu_principal` | Header |
| `footer` | Footer links |
| `redes` | Redes sociales en footer |
| `menu_secundario` | Secundario |

---

*Auditado y modernizado: mayo 2026*
