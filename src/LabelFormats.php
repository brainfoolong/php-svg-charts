<?php

namespace BrainFooLong\SvgCharts;

class LabelFormats
{

    public static function date(string $dateFormat, string $prefix = '', string $suffix = ''): string
    {
        return 'date__' . $dateFormat . '__' . $prefix . "__" . $suffix;
    }

    public static function numberFormat(
        int $decimals = 0,
        ?string $decimal_separator = '.',
        ?string $thousands_separator = ',',
        string $prefix = '',
        string $suffix = '',
    ): string {
        return 'numberFormat__' . $decimals . '__' . $decimal_separator . '__' . $thousands_separator . '__' . $prefix . '__' . $suffix;
    }

    public static function keyValueList(
        array $values,
        string $prefix = '',
        string $suffix = '',
    ): string {
        return 'list__' . base64_encode(serialize($values)) . '__' . $prefix . '__' . $suffix;
    }

    public static function customCallable(
        string $callable,
    ): string {
        return 'customCallable__' . $callable;
    }

    public static function valueToFormat(float $value, ?string $format = null): string
    {
        if (!$format) {
            return (string)$value;
        }
        if (str_starts_with($format, 'date__')) {
            $exp = explode('__', $format);
            return $exp[2] . date($exp[1], $value) . $exp[3];
        }
        if (str_starts_with($format, 'numberFormat__')) {
            $exp = explode('__', $format);
            return $exp[4] . number_format($value, $exp[1], $exp[2], $exp[3]) . $exp[5];
        }
        if (str_starts_with($format, 'customCallable__')) {
            $exp = explode('__', $format);
            return call_user_func($exp[1], $value);
        }
        if (str_starts_with($format, 'list__')) {
            $exp = explode('__', $format);
            $list = unserialize(base64_decode($exp[1]));
            return $exp[2] . ($list[$value] ?? (string)$value) . $exp[3];
        }
        return (string)$value;
    }

}