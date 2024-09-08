<?php

namespace App\Render;

use App\Prepare\Prepare;

class Image
{
    public int $fontSizeHeight = 60;
    public int $fontSizeWidth = 36;
    private int $fontWeight = 100;
    private string $fontFilePath = __DIR__ . '/../../font.ttf';

    private int $arrowWidth = 10;
    private int $pageInnerMargin = 10;
    private int $pageOuterMarginX = 20;
    private int $pageOuterMarginY = 140;

    private int $layerHeaderHeight = 70;

    private int $emptyCellHeight = 30;

    private int $boxRound = 10;

    private int $imageWidth = 3516;
    private string $imageBackgroundColor = 'white';

    /**
     * @param Prepare $prepare
     * @param $outputResource
     * @return void
     * @throws \ImagickException
     */
    public function draw(Prepare $prepare, $outputResource): void
    {
        $layers = $prepare->getLayers();

        $draw = new \ImagickDraw();

        [$pageWidth, $pageHeight] = $this->getPageSize($layers);

        $maxLayerWidth = ($pageWidth * count(end($layers))) + ($this->pageOuterMarginX * (count(end($layers)) - 1));
        $imageWidthPadding = 0;
        if ($maxLayerWidth < $this->imageWidth) {
            $imageWidthPadding = ($this->imageWidth - $maxLayerWidth) / 2;
        }

        $maxHeight = $pageHeight * count($layers) + ((count($layers) - 1) * $this->pageOuterMarginY) + 80;

        $middleX = $maxLayerWidth / 2;

        $x1 = $imageWidthPadding;
        $y1 = 0;

        $headerOffsetY = $y1 + $this->fontSizeHeight;
        foreach ($prepare->getHeaders() as $header) {
            $this->drawLayerData(
                $draw,
                10,
                $headerOffsetY,
                $header,
                $prepare->headerFontColor
            );

            $headerOffsetY += $this->fontSizeHeight + $this->fontSizeHeight / 3;
        }

        foreach ($layers as $index => $pages) {

            if ($index === 0) {
                $offsetX = $middleX - $pageWidth / 2;
                $offsetY = 0;
            } else {
                $offsetX = $middleX - (count($pages) / 2 * $pageWidth) - ((count($pages) / 2 - 1) * $this->pageOuterMarginX) - $this->pageOuterMarginX / 2;
                $offsetY = $index * ($pageHeight + $this->pageOuterMarginY);
            }

            $this->drawLayerHeader(
                $draw,
                $x1 + $offsetX,
                $y1 + $offsetY,
                ($pageWidth * count($pages)) + ($this->pageOuterMarginX * (count($pages) - 1)),
                $this->layerHeaderHeight,
                $prepare->layerHeaderBackgroundColor,
                $prepare->layerHeaderFontColor,
                $prepare->getLayerHeader($index)
            );

            $offsetY += $this->layerHeaderHeight + 10;

            foreach ($pages as $pageIndex => $page) {
                if ($pageIndex > 0) {
                    $offsetX += $pageWidth + $this->pageOuterMarginX;
                }

                $this->drawPage($draw, $page, $x1 + $offsetX, $y1 + $offsetY, $pageWidth);

                if ($index + 1 !== count($layers)) {
                    $this->drawArrow(
                        $draw,
                        $pages,
                        $index,
                        $pageIndex,
                        $x1 + $offsetX,
                        $y1 + $offsetY,
                        $pageWidth,
                        $pageHeight,
                        $this->pageOuterMarginY / 2,
                        $prepare->arrowBackgroundColor
                    );
                }
            }
        }

        $imagick = new \Imagick();
        $imagick->newImage($maxLayerWidth + $imageWidthPadding * 2, $maxHeight, $this->imageBackgroundColor);
        $imagick->setImageFormat("webp");

        $imagick->drawImage($draw);

        if ($imagick->getImageWidth() > $this->imageWidth) {
            $percent = $this->imageWidth / $imagick->getImageWidth();
        } else {
            $percent = 1;
        }

        $imagick->resizeImage(
            (int)($imagick->getImageWidth() * $percent),
            (int)($imagick->getImageHeight() * $percent),
            \Imagick::FILTER_UNDEFINED,
            1
        );

        fputs($outputResource, $imagick->getImageBlob());
    }

