<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Fonts {
	
	private $families;

	private $meta_fields;
	
	function __construct($options) {
		$this->families = new Typolog_Families($options);

		$this->meta_fields = [
			'family_name' => [
				'id' => 'family_name',
				'json_id' => 'familyName',
				'name' => __('Family Name', 'typolog')
			],
			'style_name' => [
				'id' => 'style_name',
				'json_id' => 'styleName',
				'name' => __('Style Name', 'typolog')
			],
			'display_family_name' => [
				'id' => 'display_family_name',
				'json_id' => 'displayFamilyName',
				'name' => __('Family Name for Display', 'typolog')
			],
			'display_style_name' => [
				'id' => 'display_style_name',
				'json_id' => 'displayStyleName',
				'name' => __('Style Name for Display', 'typolog')
			],
			'web_family_name' => [
				'id' => 'web_family_name',
				'json_id' => 'webFamilyName',
				'name' => __('Family Name for Webfont Use', 'typolog')
			],
			'font_weight' => [
				'id' => 'font_weight',
				'json_id' => 'fontWeight',
				'name' => __('Font Weight (100–900)', 'typolog')
			],
			'font_style' => [
				'id' => 'font_style',
				'json_id' => 'fontStyle',
				'name' => __('Font Style (normal/italic/oblique)', 'typolog')
			]
		];
	}
	
	function get_all_fonts() {
		return get_posts(array(
			'post_type' => 'typolog_font',
			'posts_per_page' => -1
		));
	}
	
	function add_family_to_font($font_id, $displayFamilyName, $familyName, $commercial = 1) {
		$family_id = $this->families->add_family($displayFamilyName, $familyName, $commercial);
		if ((is_wp_error($family_id)) || (!$family_id)) {
			return $family_id;
		}
		return wp_set_object_terms($font_id, (int) $family_id, 'typolog_family');
	}
	
	function setup_meta_fields_from_json($font_data) {
		$meta_field_values = array();
		foreach ($this->meta_fields as $meta_field) {
			if ($value = $font_data[$meta_field['json_id']]) {
				$meta_field_values['_' . $meta_field['id']] = $value;
			}
		}
		return $meta_field_values;
	}

	function get_font_meta_fields($font_id) {
		$meta_field_values = array();
		foreach ($this->meta_fields as $meta_field) {
			if ($value = $this->get_font_meta($font_id, '_' . $meta_field['id'])) {
				$meta_field_values[] = [
					'id' => $meta_field['id'],
					'name' => $meta_field['name'],
					'value' => $value
				];
			}
		}
		return $meta_field_values;
	}
	
	function set_font_meta_fields($font_id, $meta_field_values) {
		foreach ($meta_field_values as $meta_field_key => $meta_field_value) {
			$this->set_font_meta($font_id, '_' . $meta_field_key, $meta_field_value);
		}
		return true;
	}
	
	function add_font($font_data) {
		$meta_input = $this->setup_meta_fields_from_json($font_data);
		$meta_input['_font_files'] = $font_data['files'];
		return wp_insert_post(array(
			'post_type' => 'typolog_font',
			'post_status' => 'publish',
			'post_title' => $font_data['displayFamilyName'] . ' ' . $font_data['displayStyleName'],
			'post_name' => strtolower($font_data['familyName'] . $font_data['styleName']),
			'meta_input' => $meta_input
		));
	}
	
	function delete_font($font_id) {
		return wp_delete_post($font_id, true);
	}
	
	function unset_font_meta($font_id, $field) {
		return delete_post_meta($font_id, $field);
	}
	
	function set_font_meta($font_id, $field, $value = '') {
		if (!$value) {
			return $this->unset_font_meta($font_id, $field);
		}
		if (!add_post_meta($font_id, $field, $value, true)) {
			return update_post_meta($font_id, $field, $value);
		}
		return true;
	}
	
	function set_font_product_id($font_id, $product_id = '') {
		return $this->set_font_meta($font_id, '_product_id', $product_id);
	}

	function set_font_variation_ids($font_id, $variation_ids = '') {
		return $this->set_font_meta($font_id, '_variation_ids', $variation_ids);
	}

	function set_font_price($font_id, $price = '', $license = '') {
		$price_field = ($license) ? '_price_' . $license : '_price';
		return $this->set_font_meta($font_id, $price_field, $price);
	}
	
	function set_font_downloads($font_id, $downloads = '') {
		return $this->set_font_meta($font_id, '_downloads', $downloads);
	}

	function set_font_attachments($font_id, $attachments) {
		return $this->set_font_meta($font_id, '_typolog_attachments', $attachments);
	}
	
	function get_font_meta($font_id, $field) {
		return get_post_meta($font_id, $field, true);
	}
	
	function get_font_product_id($font_id) {
		return $this->get_font_meta($font_id, '_product_id');
	}

	function get_font_variation_ids($font_id) {
		return $this->get_font_meta($font_id, '_variation_ids');
	}

	function get_font_price($font_id, $license = "") {
		$price_field = ($license) ? '_price_' . $license : '_price';
		return $this->get_font_meta($font_id, $price_field);
	}
	
	function get_font_downloads($font_id) {
		return $this->get_font_meta($font_id, '_downloads');
	}

	function get_font_attachments($font_id) {
		return $this->get_font_meta($font_id, '_typolog_attachments');
	}

	function get_font_files($font_id) {
		return $this->get_font_meta($font_id, '_font_files');
	}
	
	function set_font_files($font_id, $files) {
		return $this->set_font_meta($font_id, '_font_files', $files);
	}
	
	function get_font_web_family_name($font_id) {
		return $this->get_font_meta($font_id, '_web_family_name');
	}

	function get_font_fontface($font_id) {
		return $this->get_font_meta($font_id, '_fontface');
	}
	
	function set_font_fontface($font_id, $fontface) {
		return $this->set_font_meta($font_id, '_fontface', $fontface);
	}
	
	function get_font_display_style_name($font_id) {
		return $this->get_font_meta($font_id, '_display_style_name');
	}

	function add_file_to_font($font_id, $file_id) {
		if (is_array($font_files = $this->get_font_files($font_id))) {
			array_push($font_files, $file_id);
		} else {
			$font_files = array($file_id);
		}
		return $this->set_font_files($font_id, $font_files);
	}
	
	function get_font_family($font_id) {
		if (is_array($families = get_the_terms($font_id, 'typolog_family'))) {
			file_put_contents('font-families.txt', print_r($families, true));
			return $families[0]->term_id;
		}
		return false;
	}

	function get_font_family_obj($font_id) {
		if (is_array($families = get_the_terms($font_id, 'typolog_family'))) {
			file_put_contents('font-families.txt', print_r($families, true));
			return $families[0];
		}
		return false;
	}
	
	function get_fonts_by_family($family_id) {
		if (is_array($fonts = get_posts(array(
						'post_type' => 'typolog_font', 
						'posts_per_page' => -1,
						'tax_query' => array(
							array(
								'taxonomy' => 'typolog_family', 
								'field' => 'term_id',
								'terms' => array( $family_id )
							)
						)
					)
			))) {
			return $fonts;
		}
		return false;
	}
	
	function get_families_by_collection($collection) {
		$families = array();
		if ($collection) {
			if (is_numeric($collection)) {
				$tax_query = array(
					array(
						'taxonomy' => 'typolog_collection',
						'field' => 'term_id',
						'terms' => $collection
					)
				);
			} else {
				$tax_query = array(
					array(
						'taxonomy' => 'typolog_collection',
						'field' => 'name',
						'terms' => $collection
					)
				);
			}
			$fonts = get_posts(array(
				'post_type' => 'typolog_font',
				'posts_per_page' => -1,
				'tax_query' => $tax_query
			));
			foreach ($fonts as $font) {
				$family = $this->get_font_family_obj($font->ID);
				if ($family && !array_search($family, $families)) {
					$families[] = $family;
				}
			}
		}
		return $families;
	}

	function get_all_families($collection = null) {
		if ($collection)
			return $this->get_families_by_collection($collection);
		return $this->families->get_all_families();
	}

	function set_family_product_id($family_id, $product_id = '') {
		return $this->families->set_family_product_id($family_id, $product_id);
	}

	function set_family_variation_ids($family_id, $variation_ids = '') {
		return $this->families->set_family_variation_ids($family_id, $variation_ids);
	}

	function get_family_product_id($family_id) {
		return $this->families->get_family_product_id($family_id);
	}

	function get_family_variation_ids($family_id) {
		return $this->families->get_family_variation_ids($family_id);
	}
	
	function set_family_downloads($family_id, $downloads = '') {
		return $this->families->set_family_downloads($family_id, $downloads);
	}
	
	function get_family_downloads($family_id) {
		return $this->families->get_family_downloads($family_id);
	}

	function set_family_attachments($family_id, $attachments) {
		return $this->families->set_family_attachments($family_id, $attachments);
	}
	
	function get_family_attachments($family_id) {
		return $this->families->get_family_attachments($family_id);
	}
	
	function delete_family($family_id) {
		return $this->families->delete_family($family_id);
	}
	
	function generate_single_font_package_name($font_id) {
		return str_replace(' ', '', $this->get_font_meta($font_id, '_family_name')) . str_replace(' ', '', $this->get_font_meta($font_id, '_style_name'));
	}
	
	function generate_family_package_name($family_id) {
		return str_replace(' ', '', $this->families->get_family_meta($family_id, '_family_name'));
	}
	
	function get_font_size_adjust($font_id) {
		$size_adjust = $this->get_font_meta($font_id, '_size_adjust');
		return ($size_adjust) ? $size_adjust : '100';
	}

	function set_font_size_adjust($font_id, $size_adjust = 100) {
		return $this->set_font_meta($font_id, '_size_adjust', $size_adjust);
	}
		
	function is_font($font_id) {
		$font = get_post($font_id);
		if ('typolog_font' == $font->post_type) { // make sure this is a font
			return true;
		}
		return false;
	}

	function delete_product_relationships($product_id) {
		if ($fonts = get_posts([ 'post_type' => 'typolog_font', 'meta_query' => [ 'key' => '_product_id', 'value' => $product_id ] ])) {
			foreach ($fonts as $font) {
				$this->set_font_product_id($font->ID);
			}
		}
		if ($families = get_terms('typolog_family', [ 'meta_query' => [ 'key' => '_product_id', 'value' => $product_id ] ])) {
			foreach ($families as $family) {
				$this->set_family_product_id($family->term_id);
			}
		}
		return $product_id;
	}
	
	function is_font_commercial($font_id) {
		$families = get_the_terms($font_id, 'typolog_family');
		if (!is_array($families) || (empty($families))) {
			return true;
		}
		foreach ($families as $family) {
			if ($this->families->is_family_commercial($family->term_id)) {
				return true;
			}
		}
		return false;
	}
	
	function is_family_commercial($family_id) {
		return $this->families->is_family_commercial($family_id);
	}
	
}



