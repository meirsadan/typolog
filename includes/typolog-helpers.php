<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function typolog_set_wc_credentials( $wc_consumer_key, $wc_consumer_secret ) {
	
	$options = TypologOptions();
	
	$options->set( 'wc_consumer_key', $wc_consumer_key );
	
	$options->set( 'wc_consumer_secret', $wc_consumer_secret );
	
}

// Strip all characters from string except alphanumerics

function remove_special_chars( $string ) {
	
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    
}

// Check if all required variables are set, if not - exit

function required_vars( $vars, $die = false ) {
	
	foreach ($vars as $var) {
		
		if ( !isset( $var ) ) {
			
			if ( $die ) {
				
				echo json_encode( [ "error" => "Missing variables." ] );
				
				wp_die();
				
			} else {
				
				return false;
				
			}
			
		}
		
	}
	
	return true;
}

// Echo message(s) in JSON encoded format and exit

function echo_and_die( $data ) {
	
	echo json_encode( $data );
	
	wp_die();
	
}

// Get column from array, regardless if it's an actual array or object

function array_get_column($a, $column_name) {
	
	$n = array();
	
	foreach ( $a as $m ) {
		
		if ( is_object( $m ) ) {
			
			array_push( $n, $m->$column_name );
			
		} elseif ( is_array( $m ) ) {
			
			array_push( $n, $m[$column_name] );
			
		}
		
	}
	
	return $n;
	
}

// Merge two vars into array, even if they're not arrays themselves

function array_merge_two( $first, $second ) {
	
	if ( !is_array( $first ) ) {
		
		if ( ( $first ) || ( 0 === $first ) ) {
			
			$first = [ $first ];
			
		} else {
			
			return $second;
			
		}
		
	}
	
	if ( !is_array( $second ) ) {
		
		if ( ( $second ) || ( 0 === $second ) ) {
			
			$second = [ $second ];
			
		} else {
			
			return $first;
			
		}
		
	}
	
	$final = $first;
	
	foreach ( $second as $member ) {
		
		if ( false === array_search( $member, $final ) ) {
			
			$final[] = $member;
			
		}
		
	}
	
	return $final;
	
}

// Generate random md5 hash for file secrets
	
function generate_file_secret() {
	
	return md5( time() . '_' . rand( 100000, 999999 ) );
	
}

// Get the logs directory, create it if missing

function typolog_logs_dir() {
	
	$upload_dir = wp_upload_dir();
	
	$logs_dir = $upload_dir['basedir'] . '/typolog_logs';
	
	if ( !file_exists( $logs_dir ) ) {
		
		mkdir( $logs_dir );
		
	}
	
	return $logs_dir;
	
}

// Add log file with optional contents to logs directory

function typolog_log( $key, $contents = '' ) {
	
	if ( !is_string( $contents ) ) {
		
		$contents = print_r( $contents, true ); // if variable isn't string, convert it using print_r for friendly variable reading
		
	}
	
	return file_put_contents( typolog_logs_dir() . '/' . $key . '.txt', $contents );
	
}


// JUNKYARD

$GLOBALS['typolog_options'] = get_option( 'typolog_settings' );

function typolog_set_option( $option_name, $option_value ) {
	global $typolog_options;
	typolog_log( 'typolog_options_set', $typolog_options );
	$typolog_options[ $option_name ] = $option_value;
	update_option( 'typolog_settings', $typolog_options );
}


function get_product_type_label($product_type_code) {
	$product_type_labels = array(
		"f" => "Family",
		"s" => "Single",
		"b" => "Bundle"
	);
	return $product_type_labels[substr($product_type_code, 0, 1)];
}


