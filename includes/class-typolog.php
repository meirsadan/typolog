<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://meirsadan.com/
 * @since      1.0.0
 *
 * @package    Typolog
 * @subpackage Typolog/includes
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
 * @package    Typolog
 * @subpackage Typolog/includes
 * @author     Meir Sadan <meir@sadan.com>
 */
class Typolog {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Typolog_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'typolog';
		
		$this->version = '1.0.0';

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
	 * - Typolog_Loader. Orchestrates the hooks of the plugin.
	 * - Typolog_i18n. Defines internationalization functionality.
	 * - Typolog_Admin. Defines all hooks for the admin area.
	 * - Typolog_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-typolog-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-typolog-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/typolog-options.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/typolog-helpers.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-typolog-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-typolog-public.php';

		$this->loader = new Typolog_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Typolog_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Typolog_i18n();

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

		$plugin_admin = new Typolog_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'check_for_stuff' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_pages' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_page_init' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_metaboxes' );

		// add handling of license extensions field
		$this->loader->add_action( 'typolog_license_add_form_fields', $plugin_admin, 'print_license_extensions_field' );
		$this->loader->add_action( 'typolog_license_edit_form_fields', $plugin_admin, 'print_license_edit_extensions_field' );
		$this->loader->add_action( 'edited_typolog_license', $plugin_admin, 'save_license_extensions_field' );
		$this->loader->add_action( 'create_typolog_license', $plugin_admin, 'save_license_extensions_field' );

		// add handling of license base price field
		$this->loader->add_action( 'typolog_license_add_form_fields', $plugin_admin, 'print_license_base_price_field' );
		$this->loader->add_action( 'typolog_license_edit_form_fields', $plugin_admin, 'print_license_edit_base_price_field' );
		$this->loader->add_action( 'edited_typolog_license', $plugin_admin, 'save_license_base_price_field' );
		$this->loader->add_action( 'create_typolog_license', $plugin_admin, 'save_license_base_price_field' );

		// add handling of license order field
		$this->loader->add_action( 'typolog_license_add_form_fields', $plugin_admin, 'print_license_order_field' );
		$this->loader->add_action( 'typolog_license_edit_form_fields', $plugin_admin, 'print_license_edit_order_field' );
		$this->loader->add_action( 'edited_typolog_license', $plugin_admin, 'save_license_order_field' );
		$this->loader->add_action( 'create_typolog_license', $plugin_admin, 'save_license_order_field' );

		// add handling of family commercial field
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_commercial_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_commercial_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_commercial_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_commercial_field' );

		// add handling of font order + main font in family edit form
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_font_order' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_font_order' );
		
		// add handling of family "family name" field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_family_name_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_family_name_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_family_name_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_family_name_field' );

		// add handling of family "family index name" field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_family_index_name_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_family_index_name_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_family_index_name_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_family_index_name_field' );

		// add handling of family "badge label" field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_badge_label_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_badge_label_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_badge_label_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_badge_label_field' );

		// add handling of family "buy link" field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_buy_link_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_buy_link_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_buy_link_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_buy_link_field' );

		// add handling of family "website link" field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_website_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_website_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_website_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_website_field' );

		// add handling of family prices field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_price_fields' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_price_fields' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_price_fields' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_price_fields' );

		// add handling of pdf field in family edit form
