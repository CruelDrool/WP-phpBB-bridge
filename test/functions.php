<?php
function wpbb_login_form( $args = array() ) {
	global $phpbb_url, $phpEx, $config, $user;
	$defaults = array(
		'echo'           => true,
		// Default 'redirect' value takes the user back to the request URI.
		'redirect'       => wpbb_current_url(),
		'form_id'        => 'loginform',
		'label_username'  => $user->lang['USERNAME'] . ":",
		'label_password'  => $user->lang['PASSWORD']. ":",
		'label_remember'  => $user->lang['LOG_ME_IN'],
		'label_hide'      => $user->lang['HIDE_ME'],
		'label_log_in'    => $user->lang['LOGIN'],
		'label_register'  => $user->lang['REGISTER'],
		'label_viewonline'=> $user->lang['HIDE_ME'],
		'label_lost_pass' => $user->lang['FORGOT_PASS'],
		'id_username'     => 'user_login',
		'id_password'     => 'user_pass',
		'id_remember'     => 'remember_me',
		'id_viewonline'   => 'view_online',
		'id_submit'       => 'user_submit',
		'name_viewonline' => 'viewonline',
		'name_username'   => 'username',
		'name_password'   => 'password',
		'name_remember'   => 'autologin',
		'name_redirect'   => 'redirect',
		'name_submit'     => 'login',
		'user_reg_link'   => true,
		'lost_pass_link'  => true,
		'value_username'  => '',
		// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
		'value_remember' => false,
	);

	$args = wp_parse_args( $args, $defaults );
	
	$form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . $phpbb_url.'/ucp.'.$phpEx . '?mode=login" method="post">
			<p class="login-username">
				<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label><br />
				<input type="text" name="' . esc_attr( $args['name_username'] ) . '" id="' . esc_attr( $args['id_username'] ) . '" class="user_input" value="' . esc_attr( $args['value_username'] ) . '" />
			</p>
			<p class="login-password">
				<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label><br />
				<input type="password" name="' . esc_attr( $args['name_password'] ) . '" id="' . esc_attr( $args['id_password'] ) . '" class="user_input" value="" />
			</p>
			<p class="login-options">
				<label><input name="' . esc_attr( $args['name_remember'] ) . '" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" ' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label><br />
				<label><input name="' . esc_attr( $args['name_viewonline'] ) . '" type="checkbox" id="' . esc_attr( $args['id_viewonline'] ) . '" /> ' . esc_html( $args['label_viewonline'] ) . '</label>
			</p>
			<p class="login-submit">
				<input type="submit" name="' . esc_attr( $args['name_submit'] ) . '" id="' . esc_attr( $args['id_submit'] ) . '" class="user_submit" value="' . esc_attr( $args['label_log_in'] ) . '" />
			</p>
			<input type="hidden" name="sid" value="' . $user->session_id . '" />
			<input type="hidden" name="' . esc_attr( $args['name_redirect'] ) . '" value="' . esc_url( $args['redirect'] ) . '" />
		</form>
		<ul class="login-links">
		' . ( $args['lost_pass_link'] ? '<li><a href="'. $phpbb_url.'/ucp.'.$phpEx .'?mode=sendpassword" rel="nofollow">'.esc_attr( $args['label_lost_pass'] ).'</a></li>' : '' ) . '
		' . ( ( $args['user_reg_link'] and $config['require_activation'] != USER_ACTIVATION_DISABLE ) ? '<li><a href="'. $phpbb_url.'/ucp.'.$phpEx .'?mode=register" rel="nofollow">'.esc_attr( $args['label_register'] ).'</a></li>' : '' ) . '
		</ul>';

	if ( $args['echo'] ) {
		echo $form;
	} else {
		return $form;
	}
}

function wpbb_current_url() {
	// $pageURL  = force_ssl_admin() ? 'https://' : 'http://';
	// $pageURL .= esc_attr( $_SERVER['HTTP_HOST'] );
	// $pageURL .= esc_attr( $_SERVER['REQUEST_URI'] );

	// if ( $url != "nologout" ) {
		// if ( ! strpos( $pageURL, '_login=' ) ) {
			// $rand_string = md5( uniqid( rand(), true ) );
			// $rand_string = substr( $rand_string, 0, 10 );
			// $pageURL = add_query_arg( '_login', $rand_string, $pageURL );
		// }
	// }

	// return esc_url_raw( $pageURL );
	return esc_url(set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ));
}

