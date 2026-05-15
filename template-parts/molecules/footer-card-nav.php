<?php
/**
 * Molecule · Footer card nav (Kresna)
 *
 * Card derecha del footer:
 *   - Lucky cube flotante (esquina sup-derecha)
 *   - "Feeling lucky?" manuscrito apuntando al cube
 *   - 2 columnas de navegación (Navigation, Company) — wp_nav_menu con
 *     fallback a links default del spec
 *   - Bloque CTA + subscribe form
 *   - Copyright
 *
 * Vars del Customizer:
 *   kresna_lucky_text, kresna_cta_heading, kresna_cta_strong,
 *   kresna_subscribe_placeholder, kresna_subscribe_button_label,
 *   kresna_copyright
 *
 * Menus (locations registradas en inc/menus.php):
 *   footer_navigation, footer_company  → fallback inline si no asignados.
 */

$lucky_text   = bp_kresna_get('kresna_lucky_text');
$cta_heading  = bp_kresna_get('kresna_cta_heading');
$cta_strong   = bp_kresna_get('kresna_cta_strong');
$sub_placeholder = bp_kresna_get('kresna_subscribe_placeholder');
$sub_button_label = bp_kresna_get('kresna_subscribe_button_label');
$copyright    = bp_kresna_get('kresna_copyright');

// Render del CTA heading: si el "strong" existe en el heading lo envolvemos en <strong>.
// Soporta saltos de línea (textarea).
$cta_lines = array_map('esc_html', explode("\n", $cta_heading));
$cta_html  = implode('<br>', $cta_lines);
if ($cta_strong !== '' && strpos($cta_heading, $cta_strong) !== false) {
    $cta_html = str_replace(
        esc_html($cta_strong),
        '<strong class="footer-card-nav__cta-strong">' . esc_html($cta_strong) . '</strong>',
        $cta_html
    );
}

// Fallback inline para los menus si no están asignados.
$default_navigation = array(
    array('label' => __('How it works', 'boilerplate'), 'url' => '#how-it-works'),
    array('label' => __('Features',     'boilerplate'), 'url' => '#features'),
    array('label' => __('Pricing',      'boilerplate'), 'url' => '#pricing'),
    array('label' => __('Testimonials', 'boilerplate'), 'url' => '#testimonials'),
    array('label' => __('FAQ',          'boilerplate'), 'url' => '#faq'),
);
$default_company = array(
    array('label' => __('About',     'boilerplate'), 'url' => '#about'),
    array('label' => __('Blog',      'boilerplate'), 'url' => '#blog'),
    array('label' => __('Careers',   'boilerplate'), 'url' => '#careers'),
    array('label' => __('Contact',   'boilerplate'), 'url' => '#contact'),
);

/**
 * Render fallback list para menus footer.
 *
 * @param array{label:string,url:string}[] $items
 */
$render_fallback_menu = function ($items) {
    echo '<ul class="footer-card-nav__menu" role="list">';
    foreach ($items as $item) {
        printf(
            '<li><a href="%1$s">%2$s</a></li>',
            esc_url($item['url']),
            esc_html($item['label'])
        );
    }
    echo '</ul>';
};

