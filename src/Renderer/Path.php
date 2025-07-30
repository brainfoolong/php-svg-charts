<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\SvgChart;

class Path extends SvgElement
{

    public static array $drawSettingsDefault = ['stroke' => 'black', 'strokeWidth' => 1];
    public string $tagName = 'path';

    public function __construct(
        public string $d,
        public ?DrawSettings $drawSettings = null
    ) {
        $this->id = "path";
        if (!$this->drawSettings) {
            $this->drawSettings = new DrawSettings();
        }
        $this->drawSettings = $this->drawSettings::merge(self::$drawSettingsDefault, $this->drawSettings);
    }

    public function getAllAttributes(SvgChart $chart): array
    {
        $arr = parent::getAllAttributes($chart);
        $arr['d'] = $this->d;
        return array_merge($arr, $this->drawSettings->toArray());
    }

}