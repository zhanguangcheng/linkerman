<?php

test("tests header response head", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'response-head']
    ]);
    expect($response->getStatusCode())->toBe(403)
        ->and($response->getReasonPhrase())->toBe('Forbidden');
});

test("tests header response code", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'response-code']
    ]);
    expect($response->getStatusCode())->toBe(404);
});

test("tests header location", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'location'],
        'allow_redirects' => false
    ]);
    expect($response->getStatusCode())->toBe(302)
        ->and($response->getHeaderLine('Location'))->toBe('https://github.com/zhanguangcheng/linkerman');
});

test("tests header set cookie", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'set-cookie']
    ]);
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getHeaderLine('Set-Cookie'))->toBe('name=linkerman');
});

test("tests header custom header", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'custom-header']
    ]);
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getHeaderLine('X-Custom-Header'))->toBe('linkerman');
});

test("tests header_remove", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'header-remove']
    ]);
    expect($response->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and($response->getHeaderLine('X-Custom-Header'))->toBe('')
        ->and($response->getHeaderLine('Set-Cookie'))->toBe('');
});

test("tests headers_list", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'headers-list']
    ]);
    expect($response->getBody()->getContents())->toBe('["X-Custom-Header: linkerman","Set-Cookie: name=linkerman; name2=linkerman2"]');
});

test("tests header_response_code", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'header-response-code']
    ]);
    expect($response->getStatusCode())->toBe(403)
        ->and($response->getBody()->getContents())->toBe('403');
});

test("tests getallheaders", function () {
    $response = HttpClient()->get('/func-header', [
        'headers' => [
            'X-Custom-Header' => 'linkerman'
        ],
        'query' => ['type' => 'getallheaders']
    ]);
    $json = json_decode($response->getBody()->getContents(), true);
    expect($json['X-Custom-Header'])->toBe('linkerman');
});

test("tests setcookie", function () {
    $response = HttpClient()->get('/func-header', [
        'query' => ['type' => 'setcookie']
    ]);
    expect($response->getHeader('Set-Cookie'))->toBe([
        "name=linkerman; Domain=127.0.0.1; Max-Age=3600; Path=/; Secure; HttpOnly; SameSite=Lax",
        "name2=linkerman2; Domain=127.0.0.1; Max-Age=3600; Path=/; Secure; HttpOnly; SameSite=Lax",
        "name3==",
    ]);
});
