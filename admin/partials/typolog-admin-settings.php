<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
	
?>	
<div class="wrap">
	
	<h2 class="typolog-title"><?php _e('Typolog Settings', 'typolog'); ?></h2>

<?php 
	
	if ( isset($_REQUEST['success']) && isset($_REQUEST['user_id']) && wp_verify_nonce( $_REQUEST['user_id'], 'typolog-connect-wc' ) ) : ?>
	
	<div class="notice"><p><?php _e( 'Successfully updated WooCommerce credentials!', 'typolog' ); ?></p></div>

<?php endif; 
	
?>	
	
	<form method="post" action="options.php" class="typolog-settings-form">
		
	<?php 

	settings_fields( 'typolog-settings' );
	do_settings_sections( 'typolog-settings' );
	submit_button();
	
	?>
		
	</form>
	
	<script type="text/template" id="styles_dictionary_entry">
		<p class="styles-dictionary-entry"><input type="text" name="typolog_settings[styles_dictionary_keys][]" value="" placeholder="<?php _e( 'Key', 'typolog' ); ?>" /><input type="text" name="typolog_settings[styles_dictionary_values][]" value="" placeholder="<?php _e( 'Value', 'typolog' ); ?>" /><a href="#" class="button delete-entry"><?php _e( 'Delete', 'typolog' ); ?></a></p>
	</script>

	<script type="text/template" id="weights_map_entry">
		<p class="weights-map-entry"><input type="text" name="typolog_settings[weights_map_keys][]" value="" placeholder="<?php _e( 'Key', 'typolog' ); ?>" /><input type="text" name="typolog_settings[weights_map_values][]" value="" placeholder="<?php _e( 'Value', 'typolog' ); ?>" /><a href="#" class="button delete-entry"><?php _e( 'Delete', 'typolog' ); ?></a></p>
	</script>

	<script type="text/template" id="styles_map_entry">
		<p class="styles-map-entry"><input type="text" name="typolog_settings[styles_map_keys][]" value="" placeholder="<?php _e( 'Key', 'typolog' ); ?>" /><input type="text" name="typolog_settings[styles_map_values][]" value="" placeholder="<?php _e( 'Value', 'typolog' ); ?>" /><a href="#" class="button delete-entry"><?php _e( 'Delete', 'typolog' ); ?></a></p>
	</script>

	<script type="text/template" id="allowed_file_extensions_entry">
		<p class="file-extension-entry"><input type="text" name="typolog_settings[allowed_file_extensions][]" value="" /><a href="#" class="button delete-entry"><?php _e( 'Delete', 'typolog' ); ?></a></p>
	</script>

	<script type="text/template" id="strip_from_family_names_entry">
		<p class="strip-from-family-names-entry"><input type="text" name="typolog_settings[strip_from_family_names][]" value="" /><a href="#" class="button delete-entry"><?php _e( 'Delete', 'typolog' ); ?></a></p>
	</script>

</div>

