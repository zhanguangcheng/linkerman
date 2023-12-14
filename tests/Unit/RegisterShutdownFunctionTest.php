<?php

test("tests register_shutdown_function", function () {
    $response = HttpClient()->get('/register_shutdown_function');
    expect($response->getBody()->getContents())->toBe('1')
        ->and($response->getHeaderLine('X-Shutdown'))->toBe('linkerman');
});
