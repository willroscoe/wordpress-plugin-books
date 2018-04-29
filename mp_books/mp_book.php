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

// Define book filetypes available
$book_file_types = array("pdf", "epub", "mobi");

$book_file_type['epub']['title'] = 'ePub';
$book_file_type['epub']['mimetype'] = 'application/epub+zip';

$book_file_type['pdf']['title'] = 'PDF';
$book_file_type['pdf']['mimetype'] = 'application/pdf';

$book_file_type['mobi']['title'] = 'Kindle (mobi)';
$book_file_type['mobi']['mimetype'] = 'application/x-mobipocket-ebook';

$book_file = array();

add_action( 'init', 'mp_register_custom_post_types' );
 
function mp_register_custom_post_types() {
    register_post_type( 'mp_book',
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
            'rewrite' => array('slug' => 'books', 'with_front' => false),
        )
    );

    /*
    register_post_type( 'mp_news',
        array(
            'labels' => array(
                'name' => __( 'News' ),
                'singular_name' => __( 'News' )
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
                                'comments',
                                'revisions',
                                //'page-attributes', // (menu order, hierarchical must be true to show Parent option)
                                //'post-formats',),
                                ),
            'rewrite' => array('slug' => 'news'),
        )
    );*/
}

/**
 * Additional book meta fields
 *   - Sub-Title
 *   - Author(s)
 *   - Thumbnail image
 *   - Enable read online
 *   - Buy online link
 *   - ePub file link
 *   - PDF download file link
 *   - mobi (Kindle) download file link
 */

/**
 *Book Sub-title
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
    add_meta_box("book_subtitle", "Sub-title", "book_subtitle_meta_box_markup", "mp_book", "normal", "high", null);
}

add_action("add_meta_boxes", "book_subtitle_meta_box");

/**
* Book Authors
*/
function book_authors_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "book_authors_nonce");
    ?>
        <div>
            <label for="book_authors">Authors</label>
            <textarea name="book_authors" style="width:300px"><?php echo get_post_meta($object->ID, "book_authors", true); ?></textarea>
        </div>
        <div>
            <label for="book_pre_authors">Pre Authors text</label>
            <input type="text" name="book_pre_authors" value="<?php echo get_post_meta($object->ID, "book_pre_authors", true); ?>">
        </div>
    <?php
}

function book_authors_meta_box()
{
    add_meta_box("book_authors", "Author(s)", "book_authors_meta_box_markup", "mp_book", "normal", "high", null);
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
            <label for="buy_book_link">Buy Paperback link</label>
            <input type="text" name="buy_book_link" value="<?php echo get_post_meta($object->ID, "buy_book_link", true); ?>">
        </div>
        <div>
            <label for="buy_book_hardback_link">Buy Hardback link</label>
            <input type="text" name="buy_book_hardback_link" value="<?php echo get_post_meta($object->ID, "buy_book_hardback_link", true); ?>">
        </div>
        <div>
            <label for="book_isbn">ISBN</label>
            <input type="text" name="buy_book_hardback_link" value="<?php echo get_post_meta($object->ID, "buy_book_hardback_link", true); ?>">
        </div>
    <?php
}

function buy_book_link_meta_box()
{
    add_meta_box("buy_book_link", "Buy Online links", "buy_book_link_meta_box_markup", "mp_book", "normal", "high", null);
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
    add_meta_box("enable_readonline", "Enable Read Online", "enable_readonline_meta_box_markup", "mp_book", "normal", "high", null);
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
    $fileurl = "";
    if ($thefile != null) {
        $fileurl = $thefile['url'];
    }
    $html .= sprintf('<input type="text" id="%s_url" name="%s_url" value="%s" size="40" />', $thefile_form_input_name, $thefile_form_input_name, $fileurl);
    if(strlen(trim($fileurl)) > 0) {
        $html .= '<a href="javascript:;" id="' . $thefile_form_input_name . '_delete">' . __('Delete File') . '</a>';
    }
    return $html;
}

function add_attach_book_files()
{
    add_meta_box("attach_book_files", "Upload Book files", "attach_book_files_markup", "mp_book", "normal", "high", null);
}

add_action("add_meta_boxes", "add_attach_book_files");

