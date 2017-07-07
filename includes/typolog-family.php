<?php

/* 
	
	Typolog
	
	F A M I L Y   C L A S S
	
	Handles family WP data and metadata
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Family_Query {
	
	static function get_all() {
		
		return get_terms( 'typolog_family' , [ "hide_empty" => false ] );
		
	}
	
	static function get_by_meta( $key, $value ) {
		
		return get_terms( 'typolog_family', [ 
			
			'hide_empty' => false,
			
			'meta_query' => [ 
				
				[
				
					'key' => $key,
					 
					'value' => $value,
					
					'compare' => '='
				
				]
				
			] 
		
		] );
		
	}

	static function get_by_collection( $collection ) {
		
		$families = array();
		
		if ($collection) {
			
			if ( is_numeric( $collection ) ) {
				
				$tax_query = [
					
					[
						
						'taxonomy' => 'typolog_collection',
						
						'field' => 'term_id',
						
						'terms' => $collection

					]
					
				];
				
			} else {
				
				$tax_query = [
					
					[
						
						'taxonomy' => 'typolog_collection',
						
						'field' => 'name',
						
						'terms' => $collection
						
					]
					
				];
				
			}
			
			$fonts = get_posts( [
				
				'post_type' => 'typolog_font',
				
				'posts_per_page' => -1,
				
				'tax_query' => $tax_query,
				
				'order' => 'ASC',
				
				'orderby' => 'post_title'
				
			] );
			
			foreach ( $fonts as $font ) {
				
				$family = Typolog_Font_Query::get_family( $font->ID );

				if ( ( $family ) && ( false === array_search( $family, $families ) ) ) {
					
					$families[] = $family;
					
				}
				
			}
			
			$families_ordered = array();
			$families_unordered = array();
			
			foreach ( $families as $family ) {
				if ( false !== $order_num = get_term_meta( $family->term_id, '_order_num', true ) ) {
					if ( isset( $families_ordered[ $order_num ] ) ) {
						array_push( $families_unordered, $family );
					} else {
						$families_ordered[ $order_num ] = $family;
					}
				} else {
					array_push( $families_unordered, $family );
				}
			}
			
			ksort( $families_ordered );
			
// 			typolog_log( 'order_families', array( "ordered" => $families_ordered, "unordered" => $families_unordered ) );
			
			$families = array_merge( $families_ordered, $families_unordered );
			
		}
		
		return $families;
		
	}

	
	static function get_meta( $family_id, $key = null ) {
		
		if ( !$key ) {
			
			return get_term_meta( $family_id );
			
		}
		
		return get_term_meta( $family_id, $key, true );
		
	}

	static function is_commercial( $family_id ) {
		
		return get_term_meta( $family_id, '_commercial', true );
		
	}
	
	static function get_main_font( $family_id ) {
		
		$family = new Typolog_Family( $family_id );
		
		return $family->get_main_font();
		
	}
	
	static function get_price( $family_id, $license_name ) {
		
		$family = new Typolog_Family( $family_id );
		
		return $family->get_price( $license_name );
		
	}
	
	static function get_font_order( $family_id ) {
		
		$font_order = self::get_meta( $family_id, '_font_order' );
		
		if ( !$font_order ) {
			
		 	$fonts = Typolog_Font_Query::get_by_family( $family_id );
		 	
		 	$font_order = array_column( json_decode( json_encode( $fonts ), true ), 'ID' ); // extract ID properties from array of objects
		 	
		 	typolog_log( 'get_font_order_determine', $fonts );
			
		}
		
		return $font_order;
		
	}

}

class Typolog_Family {
	
	private $family;
	
	function __construct( $family = null ) {
		
		if ( $family ) {
			
			$this->load( $family );
			
		}
		
	}

	function load( $family_id ) {
		
		if ( is_object( $family_id ) ) {
			
			$this->family = $family_id;
			
			return $family_id;
			
		}
		
		$this->family = get_term( $family_id );
		
		return $this->family;
		
	}
	
	function unload() {
		
		unset( $this->family );
		
	}
	
	function is_loaded() {
		
		return is_object( $this->family );
		
	}
	
	function get( $param ) {
		
		if ( isset( $this->family ) ) {

			return $this->family->$param;	
			
		}
		
		return false;
		
	}
	
	function get_meta( $field_name ) {
		
		if ( isset( $this->family ) ) {
			
			return get_term_meta( $this->family->term_id, $field_name, true );
		
		}
		
		return false;
		
	}
	
	function set_meta( $field_name, $field_value ) {
		
		if ( isset( $this->family ) ) {
			
			if ( !isset( $field_value ) ) {
				
				return $this->unset_meta( $field_name );
				
			}
			
			return update_term_meta( $this->family->term_id, $field_name, $field_value );
		
		}
		
		return false;
		
	}

	function unset_meta($field) {
		
		if ( isset( $this->family ) ) {
				
			return delete_term_meta( $this->family->term_id, $field );
		
		}
		
		return false;
		
	}

	function get_attachments() {
		
		return $this->get_meta( '_typolog_attachments' );
		
	}

	function get_size_adjust() {
		
		$size_adjust = $this->get_meta( '_size_adjust' );

		return ($size_adjust) ? $size_adjust : '100';
		
	}

	function generate_package_name() {
		
		if ( isset( $this->family ) ) {
			
			return str_replace( ' ', '', $this->get_meta( '_family_name' ) );
		
		}
		
	}
	
	function is_commercial() {
		
		return $this->get_meta( '_commercial' );
		
	}
	
	/* Add new family term object to WP db */
	
	function add( $display_name, $family_name = "", $is_commercial = 1 ) {
		
		$res = term_exists( $display_name, 'typolog_family' );
		
		if ( !$res ) { // Make sure it doesn't already exist
			
			$res = wp_insert_term( $display_name, 'typolog_family', [ 'slug' => $family_name ] );
			
			if ( is_wp_error( $res ) ) {
				
				return $res;
				
			}
			
			$this->load( $res['term_id'] );
			
			if ( $family_name ) {
				
				$this->set_meta( '_family_name', $family_name );
				
			}
			
			if ( $is_commercial ) {
				
				$this->set_meta( '_commercial', $is_commercial );
				
			}
			
		}
		
		return $res['term_id'];
		
	}
	
	function delete($family_id) {
		
		if ( isset( $this->family ) ) {
			
			return wp_delete_term( $this->family->term_id, 'typolog_family' );
		
		}
		
	}
	
	function get_collection() {
		
		$font = new Typolog_Font( $this->get_main_font() );
		
		return $font->get_collection();
		
	}
	
	function is_testdrive_enabled() {
		
		$collection = $this->get_collection();
		
		return get_term_meta( $collection->term_id, '_enable_testdrive', true );
		
	}
	
	function get_fonts() {
		
		if ( isset( $this->family ) ) {
			
			$query = [
				
				'post_type' => 'typolog_font', 
				
				'posts_per_page' => -1,
				
				'order' => 'ASC'
				
			];
			
			$font_order = $this->get_meta( '_font_order' );
			
			if ( is_array( $font_order ) ) {
				
				$query[ 'orderby' ] = 'post__in';
				
				$query[ 'include' ] = $font_order;
				
			} else {
				
				$query[ 'orderby' ] = 'meta_value_num';
				
				$query[ 'meta_key' ] = '_font_weight';
				
				$query[ 'tax_query' ] =  [
					
					[
						
						'taxonomy' => 'typolog_family',
						 
						'field' => 'term_id',
						
						'terms' => [ $this->family->term_id ]
						
					]
							
				];
				
			}
			
			typolog_log( 'get_fonts_for_family_query', $query );


			return get_posts( $query );

		}
		
		return false;
		
	}
	
	function get_font_order() {
		
		$font_order = $this->get_meta( '_font_order' );
		
		if ( !$font_order ) {
			
		 	$fonts = Typolog_Font_Query::get_by_family( $this->get('term_id') );
		 	
		 	$font_order = array_column( json_decode( json_encode( $fonts ), true ), 'ID' ); // extract ID properties from array of objects
			
		}
		
		return $font_order;
		
	}


	function get_price($license_name) {
		
		$price = $this->get_meta( '_price_' . strtolower($license_name) ); // See if a specific family/license pricing exists
		
		if ( (!$price) && ($price !== 0) ) {
			
			$price = $this->get_meta( '_price' ); // See if a general family price exists
			
			if ( (!$price) && ($price !== 0) ) {
				
				if ( $fonts = $this->get_fonts() ) { // Load all fonts in family
					
					foreach ($fonts as $font) {
						
						$price += Typolog_Font_Query::get_price( $font->ID, $license_name ); // Use Font->get_price to calculate total family value
						
					}
					
				}
				
			}
			
		}
		
		return $price;
		
	}

	function get_main_font() {
		
		$main_font = $this->get_meta( '_main_font' );
		
		if ( !$main_font ) {
			
			$main_font = null;
			
			$fonts = $this->get_fonts();
			
			typolog_log( 'get_main_font_get_fonts_for_family', $fonts );
			
			$main_font_obj = array_shift( $fonts );
			
			if ( is_object( $main_font_obj ) ) {
						
				$main_font = $main_font_obj->ID; // get ID of first font returned for family
				
			}
			
			foreach ( $fonts as $font ) { 
				
				if ( 400 == Typolog_Font_Query::get_meta( $font->ID, '_font_weight') ) { // try to find the regular weight
					
					$main_font = $font->ID;
					
				}
			
			}
	
		}
		
		return $main_font;
			
	}	

}



