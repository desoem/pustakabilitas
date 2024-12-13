<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_Book_Statistics {

    public function __construct() {
        add_action('wp_ajax_pustakabilitas_record_statistics', [$this, 'record_statistics']);
        add_action('wp_ajax_nopriv_pustakabilitas_record_statistics', [$this, 'record_statistics']);
    }

    public function record_statistics() {
        if ( ! isset($_POST['book_id']) || ! isset($_POST['action_type']) ) {
            wp_send_json_error(['message' => __('Missing parameters', 'pustakabilitas')]);
        }

        $book_id = intval($_POST['book_id']);
        $action_type = sanitize_text_field($_POST['action_type']);
        $user_id = get_current_user_id();

        if ( $action_type === 'download' ) {
            $this->update_statistics($book_id, '_pustakabilitas_downloads', $user_id);
        } elseif ( $action_type === 'read' ) {
            $this->update_statistics($book_id, '_pustakabilitas_reads', $user_id);
        }

        wp_send_json_success();
    }

    private function update_statistics($book_id, $meta_key, $user_id) {
        $current_value = get_post_meta($book_id, $meta_key, true);
        $current_value = is_array($current_value) ? $current_value : [];

        if (!in_array($user_id, $current_value)) {
            $current_value[] = $user_id;
            update_post_meta($book_id, $meta_key, $current_value);
        }
    }
}

new Pustakabilitas_Book_Statistics();
