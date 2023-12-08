<?php

namespace Tests;

use Linkerman\Linkerman;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

require_once __DIR__ . '/../vendor/autoload.php';

Linkerman::init();

define("APP_PATH", realpath(__DIR__ . '/../'));

global $worker;
$worker = new Worker('http://127.0.0.1:9001');
$worker->name = "Linkerman Tests";
$worker->count = 2;
$worker->onMessage = static function (TcpConnection $connection, Request $request): void {
    $response = match ($request->path()) {
        '/' => 'Hello World!',
        '/get' => json_encode($_GET),
        '/post' => json_encode($_POST),
        '/request' => json_encode($_REQUEST),
        '/files' => json_encode($_FILES),
        '/headers' => json_encode(getallheaders()),
        '/method' => $_SERVER['REQUEST_METHOD'],
        '/server_ip' => $_SERVER['SERVER_ADDR'],
        '/ip' => $_SERVER['REMOTE_ADDR'],
        '/cookies' => cookies(),
        '/server' => $_SERVER[$_GET['name']],
        '/func-header' => func_header(),
        '/sessions' => sessions(),
        default => (static function (): string {
            header('HTTP/1.1 404 Not Found');
            return '404 Not Found';
        })(),
    };

    $connection->send($response);
};

Worker::runAll();

function cookies(): string
{
    if (isset($_GET['set'])) {
        foreach ($_GET['set'] as $name => $value) {
            setcookie($name, $value);
        }
        return json_encode($_COOKIE);
    }

    if (isset($_GET['delete'])) {
        foreach ($_GET['delete'] as $name) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
                setcookie($name, '', -1);
            }
        }
        return json_encode($_COOKIE);
    }
    return json_encode($_COOKIE);
}

function sessions(): string
{
    return match ($_GET['type']) {
        'session-start' => (static function () {
            $result = [];
            $result[] = session_status() === PHP_SESSION_DISABLED;
            $result[] = session_start();
            $result[] = session_status() === PHP_SESSION_ACTIVE;
            $_SESSION['name'] = 'linkerman';
            return json_encode($result);
        })(),
        'session-get' => (static function () {
            session_start();
            return $_SESSION['name'] ?? '';
        })(),
        'session-id' => (static function () {
            session_id('linkerman');
            session_start();
            return session_id();
        })(),
        'session-name' => (static function () {
            if (isset($_GET['reset'])) {
                session_name('PHPSID');
                session_start();
                return '';
            }
            session_name('PHPSESSID');
            session_start();
            return session_name();
        })(),
        'session-regenerate-id' => (static function () {
            if (isset($_GET['set'])) {
                session_id($_GET['set']);
                session_start();
            } else {
                session_start();
                session_regenerate_id();
            }
            return '';
        })(),
        'session-unset' => (static function () {
            session_start();
            if (isset($_GET['set'])) {
                $_SESSION['name'] = 'linkerman';
                return '';
            } elseif (isset($_GET['unset'])) {
                return (string)session_unset();
            } else {
                return json_encode($_SESSION);
            }
        })(),
        'session-destroy' => (static function () {
            session_start();
            $_SESSION['name'] = 'linkerman';
            $result[] = session_destroy();
            $result[] = session_status();
            $file = session_save_path() . '/session_' . session_id();
            $result[] = empty($_SESSION);
            $result[] = file_exists($file);
            return json_encode($result);
        })(),
    };
}

function func_header(): string
{
    $response = '';
    match ($_GET['type']) {
        'response-head' => header('HTTP/1.1 403 Forbidden'),
        'response-code' => header('Content-Type: application/json', true, 404),
        'location' => header('Location: https://github.com/zhanguangcheng/linkerman'),
        'set-cookie' => header('Set-Cookie: name=linkerman'),
        'custom-header' => header('X-Custom-Header: linkerman'),
        'header-remove' => (static function () {
            header('X-Custom-Header: linkerman');
            header_remove();
            header('Set-Cookie: name=linkerman');
            header('Content-Type: application/json');
            header_remove('Set-Cookie');
        })(),
        'headers-list' => (static function () use (&$response) {
            header('X-Custom-Header: linkerman');
            header('Set-Cookie: name=linkerman');
            header('Set-Cookie: name2=linkerman2');
            $response = json_encode(headers_list());
        })(),
        'header-response-code' => (static function () use (&$response) {
            http_response_code(403);
            $response = http_response_code();
        })(),
        'getallheaders' => (static function () use (&$response) {
            $response = json_encode(getallheaders());
        })(),
        'setcookie' => (static function () {
            setcookie('name', 'linkerman', time() + 3600, '/', '127.0.0.1', true, true, 'Lax');
            setcookie('name2', 'linkerman2', [
                'expires' => time() + 3600,
                'path' => '/',
                'domain' => '127.0.0.1',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            setrawcookie('name3', '=');
        })(),
    };
    return $response;
}