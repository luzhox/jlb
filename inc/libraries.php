<?php
/**
 * Enqueue de assets — modo dual Vite / Webpack fallback.
 *
 * MODO NORMAL (default):    carga desde build/.vite/manifest.json
 * MODO VITE DEV SERVER:     define('VITE_DEV_SERVER', true) en wp-config.php
 *                           + ejecutar: npm run dev
 */

// ── Resource hints: preconnect + preload de fuentes críticas ──────────────────
// Reduce el RTT de Google Fonts (CSS) y precarga los pesos más usados de DM Sans.
add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
        );
    }
    return $urls;
}, 10, 2);

// Preload de WOFF2 de DM Sans: deshabilitado.
//
// Las URLs hash-based del CDN de Google Fonts (rP2Hp2..., rP2Cp2...) rotan
// con cada versión interna de la familia y generaban 404 en consola, sin
// aportar la mejora de LCP esperada (el browser descarta el preload no
// matched). El CSS de Google Fonts ya carga las fuentes con `font-display: swap`,
// que es suficiente para evitar FOIT.
//
// Si en el futuro se quiere preload real, hay 3 caminos:
//   1) Self-hosting de DM Sans en /assets/fonts/ con URLs estables.
//   2) Usar plugin como wp-google-fonts-pull para mirror local.
//   3) Parsear el CSS de Google Fonts en runtime y extraer las URLs activas
//      (frágil, costoso en TTFB).

// ── Fuente self-hosted: "KG Second Chances Solid" (principal del cliente) ─────
// NO está en Google Fonts y requiere licencia comercial (Kimberly Geswein), por
// eso se self-hostea desde /fonts/. Los 16 parciales SCSS la nombran con
//   font-family: "KG Second Chances Solid", "Caveat", cursive
// así que mientras el archivo no exista cae a Caveat (sí cargada por GFonts) y
// NO se rompe nada. En cuanto sueltes el .woff2 en /fonts/ (ver fonts/README.md)
// este bloque lo detecta, declara el @font-face con font-display:swap y precarga
// el woff2 para evitar el flash a Caveat. Se hace en PHP (no en el bundle Vite)
// para que: (1) el build nunca falle por un url() a un archivo ausente, (2) la
// URL sea siempre correcta vía get_template_directory_uri(), (3) se auto-active
// sin recompilar. Antes solo se veía en local porque la fuente estaba instalada
// en el SO del diseñador; en producción nadie la tiene → caía a Caveat/cursive.
add_action('wp_head', function () {
    $dir = get_template_directory() . '/fonts';
    $uri = get_template_directory_uri() . '/fonts';

    // Variantes soportadas, en orden de preferencia. format() según extensión.
    $variants = array(
        'KGSecondChancesSolid.woff2' => 'woff2',
        'KGSecondChancesSolid.woff'  => 'woff',
        'KGSecondChancesSolid.ttf'   => 'truetype',
        'KGSecondChancesSolid.otf'   => 'opentype',
    );

    $sources = array();   // format => url
    foreach ($variants as $filename => $format) {
        $path = $dir . '/' . $filename;
        if (file_exists($path)) {
            // ?v=filemtime → cache-busting estable (cambia solo si cambia el archivo)
            $sources[$format] = esc_url($uri . '/' . $filename . '?v=' . filemtime($path));
        }
    }

    if (empty($sources)) {
        return; // archivo aún no subido → fallback a Caveat, sin romper nada
    }

    // Preload del woff2 para que el kicker no parpadee (KG aparece en todo el sitio).
    // crossorigin obligatorio: las fuentes se descargan siempre en modo CORS.
    if (isset($sources['woff2'])) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            $sources['woff2']
        );
    }

    // src: woff2 → woff → ttf → otf (lo que exista, en ese orden).
    $src_parts = array();
    foreach (array('woff2', 'woff', 'truetype', 'opentype') as $format) {
        if (isset($sources[$format])) {
            $src_parts[] = "url('" . $sources[$format] . "') format('" . $format . "')";
        }
    }

    echo '<style id="jlb-kg-font">';
    echo "@font-face{font-family:'KG Second Chances Solid';";
    echo 'src:' . implode(',', $src_parts) . ';';
    echo 'font-weight:400;font-style:normal;font-display:swap;}';
    echo "</style>\n";
}, 2);

