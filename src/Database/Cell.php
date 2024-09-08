<?php

namespace App\Database;

class Cell
{
    public int $id;
    public int $rowId = 0;
    public string $payload = '';
    public ?int $leftChildId = null;
    public bool $isSelected = false;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
