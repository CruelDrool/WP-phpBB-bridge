<?php
function wpbb_init() {
	global $phpbb_root_path, $phpEx, $auth, $user, $db, $config, $cache, $template, $forum_user, $phpbb_url;
	// global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template;

	define('IN_PHPBB', true);
	$phpbb_path = get_option('wpbb_path');
	$phpbb_url = trim(get_option('wpbb_url'),"/");
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : $phpbb_path.'/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	
	// Register globals and magic quotes have been dropped in PHP 5.4
	if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
	{
		/**
		* @ignore
		*/
		define('STRIP', false);
	}
	else
	{
		@set_magic_quotes_runtime(0);

		// Be paranoid with passed vars
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on' || !function_exists('ini_get'))
		{
			deregister_globals();
		}

		define('STRIP', (get_magic_quotes_gpc()) ? true : false);
	}

	// Include files
	require($phpbb_root_path . 'config.' . $phpEx);
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
	require($phpbb_root_path . 'includes/cache.' . $phpEx);
	require($phpbb_root_path . 'includes/template.' . $phpEx);
	require($phpbb_root_path . 'includes/session.' . $phpEx);
	require($phpbb_root_path . 'includes/auth.' . $phpEx);
	require($phpbb_root_path . 'includes/functions.' . $phpEx);
	require($phpbb_root_path . 'includes/constants.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);

	// Instantiate some basic classes
	$user		= new user();
	$auth		= new auth();
	$template	= new template();
	$cache		= new cache();
	$db			= new $sql_db();
	$forum_user = $user;
	// Connect to DB
	$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);

	// We do not need this any longer, unset for safety purposes
	unset($dbpasswd);

	// Grab global variables, re-cache if necessary
	$config = $cache->obtain_config();
	
	// After lots of trying, this is currently my best way of avoiding fails during the Site Health tests for REST API and Background Updates. Should be able to do regular REST API request also.
	// Putting here so that (1) my overrides for wp_hash_password and wp_check_password will still work, (2) to avoid phpBB setting a new key_id in SESSIONS_KEYS_TABLE during execution of site-health.php (this happens as soon as $user->session_begin() is called)
	if ( strtolower(substr(trim($_SERVER['REQUEST_URI'],"/"),0,7)) == 'wp-json' || substr($_SERVER['HTTP_USER_AGENT'],0,9) == 'WordPress') {return;}
	
	// Start session management
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
	
	// Some variables for use later on
	$user_id = $user->data['user_id'];
	$user_type = $user->data['user_type'];
	$wp_user = wp_get_current_user();
		
	if ($user_id > 1 && $user_type != 1 && $user_type != 2) // Attempt to log on Wordpress if logged on phpBB
	{	
		$is_founder = ($user_type == 3) ? true : false;
		// $is_globalmod = $auth->acl_get('m_');
		
		$current_session_id = $user->session_id;
		$stored_session_id = get_user_meta($user_id, 'session_id', true);
		
		if ( $current_session_id != $stored_session_id ) 
		// Check if session ID has changed. If so, log onto Wordpress
		// Changing the username in phpBB/the database seems to log the user out in Wordpress. 
		// Probably because Wordpress doesn't allow username changes.
		{
			wp_clear_auth_cookie(); 
			// Clear just in case the user was changed on forum before visiting Wordpress. 
			// Maybe not necessary, but better safe than sorry.
			wp_set_current_user($user_id);
			wp_set_auth_cookie($user_id, true);
			update_user_meta($user_id, 'session_id', $user->session_id);
		}
		// Insert some meta-data based on data from phpbb.
		if ( empty(get_user_meta($user_id, 'has_been_given_defaults', true)))
		{	
			update_user_meta($user_id, 'first_name', '');
			update_user_meta($user_id, 'last_name', '');
			update_user_meta($user_id, 'rich_editing', 'true');
			update_user_meta($user_id, 'comment_shortcuts', 'false');
			update_user_meta($user_id, 'admin_color', 'fresh');
			update_user_meta($user_id, 'show_admin_bar_front', 'false');
			update_user_meta($user_id, 'has_been_given_defaults', '1');
		}
		
		if ($is_founder)
		{
			wpbb_set_capabilities($user_id, 'administrator');
		}
		else
		{	
			$cap_ranking = wpbb_get_available_capabilities();
			
			$grp_cap = get_option('wpbb_grp_cap', Array());
			$sql = 'SELECT group_id
					FROM '. USER_GROUP_TABLE .'
					WHERE user_id = '. $user_id;
			$result = $db->sql_query($sql);
			$highest_grp_rank = 1;
			while ($row = $db->sql_fetchrow($result))
			{
				$group_cap = $grp_cap[$row['group_id']];
				if ($group_cap) {
					$rank = $cap_ranking[$group_cap];
					if ($rank > $highest_grp_rank) {$highest_grp_rank = $rank;}
				}
			}
			$db->sql_freeresult($result);
			$cap = array_search($highest_grp_rank, $cap_ranking);
			
			wpbb_set_capabilities($user_id, $cap);
		}
		
		
		// Updates everytime Wordpress is visited.
		// Note to self: the website field is stored in the user table.
		update_user_meta($user_id, 'description', ( ( $user->data['user_occ'] !='' ) ? $user->lang['OCCUPATION'].":\n".$user->data['user_occ'] : '' ) . ( ( $user->data['user_interests'] !='' ) ? "\n\n".$user->lang['INTERESTS'].":\n".$user->data['user_interests'] : '' ));
		update_user_meta($user_id, 'aim', $user->data['user_aim']);
		update_user_meta($user_id, 'yim', $user->data['user_yim']);
		update_user_meta($user_id, 'jabber', $user->data['user_jabber']);
		update_user_meta($user_id, 'nickname', $user->data['username']);
		update_user_meta($user_id, 'use_ssl', (int) is_ssl());
		

	}
	elseif ( $user_id == 1 && $wp_user->ID > 0 ) // The user appears to have logged off from phpBB.
	{
		wp_logout();
		wp_set_current_user(0);	
	}
}
?>