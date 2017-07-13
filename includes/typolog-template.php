<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Echoes font name by ID/current post */

function tl_font_name( $font_id = null ) {
	
	global $post;
	
	if ( !$post_id ) $font_id = $post->ID;
	
	echo get_the_title( $font_id );
	
}

/* Echoes script tag with JS list of all (commercial) families */

function tl_fontef_families_js_var() {

	$families = Typolog_Family_Query::get_all();
	?>
	<script type="text/javascript"> FontefFamilies = [ <?php
	foreach( $families as $family ) : if ( Typolog_Family_Query::is_commercial( $family->term_id ) ) : 
	?> { name: "<?=$family->name ?>", id: <?=$family->term_id ?> }, <?php
	endif; endforeach; 
	?> ]; </script> <?php
	
}

/* Echoes font family name by font ID/current post */

function tl_family_name( $font_id = null ) {
	
	global $post_id;
	
	if ( !$post_id ) $font_id = $post->ID;
	
	$font = new Typolog_Font( $font_id );
	
	$family = $font->get_family();
	
	if ( is_object( $family ) ) echo $family->name;
	
}

/* Echoes fontface string */

function tl_fontface( $font_id = null ) {
	
	global $post;
	
	if ( !$font_id ) $font_id = $post->ID;
	
	$fontface = Typolog_Font_Query::get_meta( $font_id, '_fontface' );
	
	echo $fontface . "\n";
	
}

/* Echoes all fontfaces for family by ID */

function tl_family_fontfaces( $family_id ) {
	
	$fonts = Typolog_Font_Query::get_by_family( $family_id );
	
	foreach ( $fonts as $font ) {
		
		tl_fontface( $font->ID );
		
	}
	
}

/* Echoes all main fontfaces for collection by ID */

function tl_collection_main_fontface( $collection_id ) {
	
	$main_font = Typolog_Collection_Query::get_main_font( $collection_id );
	
	tl_fontface( $main_font );
	
}

/* Echoes all main fontfaces for family by ID */

function tl_family_main_fontface( $family_id ) {
	
	$main_font_id = Typolog_Family_Query::get_main_font( $family_id );
	
	tl_fontface( $main_font_id );
	
}

/* Echoes all fontfaces / by collection ID */

function tl_all_fontfaces() {
	
	$families = Typolog_Family_Query::get_all();
	
	foreach ( $families as $family ) {
		
		tl_family_fontfaces( $family->term_id );
		
	}
	
}


/* Echoes all main fontfaces / by collection ID */

function tl_all_main_fontfaces( $collection_id = null ) {
	
	if ( $collection_id ) {
		
		$families = Typolog_Collection_Query::get_families( $collection_id );
		
	} else {
		
		$families = Typolog_Family_Query::get_all();
	
	}
	
	foreach ( $families as $family ) {
		
		tl_family_main_fontface( $family->term_id );
		
	}
	
}

function tl_all_collections_main_fontfaces() {
	
	$collections = Typolog_Collection_Query::get_all();
	
	foreach ( $collections as $collection ) {
		
		tl_family_main_fontface( Typolog_Collection_Query::get_meta( $collection->term_id, '_main_font' ) );

	}
	
}

/* Echoes size adjust value for font */

function tl_webfont_size_adjust($font_id = null) {

	echo tl_get_webfont_size_adjust( $font_id );	
	
}

/* Returns webfont size adjust value for font */

function tl_get_webfont_size_adjust($font_id = null) {
	
	global $post;
	
	if ( !$font_id ) $font_id = $post->ID;
	
	$size_adjust = Typolog_Font_Query::get_size_adjust( $font_id );
	
	return $size_adjust . '%';
	
}

/* Echoes webfont name by font ID */

function tl_webfont_name( $font_id = null ) {
	
	echo tl_get_webfont_name( $font_id );

}

/* Returns webfont name by font ID */

