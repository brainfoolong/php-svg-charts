<?php

namespace BrainFooLong\SvgCharts;

use Exception;

abstract class Renderer
{

    public string $id;

    /**
     * Key/value pair of all additional element attributes to customize the behaviour
     * @var string[]
     */
    public array $additionalAttributes = [];

    /**
     * Some custom css classes if required
     * @var string[]
     */
    public array $additionalCssClasses = [];

    /**
     * SVG transformations (attribute transform)
     * @var string[]|null
     */
    public ?array $transforms = null;

    /**
     * Set the priority of when this renderer should call toSvg()
     * The output is still in the order of the render pipeline, but toSvg() with higher processPriority will be called before lower priorities
     * This is used for example to process the axis before the grid, so the grid can be properly scaled according to the axis
     * @var int
     */
    public int $processPriority = 0;

    public static function getAttributesString(array $attributes, bool $camelCaseToDashedCase = true): string
    {
        $out = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === "" || $value === false) {
                continue;
            }
            if (is_array($value)) {
                throw new Exception('Invalid attribute value, only scalar values allowed');
            }
            if ($camelCaseToDashedCase) {
                $key = strtolower(preg_replace("~([A-Z])~", "-$1", $key));
            }
            $out[$key] = "$key=\"" . htmlspecialchars($value, ENT_QUOTES) . "\"";
        }
        return implode(" ", $out);
    }

    public function getAllAttributes(SvgChart $chart): array
    {
        $out = [];
        if ($this->additionalAttributes) {
            foreach ($this->additionalAttributes as $key => $value) {
                $out[$key] = $value;
            }
        }
        if ($this->additionalCssClasses) {
            $out['class'] = array_unique(array_filter(explode(" ", implode(" ", $this->additionalCssClasses))));
        }
        if ($this->transforms) {
            $data = [];
            foreach ($this->transforms as $type => $value) {
                $data[] = $type . "(" . $value . ")";
            }
            $out['transform'] = implode(", ", $data);
        }
        $out['data-renderer-id'] = $this->id;
        return $out;
    }

    abstract public function toSvg(SvgChart $chart): string;

}