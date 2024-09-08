<?php

namespace App\Database;

class Page
{
    public int $id;
    /** @var Cell[] */
    public array $cells = [];
    public ?int $rightChildId = null;
    public array $childIds = [];
    public bool $isSelected = false;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function addCell(Cell $cell): void
    {
        $this->cells[$cell->id] = $cell;
    }

    public function getCellById(int $id): ?Cell
    {
        return $this->cells[$id] ?? null;
    }

    public function getSelectedCell(): ?Cell
    {
        foreach ($this->cells as $cell) {
            if ($cell->isSelected) {
                return $cell;
            }
        }

        return null;
    }

    public function addChildId(int $childId): void
    {
        $this->childIds[$childId] = $childId;
    }
}
