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

<form class="unused-fonts" method="post">
<input type="hidden" name="action" value="delete_fonts">

<h3><?php _e( 'Unused Fonts', 'typolog' ); ?></h3>

<ul class="cleanup-list unused-fonts-list">

<?php
	
$fonts = Typolog_Font_Query::get_all();

$unused_fonts = [];

foreach ( $fonts as $font ) {
	if ( false === Typolog_Font_Query::get_family( $font->ID ) ) {
		array_push( $unused_fonts, $font->ID ); ?>
			<li><label><input type="checkbox" name="delete_fonts[]" value="<?=$font->ID ?>" checked> <?=get_the_title( $font->ID ); ?><a href="<?=get_edit_post_link( $font->ID ) ?>" target="_blank" class="unused-link"></a></label></li>
		<?php
	}
}

?>

</ul>
<?php if ( count( $unused_fonts ) ) : ?>
<p><?php printf( __( 'Found %s unused fonts.', 'typolog' ), count( $unused_fonts ) ); ?><br><input class="button delete-unused-fonts-button" type="submit" value="<?php _e( 'Delete Selected Fonts', 'typolog' ); ?>"></p>
<?php else : ?>
<p><?php _e( 'No unused fonts found.', 'typolog' ); ?></p>
<?php endif; ?>

</form>
