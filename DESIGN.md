# DESIGN.md — Design System JLB (Colegio Jean Le Boulch)

Guía del sistema de diseño del tema **JLB**. Sirve para que cualquier diseñador, desarrollador o IA
mantenga consistencia visual y de comportamiento al construir o corregir secciones.

> **Cómo leer esto.** Cada sección trae la tabla de referencia + clases reales del código + el "porqué"
> (criterio UI). Los valores aquí están verificados contra el código fuente; si editas tokens o estilos,
> actualiza también este documento. **Fuente de verdad del código:**
> `styles/sass/organisms/_jlb-tokens.scss`, `src/main.css` (`@theme`), `styles/sass/style.scss`,
> `inc/libraries.php` y los organismos `styles/sass/organisms/_jlb-*.scss`.

---

## 1. Marca y tono

- **Quién.** Colegio Jean Le Boulch (JLB). Institución educativa.
- **Personalidad visual.** Cálida, cercana, educativa y vital. Combina rigor (DM Sans, layouts limpios,
  grids ordenados) con expresividad infantil/juvenil (tipografía manuscrita en titulares, gradiente de
  marca vibrante, esquinas asimétricas, garabatos decorativos que giran).
- **Idioma.** Todo el contenido y los microcopys en **español**. Tono directo, amable y confiado;
  segunda persona ("Inscríbete", "Conoce…", "Ver más información").
- **Principio rector.** El diseño debe transmitir confianza a las familias y energía a los estudiantes.
  Cuando dudes entre "serio" y "cálido", inclínate por lo cálido sin perder legibilidad ni contraste.

---

## 2. Color

### Tokens de marca (scope `.jlb-home-template`)

Definidos en `styles/sass/organisms/_jlb-tokens.scss`. Solo aplican dentro del template JLB para no
contaminar el resto del tema.

| Token | Hex | Uso |
|---|---|---|
| `--jlb-purple` | `#614794` | Color primario de marca. Titulares de sección (niveles, kicker), focos, acentos. |
| `--jlb-wine` | `#993356` | Paso intermedio del gradiente; acentos cálidos. |
| `--jlb-red` | `#c92323` | CTA y énfasis. Texto de botones blancos, CTA roja de objetivos, links de artículo. |
| `--jlb-ink` | `#161f33` | Tinta base del cuerpo del template (color de texto por defecto). |
| `--jlb-copy` | `#374059` | Texto secundario / cuerpo de párrafos. |
| `--jlb-soft` | `#f6f1fb` | Fondo suave lila (superficies sutiles, bandas). |

### Gradiente de marca

```css
--jlb-gradient: linear-gradient(180deg,
  var(--jlb-purple) 20%,  /* #614794 */
  var(--jlb-wine)  57%,   /* #993356 */
  var(--jlb-red)   86%);  /* #c92323 */
```

Es la firma visual de JLB. Se usa en: heroes (`.jlb-adm-hero`, `.jlb-page-hero`), banda vertical del
plan de estudios (`.jlb-taller-plan__band`), tags/píldoras de categoría (`.jlb-tag`, `.jlb-pcard__cat`),
botón gradiente (`.jlb-link-external--gradient`, `.jlb-openday__submit`) y el punto/dot decorativo
(`.jlb-taller-plan__dot`, `.jlb-taller-eyebrow::before`).

### Acentos teal y tinta de títulos

| Color | Hex | Uso |
|---|---|---|
| Teal play | `#09A699` | Stop inicial del gradiente del triángulo del botón Play (→ `#614794`). Ver §9. |
| Teal-verde títulos objetivos | `#109b8c` | `.jlb-taller-obj__title` (títulos de la sección "Objetivos"). |
| Teal link externo (Admisión) | `#367596` | Texto del `.jlb-link-external` blanco en el hero de Admisión. |
| Tinta de títulos | `#14111f` | Titulares oscuros sobre blanco (artículos, experiencias, plan, relacionados). |

> **Nota.** El texto del `.jlb-link-external` blanco cambia de color **según la página**: teal `#367596`
> (Admisión), vino `#ac2d42` (Experiencias), rojo `#c32529` (Open Day), rojo `--jlb-red` (cuota/condiciones).
> Esto se acota por `body.jlb-page-<slug>` para no pisar otras páginas.

