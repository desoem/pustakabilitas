<?php
/**
 * Plugin Deactivator
 */
class Pustakabilitas_Deactivator {
    
    public static function deactivate() {
        // Bersihkan rewrite rules
        flush_rewrite_rules();

        // Hapus scheduled tasks jika ada
        wp_clear_scheduled_hook('pustakabilitas_daily_cleanup');
        
        // Hapus transients
        self::clear_transients();
    }

    /**
     * Hapus tabel-tabel plugin (opsional, digunakan saat uninstall)
     */
    public static function remove_tables() {
        global $wpdb;

        // Daftar tabel yang akan dihapus
        $tables = [
            'pustakabilitas_dwp_bookmarks',
            'pustakabilitas_reading_history',
            'pustakabilitas_user_activity',
            'pustakabilitas_visitors'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
        }

        // Hapus opsi versi database
        delete_option('pustakabilitas_db_version');
    }

    /**
     * Bersihkan transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Hapus transients spesifik plugin
        $wpdb->query(
            "DELETE FROM $wpdb->options 
            WHERE option_name LIKE '%_transient_pustakabilitas_%'
            OR option_name LIKE '%_transient_timeout_pustakabilitas_%'"
        );
    }
} 