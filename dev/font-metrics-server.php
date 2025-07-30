<?php

if ($_SERVER["REQUEST_URI"] === "/") {
    echo file_get_contents(__DIR__ . "/font-metrics.html");
}
if ($_SERVER["REQUEST_URI"] === "/save") {
    $body = json_decode(file_get_contents("php://input"), true);
    file_put_contents(__DIR__."/../src/FontMetrics/".$body['className'].".php", $body['contents']);
}