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
            'query_var' => true,
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
                    <input name="enable_readonline" type="checkbox" value="true" <?php if($checkbox_value == "true")?> checked<?php ?>>
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
    $html .= sprintf('<input type="hidden" id="%s_url" name="%s_url" value="%s" size="30" />', $thefile_form_input_name, $thefile_form_input_name, $thefile['url']);
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


/*
epub file upload and meta field
*/
/*function epub_file_attachment_markup($object)
{
    wp_nonce_field(basename(__FILE__), "epub_file_attachment_nonce");
    //wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
     
    $html = '<p class="description">';
        $html .= 'Upload the ePub file here.';
    $html .= '</p>';
    $html .= '<input type="file" id="epub_file_attachment" name="epub_file_attachment" value="" size="100" />';

    // Grab the array of file information currently associated with the post
    $doc = get_post_meta(get_the_ID(), 'epub_file_attachment', true);

    $html .= '<input type="text" id="epub_file_attachment_url" name="epub_file_attachment_url" value=" ' . $doc['url'] . '" size="100" />';
    
    if(strlen(trim($doc['url'])) > 0) {
        $html .= '<a href="javascript:;" id="epub_file_attachment_delete">' . __('Delete File') . '</a>';
    }
    echo $html;

}

function add_epub_file_attachment()
{
    add_meta_box("epub_file_attachment", "ePub File", "epub_file_attachment_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "add_epub_file_attachment");*/


function save_book_data($id) {
 
    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['enable_readonline_meta_box_nonce'], plugin_basename(__FILE__))) {
      return $id;
    } // end if
       
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
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
        update_post_meta( $post_id, 'enable_readonline', TRUE );
    } else {
        update_post_meta( $post_id, 'enable_readonline', FALSE );
    }


    // Save 'sub-title'
    $book_subtitle_value = "";
    if(isset($_POST["book_subtitle"]))
    {
        $book_subtitle_value = sanitize_text_field($_POST["book_subtitle"]);
    }   
    update_post_meta($post_id, "book_subtitle", $book_subtitle_value);

    
    // Save authors
    $book_authors_value = "";
    if(isset($_POST["book_authors"]))
    {
        $book_authors_value = sanitize_text_field($_POST["book_authors"]);
    }   
    update_post_meta($post_id, "book_authors", $book_authors_value);


    // Save 'buy online' amazon link
    $buy_book_link_value = "";
    if(isset($_POST["buy_book_link"]))
    {
        $buy_book_link_value = sanitize_text_field($_POST["buy_book_link"]);
    }   
    update_post_meta($post_id, "buy_book_link", $buy_book_link_value);


    // Save book file attachments

    // EPUB
    upload_file_bytype("ePub", array('application/octet-stream', 'application/epub+zip'));

    // PDF
    upload_file_bytype("PDF", array('application/octet-stream', 'application/pdf'));

    // MOBI
    upload_file_bytype("mobi", array('application/octet-stream', 'x-mobipocket-ebook'));
     
} // end - save_book_data



function upload_file_bytype($filetypename, $allowedmimetypes)
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
        $delete_flag = get_post_meta($id, $thefile_form_input_name . '_url', true);
         
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

add_action('save_post_wr_book', 'save_book_data', 10 , 1); // this will only save if the post type is 'wr_book'. Use 'save_post' for any other post type.


// save data
/*
function save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["enable_readonline_meta_box_nonce"]) || !wp_verify_nonce($_POST["enable_readonline_meta_box_nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

        if('page' == $_POST['post_type']) {
            if(!current_user_can('edit_page', $id)) {
              return $id;
            } // end if
          } else {
              if(!current_user_can('edit_page', $id)) {
                  return $id;
              } // end if
          } // end if

    //$meta_box_text_value = "";
    //$meta_box_dropdown_value = "";
    

    /*if(isset($_POST["meta-box-text"]))
    {
        $meta_box_text_value = $_POST["meta-box-text"];
    }   
    update_post_meta($post_id, "meta-box-text", $meta_box_text_value);

    if(isset($_POST["meta-box-dropdown"]))
    {
        $meta_box_dropdown_value = $_POST["meta-box-dropdown"];
    }   
    update_post_meta($post_id, "meta-box-dropdown", $meta_box_dropdown_value);
    $enable_readonline_meta_box_value = "";
    if(isset($_POST["enable_readonline_meta_box"]))
    {
        $enable_readonline_meta_box_value = $_POST["enable_readonline_meta_box"];
    }   
    update_post_meta($post_id, "enable_readonline_meta_box", $enable_readonline_meta_box_value);
}

add_action("save_post", "save_custom_meta_box", 10, 3);
*/


/**************************************************************
 file attachment script for admin - to delete a file attachment
*/
function add_custom_attachment_script() {
 
    wp_register_script('custom-attachment-script', plugin_dir_url( __FILE__ ) . '/js/custom_attachment.js');
    wp_enqueue_script('custom-attachment-script');
 
} // end add_custom_attachment_script
add_action('admin_enqueue_scripts', 'add_custom_attachment_script');



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

add_action('init', 'wr_book_add_endpoints');

function wr_book_add_endpoints()
{
    add_filter( 'template_include', 'include_template', 99 );
    add_rewrite_endpoint('read', EP_PERMALINK | EP_PAGES);
}

function include_template( $template )
{
    if (get_query_var( 'wr_book' )) // this is a book
    {
        global $wp;
        $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        $thepath = parse_url($current_url, PHP_URL_PATH);

        if (preg_match('"/books/[^/]+/read/?"', $thepath))
        {
            return dirname( __FILE__ ) . '/reader.php';
        }
    }

    return $template;
}



// Class for url rewriting and loading the book reading template page
/*class BookReaderClass
{
    public function init()
    {
        add_filter( 'template_include', array( $this, 'include_template' ) );
        add_filter( 'init', array( $this, 'rewrite_rules' ) );
    }

    public function include_template( $template )
    {
        //try and get the query var we registered in our query_vars() function
        $read_book = get_query_var( 'book_page' );

        //if the query var has data, we must be on the right page, load our custom template
        if ( $read_book ) {
            return dirname( __FILE__ ) . '/reader.php';
        }

        return $template;
    }

    public function flush_rules()
    {
        $this->rewrite_rules();

        flush_rewrite_rules();
    }

    public function rewrite_rules()
    {
        add_rewrite_tag( '%book_page%', '([^&]+)');
        add_rewrite_tag( '%book_chapter%', '([^&]+)');
        //add_rewrite_rule( '^books/([^/]*)/read/?', 'index.php?book_page=$matches[1]', 'top'); // ['api/(.*?)/(.+?)']
        //add_rewrite_rule( '^books/([^/]+)/read/(.+?))', 'index.php?book_page=$matches[1]&book_chapter=$matches[2]', 'top'); // ['api/(.*?)/(.+?)']
    }

}

add_action( 'plugins_loaded', array( new BookReaderClass, 'init' ) );

// One time activation functions
register_activation_hook( __FILE__ , array( new BookReaderClass, 'flush_rules' ) );*/