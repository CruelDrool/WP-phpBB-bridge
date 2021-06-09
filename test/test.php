<?php
/*
Plugin Name: CruelDrool Bridge!
Plugin URI: https://www.nam-guild.com
Description: Will attempt to log people into Wordpress if they are logged into a phpBB forum.
Version: 0.1.20 Alpha
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
	add_action('shutdown','garbage_collection');
	add_action('wp_logout', 'wpbb_logout',1);
	add_filter('pre_get_avatar', 'wpbb_pre_get_avatar', 1, 3);
	add_filter('pre_get_avatar_data', 'wpbb_pre_get_avatar_data', 1, 2);
	
// add_filter('register_url', function(){
	// global $phpbb_url, $phpEx;
	
	// $url = add_query_arg( ['mode' => 'register'], $phpbb_url.'/ucp.'.$phpEx);
	// return $url;
// });

// add_filter('pre_option_users_can_register', function($pre_option, $option, $default){
	// global $config;
		
	// return $config['require_activation'] != USER_ACTIVATION_DISABLE ?? false;
// },10, 3);

add_filter('get_comment_author_link', 
function($return, $author, $comment_ID){
	$comment = get_comment( $comment_ID );
	$url = wpbb_get_profile_link($comment);
	return $url;
},10, 3);

	
add_filter('login_url',
function($login_url, $redirect, $force_reauth){
	global $phpbb_url, $phpEx;
	$queries = ['mode' => 'login'];
	if (! empty($redirect)) {$queries['redirect'] = urlencode( $redirect );}

	$login_url = add_query_arg( $queries, $phpbb_url.'/ucp.'.$phpEx);

	return $login_url;
}, 10, 3);

add_filter('comment_reply_link',
function($link, $args, $comment, $post) {
	if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
		$login_url = wp_login_url(wpbb_current_url());
		$link = sprintf(
			'<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
			$login_url,
			$args['login_text']
		);
	}
	
	return $link;
}, 10, 4 );
	
add_filter( 'comment_form_defaults',
function($fields) {
	$login_url = wp_login_url(wpbb_current_url());
	
	$fields['must_log_in'] = sprintf(
		'<p class="must-log-in">%s</p>',
		sprintf(
			__( 'You must be <a href="%s">logged in</a> to post a comment.' ),
			$login_url
		)
	);
	
	return $fields;
});
}




?>