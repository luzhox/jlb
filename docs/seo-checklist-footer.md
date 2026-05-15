# SEO Checklist — Footer Kresna (Fase 1)

> Audiencia: `frontend-lead` mientras implementa el footer Kresna.
> Cada sección trae snippets ready-to-paste. Las "Top recommendations" del final son las que se aplican SÍ O SÍ antes de cerrar Fase 1.
> No se toca código aquí; solo recomendaciones.

---

## 1. JSON-LD Organization — `sameAs` + diff para `inc/schema.php`

### Estado actual

`inc/schema.php` ya emite `Organization` en cada página, leyendo `redes_facebook`, `redes_instagram`, `redes_linkedin` desde `get_option()`. Funciona, pero:

- Solo cubre 3 redes (Facebook/Instagram/LinkedIn). El footer Kresna requiere **Discord, X, LinkedIn, GitHub** — combinación distinta.
- Los valores se almacenan en `wp_options` (campos sueltos, sin namespace), no en Customizer.
- `frontend-lead` está creando los Customizer fields `kresna_social_discord_url`, `kresna_social_x_url`, `kresna_social_linkedin_url`, `kresna_social_github_url` (consensuado en el doc de design system).

### Recomendación

Migra `Organization.sameAs` a leer del Customizer (mismo lugar que el footer renderiza), y mantén compat con los `wp_options` legacy durante una versión.

#### Diff sugerido en `inc/schema.php` (líneas 25-32)

Reemplaza el bloque actual:

```php
$social_links = array_filter(array(
    get_option('redes_facebook'),
    get_option('redes_instagram'),
    get_option('redes_linkedin'),
));
if ($social_links) {
    $org_schema['sameAs'] = array_values($social_links);
}
```

Por:

```php
// sameAs — preferimos Customizer (Kresna). Fallback a wp_options legacy.
$social_keys = array(
    'kresna_social_discord_url',
    'kresna_social_x_url',
    'kresna_social_linkedin_url',
    'kresna_social_github_url',
);
$social_links = array();
foreach ($social_keys as $key) {
    $url = get_theme_mod($key, '');
    if ($url) {
        $social_links[] = esc_url_raw($url);
    }
}
// Fallback: opciones legacy (Facebook/Instagram/LinkedIn) si Customizer vacío
if (empty($social_links)) {
    $legacy = array_filter(array(
        get_option('redes_facebook'),
        get_option('redes_instagram'),
        get_option('redes_linkedin'),
    ));
    $social_links = array_map('esc_url_raw', array_values($legacy));
}
if ($social_links) {
    $org_schema['sameAs'] = $social_links;
}
```

#### Campos opcionales adicionales que el cliente puede aprobar (no bloquean Fase 1)

```php
// Si el cliente expone estos campos en Customizer, agrégalos:
$contact_phone = get_theme_mod('kresna_contact_phone', '');
$contact_email = get_theme_mod('kresna_contact_email', '');

if ($contact_phone || $contact_email) {
    $org_schema['contactPoint'] = array(
        '@type'        => 'ContactPoint',
        'contactType'  => 'customer service',
        'availableLanguage' => array('English', 'Spanish'),
    );
    if ($contact_phone) {
        $org_schema['contactPoint']['telephone'] = $contact_phone;
    }
    if ($contact_email) {
        $org_schema['contactPoint']['email'] = $contact_email;
    }
}

// founder / foundingDate — solo si el cliente los comparte
$founding_date = get_theme_mod('kresna_founding_date', ''); // ISO 8601: 2023-04-15
if ($founding_date) {
    $org_schema['foundingDate'] = $founding_date;
}
```

### Validación

1. Abrir https://search.google.com/test/rich-results
2. Pegar URL del sitio o el HTML completo
3. Buscar bloque `Organization` → debe mostrar `name`, `url`, `logo`, `sameAs` con las 4 URLs sociales
4. **Sin errores** ni warnings. Si aparece "missing field 'logo'", es que `brand_img` Customizer está vacío — cargarlo
5. Validador alterno: https://validator.schema.org/ (pega URL)

---

## 2. Lazy load del vídeo de fondo

