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

<div class="wrap generator">

<h1 class="typolog-page-title"><?php _e('Catalog Generator', 'typolog'); ?></h1>

<div class="notice typolog-notice generator-notice">
	<p><?php _e('Drop or select files in the files drop zone, edit the catalog and click "Upload Files"', 'typolog'); ?></p>
</div>

<form id="generator_dropzone" class="dnd-zone">
	<input type="hidden" name="action" value="typolog_upload_font">
	<span class="dnd-label"><?php _e('Drop font files here', 'typolog'); ?></span>
</form>

<div class="generator-controls">
	<div class="generator-controls-section generator-controls-section-actions">
		<button class="button button-add-font"><?php _e('Add Empty Font', 'typolog'); ?></button>
	</div>
	<div class="generator-controls-section generator-controls-section-prepare">
		<input type="checkbox" id="commercial_all" checked> <label for="commercial_all"><?php _e( 'Create Font Products', 'typolog' ); ?></label>
		<span class="sep"></span>
		<label for="collection_all"><?php _e( 'Add to collection:', 'typolog' ); ?></label>
		<?php wp_dropdown_categories( [
				'name' => 'collection_all',
				'id' => 'collection_all',
				'taxonomy' => 'typolog_collection',
				'hide_empty' => false
			]);
		?>
		<span class="spinner"></span>
		<button class="button-primary button-upload-generator-files"><?php _e('Upload Files', 'typolog'); ?></button>
	</div>
</div>

<ul class="generated-fonts-list">
</ul>

<script type="text/template" id="font-item-template">
	<div class='font-details'>
		<div class='font-names-section display-names'>
			<label>
				<span class='font-names-section-label-text'><?php _e('Display Names:', 'typolog'); ?></span>
				<div class='text-field-wrapper'>
					<input type='text' class='display-family-name' name='displayFamilyName' value='<%- displayFamilyName %>'>
				</div>
			</label>
			<div class='text-field-wrapper'>
				<input type='text' class='display-style-name' name='displayStyleName' value='<%- displayStyleName %>'>
			</div>
		</div>
		<div class='font-names-section font-names'>
			<label>
				<span class='font-names-section-label-text'><?php _e('Font Names:', 'typolog'); ?></span>
				<div class='text-field-wrapper'>
					<input type='text' class='family-name' name='familyName' value='<%- familyName %>'>
				</div>
			</label>
			<div class='text-field-wrapper'>
				<input type='text' class='style-name' name='styleName' value='<%- styleName %>'>
			</div>
		</div>
	</div>
	<ul class='files'>
	</ul>
	<div class="toolbox">
		<a href="#" class="toolbox-button remove-font"></a>
		<a href="#" class="toolbox-button sort-handle"></a>
	</div>
	<div class='font-notice'></div>
</script>

<script type="text/template" id="file-item-template">
	<li class="file-item">
		<span class="file-name"><%= name %></span>
		<a href="#" class="remove-file"></a>
	</li>
</script>

<script type="text/template" id="uploading-files-message"><?php _e( "Uploading files...", "typolog" ); ?></script>
<script type="text/template" id="generating-message"><?php _e( "Generating", "typolog" ); ?></script>
<script type="text/template" id="done-message"><?php _e( "Done!", "typolog" ); ?></script>
<script type="text/template" id="error-message"><?php _e( "Error:", "typolog" ); ?></script>
<script type="text/template" id="unknown-error-message"><?php _e( "Unknown error", "typolog" ); ?></script>
<script type="text/template" id="no-data-error-message"><?php _e( "No data", "typolog" ); ?></script>

<script type="text/template" id="remove-font-prompt"><?php _e('Removing this font will remove all associated files. Do you wish to proceed?', 'typolog'); ?></script>
<script type="text/template" id="add-font-prompt"><?php _e('Enter family name:', 'typolog'); ?></script>

<script type="text/template" id="family-exists"><p><?php _e( "This font family already exists. This font will be added to the family.", "typolog" ); ?></p></script>
<script type="text/template" id="font-exists"><p><?php _e( "This font already exists. Its files will be overwritten by the files in this batch.", "typolog" ); ?></p></script>

<script type="text/javascript">
	<?php
		$font_names = [];
		$family_names = [];
		$families = Typolog_Family_Query::get_all();
		foreach( $families as $family ) {
			array_push( $family_names, Typolog_Family_Query::get_meta( $family->term_id, "_family_name" ) );
			$fonts = Typolog_Family_Query::get_font_order( $family->term_id );
			foreach ( $fonts as $font ) {
				array_push( $font_names, [ "family" => Typolog_Font_Query::get_meta( $font, '_family_name' ), "style" => Typolog_Font_Query::get_meta( $font, '_style_name' ) ] );
			}
		}
	?>
	FontefFamilyNames = <?=json_encode( $family_names ) ?>;
	FontefFontNames = <?=json_encode( $font_names ) ?>;
</script>

</div>