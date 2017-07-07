<?php

/**
 * Fired during plugin activation
 *
 * @link       http://meirsadan.com/
 * @since      1.0.0
 *
 * @package    Typolog
 * @subpackage Typolog/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Typolog
 * @subpackage Typolog/includes
 * @author     Meir Sadan <meir@sadan.com>
 */
class Typolog_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		// scan uploads dir and add fonts and font_products directories if they don't already exist
		
		$wp_upload_dir = wp_upload_dir();
		
		$wp_upload_dir_contents = scandir( $wp_upload_dir['basedir'] );
		
		if ( FALSE === array_search( 'fonts', $wp_upload_dir_contents ) ) {
			
			mkdir( $wp_upload_dir['basedir'] . '/fonts' );
			
		}
		
		chmod( $wp_upload_dir['basedir'] . '/fonts', 0777 ); // enable uploading to fonts directory
		
		if ( FALSE === array_search( 'font_bundles', $wp_upload_dir_contents ) ) {
			
			mkdir( $wp_upload_dir['basedir'] . '/font_products' );
			
		}
		
		chmod( $wp_upload_dir['basedir'] . '/font_products', 0777 ); // enable uploading to font_products directory
		
		// create empty index files to hide directories contents
		
		file_put_contents( $wp_upload_dir['basedir'] . '/fonts/index.html', '' );
		
		file_put_contents( $wp_upload_dir['basedir'] . '/font_products/index.html', '' );
		
		// add rewrite rules

		add_rewrite_rule( 'fontface$', 'index.php?fontface_css=1', 'top' );
		
		add_rewrite_rule( 'fontface\/\?(.*)$', 'index.php?fontface_css=1&$1', 'top' );
		
		add_rewrite_rule( 'fetch_families$', 'index.php?fetch_families=1', 'top' );
		
		add_rewrite_rule( 'fetch_families\/\?(.*)$', 'index.php?fetch_families=1&$1', 'top' );
		
		add_rewrite_rule( 'typolog_wc_connect\/\?(.*)$', 'index.php?typolog_wc_connect=1&$1', 'top' );
		
		flush_rewrite_rules();
		
	}

}