El spec literal del cliente trae `<video autoplay muted loop preload="auto">`. Mal por defecto: `preload="auto"` descarga el archivo completo desde el primer paint, robando ancho de banda al LCP del above-the-fold y subiendo TBT.

### Opción A — Conservadora (recomendada para Fase 1)

`preload="metadata"` + `poster` = autoplay sigue funcionando en Chrome/Edge/Safari (el navegador difiere automáticamente la descarga si el `<video>` está fuera del viewport inicial), y el poster da algo que pintar.

```html
<video
  class="footer-video-bg"
  autoplay
  muted
  loop
  playsinline
  preload="metadata"
  poster="<?php echo esc_url(get_theme_file_uri('images/footer-video-poster.jpg')); ?>"
  aria-hidden="true">
  <source src="<?php echo esc_url(get_theme_file_uri('videos/footer-bg.mp4')); ?>" type="video/mp4">
</video>
```

- `playsinline` es OBLIGATORIO en iOS, sin él el vídeo abre en pantalla completa al hacer tap
- `aria-hidden="true"` porque es decorativo
- El poster es un frame estático del vídeo (export con `ffmpeg -ss 00:00:01 -i footer-bg.mp4 -frames:v 1 footer-video-poster.jpg`)

### Opción B — Agresiva (si Lighthouse sigue marcando "unused bytes")

`preload="none"` + `IntersectionObserver` que dispara `video.load()` y `video.play()` cuando el footer entra al viewport.

```js
// src/footerVideo.js (módulo nuevo, IMPORT desde main.js)
function initFooterVideo() {
  const video = document.querySelector('.footer-video-bg');
  if (!video) return;

  // Reduced motion: ni cargar
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) {
    video.removeAttribute('autoplay');
    video.pause();
    return;
  }

  // IO carga el vídeo solo cuando el footer entra al viewport
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        if (video.preload !== 'auto') {
          video.preload = 'auto';
          video.load();
        }
        video.play().catch(() => { /* autoplay denied; poster se queda */ });
        io.unobserve(video);
      }
    });
  }, { rootMargin: '200px 0px', threshold: 0.01 });

  io.observe(video);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFooterVideo);
} else {
  initFooterVideo();
}
```

Y el HTML cambia a:

```html
<video class="footer-video-bg" muted loop playsinline preload="none"
       poster="..." aria-hidden="true">
  <source src="..." type="video/mp4">
</video>
```

(Quita `autoplay` — el `play()` del JS lo dispara cuando toca.)

### Trade-offs

| | Opción A | Opción B |
|---|---|---|
| Bytes en el primer paint | ~50-200KB (metadata) | ~0 |
| Complejidad | 0 (solo HTML) | JS + IntersectionObserver |
| Riesgo de no autoplay | Bajísimo (el navegador decide) | Bajo (JS gatilla play) |
| Reduced-motion | Manejado por CSS `@media (prefers-reduced-motion)` que pause | Manejado en JS |

**Mi recomendación**: empezar con Opción A. Medir en PageSpeed Insights. Si LCP/TBT del footer-page sigue mal, escalar a Opción B.

### `prefers-reduced-motion` (obligatorio en ambas opciones)

Añade en `src/main.css` o donde vivan las reglas del footer:

```css
@media (prefers-reduced-motion: reduce) {
  .footer-video-bg {
    display: none; /* o visibility: hidden; el poster no se ve si display:none */
  }
  /* Si quieres conservar el poster como imagen estática: */
  .footer-bg-fallback {
    display: block;
  }
}
```

### ¿Mismo archivo que el watermark o separado?

`frontend-lead` ya va a crear `src/footerWatermark.js` para el `getBBox()`. El handler del vídeo es independiente — sugiero archivo separado `src/footerVideo.js`. Razones:

- Dos features con triggers distintos (watermark = resize; vídeo = scroll into view)
- Ambos pueden importarse desde `main.js` con tree-shaking
- Si Fase 2 mata el vídeo o el watermark independientemente, borrar un archivo es más limpio que rebuscar

---

## 3. Contrast audit del watermark "Kresna"

### Cálculos exactos

**Light mode**: `--color-watermark: rgb(0 0 0 / 0.04)` sobre `--color-background: #FFFFFF`
- Color compuesto: `rgb(245, 245, 245)` aprox
- Contrast ratio vs `#FFFFFF`: **~1.05:1**
- WCAG 1.4.3 (texto normal): requiere 4.5:1 → **falla por mucho**
- WCAG 1.4.6 (AAA): requiere 7:1 → falla

