<?php

use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'The most basic line chart example with legends';
Examples::$description = 'This is a very basic example showing some line charts. You have many more options to use it.';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-basic", 900, 400);
    $charts->createGrid();
    $legend = $charts->createLegend();
    $legend->backgroundColor = '#e5e5e5';
    // use it to change position
    // $legend->position = $legend::POSITION_RIGHT;
    // use this to auto expand chart size depending on legend size
    // $legend->heightCalculation = $legend::HEIGHTCALC_EXPAND;
    $legend->addLabel('red', 'Custom Legend 1');
    $legend->addLabel('blue', 'Custom Legend 2');
    $legend->addLabel('green', 'Custom Legend 3');
    $legend->addLabel('yellow', 'Custom Legend 4');
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