### Dónde se definen los tokens y la regla de oro

Existen **dos fuentes** de tokens en el tema (fragmentación conocida):

1. `src/main.css` → bloque `@theme` (Tailwind v4). Tokens "modernos" (`--color-*`, `--font-*`, radios,
   sombras, breakpoints). Es el **chasis** del tema base.
2. `styles/sass/organisms/_jlb-tokens.scss` → CSS custom properties **específicas de JLB**
   (`--jlb-*`), bajo el scope `.jlb-home-template`.

> **Regla.** Un token nuevo se declara **primero en `src/main.css`** (`@theme`). Solo se "espeja" a
> `_tokens.scss` (o `styles/sass/basics/_tokens.scss`) si el SASS lo referencia. Para tokens propios del
> universo JLB (`--jlb-*`), viven en `_jlb-tokens.scss`. Cuando los tokens divergen entre archivos,
> estilos de navbar/menú/títulos se rompen silenciosamente.

### Contraste

- Texto blanco siempre sobre el gradiente o sobre fotos con overlay (nunca blanco sobre `--jlb-soft`).
- Texto oscuro: usa `--jlb-ink` / `#14111f` para titulares y `--jlb-copy` / `#231f20` para cuerpo.
- Verifica AA (4.5:1) en cuerpo y AA Large (3:1) en titulares grandes.

---

## 3. Tipografía

Fuentes cargadas vía Google Fonts en `inc/libraries.php` con `display=swap` (evita FOIT):
DM Sans (300/400/500/600/700), Raleway (400/600/700), DM Serif Display (`ital@0;1` — recta + cursiva) y Caveat (500/600/700).

| Familia | Rol | Notas |
|---|---|---|
| **KG Second Chances Solid** | Titulares display / manuscritos | Voz expresiva de marca. Fallback: `"Caveat", cursive`. KG no se sirve por Google Fonts — Caveat es el fallback equivalente que sí se carga. |
| **DM Sans** | Cuerpo, UI, botones, labels | Familia de trabajo. La mayoría del texto funcional. |
| **Raleway** | Tipo base del template + footer | `font-family` base de `.jlb-home-template` (se hereda en cuerpo, pills de niveles y footer). Declarada explícitamente, p.ej., en el subtítulo del hero y en el label inline del footer (`.jlb-footer__label`). |
| **DM Serif Display** | Watermark decorativo | Solo `.jlb-section-watermark` (números/letras gigantes al 4% de opacidad). |

### Escala tipográfica (clamp fluido)

| Elemento | Tamaño (clamp) | Familia | Clase |
|---|---|---|---|
| Título hero (Admisión) | `clamp(34px, 4.4vw, 52px)` | KG / Caveat | `.jlb-adm-hero__title` |
| Título banner (page-hero) | `clamp(40px, 5vw, 64px)` | KG / Caveat | `.jlb-page-hero__title` |
| Título sección (objetivos) | `clamp(26px, 3.4vw, 40px)` | DM Sans 700 | `.jlb-taller-obj__title` |
| Título sección (relacionados) | `clamp(28px, 3.4vw, 40px)` | DM Sans 700 | `.jlb-related__title` |
| Título sección (plan de estudios) | `clamp(24px, 3vw, 34px)` | DM Sans 700 | `.jlb-taller-plan__title` |
| Título niveles (H2) | `clamp(36px, 4vw, 44px)` | KG / Caveat | `.jlb-levels h2` |
| Kicker | `clamp(34px, 3vw, 44px)` | KG / Caveat | `.jlb-kicker` |
| Título artículo | `clamp(32px, 4.4vw, 48px)` | KG / Caveat | `.jlb-article__title` |
| Cuerpo / párrafos | `16px` (línea 1.5–1.6) | DM Sans / Raleway | `.jlb-*__text`, `.jlb-article__content` |
| Subtítulo hero | `16px` (línea 1.5) | Raleway / DM Sans | `.jlb-adm-hero__subtitle` |