/**
 * Files meta box - End
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

    // Save pre authors text
    $book_pre_authors_value = "";
    if(isset($_POST["book_pre_authors"]))
    {
        $book_pre_authors_value = sanitize_text_field($_POST["book_pre_authors"]);
    }   
    update_post_meta($id, "book_pre_authors", $book_pre_authors_value);

    // Save 'buy online' amazon link
    $buy_book_link_value = "";
    if(isset($_POST["buy_book_link"]))
    {
        $buy_book_link_value = sanitize_text_field($_POST["buy_book_link"]);
    }   
    update_post_meta($id, "buy_book_link", $buy_book_link_value);

    $buy_book_hardback_link_value = "";
    if(isset($_POST["buy_book_hardback_link"]))
    {
        $buy_book_hardback_link_value = sanitize_text_field($_POST["buy_book_hardback_link"]);
    }   
    update_post_meta($id, "buy_book_hardback_link", $buy_book_hardback_link_value);

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
         
        if ($doc != null) {
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
        }
 
    } // end if/else
}


function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_action('post_edit_form_tag', 'update_edit_form'); // allow form to upload files

add_action('save_post', 'save_book_data', 10 , 1); // this will only save if the post type is 'mp_book'. Use 'save_post' for any other post type.


/**
 * file attachment script for admin - to delete a file attachment
 */

function add_custom_attachment_script() {
 
    wp_register_script('custom-attachment-script', plugin_dir_url( __FILE__ ) . '/js/wp_custom_attachment.js');
    wp_enqueue_script('custom-attachment-script');
 
} // end add_custom_attachment_script
add_action('admin_enqueue_scripts', 'add_custom_attachment_script');


// enable /read as an endpoint for books
add_action('init', 'mp_book_add_endpoints');

function mp_book_add_endpoints()
{
    add_filter( 'template_include', 'include_template', 99 );
    add_rewrite_endpoint('read', EP_PAGES | EP_PERMALINK);
    add_rewrite_endpoint('fullread', EP_PAGES | EP_PERMALINK);
}

// if /read enpoint is hit then check if the 'reader' template/page should be used
function include_template( $template )
{
    if (get_query_var( 'mp_book' )) // this is a book
    {
        global $wp;
        $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        $thepath = parse_url($current_url, PHP_URL_PATH);

        if (preg_match('"/books/[^/]+/([full]*read$|[full]*read/.*)"', $thepath)) // on book 'read online' page - either 'read' or 'fullread'
        {
            // check there is a viewable book and readonline is enabled
            $postid = url_to_postid( $current_url );
            $enable_readonline = get_post_meta( $postid, 'enable_readonline', true );
            $epub_file_attachment = get_post_meta( $postid, 'epub_file_attachment', true );
            if ($epub_file_attachment != "" and $enable_readonline == TRUE) // book file exisits
            {
                if (preg_match('"/books/[^/]+/fullread/?"', $thepath)) { // show book using the 'ed' php version
                    return dirname( __FILE__ ) . '/reader-js/reader.php';
                }
                else
                {
                    return dirname( __FILE__ ) . '/reader-php/reader.php';
                }
            }
        }
    }
    return $template;
}

/**
 * Ebook uploader - because epub is not allowed by default
 * 
 * based on https://wordpress.org/plugins/allow-epub-and-mobi-formats-upload/
 * 
 */

function mp_ebook_mime_types1($mime_types) {

    $mime_types['epub'] = 'application/octet-stream'; 
    return $mime_types;
}

function mp_ebook_mime_types2($mimes) {
    $mimes = array_merge($mimes, array(
        'epub|mobi' => 'application/octet-stream'
    ));
    return $mimes;
}

function mp_ebook_mime_types3($mimes) {

    $new_file_types = array (
        'zip' => 'application/zip',
        'mobi' => 'application/x-mobipocket-ebook',
        'epub' => 'application/epub+zip'
    );

    return array_merge($mimes,$new_file_types);
}

add_filter('upload_mimes', 'mp_ebook_mime_types1', 1, 1);
add_filter('upload_mimes', 'mp_ebook_mime_types2');
add_filter('upload_mimes', 'mp_ebook_mime_types3');



