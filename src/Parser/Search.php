<?php

namespace App\Parser;

use App\Database;

class Search
{
    public static function parse(string $filePath): Database\Search
    {
        $search = new Database\Search();
        $lines = explode(PHP_EOL, file_get_contents($filePath));
        foreach ($lines as $index => $line) {
            if (str_starts_with($line, '### QUERY')) {
                $search->query = $lines[$index + 1];
                continue;
            }

            if (str_starts_with($line, '### SCANSTATS EST')) {
                $search->explain = implode(PHP_EOL, self::getLinesPart(array_slice($lines, $index + 1)));
                continue;
            }

            if (str_starts_with($line, '### SEEK COUNT')) {
                $search->seekCount = (int)$lines[$index + 1];
                continue;
            }

            if (str_starts_with($line, '### SEARCH DUMP')) {
                $search->traces = self::parseDump(
                    self::getLinesPart(array_slice($lines, $index + 1))
                );
                $count = self::countCompare(
                    self::getLinesPart(array_slice($lines, $index + 1))
                );
                $search->compareCount = $count[0];
                $search->filterCompareCount = $count[1];
                continue;
            }

            if (str_starts_with($line, '### RESULT')) {
                $search->result = implode(PHP_EOL,
                    self::getLinesPart(array_slice($lines, $index + 1))
                );
                continue;
            }
        }

        return $search;
    }

    private static function getLinesPart(array $lines): array
    {
        $data = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, '###')) {
                break;
            }

            $data[] = $line;
        }

        return $data;
    }

    private static function parseDump(array $lines): array
    {
        $traces = [];
        $pages = [];
        $page = null;
        $key = null;
        $comparisons = 0;
        $sqlite3BtreeNext = false;
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            $line = trim($line);

            if ($key !== null && strpos($line, 'ResultRow') !== false) {
                $traces[] = new \App\Database\Trace($key, $pages, $comparisons);
                $key = null;
                $comparisons = 0;
                continue;
            }

            preg_match('/^sqlite3DebugBtreeIndexMoveto: key=([^,]+)/', $line, $match);
            if (isset($match[1])) {
                $key = trim($match[1]);
                $pages = [];
                $comparisons = 0;
                continue;
            }

            preg_match('/^sqlite3DebugBtreeIndexMoveto: pageNumber=(\d+), cellNumber=(\d+)/', $line, $match);
            if (isset($match[1])) {
                $pageId = (int)$match[1];
                $cellId = (int)$match[2];

                if ($page === null) {
                    $page = new Database\Page($pageId);
                    $pages[] = $page;
                }

                if ($page->id !== $pageId) {
                    $newPage = new Database\Page($pageId);
                    $page->addChildId($newPage->id);
                    $page = $newPage;
                    $pages[] = $page;
                }

                $cell = new Database\Cell($cellId);
                $page->addCell($cell);
                continue;
            }

            if (strpos($line, 'sqlite3DebugBtreeNext') !== false) {
                $sqlite3BtreeNext = true;
            }

            if (strpos($line, 'sqlite3DebugBtreeIndexCompare') !== false) {
                $comparisons++;
            }
        }

        return $traces;
    }

    private static function countCompare(array $lines): array
    {
        $count = ['search' => 0, 'filter' => 0, 'other' => 0];
        $key = 'other';
        $started = false;
        foreach ($lines as $line) {
            if (str_starts_with($line, 'sqlite3DebugBtreeIndexMoveto: key')) {

                $count['search'] = 0;
                $count['filter'] = 0;
                
                $started = true;
                $key = 'search';
                continue;
            }

            if (!$started) {
                continue;
            }

            if (str_starts_with($line, 'sqlite3DebugBtreeIndexCompare:')) {
                $count[$key]++;
            }

            if (str_starts_with($line, 'sqlite3DebugBtreeIndexFilterCompare')) {
                $count[$key]++;
            }

            if (str_starts_with($line, 'sqlite3DebugResultRow:')) {
                $key = 'filter';
            }
        }

        return [$count['search'], $count['filter']];
    }
}
