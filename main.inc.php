<?php
/*
Plugin Name: Custom Download Link
Version: auto
Description: Add a specific download button on the page of the photo
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=
Author: plg
Author URI: http://le-gall.net/pierrick
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

add_event_handler('loc_begin_picture', 'cdl_deactivate_hd');
function cdl_deactivate_hd()
{
  global $user;

  $user['cdl_enabled_high'] = $user['enabled_high'];
  $user['enabled_high'] = false;
}

add_event_handler('loc_end_picture', 'cdl_add_link');
function cdl_add_link()
{
  global $conf, $template, $user, $picture;

  if (!$user['cdl_enabled_high'])
  {
    return;
  }

  if (isset($picture['current']['is_gvideo']) and $picture['current']['is_gvideo'])
  {
    return;
  }

  // compatibility with plugin Download Permissions
  if (function_exists('dlperms_is_photo_downloadable'))
  {
    if (!dlperms_is_photo_downloadable($picture['current']['id']))
    {
      return;
    }
  }

  load_language('plugin.lang', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');
  load_language('lang', PHPWG_ROOT_PATH.PWG_LOCAL_DIR, array('no_fallback'=>true, 'local'=>true) );

  $template->set_prefilter('picture', 'cdl_add_link_prefilter');
  $template->assign('CDL_LINK', get_action_url($picture['current']['id'], 'e', true));
}

function cdl_add_link_prefilter($content, &$smarty)
{
  global $conf;

  $custom_link_tpl = '<div id="customDownloadLink"><a href="{$CDL_LINK}" rel="nofollow"><img src="plugins/custom_download_link/download_white_32.png"> {\'Download Photo\'|@translate}</a></div>{combine_css path="plugins/custom_download_link/style.css"}';
  
  if (isset($conf['custom_download_link_position']))
  {
    if ('properties-after' == $conf['custom_download_link_position'])
    {
      $search = '#</dl>#';
      $replace = '</dl>'.$custom_link_tpl;

      return preg_replace($search, $replace, $content, 1);
    }

    if ('properties-before' == $conf['custom_download_link_position'])
    {
      $search = '<dl id="standard"';
      $replace = $custom_link_tpl.$search;

      return str_replace($search, $replace, $content);
    }
  }
  
  $search = '{$ELEMENT_CONTENT}';
  $replace = '{$ELEMENT_CONTENT}'.$custom_link_tpl;

  return str_replace($search, $replace, $content);
}
?>
