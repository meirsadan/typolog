<?php

/* 
	
	Typolog
	
	L I C E N S E   C L A S S
	
	Handles font licenses WP data and metadata
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_License_Query {
	
	static function get_all() {
		
		return get_terms( 'typolog_license', [
			
			'hide_empty' => false,
			'meta_key' => '_order',
			'orderby' => 'meta_value'
			
		] );
	}
	
	static function get_licenses_order() {
		
		$order_array = [];
		
		$licenses = self::get_all();
		
		foreach ( $licenses as $license ) {
			
			$order_array[ $license->slug ] = get_term_meta( $license->term_id, '_order', true );
			
		}
		
		return $order_array;
		
	}
	
	static function get_all_slugs() {

		$all_licenses = self::get_all();
		
		return array_get_column( $all_licenses, 'slug' );
		
	}
	
	static function get_by_slug( $license_slug ) {
		
		return get_term_by( 'slug', $license_slug, 'typolog_license' );
		
	}

	static function get_the_license_by_file( $file_id ) {
		
		$licenses = self::get_licenses_by_file( $file_id );
		
		if ( is_array( $licenses ) ) {
			
			return $licenses[0];
			
		}
		
		return false;
		
	}
	
	static function get_licenses_by_file( $file_id ) {
		
		$licenses = get_the_terms( $file_id, 'typolog_license' );
		
		if ( is_array( $licenses ) ) {
			
			return $licenses;
			
		}
		
		return false;
	}
	
	static function get_meta( $license_id, $key ) {
		
		return get_term_meta( $license_id, $key, true );
		
	}
		
	static function detect_by_filename( $filename ) {
		
		$licenses = self::get_all();
		
		$res = [];
		
		foreach ( $licenses as $license ) {
			
			$exts = self::get_meta( $license->term_id, '_extensions' );
			
			if ( $exts ) {
				
				$exts = explode( '|', $exts );
				
				foreach ( $exts as $ext ) {
					
					if ( strripos( $filename, $ext ) == strlen( $filename ) - strlen( $ext ) ) {
						
						array_push( $res, $license->term_id );
					}
					
				}
				
			}
			
		}
		
		return $res;
		
	}
	
}

class Typolog_Packages {
	
	private $packages;
	
	function __construct( ) {
		
		
	}
	
	function load_by_file( $file_ids ) {
		
		$this->packages = [ ];
		
		if ( is_numeric( $file_ids ) ) {
			
			$file_ids = [ $file_ids ];
			
		}
		
		foreach ( $file_ids as $file_id ) {
			
			$licenses = Typolog_License_Query::get_licenses_by_file( $file_id );
			
			if ( is_array( $licenses ) ) {
				
				foreach ($licenses as $license) {
					
					$this->packages[ $license->slug ][] = $file_id;
					
				}
				
			}
			
		}
		
	}
	
}

class Typolog_License {
	
	private $license;
	
	function __construct( $license = null ) {
		
		if ( $license ) {
			
			$this->load( $license );
			
		}
		
	}

	function load( $license_id ) {
		
		if ( is_object( $license_id ) ) {
			
			$this->license = $license_id;
			
			return $license_id;
			
		}
		
		$this->license = get_term( $license_id );
		
		return $this->license;
		
	}
	
	function load_by_slug( $license_slug ) {
		
		if ( $license = Typolog_License_Query::get_by_slug( $license_slug ) ) {
			
			return $this->load( $license );
			
		}
		
		return false;
		
	}
	
	function unload() {
		
		unset( $this->license );
		
	}
	
	function is_loaded() {
		
		return is_object( $this->license );
		
	}
	
	function get( $param ) {
		
		if ( isset( $this->license ) ) {

			return $this->license->$param;	
			
		}
		
		return false;
		
	}
	
	function get_meta( $field_name ) {
		
		if ( isset( $this->license ) ) {
			
			return get_term_meta( $this->license->term_id, $field_name, true );
		
		}
		
		return false;
		
	}
	
	function set_meta( $field_name, $field_value ) {
		
		if ( isset( $this->license ) ) {
			
			if ( !isset( $field_value ) ) {
				
				return $this->unset_meta( $field_name );
				
			}
			
			return update_term_meta( $this->license->term_id, $field_name, $field_value );
		
		}
		
		return false;
		
	}

	function unset_meta($field) {
		
		if ( isset( $this->license ) ) {
				
			return delete_term_meta( $this->license->term_id, $field );
		
		}
		
		return false;
		
	}

	function get_attachments() {
		
		return $this->get_meta( '_attachments' );
		
	}
	
	function get_font_package($file_ids, $license_name) {
		
		$package = array();
		
		foreach ($file_ids as $file_id) {
			
			if ($licenses = $this->get_file_licenses($file_id)) {
				foreach ($licenses as $license) {
					if (strtolower($license->slug) == strtolower($license_name))
						$package[] = $file_id;
				}
			}
		}
		return $package;
	}

	
	function detect_by_filename( $filename ) {
		
		$licenses = $this->get_all_licenses();
		foreach ($licenses as $license) {
			if ($_license_exts = get_term_meta($license->term_id, '_extensions', true)) {
				$license_exts = explode('|', $_license_exts);
				foreach ($license_exts as $ext) {
					if (strripos($filename, $ext) == strlen($filename) - strlen($ext)) {
						return $license;
					}
				}
			}
		}
		return false;
		
	}
	
	function get_font_packages($file_ids) {
		$packages = array();
		foreach ($file_ids as $file_id) {
			if ($licenses = $this->get_file_licenses($file_id)) {
				foreach ($licenses as $license) {
					$packages[$license->slug][] = $file_id;
				}
			}
		}
		return $packages;
	}
	
	function get_license_name($license_id) {
		if ($license = $this->get_term($license_id, 'typolog_license')) {
			return $license->slug;
		}
		return false;
	}

	function get_license_display_name($license_id) {
		if ($license = $this->get_term($license_id, 'typolog_license')) {
			return $license->name;
		}
		return false;
	}

	function reset_font_packages($file_ids) {
		$packages = array();
		foreach ($file_ids as $file_id) {
			$licenses = $this->determine_license(get_the_title($file_id));
			if ( count( $licenses ) ) {
				$license_ids = [];
				for ( $i = 0; $i < count( $licenses ); $i++ ) {
					array_push( $license_ids, $licenses[ $i ]->term_id );
					$packages[ $licenses[ $i ]->slug ][] = $file_id;
				}
				wp_set_object_terms($file_id, $license_ids, 'typolog_license');
			}
		}
		return $packages;
	}
	
	function update_font_packages($files) {
		foreach (array_keys($files) as $file_id) {
			if (is_wp_error($res = $this->set_licenses($file_id, $files[$file_id]))) {
				return $res;
			}
		}
		return true;
	}

	function set_licenses($file_id, $license_ids = array()) {
		return wp_set_object_terms($file_id, $license_ids, 'typolog_license');
	}
	
	function set_license($file_id, $license_id = null) {
		if ( !$license_id ) {
			$licenses = $this->determine_license( get_the_title( $file_id ) );
			if ( count( $licenses ) ) {
				$license_ids = [];
				for ( $i = 0; $i < count( $licenses ); $i++ ) {
					array_push( $license_ids, $licenses[ $i ]->term_id );
				}
			} else {
				return false;
			}
		} else {
			$license_ids = [ $license_id ];
		}
		return $this->set_licenses( $file_id, $license_ids );
	}

	function determine_license($filename) {
		$licenses = $this->get_all_licenses();
		$res = [];
		foreach ($licenses as $license) {
			if ($_license_exts = get_term_meta($license->term_id, '_extensions', true)) {
				$license_exts = explode('|', $_license_exts);
				foreach ($license_exts as $ext) {
					if (strripos($filename, $ext) == strlen($filename) - strlen($ext)) {
						array_push( $res, $license );
					}
				}
			}
		}
		return $res;
	}

	function unset_license_meta($license_id, $field) {
		return delete_term_meta($license_id, $field);
	}
	
	function set_license_meta($license_id, $field, $value = '') {
		if (!$value) {
			return $this->unset_license_meta($license_id, $field);
		}
		if (!add_term_meta($license_id, $field, $value, true)) {
			return update_term_meta($license_id, $field, $value);
		}
		return true;
	}

	function set_license_attachments($license_id, $attachments) {
		return $this->set_license_meta($license_id, '_typolog_attachments', $attachments);
	}

	function set_license_base_price($license_id, $base_price) {
		return $this->set_license_meta($license_id, '_base_price', $base_price);
	}

	function get_license_meta($license_id, $field) {
		return get_term_meta($license_id, $field, true);
	}

	function get_license_attachments($license_id) {
		return $this->get_license_meta($license_id, '_typolog_attachments');
	}

	function get_license_base_price($license_id) {
		return $this->get_license_meta($license_id, '_base_price');
	}

}

function get_license_id($license_name) {
	$licenses = get_terms('typolog_license', array( 'slug' => $license_name ));
	if (!is_array($licenses)) {
		return false;
	}
	return $licenses[0]->term_id;
}

function get_license_base_price($license_id) {
	if (!is_numeric($license_id)) {
		$license_id = get_license_id($license_id);
	}
	return get_term_meta($license_id, '_base_price', true);
}
