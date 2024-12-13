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
