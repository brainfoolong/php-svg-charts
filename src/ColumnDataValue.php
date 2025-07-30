<?php

namespace BrainFooLong\SvgCharts;

use BrainFooLong\SvgCharts\Renderer\TextRect;

class ColumnDataValue
{

    public function __construct(
        public float $value,
        public DrawSettings|null $drawSettings = null,
        public TextRect|null $dataPointLabelDrawSettings = null,
        public ?array $additionalAttributes = null,
    ) {}

}