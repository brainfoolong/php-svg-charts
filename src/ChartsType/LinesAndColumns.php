<?php

namespace BrainFooLong\SvgCharts\ChartsType;

use BrainFooLong\SvgCharts\ColumnDataPoint;
use BrainFooLong\SvgCharts\ColumnDataPointGrouped;
use BrainFooLong\SvgCharts\DrawColor;
use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\LineDataPoint;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\Renderer\Line;
use BrainFooLong\SvgCharts\Renderer\Rect;
use BrainFooLong\SvgCharts\Renderer\RenderGroup;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\SvgChart;
use Exception;

class LinesAndColumns extends Renderer
{

    public const TYPE_LINE = 'line';
    public const TYPE_COLUMN = 'column';

    public const LINEMODE_STRAIGHT = 'straight';
    public const LINEMODE_CURVE = 'curve';
    public const LINEMODE_STEP = 'step';

    public Renderer\XAxis $xAxis;

    /**
     * Combine all given yaxis into one
     * The first in the list dominates the axis
     * @var Renderer\YAxis[]
     */
    public array $combineYAxis = [];

    public array $dataSeries = [];

    public static function sortDataPointsByX(array &$dataPoints): void
    {
        usort(
            $dataPoints,
            function ($a, $b) {
                if ($a instanceof LineDataPoint || $b instanceof ColumnDataPoint) {
                    return $a->x <=> $b->x;
                }
                return 0;
            },
        );
    }

    public function __construct(
        public string $id,
        /**
         * If false, all points are positioned on x axis depending on their values rather then equal spaced
         * @var bool
         */
        public bool $equalXAxisSpaceBetweenPoints = true,
    ) {
        $this->xAxis = new Renderer\XAxis($this, '');
    }

    /**
     * @param Renderer\YAxis $yAxis
     * @param LineDataPoint[]|array $dataPoints
     * @param float $dataPointSize The size of the tiny point on x,y coordinates for each data point
     * @param string $lineMode How to draw the lines
     * @param float $curveStrength Any number between 0 and 1, higher values will destroy the chart
     * @param DrawSettings|null $lineDrawSettings Use "stroke" property to set the draw color
     * @param DrawSettings|null $dataPointDrawSettings If not set, no data point circles are drawn, use "fill"
     *     property to set the draw color
     * @param DrawSettings|null $areaDrawSettings If not set, no area is drawn, use "fill" property to set the
     *     draw color
     * @param Renderer\TextRect|null $valueLabelDrawSettings If not set, no value labels are drawn
     * @return void
     */
    public function addLineDataSeries(
        Renderer\YAxis $yAxis,
        array $dataPoints,
        float $dataPointSize = 5,
        string $lineMode = self::LINEMODE_STRAIGHT,
        float $curveStrength = 1,
        ?DrawSettings $lineDrawSettings = null,
        ?DrawSettings $dataPointDrawSettings = null,
        ?DrawSettings $areaDrawSettings = null,
        ?Renderer\TextRect $valueLabelDrawSettings = null,
    ): void {
        foreach ($dataPoints as $key => $dataPoint) {
            if (is_array($dataPoint)) {
                $dataPoints[$key] = new LineDataPoint(...$dataPoint);
            }
        }
        self::sortDataPointsByX($dataPoints);
        $yAxis->series = $this;
        $hue = 120 * count($this->dataSeries);
        $lineDrawSettings = $lineDrawSettings ?? new DrawSettings(
            stroke: new DrawColor(hsl: [$hue / 360, 1.0, 0.45]),
            strokeWidth: 2,
        );
        $this->dataSeries[$yAxis->id] = [
            'type' => self::TYPE_LINE,
            'yAxis' => $yAxis,
            'dataPoints' => $dataPoints,
            'dataPointSize' => $dataPointSize,
            'lineMode' => $lineMode,
            'curveStrength' => $curveStrength,
            'lineDrawSettings' => $lineDrawSettings,
            'dataPointDrawSettings' => $dataPointDrawSettings ?? new DrawSettings(
                    fill: $lineDrawSettings->stroke,
                ),
            'areaDrawSettings' => $areaDrawSettings,
            'valueLabelDrawSettings' => $valueLabelDrawSettings,
        ];
    }

