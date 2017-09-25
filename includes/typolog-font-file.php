<?php

/* 
	
	Typolog
	
	F O N T   F I L E   C L A S S
	
	Create and manage font files via WP
	
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Font_File_Query {
	
	static function get_all() {
		
		return get_posts( [
			
			'post_type' => 'typolog_file',
			
			'posts_per_page' => -1
			
		] );
		
	}
	
}

class Typolog_Font_File {
	
	private $file;
	
	private $fonts_url;
	
	private $fonts_path;
	
	private $products_url;
	
	private $products_path;
	
	private $licenses;

	function __construct( $file = null ) {
		
		$upload_dir = wp_upload_dir();
		
		$this->fonts_path = wp_normalize_path( $upload_dir['basedir'] . '/' . TypologOptions()->get( 'fonts_dir' ) . '/' ); // normalize for windows server
		
		$this->fonts_url = $upload_dir['baseurl'] . '/' . TypologOptions()->get( 'fonts_dir' ) . '/';

		$this->products_path = wp_normalize_path( $upload_dir['basedir'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/' ); // normalize for windows server
		
		$this->products_url = $upload_dir['baseurl'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';

		if ( $file ) {
			
			$this->load( $file );
			
		}
		
	}
	
	function load( $file_id ) {
		
		if ( is_object( $file_id ) ) {
			
			$this->file = $file_id;
			
			return $file_id;
			
		}
		
		$this->file = get_post( $file_id );
		
		return $this->file;
		
	}
	
	function load_by_filename( $filename ) {
		
		$this->file = get_page_by_title( $filename, 'OBJECT', 'typolog_file' );
		
		return $this->file;
		
	}

	function unload() {
		
		unset( $this->file );
		
	}
	
	function is_loaded() {
		
		return is_object( $this->file );
		
	}
	
	function get( $param ) {
		
		if ( isset( $this->file ) ) {

			return $this->file->$param;	
			
		}
		
		return false;
		
	}
		
	function get_filename() {
		
		if ( isset( $this->file ) ) {

			return get_the_title( $this->file->ID );
			
		}
		
		return false;

		
	}
	
	function get_meta( $key ) {
		
		if ( isset( $this->file ) ) {

			return get_post_meta( $this->file->ID, $key, true );
			
		}
		
		return false;
		
	}

	function set_meta( $key, $value ) {
		
		if ( isset( $this->file ) ) {

			return update_post_meta( $this->file->ID, $key, $value );
			
		}
		
		return false;
		
	}
	
	function get_file_path( ) {
		
		return $this->get_meta( '_file_path' );
		
	}

	function get_file_url( ) {
		
		return $this->get_meta( '_file_url' );
		
	}
	
	function generate_fontface( $web_family_name = 'FontPreview' ) {
		
		if ( !isset( $this->file ) ) return false;
		
		$url = $this->get_file_url();

		$extension = pathinfo( $this->get_file_path(), PATHINFO_EXTENSION );
				
		$webfont_code = "@font-face { font-family: '$web_family_name'; ";
		
		if ( 'eot' == $extension ) {
			
			$webfont_code .= "src: url('$url'); ";
			
		}
		
		$webfont_srcs = array();
		
		if ( 'eot' == $extension ) {
			
			$webfont_srcs[] = "url('$url#iefix') format('embedded-opentype')";
			
		}
		
		if ( 'woff2' == $extension ) {
			
			$webfont_srcs[] = "url('$url') format('woff2')";
			
		}
		
		if ( 'woff' == $extension ) {
			
			$webfont_srcs[] = "url('$url') format('woff')";
			
		}
		
		if ( 'ttf' == $extension ) {
			
			$webfont_srcs[] = "url('$url') format('ttf')";
			
		}

		if ( 'otf' == $extension ) {
			
			$webfont_srcs[] = "url('$url') format('opentype')";
			
		}
		
		if ( 'svg' == $extension ) {
			
			$webfont_srcs[] = "url('$url') format('svg')";
			
		}
		
		if ( count( $webfont_srcs ) ) {
			
			$webfont_code .= "src: " . implode( ',', $webfont_srcs ) . "; }";
			
		} else {
			
			$webfont_code = "";
			
		}
		
		return $webfont_code;
		
	}
	
	// Adds new font WP post after upload, or updates existing file if a file already exists under the same (original) name
		
	function add( $filename, $real_filename ) {
		
		$file_path = $this->fonts_path . $real_filename;
		
		$file_url = $this->fonts_url . $real_filename;
		
		if ( isset( $this->file ) ) {

			$this->set_meta( '_file_path', $file_path );
			
			$this->set_meta( '_file_url', $file_url );
			
			return $file->ID;			
			
		} elseif ( $this->load_by_filename( $filename ) ) { // if it already exists, just replace the file
			
			$this->set_meta( '_file_path', $file_path );
			
			$this->set_meta( '_file_url', $file_url );
			
			return $file->ID;
			
		}
		
		$file_id = wp_insert_post( [
			
				'post_type' => 'typolog_file',
				
				'post_status' => 'publish',
				
				'post_title' => $filename,
				
				'post_name' => strtolower( $filename ),
				
				'meta_input' => [
					
					'_file_path' => $file_path,
					
					'_file_url' => $file_url
					
				]
				
			]
			
		);
		
		if ( $file_id ) {
			
			$this->load( $file_id );
			
		}
		
		return $file_id;
		
	}

	/* Handles complete upload - accepts original file array from upload form and adds a new file WP post */
	
	function upload( $file_array, $path = '' ) {
		
		if ( isset( $this->file ) ) {
			
			$current_filename = $this->get_file_path();

			if ( file_exists( $current_filename ) ) {
				
				unlink( $current_filename );
				
			}
			
		}

		if ( !$path ) {
			
			$path = $this->fonts_path; // Default location for fonts is the Typolog fonts directory
			
		}
		
		$file_secret = generate_file_secret();
		
		$filename_array = explode( '.', $file_array['name'] );
		
		// Build filename with secret between file name and extension
		
		array_splice( $filename_array, -1, 0, $file_secret ); 
		
		$filename = implode( '.', $filename_array );
		
		if ( move_uploaded_file( $file_array['tmp_name'], $path . $filename ) ) { // Try to move uploaded file to final path
			
			return $this->add( $file_array['name'], $filename ); // Add new file with original name but refer to it with new added secret
			
		}
		
		return false;
		
	}
	
	function delete() {
		
		if ( isset( $this->file ) ) {
			
			$filename = $this->get_file_path();
			
			// Delete actual file (if it exists)
			
			if ( file_exists( $filename ) ) {
				
				unlink( $filename );
				
			}
			
			// Delete post (and return true if successful
			
			if ( wp_delete_post( $this->file->ID, true ) ) {
				
				$this->unload();
				
				return true;
				
			}
			
		}
		
		return false;
		
	}
	
	function get_licenses() {
		
		if ( isset( $this->file ) ) {

			$licenses = get_the_terms( $this->file->ID, 'typolog_license' );

			if ( is_array( $licenses ) ) {
				
				return $licenses;
				
			}
			
		}
		
		return false;
		
	}
	
	function get_the_license() {
		
		$licenses = $this->get_licenses();
		
		if ( is_array( $licenses ) ) {
			
			return $licenses[0];
			
		}
		
		return false;
	}

	function set_licenses( $license_ids = array() ) {
		
		if ( $this->file ) {
			
			return wp_set_object_terms( $this->file->ID, $license_ids, 'typolog_license' );
			
		}
		
	}
	
	function set_license( $license_id = null ) {
		
		if ( !$this->file ) {
			
			return false;
			
		}
		
		if ( !$license_id ) {
			
			$filename = get_the_title( $this->file->ID );
			
			$license = Typolog_License_Query::detect_by_filename( $filename );
			
			if ( is_object( $license ) ) {
				
				return $this->set_licenses( [ $license->term_id ] );
				
			}
			
		}
		
		return $this->set_licenses( [ $license_id ] );

	}

	function get_licenses_table() {
		
		$all_licenses = Typolog_License_Query::get_all_slugs();
		
		$table_var = [
		
			'id' => $this->get( 'ID' ),
				
			'title' => get_the_title( $this->get( 'ID' ) ),
				
			'licenses' => []
			
		];
		
		$licenses = $this->get_licenses();
			
		if ( is_array( $licenses ) ) {
			
			$licenses = array_get_column( $licenses, 'slug' );
			
		} else {
			
			return $table_var;
			
		}
			
		foreach ( $all_licenses as $license ) {
			
			if ( false !== array_search( $license, $licenses ) ) {
				
				$table_var[ 'licenses' ][ $license ] = 1; // file is associated with license
				
			} else {
				
				$table_var[ 'licenses' ][ $license ] = 0; // file isn't associated with license
				
			}
			
		}
		
		typolog_log( 'font_file_table_var', $table_var );
		
		return $table_var;
						
	}


	
