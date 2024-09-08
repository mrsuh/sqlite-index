<?php

namespace App\Database;

class Tree
{
    public Page $root;
    /** @var Page[] */
    public array $pages = [];

    /**
     * @return array<int, Page[]>
     */
    public function toLayers(): array
    {
        $layers = [];
        self::layer($this->root, $this->pages, $layers, 0);

        return $layers;
    }

    private static function layer(Page $page, array $pages, array &$layers, int $depth): void
    {
        if (!isset($layers[$depth])) {
            $layers[$depth] = [];
        }

        $layers[$depth][] = $page;
        foreach ($page->childIds as $child) {
            self::layer($pages[$child], $pages, $layers, $depth + 1);
        }
    }

    public function getTotalPageCount(): int
    {
        return count($this->pages);
    }

    public function getTotalCellCount(): int
    {
        return self::getCellCount($this->pages);
    }

    /**
     * @param Page[] $pages
     * @return int
     */
    public static function getCellCount(array $pages): int
    {
        $count = 0;
        foreach ($pages as $page) {
            $count += count($page->cells);
        }

        return $count;
    }
}
