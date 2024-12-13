<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_User_Dashboard {

    public function __construct() {
        add_shortcode('pustakabilitas_user_dashboard', [ $this, 'render_dashboard' ]);
    }

    public function render_dashboard() {
        // Output untuk dashboard pengguna
        if ( ! is_user_logged_in() ) {
            return '<p>' . __('Please log in to access your library dashboard.', 'pustakabilitas') . '</p>';
        }

        $user_id = get_current_user_id();
        $downloads = get_user_meta($user_id, 'pustakabilitas_downloads', true);
        $reads = get_user_meta($user_id, 'pustakabilitas_reads', true);

        ob_start();
        ?>
        <h2><?php _e( 'Your Library Dashboard', 'pustakabilitas' ); ?></h2>
        <ul>
            <li><?php _e( 'Books Downloaded: ', 'pustakabilitas' ) . esc_html($downloads); ?></li>
            <li><?php _e( 'Books Read: ', 'pustakabilitas' ) . esc_html($reads); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }
}

new Pustakabilitas_User_Dashboard();
