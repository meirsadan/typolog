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

<form class="unused-files" method="post">
<input type="hidden" name="action" value="delete_font_files">

<h3><?php _e( 'Unused Font Files', 'typolog' ); ?></h3>

<ul class="cleanup-list unused-files-list">

<?php
	
$fonts = Typolog_Font_Query::get_all();

$used_files = [];

foreach ( $fonts as $font ) {
	if ( $font_files = Typolog_Font_Query::get_meta( $font->ID, '_font_files' ) ) {
		if ( is_array( $font_files ) && count( $font_files ) ) {
			$used_files = array_merge( $used_files, $font_files );
		}
	}
}

$files = get_posts( [
	"posts_per_page" => -1,
	"post_type" => "typolog_file"
] );

$unused_files = [];

foreach ( $files as $file ) {
	
	if ( false === array_search( $file->ID, $used_files ) ) {
		array_push( $unused_files, $file->ID ); ?>
			<li><label><input type="checkbox" name="delete_files[]" value="<?=$file->ID ?>" checked> <?=get_the_title( $file->ID ); ?><a href="<?=get_edit_post_link( $file->ID ) ?>" target="_blank" class="unused-link"></a></label></li>
		<?php
	}

}
	
?>

</ul>
<?php if ( count( $unused_files ) ) : ?>
<p><?php printf( __( 'Found %s unused font files.', 'typolog' ), count( $unused_files ) ); ?><br><input class="button delete-unused-files-button" type="submit" value="<?php _e( 'Delete Selected Font Files', 'typolog' ); ?>"></p>
<?php else : ?>
<p><?php _e( 'No unused font files found.', 'typolog' ); ?></p>
<?php endif; ?>

</form>
