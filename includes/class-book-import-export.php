<?php
/**
 * Class untuk menangani Export Import buku
 */
class Pustakabilitas_Book_Import_Export {
    private static $instance = null;
    private $plugin_name;
    private $version;

    // CSV column headers
    private $csv_headers = [
        'id',
        'title',
        'content',
        'excerpt',
        'status',
        'author',
        'publisher',
        'isbn',
        'file_size',
        'book_type',
        'book_file',
        'audio_url',
        'epub_url',
        'tags',
        'categories',
        'featured_image_url'
    ];

    public static function get_instance($plugin_name, $version) {
        if (null === self::$instance) {
            self::$instance = new self($plugin_name, $version);
        }
        return self::$instance;
    }

    private function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Hooks untuk menu admin
        add_action('admin_menu', array($this, 'add_import_export_menu'));
        
        // Hooks untuk handle import/export
        add_action('admin_post_pustakabilitas_export_books', array($this, 'handle_export'));
        add_action('admin_post_pustakabilitas_import_books', array($this, 'handle_import'));
        add_action('wp_ajax_check_import_progress', array($this, 'check_import_progress'));
    }

    /**
     * Tambahkan submenu untuk Import/Export
     */
    public function add_import_export_menu() {
        add_submenu_page(
            'edit.php?post_type=pustakabilitas_book',
            __('Import/Export Buku', 'pustakabilitas'),
            __('Import/Export', 'pustakabilitas'),
            'manage_options',
            'pustakabilitas-import-export',
            array($this, 'render_import_export_page')
        );
    }

    /**
     * Render halaman Import/Export
     */
    public function render_import_export_page() {
        include plugin_dir_path(dirname(__FILE__)) . 'admin/partials/import-export-page.php';
    }

    /**
     * Handle proses export
     */
    public function handle_export() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pustakabilitas'));
        }

        // Verify nonce
        if (!isset($_POST['pustakabilitas_export_nonce']) || 
            !wp_verify_nonce($_POST['pustakabilitas_export_nonce'], 'pustakabilitas_export_books')) {
            wp_die(__('Invalid nonce specified', 'pustakabilitas'), '', array('back_link' => true));
        }

        // Get all books
        $args = array(
            'post_type' => 'pustakabilitas_book',
            'posts_per_page' => -1,
            'post_status' => 'any'
        );

        $books = get_posts($args);
        
        // Set headers for CSV download
        $filename = 'pustakabilitas-books-export-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create CSV file
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for proper Excel handling
        fputs($output, "\xEF\xBB\xBF");

        // Add headers
        fputcsv($output, $this->csv_headers);

        // Add book data
        foreach ($books as $book) {
            $row = [
                $book->ID,
                $book->post_title,
                $book->post_content,
                $book->post_excerpt,
                $book->post_status,
                get_post_meta($book->ID, '_pustakabilitas_author', true),
                get_post_meta($book->ID, '_pustakabilitas_publisher', true),
                get_post_meta($book->ID, '_pustakabilitas_isbn', true),
                get_post_meta($book->ID, '_pustakabilitas_file_size', true),
                get_post_meta($book->ID, '_pustakabilitas_book_type', true),
                get_post_meta($book->ID, '_pustakabilitas_book_file', true),
                get_post_meta($book->ID, '_pustakabilitas_audio_url', true),
                get_post_meta($book->ID, '_pustakabilitas_epub_url', true),
                implode(', ', wp_get_object_terms($book->ID, 'book_tag', array('fields' => 'names'))),
                implode(', ', wp_get_object_terms($book->ID, 'book_category', array('fields' => 'names'))),
                get_the_post_thumbnail_url($book->ID, 'full')
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Handle proses import
     */
    public function handle_import() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pustakabilitas'));
        }

        // Increase PHP limits
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        // Verify nonce
        if (!isset($_POST['pustakabilitas_import_nonce']) || 
            !wp_verify_nonce($_POST['pustakabilitas_import_nonce'], 'pustakabilitas_import_books')) {
            wp_die(__('Invalid nonce specified', 'pustakabilitas'), '', array('back_link' => true));
        }

        if (!isset($_FILES['import_file'])) {
            wp_redirect(add_query_arg('import_error', 'no_file', wp_get_referer()));
            exit;
        }

        $file = $_FILES['import_file'];
        
        // Check file type
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'csv') {
            wp_redirect(add_query_arg('import_error', 'invalid_type', wp_get_referer()));
            exit;
        }

        // Open and read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            wp_redirect(add_query_arg('import_error', 'file_error', wp_get_referer()));
            exit;
        }

        // Get the headers
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            wp_redirect(add_query_arg('import_error', 'no_headers', wp_get_referer()));
            exit;
        }

        // Start transaction
        global $wpdb;
        $wpdb->query('START TRANSACTION');

        try {
            $processed = 0;
            $batch_size = 10; // Process 10 books at a time
            $total_imported = 0;
            $errors = array();
            $import_id = uniqid();

            // Process books in batches
            while (($data = fgetcsv($handle)) !== false) {
                try {
                    $this->import_single_book($data);
                    $total_imported++;
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }

                $processed++;
                
                // Commit every batch_size records
                if ($processed % $batch_size === 0) {
                    $wpdb->query('COMMIT');
                    $wpdb->query('START TRANSACTION');
                    
                    // Reset time limit for next batch
                    set_time_limit(300);
                }

                $this->update_import_progress($import_id, $processed, count($data));
            }

            // Final commit
            $wpdb->query('COMMIT');
            fclose($handle);

            // Send JSON response for AJAX
            if (wp_doing_ajax()) {
                wp_send_json_success(array(
                    'import_id' => $import_id,
                    'message' => sprintf(__('Started importing %d books', 'pustakabilitas'), $total_imported)
                ));
            }

            // Redirect with success message for non-AJAX
            $redirect_args = array(
                'imported' => $total_imported
            );
            
            if (!empty($errors)) {
                $redirect_args['import_errors'] = implode(';', array_slice($errors, 0, 5));
            }
            
            wp_redirect(add_query_arg($redirect_args, wp_get_referer()));
            exit;

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            fclose($handle);
            wp_redirect(add_query_arg('import_error', urlencode($e->getMessage()), wp_get_referer()));
            exit;
        }
    }

    /**
     * Check import progress
     */
    public function check_import_progress() {
        // Verify nonce
        check_ajax_referer('pustakabilitas_import_progress', 'nonce');

        $import_id = isset($_POST['import_id']) ? sanitize_text_field($_POST['import_id']) : '';
        
        if (empty($import_id)) {
            wp_send_json_error('Invalid import ID');
        }

        $progress = get_transient('pustakabilitas_import_' . $import_id);
        
        if ($progress === false) {
            wp_send_json_error('Import not found');
        }

        wp_send_json_success($progress);
    }

    /**
     * Update import progress
     */
    private function update_import_progress($import_id, $processed, $total) {
        $progress = array(
            'processed' => $processed,
            'total' => $total,
            'percentage' => ($total > 0) ? round(($processed / $total) * 100) : 0,
            'completed' => ($processed >= $total)
        );
        
        set_transient('pustakabilitas_import_' . $import_id, $progress, HOUR_IN_SECONDS);
        return $progress;
    }

    private function import_single_book($data) {
        // Validate row data
        if (count($data) !== count($this->csv_headers)) {
            throw new Exception('Invalid data format');
        }

        // Create post array
        $post_data = array(
            'post_title' => sanitize_text_field($data[1]),
            'post_content' => wp_kses_post($data[2]),
            'post_excerpt' => sanitize_text_field($data[3]),
            'post_status' => sanitize_text_field($data[4]),
            'post_type' => 'pustakabilitas_book'
        );

        // If import_id is set and not empty, try to use the original ID
        $import_id = !empty($data[0]) ? intval($data[0]) : 0;
        if ($import_id > 0) {
            // Check if post with this ID exists
            $existing_post = get_post($import_id);
            if (!$existing_post) {
                // If post doesn't exist, we can safely use this ID
                $post_data['ID'] = $import_id;
                
                // Remove auto-increment for this ID if it exists
                global $wpdb;
                $wpdb->query($wpdb->prepare(
                    "ALTER TABLE {$wpdb->posts} AUTO_INCREMENT = %d",
                    $import_id + 1
                ));
            }
        }

        // Insert post
        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            // Update meta fields
            update_post_meta($post_id, '_pustakabilitas_author', sanitize_text_field($data[5]));
            update_post_meta($post_id, '_pustakabilitas_publisher', sanitize_text_field($data[6]));
            update_post_meta($post_id, '_pustakabilitas_isbn', sanitize_text_field($data[7]));
            update_post_meta($post_id, '_pustakabilitas_file_size', sanitize_text_field($data[8]));
            update_post_meta($post_id, '_pustakabilitas_book_type', sanitize_text_field($data[9]));
            update_post_meta($post_id, '_pustakabilitas_book_file', esc_url_raw($data[10]));
            update_post_meta($post_id, '_pustakabilitas_audio_url', esc_url_raw($data[11]));
            update_post_meta($post_id, '_pustakabilitas_epub_url', esc_url_raw($data[12]));
            
            // Update tags (trim untuk menghilangkan spasi)
            $tags = array_map('trim', explode(',', $data[13]));
            if (!empty($tags[0])) {
                wp_set_object_terms($post_id, $tags, 'book_tag');
            }
            
            // Update categories (trim untuk menghilangkan spasi)
            $categories = array_map('trim', explode(',', $data[14]));
            if (!empty($categories[0])) {
                wp_set_object_terms($post_id, $categories, 'book_category');
            }
            
            // Update featured image
            if (!empty($data[15])) {
                $image_url = esc_url_raw(trim($data[15]));
                
                // Cek apakah gambar sudah ada di media library
                $attachment_id = attachment_url_to_postid($image_url);
                
                if (!$attachment_id) {
                    // Jika gambar belum ada, download dan import ke media library
                    $upload_dir = wp_upload_dir();
                    $image_data = file_get_contents($image_url);
                    
                    if ($image_data !== false) {
                        $filename = basename($image_url);
                        
                        if (wp_mkdir_p($upload_dir['path'])) {
                            $file = $upload_dir['path'] . '/' . $filename;
                        } else {
                            $file = $upload_dir['basedir'] . '/' . $filename;
                        }
                        
                        file_put_contents($file, $image_data);
                        
                        $wp_filetype = wp_check_filetype($filename, null);
                        
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => sanitize_file_name($filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );
                        
                        $attachment_id = wp_insert_attachment($attachment, $file, $post_id);
                        
                        if (!is_wp_error($attachment_id)) {
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);
                        }
                    }
                }
                
                if ($attachment_id && !is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
        } else {
            throw new Exception($post_id->get_error_message());
        }
    }
}
