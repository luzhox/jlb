<?php
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'gallery', 'caption'));
    add_theme_support('post-thumbnails');
});
?>