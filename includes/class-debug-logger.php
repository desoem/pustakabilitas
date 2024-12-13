<?php
class Pustakabilitas_Debug_Logger {
    private static $log_file;
    
    public static function init() {
        self::$log_file = WP_CONTENT_DIR . '/pustakabilitas-debug.log';
        
        if (!file_exists(self::$log_file)) {
            touch(self::$log_file);
        }
    }
    
    public static function log($message, $type = 'info') {
        if (!self::$log_file) {
            self::init();
        }
        
        $timestamp = current_time('mysql');
        $formatted_message = sprintf(
            "[%s] [%s] %s\n",
            $timestamp,
            strtoupper($type),
            is_array($message) || is_object($message) ? print_r($message, true) : $message
        );
        
        error_log($formatted_message, 3, self::$log_file);
    }
    
    public static function get_logs($lines = 100) {
        if (!file_exists(self::$log_file)) {
            return [];
        }
        
        $logs = file(self::$log_file);
        return array_slice($logs, -$lines);
    }
    
    public static function clear_logs() {
        if (file_exists(self::$log_file)) {
            unlink(self::$log_file);
            self::init();
        }
    }
} 