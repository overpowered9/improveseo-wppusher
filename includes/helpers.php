<?php

/*======= loading template files ======*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly  

function improveseo_wt_load_templates($template_name, $vars = null)
{
   if ($vars != null && is_array($vars)) {
      extract($vars);
   };

   $template_path =  IMPROVESEO_WT_PATH . "/modules/{$template_name}";
   if (file_exists($template_path)) {
      include  $template_path;
   } else {
      die("Error while loading file {$template_path}");
   }
}


/* ==== print defualt array ==== */
function improveseo_wt_pa($arr)
{
   echo '<pre>';
   print_r($arr);
   echo '</pre>';
}
