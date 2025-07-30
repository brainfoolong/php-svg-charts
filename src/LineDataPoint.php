<?php

namespace BrainFooLong\SvgCharts;

class LineDataPoint
{

    public function __construct(
        public float $x,
        public float $y,
        public ?array $pointAttributes = null,
    ) {}

}