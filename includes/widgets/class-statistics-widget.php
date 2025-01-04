<?php
if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_Statistics_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'pustakabilitas_statistics';
    }

    public function get_title() {
        return __('Library Statistics', 'pustakabilitas');
    }

    public function get_icon() {
        return 'eicon-counter';
    }

    public function get_categories() {
        return ['pustakabilitas'];
    }

    protected function register_controls() {
        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '5',
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
                    '{{WRAPPER}} .pustakabilitas-statistics' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Columns Gap', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 30,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pustakabilitas-statistics' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Stats Container
        $this->start_controls_section(
            'section_stats_style',
            [
                'label' => __('Statistics Box', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .stat-item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'selector' => '{{WRAPPER}} .stat-item',
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => __('Padding', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .stat-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Numbers
        $this->start_controls_section(
            'section_number_style',
            [
                'label' => __('Numbers', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'number_typography',
                'label' => __('Typography', 'pustakabilitas'),
                'selector' => '{{WRAPPER}} .stat-number',
            ]
        );

        $this->add_control(
            'number_color',
            [
                'label' => __('Color', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .stat-number' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Labels
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => __('Labels', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => __('Typography', 'pustakabilitas'),
                'selector' => '{{WRAPPER}} .stat-label',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Color', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .stat-label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Icons
        $this->start_controls_section(
            'section_icon_style',
            [
                'label' => __('Icons', 'pustakabilitas'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Color', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .stat-icon i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Size', 'pustakabilitas'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .stat-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        try {
            $total_books = get_total_books();
            $total_users = get_total_users();
            $total_reads = get_total_reads();
            $total_downloads = get_total_downloads();
            $total_visitors = get_total_visitors();
        } catch (Exception $e) {
            error_log('Pustakabilitas Statistics Error: ' . $e->getMessage());
            return;
        }
        ?>
        <div class="pustakabilitas-statistics">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo number_format_i18n($total_books); ?></div>
                <div class="stat-label"><?php _e('Total Books', 'pustakabilitas'); ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format_i18n($total_users); ?></div>
                <div class="stat-label"><?php _e('Active Users', 'pustakabilitas'); ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-number"><?php echo number_format_i18n($total_reads); ?></div>
                <div class="stat-label"><?php _e('Total Reads', 'pustakabilitas'); ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="stat-number"><?php echo number_format_i18n($total_downloads); ?></div>
                <div class="stat-label"><?php _e('Downloads', 'pustakabilitas'); ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?php echo number_format_i18n($total_visitors); ?></div>
                <div class="stat-label"><?php _e('Total Visitors', 'pustakabilitas'); ?></div>
            </div>
        </div>
        <?php
    }
} 