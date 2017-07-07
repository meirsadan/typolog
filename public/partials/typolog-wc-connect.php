<?php

$post_keys = json_decode( file_get_contents('php://input'), true );

if ( ( isset( $post_keys['key_permissions'] ) ) && ($post_keys['key_permissions'] == 'read_write') ) {
	
	if ( isset( $post_keys['consumer_key'] ) && isset( $post_keys['consumer_secret'] ) ) {
		
		TypologOptions()->set( 'wc_consumer_key', $post_keys['consumer_key'] );
		TypologOptions()->set( 'wc_consumer_secret', $post_keys['consumer_secret'] );

		typolog_log( 'wc_success', file_get_contents('php://input') );
		exit();
		
	}

	typolog_log( 'wc_consumer_fail', file_get_contents('php://input') );
	exit();

}

typolog_log( 'wc_permissions_fail', file_get_contents('php://input') );
exit();

