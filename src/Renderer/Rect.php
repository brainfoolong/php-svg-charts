<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\SvgChart;

class Rect extends SvgElement
{

    public static array $drawSettingsDefault = ['fill' => 'white'];

    public string $tagName = 'rect';

    public function __construct(
        public float|string $x = 0,
        public float|string $y = 0,
        public float|string $width = 0,
        public float|string $height = 0,
        public ?DrawSettings $drawSettings = null,
        public float|string|null $borderRadius = null,
        public float|string|null $verticalRadius = null
    ) {
        $this->id = "rect";
        if (!$this->drawSettings) {
            $this->drawSettings = new DrawSettings();
        }
        $this->drawSettings = $this->drawSettings::merge(self::$drawSettingsDefault, $this->drawSettings);
    }

    public function getAllAttributes(SvgChart $chart): array
    {
        $arr = parent::getAllAttributes($chart);
        $arr['x'] = $this->x;
        $arr['y'] = $this->y;
        $arr['width'] = $this->width;
        $arr['height'] = $this->height;
        $arr['rx'] = $this->borderRadius;
        return array_merge($arr, $this->drawSettings->toArray());
    }

}