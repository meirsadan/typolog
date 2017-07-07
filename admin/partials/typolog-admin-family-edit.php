
	<form method="post">
		
		<?php wp_nonce_field( 'edit_family_fonts', 'edit_family_fonts_nonce' ); ?>
		
		<input type="hidden" name="family_id" value="<?=$family_id ?>">
		
		<table class="admin-table family-edit-table">
			<thead>
				<tr class="head-row">
					<td><?php _e( 'Display Family Name', 'typolog' ); ?></td>
					<td><?php _e( 'Display Style Name', 'typolog' ); ?></td>
					<td><?php _e( 'Family Name', 'typolog' ); ?></td>
					<td><?php _e( 'Style Name', 'typolog' ); ?></td>
					<td><?php _e( 'Font Weight (100–900)', 'typolog' ); ?></td>
					<td><?php _e( 'Font Style (normal/italic)', 'typolog' ); ?></td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $fonts as $index => $font_id ) : $f = new Typolog_Font( $font_id ); ?>
				<tr class="font-row show">
					<input type="hidden" name="fonts_data[<?=$index ?>][font_id]" value="<?=$font_id ?>">
					<td><input type="text" name="fonts_data[<?=$index ?>][display_family_name]" value="<?=$f->get_meta( '_display_family_name' ) ?>" placeholder="<?=$family_display_family_name ?>"></td>
					<td><input type="text" name="fonts_data[<?=$index ?>][display_style_name]" value="<?=$f->get_meta( '_display_style_name' ) ?>"></td>
					<td><input type="text" name="fonts_data[<?=$index ?>][family_name]" value="<?=$f->get_meta( '_family_name' ) ?>" placeholder="<?=$family_family_name ?> "></td>
					<td><input type="text" name="fonts_data[<?=$index ?>][style_name]" value="<?=$f->get_meta( '_style_name' ) ?>"></td>
					<td><input type="text" name="fonts_data[<?=$index ?>][font_weight]" value="<?=$f->get_meta( '_font_weight' ) ?>"></td>
					<td><input type="text" name="fonts_data[<?=$index ?>][font_style]" value="<?=$f->get_meta( '_font_style' ) ?>"></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<input class="button" type="submit" value="<?php _e( 'Save', 'typolog' ); ?>">

		<button class="button close-edit-family-fonts"><?php _e( 'Cancel', 'typolog' ); ?></button>
	
	</form>
