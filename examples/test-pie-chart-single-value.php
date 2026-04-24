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
            ['value' => 0, 'color' => 'red', "explodeDistance" => 0, 'label' => '0%'],
            ['value' => 0, 'color' => 'green', "explodeDistance" => 0, 'label' => '0%'],
            ['value' => 100, 'color' => 'blue', "explodeDistance" => 0, 'label' => '100%'],
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