/**
 * SIDEBAR WIDGET LISTING BOOK TITLES
 * 
 * http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * 
 */

// Register and load the widget
function mp_book_titles_load_widget() {
    register_widget( 'mp_book_titles_widget' );
}
add_action( 'widgets_init', 'mp_book_titles_load_widget' );
 
// Creating the widget 
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
} // Class mp_book_titles_widget ends here



/**
 * SIDEBAR WIDGET CMS PAGE TITLE
 * 
 * http://www.wpexplorer.com/create-widget-plugin-wordpress/
 * 
 */

// Register and load the widget
function mp_book_cms_page_title_load_widget() {
    register_widget( 'mp_book_cms_page_title_widget' );
}
add_action( 'widgets_init', 'mp_book_cms_page_title_load_widget' );
 
// Creating the widget 
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
} // Class page_title widget ends here


/**
 * SIDEBAR WIDGET BLOG POST META DATA
 * 
 *
 * 
 */

// Register and load the widget
function mp_book_blog_post_meta_data_load_widget() {
    register_widget( 'mp_book_blog_post_meta_data_widget' );
}
add_action( 'widgets_init', 'mp_book_blog_post_meta_data_load_widget' );
 
// Creating the widget 
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
} // Class blog_post_meta_data widget ends here



/**
 * SIDEBAR WIDGET AUTHORS LIST
 * 
 *
 * 
 */

// Register and load the widget
function mp_authors_list_load_widget() {
    register_widget( 'mp_authors_list_widget' );
}
add_action( 'widgets_init', 'mp_authors_list_load_widget' );
 
// Creating the widget 
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
} // Class authors_list widget ends here



// Shortcode extension - Used by the Display Posts Shortcode plugin 
function mp_book_template_part( $output, $original_atts ) {
    ob_start();
    if (get_post_type() == "mp_book") {
        get_template_part( 'template-parts/books', get_post_type() );
    }
    else {
        get_template_part( 'template-parts/content', get_post_format() );
    }
	$new_output = ob_get_clean();
	if( !empty( $new_output ) )
		$output = $new_output;
	return $output;
}
add_action( 'display_posts_shortcode_output', 'mp_book_template_part', 10, 2 );


/**
 * Public book functions - used in theme files etc
 * 
 * 
 * 
 */

// sub-title
function get_book_subtitle()
{
    return get_post_meta(get_the_ID(), "book_subtitle", true);
}

// authors
function get_book_authors($inc_pre_authors = TRUE, $class_for_prefix = "before-authors")
{
    refresh_book_authors();

    global $book_file;

    $result = "";
    $bookid = get_the_ID(); // book id

    $result = $book_file[$bookid]['authors'];

    if ($inc_pre_authors) { // add any 'pre author' code
        if (isset($book_file[$bookid]['preauthors'])) {
            if ($book_file[$bookid]['preauthors'] != "") {
                $result = '<span class="' . $class_for_prefix . '">'. $book_file[$bookid]['preauthors'] . '</span> ' . $result;
            }
        }
    }

    return $result;
}

function get_array_of_book_authors()
{
    refresh_book_authors();
    global $book_file;
    $result = array();
    $trimmedresult = array();
    $bookid = get_the_ID(); // book id
    $authors = $book_file[$bookid]['authors'];
    if (strlen($authors) > 0) {
        if (strpos($authors, '|') !== FALSE) {
            $result = explode('|', $authors);
        } else { // otherwise split on ','
            $result = explode(',', $authors);
        }
    }
    for($x = 0; $x < count($result); $x++) {
        $trimmedresult[$x] = trim(str_replace('and ' , '', $result[$x])); // replace name stating with 'and ' and then trim
    }
    return $trimmedresult;
}

function refresh_book_authors()
{
    global $book_file;

    $bookid = get_the_ID(); // book id
    $need_to_refresh = TRUE;
    if (isset($book_file[$bookid]['authors'])) { 
        if ($book_file[$bookid]['authors'] != "") {
            $need_to_refresh = FALSE;
        }
    }
    if ($need_to_refresh) {
        $authors = get_post_meta(get_the_ID(), "book_authors", true);
        $book_file[$bookid]['authors'] = $authors;
        $pre_authors = get_post_meta(get_the_ID(), "book_pre_authors", true);
        $book_file[$bookid]['preauthors'] = $pre_authors;
    }
}

