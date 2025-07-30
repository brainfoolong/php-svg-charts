<?php

namespace BrainFooLong\SvgCharts;

use BrainFooLong\SvgCharts\Renderer\TextRect;

class ColumnDataPoint
{

    public function __construct(
        public float $x,
        /**
         * @var float|array|ColumnDataValue[]
         */
        public float|array $values,
        public DrawSettings|null $drawSettings = null,
        public TextRect|null $dataPointLabelDrawSettings = null,
        public ?array $additionalAttributes = null,
    ) {
        if (!is_array($this->values)) {
            $this->values = [$this->values];
        }
        foreach ($this->values as $key => $value) {
            if (is_numeric($value)) {
                $this->values[$key] = new ColumnDataValue((float)$value);
            } elseif (is_array($value)) {
                $this->values[$key] = new ColumnDataValue(...$value);
            }
        }
    }

}