<div class="typolog-pdf" data-name="pdf">

<?php if ($pdf) : ?>

	<input type="hidden" id="pdf_id" name="pdf" value="<?=$pdf->ID ?>">
	<a href="<?=$pdf->guid ?>" target="_blank" class="pdf_link"><?=$pdf->post_title ?></a><br>
	<a href="#" class="remove-pdf-link" data-family-id="<?=$family->get('term_id') ?>"><?php _e('Remove PDF'); ?></a>
	
<?php else : ?>

	<input type="hidden" id="pdf_id" name="pdf" value="">
	<a href="" target="_blank" class="pdf_link"></a>
	<?php if ($family) : ?>
	<a href="#" class="remove-pdf-link" style="display: none;" data-family-id="<?=$family->get('term_id') ?>" title="<?php _e('Remove PDF'); ?>">&times;</a>
	<?php endif; ?>

<?php endif; ?>

</ul>

<a href="#" class="button upload-pdf-button" <?php if ($pdf) { ?>style="display: none;"<?php } ?>><?php _e('Add PDF'); ?></a>
