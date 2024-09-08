<?php

namespace App\Prepare;

use App\Database;

class Index extends Prepare
{
    private Database\Index $index;

    public function __construct(Database\Index $index)
    {
        $this->index = $index;
    }

    public function getHeaders(): array
    {
        return [
            sprintf('Index pages:%d',
                $this->index->getTotalPageCount(),
            ),
            sprintf('Index cells:%d',
                $this->index->getTotalCellCount()
            )
        ];
    }

    public function getLayerHeader(int $index): string
    {
        $layers = $this->index->toLayers();

        $pages = $layers[$index];

        return sprintf('Pages:%d Cells:%d', count($pages), Database\Tree::getCellCount($pages));
    }

    public function getLayers(): array
    {
        $imageLayers = [];
        foreach ($this->index->toLayers() as $depth => $layerPages) {
            $imageLayers[$depth] = [];

            if (count($layerPages) === 1) {
                $imageLayers[$depth][] = current($layerPages);
                continue;
            }

            foreach ($imageLayers[$depth - 1] as $page) {
                $imageLayers[$depth][] = $this->index->getPageById($page->cells[0]->leftChildId);
                $imageLayers[$depth][] = $this->index->getPageById($page->rightChildId);
            }
        }

        return $this->toNodes($imageLayers);
    }
}
