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

<form class="unused-downloads" method="post">
<input type="hidden" name="action" value="delete_downloads">

<h3><?php _e( 'Unused Font Downloads', 'typolog' ); ?></h3>

<ul class="cleanup-list unused-downloads-list">

<?php
	

$used_downloads = [];

$fonts = Typolog_Font_Query::get_all();
foreach ( $fonts as $font ) {
	if ( is_array( $downloads = Typolog_Font_Query::get_meta( $font->ID, "_downloads" ) ) ) {
		foreach ( $downloads as $download ) {
			array_push( $used_downloads, basename( $download[ "path" ] ) );
		}
	} 
}

$families = Typolog_Family_Query::get_all();
foreach ( $families as $family ) {
	if ( is_array( $downloads = Typolog_Family_Query::get_meta( $family->term_id, "_downloads" ) ) ) {
		foreach ( $downloads as $download ) {
			array_push( $used_downloads, basename( $download[ "path" ] ) );
		}		
	}
}

$upload_dir = wp_upload_dir();

$products_path = $upload_dir[ 'basedir' ] . '/' . TypologOptions()->get( 'font_products_dir' ) . '/';

$all_downloads = scandir( $products_path );

$unused_downloads = [];

foreach( $all_downloads as $download ) {
	if ( "zip" == pathinfo( $download, PATHINFO_EXTENSION ) ) {
		if ( false === array_search( $download, $used_downloads ) ) {
			array_push( $unused_downloads, $download ); ?>
			<li><label><input type="checkbox" name="delete_downloads[]" value="<?=$download ?>" checked> <?=$download ?><a href="<?=$products_path . $download ?>" target="_blank" class="unused-link"></a></label></li>
		<?php
		}
	}
}

?>

</ul>
<?php if ( count( $unused_downloads ) ) : ?>
<p><?php printf( __( 'Found %s unused downloads.', 'typolog' ), count( $unused_downloads ) ); ?><br><input class="button delete-unused-downloads-button" type="submit" value="<?php _e( 'Delete Selected Downloads', 'typolog' ); ?>"></p>
<?php else : ?>
<p><?php _e( 'No unused downloads found.', 'typolog' ); ?></p>
<?php endif; ?>

</form>
