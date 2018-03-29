<?php
 
/*
Plugin Name: Book Post Type + ePub Reader Post Type Plugin
Plugin URI: http://github.com/willroscoe
Description: 'Book' custom post type + allow users to read ePub books on your site.
Version: 1.0
Author: Will Roscoe
Author URI: http://github.com/willroscoe
License: MIT
*/
 
add_action( 'init', 'book_custom_post_type' );
 
function book_custom_post_type() {
    register_post_type( 'wr_book',
        array(
            'labels' => array(
                'name' => __( 'Books' ),
                'singular_name' => __( 'Book' )
            ),
            'public' => true,
            'has_archive' => true,
            'can_export' => true,
            'query_var' => true,
            'supports' => array('title',
                                'editor',
                                'excerpt',
                                'thumbnail',
                                'author',
                                'trackbacks',
                                //'custom-fields',
                                //'comments',
                                'revisions',
                                //'page-attributes', // (menu order, hierarchical must be true to show Parent option)
                                //'post-formats',),
                                ),
            'rewrite' => array('slug' => 'books'),
        )
    );
}

/**************************
 Additional book meta fields
    - Sub-Title
    - Author(s)
    - Thumbnail image
    - Enable read online
    - Buy online link
    - ePub file link
    - PDF download file link
    - mobi (Kindle) download file link
*/

/*************
Book Sub-title
*/
function book_subtitle_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "book_subtitle_nonce");
    ?>
        <div>
            <label for="book_subtitle">Sub-title</label>
            <textarea name="book_subtitle"><?php echo get_post_meta($object->ID, "book_subtitle", true); ?></textarea>
        </div>
    <?php
}

function book_subtitle_meta_box()
{
    add_meta_box("book_subtitle", "Sub-title", "book_subtitle_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "book_subtitle_meta_box");

/*************
Book Authors
*/
function book_authors_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "book_authors_nonce");
    ?>
        <div>
            <label for="book_authors">Authors</label>
            <textarea name="book_authors"><?php echo get_post_meta($object->ID, "book_authors", true); ?></textarea>
        </div>
    <?php
}

function book_authors_meta_box()
{
    add_meta_box("book_authors", "Author(s)", "book_authors_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "book_authors_meta_box");

/*****************************
Buy online link i.e. to Amazon
*/
function buy_book_link_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "buy_book_link_nonce");
    ?>
        <div>
            <label for="buy_book_link">Amazon link</label>
            <input type="text" name="buy_book_link" value="<?php echo get_post_meta($object->ID, "buy_book_link", true); ?>">
        </div>
    <?php
}

function buy_book_link_meta_box()
{
    add_meta_box("buy_book_link", "Buy Online link", "buy_book_link_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "buy_book_link_meta_box");

/*****************
Enable read online
*/
function enable_readonline_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "enable_readonline_nonce");
    ?>
        <div>
            <label for="enable_readonline">Enable Read Online</label>
            <?php
                $checkbox_value = get_post_meta($object->ID, "enable_readonline", true);
                ?>
                    <input name="enable_readonline" type="checkbox" value="TRUE" <?php if($checkbox_value == TRUE)?> checked<?php ?>>
                <?php
            ?>
        </div>
    <?php
}

