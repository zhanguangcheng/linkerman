# Linkerman

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/zhanguangcheng/linkerman/blob/master/LICENSE)

Linkerman is a library that utilizes Workerman to accelerate PHP frameworks.

If your app or fw use a Front Controller, 99% that will work. Requires minimun PHP 8.0.


## How it works

1. When the request arrives, the Workerman's Request object is called to register the hyperglobal variables: `$_GET` `$_POST` `$_COOKIE` `$_FILES` `$_SERVER` `$_REQUEST`
2. Rewrite the built-in PHP functions such as`header()` `setcookie()` `session_*`, etc., and convert them into Workerman's Response objects


## Example of framework launch template
* [laravel-workerman](https://github.com/zhanguangcheng/laravel-workerman)
* [thinkphp6-workerman](https://github.com/zhanguangcheng/thinkphp6-workerman)
* [yii2-workerman](https://github.com/zhanguangcheng/yii2-workerman)


## Installation

You can install Linkerman via Composer:

```bash
composer require zhanguangcheng/linkerman
```

## Requirements

- PHP >= 8.0

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

The Yii2 framework's start.php looks like this
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

Add to php.ini file
```ini
disable_functions=set_time_limit,header,header_remove,headers_sent,headers_list,http_response_code,setcookie,setrawcookie,session_start,session_id,session_name,session_save_path,session_status,session_write_close,session_regenerate_id,session_unset,session_destroy,session_set_cookie_params,session_get_cookie_params,is_uploaded_file,move_uploaded_file
```

Start the service
```bash
php server.php start
```

## Precautions

### Functions or statements that are known to be incompatible
> https://www.workerman.net/doc/workerman/appendices/unavailable-functions.html

* `pcntl_fork()`
  * Solution: Set the number of processes in advance
* `exit()` `die()`
  * Solution: Replace with function `exit_exception()`
* `file_get_contents("php://input")`
  * Solution: Replace with function `request_raw_body()`
* `register_shutdown_function()`
  * Why: Since the resident memory runs, the registered callback function is not actually executed, which may lead to memory leaks
  * Solution: Replace with function `register_shutdown_function_user()

### How to access the Connection Object and Request Object of Workerman
```php
// Workerman Connection Object
$GLOBALS['WORKERMAN_CONNECTION'];

// Workerman Request Object
$GLOBALS['WORKERMAN_REQUEST'];
````

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