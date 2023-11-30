# Linkerman

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/zhanguangcheng/linkerman/blob/master/LICENSE)

Linkerman is a library that utilizes Workerman to accelerate PHP frameworks.

## How it works

1. When the request arrives, the Workerman's Request object is called to register the hyperglobal variables: `$_GET` `$_POST` `$_COOKIE` `$_FILES` `$_SERVER` `$_REQUEST`
2. Rewrite the built-in PHP functions such as`header()` `setcookie()` `session_*`, etc., and convert them into Workerman's Response objects


## Installation

You can install Linkerman via Composer:

```bash
composer require zhanguangcheng/linkerman
```

## Requirements

- PHP >= 8.0
- Workerman/Workerman ^4.1

## Usage

server.php
```php
<?php

use Linkerman\Linkerman;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

Linkerman::init();

$worker = new Worker('http://127.0.0.1:8080');
$worker->count = 8;
$worker->name = 'linkerman';
$worker->onWorkerStart = static function () {
    require __DIR__ . '/start.php';
};
$worker->onMessage = static function (TcpConnection $connection, Request $request) {
    $connection->send(run($request));
};

Worker::runAll();
```

The Yii 2 framework's start.php looks like this
```php
<?php

use yii\base\InvalidConfigException;
use yii\web\Application;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
global $config;
$config = require __DIR__ . '/config/web.php';

/**
 * @throws InvalidConfigException
 */
function run($request): string
{
    global $config;
    ob_start();
    $app = new Application($config);
    $app->run();
    return (string)ob_get_clean();
}
```

php.ini
```ini
;error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
opcache.enable=1
opcache.enable_cli=1
opcache.validate_timestamps=0
opcache.save_comments=0
opcache.enable_file_override=1
opcache.huge_code_pages=1

mysqlnd.collect_statistics = Off

memory_limit = 512M

opcache.jit_buffer_size=128M
opcache.jit=tracing

disable_functions=set_time_limit,header,header_remove,headers_sent,headers_list,http_response_code,setcookie,setrawcookie,session_start,session_id,session_name,session_save_path,session_status,session_write_close,session_regenerate_id,session_unset
```

Start the service
```bash
php -c php.ini server.php start
```

## License

Linkerman is open-sourced software licensed under the [MIT license](https://github.com/zhanguangcheng/linkerman/blob/master/LICENSE).


## Security Vulnerabilities

If you discover a security vulnerability within Linkerman, Please submit an [issue](https://github.com/zhanguangcheng/linkerman/issues) or send an e-mail to zhanguangcheng at 14712905@qq.com. All security vulnerabilities will be promptly addressed.

## Tests

You can run the tests with:

```bash
vendor/bin/pest
```

## References

- [Workerman](https://www.workerman.net/)
- [AdapterMan](https://github.com/joanhey/AdapterMan)
- [Change the storage mode of the session to Redis](https://www.workerman.net/doc/workerman/http/session-control.html)

## More Information

You can find more information about Linkerman and its usage on the [homepage](https://github.com/zhanguangcheng/linkerman).

This README.md file is generated based on the provided composer.json. Please make sure to update it with the relevant information and usage instructions for the Linkerman library.