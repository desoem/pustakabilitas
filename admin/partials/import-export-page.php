<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    // Display status messages
    if (isset($_GET['imported'])) {
        $imported = intval($_GET['imported']);
        echo '<div class="notice notice-success"><p>' . 
             sprintf(__('Successfully imported %d books.', 'pustakabilitas'), $imported) . 
             '</p></div>';
    }

    if (isset($_GET['import_errors'])) {
        $errors = explode(';', $_GET['import_errors']);
        echo '<div class="notice notice-warning"><p>' . 
             __('Import completed with some warnings:', 'pustakabilitas') . '</p><ul>';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';
    }

    if (isset($_GET['import_error'])) {
        $error = $_GET['import_error'];
        $error_messages = array(
            'no_file' => __('No file was uploaded.', 'pustakabilitas'),
            'upload_error' => __('There was an error uploading the file.', 'pustakabilitas'),
            'file_open_error' => __('Could not open the uploaded file.', 'pustakabilitas'),
            'no_headers' => __('The CSV file appears to be empty.', 'pustakabilitas'),
            'invalid_format' => __('The CSV file format is invalid.', 'pustakabilitas')
        );
        
        $error_message = isset($error_messages[$error]) ? $error_messages[$error] : $error;
        echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    }
    ?>

    <div class="card">
        <h2><?php _e('Export Books', 'pustakabilitas'); ?></h2>
        <p><?php _e('Export all books to a CSV file. This file will contain all book data including metadata.', 'pustakabilitas'); ?></p>
        <p><strong><?php _e('CSV Format:', 'pustakabilitas'); ?></strong></p>
        <code>id, title, content, excerpt, status, author, publisher, isbn, file_size, book_type, book_file, audio_url, epub_url, tags, categories, featured_image_url</code>
        <p><strong><?php _e('Field Descriptions:', 'pustakabilitas'); ?></strong></p>
        <ul>
            <li><strong>id:</strong> <?php _e('Unique WordPress post ID', 'pustakabilitas'); ?></li>
            <li><strong>tags:</strong> <?php _e('Comma-separated list of tags', 'pustakabilitas'); ?></li>
            <li><strong>categories:</strong> <?php _e('Comma-separated list of categories', 'pustakabilitas'); ?></li>
            <li><strong>featured_image_url:</strong> <?php _e('URL of the featured image', 'pustakabilitas'); ?></li>
        </ul>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('pustakabilitas_export_books', 'pustakabilitas_export_nonce'); ?>
            <input type="hidden" name="action" value="pustakabilitas_export_books">
            <?php submit_button(__('Export Books', 'pustakabilitas'), 'primary', 'submit', false); ?>
        </form>
    </div>

    <div class="card">
        <h2><?php _e('Import Books', 'pustakabilitas'); ?></h2>
        <p><?php _e('Import books from a CSV file. The file should be in the same format as the export file.', 'pustakabilitas'); ?></p>
        
        <!-- Progress Bar Container -->
        <div id="import-progress-container" style="display: none; margin: 20px 0;">
            <div class="progress-bar-wrapper" style="background: #f0f0f0; border-radius: 4px; padding: 1px; margin-bottom: 10px;">
                <div id="import-progress-bar" style="background: #0073aa; height: 20px; width: 0%; border-radius: 3px; transition: width 0.3s ease-in-out;"></div>
            </div>
            <p id="import-progress-text" style="margin: 5px 0;"></p>
            <p id="import-error" class="notice notice-error" style="display: none;"></p>
        </div>

        <p><strong><?php _e('Important Notes:', 'pustakabilitas'); ?></strong></p>
        <ul>
            <li><?php _e('The CSV file must have the correct column headers', 'pustakabilitas'); ?></li>
            <li><?php _e('All URLs (book_file, audio_url, epub_url, featured_image_url) must be valid URLs', 'pustakabilitas'); ?></li>
            <li><?php _e('Status should be: publish, draft, private, or pending', 'pustakabilitas'); ?></li>
            <li><?php _e('Tags and categories should be comma-separated (e.g., "tag1, tag2, tag3")', 'pustakabilitas'); ?></li>
            <li><?php _e('Featured image URL must be from the WordPress media library', 'pustakabilitas'); ?></li>
            <li><?php _e('If ID column is provided, the system will try to preserve the original post IDs during import', 'pustakabilitas'); ?></li>
        </ul>
        
        <form id="pustakabilitas-import-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('pustakabilitas_import_books', 'pustakabilitas_import_nonce'); ?>
            <input type="hidden" name="action" value="pustakabilitas_import_books">
            <p>
                <input type="file" name="import_file" accept=".csv" required>
            </p>
            <?php submit_button(__('Import Books', 'pustakabilitas'), 'primary', 'submit', false); ?>
        </form>
    </div>

    <?php
    // Add nonce for AJAX
    wp_localize_script('pustakabilitas-admin-script', 'pustakabilitasAdmin', array(
        'importNonce' => wp_create_nonce('pustakabilitas_import_progress')
    ));
    ?>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
    padding: 20px;
    max-width: 800px;
}

.card h2 {
    margin-top: 0;
}

.card code {
    display: block;
    padding: 10px;
    margin: 10px 0;
    background: #f5f5f5;
    border-radius: 3px;
}

.card ul {
    list-style: disc;
    margin-left: 20px;
}
</style>