function tl_get_webfont_name( $font_id = null ) {
	
	global $post;
	
	if ( !$font_id ) $font_id = $post->ID;
	
	$web_family_name = Typolog_Font_Query::get_meta( $font_id, '_web_family_name' );
	
	return $web_family_name;
	
}

/* Returns all webfont names in family by family ID */

function tl_get_family_webfont_names( $family_id ) {
	
	$names = array();
	
	$fonts = Typolog_Font_Query::get_by_family( $family_id );
	
	foreach ($fonts as $font) {
		
		array_push( $names, tl_get_webfont_name( $font->ID ) );
		
	}
	
	return $names;
}

function tl_get_family_webfont_dictionary( $family_id ) {
	
	$names = array();
	
	$fonts = Typolog_Font_Query::get_by_family( $family_id );
	
	foreach ($fonts as $font) {
		
		$names[ tl_get_webfont_name( $font->ID ) ] = get_the_title( $font->ID );
		
	}
	
	return $names;
}


function tl_get_family_main_webfont_name( $family_id ) {
	
	$names = array();
	
	$font_id = Typolog_Family_Query::get_main_font( $family_id );
	
	return tl_get_webfont_name( $font_id );

}

/* Returns all webfont names */

function tl_get_all_webfont_names() {
	
	$names = array();
	
	$families = Typolog_Family_Query::get_all();
	
	foreach ( $families as $family ) {
		
		$names = array_merge( tl_get_family_webfont_names( $family->term_id ), $names );
		
	}
	
	return $names;
	
}

/* Returns all webfont names */

function tl_get_all_main_webfont_names( $collection_id = null ) {
	
	if ( $collection_id ) {
		
		$families = Typolog_Collection_Query::get_families( $collection_id );
				
	} else {
		
		$families = Typolog_Family_Query::get_all();
	
	}
	
	$names = array();
	
	foreach ( $families as $family ) {
		
		array_push( $names, tl_get_family_main_webfont_name( $family->term_id ) );
		
	}
	
	return $names;
	
}

function tl_get_all_main_webfont_dictionary( $collection_id = null ) {
	
	if ( $collection_id ) {
		
		$families = Typolog_Collection_Query::get_families( $collection_id );
				
	} else {
		
		$families = Typolog_Family_Query::get_all();
	
	}
	
	$names = array();
	
	foreach ( $families as $family ) {
		
		$main_font_id = Typolog_Family_Query::get_main_font( $family->term_id );
		
		$names[ tl_get_family_main_webfont_name( $family->term_id ) ] = get_the_title( $main_font_id );
		
	}
	
	return $names;
	
}


function get_font_family_id( $font_id ) {
	
	$font = new Typolog_Font( $font_id );
	
	return $font->get_family_id();
	
}

/* Return/echo main webfont name for family */ 

function tl_family_webfont_name( $family_id, $get = false ) {
	
	$main_font = Typolog_Family_Query::get_main_font( $family_id );
	
	if ($get) {
		
		return tl_get_webfont_name( $main_font );
		
	}
	
	tl_webfont_name( $main_font );
	
	return;
		
}

/* Return/echo main webfont size adjust value */

function tl_family_webfont_size_adjust( $family_id, $get = false ) {

	$main_font = Typolog_Family_Query::get_main_font( $family_id );
	
	if ($get) {
		
		return tl_get_webfont_size_adjust( $main_font );
		
	}
	
	tl_webfont_size_adjust( $main_font );
	
	return;

}

function get_fonts_by_family_id( $family_id ) {
	
	return Typolog_Font_Query::get_by_family( $family_id );
	
}

function tl_license_name( $license_slug, $get = false ) {
	
	$license = Typolog_License_Query::get_by_slug( $license_slug );
	
	if ( is_object( $license ) ) {
		
		if ( $get ) {
			
			return $license->name;
			
		}
		
		echo $license->name;
		
	}
	
}

