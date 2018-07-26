<?php

/**
 * A widget plugin class.
 *
 * This widget displays a list of books with meta data and info
 *
 *
 * @since      1.0.0
 * @package    MP_Books
 * @subpackage MP_Books
 * @author     Will Roscoe
 */

class mp_book_titles_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        
        // Base ID of your widget
        'mp_book_titles_widget', 
        
        // Widget name will appear in UI
        __('Book List', 'mp_book_titles_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'List all books', 'mp_book_titles_widget_domain' ), ) 
        );
    }
    
    // Creating widget front-end
    public function widget( $args, $instance ) {

        extract( $args );

        // Check the widget options
        $title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $select_orderby = isset( $instance['select_orderby'] ) ? $instance['select_orderby'] : '';
        $select_order = isset( $instance['select_order'] ) ? $instance['select_order'] : '';
        $checkbox_showimage = ! empty( $instance['checkbox_showimage'] ) ? $instance['checkbox_showimage'] : false;
        $checkbox_hideauthors = ! empty( $instance['checkbox_hideauthors'] ) ? $instance['checkbox_hideauthors'] : false;
        $checkbox_hidetitle = ! empty( $instance['checkbox_hidetitle'] ) ? $instance['checkbox_hidetitle'] : false;
        $checkbox_showsubtitle = ! empty( $instance['checkbox_showsubtitle'] ) ? $instance['checkbox_showsubtitle'] : false;

        // WordPress core before_widget hook (always include )
        echo $before_widget;

        // Display widget title if defined
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
        
        // This is where you run the code and display the output
        // https://codex.wordpress.org/Function_Reference/get_posts
        $args = array( 'post_type' => 'mp_book', 'posts_per_page' => 30, 'post_status' => 'publish', 'orderby' => $select_orderby, 'order' => $select_order ); // orderby: title/date/author
        global $post;
        $thebooks = get_posts( $args );
        echo __( '<div class="widget-books">', 'mp_book_titles_widget_domain' );
        foreach ( $thebooks as $post ) : setup_postdata( $post );
            $book_authors = get_post_meta(get_the_ID(), "book_authors", true);
            $book_subtitle = get_post_meta(get_the_ID(), "book_subtitle", true); ?>
                <div class="widget-book">
                    <?php if (!$checkbox_hidetitle) { ?>
                        <div class="widget-book-title"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></div>
                    <?php } ?>
                    <?php if ($checkbox_showsubtitle && strlen($book_subtitle) > 0) { ?>
                        <div class="widget-book-subtitle"><?php echo $book_subtitle; ?></div>
                    <?php } ?>
                    <?php if (!$checkbox_hideauthors) { ?>
                        <div class="widget-book-authors"><?php echo $book_authors; ?></div>
                    <?php } ?>
                    <?php if ($checkbox_showimage) {
                        matteringpress_post_thumbnail();
                    } ?>
                </div>
            <?php
        endforeach; 
        wp_reset_postdata();
        echo __( '</div>', 'mp_book_titles_widget_domain' );

        // WordPress core after_widget hook (always include )
	    echo $after_widget;
    }
    
    // Widget Backend 
    public function form( $instance ) {
        // Set widget defaults
        $defaults = array(
            'title'    => 'Books',
            'checkbox_showimage' => '',
            'checkbox_hideauthors' => '',
            'checkbox_hidetitle' => '',
            'checkbox_showsubtitle' => '',
            'select_orderby' => 'date',
            'select_order' => 'DESC',
        );
        
        // Parse current settings with defaults
	    extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

        <?php // Widget Title ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'text_domain' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            
            <?php // Checkbox - hide title ?>
            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'checkbox_hidetitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox_hidetitle' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox_hidetitle ); ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'checkbox_hidetitle' ) ); ?>"><?php _e( 'Hide Book title', 'text_domain' ); ?></label>
            </p>

            <?php // Checkbox - hide authors ?>
            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'checkbox_hideauthors' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox_hideauthors' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox_hideauthors ); ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'checkbox_hideauthors' ) ); ?>"><?php _e( 'Hide Authors', 'text_domain' ); ?></label>
            </p>
            <?php // Checkbox - show subtitle ?>
            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'checkbox_showsubtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox_showsubtitle' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox_showsubtitle ); ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'checkbox_showsubtitle' ) ); ?>"><?php _e( 'Show subtitle', 'text_domain' ); ?></label>
            </p>
            <?php // Checkbox - show cover thumbnail ?>
            <p>
                <input id="<?php echo esc_attr( $this->get_field_id( 'checkbox_showimage' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox_showimage' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox_showimage ); ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'checkbox_showimage' ) ); ?>"><?php _e( 'Show cover thumbnail', 'text_domain' ); ?></label>
            </p>

            <?php // Dropdown - order by ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'select_orderby' ); ?>"><?php _e( 'Order by', 'text_domain' ); ?></label>
                <select name="<?php echo $this->get_field_name( 'select_orderby' ); ?>" id="<?php echo $this->get_field_id( 'select_orderby' ); ?>" class="widefat">
                <?php
                // Your options array
                $options = array(
                    'date' => __( 'Published date', 'text_domain' ),
                    'title' => __( 'Title', 'text_domain' ),
                );

                // Loop through options and add each one to the select dropdown
                foreach ( $options as $key => $name ) {
                    echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $select_orderby, $key, false ) . '>'. $name . '</option>';

                } ?>
                </select>
            </p>

            <?php // Dropdown order ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'select_order' ); ?>"><?php _e( 'Order', 'text_domain' ); ?></label>
                <select name="<?php echo $this->get_field_name( 'select_order' ); ?>" id="<?php echo $this->get_field_id( 'select_order' ); ?>" class="widefat">
                <?php
                // Your options array
                $options = array(
                    'DESC' => __( 'Descending', 'text_domain' ),
                    'ASC' => __( 'Ascending', 'text_domain' ),
                );

                // Loop through options and add each one to the select dropdown
                foreach ( $options as $key => $name ) {
                    echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $select_order, $key, false ) . '>'. $name . '</option>';

                } ?>
                </select>
            </p>
        <?php
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        $instance['checkbox_showimage'] = isset( $new_instance['checkbox_showimage'] ) ? 1 : false;
        $instance['checkbox_hideauthors'] = isset( $new_instance['checkbox_hideauthors'] ) ? 1 : false;
        $instance['checkbox_hidetitle'] = isset( $new_instance['checkbox_hidetitle'] ) ? 1 : false;
        $instance['checkbox_showsubtitle'] = isset( $new_instance['checkbox_showsubtitle'] ) ? 1 : false;
        $instance['select_orderby'] = isset( $new_instance['select_orderby'] ) ? wp_strip_all_tags( $new_instance['select_orderby'] ) : '';
        $instance['select_order'] = isset( $new_instance['select_order'] ) ? wp_strip_all_tags( $new_instance['select_order'] ) : '';
        return $instance;
    }
}

?>