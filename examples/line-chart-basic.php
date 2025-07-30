<?php

use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'The most basic line chart example';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-basic", 900, 400);
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->title = "Time";
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect();
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => 0, 'y' => 5],
            ['x' => 1, 'y' => 10],
            ['x' => 2, 'y' => 80],
            ['x' => 4, 'y' => 1],
            ['x' => 5, 'y' => -90],
        ],
    );
    return $charts;
};