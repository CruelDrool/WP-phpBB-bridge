<?php
/*
Plugin Name: CruelDrool Bridge!
Plugin URI: https://www.nam-guild.com
Description: Will attempt to log people into Wordpress if they are logged into a phpBB forum.
Version: 0.1.14 Alpha
Author: CruelDrool
Author URI: https://www.nam-guild.com
*/

$plugin_path = dirname(__FILE__);
include_once($plugin_path . '/admin.php');

add_action('admin_menu', 'wpbb_admin_menu');

if ( get_option('wpbb_active') == "yes" ) {
	include_once($plugin_path . '/functions.php');
	include_once($plugin_path . '/override.php');
	include_once($plugin_path . '/class-wpbb-walker-comment.php');
	include_once($plugin_path . '/session.php');
	include_once($plugin_path . '/sidebar-login.php');
	
	add_action('init','wpbb_init');
	add_action('wp_footer','garbage_collection');
	add_action('admin_footer','garbage_collection');
	add_action('wp_logout', 'wpbb_logout',1);
	add_filter('pre_get_avatar', 'wpbb_get_avatar', 1, 3);
}

?>