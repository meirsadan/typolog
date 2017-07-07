<ul class="font-meta-fields">
	
<?php
		foreach ( $font_meta_fields as $font_meta_key => $font_meta_label ) :  
		
			$font_meta_value = get_post_meta( $font->ID, $font_meta_key, true );
		
?>

	<li class="font-meta-field-item">
		<label for="font_meta[<?=$font_meta_key ?>]"><?=$font_meta_label ?></label>
		<input type="text" id="font_meta[<?=$font_meta_key ?>]" name="font_meta[<?=$font_meta_key ?>]" value="<?=$font_meta_value ?>">
	</li>
	
<?php	endforeach; ?>

</ul>