    /**
     * @param Renderer\YAxis $yAxis
     * @param ColumnDataPoint[]|ColumnDataPointGrouped[]|array $dataPoints
     * @param float $columnWidth Any value between 0 and 1 - 1 means maximum width of the section
     * @param float $edgeRoundness Any value between 0 and 1 - 1 means maximum roundness
     * @param float $yBaseline Draw a baseline and calculate y values from that baseline
     * @param DrawSettings|null $drawSettings The defaults, use "fill" property to set the column fill color, use
     *     "stroke" property to set the column border color
     * @param Renderer\TextRect|null $valueLabelDrawSettings Use this text rect defaults to draw the label, if null no
     *     labels will be drawn
     * @param Renderer\TextRect|null $summaryLabelDrawSettings If not set, no summary labels are drawn
     * @return void
     */
    public function addColumnDataSeries(
        Renderer\YAxis $yAxis,
        array $dataPoints,
        float $columnWidth = 0.5,
        float $edgeRoundness = 0,
        float $yBaseline = 0,
        DrawSettings|null $drawSettings = null,
        Renderer\TextRect|null $valueLabelDrawSettings = null,
        ?Renderer\TextRect $summaryLabelDrawSettings = null,
    ): void {
        $dataPointsFlat = [];
        foreach ($dataPoints as $key => $dataPoint) {
            if (is_array($dataPoint)) {
                $dataPoint = new ColumnDataPoint(...$dataPoint);
                $dataPoints[$key] = $dataPoint;
                $dataPointsFlat[] = $dataPoint;
            } elseif ($dataPoint instanceof ColumnDataPoint) {
                $dataPoints[$key] = $dataPoint;
                $dataPointsFlat[] = $dataPoint;
            } elseif (($dataPoint instanceof ColumnDataPointGrouped)) {
                $dataPoints[$key] = $dataPoint;
                $dataPointsFlat = array_merge($dataPointsFlat, $dataPoint->dataPoints);
            } else {
                throw new Exception(
                    "Wrong data point value, required an instance of ColumnDataPoint or ColumnDataPointGrouped",
                );
            }
        }
        self::sortDataPointsByX($dataPointsFlat);
        $yAxis->series = $this;
        $hue = 120 * count($this->dataSeries);
        $this->dataSeries[$yAxis->id] = [
            'type' => self::TYPE_COLUMN,
            'yAxis' => $yAxis,
            'dataPoints' => $dataPointsFlat,
            'dataPointsSource' => $dataPoints,
            'yBaseline' => $yBaseline,
            'columnWidth' => $columnWidth,
            'edgeRoundness' => $edgeRoundness,
            'columnDrawSettings' => $drawSettings ?? new DrawSettings(fill: new DrawColor(hsl: [$hue / 360, 1.0, 0.45], hslModifyStep: [0, 0, 0.2])),
            'valueLabelDrawSettings' => $valueLabelDrawSettings,
            'summaryLabelDrawSettings' => $summaryLabelDrawSettings,
        ];
    }

    /**
     * @param string|null $yAxisId
     * @return array{minX: float, maxX:float, minY: float, maxY: float}
     */
    public function getMinMaxCoordinates(?string $yAxisId = null): array
    {
        $arr = ['minX' => null, 'maxX' => null, 'minY' => null, 'maxY' => null];

        $setAxisBounds = function (mixed $bound, string $field) use (&$arr) {
            if ($bound === null) {
                return;
            }
            if (is_numeric($bound)) {
                if ($arr[$field] === null || (str_starts_with(
                        $field,
                        "min",
                    ) ? $arr[$field] > $bound : $arr[$field] < $bound)) {
                    $arr[$field] = $bound;
                }
            } else {
                $arr[$field] += (float)(substr((string)$bound, 0, 1) . substr((string)$bound, 2));
            }
        };

        foreach ($this->dataSeries as $dataSeries) {
            $yAxis = $dataSeries['yAxis'];
            if ($yAxisId !== null && $yAxis->id !== $yAxisId) {
                continue;
            }
            $dataPoints = $yAxis->getDataPoints(true);
            if ($dataSeries['type'] === LinesAndColumns::TYPE_COLUMN) {
                $dataPoints[] = $dataSeries['yBaseline'];
            }
            foreach ($dataPoints as $dataPoint) {
                if ($dataPoint instanceof ColumnDataPoint) {
                    $x = $dataPoint->x;
                    $sum = 0;
                    foreach ($dataPoint->values as $value) {
                        $sum += $value->value;
                    }
                    $values = [$sum];
                } elseif ($dataPoint instanceof LineDataPoint) {
                    $x = $dataPoint->x;
                    $values = [$dataPoint->y];
                } else {
                    $x = null;
                    $values = [(float)$dataPoint];
                }
                if ($x !== null) {
                    if ($arr['minX'] === null || $arr['minX'] > $x) {
                        $arr['minX'] = $x;
                    }
                    if ($arr['maxX'] === null || $arr['maxX'] < $x) {
                        $arr['maxX'] = $x;
                    }
                }
                foreach ($values as $y) {
                    if ($arr['minY'] === null || $arr['minY'] > $y) {
                        $arr['minY'] = $y;
                    }
                    if ($arr['maxY'] === null || $arr['maxY'] < $y) {
                        $arr['maxY'] = $y;
                    }
                }
            }
        }
        $setAxisBounds($this->xAxis->minValue, 'minX');
        $setAxisBounds($this->xAxis->maxValue, 'maxX');
        foreach ($this->dataSeries as $dataSeries) {
            $yAxis = $dataSeries['yAxis'];
            $setAxisBounds($yAxis->minValue, 'minY');
            $setAxisBounds($yAxis->maxValue, 'maxY');
        }
        return $arr;
    }

