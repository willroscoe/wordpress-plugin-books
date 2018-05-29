<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    MP_Books
 * @subpackage MP_Books/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    MP_Books
 * @subpackage MP_Books/public
 * @author     Will Roscoe
 */
class MP_Books_Public {

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


	// Define book filetypes available, and store locally
	private $book_file_types = array();
	private $book_file_type = array();
	private $book_file = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->initialise_variables();
	}

	/**
	 * Initialise variables used in the class
	 *
	 * @since    1.0.0
	 */
	private function initialise_variables() {

		$this->book_file_types = array("pdf", "epub", "mobi");

		$this->book_file_type['epub']['title'] = 'ePub';
		$this->book_file_type['epub']['mimetype'] = 'application/epub+zip';

		$this->book_file_type['pdf']['title'] = 'PDF';
		$this->book_file_type['pdf']['mimetype'] = 'application/pdf';

		$this->book_file_type['mobi']['title'] = 'Kindle (mobi)';
		$this->book_file_type['mobi']['mimetype'] = 'application/x-mobipocket-ebook';
	}


	/**
	 * Add endpoints to navigate to /read or /search for books
	 *
	 */
	public function mp_book_add_endpoints()
	{
		add_rewrite_endpoint('read', EP_PAGES | EP_PERMALINK);
		add_rewrite_endpoint('fullread', EP_PAGES | EP_PERMALINK);
		add_rewrite_endpoint('search', EP_PAGES | EP_PERMALINK);
	}


	// if /read endpoint is hit then check if the 'reader' template/page should be used
	// if /search endpoint is hit then check if the 'search' template/page should be used
	public function include_template( $template )
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
						//return dirname( __FILE__ ) . '/reader-js/reader.php';
						return plugin_dir_path( dirname( __FILE__ ) ) . 'public/reader-js/reader.php';
					}
					else
					{
						return plugin_dir_path( dirname( __FILE__ ) ) . 'public/reader-php/reader.php';
					}
				}
			} elseif (preg_match('"/books/[^/]+/search$|search/.*"', $thepath)) { // search results
				return plugin_dir_path( dirname( __FILE__ ) ) . 'public/reader-php/search.php';
			}
		}
		return $template;
	}


	/**
	 * Add book meta tags to head of book pages
	 *
	 */
	public function mp_book_add_book_metatags() {

		if (get_query_var( 'mp_book' )) // this is a book
		{
			global $wp;
			global $post;
	
			$bookid = get_the_ID();
	
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
			$thepath = parse_url($current_url, PHP_URL_PATH);
	
			$this->refresh_book_file_details();
	
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
	
				$authors = $this->get_array_of_book_authors();
	
				for($x = 0; $x < count($authors); $x++) {
					echo '<meta name="citation_author" content="' . $authors[$x] . '">', PHP_EOL;
					echo '<meta property="book:author" content="' . $authors[$x] . '" />', PHP_EOL;
				}
	
				echo '<meta name="citation_publication_date" content="' . the_date() . '">', PHP_EOL; // YYYY/MM/DD
				if (isset($this->book_file[$bookid]['pdf']['url'])) {
					echo '<meta name="citation_pdf_url" content="' . $this->book_file[$bookid]['pdf']['url'] . '">', PHP_EOL;
				}
		
				for($x = 0; $x < count($book_file_types); $x++) {
					if (isset($this->book_file[$bookid][$this->book_file_types[$x]]['url'])) {
						$thisurl = $this->book_file[$bookid][$this->book_file_types[$x]]['url'];
						echo '<link rel="alternate" type="' . $this->book_file_type[$this->book_file_types[$x]]['mimetype'] . '" href="' . $thisurl . '">', PHP_EOL;
					}
				}
	
				wp_enqueue_script( 'hypothesis', 'https://hypothes.is/embed.js', array(), false, true );
			}
		}
	}


	/**
	 * Makes the 'books' nave menu active if the request is on a book page. The book archive page automatically does this anyway.
	 * 
	 */
	public function book_active_item_classes($classes = array(), $menu_item = false) {
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


	/**
	 * Build the standard book links i.e. read online, download, buy etc.
	 * 
	 */
	public function get_book_links_block()
	{
		$showlinkblock = FALSE;
		$epub_file_url = $this->get_epub_file_url();
		$downloadlinks = $this->get_download_links();
		$buy_book_link = $this->get_buy_book_link();
		$buy_book_hardback_link = $this->get_buy_hardback_book_link();
		
		if ($epub_file_url != "" || $downloadlinks != "" || $buy_book_link != "" || $buy_book_hardback_link != "")
			$showlinkblock = TRUE;

		if ($showlinkblock) {
			echo "<ul class='link-block'>";
		}
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
		if ($showlinkblock) {
			echo "</ul>";
		}
	}

	// Shortcode extension - Used by the Display Posts Shortcode plugin 
	public function mp_book_template_part( $output, $original_atts ) {
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



	/**
	 * Public book functions - access by theme files etc
	 * 
	 * 
	 * 
	 * 
	 * 
	 */

	public function get_book_meta_info() {
		$book_subtitle = $this->get_book_subtitle();
		$book_authors = $this->get_book_authors();

		if ($book_subtitle != "")
			echo sprintf( '<h3 class="book-subtitle">%s</h3>', $book_subtitle );

		if ($book_authors != "")
			echo sprintf( '<h4 class="book-authors">%s</h4>', $book_authors );
	}

	// sub-title
	public function get_book_subtitle()
	{
		return get_post_meta(get_the_ID(), "book_subtitle", true);
	}

	// authors
	public function get_book_authors($inc_pre_authors = TRUE, $class_for_prefix = "before-authors")
	{
		$this->refresh_book_authors();

		$result = "";
		$bookid = get_the_ID(); // book id

		$result = $this->book_file[$bookid]['authors'];

		if ($inc_pre_authors) { // add any 'pre author' code
			if (isset($this->book_file[$bookid]['preauthors'])) {
				if ($this->book_file[$bookid]['preauthors'] != "") {
					$result = '<span class="' . $class_for_prefix . '">'. $this->book_file[$bookid]['preauthors'] . '</span> ' . $result;
				}
			}
		}

		return $result;
	}

	public function get_array_of_book_authors()
	{
		$this->refresh_book_authors();

		$result = array();
		$trimmedresult = array();
		$bookid = get_the_ID(); // book id
		$authors = $this->book_file[$bookid]['authors'];
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

	public function refresh_book_authors()
	{
		$bookid = get_the_ID(); // book id
		$need_to_refresh = TRUE;
		if (isset($this->book_file[$bookid]['authors'])) { 
			if ($this->book_file[$bookid]['authors'] != "") {
				$need_to_refresh = FALSE;
			}
		}
		if ($need_to_refresh) {
			$authors = get_post_meta(get_the_ID(), "book_authors", true);
			$this->book_file[$bookid]['authors'] = $authors;
			$pre_authors = get_post_meta(get_the_ID(), "book_pre_authors", true);
			$this->book_file[$bookid]['preauthors'] = $pre_authors;
		}
	}

	// read online
	public function check_can_read_online() {
		$epub_file_url = "";
		$enable_readonline = get_post_meta( get_the_ID(), 'enable_readonline', true );
		$epub_file_attachment = get_post_meta( get_the_ID(), 'epub_file_attachment', true );
		if ($epub_file_attachment != "" and $enable_readonline == TRUE)
		{
			return TRUE;
		}
		return FALSE;
	}

	public function get_epub_file_url()
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

	public function get_buy_book_link()
	{
		return get_post_meta( get_the_ID(), 'buy_book_link', true );
	}

	public function get_buy_hardback_book_link()
	{
		return get_post_meta( get_the_ID(), 'buy_book_hardback_link', true );
	}

	/**
	 * Update the array variables which hold the book info
	 * 
	 * 
	 */
	private function refresh_book_file_details()
	{
		$bookid = get_the_ID(); // book id
		$need_to_refresh = TRUE;
		if (isset($this->book_file[$bookid])) {
			if (isset($this->book_file[$bookid]['pdf'])) {
				if (isset($this->book_file[$bookid]['pdf']['url'])) {
					if ($this->book_file[$bookid]['pdf']['url'] != "") {
						$need_to_refresh = FALSE;
					}
				}
			} elseif (isset($this->book_file[$bookid]['epub'])) {
				if (isset($this->book_file[$bookid]['epub']['url'])) {
					if ($this->book_file[$bookid]['epub']['url'] != "") {
						$need_to_refresh = FALSE;
					}
				}
			}
		}

		if ($need_to_refresh) {
			for($x = 0; $x < count($this->book_file_types); $x++) { // loop book file types ie. pdf, epub etc
				$file_attachment = get_post_meta( $bookid, $this->book_file_types[$x].'_file_attachment', true );
				if ($file_attachment != "")
				{
					$this->book_file[$bookid][$this->book_file_types[$x]]['url'] = $file_attachment['url']; // set the book file url
				}
			}
		}
	}

	/**
	 * Return html links to download the available file types for the book. i.e. epub, pdf, mobi etc
	 * 
	 * 
	 */
	public function get_download_links()
	{
		$this->refresh_book_file_details();

		$bookid = get_the_ID(); // book id
		$downloadlinks = "";

		for($x = 0; $x < count($this->book_file_types); $x++) {
			if ($this->book_file[$bookid][$this->book_file_types[$x]]['url'] != "") {
				if ($downloadlinks != "") {
					$downloadlinks .= ", ";
				}
				$downloadlinks .= sprintf("<a href='%s'>" . $this->book_file_type[$this->book_file_types[$x]]['title'] . "</a>", $this->book_file[$bookid][$this->book_file_types[$x]]['url']);
			}
		}

		return $downloadlinks;
	}

	/**
	 * Get the full absolute filesystem path to the epub file
	 * 
	 * 
	 */
	public function get_book_full_filesystem_path()
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
}
