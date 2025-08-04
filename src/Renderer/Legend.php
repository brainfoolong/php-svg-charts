<?php

namespace BrainFooLong\SvgCharts\Renderer;

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\Renderer;
use BrainFooLong\SvgCharts\SvgChart;

class Legend extends Renderer
{

    public const POSITION_TOP = 'top';
    public const POSITION_RIGHT = 'right';
    public const POSITION_BOTTOM = 'bottom';
    public const POSITION_LEFT = 'left';

    /**
     * This keep the original svg height the same and reduces the chart plot area by legend size
     * Result: Original SVG size, reduced chart size
     */
    public const HEIGHTCALC_CONSTRAIN = 'constrain';
    /**
     * This increase svg height by legend size
     * Result: Increased SVG size, original chart size
     */
    public const HEIGHTCALC_EXPAND = 'expand';

    /**
     * @var TextRect[]
     */
    private array $labels = [];

    private float $startX = 0;
    private float $startY = 0;
    private float $width = 0;
    private float $height = 0;

    public int $processPriority = -2;

    public function __construct(
        public string $position = self::POSITION_TOP,

        /**
         * How the height of the legend and chart should be calculated
         * @var string
         */
        public string $heightCalculation = self::HEIGHTCALC_CONSTRAIN,

        /**
         * Padding for each column
         * @var float|float[]|int
         */
        public float|array $columnPadding = 5,

        /**
         * Margin between legend and other chart draw areas
         * @var float|int
         */
        public float $margin = 20,

        /**
         * How many columns should be drawn per row
         * Null = defaults
         * Defaults = position left/right = 1, position top/bottom = 2
         * @var int|null
         */
        public ?int $columnsPerRow = null,

        /**
         * How large is a column
         * Null = defaults
         * Defaults = position left/right = 150px, position top/bottom = total chart width divided by $columnsPerRow
         * @var int|null
         */
        public ?int $columnWidth = null,

        /**
         * Draw a background behind the complete legend
         * @var string|null
         */
        public ?string $backgroundColor = null,
    ) {}

    public function addLabel(string $dotColor, string $title, ?string $textColor = null, string $dotChar = '⦿'): TextRect
    {
        $title = '<tspan fill="' . $dotColor . '">' . $dotChar . '</tspan> <tspan>' . $title . '</tspan>';
        $rect = new TextRect($title, drawSettings: new DrawSettings($textColor));
        $rect->setBackground($this->columnPadding, 'transparent');
        $rect->anchorHorizontal = $rect::ANCHOR_HORIZONTAL_LEFT;
        $rect->anchorVertical = $rect::ANCHOR_VERTICAL_TOP;
        $this->labels[] = $rect;
        return $rect;
    }

    public function getColumnsPerRow(SvgChart $chart): int
    {
        if ($this->columnsPerRow !== null) {
            return $this->columnsPerRow;
        }
        return ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) ? 2 : 1;
    }

    public function getColumnWidth(SvgChart $chart): float
    {
        if ($this->columnWidth !== null) {
            return $this->columnWidth;
        }
        $chartWidth = $chart->width - $chart->leftMargin - $chart->rightMargin;
        return ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) ? $chartWidth / $this->getColumnsPerRow($chart) : 150;
    }

    public function prepareDrawing(SvgChart $chart): void
    {
        $columnsPerRow = $this->getColumnsPerRow($chart);
        $width = $this->getColumnWidth($chart) * $this->getColumnsPerRow($chart);

        $height = 0;
        $count = 0;
        foreach ($this->labels as $textRect) {
            if (!$count) {
                $height += $textRect->getTotalHeight($chart);
            }
            $count++;
            if ($count >= $columnsPerRow) {
                $count = 0;
            }
        }
        $this->width = $width;
        $this->height = $height;

        $calc = $this->heightCalculation;
        $pos = $this->position;
        $usedWidth = $this->width + $this->margin;
        $usedHeight = $this->height + $this->margin;
        if ($pos === self::POSITION_TOP) {
            $this->startX = $chart->leftMargin;
            $this->startY = $chart->topMargin;
            $chart->topMargin += $usedHeight;
            if ($calc === self::HEIGHTCALC_EXPAND) {
                $chart->height += $usedHeight;
            }
        }
        if ($pos === self::POSITION_BOTTOM) {
            $this->startX = $chart->leftMargin;
            $chart->bottomMargin += $usedHeight;
            if ($calc === self::HEIGHTCALC_EXPAND) {
                $chart->height += $usedHeight;
            }
            $this->startY = $chart->height - $this->height - $chart->topMargin;
        }
        if ($pos === self::POSITION_LEFT) {
            $this->startX = $chart->leftMargin;
            $this->startY = $chart->topMargin;
            $chart->leftMargin += $usedWidth;
            if ($calc === self::HEIGHTCALC_EXPAND) {
                $chart->width += $usedWidth;
            }
        }
        if ($pos === self::POSITION_RIGHT) {
            $this->startY = $chart->topMargin;
            if ($calc === self::HEIGHTCALC_EXPAND) {
                $chart->width += $usedWidth;
            }
            $chart->rightMargin += $usedWidth;
            $this->startX = $chart->width - $this->width - $chart->leftMargin;
        }
    }

    public function toSvg(SvgChart $chart): string
    {
        $group = new RenderGroup('legend');
        $bg = null;
        if ($this->backgroundColor !== null) {
            $bg = new Rect($this->startX, $this->startY, $this->width, $this->height, new DrawSettings($this->backgroundColor));
            $group->renderers[] = $bg;
        }

        $columnsPerRow = $this->getColumnsPerRow($chart);
        $columnWidth = $this->getColumnWidth($chart);

        $x = $this->startX;
        $y = $this->startY;

        $count = 0;
        foreach ($this->labels as $textRect) {
            $count++;
            $textRect = clone $textRect;
            $textRect->x = $x;
            $textRect->y = $y;
            $x += $columnWidth;
            if ($count >= $columnsPerRow) {
                $count = 0;
                $x = $this->startX;
                $y += $textRect->getTotalHeight($chart);
            }
            $group->renderers[] = $textRect;
        }
        return $group->toSvg($chart);
    }

}