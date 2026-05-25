<?php
/**
 * Módulo: jlb_niveles — Grid de niveles educativos.
 *
 * Datos:
 *   titulo  string
 *   items   array of array{ titulo, imagen{url,alt}, link{title,url,target}, wide bool }
 */

$args = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$titulo = $get('titulo', 'Nuestros niveles educativos');
$items = $get('items', array());

if (empty($items) || !is_array($items)) {
    return;
}
?>
<section class="jlb-levels" id="niveles" aria-labelledby="jlb-levels-title">
    <p class="jlb-section-watermark" aria-hidden="true"><?php echo esc_html__('Niveles', 'boilerplate'); ?></p>

    <div class="jlb-container">
        <?php if ($titulo): ?>
            <h2 id="jlb-levels-title" data-gsap="fade-up"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <div class="jlb-levels__grid" data-gsap-batch=".jlb-level-card">
            <?php foreach ($items as $level):
                $level_title = $level['titulo'] ?? '';
                $level_img = $level['imagen'] ?? null;
                $level_link = $level['link'] ?? null;
                $is_wide = !empty($level['wide']);
                $href = !empty($level_link['url']) ? $level_link['url'] : '#';
                $target = $level_link['target'] ?? '_self';
                ?>
                <article class="jlb-level-card<?php echo $is_wide ? ' jlb-level-card--wide' : ''; ?>">
                    <?php if (!empty($level_img['url'])): ?>
                        <img src="<?php echo esc_url($level_img['url']); ?>"
                            alt="<?php echo esc_attr($level_img['alt'] ?? $level_title); ?>" loading="lazy">
                    <?php endif; ?>

                    <?php // Link overlay: toda la card es clickeable. ?>
                    <a class="jlb-level-card__link" href="<?php echo esc_url($href); ?>"
                        target="<?php echo esc_attr($target); ?>" <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                        aria-label="<?php echo esc_attr(sprintf(__('Ver nivel %s', 'boilerplate'), $level_title)); ?>"></a>

                    <div class="jlb-level-card__pill">
                        <p><?php echo esc_html($level_title); ?></p>
                        <span aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="27"
                                viewBox="0 0 30 27" fill="none">
                                <path
                                    d="M0.486257 24.4394C-0.162086 25.0252 -0.162086 25.9748 0.486257 26.5606C1.1346 27.1464 2.1858 27.1464 2.83414 26.5606L18.2622 12.6214L27.0496 20.5606C27.5244 20.9896 28.2385 21.118 28.8588 20.8858C29.4792 20.6536 29.8837 20.1066 29.8837 19.5V1.5C29.8837 0.671581 29.1403 0 28.2235 0H8.30101C7.62951 0 7.02416 0.36546 6.76718 0.925981C6.51023 1.48648 6.65225 2.13166 7.12707 2.56066L15.9143 10.5L0.486257 24.4394Z"
                                    fill="url(#paint0_linear_4232_1333)" />
                                <defs>
                                    <linearGradient id="paint0_linear_4232_1333" x1="14.7348" y1="1.58739" x2="15.1096"
                                        y2="28.0035" gradientUnits="userSpaceOnUse">
                                        <stop offset="0.32" stop-color="#614794" />
                                        <stop offset="1" stop-color="#1580C3" />
                                    </linearGradient>
                                </defs>
                            </svg></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>