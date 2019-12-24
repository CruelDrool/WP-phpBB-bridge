<?php
/*
This widget is heavily based upon Mike Jolley's (http://blue-anvil.com) plugin, Sidebar Login (http://wordpress.org/extend/plugins/sidebar-login/).
*/

class SidebarLoginWidget extends WP_Widget {
	
	private $instance = '';
	private $user     = null;
	private $options  = array();
	
	function __construct(){
		/* Widget settings. */
		$widget_ops = array('description' => 'Adds a sidebar login that will log you into your forum.' );

		/* Create the widget. */
		parent::__construct('wp_sidebarlogin', 'Sidebar Login (CruelDrool Bridge!)', $widget_ops);
	}

	private function current_url( $url = '' ) {
		return wpbb_current_url($url);
	}

    public function replace_tags( $text ) {
		global $phpbb_url, $phpEx, $user, $auth;
		$text = str_replace(
			array(
				'%username%',
				'%userid%',
				'%firstname%',
				'%lastname%',
				'%name%',
				'%admin_url%',
				'%ucp_text%',
				'%ucp_url%',
				'%pm_text%',
				'%pm_url%',
				'%logout_text%',
				'%logout_url%',
				'%acp_text%',
				'%acp_url%',
				),
			array(
				ucwords( $this->user->display_name ),
				$this->user->ID,
				$this->user->first_name,
				$this->user->last_name,
				trim( $this->user->first_name . ' ' . $this->user->last_name ),
				untrailingslashit( admin_url() ),
				$user->lang['PROFILE'],
				$phpbb_url.'/ucp.'.$phpEx,
				wpbb_get_privmsg(),
				$phpbb_url.'/ucp.'.$phpEx.'?i=pm&amp;folder=inbox',
				$user->lang['LOGOUT'],
				wp_logout_url( $this->current_url( 'nologout' ) ),
				$user->lang['ACP'],
				$phpbb_url.'/adm/index.php?sid='.$user->session_id,
			),
			$text
		);
		
	    $text = do_shortcode( $text );
		
	    return $text;
    }
	
	
    public function define_options() {
	    // Define options for widget
		$this->options       = array(
			'header-logged_out'   => array(
				'type'            => 'header',
				'value'           => 'Logged out',
			),
			'logged_out_title'    => array(
				'label'           => 'Heading',
				'default'         => 'Login',
				'type'            => 'text'
			),
			'show_register_link'  => array(
				'label'           => 'Show Register Link',
				'default'         => 1,
				'type'            => 'checkbox'
			),
			'show_lost_password_link'  => array(
				'label'           => 'Show Lost Password Link',
				'default'         => 1,
				'type'            => 'checkbox'
			),
			'header-logged_in'    => array(
				'type'            => 'header',
				'value'           => 'Logged in',
			),
			'logged_in_title'     => array(
				'label'           => 'Heading',
				'default'         => 'Welcome, %username%',
				'type'            => 'text'
			),
			'logged_in_links'     => array(
				'label'           => 'Links to show (<code>Text | HREF | Capability</code>)',
				'description'     => '<a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Capability</a> (optional) refers to the type of user who can view the link.<br /><br />
Available tags: 
<code>%username%</code>,
<code>%userid%</code>,
<code>%firstname%</code>,
<code>%lastname%</code>,
<code>%name%</code>,
<code>%admin_url%</code>,
<code>%ucp_text%</code>,
<code>%ucp_url%</code>,
<code>%pm_text%</code> (will show new/unread PMs),
<code>%pm_url%</code>,
<code>%logout_text%</code>,
<code>%logout_url%</code>,
<code>%acp_text%</code>,
<code>%acp_url%</code> (this link will be hidden if the user isn\'t an administrator).',
				'default'         => "Dashboard | %admin_url%\nAdd new post | %admin_url%/post-new.php | administrator, editor, author, contributor\n%ucp_text% | %ucp_url%\n%pm_text% | %pm_url%\n%logout_text% | %logout_url%",
				'type'            => 'textarea'
			),
			'show_avatar'         => array(
				'label'           => 'Show avatar',
				'default'         => 1,
				'type'            => 'checkbox'
			),
			'avatar_size'         => array(
				'label'           => 'Avatar size',
				'default'         => '64',
				'type'            => 'number'
			),
			'avatar_style'       => array(
				'label'           => 'Avatar CSS',
				'default'         => 'float: right; margin: 0px 0px 5px 5px',
				'type'            => 'text',
				'description'     => 'Extra <a href="https://www.w3schools.com/css/" target="_blank">CSS</a> settings for the avatar.'
			),
			// 'header-other'        => array(
				// 'type'            => 'header',
				// 'value'           => 'Other',
			// ),
			// 'use_stylesheet'      => array(
				// 'label'           => 'Use stylesheet',
				// 'default'         => 1,
				// 'type'            => 'checkbox'
			// ),
			// 'stylesheet'          => array(
				// 'label'           => 'Stylesheet',
				// 'default'         => ".widget_wp_sidebarlogin, #sidebar-login {\n	overflow: hidden;\n}\n.widget_wp_sidebarlogin .avatar_container, #sidebar-login .avatar_container {\n	float:right;\n}\n.widget_wp_sidebarlogin ul {\n	list-style: none outside !important;\n}\n.widget_wp_sidebarlogin .avatar_container img, #sidebar-login .avatar_container img {\n	/*padding: 3px;\n	border: 1px solid #ddd;*/\n	-moz-border-radius: 4px;\n	-webkit-border-radius: 4px;\n	margin-right: 8px;\n	margin-top: 5px;\n}\n.widget_wp_sidebarlogin hr {\n	display: block;\n	clear: both; \n	border: 0; \n	border-top: 1px solid #999; \n	height: 1px;\n}",
				// 'type'            => 'textarea'
			// ),
		);
    }
	
	public function widget($args, $instance) {    
	
		// Filter can be used to conditonally hide the widget
		if ( ! apply_filters( 'sidebar_login_widget_display', true ) ) {
			return;
		}

		// Record $instance
		$this->instance = $instance;
				
		//Get options
		$this->define_options();
		
		// global $user_ID, $phpbb_url, $phpEx, $config, $user;
		// global $auth, $user;
		
		$defaults = array(
			'logged_in_title'         => ! empty( $instance['logged_in_title'] ) ? $instance['logged_in_title'] : $this->options['logged_in_title']['default'],
			'logged_out_title'        => ! empty( $instance['logged_out_title'] ) ? $instance['logged_out_title'] : $this->options['logged_out_title']['default'],
			'logged_in_links'         => ! empty( $instance['logged_in_links'] ) ? $instance['logged_in_links'] : $this->options['logged_in_links']['default'],
			// 'use_stylesheet'          => isset( $instance['use_stylesheet'] ) ? $instance['use_stylesheet'] : $this->options['use_stylesheet']['default'],
			// 'stylesheet'              => ! empty( $instance['stylesheet'] ) ? $instance['stylesheet'] : $this->options['stylesheet']['default'],
			'show_avatar'             => isset( $instance['show_avatar'] ) ? $instance['show_avatar'] : $this->options['show_avatar']['default'],
			'avatar_size'             => isset( $instance['avatar_size'] ) ? $instance['avatar_size'] : $this->options['avatar_size']['default'],
			'avatar_style'          => ! empty( $instance['avatar_style'] ) ? $instance['avatar_style'] : $this->options['avatar_style']['default'],
			'show_register_link'      => isset( $instance['show_register_link'] ) ? $instance['show_register_link'] : $this->options['show_register_link']['default'],
			'show_lost_password_link' => isset( $instance['show_lost_password_link'] ) ? $instance['show_lost_password_link'] : $this->options['show_lost_password_link']['default'],
		);
		
		$args = array_merge($defaults, $args);
		extract($args);
		
		
		echo $before_widget;
		// if ($use_stylesheet) {echo '<style type="text/css">'. "\n" . $stylesheet . "\n" .'</style>';}

		// if ($user_ID != '') {
		if ( is_user_logged_in() ) {	
		
			$this->user = get_user_by( 'id', get_current_user_id() );
			
			echo $before_title . $this->replace_tags($logged_in_title). $after_title;
			
			// if ($show_avatar) echo '<div class="avatar_container">'.get_avatar($this->user->ID, $size = $avatar_size).'</div>';
			if ($show_avatar) echo get_avatar($this->user->ID, $avatar_size, '', '', array('extra_attr' => 'style="' . esc_attr( $avatar_style ) .'"'));
						
		    $raw_links = array_map( 'trim', explode( "\n", $logged_in_links ) );
		    $links = array();
		    foreach ( $raw_links as $link ) {
		    	$link     = array_map( 'trim', explode( '|', $link ) );
		    	$link_cap = '';

		    	if ( sizeof( $link ) == 3 ) {
					list( $link_text, $link_href, $link_cap ) = $link;
		    	} elseif ( sizeof( $link ) == 2 ) {
					list( $link_text, $link_href ) = $link;
		    	} else {
					continue;
		    	}

				// Check capability
				if ( ! empty( $link_cap ) ) {
					$link_cap = array_map( 'trim', explode( ',', $link_cap ) );
					$has_cap = false;
					foreach ($link_cap as $cap) {
						if ( current_user_can( strtolower( $cap ) ) ) {
							$has_cap = true;
							break;
						}
					}
					
					if ( ! $has_cap ) {
						continue;
					}
				}
				
				if ( ($link_href == '%acp_url%' or $link_text == '%acp_url%') && !current_user_can('administrator') ) {
					continue;
				}
				
				$links[ sanitize_title( $link_text ) ] = array(
					'text' => $link_text,
					'href' => $link_href
				);
		    }
									
			if ( ! empty( $links ) && is_array( $links ) && sizeof( $links ) > 0 ) {
				echo '<ul class="pagenav">';
				foreach ( $links as $id => $link ) {
					echo '<li class="page_item"><a href="' . esc_url(  $this->replace_tags ( $link['href'] ) ) . '">' . wp_kses_post( $this->replace_tags ( $link['text'] ) ) . '</a></li>';
				}
				echo '</ul>';
			}
		} else {
			// User is NOT logged in!!!
						
			echo $before_title .'<span>'. $logged_out_title .'</span>' . $after_title;
			$login_form_args = array(
				'user_reg_link'   => $show_register_link,
				'lost_pass_link'  => $show_lost_password_link,	
			);
			wpbb_login_form($login_form_args );
		}		
			
		// echo widget closing tag
		echo $after_widget;
	}
	
	public function form($instance) {
		$this->define_options();

		foreach ( $this->options as $name => $option ) {

			if ( $option['type'] == 'break' ) {
				echo '<hr style="border: 1px solid #ddd; margin: 1em 0" />';
				continue;
			}
			
			if ( $option['type'] == 'header' ) {
				echo '<h2>' . esc_attr( $option['value'] ) . '</h2>';
				continue;
			}
			
			if ( ! isset( $instance[ $name ] ) ) {
				$instance[ $name ] = $option['default'];
			}

			if ( empty( $option['placeholder'] ) ) {
				$option['placeholder'] = $option['default'];
			}

			echo '<p>';

			switch ( $option['type'] ) {
				case "text" :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"><?php echo wp_kses_post( $option['label'] ) ?>:</label><br />
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>" value="<?php echo esc_attr( $instance[ $name ] ); ?>" />
					<?php
				break;
				case "checkbox" :
					?>
					<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" <?php checked( $instance[ $name ], 1 ) ?> value="1" />
					<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"> <?php echo wp_kses_post( $option['label'] ) ?></label>
					<?php
				break;
				case "radio" :
					echo wp_kses_post( $option['label'] ).":";

					foreach ( $option['options'] as $label => $value ) {
						$id = "$name-$value";
						?>
						<input type="radio" id="<?php echo esc_attr( $this->get_field_id( $id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" value="<?php echo esc_attr ( $value ); ?>" <?php checked( $instance[ $name ], $value ) ?> />
						<label for="<?php echo esc_attr( $this->get_field_id( $id ) ); ?>"><?php echo wp_kses_post( $label ); ?>&nbsp;&nbsp;</label>
						<?php
					}
				break;
				case "number" :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"><?php echo wp_kses_post( $option['label'] ) ?>: </label>
					<input type="number" class="small-text" id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" value="<?php echo esc_attr( $instance[ $name ] ); ?>" step="1" min="1" />
					<?php
				break;
				case "textarea" :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"><?php echo wp_kses_post( $option['label'] ) ?>:</label><br />
					<textarea class="widefat" cols="20" rows="6" id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"><?php echo esc_textarea( $instance[ $name ] ); ?></textarea>
					<?php
				break;
			}

			if ( ! empty( $option['description'] ) ) {
				echo '<span class="description" style="display:block; padding-top:.25em">' . wp_kses_post( $option['description'] ) . '</span>';
			}

			echo '</p>';
		}
	}
	
	public function update( $new_instance, $old_instance ) {
		$this->define_options();

		foreach ( $this->options as $name => $option ) {
			if ( $option['type'] == 'break' ) {
				continue;
			}

			$instance[ $name ] = strip_tags( stripslashes( $new_instance[ $name ] ) );
		}
		return $instance;	
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'SidebarLoginWidget' );
});
?>