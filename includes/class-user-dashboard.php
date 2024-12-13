<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_User_Dashboard {

    public function __construct() {
        add_shortcode( 'pustakabilitas_dashboard', [ $this, 'render_dashboard' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_dashboard_assets' ] );
    }

    public function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return sprintf(
                '<p>%s <a href="%s">%s</a></p>',
                __('Please', 'pustakabilitas'),
                wp_login_url(get_permalink()),
                __('login', 'pustakabilitas')
            );
        }

        $user_id = get_current_user_id();
        $downloaded_books = $this->get_user_books($user_id, 'downloaded');
        $read_books = $this->get_user_books($user_id, 'read');

        ob_start();
        ?>
        <div class="pustakabilitas-dashboard">
            <h2><?php _e('My Library', 'pustakabilitas'); ?></h2>
            
            <?php if (empty($downloaded_books) && empty($read_books)) : ?>
                <div class="empty-library">
                    <p><?php _e('You haven\'t downloaded or read any books yet.', 'pustakabilitas'); ?></p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('pustakabilitas_book')); ?>" 
                       class="button">
                        <?php _e('Browse Books', 'pustakabilitas'); ?>
                    </a>
                </div>
            <?php else : ?>
                <!-- Downloaded Books -->
                <div class="dashboard-section">
                    <h3><?php _e('Downloaded Books', 'pustakabilitas'); ?></h3>
                    <?php $this->render_book_list($downloaded_books); ?>
                </div>

                <!-- Read Books -->
                <div class="dashboard-section">
                    <h3><?php _e('Read Books', 'pustakabilitas'); ?></h3>
                    <?php $this->render_book_list($read_books); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_user_books($user_id, $type) {
        $meta_key = $type === 'downloaded' ? '_pustakabilitas_downloads' : '_pustakabilitas_reads';
        
        return get_posts([
            'post_type' => 'pustakabilitas_book',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => $meta_key,
                    'value' => $user_id,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
    }

    private function render_book_list($books) {
        if (empty($books)) {
            echo '<p>' . __('No books found.', 'pustakabilitas') . '</p>';
            return;
        }

        echo '<div class="book-grid">';
        foreach ($books as $book) {
            ?>
            <div class="book-card">
                <?php if (has_post_thumbnail($book->ID)) : ?>
                    <div class="book-thumbnail">
                        <?php echo get_the_post_thumbnail($book->ID, 'thumbnail'); ?>
                    </div>
                <?php endif; ?>
                <div class="book-info">
                    <h4><?php echo get_the_title($book->ID); ?></h4>
                    <a href="<?php echo get_permalink($book->ID); ?>" class="button">
                        <?php _e('View Book', 'pustakabilitas'); ?>
                    </a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    public function enqueue_dashboard_assets() {
        wp_enqueue_style(
            'pustakabilitas-dashboard',
            plugin_dir_url(__FILE__) . '../assets/css/dashboard-styles.css',
            [],
            '1.0.0'
        );
    }
}

new Pustakabilitas_User_Dashboard();
