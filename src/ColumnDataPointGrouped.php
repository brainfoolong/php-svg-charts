<?php

namespace BrainFooLong\SvgCharts;

class ColumnDataPointGrouped
{

    public function __construct(
        public float $x,
        public array $dataPoints,
    ) {
        foreach ($this->dataPoints as $key => $dataPoint) {
            if (is_array($dataPoint)) {
                $dataPoint['x'] = $x;
                $this->dataPoints[$key] = new ColumnDataPoint(...$dataPoint);
            }
        }
    }

}