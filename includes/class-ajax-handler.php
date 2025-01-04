<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Ajax_Handler {
    private $plugin_path;

    public function __construct() {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        
        // Player initialization
        add_action('wp_ajax_init_daisy_player', [$this, 'init_player']);
        add_action('wp_ajax_nopriv_init_daisy_player', [$this, 'init_player']);
        
        // Statistics tracking
        add_action('wp_ajax_pustakabilitas_record_statistics', [$this, 'record_statistics']);
        add_action('wp_ajax_nopriv_pustakabilitas_record_statistics', [$this, 'record_statistics']);
        
        // Add pagination handler
        add_action('wp_ajax_load_more_books', [$this, 'load_more_books']);
        add_action('wp_ajax_nopriv_load_more_books', [$this, 'load_more_books']);
    }

    public function init_player() {
        try {
            check_ajax_referer('pustakabilitas_ajax', 'nonce');
            
            $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
            if (!$book_id) {
                throw new Exception(__('Invalid book ID', 'pustakabilitas'));
            }

            $book_file = get_post_meta($book_id, '_pustakabilitas_book_file', true);
            if (empty($book_file)) {
                $book_file = get_post_meta($book_id, '_pustakabilitas_audio_url', true);
            }

            if (!$book_file) {
                throw new Exception(__('Book file not found', 'pustakabilitas'));
            }

            // Return player HTML directly instead of including template
            $player_html = sprintf(
                '<div class="audio-player-wrapper">
                    <audio id="audioPlayer" preload="auto">
                        <source src="%s" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <div class="player-controls">
                        <div class="progress">
                            <div class="progress-bar"></div>
                        </div>
                        <div class="time-info">
                            <span class="current-time">0:00</span> / 
                            <span class="duration">0:00</span>
                        </div>
                        <div class="speed-controls">
                            <button class="speed-control" data-speed="0.5">0.5x</button>
                            <button class="speed-control" data-speed="0.75">0.75x</button>
                            <button class="speed-control" data-speed="1.0">1.0x</button>
                            <button class="speed-control" data-speed="1.25">1.25x</button>
                            <button class="speed-control" data-speed="1.5">1.5x</button>
                            <button class="speed-control" data-speed="2.0">2.0x</button>
                        </div>
                    </div>
                    %s
                </div>',
                esc_url($book_file),
                WP_DEBUG ? '<div class="debug-panel"><h3>Debug Output</h3><pre id="debug-output"></pre></div>' : ''
            );

            // Record read statistic
            $this->record_read($book_id);

            wp_send_json_success([
                'html' => $player_html,
                'book_url' => $book_file,
                'book_title' => get_the_title($book_id)
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

    /**
     * Menangani permintaan AJAX untuk menambahkan buku ke koleksi
     */
    public function add_to_collection() {
        check_ajax_referer('pustakabilitas_ajax', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Silakan login terlebih dahulu.');
            return;
        }

        $book_id = intval($_POST['book_id']);
        $user_id = get_current_user_id();
        
        // Ambil koleksi yang ada
        $collection = get_user_meta($user_id, '_pustakabilitas_collection', true) ?: array();
        
        // Cek apakah buku sudah ada di koleksi
        if (in_array($book_id, $collection)) {
            wp_send_json_error('Buku sudah ada dalam koleksi Anda.');
            return;
        }
        
        // Tambahkan buku ke koleksi
        $collection[] = $book_id;
        update_user_meta($user_id, '_pustakabilitas_collection', $collection);
        
        wp_send_json_success('Buku berhasil ditambahkan ke koleksi.');
    }

    /**
     * Handle AJAX pagination for books grid
     */
    public function load_more_books() {
        try {
            check_ajax_referer('pustakabilitas_ajax_nonce', 'nonce');

            $settings = $_POST['settings'];
            $page = intval($_POST['page']);

            $args = [
                'post_type' => 'pustakabilitas_book',
                'posts_per_page' => $settings['posts_per_page'],
                'paged' => $page
            ];

            // Add query conditions based on settings
            if ($settings['query_type'] === 'popular') {
                $args['meta_key'] = '_pustakabilitas_read_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
            } elseif ($settings['query_type'] === 'category' && !empty($settings['category_id'])) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'book_category',
                        'field' => 'term_id',
                        'terms' => $settings['category_id'],
                    ],
                ];
            } else {
                // Latest books (default)
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
            }

            $query = new WP_Query($args);
            
            ob_start();
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/book-card.php';
                }
                wp_reset_postdata();
            }
            $html = ob_get_clean();
            
            wp_send_json_success([
                'html' => $html,
                'pagination_url' => add_query_arg('paged', $page, get_permalink()),
                'total_pages' => $query->max_num_pages,
                'current_page' => $page
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
} 