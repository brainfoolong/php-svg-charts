<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Line charts with many data points and auto rotation/skip';
Examples::$description = 'This chart show how the x-axis auto fit itself so nothing overlaps';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-many", 900, 400, 20, 30, 30, 30);

    $grid = $charts->createGrid();
    $grid->lines = 10;

    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->title = "Time";
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect(rotate: -40);
    $chart->xAxis->minValue = "-=200";
    $chart->xAxis->maxValue = "+=200";
    $chart->xAxis->labelFormatter = LabelFormats::date("d.m.Y H:i");
    //    $chart->xAxis->autoRotate = false;
    $yAxis = new YAxis(
        'data',
        'BEST',
        LabelFormats::numberFormat(2),
        drawSettingsTitle: new DrawSettings(fontWeight: 'bold'),
    );
    $yAxis->minValue = "-=10";
    $yAxis->maxValue = "+=20";
    $yAxis->titleDefaults = new TextRect();
    $background = $yAxis->titleDefaults->setBackground(5, 'green', 5);
    $background->drawSettings->fillOpacity = 0.4;
    $now = 1029281711;
    $data = [];
    for ($i = 0; $i < 255; $i++) {
        $data[] = ['x' => $now + ($i * 1000), 'y' => sin($i) * 100];
    }
    $chart->addLineDataSeries(
        $yAxis,
        $data,
        5,
        'straight',
        1,
        new DrawSettings(stroke: 'green', strokeWidth: 5),
        new DrawSettings(fill: 'green')
    );

    return $charts;
};