// ── @vite/client en dev mode ──────────────────────────────────────────────────
// MUY IMPORTANTE: este add_action debe estar al top-level del archivo, NO
// anidado dentro de un callback de wp_enqueue_scripts. Si se registra desde
// dentro de wp_enqueue_scripts (que corre durante wp_head, prioridad ~10),
// la prioridad 1 ya pasó y el callback nunca se dispara. Sin @vite/client
// los CSS importados desde main.js (Tailwind v4) no se inyectan en el DOM.
add_action('wp_head', function () {
    if (bp_is_vite_dev()) {
        echo '<script type="module" src="' . esc_url(VITE_HOST . '/@vite/client') . '"></script>' . "\n";
    }
}, 1);

add_action('wp_enqueue_scripts', function () {
    $theme_ver = wp_get_theme()->get('Version');
    $is_dev    = bp_is_vite_dev();

    // ── Google Fonts: DM Sans + Raleway + DM Serif + Caveat ──────────────────
    // Pesos cargados:
    //   DM Sans  400 / 500 / 600 / 700  (body, labels, headings)
    //   Caveat   500 / 600 / 700        (manuscrito — solo 3 sitios autorizados)
    // display=swap para evitar FOIT y mejorar LCP
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Raleway:wght@400;600;700&family=DM+Serif+Display:ital@0;1&family=Caveat:wght@500;600;700&display=swap',
        array(),
        null
    );

    // ── AOS CSS (vendor, no bundleado) ───────────────────────────────────────
    wp_enqueue_style('aos', get_template_directory_uri() . '/styles/css/aos.css', array(), $theme_ver);

    if ($is_dev) {
        // ── MODO DESARROLLO: Vite dev server ─────────────────────────────────
        // NOTA: el <script type="module" src=".../@vite/client"></script> se
        // inyecta en wp_head desde el add_action top-level de este archivo
        // (más abajo, fuera de wp_enqueue_scripts). Anidarlo aquí provocaba
        // que se registrase DESPUÉS de que wp_head ya hubiera pasado la
        // prioridad 1, y el callback nunca se disparaba → sin @vite/client
        // los `import './main.css'` de main.js no inyectan estilos al DOM.
        wp_enqueue_script('vite-main', VITE_HOST . '/src/main.js', array(), null, true);
        bp_vite_module_tag('vite-main');

    } else {
        // ── MODO PRODUCCIÓN: assets del manifest de Vite ─────────────────────
        $css_url = bp_vite_css('src/main.js');
        if ($css_url) {
            wp_enqueue_style('vite-style', $css_url, array(), null);
        }

        $js_url = bp_vite_asset('src/main.js');
        if ($js_url) {
            wp_enqueue_script('vite-main', $js_url, array('jquery'), null, true);
            bp_vite_module_tag('vite-main');
        } else {
            // Fallback: bundle Webpack si no existe manifest de Vite
            wp_enqueue_style('estilos', get_template_directory_uri() . '/build/css/main.css', array(), $theme_ver);
            wp_enqueue_script('main', get_template_directory_uri() . '/build/js/main.js', array('jquery'), $theme_ver, true);
        }
    }

    // ── jQuery siempre disponible ─────────────────────────────────────────────
    wp_enqueue_script('jquery');

    // ── AOS JS ───────────────────────────────────────────────────────────────
    wp_enqueue_script('aos', get_template_directory_uri() . '/vendors/aos.js', array(), $theme_ver, true);

    // ── Colorbox — solo en singulares ─────────────────────────────────────────
    if (is_singular()) {
        wp_enqueue_script(
            'colorbox',
            get_template_directory_uri() . '/vendors/jquery.colorbox-min.js',
            array('jquery'),
            $theme_ver,
            true
        );
    }
});
