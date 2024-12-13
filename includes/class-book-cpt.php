<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_Book_CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_book_meta' ] );
        add_filter( 'manage_pustakabilitas_book_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_pustakabilitas_book_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
        add_action( 'init', [ $this, 'register_taxonomy' ] );
    }

    /**
     * Mendaftarkan Custom Post Type Buku
     */
    public function register_post_type() {
        $labels = [
            'name'               => __( 'Books', 'pustakabilitas' ),
            'singular_name'      => __( 'Book', 'pustakabilitas' ),
            'menu_name'          => __( 'Pustakabilitas', 'pustakabilitas' ),
            'add_new'            => __( 'Add New Book', 'pustakabilitas' ),
            'add_new_item'       => __( 'Add New Book', 'pustakabilitas' ),
            'edit_item'          => __( 'Edit Book', 'pustakabilitas' ),
            'new_item'           => __( 'New Book', 'pustakabilitas' ),
            'view_item'          => __( 'View Book', 'pustakabilitas' ),
            'search_items'       => __( 'Search Books', 'pustakabilitas' ),
            'not_found'          => __( 'No books found.', 'pustakabilitas' ),
            'not_found_in_trash' => __( 'No books found in trash.', 'pustakabilitas' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'books' ],
            'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'menu_icon'          => 'dashicons-book',
            'show_in_rest'       => true,
            'taxonomies'         => ['book_category'],
        ];

        register_post_type( 'pustakabilitas_book', $args );
    }

    /**
     * Menambahkan meta box untuk buku
     */
    public function add_meta_boxes() {
        add_meta_box(
            'pustakabilitas_book_details',
            __('Book Details', 'pustakabilitas'),
            [$this, 'render_meta_box'],
            'pustakabilitas_book',
            'normal',
            'high'
        );
    }

    /**
     * Render tampilan form Book Meta Data
     */
    public function render_meta_box($post) {
        wp_nonce_field('pustakabilitas_save_book_meta', 'pustakabilitas_meta_nonce');

        // Get existing values
        $author = get_post_meta($post->ID, '_pustakabilitas_author', true);
        $publisher = get_post_meta($post->ID, '_pustakabilitas_publisher', true);
        $isbn = get_post_meta($post->ID, '_pustakabilitas_isbn', true);
        $file_size = get_post_meta($post->ID, '_pustakabilitas_file_size', true);
        $epub_url = get_post_meta($post->ID, '_pustakabilitas_epub_url', true);
        $audio_url = get_post_meta($post->ID, '_pustakabilitas_audio_url', true);
        ?>
        
        <div class="pustakabilitas-meta-box">
            <style>
                .pustakabilitas-meta-box {
                    display: grid;
                    grid-gap: 15px;
                    padding: 15px;
                }
                .form-group {
                    display: grid;
                    grid-template-columns: 150px 1fr;
                    align-items: center;
                    gap: 10px;
                }
                .form-group label {
                    font-weight: bold;
                }
                .form-group input, .form-group select {
                    width: 100%;
                    padding: 8px;
                }
                .file-upload-wrapper {
                    display: grid;
                    grid-template-columns: 1fr auto;
                    gap: 10px;
                }
            </style>

            <div class="form-group">
                <label for="epub_file"><?php _e('EPUB File', 'pustakabilitas'); ?></label>
                <div class="file-upload-wrapper">
                    <input type="text" id="epub_url" name="_pustakabilitas_epub_url" 
                           value="<?php echo esc_url($epub_url); ?>" 
                           placeholder="<?php _e('Upload or enter URL', 'pustakabilitas'); ?>">
                    <button type="button" class="button upload-epub">
                        <?php _e('Upload', 'pustakabilitas'); ?>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="audio_file"><?php _e('Audio File', 'pustakabilitas'); ?></label>
                <div class="file-upload-wrapper">
                    <input type="text" id="audio_url" name="_pustakabilitas_audio_url" 
                           value="<?php echo esc_url($audio_url); ?>"
                           placeholder="<?php _e('Upload or enter URL', 'pustakabilitas'); ?>">
                    <button type="button" class="button upload-audio">
                        <?php _e('Upload', 'pustakabilitas'); ?>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="author"><?php _e('Author', 'pustakabilitas'); ?></label>
                <input type="text" id="author" name="_pustakabilitas_author" 
                       value="<?php echo esc_attr($author); ?>">
            </div>

            <div class="form-group">
                <label for="publisher"><?php _e('Publisher', 'pustakabilitas'); ?></label>
                <input type="text" id="publisher" name="_pustakabilitas_publisher" 
                       value="<?php echo esc_attr($publisher); ?>">
            </div>

            <div class="form-group">
                <label for="isbn"><?php _e('ISBN', 'pustakabilitas'); ?></label>
                <input type="text" id="isbn" name="_pustakabilitas_isbn" 
                       value="<?php echo esc_attr($isbn); ?>">
            </div>

            <div class="form-group">
                <label for="file_size"><?php _e('File Size', 'pustakabilitas'); ?></label>
                <input type="text" id="file_size" name="_pustakabilitas_file_size" 
                       value="<?php echo esc_attr($file_size); ?>">
            </div>

            <!-- Statistics Display -->
            <div class="book-statistics">
                <h4><?php _e('Book Statistics', 'pustakabilitas'); ?></h4>
                <p>
                    <?php 
                    $downloads = get_post_meta($post->ID, '_pustakabilitas_downloads', true) ?: 0;
                    $reads = get_post_meta($post->ID, '_pustakabilitas_reads', true) ?: 0;
                    printf(
                        __('Downloads: %d | Reads: %d', 'pustakabilitas'),
                        $downloads,
                        $reads
                    );
                    ?>
                </p>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // File upload handling
            $('.upload-epub, .upload-audio').on('click', function() {
                var button = $(this);
                var isEpub = button.hasClass('upload-epub');
                
                var frame = wp.media({
                    title: isEpub ? 'Select EPUB File' : 'Select Audio File',
                    button: {
                        text: 'Use this file'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var urlInput = isEpub ? $('#epub_url') : $('#audio_url');
                    urlInput.val(attachment.url);
                    
                    // Update file size if empty
                    if ($('#file_size').val() === '') {
                        $('#file_size').val(formatBytes(attachment.filesize));
                    }
                });

                frame.open();
            });

            // Helper function to format bytes
            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
        </script>
        <?php
    }

    /**
     * Menyimpan metadata buku saat disimpan
     */
    public function save_book_meta($post_id) {
        if (!isset($_POST['pustakabilitas_meta_nonce']) || 
            !wp_verify_nonce($_POST['pustakabilitas_meta_nonce'], 'pustakabilitas_save_book_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Debug information
        error_log('Saving book meta for post ID: ' . $post_id);

        $fields = [
            '_pustakabilitas_author',
            '_pustakabilitas_publisher',
            '_pustakabilitas_isbn',
            '_pustakabilitas_file_size',
            '_pustakabilitas_epub_url',
            '_pustakabilitas_audio_url'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                error_log("Saved $field: " . $_POST[$field]);
            }
        }
    }

    public function enqueue_admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'pustakabilitas-admin-style',
            plugin_dir_url(__FILE__) . '../admin/css/admin-styles.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'pustakabilitas-admin-script',
            plugin_dir_url(__FILE__) . '../admin/js/admin-scripts.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'pustakabilitas-frontend',
            plugin_dir_url(__FILE__) . '../assets/js/frontend-scripts.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('pustakabilitas-frontend', 'pustakabilitas', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function add_custom_columns($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'thumbnail' => __('Cover', 'pustakabilitas'),
            'title' => __('Title', 'pustakabilitas'),
            'author' => __('Author', 'pustakabilitas'),
            'format' => __('Format', 'pustakabilitas'),
            'downloads' => __('Downloads', 'pustakabilitas'),
            'date' => $columns['date']
        );
        return $new_columns;
    }

    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(50, 50));
                }
                break;
            case 'author':
                echo get_post_meta($post_id, '_pustakabilitas_author', true);
                break;
            case 'format':
                echo get_post_meta($post_id, '_pustakabilitas_format', true);
                break;
            case 'downloads':
                echo get_post_meta($post_id, '_pustakabilitas_downloads', true);
                break;
        }
    }

    /**
     * Mendaftarkan Taxonomy Kategori Buku
     */
    public function register_taxonomy() {
        $labels = [
            'name'              => __( 'Book Categories', 'pustakabilitas' ),
            'singular_name'     => __( 'Book Category', 'pustakabilitas' ),
            'search_items'      => __( 'Search Categories', 'pustakabilitas' ),
            'all_items'         => __( 'All Categories', 'pustakabilitas' ),
            'parent_item'       => __( 'Parent Category', 'pustakabilitas' ),
            'parent_item_colon' => __( 'Parent Category:', 'pustakabilitas' ),
            'edit_item'         => __( 'Edit Category', 'pustakabilitas' ),
            'update_item'       => __( 'Update Category', 'pustakabilitas' ),
            'add_new_item'      => __( 'Add New Category', 'pustakabilitas' ),
            'new_item_name'     => __( 'New Category Name', 'pustakabilitas' ),
            'menu_name'         => __( 'Categories', 'pustakabilitas' ),
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'book-category'],
        ];

        register_taxonomy( 'book_category', 'pustakabilitas_book', $args );
    }
}

// Inisialisasi Custom Post Type
new Pustakabilitas_Book_CPT();