/*
	function get_font_package($files, $license_name) {
		return $this->licenses->get_font_package($files, $license_name);
	}
	
	function get_font_packages($files) {
		return $this->licenses->get_font_packages($files);
	}
	
	function update_font_packages($packages) {
		return $this->licenses->update_font_packages($packages);
	}
	
	function reset_font_packages($font_id) {
		return $this->update_font_packages($font_id, $this->licenses->reset_font_packages($this->get_the_files($font_id)));
	}

	function get_packages_table($font_id) {
		if ($font_id) {
			$packages_table = $this->licenses->get_packages_table_var($this->get_the_files($font_id));
			ob_start();
			include plugin_dir_path( __FILE__ ) . '../admin/partials/typolog-admin-font-files-table.php';
			return ob_get_clean();
		}
		return '';
	}
	
	function delete_all_files() {
		$files = get_posts([ 'post_type' => 'typolog_file', 'posts_per_page' => -1 ]);
		foreach ($files as $file) {
			$this->delete_font_file($file->ID);
		}
		return true;
	}
	
	function set_license($file_id) {
		return $this->licenses->set_license($file_id);
	}

	function set_license_attachments($license_id, $attachments) {
		return $this->licenses->set_license_attachments($license_id, $attachments);
	}

	function get_license_attachments($license_id) {
		return $this->licenses->get_license_attachments($license_id);
	}
*/

}



