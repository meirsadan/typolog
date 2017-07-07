<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Package_Factory {
	
	private $package_types;
	
	function __construct($types = null) {
		$this->package_types = new Typolog_Package_Types();
	}
	
	function generate_font_package($font_id, $type) {
		$files = $this->package_types->get_files_by_type(get_post_meta($font_id, '_font_files', true), $type);
		return $files;
	}
	
	function generate_font_packages($font_id) {
		$res = array();
		foreach ($this->package_types->get_types() as $type) {
			$res[$this->package_types->get_type_name($type)] = $this->generate_font_package($font_id, $type);
		}
		return $res;
	}
	
	function get_font_package($font_id, $type) {
		$packages = get_post_meta($font_id, '_font_packages', true);
		return (is_array($packages[$type])) ?  $packages[$type] :  $this->generate_font_package($font_id, $type);
	}

	function get_font_packages($font_id) {
		$packages = get_post_meta($font_id, '_font_packages', true);
		return (is_array($packages)) ?  $packages :  $this->generate_font_packages($font_id);
	}
	
	function reset_font_packages($font_id) {
		return update_post_meta($font_id, '_font_packages', $this->generate_font_packages($font_id));
	}
	
	function drop_file_to_packages($filename, $font_id) {
		$type = $this->package_types->get_type_by_file($filename);
		$font_packages = get_post_meta($font_id, '_font_packages', true);
		array_push($font_packages[$this->package_types->get_type_name($type)], $filename);
		return update_post_meta($font_id, '_font_packages', $font_packages);
	}
	
	function generate_family_package($family_id, $type) {
		$family_package = array();
		$fonts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => 'typolog_font',
			'tax_query' => array(
				'taxonomy' => 'typolog_family',
				'field' => 'term_id',
				'terms' => $family_id
			)
		));
		foreach ($fonts as $font) {
			$family_package = array_merge($family_package, $this->get_font_package($font->ID, $type));
		}
		return $family_package;
	}
	
	function generate_family_packages($family_id) {
		$res = array();
		$fonts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => 'typolog_font',
			'tax_query' => array(
				'taxonomy' => 'typolog_family',
				'field' => 'term_id',
				'terms' => $family_id
			)
		));
		foreach ($fonts as $font) {
			$res = array_merge_recursive($this->get_font_packages($font->ID), $res);
		}
		return $res;
	}
	
	function update_font_packages($font_id, $packages) {
		return update_post_meta($font_id, '_font_packages', $packages);
	}

	function remove_file_from_packages($filename, $font_id) {
		$new_font_packages = array();
		$font_packages = get_post_meta($font_id, '_font_packages', true);
		foreach ($font_packages as $type => $font_files) {
			$new_font_packages[$type] = array();
			foreach ($font_files as $font_file) {
				if ($font_file !== $filename) {
					array_push($new_font_packages[$type], $font_file);
				}
			}
		}
		return update_post_meta($font_id, '_font_packages', $new_font_packages);
	}
	
	function get_packages_table_var($font_id) {
		$table_var = array();
		$font_packages = $this->get_font_packages($font_id);
		$types = $this->package_types->get_types('post_name');
		foreach ($types as $type) {
			$table_var[$type] = array(
				"label" => $this->package_types->get_type_label($type),
				"files" => (is_array($font_packages[$type])) ? $font_packages[$type] : array()
			);
		}
		return $table_var;
	}
	
}


