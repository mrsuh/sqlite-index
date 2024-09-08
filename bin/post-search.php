<?php

$data = [
    'search-equal' => [
        'search-equal'
    ],
    'search-range' => [
        'search-range-1',
        'search-range-1000000',
    ],
    'search-order' => [
       'search-order-asc',
       'search-order-desc',
    ],
    'search-greater-than' => [
       'search-greater-than',
    ],
    'search-expression' => [
       'search-expression',
    ],
    'search-unique' => [
       'search-unique',
    ],
    'search-partial' => [
       'search-partial',
    ],
    'search-complex-equal' => [
       'search-complex-equal',
    ],
    'search-complex-cardinality-equal' => [
       'search-complex-cardinality-equal-column1',
       'search-complex-cardinality-equal-column2',
    ],
    'search-complex-cardinality-greater-than' => [
       'search-complex-cardinality-greater-than-column1',
       'search-complex-cardinality-greater-than-column2',
    ],
];

$content = '';

foreach($data as $name => $searchNames) {

    $databaseInfo = 'data/search/database/'.$name.'.txt';

    $content .= '### ' . $name. PHP_EOL . PHP_EOL;
    $content .= file_get_contents($databaseInfo) . PHP_EOL;

    foreach($searchNames as $searchName) {
        $content .= '#### ' . $searchName. PHP_EOL . PHP_EOL;

        $searchInfo = 'data/search/render/'.$searchName.'.txt';
        $searchImage = 'data/search/render/'.$searchName.'.webp';

        $content .= '```sql' . PHP_EOL;
        $content .= file_get_contents($searchInfo);
        $content .= '```' . PHP_EOL;
        $content .= sprintf('![](%s)', $searchImage) . PHP_EOL;
    }

}

file_put_contents(__DIR__ . '/../post-search-auto.md', $content);

