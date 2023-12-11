<?php

/**
 * This file is part of linkerman.
 *
 * @author  zhanguangcheng<14712905@qq.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Linkerman;

use Exception;

class Linkerman
{
    const VERSION = '0.2.0';

    const REWRITE_FUNCTIONS = [
        'set_time_limit',
        'header',
        'header_remove',
        'headers_sent',
        'headers_list',
        'http_response_code',
        'setcookie',
        'setrawcookie',

        'session_start',
        'session_id',
        'session_name',
        'session_save_path',
        'session_status',
        'session_write_close',
        'session_regenerate_id',
        'session_unset',
        'session_destroy',

        'is_uploaded_file',
        'move_uploaded_file',
    ];

    public static function init(): void
    {
        try {
            static::checkEnv();
            require_once __DIR__ . '/Functions.php';
            \class_alias(Http::class, \Protocols\Http::class);
            \fwrite(STDOUT, "Linkerman v" . static::VERSION . " OK\n");
        } catch (Exception $e) {
            \fwrite(STDERR, "Linkerman v" . static::VERSION . " Error:\n" . $e->getMessage());
            exit(1);
        }
    }

    /**
     * @throws Exception
     */
    public static function checkEnv(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            throw new Exception("* PHP version must be 8 or higher." . PHP_EOL . "* Actual PHP version: " . \PHP_VERSION . PHP_EOL);
        }
        foreach (static::REWRITE_FUNCTIONS as $fun) {
            if (\function_exists($fun)) {
                $iniPath = \php_ini_loaded_file();
                $methods = \implode(',', static::REWRITE_FUNCTIONS);
                throw new Exception("Functions not disabled in php.ini.\nAdd in file: $iniPath\ndisable_functions=$methods\n");
            }
        }
    }
}
