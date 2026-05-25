<?php
/**
 * Módulo: jlb_noticias — Listado de noticias destacadas (3 cards).
 *
 * Datos:
 *   titulo   string
 *   items    array of array{ titulo, fecha, etiqueta, imagen{url,alt}, link{url,target} }
 */

$args    = isset($args) && is_array($args) ? $args : array();
$in_flex = function_exists('get_row_layout') && get_row_layout();
$get     = function ($key, $default = '') use ($in_flex, $args) {
    return $in_flex ? get_sub_field($key) : ($args[$key] ?? $default);
};

$titulo = $get('titulo', 'Noticias');
$items  = $get('items', array());

if (empty($items) || !is_array($items)) {
    return;
}
?>
<section class="jlb-news" id="blog" aria-labelledby="jlb-news-title">
    <div class="jlb-container">
        <?php if ($titulo): ?>
            <h2 id="jlb-news-title" data-gsap="fade-up"><?php echo esc_html($titulo); ?></h2>
        <?php endif; ?>

        <div class="jlb-news__grid" data-gsap-batch=".jlb-news-card">
            <?php foreach ($items as $item):
                $item_title    = $item['titulo']   ?? '';
                $item_date     = $item['fecha']    ?? '';
                $item_tag      = $item['etiqueta'] ?? __('Noticias', 'boilerplate');
                $item_img      = $item['imagen']   ?? null;
                $item_link     = $item['link']     ?? null;
                $href          = !empty($item_link['url']) ? $item_link['url'] : '#blog';
                $target        = $item_link['target'] ?? '_self';
            ?>
                <article class="jlb-news-card">
                    <div class="jlb-news-card__media">
                        <?php if (!empty($item_img['url'])): ?>
                            <img src="<?php echo esc_url($item_img['url']); ?>"
                                alt="<?php echo esc_attr($item_img['alt'] ?? $item_title); ?>"
                                loading="lazy">
                        <?php endif; ?>

                        <?php // Flecha roja anclada a la esquina inferior-derecha de la imagen. ?>
                        <span class="jlb-news-card__arrow" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8h9M8.5 3.5L13 8l-4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    <?php if ($item_date): ?>
                        <p class="jlb-news-card__date"><?php echo esc_html(sprintf(__('Publicado el : %s', 'boilerplate'), $item_date)); ?></p>
                    <?php endif; ?>

                    <?php if ($item_tag): ?>
                        <span class="jlb-news-card__tag"><?php echo esc_html($item_tag); ?></span>
                    <?php endif; ?>

                    <?php if ($item_title): ?>
                        <h3 class="jlb-news-card__title"><?php echo esc_html($item_title); ?></h3>
                    <?php endif; ?>

                    <?php // Link overlay: toda la card es clickeable. ?>
                    <a class="jlb-news-card__link"
                        href="<?php echo esc_url($href); ?>"
                        target="<?php echo esc_attr($target); ?>"
                        <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                        aria-label="<?php echo esc_attr(sprintf(__('Leer noticia: %s', 'boilerplate'), $item_title)); ?>"></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
