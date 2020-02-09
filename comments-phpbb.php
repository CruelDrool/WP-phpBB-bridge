<?php

// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments-phpbb.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die;

if ( post_password_required() ) {
	return;
}

global $phpbb_url, $phpEx, $user;
?>


<?php if ( have_comments() ) : ?>
<div id="comments">
	<h3><?php comments_number('No Responses', 'One Response', '% Responses' );?> to &#8220;<?php the_title(); ?>&#8221;</h3>

	<div class="navigation">
		<?php 
		$links = paginate_comments_links( Array('prev_text' => '&laquo;', 'next_text' => '&raquo;') );
		if ( $links ) {
			echo $links;
		}
		?>
	</div>

	<ol class="commentlist">
		<?php 
		$avatar_size = get_option('wpbb_commentsavatarsize');
		wpbb_list_comments(Array('avatar_size'=>( ($avatar_size != '') ? (int) $avatar_size : 48 ) )); ?>
	</ol>

	<div class="navigation">
		<?php 
		$links = paginate_comments_links( Array('prev_text' => '&laquo;', 'next_text' => '&raquo;') );
		if ( $links ) {
			echo $links;
		}
		?>
	</div>
</div>
<?php endif; ?>

<?php 

if ('open' == $post->comment_status) {

	$post_id = get_the_ID();
	$commenter = wp_get_current_commenter();
	$html5 = true;
	$html_req = ( $req ? " required='required'" : '' );
	$comment_field = sprintf('<p class="comment-form-comment">%s</p>','<textarea id="comment" name="comment" cols="45" rows="10" maxlength="65525" required="required"></textarea>');
		
	$fields = array(
		'author' => sprintf(
			'<p class="comment-form-author">%s %s</p>',
			sprintf(
				'<input id="author" name="author" type="text" value="%s" size="30" maxlength="245"%s />',
				esc_attr( $commenter['comment_author'] ),
				$html_req
			),
			sprintf(
				'<label for="author"><small>%s%s</small></label>',
				__( 'Name' ),
				( $req ? ' <span class="required">*</span>' : '' )
			)
		),
		'email'  => sprintf(
			'<p class="comment-form-email">%s %s</p>',
			sprintf(
				'<input id="email" name="email" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes"%s />',
				( $html5 ? 'type="email"' : 'type="text"' ),
				esc_attr( $commenter['comment_author_email'] ),
				$html_req
			),
			sprintf(
				'<label for="email"><small>%s%s</small></label>',
				__( 'Email' ),
				( $req ? ' <span class="required">*</span>' : '' )
			)
		),
		'url'    => sprintf(
			'<p class="comment-form-url">%s %s</p>',
			sprintf(
				'<input id="url" name="url" %s value="%s" size="30" maxlength="200" />',
				( $html5 ? 'type="url"' : 'type="text"' ),
				esc_attr( $commenter['comment_author_url'] )
			),
			sprintf(
				'<label for="url"><small>%s</small></label>',
				__( 'Website' )
			)

		),
		// I want the comment field to be the last field.
		'comment_field' => $comment_field,
	);
	
		
	$comment_form_args = array (
		'fields' => $fields,
		'logged_in_as'         => sprintf(
			'<p class="logged-in-as">%s</p>',
			sprintf(
				__( 'Logged in as <a href="%1$s" aria-label="%2$s" style="font-weight: bold;color:#' . $user->data['user_colour'] . '">%3$s</a>. <a href="%4$s">Log out?</a>' ),
				$phpbb_url.'/ucp.'.$phpEx,
				esc_attr( sprintf( __( 'Logged in as %s. Edit your profile.' ), $user_identity ) ),
				$user_identity,
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
			)
		),
		'comment_field' => '',
		'must_log_in'          => sprintf(
			'<p class="must-log-in">%s</p>',
			sprintf(
				__( 'You must be <a href="%s" onclick="document.getElementById(\'showlogin\').style.display = \'\'">logged in</a> to post a comment.' ),
				'#commentlogin'
			)
		),
	);
	
	if (is_user_logged_in()) {
		// The comment field will disappear when the user is logged in, so I'm doing the ol' switcheroo here.
		$comment_form_args['fields']['comment_field'] = '';
		$comment_form_args['comment_field'] = $comment_field;
	}

	comment_form($comment_form_args);
	
	if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
?>

		<a name="commentlogin"></a>
		<div>
		<div id="showlogin" style="display: none">
			<h3><?php echo $user->lang['LOGIN'] ?></h3>
			<small><a href="#commentlogin" onclick="document.getElementById('showlogin').style.display = 'none'">Click here to cancel login</a><br/></small>				
			<?php
			$prefix = "comment_";
			$login_form_args = array(
				'form_id'        => $prefix . 'loginform',
				'id_username'     => $prefix . 'user_login',
				'id_password'     => $prefix . 'user_pass',
				'id_remember'     => $prefix . 'remember_me',
				'id_viewonline'   => $prefix . 'view_online',
				'id_submit'       => $prefix . 'user_submit',
				'redirect'        => apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ),
			);
			wpbb_login_form($login_form_args)
			?>
		</div>
		</div>
<?php 
	}
} 
?>