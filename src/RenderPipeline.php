<?php

namespace BrainFooLong\SvgCharts;

class RenderPipeline
{

    /**
     * @var Renderer[]
     */
    public array $renderers = [];

    public function toSvg(SvgChart $chart): string
    {
        $outputs = [];
        $renderGroups = [];
        foreach ($this->renderers as $key => $renderer) {
            $outputs[$key] = '';
            $renderGroups[$renderer->processPriority][$key] = $renderer;
        }
        krsort($renderGroups, SORT_NUMERIC);
        foreach ($renderGroups as $renderGroup) {
            foreach ($renderGroup as $outputKey => $renderer) {
                $outputs[$outputKey] = $renderer->toSvg($chart);
            }
        }
        return implode("\n", $outputs);
    }

}