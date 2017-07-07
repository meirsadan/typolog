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


if ( isset( $_POST['edit_family_fonts_nonce'] ) && wp_verify_nonce( $_POST['edit_family_fonts_nonce'], 'edit_family_fonts' ) ) {
	
	if ( ( $family_id = $_POST[ 'family_id' ] ) && ( $fonts_data = $_POST[ 'fonts_data' ] ) ) {
		
		$family_display_family_name = get_term( $family_id )->name;
		
		$family_family_name = get_term_meta( $family_id, '_family_name', true );
		
		$changed_fonts_counter = 0;
		
		foreach ( $fonts_data as $font_data ) {
			
			if ( $font_id = $font_data[ 'font_id' ] ) {
				
				$changed_fonts_counter++;
				
				$font_title = "";
				$webfont_name = "";
				
				if ( $font_data[ 'display_family_name' ] ) {
					update_post_meta( $font_id, '_display_family_name', $font_data[ 'display_family_name' ] );
					$font_title = $font_data[ 'display_family_name' ];
				} else {
					update_post_meta( $font_id, '_display_family_name', $family_display_family_name );
					$font_title = $family_display_family_name;
				}

				if ( $font_data[ 'display_style_name' ] ) {
					update_post_meta( $font_id, '_display_style_name', $font_data[ 'display_style_name' ] );
					$font_title .= " " . $font_data[ 'display_style_name' ];
				}
				
				if ( $font_title ) {
					wp_update_post( [
						'ID' => $font_id,
						'post_title' => $font_title
					 ] );
				}

				if ( $font_data[ 'family_name' ] ) {
					update_post_meta( $font_id, '_family_name', $font_data[ 'family_name' ] );
					$webfont_name = $font_data[ 'family_name' ];
				} else {
					update_post_meta( $font_id, '_family_name', $family_family_name );
					$webfont_name = $family_family_name;
				}

				if ( $font_data[ 'style_name' ] ) {
					update_post_meta( $font_id, '_style_name', $font_data[ 'style_name' ] );
					$webfont_name .= " " . $font_data[ 'style_name' ];
				}
				
				if ( $webfont_name ) {
					update_post_meta( $font_id, '_web_family_name', $webfont_name );
				}

				if ( $font_data[ 'font_weight' ] ) {
					update_post_meta( $font_id, '_font_weight', $font_data[ 'font_weight' ] );
				}

				if ( $font_data[ 'font_style' ] ) {
					update_post_meta( $font_id, '_font_style', $font_data[ 'font_style' ] );
				}
				
			}
			
		}
		
	}
	
}

$collections = Typolog_Collection_Query::get_all();

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap control">
	
<h1 class="typolog-page-title"><?php _e( 'Control Table', 'typolog' ); ?></h1>

<div class="notice-wrap regenerate-fonts-report">
<?php if ( $changed_fonts_counter ) : ?>
	<div class="notice updated"><p><?php printf( __( 'Successfully edited %s fonts.', 'typolog' ), $changed_fonts_counter ); ?></p></div>
<?php endif; ?>
</div>

<div class="edit-family-fonts-container"></div>

<form class="price-table-form" method="post">
	
	<?php wp_nonce_field( 'update_control_table_fields', 'update_control_table_nonce' ); ?>

