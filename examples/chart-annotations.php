<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\LabelFormats;
use BrainFooLong\SvgCharts\Renderer\Circle;
use BrainFooLong\SvgCharts\Renderer\Line;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\Renderer\YAxis;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'Annotations and Fonts';
Examples::$description = '
You can add several annotation types to x and y axis
Also there are most websafe fonts built in
You can use any font you want, but only the websafe ones correctly calculates text dimensions (required to properly place labels)
';
Examples::$code = function (): SvgChart {
    // setup chart
    $charts = new SvgChart("chart-annotations", 900, 400);
    $charts->createGrid();
    $yAxis = new YAxis('data', 'Awesome lines', LabelFormats::numberFormat(2));
    $chart = $charts->createLineAndColumnChart();
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
    $annotationY = new TextRect('Y Line Annotation', drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
    $annotationY->setBackground(5, 'blue');
    $annotationY->anchorVertical = $annotationY::ANCHOR_VERTICAL_BOTTOM;
    $yAxis->addLineAnnotation(
        50,
        new Line(drawSettings: new DrawSettings(stroke: 'blue', strokeDasharray: '4 2')),
        $annotationY
    );

    // some x annotations
    $annotationX = new TextRect('X Line Annotation', drawSettings: new DrawSettings(fill: 'white', fontSize: 11));
    $annotationX->setBackground(5, 'blue');
    $annotationX->anchorVertical = $annotationX::ANCHOR_VERTICAL_BOTTOM;
    $chart->xAxis->addLineAnnotation(
        3,
        new Line(drawSettings: new DrawSettings(stroke: 'blue', strokeDasharray: '4 2')),
        $annotationX
    );

    $fonts = ['arial', 'verdana', 'tahoma', 'trebuchet ms', 'times new roman', 'georgia', 'garamond', 'courier new'];
    $y = 80;
    $x = 0;
    foreach ($fonts as $font) {
        $annotationPointY = new TextRect("Annotation Point\n" . $font, drawSettings: new DrawSettings(fill: 'white', fontFamily: $font, fontSize: 11));
        $annotationPointY->setBackground(5, 'brown');
        $annotationPointY->anchorVertical = $annotationX::ANCHOR_VERTICAL_TOP;
        $annotationPointY->anchorHorizontal = $annotationX::ANCHOR_HORIZONTAL_LEFT;
        $yAxis->addPointAnnotation(
            $x,
            $y,
            new Circle(r: 5, drawSettings: new DrawSettings(fill: 'brown')),
            $annotationPointY
        );
        $y -= 20;
        $x += 0.5;
    }

    return $charts;
};