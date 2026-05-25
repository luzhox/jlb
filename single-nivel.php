<?php
/**
 * single-nivel.php — Formato de Nivel (Figma 4110:116).
 * Reutiliza el formato de detalle compartido con Talleres
 * (template-parts/jlb-formato-detalle.php). Campos ACF en inc/cpt-nivel.php.
 */
get_header('jlb');
while (have_posts()): the_post();
    get_template_part('template-parts/jlb-formato-detalle');
endwhile;
get_footer('jlb');
