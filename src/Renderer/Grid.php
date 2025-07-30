<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class Grid extends Renderer
{

    public static array $drawSettingsDefault = ['stroke' => '#ccc', 'strokeWidth' => 1, 'shapeRendering' => 'crispEdges'];

    public int $processPriority = -1;

    public function __construct(
        public int $lines,
        public string $lineColor,
        public float $thickness = 1,
        public float $opacity = 0.2,
        public ?DrawSettings $drawSettings = null
    ) {
        if (!$this->drawSettings) {
            $this->drawSettings = new DrawSettings();
        }
        $this->drawSettings = $this->drawSettings::merge(self::$drawSettingsDefault, $this->drawSettings);
    }

    public function toSvg(SvgChart $chart): string
    {
        $numLines = $this->lines;
        $plotArea = $chart->getPlotArea();
        $lineSpacing = $plotArea->getHeight() / $numLines;
        $renderers = [];
        for ($i = 0; $i <= $numLines; $i++) {
            $y = $plotArea->y2 - ($i * $lineSpacing);
            $renderers[] = new Line(
                x1: $plotArea->x1,
                y1: $y,
                x2: $plotArea->x2,
                y2: $y,
                drawSettings: $this->drawSettings
            );
        }
        return (new RenderGroup('grid', $renderers))->toSvg($chart);
    }

}