<?php

/* 
	
	Typolog
	
	P R O D U C T   F A C T O R Y   C L A S S
	
	Connects to the WP API and creates/updates/deletes products
	based on the font/family structure defined by the Typolog WP data
	
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Automattic\WooCommerce\Client;

use Automattic\WooCommerce\HttpClient\HttpClientException;



class Typolog_Product_Factory {
	
	private $woocommerce;
	
	function __construct() {
		
		$this->open_wc_connection();
		
	}
	
	/* Check for credentials and opens a connection to WC API */
	
	function open_wc_connection() {
		
		$wc_key = TypologOptions()->get( 'wc_consumer_key' );
		
		$wc_secret = TypologOptions()->get( 'wc_consumer_secret' );
		
		if (($wc_key) && ($wc_secret)) { // If key & secret exist, try to connect to WC API
			
			try {
				
				$this->woocommerce = new Client(
					
					get_site_url(), $wc_key, $wc_secret,
					
					[
						
						'wp_api' => true,
						
						'version' => 'wc/v2',
						
						'query_string_auth' => true
						
					]
					
				);
				
			} catch (HttpClientException $e) {
				
				echo $e->getMessage();
				
				unset( $this->woocommerce );
				
				return new WP_Error( 'typolog_wc_error', $e->getMessage() );
				
			}
			
			return true;
			
		}

	}
	
	function wc_build_authorize_link() {
		
		$store_url = get_site_url();
		
		$endpoint = '/wc-auth/v1/authorize';
		
		$params = [
			
			'app_name' => 'Typolog',
			
			'scope' => 'read_write',
			
			'user_id' =>  wp_create_nonce( 'typolog-connect-wc' ),
			
			'return_url' => get_admin_url() . 'options-general.php?page=typolog-settings.php',
			
			'callback_url' => get_site_url() . '?typolog_wc_connect=1'
			
		];
		
		$query_string = http_build_query($params);
		
		return $store_url . $endpoint . '?' . $query_string;
		
	}
		
	/* Ping the WC API service to check if it's available */
	
	function check_for_woocommerce_api() {
		
		if ( isset( $this->woocommerce ) ) {
			
			try {
				
				$this->woocommerce->get('');
				
			} catch (HttpClientException $e) {
				
				return false;
				
			}
			
			return true;
			
		}
		
		return false;
	}
	
	/* Fetch the fonts product category ID, return false if not found */
	
	function get_font_product_category() {
		
		if ( $id = TypologOptions()->get( 'fonts_product_category' ) ) { // Is it already set?
			
			return $id; 
			
		} elseif ( isset( $this->woocommerce ) ) {
			
			try {
				
				$res = $this->woocommerce->get( 'products/categories' ); // Try fetching a list of product categories
				
			} catch ( HttpClientException $e ) {
				
				return new WP_Error( 'typolog_wc_error', $e->getMessage() );
				
			}
			
			if ( is_array( $res ) ) { // Did we succeed?
				
				foreach ( $res as $cat ) {
					
					if ( $cat['slug'] == 'fonts' ) {
						
						TypologOptions()->set( 'fonts_product_category', $cat['id'] ); // Set the option
						
						return $cat['id'];
						
					}
					
				}
				
			}
			
			return false; // Fonts category not found
			
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
		
	}
	
	/* Create the fonts product category if it doesn't exist */
	
	function create_font_product_category() {
		
		$id = $this->get_font_product_category();
		
		if ( ( $id ) || ( is_wp_error( $id ) ) ) { // Does it already exist / some other shenanigan?
			
			return $id;
			
		}
		
		if ( isset( $this->woocommerce ) ) { // If not, attempt to create it via the WC API
			
			$data = [
				'name' => __('Fonts', 'typolog'), 
				'slug' => 'fonts'
			];
			
			try {
				
				$res = $this->woocommerce->post( 'products/categories', $data );
				
				typolog_log('product_categories', $res);
				
			} catch (HttpClientException $e) {
				
				return new WP_Error( 'typolog_wc_error', $this->woocommerce->http->getRequest()->getUrl() . ': ' . $e->getMessage() );
				
			}
			
			if ( is_array( $res ) ) { // Did we succeed?
				
				TypologOptions()->set( 'fonts_product_category', $res['id'] ); // Set the option
				
				return $res['id'];
				
			}

		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
	}
	
	/* Get the license attribute from the WC API */

	function get_license_attribute() {
		
		if ( $id = TypologOptions()->get( 'license_product_attribute' ) ) { // Is it already set?
			
			return $id; 
			
		} elseif ( isset( $this->woocommerce ) ) { // If not, attempt to create it via the WC API
			
			try {
				
				$res = $this->woocommerce->get( 'products/attributes' );
				
			} catch (HttpClientException $e) {
				
				return new WP_Error( 'typolog_wc_error', $e->getMessage() );
				
			}
			
			if (is_array($res)) {
				
				foreach ($res as $att) {
					
					if ($att['slug'] == 'pa_license') {

						TypologOptions()->set( 'license_product_attribute', $att['id'] ); // Set the option
						
						return $att['id'];
						
					}
					
				}
				
			}
			
			return false; // Not found
			
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
		
	}

	/* Create license product attribute if not found already */
		
	function create_license_attribute() {

		$id = $this->get_license_attribute(); // Check for existing license attribute
		
		if ( ($id !== false) || (is_wp_error($id)) ) { // Check for false or error object
			
			return $id; 
			
		}
			
		
		if ( isset( $this->woocommerce ) ) {
			
			// If not found, attempt to create attribute
			
			$data = [
				
				'name' => __( 'License', 'typolog' ), 
				'slug' => 'pa_license',
				'type' => 'select',
				'order_by' => 'menu_order',
				'has_archives' => false
				
			];
			
			try {
				
				$res = $this->woocommerce->post( 'products/attributes', $data );
				
				typolog_log( 'create_license_attribute', $res );
				
			} catch ( HttpClientException $e ) {
				
				return new WP_Error( 'typolog_wc_error', $this->woocommerce->http->getRequest()->getUrl() . ': ' . $e->getMessage() );
				
			}
			
			if ( is_array( $res ) ) { // Did we succeed?
			
				TypologOptions()->set( 'license_product_attribute', $res['id'] ); // Set the option

				return $res['id'];
				
			}
				
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
	}

	/* Add license product attribute term for name and (optional) slug */
		
	function add_license_attribute_term( $term_name, $term_slug = null ) {
		
		$attribute_id = $this->get_license_attribute(); // Check for existing license attribute
		
		if ( ($attribute_id == false) || (is_wp_error($attribute_id)) ) { // Check for false or error object
			
			return $attribute_id; 
			
		}
			
		if ( isset( $this->woocommerce ) ) {
			
			$data = [ 'name' => $term_name ];

			if ( $term_slug )
				
				$data[ 'slug' ] = $term_slug;
				
			try {
				
				$res = $this->woocommerce->post( 'products/attributes/' . $attribute_id . '/terms', $data );
				
				typolog_log( 'create_license_attribute_term', $res );
				
			} catch ( HttpClientException $e ) {
				
				return new WP_Error( 'typolog_wc_error', $this->woocommerce->http->getRequest()->getUrl() . ': ' . $e->getMessage() );
				
			}
			
			return $res;
				
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
	}

	/* Update license product attribute term for ID, name and (optional) slug */
		
	function update_license_attribute_term( $term_id, $term_name, $term_slug = null ) {
		
		$attribute_id = $this->get_license_attribute(); // Check for existing license attribute
		
		if ( ($attribute_id == false) || (is_wp_error($attribute_id)) ) { // Check for false or error object
			
			return $attribute_id; 
			
		}
			
		if ( isset( $this->woocommerce ) ) {
			
			$data = [ 'name' => $term_name ];

			if ( $term_slug )
				
				$data[ 'slug' ] = $term_slug;
				
			try {
				
				$res = $this->woocommerce->put( 'products/attributes/' . $attribute_id . '/terms/' . $term_id, $data );
				
				typolog_log( 'update_license_attribute_term', $res );
				
			} catch ( HttpClientException $e ) {
				
				return new WP_Error( 'typolog_wc_error', $this->woocommerce->http->getRequest()->getUrl() . ': ' . $e->getMessage() );
				
			}
			
			return $res;
				
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
	}

	/* Delete license product attribute by ID */
		
	function delete_license_attribute_term( $term_id ) {
		
		$attribute_id = $this->get_license_attribute(); // Check for existing license attribute
		
		if ( ($attribute_id == false) || (is_wp_error($attribute_id)) ) { // Check for false or error object
			
			return $attribute_id; 
			
		}
			
		if ( isset( $this->woocommerce ) ) {
			
			$data = [ 'force' => true ];
				
			try {
				
				$res = $this->woocommerce->delete( 'products/attributes/' . $attribute_id . '/terms/' . $term_id, $data );
				
				typolog_log( 'delete_license_attribute_term', $res );
				
			} catch ( HttpClientException $e ) {
				
				return new WP_Error( 'typolog_wc_error', $this->woocommerce->http->getRequest()->getUrl() . ': ' . $e->getMessage() );
				
			}
			
			return $res;
				
		} else {
			
			return new WP_Error( 'typolog_wc_unavailable', __('WooCommerce is not connected. Please use the WooCommerce Panel to connect it.', 'typolog') );
			
		}
	}
	
	/* Get a list of products from the WC API */
	
	function get_all_products() {
		
		if ( $this->check_for_woocommerce_api() ) {
			
			return $this->woocommerce->get( 'products', [ 'category' => $this->get_font_product_category() ] );
			
		}
		
		return false;
		
	}
	
	/* Delete a product */
	
	function delete_product($product_id) {
		
		$res = $this->woocommerce->delete( 'products/' . $product_id, [ 'force' => true ] );
		
		if ( isset( $res['errors'] ) ) {
			
			return new WP_Error( $res['errors'][0]->code, $res['errors'][0]->message );
			
		}
		
		return true;
		
	}
	
	/* Delete all products */
	
	function delete_all_products() {
		
		$products = $this->get_all_products();
		
		if ( is_array( $products ) ) {
			
			foreach ( $products as $product ) {
				
				$this->delete_product( $product['id'] );
				
			}
			
			return true;

		}
			
		return false;
		
	}
	
	/* Get associated product ID for font */
	
	function get_font_product_id( $font_id ) {
		
		return get_post_meta( $font_id, '_product_id', true );
		
	}
	
	/* Get associated product ID for family */
	
	function get_family_product_id( $family_id ) {
		
		return get_term_meta( $family_id, '_product_id', true );
		
	}
	
	/* Update associated product ID for font */
	
	function update_font_product_id( $font_id, $product_id ) {
		
		if (!add_post_meta( $font_id, '_product_id', $product_id, true )) {
			
			update_post_meta( $font_id, '_product_id', $product_id );
			
		}
		
		return true;
	}
	
	/* Update associated product ID for family */
	
	function update_family_product_id( $family_id, $product_id ) {
		
		update_term_meta( $family_id, '_product_id', $product_id );
		
		return true;
		
	}

	/* Update associated variation IDs for font */
	
	function update_font_variation_ids( $font_id, $variation_ids ) {
		
		if (!add_post_meta( $font_id, '_variation_ids', $variation_ids, true )) {
			
			update_post_meta( $font_id, '_variation_ids', $variation_ids );
			
		}
		
		return true;
	}
	
	/* Update associated product ID for family */
	
	function update_family_variation_ids( $family_id, $variation_ids ) {
		
		update_term_meta( $family_id, '_variation_ids', $variation_ids );
		
		return true;
		
	}
	
	/* Prepare standard font variation array */
	
	function generate_variation( $package_type, $filename_array, $price, $id = '' ) {
		
		typolog_log('filename_array', $filename_array);
		
		unset( $filename_array['path'] );
		
		$variation = array(
			
			"downloadable" => true,
			
			"virtual" => true,
			
			"regular_price" => $price,
			
			"attributes" => array(
				
				array(
					
					"id" => $this->get_license_attribute(),
					
					"option" => $package_type
					
				)
				
			),
			
			"downloads" => array( $filename_array )
			
		);
		
		if ( TypologOptions()->get( 'download_limit' ) ) {
			$variation[ 'download_limit' ] = TypologOptions()->get( 'download_limit' );
		}

		if ( TypologOptions()->get( 'download_expiry' ) ) {
			$variation[ 'download_expiry' ] = TypologOptions()->get( 'download_expiry' );
		}
		
		if ( $id ) {
			
			$variation['id'] = $id;
			
		}
		
		return $variation;
	}
	
	/* Get a list of product variations (assoc. array of "option" => id) by product ID from WC API */
	
	function get_product_variations( $product_id ) {
		
		if ( is_numeric($product_id) ) {
			
			$product = $this->woocommerce->get( 'products/' . $product_id );
			
			typolog_log( 'get_product_data', $product );
			
			if ( is_array($product) ) {
				
				if (isset($product['variations'])) {
					
					$variation_ids = array();
					
					foreach ($product['variations'] as $variation) {
						
						if ( isset( $variation['attributes'] ) && is_array( $variation['attributes'] ) ) { // access the variation attribute array
							
							$attribute = array_shift( $variation['attributes'] ); // Get the first attribute (presumably the license attribute)
							
							$variation_ids[strtolower( $attribute['option'] )] = $variation['id']; // add variation ID to the final result array
							
						}
						
					}
					
				}
				
			}
			
			return $variation_ids;
			
		}
		
		return false;
	}
	
	function generate_product_data( $id, $type = 'font' ) {

		switch ($type) {
			
			case 'font':
			
				$obj = new Typolog_Font( $id );
			
				$title = get_the_title( $id );
				
				break;
				
			case 'family':
			
				$obj = new Typolog_Family( $id );
			
				$title = $obj->get( 'name' );
				
				break;
				
			default: return; // Not 'font' or 'family'? No service!
				
		}
		
		$downloads = $obj->get_meta( '_downloads' );
		
		$product_id = $obj->get_meta( '_product_id' );
				
		$variation_ids = $obj->get_meta( '_variation_ids' );
				
		if ( !is_array( $downloads ) ) { // If no downloads are available, there is no need to create a producct
			
			return false;
			
		}
		
		if ( $product_id ) { // Check if a current product is already associated with this font/family
			
			if ( !$variation_ids ) { // Check for an associated variations ID array
				
				$variation_ids = $this->get_product_variations( $product_id ); // Generate the array if not found based on current product variations
				
			}
			
		} else {
			
			$variation_ids = array();
			
		}
		
		// Now verify & create variations based on the font/family downloads array and create a fresh variations array
		
		$variations = array();
		
		foreach ($downloads as $package_type => $filename) { // Do this for each package type
			
			switch ($type) { // Get price according to product type (family/font)
				
				case 'font':	$price = Typolog_Font_Query::get_price( $id, $package_type ); break;
				
				case 'family':	$price = Typolog_Family_Query::get_price( $id, $package_type ); break;
				
			}
			
			if ( isset( $variation_ids[ $package_type ] ) ) { // Is this variation set already within the existing product?
				
				$variation = $this->generate_variation( $package_type, $filename, $price, $variation_ids[ $package_type ] );
				
				unset( $variation_ids[ $package_type ] ); // Pull this variation out of the verification array
				
			} else { // Otherwise, create new variation
				
				$variation = $this->generate_variation( $package_type, $filename, $price );
				
			}
			
			array_push( $variations, $variation );
			
		}

		// Generate main dataset for product

		$data = [
			
			'name' => $title,
			
			'type' => 'variable',
			
			'sold_individually' => true,
			
			'categories' => [ 
				
				[ 
					
					'id' => $this->get_font_product_category()
					
				]
								
			],
							
			'attributes' => [
				
				[
					'id' => $this->get_license_attribute(),
					
					'variation' => true,

					'options' => Typolog_License_Query::get_all_slugs()
					
				]
				
			]
			
		];
		
		if ( $product_id ) {
		
			$data[ 'id' ] = $product_id;
			
		}

		$variations_data = [ 'create' => [], 'update' => [], 'delete' => [] ];

		foreach ( $variations as $variation ) {
			
			if ( isset( $variation[ 'id' ] ) ) {
				
				array_push( $variations_data[ 'update' ], $variation );
				
			} else {
				
				array_push( $variations_data[ 'create' ], $variation );
				
			}
			
		}
		
		// Delete unncessary variations
		
		foreach ( $variation_ids as $variation_id ) {
			
			array_push( $variations_data[ 'delete' ], $variation_id );
			
		}
		
		return [
			
			'product' => $data,
			
			'variations' => $variations_data
			
		];
		
	}
	
	function update_product_id( $id, $product_id, $type = 'font' ) {

		switch ( $type ) {
			
			case 'font':
			
				$this->update_font_product_id( $id, $product_id ); // Update the product ID record in the font WP item
				
				break;
				
			case 'family':	
			
				$this->update_family_product_id( $id, $product_id ); // Update the product ID record in the family WP term
				
				break;
				
		}

	}

	function update_product_variation_ids( $id, $variation_ids, $type = 'font' ) {

		switch ( $type ) {
			
			case 'font':
			
				$this->update_font_variation_ids( $id, $variation_ids ); // Update the product ID record in the font WP item
				
				break;
				
			case 'family':	
			
				$this->update_family_variation_ids( $id, $variation_ids ); // Update the product ID record in the family WP term
				
				break;
				
		}

	}
	

	/* Generate product and add/update it via WC API */
	
	function generate_product( $id, $type = 'font' ) {
		
		$data = [ 'create' => [], 'update' => [], 'delete' => [] ];
		
		$product_ids = [];
		
		$variations_data = [];

		$new_variations_data = [];
		
		if ( is_array( $id ) ) {
			
			foreach ( $id as $object ) {
				
				$object_data = $this->generate_product_data( $object[ 'id' ], $object[ 'type' ] );
				
				if ( isset( $object_data[ 'product' ][ 'id' ] ) ) {
					
					array_push( $variations_data, [ "id" => $object['id'], "product_id" => $object_data[ 'product' ][ 'id' ], "type" => $object[ 'type' ], "data" => $object_data[ 'variations' ] ] );
					
					array_push( $data[ 'update' ], $object_data[ 'product' ] );
					
				} else {

					array_push( $new_variations_data, [ "id" => $object['id'], "product_id" => null, "type" => $object[ 'type' ], "data" => $object_data[ 'variations' ] ] );
				
					array_push( $data[ 'create' ], $object_data[ 'product' ] );
					
				}
				
			}
			
		} else {
			
			return $this->generate_product( [ 'id' => $id, 'type' => $type ] );
			
		}
				
		typolog_log( 'product_data', $data );

		try {
			
			$result = $this->woocommerce->post( 'products/batch', $data );
			
		} catch (HttpClientException $e) {
			
			typolog_log( 'product_error', $e->getMessage() );
			
			return false;
			
		}

		typolog_log( 'product_result', $result );
		
		if ( isset( $result[ 'create' ] ) ) {
			
			foreach( $result[ 'create' ] as $index => $new_product ) {
				
				$new_variations_data[ $index ][ 'product_id' ] = $new_product[ 'id' ];
				
				$this->update_product_id( $new_variations_data[ $index ][ 'id' ],  $new_variations_data[ $index ][ 'product_id' ], $new_variations_data[ $index ][ 'type' ] );
				
			}
			
		}
		
		$variations_data = array_merge( $variations_data, $new_variations_data );
		
		typolog_log( 'product_variations_data', $variations_data );
				
		// Create batch update for variations
		
		$result = [];
		
		foreach ( $variations_data as $variations ) {
			
			try {
				
				$result = $this->woocommerce->post( 'products/' . $variations[ 'product_id' ] . '/variations/batch', $variations[ 'data' ] );
				
			} catch (HttpClientException $e) {
				
				typolog_log( 'product_variation_error', $e->getMessage() );
				
			}
			
			$variation_ids = [];
			
			// Based on result from the Woocommerce API, Make a new array of variation IDs to store in the font/family
			
			if ( isset( $result[ 'create' ] ) ) {
				
				foreach( $result[ 'create' ] as $new_variation ) {
					
					$variation_ids[ $new_variation[ 'attributes' ][ 0 ][ 'option' ] ] = $new_variation[ 'id' ]; 
					
				}
				
			}

			if ( isset( $result[ 'update' ] ) ) {
				
				foreach( $result[ 'update' ] as $existing_variation ) {
					
					$variation_ids[ $existing_variation[ 'attributes' ][ 0 ][ 'option' ] ] = $existing_variation[ 'id' ]; 
					
				}
				
			}
			
			$this->update_product_variation_ids( $variations[ 'id' ], $variation_ids, $variations[ 'type' ] );
				
			typolog_log( 'product_variations_result', $result );
			
			$result[ $variations[ 'product_id' ] ] = [
				
				"product_id" => $variations[ 'product_id' ],
				
				"variation_ids" => $variation_ids
				
			];
		
		}

		
		return $result;
		
	}
	
	// Wrapper function for generating a font product via WC API
	
	function generate_font_product($font_id) {
		
		return $this->generate_product($font_id, 'font');
		
	}
	
	// Wrapper function for generating a family product via WC API
	
	function generate_family_product($family_id) {
		
		return $this->generate_product($family_id, 'family');
		
	}
	
	
	/// THESE SHOULD BE MEMBERS OF DIFFERENT CLASS ///
	
	
	
	/* Main function for checking whether WC assets are properly defined, if not - plot out error message */
	
	function wc_checker_handler() {
		
		if ( isset( $this->woocommerce ) ) {
			
			/* Check for font product category */

			$res = $this->create_font_product_category(); // Then attempt to create it
			
			if ( is_wp_error( $res ) ) {
				
				echo '<div class="notice notice-error"><p>' . $res->get_error_message() . '</p></div>';
				
			}
			
			/* Check for license attribute */

			$res = $this->create_license_attribute(); // Then attempt to create it
			
			if ( is_wp_error( $res ) ) {
				
				echo '<div class="notice notice-error"><p>' . $res->get_error_message() . '</p></div>';
				
			}
				
		} else {
			
			return '<div class="notice notice-error"><p>' . sprintf(__('Store isn\'t connected! <a href="%s">Click here</a> to connect with Woocommerce.', 'typolog'), $this->wc_build_authorize_link()) . '</p></div>';
			
		}
		
	}


}