**Dark mode**: `--color-watermark: rgb(255 255 255 / 0.06)` sobre `oklch(0.141 0.005 285.823)` ≈ `#09090B`
- Color compuesto: ~`rgb(24, 24, 26)`
- Contrast ratio vs background: **~1.10:1**
- También falla WCAG si fuera texto.

### Por qué NO es un problema

WCAG 1.4.3 / 1.4.6 explícitamente excluyen "text that is part of an inactive user interface component, that is pure decoration, that is not visible to anyone, or that is part of a picture that contains significant other visual content".

El watermark "Kresna" entra en **pure decoration** — no transmite información (la marca ya aparece en el logo y en el `<title>`). Sin embargo, el SVG `<text>` SÍ es leído por screen readers y crawlers a menos que se marque explícitamente.

### Acciones requeridas

```html
<div class="footer-watermark" aria-hidden="true">
  <svg
    xmlns="http://www.w3.org/2000/svg"
    role="presentation"
    aria-hidden="true"
    focusable="false"
    tabindex="-1">
    <text x="0" y="0">Kresna</text>
  </svg>
</div>
```

- `aria-hidden="true"` en el wrapper Y en el SVG (defensa en profundidad — si el SVG escapa del wrapper en un contexto raro, sigue oculto)
- `role="presentation"` en el SVG — refuerza que NO es semántico
- `focusable="false"` — IE/Edge legacy interpretan SVG como focusable por default; aunque el target moderno no es ese, no cuesta nada
- `tabindex="-1"` — extra, evita que keyboard focus aterrice ahí
- **Asegúrate de que NO haya un `<title>` dentro del `<svg>`** (eso revierte el `aria-hidden` en algunos screen readers)

### Riesgo SEO de no hacerlo

Si Googlebot parsea el `<text>Kresna</text>` como contenido, la palabra "Kresna" aparece duplicada en cada página → puede inflar artificialmente keyword density del término "Kresna" hasta el punto de parecer keyword stuffing si la marca no está en el contenido principal. `aria-hidden` no influye en Google, pero el `<text>` dentro de un `<svg>` con `role="presentation"` sí baja la prioridad del crawler. **La mejor mitigación**: el watermark ES la palabra "Kresna" y la marca real es "Kresna" — coincide. El riesgo solo aplicaría si el watermark dijera otra cosa.

### Validación

- Lighthouse → Accessibility audit, sección "ARIA": no debe quejarse del SVG
- Manualmente: NVDA/VoiceOver navegando el footer → no debe leer "Kresna" del watermark, solo del logo

---

## 4. Subscribe form — INP, validación y handler

### HTML mínimo

```html
<form class="footer-subscribe" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" novalidate>
  <?php wp_nonce_field('kresna_subscribe', '_kresna_subscribe_nonce'); ?>
  <input type="hidden" name="action" value="kresna_subscribe">

  <label for="footer-email" class="sr-only">
    <?php esc_html_e('Email address', 'boilerplate'); ?>
  </label>

  <input
    type="email"
    id="footer-email"
    name="email"
    autocomplete="email"
    inputmode="email"
    required
    placeholder="<?php esc_attr_e('Enter your email', 'boilerplate'); ?>"
    aria-describedby="footer-subscribe-msg"
  >

  <button
    type="submit"
    aria-label="<?php esc_attr_e('Subscribe to our newsletter', 'boilerplate'); ?>">
    <?php esc_html_e('Subscribe', 'boilerplate'); ?>
  </button>

  <div id="footer-subscribe-msg" class="footer-subscribe__msg" role="status" aria-live="polite"></div>
</form>
```

Puntos críticos:

- `<label class="sr-only">` siempre presente — el placeholder NO sustituye al label (a11y requirement)
- `autocomplete="email"` + `inputmode="email"` — teclado mobile correcto, autofill funcional
- `novalidate` en el form + `required` en el input → permite manejar errores con UI propia, pero conserva la semántica
- `aria-describedby` apunta al div de mensajes
- `role="status" aria-live="polite"` en el div de mensajes — screen readers anuncian el resultado sin interrumpir
- `<button type="submit">` — no `<a>`, no `<div>` con click handler

