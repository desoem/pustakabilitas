<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Books_Grid_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'pustakabilitas_books_grid';
    }

    public function get_title() {
        return __('Books Grid', 'pustakabilitas');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['pustakabilitas'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Query Type
        $this->add_control(
            'query_type',
            [
                'label' => __('Display Type', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'latest',
                'options' => [
                    'latest' => __('Latest Books', 'pustakabilitas'),
                    'popular' => __('Popular Books', 'pustakabilitas'),
                    'category' => __('Category Books', 'pustakabilitas'),
                ]
            ]
        );

        // Category Selection
        $categories = get_terms([
            'taxonomy' => 'book_category',
            'hide_empty' => false,
        ]);

        $category_options = [];
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_options[$category->term_id] = $category->name;
            }
        }

        $this->add_control(
            'category_id',
            [
                'label' => __('Select Category', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $category_options,
                'condition' => [
                    'query_type' => 'category',
                ],
            ]
        );

        // Books Per Page
        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Books Per Page', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 100,
            ]
        );

        // Grid Columns
        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'tablet_default' => '3',
                'mobile_default' => '2',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .pustakabilitas-books-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label' => __('Enable Pagination', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pagination_alignment',
            [
                'label' => __('Pagination Alignment', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'pustakabilitas'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'pustakabilitas'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'pustakabilitas'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'condition' => [
                    'enable_pagination' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .pustakabilitas-pagination' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Generate unique ID untuk widget instance
        $widget_id = 'pustakabilitas-books-' . $this->get_id();
        
        // Get current page
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $args = [
            'post_type' => 'pustakabilitas_book',
            'posts_per_page' => $settings['posts_per_page'],
            'paged' => $paged,
        ];

        // Query berdasarkan tipe yang dipilih
        switch ($settings['query_type']) {
            case 'popular':
                $args['meta_key'] = '_pustakabilitas_read_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;

            case 'category':
                if (!empty($settings['category_id'])) {
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'book_category',
                            'field' => 'term_id',
                            'terms' => $settings['category_id'],
                        ],
                    ];
                }
                break;

            default: // latest
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        $books_query = new WP_Query($args);

        if ($books_query->have_posts()) {
            echo '<div id="' . esc_attr($widget_id) . '" class="pustakabilitas-books-container" 
                data-settings="' . esc_attr(json_encode([
                    'query_type' => $settings['query_type'],
                    'category_id' => $settings['category_id'] ?? '',
                    'posts_per_page' => $settings['posts_per_page'],
                    'widget_id' => $widget_id
                ])) . '">';
            
            echo '<div class="pustakabilitas-books-grid">';
            while ($books_query->have_posts()) {
                $books_query->the_post();
                include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/book-card.php';
            }
            echo '</div>';

            if ($settings['enable_pagination'] === 'yes') {
                echo '<div class="pustakabilitas-pagination">';
                $total_pages = $books_query->max_num_pages;
                $current_page = $paged;

                // Previous Button
                if ($current_page > 1) {
                    echo '<a href="#" class="prev page-numbers" data-page="' . ($current_page - 1) . '">
                        <i class="fas fa-chevron-left"></i>
                    </a>';
                }

                // First Page
                echo '<a href="#" class="page-numbers ' . ($current_page === 1 ? 'current' : '') . '" data-page="1">1</a>';

                // Dots after first page
                if ($current_page > 3) {
                    echo '<span class="page-numbers dots">...</span>';
                }

                // Pages around current page
                for ($i = max(2, $current_page - 1); $i <= min($total_pages - 1, $current_page + 1); $i++) {
                    if ($i > 1 && $i < $total_pages) {
                        echo '<a href="#" class="page-numbers ' . ($i === $current_page ? 'current' : '') . '" data-page="' . $i . '">' . $i . '</a>';
                    }
                }

                // Dots before last page
                if ($current_page < $total_pages - 2) {
                    echo '<span class="page-numbers dots">...</span>';
                }

                // Last Page
                if ($total_pages > 1) {
                    echo '<a href="#" class="page-numbers ' . ($current_page === $total_pages ? 'current' : '') . '" data-page="' . $total_pages . '">' . $total_pages . '</a>';
                }

                // Next Button
                if ($current_page < $total_pages) {
                    echo '<a href="#" class="next page-numbers" data-page="' . ($current_page + 1) . '">
                        <i class="fas fa-chevron-right"></i>
                    </a>';
                }

                echo '</div>';
                echo '<div class="pustakabilitas-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>';
            }
            
            echo '</div>'; // Close books-container
            
            wp_reset_postdata();
        } else {
            echo '<p class="no-books">' . __('No books found.', 'pustakabilitas') . '</p>';
        }
    }
} 