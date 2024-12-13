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
