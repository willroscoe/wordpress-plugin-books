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
    - Authors [todo]
    - Summary paragraph [todo]
    - Thumbnail image [todo]
    - Disable read online [todo]
    - ePub file link
    - Buy link [todo]
    - PDF download file link [todo]
    - Kindle download file link [todo]
*/

/*
Book Sub-title
*/
function book_subtitle_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "book_subtitle_meta_box_nonce");

    ?>
        <div>
            <label for="book_subtitle_meta_box">Text</label>
            <textarea name="book_subtitle_meta_box"><?php echo get_post_meta($object->ID, "book_subtitle_meta_box", true); ?></textarea>
        </div>
    <?php
}

function add_book_subtitle_meta_box()
{
    add_meta_box("book_subtitle_meta_box", "Sub-title", "book_subtitle_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "add_book_subtitle_meta_box");

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
    $html = sprintf('<p class="description">Upload the %s file here</p><input type="file" id="%s" name="%s" value="%s" size="40" />', $filetypename, $thefile_form_input_name, $thefile_form_input_name, $thefile['url']);
    
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
     
    // Make sure the file array isn't empty
    
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
         
    } // end if
}

function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_action('post_edit_form_tag', 'update_edit_form'); // allow form to upload files

add_action('save_post', 'save_book_data', 10 , 1);


/*************************
Enable read online - BEGIN
*/
function enable_readonline_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "enable_readonline_meta_box_nonce");

    ?>
        <div>
            <label for="enable_readonline_meta_box">Enable Read Online</label>
            <?php
                $checkbox_value = get_post_meta($object->ID, "enable_readonline_meta_box", false);
                ?>
                    <input name="enable_readonline_meta_box" type="checkbox" value="true" <?php if($checkbox_value == "true")?> checked<?php ?>>
                <?php
            ?>
        </div>
    <?php
}

function enable_readonline_meta_box()
{
    add_meta_box("enable_readonline_meta_box", "Enable Read Online", "enable_readonline_meta_box_markup", "wr_book", "normal", "high", null);
}

add_action("add_meta_boxes", "enable_readonline_meta_box");

/*
Enable read online - END
**************************/


// save data
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
    $enable_readonline_meta_box_value = "";

    /*if(isset($_POST["meta-box-text"]))
    {
        $meta_box_text_value = $_POST["meta-box-text"];
    }   
    update_post_meta($post_id, "meta-box-text", $meta_box_text_value);

    if(isset($_POST["meta-box-dropdown"]))
    {
        $meta_box_dropdown_value = $_POST["meta-box-dropdown"];
    }   
    update_post_meta($post_id, "meta-box-dropdown", $meta_box_dropdown_value);*/

    if(isset($_POST["enable_readonline_meta_box"]))
    {
        $enable_readonline_meta_box_value = $_POST["enable_readonline_meta_box"];
    }   
    update_post_meta($post_id, "enable_readonline_meta_box", $enable_readonline_meta_box_value);
}

add_action("save_post", "save_custom_meta_box", 10, 3);


/**********************************************
 ebook uploader - epub is not allowed by default
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