<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\Circle;
use BrainFooLong\SvgCharts\Renderer\Line;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Line charts with multiple lines sharing same yaxis';
Examples::$description = '
Also including a grid and smooth curved lines
Each series has different x values so it starts and stops independently
Also each series have different styles so each series can be styled for your needs
Extra annotations on each of the axis are also added
And on top, we a have a few special stylings attached, like gradients
';
Examples::$code = function (): SvgChart {
    $now = 1029281711;

    // setup chart
    $charts = new SvgChart("line-chart-multi", 900, 400, 20, 30, 30, 30);
    $charts->defaultFontDrawSettings->fontFamily = 'trebuchet ms';

    // define a gradient to use as color
    $gradientColor = $charts->defineLinearGradient(
        90,
        [0 => '#b70c00', 14 => '#c10020', 28 => '#c6003a', 42 => '#c50055', 57 => '#bd0073', 71 => '#aa0093', 85 => '#8c00b2', 100 => '#5900cd']
    );

    // setup grid
    $grid = $charts->createGrid();
    $grid->lines = 10;

    // create chart
    $chart = $charts->createLineAndColumnChart();

    // some x annotations
    $annotationX = new TextRect('Annotation X', drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
    $annotationX->setBackground(5, 'blue');
    $annotationX->anchorVertical = $annotationX::ANCHOR_VERTICAL_BOTTOM;
    $chart->xAxis->addLineAnnotation(
        $now - 1220,
        new Line(drawSettings: new DrawSettings(stroke: 'blue', strokeDasharray: '4 2')),
        $annotationX
    );

    // xaxis settings
    $chart->xAxis->title = "Time";
    $chart->xAxis->titleDrawSettings = new TextRect();
    $chart->xAxis->valueLabelDrawSettings = new TextRect(rotate: -40);
    $chart->xAxis->minValue = "-=200";
    $chart->xAxis->maxValue = "+=200";
    $chart->xAxis->labelFormatter = LabelFormats::date("d.m.Y H:i");

    // first yaxis
    $yAxis = new YAxis(
        'data',
        'BEST',
        LabelFormats::numberFormat(2),
        drawSettingsTitle: new DrawSettings(fontWeight: 'bold'),
    );
    $yAxis->minValue = "-=10";
    $yAxis->maxValue = "+=20";
    $yAxis->titleDefaults = new TextRect();

    // some y axis annotations
    $annotationY = new TextRect('Annotation Y', drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
    $annotationY->setBackground(5, 'green');
    $annotationY->anchorVertical = $annotationX::ANCHOR_VERTICAL_BOTTOM;
    $annotationY->anchorHorizontal = $annotationX::ANCHOR_HORIZONTAL_RIGHT;
    $yAxis->addLineAnnotation(
        50,
        new Line(drawSettings: new DrawSettings(stroke: 'green', strokeDasharray: '4 2')),
        $annotationY
    );
    $annotationPointY = new TextRect('Annotation Point', drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
    $annotationPointY->setBackground(5, 'brown');
    $annotationPointY->anchorVertical = $annotationX::ANCHOR_VERTICAL_TOP;
    $annotationPointY->anchorHorizontal = $annotationX::ANCHOR_HORIZONTAL_LEFT;
    $yAxis->addPointAnnotation(
        $now - 2220,
        70,
        new Circle(r: 5, drawSettings: new DrawSettings(fill: 'brown')),
        $annotationPointY
    );

    $background = $yAxis->titleDefaults->setBackground(5, 'green', 5);
    $background->drawSettings->fillOpacity = 0.4;
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
    $yAxisBase = $yAxis;

    // second yaxis
    $yAxis = new YAxis('data2', 'BEST', LabelFormats::numberFormat(2));
    $yAxis->titleDefaults = new TextRect();
    $yAxis->position = 'right';
    $background = $yAxis->titleDefaults->setBackground();
    $background->borderRadius = 5;
    $background->drawSettings = new DrawSettings('blue', fillOpacity: 0.4);
    $valueLabelDrawSettings = new TextRect(drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
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

    // combine yaxis into one
    $chart->combineYAxis = [$yAxisBase, $yAxis];
    return $charts;
};