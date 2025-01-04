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

    /**
     * Get total number of book downloads
     */
    public function get_total_downloads() {
        global $wpdb;
        $total = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_pustakabilitas_download_count'
        ");
        return intval($total);
    }

    /**
     * Get total number of book reads
     */
    public function get_total_reads() {
        global $wpdb;
        $total = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_pustakabilitas_read_count'
        ");
        return intval($total);
    }

    /**
     * Get book statistics for a specific book
     */
    public function get_book_stats($book_id) {
        $downloads = get_post_meta($book_id, '_pustakabilitas_download_count', true);
        $reads = get_post_meta($book_id, '_pustakabilitas_read_count', true);
        
        return array(
            'downloads' => intval($downloads) ?: 0,
            'reads' => intval($reads) ?: 0
        );
    }

    public function track_book_download($book_id, $user_id) {
        global $wpdb;
        
        // Update jumlah unduh buku
        $current_count = get_post_meta($book_id, '_pustakabilitas_download_count', true);
        $new_count = intval($current_count) + 1;
        update_post_meta($book_id, '_pustakabilitas_download_count', $new_count);
        
        // Catat aktivitas user
        $wpdb->insert(
            $wpdb->prefix . 'pustakabilitas_user_activity',
            array(
                'user_id' => $user_id,
                'book_id' => $book_id,
                'activity_type' => 'download',
                'activity_date' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );
    }

    public function track_book_read($book_id, $user_id) {
        global $wpdb;
        
        // Update jumlah baca buku
        $current_count = get_post_meta($book_id, '_pustakabilitas_read_count', true);
        $new_count = intval($current_count) + 1;
        update_post_meta($book_id, '_pustakabilitas_read_count', $new_count);
        
        // Catat aktivitas user
        $wpdb->insert(
            $wpdb->prefix . 'pustakabilitas_user_activity',
            array(
                'user_id' => $user_id,
                'book_id' => $book_id,
                'activity_type' => 'read',
                'activity_date' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );
    }

    public function update_read_count($post_id) {
        $current_count = get_post_meta($post_id, '_pustakabilitas_read_count', true);
        $new_count = ($current_count) ? $current_count + 1 : 1;
        update_post_meta($post_id, '_pustakabilitas_read_count', $new_count);
    }

    public function update_download_count($post_id) {
        $current_count = get_post_meta($post_id, '_pustakabilitas_download_count', true);
        $new_count = ($current_count) ? $current_count + 1 : 1;
        update_post_meta($post_id, '_pustakabilitas_download_count', $new_count);
    }
}

new Pustakabilitas_Book_Statistics();
