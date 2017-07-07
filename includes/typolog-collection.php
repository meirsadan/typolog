<?php

/* 
	
	Typolog
	
	C O L L E C T I O N   C L A S S
	
	Handles collection WP data and metadata
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Collection_Query {
	
	static function get_all() {
		
		return get_terms( 'typolog_collection' , [ "hide_empty" => false ] );
		
	}
	
	static function get_by_meta( $key, $value ) {
		
		return get_terms( 'typolog_collection', [ 
			
			'meta_query' => [ 
				
				'key' => $key,
				 
				'value' => $value
				
			] 
		
		] );
		
	}

	static function get_families( $collection ) {
		
		$families = array();
		
		if ( $collection ) {
			
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
				
				'tax_query' => $tax_query
				
			] );
			
			foreach ( $fonts as $font ) {
				
				$family = Typolog_Font_Query::get_family( $font->ID );
				
				if ( $family ) {
					
					$families[ $family->term_id ] = $family;
					
				}
				
			}
			
			$families = array_values( $families );
			
		}
		
		return $families;
		
	}

	
	static function get_meta( $collection_id, $key = null ) {
		
		if ( !$key ) {
			
			return get_term_meta( $collection_id );
			
		}
		
		return get_term_meta( $collection_id, $key, true );
		
	}
	
	static function get_main_font( $collection_id ) {
		
		$collection = new Typolog_Collection( $collection_id );
		
		return $collection->get_main_font();
		
	}

}

class Typolog_Collection {
	
	private $collection;
	
	function __construct( $collection = null ) {
		
		if ( $collection ) {
			
			$this->load( $collection );
			
		}
		
	}

	function load( $collection_id ) {
		
		if ( is_object( $collection_id ) ) {
			
			$this->collection = $collection_id;
			
			return $collection_id;
			
		}
		
		$this->collection = get_term( $collection_id );
		
		return $this->collection;
		
	}
	
	function unload() {
		
		unset( $this->collection );
		
	}
	
	function is_loaded() {
		
		return is_object( $this->collection );
		
	}
	
	function get( $param ) {
		
		if ( isset( $this->collection ) ) {

			return $this->collection->$param;	
			
		}
		
		return false;
		
	}
	
	function get_meta( $field_name ) {
		
		if ( isset( $this->collection ) ) {
			
			return get_term_meta( $this->collection->term_id, $field_name, true );
		
		}
		
		return false;
		
	}
	
	function set_meta( $field_name, $field_value ) {
		
		if ( isset( $this->collection ) ) {
			
			if ( !isset( $field_value ) ) {
				
				return $this->unset_meta( $field_name );
				
			}
			
			return update_term_meta( $this->collection->term_id, $field_name, $field_value );
		
		}
		
		return false;
		
	}

	function unset_meta($field) {
		
		if ( isset( $this->collection ) ) {
				
			return delete_term_meta( $this->collection->term_id, $field );
		
		}
		
		return false;
		
	}

	function get_attachments() {
		
		return $this->get_meta( '_typolog_attachments' );
		
	}

	function is_commercial() {
		
		return $this->get_meta( '_commercial' );
		
	}
	
	function get_fonts() {
		
		if ( isset( $this->collection ) ) {
			
			$query = [
				
				'post_type' => 'typolog_font', 
				
				'posts_per_page' => -1,
				
				'order' => 'ASC',
				
				'orderby' => 'meta_value',
				
				'meta_key' => '_family_name',
				
				'tax_query' =>  [
					
					[
						
						'taxonomy' => 'typolog_collection',
						 
						'field' => 'term_id',
						
						'terms' => [ $this->collection->term_id ]
						
					]
							
				]
				
			];
			
			typolog_log( 'get_fonts_for_collection_query', $query );

			return get_posts( $query );

		}
		
		return false;
		
	}

	function get_main_font() {
		
		$main_family = $this->get_meta( '_main_font' );
		
		if ( $main_family ) {
			
			return Typolog_Family_Query::get_main_font( $main_family );
			
		}
		
		return false;
			
	}	
	
}



