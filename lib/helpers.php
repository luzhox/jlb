<?php
/**
 * Fechas en español sin depender del locale global del sitio (que está en en_US).
 * jlb_fecha_larga(ts) => "2 de julio"   ·   jlb_mes_abbr_es(ts) => "Jul"
 */
function jlb_meses_es($abbr = false)
{
  $full = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
  $abr  = array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
  return $abbr ? $abr : $full;
}

function jlb_fecha_larga($timestamp)
{
  $m = (int) date('n', (int) $timestamp) - 1;
  $meses = jlb_meses_es();
  return (int) date('j', (int) $timestamp) . ' de ' . ($meses[$m] ?? '');
}

function jlb_mes_abbr_es($timestamp)
{
  $m = (int) date('n', (int) $timestamp) - 1;
  $meses = jlb_meses_es(true);
  return $meses[$m] ?? '';
}

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