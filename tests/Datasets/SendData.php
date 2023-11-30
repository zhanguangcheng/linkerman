<?php

dataset('send data', [
    'empty' => [[]],
    'one var' => [['foo' => 'bar']],
    'two vars' => [['foo' => 'bar', 'key' => 'hello Linkerman']],
    'indexed-array' => [['indexed-array' => ['this', 'is', 'an', 'array']]],
    'associative-array' => [['associative-array' => [
        'foo' => 'bar',
        'hello' => 'Linkerman',
    ]
    ]],
    //'multidimensional-array' => [[]],
    'all mixed' => [[
        'foo' => 'bar',
        'key' => 'Hello Linkerman',
        'indexed-array' => ['this', 'is', 'an', 'array'],
        'associative-array' => [
            'foo' => 'bar',
            'hello' => 'Linkerman',
        ],
    ]],

    '10k body' => [[
        'data' => str_repeat('a', 1000),
    ]],
]);
