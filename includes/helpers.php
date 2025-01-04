<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Tidak diakses langsung
}

// Fungsi untuk menambahkan metabox ke CPT Buku (hanya satu kali)
function pustakabilitas_add_book_metabox() {
    // Pastikan hanya menambahkan satu metabox
    if ( ! has_action( 'add_meta_boxes', 'pustakabilitas_add_book_metabox' ) ) {
        add_meta_box(
            'pustakabilitas_book_metadata', // ID metabox
            __( 'Book Metadata', 'pustakabilitas' ), // Judul metabox
            'pustakabilitas_render_book_metabox', // Fungsi render
            'pustakabilitas_book', // Post type
            'normal', // Letak metabox
            'default' // Prioritas
        );
    }
}
add_action( 'add_meta_boxes', 'pustakabilitas_add_book_metabox' );

// Render isi metabox
function pustakabilitas_render_book_metabox( $post ) {
    // Nonce untuk keamanan
    wp_nonce_field( 'pustakabilitas_save_metadata', 'pustakabilitas_metadata_nonce' );

    // Ambil metadata
    $metadata = [
        'author'    => get_post_meta( $post->ID, '_pustakabilitas_author', true ),
        'publisher' => get_post_meta( $post->ID, '_pustakabilitas_publisher', true ),
        'isbn'      => get_post_meta( $post->ID, '_pustakabilitas_isbn', true ),
        'file_size' => get_post_meta( $post->ID, '_pustakabilitas_file_size', true ),
        'download_count' => get_post_meta( $post->ID, '_pustakabilitas_download_count', true ),
        'read_count' => get_post_meta( $post->ID, '_pustakabilitas_read_count', true ),
        'epub_url'  => get_post_meta( $post->ID, '_pustakabilitas_epub_url', true ),
        'audio_url' => get_post_meta( $post->ID, '_pustakabilitas_audio_url', true ),
    ];

    ?>
    <p>
        <label for="pustakabilitas_author"><?php _e( 'Author', 'pustakabilitas' ); ?></label><br>
        <input type="text" name="pustakabilitas_author" id="pustakabilitas_author" class="regular-text" value="<?php echo esc_attr( $metadata['author'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_publisher"><?php _e( 'Publisher', 'pustakabilitas' ); ?></label><br>
        <input type="text" name="pustakabilitas_publisher" id="pustakabilitas_publisher" class="regular-text" value="<?php echo esc_attr( $metadata['publisher'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_isbn"><?php _e( 'ISBN', 'pustakabilitas' ); ?></label><br>
        <input type="text" name="pustakabilitas_isbn" id="pustakabilitas_isbn" class="regular-text" value="<?php echo esc_attr( $metadata['isbn'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_file_size"><?php _e( 'File Size (MB)', 'pustakabilitas' ); ?></label><br>
        <input type="text" name="pustakabilitas_file_size" id="pustakabilitas_file_size" class="regular-text" value="<?php echo esc_attr( $metadata['file_size'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_download_count"><?php _e( 'Download Count', 'pustakabilitas' ); ?></label><br>
        <input type="number" name="pustakabilitas_download_count" id="pustakabilitas_download_count" class="regular-text" value="<?php echo esc_attr( $metadata['download_count'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_read_count"><?php _e( 'Read Count (Audio)', 'pustakabilitas' ); ?></label><br>
        <input type="number" name="pustakabilitas_read_count" id="pustakabilitas_read_count" class="regular-text" value="<?php echo esc_attr( $metadata['read_count'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_epub_url"><?php _e( 'ePub URL', 'pustakabilitas' ); ?></label><br>
        <input type="url" name="pustakabilitas_epub_url" id="pustakabilitas_epub_url" class="regular-text" value="<?php echo esc_attr( $metadata['epub_url'] ); ?>">
    </p>
    <p>
        <label for="pustakabilitas_audio_url"><?php _e( 'Audio Book URL', 'pustakabilitas' ); ?></label><br>
        <input type="url" name="pustakabilitas_audio_url" id="pustakabilitas_audio_url" class="regular-text" value="<?php echo esc_attr( $metadata['audio_url'] ); ?>">
    </p>
    <?php
}