// read online
function get_epub_file_url()
{
    $epub_file_url = "";
    $enable_readonline = get_post_meta( get_the_ID(), 'enable_readonline', true );
    $epub_file_attachment = get_post_meta( get_the_ID(), 'epub_file_attachment', true );
    if ($epub_file_attachment != "" and $enable_readonline == TRUE)
    {
        $epub_file_url = $epub_file_attachment['url'];
    }
    return $epub_file_url;
}

function get_buy_book_link()
{
	return get_post_meta( get_the_ID(), 'buy_book_link', true );
}

function get_buy_hardback_book_link()
{
	return get_post_meta( get_the_ID(), 'buy_book_hardback_link', true );
}

function refresh_book_file_details()
{
    global $book_file_types;
    global $book_file_type;
    global $book_file;

    $bookid = get_the_ID(); // book id
    $need_to_refresh = TRUE;
    if (isset($book_file[$bookid])) {
        if (isset($book_file[$bookid]['pdf'])) {
            if (isset($book_file[$bookid]['pdf']['url'])) {
                if ($book_file[$bookid]['pdf']['url'] != "") {
                    $need_to_refresh = FALSE;
                }
            }
        } elseif (isset($book_file[$bookid]['epub'])) {
            if (isset($book_file[$bookid]['epub']['url'])) {
                if ($book_file[$bookid]['epub']['url'] != "") {
                    $need_to_refresh = FALSE;
                }
            }
        }
    }

    if ($need_to_refresh) {
        for($x = 0; $x < count($book_file_types); $x++) { // loop book file types ie. pdf, epub etc
            $file_attachment = get_post_meta( $bookid, $book_file_types[$x].'_file_attachment', true );
            if ($file_attachment != "")
            {
                $book_file[$bookid][$book_file_types[$x]]['url'] = $file_attachment['url']; // set the book file url
            }
        }
    }
}

function get_download_links()
{
    refresh_book_file_details();

    $bookid = get_the_ID(); // book id

    $downloadlinks = "";
    
    global $book_file_types;
    global $book_file_type;
    global $book_file;

    for($x = 0; $x < count($book_file_types); $x++) {
        if ($book_file[$bookid][$book_file_types[$x]]['url'] != "") {
            if ($downloadlinks != "") {
                $downloadlinks .= ", ";
            }
            $downloadlinks .= sprintf("<a href='%s'>" . $book_file_type[$book_file_types[$x]]['title'] . "</a>", $book_file[$bookid][$book_file_types[$x]]['url']);
        }
    }

	return $downloadlinks;
}

/**
 * Get the full absolute filesystem path to the epub file
 * 
 * 
 */
function get_book_full_filesystem_path()
{
    global $wp;
    $book_full_filesystem_path = "";
    $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
    $post_id = url_to_postid( $current_url );
    $epub_file_attachment = get_post_meta( $post_id, 'epub_file_attachment', true );
    if ($epub_file_attachment != "")
    {
        $book_full_filesystem_path = $epub_file_attachment['file'];
    }
    return $book_full_filesystem_path;
}

/**
 * Makes the 'books' nave menu active if the request is on a book page. The book archive page automatically does this anyway.
 * 
 * 
 */
function book_active_item_classes($classes = array(), $menu_item = false) {
    global $post;
    if ( !is_singular( 'post' ) ) {
        // Get post ID, if nothing found set to NULL
        $id = ( isset( $post->ID ) ? get_the_ID() : NULL );

        if (isset( $id )){
            // Getting the post type of the current post
            $current_post_type = get_post_type_object(get_post_type($post->ID));
            $current_post_type_slug = $current_post_type->rewrite['slug'];
                
            // Getting the URL of the menu item
            $menu_slug = strtolower(trim($menu_item->url));
            
            // If the menu item URL contains the current post types slug add the current-menu-item class
            if (strpos($menu_slug,$current_post_type_slug) !== false or $menu_slug == '/about') {
                $classes[] = 'current-menu-item active';
            }
            else if( in_array('current-page-ancestor', $classes) ){
                $classes[] = 'current-menu-item active ';
            }
            else
            {
                $classes[] = '';
            }
        }
    }
    else // for individual blog posts
    {
        $id = ( isset( $post->ID ) ? get_the_ID() : NULL );
        if (isset( $id )){
            $menu_slug = strtolower(trim($menu_item->url));
            if ($menu_slug == '/blog')
            {
                $classes[] = 'current-menu-item active';
            }
            else
            {
                $classes[] = '';
            }
        }
    }
	// Return the corrected set of classes to be added to the menu item
    return $classes;
}
add_filter( 'nav_menu_css_class', 'book_active_item_classes', 10, 2 );

