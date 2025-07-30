<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

abstract class SvgElement extends Renderer
{

    public string $contents = '';

    public string $tagName = '';

    public function toSvg(SvgChart $chart): string
    {
        $out = '';
        $attributesString = self::getAttributesString($this->getAllAttributes($chart));
        if ($this->contents) {
            $contents = str_contains($this->contents, '<tspan') ? $this->contents : htmlspecialchars($this->contents);
            $out .= '<' . $this->tagName . ' ' . $attributesString . '>' . $contents . '</' . $this->tagName . '>';
        } else {
            $out .= '<' . $this->tagName . ' ' . $attributesString . '/>';
        }
        return $out;
    }

}