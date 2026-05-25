<?php
/**
 * template-part: jlb-formato-detalle — Formato de detalle compartido por los CPT
 * `taller` (Figma 4131:452) y `nivel` (Figma 4110:116).
 *
 * Debe llamarse DENTRO del loop (tras the_post()). Lee los campos ACF del post
 * actual y renderiza: hero (reusa jlb_admision_hero) + Plan de estudios + Video
 * (opcional) + Objetivos + Galería (reusa jlb_galeria + imagen full opcional) +
 * Testimonial (reusa jlb_testimoniales).
 *
 * Plan de estudios admite dos formas:
 *   · `plan_grupos` (repeater: etiqueta + cursos) → varias bandas (niveles).
 *   · `cursos` plano + `plan_etiqueta` → una banda (talleres).
 *
 * Estilos en styles/sass/organisms/_jlb-taller.scss (prefijo .jlb-taller-*).
 */

$play_svg = get_template_directory_uri() . '/assets/figma-home/blog/play.svg';
$ext_icon = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 16L16 8M16 8H9.5M16 8V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// ── Hero (reutiliza el módulo jlb_admision_hero) ──
$botones = array();
if (have_rows('hero_botones')) {
    while (have_rows('hero_botones')) {
        the_row();
        $u = (string) get_sub_field('url');
        if ($u === '') continue;
        $botones[] = array('texto' => (string) get_sub_field('texto'), 'url' => $u, 'target' => '_self');
    }
}
get_template_part('modules/jlb-admision-hero/jlb-admision-hero', null, array(
    'titulo'    => get_the_title(),
    'subtitulo' => (string) get_field('hero_subtitulo'),
    'imagen'    => get_field('hero_imagen'),
    'botones'   => $botones,
));

// ── Plan de estudios: arma los grupos (grupos o flat) ──
$plan_grupos = array();
if (have_rows('plan_grupos')) {
    while (have_rows('plan_grupos')) {
        the_row();
        $cs = array();
        if (have_rows('cursos')) {
            while (have_rows('cursos')) { the_row(); $n = (string) get_sub_field('nombre'); if ($n !== '') $cs[] = $n; }
        }
        if ($cs) $plan_grupos[] = array('etiqueta' => (string) get_sub_field('etiqueta'), 'cursos' => $cs);
    }
} else {
    $cs = array();
    if (have_rows('cursos')) {
        while (have_rows('cursos')) { the_row(); $n = (string) get_sub_field('nombre'); if ($n !== '') $cs[] = $n; }
    }
    if ($cs) $plan_grupos[] = array('etiqueta' => (string) get_field('plan_etiqueta'), 'cursos' => $cs);
}
$plan_titulo = (string) get_field('plan_titulo');

// ── Objetivos ──
$obj_titulo = (string) get_field('obj_titulo');
$puntos = array();
if (have_rows('obj_puntos')) {
    while (have_rows('obj_puntos')) { the_row(); $t = (string) get_sub_field('texto'); if ($t !== '') $puntos[] = $t; }
}
$obj_card_img = get_field('obj_card_imagen');
$obj_card_txt = (string) get_field('obj_card_texto');
$obj_card_btn = get_field('obj_card_boton');

// ── Video (opcional) ──
$video  = (string) get_field('video_url');
$poster = get_field('video_poster');

// ── Galería ──
$gal_full = get_field('gal_full');

