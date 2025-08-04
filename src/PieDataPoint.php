<?php

namespace BrainFooLong\SvgCharts;

class PieDataPoint
{

    public function __construct(
        public float $value,
        public string $color,
        public ?string $label = null,
        public ?float $explodeDistance = null,
    ) {}

}