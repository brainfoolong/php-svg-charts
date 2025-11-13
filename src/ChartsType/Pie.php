<?php

namespace BrainFooLong\SvgCharts\ChartsType;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\PieDataPoint;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\Renderer\RenderGroup;
use BrainFooLong\SvgCharts\SvgChart;

class Pie extends Renderer
{

    public const POSITION_LEFT = 'left';
    public const POSITION_RIGHT = 'right';

    public const LABELPOS_INSIDE = 'inside';
    public const LABELPOS_OUTSIDE = 'outside';

    public array $dataSeries = [];

    public function __construct(public string $id) {}

    /**
     * @param PieDataPoint[]|array $dataPoints
     * @param string $position
     * @param string $valueLabelPosition
     * @param float $shadowIntensity
     * @param DrawSettings|null $segmentDrawSettings
     * @param Renderer\TextRect|null $valueLabelDrawSettings If not set, no value labels are drawn
     * @return void
     */
    public function addDataSeries(
        array $dataPoints,
        string $position = self::POSITION_LEFT,
        string $valueLabelPosition = self::LABELPOS_OUTSIDE,
        float $shadowIntensity = 0.3,
        ?DrawSettings $segmentDrawSettings = null,
        ?Renderer\TextRect $valueLabelDrawSettings = null,
    ): void {
        foreach ($dataPoints as $key => $dataPoint) {
            if (is_array($dataPoint)) {
                $dataPoints[$key] = new PieDataPoint(...$dataPoint);
            }
        }
        $segmentDrawSettings = $segmentDrawSettings ?? new DrawSettings();
        $this->dataSeries[] = [
            'dataPoints' => $dataPoints,
            'position' => $position,
            'valueLabelPosition' => $valueLabelPosition,
            'shadowIntensity' => $shadowIntensity,
            'segmentDrawSettings' => $segmentDrawSettings,
            'valueLabelDrawSettings' => $valueLabelDrawSettings,
        ];
    }

    public function toSvg(SvgChart $chart): string
    {
        if (!$this->dataSeries) {
            return '';
        }
        $renderGroup = new RenderGroup('pies-' . $this->id);
        foreach ($this->dataSeries as $dataSeries) {
            $renderShadow = new RenderGroup('shadow');
            $renderGroup->renderers[] = $renderShadow;

            $renderPie = new RenderGroup('pie');
            $renderGroup->renderers[] = $renderPie;

            $renderAnnotations = new RenderGroup('annotations');
            $renderGroup->renderers[] = $renderAnnotations;

            /** @var PieDataPoint[] $dataPoints */
            $dataPoints = $dataSeries['dataPoints'];

            $total = array_sum(array_map(function (PieDataPoint $data) {
                return $data->value;
            }, $dataPoints));

            if ($total <= 0.0) {
                return '';
            }
            $chart->legend?->prepareDrawing($chart);

            $plotArea = $chart->getPlotArea();
            $size = min($plotArea->getWidth(), $plotArea->getHeight());

            $maxExplodeDistance = max(array_map(function (PieDataPoint $data) {
                return $data->explodeDistance ?? 0;
            }, $dataPoints));

            $cx = $size / 2 + $chart->leftMargin;
            if ($dataSeries['position'] === self::POSITION_RIGHT) {
                $cx = $chart->width - $chart->rightMargin - $size / 2;
            }
            $cy = $size / 2 + $chart->topMargin;

            $radius = ($size / 2) - $maxExplodeDistance;
            if ($dataSeries['valueLabelPosition'] === self::LABELPOS_OUTSIDE) {
                $radius *= 0.8;
            }

            $currentAngle = 0;

            foreach ($dataPoints as $data) {
                if ($data->value <= 0) {
                    continue;
                }
                $radiusLabel = $dataSeries['valueLabelPosition'] === self::LABELPOS_INSIDE ? $radius / 1.5 : $radius * 1.2;

                $sliceAngle = ($data->value / $total) * 360;
                $currentAngle += $sliceAngle;
                $largeArcFlag = $sliceAngle > 180 ? 1 : 0;

                $midAngle = deg2rad($currentAngle - $sliceAngle / 2);
                $explodeX = $data->explodeDistance * cos($midAngle);
                $explodeY = $data->explodeDistance * sin($midAngle);

                $adjustedCx = $cx + $explodeX;
                $adjustedCy = $cy + $explodeY;

                $x1 = $adjustedCx + $radius * cos(deg2rad($currentAngle - $sliceAngle));
                $y1 = $adjustedCy + $radius * sin(deg2rad($currentAngle - $sliceAngle));
                $x2 = $adjustedCx + $radius * cos(deg2rad($currentAngle));
                $y2 = $adjustedCy + $radius * sin(deg2rad($currentAngle));

                $pathData = "M $adjustedCx,$adjustedCy L $x1,$y1 A $radius,$radius 0 $largeArcFlag,1 $x2,$y2 Z";

                $labelX = $adjustedCx + $radiusLabel * cos($midAngle);
                $labelY = $adjustedCy + $radiusLabel * sin($midAngle);

                if (count($dataSeries['dataPoints']) === 1) {
                    $pathData = "M $cx,$cy m -$radius,0 a $radius,$radius 0 1,0 " . (2 * $radius) . ",0 a $radius,$radius 0 1,0 -" . (2 * $radius) . ',0 Z';
                }

                if ($dataSeries['shadowIntensity'] > 0) {
                    $path = new Renderer\Path($pathData, new DrawSettings('black', $dataSeries['shadowIntensity'], stroke: '', strokeWidth: 0));
                    $path->additionalAttributes['transform'] = 'translate(4,4)';
                    $renderShadow->renderers[] = $path;
                }

                $segmentDrawSettings = DrawSettings::merge(new DrawSettings($data->color), $dataSeries['segmentDrawSettings']);
                $path = new Renderer\Path($pathData, $segmentDrawSettings);
                $renderPie->renderers[] = $path;

                if ($dataSeries['valueLabelDrawSettings'] && $data->label !== null) {
                    /** @var Renderer\TextRect $label */
                    $label = clone $dataSeries['valueLabelDrawSettings'];
                    $label->drawSettings = DrawSettings::merge($chart->defaultFontDrawSettings, $label->drawSettings);
                    $label->text = $data->label;
                    $label->x = $labelX;
                    $label->y = $labelY;
                    $renderAnnotations->renderers[] = $label;
                }
            }
        }
        return $renderGroup->toSvg($chart);
    }

}