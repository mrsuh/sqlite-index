<?php

namespace App\Database;

class Search extends Tree
{
    public string $query;
    public string $explain;
    public string $seekCount;
    public string $compareCount;
    public string $filterCompareCount;
    public string $result;

    /** @var Trace[] */
    public array $traces;
}
