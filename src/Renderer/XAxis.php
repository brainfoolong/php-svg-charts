<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\ChartsType\LinesAndColumns;
use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\LineDataPoint;
use BrainFooLong\SvgCharts\PlotArea;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class XAxis extends Renderer
{

    public PlotArea $availablePlotArea;

    private array $annotations = [];

    public function __construct(
        public LinesAndColumns $series,
        public string $title,
        public ?string $labelFormatter = null,
        /**
         * Use +=x to add x to the value defined by the data series
         * Use -=x to subtract x to the value defined by the data series
         * @var float|string|null
         */
        public float|string|null $minValue = null,
        /**
         * Use +=x to add x to the value defined by the data series
         * Use -=x to subtract x to the value defined by the data series
         * @var float|string|null
         */
        public float|string|null $maxValue = null,
        /**
         * How to draw the title
         * If null, no title is drawn
         * @var TextRect|null
         */
        public ?TextRect $titleDrawSettings = null,
        /**
         * How to draw the value labels
         * If null, no labels are drawn
         * @var TextRect|null
         */
        public ?TextRect $valueLabelDrawSettings = null,
        /**
         * Line draw settings (bottom line and x-data point line spikes at each x value)
         * Default is black
         * @var DrawSettings
         */
        public DrawSettings $lineDrawSettings = new DrawSettings('black'),
        /**
         * If given to many x values and they start to overlap, try to rotate them to fix the overlap
         * @var bool
         */
        public bool $autoRotate = true,
        /**
         * If given to many x values and they start to overlap, try to set $showNthLabel to the best value
         * @var bool
         */
        public bool $autoSkip = true,
        /**
         * Show only each x-th label, including always start and end label
         * @var int|null
         */
        public ?int $showNthLabel = null
    ) {}

    /**
     * @return LineDataPoint[]
     */
    public function getSeriesTotalUniqueXDataPoints(): array
    {
        $arr = [];
        foreach ($this->series->dataSeries as $dataSeries) {
            foreach ($dataSeries['dataPoints'] as $dataPoint) {
                $arr[$dataPoint->x] = $dataPoint;
            }
        }
        LinesAndColumns::sortDataPointsByX($arr);
        return array_values($arr);
    }

    /**
     * @param LineDataPoint[] $dataPoints
     * @param int $totalPoints
     * @param array|null $additionalXValues If set, this x values will also be converted to x coorindates
     * @return float[]
     */
    public function getPointXCoordinates(array $dataPoints, int $totalPoints, ?array &$additionalXValues = null): array
    {
        if (!$dataPoints) {
            return [];
        }
        $plotArea = $this->availablePlotArea;
        $minMax = $this->series->getMinMaxCoordinates();
        $hasColumns = false;
        foreach ($this->series->dataSeries as $dataSeries) {
            if ($dataSeries['type'] === $this->series::TYPE_COLUMN) {
                $hasColumns = true;
                break;
            }
        }
        $totalWidth = $minMax['maxX'] - $minMax['minX'];
        $fixedOffset = 0;
        if ($hasColumns) {
            $totalPoints += 2;
            $fixedOffset = $this->availablePlotArea->getWidth() / $totalPoints;
        }
        $dataPointEnd = end($dataPoints);
        $dataPointStart = reset($dataPoints);
        $getX = function (float $x) use ($totalWidth, $minMax, $plotArea) {
            $offset = (1 / $totalWidth) * ($x - $minMax['minX']);
            return $plotArea->x1 + $offset * $plotArea->getWidth();
        };
        $spacerBetween = ($getX($dataPointEnd->x) - $getX($dataPointStart->x)) / ($totalPoints - 1);
        $xForEqual = null;
        $arr = [];
        foreach ($dataPoints as $key => $dataPoint) {
            $x = $fixedOffset + $getX($dataPoint->x);
            if ($this->series->equalXAxisSpaceBetweenPoints) {
                if ($xForEqual === null) {
                    $xForEqual = $x;
                } else {
                    $x = $xForEqual;
                }
                $xForEqual += $spacerBetween;
            }
            $arr[$key] = $x;
        }
        if (is_array($additionalXValues)) {
            foreach ($additionalXValues as $key => $value) {
                $additionalXValues[$key] = $getX($value);
            }
        }
        return $arr;
    }

    public function addLineAnnotation(
        float $x,
        Line $line,
        TextRect $textRect
    ): void {
        $this->annotations[] = [
            "x" => $x,
            "line" => $line,
            "textRect" => $textRect
        ];
    }

    public function toSvg(SvgChart $chart): string
    {
        $renderers = [];
        $renderGroup = new RenderGroup('xaxis');
        $renderGroupAnnotations = null;
        $lineDrawSettings = clone $this->lineDrawSettings;
        $lineDrawSettings->shapeRendering = 'crispEdges';

        $annotationXCoordinates = [];
        foreach ($this->annotations as $row) {
            $annotationXCoordinates[] = $row['x'];
        }
        $plotArea = $this->availablePlotArea;
        $allDataPoints = $this->getSeriesTotalUniqueXDataPoints();
        $xCoordinates = $this->getPointXCoordinates($allDataPoints, count($allDataPoints), $annotationXCoordinates);
        $xCoordinatesCount = count($xCoordinates);

        $rotation = $this->valueLabelDrawSettings->rotate ?? 0;
        $showNth = $this->showNthLabel ?? 0;

        // find best values for autoRotate and autoSkip
        if (($this->autoRotate || $this->autoSkip) && $this->valueLabelDrawSettings) {
            $test = function () use (&$xCoordinates, &$allDataPoints, &$chart, &$rotation, &$showNth, &$test): void {
                $lastX1 = null;
                $lastX2 = null;
                $labelCount = 0;
                $xCoordinatesCount = count($xCoordinates);
                foreach ($xCoordinates as $dataPointId => $x) {
                    $labelCount++;

                    if ($showNth > 0 && ($labelCount % $showNth !== 0) && $labelCount > 1 && $labelCount < $xCoordinatesCount) {
                        continue;
                    }

                    $labelRect = clone $this->valueLabelDrawSettings;
                    $labelRect->anchorVertical = $labelRect::ANCHOR_VERTICAL_TOP;
                    $labelRect->text = LabelFormats::valueToFormat($allDataPoints[$dataPointId]->x ?? $x, $this->labelFormatter);
                    $labelRect->x = $x;
                    $labelRect->rotate = $rotation;
                    $meta = $labelRect->getMetaInformation($chart);
                    $totalWidth = $labelRect->getTotalWidth($chart);
                    // if labels are rotated, we can shorten the width as angled text don't overlap even if bounds are touching
                    if ($rotation) {
                        $totalWidth -= $totalWidth * (abs($rotation) / 90);
                        if ($totalWidth < $meta['lineHeight']) {
                            $totalWidth = $meta['lineHeight'];
                        }
                    }
                    $x1 = $x;
                    $x2 = $x1 + $totalWidth;

                    if ($lastX1 !== null) {
                        if ($lastX2 >= $x1) {
                            if (abs($rotation) < 90 && $this->autoRotate) {
                                if ($rotation > 0) {
                                    $rotation += 10;
                                } else {
                                    $rotation -= 10;
                                }
                            } elseif ($this->autoSkip && $showNth < 100) {
                                $showNth++;
                            } else {
                                return;
                            }
                            $test();
                            return;
                        }
                    }

                    $lastX1 = $x1;
                    $lastX2 = $x2;
                }
            };
            $test();
        }

        $maxHeight = null;
        $padding = 10;
        $rects = [];
        $indicatorLines = [];

        // label
        if ($this->title !== '' && $this->titleDrawSettings) {
            $labelRect = clone $this->titleDrawSettings;
            $labelRect->anchorHorizontal = $labelRect::ANCHOR_HORIZONTAL_CENTER;
            $labelRect->anchorVertical = $labelRect::ANCHOR_VERTICAL_MIDDLE;
            $labelRect->text = $this->title;
            $meta = $labelRect->getMetaInformation($chart);
            $labelRect->x = $plotArea->x1 + ($plotArea->getWidth() / 2 - $meta['width'] / 2);
            $labelRect->y = $plotArea->y2 - ($meta['height'] / 2);
            $renderers[] = $labelRect;
            $reduce = $padding * 2 + $meta['height'];
            $plotArea->reduceHeight($reduce);
        }

        $labelCount = 0;
        foreach ($xCoordinates as $dataPointId => $x) {
            $labelCount++;
            // x indicators
            $line = new Line(
                x1: $x,
                y1: 0,
                x2: $x,
                y2: 0,
                drawSettings: $lineDrawSettings,
            );
            if (!$this->valueLabelDrawSettings) {
                $indicatorLines[] = $line;
                $renderers[] = $line;
                continue;
            }

            if ($showNth > 0 && ($labelCount % $showNth !== 0) && $labelCount > 1 && $labelCount < $xCoordinatesCount) {
                continue;
            }
            $indicatorLines[] = $line;
            $renderers[] = $line;
            $labelRect = clone $this->valueLabelDrawSettings;
            $labelRect->rotate = $rotation;
            if (abs($labelRect->rotate) == 90) {
                $labelRect->anchorVertical = $labelRect::ANCHOR_VERTICAL_MIDDLE;
                $labelRect->anchorHorizontal = $labelRect::ANCHOR_HORIZONTAL_RIGHT;
            } elseif ($labelRect->rotate != 0) {
                $labelRect->anchorVertical = $labelRect::ANCHOR_VERTICAL_MIDDLE;
                $labelRect->anchorHorizontal = $labelRect::ANCHOR_HORIZONTAL_RIGHT;
            } else {
                $labelRect->anchorVertical = $labelRect::ANCHOR_VERTICAL_TOP;
                $labelRect->anchorHorizontal = $labelRect::ANCHOR_HORIZONTAL_CENTER;
            }
            $labelRect->text = LabelFormats::valueToFormat($allDataPoints[$dataPointId]->x ?? $x, $this->labelFormatter);
            $labelRect->x = $x;
            $totalHeight = $labelRect->getTotalHeight($chart);

            $rects[] = $labelRect;
            $renderers[] = $labelRect;
            if ($maxHeight === null || $maxHeight < $totalHeight) {
                $maxHeight = $totalHeight;
            }
        }
        $reduce = $maxHeight + $padding;
        $plotArea->reduceHeight($reduce);
        // update y values based on new reduced plot area
        foreach ($rects as $labelRect) {
            $labelRect->y = $plotArea->y2 + $padding;
        }
        foreach ($indicatorLines as $indicatorLine) {
            $indicatorLine->y1 = $plotArea->y2;
            $indicatorLine->y2 = $plotArea->y2 + $padding / 2;
        }
        // bottom line
        $renderers[] = new Line(
            x1: $plotArea->x1,
            y1: $plotArea->y2,
            x2: $plotArea->x2,
            y2: $plotArea->y2,
            drawSettings: $lineDrawSettings,
        );
        // draw annotations
        foreach ($this->annotations as $key => $row) {
            if (!$renderGroupAnnotations) {
                $renderGroupAnnotations = new RenderGroup('xaxis-annotations');
                $chart->renderPipelineAnnotations->renderers[] = $renderGroupAnnotations;
            }
            $x = $annotationXCoordinates[$key];

            /** @var Line $annotationLine */
            $annotationLine = clone $row['line'];
            $annotationLine->drawSettings->shapeRendering = 'crispEdges';
            $annotationLine->x1 = $x;
            $annotationLine->x2 = $x;
            $annotationLine->y1 = $plotArea->y1;
            $annotationLine->y2 = $plotArea->y2;
            /** @var TextRect $annotationTextRect */
            $annotationTextRect = clone $row['textRect'];
            $annotationTextRect->x = $x;
            $annotationTextRect->y = $annotationTextRect->anchorVertical === 'bottom' ? $plotArea->y2 : $plotArea->y1;
            $renderGroupAnnotations->renderers[] = $annotationLine;
            $renderGroupAnnotations->renderers[] = $annotationTextRect;
        }
        $renderGroup->renderers = $renderers;
        return $renderGroup->toSvg($chart);
    }

}