<table class="admin-table control-table">
	<thead>
	<tr class="head-row">
		<td><?php _e( 'Item', 'typolog' ); ?></td>
		<td><?php _e( 'Product', 'typolog' ); ?></td>
		<td><?php _e( 'Files', 'typolog' ); ?></td>
		<td><?php _e( 'Downloads', 'typolog' ); ?></td>
	</tr>
	</thead>
	<tbody>
		
	<?php foreach ( $collections as $collection ) : ?>
	
	<tr class="collection-row">
		<td colspan="4"><h3 class="collection-title"><?=$collection->name ?></h3></td>
	</tr>
	
	<?php
		
		$families = Typolog_Family_Query::get_by_collection( $collection->term_id );
		
	?>
	
	<?php foreach ( $families as $family ) :
		
		$buy_link = null;
		
		if ( !$product_id = get_term_meta( $family->term_id, "_product_id", true ) ) {
			if ( !get_term_meta( $family->term_id, '_commercial', true ) ) {
				$buy_link = get_term_meta( $family->term_id, '_buy_link', true );
			}
		}
		$downloads = get_term_meta( $family->term_id, "_downloads", true );
		
	?>
	
	<tr class="family-row family-row-<?=$family->term_id ?>">
		<td class="family-cell family-item item-cell"><a href="#" class="family-toggle"></a><a href="<?=get_edit_term_link( $family->term_id, "typolog_family", "typolog_font" ) ?>" target="_blank"><?=$family->name ?></a><a href="#" class="action-link edit-fonts" data-type="family" data-id="<?=$family->term_id ?>" title="<?php _e( "Edit All Fonts in Family", "typolog" ); ?>"></a><span class="spinner"></span></td>
		<td class="family-cell family-product product-cell"><?php if ( $product_id ) : ?><a href="<?=get_edit_post_link( $product_id ) ?>" target="_blank" class="product-link"><?=get_the_title( $product_id ) ?></a><?php elseif ( $buy_link ) : ?><a href="<?=$buy_link ?>" target="_blank" class="buy-link"><?php _e('External Link', 'typolog' ) ?></a><?php else : ?><span class="no-product"></span><?php endif; ?><a href="#" class="action-link update-family-products" data-type="family" data-id="<?=$family->term_id ?>" title="<?php _e( "Update Products", "typolog" ); ?>"></td>
		<td class="family-cell family-files files-cell"></td>
		<td class="family-cell family-downloads downloads-cell">
			<ul class="files-list">
			<?php if ( is_array( $downloads ) ) foreach ( $downloads as $license_name => $download_file ) : ?>
				<li><a href="<?=$download_file[ 'file' ] ?>"><?=$license_name ?></a></li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	
	<?php
		
		$fonts = Typolog_Font_Query::get_by_family( $family->term_id );
		
	?>
		
	<?php foreach ( $fonts as $font ) : 
		
		$product_id = get_post_meta( $font->ID, "_product_id", true );
		$files = get_post_meta( $font->ID, "_font_files", true );
		$downloads = get_post_meta( $font->ID, "_downloads", true );

	?>

	<tr class="font-row font-row-<?=$font->ID ?>">
		<td class="font-cell font-item item-cell"><a href="<?=get_edit_post_link( $font->ID ) ?>" target="_blank" class="font-link"><?=get_the_title( $font->ID ) ?></a><span class="spinner"></span></td>
		<td class="font-cell font-product product-cell"><?php if ( $product_id ) : ?><a href="<?=get_edit_post_link( $product_id ) ?>" class="product-link" target="_blank"><?=get_the_title( $product_id ) ?></a><?php elseif ( $buy_link ) : ?><a href="<?=$buy_link ?>" target="_blank" class="buy-link"><?php _e('External Link', 'typolog' ) ?></a><?php else : ?><span class="no-product"></span><?php endif; ?></td>
		<td class="font-cell font-files files-cell">
			<ul class="files-list">
			<?php if ( is_array( $files ) ) foreach ( $files as $file_id ) : ?>
				<?php if ( false === get_post_status( $file_id ) ) : ?>
				<li><a href="#" class="font-file-link no-file"></a></li>
				<?php else : ?>
				<li><a href="<?=get_edit_post_link( $file_id ) ?>" class="font-file-link" title="<?=get_the_title( $file_id ) ?>"></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
			</ul>
		</td>
		<td class="font-cell font-downloads downloads-cell">
			<ul class="files-list">
			<?php if ( is_array( $downloads ) ) foreach ( $downloads as $license_name => $download_file ) : ?>
				<li><a href="<?=$download_file[ 'file' ] ?>"><?=$license_name ?></a></li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	
	<?php endforeach; ?>
	
	<?php endforeach; ?>
	
	<?php endforeach; ?>
	
	</tbody>
	
</table>

</form>
	

</div>