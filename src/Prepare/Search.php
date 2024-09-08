<?php

namespace App\Prepare;

use App\Database;

class Search extends Prepare
{
    private Database\Index $index;
    private Database\Search $search;
    private Database\Trace $trace;

    public function __construct(Database\Index $index, Database\Search $search, Database\Trace $trace)
    {
        $this->index = $index;
        $this->search = $search;
        $this->trace = $trace;
    }

    public function getHeaders(): array
    {
        $pageCount = 0;
        $cellCount = 0;
        foreach ($this->search->traces as $trace) {
            $pageCount += $trace->getTotalPageCount();
            $cellCount += $trace->getTotalCellCount();
        }
        return [
            sprintf('Index pages:%d',
                $this->index->getTotalPageCount(),
            ),
            sprintf('Index cells:%d',
                $this->index->getTotalCellCount()
            ),
            sprintf('Search pages:%d',
                $pageCount
            ),
            sprintf('Search sells:%d',
                $cellCount
            ),
            sprintf('Search seek:%d',
                $this->search->seekCount
            ),
            sprintf('Search comparisons:%d',
                $this->search->compareCount
            ),
            sprintf('Filter comparisons:%d',
                $this->search->filterCompareCount
            )
        ];
    }

    public function getLayerHeader(int $index): string
    {
        $page = $this->trace->pages[$index];

        return sprintf('Search cells:%d', count($page->cells));
    }

    public function getLayers(): array
    {
        $imageLayers = [];
        foreach ($this->index->toLayers() as $depth => $layerPages) {
            $imageLayers[$depth] = [];

            if (count($layerPages) === 1) {
                $page = current($layerPages);

                $tracePage = $this->trace->getPageById($page->id);
                $nextTracePage = $this->trace->getNexPageById($tracePage->id);
                if ($nextTracePage !== null) {
                    foreach ($tracePage->cells as $traceCell) {
                        $cell = $page->getCellById($traceCell->id);
                        if ($nextTracePage->id === $cell->leftChildId) {
                            $cell->isSelected = true;
                        }
                    }
                    if ($nextTracePage->id === $page->rightChildId) {
                        $page->isSelected = true;
                    }
                } else {
                    $cell = $page->getCellById(end($tracePage->cells)->id);
                    $cell->isSelected = true;
                }

                $imageLayers[$depth][] = $page;
                continue;
            }

            foreach ($imageLayers[$depth - 1] as $page) {

                $tracePage = $this->trace->getNexPageById($page->id);
                if ($tracePage !== null) {
                    $modifiedPage = $this->index->getPageById($tracePage->id);
                    $cell = $modifiedPage->getCellById(end($tracePage->cells)->id);

                    $nextTracePage = $this->trace->getNexPageById($tracePage->id);
                    if ($nextTracePage !== null) {
                        if ($nextTracePage->id === $cell->leftChildId) {
                            $cell->isSelected = true;
                        }
                        if ($nextTracePage->id === $modifiedPage->rightChildId) {
                            $modifiedPage->isSelected = true;
                        }
                    } else {
                        $cell->isSelected = true;// result
                    }

                    $leftPage = $this->index->getPageById(reset($page->cells)->leftChildId);
                    if ($modifiedPage->id === $leftPage->id) {
                        $imageLayers[$depth][] = $modifiedPage;
                        $imageLayers[$depth][] = $this->index->getPageById($page->rightChildId);
                    } else {
                        $imageLayers[$depth][] = $leftPage;
                        $imageLayers[$depth][] = $modifiedPage;
                    }

                } else {
                    $imageLayers[$depth][] = $this->index->getPageById(reset($page->cells)->leftChildId);
                    $imageLayers[$depth][] = $this->index->getPageById($page->rightChildId);
                }
            }
        }

        return $this->toNodes($imageLayers);
    }
}