// Simpan metadata
function pustakabilitas_save_book_metadata( $post_id ) {
    if ( ! isset( $_POST['pustakabilitas_metadata_nonce'] ) || ! wp_verify_nonce( $_POST['pustakabilitas_metadata_nonce'], 'pustakabilitas_save_metadata' ) ) {
        return;
    }

    // Cek apakah user memiliki hak untuk menyimpan post
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Simpan semua metadata
    $fields = [
        '_pustakabilitas_author'    => sanitize_text_field( $_POST['pustakabilitas_author'] ),
        '_pustakabilitas_publisher' => sanitize_text_field( $_POST['pustakabilitas_publisher'] ),
        '_pustakabilitas_isbn'      => sanitize_text_field( $_POST['pustakabilitas_isbn'] ),
        '_pustakabilitas_file_size' => sanitize_text_field( $_POST['pustakabilitas_file_size'] ),
        '_pustakabilitas_download_count' => intval( $_POST['pustakabilitas_download_count'] ),
        '_pustakabilitas_read_count'    => intval( $_POST['pustakabilitas_read_count'] ),
        '_pustakabilitas_epub_url'  => esc_url_raw( $_POST['pustakabilitas_epub_url'] ),
        '_pustakabilitas_audio_url' => esc_url_raw( $_POST['pustakabilitas_audio_url'] ),
    ];

    foreach ( $fields as $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }
}
add_action( 'save_post', 'pustakabilitas_save_book_metadata' );

function pustakabilitas_get_file_type($url) {
    $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $valid_types = ['epub', 'mp3', 'daisy'];
    
    return in_array($extension, $valid_types) ? $extension : false;
}

function pustakabilitas_validate_audio_url($url) {
    if (empty($url)) {
        return new WP_Error('empty_url', __('Audio URL is required', 'pustakabilitas'));
    }

    $response = wp_remote_head($url);
    if (is_wp_error($response)) {
        return new WP_Error('invalid_url', __('Audio file not accessible', 'pustakabilitas'));
    }

    $file_type = pustakabilitas_get_file_type($url);
    if (!$file_type) {
        return new WP_Error('invalid_type', __('Invalid file type', 'pustakabilitas'));
    }

    return true;
}

function pustakabilitas_get_book_format($post_id) {
    return get_post_meta($post_id, '_pustakabilitas_format', true) ?: 'mp3';
}

/**
 * Format file size to human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Menambahkan buku ke koleksi pengguna
 */
function add_book_to_collection($book_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Ambil array pembaca buku saat ini
    $book_readers = get_post_meta($book_id, 'book_readers', true);
    if (!is_array($book_readers)) {
        $book_readers = array();
    }
    
    // Tambahkan user ke array jika belum ada
    if (!in_array($user_id, $book_readers)) {
        $book_readers[] = $user_id;
        update_post_meta($book_id, 'book_readers', $book_readers);
        
        // Catat waktu penambahan buku
        update_post_meta($book_id, 'book_added_' . $user_id, current_time('mysql'));
        
        return true;
    }
    
    return false;
}

/**
 * Menambahkan action hooks untuk menambahkan buku ke koleksi
 */
function auto_add_book_to_collection() {
    // Saat buku diakses
    add_action('template_redirect', function() {
        if (is_singular('pustakabilitas_book')) {
            $book_id = get_the_ID();
            add_book_to_collection($book_id);
        }
    });
    
    // Saat buku diunduh
    add_action('pustakabilitas_before_book_download', function($book_id, $user_id) {
        add_book_to_collection($book_id, $user_id);
    }, 10, 2);
    
    // Saat progress membaca disimpan
    add_action('pustakabilitas_update_reading_progress', function($book_id, $user_id) {
        add_book_to_collection($book_id, $user_id);
    }, 10, 2);
}

// Inisialisasi hooks
auto_add_book_to_collection();

