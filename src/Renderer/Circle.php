<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\SvgChart;

class Circle extends SvgElement
{

    public static array $drawSettingsDefault = ['fill' => 'black'];

    public string $tagName = 'circle';

    public function __construct(
        public float $cx = 0,
        public float $cy = 0,
        public float $r = 0,
        public ?DrawSettings $drawSettings = null
    ) {
        $this->id = "circle";
        if (!$this->drawSettings) {
            $this->drawSettings = new DrawSettings();
        }
        $this->drawSettings = $this->drawSettings::merge(self::$drawSettingsDefault, $this->drawSettings);
    }

    public function getAllAttributes(SvgChart $chart): array
    {
        $arr = parent::getAllAttributes($chart);
        $arr['cx'] = $this->cx;
        $arr['cy'] = $this->cy;
        $arr['r'] = $this->r;
        return array_merge($arr, $this->drawSettings->toArray());
    }

}