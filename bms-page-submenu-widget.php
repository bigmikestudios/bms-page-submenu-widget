<?php
/**
 * @author Mike Lathrop
 * @version 0.0.1
 */
/*
Plugin Name: BMS Page Submenu Widget
Plugin URI: http://bigmikestudios.com
Depends: 
Description: Adds a simple widget for displaying pages in the same branch in the page hierarchy.
Version: 0.0.1
Author URI: http://bigmikestudios.com
*/

// =============================================================================

//////////////////////////
//
// WIDGETS
//
//////////////////////////

class Bms_Page_Submenu_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'bms_page_submenu_widget', // Base ID
			'BMS Page Submenu Widget', // Name
			array( 'description' => __( 'Creates a simple widget to display pages in the same branch in the page hierarchy.', 'text_domain' ), ) // Args 
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;
		if (isset($post->ID)) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			$depth = intval($instance['depth']);
			$include_parent = $instance['include_parent'];
			
			// What depth am I?
			$parent_id  = isset($post->post_parent) ? $post->post_parent : NULL;
			$my_depth = 0;
			while ($parent_id > 0) {
				$page = get_page($parent_id);
				$parent_id = $page->post_parent;
				$my_depth++;
			}
			
			// if I have children, then I am the parent.
			$children = get_pages(array('child_of' => $post->ID));
			$is_parent = (count( $children ) != 0 ) ? true : false;
			$is_child = ($post->post_parent) ? true : false;
			$siblings = get_pages(array('child_of' => $post->post_parent));
			$include = array();
			
			// what goes in the menu?
			if( $is_parent && ($my_depth == $depth) ) { 
				foreach($children as $child) {
					$include[] = $child->ID;
				}
				if ($include_parent == 'checked') $include[] = $post->ID;
			} else if ( count($siblings) != 0 && $my_depth == ($depth+1) ) {
				foreach ($siblings as $sib) {
					$include[] = $sib->ID;
				}
				if ($include_parent == 'checked') $include[] = $post->post_parent;
			}
			
			if ( $include != '' ) {
				// get the menu
				$args = array(
				  'include' => $include,
				  'echo' => false,
				  'posts_per_page' => 999,
				);
				$subpage_list = wp_page_menu($args);
		
				// apply variables
				$title = str_replace('@@title@@', get_the_title($parent_id), $title);
				
				if ($subpage_list) {
					echo $before_widget;
					if ( ! empty( $title ) )
						echo $before_title . $title . $after_title;
			
					?>
						<?php echo($subpage_list); ?>
					<?php
					echo $after_widget;
				}
			}
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['depth'] = intval( $new_instance['depth'] );
		if ($new_instance['include_parent']=='checked') $instance['include_parent'] = 'checked';
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'text_domain' );
		}
		
		if ( isset( $instance[ 'depth' ] ) ) {
			$depth = $instance[ 'depth' ];
		} else {
			$depth = 0;
		}
		if ( isset( $instance[ 'include_parent' ] ) ) {
			$include_parent = $instance[ 'include_parent' ];
			$include_parent = ($include_parent=='checked') ? 'checked' : '';
		} else {
			$include_parent = '';
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /><br /><small>Use <em>@@title@@</em> to include the title of the parent page</small>
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'include_parent' ); ?>"><?php _e( 'Include parent page in menu?' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'include_parent' ); ?>" name="<?php echo $this->get_field_name( 'include_parent' ); ?>" type="checkbox" value="checked" <?php echo $include_parent; ?> />
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'depth' ); ?>"><?php _e( 'Depth:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'depth' ); ?>" name="<?php echo $this->get_field_name( 'depth' ); ?>" type="text" value="<?php echo esc_attr( $depth ); ?>" />
		</p>
		<?php 
	}

} // class Foo_Widget

// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "bms_page_submenu_widget" );' ) );

?>