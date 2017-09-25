<?php

/* 
	
	Typolog
	
	F O N T   F A C T O R Y   C L A S S
	
	Manages all processes regarding fonts<->files<->products relationships
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Font_Factory {
	
	private $products_url;
	
	private $products_path;

	private $fonts;

	private $font_files;

	private $product_factory;

	private $package_factory;
	
	function __construct() {
		
		$upload_dir = wp_upload_dir();
		
		$this->products_path = wp_normalize_path( $upload_dir['basedir'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/' ); // normalize for Windows servers
		
		$this->products_url = $upload_dir['baseurl'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';
		
/*
		$this->fonts = new Typolog_Fonts();
		
		$this->font_files = new Typolog_Font_Files();
*/
		
		$this->product_factory  = new Typolog_Product_Factory();
		
		$this->package_factory = new Typolog_Package_Factory();
		
	}
	
	function is_wc_ready() {
		
		return $this->product_factory->check_for_woocommerce_api();
		
	}
	
	function create_wc_categories() {
		
		$product_category_id = $this->product_factory->create_font_product_category();
		
		if ( is_wp_error( $product_category_id) ) {
			
			return $product_category_id;
		}
	
		$license_attribute_id = $this->product_factory->create_license_attribute();

		if ( is_wp_error( $license_attribute_id) ) {
			
			return $license_attribute_id;
		}
	
		return true;
		
	}
	
	function wc_build_authorize_link() {
		
		return $this->product_factory->wc_build_authorize_link();
		
	}
	
	function delete_product_relationships( $product_id ) {
		
		$fonts = Typolog_Font_Query::get_by_meta( '_product_id', $product_id );
		
		$font_obj = new Typolog_Font();
		
		foreach ( $fonts as $font ) {
			
			$font_obj->load( $font );
			
			$font_obj->unset_meta( '_product_id' );
			
		}
		
		$families = Typolog_Family_Query::get_by_meta( '_product_id', $product_id );
		
		$family_obj = new Typolog_Family();
		
		foreach ( $families as $family ) {
			
			$family_obj->load( $family );
			
			$family_obj->unset_meta( '_product_id' );
			
		}
			
		return $product_id;
		
	}

	// Generate all products related to this font ID (font & family), returns array with product IDs for font ('single') and 'family'
	
	function generate_products( $font_id, $generate_family = true ) {
		
		$product_ids = array();
		
		$objects = [
			[ "id" => $font_id, "type" => "font" ]
		];

		$font = new Typolog_Font( $font_id );
		
		if ( $generate_family ) {
			$family = new Typolog_Family( $font->get_family_id() );
			array_push( $objects, [ "id" => $family->get( 'term_id' ), "type" => "family" ] );
		}
		
		$res = $this->product_factory->generate_product( $objects );
		
		if ( !is_wp_error( $res ) ) {
			
			$product_ids = [
				"single" => $res[ $font->get_meta( '_product_id' ) ]
			];
			
			if ( $generate_family ) {
				$product_ids[ "family" ] = $res[ $family->get_meta( '_product_id' ) ];
			}
			
			return $product_ids;
						
		}
		
		return $res;
		
	}
	
	function prepare_single_font( $font_id, $generate_family = true ) {

		if ( Typolog_Font_Query::is_font( $font_id ) ) {
			
			$font = new Typolog_Font( $font_id );

			$family = new Typolog_Family( $font->get_family() );
			
			$font->set_meta( '_fontface', $this->generate_fontface( $font_id ) );
			
			if ( $is_commercial = $font->is_commercial() ) { // only generate products for commercial fonts
				
				typolog_log( 'before_zip_generation', $font );
				
				$res = $this->package_factory->generate_zips( $font_id, $generate_family );

				if ( $res ) {
					
					if ( is_wp_error( $res ) ) {
						
						typolog_log( 'zip_error', $res );
						
						return false;
						
					}
					
					return $res;
				}
				
			}
				
		}
		
		return $is_commercial;
		
	}
	
	function prepare_fonts( $font_ids ) {
		
		$report = [];
		
		foreach ( $font_ids as $font_id ) {
			
			$report[ $font_id ] = $this->prepare_single_font( $font_id );
			
		}
		
		return $report;
		
	}
	
	function update_font_files( $font_files ) {
		
		$report = [];
		
		foreach ( $font_files as $font_file_id => $font_file_licenses ) {
			
			$result = wp_set_object_terms( $font_file_id, $font_file_licenses, 'typolog_license' );
			
			$report[ $font_file_id ] = ( is_array( $result ) && ( count( $result ) > 0 ) ) ? true : false;
			
		}
		
		return $report;
		
	}
	

	function update_single_font_products( $font_id, $generate_family = true ) {
		
		if ( Typolog_Font_Query::is_font( $font_id ) ) {
			
			$font = new Typolog_Font( $font_id );

			$family = new Typolog_Family( $font->get_family() );
			
			$font->set_meta( '_fontface', $this->generate_fontface( $font_id ) );
			
			if ( $font->is_commercial() ) { // only generate products for commercial fonts
				
				typolog_log( 'before_zip_generation', $font );
				
				$res = $this->package_factory->generate_zips( $font_id, $generate_family );
			
				if ( $res ) {
					
					if ( is_wp_error( $res ) ) {
						
						typolog_log('zip_error', $res);
						
						return false;
						
					}
					
					$res = $this->generate_products( $font_id, $generate_family );
					
					if ( is_array( $res ) ) {
						
/*
						$font->set_meta( '_product_id', $res['single']['product_id'] );
						
						$font->set_meta( '_variation_ids', $res['single']['variation_ids'] );
						
						if ( ( $generate_family ) && $family->is_loaded() ) {
							
							$family->set_meta( '_product_id', $res['family']['product_id'] );
							
							$family->set_meta( '_variation_ids', $res['family']['variation_ids'] );
							
						}
*/

						typolog_log('generate_fonts_result', $res);
						
						return true;
						
					}
					
				}
				
				return $res;
				
			} else { // if font isn't commercial, delete irrelevant metadata
				
				$this->unlink_product( $font );
				
				$this->unlink_product( $family );
				
				return true;
				
			}
			
		}
		
		return false;
		
	}
	
	function unlink_product( $obj ) {
		
		if ( $obj->is_loaded() ) {
			
			$downloads = $obj->get_meta( '_downloads' );
			
			if ( $downloads ) {
				
				$this->package_factory->delete_current_downloads( $downloads );
				
			}
			
			$product_id = $obj->get_meta( '_product_id' );
			
			if ( $product_id ) {
				
				$this->product_factory->delete_product( $product_id );
				
			}
			
			$obj->unset_meta( '_downloads' );
			
			$obj->unset_meta( '_product_id' );
			
			return true;
			
		}
		
	}
	
	function update_font_products( $font_ids = null ) {
		
		$objects = [];
		
		$family_ids = [];
		
		if ( !$font_ids ) {
			
			$fonts = Typolog_Font_Query::get_all();
			
			$fonts = json_decode( json_encode( $fonts ), true ); // convert query result to assoc. array
			
			$font_ids = array_column( $fonts, 'ID' );
			
		} elseif ( is_numeric( $font_ids ) ) { // is it a single ID?
			
			$font_ids = [ $font_ids ]; // convert to array
			
		}
		
		typolog_log( 'font_ids_for_update_font_products', $font_ids );
		
		foreach( $font_ids as $font_id ) {
			
			$res = $this->prepare_single_font( $font_id );
			
			if ( $res == true ) {

				array_push( $objects, [ "id" => $font_id, "type" => "font" ] );
				if ( $family_obj = Typolog_Font_Query::get_family( $font_id ) ) {
					$family_id = $family_obj->term_id;
					if ( ( $family_id ) && ( false === array_search( $family_id, $family_ids ) ) ) {
						array_push( $family_ids, $family_id );
						array_push( $objects, [ "id" => $family_id, "type" => "family" ] );
					}
				}
				
			}
			
		}
		
		typolog_log( 'update_font_products', $objects );
		
		return $this->product_factory->generate_product( $objects );
		
	}

	function update_family_products( $family_id ) {
		
		$family_fonts = Typolog_Family_Query::get_font_order( $family_id );
		
		return $this->update_font_products( $family_fonts );
		
	}

	function delete_font_file( $file_id, $font_id = '' ) {
		
		if ( $font_id ) {
			
			$font = new Typolog_Font( $font_id );
			
			if ( !$font->remove_file( $file_id ) ) {
				
				return false;
				
			}
			
		}
		
		$font_file = new Typolog_Font_File( $file_id );
		
		return $font_file->delete( $file_id );
		
	}
	
	function delete_font_files_by_list( $file_ids ) {
		
		if ( is_array( $file_ids ) ) {
			
			$font_file = new Typolog_Font_File();
				
			foreach ( $file_ids as $file_id ) {
				
				$font_file->load( $file_id );
				
				$font_file->delete();
				
			}
			
		}
		
		return true;
		
	}

	function delete_font_files( $font_id ) {
		
		$font = new Typolog_Font( $font_id );
		
		$file_ids = $font->get_meta( '_font_files' );
		
		$font->unset_meta( '_font_files' );
		
		return $this->delete_font_files_by_list( $file_ids );

	}

	function delete_all_fonts() {
		
		$fonts = Typolog_Font_Query::get_all();
		
		$font_obj = new Typolog_Font();

		foreach ( $fonts as $font ) {
			
			$font_obj->load( $font );
			
			if ( !$this->unlink_product( $font_obj ) ) {
				
				return new WP_Error( 'typolog_error_deleting_product_from_font', __('Error deleting product from font.', 'typolog'), [ 'font_id' => $font->ID ] );
				
			}

			if ( !$this->delete_font_files( $font->ID ) ) {
				
				return new WP_Error( 'typolog_error_deleting_font_files', __('Error deleting font files.', 'typolog'), array( 'font_id' => $font->ID ) );
				
			}
			
			if ( false === $font_obj->delete() ) {
				
				return new WP_Error( 'typolog_error_deleting_font', __('Error deleting font.', 'typolog'), array( 'font_id' => $font->ID ) );
				
			}

		}
		
		typolog_log( 'deleted_all_fonts' );
		
		$families = Typolog_Family_Query::get_all();
		
		$family_obj = new Typolog_Family();
		
		foreach ( $families as $family ) {
			
			$family_obj->load( $family );
			
			if ( !$this->unlink_product( $family_obj ) ) {
				
				return new WP_Error( 'typolog_error_deleting_product_from_family', __('Error deleting product from family.', 'typolog'), array( 'family_id' => $family->term_id ) );
				
			}
			
			if ( !$family_obj->delete() ) {
				
				return new WP_Error( 'typolog_error_deleting_family', __('Error deleting family.', 'typolog'), array( 'family_id' => $family->term_id ) );
				
			}
			
		}
		
		typolog_log( 'deleted_all_families' );

		if ( !$this->product_factory->delete_all_products() ) {
			
			return new WP_Error( 'typolog_error_deleting_all_products', __('Error deleting all products.', 'typolog') );
			
		}

		typolog_log( 'deleted_all_products' );
		
		$files = Typolog_Font_File_Query::get_all();
		
		if ( !$this->delete_font_files_by_list( $files ) ) {
			
			return new WP_Error( 'typolog_error_deleting_all_files', __('Error deleting all files.', 'typolog') );
			
		}

		typolog_log( 'deleted_all_files' );
		
		return true;
		
	}
	
	/* Converts a list of file names to a list of file IDs */

	function convert_file_list( $file_list ) {
		
		$file_obj = new Typolog_Font_File();
		
		foreach ( $file_list as &$font_file ) {
			
			$file_obj->load_by_filename( $font_file );
			
			if ( $file_obj->is_loaded() ) {
				
				$font_file = $file_obj->get( 'ID' );
				
			}
			
		}
		
		return $file_list;
		
	}
	
	function upload_catalog( $fonts_data, $commercial = null, $collection_id = null ) {
		
		$font_ids = [];
		
		$font_obj = new Typolog_Font();
		
		$report = [ "created" => [], "updated" => [], "error" => [] ];
		
		foreach ( $fonts_data as $font_data ) {
			
			if ( !is_array( $font_data ) ) {
				
				$font_data = (array) $font_data;
				
			}
			
			$font_data[ 'files' ] = $this->convert_file_list( $font_data[ 'files' ] );

			if ( $font_id = Typolog_Font_Query::font_exists( $font_data[ 'familyName' ], $font_data[ 'styleName' ] ) ) {
				
				$font_obj->load( $font_id );
				
				$font_id = $font_obj->update( $font_data );
				
				$group = "updated";

			} else {
				
				$font_id = $font_obj->create( $font_data );

				$group = "created";

			}
			
			typolog_log( 'font_created', $font_obj->get( 'ID' ) );
			
			if ( is_wp_error( $font_id ) || ( !$font_id ) ) {
				
				array_push( $report[ "error" ], $font_id );
				
				continue;
				
			} else {

				array_push( $report[ $group ], $font_id );
				
			}
			
			$font_obj->add_family( $font_data[ 'displayFamilyName' ], $font_data[ 'familyName' ], $commercial );
			
			if ( $collection_id ) $font_obj->set_collection( $collection_id );
			
			if ( is_numeric( $font_id ) ) {

				array_push( $font_ids, $font_id );

			}
			
		}

		$res = $this->update_font_products( $font_ids );
		
		return $report;
		
	}

	function upload_font( $file_array, $font_id = null, $font_file_id = null ) {
		
		$font_file = new Typolog_Font_File();
		
		if ( $font_file_id ) {
			
			$font_file->load( $font_file_id );
			
		} else {
			
			if ( is_object( $font_file_object = get_page_by_title( $file_array[ 'name' ] ) ) ) {
				
				$font_file->load( $font_file_object->ID );
				
			}
			
		}
		
		$font_file->upload( $file_array );
		
		if ( $font_file->is_loaded() ) {
			
			$font_file->set_license();
			
			if ( $font_id ) {
				
				$font = new Typolog_Font( $font_id );
				
				$font->add_file( $font_file->get( 'ID' ) );

				return $this->update_font_products( $font_id );

			}
			
			return true;
			
		}
		
		return false;
		
	}

	function generate_fontface( $font_id ) {
		
		$font = new Typolog_Font( $font_id );
		
		$web_family_name = $font->get_meta( '_web_family_name' );

		$file_ids = $font->get_meta( '_font_files' );
		
		$web_file_ids = $this->package_factory->get_font_package( $file_ids, 'web' );
		
		$web_files = array();
		
		$font_file = new Typolog_Font_File();
		
		foreach ( $web_file_ids as $web_file_id ) {
			
			$font_file->load( $web_file_id );
			
			$extension = pathinfo( $font_file->get_file_path(), PATHINFO_EXTENSION );
			
			$url = $font_file->get_file_url();
			
			$web_files[$extension] = $url;
			
		}
		
		$webfont_code = "@font-face { font-family: '$web_family_name'; ";
		
		if ( isset( $web_files['eot'] ) ) {
			
			$webfont_code .= "src: url('{$web_files['eot']}'); ";
			
		}
		
		$webfont_srcs = array();
		
		if ( isset( $web_files['eot'] ) ) {
			
			$webfont_srcs[] = "url('{$web_files['eot']}#iefix') format('embedded-opentype')";
			
		}
		
		if ( isset( $web_files['woff2'] ) ) {
			
			$webfont_srcs[] = "url('{$web_files['woff2']}') format('woff2')";
			
		}
		
		if ( isset( $web_files['woff'] ) ) {
			
			$webfont_srcs[] = "url('{$web_files['woff']}') format('woff')";
			
		}
		
		if ( isset( $web_files['ttf'] ) ) {
			
			$webfont_srcs[] = "url('{$web_files['ttf']}') format('ttf')";
			
		}
		
		if ( isset( $web_files['svg'] ) ) {
			
			$webfont_srcs[] = "url('{$web_files['svg']}') format('svg')";
			
		}
		
		if ( count( $webfont_srcs ) ) {
			
			$webfont_code .= "src: " . implode( ',', $webfont_srcs ) . "; }";
			
		} else {
			
			$webfont_code = "";
			
		}
		
// 		typolog_log('webfont_code_' . $font_id, $web_file_ids);
		
		return $webfont_code;
		
	}

	function get_size_adjust_tree() {
		
		$tree = [];
		
		$families = Typolog_Family_Query::get_all();
		
		foreach ( $families as $family ) {
			
			$fonts = Typolog_Font_Query::get_by_family( $family->term_id );
			
			$tree[ $family->name ] = [ 
				
				'id' => $family->term_id, 
				
				'name' => $family->name, 
				
				'fonts' => [] 
				
			];
			
			foreach ( $fonts as $font ) {
				
				$tree[ $family->name ][ 'fonts' ][] = [
					
					'id' => $font->ID,
					
					'name' => $font->post_title,
					
					'webfont_name' => Typolog_Font_Query::get_meta( $font->ID, '_web_family_name' ),
					
					'size_adjust' => Typolog_Font_Query::get_size_adjust( $font->ID )
					
				];
				
			}
			
		}
		
		return $tree;
		
	}
	
	function save_size_adjustments( $sizes ) {
		
		$font = new Typolog_Font();
		
		foreach ( $sizes as $size ) {
			
			$size = (array) $size;
			
			$font->load( $size['id'] );
			
			$font->set_meta( '_size_adjust', $size['size_adjust'] );
			
		}
		
		return true;
		
	}
	
	function save_license_attribute_term( $term_id ) {
		
		$license = new Typolog_License( $term_id );
		
		$attribute_term = $license->get_meta( '_attribute_term_id' );
		
		if ( $attribute_term ) {
			
			$attribute_term = $this->product_factory->update_license_attribute_term( $attribute_term, $license->get('name'), $license->get('slug') );
							
		} else {
				
			$attribute_term = $this->product_factory->add_license_attribute_term( $license->get('name'), $license->get('slug') );
			
			if ( is_array( $attribute_term ) ) {
				
				if ( isset( $attribute_term['id'] ) ) 
				
					$license->set_meta( '_attribute_term_id', $attribute_term[ 'id' ] );
				
			}
				
		}
		
	}

	function delete_license_attribute_term( $term_id ) {
		
		$license = new Typolog_License( $term_id );
		
		$attribute_term = $license->get_meta( '_attribute_term_id' );
		
		if ( $attribute_term ) {
			
			$this->product_factory->delete_license_attribute_term( $attribute_term );
			
		}
		
	}
	
}



