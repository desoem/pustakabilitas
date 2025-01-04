<?php
/**
 * Elementor Widgets Integration
 *
 * @package Pustakabilitas
 * @subpackage Elementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Pustakabilitas_Elementor_Widgets {
    /**
     * Constructor
     */
    public function __construct() {
        // Register widgets when Elementor is ready
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Add widget categories
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_categories']);

        // Register widget styles
        add_action('elementor/frontend/after_register_styles', [$this, 'register_widget_styles']);
        
        // Enqueue Font Awesome
        add_action('wp_enqueue_scripts', [$this, 'enqueue_font_awesome']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_font_awesome']);

        // Register and enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    }

    /**
     * Register Elementor widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager
     */
    public function register_widgets($widgets_manager) {
        // Include widget files
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/widgets/class-books-grid-widget.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/widgets/class-statistics-widget.php';
        require_once PUSTAKABILITAS_PLUGIN_DIR . 'includes/widgets/class-book-search-widget.php';
        
        // Register widgets
        $widgets_manager->register(new \Pustakabilitas_Books_Grid_Widget());
        $widgets_manager->register(new \Pustakabilitas_Statistics_Widget());
        $widgets_manager->register(new \Pustakabilitas_Book_Search_Widget());
    }

    /**
     * Add custom widget categories
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'pustakabilitas',
            [
                'title' => __('Pustakabilitas', 'pustakabilitas'),
                'icon' => 'fa fa-book',
            ]
        );
    }

    /**
     * Register widget styles
     */
    public function register_widget_styles() {
        wp_register_style(
            'pustakabilitas-elementor-widgets',
            PUSTAKABILITAS_PLUGIN_URL . 'assets/css/elementor-widgets.css',
            [],
            PUSTAKABILITAS_VERSION
        );

        // Enqueue widget styles
        wp_enqueue_style('pustakabilitas-elementor-widgets');
    }

    /**
     * Enqueue Font Awesome
     */
    public function enqueue_font_awesome() {
        // Enqueue Font Awesome 5 from CDN
        wp_enqueue_style(
            'font-awesome-5',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            [],
            '5.15.4'
        );

        // Atau gunakan Font Awesome 6 jika diinginkan
        // wp_enqueue_style(
        //     'font-awesome-6',
        //     'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        //     [],
        //     '6.4.0'
        // );
    }

    /**
     * Register and enqueue scripts
     */
    public function register_scripts() {
        wp_register_script(
            'pustakabilitas-pagination',
            PUSTAKABILITAS_PLUGIN_URL . 'assets/js/books-pagination.js',
            ['jquery'],
            PUSTAKABILITAS_VERSION,
            true
        );

        wp_localize_script('pustakabilitas-pagination', 'pustakabilitasAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pustakabilitas_ajax_nonce')
        ]);

        wp_enqueue_script('pustakabilitas-pagination');
    }
}

// Initialize Elementor widgets
new Pustakabilitas_Elementor_Widgets(); 