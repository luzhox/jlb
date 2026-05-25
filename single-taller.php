<?php
/**
 * single-taller.php — Formato de Taller (Figma 4131:452).
 * Usa el formato de detalle compartido (template-parts/jlb-formato-detalle.php),
 * que también renderiza el CPT `nivel`. Campos ACF del CPT en inc/cpt-taller.php.
 */
get_header('jlb');
while (have_posts()): the_post();
    get_template_part('template-parts/jlb-formato-detalle');
endwhile;
get_footer('jlb');
