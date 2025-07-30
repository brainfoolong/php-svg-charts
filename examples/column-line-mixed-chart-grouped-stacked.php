<?php

use BrainFooLong\SvgCharts\ColumnDataPointGrouped;
use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Column and line charts with grouped and stacked data combined';
Examples::$description = '
You can group and stack at the same time, as well as adding line charts on top
';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("column-line-mixed-chart-grouped-stacked", 900, 400);
    $charts->createGrid();
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect();
    $chart->addColumnDataSeries(
        $yAxis,
        [
            new ColumnDataPointGrouped(
                0, [['values' => [5, 30, 20]], ['values' => [5, 30, 20]], ['values' => [5, 30, 20]]]
            ),
            new ColumnDataPointGrouped(
                1, [['values' => [1, 20, 66]], ['values' => [1, 20, 66]], ['values' => [1, 20, 66]]]
            ),
            new ColumnDataPointGrouped(
                2, [['values' => [80, 10, 33]], ['values' => [80, 10, 33]], ['values' => [80, 10, 33]]]
            ),
            new ColumnDataPointGrouped(
                3, [['values' => [90, 120, 5]], ['values' => [90, 120, 5]], ['values' => [90, 120, 5]]]
            ),
            new ColumnDataPointGrouped(
                4, [['values' => [20, 6, 10]], ['values' => [20, 6, 10]], ['values' => [20, 6, 10]]]
            ),
        ],
    );
    $yAxis2 = new YAxis('data2', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart->xAxis->title = "Time";
    $chart->addLineDataSeries(
        $yAxis2,
        [
            ['x' => 0, 'y' => 5],
            ['x' => 1, 'y' => 10],
            ['x' => 2, 'y' => 80],
            ['x' => 3, 'y' => 1],
            ['x' => 4, 'y' => 10],
        ],
        lineDrawSettings: new DrawSettings(stroke: '#333', strokeWidth: 3)
    );
    $chart->combineYAxis = [$yAxis, $yAxis2];
    return $charts;
};