// ── Testimonial ──
$ti = get_field('testi_imagen');
$tq = (string) get_field('testi_quote');
?>
<article class="jlb-detalle" itemscope itemtype="https://schema.org/Course">

    <?php if ($plan_titulo || $plan_grupos): ?>
        <section class="jlb-taller-plan" aria-labelledby="jlb-detalle-plan-t">
            <div class="jlb-container">
                <?php if ($eyebrow = (string) get_field('plan_eyebrow')): ?>
                    <p class="jlb-taller-eyebrow"><?php echo esc_html($eyebrow); ?></p>
                <?php endif; ?>
                <?php if ($plan_titulo): ?>
                    <h2 class="jlb-taller-plan__title" id="jlb-detalle-plan-t"><?php echo esc_html($plan_titulo); ?></h2>
                <?php endif; ?>

                <?php if ($plan_grupos): ?>
                    <div class="jlb-taller-plan__grupos" data-gsap="fade-up">
                        <?php foreach ($plan_grupos as $g): ?>
                            <div class="jlb-taller-plan__grid">
                                <?php if ($g['etiqueta'] !== ''): ?>
                                    <div class="jlb-taller-plan__band"><span><?php echo esc_html($g['etiqueta']); ?></span></div>
                                <?php endif; ?>
                                <ul class="jlb-taller-plan__cards">
                                    <?php foreach ($g['cursos'] as $c): ?>
                                        <li class="jlb-taller-plan__card">
                                            <span class="jlb-taller-plan__dot" aria-hidden="true"></span>
                                            <span><?php echo esc_html($c); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($poster['url'])):
        $vtag = $video !== '' ? 'a' : 'figure';
    ?>
        <section class="jlb-taller-video">
            <div class="jlb-container">
                <<?php echo $vtag; ?> class="jlb-taller-video__frame<?php echo $video !== '' ? ' is-video' : ''; ?>"
                    data-gsap="fade-up"
                    <?php if ($video !== ''): ?>href="<?php echo esc_url($video); ?>" data-jlb-video="<?php echo esc_url($video); ?>" aria-label="<?php echo esc_attr(sprintf(__('Reproducir video: %s', 'boilerplate'), get_the_title())); ?>"<?php endif; ?>>
                    <img src="<?php echo esc_url($poster['url']); ?>" alt="<?php echo esc_attr($poster['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                    <?php if ($video !== ''): ?><img class="jlb-taller-video__play" src="<?php echo esc_url($play_svg); ?>" alt="" aria-hidden="true" width="110" height="110"><?php endif; ?>
                </<?php echo $vtag; ?>>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($obj_titulo || $puntos): ?>
        <section class="jlb-taller-obj" aria-labelledby="jlb-detalle-obj-t">
            <div class="jlb-container jlb-taller-obj__inner">
                <div class="jlb-taller-obj__main" data-gsap="fade-right">
                    <img class="jlb-taller-obj__deco" src="<?php echo esc_url(get_template_directory_uri() . '/assets/figma-home/blog/scribble.svg'); ?>" alt="" aria-hidden="true" width="88" height="88">
                    <?php if ($eb = (string) get_field('obj_eyebrow')): ?>
                        <p class="jlb-taller-eyebrow"><?php echo esc_html($eb); ?></p>
                    <?php endif; ?>
                    <?php if ($obj_titulo): ?>
                        <h2 class="jlb-taller-obj__title" id="jlb-detalle-obj-t"><?php echo esc_html($obj_titulo); ?></h2>
                    <?php endif; ?>
                    <?php if ($puntos): ?>
                        <ol class="jlb-taller-obj__list">
                            <?php foreach ($puntos as $i => $p): ?>
                                <li class="jlb-taller-obj__item">
                                    <span class="jlb-taller-obj__num" aria-hidden="true"><?php echo esc_html($i + 1); ?></span>
                                    <span><?php echo esc_html($p); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>

                <aside class="jlb-taller-obj__aside" data-gsap="fade-left" data-gsap-delay="0.12">
                    <?php if (!empty($obj_card_img['url'])): ?>
                        <figure class="jlb-taller-obj__img">
                            <img src="<?php echo esc_url($obj_card_img['url']); ?>" alt="<?php echo esc_attr($obj_card_img['alt'] ?? ''); ?>" loading="lazy">
                        </figure>
                    <?php endif; ?>
                    <?php if ($obj_card_txt || !empty($obj_card_btn['url'])): ?>
                        <div class="jlb-taller-obj__cta">
                            <?php if ($obj_card_txt): ?><p class="jlb-taller-obj__cta-text"><?php echo esc_html($obj_card_txt); ?></p><?php endif; ?>
                            <?php if (!empty($obj_card_btn['url'])):
                                $blank = ($obj_card_btn['target'] ?? '') === '_blank';
                            ?>
                                <a class="jlb-taller-obj__cta-btn" href="<?php echo esc_url($obj_card_btn['url']); ?>" target="<?php echo esc_attr($obj_card_btn['target'] ?: '_self'); ?>" <?php echo $blank ? 'rel="noopener noreferrer"' : ''; ?>>
                                    <span><?php echo esc_html($obj_card_btn['title'] ?: __('Ir a admisión', 'boilerplate')); ?></span>
                                    <?php echo $ext_icon; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </section>
    <?php endif; ?>

    <?php
    get_template_part('modules/jlb-galeria/jlb-galeria', null, array(
        'imagen_ancha'   => get_field('gal_ancha'),
        'imagen_angosta' => get_field('gal_angosta'),
    ));
    ?>

    <?php if (!empty($gal_full['url'])): ?>
        <section class="jlb-taller-galfull">
            <div class="jlb-container">
                <figure class="jlb-taller-galfull__frame" data-gsap="fade-up">
                    <img src="<?php echo esc_url($gal_full['url']); ?>" alt="<?php echo esc_attr($gal_full['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                </figure>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($tq || !empty($ti['url'])):
        get_template_part('modules/jlb-testimoniales/jlb-testimoniales', null, array(
            'kicker'                  => __('Testimoniales', 'boilerplate'),
            'mostrar_arco_decorativo' => true,
            'items'                   => array(array(
                'imagen'       => $ti,
                'video_url'    => (string) get_field('testi_video_url'),
                'titulo'       => $tq,
                'cita'         => (string) get_field('testi_texto'),
                'autor_nombre' => (string) get_field('testi_autor'),
                'autor_rol'    => (string) get_field('testi_rol'),
            )),
        ));
    endif;
    ?>

</article>