    private function getPageSize(array $layers): array
    {
        $pageWidth = 0;
        $pageHeight = 0;
        foreach ($layers as $nodes) {
            foreach ($nodes as $node) {
                [$nodeWidth, $nodeHeight] = $this->mathPageSize($node);
                if ($nodeWidth > $pageWidth) {
                    $pageWidth = $nodeWidth;
                }
                if ($nodeHeight > $pageHeight) {
                    $pageHeight = $nodeHeight;
                }
            }
        }

        return [$pageWidth, $pageHeight];
    }

    private function mathPageSize(Page $data): array
    {
        $maxLineLength = 0;
        foreach ($data->cells as $node) {
            foreach ($node->lines as $line) {
                $lineLength = strlen($line);
                if ($lineLength > $maxLineLength) {
                    $maxLineLength = $lineLength;
                }
            }
        }
        $maxLineWidth = $maxLineLength * $this->fontSizeWidth;

        $maxLinesHeight = 0;
        foreach ($data->cells as $node) {
            if (empty($node->lines)) {
                $maxLinesHeight += $this->emptyCellHeight;
                continue;
            }
            $maxLinesHeight += (count($node->lines) * $this->fontSizeHeight);
        }

        $maxLinesHeight += $this->pageInnerMargin * (count($data->cells) - 1);

        return [$maxLineWidth, $maxLinesHeight];
    }

    private function drawLayerData(\ImagickDraw $draw, int $x1, int $y1, string $text, string $fontColor): void
    {
        $draw->push();

        $draw->setFont($this->fontFilePath);
        $draw->setFontSize($this->fontSizeHeight);
        $draw->setFillColor(new \ImagickPixel($fontColor));
        $draw->setFontWeight($this->fontWeight);

        $draw->annotation($x1, $y1, $text);

        $draw->pop();
    }

    private function drawLayerHeader(\ImagickDraw $draw, int $x1, int $y1, int $width, int $height, string $boxColor, string $fontColor, string $text): void
    {
        $draw->push();

        $this->drawEmptyBox($draw, $x1, $y1, $width, $height, $boxColor);

        $draw->setFont($this->fontFilePath);
        $draw->setFontSize($this->fontSizeHeight);
        $draw->setFillColor(new \ImagickPixel($fontColor));
        $draw->setFontWeight($this->fontWeight);
        $draw->setTextAlignment(\Imagick::ALIGN_CENTER);

        $draw->annotation($x1 + $width / 2, $y1 + $this->fontSizeHeight - 5, $text);

        $draw->pop();
    }

    private function drawEmptyBox(\ImagickDraw $draw, int $x1, int $y1, int $width, int $height, string $color): void
    {
        $draw->setFillColor(new \ImagickPixel($color));
        $draw->roundRectangle($x1, $y1, $x1 + $width, $y1 + $height, $this->boxRound, $this->boxRound);
    }

    /**
     * @param \ImagickDraw $draw
     * @param int $x1
     * @param int $y1
     * @param $maxLineLength
     * @return void
     */
    private function drawPage(\ImagickDraw $draw, Page $data, int $x1, int $y1, $maxLineLength): void
    {
        $offsetY = 0;
        foreach ($data->cells as $node) {

            if ($node->isEmpty()) {
                $this->drawEmptyBox(
                    $draw,
                    $x1,
                    $y1 + $offsetY,
                    $maxLineLength,
                    $this->emptyCellHeight,
                    $node->backgroundColor
                );
                $offsetY += $this->emptyCellHeight + $this->pageInnerMargin;
                continue;
            }

            $height = count($node->lines) * $this->fontSizeHeight;
            $this->drawBox(
                $draw,
                $node->lines,
                $x1,
                $y1 + $offsetY,
                $maxLineLength,
                $height,
                $node->backgroundColor,
                $node->fontColor
            );
            $offsetY += $height + $this->pageInnerMargin;
        }
    }