// 		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_pdf_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_pdf_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_pdf_field' );
// 		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_pdf_field' );

		// add handling of attachment field in family edit form
		$this->loader->add_action( 'typolog_family_add_form_fields', $plugin_admin, 'print_family_attachments_field' );
		$this->loader->add_action( 'typolog_family_edit_form_fields', $plugin_admin, 'print_family_edit_attachments_field' );
		$this->loader->add_action( 'edited_typolog_family', $plugin_admin, 'save_family_attachments_field' );
		$this->loader->add_action( 'create_typolog_family', $plugin_admin, 'save_family_attachments_field' );

		// add handling of attachment field in license edit form
		$this->loader->add_action( 'typolog_license_add_form_fields', $plugin_admin, 'print_license_attachments_field' );
		$this->loader->add_action( 'typolog_license_edit_form_fields', $plugin_admin, 'print_license_edit_attachments_field' );
		$this->loader->add_action( 'edited_typolog_license', $plugin_admin, 'save_license_attachments_field' );
		$this->loader->add_action( 'create_typolog_license', $plugin_admin, 'save_license_attachments_field' );
		
		// add syncing license attribute terms between typolog and woocommerce
		$this->loader->add_action( 'edited_typolog_license', $plugin_admin, 'save_license_attribute_term' );
		$this->loader->add_action( 'create_typolog_license', $plugin_admin, 'save_license_attribute_term' );
		$this->loader->add_action( 'delete_typolog_license', $plugin_admin, 'delete_license_attribute_term' );

		// add handling of deleting linked products before deleting a font
		$this->loader->add_action( 'before_delete_post', $plugin_admin, 'delete_product_relationships' );

		// add handling of deleting linked files before deleting a font file post
		$this->loader->add_action( 'before_delete_post', $plugin_admin, 'delete_font_file_before_delete_post' );

		// add syncing data and products with each font save
		$this->loader->add_action( 'save_post', $plugin_admin, 'update_font_data' );		
		$this->loader->add_action( 'save_post', $plugin_admin, 'update_font_products_handler' );
		
		// add admin ajax commands
		$this->loader->add_action( 'wp_ajax_typolog_upload_font', $plugin_admin, 'upload_font_handler' );
		$this->loader->add_action( 'wp_ajax_typolog_upload_catalog', $plugin_admin, 'upload_catalog_handler' );
		$this->loader->add_action( 'wp_ajax_delete_font_file', $plugin_admin, 'delete_font_file_handler' );
		$this->loader->add_action( 'wp_ajax_update_font_packages', $plugin_admin, 'update_font_packages_handler' );
		$this->loader->add_action( 'wp_ajax_delete_all_fonts', $plugin_admin, 'delete_all_fonts_handler' );
		$this->loader->add_action( 'wp_ajax_regenerate_fonts', $plugin_admin, 'regenerate_fonts_handler' );
		$this->loader->add_action( 'wp_ajax_regenerate_family', $plugin_admin, 'regenerate_family_handler' );
		$this->loader->add_action( 'wp_ajax_reset_products', $plugin_admin, 'reset_products_handler' );
		$this->loader->add_action( 'wp_ajax_save_size_adjustments', $plugin_admin, 'save_size_adjustments_handler' );
		$this->loader->add_action( 'wp_ajax_update_products', $plugin_admin, 'update_products_handler' );
		$this->loader->add_action( 'wp_ajax_edit_family_fonts', $plugin_admin, 'edit_family_fonts_handler' );

		$this->loader->add_action( 'wp_ajax_delete_products', $plugin_admin, 'delete_products_handler' );
		$this->loader->add_action( 'wp_ajax_delete_font_files', $plugin_admin, 'delete_font_files_handler' );
		$this->loader->add_action( 'wp_ajax_delete_fonts', $plugin_admin, 'delete_fonts_handler' );
		$this->loader->add_action( 'wp_ajax_delete_downloads', $plugin_admin, 'delete_downloads_handler' );
		$this->loader->add_action( 'wp_ajax_delete_originals', $plugin_admin, 'delete_originals_handler' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Typolog_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'register_custom_types' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_action( 'init', $plugin_public, 'init_internal_pages' );
		$this->loader->add_action( 'query_vars', $plugin_public, 'query_internal_pages_vars' );
		$this->loader->add_action( 'parse_request', $plugin_public, 'parse_internal_pages_request' );

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
	 * @return    Typolog_Loader    Orchestrates the hooks of the plugin.
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