> **Patrón.** Titulares emotivos → KG/Caveat (`font-weight: 400`, line-height ~1.05–1.15). Titulares
> "informativos"/de datos → DM Sans 700. Cuerpo → 16px DM Sans. No uses KG en cuerpo ni en botones.

---

## 4. Layout y espaciado

- **Contenedor de marca:** `.jlb-container` → `width: min(1140px, calc(100% - 40px))`, centrado
  (`margin-inline: auto`). En ≤820px el gutter baja a 28px.
- **Contenedores anchos puntuales:** blog/artículo/open-day usan `min(1200px, calc(100% - 40px))`.
- **Padding vertical de sección:** patrón fluido `clamp(40–48px, 6–7vw, 80–96px)`. Mantén el ritmo
  vertical entre secciones consistente con ese rango.
- **Grids responsivos típicos:**
  - Niveles: `repeat(3, minmax(0, 1fr))` → 1 col en ≤820px.
  - Blog: `repeat(3, …)` → 2 col en ≤900px → 1 col en ≤560px.
  - Experiencias: 2 columnas alternadas (texto/imagen) → 1 col en ≤900px (imagen siempre arriba).
  - Open Day form / relacionados: `1fr 1fr` → 1 col (≤720px / ≤900px).

### Breakpoints usados

No hay una escala única; los organismos usan los breakpoints que pide cada layout en Figma:

| Breakpoint | Uso habitual |
|---|---|
| `≤1024px` | Hero pasa de 2 a 1 columna. |
| `≤900px` | Blog a 2 col; experiencias/relacionados a 1 col. |
| `≤860px` | Objetivos / plan a 1 col. |
| `≤820px` | Niveles a 1 col; gutter del container a 28px. |
| `≤720px` | Formulario Open Day a 1 col. |
| `≤560px` | Blog a 1 col; acciones de hero apiladas; botones full-width. |

> **Pixel-perfect.** El referente es **desktop 1440**. Tablet y mobile son **propuesta responsive**
> (no hay comp pixel-perfect por breakpoint): respeta intención, jerarquía y los breakpoints de arriba.

---

## 5. Radios y formas

| Forma | Valor | Uso |
|---|---|---|
| **Píldora de marca** | `border-radius: 13px 13px 44px 13px` | `.jlb-btn`, pill de niveles (`.jlb-level-card__pill`). Esquina inferior-derecha pronunciada = firma JLB. |
| Card de nivel | `32px 32px 55px 32px` | `.jlb-level-card` (esquina inferior-derecha aún más pronunciada). |
| Card de entrada / relacionada | `24px` | `.jlb-pcard`, `.jlb-rcard__image`. |
| Imagen/video grande | `28px` / `32px` | `.jlb-taller-video__frame` (28), `.jlb-article__featured .jlb-article__cover` (32). |
| Tag / píldora gradiente | `14px` | `.jlb-tag`. |
| Botón gradiente | `20px` | `.jlb-link-external--gradient`, `.jlb-openday__submit`. |
| Botón link externo (blanco) | `24px` | `.jlb-link-external`. |
| Esquina asimétrica de imagen hero | `border-bottom-left-radius: clamp(80px, 12vw, 160px)` | `.jlb-adm-hero__media`. En Experiencias también redondea la superior-izquierda. |
| Wave de transición | SVG full-width al pie del hero | `.jlb-adm-hero__wave`, `.jlb-page-hero__wave` (blanco, conecta hero→fondo blanco). |

> **Criterio.** La asimetría (esquina inferior-derecha) es deliberada y reconocible: úsala en
> botones y cards de marca. No la apliques a inputs ni a contenedores neutrales.

---

## 6. Botones

