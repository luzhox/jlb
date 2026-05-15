<?php

add_action('wp_head', function () {
    global $post;

    // ── Organization (todas las páginas) ───────────────────────────────────
    $logo     = get_theme_mod('brand_img');
    $org_name = get_bloginfo('name');
    $org_url  = home_url('/');

    $org_schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $org_name,
        'url'      => $org_url,
    );

    if ($logo) {
        $org_schema['logo'] = array(
            '@type' => 'ImageObject',
            'url'   => esc_url($logo),
        );
    }

    // sameAs — preferimos el menu nativo "Redes Sociales" del footer.
    $social_links = array();
    $locations    = get_nav_menu_locations();
    if (!empty($locations['redes'])) {
        $menu_items = wp_get_nav_menu_items($locations['redes']);
        if ($menu_items) {
            foreach ($menu_items as $item) {
                if (!empty($item->url)) {
                    $social_links[] = esc_url_raw($item->url);
                }
            }
        }
    }

    if (empty($social_links)) {
        $legacy_theme_mods = array(
            'kresna_social_discord_url',
            'kresna_social_x_url',
            'kresna_social_linkedin_url',
            'kresna_social_github_url',
        );
        foreach ($legacy_theme_mods as $key) {
            $url = get_theme_mod($key, '');
            if ($url) {
                $social_links[] = esc_url_raw($url);
            }
        }
    }

    if (empty($social_links)) {
        $legacy = array_filter(array(
            get_option('redes_facebook'),
            get_option('redes_instagram'),
            get_option('redes_linkedin'),
        ));
        $social_links = array_map('esc_url_raw', array_values($legacy));
    }
    if ($social_links) {
        $org_schema['sameAs'] = $social_links;
    }

    echo '<script type="application/ld+json">'
        . wp_json_encode($org_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";

    // ── WebSite + SearchAction (solo portada) ──────────────────────────────
    if (is_front_page()) {
        $website_schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $org_name,
            'url'             => $org_url,
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => array(
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => esc_url(home_url('/')) . '?s={search_term_string}',
                ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        echo '<script type="application/ld+json">'
            . wp_json_encode($website_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }

    // ── BreadcrumbList (páginas interiores) ────────────────────────────────
    if (is_singular() && !is_front_page()) {
        $breadcrumb_items = array(
            array(
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Inicio',
                'item'     => $org_url,
            ),
        );

        if (is_singular('post')) {
            $categories = get_the_category($post->ID);
            if ($categories) {
                $cat              = $categories[0];
                $breadcrumb_items[] = array(
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => esc_html($cat->name),
                    'item'     => esc_url(get_category_link($cat->term_id)),
                );
                $breadcrumb_items[] = array(
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => esc_html(get_the_title($post->ID)),
                );
            } else {
                $breadcrumb_items[] = array(
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => esc_html(get_the_title($post->ID)),
                );
            }
        } else {
            $breadcrumb_items[] = array(
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => esc_html(get_the_title($post->ID)),
            );
        }

        $breadcrumb_schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadcrumb_items,
        );

        echo '<script type="application/ld+json">'
            . wp_json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }

    // ── Article (posts individuales) ───────────────────────────────────────
    if (is_singular('post') && $post) {
        $article_schema = array(
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => esc_html(get_the_title($post->ID)),
            'datePublished'    => get_the_date('c', $post->ID),
            'dateModified'     => get_the_modified_date('c', $post->ID),
            'author'           => array(
                '@type' => 'Person',
                'name'  => esc_html(get_the_author_meta('display_name', $post->post_author)),
            ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => $org_name,
            ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => esc_url(get_permalink($post->ID)),
            ),
        );

        if (has_post_thumbnail($post->ID)) {
            $article_schema['image'] = array(
                '@type' => 'ImageObject',
                'url'   => esc_url(get_the_post_thumbnail_url($post->ID, 'large')),
            );
        }

        $description = has_excerpt($post->ID)
            ? get_the_excerpt($post->ID)
            : wp_trim_words(strip_shortcodes(get_post_field('post_content', $post->ID)), 25);
        if ($description) {
            $article_schema['description'] = wp_strip_all_tags($description);
        }

        echo '<script type="application/ld+json">'
            . wp_json_encode($article_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }
}, 3);
