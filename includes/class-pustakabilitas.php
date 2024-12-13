<?php

class Pustakabilitas {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_ajax_init_daisy_player', [$this, 'handle_init_daisy_player']);
        add_action('wp_ajax_nopriv_init_daisy_player', [$this, 'handle_init_daisy_player']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'pustakabilitas-player', 
            PUSTAKABILITAS_PLUGIN_URL . 'assets/js/daisy-player.js',
            ['jquery'],
            PUSTAKABILITAS_VERSION,
            true
        );

        wp_localize_script('pustakabilitas-player', 'pustakabilitasAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('init_daisy_player')
        ]);
    }

    public function handle_init_daisy_player() {
        try {
            Pustakabilitas_Debug_Logger::log('Starting DAISY player initialization');
            
            check_ajax_referer('init_daisy_player', 'nonce');
            
            $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
            $audio_url = isset($_POST['audio_url']) ? esc_url_raw($_POST['audio_url']) : '';
            $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

            Pustakabilitas_Debug_Logger::log([
                'book_id' => $book_id,
                'audio_url' => $audio_url,
                'title' => $title
            ], 'debug');

            if (empty($audio_url)) {
                throw new Exception('Audio URL is required');
            }

            // Validate audio URL
            $response = wp_remote_head($audio_url);
            if (is_wp_error($response)) {
                throw new Exception('Audio file not accessible: ' . $response->get_error_message());
            }

            $file_info = pathinfo($audio_url);
            $extension = strtolower($file_info['extension']);
            
            Pustakabilitas_Debug_Logger::log('File type: ' . $extension);

            // Initialize player
            if ($extension === 'epub') {
                $player_html = Pustakabilitas_Daisy_Integration::init_epub_player($audio_url, $title, $book_id);
            } else {
                $player_html = Pustakabilitas_Daisy_Integration::init_audio_player($audio_url, $title, $book_id);
            }

            if (empty($player_html)) {
                throw new Exception('Failed to generate player HTML');
            }

            Pustakabilitas_Debug_Logger::log('Player initialized successfully');

            wp_send_json_success([
                'html' => $player_html,
                'type' => $extension
            ]);

        } catch (Exception $e) {
            Pustakabilitas_Debug_Logger::log($e->getMessage(), 'error');
            wp_send_json_error($e->getMessage());
        }
    }
} 