| Clase | Aspecto | Uso |
|---|---|---|
| `.jlb-link-external` | Píldora **blanca**, radio 24px, texto teal/vino/rojo según página, icono ↗ | CTA principal sobre gradiente; "Ver condiciones". |
| `.jlb-link-external--outline` | Transparente, borde blanco 1px, texto blanco | CTA secundario sobre gradiente. |
| `.jlb-link-external--gradient` | **Gradiente de marca**, texto blanco, radio 20px | "Ver más información" sobre fondo blanco. |
| `.jlb-btn` | Píldora `13px 13px 44px 13px`, min-height 56px, DM Sans 700 18px | Botón base reutilizable (hero/módulos). Modificadores `--light` (fondo blanco/texto rojo), `--outline` (borde blanco). |
| `.jlb-openday__submit` | Gradiente, radio 20px, texto blanco, submit `<button>` | Envío del formulario Open Day. Estado `:disabled` → opacidad 0.6 + cursor progress. |
| `.jlb-taller-obj__cta-btn` | Píldora blanca sobre card roja, texto rojo | CTA dentro de la card de objetivos. |

Patrón compartido: `display: inline-flex; gap: 8px;` (texto + icono), hover `translateY(-2px)` + sombra,
`:focus-visible` con outline visible.

> **Gotcha crítico (color de texto en links).** `.jlb-home-template a { color: inherit }` (especificidad
> 0,1,1) **vence** al `color` base de un `.jlb-link-external` (0,1,0) → el texto hereda blanco y queda
> ilegible. **Solución:** reasertar con selector de tipo `a.` (mismo `@layer`, gana por especificidad):
>
> ```scss
> a.jlb-link-external { color: var(--jlb-red); }     // o el teal/vino según página
> a.jlb-link-external--gradient { color: #fff; }
> ```
> Acota los overrides por página (`body.jlb-page-<slug> .jlb-adm-hero a.jlb-link-external { … }`) para no
> pisar otros botones.

---

## 7. Componentes / organismos

Cada organismo vive en `modules/<slug>/<slug>.php` (markup) + `styles/sass/organisms/_jlb-<slug>.scss`
(estilos, importados en `@layer components` desde `style.scss`).

| Componente | Clase raíz | Notas de diseño |
|---|---|---|
| **Hero reutilizable** | `.jlb-adm-hero` | Módulo `jlb_admision_hero`: gradiente full-width, texto izq + imagen der con esquina redondeada + wave al pie. Campos opcionales: `eyebrow`, `video` (overlay vino-morado + play centrado + caption), `titulo_imagen` (logo en vez de texto). Lo reusan Admisión, Open Day y Experiencias con overrides por página. |
| **Banner secundario** | `.jlb-page-hero` | Hero centrado (título + subtítulo) sobre gradiente + wave. Para blog/páginas interiores. |
| **Niveles** | `.jlb-levels` / `.jlb-level-card` | Grid de 3 cards con foto + pill (`13px 13px 44px 13px`). Hover: card sube (`translateY(-6px) !important`), zoom de imagen, pill resalta. Última card opcional `--wide` (ancho completo). Overlay link cubre toda la card. |
| **Manifesto** | `.jlb-manifesto` | Bloque de texto-marca. |
| **Experience** | `.jlb-experience` | Grid de videos con play (lightbox). Versión home del patrón de videos. |
| **Experiencias** | `.jlb-experiencias` | Filas alternadas media/texto (`--reverse`). Media con play → video-lightbox. Título KG/Caveat, tinta `#14111f`. |
| **Testimoniales** | `.jlb-testimoniales` | Slider (Swiper) con arco decorativo. |
| **Testimonio padres** | `.jlb-testimonio-padres` | Cita de familias. |
| **Noticias** | `.jlb-noticias` | Carrusel/grid de últimas entradas en home. |
| **Footer** | `.jlb-footer` (footer-jlb.php) | Raleway heredada + `h2` en KG/Caveat; grid administrable (logo + teléfono + redes + escríbenos + legal). El `.site-footer` de `src/main.css` es el footer **genérico/legacy** del boilerplate, no el de JLB. |
| **Tarjeta de entrada** | `.jlb-pcard` | Blog card: imagen (zoom hover), badge de fecha, tag de categoría (gradiente), overlay link. Radio 24px, hover lift `translateY(-4px)`. |
| **Tag de categoría** | `.jlb-tag` | Píldora con gradiente, texto blanco, radio 14px. |
| **FAQ acordeón** | `.jlb-faq` | Preguntas plegables. |
| **Cuota (calculadora)** | `.jlb-cuota` | Cálculo de cuota; usa `.jlb-link-external` "Ver condiciones". |
| **Proceso (stepper)** | `.jlb-proceso` | Pasos numerados del proceso de admisión. |
| **Galería (lightbox zoom)** | `.jlb-galeria` | Grid de imágenes con `data-jlb-zoom` (ver §9). |
| **Plan de estudios** | `.jlb-taller-plan` | Banda vertical (gradiente, texto rotado) + grid de cards `rgb(114 125 208 / .1)` con dot gradiente. |
| **Objetivos** | `.jlb-taller-obj` | Lista numerada (numeral gris `#c7ccdb`), título teal `#109b8c`, aside = imagen + CTA roja conectadas (sin gap). Garabato decorativo que gira. |
| **Formato compartido** | `template-parts/jlb-formato-detalle.php` | Cuerpo común de los CPT Talleres y Niveles: **ambos** `single-taller.php` y `single-nivel.php` lo incluyen. Reusa hero (`get_template_part('modules/jlb-admision-hero/…')`) + galería (`modules/jlb-galeria/…`) + plan + objetivos + testimoniales, todos vía `get_template_part(…, $args)`. |

