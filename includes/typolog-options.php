<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $typolog_options_global;

/* Handles all options for Typolog plugin, services all other classes */

class Typolog_Options {

	/* Default option values if not set */
	
	const OPTIONS_VAR = 'typolog_settings';
	
	private $DEFAULTS = [
		
		'base_price' => 325,
		
		'fonts_dir' => 'fonts',
		
		'font_products_dir' => 'font_products',
		
		'general_attachments' => []
		
	];
	
	private $options;
	
	function __construct() {

		$options = get_option( self::OPTIONS_VAR );
		
		if ( is_array( $options ) ) {
			
			$this->options = array_merge( $this->DEFAULTS, $options );
			
		} else {
			
			$this->options = $this->DEFAULTS;
			
		}
		
	}
	
	/* Retrieve option value, returns false / other default if it doesn't exist */
	
	function get( $option_name, $default_value = false ) {
		
		if ( isset( $this->options[ $option_name ]) ) {
			
			return $this->options[ $option_name ];
			
		}
		
		return $default_value;
		
	}
	
	function get_default( $option_name ) {
		
		if ( isset( $this->DEFAULTS[ $option_name ] ) ) return $this->DEFAULTS[ $option_name ];
		
		return '';
		
	}
	
	/* Set an option and update WP */
	
	function set( $option_name, $option_value = '' ) {
		
		$this->options[ $option_name ] = $option_value;
		
		update_option( self::OPTIONS_VAR, $this->options );
		
	}
	
	/* Delete an option and update WP */
	
	function delete( $option_name ) {
		
		if ( isset( $this->options[ $option_name ]) ) {
			
			unset( $this->options[ $option_name ]);
			
			update_option( self::OPTIONS_VAR, $this->options );
			
		}
		
	}
	
}

$typolog_options_global = new Typolog_Options();

/* Wrapper for global Typolog options variable */

function TypologOptions() {
	
	global $typolog_options_global;
	
	return $typolog_options_global;
	
}