$nav_menu_args = array(
    'menu_class'     => 'footer-card-nav__menu',
    'container'      => false,
    'depth'          => 1,
    'fallback_cb'    => false,
);
?>
<article class="footer-card-nav" aria-labelledby="footer-card-nav-cta">
    <!-- Lucky cube flotante -->
    <div class="footer-card-nav__cube-slot">
        <?php
        get_template_part('template-parts/atoms/lucky-cube', null, array(
            'size'     => 'md',
            'mark'     => 'K',
            'rotation' => -10,
        ));
        ?>
        <?php if ($lucky_text): ?>
            <span class="footer-card-nav__lucky" aria-hidden="true">
                <?php echo esc_html($lucky_text); ?>
                <svg viewBox="0 0 48 32" class="footer-card-nav__lucky-arrow" aria-hidden="true">
                    <path d="M2 4 C 18 6, 30 14, 38 22 M 32 22 L 38 22 L 38 16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        <?php endif; ?>
    </div>

    <!-- Columnas de navegación -->
    <div class="footer-card-nav__columns">
        <nav class="footer-card-nav__col" aria-label="<?php echo esc_attr__('Footer navigation', 'boilerplate'); ?>">
            <h3 class="footer-card-nav__col-title"><?php esc_html_e('Navigation', 'boilerplate'); ?></h3>
            <?php
            if (has_nav_menu('footer_navigation')) {
                wp_nav_menu(array_merge($nav_menu_args, array('theme_location' => 'footer_navigation')));
            } else {
                $render_fallback_menu($default_navigation);
            }
            ?>
        </nav>
        <nav class="footer-card-nav__col" aria-label="<?php echo esc_attr__('Company links', 'boilerplate'); ?>">
            <h3 class="footer-card-nav__col-title"><?php esc_html_e('Company', 'boilerplate'); ?></h3>
            <?php
            if (has_nav_menu('footer_company')) {
                wp_nav_menu(array_merge($nav_menu_args, array('theme_location' => 'footer_company')));
            } else {
                $render_fallback_menu($default_company);
            }
            ?>
        </nav>
    </div>

    <!-- CTA + Subscribe -->
    <div class="footer-card-nav__cta">
        <?php if ($cta_heading): ?>
            <p id="footer-card-nav-cta" class="footer-card-nav__cta-heading">
                <?php echo $cta_html; // ya escapado arriba ?>
            </p>
        <?php endif; ?>

        <form
            class="footer-card-nav__subscribe"
            action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
            method="post"
            novalidate
            aria-label="<?php echo esc_attr__('Newsletter subscription', 'boilerplate'); ?>"
        >
            <input type="hidden" name="action" value="kresna_subscribe">
            <?php wp_nonce_field('kresna_subscribe', 'kresna_subscribe_nonce'); ?>

            <?php
            get_template_part('template-parts/atoms/input', null, array(
                'name'          => 'email',
                'type'          => 'email',
                'aria_label'    => __('Email address', 'boilerplate'),
                'placeholder'   => $sub_placeholder,
                'required'      => true,
                'autocomplete'  => 'email',
                'wrapper_class' => 'footer-card-nav__subscribe-field',
            ));
            ?>

            <?php
            get_template_part('template-parts/atoms/button', null, array(
                'tag'     => 'button',
                'type'    => 'submit',
                'label'   => $sub_button_label,
                'variant' => 'kresna-dark',
                'size'    => 'md',
                'class'   => 'footer-card-nav__subscribe-btn',
            ));
            ?>

            <div class="footer-card-nav__subscribe-status sr-text" role="status" aria-live="polite" aria-atomic="true"></div>
        </form>
    </div>

    <?php if ($copyright): ?>
        <p class="footer-card-nav__copyright">
            <?php echo esc_html($copyright); ?>
        </p>
    <?php endif; ?>
</article>

<?php
if (!isset($GLOBALS['__bp_footer_card_nav_styled'])) {
    $GLOBALS['__bp_footer_card_nav_styled'] = true;
    ?>
    <style>
        .footer-card-nav {
            position: relative;
            background: var(--color-card-soft);
            color: var(--color-foreground);
            border-radius: var(--radius-2xl);
            padding: 2rem;
            display: grid;
            grid-template-rows: auto 1fr auto auto;
            gap: 2rem;
        }
        .footer-card-nav__cube-slot {
            position: absolute;
            top: -36px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 2;
        }
        .footer-card-nav__lucky {
            font-family: var(--font-handwritten);
            font-size: var(--text-xl);
            line-height: 1;
            color: var(--color-foreground);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transform: translateY(-12px);
        }
        .footer-card-nav__lucky-arrow {
            width: 36px;
            height: 24px;
            color: var(--color-foreground);
        }
        .footer-card-nav__columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            padding-top: 2rem;
        }
        .footer-card-nav__col-title {
            font-family: var(--font-handwritten);
            font-size: var(--text-2xl);
            font-weight: 500;
            margin: 0 0 0.75rem;
            color: var(--color-foreground);
        }
        .footer-card-nav__menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .footer-card-nav__menu a {
            font-family: var(--font-sans);
            font-size: var(--text-sm);
            color: var(--color-muted-foreground);
            text-decoration: none;
            transition: color var(--duration-fast) var(--ease-out);
        }
        .footer-card-nav__menu a:hover,
        .footer-card-nav__menu a:focus-visible {
            color: var(--color-foreground);
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .footer-card-nav__cta-heading {
            font-family: var(--font-display);
            font-size: var(--text-2xl);
            line-height: 1.2;
            margin: 0 0 1rem;
            color: var(--color-muted-foreground);
            font-weight: 400;
        }
        .footer-card-nav__cta-strong {
            color: var(--color-foreground);
            font-weight: 700;
        }
        .footer-card-nav__subscribe {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .footer-card-nav__subscribe-field { flex: 1; margin: 0; }
        .footer-card-nav__copyright {
            margin: 0;
            font-size: var(--text-xs);
            color: var(--color-muted-foreground);
        }
        @media (min-width: 40rem) {
            .footer-card-nav { padding: 2.5rem; }
            .footer-card-nav__subscribe {
                flex-direction: row;
                align-items: stretch;
            }
            .footer-card-nav__subscribe-btn { flex-shrink: 0; }
        }
    </style>
    <?php
}