function get_font_families() {
	
	return Typolog_Family_Query::get_all();
	
}

/* Returns complex array describing all families and fonts */

function get_families_tree() {
	
	$licenses_all = Typolog_License_Query::get_all();
	
	$license_descriptions = array();
	
	foreach ( $licenses_all as $license ) {
		$license_descriptions[ $license->slug ] = term_description( $license->term_id, 'typolog_license' );
	}
	
	$licenses_order = Typolog_License_Query::get_licenses_order();
	
	$families = [];
	
	$families_wp = Typolog_Family_Query::get_all();
	
	foreach ( $families_wp as $family_wp ) {
		
		$fonts = [];
		
		$fonts_wp = Typolog_Font_Query::get_by_family( $family_wp->term_id );
		
		foreach ( $fonts_wp as $font_wp ) {
			
			$licenses = [];
			
			if ( is_array( $variations = Typolog_Font_Query::get_meta( $font_wp->ID, '_variation_ids' ) ) ) {
				
				foreach ( $variations as $variation_slug => $variation_id ) {
					
					$licenses[ $licenses_order[ $variation_slug ] ] = [
						
						'title' => tl_license_name( $variation_slug, true ),
						
						'description' => $license_descriptions[ $variation_slug ],
						
						'product_id' => Typolog_Font_Query::get_meta( $font_wp->ID, '_product_id' ),
						
						'variation_id' => $variation_id,
						
						'price' => Typolog_Font_Query::get_price( $font_wp->ID, $variation_slug )
						
					];
					
				}
				
				ksort( $licenses, SORT_NUMERIC );
				
				$licenses = array_values( $licenses );
				
				array_push( $fonts, [
					
					'id' => $font_wp->ID,
					
					'title' => Typolog_Font_Query::get_meta( $font_wp->ID, '_display_style_name' ),
					
					'family_title' => $family_wp->name,
					
					'web_family_name' => Typolog_Font_Query::get_meta( $font_wp->ID, '_web_family_name' ),
					
					'size_adjust' => Typolog_Font_Query::get_size_adjust( $font_wp->ID ),
					
					'family_web_family_name' => tl_family_webfont_name( $family_wp->term_id, true ),
					
					'family_size_adjust' => tl_family_webfont_size_adjust( $family_wp->term_id, true ),
					
					'product_id' => Typolog_Font_Query::get_meta( $font_wp->ID, '_product_id' ),
					
					'licenses' => $licenses
					
				] );
			
			}
			
		}
		
		$licenses = array( );
		
		if ( is_array( $variations = Typolog_Family_Query::get_meta( $family_wp->term_id, '_variation_ids' ) ) ) {
		
			foreach ( $variations as $variation_slug => $variation_id ) {
				
				$licenses[ $licenses_order[ $variation_slug ] ] = [
					
					'title' => tl_license_name( $variation_slug, true ),
				
					'description' => $license_descriptions[ $variation_slug ],
						
					'product_id' => Typolog_Family_Query::get_meta( $family_wp->term_id, '_product_id' ),
					
					'variation_id' => $variation_id,
					
					'price' => Typolog_Family_Query::get_price( $family_wp->term_id, $variation_slug )
					
				];
			}
			
			ksort( $licenses, SORT_NUMERIC );
			
			$licenses = array_values( $licenses );
	
			array_push( $families, [
				
				'id' => $family_wp->term_id,
				
				'title' => $family_wp->name,
				
				'web_family_name' => tl_family_webfont_name( $family_wp->term_id, true ),
				
				'size_adjust' => tl_family_webfont_size_adjust($family_wp->term_id, true),
				
				'product_id' => Typolog_Family_Query::get_meta( $family_wp->term_id, '_product_id' ),
				
				'licenses' => $licenses,
				
				'fonts' => $fonts
				
			]);
			
		}
		
	}
	
	$families = apply_filters( 'typolog_families_tree', $families );
	
	return $families;
	
}

