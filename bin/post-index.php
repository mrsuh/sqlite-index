<?php

$data = [
    'index-1' => [
        'index-1'
    ],
    'index-1000' => [
        'index-1000'
    ],
    'index-1000000' => [
        'index-1000000'
    ],
    'index-order' => [
        'index-order-asc',
        'index-order-desc'
    ],
    'index-expression' => [
        'index-expression'
    ],
    'index-unique' => [
        'index-unique'
    ],
    'index-partial' => [
        'index-partial'
    ],
    'index-complex' => [
        'index-complex'
    ],
    'index-time' => [
        'index-time-before',
        'index-time-after'
    ],
    'index-vacuum' => [
        'index-vacuum-before',
        'index-vacuum-after'
    ],
    'index-reindex' => [
        'index-reindex-before',
        'index-reindex-after',
    ],
    'index-text' => [
        'index-text'
    ],
    'index-real' => [
        'index-real'
    ],
    'index-integer-text' => [
        'index-integer-text'
    ],
];

$content = '';

foreach ($data as $name => $indexNames) {
    $content .= '### ' . $name . PHP_EOL . PHP_EOL;

    $info = 'data/index/database/' . $name . '.txt';
    $content .= file_get_contents($info) . PHP_EOL;

    foreach ($indexNames as $indexName) {
        $content .= '#### ' . $indexName . PHP_EOL;
        $image = 'data/index/render/' . $indexName . '.webp';
        $content .= sprintf('![](%s)', $image) . PHP_EOL;
    }

    $content .= PHP_EOL;

    $diffPath = 'data/index/render/' . $name . '.txt';
    if (file_exists($diffPath)) {
        $content .= '```bash' . PHP_EOL;
        $content .= file_get_contents($diffPath) . PHP_EOL;
        $content .= '```' . PHP_EOL;
    }
    $content .= PHP_EOL;
}

file_put_contents(__DIR__ . '/../post-index-auto.md', $content);