/**
 * Mengecek apakah buku ada dalam koleksi pengguna
 */
function is_book_in_collection($book_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $book_readers = get_post_meta($book_id, 'book_readers', true);
    return is_array($book_readers) && in_array($user_id, $book_readers);
}

/**
 * Mendapatkan informasi tambahan buku
 */
function get_book_additional_info($book_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    return array(
        'progress' => get_reading_progress($book_id, $user_id),
        'last_read' => get_user_meta($user_id, 'last_read_' . $book_id, true),
        'added_date' => get_user_meta($user_id, 'book_added_' . $book_id, true),
        'is_bookmarked' => is_book_bookmarked($book_id, $user_id),
        'download_count' => get_user_book_download_count($book_id, $user_id)
    );
}

// Fungsi untuk mendapatkan total buku
function get_total_books() {
    // Mengambil jumlah buku yang dipublikasikan
    $count_posts = wp_count_posts('pustakabilitas_book');
    return intval($count_posts->publish);
}

// Fungsi untuk mendapatkan total pengguna
function get_total_users() {
    // Mengambil jumlah pengguna terdaftar
    $user_count = count_users();
    return intval($user_count['total_users']);
}

// Fungsi untuk mendapatkan total pembacaan audio
function get_total_reads() {
    global $wpdb;
    
    // Mengambil total pembacaan dari tabel aktivitas
    $table_name = $wpdb->prefix . 'pustakabilitas_user_activity';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        $total_reads = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM $table_name 
            WHERE activity_type = 'read'
        ");
    } else {
        // Fallback ke metode lama jika tabel belum ada
        $total_reads = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_pustakabilitas_read_count'
        ");
    }
    
    return intval($total_reads) ?: 0;
}

// Fungsi untuk mendapatkan total unduhan
function get_total_downloads() {
    global $wpdb;
    
    // Mengambil total unduhan dari tabel aktivitas
    $table_name = $wpdb->prefix . 'pustakabilitas_user_activity';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        $total_downloads = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM $table_name 
            WHERE activity_type = 'download'
        ");
    } else {
        // Fallback ke metode lama jika tabel belum ada
        $total_downloads = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_pustakabilitas_download_count'
        ");
    }
    
    return intval($total_downloads) ?: 0;
}

// Fungsi untuk mendapatkan total pengunjung
function get_total_visitors() {
    // Menggunakan transient untuk caching
    $visitors = get_transient('pustakabilitas_total_visitors');
    
    if (false === $visitors) {
        global $wpdb;
        
        // Hitung dari tabel log pengunjung
        $visitors = $wpdb->get_var("
            SELECT COUNT(DISTINCT ip_address) 
            FROM {$wpdb->prefix}pustakabilitas_visitors
        ");
        
        // Cache hasil untuk 1 jam
        set_transient('pustakabilitas_total_visitors', $visitors, HOUR_IN_SECONDS);
    }
    
    return intval($visitors);
}

function get_popular_books($limit = 8) {
    global $wpdb;
    
    // Query untuk menghitung total downloads dan reads untuk setiap buku
    $query = $wpdb->prepare("
        SELECT p.ID, p.post_title,
            (
                COALESCE(
                    (SELECT COUNT(*) FROM {$wpdb->postmeta} 
                    WHERE post_id = p.ID AND meta_key = '_pustakabilitas_downloads'), 
                0)
                +
                COALESCE(
                    (SELECT COUNT(*) FROM {$wpdb->postmeta} 
                    WHERE post_id = p.ID AND meta_key = '_pustakabilitas_reads'), 
                0)
            ) as total_interactions
        FROM {$wpdb->posts} p
        WHERE p.post_type = 'pustakabilitas_book'
        AND p.post_status = 'publish'
        GROUP BY p.ID
        ORDER BY total_interactions DESC
        LIMIT %d
    ", $limit);

    return new WP_Query([
        'post__in' => $wpdb->get_col($query),
        'post_type' => 'pustakabilitas_book',
        'posts_per_page' => $limit,
        'orderby' => 'post__in'
    ]);
}
