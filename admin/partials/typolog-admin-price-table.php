<?php
	
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://meirsadan.com/
 * @since      1.0.0
 *
 * @package    Typolog
 * @subpackage Typolog/admin/partials
 */
 
$licenses = Typolog_License_Query::get_all();
$families = Typolog_Family_Query::get_all();

if ( isset( $_POST['update_price_table_nonce'] ) && wp_verify_nonce( $_POST['update_price_table_nonce'], 'update_price_table_fields' ) ) {
	
	$prices = $_POST[ 'prices' ];
	
	if ( $prices[0][0][0] ) {
		TypologOptions()->set( 'base_price', $prices[0][0][0] );
	}
	
	foreach ( $licenses as $license ) {
		if ( $prices[0][0][$license->term_id] ) {
			update_term_meta( $license->term_id, '_base_price', $prices[0][0][$license->term_id] );
		} else {
			delete_term_meta( $license->term_id, '_base_price' );
		}
	}
	
	foreach ( $families as $family ) {
		if ( $prices[ $family->term_id ][0][0] ) {
			update_term_meta( $family->term_id, '_price', $prices[ $family->term_id ][0][0] );
		} else {
			delete_term_meta( $family->term_id, '_price' );
		}
		foreach ( $licenses as $license ) {
			if ( $prices[ $family->term_id ][0][ $license->term_id ] ) {
				update_term_meta( $family->term_id, '_price' . $license->slug, $prices[ $family->term_id ][0][ $license->term_id ] );
			} else {
				delete_term_meta( $family->term_id, '_price' . $license->slug );
			}
		}
		$fonts = Typolog_Font_Query::get_by_family( $family->term_id );
		foreach ( $fonts as $font ) {
			if ( $prices[ $family->term_id ][ $font->ID ][0] ) {
				update_post_meta( $font->ID, '_price', $prices[ $family->term_id ][ $font->ID ][0] );
			} else {
				delete_post_meta( $font->ID, '_price' );
			}
			foreach ( $licenses as $license ) {
				if ( $prices[ $family->term_id ][ $font->ID ][ $license->term_id ] ) {
					update_post_meta( $font->ID, '_price' . $license->slug, $prices[ $family->term_id ][ $font->ID ][ $license->term_id ] );
				} else {
					delete_post_meta( $font->ID, '_price' . $license->slug );
				}
			}
		}
	}
	
}

 $base_price = TypologOptions()->get( 'base_price' );
 
 $base_licenses_prices = array();
 
 foreach ( $licenses as $license ) {
	 if (!$base_licenses_prices[ $license->slug ] = get_term_meta( $license->term_id, '_base_price', true ) ) {
		$base_licenses_prices[ $license->slug ] = $base_price;
	}
 }
 
 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap prices">
	
<h1 class="typolog-page-title"><?php _e( 'Price Table', 'typolog' ); ?></h1>

<div class="notice-wrap typolog-notice regenerate-fonts-report"></div>

<form class="price-table-form" method="post">
	
	<?php wp_nonce_field( 'update_price_table_fields', 'update_price_table_nonce' ); ?>

<div class="prices-control">
	<input type="submit" class="button update-prices" disabled value="<?php _e( 'Update', 'typolog' ); ?>">
	<span class="regenerate-all-fonts-container"><button class="button regenerate-all-fonts"> <?php _e('Regenerate All Products', 'typolog') ?></button><span class="spinner"></span></span>
	<?php tl_fontef_families_js_var(); ?>
</div>

<table class="admin-table price-table">
	<thead>
	<tr class="head-row">
		<td></td>
		<td><?php _e( 'Default License', 'typolog' ); ?></td>
		<?php foreach ( $licenses as $license ) : ?>
		<td><?php printf( __( '%s License', 'typolog' ), $license->name ); ?></td>
		<?php endforeach; ?>
	</tr>
	</thead>
	
	<tbody>
	<tr>
		<td><?php _e( 'Default Font', 'typolog' ); ?></td>
		<td><input name="prices[0][0][0]" type="text" value="<?=$base_price; ?>"></td>
		<?php foreach ( $licenses as $license ) : ?>
		<td><input name="prices[0][0][<?=$license->term_id ?>]" type="text" value="<?=get_term_meta( $license->term_id, '_base_price', true ) ?>" placeholder="<?=$base_price ?>"></td>
		<?php endforeach; ?>
	</tr>
	
	<?php foreach ( $families as $family ) :
		
		if ( !get_term_meta( $family->term_id, '_commercial', true ) ) continue;
		
		$fonts = Typolog_Font_Query::get_by_family( $family->term_id );
		
		$default_family_price = array();
		
		$default_family_price_meta = get_term_meta( $family->term_id, '_price', true );

		foreach ($fonts as $font) {
			
			$default_family_price[ 0 ] += Typolog_Font_Query::get_price( $font->ID ); // Use Font->get_price to calculate total family value		

			foreach ( $licenses as $license ) {

				$default_family_price[ $license->slug ] += Typolog_Font_Query::get_price( $font->ID, $license->slug ); // Use Font->get_price to calculate total family value		
				
			}
			
		}
		
	?>
	
	<tr class="family-row">
		<td class="family-name family-item"><a href="#" class="family-toggle"></a><?=$family->name ?></td>
		<td><input name="prices[<?=$family->term_id ?>][0][0]" type="text" value="<?=$default_family_price_meta ?>" placeholder="<?=$default_family_price[0] ?>"></td>
		<?php foreach ( $licenses as $license ) : ?>
		<td><input name="prices[<?=$family->term_id ?>][0][<?=$license->term_id ?>]" type="text" value="<?=get_term_meta( $family->term_id, '_price_' . $license->slug, true ) ?>" placeholder="<?=( $default_family_price_meta ? $default_family_price_meta : $default_family_price[$license->slug] ) ?>"></td>
		<?php endforeach; ?>
	</tr>
	
	<?php if ( count( $fonts ) > 1 ) : ?>
	
	<?php foreach ( $fonts as $font ) : 
		
		$font_default_price = get_post_meta( $font->ID, '_price', true );
		
	?>

	<tr class="font-row">
		<td class="font-name"><?=$font->post_title ?></td>
		<td><input name="prices[<?=$family->term_id ?>][<?=$font->ID ?>][0]" type="text" value="<?=$font_default_price ?>" placeholder="<?=$base_price ?>"></td>
		<?php foreach ( $licenses as $license ) : ?>
		<td><input name="prices[<?=$family->term_id ?>][<?=$font->ID ?>][<?=$license->term_id ?>]" type="text" value="<?=get_post_meta( $font->ID, '_price_' . $license->slug, true ) ?>" placeholder="<?=( $font_default_price ? $font_default_price : $base_licenses_prices[ $license->slug ] ) ?>"></td>
		<?php endforeach; ?>
	</tr>
	
	<?php endforeach; ?>
	
	<?php endif; ?>
	
	<?php endforeach; ?>
	
	</tbody>
	
</table>

</form>
	

</div>