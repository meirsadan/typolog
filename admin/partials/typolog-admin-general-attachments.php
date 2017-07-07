<ul class="typolog-attachment-list" data-name="typolog_settings[general_attachments]">

<?php foreach ($attachments as $attachment) : ?>

	<li class="typolog-attachment-item">
		<input type="checkbox" name="typolog_settings[general_attachments][]" value="<?=$attachment['id'] ?>" checked>
		<a href="<?=$attachment['url'] ?>" target="_blank"><?=$attachment['name'] ?></a>
	</li>

<?php endforeach; ?>

</ul>

<a href="#" class="button upload-attachment-button"><?php _e('Add Attachment'); ?></a>
