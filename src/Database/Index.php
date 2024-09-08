<?php

namespace App\Database;

class Index extends Tree
{
    public function addPage(Page $page): void
    {
        $this->pages[$page->id] = $page;
    }

    public function getPageById(int $id): ?Page
    {
        return $this->pages[$id] ?? null;
    }
}
