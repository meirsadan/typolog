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

<div class="wrap sizes">

<h1 class="typolog-page-title"><?php _e( 'Font Size Adjustments', 'typolog' ); ?></h1>

<div class="notice-wrap"></div>

<style><?php tl_all_fontfaces(); ?></style>

<div class="sizes-control">	
	<div class="size-adjustment-inputs">
		<span class="spinner"></span>
		<button class="button save-sizes" disabled><? _e( 'Save', 'typolog' ); ?></button>
		<input type="text" class="size-adjust">
		<button class="button add-size">+</button>
		<button class="button sub-size">-</button>
	</div>
	
</div>

<div class="sizes-display">

<?php 
	
	foreach ( $size_adjust_tree as $family ) :
	
	foreach ($family['fonts'] as $font) : 

?>
	
	<a 	href="#" 
		class="font-display family-<?=$family['id'] ?> font-<?=str_replace( ' ', '', strtolower( $font['webfont_name'] ) ) ?>"
		data-family-id="<?=$family['id'] ?>"
		data-font-id="<?=$font['id'] ?>"
		data-size-adjust="<?=$font['size_adjust'] ?>"
		style="font-family: '<?=$font['webfont_name'] ?>'; font-size: <?=$font['size_adjust'] ?>%;"><span><?=$family['name'] ?></span></a>
	
<?php 
	
	endforeach;	

	endforeach;
	
?>

</div>

</div>