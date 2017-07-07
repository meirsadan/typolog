<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Package_Types {
	
	function __construct($types = null) {
		
	}
	
	static function get_package_type_id($type_slug) {
		if ( is_array( $the_type = get_posts( array('name' => $type_slug, 'post_type' => 'typolog_package_type', 'posts_per_page' => 1) ) ) ) {
			return $the_type[0];
		} 
		return false;
	}
	
	function get_exts($type_id) {
		$type_id = (is_numeric($type_id)) ? $type_id : $this->get_package_type_id($type_id);
		if (is_array($the_exts = get_the_terms($type_id, 'typolog_file_extension'))) {
			return array_map(function($t) {
				return is_object($t) ? $t->name : $t['name'];
			}, $the_exts);
		}
		return false;
	}
	
	function get_types($column = 'ID') {
		if (is_array($the_types = get_posts(array('post_type' => 'typolog_package_type', 'order' => 'ASC', 'posts_per_page' => -1)))) {
			return array_map(function($t) use ($column) {
				return is_object($t) ? $t->$column : $t[$column];
			}, $the_types);
		}
		return false;
	}
	
	function get_type_label($type_id) {
		if ( is_numeric($type_id) ) {
			return get_the_title($type_id);		
		} else {
			if ( $type_id = $this->get_package_type_id($type_id) ) {
				return get_the_title($type_id);
				
			}
		}
	}
	
	function get_type_name($type_id) {
		return get_post($type_id)->post_name;
	}
	
	function get_files_by_type($files, $type_id) {
		$res = array();
		$exts = $this->get_exts($type_id);
		foreach ($files as $file) {
			foreach ($exts as $ext) {
				if (strripos($file, $ext) == strlen($file) - strlen($ext)) {
					array_push($res, $file);
				}
			}
		}
		return $res;
	}
	
	function get_type_by_file($filename) {
		foreach ($this->get_types() as $type_id) {
			$exts = $this->get_exts($type_id);
			foreach ($exts as $ext) {
				if (strripos($filename, $ext) == strlen($filename) - strlen($ext)) {
					return $type_id;
				}
			}
		}
		return false;
	}
	
}


