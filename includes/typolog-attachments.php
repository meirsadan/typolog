<?php

class Typolog_Attachments {
	
	private $obj;
	
	private $attachments;
	
	function __construct( $obj = null ) {
		
		if ( $obj ) {
			
			$this->load( $obj );
			
		}
		
	}
	
	function load( $obj ) {
		
		if ( method_exists( $obj , 'get_meta' ) ) {
			
			$this->obj = $obj;
			
			$this->attachments = $obj->get_meta( '_attachments' );
			
			if ( !$this->attachments ) {
				
				$this->attachments = [];
				
			}
			
		}
		
	}
	
	function load_font( $font_id ) {
		
		$this->load( new Typolog_Font( $font_id ) );
		
		
	}
	
	function load_family( $family_id ) {
		
		$this->load( new Typolog_Family( $family_id ) );
		
	}
	
	function load_license( $license_id ) {
		
		$this->load( new Typolog_License( $license_id ) );
		
	}
	
	function load_general() {
		
		$this->unload();
		
		$this->attachments = TypologOptions()->get( 'general_attachments' );
		
	}
	
	function unload() {
		
		unset( $this->obj );
		
		unset( $this->attachments );
		
	}
	
	function is_loaded() {
		
		return isset( $this->attachments );
		
	}
	
	function get() {
		
		return $this->attachments;
		
	}
	
	function set( $attachments = null ) {
		
		if ( !$attachments ) $attachments = [];
				
		$this->attachments = $attachments;
		
		if ( isset( $this->obj ) ) {
			
			$this->obj->set_meta( '_attachments', $this->attachments );
			
		} else {
			
			TypologOptions()->set( 'general_attachments', $this->attachments );
			
		}
		
	}
		
	function get_table() {
		
		$table = [];
		
		if ( is_array( $this->attachments ) ) {
			
			foreach ( $this->attachments as $attachment_id ) {
				
				$attachment = get_post( $attachment_id );
				
				$table[] = [
					
					'id' => $attachment->ID,
					
					'url' => $attachment->guid,
					
					'name' => $attachment->post_title
					
				];
				
			}
			
		}
			
		return $table;
		
	}
	
}