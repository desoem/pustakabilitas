<?php
/**
 * User Dashboard Class
 *
 * @package Pustakabilitas
 * @subpackage Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_User_Dashboard {
    private $plugin_name;
    private $version;

    public function __construct() {
        $this->plugin_name = 'pustakabilitas';
        $this->version = PUSTAKABILITAS_VERSION;
        $this->setup_hooks();
    }

    private function setup_hooks() {
        // Shortcode
        add_shortcode('pustakabilitas_user_dashboard', array($this, 'render_user_dashboard'));

        // Assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));

        // AJAX handlers
        add_action('wp_ajax_toggle_bookmark', array($this, 'toggle_book_bookmark'));
        add_action('wp_ajax_update_bookmark_note', array($this, 'update_bookmark_note'));
        add_action('wp_ajax_remove_bookmark', array($this, 'remove_bookmark'));
        add_action('wp_ajax_load_more_reading_history', array($this, 'load_more_reading_history'));
        add_action('wp_ajax_remove_download_history', array($this, 'remove_download_history'));
        add_action('wp_ajax_track_book_download', array($this, 'track_book_download'));
        add_action('wp_ajax_track_book_action', array($this, 'track_book_action'));
    }

    /**
     * Render user dashboard
     */
    public function render_user_dashboard($atts) {
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="pustakabilitas-login-required">%s <a href="%s">%s</a></div>',
                __('Silakan', 'pustakabilitas'),
                wp_login_url(get_permalink()),
                __('login terlebih dahulu', 'pustakabilitas')
            );
        }

        // Load dashboard template
        ob_start();
        include(PUSTAKABILITAS_PLUGIN_DIR . '/templates/user-dashboard.php');
        return ob_get_clean();
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets() {
        if (!is_page() || !has_shortcode(get_post()->post_content, 'pustakabilitas_user_dashboard')) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style('dashicons');
        wp_enqueue_style('pustakabilitas-dashboard', 
            PUSTAKABILITAS_PLUGIN_URL . 'assets/css/dashboard.css',
            array('dashicons'),
            $this->version
        );

        // Enqueue scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('pustakabilitas-dashboard',
            PUSTAKABILITAS_PLUGIN_URL . 'assets/js/dashboard.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script
        wp_localize_script('pustakabilitas-dashboard', 'pustakabilitasAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pustakabilitas_dashboard'),
            'strings' => array(
                'confirmDelete' => __('Apakah Anda yakin ingin menghapus ini?', 'pustakabilitas'),
                'errorLoading' => __('Terjadi kesalahan saat memuat data', 'pustakabilitas'),
                'errorSaving' => __('Terjadi kesalahan saat menyimpan data', 'pustakabilitas')
            )
        ));
    }

    /**
     * Get user reading history
     * 
     * @param int $limit Number of items to retrieve
     * @param int $offset Offset for pagination
     * @return array Array of reading history entries
     */
    public function get_user_reading_history($limit = 10, $offset = 0) {
        $user_id = get_current_user_id();
        $history = get_user_meta($user_id, '_pustakabilitas_reading_history', true);
        
        if (!is_array($history)) {
            return array();
        }

        // Sort by timestamp descending
        usort($history, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Apply pagination
        return array_slice($history, $offset, $limit);
    }

    /**
     * Add reading history entry
     * 
     * @param int $book_id Book ID
     * @param int $progress Reading progress (0-100)
     * @return bool True on success, false on failure
     */
    public function add_reading_history($book_id, $progress) {
        if (!$book_id || !is_numeric($progress)) {
            return false;
        }

        $user_id = get_current_user_id();
        $history = get_user_meta($user_id, '_pustakabilitas_reading_history', true);
        
        if (!is_array($history)) {
            $history = array();
        }

        // Check if entry exists
        $exists = false;
        foreach ($history as &$entry) {
            if ($entry['book_id'] == $book_id) {
                $entry['progress'] = $progress;
                $entry['timestamp'] = current_time('mysql');
                $exists = true;
                break;
            }
        }

        // Add new entry if doesn't exist
        if (!$exists) {
            $history[] = array(
                'book_id' => $book_id,
                'progress' => $progress,
                'timestamp' => current_time('mysql')
            );
        }

        // Keep only last 100 entries
        if (count($history) > 100) {
            usort($history, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            $history = array_slice($history, 0, 100);
        }

        return update_user_meta($user_id, '_pustakabilitas_reading_history', $history);
    }

    /**
     * Clear reading history
     * 
     * @return bool True on success, false on failure
     */
    public function clear_reading_history() {
        $user_id = get_current_user_id();
        return delete_user_meta($user_id, '_pustakabilitas_reading_history');
    }

    /**
     * Get reading progress for a book
     */
    public function get_reading_progress($book_id) {
        $user_id = get_current_user_id();
        $progress = get_user_meta($user_id, '_pustakabilitas_reading_progress_' . $book_id, true);
        return is_numeric($progress) ? intval($progress) : 0;
    }

    /**
     * Get last read time for a book
     */
    public function get_last_read_time($book_id) {
        $user_id = get_current_user_id();
        $history = get_user_meta($user_id, '_pustakabilitas_reading_history', true);
        
        if (!is_array($history)) {
            return false;
        }

        foreach ($history as $entry) {
            if ($entry['book_id'] == $book_id) {
                return $entry['timestamp'];
            }
        }

        return false;
    }

    /**
     * Get book readers count
     */
    public function get_book_readers_count($book_id) {
        $readers = get_post_meta($book_id, 'book_readers', true);
        return is_array($readers) ? count($readers) : 0;
    }

    /**
     * Get book download count
     */
    public function get_book_download_count($book_id) {
        return intval(get_post_meta($book_id, '_pustakabilitas_download_count', true));
    }

    /**
     * Toggle book bookmark
     */
    public function toggle_book_bookmark() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        if (!$book_id) {
            wp_send_json_error('Invalid book ID');
        }
        
        $user_id = get_current_user_id();
        $bookmarks = get_user_meta($user_id, '_pustakabilitas_bookmarks', true);
        
        if (!is_array($bookmarks)) {
            $bookmarks = array();
        }
        
        $bookmark_index = array_search($book_id, array_column($bookmarks, 'book_id'));
        
        if ($bookmark_index !== false) {
            // Remove bookmark
            array_splice($bookmarks, $bookmark_index, 1);
            $action = 'removed';
        } else {
            // Add bookmark
            $bookmarks[] = array(
                'book_id' => $book_id,
                'timestamp' => current_time('mysql'),
                'note' => ''
            );
            $action = 'added';
        }
        
        update_user_meta($user_id, '_pustakabilitas_bookmarks', $bookmarks);
        
        wp_send_json_success(array(
            'action' => $action,
            'message' => $action === 'added' ? 
                __('Buku ditambahkan ke bookmark', 'pustakabilitas') : 
                __('Buku dihapus dari bookmark', 'pustakabilitas')
        ));
    }

    /**
     * Update bookmark note
     */
    public function update_bookmark_note() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        if (!$book_id) {
            wp_send_json_error('Invalid book ID');
        }
        
        $user_id = get_current_user_id();
        $bookmarks = get_user_meta($user_id, '_pustakabilitas_bookmarks', true);
        
        if (!is_array($bookmarks)) {
            wp_send_json_error('Bookmark not found');
        }
        
        $bookmark_index = array_search($book_id, array_column($bookmarks, 'book_id'));
        
        if ($bookmark_index === false) {
            wp_send_json_error('Bookmark not found');
        }
        
        $bookmarks[$bookmark_index]['note'] = $note;
        update_user_meta($user_id, '_pustakabilitas_bookmarks', $bookmarks);
        
        wp_send_json_success(array(
            'message' => __('Catatan bookmark berhasil diperbarui', 'pustakabilitas')
        ));
    }

    /**
     * Remove bookmark
     */
    public function remove_bookmark() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        if (!$book_id) {
            wp_send_json_error('Invalid book ID');
        }
        
        $user_id = get_current_user_id();
        $bookmarks = get_user_meta($user_id, '_pustakabilitas_bookmarks', true);
        
        if (!is_array($bookmarks)) {
            wp_send_json_error('Bookmark not found');
        }
        
        $bookmark_index = array_search($book_id, array_column($bookmarks, 'book_id'));
        
        if ($bookmark_index === false) {
            wp_send_json_error('Bookmark not found');
        }
        
        array_splice($bookmarks, $bookmark_index, 1);
        update_user_meta($user_id, '_pustakabilitas_bookmarks', $bookmarks);
        
        wp_send_json_success(array(
            'message' => __('Bookmark berhasil dihapus', 'pustakabilitas')
        ));
    }

    /**
     * Load more reading history
     */
    public function load_more_reading_history() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        
        $history = $this->get_user_reading_history($limit, $offset);
        
        ob_start();
        foreach ($history as $entry) {
            $book = get_post($entry['book_id']);
            if (!$book) continue;
            
            include(PUSTAKABILITAS_PLUGIN_DIR . 'templates/dashboard/partials/history-item.php');
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
            'hasMore' => count($history) === $limit
        ));
    }

    /**
     * Remove download history
     */
    public function remove_download_history() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $download_id = isset($_POST['download_id']) ? intval($_POST['download_id']) : 0;
        if (!$download_id) {
            wp_send_json_error('Invalid download ID');
        }
        
        $user_id = get_current_user_id();
        $downloads = get_user_meta($user_id, '_pustakabilitas_downloads', true);
        
        if (!is_array($downloads)) {
            wp_send_json_error('Download history not found');
        }
        
        $download_index = array_search($download_id, array_column($downloads, 'id'));
        
        if ($download_index === false) {
            wp_send_json_error('Download history not found');
        }
        
        array_splice($downloads, $download_index, 1);
        update_user_meta($user_id, '_pustakabilitas_downloads', $downloads);
        
        wp_send_json_success(array(
            'message' => __('Riwayat unduhan berhasil dihapus', 'pustakabilitas')
        ));
    }

    /**
     * Track book download
     */
    public function track_book_download() {
        check_ajax_referer('pustakabilitas_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $download_id = isset($_POST['download_id']) ? intval($_POST['download_id']) : 0;
        if (!$download_id) {
            wp_send_json_error('Invalid download ID');
        }
        
        $user_id = get_current_user_id();
        $downloads = get_user_meta($user_id, '_pustakabilitas_downloads', true);
        
        if (!is_array($downloads)) {
            $downloads = array();
        }
        
        $download_index = array_search($download_id, array_column($downloads, 'id'));
        
        if ($download_index !== false) {
            // Update timestamp
            $downloads[$download_index]['timestamp'] = current_time('mysql');
            update_user_meta($user_id, '_pustakabilitas_downloads', $downloads);
            
            // Update book download count
            $book_id = $downloads[$download_index]['book_id'];
            $count = get_post_meta($book_id, '_pustakabilitas_download_count', true);
            update_post_meta($book_id, '_pustakabilitas_download_count', intval($count) + 1);
        }
        
        wp_send_json_success();
    }

    /**
     * Get user book count by type
     * 
     * @param string $type Type of count ('read', 'downloaded', 'bookmarked')
     * @return int Count of books
     */
    public function get_user_book_count($type = 'read') {
        $user_id = get_current_user_id();
        $count = 0;

        switch ($type) {
            case 'read':
                $history = get_user_meta($user_id, '_pustakabilitas_reading_history', true);
                $count = is_array($history) ? count($history) : 0;
                break;
            
            case 'downloaded':
                $downloads = get_user_meta($user_id, '_pustakabilitas_downloads', true);
                $count = is_array($downloads) ? count($downloads) : 0;
                break;
            
            case 'bookmarked':
                $bookmarks = get_user_meta($user_id, '_pustakabilitas_bookmarks', true);
                $count = is_array($bookmarks) ? count($bookmarks) : 0;
                break;
        }

        return $count;
    }

    /**
     * Get user bookmarks
     * 
     * @param int $user_id User ID
     * @return array Array of bookmarks
     */
    public function get_user_bookmarks($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $bookmarks = get_user_meta($user_id, '_pustakabilitas_bookmarks', true);
        return is_array($bookmarks) ? $bookmarks : array();
    }

    /**
     * Get user bookmark count
     * 
     * @return int Count of bookmarks
     */
    public function get_user_bookmark_count() {
        return $this->get_user_book_count('bookmarked');
    }

    /**
     * Get user downloads
     * 
     * @param int $user_id User ID
     * @return array Array of downloads
     */
    public function get_user_downloads($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $downloads = get_user_meta($user_id, '_pustakabilitas_downloads', true);
        return is_array($downloads) ? $downloads : array();
    }

    /**
     * Track book actions (download/read)
     */
    public function track_book_action() {
        check_ajax_referer('pustakabilitas_tracking', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        if (!$book_id || !in_array($action_type, ['download', 'read'])) {
            wp_send_json_error('Invalid parameters');
        }
        
        $user_id = get_current_user_id();
        $current_time = current_time('mysql');
        
        if ($action_type === 'download') {
            // Track downloads
            $downloads = get_user_meta($user_id, '_pustakabilitas_downloads', true);
            if (!is_array($downloads)) {
                $downloads = array();
            }
            
            $downloads[] = array(
                'id' => uniqid(),
                'book_id' => $book_id,
                'timestamp' => $current_time
            );
            
            update_user_meta($user_id, '_pustakabilitas_downloads', $downloads);
            
            // Update book download count
            $book_downloads = get_post_meta($book_id, '_pustakabilitas_downloads', true);
            if (!is_array($book_downloads)) {
                $book_downloads = array();
            }
            $book_downloads[] = array(
                'user_id' => $user_id,
                'timestamp' => $current_time
            );
            update_post_meta($book_id, '_pustakabilitas_downloads', $book_downloads);
            
        } else if ($action_type === 'read') {
            // Track reads
            $reads = get_post_meta($book_id, '_pustakabilitas_reads', true);
            if (!is_array($reads)) {
                $reads = array();
            }
            
            $reads[] = array(
                'user_id' => $user_id,
                'timestamp' => $current_time
            );
            
            update_post_meta($book_id, '_pustakabilitas_reads', $reads);
            
            // Add to reading history
            $this->add_reading_history($book_id, 0); // Start with 0 progress
        }
        
        wp_send_json_success();
    }
}
// End of class