> **Reutilización.** Antes de crear un componente nuevo, revisa si un organismo existente cubre el caso
> con un modificador (`--reverse`, `--wide`, `--outline`, `--gradient`). El hero `jlb_admision_hero` y el
> formato `jlb-formato-detalle.php` están diseñados para reutilizarse en varias páginas.

---

## 8. Movimiento

Animaciones controladas por **GSAP** vía data-attributes (el scanner de `src/animations/` las cablea solo;
no hace falta JS por plantilla):

```html
<div data-gsap="fade-up"  data-gsap-delay="0.2" data-gsap-duration="1">…</div>
<!-- fade-up | fade-down | fade-left | fade-right | zoom-in | zoom-out | fade -->
<!-- opcionales extra: data-gsap-ease, data-gsap-start -->
<div data-gsap-batch=".card">…</div>                      <!-- stagger del grupo -->
<div data-gsap-parallax data-gsap-speed="0.3">…</div>     <!-- fondo con scroll lento -->
<span data-gsap-counter>250</span>                        <!-- cuenta 0 → valor -->
```

Scanner: `src/animations/onScroll.js` (entrada `initScrollAnimations`); setup GSAP/ScrollTrigger en
`src/animations/gsap.js`.

- **Deco que gira.** Garabatos decorativos usan `animation: jlb-deco-spin 6s linear infinite`
  (`@keyframes jlb-deco-spin` definido en `_jlb-blog.scss`). Aparecen en `.jlb-article__deco`,
  `.jlb-taller-obj__deco` y la deco de `.jlb-cuota`.
- **Hover.** Cards/botones: `translateY(-2/-4/-6px)` + sombra; imágenes: `scale(1.03–1.06)` con
  `transition: 600ms cubic-bezier(0.22, 1, 0.36, 1)`.

> **Obligatorio: `prefers-reduced-motion`.** Toda animación debe degradar. El tema ya lo cubre dos veces:
> global en `src/main.css` (anula transitions/animations, oculta `video[data-decorative]`) y específico
> en `_jlb-tokens.scss` (fuerza `opacity:1; transform:none` en `[data-gsap]` y desactiva hovers). Cuando
> añadas movimiento nuevo, agrega su regla en el bloque `@media (prefers-reduced-motion: reduce)` del
> organismo (como hacen niveles/blog/experiencias).

---

## 9. Media / lightbox

| Atributo | Qué hace | Dónde |
|---|---|---|
| `data-jlb-video` | Abre lightbox de video (YouTube / Vimeo / MP4) al hacer click en el poster. | `src/jlbVideoLightbox.js`, heroes, experiencias, taller. |
| `data-jlb-zoom` | Abre lightbox de zoom de imagen. | `src/jlbImageLightbox.js`, `modules/jlb-galeria`. |

### Botón Play de marca (asset `assets/figma-home/testimonial-play.svg`)

