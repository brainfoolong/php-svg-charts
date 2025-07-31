<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\ChartsType\LinesAndColumns;
use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\LineDataPoint;
use BrainFooLong\SvgCharts\PlotArea;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class YAxis extends Renderer
{

    public const LABEL_POSITION_LEFT = 'left';
    public const LABEL_POSITION_RIGHT = 'right';

    public LinesAndColumns $series;
    public PlotArea $availablePlotArea;
    public array $annotations = [];

    public function __construct(
        public string $id,
        public string $label,
        public ?string $labelFormatter = null,
        public ?int $verticalLabels = null,
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
        public string $position = self::LABEL_POSITION_LEFT,
        public ?DrawSettings $drawSettingsValues = null,
        public ?DrawSettings $drawSettingsTitle = null,
        public ?TextRect $titleDefaults = null,
        public ?TextRect $labelDefaults = null,
        public float $margin = 10
    ) {}

    /**
     * @param bool $combineAxis
     * @return LineDataPoint[]
     */
    public function getDataPoints(bool $combineAxis): array
    {
        if ($combineAxis && $this->series->combineYAxis) {
            $combine = false;
            foreach ($this->series->combineYAxis as $axis) {
                if ($axis === $this) {
                    $combine = true;
                    break;
                }
            }
            if ($combine) {
                $dataPoints = [];
                foreach ($this->series->combineYAxis as $axis) {
                    $dataPoints = array_merge($dataPoints, $this->series->dataSeries[$axis->id]['dataPoints']);
                }
                LinesAndColumns::sortDataPointsByX($dataPoints);
                return $dataPoints;
            }
        }
        return $this->series->dataSeries[$this->id]['dataPoints'];
    }

    /**
     * @param LineDataPoint[] $dataPoints
     * @param array|null $additionalYValues If set, this x values will also be converted to x coorindates
     * @return float[]
     */
    public function getPointYCoordinates(array $dataPoints, ?array &$additionalYValues = null): array
    {
        $plotArea = $this->availablePlotArea;
        $minMax = $this->series->getMinMaxCoordinates($this->id);
        $dataHeight = $minMax['maxY'] - $minMax['minY'];
        $arr = [];
        foreach ($dataPoints as $dataPoint) {
            $y = $dataPoint->y;
            $offset = (1 / $dataHeight) * ($y - $minMax['minY']);
            $arr[] = $plotArea->y2 - $offset * $plotArea->getHeight();
        }
        if ($additionalYValues) {
            foreach ($additionalYValues as $key => $y) {
                $offset = (1 / $dataHeight) * ($y - $minMax['minY']);
                $additionalYValues[$key] = $plotArea->y2 - $offset * $plotArea->getHeight();
            }
        }
        return $arr;
    }

    public function addLineAnnotation(
        float $y,
        Line $line,
        TextRect $textRect
    ): void {
        $this->annotations[] = [
            "type" => "line",
            "y" => $y,
            "line" => $line,
            "textRect" => $textRect
        ];
    }

    public function addPointAnnotation(
        float $x,
        float $y,
        Circle $circle,
        TextRect $textRect
    ): void {
        $this->annotations[] = [
            "type" => "point",
            "x" => $x,
            "y" => $y,
            "circle" => $circle,
            "textRect" => $textRect
        ];
    }

    public function toSvg(SvgChart $chart): string
    {
        $plotArea = $this->availablePlotArea;

        $titleRectDefault = $this->titleDefaults ? clone $this->titleDefaults : new TextRect();
        $titleRectDefault->rotate = 90;
        $titleRectDefault->anchorHorizontal = $titleRectDefault::ANCHOR_HORIZONTAL_CENTER;
        $titleRectDefault->anchorVertical = $titleRectDefault::ANCHOR_VERTICAL_MIDDLE;
        $titleRectDefault->textAlignment = $titleRectDefault::TEXT_ALIGN_CENTER;

        $labelRectDefault = $this->labelDefaults ? clone $this->labelDefaults : new TextRect();
        $labelRectDefault->anchorHorizontal = $this->position === self::LABEL_POSITION_LEFT ? $labelRectDefault::ANCHOR_HORIZONTAL_RIGHT : $labelRectDefault::ANCHOR_HORIZONTAL_LEFT;
        $labelRectDefault->anchorVertical = $labelRectDefault::ANCHOR_VERTICAL_MIDDLE;
        $labelRectDefault->textAlignment = $labelRectDefault->anchorHorizontal;

        $lineSpacing = $plotArea->getHeight() / $this->verticalLabels;
        $minMax = $this->series->getMinMaxCoordinates($this->id);

        $minValue = $minMax['minY'];
        $maxValue = $minMax['maxY'];

        $valueRange = $maxValue - $minValue;
        $valueStep = $valueRange / $this->verticalLabels;

        $outputs = [];
        $labels = [];
        $maxWidth = 0;
        $margin = $this->margin;
        for ($i = 0; $i <= $this->verticalLabels; $i++) {
            $value = $minValue + ($valueStep * $i);
            $labelText = LabelFormats::valueToFormat($value, $this->labelFormatter);
            $labels[$i] = ["label" => $labelText];
            $width = $labelRectDefault->getMetaInformation($chart, $labelText)['width'];
            if ($maxWidth < $width) {
                $maxWidth = $width;
            }
        }
        $xKey = $this->position === self::LABEL_POSITION_LEFT ? 'x1' : 'x2';
        $xMulti = $this->position === self::LABEL_POSITION_LEFT ? 1 : -1;
        $plotArea->reduceWidth($maxWidth, $this->position);
        foreach ($labels as $i => $row) {
            $labelX = $plotArea->{$xKey} + ($margin * $xMulti);
            $labelY = $plotArea->y1 + $plotArea->getHeight() - ($i * $lineSpacing);
            $renderer = clone $labelRectDefault;
            $renderer->text = $row['label'];
            $renderer->x = $labelX;
            $renderer->y = $labelY;
            $outputs[] = $renderer->toSvg($chart);
        }
        $plotArea->reduceWidth($margin, $this->position);

        if ($this->label !== '') {
            $titleY = ($plotArea->getHeight() / 2) + $chart->topMargin;
            $titleX = $plotArea->{$xKey} - (($maxWidth + $margin) * $xMulti);
            $plotArea->reduceWidth(($titleRectDefault->getTotalHeight($chart) / 2), $this->position);
            $renderer = clone $titleRectDefault;
            $renderer->text = $this->label;
            $renderer->x = $titleX;
            $renderer->y = $titleY;
            $outputs[] = $renderer->toSvg($chart);
        }
        $plotArea->reduceWidth($margin, $this->position);
        return (new RenderGroup('yaxis', $outputs))->toSvg($chart);
    }

}