function wpbb_get_comment_reply_link($args = array(), $comment = null, $post = null) {
	global $user_ID;

	$defaults = array('add_below' => 'comment', 'respond_id' => 'respond', 'reply_text' => __('Reply'),
		'login_text' => __('Log in to Reply'), 'depth' => 0, 'before' => '', 'after' => '');

	$args = wp_parse_args($args, $defaults);

	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] )
		return;

	extract($args, EXTR_SKIP);

	$comment = get_comment($comment);
	if ( empty($post) )
		$post = $comment->comment_post_ID;
	$post = get_post($post);

	if ( !comments_open($post->ID) )
		return false;

	$link = '';

	if ( get_option('comment_registration') && !$user_ID )
		$link = '<a rel="nofollow" class="comment-reply-login" href="#commentlogin" onclick="document.getElementById(\'showlogin\').style.display = \'\'">' . $login_text . '</a>';
	else
		$link = "<a rel='nofollow' class='comment-reply-link' href='" . esc_url( add_query_arg( 'replytocom', $comment->comment_ID ) ) . "#" . $respond_id . "' onclick='return addComment.moveForm(\"$add_below-$comment->comment_ID\", \"$comment->comment_ID\", \"$respond_id\", \"$post->ID\")'>$reply_text</a>";
	return apply_filters('comment_reply_link', $before . $link . $after, $args, $comment, $post);
}


function wpbb_comment_reply_link($args = array(), $comment = null, $post = null) {
	echo wpbb_get_comment_reply_link($args, $comment, $post);
}


