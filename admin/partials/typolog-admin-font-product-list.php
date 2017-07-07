<ul class="font-product-list">
	
<?php	foreach ($font_products as $font_product_type => $font_product_id) : 
	
		if ($font_product_id) :
?>

	<li class="font-product-item <?=$font_product_type ?>">
		<span class="product-name">
			<a href="<?=get_edit_post_link($font_product_id) ?>"><?=get_the_title($font_product_id) ?></a>
		</span>
		<span class="product-actions">
			<a href="<?=get_delete_post_link($font_product_id) ?>" class="button delete"><?php _e('Delete', 'typolog') ?></a>
		</span>
	</li>
	
<?php	
		else:		
?>

	<li class="font-product-item <?=$font_product_type ?>"><?=sprintf(__('No %s product for font.', 'typolog'), $font_product_type) ?></li>

<?php
		endif;
		
		endforeach;

?>

</ul>