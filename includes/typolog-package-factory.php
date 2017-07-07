<?php
	
/* 
	
	Typolog
	
	P A C K A G E   F A C T O R Y   C L A S S
	
	Creates and manages file packages for font products
	
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Package_Factory {
	
	private $products_path;
	
	private $products_url;
		
	function __construct() {
		
		$upload_dir = wp_upload_dir();
		
		$this->products_path = $upload_dir['basedir'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';
		
		$this->products_url = $upload_dir['baseurl'] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';
				
	}
	
	function get_font_package( $file_ids, $license_name ) {
		
		$package = array();
		
		$file = new Typolog_Font_File();
		
		if ( !$file_ids ) $file_ids = [];
		
		foreach ( $file_ids as $file_id ) {
			
			$file->load( $file_id );
			
			if ( $licenses = $file->get_licenses() ) {
				
				foreach ( $licenses as $license ) {
					
					if ( strtolower( $license->slug ) == strtolower( $license_name ) )
					
						$package[] = $file_id;
						
				}
				
			}
			
		}
		
		return $package;
	}

	/* Get a list of font files, packaged by license - according to a list of file ids */
	
	function get_packages( $file_ids ) {
		
		$font_file = new Typolog_Font_File();
		
		$packages = array();

		foreach ( $file_ids as $file_id ) {
			
			$font_file->load( $file_id );
			
			$licenses = $font_file->get_licenses();
			
			if ( is_array( $licenses ) ) {
				
				// Create multi-dimensional array of license types and file IDs
				
				foreach ( $licenses as $license ) {
					
					$packages[$license->slug][] = $file_id;
					
				}
				
			}
			
		}
		
		return $packages;
		
	}

	/* Get a list of font files, packages by license - according to family ID */

	function get_family_packages( $family_id ) {
		
		$res = [];
		
		$family_obj = new Typolog_Family( $family_id );
		
		$fonts = $family_obj->get_fonts();
		
		foreach ( $fonts as $font ) {
			
			$font_file_ids = Typolog_Font_Query::get_meta( $font->ID, '_font_files' );
			
			foreach ( $font_file_ids as $font_file_id ) {
				
				if (false === array_search( $font_file_id, $res ) ) { // if file ID doesn't exist in final array, add it
					
					$res[] = $font_file_id;
					
				}				
			}
			
		}
		
		typolog_log( 'get_family_packages_file_ids_' . $family_id, $res );
		
		return $this->get_packages( $res );
		
	}
	
	
	function generate_zip_filename( $base_name, $secret ) {
		
		return $base_name . '_' . $secret . '.zip';
		
	}
	
	/* Creates a ZIP package with given file IDs and attachments, returns array with file 'name', 'file' url and file 'path' */
	
	function zip_package( $file_ids, $zip_filename, $attachments = [ ] ) {
		
		$font_file = new Typolog_Font_File();
		
		$zip = new ZIPArchive();
		
		$secret = generate_file_secret(); // Generate secret to be added to file
		
		$full_package_filename = $this->products_path . $this->generate_zip_filename( $zip_filename, $secret ); // path to final zip file
		
		$full_package_url = $this->products_url . $this->generate_zip_filename( $zip_filename, $secret ); // url to final zip file

		if ( true !== $res = $zip->open( $full_package_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) { // Try to create a new zip file
			
			return new WP_Error( 'typolog_cant_zip', __( 'Can\'t open ZIP ', 'typolog' ), array( 'zip_filename' => $full_package_filename, 'error_code' => $res ) );
			
		}
		
		typolog_log( 'zip_package_file_ids', $file_ids );
		
		// Go through files and add them to zip archive
		
		foreach ($file_ids as $font_file_id) {
			
			$font_file->load( $font_file_id );
			
			$font_filename = $font_file->get_filename(); // Get the file name
			
			$full_font_filename = $font_file->get_file_path(); // Get the full path to file
			
			typolog_log( 'font_data', [ $font_filename, $full_font_filename ] );
			
			if ( !$zip->addFile( $full_font_filename, $zip_filename . "/" . $font_filename ) ) { // Try to add the file to zip archive
				
				return new WP_Error( 'typolog_cant_add_to_zip', __( 'Can\'t add to ZIP', 'typolog' ), array( 'zip_filename' => $full_package_filename, 'file_to_add' =>  $full_font_filename ) );
				
			}
			
		}
		
		// Add attachments, if any
		
		$attachments = array_merge_two($attachments, TypologOptions()->get( 'general_attachments' ) ); // Add general attachments to requested attachments
		
		if ( count($attachments) ) {
			
			foreach ($attachments as $attachment) {
				
				$attachment_filename = get_attached_file( $attachment );
				
				if ( !$zip->addFile($attachment_filename, $zip_filename . "/" . basename( $attachment_filename )) ) { // Try to add attachment to zip archive
					
					return new WP_Error( 'typolog_cant_add_to_zip', __( 'Can\'t add to ZIP', 'typolog' ), array( 'zip_filename' => $full_package_filename, 'file_to_add' =>  $attachment ) );
					
				}
			}
			
		}
		
		$zip->close();
		
		return [
			
			'name' => $zip_filename . '.zip', 
			
			'file' => $full_package_url,
			
			'path' => $full_package_filename
			
		];
		
	}
	
	/* Create packages according to license bundles for font */
	
	function zip_packages( $font_files, $package_family_name, $attachments = null ) {
		
		$packages_array = array();
		
		$license = new Typolog_License();
		
		foreach ( $font_files as $type => $files ) {
			
			$license->load_by_slug( $type );
			
			$license_attachments = $license->get_attachments();
			
			$attachments = array_merge_two( $attachments, $license_attachments );
			
			$res = $this->zip_package( $files, $package_family_name . "_" . $type, $attachments );
			
			if ( is_wp_error( $res ) ) {
				
				return $res;
				
			}
			
			$packages_array[$type] = $res;
			
		}
		
		return $packages_array;
	
	}
	
	/* Get attachments for single font */
	
	function get_single_font_attachments( $font_id ) {
		
		$font = new Typolog_Font( $font_id );

		$font_attachments = $font->get_attachments();
		
		$family = new Typolog_Family( $font->get_family() );
		
		$family_attachments = $family->get_attachments();
		
		return array_merge_two($font_attachments, $family_attachments);
		
	}
	
	/* Create ZIP archive for a single font by ID */
	
	function zip_single_packages( $font_id ) {
		
		$font = new Typolog_Font();
		
		$font->load( $font_id );
		
		$font_files = $font->get_meta( '_font_files' );
		
		if ( isset( $font_files ) ) {
			
			$packages = $this->get_packages( $font_files );
			
			$package_file_name = $font->generate_package_name();

			$res = $this->zip_packages( $packages, $package_file_name, $this->get_single_font_attachments( $font_id ) );

			if ( is_array( $res ) ) { // Did we succeed?
				
				$this->delete_current_downloads( $font->get_meta( '_downloads' ) );
				
				$font->set_meta( '_downloads', $res );

				return true;
				
			}

			return $res;
		}
		
		return false;
		
	}
	
	/* Deletes current ZIP archives according to list */
	
	function delete_current_downloads( $downloads_list ) {
		
		if ( is_array( $downloads_list ) ) {
			
			foreach ( $downloads_list as $download_item ) {
				
				if ( isset( $download_item['path'] ) ) {
					
					if ( file_exists( $download_item['path'] ) )  {
						
						unlink( $download_item['path'] );
						
					}
					
				}
				
			}
			
		}
		
		return true;
		
	}
	
	/* Create ZIP package according to family */

	function zip_family_packages( $family_id ) {
		
		$family = new Typolog_Family( $family_id );
		
		$font_files = $this->get_family_packages( $family_id );
		
		typolog_log( 'zip_family_packages_font_files_' . $family_id, $font_files );
		
		$package_file_name = $family->generate_package_name();
		
		$attachments = $family->get_attachments();
		
		$res = $this->zip_packages( $font_files, $package_file_name, $attachments );
		
		if ( is_array( $res ) ) {
			
			$this->delete_current_downloads( $family->get_meta( '_downloads' ) );
			
			$family->set_meta( '_downloads', $res );
			
			return true;
			
		}
		
		return $res;
		
	}
	
	/* Generate all associated ZIP packages for font */
	
	function generate_zips( $font_id, $generate_family = true ) {
		
		$res = $this->zip_single_packages( $font_id );
		
		if ( ( $res ) && ( !is_wp_error( $res ) ) && ( $generate_family ) ) {
			
			$font = new Typolog_Font();
			
			$font->load( $font_id );
			
			if ( $family_id = $font->get_family_id() ) {
				
				return $this->zip_family_packages( $family_id );
				
			}
			
			return $family_id;
			
		}
		
		return $res;
		
	}
	
}


