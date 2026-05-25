<?php
/**
 * Chrome JLB — invocado vía get_header('jlb') desde front-page.php
 * (y cualquier otra plantilla que use el diseño Jean Le Boulch).
 *
 * Mantiene la cabecera Figma (sticky con nav horizontal) en paralelo
 * al header.php "Kresna" del boilerplate.
 *
 * Los enlaces del nav se intentan resolver con wp_get_nav_menu_items()
 * desde el menú "menu_principal"; si no hay menú asignado, cae al set
 * estático del Figma.
 */

$asset_base = get_template_directory_uri() . '/assets/figma-home/';
$asset = function ($file) use ($asset_base) {
    return esc_url($asset_base . ltrim($file, '/'));
};

$logo_id  = function_exists('get_field') ? get_field('logo_header', 'option') : 0;
$logo_url = $logo_id ? wp_get_attachment_url($logo_id) : $asset('logo.svg');

// Items del nav.
// Estructura soportada:
//   ['label' => ..., 'url' => ..., 'target' => '_self|_blank', 'children' => [ ... ]]
//
// Si el cliente asigna un menú "menu_principal" en wp-admin, se construye la
// jerarquía a partir de `menu_item_parent`. Si no, cae al menú Figma estático.
$nav_items = array();

if (has_nav_menu('menu_principal')) {
    $locations = get_nav_menu_locations();
    if (!empty($locations['menu_principal'])) {
        $menu_items = wp_get_nav_menu_items($locations['menu_principal']);
        if (is_array($menu_items) && !empty($menu_items)) {
            $by_id = array();
            foreach ($menu_items as $item) {
                $by_id[(int) $item->ID] = array(
                    'label'    => $item->title,
                    'url'      => $item->url,
                    'target'   => $item->target ?: '_self',
                    'children' => array(),
                    '_parent'  => (int) $item->menu_item_parent,
                );
            }
            foreach ($by_id as $id => $node) {
                if ($node['_parent'] && isset($by_id[$node['_parent']])) {
                    $by_id[$node['_parent']]['children'][] = $node;
                }
            }
            foreach ($by_id as $node) {
                if (!$node['_parent']) {
                    unset($node['_parent']);
                    $nav_items[] = $node;
                }
            }
        }
    }
}

// Fallback estático — enlaces del Figma con submenú en "Niveles".
if (empty($nav_items)) {
    $nav_items = array(
        array('label' => __('Nuestro colegio', 'boilerplate'),          'url' => '#colegio',     'target' => '_self'),
        array(
            'label'    => __('Niveles', 'boilerplate'),
            'url'      => '#niveles',
            'target'   => '_self',
            'children' => array(
                array('label' => __('Inicial', 'boilerplate'),     'url' => '#inicial',     'target' => '_self'),
                array('label' => __('Primaria', 'boilerplate'),    'url' => '#primaria',    'target' => '_self'),
                array('label' => __('Secundaria', 'boilerplate'),  'url' => '#secundaria',  'target' => '_self'),
                array('label' => __('Bachiller', 'boilerplate'),   'url' => '#bachiller',   'target' => '_self'),
            ),
        ),
        array('label' => __('Talleres', 'boilerplate'),                 'url' => '#talleres',     'target' => '_self'),
        array('label' => __('Experiencias innovadoras', 'boilerplate'), 'url' => '#experiencias', 'target' => '_self'),
        array('label' => __('Blog', 'boilerplate'),                     'url' => '#blog',         'target' => '_self'),
        array('label' => __('Open Day', 'boilerplate'),                 'url' => '#open-day',     'target' => '_self'),
        array('label' => __('Intranet', 'boilerplate'),                 'url' => '#intranet',     'target' => '_self'),
        array('label' => __('Admisión', 'boilerplate'),                 'url' => '#admision',     'target' => '_self'),
    );
}

