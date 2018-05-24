<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    MP_Books
 * @subpackage MP_Books/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    MP_Books
 * @subpackage MP_Books/admin
 * @author     Will Roscoe
 */
class MP_Books_Admin {

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
	 * @param      string    $mp_books       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_dependencies();
	}

	/**
	 * Load the required dependencies for the Admin facing functionality.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Custom_Post_Type_MP_Book. Registers the custom post type MP_Book and custom post fields.
	 *
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for the custom post type MP_Book
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/class-custom-post-type-mp-book.php';

		/**
		 * The widget class for displaying a list of blog authors
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/widgets/widget-class-mp-authors-list-widget.php';

		/**
		 * The widget class for displaying a book's meta data and info
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/widgets/widget-class-mp-book-blog-post-meta-data-widget.php';

		/**
		 * The widget class for displaying a single book's meta data and info
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/widgets/widget-class-mp-book-cms-page-title-widget.php';

		/**
		 * The widget class for displaying a list of books with meta data and info
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/widgets/widget-class-mp-book-titles-widget.php';
	}

	/**
	 * Register all included widgets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_all_widgets() {
		register_widget( 'mp_authors_list_widget' );
		register_widget( 'mp_book_blog_post_meta_data_widget' );
		register_widget( 'mp_book_cms_page_title_widget' );
		register_widget( 'mp_book_titles_widget' );
	}
	

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in MP_Books_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The MP_Books_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mp-books-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in MP_Books_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The MP_Books_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mp-books-admin.js', array( 'jquery' ), $this->version, false );

	}
}
