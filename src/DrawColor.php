<?php

namespace BrainFooLong\SvgCharts;

class DrawColor
{

    public static function merge(...$colors): DrawColor
    {
        $new = new DrawColor();
        foreach ($colors as $row) {
            if (!$row) {
                continue;
            }
            foreach ($new as $prop => $value) {
                if ((is_array($row) && array_key_exists($prop, $row)) || (is_object($row) && property_exists($row, $prop))) {
                    $newValue = (is_array($row) ? $row[$prop] ?? null : $row->{$prop} ?? null) ?? $value;
                    $new->{$prop} = $newValue;
                }
            }
        }
        return $new;
    }

    public function __construct(
        /**
         * Hex value in format #000000
         */
        public ?string $hex = null,
        /**
         * HSL (Hue, Saturation, Lightness) value
         * Each value is float between 0 and 1
         * Example: [0.2, 0.3, 0.5]
         * @var float[]|null
         */
        public ?array $hsl = null,

        /**
         * If set, it is internally used to add/substract given values of $hsl on each draw step of stacked columns to provide slight color variation for the stack
         * See examples/column-line-mixed-chart.php
         * Each value is float between 0 and 1
         * Example: [0.2, 0.3, 0.5]
         * @var float[]|null
         */
        public ?array $hslModifyStep = null

    ) {
        $this->hslNormalize("hsl");
        $this->hslNormalize("hslModifyStep");
    }

    public function applyHslModifyStep(): void
    {
        if ($this->hsl && $this->hslModifyStep) {
            $this->hsl["h"] += $this->hslModifyStep["h"] ?? 0;
            $this->hsl["s"] += $this->hslModifyStep["s"] ?? 0;
            $this->hsl["l"] += $this->hslModifyStep["l"] ?? 0;
            $this->clampHsl($this->hsl);
        }
    }

    public function __toString(): string
    {
        if ($this->hex) {
            return $this->hex;
        }
        if ($this->hsl) {
            return "hsl(" . ($this->hsl['h'] * 360) . ", " . ($this->hsl['s'] * 100) . "%, " . ($this->hsl['l'] * 100) . "%)";
        }
        return 'black';
    }

    private function hslNormalize(string $property): void
    {
        $original = $this->{$property};
        if ($original) {
            $arr = [];
            foreach ($original as $k => $value) {
                if ($k === 0 || $k === 'h') {
                    $arr["h"] = $value;
                }
                if ($k === 1 || $k === 's') {
                    $arr["s"] = $value;
                }
                if ($k === 2 || $k === 'l') {
                    $arr["l"] = $value;
                }
            }
            $this->clampHsl($arr);
            $this->{$property} = $arr;
        }
    }

    private function clampHsl(array &$arr): void
    {
        if (isset($arr['s'])) {
            if ($arr['s'] > 1) {
                $arr['s'] = 1;
            } elseif ($arr['s'] < 0) {
                $arr['s'] = 0;
            }
        }
        if (isset($arr['l'])) {
            if ($arr['l'] > 1) {
                $arr['l'] = 1;
            } elseif ($arr['l'] < 0) {
                $arr['l'] = 0;
            }
        }
    }

}