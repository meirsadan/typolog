<?php
	
class Typolog_Admin_Settings {
	
	private $options;
	
	function __construct() {
		
		$this->options = TypologOptions();
		
		register_setting(
			
            'typolog-settings', // Option group
            
            'typolog_settings', // Option name
            
            array( $this, 'sanitize_setting' ) // Sanitize
            
        );

        add_settings_section(
	        
            'typolog_woocommerce_settings', // ID
            
            __('WooCommerce API Settings', 'typolog'), // Title
            
            array( $this, 'print_settings_wc_info' ), // Callback
            
            'typolog-settings' // Page
            
        );  

        add_settings_field(
	        
            'wc_consumer_key', // ID
            
            __('WooCommerce Consumer Key', 'typolog'), // Title 
            
            array( $this, 'wc_consumer_key_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_woocommerce_settings' // Section           
            
       );      

        add_settings_field(
	        
            'wc_consumer_secret', 
            
            __('WooCommerce Consumer Secret', 'typolog'), 
            
            array( $this, 'wc_consumer_secret_callback' ), 
            
            'typolog-settings', 
            
            'typolog_woocommerce_settings'
            
        );      

        add_settings_field(
	        
            'fonts_product_category', 
            
            __('Fonts Product Category', 'typolog'), 
            
            array( $this, 'fonts_product_category_callback' ), 
            
            'typolog-settings', 
            
            'typolog_woocommerce_settings'
            
        );      

        add_settings_field(
	        
            'license_product_attribute', 
            
            __('License Product Attribute', 'typolog'), 
            
            array( $this, 'license_product_attribute_callback' ), 
            
            'typolog-settings', 
            
            'typolog_woocommerce_settings'
            
        );      

        add_settings_field(
	        
            'base_price', // ID
            
            __('Base Price', 'typolog'), // Title 
            
            array( $this, 'base_price_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_woocommerce_settings' // Section           
            
        );      

        add_settings_field(
	        
            'download_limit', // ID
            
            __('Limit Number of Downloads', 'typolog'), // Title 
            
            array( $this, 'download_limit_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_woocommerce_settings' // Section           
            
        );      

        add_settings_field(
	        
            'download_expiry', // ID
            
            __('Expire Download (days)', 'typolog'), // Title 
            
            array( $this, 'download_expiry_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_woocommerce_settings' // Section           
            
        );      

        add_settings_section(
	        
            'typolog_directory_settings', // ID
            
            __('Directory Settings', 'typolog'), // Title
            
            array( $this, 'print_settings_directory_info' ), // Callback
            
            'typolog-settings' // Page
            
        );  

        add_settings_field(
	        
            'fonts_dir', // ID
            
            __('Font Files Directory', 'typolog'), // Title 
            
            array( $this, 'fonts_directory_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_directory_settings' // Section           
            
        );      

        add_settings_field(
	        
            'font_products_dir', 
            
            __('Font Products Directory', 'typolog'), 
            
            array( $this, 'font_products_directory_callback' ), 
            
            'typolog-settings', 
            
            'typolog_directory_settings'
            
        );      

        add_settings_section(
	        
            'typolog_general_attachments_section', // ID
            
            __('General Attachments', 'typolog'), // Title
            
            array( $this, 'print_general_attachments_info' ), // Callback
            
            'typolog-settings' // Page
            
        );  

        add_settings_field(
	        
            'general_attachments', // ID
            
            __('Attachments', 'typolog'), // Title 
            
            array( $this, 'general_attachments_callback' ), // Callback
            
            'typolog-settings', // Page
            
            'typolog_general_attachments_section' // Section           
            
        );      

        add_settings_section(
	        
            'typolog_generator_settings', // ID
            
            __('Generator Settings', 'typolog'), // Title
            
            array( $this, 'print_generator_settings_info' ), // Callback
            
            'typolog-settings' // Page
            
        );  

        add_settings_field(
	        
            'generator_allowed_file_extensions', 
            
            __( 'Allowed File Extensions', 'typolog' ), 
            
            array( $this, 'generator_allowed_file_extensions_callback' ), 
            
            'typolog-settings', 
            
            'typolog_generator_settings'
            
        );      

        add_settings_field(
	        
            'generator_strip_from_family_names', 
            
            __( 'Phrases to Strip from Family Names', 'typolog' ), 
            
            array( $this, 'generator_strip_from_family_names_callback' ), 
            
            'typolog-settings', 
            
            'typolog_generator_settings'
            
        );      

        add_settings_field(
	        
            'generator_weights_map', 
            
            __('Generator Weights Map (100–900)', 'typolog'), 
            
            array( $this, 'generator_weights_map_callback' ), 
            
            'typolog-settings', 
            
            'typolog_generator_settings'
            
        );      

        add_settings_field(
	        
            'generator_styles_map', 
            
            __('Generator Styles Map (normal/italic/oblique)', 'typolog'), 
            
            array( $this, 'generator_styles_map_callback' ), 
            
            'typolog-settings', 
            
            'typolog_generator_settings'
            
        );      

        add_settings_field(
	        
            'generator_styles_dictionary', 
            
            __('Generator Styles Dictionary', 'typolog'), 
            
            array( $this, 'generator_styles_dictionary_callback' ), 
            
            'typolog-settings', 
            
            'typolog_generator_settings'
            
        );      

        add_settings_section(
	        
            'typolog_danger_zone', // ID
            
            __('Danger Zone', 'typolog'), // Title
            
            array( $this, 'print_danger_zone' ), // Callback
            
            'typolog-settings' // Page
            
        );  

	}

	private function get_esc_option( $option_name, $default_value = '' ) {
	
		$val = $this->options->get( $option_name, $default_value );
		
		return esc_attr( $val );
		
	}
	
	
	function sanitize_setting( $input ) {
		
        $new_input = array();
        
        if( isset( $input['wc_consumer_key'] ) ) {
	        
	        if ( '' !== $input['wc_consumer_key'] ) {
		        
          	  $new_input['wc_consumer_key'] = sanitize_text_field( $input['wc_consumer_key'] );
          	  
	        }
	        
        }

        if( isset( $input['wc_consumer_secret'] ) ) {
	        
	        if ( '' !== $input['wc_consumer_secret'] ) {
		        
	            $new_input['wc_consumer_secret'] = sanitize_text_field( $input['wc_consumer_secret'] );
	            
	        }
	        
        }

        if( isset( $input['fonts_product_category'] ) ) {
	        
	        if ('' !== $input['fonts_product_category'] ) {
		        
	            $new_input['fonts_product_category'] = sanitize_text_field( $input['fonts_product_category'] );
	            
	        }
	        
        }
 
        if( isset( $input['license_product_attribute'] ) ) {
	        if ($input['license_product_attribute'] !== '') {
	            $new_input['license_product_attribute'] = sanitize_text_field( $input['license_product_attribute'] );
	        }
        }  

        if( isset( $input['base_price'] ) )
            $new_input['base_price'] = sanitize_text_field( $input['base_price'] );

        if( isset( $input['download_limit'] ) )
            $new_input['download_limit'] = sanitize_text_field( $input['download_limit'] );

        if( isset( $input['download_expiry'] ) )
            $new_input['download_expiry'] = sanitize_text_field( $input['download_expiry'] );

        if( isset( $input['fonts_dir'] ) )
            $new_input['fonts_dir'] = sanitize_text_field( $input['fonts_dir'] );

        if( isset( $input['font_products_dir'] ) )
            $new_input['font_products_dir'] = sanitize_text_field( $input['font_products_dir'] );

        if( isset( $input['general_attachments'] ) )
            $new_input['general_attachments'] = $input['general_attachments'];
            
        if ( isset( $input[ 'weights_map_keys' ] ) && isset( $input[ 'weights_map_values' ] ) ) {
	        foreach ( $input[ 'weights_map_keys' ] as $index => $key ) {
		        if ( $key && $input[ 'weights_map_values' ][ $index ] ) {
			        $new_input[ 'weights_map' ][ sanitize_text_field( $key ) ] = sanitize_text_field( $input[ 'weights_map_values' ][ $index ] );
		        }
	        }
        }

        if ( isset( $input[ 'styles_map_keys' ] ) && isset( $input[ 'styles_map_values' ] ) ) {
	        foreach ( $input[ 'styles_map_keys' ] as $index => $key ) {
		        if ( $key && $input[ 'styles_map_values' ][ $index ] ) {
			        $new_input[ 'styles_map' ][ sanitize_text_field( $key ) ] = sanitize_text_field( $input[ 'styles_map_values' ][ $index ] );
		        }
	        }
        }

        if ( isset( $input[ 'styles_dictionary_keys' ] ) && isset( $input[ 'styles_dictionary_values' ] ) ) {
	        foreach ( $input[ 'styles_dictionary_keys' ] as $index => $key ) {
		        if ( $key && $input[ 'styles_dictionary_values' ][ $index ] ) {
			        $new_input[ 'styles_dictionary' ][ sanitize_text_field( $key ) ] = sanitize_text_field( $input[ 'styles_dictionary_values' ][ $index ] );
		        }
	        }
        }

        if ( isset( $input[ 'allowed_file_extensions' ] ) ) {
	        $new_input[ 'allowed_file_extensions' ] = [];
	        foreach ( $input[ 'allowed_file_extensions' ] as $extension ) {
		        array_push( $new_input[ 'allowed_file_extensions' ], sanitize_text_field( $extension ) );
	        }
        }

        if ( isset( $input[ 'strip_from_family_names' ] ) ) {
	        $new_input[ 'strip_from_family_names' ] = [];
	        foreach ( $input[ 'strip_from_family_names' ] as $str ) {
		        array_push( $new_input[ 'strip_from_family_names' ], sanitize_text_field( $str ) );
	        }
        }

        return $new_input;
	}
	
	function print_danger_zone() {
		echo "<div class='section-info'>";
		echo '<p class="regenerate-all-fonts-container"><button class="button regenerate-all-fonts">' . __('Regenerate All Products', 'typolog') . '</button><span class="spinner"></span></p><p class="regenerate-fonts-report"></p>';
		tl_fontef_families_js_var();
		echo '<p class="reset-products-container"><button class="button reset-products">' . __('Reset Font Products', 'typolog') . '</button><span class="spinner"></span></p>';
		echo '<p class="delete-all-fonts-container"><button class="button delete delete-all-fonts">' . __('Delete All Fonts', 'typolog') . '</button><span class="spinner"></span></p>';
		echo "</div>";
	}
	
	function print_settings_wc_info() {
/*
		echo '<div class="typolog-wc-status">';
		if ($this->font_factory->wc_check_for_api()) {
			printf('<p class="connected">%s</p>', __('WooCommerce is connected.', 'typolog') );
		} else {
			printf('<p class="disconnnected">%s</p>', __('WooCommerce is not connected. Click above to connect or enter credentials here:', 'typolog') );
		}
		echo '</div>';
*/
	}

	function print_settings_directory_info() {
		
		printf( "<p class='section-info'>%s</p>", __( 'Set directories here:', 'typolog' ) );
		
	}

	function print_general_attachments_info() {
		
		printf( "<p class='section-info'>%s</p>", __( 'Manage attachments appended to all font product bundles:', 'typolog' ) );
		
	}

	function print_generator_settings_info() {
		
		printf( "<p class='section-info'>%s</p>", __('Manage settings related to the catalog generator:', 'typolog') );
		
	}
	
	function wc_consumer_key_callback() {
		
        printf(
	        
            '<input type="text" id="wc_consumer_key" name="typolog_settings[wc_consumer_key]" value="%s" />',
            
            $this->get_esc_option( 'wc_consumer_key' )
            
        );
        
	}

	function wc_consumer_secret_callback() {
		
        printf(
	        
            '<input type="text" id="wc_consumer_secret" name="typolog_settings[wc_consumer_secret]" value="%s" />',
            
            $this->get_esc_option( 'wc_consumer_secret' )
            
        );
	}

	function fonts_product_category_callback() {
		
        printf(
	        
            '<input type="text" id="fonts_product_category" name="typolog_settings[fonts_product_category]" value="%s" />',
            
            $this->get_esc_option( 'fonts_product_category' )
            
        );
        
	}

	function license_product_attribute_callback() {
		
        printf(
	        
            '<input type="text" id="license_product_attribute" name="typolog_settings[license_product_attribute]" value="%s" />',
            
            $this->get_esc_option( 'license_product_attribute' )
            
        );
        
	}

	function base_price_callback() {
		
        printf(
	        
            '<input type="text" id="base_price" name="typolog_settings[base_price]" value="%s" />',
            
            $this->get_esc_option( 'base_price' )
        );
	}

	function generator_allowed_file_extensions_callback() {
		
		$allowed_file_extensions = $this->options->get( 'allowed_file_extensions', [ 'woff', 'woff2', 'eot', 'ttf', 'otf', 'svg' ] );
		
		printf( "<div class='allowed-file-extensions-wrapper'>" );
		
		foreach ( $allowed_file_extensions as $extension ) {
			
			printf( '<p class="file-extension-entry"><input type="text" name="typolog_settings[allowed_file_extensions][]" value="%s" /><a href="#" class="button delete-entry">%s</a></p>', esc_attr( $extension ), __( 'Delete', 'typolog' ) );
			
		}

		printf( '<p><a href="#" class="button add-entry">%s</a></p>', __( 'Add', 'typolog' ) );

		printf( "</div>" );

	}

	function generator_strip_from_family_names_callback() {
		
		$strip_from_family_names = $this->options->get( 'strip_from_family_names', [ ] );
		
		printf( "<div class='strip-from-family-names-wrapper'>" );
		
		foreach ( $strip_from_family_names as $str ) {
			
			printf( '<p class="strip-from-family-names-entry"><input type="text" name="typolog_settings[strip_from_family_names][]" value="%s" /><a href="#" class="button delete-entry">%s</a></p>', esc_attr( $str ), __( 'Delete', 'typolog' ) );
			
		}

		printf( '<p><a href="#" class="button add-entry">%s</a></p>', __( 'Add', 'typolog' ) );

		printf( "</div>" );

	}

	function generator_weights_map_callback() {
		
		$weights_map = $this->options->get( 'weights_map', [ 
			'thin' => 250,
			'extralight' => 250,
			'ultralight' => 250,
			'light' => 300,
			'normal' => 400,
			'regular' => 400,
			'book' => 400,
			'medium' => 500,
			'demi' => 600,
			'semibold' => 600,
			'bold' => 700,
			'ultrabold' => 800,
			'extrabold' => 800,
			'black' => 900,
			'heavy' => 900
		 ] );
		
		printf( "<div class='weights-map-wrapper'>" );
		
		foreach ( $weights_map as $key => $value ) {
			
			printf( '<p class="weights-map-entry"><input type="text" name="typolog_settings[weights_map_keys][]" value="%s" placeholder="%s" /><input type="text" name="typolog_settings[weights_map_values][]" value="%s" placeholder="%s" /><a href="#" class="button delete-entry">%s</a></p>', esc_attr( $key ), __( 'Key', 'typolog' ), esc_attr( $value ), __( 'Value', 'typolog' ), __( 'Delete', 'typolog' ) );
			
		}

		printf( '<p><a href="#" class="button add-entry">%s</a></p>', __( 'Add', 'typolog' ) );

		printf( "</div>" );

	}

	function generator_styles_map_callback() {
		
		$styles_map = $this->options->get( 'styles_map', [ 
			'normal' => 'normal',
			'italic' => 'italic',
			'oblique' => 'oblique',
			'slanted' => 'oblique'
		 ] );
		
		printf( "<div class='styles-map-wrapper'>" );
		
		foreach ( $styles_map as $key => $value ) {
			
			printf( '<p class="styles-map-entry"><input type="text" name="typolog_settings[styles_map_keys][]" value="%s" placeholder="%s" /><input type="text" name="typolog_settings[styles_map_values][]" value="%s" placeholder="%s" /><a href="#" class="button delete-entry">%s</a></p>', esc_attr( $key ), __( 'Key', 'typolog' ), esc_attr( $value ), __( 'Value', 'typolog' ), __( 'Delete', 'typolog' ) );
			
		}

		printf( '<p><a href="#" class="button add-entry">%s</a></p>', __( 'Add', 'typolog' ) );

		printf( "</div>" );

	}

	function generator_styles_dictionary_callback() {
		
		$styles_dictionary = $this->options->get( 'styles_dictionary', [ ] );
		
		printf( "<div class='styles-dictionary-wrapper'>" );
		
		foreach ( $styles_dictionary as $key => $value ) {
			
			printf( '<p class="styles-dictionary-entry"><input type="text" name="typolog_settings[styles_dictionary_keys][]" value="%s" placeholder="%s" /><input type="text" name="typolog_settings[styles_dictionary_values][]" value="%s" placeholder="%s" /><a href="#" class="button delete-entry">%s</a></p>', esc_attr( $key ), __( 'Key', 'typolog' ), esc_attr( $value ), __( 'Value', 'typolog' ), __( 'Delete', 'typolog' ) );
			
		}

		printf( '<p><a href="#" class="button add-entry">%s</a></p>', __( 'Add', 'typolog' ) );

		printf( "</div>" );

	}

	function download_limit_callback() {
		
        printf(
	        
            '<input type="text" id="base_price" name="typolog_settings[download_limit]" value="%s" />',
            
            $this->get_esc_option( 'download_limit' )
        );
	}

	function download_expiry_callback() {
		
        printf(
	        
            '<input type="text" id="base_price" name="typolog_settings[download_expiry]" value="%s" />',
            
            $this->get_esc_option( 'download_expiry' )
        );
	}

	function fonts_directory_callback() {
		
		$uploads_dir = wp_upload_dir();
		
        printf(
	        
            '<span dir=ltr><code>%s/</code> <input type="text" id="fonts_dir" name="typolog_settings[fonts_dir]" value="%s" placeholder="%s" /> <code>/</code></span>',
            
            $uploads_dir['baseurl'],
            
            $this->get_esc_option( 'fonts_dir' ),
            
            $this->options->get_default( 'fonts_dir' )
            
        );
        
	}

	function font_products_directory_callback() {
		
		$uploads_dir = wp_upload_dir();
		
        printf(
	        
            '<span dir=ltr><code>%s/</code> <input type="text" id="font_products_dir" name="typolog_settings[font_products_dir]" value="%s" placeholder="%s" /> <code>/</code></span>',
            
            $uploads_dir['baseurl'],
            
            $this->get_esc_option( 'font_products_dir' ),
            
            $this->options->get_default( 'font_products_dir' )
            
        );
        
	}

	function general_attachments_callback() {
		
		$attachments_obj = new Typolog_Attachments();
		
		$attachments_obj->load_general();
		
		$attachments = $attachments_obj->get_table();
		
		include plugin_dir_path( __FILE__ ) . '../admin/partials/typolog-admin-general-attachments.php';
	}
	
}