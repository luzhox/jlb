<?php
// Render a module from the "modules" directory
function the_module($module_name = '')
{
  if (empty($module_name)) {
    return false;
  }

  locate_template("/modules/$module_name/$module_name.php", true, false);
}

function get_module($module_name = '')
{
  if (empty($module_name)) {
    return false;
  }

  ob_start();

  the_module($module_name);

  $html = ob_get_contents();

  ob_end_clean();

  return $html;
}

function the_modules_loop($modules_field = 'modules')
{
  if (!function_exists('have_rows') || !have_rows($modules_field)) {
    return;
  }
  while (have_rows($modules_field)) {
    the_row();
    $module_name = str_replace('_', '-', get_row_layout());
    the_module($module_name);
  }
}