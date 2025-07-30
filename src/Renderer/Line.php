<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\SvgChart;

class Line extends SvgElement
{

    public static array $drawSettingsDefault = ['stroke' => 'black', 'strokeWidth' => 1];

    public string $tagName = 'line';

    public function __construct(
        public float|string $x1 = 0,
        public float|string $y1 = 0,
        public float|string $x2 = 0,
        public float|string $y2 = 0,
        public ?DrawSettings $drawSettings = null
    ) {
        $this->id = "line";
        if (!$this->drawSettings) {
            $this->drawSettings = new DrawSettings();
        }
        $this->drawSettings = $this->drawSettings::merge(self::$drawSettingsDefault, $this->drawSettings);
    }

    public function getAllAttributes(SvgChart $chart): array
    {
        $arr = parent::getAllAttributes($chart);
        $arr['x1'] = $this->x1;
        $arr['y1'] = $this->y1;
        $arr['x2'] = $this->x2;
        $arr['y2'] = $this->y2;
        return array_merge($arr, $this->drawSettings->toArray());
    }

}