function wpbb_list_comments( $args = array(), $comments = null ) {
	global $wp_query, $comment_alt, $comment_depth, $comment_thread_alt, $overridden_cpage, $in_comment_loop;

	$in_comment_loop = true;

	$comment_alt   = $comment_thread_alt = 0;
	$comment_depth = 1;

	$defaults = array(
		'walker'            => null,
		'max_depth'         => '',
		'style'             => 'ul',
		'callback'          => null,
		'end-callback'      => null,
		'type'              => 'all',
		'page'              => '',
		'per_page'          => '',
		'avatar_size'       => 32,
		'reverse_top_level' => null,
		'reverse_children'  => '',
		'format'            => current_theme_supports( 'html5', 'comment-list' ) ? 'html5' : 'xhtml',
		'short_ping'        => false,
		'echo'              => true,
	);

	$r = wp_parse_args( $args, $defaults );

	/**
	 * Filters the arguments used in retrieving the comment list.
	 *
	 * @since 4.0.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param array $r An array of arguments for displaying comments.
	 */
	$r = apply_filters( 'wp_list_comments_args', $r );

	// Figure out what comments we'll be looping through ($_comments)
	if ( null !== $comments ) {
		$comments = (array) $comments;
		if ( empty( $comments ) ) {
			return;
		}
		if ( 'all' != $r['type'] ) {
			$comments_by_type = separate_comments( $comments );
			if ( empty( $comments_by_type[ $r['type'] ] ) ) {
				return;
			}
			$_comments = $comments_by_type[ $r['type'] ];
		} else {
			$_comments = $comments;
		}
	} else {
		/*
		 * If 'page' or 'per_page' has been passed, and does not match what's in $wp_query,
		 * perform a separate comment query and allow Walker_Comment to paginate.
		 */
		if ( $r['page'] || $r['per_page'] ) {
			$current_cpage = get_query_var( 'cpage' );
			if ( ! $current_cpage ) {
				$current_cpage = 'newest' === get_option( 'default_comments_page' ) ? 1 : $wp_query->max_num_comment_pages;
			}

			$current_per_page = get_query_var( 'comments_per_page' );
			if ( $r['page'] != $current_cpage || $r['per_page'] != $current_per_page ) {
				$comment_args = array(
					'post_id' => get_the_ID(),
					'orderby' => 'comment_date_gmt',
					'order'   => 'ASC',
					'status'  => 'approve',
				);

				if ( is_user_logged_in() ) {
					$comment_args['include_unapproved'] = get_current_user_id();
				} else {
					$unapproved_email = wp_get_unapproved_comment_author_email();

					if ( $unapproved_email ) {
						$comment_args['include_unapproved'] = array( $unapproved_email );
					}
				}

				$comments = get_comments( $comment_args );

				if ( 'all' != $r['type'] ) {
					$comments_by_type = separate_comments( $comments );
					if ( empty( $comments_by_type[ $r['type'] ] ) ) {
						return;
					}

					$_comments = $comments_by_type[ $r['type'] ];
				} else {
					$_comments = $comments;
				}
			}

			// Otherwise, fall back on the comments from `$wp_query->comments`.
		} else {
			if ( empty( $wp_query->comments ) ) {
				return;
			}
			if ( 'all' != $r['type'] ) {
				if ( empty( $wp_query->comments_by_type ) ) {
					$wp_query->comments_by_type = separate_comments( $wp_query->comments );
				}
				if ( empty( $wp_query->comments_by_type[ $r['type'] ] ) ) {
					return;
				}
				$_comments = $wp_query->comments_by_type[ $r['type'] ];
			} else {
				$_comments = $wp_query->comments;
			}

			if ( $wp_query->max_num_comment_pages ) {
				$default_comments_page = get_option( 'default_comments_page' );
				$cpage                 = get_query_var( 'cpage' );
				if ( 'newest' === $default_comments_page ) {
					$r['cpage'] = $cpage;

					/*
					* When first page shows oldest comments, post permalink is the same as
					* the comment permalink.
					*/
				} elseif ( $cpage == 1 ) {
					$r['cpage'] = '';
				} else {
					$r['cpage'] = $cpage;
				}

				$r['page']     = 0;
				$r['per_page'] = 0;
			}
		}
	}

	if ( '' === $r['per_page'] && get_option( 'page_comments' ) ) {
		$r['per_page'] = get_query_var( 'comments_per_page' );
	}

	if ( empty( $r['per_page'] ) ) {
		$r['per_page'] = 0;
		$r['page']     = 0;
	}

	if ( '' === $r['max_depth'] ) {
		if ( get_option( 'thread_comments' ) ) {
			$r['max_depth'] = get_option( 'thread_comments_depth' );
		} else {
			$r['max_depth'] = -1;
		}
	}

	if ( '' === $r['page'] ) {
		if ( empty( $overridden_cpage ) ) {
			$r['page'] = get_query_var( 'cpage' );
		} else {
			$threaded  = ( -1 != $r['max_depth'] );
			$r['page'] = ( 'newest' == get_option( 'default_comments_page' ) ) ? get_comment_pages_count( $_comments, $r['per_page'], $threaded ) : 1;
			set_query_var( 'cpage', $r['page'] );
		}
	}
	// Validation check
	$r['page'] = intval( $r['page'] );
	if ( 0 == $r['page'] && 0 != $r['per_page'] ) {
		$r['page'] = 1;
	}

	if ( null === $r['reverse_top_level'] ) {
		$r['reverse_top_level'] = ( 'desc' == get_option( 'comment_order' ) );
	}

	wp_queue_comments_for_comment_meta_lazyload( $_comments );

	if ( empty( $r['walker'] ) ) {
		$walker = new Wpbb_Walker_Comment;
	} else {
		$walker = $r['walker'];
	}

	$output = $walker->paged_walk( $_comments, $r['max_depth'], $r['page'], $r['per_page'], $r );

	$in_comment_loop = false;

	if ( $r['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}


function wpbb_get_privmsg() 
{
	global $user, $db;
	$l_privmsgs_text = $l_privmsgs_text_unread = '';
	$s_privmsg_new = false;

	// Obtain number of new private messages if user is logged in
	if (!empty($user->data['is_registered']))
	{
		if ($user->data['user_new_privmsg'])
		{
			$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
			$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);

			if (!$user->data['user_last_privmsg'] || $user->data['user_last_privmsg'] > $user->data['session_last_visit'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_last_privmsg = ' . $user->data['session_last_visit'] . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				$s_privmsg_new = true;
			}
			else
			{
				$s_privmsg_new = false;
			}
		}
		else
		{
			$l_privmsgs_text = $user->lang['NO_NEW_PM'];
			$s_privmsg_new = false;
		}

		$l_privmsgs_text_unread = '';

		if ($user->data['user_unread_privmsg'] && $user->data['user_unread_privmsg'] != $user->data['user_new_privmsg'])
		{
			$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
			$l_privmsgs_text_unread = sprintf($l_message_unread, $user->data['user_unread_privmsg']);
		}
	}
	
	if (!empty($l_privmsgs_text_unread)) {
		$l_privmsgs_text .= " (".$l_privmsgs_text_unread.")";
	}
	
	return $l_privmsgs_text;
}

function wpbb_set_capabilities($userid, $capability = '')
{
	global $wpdb;
	
	$blog_prefix = $wpdb->get_blog_prefix(get_current_blog_id());

	switch ($capability)
	{
		case 'administrator':
			update_user_meta($userid, $blog_prefix . 'capabilities', array('administrator'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 10);
		break;
		case 'editor':
			update_user_meta($userid, $blog_prefix . 'capabilities', array('editor'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 7);
		break;
		case 'author':
			update_user_meta($userid, $blog_prefix . 'capabilities', array('author'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 2);
		break;
		case 'contributor':
			update_user_meta($userid, $blog_prefix . 'capabilities', array('contributor'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 1);
		break;
		case 'subscriber':
			update_user_meta($userid, $blog_prefix . 'capabilities', array('subscriber'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 0);
		break;
		case 'none':
			update_user_meta($userid, $blog_prefix . 'capabilities', array());
			update_user_meta($userid, $blog_prefix . 'user_level', 0);
		break;
		default:
			update_user_meta($userid, $blog_prefix . 'capabilities', array('subscriber'=>1));
			update_user_meta($userid, $blog_prefix . 'user_level', 0);
		break;
	}
}

function wpbb_get_profile_link($comment)
{
	global $phpbb_url,$phpEx;
	$user_id = (int) $comment->user_id;
	$userdata = get_userdata($user_id);
	if (is_object($userdata))
	{
		if ($userdata->ID > 1 && strtotime($comment->comment_date) > strtotime($userdata->user_registered) )
		{
			if($comment->comment_author != $userdata->user_login)
			{
				$comment->comment_author = $userdata->user_login;
				wp_update_comment((array)$comment);
			}
			if($comment->comment_author_email != $userdata->user_email)
			{
				$comment->comment_author_email = $userdata->user_email;
				wp_update_comment((array)$comment);
			}
			if($comment->comment_author_url != $userdata->user_url)
			{
				$comment->comment_author_url = $userdata->user_url;
				wp_update_comment((array)$comment);
			}
			$user_colour = wpbb_get_usercolour($user_id);
			$is_logged_in = is_user_logged_in();
			$comment_author = $userdata->display_name;
			
			if ($is_logged_in && $user_colour) {
				// $profile_link = '<a href="'.$phpbb_url . '/memberlist.'.$phpEx.'?mode=viewprofile&amp;u='.$user_id.'" style="color:#'.$user_colour.'">'.$comment_author.'</a>';
				$profile_link = sprintf('<a href="%s/memberlist.%s?mode=viewprofile&amp;u=%s" style="color: #%s">%s</a>', $phpbb_url, $phpEx, $user_id, $user_colour, $comment_author );
			} else if ($is_logged_in && !$user_colour) {
				// $profile_link = '<a href="'.$phpbb_url . '/memberlist.'.$phpEx.'?mode=viewprofile&amp;u='.$user_id.'">'.$comment_author.'</a>';
				$profile_link = sprintf('<a href="%s/memberlist.%s?mode=viewprofile&amp;u=%s">%s</a>', $phpbb_url, $phpEx, $user_id, $comment_author );
			} else if (!$is_logged_in && $user_colour) {
				$profile_link = sprintf('<a style="color:#%s">%s</a>', $user_colour, $comment_author);
			} else {
				$profile_link = sprintf('<a>%s</a>', $comment_author);
			}
		}
	}
	else
	{	
		$profile_link = $comment->comment_author;
	}

	return $profile_link;
}

function wpbb_get_usercolour($userid)
{
	global $config, $db;
		if ($userid > 1) {
			$sql = 'SELECT user_colour
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $userid;
			$result = $db->sql_query($sql, 300);
			$row = $db->sql_fetchrow($result);
			$user_colour = $row['user_colour'];
			$db->sql_freeresult($result);
			if ($user_colour){
				return $user_colour;
			}
			else return '';
		}
}

function wpbb_is_groupmember($userid,$groupid) 
{
	global $config, $db;
	$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE group_id = '.$groupid.' and user_id = ' . $userid;
	$result = $db->sql_query($sql, 60);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if ($row['group_id'] == $groupid)
	{
		return true;
	}
	else 
	{
		return false;
	}
}

function wpbb_pre_get_avatar($avatar, $id_or_email, $args){
	if (is_object($id_or_email)) {
		if (isset($id_or_email->user_id)) {
			$id = $id_or_email->user_id;
		}
		elseif (isset($id_or_email->ID)) {
			$id = $id_or_email->ID;
		}
	}
	elseif (is_numeric($id_or_email)) {
		$id = $id_or_email;
	}
	
	if (isset($id)) {
		$data = wpbb_get_avatar_data($id);
		if ($data['found_avatar']) {
			$height = round($args['size'] / $data['width'] * $data['height']);
			
			$class = ['avatar', 'avatar-' . (int) $args['size'], 'photo' ];
		 		 
			if ( $args['class'] ) {
				if ( is_array( $args['class'] ) ) {
					$class = array_merge( $class, $args['class'] );
				} else {
					$class[] = $args['class'];
				}
			}
			
			$extra_attr = $args['extra_attr'];
			$loading    = $args['loading'];
		 
			if ( in_array( $loading, [ 'lazy', 'eager' ], true ) && ! preg_match( '/\bloading\s*=/', $extra_attr ) ) {
				if ( ! empty( $extra_attr ) ) {
					$extra_attr .= ' ';
				}
		 
				$extra_attr .= sprintf('loading="%s"', $loading);
			}
			
			$avatar = sprintf(
				'<img alt="%s" src="%s" class="%s" height="%d" width="%d" %s/>',
				esc_attr( $args['alt'] ),
				esc_url( $data['url'] ),
				esc_attr( implode( ' ', $class ) ),
				$height,
				(int) $args['width'],
				$extra_attr
			);			
		}
	}
	return $avatar;
}

function wpbb_pre_get_avatar_data($args, $id_or_email){
	if (is_object($id_or_email)) {
		if (isset($id_or_email->user_id)) {
			$id = $id_or_email->user_id;
		}
		elseif (isset($id_or_email->ID)) {
			$id = $id_or_email->ID;
		}
	}
	elseif (is_numeric($id_or_email)) {
		$id = $id_or_email;
	}
	
	if (isset($id)) {
		$data = wpbb_get_avatar_data($id);
		$args = wp_parse_args($data, $args);
	}
	
	return $args;
}

function wpbb_get_avatar_data($userid) 
{
	global $config, $phpbb_url, $db, $phpEx;
	
	$data = ['found_avatar' => false];
	
	if ($userid > 1) {
		$sql = 'SELECT user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $userid;
		$result = $db->sql_query($sql, 300);
		$row = $db->sql_fetchrow($result);
		
		if ($row) {
			switch ($row['user_avatar_type'])
			{
				case 1:
					$url = $phpbb_url . '/download/file.'.$phpEx.'?avatar=' . $row['user_avatar'];
					break;
				case 2:
					$url = $row['user_avatar'];
					break;
				case 3:
					$url = $phpbb_url . "/" . $config['avatar_gallery_path'] . '/' . $row['user_avatar'];
					break;
				default:
					break;
			}
				
			if (isset($url)) {
				$data['size'] = $data['width'] = $row['user_avatar_width'];
				$data['height'] = $row['user_avatar_height'];
				$data['url'] = $url;
				$data['found_avatar'] = true;
			}
		}
		$db->sql_freeresult($result);
	}
	return $data;
}

function wpbb_logout() {
	global $forum_user;
	
	if ($forum_user->data['user_id'] != ANONYMOUS)
		{
			$forum_user->session_kill();
			// $forum_user->session_begin();
		}
	// update_user_meta(wp_get_current_user()->ID, 'session_id', '');
}

function wpbb_get_groups() {
	global $db;
	$sql = 'SELECT group_id, group_type, group_name, group_colour
			FROM ' . GROUPS_TABLE;
	$result = $db->sql_query($sql, 300);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['group_name'] != 'BOTS' && $row['group_name'] != 'GUESTS' )
		$groups[$row['group_id']] = Array(
			'name' => $row['group_name'],
			'type' => $row['group_type'],
			'colour' => $row['group_colour'],
		);
	}
	$db->sql_freeresult($result);
	return $groups;
}

function wpbb_get_available_capabilities() {
	$array = Array(
		'subscriber'    => 1,
		'contributor'   => 2,
		'author'        => 3,
		'editor' 		=> 4,
		'administrator' => 5,
	);
	return $array;
}

?>
