<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Book_Search_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'pustakabilitas_book_search';
    }

    public function get_title() {
        return __('Book Search', 'pustakabilitas');
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return ['pustakabilitas'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Search Settings', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'placeholder_text',
            [
                'label' => __('Placeholder Text', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Search books...', 'pustakabilitas'),
            ]
        );

        $this->end_controls_section();

        // Style Controls
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style Settings', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_background',
            [
                'label' => __('Input Background', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pustakabilitas-search-input' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="pustakabilitas-book-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="hidden" name="post_type" value="pustakabilitas_book">
                <input type="search" 
                       class="pustakabilitas-search-input" 
                       placeholder="<?php echo esc_attr($settings['placeholder_text']); ?>" 
                       value="<?php echo get_search_query(); ?>" 
                       name="s">
                <button type="submit" class="search-submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <?php
    }
} 