// Añade la clase jlb-home-template al body sin tocar templates.
// También expone el slug de la página (jlb-page-<slug>) para scopear estilos
// por página sin acoplar los módulos compartidos (p.ej. color del botón del hero).
add_filter('body_class', function ($classes) {
    if (!in_array('jlb-home-template', $classes, true)) {
        $classes[] = 'jlb-home-template';
    }
    if (is_page()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && $post->post_name) {
            $classes[] = 'jlb-page-' . sanitize_html_class($post->post_name);
        }
    }
    return $classes;
});
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo esc_url(site_icon_url()); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link sr-text" href="#contenido"><?php esc_html_e('Skip to content', 'boilerplate'); ?></a>

    <header class="jlb-header" role="banner">
        <div class="jlb-header__inner">
            <a class="jlb-header__logo" href="<?php echo esc_url(home_url('/')); ?>"
                aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img src="<?php echo esc_url($logo_url); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                    width="311" height="61">
            </a>

            <nav class="jlb-header__nav" aria-label="<?php esc_attr_e('Navegación principal', 'boilerplate'); ?>">
                <?php foreach ($nav_items as $item):
                    $has_children = !empty($item['children']);
                ?>
                    <div class="jlb-header__nav-item<?php echo $has_children ? ' jlb-header__nav-item--has-children' : ''; ?>">
                        <a href="<?php echo esc_url($item['url']); ?>"
                            target="<?php echo esc_attr($item['target']); ?>"
                            <?php echo $item['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                            <?php if ($has_children): ?>aria-haspopup="true"<?php endif; ?>>
                            <?php echo esc_html($item['label']); ?>
                            <?php if ($has_children): ?>
                                <svg class="jlb-header__nav-caret" width="10" height="6" viewBox="0 0 10 6" aria-hidden="true">
                                    <path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            <?php endif; ?>
                        </a>

                        <?php if ($has_children): ?>
                            <div class="jlb-header__nav-submenu" role="menu">
                                <?php foreach ($item['children'] as $child): ?>
                                    <a role="menuitem"
                                        href="<?php echo esc_url($child['url']); ?>"
                                        target="<?php echo esc_attr($child['target']); ?>"
                                        <?php echo $child['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                                        <?php echo esc_html($child['label']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>

            <button
                type="button"
                class="jlb-header__toggle"
                aria-controls="jlb-mobile-nav"
                aria-expanded="false"
                aria-label="<?php esc_attr_e('Abrir menú', 'boilerplate'); ?>"
                data-jlb-menu-toggle>
                <span class="jlb-header__toggle-bars" aria-hidden="true">
                    <span></span><span></span><span></span>
                </span>
            </button>
        </div>
    </header>

    <div
        class="jlb-mobile-nav"
        id="jlb-mobile-nav"
        role="dialog"
        aria-modal="true"
        aria-label="<?php esc_attr_e('Navegación principal', 'boilerplate'); ?>"
        aria-hidden="true"
        hidden
        data-jlb-menu>
        <div class="jlb-mobile-nav__backdrop" data-jlb-menu-close></div>

        <div class="jlb-mobile-nav__panel" role="document">
            <div class="jlb-mobile-nav__head">
                <a class="jlb-mobile-nav__logo" href="<?php echo esc_url(home_url('/')); ?>"
                    aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <img src="<?php echo esc_url($logo_url); ?>"
                        alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                        width="311" height="61">
                </a>

                <button
                    type="button"
                    class="jlb-mobile-nav__close"
                    aria-label="<?php esc_attr_e('Cerrar menú', 'boilerplate'); ?>"
                    data-jlb-menu-close>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <nav class="jlb-mobile-nav__list" aria-label="<?php esc_attr_e('Navegación principal', 'boilerplate'); ?>">
                <?php foreach ($nav_items as $i => $item):
                    $has_children = !empty($item['children']);
                    $sub_id = 'jlb-submenu-' . $i;
                ?>
                    <div class="jlb-mobile-nav__row<?php echo $has_children ? ' jlb-mobile-nav__row--has-children' : ''; ?>"
                        style="--jlb-mobile-nav-i: <?php echo (int) $i; ?>">
                        <a class="jlb-mobile-nav__link"
                            href="<?php echo esc_url($item['url']); ?>"
                            target="<?php echo esc_attr($item['target']); ?>"
                            <?php echo $item['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                            data-jlb-menu-link>
                            <span><?php echo esc_html($item['label']); ?></span>
                            <?php if (!$has_children): ?>
                                <span class="jlb-mobile-nav__arrow" aria-hidden="true">&rarr;</span>
                            <?php endif; ?>
                        </a>

                        <?php if ($has_children): ?>
                            <button type="button"
                                class="jlb-mobile-nav__expand"
                                aria-expanded="false"
                                aria-controls="<?php echo esc_attr($sub_id); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Mostrar opciones de %s', 'boilerplate'), $item['label'])); ?>"
                                data-jlb-submenu-toggle>
                                <svg width="14" height="9" viewBox="0 0 14 9" aria-hidden="true">
                                    <path d="M1 1l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($has_children): ?>
                        <div class="jlb-mobile-nav__submenu" id="<?php echo esc_attr($sub_id); ?>" data-jlb-submenu>
                            <div class="jlb-mobile-nav__submenu-inner">
                                <?php foreach ($item['children'] as $ci => $child): ?>
                                    <a class="jlb-mobile-nav__sublink"
                                        href="<?php echo esc_url($child['url']); ?>"
                                        target="<?php echo esc_attr($child['target']); ?>"
                                        <?php echo $child['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                        style="--jlb-mobile-sub-i: <?php echo (int) $ci; ?>"
                                        data-jlb-menu-link>
                                        <span class="jlb-mobile-nav__bullet" aria-hidden="true"></span>
                                        <?php echo esc_html($child['label']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="jlb-mobile-nav__foot">
                <a href="mailto:informes@jlb.edu.pe">informes@jlb.edu.pe</a>
                <a href="https://api.whatsapp.com/send/?phone=51976369407" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Admisión por WhatsApp', 'boilerplate'); ?>
                </a>
            </div>
        </div>
    </div>

    <main id="contenido" class="jlb-home">
