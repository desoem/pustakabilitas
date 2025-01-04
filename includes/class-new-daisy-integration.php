<?php
class Pustakabilitas_New_Daisy_Integration {
    private $plugin_path;
    private $plugin_url;

    public function __construct() {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));
        
        // Register proxy endpoint
        add_action('init', [$this, 'register_proxy_endpoint']);
    }

    public function register_proxy_endpoint() {
        add_rewrite_rule(
            'pustakabilitas/proxy/?$',
            'index.php?pustakabilitas_proxy=1',
            'top'
        );

        add_filter('query_vars', function($vars) {
            $vars[] = 'pustakabilitas_proxy';
            return $vars;
        });

        add_action('template_redirect', function() {
            if (get_query_var('pustakabilitas_proxy')) {
                require_once($this->plugin_path . 'daisywp/proxy.php');
                exit;
            }
        });
    }
}

new Pustakabilitas_New_Daisy_Integration();
