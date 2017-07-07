<ul dir="ltr" id="font_file_list">
	
<?php foreach ( $packages_table as $font_file ) : ?>
		
	<li class="font-file-line" data-id="<?=$font_file['id'] ?>">
		
		<a href="<?=get_edit_post_link($font_file['id']) ?>" class="filename" data-id="<?=$font_file['id'] ?>"><?=$font_file['title'] ?></a>
		
		<span class="actions">
		
			<span class="spinner"></span>
		
			<?php foreach ( $font_file['licenses'] as $license => $is_license ) : ?>
			
				<span class="license-select"><input type="checkbox" class="checkbox license-checkbox" id="license_<?=$font_file['id'] ?>_<?=$license ?>" data-license="<?=$license ?>" <?php checked(1, $is_license); ?>> <label class="license-label" for="license_<?=$font_file['id'] ?>_<?=$license ?>"><?=$license ?></label></span>
		
			<?php endforeach; ?>

		<button class="button delete-font-file" data-font-id="<?=$font_id ?>" data-file-id="<?=$font_file['id'] ?>">Delete</button></span>
		
	</li>

<?php endforeach; ?>

</ul>
