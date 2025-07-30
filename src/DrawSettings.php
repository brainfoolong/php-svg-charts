<?php

namespace BrainFooLong\SvgCharts;

class DrawSettings
{

    public static function merge(...$settings): DrawSettings
    {
        $new = new DrawSettings();
        foreach ($settings as $row) {
            if (!$row) {
                continue;
            }
            foreach ($new as $prop => $value) {
                $newValue = (is_array($row) ? $row[$prop] ?? null : $row->{$prop} ?? null) ?? $value;
                $new->{$prop} = $newValue;
            }
        }
        return $new;
    }

    public function __construct(
        public ?string $fill = null,
        public ?float $fillOpacity = null,
        public ?string $stroke = null,
        public ?float $strokeOpacity = null,
        public ?float $strokeWidth = null,
        public ?string $strokeDasharray = null,
        /**
         * Values: 'auto', 'crispEdges', 'optimizeSpeed', 'geometricPrecision'
         * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/shape-rendering
         * @var string|null
         */
        public ?string $shapeRendering = null,
        public ?string $fontFamily = null,
        public ?float $fontSize = null,
        /**
         * Values: 'normal', 'italic', 'oblique'
         * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/font-style
         * @var string|null
         */
        public ?string $fontStyle = null,
        /**
         * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/font-variant
         * @var string|null
         */
        public ?string $fontVariant = null,
        /**
         * Values: 'normal', 'bold'
         * Other values here dont make sense as most fonts only support bold and normal very well
         * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/font-weight
         * @var string|null
         */
        public ?string $fontWeight = null,
    ) {}

    public function toArray(): array
    {
        return array_filter((array)$this);
    }

}