    private function drawBox(\ImagickDraw $draw, array $lines, int $x1, int $y1, int $width, int $height, string $color, string $fontColor): void
    {
        $draw->setFillColor(new \ImagickPixel($color));
        $draw->roundRectangle($x1, $y1, $x1 + $width, $y1 + $height, $this->boxRound, $this->boxRound);

        $draw->setFont($this->fontFilePath);
        $draw->setFontSize($this->fontSizeHeight);
        $draw->setFillColor(new \ImagickPixel($fontColor));
        $draw->setFontWeight($this->fontWeight);
        foreach ($lines as $index => $line) {
            $draw->annotation($x1, $y1 + ($index * $this->fontSizeHeight) + $this->fontSizeHeight - 10, $line);
        }
    }

    private function drawArrow(
        \ImagickDraw $draw,
        array        $pages,
        int          $index,
        int          $pageIndex,
        int          $x1,
        int          $y1,
        int          $pageWidth,
        int          $pageHeight,
        int          $pageMarginY,
        string       $backgroundColor
    ): void
    {
        $draw->push();
        $lineOffsetX = $pageWidth / 2;
        $lineOffsetY = $pageHeight;
        $draw->setStrokeWidth($this->arrowWidth);
        $draw->setStrokeColor(new \ImagickPixel($backgroundColor));
        $draw->line(
            $x1 + $lineOffsetX,
            $y1 + $lineOffsetY,
            $x1 + $lineOffsetX,
            $y1 + $lineOffsetY + $pageMarginY / 2 + $this->arrowWidth / 2
        );

        if ($index === 0) {
            $draw->line(
                $x1 + $lineOffsetX,
                $y1 + $lineOffsetY + $pageMarginY / 2,
                $x1 + $lineOffsetX + $pageWidth / 2 + $this->arrowWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY / 2
            );
            $draw->line(
                $x1 + $lineOffsetX + $pageWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY / 2,
                $x1 + $lineOffsetX + $pageWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY
            );

            $draw->line(
                $x1 + $lineOffsetX,
                $y1 + $lineOffsetY + $pageMarginY / 2,
                $x1 + $lineOffsetX - $pageWidth / 2 - $this->arrowWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY / 2
            );
            $draw->line(
                $x1 + $lineOffsetX - $pageWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY / 2,
                $x1 + $lineOffsetX - $pageWidth / 2,
                $y1 + $lineOffsetY + $pageMarginY
            );
        } else {
            $draw->line(
                $x1 + $lineOffsetX,
                $y1 + $lineOffsetY + $pageMarginY / 2,
                $x1 + $lineOffsetX,
                $y1 + $lineOffsetY + $pageMarginY
            );

            if ($pageIndex + 1 <= count($pages) / 2) {
                $draw->line(
                    $x1 + $lineOffsetX,
                    $y1 + $lineOffsetY + $pageMarginY / 2,
                    $x1 + $lineOffsetX - $pageWidth - $this->arrowWidth / 2,
                    $y1 + $lineOffsetY + $pageMarginY / 2
                );
                $draw->line(
                    $x1 + $lineOffsetX - $pageWidth,
                    $y1 + $lineOffsetY + $pageMarginY / 2,
                    $x1 + $lineOffsetX - $pageWidth,
                    $y1 + $lineOffsetY + $pageMarginY
                );
            } else {
                $draw->line(
                    $x1 + $lineOffsetX,
                    $y1 + $lineOffsetY + $pageMarginY / 2,
                    $x1 + $lineOffsetX + $pageWidth + $this->arrowWidth / 2,
                    $y1 + $lineOffsetY + $pageMarginY / 2
                );
                $draw->line(
                    $x1 + $lineOffsetX + $pageWidth,
                    $y1 + $lineOffsetY + $pageMarginY / 2,
                    $x1 + $lineOffsetX + $pageWidth,
                    $y1 + $lineOffsetY + $pageMarginY
                );
            }
        }
        $draw->pop();
    }
}
