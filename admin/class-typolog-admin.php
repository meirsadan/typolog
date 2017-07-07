<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://meirsadan.com/
 * @since      1.0.0
 *
 * @package    Typolog
 * @subpackage Typolog/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Typolog
 * @subpackage Typolog/admin
 * @author     Meir Sadan <meir@sadan.com>
 */

class Typolog_Admin {
	
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

	private $options;
	
	private $admin_settings;
	
	private $DEFAULTS = [
		'base_price' => 325,
		'fonts_dir' => 'fonts',
		'font_products_dir' => 'font_products',
		'general_attachments' => array()
	];
	
	private $font_factory;

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
		
		$options = get_option( 'typolog_settings' );
		
		
		if ( is_array($options) ) {
			$this->options = array_merge($this->DEFAULTS, get_option( 'typolog_settings' ));
		} else {
			$this->options = $this->DEFAULTS;
		}

		$this->font_factory = null;
				
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
		 * defined in Typolog_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Typolog_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/typolog-admin.css', array(), $this->version, 'all' );

		if ( is_rtl() ) {
			wp_enqueue_style( $this->plugin_name . '-rtl', plugin_dir_url( __FILE__ ) . 'css/typolog-admin-rtl.css', array(), $this->version, 'all' );
		}

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
		 * defined in Typolog_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Typolog_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'backbone' );
		wp_enqueue_media();
		wp_enqueue_script( 'dropzonejs', plugin_dir_url( __FILE__ ) . 'js/vendor/dropzone.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'opentypejs', plugin_dir_url( __FILE__ ) . 'js/vendor/opentype.min.js', array( ), $this->version, false );
/*
		wp_enqueue_script( 'underscorejs', plugin_dir_url( __FILE__ ) . 'js/vendor/underscore-min.js', array( ), $this->version, false );
		wp_enqueue_script( 'backbonejs', plugin_dir_url( __FILE__ ) . 'js/vendor/backbone-min.js', array( 'underscorejs' ), $this->version, false );
*/
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/typolog-admin.min.js', array( 'jquery', 'dropzonejs', 'opentypejs', 'underscore', 'backbone' ), $this->version, false );

		wp_localize_script( $this->plugin_name, "acceptedFileTypes", $this->options[ 'allowed_file_extensions' ] );

		if ( 'typolog' == $_REQUEST[ 'page' ] ) {

			wp_enqueue_script( $this->plugin_name . '-generator', plugin_dir_url( __FILE__ ) . 'js/typolog-generator.min.js', array( 'jquery', 'dropzonejs', 'opentypejs', 'underscore', 'backbone' ), $this->version, false );

			wp_localize_script( $this->plugin_name . '-generator', "stripFromFamilyNames", $this->options[ 'strip_from_family_names' ] );
			
			wp_localize_script( $this->plugin_name . '-generator', "fontWeightMap", $this->options[ 'weights_map' ] );

			wp_localize_script( $this->plugin_name . '-generator', "fontStyleMap", $this->options[ 'styles_map' ] );

			wp_localize_script( $this->plugin_name . '-generator', "fontStylesDictionary", $this->options[ 'styles_dictionary' ] );
			
		}

	}
	
	public function create_generator_page() {
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-generator.php';
		
	}

	public function create_sizes_page() {
		
// 		$webfont_names = tl_get_all_webfont_names();

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$size_adjust_tree = $this->font_factory->get_size_adjust_tree();
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-sizes.php';
		
	}

	public function create_prices_page() {
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-price-table.php';
		
	}

	public function create_control_page() {
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-control-table.php';
		
	}

	public function create_family_order_page() {
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-family-order.php';
		
	}

	public function create_cleanup_page() {

		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-cleanup.php';
		
	}
	
	public function create_settings_page() {
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-settings.php';
		
	}
	
	public function add_admin_pages() {

		add_submenu_page( 'edit.php?post_type=typolog_font', __('Licenses', 'typolog'), __('Licenses', 'typolog'), 'edit_posts', 'edit-tags.php?taxonomy=typolog_license&post_type=typolog_file', null );
		
		add_menu_page( __( "Catalog Generator", "typolog" ), __( "Typolog", "typolog" ), 'edit_posts', 'typolog', array(&$this, 'create_generator_page' ), 'none', 49 );
		
		add_submenu_page( 'typolog', __('Catalog Generator', 'typolog'), __('Generator', 'typolog'), 'edit_posts', 'typolog', array(&$this, 'create_generator_page') );

		add_submenu_page( 'typolog', __('Font Size Adjustments', 'typolog'), __('Font Sizes', 'typolog'), 'edit_posts', 'typolog-sizes', array(&$this, 'create_sizes_page') );

		add_submenu_page( 'typolog', __('Order Families', 'typolog'), __('Order Families', 'typolog'), 'edit_posts', 'typolog-family-order', array(&$this, 'create_family_order_page') );

		add_submenu_page( 'typolog', __('Price Table', 'typolog'), __('Price Table', 'typolog'), 'edit_posts', 'typolog-prices', array(&$this, 'create_prices_page') );

		add_submenu_page( 'typolog', __('Control Table', 'typolog'), __('Control Table', 'typolog'), 'edit_posts', 'typolog-control', array(&$this, 'create_control_page') );

		add_submenu_page( 'typolog', __('Cleanup', 'typolog'), __('Cleanup', 'typolog'), 'edit_posts', 'typolog-cleanup', array(&$this, 'create_cleanup_page') );

		add_submenu_page( 'typolog', __('Typolog Settings', 'typolog'), __('Settings', 'typolog'), 'edit_posts', 'typolog-settings', array(&$this, 'create_settings_page') );

// 		add_options_page( __('Typolog Settings', 'typolog'), __('Typolog', 'typolog'), 'edit_posts', 'typolog-settings', array(&$this, 'create_settings_page') );
		
	}
	
	function settings_page_init() {
		
		$this->admin_settings = new Typolog_Admin_Settings();
		
	}
	
	function check_for_stuff() {
		
		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		// First, is WC API connected properly?
		
		if ( !$this->font_factory->is_wc_ready() ) {
			
			echo '<div class="notice notice-error"><p>' . sprintf( __('Store isn\'t connected! <a href="%s">Click here</a> to connect with Woocommerce.', 'typolog' ), $this->font_factory->wc_build_authorize_link()) . '</p></div>';
			
			return;			
			
		}
		
		// Then, check if WC categories are defined as they should
		
		if ( ( !TypologOptions()->get( 'fonts_product_category' ) ) || ( !TypologOptions()->get( 'license_product_attribute' ) ) ) {
			
			$res = $this->font_factory->create_wc_categories();
			
			if ( is_wp_error( $res ) ) {
				
				echo '<div class="notice notice-error"><p>' . $res->get_error_message() . '</p></div>';
				
			} else {
				
				echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Fonts Product Category and License Attribute created successfully!', 'typolog' ) . '</p></div>';
				
			}
			
		}
		
	}
	
	
	/* Handles updating the associated font product on save */
	
	function update_font_products_handler( $font_id ) {

		if ( Typolog_Font_Query::is_font( $font_id ) ) {

			if ( !$this->font_factory ) {
				$this->font_factory = new Typolog_Font_Factory($this->options);		
			}
			return $this->font_factory->update_font_products( $font_id );
		}
		
	}
	
	function update_products_handler() {

		$id = ( isset( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : null;

		$type = ( isset( $_REQUEST['type'] ) ) ? $_REQUEST['type'] : null;
		
		if ( ( "family" == $type ) && ( $id ) ) {
			
			$font_ids = Typolog_Family_Query::get_font_order( $id );
			
		} elseif ( ( "font" == $type ) && ( $id ) ) {
			
			$font_ids = [ $id ];
			
		}

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		if ( $this->font_factory->update_font_products( $font_ids ) ) {
			
			$data_array = [
				
				"family" => [],
				
				"font" => []
				
			];
			
			foreach ( $font_ids as $font_id ) {
				
				$family_id = Typolog_Font_Query::get_family( $font_id )->term_id;
				
				$product_id = Typolog_Family_Query::get_meta( $family_id, "_product_id" );

				if ( !isset( $data_array[ "family" ][ $family_id ] ) && ( $product_id ) ) {
					
					$data_array[ "family" ][ $family_id ][ "id" ] = $family_id;
					$data_array[ "family" ][ $family_id ][ "name" ] = get_the_title( $product_id );
					$data_array[ "family" ][ $family_id ][ "url" ] = get_edit_post_link( $product_id );
					
					
				}
				
				$product_id = Typolog_Font_Query::get_meta( $font_id, "_product_id" );			

				$data_array[ "font" ][ $font_id ][ "id" ] = $font_id;
				$data_array[ "font" ][ $font_id ][ "name" ] = get_the_title( $product_id );
				$data_array[ "font" ][ $font_id ][ "url" ] = get_edit_post_link( $product_id );
				
			}

			echo_and_die( [
				
				"success" => __( "Updated products successfully.", "typolog" ),
				
				"data" => $data_array
				
			] );
			
		}
		
		echo_and_die( [
			
			"error" => __( "An error occured while updating products.", "typolog" )
			
		] );
		
	}

	function edit_family_fonts_handler() {
		
		$family_id = ( isset( $_REQUEST['family_id'] ) ) ? $_REQUEST['family_id'] : null;
		
		$fonts = Typolog_Family_Query::get_font_order( $family_id );

		$family_display_family_name = get_term( $family_id )->name;
		
		$family_family_name = get_term_meta( $family_id, '_family_name', true );
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-family-edit.php';
		
		exit;
		
	}

	function regenerate_fonts_handler() {
		
		$font_id = ( isset( $_REQUEST['font_id'] ) ) ? $_REQUEST['font_id'] : null;

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		if ( $this->font_factory->update_font_products( $font_id ) ) {
			
			echo_and_die( [ "success" => __( "Regenerated fonts successfully.", "typolog" ) ] );
			
		}
		
		echo_and_die( [ "error" => __( "An error occured while regenerating fonts.", "typolog" ) ] );
		
	}

	function regenerate_family_handler() {
		
		$family_id = ( isset( $_REQUEST['family_id'] ) ) ? $_REQUEST['family_id'] : null;
		
		if ( !$family_id ) {
			echo_and_die( [	"error" => "No family given." ] );
		}

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		if ( $this->font_factory->update_family_products( $family_id ) ) {
			
			echo_and_die( [ "success" => __( "Regenerated family fonts successfully.", "typolog" ) ] );
			
		}
		
		echo_and_die( [ "error" => __( "An error occured while regenerating fonts.", "typolog" ) ] );
		
	}
	
	function reset_products_handler() {
		
		$fonts = Typolog_Font_Query::get_all();

		foreach( $fonts as $font ) {
			delete_post_meta( $font->ID, '_product_id' );
			delete_post_meta( $font->ID, '_variation_ids' );
		}
		
		$families = Typolog_Family_Query::get_all();
		
		foreach ( $families as $family ) {
			delete_term_meta( $family->term_id, '_product_id' );
			delete_term_meta( $family->term_id, '_variation_ids' );
		}

		echo_and_die( [ "success" => __( "Reset all products successfully.", "typolog" ) ] );
		
	}
	
	function update_font_packages_handler() {
		
		required_vars( [ $_REQUEST['files'], $_REQUEST['font_id'] ], true );

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory( $this->options );		
		}
		
		$result = $this->font_factory->update_font_files( $_REQUEST['files'] );
		
		if ( is_array( $result ) ) {
			
			echo_and_die( [ 
				"success" => __( "Updated packages successfully.", "typolog" ), 
				"table" => Typolog_Font_Query::get_licenses_table( $_REQUEST['font_id'] ), 
				"report" => $result 
			] );
			
		}
		
		echo_and_die( [ "error" => __( "An error occured while updating packages.", "typolog" ) ] );
	}
	
	function upload_font_handler() {
		
		required_vars( [ $_FILES['file'] ] , true );
		
		$font_id = ( isset( $_REQUEST['font_id'] ) ) ? $_REQUEST['font_id'] : null;

		$font_file_id = ( isset( $_REQUEST['font_file_id'] ) ) ? $_REQUEST['font_file_id'] : null;

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		if ( $this->font_factory->upload_font( $_FILES['file'], $font_id, $font_file_id ) ) {
			
			if ( $font_id ) {
				
				ob_start();
				
				$this->render_licenses_table( $font_id );
				
				$table = ob_get_clean();
				
			} else {
				
				$table = '';
				
			}
			
			if ( $font_file_id ) {
				
				$font_file = new Typolog_Font_File( $font_file_id );
				
				$url = $font_file->get_file_url();
				
			} else {
				
				$url = "";
				
			}
			
			echo_and_die( [
				
				"success" => __( "File uploaded successfully!", "typolog" ),
				
				"table" => $table,
				
				"url" => $url
			
			] );
			
		}
		
		echo_and_die( [ "error" => __( "An error occured while moving the uploaded file", "typolog" ) ] );
		
	}

	function delete_font_file_handler() {
		
		required_vars( [ $_REQUEST['font_id'], $_REQUEST['file_id'] ] , true );

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$res = $this->font_factory->delete_font_file( $_REQUEST['file_id'], $_REQUEST['font_id'] );
		
		if ( $res ) {
			
			echo_and_die( [ 
				
				"success" => __( "File deleted successfully.", "typolog" ),
				
				"table" => Typolog_Font_Query::get_licenses_table( $_REQUEST['font_id'] )
				
			] );
			
		}
		
		echo_and_die( [ "error" => __( "An error occured while deleting the file", "typolog" ) ] );
		
	}
	
	function upload_catalog_handler() {
		
		required_vars( [ $_REQUEST['fonts'] ] , true );
		
		$commercial = ( isset( $_REQUEST['commercial'] ) ) ? $_REQUEST['commercial'] : null;

		$collection = ( isset( $_REQUEST['collection'] ) ) ? $_REQUEST['collection'] : null;
		
		$fonts = json_decode( stripcslashes( $_REQUEST['fonts'] ) );
		
		if ( !$fonts ) {
			
			echo_and_die( [ "error" => json_last_error_msg() ] );
			
		}

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory( $this->options );
		}
		
		$res = $this->font_factory->upload_catalog( $fonts, $commercial, $collection );
		
		if ( is_wp_error( $res ) ) {
			
			echo_and_die( [ 
				
				"error" => $res->get_error_message(),
				
				"data" => $res->get_error_data()
				
			] );
			
		} elseif ( !$res ) {
			
			echo_and_die( [ "error" => __( "Unknown error occured", "typolog" ) ] );
			
		} else {
			
			echo_and_die( [ "success" => __( "Fonts updated successfully!", "typolog" ), "report" => $res, "report_message" => sprintf( __( "Created %s new fonts. Updated %s existing fonts. %s errors.", "typolog" ), count( $res[ "created"] ), count( $res[ "updated" ] ), count( $res[ "error" ] ) ) ] );
			
		}
			
	}
	
	function delete_all_fonts_handler() {

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$res = $this->font_factory->delete_all_fonts();
		
		typolog_log( 'delete_fonts', $res );
		
		if ( is_wp_error( $res ) ) {
			
			echo_and_die( [ 'error' => $res->get_error_message(), 'data' => $res->get_error_data() ] );
			
		} elseif ( !$res ) {
			
			echo_and_die( [ 'error' => 'Error deleting all files and families.' ] );
			
		}
		
		echo_and_die( [ 'success' => 'Deleted all fonts and families!' ] );
		
	}

	function delete_fonts_handler() {

		required_vars( array( $_REQUEST[ 'delete_fonts' ] ), true );
		
		$report = [ "success" => [], "fail" => [] ] ;

		foreach ( $_REQUEST[ 'delete_fonts' ] as $font_id ) {
			$f = new Typolog_Font( $font_id );
			if ( $f->delete() ) {
				array_push( $report[ "success" ], $font_id );
			} else {
				array_push( $report[ "fail" ], $font_id );
			}
		}
		
		typolog_log( 'delete_fonts', $report );
		
		echo_and_die( [ 
			'success' => sprintf( __( 'Deleted %s fonts successfully. Failed to delete %s fonts.', 'typolog' ), count( $report["success"] ), count( $report["fail"] ) ), 
			'report' => $report
		] );
		
	}

	function delete_font_files_handler() {

		required_vars( array( $_REQUEST[ 'delete_files' ] ), true );
		
		$report = [ "success" => [], "fail" => [] ] ;

		foreach ( $_REQUEST[ 'delete_files' ] as $file_id ) {
			$f = new Typolog_Font_file( $file_id );
			if ( $f->delete() ) {
				array_push( $report[ "success" ], $file_id );
			} else {
				array_push( $report[ "fail" ], $file_id );
			}
		}
		
		typolog_log( 'delete_files', $report );
		
		echo_and_die( [ 
			'success' => sprintf( __( 'Deleted %s files successfully. Failed to delete %s files.', 'typolog' ), count( $report["success"] ), count( $report["fail"] ) ), 
			'report' => $report
		] );
		
	}

	function delete_products_handler() {

		required_vars( array( $_REQUEST[ 'delete_products' ] ), true );
		
		$report = [ "success" => [], "fail" => [] ] ;

		foreach ( $_REQUEST[ 'delete_products' ] as $product_id ) {
			if ( wp_delete_post( $product_id, true ) ) {
				array_push( $report[ "success" ], $product_id );
			} else {
				array_push( $report[ "fail" ], $product_id );
			}
		}
		
		typolog_log( 'delete_products', $report );
		
		echo_and_die( [ 
			'success' => sprintf( __( 'Deleted %s products successfully. Failed to delete %s products.', 'typolog' ), count( $report["success"] ), count( $report["fail"] ) ), 
			'report' => $report
		] );
		
	}

	function delete_downloads_handler() {

		required_vars( array( $_REQUEST[ 'delete_downloads' ] ), true );

		$upload_dir = wp_upload_dir();
		
		$products_path = $upload_dir[ 'basedir' ] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';
		
		$report = [ "success" => [], "fail" => [] ] ;

		foreach ( $_REQUEST[ 'delete_downloads' ] as $download ) {
			if ( unlink( $products_path . $download ) ) {
				array_push( $report[ "success" ], $download );
			} else {
				array_push( $report[ "fail" ], $download );
			}
		}
		
		typolog_log( 'delete_downloads', $report );
		
		echo_and_die( [ 
			'success' => sprintf( __( 'Deleted %s downloads successfully. Failed to delete %s downloads.', 'typolog' ), count( $report["success"] ), count( $report["fail"] ) ), 
			'report' => $report
		] );
		
	}

	function delete_originals_handler() {

		required_vars( array( $_REQUEST[ 'delete_originals' ] ), true );

		$upload_dir = wp_upload_dir();
		
		$originals_dir = $upload_dir[ 'basedir' ] . '/' . TypologOptions()->get( 'fonts_dir' ) . '/';
		
		$report = [ "success" => [], "fail" => [] ] ;

		foreach ( $_REQUEST[ 'delete_originals' ] as $original ) {
			if ( unlink( $originals_dir . $original ) ) {
				array_push( $report[ "success" ], $original );
			} else {
				array_push( $report[ "fail" ], $original );
			}
		}
		
		typolog_log( 'delete_originals', $report );
		
		echo_and_die( [ 
			'success' => sprintf( __( 'Deleted %s original font files successfully. Failed to delete %s files.', 'typolog' ), count( $report["success"] ), count( $report["fail"] ) ), 
			'report' => $report
		] );
		
	}
	
	function save_size_adjustments_handler() {
		
		required_vars( array( $_REQUEST['sizes'] ), true );

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$res = $this->font_factory->save_size_adjustments( $_REQUEST['sizes'] );
		
		if ( is_wp_error( $res ) ) {
			
			echo_and_die( [ 'error' => $res->get_error_message(), 'data' => $res->get_error_data() ] );
			
		} elseif ( !$res ) {
			
			echo_and_die( [ 'error' => __( 'Error saving font size adjustments.', 'typolog' ) ] );
			
		}
		
		echo_and_die( [ 'success' => __( 'Saved all font size adjustments!', 'typolog' ) ] );
		
	}
	
	function delete_font_file_before_delete_post( $font_file_id ) {
		
		if ( "typolog_file" != get_post_type( $font_file_id ) ) return;
		
		$font_file = new Typolog_Font_File( $font_file_id );
		
		$filename = $font_file->get_file_path();
		
		// Delete actual file (if it exists)
		
		if ( file_exists( $filename ) ) {
			
			unlink( $filename );
			
		}

	}
	
	function delete_product_relationships( $product_id ) {
		
		// do this before deleting a product
		
		if ( "product" != get_post_type( $product_id ) ) return; 
		
		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory( $this->options );		
		}
		
		return $this->font_factory->delete_product_relationships( $product_id );
		
	}
	
	function print_font_debug_metabox( $font ) {
		
		$font_meta = Typolog_Font_Query::get_meta( $font->ID );
		
		echo '<table dir=ltr width=100%>';
		
		foreach ( $font_meta as $font_meta_key => $font_meta_value ) {
			
			echo "<tr><td>$font_meta_key</td><td>{$font_meta_value[0]}</td></tr>";
			
		}
		
		echo '</table>';
		
		echo '<hr>';
		
		$family = Typolog_Font_Query::get_family( $font->ID );
		
		if ( is_object( $family ) ) {
			
			$font_meta = Typolog_Family_Query::get_meta( $family->term_id );
			
			typolog_log( 'font_debug_family_meta', $font_meta );
		
			echo '<table dir=ltr width=100%>';
			
			foreach ( $font_meta as $font_meta_key => $font_meta_value ) {
				
				echo "<tr><td>$font_meta_key</td><td>{$font_meta_value[0]}</td></tr>";
				
			}
			
			echo '</table>';
			
		}
		
		
		echo '<pre>';
		
		echo '</pre>';
?>

<?php
	}
	
	function register_metaboxes() {
		
		add_meta_box( 'typolog-font-preview-box', __( 'Font Preview', 'typolog' ), array($this, 'print_font_preview_metabox'), array( 'typolog_font', 'typolog_file' ) );
		
		add_meta_box( 'typolog-font-files-box', __( 'Font Files', 'typolog' ), array($this, 'print_font_files_metabox'), 'typolog_font' );
		
		add_meta_box( 'typolog-font-meta-fields', __( 'Font Meta Fields', 'typolog' ), array($this, 'print_font_meta_fields_metabox'), 'typolog_font' );
		
		add_meta_box( 'typolog-font-products-box', __( 'Font Products', 'typolog' ), array($this, 'print_font_products_metabox'), 'typolog_font', 'side' );
		
		add_meta_box( 'typolog-font-attachments-box', __( 'Font Attachments', 'typolog' ), array($this, 'print_font_attachments_box'), 'typolog_font', 'side' );
		
		add_meta_box( 'typolog-font-debug-box', __( 'Font Debug', 'typolog' ), array($this, 'print_font_debug_metabox'), 'typolog_font' );

		add_meta_box( 'typolog-font-file-update-box', __( 'Font File', 'typolog' ), array($this, 'print_font_file_update_metabox'), 'typolog_file' );
		
	}
	
	function update_font_data( $font_id ) {
		
		$res = $this->save_font_meta_fields( $font_id );
		
		return $this->save_font_attachments( $font_id );

		
	}
	
	function print_font_preview_metabox( $font ) {
		
		if ( 'typolog_file' == $font->post_type ) {
			
			$font_file_obj = new Typolog_Font_File( $font->ID );
			
			$fontface = $font_file_obj->generate_fontface( 'FontPreview' );
			
			$web_family_name = 'FontPreview';
			
		} else {
			
			$fontface = Typolog_Font_Query::get_meta( $font->ID, '_fontface' );
			
			$web_family_name = Typolog_Font_Query::get_meta( $font->ID, '_web_family_name' );
	
		}
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-font-preview.php';
		
	}

	function print_font_products_metabox( $font ) {
		
		$font_products = Typolog_Font_Query::get_products( $font->ID );
		
		if ( $font_products ) {
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-font-product-list.php';
			
		} else {
			
			_e( 'No products are currently associated with this font.' , 'typolog');
			
		}
		
	}

	function print_font_meta_fields_metabox( $font ) {
		
		wp_nonce_field( 'font_edit_meta_fields', 'font_meta_fields_nonce' );
		
		$font_meta_fields = [
			"_display_family_name" => __( "Display Font Family Name", "typolog" ),
			"_display_style_name" => __( "Display Font Style Name", "typolog" ),
			"_family_name" => __( "Original Font Family Name", "typolog" ),
			"_style_name" => __( "Original Font Style Name", "typolog" ),
			"_web_family_name" => __( "Web Family Name", "typolog" ),
			"_font_weight" => __( "Font Weight (100–900)", "typolog" ),
			"_font_style" => __( "Font Style (normal/italic)", "typolog" ),
			"_price" => __( "Font Price", "typolog" )
		];
		
		$licenses = Typolog_License_Query::get_all_slugs();
		
		foreach ( $licenses as $license ) {
			$font_meta_fields[ "_price_" . $license ] = sprintf( __( "Font Price for %s license", "typolog" ), $license );
		}
		
		if ( $font_meta_fields ) {
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-font-meta-fields.php';
			
		}
		
	}
	
	function save_font_meta_fields( $font_id ) {
		
		if ( !isset( $_POST['font_meta_fields_nonce'] ) || !wp_verify_nonce( $_POST['font_meta_fields_nonce'], 'font_edit_meta_fields' ) ) {
			
			return $font_id;
			
		}
		
		if ( Typolog_Font_Query::is_font( $font_id ) ) {
			
			if ( isset( $_POST['font_meta'] ) && ( !empty( $_POST['font_meta'] ) ) ) {
				
				$font = new Typolog_Font( $font_id );
				
				$font->set_meta( $_POST['font_meta'] );
				
			}
			
		}
		
		return $font_id;
		
	}

	function render_licenses_table( $font_id ) {
		
		if ( $font_id ) {
			
			$packages_table = Typolog_Font_Query::get_licenses_table( $font_id );
			
			include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-font-files-table.php';
			
		}
		
	}

	function print_font_files_metabox( $font ) {
		
		?>
<div id="font_file_dropzone" class="dnd-zone">
	<span class="dnd-label"><?php _e('Drop font files here', 'typolog'); ?></span>
</div>
		<?php
			
		$this->render_licenses_table( $font->ID );
		
	}

	function print_font_file_update_metabox( $font_file ) {
		
		$font_file_obj = new Typolog_Font_File( $font_file->ID );
		$font_file_url = $font_file_obj->get_file_url();
		
		?>
<div id="font_file_update_dropzone" class="dnd-zone">
	<span class="dnd-label"><?php _e('Drop font files here', 'typolog'); ?></span>
</div>
<p>
<a href="<?=$font_file_url ?>" class="button font-file-download"><?php _e( 'Download file', 'typolog' ); ?></a>
</p>
		<?php
		
	}
	
	function print_license_extensions_field() {
		
	?>
	<div class="form-field">
		<label for="license_extensions"><?php _e( 'Associated file extensions', 'typolog' ); ?></label>
		<input type="text" name="license_extensions" id="license_extensions" value="">
		<p class="description"><?php _e( 'Enter extensions associated with this license, separated by |','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_license_edit_extensions_field($term) {
	 
		$license_extensions = get_term_meta( $term->term_id, '_extensions', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="license_extensions"><?php _e( 'Associated file extensions', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="license_extensions" id="license_extensions" value="<?php echo esc_attr( $license_extensions ) ? esc_attr( $license_extensions ) : ''; ?>">
				<p class="description"><?php _e( 'Enter extensions associated with this license, separated by |','typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_license_extensions_field( $term_id ) {
		
		if ( isset( $_POST['license_extensions'] ) ) {
			
			$license_extensions = sanitize_text_field( $_POST['license_extensions'] );
			
			update_term_meta( $term_id, '_extensions', $license_extensions );
			
		}
		
	}  


	function print_license_base_price_field() {
		
	?>
	<div class="form-field">
		<label for="base_price"><?php _e( 'Base price', 'typolog' ); ?></label>
		<input type="text" name="base_price" id="base_price" value="">
		<p class="description"><?php _e( 'Enter base price for license type','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_license_edit_base_price_field($term) {
	 
		$base_price = get_term_meta( $term->term_id, '_base_price', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="base_price"><?php _e( 'Base price', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="base_price" id="base_price" value="<?php echo esc_attr( $base_price ) ? esc_attr( $base_price ) : ''; ?>">
				<p class="description"><?php _e( 'Enter base price for license type','typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_license_base_price_field( $term_id ) {
		
		if ( isset( $_POST['base_price'] ) ) {
			
			$base_price = sanitize_text_field( $_POST['base_price'] );
			
			update_term_meta( $term_id, '_base_price', $base_price );
			
		}
		
	}  



	function print_license_order_field() {
		
	?>
	<div class="form-field">
		<label for="license_order"><?php _e( 'Order', 'typolog' ); ?></label>
		<input type="text" name="license_order" id="license_order" value="">
		<p class="description"><?php _e( 'Enter order # of license','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_license_edit_order_field($term) {
	 
		$license_order = get_term_meta( $term->term_id, '_order', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="license_order"><?php _e( 'Order', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="license_order" id="license_order" value="<?php echo esc_attr( $license_order ) ? esc_attr( $license_order ) : ''; ?>">
				<p class="description"><?php _e( 'Enter order # of license','typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_license_order_field( $term_id ) {
		
		if ( isset( $_POST['license_order'] ) ) {
			
			$license_order = sanitize_text_field( $_POST['license_order'] );
			
			update_term_meta( $term_id, '_order', $license_order );
			
		}
		
	}  

	function save_license_attribute_term( $term_id ) {

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$this->font_factory->save_license_attribute_term( $term_id );
		
	}

	function delete_license_attribute_term( $term_id ) {

		if ( !$this->font_factory ) {
			$this->font_factory = new Typolog_Font_Factory($this->options);		
		}
		
		$this->font_factory->delete_license_attribute_term( $term_id );
		
	}

	function print_family_family_name_field() {
		
	?>
	<div class="form-field">
		<label for="family_name"><?php _e( 'Family Name', 'typolog' ); ?></label>
		<input type="text" name="family_name" id="family_name" value="">
		<p class="description"><?php _e( 'Enter family name in Latin letters for product archives','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_family_name_field( $term ) {
	 
		$family_name = get_term_meta( $term->term_id, '_family_name', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="family_name"><?php _e( 'Family Name', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="family_name" id="family_name" value="<?php echo esc_attr( $family_name ) ? esc_attr( $family_name ) : ''; ?>">
				<p class="description"><?php _e( 'Enter family name in Latin letters for product archives','typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_family_name_field( $term_id ) {
		
		if ( isset( $_POST['family_name'] ) ) {
			
			$family_name = sanitize_text_field( $_POST['family_name'] );
			
			update_term_meta( $term_id, '_family_name', $family_name );
			
		}
		
	}

	function print_family_family_index_name_field() {
		
	?>
	<div class="form-field">
		<label for="family_index_name"><?php _e( 'Family Index Name', 'typolog' ); ?></label>
		<input type="text" name="family_index_name" id="family_index_name" value="">
		<p class="description"><?php _e( 'Enter family name for display on index pages (i.e. homepage)', 'typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_family_index_name_field( $term ) {
	 
		$family_index_name = get_term_meta( $term->term_id, '_family_index_name', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="family_index_name"><?php _e( 'Family Index Name', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="family_index_name" id="family_index_name" value="<?php echo esc_attr( $family_index_name ) ? esc_attr( $family_index_name ) : ''; ?>">
				<p class="description"><?php _e( 'Enter family name for display on index pages (i.e. homepage)', 'typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_family_index_name_field( $term_id ) {
		
		if ( isset( $_POST['family_index_name'] ) ) {
			
			$family_index_name = sanitize_text_field( $_POST['family_index_name'] );
			
			update_term_meta( $term_id, '_family_index_name', $family_index_name );
			
		}
		
	}


	function print_family_badge_label_field() {
		
	?>
	<div class="form-field">
		<label for="badge_label"><?php _e( 'Badge Label', 'typolog' ); ?></label>
		<input type="text" name="badge_label" id="badge_label" value="">
		<p class="description"><?php _e( 'Enter badge label for special offers/etc.','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_badge_label_field( $term ) {
	 
		$badge_label = get_term_meta( $term->term_id, '_badge_label', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="badge_label"><?php _e( 'Badge Label', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="badge_label" id="badge_label" value="<?php echo esc_attr( $badge_label ) ? esc_attr( $badge_label ) : ''; ?>">
				<p class="description"><?php _e( 'Enter badge label for special offers/etc.', 'typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_badge_label_field( $term_id ) {
		
		if ( isset( $_POST['badge_label'] ) ) {
			
			$badge_label = sanitize_text_field( $_POST['badge_label'] );
			
			update_term_meta( $term_id, '_badge_label', $badge_label );
			
		}
		
	}


	function print_family_buy_link_field() {
		
	?>
	<div class="form-field">
		<label for="buy_link"><?php _e( 'Buy Link', 'typolog' ); ?></label>
		<input type="text" name="buy_link" id="buy_link" value="">
		<p class="description"><?php _e( 'Enter link for buying at an external website.','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_buy_link_field( $term ) {
	 
		$buy_link = get_term_meta( $term->term_id, '_buy_link', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="buy_link"><?php _e( 'Buy Link', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="buy_link" id="buy_link" value="<?php echo esc_attr( $buy_link ) ? esc_attr( $buy_link ) : ''; ?>">
				<p class="description"><?php _e( 'Enter link for buying at an external website.', 'typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_buy_link_field( $term_id ) {
		
		if ( isset( $_POST['buy_link'] ) ) {
			
			$buy_link = sanitize_text_field( $_POST['buy_link'] );
			
			update_term_meta( $term_id, '_buy_link', $buy_link );
			
		}
		
	}


	function print_family_website_field() {
		
	?>
	<div class="form-field">
		<label for="website_link"><?php _e( 'Website Link', 'typolog' ); ?></label>
		<input type="text" name="website_link" id="website_link" value="">
		<p class="description"><?php _e( 'Enter link for promotional website.','typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_website_field( $term ) {
	 
		$website_link = get_term_meta( $term->term_id, '_website_link', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="buy_link"><?php _e( 'Website Link', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="website_link" id="website_link" value="<?php echo esc_attr( $website_link ) ? esc_attr( $website_link ) : ''; ?>">
				<p class="description"><?php _e( 'Enter link for promotional website.', 'typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_website_field( $term_id ) {
		
		if ( isset( $_POST['website_link'] ) ) {
			
			$website_link = sanitize_text_field( $_POST['website_link'] );
			
			update_term_meta( $term_id, '_website_link', $website_link );
			
		}
		
	}


	function print_family_price_fields() {
		
		$licenses = Typolog_License_Query::get_all_slugs();
		
	?>
	<div class="form-field">
		<label for="family_price"><?php _e( 'Family Price', 'typolog' ); ?></label>
		<input type="text" name="family_price[all]" id="family_price" value="">
		<p class="description"><?php _e( 'Enter family price for all license types (leave blank for default).','typolog' ); ?></p>
	</div>

<?php foreach ( $licenses as $license ) : ?>
	<div class="form-field">
		<label for="family_price_<?=$license ?>"> <?php _e( 'Family Price', 'typolog' ); ?> - <?=$license ?></label>
		<input type="text" name="family_price[<?=$license ?>]" id="family_price_<?=$license ?>" value="">
		<p class="description"><?php printf( __( 'Enter family price for %s license type (leave blank for default).', 'typolog' ), $license ); ?></p>
	</div>
<?php endforeach; ?>
		
<?php
	
	}

	function print_family_edit_price_fields( $term ) {
	 
		$licenses = Typolog_License_Query::get_all_slugs();

		$family_price[ 'all' ] = get_term_meta( $term->term_id, '_price', true );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="family_price"><?php _e( 'Family Price', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="family_price[all]" id="family_price" value="<?php echo esc_attr( $family_price[ 'all' ] ) ? esc_attr( $family_price[ 'all' ] ) : ''; ?>">
				<p class="description"><?php _e( 'Enter family price for all license types (leave blank for default).', 'typolog' ); ?></p>
			</td>
		</tr>
<?php foreach ( $licenses as $license ) : 
		$family_price[ $license ] = get_term_meta( $term->term_id, '_price_' . $license, true );

?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="family_price_<?=$license ?>"> <?=$license ?> <?php _e( 'Family Price', 'typolog' ); ?></label></th>
			<td>
				<input type="text" name="family_price[<?=$license ?>]" id="family_price_<?=$license ?>" value="<?php echo esc_attr( $family_price[ $license ] ) ? esc_attr( $family_price[ $license ] ) : ''; ?>">
				<p class="description"><?php printf( __( 'Enter family price for %s license type (leave blank for default).', 'typolog' ), $license ); ?></p>
			</td>
		</tr>
<?php endforeach; ?>
	<?php
	
	}
	
	function save_family_price_fields( $term_id ) {
		
		if ( isset( $_POST['family_price'] ) ) {
			
			$family_price = $_POST['family_price'];
			
			if ( is_array( $family_price ) ) {

				$licenses = Typolog_License_Query::get_all_slugs();
				
				if ( isset( $family_price[ 'all' ] ) ) {
					update_term_meta( $term_id, '_price', $family_price[ 'all' ] );
				} else {
					delete_term_meta( $term_id, '_price' );
				}
				
				foreach ( $licenses as $license ) {
					if ( isset( $family_price[ $license ] ) ) {
						update_term_meta( $term_id, '_price_' . $license, $family_price[ $license ] );
					} else {
						delete_term_meta( $term_id, '_price_' . $license );
					}
				}
				

			}
			
			
		}
		
	}


	
	function print_font_attachments_box( $font ) {
		
		wp_nonce_field( 'typolog_font_attachments_fields', 'typolog_font_attachments_nonce' );
		
		$attachments_obj = new Typolog_Attachments();
		
		$attachments_obj->load_font( $font );
		
		$attachments = $attachments_obj->get_table();
				
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-attachments.php';
		
	}
	
	function save_font_attachments( $font_id ) {
		
		if ( !isset( $_POST['typolog_font_attachments_nonce'] ) || !wp_verify_nonce( $_POST['typolog_font_attachments_nonce'], 'typolog_font_attachments_fields' ) ) {
			
			return $font_id;
			
		}
		
		if ( Typolog_Font_Query::is_font( $font_id ) ) {
			
			$attachments_data = ( isset( $_POST['attachments'] ) ) ? $_POST['attachments'] : [];
			
			$attachments = new Typolog_Attachments();
			
			$attachments->load_font( $font_id );
			
			$attachments->set( $attachments_data );
						
		}
		
		return $font_id;
		
	}

	function print_family_attachments_field() {
		
		$attachments = [];
		
		echo '<div class="form-field">';
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-attachments.php';
		
		echo '</div>';
		
	}

	function print_family_edit_attachments_field( $term ) {
		
		$attachments_obj = new Typolog_Attachments();
		
		$attachments_obj->load_family( $term );
		
		$attachments = $attachments_obj->get_table();
		
		echo '<tr class="form-field"><th scope="row" valign="top"><label for="attachments">' . __( 'Attachments', 'typolog' ) . '</label></th><td>';
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-attachments.php';
		
		echo '</td></tr>';
		
	}
	
	function save_family_attachments_field( $term_id ) {
		
		if ( isset( $_POST['attachments'] ) && is_array( $_POST['attachments'] ) ) {
			
			$attachments = new Typolog_Attachments();
			
			$attachments->load_family( $term_id );
			
			$attachments->set( $_POST['attachments'] );
			
		}
		
	}


	function print_family_pdf_field() {
		
		$pdf = null;
		
		echo '<div class="form-field">';
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-pdf.php';
		
		echo '</div>';
		
	}

	function print_family_edit_pdf_field( $term ) {
		
		$family = new Typolog_Family( $term );
		
		$pdf_id = $family->get_meta( '_pdf' );
		
		$pdf = get_post( $pdf_id );

		echo '<tr class="form-field"><th scope="row" valign="top"><label for="pdf">' . __( 'PDF', 'typolog' ) . '</label></th><td>';
				
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-pdf.php';
		
		echo '</td></tr>';
		
	}
	
	function save_family_pdf_field( $term_id ) {
		
		if ( isset( $_POST['pdf'] ) ) {
			
			update_term_meta ( $term_id, '_pdf', $_POST['pdf'] );
			
		}
		
	}



	function print_license_attachments_field() {
		
		$attachments = [];
		
		echo '<div class="form-field">';
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-attachments.php';
		
		echo '</div>';
		
	}

	function print_license_edit_attachments_field($term) {
		
		$attachments_obj = new Typolog_Attachments();	
		
		$attachments_obj->load_license( $term );
		
		$attachments = $attachments_obj->get_table();
				
		echo '<tr class="form-field"><th scope="row" valign="top"><label for="attachments">' . __( 'Attachments', 'typolog' ) . '</label></th><td>';
		
		include plugin_dir_path( __FILE__ ) . 'partials/typolog-admin-attachments.php';
		
		echo '</td></tr>';
		
	}
	
	function save_license_attachments_field( $term_id ) {
		
		if ( isset( $_POST['attachments'] ) && is_array( $_POST['attachments'] ) ) {
			
			$attachments = new Typolog_Attachments();
			
			$attachments->load_license( $term_id );
			
			$attachments->set( $_POST['attachments'] );
			
		}
		
	}

	function print_family_commercial_field() {
		
	?>
	<div class="form-field">
		<input type="checkbox" name="family_commercial" id="family_commercial" value="1" checked>
		<label for="family_commercial"><?php _e( 'Commercial', 'typolog' ); ?></label>
		<p class="description"><?php _e( 'Determines whether Typolog will create products for font family in the store.', 'typolog' ); ?></p>
	</div>
<?php
	
	}

	function print_family_edit_commercial_field( $term ) {
		
		$family_commercial = Typolog_Family_Query::get_meta( $term->term_id, '_commercial' );
		
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"></th>
			<td>
				<input type="checkbox" name="family_commercial" id="family_commercial" value="1" <?php checked(1, $family_commercial); ?>>
				<label for="family_commercial"><?php _e( 'Commercial', 'typolog' ); ?></label>
				<p class="description"><?php _e( 'Determines whether Typolog will create products for font family in the store.', 'typolog' ); ?></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_commercial_field( $term_id ) {
		
		$family = new Typolog_Family( $term_id );
		
		if ( isset( $_POST['family_commercial'] ) ) {
			
			$family->set_meta( '_commercial', 1 );
			
		} else {
			
			$family->unset_meta( '_commercial' );
			
		}
		
	}  


	function print_family_edit_font_order($term) {
		
		$font_order = Typolog_Family_Query::get_font_order( $term->term_id );
		
		$main_font = Typolog_Family_Query::get_main_font( $term->term_id );
	 	 	
?>
	 
		<tr class="form-field">
		<th scope="row" valign="top"><label for="family_font_order"><?php _e( 'Font Order', 'typolog' ); ?></label></th>
			<td>
				<ul class="family-font-order">
					<?php foreach ($font_order as $font_id) : ?>
						<li class="family-font-order-item">
							<input class="main-font-select" type="radio" name="family_main_font" value="<?=$font_id ?>" <?php checked($font_id, $main_font); ?>>
							<span class="family-font-order-item-title"><?=get_the_title($font_id) ?></span>
							<input type="hidden" name="family_font_order[]" value="<?=$font_id ?>">
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="description"><?php _e( 'Set a custom display order for fonts in the family.', 'typolog' ); ?></p>
				<p><a href="#" class="button reset-font-order"><?php _e( 'Reset Font Order', 'typolog' ); ?></a></p>
			</td>
		</tr>
	<?php
	
	}
	
	function save_family_font_order( $term_id ) {
		
		if ( isset( $_POST[ 'family_font_order' ] ) ) {
			
			if ( !$_POST[ 'family_font_order' ] ) {

				delete_term_meta( $term_id, '_font_order' );
				
			} else {
				
				update_term_meta( $term_id, '_font_order', $_POST['family_font_order'] ); 

			}
			
		}
		
		if ( isset( $_POST[ 'family_main_font' ] ) ) {
			
			if ( !$_POST[ 'family_main_font' ] ) {
				
				delete_term_meta( $term_id, '_main_font' );

			} else {
				
				update_term_meta( $term_id, '_main_font', $_POST['family_main_font'] );

			}
			
			
		}
		
	}  
	
}
