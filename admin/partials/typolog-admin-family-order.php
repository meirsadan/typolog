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

if ( isset( $_POST['update_family_order_nonce'] ) && wp_verify_nonce( $_POST['update_family_order_nonce'], 'update_family_order_fields' ) ) {
	
	$collections = $_POST[ 'collection' ];
	
	typolog_log( "order_families_var", $collections );
	
	foreach ( $collections as $collection ) {
		
		foreach ( $collection as $order_num => $family_id ) {
			
			update_term_meta( $family_id, '_order_num', $order_num );
			
		}
		
	}
	
}

 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap family-order">

<h1 class="typolog-page-title"><?php _e( 'Order Families', 'typolog' ); ?></h1>

<div class="notice-wrap"></div>

<form method="post">

<?php wp_nonce_field( 'update_family_order_fields', 'update_family_order_nonce' ); ?>

<div class="family-order-control">	
	<span class="spinner"></span>
	<input type="submit" value="<? _e( 'Save', 'typolog' ); ?>" class="button save-family-order">
</div>

<?php
	
$collections = Typolog_Collection_Query::get_all();

foreach ( $collections as $collection ) :
	$families = Typolog_Family_Query::get_by_collection( $collection->term_id );
	
?>

<div class="family-order family-order-collection">
	<h1><?=$collection->name ?></h1>
	<ul class="family-order-list">
<?php foreach ( $families as $family ) : ?>
		<li><?=$family->name ?><input type="hidden" name="collection[<?=$collection->term_id ?>][]" value="<?=$family->term_id ?>"></li>
<?php endforeach; ?>
	</ul>
</div>

<?php endforeach; ?>

</form>

</div>