<?php

namespace App\Util;

class Logger {
    private static $log_file_path = null;

    private static function getLogPath() {
        if (self::$log_file_path === null) {
            // Assumes the Util directory is one level deep from the project root.
            self::$log_file_path = dirname(__DIR__, 2) . '/app.log';
        }
        return self::$log_file_path;
    }

    private static function log($level, $message, $context = []) {
        // Ensure the message is a string
        if (!is_string($message)) {
            $message = print_r($message, true);
        }

        $date = date('Y-m-d H:i:s');
        $context_str = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : '';
        
        $log_entry = "[$date] [$level]: $message
";
        if ($context_str) {
            $log_entry .= "Context: $context_str
";
        }
        $log_entry .= "---------------------------------
";
        
        // Safely append to the log file.
        // The '3' means the message is appended to the file destination.
        error_log($log_entry, 3, self::getLogPath());
    }

    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }

    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }

    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
}
