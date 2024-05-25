<?php

/**
 * This file is part of linkerman.
 *
 * @author  zhanguangcheng<14712905@qq.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

namespace Linkerman;

use Workerman\Connection\TcpConnection;

class Http extends \Workerman\Protocols\Http
{
    protected static $_requestClass = 'Linkerman\Request';

    public static ?Request $request = null;
    public static ?Response $response = null;
    public static bool $sessionIsStarted = false;
    public static ?string $sessionSavePath = null;
    public static array $shutdownCallbacks = [];
    public static bool $autoCleanAtEnd = true;

    /**
     * @param $recv_buffer
     * @param TcpConnection $connection
     * @return Request
     */
    public static function decode($recv_buffer, TcpConnection $connection): Request
    {
        $request = parent::decode($recv_buffer, $connection);
        /** @var Request $request */
        static::$request = $request;
        static::$response = new Response();
        static::registerGlobalVariables($connection);
        static::$shutdownCallbacks = [];
        static::$sessionIsStarted = false;
        return $request;
    }

    /**
     * @param $response
     * @param TcpConnection $connection
     * @return string
     */
    public static function encode($response, TcpConnection $connection): string
    {
        if (static::$autoCleanAtEnd) {
            if (static::$sessionIsStarted) {
                \session_write_close();
            }
            \call_shutdown_function();
        }
        if ($response instanceof \Workerman\Protocols\Http\Response) {
            return parent::encode($response, $connection);
        }
        static::$response->withBody((string)$response);
        return parent::encode(static::$response, $connection);
    }

    public static function registerGlobalVariables($connection): void
    {
        defined('APP_PATH') or define('APP_PATH', dirname(__DIR__, 4));
        $request = static::$request;
        $_SESSION = null;
        $_GET = $request->get();
        $_POST = $request->post();
        $_REQUEST = \array_merge($_GET, $_POST);
        $_FILES = $request->file();
        $_COOKIE = $request->cookie();
        $GLOBALS['WORKERMAN_CONNECTION'] = $connection;
        $GLOBALS['WORKERMAN_REQUEST'] = $request;

        global $_SERVER;
        $_SERVER = [
            'HTTP_COOKIE' => '',
            'HTTP_USER_AGENT' => '',
            'HTTP_ACCEPT' => '',
            'HTTP_HOST' => '',
            'HTTP_ACCEPT_ENCODING' => '',
            'HTTP_CONNECTION' => '',
            'SERVER_SOFTWARE' => 'workerman',
            'SERVER_NAME' => $request->host(true),
            'SERVER_ADDR' => $connection->getLocalIp(),
            'SERVER_PORT' => $connection->getLocalPort(),
            'REMOTE_ADDR' => $connection->getRemoteIp(),
            'REMOTE_PORT' => $connection->getRemotePort(),
            'DOCUMENT_ROOT' => APP_PATH,
            'SCRIPT_FILENAME' => APP_PATH . '/public/index.php',
            'SERVER_PROTOCOL' => $request->protocolVersion(),
            'REQUEST_METHOD' => $request->method(),
            'QUERY_STRING' => $request->queryString(),
            'REQUEST_URI' => $request->uri(),
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
        ];
        if (null !== ($contentType = $request->header('content-type'))) {
            $_SERVER['CONTENT_TYPE'] = $contentType;
        }
        if (null !== $contentLength = $request->header('content-length')) {
            $_SERVER['CONTENT_LENGTH'] = $contentLength;
        }
        foreach ($request->header() as $key => $value) {
            $_SERVER['HTTP_' . \strtoupper(\str_replace('-', '_', $key))] = $value;
        }
        if (isset($_SERVER['HTTP_HTTPS'])) {
            $_SERVER['HTTPS'] = $_SERVER['HTTP_HTTPS'];
        }
    }
}
