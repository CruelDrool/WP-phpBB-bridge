<?php
class Wpbb_Walker_Comment extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since unknown
	 * @var string
	 */
	var $tree_type = 'comment';

	/**
	 * @see Walker::$db_fields
	 * @since unknown
	 * @var array
	 */
	var $db_fields = array ('parent' => 'comment_parent', 'id' => 'comment_ID');

	/**
	 * @see Walker::start_lvl()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;

		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "<ol class='children'>\n";
				break;
			default:
			case 'ul':
				echo "<ul class='children'>\n";
				break;
		}
	}

	/**
	 * @see Walker::end_lvl()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args Will only append content if style argument value is 'ol' or 'ul'.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;

		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "</ol>\n";
				break;
			default:
			case 'ul':
				echo "</ul>\n";
				break;
		}
	}

	/**
	 * This function is designed to enhance Walker::display_element() to
	 * display children of higher nesting levels than selected inline on
	 * the highest depth level displayed. This prevents them being orphaned
	 * at the end of the comment list.
	 *
	 * Example: max_depth = 2, with 5 levels of nested content.
	 * 1
	 *  1.1
	 *    1.1.1
	 *    1.1.1.1
	 *    1.1.1.1.1
	 *    1.1.2
	 *    1.1.2.1
	 * 2
	 *  2.2
	 *
	 */
	function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];
		$id = $element->$id_field;

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );

		// If we're at the max depth, and the current element still has children, loop over those and display them at this level
		// This is to prevent them being orphaned to the end of the list.
		if ( $max_depth <= $depth + 1 && isset( $children_elements[$id]) ) {
			foreach ( $children_elements[ $id ] as $child )
				$this->display_element( $child, $children_elements, $max_depth, $depth, $args, $output );

			unset( $children_elements[ $id ] );
		}

	}

	/**
	 * @see Walker::start_el()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment Comment data object.
	 * @param int $depth Depth of comment in reference to parents.
	 * @param array $args
	 */
	// function start_el(&$output, $comment, $depth, $args) {
	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;

		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $object, $args, $depth);
			return;
		}

		$GLOBALS['comment'] = $object;
		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
		<?php endif; ?>
		<div class="comment-author vcard">
		<?php if ($args['avatar_size'] != 0) echo get_avatar( $object, $args['avatar_size'] ); ?>
		<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), wpbb_get_profile_link($object)) ?>
		</div>
		<?php if ($object->comment_approved == '0') : ?>
				<em><?php _e('Your comment is awaiting moderation.') ?></em>
				<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $object->comment_ID ) ) ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','' );
			?>
		</div>

		<?php comment_text() ?>

		<div class="reply">
		<?php wpbb_comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>

		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
	}

	/**
	 * @see Walker::end_el()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment
	 * @param int $depth Depth of comment.
	 * @param array $args
	 */
	// function end_el(&$output, $comment, $depth, $args) {
	function end_el( &$output, $object, $depth = 0, $args = array() ) {
		if ( !empty($args['end-callback']) ) {
			call_user_func($args['end-callback'], $object, $args, $depth);
			return;
		}
		if ( 'div' == $args['style'] )
			echo "</div>\n";
		else
			echo "</li>\n";
	}

}
?>