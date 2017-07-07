<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Licenses {
	
	function __construct($types = null) {
	}
	
	function get_all_licenses() {
		return get_terms('typolog_license', array(
			'hide_empty' => false
		));
	}
	
	function get_the_license($file_id) {
		if (is_array($license = get_the_terms($file_id, 'typolog_license'))) {
			return $license[0];
		}
		return false;
	}
	
	function get_file_licenses($file_id) {
		if (is_array($licenses = get_the_terms($file_id, 'typolog_license'))) {
			return $licenses;
		}
		return false;
	}

	function get_font_package($file_ids, $license_name) {
		$package = array();
		foreach ($file_ids as $file_id) {
			if ($licenses = $this->get_file_licenses($file_id)) {
				foreach ($licenses as $license) {
					if (strtolower($license->slug) == strtolower($license_name))
						$package[] = $file_id;
				}
			}
		}
		return $package;
	}
	
	function get_font_packages($file_ids) {
		$packages = array();
		foreach ($file_ids as $file_id) {
			if ($licenses = $this->get_file_licenses($file_id)) {
				foreach ($licenses as $license) {
					$packages[$license->slug][] = $file_id;
				}
			}
		}
		return $packages;
	}
	
	function get_license_name($license_id) {
		if ($license = $this->get_term($license_id, 'typolog_license')) {
			return $license->slug;
		}
		return false;
	}

	function get_license_display_name($license_id) {
		if ($license = $this->get_term($license_id, 'typolog_license')) {
			return $license->name;
		}
		return false;
	}

	function reset_font_packages($file_ids) {
		$packages = array();
		foreach ($file_ids as $file_id) {
			if ($license = $this->determine_license(get_the_title($file_id))) {
				wp_set_object_terms($file_id, $license->term_id, 'typolog_license');
				$packages[$license->slug][] = $file_id;
			}
		}
		return $packages;
	}
	
	function update_font_packages($files) {
		foreach (array_keys($files) as $file_id) {
			if (is_wp_error($res = $this->set_licenses($file_id, $files[$file_id]))) {
				return $res;
			}
		}
		return true;
	}

	function get_packages_table_var($file_ids) {
		$all_licenses = $this->get_all_licenses();
		$all_licenses = array_get_column($all_licenses, 'slug');
		$table_var = array();
		foreach ($file_ids as $file_id) {
			$licenses = $this->get_file_licenses($file_id);
			if (is_array($licenses)) {
				$licenses = array_get_column($licenses, 'slug');
			} else {
				$licenses = array();
			}
			file_put_contents('table-var.txt', print_r($licenses, true));
			$licenses_arr = array();
			foreach ($all_licenses as $license) {
				if (array_search($license, $licenses) !== false) {
					$licenses_arr[$license] = 1;
				} else {
					$licenses_arr[$license] = 0;
				}
			}
			array_push($table_var, array(
				"id" => $file_id,
				"title" => get_the_title($file_id),
				"licenses" => $licenses_arr
			));
		}
		return $table_var;
/*
		$table_var = array();
		$font_packages = $this->get_font_packages($file_ids);
		$licenses = $this->get_all_licenses();
		foreach ($font_packages as &$font_package) {
			foreach ($font_package as &$font_file) {
				$font_file = array(
					"id" => $font_file,
					"title" => get_the_title($font_file)	
				);
			}
		}
		foreach ($licenses as $license) {
			$table_var[$license->name] = array(
				"label" => $license->name,
				"files" => (isset($font_packages[$license->name])) ? $font_packages[$license->name] : array()
			);
		}
		return $table_var;
*/
	}

	function set_licenses($file_id, $license_ids = array()) {
		return wp_set_object_terms($file_id, $license_ids, 'typolog_license');
	}
	
	function set_license($file_id, $license_id = null) {
		if (!$license_id) {
			if ($license = $this->determine_license(get_the_title($file_id))) {
				$license_id = $license->term_id;
			} else {
				return false;
			}
		}
		return $this->set_licenses($file_id, array($license_id));
	}

	function determine_license($filename) {
		$licenses = $this->get_all_licenses();
		foreach ($licenses as $license) {
			if ($_license_exts = get_term_meta($license->term_id, '_extensions', true)) {
				$license_exts = explode('|', $_license_exts);
				foreach ($license_exts as $ext) {
					if (strripos($filename, $ext) == strlen($filename) - strlen($ext)) {
						return $license;
					}
				}
			}
		}
		return false;
	}

	function unset_license_meta($license_id, $field) {
		return delete_term_meta($license_id, $field);
	}
	
	function set_license_meta($license_id, $field, $value = '') {
		if (!$value) {
			return $this->unset_license_meta($license_id, $field);
		}
		if (!add_term_meta($license_id, $field, $value, true)) {
			return update_term_meta($license_id, $field, $value);
		}
		return true;
	}

	function set_license_attachments($license_id, $attachments) {
		return $this->set_license_meta($license_id, '_typolog_attachments', $attachments);
	}

	function set_license_base_price($license_id, $base_price) {
		return $this->set_license_meta($license_id, '_base_price', $base_price);
	}

	function get_license_meta($license_id, $field) {
		return get_term_meta($license_id, $field, true);
	}

	function get_license_attachments($license_id) {
		return $this->get_license_meta($license_id, '_typolog_attachments');
	}

	function get_license_base_price($license_id) {
		return $this->get_license_meta($license_id, '_base_price');
	}

}

function get_license_id($license_name) {
	$licenses = get_terms('typolog_license', array( 'slug' => $license_name ));
	if (!is_array($licenses)) {
		return false;
	}
	return $licenses[0]->term_id;
}

function get_license_base_price($license_id) {
	if (!is_numeric($license_id)) {
		$license_id = get_license_id($license_id);
	}
	return get_term_meta($license_id, '_base_price', true);
}