function enable_readonline_meta_box()
{
    add_meta_box("enable_readonline", "Enable Read Online", "enable_readonline_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "enable_readonline_meta_box");



/*
Files meta box - Begin
*/
function attach_book_files_markup($object)
{
    wp_nonce_field(basename(__FILE__), "attach_book_files_nonce");
    $html = '';
    
    // EPUB
    $html .= build_book_attachment_type_markup('ePub');

    // PDF
    $html .= build_book_attachment_type_markup('PDF');

    // MOBI
    $html .= build_book_attachment_type_markup('mobi');

    echo $html;
}

function build_book_attachment_type_markup($filetypename)
{
    $thefile_form_input_name = strtolower($filetypename) . "_file_attachment";

    $thefile = get_post_meta(get_the_ID(), $thefile_form_input_name, true);
    $html = sprintf('<p class="description">Upload the %s file here</p><input type="file" id="%s" name="%s" value="" size="40" />', $filetypename, $thefile_form_input_name, $thefile_form_input_name);
    $html .= sprintf('<input type="text" id="%s_url" name="%s_url" value="%s" size="40" />', $thefile_form_input_name, $thefile_form_input_name, $thefile['url']);
    if(strlen(trim($thefile['url'])) > 0) {
        $html .= '<a href="javascript:;" id="' . $thefile_form_input_name . '_delete">' . __('Delete File') . '</a>';
    }
    return $html;
}

function add_attach_book_files()
{
    add_meta_box("attach_book_files", "Upload Book files", "attach_book_files_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "add_attach_book_files");

/*
Files meta box - End
*/


function save_book_data($id) {
 
    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['attach_book_files_nonce'], plugin_basename(__FILE__))) {
      //return $id;
    } // end if
       
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      //return $id;
    } // end if
       /*
    if('page' == $_POST['post_type']) {
      if(!current_user_can('edit_page', $id)) {
        return $id;
      } // end if
    } else {
        if(!current_user_can('edit_page', $id)) {
            return $id;
        } // end if
    } // end if*/
    /* - end security verification - */


    // Save 'read online' checkbox - Checkboxes are present if checked, absent if not.
    if ( isset( $_POST['enable_readonline'] ) ) {
        update_post_meta( $id, 'enable_readonline', TRUE );
    } else {
        update_post_meta( $id, 'enable_readonline', FALSE );
    }


    // Save 'sub-title'
    $book_subtitle_value = "";
    if(isset($_POST["book_subtitle"]))
    {
        $book_subtitle_value = $_POST["book_subtitle"];
    }   
    update_post_meta($id, "book_subtitle", $book_subtitle_value);

    
    // Save authors
    $book_authors_value = "";
    if(isset($_POST["book_authors"]))
    {
        $book_authors_value = sanitize_text_field($_POST["book_authors"]);
    }   
    update_post_meta($id, "book_authors", $book_authors_value);


    // Save 'buy online' amazon link
    $buy_book_link_value = "";
    if(isset($_POST["buy_book_link"]))
    {
        $buy_book_link_value = sanitize_text_field($_POST["buy_book_link"]);
    }   
    update_post_meta($id, "buy_book_link", $buy_book_link_value);


    // Save book file attachments

    // EPUB
    upload_file_bytype($id, "ePub", array('application/octet-stream', 'application/epub+zip'));

    // PDF
    upload_file_bytype($id, "PDF", array('application/octet-stream', 'application/pdf'));

    // MOBI
    upload_file_bytype($id, "mobi", array('application/octet-stream', 'x-mobipocket-ebook'));
     
} // end - save_book_data



function upload_file_bytype($id, $filetypename, $allowedmimetypes)
{
    $thefile_form_input_name = strtolower($filetypename) . "_file_attachment";

    if(!empty($_FILES[$thefile_form_input_name]['name'])) {
         
        // Setup the array of supported file types. In this case, it's just PDF.
        //$supported_types = array('application/octet-stream', 'application/epub+zip','application/x-mobipocket-ebook','application/pdf','application/vnd.amazon.ebook');
         
        $supported_types = $allowedmimetypes;

        // Get the file type of the upload
        $arr_file_type = wp_check_filetype(basename($_FILES[$thefile_form_input_name]['name']));
        $uploaded_type = $arr_file_type['type'];
         
        // Check if the type is supported. If not, throw an error.
        if(in_array($uploaded_type, $supported_types)) {
 
            // Use the WordPress API to upload the file
            $upload = wp_upload_bits($_FILES[$thefile_form_input_name]['name'], null, file_get_contents($_FILES[$thefile_form_input_name]['tmp_name']));
     
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                add_post_meta($id, $thefile_form_input_name, $upload);
                update_post_meta($id, $thefile_form_input_name, $upload);     
            } // end if/else
 
        } else {
            wp_die("The file type that you've uploaded for " . $filetypename . " is not correct.");
        } // end if/else
         
    } else { // check if we need to delete the attachment
 
        // Grab a reference to the file associated with this post
        $doc = get_post_meta($id, $thefile_form_input_name, true);
         
        // Grab the value for the URL to the file stored in the text element
        $delete_flag = $_POST[$thefile_form_input_name . "_url"]; //get_post_meta($id, $thefile_form_input_name . '_url', true);
         
        // Determine if a file is associated with this post and if the delete flag has been set (by clearing out the input box)
        if(strlen(trim($doc['url'])) > 0 && strlen(trim($delete_flag)) == 0) {
         
            // Attempt to remove the file. If deleting it fails, print a WordPress error.
            if(unlink($doc['file'])) {
                 
                // Delete succeeded so reset the WordPress meta data
                update_post_meta($id, $thefile_form_input_name, null);
                update_post_meta($id, $thefile_form_input_name . '_url', '');
                 
            } else {
                wp_die('There was an error trying to delete your file.');
            } // end if/el;se
             
        } // end if
 
    } // end if/else
}


function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_action('post_edit_form_tag', 'update_edit_form'); // allow form to upload files

