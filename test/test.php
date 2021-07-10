<?php
/*
Plugin Name: WordPress-phpBB bridge
Plugin URI: https://github.com/CruelDrool/WP-phpBB-bridge
Description: Will attempt to log people into Wordpress if they are logged into a phpBB forum.
Version: 0.1.21 Alpha
Author: CruelDrool
Author URI: https://github.com/CruelDrool
*/

$plugin_path = dirname(__FILE__);
include_once($plugin_path . '/admin.php');

$parent_slug = 'options-general.php';
$menu_slug = 'wpbb-admin';

add_action('admin_menu', function(){
	global $parent_slug, $menu_slug;
	$plugin_data = get_plugin_data( __FILE__ );
	add_submenu_page($parent_slug , sprintf('%s %s', $plugin_data['Name'], __('Settings')), $plugin_data['Name'], 'administrator', $menu_slug, function() { wpbb_display_options(get_plugin_data( __FILE__ ));});
});

// plugin_basename() not playing nicely with Windows Junctions
add_filter( 'plugin_action_links_' . implode('/', [basename($plugin_path),basename(__FILE__)]), function( $actions ) {
	global $parent_slug, $menu_slug;
	$path = sprintf('%s?page=%s', $parent_slug, $menu_slug );
	$links = [
		sprintf('<a href="%s">%s</a>', admin_url( $path ), __('Settings') ),
	];
	$actions = array_merge( $actions, $links );
	return $actions;
});

if ( get_option('wpbb_active') == "yes" ) {
	include_once($plugin_path . '/functions.php');
	include_once($plugin_path . '/override.php');
	include_once($plugin_path . '/class-wpbb-walker-comment.php');
	include_once($plugin_path . '/session.php');
	include_once($plugin_path . '/sidebar-login.php');
	
	add_action('init','wpbb_init');
	add_action('shutdown','garbage_collection');
	add_action('wp_logout', 'wpbb_logout',1);
	add_filter('pre_get_avatar', 'wpbb_pre_get_avatar', 1, 3);
	add_filter('pre_get_avatar_data', 'wpbb_pre_get_avatar_data', 1, 2);
	
	add_filter('login_url',
	function($login_url, $redirect, $force_reauth){
		global $phpbb_url, $phpEx;
		$queries = ['mode' => 'login'];
		if (! empty($redirect)) {$queries['redirect'] = urlencode( $redirect );}

		$url = add_query_arg( $queries, $phpbb_url.'/ucp.'.$phpEx);

		return $url;
	}, 10, 3);
	
	add_filter('register_url', function(){
		global $phpbb_url, $phpEx;
		
		$url = add_query_arg( ['mode' => 'register'], $phpbb_url.'/ucp.'.$phpEx);
		return $url;
	});
	
	add_filter( 'lostpassword_url', function($lostpassword_url, $redirect){
		global $phpbb_url, $phpEx;
		$queries = ['mode' => 'sendpassword'];
		if (! empty($redirect)) {$queries['redirect'] = urlencode( $redirect );}

		$url = add_query_arg( $queries, $phpbb_url.'/ucp.'.$phpEx);

		return $url;
	}, 10, 3);

	
	add_action('login_form_login', function(){
		wp_redirect(wp_login_url());
		exit();
	});

	add_action('login_form_register', function(){
		wp_redirect(wp_registration_url());
		exit();
	});
	
	add_action('login_form_lostpassword', function(){
		wp_redirect(wp_lostpassword_url());
		exit();
	});
	
	add_action('login_form_retrievepassword', function(){
		wp_redirect(wp_lostpassword_url());
		exit();
	});

	add_filter('pre_option_users_can_register', function($pre_option, $option, $default){
		global $config;
			
		return $config['require_activation'] != USER_ACTIVATION_DISABLE ?? false;
	},10, 3);

	add_filter('get_comment_author_link', 
	function($return, $author, $comment_ID){
		$comment = get_comment( $comment_ID );
		$url = wpbb_get_profile_link($comment);
		return $url;
	},10, 3);
}

?>
