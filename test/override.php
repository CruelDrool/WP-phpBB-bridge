<?php

// For password changes within Wordpress (pew pew)
if ( ! function_exists( 'wp_hash_password' ) ) :
function wp_hash_password($password) {
	return phpbb_hash($password);
}
endif;

if ( ! function_exists( 'wp_check_password' ) ) :
function wp_check_password($password, $hash, $user_id = '') {
	$check = phpbb_check_hash($password, $hash);
	return apply_filters('check_password', $check, $password, $hash, $user_id);
}
endif;

// if ( ! function_exists( 'wp_logout' ) ) :
// function wp_logout() {
	// global $forum_user;
	
	// if ($forum_user->data['user_id'] != ANONYMOUS)
		// {
			// $forum_user->session_kill();
			// // $forum_user->session_begin();
		// }
	// update_user_meta(wp_get_current_user()->ID, 'session_id', '');
	// wp_destroy_current_session();
	// wp_clear_auth_cookie();
	// do_action('wp_logout');
// }
// endif;
?>