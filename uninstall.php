<?php
// Jika uninstall tidak dipanggil dari WordPress, keluar
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load class deactivator
require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';

// Hapus semua data plugin (opsional)
if (get_option('pustakabilitas_delete_data_on_uninstall', false)) {
    // Hapus tabel-tabel
    Pustakabilitas_Deactivator::remove_tables();
    
    // Hapus post type dan meta data
    $posts = get_posts([
        'post_type' => 'pustakabilitas_book',
        'numberposts' => -1,
        'post_status' => 'any'
    ]);

    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }

    // Hapus opsi plugin
    $options = [
        'pustakabilitas_db_version',
        'pustakabilitas_settings',
        'pustakabilitas_delete_data_on_uninstall'
    ];

    foreach ($options as $option) {
        delete_option($option);
    }
} 