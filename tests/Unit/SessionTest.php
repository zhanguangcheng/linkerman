<?php

use GuzzleHttp\Cookie\CookieJar;

test("tests session_start session_status", function() {
    $cookie = new CookieJar();
    $response = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-start']
    ]);
    expect($response->getBody()->getContents())->toBeJson()->json()->toBe([
        true, true, true
    ]);

    $response2 = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-get']
    ]);
    expect($response2->getBody()->getContents())->toBe('linkerman');
});

test("tests session_id", function() {
    $response = HttpClient()->get('/sessions', [
        'query' => ['type' => 'session-id']
    ]);
    expect($response->getBody()->getContents())->toBe('linkerman')
        ->and($response->getHeaderLine('Set-Cookie'))->toStartWith('PHPSID=linkerman');
});

test("tests session_regenerate_id", function() {
    $cookie = new CookieJar();
    $response = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-regenerate-id', 'set'=>'linkerman']
    ]);
    expect($response->getHeaderLine('Set-Cookie'))->toStartWith('PHPSID=linkerman');

    $response = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-regenerate-id']
    ]);
    $cookie = $response->getHeaderLine('Set-Cookie');
    expect(explode('=', $cookie)[1])->not()->toStartWith('linkerman');
});

test("tests session_unset", function() {
    $cookie = new CookieJar();
    HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-unset', 'set' => 1]
    ]);

    $response = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-unset', 'unset' => 1]
    ]);
    expect($response->getBody()->getContents())->toBe('1');

    $response = HttpClient()->get('/sessions', [
        'cookies' => $cookie,
        'query' => ['type' => 'session-unset']
    ]);
    expect($response->getBody()->getContents())->toBe('[]');
});

test("tests session_name", function() {
    $response = HttpClient()->get('/sessions', [
        'query' => ['type' => 'session-name']
    ]);
    expect($response->getBody()->getContents())->toBe('PHPSESSID')
        ->and($response->getHeaderLine('Set-Cookie'))->toStartWith('PHPSESSID=');

    HttpClient()->get('/sessions', [
        'query' => ['type' => 'session-name', 'reset'=>1]
    ]);
});
