<?php
/**
 * Atom · Button
 *
 * Uso:
 *   get_template_part('template-parts/atoms/button', null, [
 *       'label'   => 'Ver más',
 *       'url'     => 'https://...',
 *       'variant' => 'primary',
 *       'size'    => '',
 *       'target'  => '_self',
 *       'class'   => '',
 *       'attrs'   => [],
 *       'tag'     => 'a',          // 'a' | 'button'
 *       'type'    => 'submit',     // solo si tag='button'
 *   ]);
 *
 * Variantes:
 *   LEGACY (compat — clases SASS):
 *     primary | outline | ghost
 *
 *   SHADCN (sistema nuevo — clases @layer components en main.css):
 *     shadcn-primary  → inverso (fg/bg)
 *     shadcn-outline  → border + texto, transparente
 *     shadcn-ghost    → solo texto + hover bg muted
 *     brand           → azul Kresna (acción principal)
 *     kresna-dark     → negro #111214 + sombra dramática (Subscribe footer)
 *     destructive
 *
 * Tamaños (solo aplican a las variantes shadcn):
 *   sm | md | lg     (default md)
 */

$args    = $args ?? [];
$label   = $args['label']   ?? 'Ver más';
$url     = $args['url']     ?? '#';
$variant = $args['variant'] ?? 'primary';
$size    = $args['size']    ?? '';
$target  = $args['target']  ?? '_self';
$extra   = $args['class']   ?? '';
$attrs   = $args['attrs']   ?? [];
$tag     = $args['tag']     ?? 'a';
$btn_type = $args['type']   ?? 'button';

// ── Mapeo de variantes ──────────────────────────────────────────────────────
$legacy_map = array(
    'primary' => 'btn__primary',
    'outline' => 'btn__primary--border',
    'ghost'   => 'btn__ghost',
);
$shadcn_map = array(
    'shadcn-primary' => 'btn-base btn-shadcn-primary',
    'shadcn-outline' => 'btn-base btn-shadcn-outline',
    'shadcn-ghost'   => 'btn-base btn-shadcn-ghost',
    'brand'          => 'btn-base btn-brand',
    'kresna-dark'    => 'btn-base btn-kresna-dark',
    'destructive'    => 'btn-base btn-destructive',
);

if (isset($shadcn_map[$variant])) {
    $base_class = $shadcn_map[$variant];
    $size_norm  = in_array($size, array('sm', 'md', 'lg'), true) ? $size : 'md';
    $size_class = ' btn-size-' . $size_norm;
} else {
    $base_class = $legacy_map[$variant] ?? 'btn__primary';
    $size_class = $size ? ' btn--' . esc_attr($size) : '';
}
$classes = trim($base_class . $size_class . ($extra ? ' ' . $extra : ''));

// ── Atributos extra ─────────────────────────────────────────────────────────
$attr_str = '';
foreach ($attrs as $key => $val) {
    $attr_str .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
}

// ── Render ──────────────────────────────────────────────────────────────────
if ($tag === 'button') {
    ?>
    <button
        type="<?php echo esc_attr($btn_type); ?>"
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $attr_str; ?>
    >
        <?php echo esc_html($label); ?>
    </button>
    <?php
} else {
    ?>
    <a
        href="<?php echo esc_url($url); ?>"
        target="<?php echo esc_attr($target); ?>"
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $attr_str; ?>
        <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
    >
        <?php echo esc_html($label); ?>
    </a>
    <?php
}
