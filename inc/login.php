<?php


function admin_styles(){
    wp_enqueue_style('CssVegas',get_template_directory_uri().'/login/css/vegas.min.css',false);
    wp_enqueue_style('loginCSS',get_template_directory_uri().'/login/css/loginStyles.css',false);
    wp_enqueue_script('jquery');
    wp_enqueue_script('jqueryvegas', get_template_directory_uri() . '/login/js/vegas.min.js', array('jquery'),'1.0',true);
    wp_enqueue_script('loginjs', get_template_directory_uri() . '/login/js/login.js', array('jquery'),'1.0',true);
    wp_localize_script(
        'loginjs','login_imagenes',
        array(
          "ruta_plantilla" => get_template_directory_uri()
          )
      );

}
add_action('login_enqueue_scripts','admin_styles', 10 );


?>