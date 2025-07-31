<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Line charts with multiple lines each having it\'s own y-axis';
Examples::$description = '
Multi line charts, each line with it\'s own y-axis
';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("line-chart-multi-separate", 900, 400, 20, 30, 30, 30);
    $charts->defaultFontDrawSettings->fontFamily = 'trebuchet ms';

    $gradientColor = "green";

    $grid = $charts->createGrid();
    $grid->lines = 10;
    $chart = $charts->createLineAndColumnChart();
    $chart->xAxis->title = "Time";
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect(rotate: -40);
    $chart->xAxis->minValue = "-=200";
    $chart->xAxis->maxValue = "+=200";
    $chart->xAxis->labelFormatter = LabelFormats::date("d.m.Y H:i");
    $yAxis = new YAxis(
        'data',
        '',
        LabelFormats::numberFormat(2),
        drawSettingsTitle: new DrawSettings(fontWeight: 'bold'),
    );
    $yAxis->minValue = "-=10";
    $yAxis->maxValue = "+=20";
    $yAxis->titleDefaults = new TextRect();
    $background = $yAxis->titleDefaults->setBackground(5, 'green', 5);
    $background->drawSettings->fillOpacity = 0.4;
    $now = 1029281711;
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => $now - 3600, 'y' => 10],
            ['x' => $now - 2600, 'y' => 20, 'pointAttributes' => ['title' => 'foobar']],
            ['x' => $now - 1600, 'y' => 30],
            ['x' => $now - 1300, 'y' => 10],
            ['x' => $now - 1200, 'y' => -10],
            ['x' => $now - 350, 'y' => -20],
        ],
        5,
        'curve',
        1,
        new DrawSettings(stroke: 'green', strokeWidth: 5),
        new DrawSettings(fill: 'green'),
        new DrawSettings($gradientColor, 0.5),
    );

    $yAxis = new YAxis('data2', 'BEST', LabelFormats::numberFormat(2));
    $yAxis->titleDefaults = new TextRect();
    $yAxis->position = $yAxis::LABEL_POSITION_RIGHT;
    $background = $yAxis->titleDefaults->setBackground(5, 'blue', 5);
    $background->drawSettings->fillOpacity = 0.4;
    $valueLabelDrawSettings = new TextRect(
        drawSettings: new DrawSettings(
            fill: 'white',
            fontSize: 11,
        )
    );
    $valueLabelDrawSettings->setBackground(5, 'red');
    $valueLabelDrawSettings->anchorHorizontal = $valueLabelDrawSettings::ANCHOR_HORIZONTAL_CENTER;
    $valueLabelDrawSettings->anchorVertical = $valueLabelDrawSettings::ANCHOR_VERTICAL_BOTTOM;
    $valueLabelDrawSettings->anchorVerticalOffset = -7;
    $chart->addLineDataSeries(
        $yAxis,
        [
            ['x' => $now - 4600, 'y' => 30],
            ['x' => $now - 2600, 'y' => 70, 'pointAttributes' => ['title' => 'foobar']],
            ['x' => $now - 1600, 'y' => 80],
            ['x' => $now - 1300, 'y' => 100],
            ['x' => $now - 1200, 'y' => -10],
            ['x' => $now - 100, 'y' => -20],
        ],
        lineDrawSettings: new DrawSettings(stroke: 'blue'),
        valueLabelDrawSettings: $valueLabelDrawSettings
    );
    return $charts;
};