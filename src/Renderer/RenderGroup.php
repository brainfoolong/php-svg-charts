<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class RenderGroup extends Renderer
{

    public function __construct(
        public string $id,
        /**
         * @var Renderer[]
         */
        public array $renderers = []
    ) {}

    public function toSvg(SvgChart $chart): string
    {
        $out = '<g ' . self::getAttributesString($this->getAllAttributes($chart)) . '>';
        foreach ($this->renderers as $renderer) {
            if (is_string($renderer)) {
                $out .= $renderer;
            } else {
                $out .= $renderer->toSvg($chart);
            }
        }
        $out .= '</g>';
        return $out;
    }

}