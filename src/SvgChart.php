<?php

namespace BrainFooLong\SvgCharts;

use BrainFooLong\SvgCharts\ChartsType\LinesAndColumns;
use BrainFooLong\SvgCharts\Renderer\Grid;
use BrainFooLong\SvgCharts\Renderer\Rect;
use BrainFooLong\SvgCharts\Renderer\RenderGroup;

class SvgChart
{

    public RenderPipeline $renderPipeline;
    public RenderPipeline $renderPipelineAnnotations;
    public Rect $background;
    public Grid $grid;
    public LinesAndColumns $chartType;

    /**
     * Svg <defs> data, so you are able to reuse them in the svg
     * Usefull for line gradients and such stuff
     * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Tutorials/SVG_from_scratch/Gradients
     * @var string
     */
    public string $svgDefs = '';

    /**
     * Internal counter for defs ids
     * @var int
     */
    private int $defsCounter = 0;

    public static function rotate(float $angle, float $x, float $y): array
    {
        if (!$angle) {
            return [$x, $y];
        }
        $angleRadians = deg2rad($angle);
        $absCos = abs(cos($angleRadians));
        $absSin = abs(sin($angleRadians));
        $newWidth = ($x * $absCos) + ($y * $absSin);
        $height = ($x * $absSin) + ($y * $absCos);
        $width = $newWidth;
        return [$width, $height];
    }

    public function __construct(
        public string $id,
        public float $width,
        public float $height,
        public float $leftMargin = 10,
        public float $rightMargin = 10,
        public float $bottomMargin = 10,
        public float $topMargin = 10,
        public ?DrawSettings $defaultFontDrawSettings = null,
    ) {
        $this->defaultFontDrawSettings = DrawSettings::merge(['fontFamily' => 'arial', 'fontSize' => 14, 'fill' => 'black'], $this->defaultFontDrawSettings);
        $this->renderPipeline = new RenderPipeline();
        $this->renderPipelineAnnotations = new RenderPipeline();
        $renderer = new Rect(0, 0, "100%", "100%", new DrawSettings('white'));
        $renderGroup = new RenderGroup('background', [$renderer]);
        $this->renderPipeline->renderers[] = $renderGroup;
        $this->background = $renderer;
    }

    /**
     * Define a line gradient
     * Reference it later in fill/stroke by using its return value
     * Notice: MPDFs doesnt properly support the $rotate parameter
     * @param int $rotate 0 = left to right, 90 = top to down
     * @param array $steps Key is step in percentage (between 0 and 100) and value is the color
     *  Example: [0 => 'red', 50 => 'white', 100 => 'red']
     * @return string The color reference
     */
    public function defineLinearGradient(int $rotate, array $steps): string
    {
        $this->defsCounter++;
        $id = str_replace("-", "_", $this->id) . "_" . $this->defsCounter;
        $this->svgDefs .= '<linearGradient id="' . $id . '" gradientTransform="rotate(' . $rotate . ')" >';
        foreach ($steps as $offset => $color) {
            $this->svgDefs .= '<stop offset="' . $offset . '%" stop-color="' . $color . '"/>';
        }
        $this->svgDefs .= '</linearGradient>';
        return 'url(#' . $id . ')';
    }

    public function createGrid(): Grid
    {
        $renderer = new Grid(5, "#555");
        $this->renderPipeline->renderers[] = $renderer;
        $this->grid = $renderer;
        return $renderer;
    }

    public function createLineAndColumnChart(): LinesAndColumns
    {
        $renderer = new LinesAndColumns("lianco");
        $this->renderPipeline->renderers[] = $renderer;
        $this->chartType = $renderer;
        return $renderer;
    }

    /**
     * @return PlotArea
     */
    public function getPlotArea(): PlotArea
    {
        return new PlotArea(
            $this->leftMargin,
            $this->width - $this->rightMargin,
            $this->topMargin,
            $this->height - $this->bottomMargin
        );
    }

    public function toSvg(?array $customSvgTagAttributes = null): string
    {
        $original = unserialize(serialize($this));;
        $viewBox = "0 0 $this->width $this->height";
        $customSvgTagAttributes['xmlns'] = 'http://www.w3.org/2000/svg';
        if (!isset($customSvgTagAttributes['id'])) {
            $customSvgTagAttributes['id'] = $this->id;
        }
        $customSvgTagAttributes['width'] = $this->width;
        $customSvgTagAttributes['height'] = $this->height;
        $customSvgTagAttributes['viewBox'] = $viewBox;
        $customSvgTagAttributes['class'] = trim(($customSvgTagAttributes['class'] ?? '') . " php-svg-charts");
        $svg = '<svg ' . Renderer::getAttributesString($customSvgTagAttributes, false) . '>';
        if ($this->svgDefs) {
            $svg .= '<defs>' . $this->svgDefs . '</defs>';
        }
        $svg .= $this->renderPipeline->toSvg($this);
        $svg .= $this->renderPipelineAnnotations->toSvg($this);
        $svg .= '</svg>';
        // rendering can modify charts, so make sure to reset to original
        foreach ($original as $key => $value) {
            $this->{$key} = $value;
        }
        return $svg;
    }

}