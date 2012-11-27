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
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// which parent should I get a menu for?
		$menu_parent_id = 0;
		if ($post->post_parent) {
			$menu_parent_id = $post->post_parent;
		} else {
			$menu_parent_id = $post->ID;
		}	
		
		// do we need to display the menu at all?
		$children = get_pages('child_of='.$menu_parent_id);
		if( count( $children ) != 0 ) { 
		
			// get the menu
			$args = array(
			  'container' => '', 
			  'child_of' => $menu_parent_id,
			  'echo' => false,
			);
			$subpage_list = wp_nav_menu($args);
	
			// apply variables
			$title = str_replace($title, '@@title@@', get_the_title($menu_parent_id));
			
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
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );

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
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		if ( isset( $instance[ 'taxonomy' ] ) ) {
			$taxonomy = $instance[ 'taxonomy' ];
		}
		else {
			$taxonomy = __( 'Enter taxonomy slug here', 'text_domain' );
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        <label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" type="text" value="<?php echo esc_attr( $taxonomy ); ?>" />
		</p>
		<?php 
	}

} // class Foo_Widget

// register Foo_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "bms_page_submenu_widget" );' ) );

?>