<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Basic column chart example';
Examples::$description = 'Very basic column charts. y-Baseline is set to -110, bellow all given values. So all columns grow upwards.';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("column-chart-basic", 900, 400);
    $charts->createGrid();
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect();
    $valueLabelDrawSettings = new TextRect(
        drawSettings: new DrawSettings(
            fill: 'white',
            fontSize: 11,
        )
    );
    $valueLabelDrawSettings->setBackground([1, 5, 1, 5], '#333', 5);
    $valueLabelDrawSettings->anchorVertical = $valueLabelDrawSettings::ANCHOR_VERTICAL_TOP;
    $chart->addColumnDataSeries(
        $yAxis,
        [
            ['x' => 0, 'values' => 5],
            ['x' => 1, 'values' => 10],
            ['x' => 2, 'values' => 80],
            ['x' => 4, 'values' => 1],
            ['x' => 5, 'values' => -90],
        ],
        0.5,
        10,
        -110,
        valueLabelDrawSettings: $valueLabelDrawSettings
    );
    return $charts;
};