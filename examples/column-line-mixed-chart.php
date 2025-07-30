<?php

use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Mixed column and line charts';
Examples::$description = '
You can freely mix any line and column chart together
';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("column-line-mixed-chart", 900, 400);
    $charts->createGrid();
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect();
    $chart->xAxis->labelFormatter = LabelFormats::keyValueList([1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June']);

    $yAxis2 = new YAxis('data2', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart->addColumnDataSeries(
        $yAxis2,
        [
            ['x' => 1, 'values' => [5, 30, 20]],
            ['x' => 2, 'values' => [1, 20, 66]],
            ['x' => 3, 'values' => [80, 10, 33]],
            ['x' => 4, 'values' => [90, 120, 5]],
            ['x' => 5, 'values' => [20, 6, 10]],
            ['x' => 6, 'values' => [-5, -30, -20]],
        ],
    );

    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart->xAxis->title = "Time";
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => 1, 'y' => 5],
            ['x' => 2, 'y' => 10],
            ['x' => 3, 'y' => 80],
            ['x' => 4, 'y' => 1],
            ['x' => 5, 'y' => 10],
            ['x' => 6, 'y' => -20],
        ],
    );
    $chart->combineYAxis = [$yAxis, $yAxis2];
    return $charts;
};