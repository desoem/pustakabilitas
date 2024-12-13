<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Upload_Handler {
    private $allowed_types = ['audio/mpeg', 'audio/mp3', 'application/epub+zip'];
    private $max_file_size = 524288000; // 500MB

    public function __construct() {
        add_action('admin_post_upload_audio_file', [$this, 'handle_file_upload']);
        add_action('wp_ajax_pustakabilitas_upload_file', [$this, 'ajax_file_upload']);
    }

    public function handle_file_upload() {
        check_admin_referer('pustakabilitas_upload');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'pustakabilitas'));
        }

        $uploaded_file = $_FILES['audio_file'] ?? null;
        if (!$uploaded_file) {
            wp_die(__('No file was uploaded.', 'pustakabilitas'));
        }

        $upload_result = $this->process_upload($uploaded_file);
        if (is_wp_error($upload_result)) {
            wp_die($upload_result->get_error_message());
        }

        return $upload_result['url'];
    }

    public function ajax_file_upload() {
        check_ajax_referer('pustakabilitas_upload', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied', 'pustakabilitas'));
        }

        $uploaded_file = $_FILES['file'] ?? null;
        if (!$uploaded_file) {
            wp_send_json_error(__('No file uploaded', 'pustakabilitas'));
        }

        $result = $this->process_upload($uploaded_file);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success([
            'url' => $result['url'],
            'file' => $result['file']
        ]);
    }

    private function process_upload($file) {
        if (!in_array($file['type'], $this->allowed_types)) {
            return new WP_Error('invalid_type', __('Invalid file type.', 'pustakabilitas'));
        }

        if ($file['size'] > $this->max_file_size) {
            return new WP_Error('file_too_large', __('File is too large.', 'pustakabilitas'));
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        // Ensure upload directory exists
        $upload_dir = PUSTAKABILITAS_UPLOAD_DIR;
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        // Custom upload handling
        $filename = wp_unique_filename($upload_dir, $file['name']);
        $new_file = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $new_file)) {
            return [
                'url' => PUSTAKABILITAS_UPLOAD_URL . $filename,
                'file' => $new_file
            ];
        }

        return new WP_Error('upload_error', __('Failed to upload file.', 'pustakabilitas'));
    }
} 