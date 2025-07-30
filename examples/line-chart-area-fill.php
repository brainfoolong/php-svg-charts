<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Line charts with more styling and customization';
Examples::$description = 'This chart show you a little bit more options to style your charts, including grid, gradients and curved lines';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-area-fill", 900, 400, 20, 30, 30, 30);
    $gradientColor = $charts->defineLinearGradient(
        90,
        [0 => '#b70c00', 14 => '#c10020', 28 => '#c6003a', 42 => '#c50055', 57 => '#bd0073', 71 => '#aa0093', 85 => '#8c00b2', 100 => '#5900cd']
    );

    $grid = $charts->createGrid();
    $grid->lines = 10;

    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => 0, 'y' => 5],
            ['x' => 1, 'y' => 10],
            ['x' => 2, 'y' => 80],
            ['x' => 4, 'y' => 1],
            ['x' => 5, 'y' => -90],
        ],
        dataPointSize: 10,
        lineMode: 'curve',
        lineDrawSettings: new DrawSettings(stroke: 'red'),
        dataPointDrawSettings: new DrawSettings(fill: '#ddd', stroke: 'red', strokeWidth: 3),
        areaDrawSettings: new DrawSettings($gradientColor),
    );
    return $charts;
};