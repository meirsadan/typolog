<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://meirsadan.com/
 * @since      1.0.0
 *
 * @package    Typolog
 * @subpackage Typolog/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Typolog
 * @subpackage Typolog/public
 * @author     Meir Sadan <meir@sadan.com>
 */
class Typolog_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	
	private function fontface_url() {
		
		$fontface_url = home_url('/fontface/');
		
		if (is_tax('typolog_family')) {
			
			$fontface_url .= '?families=' . get_queried_object()->term_id;
			
		} elseif (is_tax('typolog_collection')) {
			
			$fontface_url .= '?collections=' . get_queried_object()->term_id;
			
		} elseif (is_singular('typolog_font')) {
			
			$fontface_url .= '?fonts=' . get_queried_object()->ID;
			
		}
		
		return $fontface_url;
		
	}
	
	private function get_webfont_families() {
		
		if (is_tax('typolog_family')) {
			
			return tl_get_family_webfont_names(get_queried_object()->term_id);
			
		} elseif (is_tax('typolog_collection')) {
			
			return tl_get_all_main_webfont_names(get_queried_object()->term_id);
			
		} elseif (is_singular('typolog_font')) {
			
			return array( tl_get_webfont_name(get_queried_object()->ID) );
			
		}
		
		return tl_get_all_main_webfont_names();
		
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Typolog_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Typolog_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		 
		wp_enqueue_style( 'typolog-fontface', $this->fontface_url(), array(), $this->version, 'all');

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/typolog-public.css', array(), $this->version, 'all' );
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Typolog_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Typolog_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

// 		wp_enqueue_script( 'webfont-loader', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js', array( ), '1.6', false );

/*
		wp_enqueue_script( 'underscorejs', plugin_dir_url( __FILE__ ) . 'js/vendor/underscore-min.js', array( ), $this->version, false );
		wp_enqueue_script( 'backbonejs', plugin_dir_url( __FILE__ ) . 'js/vendor/backbone-min.js', array( 'jquery', 'underscorejs' ), $this->version, false );
*/

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'backbone' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/typolog-public.js', array( 'jquery', 'underscore', 'backbone' ), $this->version, false );

// 		wp_localize_script( 'webfont-loader', 'WebFontConfig', new stdClass() );

/*
		wp_localize_script( 'webfont-loader', 'WebFontConfig', array(
			"custom" => array(
				"families" => $this->get_webfont_families()
			)
		) );
*/

	}
	
	private function register_taxonomy_family() {

		$labels = array(
			'name'                       => _x( 'Font Families', 'Taxonomy General Name', 'typolog' ),
			'singular_name'              => _x( 'Font Family', 'Taxonomy Singular Name', 'typolog' ),
			'menu_name'                  => __( 'Font Families', 'typolog' ),
			'all_items'                  => __( 'All Families', 'typolog' ),
			'parent_item'                => __( 'Parent Family', 'typolog' ),
			'parent_item_colon'          => __( 'Parent Family:', 'typolog' ),
			'new_item_name'              => __( 'New Family', 'typolog' ),
			'add_new_item'               => __( 'Add New Family', 'typolog' ),
			'edit_item'                  => __( 'Edit Family', 'typolog' ),
			'update_item'                => __( 'Update Family', 'typolog' ),
			'view_item'                  => __( 'View Family', 'typolog' ),
			'separate_items_with_commas' => __( 'Separate families with commas', 'typolog' ),
			'add_or_remove_items'        => __( 'Add or remove families', 'typolog' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'typolog' ),
			'popular_items'              => __( 'Popular Families', 'typolog' ),
			'search_items'               => __( 'Search Families', 'typolog' ),
			'not_found'                  => __( 'Not Found', 'typolog' ),
			'no_terms'                   => __( 'No families', 'typolog' ),
			'items_list'                 => __( 'Families list', 'typolog' ),
			'items_list_navigation'      => __( 'Families list navigation', 'typolog' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'rewrite'					 => array(
												'slug' => 'family',
												'with_front' => false
											)
		);
		register_taxonomy( 'typolog_family', array( 'typolog_font' ), $args );

	}
	
	// Register Custom Taxonomy
	function register_taxonomy_collection() {
	
		$labels = array(
			'name'                       => _x( 'Collections', 'Taxonomy General Name', 'typolog' ),
			'singular_name'              => _x( 'Collection', 'Taxonomy Singular Name', 'typolog' ),
			'menu_name'                  => __( 'Collections', 'typolog' ),
			'all_items'                  => __( 'All Collections', 'typolog' ),
			'parent_item'                => __( 'Parent Collection', 'typolog' ),
			'parent_item_colon'          => __( 'Parent Collection:', 'typolog' ),
			'new_item_name'              => __( 'New Collection Name', 'typolog' ),
			'add_new_item'               => __( 'Add New Collection', 'typolog' ),
			'edit_item'                  => __( 'Edit Collection', 'typolog' ),
			'update_item'                => __( 'Update Collection', 'typolog' ),
			'view_item'                  => __( 'View Collection', 'typolog' ),
			'separate_items_with_commas' => __( 'Separate collections with commas', 'typolog' ),
			'add_or_remove_items'        => __( 'Add or remove collections', 'typolog' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'typolog' ),
			'popular_items'              => __( 'Popular Collections', 'typolog' ),
			'search_items'               => __( 'Search Collections', 'typolog' ),
			'not_found'                  => __( 'Not Found', 'typolog' ),
			'no_terms'                   => __( 'No collections', 'typolog' ),
			'items_list'                 => __( 'Collections list', 'typolog' ),
			'items_list_navigation'      => __( 'Collections list navigation', 'typolog' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'rewrite'					 => array(
												'slug' => 'collection',
												'with_front' => false
											)
		);
		register_taxonomy( 'typolog_collection', array( 'typolog_font' ), $args );
	
	}

	private function register_post_type_font() {

		$labels = array(
			'name'                  => _x( 'Fonts', 'Post Type General Name', 'typolog' ),
			'singular_name'         => _x( 'Font', 'Post Type Singular Name', 'typolog' ),
			'menu_name'             => __( 'Fonts', 'typolog' ),
			'name_admin_bar'        => __( 'Fonts', 'typolog' ),
			'archives'              => __( 'Font Archives', 'typolog' ),
			'parent_item_colon'     => __( 'Parent Font:', 'typolog' ),
			'all_items'             => __( 'All Fonts', 'typolog' ),
			'add_new_item'          => __( 'Add New Font', 'typolog' ),
			'add_new'               => __( 'Add New', 'typolog' ),
			'new_item'              => __( 'New Font', 'typolog' ),
			'edit_item'             => __( 'Edit Font', 'typolog' ),
			'update_item'           => __( 'Update Font', 'typolog' ),
			'view_item'             => __( 'View Font', 'typolog' ),
			'search_items'          => __( 'Search Font', 'typolog' ),
			'not_found'             => __( 'Not found', 'typolog' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'typolog' ),
			'featured_image'        => __( 'Featured Image', 'typolog' ),
			'set_featured_image'    => __( 'Set featured image', 'typolog' ),
			'remove_featured_image' => __( 'Remove featured image', 'typolog' ),
			'use_featured_image'    => __( 'Use as featured image', 'typolog' ),
			'insert_into_item'      => __( 'Insert into font', 'typolog' ),
			'uploaded_to_this_item' => __( 'Uploaded to this font', 'typolog' ),
			'items_list'            => __( 'Fonts list', 'typolog' ),
			'items_list_navigation' => __( 'Fonts list navigation', 'typolog' ),
			'filter_items_list'     => __( 'Filter fonts list', 'typolog' ),
		);
		$args = array(
			'label'                 => __( 'Font', 'typolog' ),
			'description'           => __( 'Represents a single font style in the type catalog', 'typolog' ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'taxonomies'            => array( 'typolog_family', 'typolog_collection' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 50,
			'menu_icon'				=> null,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'rewrite'					 => array(
												'slug' => 'font'
											)
		);
		register_post_type( 'typolog_font', $args );

	}

	private function register_post_type_file() {
	
		$labels = array(
			'name'                  => _x( 'Files', 'Post Type General Name', 'typolog' ),
			'singular_name'         => _x( 'File', 'Post Type Singular Name', 'typolog' ),
			'menu_name'             => __( 'Files', 'typolog' ),
			'name_admin_bar'        => __( 'File', 'typolog' ),
			'archives'              => __( 'File Archives', 'typolog' ),
			'parent_item_colon'     => __( 'Parent File:', 'typolog' ),
			'all_items'             => __( 'Files', 'typolog' ),
			'add_new_item'          => __( 'Add New File', 'typolog' ),
			'add_new'               => __( 'Add New', 'typolog' ),
			'new_item'              => __( 'New File', 'typolog' ),
			'edit_item'             => __( 'Edit File', 'typolog' ),
			'update_item'           => __( 'Update File', 'typolog' ),
			'view_item'             => __( 'View File', 'typolog' ),
			'search_items'          => __( 'Search File', 'typolog' ),
			'not_found'             => __( 'Not found', 'typolog' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'typolog' ),
			'featured_image'        => __( 'Featured Image', 'typolog' ),
			'set_featured_image'    => __( 'Set featured image', 'typolog' ),
			'remove_featured_image' => __( 'Remove featured image', 'typolog' ),
			'use_featured_image'    => __( 'Use as featured image', 'typolog' ),
			'insert_into_item'      => __( 'Insert into file', 'typolog' ),
			'uploaded_to_this_item' => __( 'Uploaded to this file', 'typolog' ),
			'items_list'            => __( 'Files list', 'typolog' ),
			'items_list_navigation' => __( 'Files list navigation', 'typolog' ),
			'filter_items_list'     => __( 'Filter files list', 'typolog' ),
		);
		$args = array(
			'label'                 => __( 'File', 'typolog' ),
			'description'           => __( 'Files for Typolog', 'typolog' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'taxonomies'            => array( 'typolog_license' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => 'edit.php?post_type=typolog_font',
			'menu_position'         => 50,
			'menu_icon'             => 'dashicons-media-default',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'typolog_file', $args );
	
	}	

	// Register Custom Taxonomy
	function register_taxonomy_license() {
	
		$labels = array(
			'name'                       => _x( 'Licenses', 'Taxonomy General Name', 'typolog' ),
			'singular_name'              => _x( 'License', 'Taxonomy Singular Name', 'typolog' ),
			'menu_name'                  => __( 'Licenses', 'typolog' ),
			'all_items'                  => __( 'All Licenses', 'typolog' ),
			'parent_item'                => __( 'Parent License', 'typolog' ),
			'parent_item_colon'          => __( 'Parent License:', 'typolog' ),
			'new_item_name'              => __( 'New License Name', 'typolog' ),
			'add_new_item'               => __( 'Add New License', 'typolog' ),
			'edit_item'                  => __( 'Edit License', 'typolog' ),
			'update_item'                => __( 'Update License', 'typolog' ),
			'view_item'                  => __( 'View License', 'typolog' ),
			'separate_items_with_commas' => __( 'Separate licenses with commas', 'typolog' ),
			'add_or_remove_items'        => __( 'Add or remove licenses', 'typolog' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'typolog' ),
			'popular_items'              => __( 'Popular Licenses', 'typolog' ),
			'search_items'               => __( 'Search Licenses', 'typolog' ),
			'not_found'                  => __( 'Not Found', 'typolog' ),
			'no_terms'                   => __( 'No licenses', 'typolog' ),
			'items_list'                 => __( 'Licenses list', 'typolog' ),
			'items_list_navigation'      => __( 'Licenses list navigation', 'typolog' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'typolog_license', array( 'typolog_file' ), $args );
	
	}
	
	public function register_custom_types() {
		
		$this->register_post_type_font();

		$this->register_post_type_file();

		$this->register_taxonomy_family();

		$this->register_taxonomy_license();

		$this->register_taxonomy_collection();

	}
	
	public function init_internal_pages() {
		
		add_rewrite_rule( 'fontface$', 'index.php?fontface_css=1', 'top' );
		
		add_rewrite_rule( 'fontface\/\?(.*)$', 'index.php?fontface_css=1&$1', 'top' );
		
		add_rewrite_rule( 'fetch_families$', 'index.php?fetch_families=1', 'top' );
		
		add_rewrite_rule( 'fetch_families\/\?(.*)$', 'index.php?fetch_families=1&$1', 'top' );
		
		add_rewrite_rule( 'typolog_wc_connect$', 'index.php?typolog_wc_connect=1', 'top' );
		
		add_rewrite_rule( 'typolog_wc_connect\/\?(.*)$', 'index.php?typolog_wc_connect=1&$1', 'top' );
		
	}
	
	public function query_internal_pages_vars($query_vars) {
		
		$query_vars[] = 'fontface_css';
		
		$query_vars[] = 'fetch_families';
		
		$query_vars[] = 'typolog_wc_connect';
		
		return $query_vars;
		
	}
	
	public function parse_internal_pages_request(&$wp) {
		
		typolog_log('query_vars', $_REQUEST);
		
		if ( array_key_exists( 'fontface_css', $wp->query_vars ) ) {
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-fontface-css.php';
			
			exit();
			
		}
		
		if ( array_key_exists( 'typolog_wc_connect', $wp->query_vars ) ) {
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-wc-connect.php';
			
			exit();
			
		}
		
		if ( array_key_exists( 'fetch_families', $wp->query_vars ) ) {
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-fetch-families.php';
			
			exit();
			
		}
		
		return;
		
	}
	
}


