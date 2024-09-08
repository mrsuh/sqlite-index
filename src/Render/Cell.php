<?php

namespace App\Render;

class Cell
{
    public array $lines;
    public string $backgroundColor;
    public string $fontColor;

    public function __construct(array $lines, string $backgroundColor, string $fontColor)
    {
        $this->lines = $lines;
        $this->backgroundColor = $backgroundColor;
        $this->fontColor = $fontColor;
    }

    public function isEmpty(): bool
    {
        return empty($this->lines);
    }
}
