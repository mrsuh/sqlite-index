<?php

namespace App\Database;

class Trace extends Tree
{
    public string $key;
    public int $comparisons;

    public function __construct(string $key, array $pages, int $comparisons)
    {
        $this->key = $key;
        $this->pages = $pages;
        $this->comparisons = $comparisons;
    }

    public function getNexPageById(int $id): ?Page
    {
        foreach ($this->pages as $index => $page) {
            if ($page->id === $id) {
                return $this->pages[$index + 1] ?? null;
            }
        }

        return null;
    }

    public function getPageById(int $id): ?Page
    {
        foreach ($this->pages as $page) {
            if ($page->id === $id) {
                return $page;
            }
        }

        return null;
    }
}
