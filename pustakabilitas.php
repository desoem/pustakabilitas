<?php
/*
Plugin Name: Pustakabilitas
Description: Plugin untuk perpustakaan digital bagi penyandang disabilitas netra.
Version: 1.0
Author: Ridwan Sumantri
Text Domain: pustakabilitas
*/

// Prevent direct access
defined('ABSPATH') or die('No script kiddies please!');

// Define plugin constants
define('PUSTAKABILITAS_VERSION', '1.0.0');
define('PUSTAKABILITAS_PATH', plugin_dir_path(__FILE__));
define('PUSTAKABILITAS_URL', plugin_dir_url(__FILE__));
define('PUSTAKABILITAS_ASSETS_URL', PUSTAKABILITAS_URL . 'assets/');
define('PUSTAKABILITAS_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/pustakabilitas/');
define('PUSTAKABILITAS_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/pustakabilitas/');
define('PUSTAKABILITAS_DEBUG', true);

class Pustakabilitas {
    private static $instance = null;
    private $components = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->setup_hooks();
    }

    private function load_dependencies() {
        $required_files = [
            'includes/class-debug-logger.php',
            'includes/class-book-cpt.php',
            'includes/class-upload-handler.php',
            'includes/class-ajax-handler.php',
            'includes/class-user-dashboard.php',
            'includes/class-book-statistics.php',
            'includes/class-bookmark-handler.php',
            'includes/class-production-notes.php',
            'includes/class-admin-page.php',
            'includes/shortcode-library.php'
        ];

        foreach ($required_files as $file) {
            $file_path = PUSTAKABILITAS_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("Pustakabilitas Error: Required file not found - {$file_path}");
            }
        }
    }

    private function init_components() {
        // Initialize core components
        $this->components['book_cpt'] = new Pustakabilitas_Book_CPT();
        $this->components['upload_handler'] = new Pustakabilitas_Upload_Handler();
        $this->components['ajax_handler'] = new Pustakabilitas_Ajax_Handler();
        $this->components['user_dashboard'] = new Pustakabilitas_User_Dashboard();
        $this->components['book_statistics'] = new Pustakabilitas_Book_Statistics();
        
        // Register shortcodes
        pustakabilitas_register_shortcodes();
    }

    private function setup_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Add custom template for single book
        add_filter('single_template', [$this, 'load_book_template']);
        
        // Initialize debug logging if enabled
        if (PUSTAKABILITAS_DEBUG) {
            add_action('init', [$this, 'init_debug_logging']);
        }
    }

    public function enqueue_public_assets() {
        // Enqueue only on single book pages
        if (is_singular('pustakabilitas_book')) {
            // Enqueue Daisy Player assets
            wp_enqueue_style(
                'pustakabilitas-daisy-player',
                PUSTAKABILITAS_ASSETS_URL . 'css/daisy-player.css',
                [],
                PUSTAKABILITAS_VERSION
            );

            wp_enqueue_script(
                'pustakabilitas-daisy-player',
                PUSTAKABILITAS_ASSETS_URL . 'js/daisy-player.js',
                ['jquery'],
                PUSTAKABILITAS_VERSION,
                true
            );

            wp_enqueue_script(
                'pustakabilitas-book-interactions',
                PUSTAKABILITAS_ASSETS_URL . 'js/book-interactions.js',
                ['jquery'],
                PUSTAKABILITAS_VERSION,
                true
            );

            wp_localize_script('pustakabilitas-book-interactions', 'pustakabilitasAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pustakabilitas_ajax')
            ]);
        }

        // Enqueue dashboard styles if needed
        if (has_shortcode(get_post()->post_content, 'pustakabilitas_dashboard')) {
            wp_enqueue_style(
                'pustakabilitas-dashboard',
                PUSTAKABILITAS_ASSETS_URL . 'css/dashboard-styles.css',
                [],
                PUSTAKABILITAS_VERSION
            );
        }
    }

    public function enqueue_admin_assets($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            global $post_type;
            if ('pustakabilitas_book' === $post_type) {
                wp_enqueue_media();
                wp_enqueue_style(
                    'pustakabilitas-admin',
                    PUSTAKABILITAS_ASSETS_URL . 'css/admin-styles.css',
                    [],
                    PUSTAKABILITAS_VERSION
                );
            }
        }
    }

    public function load_book_template($template) {
        if (is_singular('pustakabilitas_book')) {
            $custom_template = PUSTAKABILITAS_PATH . 'templates/single-pustakabilitas_book.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function init_debug_logging() {
        if (!class_exists('Pustakabilitas_Debug_Logger')) {
            require_once PUSTAKABILITAS_PATH . 'includes/class-debug-logger.php';
        }
    }

    public function activate() {
        // Create upload directory if it doesn't exist
        if (!file_exists(PUSTAKABILITAS_UPLOAD_DIR)) {
            wp_mkdir_p(PUSTAKABILITAS_UPLOAD_DIR);
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }
}

// Initialize plugin
function pustakabilitas() {
    return Pustakabilitas::get_instance();
}

// Activation/deactivation hooks
register_activation_hook(__FILE__, [pustakabilitas(), 'activate']);
register_deactivation_hook(__FILE__, [pustakabilitas(), 'deactivate']);

// Start the plugin
pustakabilitas();
