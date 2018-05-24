<?php

/**
 * A widget plugin class.
 *
 * This widget displays book info and meta data
 *
 *
 * @since      1.0.0
 * @package    MP_Books
 * @subpackage MP_Books
 * @author     Will Roscoe
 */

class mp_book_blog_post_meta_data_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        
        // Base ID of your widget
        'mp_book_blog_post_meta_data_widget', 
        
        // Widget name will appear in UI
        __('Blog Post Meta Data', 'mp_book_blog_post_meta_data_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'Just displays the title', 'mp_book_blog_post_meta_data_widget_domain' ), ) 
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

        // display meta data here
        $author_avatar_size = apply_filters( 'matteringpress_author_avatar_size', 100 );
        
        if(function_exists('coauthors')) {
            $allauthors = get_coauthors();
            if (count($allauthors) > 1) {
                echo get_avatar('mp@matteringpress.org', $author_avatar_size );
            }
            elseif (count($allauthors) == 1) {
                echo get_avatar($allauthors[0]->user_email, $author_avatar_size );
            }
            
        } else {
            echo get_avatar(get_the_author_meta( 'user_email' ), $author_avatar_size );
        }
        
        echo '<div class="widget-post-authors-container">by <span class="widget-post-authors">';
        if(function_exists('coauthors_posts_links')) {
            coauthors_posts_links();
        } else {
            the_author();
        }
        echo '</span></div>';

        echo '<h2 class="widget-title">Published</h2>';
        matteringpress_entry_date_no_link();

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
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Override Title', 'text_domain' ); ?></label>
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
