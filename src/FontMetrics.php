<?php

namespace BrainFooLong\SvgCharts;

use BrainFooLong\SvgCharts\FontMetrics\FontMetricArialNormal;

class FontMetrics
{

    /**
     * Get rough width/height text dimensions of given string
     * @return array{width: float, height: float}
     */
    public static function getTextDimensions(
        DrawSettings $drawSettings,
        string $string,
        ?float $angle = null,
    ): array {
        $fontData = self::getFontData($drawSettings);
        $lines = explode("\n", $string);
        $height = 0;
        $maxWidth = 0;
        // the font metrics are calculated with 14px font size
        $fontSizeMulti = 1 / 14 * $drawSettings->fontSize;
        foreach ($lines as $line) {
            $chars = mb_str_split($line);
            // add linespacing if we already have a line before
            $height += $fontData['lineHeight'];
            $width = 0;
            foreach ($chars as $char) {
                if (!isset($fontData[$char])) {
                    $width += $fontData['a'];
                } else {
                    $width += $fontData[$char];
                }
            }
            if ($width > $maxWidth) {
                $maxWidth = $width;
            }
        }
        $width = $maxWidth;
        if ($angle) {
            [$width, $height] = SvgChart::rotate($angle, $width, $height);
        }
        return ["width" => round($width * $fontSizeMulti, 2), "height" => round($height * $fontSizeMulti, 2)];
    }

    public static function getLineHeight(DrawSettings $drawSettings): float
    {
        return self::getFontData($drawSettings)['lineHeight'] * (1 / 14 * $drawSettings->fontSize);
    }

    public static function getFontData(DrawSettings $drawSettings): array
    {
        $fontName = preg_replace(
            "~[^0-9a-z]~i",
            '',
            ucwords(strtolower($drawSettings->fontFamily)) . ucfirst(strtolower($drawSettings->fontWeight ?? 'normal'))
        );
        $className = "\\BrainFooLong\\SvgCharts\\FontMetrics\\FontMetric" . $fontName;
        if (!class_exists($className)) {
            $className = FontMetricArialNormal::class;
        }
        return $className::$data;
    }

}