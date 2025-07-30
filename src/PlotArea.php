<?php

namespace BrainFooLong\SvgCharts;

class PlotArea
{

    public function __construct(
        public float $x1,
        public float $x2,
        public float $y1,
        public float $y2
    ) {}

    public function getWidth(): float
    {
        return $this->x2 - $this->x1;
    }

    public function getHeight(): float
    {
        return $this->y2 - $this->y1;
    }

    public function reduceWidth(float $width, string $from = "left"): void
    {
        if ($from === "left") {
            $this->x1 += $width;
        } else {
            $this->x2 -= $width;
        }
    }

    public function reduceHeight(float $height, string $from = "bottom"): void
    {
        if ($from === "bottom") {
            $this->y2 -= $height;
        } else {
            $this->y1 += $height;
        }
    }

}