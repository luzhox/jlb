<?php
/**
 * Molécula: Breadcrumb
 *
 * Uso:
 *   get_template_part('template-parts/molecules/breadcrumb');
 *
 * Genera automáticamente la ruta desde el contexto de WordPress.
 * También genera el structured data BreadcrumbList (si inc/schema.php
 * no lo hace para esa página específica).
 */

if (is_front_page()) return;

$items = [
    ['label' => 'Inicio', 'url' => home_url('/'), 'current' => false],
];

if (is_singular('post')) {
    $categories = get_the_category();
    if ($categories) {
        $items[] = [
            'label'   => esc_html($categories[0]->name),
            'url'     => esc_url(get_category_link($categories[0]->term_id)),
            'current' => false,
        ];
    }
    $items[] = ['label' => get_the_title(), 'url' => '', 'current' => true];

} elseif (is_page()) {
    $ancestors = get_post_ancestors(get_the_ID());
    foreach (array_reverse($ancestors) as $ancestor_id) {
        $items[] = [
            'label'   => get_the_title($ancestor_id),
            'url'     => get_permalink($ancestor_id),
            'current' => false,
        ];
    }
    $items[] = ['label' => get_the_title(), 'url' => '', 'current' => true];

} elseif (is_category()) {
    $items[] = ['label' => single_cat_title('', false), 'url' => '', 'current' => true];

} elseif (is_search()) {
    $items[] = ['label' => 'Búsqueda: ' . get_search_query(), 'url' => '', 'current' => true];

} elseif (is_404()) {
    $items[] = ['label' => 'Página no encontrada', 'url' => '', 'current' => true];
}
?>
<nav class="breadcrumb" aria-label="Ruta de navegación">
    <ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach ($items as $i => $item): ?>
            <li
                class="breadcrumb__item<?php echo $item['current'] ? ' breadcrumb__item--current' : ''; ?>"
                itemprop="itemListElement"
                itemscope
                itemtype="https://schema.org/ListItem"
            >
                <?php if ($item['url'] && !$item['current']): ?>
                    <a href="<?php echo esc_url($item['url']); ?>" itemprop="item">
                        <span itemprop="name"><?php echo esc_html($item['label']); ?></span>
                    </a>
                <?php else: ?>
                    <span itemprop="name" aria-current="page"><?php echo esc_html($item['label']); ?></span>
                <?php endif; ?>
                <meta itemprop="position" content="<?php echo esc_attr($i + 1); ?>">
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
