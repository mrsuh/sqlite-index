<?php

namespace App\Prepare;

use App\Database;
use App\Render;

abstract class Prepare
{
    private const DARK_BACKGROUND_COLOR = '#26408B';
    private const LIGHT_BACKGROUND_COLOR = '#64B4F0';
    private const SELECTED_BACKGROUND_COLOR = '#FF9505';

    private const DARK_FONT_COLOR = '#1E1E1E';
    private const LIGHT_FONT_COLOR = '#FFFFFF';

    public string $headerFontColor = self::DARK_FONT_COLOR;

    public string $layerHeaderFontColor = self::LIGHT_FONT_COLOR;
    public string $layerHeaderBackgroundColor = self::DARK_BACKGROUND_COLOR;

    public string $arrowBackgroundColor = self::DARK_FONT_COLOR;

    public string $fontColorBlack = self::DARK_FONT_COLOR;
    public string $fontColorWhite = self::LIGHT_FONT_COLOR;

    public string $pageBackgroundColor = self::DARK_BACKGROUND_COLOR;
    public string $selectedPageBackgroundColor = self::SELECTED_BACKGROUND_COLOR;
    public string $cellBackgroundColor = self::LIGHT_BACKGROUND_COLOR;
    public string $selectedCellBackgroundColor = self::SELECTED_BACKGROUND_COLOR;

    abstract public function getHeaders(): array;

    abstract public function getLayerHeader(int $index): string;

    abstract public function getLayers(): array;

    protected function toNodes(array $layers): array
    {
        [$pageNumberMaxLength,
            $cellNumberMaxLength,
            $cellRowMaxLength,
            $cellPayloadMaxLength
        ] = self::mathNodeParameters($layers);

        $imageLayers = [];
        foreach ($layers as $depth => $pages) {
            $imageLayers[$depth] = [];
            foreach ($pages as $page) {
                $imageLayers[$depth][] = $this->toNode(
                    $page,
                    $pageNumberMaxLength,
                    $cellNumberMaxLength,
                    $cellRowMaxLength,
                    $cellPayloadMaxLength
                );
            }
        }

        return $imageLayers;
    }

    /**
     * @param array $layers
     * @return array
     */
    private static function mathNodeParameters(array $layers): array
    {
        $pageNumberMaxLength = 5;
        $cellNumberMaxLength = 4;
        $cellRowMaxLength = 7;
        $cellPayloadMaxLength = 10;
        foreach ($layers as $pages) {
            foreach ($pages as $page) {
                $pageNumberLength = strlen($page->id);
                if ($pageNumberLength > $pageNumberMaxLength) {
                    $pageNumberMaxLength = $pageNumberLength;
                }

                foreach ($page->cells as $cell) {
                    $cellNumberLength = strlen($cell->id);
                    if ($cellNumberLength > $cellNumberMaxLength) {
                        $cellNumberMaxLength = $cellNumberLength;
                    }

                    $cellRowLength = strlen($cell->rowId);
                    if ($cellRowLength > $cellRowMaxLength) {
                        $cellRowMaxLength = $cellRowLength;
                    }

                    $cellPayloadLength = strlen($cell->payload);
                    if ($cellPayloadLength > $cellPayloadMaxLength) {
                        $cellPayloadMaxLength = $cellPayloadLength;
                    }
                }
            }
        }

        return [
            $pageNumberMaxLength,
            $cellNumberMaxLength,
            $cellRowMaxLength,
            $cellPayloadMaxLength
        ];
    }

    private function toNode(
        Database\Page $page,
        int           $pageNumberMaxLength,
        int           $cellNumberMaxLength,
        int           $cellRowMaxLength,
        int           $cellPayloadMaxLength
    ): Render\Page
    {
        $selectedSell = $page->getSelectedCell();
        if ($selectedSell !== null) {
            $firstCell = reset($page->cells);
            $lastCell = end($page->cells);
            if (
                $firstCell->id !== $selectedSell->id &&
                $lastCell->id !== $selectedSell->id
            ) {
                $lastCell = $selectedSell;
            }
        } else {
            $firstCell = reset($page->cells);
            $lastCell = end($page->cells);

            $notNullableCells = [];
            foreach ($page->cells as $cell) {
                if ($cell->payload !== 'NULL') {
                    $notNullableCells[] = $cell;
                }
            }

            if (count($notNullableCells) > 1) {
                $firstCell = reset($notNullableCells);
                $lastCell = end($notNullableCells);
            }
        }

        $renderPage = new Render\Page([]);
        $renderPage->cells = [
            new Render\Cell(
                [
                    sprintf('Page:%s  Cells:%s',
                        self::renderData($page->id, $pageNumberMaxLength),
                        self::renderData(count($page->cells), $cellRowMaxLength),
                    ),
                    sprintf('RightChildPage:%s',
                        self::renderData($page->rightChildId, $pageNumberMaxLength)
                    ),
                ],
                $page->isSelected ? $this->selectedPageBackgroundColor : $this->pageBackgroundColor,
                $page->isSelected ? $this->fontColorBlack : $this->fontColorWhite,
            ),
            new Render\Cell(
                [
                    sprintf('Cell:%s  RowId:%s',
                        self::renderData($firstCell->id, $cellNumberMaxLength),
                        self::renderData($firstCell->rowId, $cellRowMaxLength),
                    ),
                    sprintf('LeftChildPage:%s',
                        self::renderData($firstCell->leftChildId, $pageNumberMaxLength),
                    ),
                    sprintf('Payload:%s',
                        self::renderData($firstCell->payload, $cellPayloadMaxLength),
                    ),
                ],
                $firstCell->isSelected ? $this->selectedCellBackgroundColor : $this->cellBackgroundColor,
                $this->fontColorBlack,
            ),
            new Render\Cell([], $this->cellBackgroundColor, $this->fontColorBlack),
        ];

        if ($firstCell->id !== $lastCell->id) {
            $renderPage->cells[] = new Render\Cell(
                [
                    sprintf('Cell:%s  RowId:%s',
                        self::renderData($lastCell->id, $cellNumberMaxLength),
                        self::renderData($lastCell->rowId, $cellRowMaxLength),
                    ),
                    sprintf('LeftChildPage:%s',
                        self::renderData($lastCell->leftChildId, $pageNumberMaxLength),
                    ),
                    sprintf('Payload:%s',
                        self::renderData($lastCell->payload, $cellPayloadMaxLength),
                    )
                ],
                $lastCell->isSelected ? $this->selectedCellBackgroundColor : $this->cellBackgroundColor,
                $this->fontColorBlack,
            );
        } else {
            $renderPage->cells[] = new Render\Cell(
                [
                    '',
                    '',
                    '',
                ],
                $this->cellBackgroundColor,
                $this->fontColorBlack,
            );
        }

        return $renderPage;
    }

    private static function renderData($data, int $length): string
    {
        $diff = $length - strlen($data);
        return (string)$data . ($diff > 0 ? str_repeat(' ', $diff) : '');
    }
}
