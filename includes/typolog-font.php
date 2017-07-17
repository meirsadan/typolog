<?php

/* 
	
	Typolog
	
	F O N T   C L A S S
	
	Handles font WP data and metadata
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Font_Query {
	
	static function get_all() {
		
		return get_posts( [
			
			'post_type' => 'typolog_font',
			
			'posts_per_page' => -1
			
		] );

	}
	
	static function get_by_meta( $key, $value = null ) {
		
		if ( is_array( $key ) ) {
			
			return get_posts( [
				
				'post_type' => 'typolog_font', 
				
				'meta_query' => $key
				
			] );
			
		}
		
		return get_posts( [
			
			'post_type' => 'typolog_font', 
			
			'meta_query' => [ 
				
				[
				
					'key' => $key, 
					
					'value' => $value
				
				]
			
			] 
			
		] );
		
	}
	
	static function font_exists( $family_name, $style_name ) {

		$existing_fonts = self::get_by_meta( [
			
			[ "key" => "_family_name", "value" => $family_name ],
			
			[ "key" => "_style_name", "value" => $style_name ]
			
		] );
		
		return ( count( $existing_fonts ) ) ? $existing_fonts[ 0 ]->ID : false;
	}
	
	static function get_meta( $font_id, $key = null ) {
		
		if ( !$key ) {
			
			return get_post_meta( $font_id );
			
		}
		
		return get_post_meta( $font_id, $key, true );
		
	}

	static function is_font( $font_id ) {
		
		if ( "typolog_font" == get_post_type( $font_id ) ) { // make sure this is a font
			
			return true;
			
		}
		
		return false;
		
	}
	
	static function get_by_family( $family_id ) {
		
		$family = new Typolog_Family( $family_id );
		
		return $family->get_fonts();
		
	}
	
	static function get_price( $font_id, $license_name = "" ) {
		
		$font = new Typolog_Font( $font_id );
		
		return $font->get_price( $license_name );
		
	}

	static function get_family( $font_id ) {
		
		$families = get_the_terms( $font_id, 'typolog_family' );
		
		if ( is_array( $families ) ) {
			
			return $families[0];
			
		}
		
		return false;
		
	}

	static function get_size_adjust( $font_id ) {
		
		$size_adjust = self::get_meta( $font_id, '_size_adjust' );

		return ($size_adjust) ? $size_adjust : '100';
		
	}
	
	static function get_licenses_table( $font_id ) {
		
		$font = new Typolog_Font( $font_id );
		
		typolog_log( 'get_licenses_table_font', $font );
		
		return $font->get_licenses_table();
		
	}
		
	static function get_products( $font_id ) {
		
		$font = new Typolog_Font( $font_id );
		
		$family = new Typolog_Family( $font->get_family() );
		
		return array(
			
			'single' => $font->get_meta( '_product_id' ),
			
			'family' => $family->get_meta( '_product_id' )
			
		);
		
	}
	

}

class Typolog_Font_Meta {
	
	private $meta_fields;

	function __construct( $data = null ) {

		$this->meta_fields = [
			
			'family_name' => [
				
				'id' => 'family_name',
				
				'json_id' => 'familyName',
				
				'name' => __( 'Family Name', 'typolog' ),
				
				'default' => 'Fontef'
				
			],
			
			'style_name' => [
				
				'id' => 'style_name',
				
				'json_id' => 'styleName',
				
				'name' => __('Style Name', 'typolog'),
				
				'default' => 'Regular'
				
			],
			
			'display_family_name' => [
				
				'id' => 'display_family_name',
				
				'json_id' => 'displayFamilyName',
				
				'name' => __('Family Name for Display', 'typolog'),
				
				'default' => __( 'Fontef', 'typolog' )
				
			],
			
			'display_style_name' => [
				
				'id' => 'display_style_name',
				
				'json_id' => 'displayStyleName',
				
				'name' => __('Style Name for Display', 'typolog'),
				
				'default' => __( 'Regular', 'typolog' )
				
			],
			
			'web_family_name' => [
				
				'id' => 'web_family_name',
				
				'json_id' => 'webFamilyName',
				
				'name' => __('Family Name for Webfont Use', 'typolog'),
				
				'default' => 'FontefRegular'
				
			],
			
			'font_weight' => [
				
				'id' => 'font_weight',
				
				'json_id' => 'fontWeight',
				
				'name' => __('Font Weight (100–900)', 'typolog'),
				
				'default' => 400
				
			],
			
			'font_style' => [
				
				'id' => 'font_style',
				
				'json_id' => 'fontStyle',
				
				'name' => __('Font Style (normal/italic/oblique)', 'typolog'),
				
				'default' => 'normal'
				
			]
		
		];
		
		if ( $data ) {
			
			$this->setup_meta( $data );
			
		}
		
	}

	function setup_meta( $data ) {
		
		$res = [];
		
		foreach ( $this->meta_fields as &$meta_field ) {
			
			$val = $data[ $meta_field[ 'json_id' ] ];
			
			if ( $val ) {
				
				$res[ '_' . $meta_field['id'] ] = $val;
				
				$meta_field[ 'value' ] = $val;
				
			} else {
				
				$res[ '_' . $meta_field['id'] ] = $meta_field['default'];
				
				$meta_field[ 'value' ] = $meta_field['default'];
				
			}
			
		}
		
		return $res;
		
	}
	
	function set_field( $key, $value ) {
		
		$this->meta_fields[ $key ] = [
			
			'id' => $key,
			
			'value' => $value
			
		];
		
	}
	
	function unset_field( $key ) {
		
		unset( $this->meta_fields[ $key ] );
		
	}
	
	function load_fields( $font ) {
		
		foreach ( $this->meta_fields as &$meta_field ) {
			
			$val = $font->get_meta( '_' . $meta_field['id'] );
			
			if ( $val ) {
				
				$meta_field[ 'value' ] = $val;
				
			}
			
		}
		
		return $this->meta_fields;
		
	}

	function set_fields( $font ) {
		
		foreach ( $meta_field_values as $meta_field_key => $meta_field_value ) {
			
			$font->set_meta( '_' . $meta_field_key, $meta_field_value );
			
		}
		
		return true;
	}
	
	function get_fields() {
		
		$res = [];
		
		foreach ( $this->meta_fields as &$meta_field ) {
			
			$res[ '_' . $meta_field['id'] ] = $meta_field[ 'value' ];
			
		}
		
		return $res;
		
	}

}

class Typolog_Font {
	
	private $font;
	
	function __construct( $font = null ) {
		
		if ( $font ) {
			
			$this->load( $font );
			
		}
		
		
	}
	
	function load( $font_id ) {
		
		if ( is_object( $font_id ) ) {
			
			$this->font = $font_id;
			
			return $font_id;
			
		}
		
		$this->font = get_post( $font_id );
		
		return $this->font;
		
	}
	
	function unload() {
		
		unset( $this->font );
		
	}
	
	function is_loaded() {
		
		return is_object( $this->font );
		
	}
	
	function get( $param ) {
		
		if ( isset( $this->font ) ) {

			return $this->font->$param;	
			
		}
		
		return false;
		
	}
	
	function get_meta( $field_name = null ) {
		
		if ( isset( $this->font ) ) {
			
			if ( !$field_name ) {
				
				return get_post_meta( $this->font->ID );
			}
			
			return get_post_meta( $this->font->ID, $field_name, true );
		
		}
		
		return false;
		
	}
	
	function set_meta( $field_name, $field_value = null ) {
		
		if ( isset( $this->font ) ) {
			
			if ( is_array( $field_name ) ) {
				
				foreach ( $field_name as $key => $value ) {
					
					$this->set_meta( $key, $value );
					
				}
				
				return true;
				
			}
			
			return update_post_meta( $this->font->ID, $field_name, $field_value );
		
		}
		
		return false;
		
	}

	function unset_meta($field) {
		
		if ( isset( $this->font ) ) {
				
			return delete_post_meta($this->font->ID, $field);
		
		}
		
		return false;
		
	}

	function get_attachments() {
		
		return $this->get_meta( '_attachments' );
		
	}

	function get_size_adjust( $percent = false ) {
		
		$size_adjust = $this->get_meta( '_size_adjust' );
		
		if ( $percent ) {

			return ( $size_adjust ) ? $size_adjust . "%" : '100%';
			
		}

		return ( $size_adjust ) ? $size_adjust : '100';
		
	}
	
	function update( $font_data, $try_to_create = true ) {
		
		if ( !isset( $this->font ) ) return $try_to_create ? $this->create( $font_data ) : false;

		$font_meta = new Typolog_Font_Meta( $font_data );
		
		$font_meta->set_field( 'font_files', $font_data['files'] );
		
		$meta_input = $font_meta->get_fields();

		$this->set_meta( $meta_input );
		
		return wp_update_post( [
			
			'ID' => $this->font->ID,
			
			'post_title' => $font_data['displayFamilyName'] . ' ' . $font_data['displayStyleName'],
			
			'post_name' => strtolower( $font_data['familyName'] . $font_data['styleName'] )
			
		], true );
		
	}

	function create( $font_data ) {
		
		$font_meta = new Typolog_Font_Meta( $font_data );
		
		$font_meta->set_field( 'font_files', $font_data['files'] );
		
		$meta_input = $font_meta->get_fields();
		
		$font_id = wp_insert_post( [
			
			'post_type' => 'typolog_font',
			
			'post_status' => 'publish',
			
			'post_title' => $font_data['displayFamilyName'] . ' ' . $font_data['displayStyleName'],
			
			'post_name' => strtolower( $font_data['familyName'] . $font_data['styleName'] ),
			
			'meta_input' => $meta_input
			
		] );
		
		if ( ( $font_id ) && ( !is_wp_error( $font_id ) ) ) {
			
			$this->load( $font_id );
			
		}
		
		return $font_id;
		
	}
	
	function delete() {
		
		if ( isset( $this->font ) ) {
			
			if ( false !== wp_delete_post( $this->font->ID, true ) ) {
				
				typolog_log( 'deleted_font', $this->font->ID );
				
				$this->unload();
				
				return true;
				
			}
		
		}
		
		return false;
		
	}
	
	
	function add_file( $file_id ) {
		
		$font_files = $this->get_meta( '_font_files' );
		
		if ( is_array( $font_files ) ) {
			
			if ( false === array_search( $file_id, $font_files ) ) // make sure the file isn't added twice
			
				array_push( $font_files, $file_id );
			
		} else {
			
			$font_files = [ $file_id ];
			
		}
		
		return $this->set_meta( '_font_files', $font_files );
		
	}

	function remove_file( $file_id ) {
		
		$font_files = $this->get_meta( '_font_files' );
		
		array_splice( $font_files, array_search( $file_id, $font_files ), 1 );
		
		return $this->set_meta( '_font_files', $font_files);
		
	}
	

	function set_family( $family_id ) {
		
		if ( isset( $this->font ) ) {

			return wp_set_object_terms( $this->font->ID, (int) $family_id, 'typolog_family' );
			
		}
		
		return false;
		
	}

	function add_family( $displayFamilyName, $familyName, $commercial = 1 ) {
		
		$existing_families = Typolog_Family_Query::get_by_meta( '_family_name', $familyName );
		
		if ( count( $existing_families ) ) {

			typolog_log( 'family_already_exists_result', array( "family_name" => $familyName, "existing_results" => $existing_families ) );
			
			return $this->set_family( $existing_families[0]->term_id );
			
		}
		
		$family = new Typolog_Family();
		
		$family_id = $family->add( $displayFamilyName, $familyName, $commercial );
		
		return $this->set_family( $family_id );
		
	}
	
	function get_family() {
		
		if ( !isset( $this->font ) ) return false;
		
		$families = get_the_terms( $this->font->ID, 'typolog_family' );
		
		if ( is_array( $families ) ) {
			
			return $families[0];
			
		}
		
		return false;
		
	}

	function get_family_id() {
		
		$family = $this->get_family();
		
		if ( $family ) {
			
			return $family->term_id;

		}
		
		return false;
	}

	function set_collection( $collection_id ) {
		
		if ( isset( $this->font ) ) {

			return wp_set_object_terms( $this->font->ID, (int) $collection_id, 'typolog_collection' );
			
		}
		
		return false;
		
	}

	function get_collections() {
		
		if ( !isset( $this->font ) ) return false;
		
		$collections = get_the_terms( $this->font->ID, 'typolog_collection' );
		
		if ( is_array( $collections ) ) {
			
			return $collections;
			
		}
		
		return false;
		
	}

	function get_collection() {
		
		$collections = $this->get_collections();
				
		if ( is_array( $collections ) ) {
			
			return $collections[0];
			
		}
		
		return false;
		
	}

	function generate_package_name() {
		
		if ( isset( $this->font ) ) {

			return str_replace( ' ', '', $this->get_meta( '_family_name' ) . $this->get_meta( '_style_name' ) );
			
		}
		
		return false;
		
	}

	function is_commercial() {
		
		if ( isset( $this->font ) ) {
			
			$family = $this->get_family();
			
			if ( $family ) {
				
				$family_obj = new Typolog_Family( $family );
				
				return $family_obj->is_commercial();
			
			}
			
		}
		
		return false;
		
	}

	function get_price( $license_name = "" ) {
		
		$price = $this->get_meta( '_price_' . strtolower( $license_name ) ); // Check font meta
		
		if ( (!$price) && ($price !== 0) ) {
			
			$price = $this->get_meta( '_price' ); // Check font meta for general price
			
			if ( (!$price) && ($price !== 0) ) {
				
				if ( $license_name ) {

					$license = Typolog_License_Query::get_by_slug( $license_name );
					
					if ( is_object( $license ) ) {
						
						$price = Typolog_License_Query::get_meta( $license->term_id, '_base_price' ); // Get base price for entire license
						
						if ((!$price) && ($price !== 0)) {
							
							$price = TypologOptions()->get( 'base_price' ); // Get general base price
							
						}
		
					}
					
				}

				$price = TypologOptions()->get( 'base_price' ); // Get general base price
				
			}
		}
		
		return $price;
		
	}
	
	function get_licenses_table() {
		
		$table_var = [];
		
		$files = $this->get_meta( '_font_files' );
		
		typolog_log( 'font_table_var_font', $this->font );

		if ( !$files ) $files = [];
		
		$file_obj = new Typolog_Font_File();
		
		foreach ( $files as $file ) {
			
			$file_obj->load( $file );
			
			$table_var[] = $file_obj->get_licenses_table();
			
		}

		typolog_log( 'font_table_var', $table_var );
		
		return $table_var;
		
	}

}



