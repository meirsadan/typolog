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

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<form class="unused-products" method="post">
<input type="hidden" name="action" value="delete_products">

<h3><?php _e( 'Unused Products', 'typolog' ); ?></h3>

<ul class="cleanup-list unused-products-list">

<?php

$products = get_posts( [
	"posts_per_page" => -1,
	"post_type" => "product"
] );

$unused_products = [];

foreach ( $products as $product ) {
	$fonts = get_posts( [
		"posts_per_page" => -1,
		"post_type" => "typolog_font",
		"meta_key" => "_product_id",
		"meta_value" => $product->ID
	] );
	if ( !count( $fonts ) ) {
		$families = get_terms( [
			"taxonomy" => "typolog_family",
			"meta_key" => "_product_id",
			"meta_value" => $product->ID
		] );
		if ( !count( $families ) ) {
			array_push( $unused_products, $product->ID ); ?>
			<li><label><input type="checkbox" name="delete_products[]" value="<?=$product->ID ?>" checked> <?=get_the_title( $product->ID ); ?><a href="<?=get_edit_post_link( $product->ID ) ?>" target="_blank" class="unused-link"></a></label></li>
			<?php
		}
	}
}
	
?>

</ul>
<?php if ( count( $unused_products ) ) : ?>
<p><?php printf( __( 'Found %s unused products.', 'typolog' ), count( $unused_products ) ); ?><br><input class="button delete-unused-products-button" type="submit" value="<?php _e( 'Delete Selected Products', 'typolog' ); ?>"></p>
<?php else : ?>
<p><?php _e( 'No unused products found.', 'typolog' ); ?></p>
<?php endif; ?>

</form>
