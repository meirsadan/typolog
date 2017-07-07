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

<form class="unused-originals" method="post">
<input type="hidden" name="action" value="delete_originals">

<h3><?php _e( 'Unused Original Font Files', 'typolog' ); ?></h3>

<ul class="cleanup-list unused-originals-list">

<?php
	

$used_originals = [];

$files = Typolog_Font_File_Query::get_all();

foreach ( $files as $file ) {
	$f = new Typolog_Font_File( $file->ID );
	if ( $file_path = $f->get_file_path() ) {
		array_push( $used_originals, basename( $file_path ) );
	}
}

$upload_dir = wp_upload_dir();

$originals_path = $upload_dir[ 'basedir' ] . '/' . TypologOptions()->get( 'fonts_dir' ) . '/';

$all_originals = scandir( $originals_path );

$unused_originals = [];

foreach( $all_originals as $original ) {
	if ( ( $original == '.' ) || ( $original == '..' )|| ( $original == 'index.html' ) ) continue;
	if ( false === array_search( $original, $used_originals ) ) {
		array_push( $unused_originals, $original ); ?>
		<li><label><input type="checkbox" name="delete_originals[]" value="<?=$original ?>" checked> <?=$original ?><a href="<?=$originals_path . $original ?>" target="_blank" class="unused-link"></a></label></li>
	<?php
	}
}

?>

</ul>
<?php if ( count( $unused_originals ) ) : ?>
<p><?php printf( __( 'Found %s unused original font files.', 'typolog' ), count( $unused_originals ) ); ?><br><input class="button delete-unused-originals-button" type="submit" value="<?php _e( 'Delete Selected Original Files', 'typolog' ); ?>"></p>
<?php else : ?>
<p><?php _e( 'No unused original files found.', 'typolog' ); ?></p>
<?php endif; ?>

</form>