### Handler — Opción A (PHP, sin JS — ideal para Fase 1)

En `functions.php` o nuevo `inc/forms.php`:

```php
add_action('admin_post_nopriv_kresna_subscribe', 'kresna_handle_subscribe');
add_action('admin_post_kresna_subscribe', 'kresna_handle_subscribe');
function kresna_handle_subscribe() {
    if (!isset($_POST['_kresna_subscribe_nonce'])
        || !wp_verify_nonce($_POST['_kresna_subscribe_nonce'], 'kresna_subscribe')) {
        wp_safe_redirect(add_query_arg('subscribe', 'error', wp_get_referer() ?: home_url('/')));
        exit;
    }

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!is_email($email)) {
        wp_safe_redirect(add_query_arg('subscribe', 'invalid', wp_get_referer() ?: home_url('/')) . '#footer-email');
        exit;
    }

    // TODO: integración con Mailchimp/Brevo. Por ahora: mailto admin.
    $to      = get_option('admin_email');
    $subject = sprintf('[%s] Nuevo subscriber', get_bloginfo('name'));
    $body    = sprintf("Email: %s\nIP: %s\nDate: %s", $email, $_SERVER['REMOTE_ADDR'] ?? '?', current_time('mysql'));
    wp_mail($to, $subject, $body);

    wp_safe_redirect(add_query_arg('subscribe', 'ok', wp_get_referer() ?: home_url('/')) . '#footer-subscribe-msg');
    exit;
}
```

Y en el template, leer el query param para pintar mensaje:

```php
<?php if (isset($_GET['subscribe'])): ?>
  <div id="footer-subscribe-msg" role="status" aria-live="polite">
    <?php
    $msg = sanitize_key($_GET['subscribe']);
    if ($msg === 'ok')      esc_html_e('Thanks! Check your inbox.', 'boilerplate');
    if ($msg === 'invalid') esc_html_e('That email looks invalid.', 'boilerplate');
    if ($msg === 'error')   esc_html_e('Something went wrong. Try again.', 'boilerplate');
    ?>
  </div>
<?php endif; ?>
```

### Handler — Opción B (fetch + JSON)

Solo si quieres feedback in-page. Implementación más larga; **NO recomendada para Fase 1** (suma JS al footer y complica el INP). Si se decide, recordar:

- Validación HTML5 sincrónica ANTES del fetch (`form.checkValidity()`) → INP no se ve afectado por la red
- `fetch()` con `signal` de un AbortController por si el usuario reenvía rápido
- Spinner CSS, no JS animación — menos main-thread

### Fallback noscript

```html
<noscript>
  <p class="footer-subscribe__noscript">
    <?php esc_html_e('JavaScript is disabled. Email us at', 'boilerplate'); ?>
    <a href="mailto:hello@kresna.com">hello@kresna.com</a>
    <?php esc_html_e('to subscribe.', 'boilerplate'); ?>
  </p>
</noscript>
```

(En realidad el form de Opción A funciona sin JS, así que el noscript es redundante. Pero NO hace daño y mejora la confianza.)

---

## 5. Internal linking patterns del footer

El footer renderiza en CADA página del sitio → es el **mayor amplificador de PageRank interno** que tiene el tema. Cada link en el footer recibe equity de todas las páginas indexadas.

### Reglas

1. **Pillar pages first**. Los slots prime del footer (primeros 3-4 links de "Navigation") deben apuntar a:
   - Pricing
   - How it works / Features
   - Use cases / Solutions
   - NO a "Contact us" (utility — bajo valor SEO) ni "Privacy" (boilerplate — desperdicia equity)

2. **Anchor text descriptivo**. Los menús de WordPress permiten editar el texto visible: úsalo. "Sales automation features" >>> "Features". Pero no te pases — el footer es scanneable, los anchors muy largos rompen visual.

3. **NO `nofollow` en links internos**. PageRank sculpting con `nofollow` dejó de funcionar en 2009 — Google sigue contando el link pero no lo persigue, evaporando equity. Para Privacy/Terms: dejar `dofollow`. Si no quieres que aparezcan tan prominentes, ponlos en una fila secundaria con tipografía más pequeña, no `nofollow`.

