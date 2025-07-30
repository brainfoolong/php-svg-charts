<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Stacked columns';
Examples::$description = '
Multiple values stacked in one column
y-Baseline is set to zero (default), so values bellow zero go down
Value labels are added as well as a summary label for each column
';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("column-chart-stacked", 900, 400);
    $charts->createGrid();
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $yAxis->minValue = "-=20";
    $yAxis->maxValue = "+=20";
    $yAxis->labelFormatter = LabelFormats::numberFormat(1, suffix: "%");
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

    $summaryLabelDrawSettings = new TextRect(
        drawSettings: new DrawSettings(
            fill: 'white',
            fontSize: 15,
        )
    );
    $summaryLabelDrawSettings->setBackground([1, 5, 1, 5], '#666', 5);
    $summaryLabelDrawSettings->anchorVerticalOffset = 5;

    $chart->addColumnDataSeries(
        $yAxis,
        [
            ['x' => 0, 'values' => [5, 30, 20]],
            ['x' => 1, 'values' => [1, 20, 66]],
            ['x' => 2, 'values' => [80, 20, 33]],
            ['x' => 4, 'values' => [90, 120, 15]],
            ['x' => 5, 'values' => [20, 22, 10]],
            ['x' => 6, 'values' => [-15, -30, -20]],
        ],
        valueLabelDrawSettings: $valueLabelDrawSettings,
        summaryLabelDrawSettings: $summaryLabelDrawSettings
    );
    return $charts;
};