Círculo blanco + triángulo con **gradiente teal → morado** (`#09A699` → `#614794`). Se inyecta **inline**
(`file_get_contents` + `wp_kses`) para conservar el `<linearGradient>`; usar `<img>` aplanaría el gradiente.
El poster lleva overlay oscuro (tinte vino-morado, p.ej. `rgb(48 28 63 / .52)`) y el play escala en hover.

> **Gotchas de assets.**
> - **"Plays horneados":** algunas imágenes de Figma vienen con el play ya dibujado encima. No las uses
>   como poster + play superpuesto (saldría doble). Usa el poster limpio + el SVG de play del sistema.
> - **Transparencia:** los screenshots exportados de Figma vienen **sobre fondo blanco**. Para iconos/play
>   que deben ir sobre foto o gradiente, usa el **asset raw/SVG** (con alpha real), no el PNG aplanado.

---

## 10. Accesibilidad

- **Focus visible.** Todo elemento interactivo expone `:focus-visible` con outline (blanco sobre gradiente,
  `--jlb-purple`/`--jlb-red` sobre blanco) y `outline-offset`. No elimines outlines sin reemplazo.
- **Área clickeable completa.** Las cards usan un **overlay link** absoluto (`.jlb-pcard__link`,
  `.jlb-level-card__link`, `inset:0; z-index:3`) que cubre toda la tarjeta → target táctil grande (Ley de
  Fitts) sin anidar `<a>` dentro de `<a>`.
- **Texto solo para lectores.** `.sr-text` / `.sr-only` (definidas globalmente en `_jlb-tokens.scss`)
  para etiquetas no visibles. El honeypot del form Open Day (`.jlb-openday__hp`) se mueve fuera de pantalla.
- **aria.** Botones de video/zoom con `aria-label` descriptivo; SVG decorativos con `aria-hidden="true"`;
  estados de formulario con `aria-live`. El form de Open Day (`modules/jlb-open-day-form`) envía vía
  `fetch` al endpoint REST `POST /wp-json/jlb/v1/open-day` (proxy server-side a HubSpot, `inc/hubspot.php`)
  y reporta resultado en `.jlb-openday__status` (`aria-live`). Los formularios genéricos del módulo
  `.formulario` usan **Contact Form 7** (estilos shadcn sobre `.wpcf7` en `src/main.css`, respuesta
  `polite`). No confundir ambos: Open Day = HubSpot/REST; `.formulario` = CF7.
- **Contraste.** Ver §2. Sobre fotos/gradiente, garantiza overlay suficiente antes de poner texto blanco.

---

## 11. Convenciones de arquitectura relevantes al diseño

- **Capa de cascada: `@layer components`, NO `legacy`.** Todos los `@import` de organismos JLB van en
  `@layer components` dentro de `styles/sass/style.scss`. **Por qué:** Tailwind v4 publica su reset en
  `@layer base`, y el orden declarado es `legacy, theme, base, components, utilities`. Si pusieras los
  estilos JLB en `legacy`, `base` (reset de Tailwind) ganaría y colapsaría titulares (`h1 { font-size:
  inherit }`) y bordes. En `components` quedan **por encima** del reset y **por debajo** de las utilities.
- **Scope por página: `body.jlb-page-<slug>`.** Overrides específicos de una página (color de botón, radio
  de imagen del hero) se acotan con esta clase para no afectar otras páginas que reusan el mismo módulo.
- **Scope del template: `.jlb-home-template`.** Tokens `--jlb-*`, tipografía base (Raleway) y `a { color:
  inherit }` viven bajo este scope. Recuerda el gotcha de §6 al estilar links.
- **`!important` justificado.** Solo cuando GSAP deja un `transform` inline que el hover debe vencer
  (`.jlb-level-card:hover`) o para vencer un `margin` global sin capa (`.jlb-taller-obj__img`). Documenta
  el motivo en el código; no lo uses por comodidad.
- **Pixel-perfect = desktop 1440.** Tablet/mobile son propuesta responsive guiada por los breakpoints de §4.
- **Dev vs build.** En desarrollo (`VITE_DEV_SERVER`) el CSS lo inyecta HMR; en producción se sirve desde
  el manifest. Si validas visualmente, asegúrate de saber en qué modo estás (un cambio SCSS sin recompilar
  no se verá en build).
```