<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Ajax_Handler {
    public function __construct() {
        // Player initialization
        add_action('wp_ajax_init_daisy_player', [$this, 'init_player']);
        add_action('wp_ajax_nopriv_init_daisy_player', [$this, 'init_player']);
        
        // Statistics tracking
        add_action('wp_ajax_pustakabilitas_record_statistics', [$this, 'record_statistics']);
        add_action('wp_ajax_nopriv_pustakabilitas_record_statistics', [$this, 'record_statistics']);
    }

    public function init_player() {
        try {
            check_ajax_referer('pustakabilitas_ajax', 'nonce');
            
            $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
            $audio_url = isset($_POST['audio_url']) ? esc_url_raw($_POST['audio_url']) : '';
            
            if (!$book_id || !$audio_url) {
                throw new Exception(__('Invalid parameters', 'pustakabilitas'));
            }

            // Get book metadata
            $metadata = [
                'title' => get_the_title($book_id),
                'author' => get_post_meta($book_id, '_pustakabilitas_author', true),
                'publisher' => get_post_meta($book_id, '_pustakabilitas_publisher', true)
            ];

            // Record read statistic
            $this->record_read($book_id);

            // Generate player HTML using daisy-player.php
            ob_start();
            include PUSTAKABILITAS_PATH . 'templates/partials/daisy-player.php';
            $player_html = ob_get_clean();

            wp_send_json_success([
                'html' => $player_html,
                'metadata' => $metadata
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function record_statistics() {
        try {
            check_ajax_referer('pustakabilitas_ajax', 'nonce');
            
            $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
            $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
            
            if (!$book_id || !$action_type) {
                throw new Exception(__('Invalid parameters', 'pustakabilitas'));
            }

            if ($action_type === 'download') {
                $this->record_download($book_id);
            } elseif ($action_type === 'read') {
                $this->record_read($book_id);
            }

            wp_send_json_success();

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    private function record_download($book_id) {
        $downloads = get_post_meta($book_id, '_pustakabilitas_downloads', true) ?: [];
        if (!is_array($downloads)) {
            $downloads = [];
        }
        
        $user_id = get_current_user_id();
        if ($user_id && !in_array($user_id, $downloads)) {
            $downloads[] = $user_id;
            update_post_meta($book_id, '_pustakabilitas_downloads', $downloads);
        }
    }

    private function record_read($book_id) {
        $reads = get_post_meta($book_id, '_pustakabilitas_reads', true) ?: [];
        if (!is_array($reads)) {
            $reads = [];
        }
        
        $user_id = get_current_user_id();
        if ($user_id && !in_array($user_id, $reads)) {
            $reads[] = $user_id;
            update_post_meta($book_id, '_pustakabilitas_reads', $reads);
        }
    }
} 