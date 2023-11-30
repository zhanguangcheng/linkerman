<?php

define("__APP_PATH__", realpath(__DIR__ . '/../../'));

dataset('servers', [
    'REQUEST_METHOD' => [['REQUEST_METHOD', "POST"]],
    'DOCUMENT_ROOT' => [['DOCUMENT_ROOT', __APP_PATH__]],
    'SCRIPT_FILENAME' => [['SCRIPT_FILENAME', __APP_PATH__ . '/public/index.php']],
    'SCRIPT_NAME' => [['SCRIPT_NAME', '/index.php']],
    'REQUEST_URI' => [['REQUEST_URI', '/server?name=REQUEST_URI']],
    'SERVER_SOFTWARE' => [['SERVER_SOFTWARE', 'workerman']],
    'HTTP_USER_AGENT' => [['HTTP_USER_AGENT', 'Testing/1.0']],
    'REMOTE_ADDR' => [['REMOTE_ADDR', '127.0.0.1']],
    'SERVER_PORT' => [['SERVER_PORT', '9001']],
    'SERVER_ADDR' => [['SERVER_ADDR', '127.0.0.1']],
    'CONTENT_TYPE' => [['CONTENT_TYPE', 'application/x-www-form-urlencoded']],
    'CONTENT_LENGTH' => [['CONTENT_LENGTH', '14']],
    'HTTP_X_CUSTOM_HEADER' => [['HTTP_X_CUSTOM_HEADER', 'Linkerman']],
]);

it('tests SERVER', function (array $data) {
    $response = HttpClient()->post('/server', [
        'headers' => [
            'X-Custom-Header' => 'Linkerman',
        ],
        'query' => [
            'name' => $data[0],
        ],
        'form_params' => [
            'name' => 'linkerman'
        ],
    ]);
    expect($response->getBody()->getContents())
        ->toBe($data[1]);
})->with('servers');

