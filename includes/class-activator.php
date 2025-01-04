<?php
/**
 * Plugin Activator
 */
class Pustakabilitas_Activator {
    
    public static function activate() {
        self::create_required_tables();
        flush_rewrite_rules();
    }

    private static function create_required_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabel Bookmark
        $bookmark_table = $wpdb->prefix . 'pustakabilitas_dwp_bookmarks';
        $sql_bookmark = "CREATE TABLE IF NOT EXISTS $bookmark_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            book_id bigint(20) NOT NULL,
            page_number int NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY book_id (book_id)
        ) $charset_collate;";

        // Tabel Reading History
        $history_table = $wpdb->prefix . 'pustakabilitas_reading_history';
        $sql_history = "CREATE TABLE IF NOT EXISTS $history_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            book_id bigint(20) NOT NULL,
            last_page int NOT NULL DEFAULT 1,
            total_pages int NOT NULL DEFAULT 1,
            reading_progress float DEFAULT 0,
            last_read datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY book_id (book_id)
        ) $charset_collate;";

        // Tabel User Activity
        $activity_table = $wpdb->prefix . 'pustakabilitas_user_activity';
        $sql_activity = "CREATE TABLE IF NOT EXISTS $activity_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            book_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            activity_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY book_id (book_id),
            KEY activity_type (activity_type)
        ) $charset_collate;";

        // Tabel Visitors
        $visitors_table = $wpdb->prefix . 'pustakabilitas_visitors';
        $sql_visitors = "CREATE TABLE IF NOT EXISTS $visitors_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            user_agent varchar(255),
            page_visited varchar(255),
            visit_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address),
            KEY visit_date (visit_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Eksekusi pembuatan tabel
        dbDelta($sql_bookmark);
        dbDelta($sql_history);
        dbDelta($sql_activity);
        dbDelta($sql_visitors);

        // Simpan versi database
        update_option('pustakabilitas_db_version', '1.0');
    }
} 