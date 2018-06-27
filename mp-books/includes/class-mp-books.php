<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    MP_Books
 * @subpackage MP_Books/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    MP_Books
 * @subpackage MP_Books/includes
 * @author     Will Roscoe
 */
class MP_Books {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      MP_Books_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MP_BOOKS_VERSION' ) ) {
			$this->version = MP_BOOKS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'mp-books';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - MP_Books_Loader. Orchestrates the hooks of the plugin.
	 * - MP_Books_i18n. Defines internationalization functionality.
	 * - MP_Books_Admin. Defines all hooks for the admin area.
	 * - MP_Books_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mp-books-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mp-books-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mp-books-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-mp-books-public.php';

		$this->loader = new MP_Books_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the MP_Books_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new MP_Books_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new MP_Books_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_custom_post_type_mp_book = new Custom_Post_Type_MP_Book($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// register all custom widgets
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'register_all_widgets' );

		// Custom Post Type - MP_Book functions
		$this->loader->add_action( 'init', $plugin_custom_post_type_mp_book, 'register_custom_post_type_mp_book' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_custom_post_type_mp_book, 'register_mp_book_meta_boxes' );
		$this->loader->add_action( 'post_edit_form_tag', $plugin_custom_post_type_mp_book,  'update_edit_form' ); // allow form to upload files
		$this->loader->add_action( 'save_post', $plugin_custom_post_type_mp_book,  'save_book_data', 10 , 1 ); // this will only save if the post type is 'mp_book'. Use 'save_post' for any other post type.

		// epub mime types - as wp doesn't support this for uploading
		$this->loader->add_filter( 'upload_mimes', $plugin_custom_post_type_mp_book, 'mp_ebook_mime_types1', 1, 1);
		$this->loader->add_filter( 'upload_mimes', $plugin_custom_post_type_mp_book, 'mp_ebook_mime_types2');
		$this->loader->add_filter( 'upload_mimes', $plugin_custom_post_type_mp_book, 'mp_ebook_mime_types3');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new MP_Books_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'mp_book_add_book_metatags' );

		// Custom shortcode extension - Used by the Display Posts Shortcode plugin 
		$this->loader->add_action( 'display_posts_shortcode_output', $plugin_public, 'mp_book_template_part', 10, 2 );

		// register custom end points /read, /search etc
		$this->loader->add_action('init', $plugin_public, 'mp_book_add_endpoints');
		// run this function when hitting custom endpoints
		$this->loader->add_filter( 'template_include', $plugin_public, 'include_template', 99 );

		$this->loader->add_filter( 'nav_menu_css_class', $plugin_public, 'book_active_item_classes', 10, 2 );

		// actions for the theme
		$this->loader->add_action( 'mp_books_get_book_links', $plugin_public, 'get_book_links_block');
		$this->loader->add_action( 'mp_books_get_book_meta_info', $plugin_public, 'get_book_meta_info');
		$this->loader->add_filter( 'mp_book_check_can_read_online', $plugin_public, 'check_can_read_online');
		$this->loader->add_filter( 'mp_book_get_book_full_filesystem_path', $plugin_public, 'get_book_full_filesystem_path');
		$this->loader->add_filter( 'mp_book_cleanse_search_terms', $plugin_public, 'cleanse_search_terms', 10, 1);
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    MP_Books_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