4. **`rel="me"` en sociales — refuerza identidad cruzada**. Las URLs en `Organization.sameAs` del JSON-LD (§1) deben coincidir con `<a rel="me">` en los iconos del footer. Esto da una señal redundante de propiedad del perfil.

### Cómo añadir `rel="me"` SIN parchear `wp_nav_menu()`

`frontend-lead` planea usar `wp_nav_menu()` con `theme_location => 'footer_social'` (o equivalente). Filter `nav_menu_link_attributes`:

```php
// inc/menus.php (al final)
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    if (empty($args->theme_location) || $args->theme_location !== 'footer_social') {
        return $atts;
    }

    $url = isset($atts['href']) ? $atts['href'] : '';
    $social_hosts = array(
        'discord.com', 'discord.gg',
        'twitter.com', 'x.com',
        'linkedin.com',
        'github.com',
    );

    foreach ($social_hosts as $host) {
        if (stripos($url, $host) !== false) {
            // rel="me" + noopener para target=_blank
            $existing_rel = isset($atts['rel']) ? $atts['rel'] : '';
            $atts['rel']    = trim($existing_rel . ' me noopener');
            $atts['target'] = '_blank';
            break;
        }
    }

    return $atts;
}, 10, 3);
```

Si los iconos sociales NO se renderizan con `wp_nav_menu()` (sino que `frontend-lead` los hardcodea desde Customizer fields), aplicar `rel="me" target="_blank" rel="noopener"` directamente en el template del footer.

### Tabla de prioridades para el cliente (compartir con UI/PO)

| Slot footer | Prioridad SEO | Justificación |
|---|---|---|
| Navigation column 1, link 1 | ALTA | Equity máxima |
| Subscribe form | MEDIA | Conversión, no SEO |
| Iconos sociales | MEDIA | Identidad (`rel="me"`) — no transmite equity (`target="_blank"`) |
| Privacy / Terms | BAJA | Link obligatorio legal |
| Copyright text | NULA | No link; texto |

---

## 6. Performance / Core Web Vitals del footer

### Watermark `getBBox()` resize handler

`getBBox()` fuerza un layout reflow del SVG. En un resize handler sin throttle, esto se ejecuta decenas de veces por segundo → INP regression visible especialmente en mobile orientation change.

Solución (vanilla, sin librerías):

```js
// src/footerWatermark.js
function debounce(fn, wait) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn.apply(null, args), wait);
  };
}

function recalcWatermark() {
  const svg = document.querySelector('.footer-watermark svg');
  if (!svg) return;
  const text = svg.querySelector('text');
  if (!text) return;
  const bbox = text.getBBox(); // forzar layout
  svg.setAttribute('viewBox', `${bbox.x} ${bbox.y} ${bbox.width} ${bbox.height}`);
}

// Inicial: usar rAF para asegurar que el SVG está pintado
requestAnimationFrame(recalcWatermark);

// Resize: debounce 150ms (suficiente para mobile orientation change ~300ms)
window.addEventListener('resize', debounce(recalcWatermark, 150), { passive: true });
```

Alternativa más moderna: `ResizeObserver` sobre el wrapper, también con debounce o flag `requestAnimationFrame`. Cualquiera sirve; lo crítico es **no llamar `getBBox()` síncronamente en cada `resize` event**.

### Google Fonts — `font-display: swap` y preload selectivo

Estado actual en `inc/libraries.php` líneas 14-20:

```php
wp_enqueue_style(
    'google-fonts',
    'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;600;700&display=swap',
    ...
);
```

Bien: `display=swap` ya está. PERO: las families son las legacy (Open Sans + Poppins). El design system Kresna pide DM Sans + Caveat. `frontend-lead` debería actualizar a:

```php
wp_enqueue_style(
    'google-fonts',
    'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Caveat:wght@400;700&display=swap',
    array(),
    null
);
```

#### Preload selectivo

Añadir en `inc/seo.php` (donde ya están los preconnect):

