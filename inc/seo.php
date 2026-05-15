<?php

// Preconnect a Google Fonts — debe ir lo antes posible en el <head>
add_action('wp_head', function () {
  echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
  echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

// Canonical, meta description, Open Graph y Twitter Cards
add_action('wp_head', function () {
  global $post;

  // Canonical
  if (is_singular()) {
    $canonical = get_permalink($post->ID);
  } elseif (is_front_page() || is_home()) {
    $canonical = home_url('/');
  } else {
    // Fix: get_query_var('paged') es 0 en archives sin paginar → URL malformada.
    // Usamos página 1 como base canónica y solo añadimos /page/N/ si > 1.
    $paged     = (int) get_query_var('paged');
    $canonical = $paged > 1 ? get_pagenum_link($paged) : get_pagenum_link(1);
  }
  echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

  // Meta description
  $description = '';
  if (is_singular() && $post) {
    $description = has_excerpt($post->ID)
      ? get_the_excerpt($post->ID)
      : wp_trim_words(strip_shortcodes(get_post_field('post_content', $post->ID)), 25);
  } elseif (is_front_page() || is_home()) {
    $description = get_bloginfo('description');
  }
  $description = wp_strip_all_tags($description);
  if ($description) {
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
  }

  // Open Graph
  $og_type  = is_singular('post') ? 'article' : 'website';
  $og_title = is_singular() ? get_the_title($post->ID) : get_bloginfo('name');
  $og_image = '';
  if (is_singular() && $post && has_post_thumbnail($post->ID)) {
    $og_image = get_the_post_thumbnail_url($post->ID, 'large');
  }

  echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
  echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
  echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
  echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
  echo '<meta property="og:locale" content="' . esc_attr(str_replace('-', '_', get_locale())) . '">' . "\n";
  if ($description) {
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
  }
  if ($og_image) {
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
  }

  // Twitter Cards
  echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '">' . "\n";
  if ($description) {
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
  }
  if ($og_image) {
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
  }
}, 2);