    public function toSvg(SvgChart $chart): string
    {
        $renderGroupSeries = new RenderGroup('lianco-' . $this->id);
        $renderGroupAxis = new RenderGroup('axis');
        $renderGroupDataSeries = new RenderGroup('series');
        $renderGroupDataLabels = new RenderGroup('labels');
        $renderGroupSeries->renderers = [
            $renderGroupAxis,
            $renderGroupDataSeries,
            $renderGroupDataLabels
        ];

        if ($legend = $chart->legend) {
            $legend->prepareDrawing($chart);
        }

        $startPlotArea = $chart->getPlotArea();
        $finalPlotArea = $chart->getPlotArea();

        foreach ($this->dataSeries as $dataSeries) {
            $yAxis = $dataSeries['yAxis'];
            if ($this->combineYAxis && reset($this->combineYAxis) !== $yAxis) {
                continue;
            }
            if ($yAxis->verticalLabels === null) {
                $yAxis->verticalLabels = $chart->grid->lines ?? 5;
            }
            $yAxis->availablePlotArea = $finalPlotArea;
            // simulate rendering to update $finalPlotArea
            $yAxis->toSvg($chart);
        }

        $xAxisHeight = $finalPlotArea->getHeight();
        $this->xAxis->availablePlotArea = $finalPlotArea;
        $renderGroupAxis->renderers[] = $this->xAxis->toSvg($chart);
        $xAxisHeight = $xAxisHeight - $finalPlotArea->getHeight();
        $startPlotArea->reduceHeight($xAxisHeight);
        foreach ($this->dataSeries as $dataSeries) {
            /** @var Renderer\YAxis $yAxis */
            $yAxis = $dataSeries['yAxis'];
            if ($this->combineYAxis && reset($this->combineYAxis) !== $yAxis) {
                continue;
            }
            if ($yAxis->verticalLabels === null) {
                $yAxis->verticalLabels = $chart->grid->lines ?? 5;
            }
            $yAxis->availablePlotArea = $startPlotArea;
            $renderGroupAxis->renderers[] = $yAxis->toSvg($chart);
        }

        $chart->leftMargin = $finalPlotArea->x1;
        $chart->rightMargin = $chart->width - $finalPlotArea->x2;
        $chart->topMargin = $finalPlotArea->y1;
        $chart->bottomMargin = $chart->height - $finalPlotArea->y2;

        $minMax = $this->getMinMaxCoordinates();
        $getYCoordinate = function (float $yValue) use ($minMax, $finalPlotArea) {
            $dataHeight = $minMax['maxY'] - $minMax['minY'];
            $offset = $dataHeight > 0 ? (1 / $dataHeight) * ($yValue - $minMax['minY']) : 0;
            return $finalPlotArea->y2 - $offset * $finalPlotArea->getHeight();
        };

        $allXDataPoints = $this->xAxis->getSeriesTotalUniqueXDataPoints();
        $allXPointsCount = count($allXDataPoints);
        $plotArea = $chart->getPlotArea();
        $renderYAnnotations = null;
        foreach ($this->dataSeries as $dataSeries) {
            /** @var Renderer\YAxis $yAxis */
            $yAxis = $dataSeries['yAxis'];
            $yAxis->availablePlotArea = $plotArea;

            $renderGroupDataSeriesSingle = new RenderGroup('series-' . $yAxis->id);
            $renderGroupDataSeries->renderers[] = $renderGroupDataSeriesSingle;

            $annotationXCoordinates = [];
            // y axis annotations
            foreach ($yAxis->annotations as $key => $row) {
                if (!$renderYAnnotations) {
                    $renderYAnnotations = new RenderGroup('yaxis-annotations-' . $yAxis->id);
                    $chart->renderPipelineAnnotations->renderers[] = $renderYAnnotations;
                }
                $annotationYCoordinates = [$row['y']];
                $yAxis->getPointYCoordinates([], $annotationYCoordinates);
                if ($row['type'] === 'line') {
                    /** @var Line $annotationLine */
                    $annotationLine = clone $row['line'];
                    $annotationLine->drawSettings->shapeRendering = 'crispEdges';
                    $annotationLine->x1 = $plotArea->x1;
                    $annotationLine->x2 = $plotArea->x2;
                    $annotationLine->y1 = $annotationYCoordinates[0];
                    $annotationLine->y2 = $annotationYCoordinates[0];
                    $renderYAnnotations->renderers[] = $annotationLine;

                    /** @var TextRect $annotationTextRect */
                    $annotationTextRect = clone $row['textRect'];
                    $annotationTextRect->x = $annotationTextRect->anchorHorizontal === $annotationTextRect::ANCHOR_HORIZONTAL_RIGHT ? $plotArea->x2 : $plotArea->x1;
                    $annotationTextRect->y = $annotationYCoordinates[0];
                    $renderYAnnotations->renderers[] = $annotationTextRect;
                }
                if ($row['type'] === 'point') {
                    /** @var Renderer\Circle $annotationCircle */
                    $annotationCircle = clone $row['circle'];
                    $annotationXCoordinates[$key] = $row['x'];
                    $annotationCircle->cx = &$annotationXCoordinates[$key];
                    $annotationCircle->cy = $annotationYCoordinates[0];
                    $renderYAnnotations->renderers[] = $annotationCircle;

                    /** @var TextRect $annotationTextRect */
                    $annotationTextRect = clone $row['textRect'];
                    $annotationTextRect->x = &$annotationXCoordinates[$key];
                    $annotationTextRect->y = $annotationYCoordinates[0];
                    $renderYAnnotations->renderers[] = $annotationTextRect;
                }
            }
            $allXCoordinates = $this->xAxis->getPointXCoordinates($allXDataPoints, $allXPointsCount, $annotationXCoordinates);

            if ($dataSeries['type'] === self::TYPE_COLUMN) {
                /** @var Renderer\TextRect|null $valueLabelDrawSettings */
                $valueLabelDrawSettings = $dataSeries['valueLabelDrawSettings'] ?? null;
                /** @var Renderer\TextRect|null $summaryLabelDrawSettings */
                $summaryLabelDrawSettings = $dataSeries['summaryLabelDrawSettings'] ?? null;

                $dataPointId = 0;
                $yCoordinate = $getYCoordinate($dataSeries['yBaseline']);
                $baseline = new Renderer\Line(
                    $finalPlotArea->x1,
                    $yCoordinate,
                    $finalPlotArea->x2,
                    $yCoordinate,
                    new DrawSettings(stroke: 'black', shapeRendering: 'crispEdges'),
                );
                $renderGroupDataSeriesSingle->renderers[] = $baseline;
                foreach ($dataSeries['dataPointsSource'] as $dataPoint) {
                    if ($dataPoint instanceof ColumnDataPointGrouped) {
                        $columnDataPoints = $dataPoint->dataPoints;
                    } else {
                        $columnDataPoints = [$dataPoint];
                    }
                    /** @var ColumnDataPoint[] $columnDataPoints */
                    $columnDataPoints = array_values($columnDataPoints);
                    $columnDataPointsCount = count($columnDataPoints);
                    $totalAvailableColumnWidth = ($finalPlotArea->getWidth() / $allXPointsCount - 2);
                    $totalSizePerColumn = $totalAvailableColumnWidth * $dataSeries['columnWidth'];
                    $x = $allXCoordinates[$dataPointId] - ($totalAvailableColumnWidth / 2) + ($totalSizePerColumn / 2);

                    $lastYValue = null;
                    foreach ($columnDataPoints as $columnDataPoint) {
                        $values = $columnDataPoint->values;
                        $lastYValue = $dataSeries['yBaseline'];
                        $yValueTotal = 0;
                        $sizePerColumn = $totalSizePerColumn / $columnDataPointsCount;
                        $padd = $sizePerColumn * $dataSeries['columnWidth'] * 0.5;
                        if ($columnDataPointsCount <= 1) {
                            $padd = 0;
                        }
                        $x += $padd / 2;
                        $sizePerColumn -= $padd;
                        $sum = 0;
                        $yDirection = 0;
                        $lastYCoordinate = null;
                        $valueCount = 0;
                        foreach ($values as $value) {
                            $sum += $value->value;
                            $drawSettings = DrawSettings::merge(
                                $dataSeries['columnDrawSettings'],
                                $columnDataPoint->drawSettings,
                                $value->drawSettings,
                            );
                            if ($valueCount) {
                                for ($i = 0; $i < $valueCount; $i++) {
                                    $drawSettings->fill?->applyHslModifyStep();
                                }
                            }
                            $yValue = $yValueTotal + $value->value;
                            $yCoordinate = $getYCoordinate($yValue);
                            if ($lastYCoordinate !== null) {
                                $yDirection = $lastYCoordinate > $yCoordinate ? -1 : 1;
                            }
                            $height = $getYCoordinate($lastYValue) - $yCoordinate;
                            $roundness = $dataSeries['edgeRoundness'];
                            $rect = new Rect(
                                $x,
                                $yCoordinate + min(0, $height),
                                $sizePerColumn,
                                abs($height),
                                $drawSettings,
                                $roundness,
                                $roundness,
                            );
                            $renderGroupDataSeriesSingle->renderers[] = $rect;
                            if ($valueLabelDrawSettings) {
                                $valueLabel = clone $valueLabelDrawSettings;
                                $valueLabel->x = $x + ($sizePerColumn / 2);
                                $valueLabel->y = $rect->y;
                                $valueLabel->anchorHorizontal = $valueLabel->anchorHorizontal ?? $valueLabel::ANCHOR_HORIZONTAL_CENTER;
                                $valueLabel->text = LabelFormats::valueToFormat($value->value, $yAxis->labelFormatter);
                                $renderGroupDataLabels->renderers[] = $valueLabel;
                            }
                            $lastYValue = $yValue;
                            $lastYCoordinate = $yCoordinate;
                            $yValueTotal += $value->value;
                            $valueCount++;
                        }
                        if ($summaryLabelDrawSettings && $lastYValue !== null) {
                            $valueLabel = clone $summaryLabelDrawSettings;
                            $valueLabel->x = $x + ($sizePerColumn / 2);
                            $valueLabel->y = $getYCoordinate($lastYValue);
                            $valueLabel->text = LabelFormats::valueToFormat($sum, $yAxis->labelFormatter);
                            $valueLabel->anchorHorizontal = $valueLabel->anchorHorizontal ?? $valueLabel::ANCHOR_HORIZONTAL_CENTER;
                            if ($valueLabel->anchorVertical === null) {
                                $valueLabel->anchorVertical = $yDirection === -1 ? $valueLabel::ANCHOR_VERTICAL_BOTTOM : $valueLabel::ANCHOR_VERTICAL_TOP;
                                $valueLabel->anchorVerticalOffset *= $yDirection;
                            }
                            $renderGroupDataLabels->renderers[] = $valueLabel;
                        }
                        $x += $padd / 2;
                        $x += $sizePerColumn;
                    }
                    $dataPointId++;
                }
            }
            if ($dataSeries['type'] === self::TYPE_LINE) {
                $path = new Renderer\Path('');
                $pathArea = new Renderer\Path('');
                $renderGroupDataSeriesSingle->renderers[] = $pathArea;
                $renderGroupDataSeriesSingle->renderers[] = $path;

                /** @var DrawSettings $lineDrawSettings */
                $lineDrawSettings = clone $dataSeries['lineDrawSettings'];
                $lineDrawSettings->fill = 'none';
                $lineDrawSettings->fillOpacity = null;

                /** @var DrawSettings $dataPointDrawSettings */
                $dataPointDrawSettings = clone $dataSeries['dataPointDrawSettings'];

                /** @var Renderer\TextRect|null $valueLabelDrawSettings */
                $valueLabelDrawSettings = $dataSeries['valueLabelDrawSettings'] ?? null;

                /** @var DrawSettings|null $areaDrawSettings */
                $areaDrawSettings = $dataSeries['areaDrawSettings'] ? clone $dataSeries['areaDrawSettings'] : null;

                /** @var LineDataPoint[] $dataPoints */
                $dataPoints = $dataSeries['dataPoints'];
                $xPoints = [];
                foreach ($dataPoints as $dataPoint) {
                    foreach ($allXDataPoints as $xPointId => $dataPointX) {
                        if ($dataPointX->x >= $dataPoint->x && $dataPointX->x <= $dataPoint->x) {
                            $xPoints[] = $allXCoordinates[$xPointId];
                        }
                    }
                }
                $yPoints = $yAxis->getPointYCoordinates($dataPoints);
                $lastX = $xPoints[0];
                $lastYValue = $yPoints[0];
                $d = "M $lastX,$lastYValue ";
                for ($i = 0; $i < count($yPoints); $i++) {
                    $x = $xPoints[$i];
                    $yCoordinate = $yPoints[$i];
                    $circleSize = $dataSeries['dataPointSize'];
                    $circle = new Renderer\Circle(
                        $x,
                        $yCoordinate,
                        $circleSize,
                        DrawSettings::merge(
                            $dataPointDrawSettings,
                            [
                                'fill' => $circleSize > 0 ? $dataPointDrawSettings->fill : 'none',
                            ],
                        ),
                    );
                    $dataPoint = $dataPoints[$i];
                    $circle->additionalAttributes = $dataPoint->pointAttributes ?? [];
                    $circle->additionalAttributes['data-value-x-label'] = LabelFormats::valueToFormat(
                        $dataPoint->x,
                        $this->xAxis->labelFormatter,
                    );
                    $circle->additionalAttributes['data-value-x'] = $dataPoint->x;
                    $circle->additionalAttributes['data-value-y'] = $dataPoint->y;
                    $circle->additionalAttributes['data-value-y-label'] = LabelFormats::valueToFormat($dataPoint->y, $yAxis->labelFormatter);
                    $renderGroupDataSeriesSingle->renderers[] = $circle;

                    if ($valueLabelDrawSettings) {
                        $valueLabel = clone $valueLabelDrawSettings;
                        $valueLabel->x = $x;
                        $valueLabel->y = $yCoordinate;
                        $valueLabel->text = LabelFormats::valueToFormat($dataPoint->y, $yAxis->labelFormatter);
                        $renderGroupDataLabels->renderers[] = $valueLabel;
                    }

                    if ($i > 0) {
                        $controlPointOffsetX = ($x - $lastX) * $dataSeries['curveStrength'] * 0.5;
                        if ($dataSeries['lineMode'] === self::LINEMODE_STRAIGHT) {
                            $d .= "L $x,$yCoordinate ";
                        }
                        if ($dataSeries['lineMode'] === self::LINEMODE_CURVE) {
                            $xControl = $lastX + $controlPointOffsetX;
                            $yControl = $lastYValue;
                            $lastCurveControlPoint = "$xControl,$yControl";
                            $d .= " C$lastCurveControlPoint ";
                            $xControl = $x - $controlPointOffsetX;
                            $yControl = $yCoordinate;
                            $lastCurveControlPoint = "$xControl,$yControl";
                            $d .= " $lastCurveControlPoint ";
                            $d .= " $x,$yCoordinate ";
                        }
                        if ($dataSeries['lineMode'] === self::LINEMODE_STEP) {
                            $d .= "L $x,$lastYValue L $x,$yCoordinate ";
                        }
                    }
                    $lastX = $x;
                    $lastYValue = $yCoordinate;
                }
                $path->d = $d;
                $path->drawSettings = $lineDrawSettings;
                if ($areaDrawSettings) {
                    $d .= "L $lastX,{$plotArea->y2}";
                    $d .= "L {$xPoints[0]},{$plotArea->y2}";
                    $d .= " Z";
                    $pathArea->d = $d;
                    $pathArea->drawSettings = $areaDrawSettings;
                }
            }
        }
        return $renderGroupSeries->toSvg($chart);
    }

}