```php
add_action('wp_head', function () {
  // Preconnect ya existe.
  // Preload SOLO los pesos críticos above-the-fold.
  // DM Sans 400 (body) + 700 (display H1/H2). Caveat NO preload — accent.
  $base = 'https://fonts.gstatic.com/s/dmsans/v16/'; // verificar URL exacta inspeccionando el CSS de Google Fonts
  echo '<link rel="preload" as="font" type="font/woff2" crossorigin '
       . 'href="' . esc_url($base . 'rP2Hp2ywxg089UriCZ2IHTcEkMfvk1zE6JtV.woff2') . '">' . "\n";
}, 1);
```

**Importante**: la URL exacta del woff2 cambia con cada versión de Google Fonts. Para evitar mantenimiento, hay dos caminos:

- **Self-host** (mejor performance, más mantenimiento): bajar los `.woff2` con https://gwfh.mranftl.com/, servir desde `/fonts/`, declarar `@font-face` propio. Elimina la dependencia de `fonts.googleapis.com` (1 round-trip menos).
- **Aceptar Google Fonts y NO preload el woff2** (más simple, ~50ms peor LCP en first-paint). El `display=swap` minimiza el FOUT.

Para Fase 1, **no preload del woff2 si no quieres mantener la URL**. Mantén el preconnect (ya está). Decisión: ROI bajo para Fase 1, escalable a Fase 2 cuando se decida self-host.

### LCP del footer

El footer típicamente NO es el elemento LCP de la página (está abajo, fuera del viewport inicial). Excepciones:

- Single-page muy corto (landing de un párrafo) → footer puede entrar en viewport inicial → poster del vídeo sería LCP
- Pages con scroll instantáneo a anchor del footer

En cualquier caso, asegúrate de que el `poster` del vídeo es:
- WebP o JPEG optimizado (< 80KB)
- Mismas dimensiones que el `<video>` (evitar CLS)
- No tiene `loading="lazy"` (si entra en viewport inicial, lazy lo penaliza)

---

## 7. Accesibilidad mínima del footer

### Checklist semántica

- `<footer role="contentinfo">` — **ya está** en el footer.php actual. Asegúrate de que NO se duplique (un solo `contentinfo` por página). Si Gutenberg block-themes meten otro `<footer>`, retirar `role`.
- `<nav aria-label="Footer navigation">` y `<nav aria-label="Company links">` — labels distintos por cada `<nav>`. SR como NVDA listan los landmarks; sin label se llaman "navigation" y "navigation" (confuso).
- Skip link "Skip to footer" — opcional, solo si el sitio ya tiene una skip-link strategy.
- `<input>` del subscribe → `<label>` (puede ser `sr-only`, ya cubierto en §4).
- Lucky cube + watermark → `aria-hidden="true"`.
- Iconos sociales: si son `<svg>` sin texto visible, `aria-label` en el `<a>`:
  ```html
  <a href="https://discord.gg/..." rel="me noopener" target="_blank" aria-label="Discord">
    <svg aria-hidden="true" focusable="false">…</svg>
  </a>
  ```
- Reduced motion → vídeo pausado/oculto + transitions reducidas:
  ```css
  @media (prefers-reduced-motion: reduce) {
    .footer-video-bg { display: none; }
    .footer-cube { animation: none !important; }
    *, *::before, *::after {
      transition-duration: 0.01ms !important;
      animation-duration: 0.01ms !important;
    }
  }
  ```

### Contraste de texto del footer

El footer Kresna lleva texto blanco/claro sobre fondo oscuro (vídeo). Verificar que el texto del subscribe label, los nav links y el copyright tengan contraste ≥ 4.5:1 contra el frame **más claro** del vídeo (no contra el frame promedio). Si el vídeo tiene momentos brillantes, añadir un overlay:

```css
.footer-video-bg::after {
  content: "";
  position: absolute; inset: 0;
  background: linear-gradient(180deg, rgb(0 0 0 / 0.45), rgb(0 0 0 / 0.65));
}
```

(Idealmente el overlay es un `<div>` separado encima del `<video>`, no un `::after` del vídeo, porque elementos replaced no soportan pseudo-elementos en todos los navegadores.)

---

## 8. Quick wins SEO existentes (fuera del scope estricto del footer)

Detectados leyendo `inc/seo.php` y `inc/schema.php`. No bloquean Fase 1, pero son arreglos baratos que el `frontend-lead` puede aplicar de paso:

