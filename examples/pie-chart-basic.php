<?php

use BrainFooLong\SvgCharts\DrawSettings;
use BrainFooLong\SvgCharts\Renderer\TextRect;
use BrainFooLong\SvgCharts\SvgChart;

Examples::$title = 'The most basic pie chart example';
Examples::$code = function (): SvgChart {
    $charts = new SvgChart("pie-chart-basic", 700, 400);
    $chart = $charts->createPieChart();
    $labelDrawSettings = new TextRect(
        anchorHorizontal: TextRect::ANCHOR_HORIZONTAL_CENTER,
        anchorVertical: TextRect::ANCHOR_VERTICAL_MIDDLE,
        drawSettings: $charts->defaultFontDrawSettings
    );
    $labelDrawSettings->setBackground();
    $chart->addDataSeries(
        [
            ['value' => 100, 'color' => 'red', "explodeDistance" => 20, 'label' => '20%'],
            ['value' => 200, 'color' => 'blue', 'label' => '30%'],
            ['value' => 300, 'color' => 'yellow', 'label' => '50%'],
        ],
        $chart::POSITION_RIGHT,
        $chart::LABELPOS_OUTSIDE,
        0.3,
        new DrawSettings(stroke: 'white', strokeWidth: 2),
        $labelDrawSettings

    );
    $legend = $charts->createLegend();
    $legend->position = $legend::POSITION_LEFT;
    $legend->addLabel('red', 'Tongue');
    $legend->addLabel('blue', 'Jaw');
    $legend->addLabel('yellow', 'Head');
    return $charts;
};