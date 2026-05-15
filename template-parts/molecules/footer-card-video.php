<?php
/**
 * Molecule · Footer card vídeo (Kresna)
 *
 * Card izquierda del footer:
 *   - Vídeo de fondo (assets/video/footer-bg.mp4) + fallback color brand
 *   - Logo Kresna (utiliza brand_img-revert del Customizer existente)
 *   - Tagline (textarea Customizer)
 *   - Social row con label manuscrito + 4 iconos sociales
 *
 * Vars del Customizer (ver inc/customizer-footer.php):
 *   kresna_tagline, kresna_tagline_accent, kresna_social_label,
 *   kresna_social_{discord,x,linkedin,github}_url
 *
 * Reduced motion: el <video> lleva data-decorative que oculta vía main.css.
 */

$logo            = get_theme_mod('brand_img-revert');
$tagline         = bp_kresna_get('kresna_tagline');
$tagline_accent  = bp_kresna_get('kresna_tagline_accent');
$social_label    = bp_kresna_get('kresna_social_label');

$video_url       = get_template_directory_uri() . '/assets/video/footer-bg.mp4';
$video_path      = get_template_directory()     . '/assets/video/footer-bg.mp4';
$has_video       = file_exists($video_path);

// Render del tagline: si el accent existe en el tagline lo envolvemos en <span>.
$tagline_html = esc_html($tagline);
if ($tagline_accent !== '' && $tagline !== '' && strpos($tagline, $tagline_accent) !== false) {
    $tagline_html = str_replace(
        esc_html($tagline_accent),
        '<span class="footer-card-video__accent">' . esc_html($tagline_accent) . '</span>',
        esc_html($tagline)
    );
}

$socials = array(
    'discord'  => bp_kresna_get('kresna_social_discord_url'),
    'x'        => bp_kresna_get('kresna_social_x_url'),
    'linkedin' => bp_kresna_get('kresna_social_linkedin_url'),
    'github'   => bp_kresna_get('kresna_social_github_url'),
);
$socials = array_filter($socials, static function ($url) { return trim((string) $url) !== ''; });
?>
<article class="footer-card-video" aria-labelledby="footer-card-video-tagline">
    <?php if ($has_video): ?>
        <video
            class="footer-card-video__bg"
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            aria-hidden="true"
            data-decorative
            poster="<?php echo esc_url(get_template_directory_uri() . '/assets/video/footer-bg-poster.jpg'); ?>"
        >
            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
        </video>
    <?php endif; ?>

    <div class="footer-card-video__overlay" aria-hidden="true"></div>

    <div class="footer-card-video__body">
        <?php if ($logo): ?>
            <div class="footer-card-video__logo">
                <img
                    src="<?php echo esc_url($logo); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                    width="160"
                    height="48"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        <?php endif; ?>

        <?php if ($tagline): ?>
            <p id="footer-card-video-tagline" class="footer-card-video__tagline">
                <?php echo $tagline_html; // ya escapado arriba ?>
            </p>
        <?php endif; ?>

        <?php if ($socials || $social_label): ?>
            <div class="footer-card-video__social">
                <?php if ($social_label): ?>
                    <span class="footer-card-video__social-label">
                        <?php echo esc_html($social_label); ?>
                    </span>
                <?php endif; ?>

                <?php if ($socials): ?>
                    <ul class="footer-card-video__social-list" role="list">
                        <?php foreach ($socials as $network => $url): ?>
                            <li>
                                <?php
                                get_template_part('template-parts/atoms/social-icon', null, array(
                                    'network' => $network,
                                    'url'     => $url,
                                ));
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>

<?php
if (!isset($GLOBALS['__bp_footer_card_video_styled'])) {
    $GLOBALS['__bp_footer_card_video_styled'] = true;
    ?>
    <style>
        .footer-card-video {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border-radius: var(--radius-2xl);
            background: var(--color-brand-500);
            color: #fff;
            box-shadow: var(--shadow-brand-card);
            min-height: 360px;
            display: flex;
            align-items: flex-end;
            padding: 2rem;
        }
        .footer-card-video__bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        .footer-card-video__overlay {
            position: absolute;
            inset: 0;
            background: var(--gradient-card-fade);
            z-index: 1;
        }
        .footer-card-video__body {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }
        .footer-card-video__logo img {
            display: block;
            height: 32px;
            width: auto;
            filter: brightness(0) invert(1);
        }
        .footer-card-video__tagline {
            font-family: var(--font-display);
            font-size: var(--text-3xl);
            line-height: 1.15;
            font-weight: 600;
            letter-spacing: -0.015em;
            margin: 0;
            color: #fff;
            max-width: 22ch;
        }
        .footer-card-video__accent {
            opacity: 0.7;
            font-weight: 400;
        }
        .footer-card-video__social {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .footer-card-video__social-label {
            font-family: var(--font-handwritten);
            font-size: var(--text-2xl);
            line-height: 1;
            color: rgb(255 255 255 / 0.95);
        }
        .footer-card-video__social-list {
            display: flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        @media (min-width: 48rem) {
            .footer-card-video { padding: 2.5rem; min-height: 420px; }
        }
    </style>
    <?php
}
