<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Typolog_Font_Files {
	
	private $fonts_url;
	
	private $fonts_path;
	
	private $products_url;
	
	private $products_path;
	
	private $licenses;

	function __construct($options) {
		$upload_dir = wp_upload_dir();
		$this->fonts_path = $upload_dir['basedir'] . '/' . $options['fonts_dir'] . '/';
		$this->fonts_url = $upload_dir['baseurl'] . '/' . $options['fonts_dir'] . '/';
		$this->products_path = $upload_dir['basedir'] . '/' . $options['font_products_dir'] . '/';
		$this->products_url = $upload_dir['baseurl'] . '/' . $options['font_products_dir'] . '/';
		$this->licenses = new Typolog_Licenses();
	}
		
	function get_filename($file_id) {
		return get_the_title($file_id);
	}
	
	function get_file_path($file_id) {
		return get_post_meta($file_id, '_file_path', true);
	}

	function get_file_url($file_id) {
		return get_post_meta($file_id, '_file_url', true);
	}
		
	function add_file($filename, $real_filename) {
		$file_path = $this->fonts_path . $real_filename;
		$file_url = $this->fonts_url . $real_filename;
		if ($file = $this->get_file_by_filename($filename)) {
			update_post_meta($file->ID, '_file_path', $file_path);
			update_post_meta($file->ID, '_file_url', $file_url);
			return $file->ID;
		}
		return wp_insert_post(array(
			'post_type' => 'typolog_file',
			'post_status' => 'publish',
			'post_title' => $filename,
			'post_name' => strtolower($filename),
			'meta_input' => array(
				'_file_path' => $file_path,
				'_file_url' => $file_url
			)
		));
	}
	
	function upload_file($file_array, $path = '') {
		if (!$path) {
			$path = $this->fonts_path;
		}
		$file_secret = generate_file_secret();
		$filename_array = explode('.', $file_array['name']);
		array_splice($filename_array, -1, 0, $file_secret);
		$filename = implode('.', $filename_array);
		if (move_uploaded_file($file_array['tmp_name'], $path . $filename)) {
			return $this->add_file($file_array['name'], $filename);
		}
		return false;
	}

	function get_the_files($font_id) {
		return get_post_meta($font_id, '_font_files', true);
	}
	
	function update_the_files($font_id, $files) {
		return update_post_meta($font_id, '_font_files', $files);
	}
	
	function get_file_by_filename($filename) {
		return get_page_by_title($filename, 'OBJECT', 'typolog_file');
	}

	function delete_font_file($file_id) {
		if (file_exists($filename = $this->get_file_path($file_id))) {
			unlink($filename);
		}
		wp_delete_post($file_id, true);
		return true;
	}

	function delete_files($file_ids) {
		foreach ($file_ids as $file_id) {
			$res = $this->delete_font_file($file_id);
		}
		return true;
	}
	
	function get_font_package($files, $license_name) {
		return $this->licenses->get_font_package($files, $license_name);
	}
	
	function get_font_packages($files) {
		return $this->licenses->get_font_packages($files);
	}
	
	function update_font_packages($packages) {
		return $this->licenses->update_font_packages($packages);
	}
	
	function reset_font_packages($font_id) {
		return $this->update_font_packages($font_id, $this->licenses->reset_font_packages($this->get_the_files($font_id)));
	}

	function get_packages_table($font_id) {
		if ($font_id) {
			$packages_table = $this->licenses->get_packages_table_var($this->get_the_files($font_id));
			ob_start();
			include plugin_dir_path( __FILE__ ) . '../admin/partials/typolog-admin-font-files-table.php';
			return ob_get_clean();
		}
		return '';
	}
	
	function delete_all_files() {
		$files = get_posts([ 'post_type' => 'typolog_file', 'posts_per_page' => -1 ]);
		foreach ($files as $file) {
			$this->delete_font_file($file->ID);
		}
		return true;
	}
	
	function set_license($file_id) {
		return $this->licenses->set_license($file_id);
	}

	function set_license_attachments($license_id, $attachments) {
		return $this->licenses->set_license_attachments($license_id, $attachments);
	}

	function get_license_attachments($license_id) {
		return $this->licenses->get_license_attachments($license_id);
	}

}



