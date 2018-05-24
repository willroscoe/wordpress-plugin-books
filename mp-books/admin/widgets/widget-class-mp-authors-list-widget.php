<?php

/**
 * A widget plugin class.
 *
 * This widget displays a list of blog authors
 *
 *
 * @since      1.0.0
 * @package    MP_Books
 * @subpackage MP_Books
 * @author     Will Roscoe
 */

class mp_authors_list_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        
        // Base ID of your widget
        'mp_authors_list_widget', 
        
        // Widget name will appear in UI
        __('Authors List', 'mp_authors_list_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'Display a list of all blog authors', 'mp_authors_list_widget_domain' ), ) 
        );
    }
    
    // Creating widget front-end
    public function widget( $args, $instance ) {
        global $post;
        extract( $args );

        // Check the widget options
        $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';

        // WordPress core before_widget hook (always include )
        echo $before_widget;

        // Display widget title if defined
		if ( $title != "" ) {
			echo $before_title . $title . $after_title;
		}

        // display author list here
        if(function_exists('coauthors_wp_list_authors')) {
            echo '<ul>';
		    coauthors_wp_list_authors(array(
            'show_fullname' => 1,
            'optioncount'   => FALSE,
            'exclude_admin' => TRUE
                )); 
            echo '</ul>';
        } else {
            echo '<ul>';
		    wp_list_authors(array(
            'show_fullname' => 1,
            'optioncount'   => FALSE,
            'exclude_admin' => TRUE
                )); 
            echo '</ul>';
        }

        // WordPress core after_widget hook (always include )
	    echo $after_widget;
    }
    
    // Widget Backend 
    public function form( $instance ) {
        // Set widget defaults
        $defaults = array(
            'title'    => '',
        );
        
        // Parse current settings with defaults
	    extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

        <?php // Widget Title ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'text_domain' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
        <?php
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

?>