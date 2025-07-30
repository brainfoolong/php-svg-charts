<?php

use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Basic line charts with custom labels and step lines';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-custom-labels", 900, 400);
    $charts->createGrid();
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->title = "Time";
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect();
    $chart->xAxis->labelFormatter = LabelFormats::keyValueList(
        [0 => 'Foo', 1 => 'Bar', 2 => 'Cool', 4 => 'Isn\'t', 5 => 'It'],
    );
    $yAxis->labelFormatter = LabelFormats::keyValueList(
        [5 => 'Five', 10 => 'Ten', 80 => 'Eighty', 1 => 'One', -90 => 'Minus Ninety'],
    );
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => 0, 'y' => 5],
            ['x' => 1, 'y' => 10],
            ['x' => 2, 'y' => 80],
            ['x' => 4, 'y' => 1],
            ['x' => 5, 'y' => -90],
        ],
        0,
        lineMode: 'step'
    );
    return $charts;
};