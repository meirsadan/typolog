<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Families {
	
	function __construct($options) {
	}
	
	function get_all_families() {
		return get_terms('typolog_family', array( "hide_empty" => false ));
	}
	
	function add_family($displayFamilyName, $familyName = '', $commercial = 1) {
		if (!$family = term_exists($displayFamilyName, 'typolog_family')) {
			$family = wp_insert_term($displayFamilyName, 'typolog_family', array( 'slug' => $familyName ));
			if (is_wp_error($family)) {
				return $family;
			}
			if ($familyName) {
				$this->set_family_name($family['term_id'], $familyName);
			}
			if ($commercial) {
				$this->set_family_commercial($family['term_id']);
			}
		}
		return $family['term_id'];
	}
	
	function delete_family($family_id) {
		return wp_delete_term($family_id, 'typolog_family');
	}
	
	function unset_family_meta($family_id, $field) {
		return delete_term_meta($family_id, $field);
	}
	
	function set_family_meta($family_id, $field, $value = '') {
		if (!$value) {
			return $this->unset_family_meta($family_id, $field);
		}
		if (!add_term_meta($family_id, $field, $value, true)) {
			return update_term_meta($family_id, $field, $value);
		}
		return true;
	}
	
	function set_family_name($family_id, $family_name) {
		return $this->set_family_meta($family_id, '_family_name', $family_name);
	}
	
	function set_family_product_id($family_id, $product_id = '') {
		return $this->set_family_meta($family_id, '_product_id', $product_id);
	}

	function set_family_variation_ids($family_id, $variation_ids = '') {
		return $this->set_family_meta($family_id, '_variation_ids', $variation_ids);
	}

	function set_family_price($family_id, $price = '', $license = '') {
		$price_field = ($license) ? '_price_' . $license : '_price';
		return $this->set_family_meta($family_id, $price_field, $price);
	}
	
	function set_family_downloads($family_id, $downloads) {
		return $this->set_family_meta($family_id, '_downloads', $downloads);
	}

	function set_family_attachments($family_id, $attachments) {
		return $this->set_family_meta($family_id, '_typolog_attachments', $attachments);
	}
	
	function set_family_commercial($family_id, $commercial = 1) {
		return $this->set_family_meta($family_id, '_commercial', $commercial);
	}
	
	function unset_family_commercial($family_id) {
		return $this->set_family_meta($family_id, '_commercial');
	}
	
	function get_family_meta($family_id, $field) {
		return get_term_meta($family_id, $field, true);
	}
	
	function get_family_name($family_id) {
		return $this->get_family_meta($family_id, '_family_name');
	}
	
	function get_family_product_id($family_id) {
		return $this->get_family_meta($family_id, '_product_id');
	}

	function get_family_variation_ids($family_id) {
		return $this->get_family_meta($family_id, '_variation_ids');
	}

	function get_family_price($family_id, $license = "") {
		$price_field = ($license) ? '_price_' . $license : '_price';
		return $this->get_family_meta($family_id, $price_field);
	}
	
	function get_family_downloads($family_id) {
		return $this->get_family_meta($family_id, '_downloads');
	}

	function get_family_attachments($family_id) {
		return $this->get_family_meta($family_id, '_typolog_attachments');
	}
	
	function is_family_commercial($family_id) {
		return $this->get_family_meta($family_id, '_commercial');
	}

}



