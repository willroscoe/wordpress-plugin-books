<?php

/**
 * A widget plugin class.
 *
 * This widget displays page titles
 *
 *
 * @since      1.0.0
 * @package    MP_Books
 * @subpackage MP_Books
 * @author     Will Roscoe
 */

class mp_book_cms_page_title_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        
        // Base ID of your widget
        'mp_book_cms_page_title_widget', 
        
        // Widget name will appear in UI
        __('Page Title', 'mp_book_cms_page_title_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'Just displays the current page title', 'mp_book_cms_page_title_widget_domain' ), ) 
        );
    }
    
    // Creating widget front-end
    public function widget( $args, $instance ) {
        global $post;
        extract( $args );

        // Check the widget options
        $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $checkbox_toplevel = ! empty( $instance['checkbox_toplevel'] ) ? $instance['checkbox_toplevel'] : false;

        if ($title == "") // get the title of the top level page
        {
            if ($checkbox_toplevel)
            {
                $parents = array_reverse( get_ancestors( $the_post->ID, 'page' ) );
                $title = get_the_title( $parents[0] );
            }
            else
            {
                $title = the_title();
            }
        }

        // WordPress core before_widget hook (always include )
        echo $before_widget;

        // Display widget title if defined
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

        // WordPress core after_widget hook (always include )
	    echo $after_widget;
    }
    
    // Widget Backend 
    public function form( $instance ) {
        // Set widget defaults
        $defaults = array(
            'title'    => '',
            'checkbox_toplevel' => '',
        );
        
        // Parse current settings with defaults
	    extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

        <?php // Widget Title ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Override Title', 'text_domain' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            <?php // Checkbox - top level ?>
            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'checkbox_toplevel' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox_toplevel' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox_toplevel ); ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'checkbox_toplevel' ) ); ?>"><?php _e( 'Display top level title', 'text_domain' ); ?></label>
            </p>
        <?php
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        $instance['checkbox_toplevel'] = isset( $new_instance['checkbox_toplevel'] ) ? 1 : false;
        return $instance;
    }
}

?>