<?php
	
header('Content-type: text/css');

if (array_key_exists('fonts', $_REQUEST)) {
	$fonts = explode(',', $_REQUEST['fonts']);
	foreach ($fonts as $font) {
		tl_fontface($font);
	}
} elseif (array_key_exists('families', $_REQUEST)) {
	$families = explode(',', $_REQUEST['families']);
	foreach ($families as $family) {
		tl_family_fontfaces($family);
	}
} elseif (array_key_exists('collections', $_REQUEST)) {
	$collections = explode(',', $_REQUEST['collections']);
	foreach ($collections as $collection) {
		tl_all_main_fontfaces($collection);
		tl_all_collections_main_fontfaces();
	}
} else {
	tl_all_main_fontfaces();
	tl_all_collections_main_fontfaces();
}

exit();