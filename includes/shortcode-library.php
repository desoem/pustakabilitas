<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pustakabilitas_register_shortcodes() {
    add_shortcode('pustakabilitas_books', 'pustakabilitas_books_shortcode');
}

function pustakabilitas_books_shortcode() {
    // Shortcode untuk menampilkan daftar buku
    $args = [
        'post_type' => 'pustakabilitas_book',
        'posts_per_page' => 10
    ];

    $query = new WP_Query($args);
    ob_start();
    if ($query->have_posts()) {
        echo '<ul>';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li>' . get_the_title() . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>' . __('No books found.', 'pustakabilitas') . '</p>';
    }
    wp_reset_postdata();
    return ob_get_clean();
}

// Shortcode untuk form pencarian
function pustakabilitas_search_form_shortcode() {
    ob_start();
    include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/search-form.php';
    return ob_get_clean();
}
add_shortcode('pustakabilitas_search_form', 'pustakabilitas_search_form_shortcode');

// Shortcode untuk statistik
function pustakabilitas_statistics_shortcode($atts) {
    $atts = shortcode_atts(array(
        'columns' => 5,
        'show_visitors' => true
    ), $atts);

    // Get statistics data
    $total_books = get_total_books();
    $total_users = get_total_users();
    $total_reads = get_total_reads();
    $total_downloads = get_total_downloads();
    $total_visitors = get_total_visitors();

    ob_start();
    ?>
    <div class="pustakabilitas-statistics columns-<?php echo esc_attr($atts['columns']); ?>">
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

        <?php if ($atts['show_visitors']) : ?>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number"><?php echo number_format_i18n($total_visitors); ?></div>
            <div class="stat-label"><?php _e('Total Visitors', 'pustakabilitas'); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pustakabilitas_statistics', 'pustakabilitas_statistics_shortcode');

// Shortcode untuk buku terbaru
function pustakabilitas_latest_books_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 8,
        'pagination' => 'yes'
    ), $atts);

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $latest_books = new WP_Query([
        'post_type' => 'pustakabilitas_book',
        'posts_per_page' => $atts['count'],
        'orderby' => 'date',
        'order' => 'DESC',
        'paged' => $paged
    ]);

    $show_pagination = ($atts['pagination'] === 'yes');
    
    ob_start();
    include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/books-grid.php';
    return ob_get_clean();
}
add_shortcode('pustakabilitas_latest_books', 'pustakabilitas_latest_books_shortcode');

// Shortcode untuk buku populer
function pustakabilitas_popular_books_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 8
    ), $atts);

    $popular_books = get_popular_books($atts['count']);

    ob_start();
    include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/books-grid.php';
    return ob_get_clean();
}
add_shortcode('pustakabilitas_popular_books', 'pustakabilitas_popular_books_shortcode');

// Tambahkan shortcode baru untuk kategori buku
function pustakabilitas_category_books_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'count' => 8
    ), $atts);

    $args = [
        'post_type' => 'pustakabilitas_book',
        'posts_per_page' => $atts['count'],
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'book_category',
                'field' => 'slug',
                'terms' => $atts['category'],
            ],
        ];
    }

    $category_books = new WP_Query($args);
    
    ob_start();
    include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/books-grid.php';
    return ob_get_clean();
}
add_shortcode('pustakabilitas_category_books', 'pustakabilitas_category_books_shortcode');