function get_book_links_block()
{
    $epub_file_url = get_epub_file_url();
    $downloadlinks = get_download_links();
    $buy_book_link = get_buy_book_link();
    $buy_book_hardback_link = get_buy_hardback_book_link();
    
    echo "<ul class='link-block'>";
    if ($epub_file_url != "") {
        echo '<li><span class="label">Read</span> <span class="links"><a href="' . esc_url( get_permalink() ) . '/read" class="colorbox donate" data-colorbox-href="#donate-popup" data-colorbox-inline="true">online</a></span></li>';
    }

    if ($downloadlinks != "") {
        echo '<li><span class="label">Download</span> <span class="links">' . $downloadlinks . '</span></li>';
    }

    if ($buy_book_link != "") {
        echo '<li><span class="label">Buy</span> <span class="links"><a href="' . $buy_book_link . '">Paperback</a></span></li>';
    }

    if ($buy_book_hardback_link != "") {
        echo '<li><span class="label">Buy</span> <span class="links"><a href="' . $buy_book_hardback_link . '">Hardback</a></span></li>';
    }

	echo "</ul>";
}

/**
 * Add book meta tags to /read pages to help with search engines, google scholar, hypothesis etc
 * 
 */
add_action('wp_head', function(){
    if (get_query_var( 'mp_book' )) // this is a book
    {
        global $wp;
        global $post;

        $bookid = get_the_ID();

        $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        $thepath = parse_url($current_url, PHP_URL_PATH);

        refresh_book_file_details();

        global $book_file_types;
        global $book_file_type;
        global $book_file;

        if (preg_match('"/books/[^/]+/([full]*read$|[full]*read/.*)"', $thepath)) // on book 'read online' page - either 'read' or 'fullread'
        {

            echo '<meta property="og:title" content="' . get_the_title() . '" />', PHP_EOL;
            echo '<meta property="og:url" content="' . $current_url . '" />', PHP_EOL;
            //echo '<meta property="og:image" content="' .  . '" />', PHP_EOL; // thumbnail
            echo '<meta property="og:type" content="book" />', PHP_EOL;
            //echo '<meta property="book:isbn" content="' .  . '" />';
            echo '<meta property="book:release_date" content="' . the_date() . '" />', PHP_EOL;
            //echo '<meta property="book:tag" content="" />';

            echo '<meta name="citation_title" content="' . get_the_title() . '">', PHP_EOL;

            $authors = get_array_of_book_authors();

            for($x = 0; $x < count($authors); $x++) {
                echo '<meta name="citation_author" content="' . $authors[$x] . '">', PHP_EOL;
                echo '<meta property="book:author" content="' . $authors[$x] . '" />', PHP_EOL;
            }

            echo '<meta name="citation_publication_date" content="' . the_date() . '">', PHP_EOL; // YYYY/MM/DD
            if (isset($book_file[$bookid]['pdf']['url'])) {
                echo '<meta name="citation_pdf_url" content="' . $book_file[$bookid]['pdf']['url'] . '">', PHP_EOL;
            }
    
            for($x = 0; $x < count($book_file_types); $x++) {
                if (isset($book_file[$bookid][$book_file_types[$x]]['url'])) {
                    $thisurl = $book_file[$bookid][$book_file_types[$x]]['url'];
                    echo '<link rel="alternate" type="' . $book_file_type[$book_file_types[$x]]['mimetype'] . '" href="' . $thisurl . '">', PHP_EOL;
                }
            }

            wp_enqueue_script( 'hypothesis', 'https://hypothes.is/embed.js', array(), false, true );
        }
    }
});