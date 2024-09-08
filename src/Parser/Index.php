<?php

namespace App\Parser;

use App\Database\Cell;
use App\Database\Page;

class Index
{
    public static function parse(string $filePath): \App\Database\Index
    {
        $page = null;
        $isRoot = true;
        $tree = new \App\Database\Index();
        foreach (explode(PHP_EOL, file_get_contents($filePath)) as $line) {
            preg_match('/^sqlite3DebugBtreeIndexDump:\spage,\snumber=(\d+),\srightChildPageNumber=(-{0,1}\d+)/', $line, $match);
            if (isset($match[1])) {

                $page = new Page((int)$match[1]);
                if ($isRoot) {
                    $tree->root = $page;
                    $isRoot = false;
                }

                $rightChildIndex = (int)$match[2] === -1 ? null : (int)$match[2];
                if ($rightChildIndex !== null) {
                    $page->rightChildId = $rightChildIndex;
                    $page->addChildId($rightChildIndex);
                }

                $tree->addPage($page);
            }

            preg_match('/^sqlite3DebugBtreeIndexDump:\scell,\snumber=(\d+),\sleftChildPageNumber=(-{0,1}\d+), payload=([^,]+), rowId=(\d+)/', $line, $match);
            if (isset($match[1])) {

                $cell = new Cell((int)$match[1]);
                $cell->rowId = (int)$match[4];
                $cell->payload = trim($match[3]);

                $leftChildIndex = (int)$match[2] === -1 ? null : (int)$match[2];
                if ($leftChildIndex !== null) {
                    $cell->leftChildId = $leftChildIndex;
                    $page->addChildId($leftChildIndex);
                }

                $page->addCell($cell);
            }
        }

        return $tree;
    }
}
