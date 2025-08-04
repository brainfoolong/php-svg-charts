<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\FontMetrics;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class TextRect extends Renderer
{

    public const ANCHOR_VERTICAL_TOP = 'top';
    public const ANCHOR_VERTICAL_MIDDLE = 'middle';
    public const ANCHOR_VERTICAL_BOTTOM = 'bottom';

    public const ANCHOR_HORIZONTAL_LEFT = 'left';
    public const ANCHOR_HORIZONTAL_CENTER = 'center';
    public const ANCHOR_HORIZONTAL_RIGHT = 'right';

    public const TEXT_ALIGN_LEFT = 'left';
    public const TEXT_ALIGN_CENTER = 'center';
    public const TEXT_ALIGN_RIGHT = 'right';

    private ?array $background = null;
    private ?array $metaCache = null;

    public function __construct(
        public string $text = '',
        public float|string $x = 0,
        public float|string $y = 0,
        public ?string $anchorHorizontal = null, // default is left
        public ?string $anchorVertical = null, // default is top
        public ?string $textAlignment = null, // default is left
        public ?float $rotate = null,
        public ?DrawSettings $drawSettings = null,
        public float $anchorVerticalOffset = 0,
        public float $anchorHorizontalOffset = 0
    ) {
        $this->id = "textrect";
    }

    public function removeBackground(): void
    {
        $this->background = null;
    }

    public function setBackground(array|float $padding = 5, string $color = "white", float $borderRadius = 0): Rect
    {
        $rect = new Rect(0, 0, 0, 0, drawSettings: new DrawSettings(fill: $color), borderRadius: $borderRadius);
        $this->background = [
            'padding' => is_numeric($padding) ? [$padding, $padding, $padding, $padding] : $padding,
            'rect' => $rect,
        ];
        return $rect;
    }

    /**
     * Get rough width/height text dimensions of given string
     * @param SvgChart $chart
     * @param string|null $text Override text if required
     * @param float|null $rotate Override rotation if required
     * @return array{width: float, height: float, lineHeight: float, drawSettings: DrawSettings}
     */
    public function getMetaInformation(SvgChart $chart, ?string $text = null, ?float $rotate = null): array
    {
        $text = $text ?? $this->text;
        $text = strip_tags($text);
        $rotate = $rotate ?? $this->rotate ?? 0;
        $cacheKey = $text . "_" . number_format($rotate, 2);
        if (isset($this->metaCache[$cacheKey])) {
            return $this->metaCache[$cacheKey];
        }
        $drawSettings = DrawSettings::merge($chart->defaultFontDrawSettings, $this->drawSettings);
        $data = FontMetrics::getTextDimensions($drawSettings, $text, $rotate);
        $data['lineHeight'] = FontMetrics::getLineHeight($drawSettings);
        $data['drawSettings'] = $drawSettings;
        $this->metaCache[$cacheKey] = $data;
        return $this->metaCache[$cacheKey];
    }

    public function getTotalWidth(SvgChart $chart, ?string $text = null, ?float $rotate = null): float
    {
        return $this->getMetaInformation($chart, $text, $rotate)['width'] + ($this->background['padding'][1] ?? 0) + ($this->background['padding'][3] ?? 0);
    }

    public function getTotalHeight(SvgChart $chart, ?string $text = null, ?float $rotate = null): float
    {
        return $this->getMetaInformation($chart, $text, $rotate)['height'] + ($this->background['padding'][0] ?? 0) + ($this->background['padding'][2] ?? 0);
    }

    public function toSvg(SvgChart $chart): string
    {
        $outputs = [];
        $meta = $this->getMetaInformation($chart, rotate: 0);
        $drawSettings = $meta['drawSettings'];
        $lineHeight = $meta['lineHeight'];
        $lines = explode("\n", $this->text);

        $textWidth = $meta['width'];
        $totalWidth = $this->getTotalWidth($chart, rotate: 0);
        $totalHeight = $this->getTotalHeight($chart, rotate: 0);
        $padding = $this->background['padding'] ?? null;

        $left = $this->x + ($padding[3] ?? 0);
        if ($this->anchorHorizontal === self::ANCHOR_HORIZONTAL_CENTER) {
            $left -= $totalWidth / 2;
        } elseif ($this->anchorHorizontal === self::ANCHOR_HORIZONTAL_RIGHT) {
            $left -= $totalWidth;
        }

        $top = $this->y + $lineHeight + ($padding[0] ?? 0);
        if ($this->anchorVertical === self::ANCHOR_VERTICAL_MIDDLE) {
            $top -= ($totalHeight + $lineHeight * 0.25) / 2;
        } elseif ($this->anchorVertical === self::ANCHOR_VERTICAL_BOTTOM) {
            $top -= $totalHeight;
        }
        $left += $this->anchorHorizontalOffset;
        $top += $this->anchorVerticalOffset;
        $textY = $top - count($lines);
        $attributes = [];
        foreach ($lines as $line) {
            $textX = $left;
            if ($this->textAlignment === 'center') {
                $textX += $textWidth / 2;
                $attributes['text-anchor'] = 'middle';
            } elseif ($this->textAlignment === 'right') {
                $textX += $textWidth;
                $attributes['text-anchor'] = 'end';
            }
            $attributes['x'] = $textX;
            $attributes['y'] = $textY;
            $attributes['textRendering'] = 'optimizeLegibility';
            $attributes['dominantBaseline'] = 'alphabetic';
            $attributes['alignmentBaseline'] = 'auto';
            $attributes = array_merge($attributes, $drawSettings->toArray());
            $textY += $lineHeight;
            $contents = str_contains($line, '<tspan') ? $line : htmlspecialchars($line);
            $outputs[] = '<text ' . self::getAttributesString($attributes) . '>' . $contents . '</text>';
        }

        if ($this->background) {
            $rectX = $left - $padding[3];
            $rectY = $top - ($padding[0] + $lineHeight) + count($lines);

            /**
             * @var Rect $rect
             */
            $rect = clone $this->background['rect'];
            $rect->x = $rectX;
            $rect->y = $rectY;
            $rect->width = $totalWidth;
            $rect->height = $totalHeight;
            $centerX = $rectX + $totalWidth / 2;
            $centerY = $rectY + $totalHeight / 2;
            array_unshift($outputs, $rect->toSvg($chart));
        } else {
            $centerX = $this->x;
            $centerY = $this->y;
        }

        // debug helper
//        $outputs[] = $chart->createDebugPoint($this->x, $this->y, debugInfo: $this->text);
        $group = new RenderGroup('textrect', $outputs);
        if ($this->rotate) {
            $group->transforms['rotate'] = $this->rotate . ', ' . $centerX . ', ' . $centerY;
        }
        return $group->toSvg($chart);
    }

}