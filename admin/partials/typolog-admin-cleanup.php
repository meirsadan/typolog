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

$cleanup_pages = [ "products" => __( "Products", "typolog" ), "font-files" => __( "Font Files", "typolog" ), "fonts" => __( "Fonts", "typolog" ), "downloads" => __( "Downloads", "typolog" ), "original-files" => __( "Original Font Files", "typolog" ) ];

if ( !$cleanup_page = $_REQUEST[ "cleanup_page" ] ) {
	$cleanup_page = "products";
}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap regenerate-products">
	
<h1 class="typolog-page-title"><?php _e( 'Cleanup', 'typolog' ); ?></h1>

<ul class="cleanup-nav">
	<?php foreach ( $cleanup_pages as $page_slug => $page_name ) : ?>
	<li<?=( $page_slug == $cleanup_page ) ? ' class="active"' : '' ?>><a href="admin.php?page=typolog-cleanup&cleanup_page=<?=$page_slug ?>"><?=$page_name ?></a></li>
	<?php endforeach; ?>
</ul>

<div class="notice-wrap"></div>	

<?php
	
include plugin_dir_path( __FILE__ ) . 'typolog-admin-cleanup-' . $cleanup_page . '.php';

?>

</div>