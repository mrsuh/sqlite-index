<?php

namespace App\Render;

class Page
{
    /** @var Cell[] */
    public array $cells;

    public function __construct(array $cells)
    {
        $this->cells = $cells;
    }
}
