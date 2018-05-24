<?php

/**
 * The settings of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    MP_Books
 * @subpackage MP_Books/admin
 */

/**
 * Class WordPress_Plugin_Template_Settings
 *
 */
class Custom_Post_Type_MP_Book {


	/**
	 * The custom post type string name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $custom_post_type = 'mp_book';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * This function registers the customer post type MP_Book which adds a 'Book' post section in the admin.
	 * 'WPPB Demo' menu.
	 */    
    public function register_custom_post_type_mp_book() {
        register_post_type( $this->custom_post_type,
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
    }

	/**
	 * Provides default values for the Display Options.
	 *
	 * @return array
	 */

	public function register_mp_book_meta_boxes()
	{
		add_meta_box("book_subtitle", "Sub-title", [$this, 'book_subtitle_meta_box_markup'], $this->custom_post_type, "normal", "high", null);
		add_meta_box("book_authors", "Author(s)", [$this, 'book_authors_meta_box_markup'], $this->custom_post_type, "normal", "high", null);
		add_meta_box("buy_book_link", "Buy Online links", [$this, 'buy_book_link_meta_box_markup'], $this->custom_post_type, "normal", "high", null);
		add_meta_box("enable_readonline", "Enable Read Online", [$this, 'enable_readonline_meta_box_markup'], $this->custom_post_type, "normal", "high", null);
		add_meta_box("attach_book_files", "Upload Book files", [$this, 'attach_book_files_markup'], $this->custom_post_type, "normal", "high", null);
	}

	/**
	 *Book Sub-title
	*/
	public function book_subtitle_meta_box_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "book_subtitle_nonce");
		?>
			<div class="mp-book-meta-box">
				<label for="book_subtitle">Sub-title</label>
				<textarea name="book_subtitle"><?php echo get_post_meta($object->ID, "book_subtitle", true); ?></textarea>
			</div>
		<?php
	}


	/**
	* Book Authors
	*/
	public function book_authors_meta_box_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "book_authors_nonce");
		?>
			<div class="mp-book-meta-box">
				<label for="book_authors">Authors</label>
				<textarea name="book_authors"><?php echo get_post_meta($object->ID, "book_authors", true); ?></textarea>
			</div>
			<div class="mp-book-meta-box">
				<label for="book_pre_authors">Pre Authors text</label>
				<input type="text" name="book_pre_authors" value="<?php echo get_post_meta($object->ID, "book_pre_authors", true); ?>">
			</div>
		<?php
	}

	/*****************************
	Buy online link i.e. to Amazon
	*/
	public function buy_book_link_meta_box_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "buy_book_link_nonce");
		?>
			<div class="mp-book-meta-box">
				<label for="buy_book_link">Buy Paperback link</label>
				<input type="text" name="buy_book_link" value="<?php echo get_post_meta($object->ID, "buy_book_link", true); ?>">
			</div>
			<div class="mp-book-meta-box">
				<label for="buy_book_hardback_link">Buy Hardback link</label>
				<input type="text" name="buy_book_hardback_link" value="<?php echo get_post_meta($object->ID, "buy_book_hardback_link", true); ?>">
			</div>
			<div class="mp-book-meta-box">
				<label for="book_isbn">ISBN</label>
				<input type="text" name="buy_book_hardback_link" value="<?php echo get_post_meta($object->ID, "buy_book_hardback_link", true); ?>">
			</div>
		<?php
	}

	/*****************
	Enable read online
	*/
	public function enable_readonline_meta_box_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "enable_readonline_nonce");
		?>
			<div class="mp-book-meta-box">
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

	/*
	Files meta box - Begin
	*/
	public function attach_book_files_markup($object)
	{
		wp_nonce_field(basename(__FILE__), "attach_book_files_nonce");
		$html = '';
		
		// EPUB
		$html .= $this->build_book_attachment_type_markup('ePub');

		// PDF
		$html .= $this->build_book_attachment_type_markup('PDF');

		// MOBI
		$html .= $this->build_book_attachment_type_markup('mobi');

		echo $html;
	}

	/*
	Generic build attachment function
	*/
	public function build_book_attachment_type_markup($filetypename)
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
		return '<div class="mp-book-attachments">' . $html . '</div>';
	}

	/**
	 * Files meta box - End
	 */


	public function save_book_data($id) {
	
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
		$this->upload_file_bytype($id, "ePub", array('application/octet-stream', 'application/epub+zip'));

		// PDF
		$this->upload_file_bytype($id, "PDF", array('application/octet-stream', 'application/pdf'));

		// MOBI
		$this->upload_file_bytype($id, "mobi", array('application/octet-stream', 'x-mobipocket-ebook'));
		
	} // end - save_book_data


	private function upload_file_bytype($id, $filetypename, $allowedmimetypes)
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

	/**
	 * Allow file uploads to the form
	 *
	 */
	public function update_edit_form() {
		echo ' enctype="multipart/form-data"';
	}



	/**
	 * Ebook uploader - MIME types - because epub is not allowed by default
	 * 
	 * based on https://wordpress.org/plugins/allow-epub-and-mobi-formats-upload/
	 * 
	 */

	public function mp_ebook_mime_types1($mime_types) {

		$mime_types['epub'] = 'application/octet-stream'; 
		return $mime_types;
	}

	public function mp_ebook_mime_types2($mimes) {
		$mimes = array_merge($mimes, array(
			'epub|mobi' => 'application/octet-stream'
		));
		return $mimes;
	}

	public function mp_ebook_mime_types3($mimes) {

		$new_file_types = array (
			'zip' => 'application/zip',
			'mobi' => 'application/x-mobipocket-ebook',
			'epub' => 'application/epub+zip'
		);

		return array_merge($mimes,$new_file_types);
	}

}