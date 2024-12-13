<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_Admin_Page {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_submenu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Menambahkan submenu di bawah Categories untuk pengaturan plugin.
     */
    public function add_admin_submenu() {
        add_submenu_page(
            'edit.php?post_type=pustakabilitas_book', // Mengubah parent slug ke CPT Pustakabilitas
            __( 'Pustakabilitas Settings', 'pustakabilitas' ),
            __( 'Settings', 'pustakabilitas' ), // Mengubah label menu menjadi lebih singkat
            'manage_options',
            'pustakabilitas-settings',
            [ $this, 'settings_page' ]
        );
    }

    /**
     * Membuat halaman pengaturan untuk plugin.
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Pustakabilitas Settings', 'pustakabilitas' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'pustakabilitas_options_group' );
                do_settings_sections( 'pustakabilitas-settings' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Enable Download Tracking', 'pustakabilitas' ); ?></th>
                        <td>
                            <input type="checkbox" name="pustakabilitas_enable_download_tracking" value="1" <?php checked( 1, get_option( 'pustakabilitas_enable_download_tracking' ), true ); ?> />
                            <label for="pustakabilitas_enable_download_tracking"><?php _e( 'Enable tracking for book downloads.', 'pustakabilitas' ); ?></label>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Enable Read Tracking', 'pustakabilitas' ); ?></th>
                        <td>
                            <input type="checkbox" name="pustakabilitas_enable_read_tracking" value="1" <?php checked( 1, get_option( 'pustakabilitas_enable_read_tracking' ), true ); ?> />
                            <label for="pustakabilitas_enable_read_tracking"><?php _e( 'Enable tracking for books read (audio playback).', 'pustakabilitas' ); ?></label>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Custom Footer Message', 'pustakabilitas' ); ?></th>
                        <td>
                            <textarea name="pustakabilitas_custom_footer_message" rows="5" cols="50"><?php echo esc_textarea( get_option( 'pustakabilitas_custom_footer_message' ) ); ?></textarea>
                            <p class="description"><?php _e( 'Custom message that will be displayed in the footer of the plugin.', 'pustakabilitas' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Menyusun dan mendaftarkan pengaturan plugin.
     */
    public function register_settings() {
        // Menambahkan pengaturan untuk plugin
        register_setting( 'pustakabilitas_options_group', 'pustakabilitas_enable_download_tracking' );
        register_setting( 'pustakabilitas_options_group', 'pustakabilitas_enable_read_tracking' );
        register_setting( 'pustakabilitas_options_group', 'pustakabilitas_custom_footer_message' );
    }
}

// Inisialisasi halaman pengaturan admin
new Pustakabilitas_Admin_Page();
