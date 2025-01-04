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
define('PUSTAKABILITAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PUSTAKABILITAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PUSTAKABILITAS_ASSETS_URL', PUSTAKABILITAS_PLUGIN_URL . 'assets/');
define('PUSTAKABILITAS_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/pustakabilitas/');
define('PUSTAKABILITAS_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/pustakabilitas/');
define('PUSTAKABILITAS_DEBUG', true);

// Include helpers
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';

class Pustakabilitas {
    private static $instance = null;
    private $components = [];
    private $plugin_name;
    private $version;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_name = 'pustakabilitas';
        $this->version = PUSTAKABILITAS_VERSION;
        $this->load_dependencies();
        $this->init_components();
        $this->setup_hooks();
        
        // Cek dan update database jika perlu
        $this->maybe_update_database();
    }

    private function load_dependencies() {
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-debug-logger.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-book-cpt.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-new-daisy-integration.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-upload-handler.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-ajax-handler.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-user-dashboard.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-book-statistics.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-bookmark-handler.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-production-notes.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-admin-page.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/shortcode-library.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-book-import-export.php';
    }

    private function init_components() {
        // Initialize core components
        $this->components['book_cpt'] = new Pustakabilitas_Book_CPT();
        $this->components['upload_handler'] = new Pustakabilitas_Upload_Handler();
        $this->components['ajax_handler'] = new Pustakabilitas_Ajax_Handler();
        
        // Initialize user dashboard
        global $pustakabilitas_user_dashboard;
        $pustakabilitas_user_dashboard = new Pustakabilitas_User_Dashboard();
        $this->components['user_dashboard'] = $pustakabilitas_user_dashboard;
        
        // Buat instance book statistics yang bisa diakses global
        global $pustakabilitas_book_statistics;
        $pustakabilitas_book_statistics = new Pustakabilitas_Book_Statistics();
        $this->components['book_statistics'] = $pustakabilitas_book_statistics;
        
        $this->components['book_import_export'] = Pustakabilitas_Book_Import_Export::get_instance($this->plugin_name, $this->version);
        
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

        add_filter('theme_page_templates', [$this, 'register_templates']);
        add_filter('template_include', [$this, 'load_plugin_template']);

        // Add archive template
        add_filter('archive_template', [$this, 'load_archive_template']);

        // Set posts per page for book archive
        add_action('pre_get_posts', [$this, 'set_books_per_page']);

        // Tambahkan support Elementor
        add_action('elementor/init', [$this, 'init_elementor_support']);
    }

    public function enqueue_public_assets() {
        // Debug: Cek path CSS
        if (is_singular('pustakabilitas_book')) {
            error_log('CSS Path: ' . PUSTAKABILITAS_ASSETS_URL . 'css/single-book-styles.css');
        }

        // Enqueue Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            [],
            '5.15.4'
        );

        if (is_singular('pustakabilitas_book')) {
            // Debug: Cek apakah fungsi dipanggil
            error_log('Enqueuing single book styles');
            
            // Force reload CSS saat development
            $version = PUSTAKABILITAS_DEBUG ? time() : $this->version;
            
            wp_enqueue_style(
                'pustakabilitas-single-book',
                PUSTAKABILITAS_ASSETS_URL . 'css/single-book-styles.css',
                [],
                $version
            );

            // Book interactions script
            wp_enqueue_script(
                'pustakabilitas-book-interactions',
                PUSTAKABILITAS_ASSETS_URL . 'js/book-interactions.js',
                ['jquery'],
                $this->version,
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

        // Enqueue front page styles
        if (is_page_template('templates/front-page.php')) {
            wp_enqueue_style(
                'pustakabilitas-frontend',
                PUSTAKABILITAS_ASSETS_URL . 'css/frontend-styles.css',
                [],
                PUSTAKABILITAS_VERSION
            );
            
            wp_enqueue_script(
                'pustakabilitas-frontend',
                PUSTAKABILITAS_ASSETS_URL . 'js/frontend-scripts.js',
                ['jquery'],
                PUSTAKABILITAS_VERSION,
                true
            );
        }

        // Enqueue archive styles
        if (is_post_type_archive('pustakabilitas_book') || is_tax('pustakabilitas_book_category')) {
            wp_enqueue_style(
                'pustakabilitas-archive',
                PUSTAKABILITAS_ASSETS_URL . 'css/archive.css',
                [],
                PUSTAKABILITAS_VERSION
            );
            
            wp_enqueue_script(
                'pustakabilitas-archive',
                PUSTAKABILITAS_ASSETS_URL . 'js/archive.js',
                ['jquery'],
                PUSTAKABILITAS_VERSION,
                true
            );
            
            wp_enqueue_script(
                'pustakabilitas-archive-ajax',
                PUSTAKABILITAS_ASSETS_URL . 'js/archive-ajax.js',
                ['jquery'],
                PUSTAKABILITAS_VERSION,
                true
            );
        }

        // Enqueue Elementor widget styles
        if (\Elementor\Plugin::$instance->preview->is_preview_mode() || is_singular('pustakabilitas_book')) {
            wp_enqueue_style(
                'pustakabilitas-elementor-widgets',
                PUSTAKABILITAS_ASSETS_URL . 'css/elementor-widgets.css',
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
            $custom_template = PUSTAKABILITAS_PLUGIN_DIR . 'templates/single-pustakabilitas_book.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function init_debug_logging() {
        if (!class_exists('Pustakabilitas_Debug_Logger')) {
            require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-debug-logger.php';
        }
    }

    public function activate() {
        global $wpdb;
        
        // Buat tabel aktivitas user jika belum ada
        $table_name = $wpdb->prefix . 'pustakabilitas_user_activity';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            book_id bigint(20) NOT NULL,
            activity_type varchar(20) NOT NULL,
            activity_date datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY book_id (book_id),
            KEY activity_type (activity_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set versi database
        add_option('pustakabilitas_db_version', '1.0');
    }

    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }

    public function register_templates($templates) {
        $plugin_templates = array(
            'templates/front-page.php' => 'Pustakabilitas Front Page',
            // ... template lainnya ...
        );
        return array_merge($templates, $plugin_templates);
    }

    public function load_plugin_template($template) {
        if (is_page()) {
            $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
            if ('templates/front-page.php' === $page_template) {
                $template = PUSTAKABILITAS_PLUGIN_DIR . 'templates/front-page.php';
            }
        }
        return $template;
    }

    public function load_archive_template($template) {
        if (is_post_type_archive('pustakabilitas_book')) {
            $custom_template = PUSTAKABILITAS_PLUGIN_DIR . 'templates/archive-pustakabilitas_book.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public function set_books_per_page($query) {
        if (!is_admin() && $query->is_main_query() && is_post_type_archive('pustakabilitas_book')) {
            $query->set('posts_per_page', 12);
        }
    }

    private function maybe_update_database() {
        $current_version = get_option('pustakabilitas_db_version', '0');
        if (version_compare($current_version, '1.0', '<')) {
            $this->activate();
        }
    }

    public function init_elementor_support() {
        // Load widget Elementor
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/class-elementor-widgets.php';
        new Pustakabilitas_Elementor_Widgets();
    }
}

// Initialize plugin
function pustakabilitas() {
    return Pustakabilitas::get_instance();
}

// Activation/deactivation hooks
register_activation_hook(__FILE__, ['Pustakabilitas_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['Pustakabilitas_Deactivator', 'deactivate']);

// Start the plugin
pustakabilitas();