add_action('save_post', 'save_book_data', 10 , 1); // this will only save if the post type is 'wr_book'. Use 'save_post' for any other post type.


/**************************************************************
 file attachment script for admin - to delete a file attachment
*/
function add_custom_attachment_script() {
 
    wp_register_script('custom-attachment-script', plugin_dir_url( __FILE__ ) . '/js/wp_custom_attachment.js');
    wp_enqueue_script('custom-attachment-script');
 
} // end add_custom_attachment_script
add_action('admin_enqueue_scripts', 'add_custom_attachment_script');


// enable /read as an endpoint for books
add_action('init', 'wr_book_add_endpoints');

function wr_book_add_endpoints()
{
    add_filter( 'template_include', 'include_template', 99 );
    add_rewrite_endpoint('read', EP_PERMALINK | EP_PAGES);
}

// if /read enpoint is hit then check if the 'reader' template/page should be used
function include_template( $template )
{
    if (get_query_var( 'wr_book' )) // this is a book
    {
        global $wp;
        $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        $thepath = parse_url($current_url, PHP_URL_PATH);

        if (preg_match('"/books/[^/]+/read/?"', $thepath)) // on book read online page
        {
            // check there is a viewable book and readonline is enabled
            global $wp;
            $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
            $postid = url_to_postid( $current_url );
            $enable_readonline = get_post_meta( $postid, 'enable_readonline', true );
            $epub_file_attachment = get_post_meta( $postid, 'epub_file_attachment', true );
            if ($epub_file_attachment != "")// and $enable_readonline == TRUE) // book file exisits
            {
                return dirname( __FILE__ ) . '/reader/reader.php';
            }
        }
    }
    return $template;
}

/**********************************************
 ebook uploader - because epub is not allowed by default
 based on https://wordpress.org/plugins/allow-epub-and-mobi-formats-upload/
*/

function wr_ebook_mime_types1($mime_types) {

    $mime_types['epub'] = 'application/octet-stream'; 
    return $mime_types;
}

function wr_ebook_mime_types2($mimes) {
    $mimes = array_merge($mimes, array(
        'epub|mobi' => 'application/octet-stream'
    ));
    return $mimes;
}

function wr_ebook_mime_types3($mimes) {

    $new_file_types = array (
        'zip' => 'application/zip',
        'mobi' => 'application/x-mobipocket-ebook',
        'epub' => 'application/epub+zip'
    );

    return array_merge($mimes,$new_file_types);
}

add_filter('upload_mimes', 'wr_ebook_mime_types1', 1, 1);
add_filter('upload_mimes', 'wr_ebook_mime_types2');
add_filter('upload_mimes', 'wr_ebook_mime_types3');



/************************************
 * SIDEBAR WIDGET LISTING BOOK TITLES
 * 
 * http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * 
*/

// Register and load the widget
function wr_book_titles_load_widget() {
    register_widget( 'wr_book_titles_widget' );
}
add_action( 'widgets_init', 'wr_book_titles_load_widget' );
 
// Creating the widget 
class wr_book_titles_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        
        // Base ID of your widget
        'wr_book_titles_widget', 
        
        // Widget name will appear in UI
        __('Book List', 'wr_book_titles_widget_domain'), 
        
        // Widget description
        array( 'description' => __( 'List all books', 'wr_book_titles_widget_domain' ), ) 
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

        // WordPress core before_widget hook (always include )
        echo $before_widget;

        // Display widget title if defined
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
        
        // This is where you run the code and display the output
        // https://codex.wordpress.org/Function_Reference/get_posts
        $args = array( 'post_type' => 'wr_book', 'posts_per_page' => 30, 'post_status' => 'publish', 'orderby' => $select_orderby, 'order' => $select_order ); // orderby: title/date/author
        global $post;
        $thebooks = get_posts( $args );
        echo __( '<div class="widget-books">', 'wr_book_titles_widget_domain' );
        foreach ( $thebooks as $post ) : setup_postdata( $post );
            $book_authors = get_post_meta(get_the_ID(), "book_authors", true); ?>
                <div class="widget-book">
                    <?php if (!$checkbox_hidetitle) { ?>
                        <div class="widget-book-title"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></div>
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
        echo __( '</div>', 'wr_book_titles_widget_domain' );

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
        $instance['select_orderby'] = isset( $new_instance['select_orderby'] ) ? wp_strip_all_tags( $new_instance['select_orderby'] ) : '';
        $instance['select_order'] = isset( $new_instance['select_order'] ) ? wp_strip_all_tags( $new_instance['select_order'] ) : '';
        return $instance;
    }
} // Class wr_book_titles_widget ends here