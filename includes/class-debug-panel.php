<?php
class Pustakabilitas_Debug_Panel {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_debug_menu']);
        add_action('wp_ajax_clear_pustakabilitas_logs', [$this, 'clear_logs']);
    }
    
    public function add_debug_menu() {
        add_submenu_page(
            'edit.php?post_type=pustakabilitas_book',
            'Debug Panel',
            'Debug Panel',
            'manage_options',
            'pustakabilitas-debug',
            [$this, 'render_debug_page']
        );
    }
    
    public function render_debug_page() {
        ?>
        <div class="wrap">
            <h1>Pustakabilitas Debug Panel</h1>
            
            <div class="debug-controls">
                <button id="clearLogs" class="button button-secondary">Clear Logs</button>
                <button id="refreshLogs" class="button button-primary">Refresh Logs</button>
            </div>
            
            <div class="debug-info">
                <h2>System Information</h2>
                <ul>
                    <li>PHP Version: <?php echo PHP_VERSION; ?></li>
                    <li>WordPress Version: <?php echo get_bloginfo('version'); ?></li>
                    <li>Plugin Version: <?php echo PUSTAKABILITAS_VERSION; ?></li>
                    <li>Debug Mode: <?php echo WP_DEBUG ? 'Enabled' : 'Disabled'; ?></li>
                </ul>
            </div>
            
            <div class="debug-logs">
                <h2>Recent Logs</h2>
                <pre id="logContent"><?php
                    $logs = Pustakabilitas_Debug_Logger::get_logs();
                    echo esc_html(implode('', $logs));
                ?></pre>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clearLogs').on('click', function() {
                $.post(ajaxurl, {
                    action: 'clear_pustakabilitas_logs',
                    nonce: '<?php echo wp_create_nonce('clear_pustakabilitas_logs'); ?>'
                }).done(function() {
                    $('#logContent').empty();
                });
            });
            
            $('#refreshLogs').on('click', function() {
                location.reload();
            });
        });
        </script>
        <?php
    }
    
    public function clear_logs() {
        check_ajax_referer('clear_pustakabilitas_logs', 'nonce');
        Pustakabilitas_Debug_Logger::clear_logs();
        wp_send_json_success();
    }
} 