1. **Canonical sin paginación correcta** (`inc/seo.php` línea 19): si la página NO es singular ni front_page, usa `get_pagenum_link(get_query_var('paged'))`. Esto da string vacía en muchas archives raíz → canonical roto. Mejor:
   ```php
   } else {
     $canonical = home_url(add_query_arg(null, null));
   }
   ```
2. **Meta description vacía en archives** (categoría, tag, búsqueda): no se emite ninguna. Añadir fallback:
   ```php
   } elseif (is_category() || is_tag() || is_tax()) {
     $description = term_description() ?: get_bloginfo('description');
   }
   ```
3. **Falta `og:image` en home** (`inc/seo.php` línea 41): solo se emite si `is_singular() && has_post_thumbnail()`. Para home y archives, queda sin imagen → previews pobres. Fallback: usar `brand_img-revert` Customizer.
4. **JSON-LD `Article.image` sin `width`/`height`** (`inc/schema.php` línea 134): Google recomienda `width` y `height` para `ImageObject`. Añadir `wp_get_attachment_image_src()` y poblar.
5. **`Organization.logo` sin dimensiones**: idem (línea 19).

Estos quick wins son 30-60 minutos de trabajo y mejoran rich results / OG cards instantáneamente.

---

## Top recommendations — apply before closing Phase 1

> Frontend-lead: aplica estos sí o sí antes de cerrar Fase 1. El resto del documento es contexto para entender el "por qué".

- **Add `Organization` JSON-LD `sameAs`** populado desde `kresna_social_*_url` Customizer fields (diff exacto en §1). Validar en Rich Results Test.
- **`<video preload="metadata" poster="..." playsinline aria-hidden="true">`** — NO `preload="auto"`. Optar por Opción A de §2 para Fase 1.
- **`aria-hidden="true"` + `role="presentation"` + `focusable="false"`** en el `<svg>` Y wrapper del watermark "Kresna" (§3). Evita ruido SEO + a11y warnings.
- **`<footer role="contentinfo">` (ya está) + `<nav aria-label="Footer navigation">` y `<nav aria-label="Company links">`** distintos por cada nav del footer (§7).
- **Watermark resize handler con debounce 150ms** (snippet en §6) — sin esto, INP regression en mobile rotation.
- **Subscribe form**: `<input type="email" name="email" required autocomplete="email" inputmode="email">` + `<label class="sr-only">` + `<button type="submit" aria-label="...">` + `aria-live="polite"` div para mensajes (§4). Handler PHP via `admin-post.php` (Opción A).
- **`rel="me" target="_blank" rel="noopener"`** en los 4 iconos sociales — coincidiendo con las URLs del `Organization.sameAs`. Filter `nav_menu_link_attributes` en §5 si vienen de `wp_nav_menu()`.
- **Google Fonts a DM Sans + Caveat** con `display=swap` (ya soportado). Preload de woff2 NO requerido en Fase 1 (decidir self-hosting en Fase 2).

---

## Resumen ejecutivo (3-5 líneas para el humano)

**Top 3 issues detectados**:
1. El footer Kresna con `<video preload="auto">` literal del spec mete 5+MB de descarga en el primer paint → killer de TTI/INP. Trivial de arreglar (`preload="metadata"` + `poster`).
2. El `Organization.sameAs` actual está hardcoded a 3 redes legacy en `wp_options`; el diff propuesto lo migra a las 4 sociales Kresna desde Customizer y mantiene fallback compat.
3. El watermark `<text>Kresna</text>` SVG sin `aria-hidden` + `role="presentation"` puede inflar keyword density y aparecer en screen readers — riesgo bajo pero cero coste de mitigar.

**Quick win extra (fuera del footer)**: el canonical de archives en `inc/seo.php` está roto cuando no hay paginación; 2 líneas para arreglarlo.

**Confianza**: ALTA. Todos los snippets son aplicables sin fricción al stack actual (Vite + ACF + Customizer + Tailwind v4) y respetan las invariantes de `CLAUDE.md` (escape de output, vanilla JS, no romper la pipeline Vite). El único item con dependencia externa es la URL exacta del woff2 de DM Sans para preload — por eso lo dejé como opcional en Fase 1.
