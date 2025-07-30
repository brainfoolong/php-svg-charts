<?php

$outputData = [];
require __DIR__ . '/../dev/__generate_docs.php';

$existingFiles = [];
$svgs = scandir(__DIR__ . "/../docs/svgs");
foreach ($svgs as $pdf) {
    if (str_ends_with($pdf, ".svg")) {
        $existingFiles["svgs/$pdf"] = "svgs/$pdf";
    }
}
$errors = [];
unset($outputData['index.html']);
foreach ($outputData as $file => $data) {
    if (!str_ends_with($file, ".svg")) {
        continue;
    }
    file_put_contents(__DIR__ . "/generated/$file", $data);
    if (!isset($existingFiles[$file])) {
        $errors[] = $file . " is being generated but not exist in expected stored files";
    } else {
        unset($existingFiles[$file]);
    }
    $originalData = file_get_contents(__DIR__ . "/../docs/$file");
    $originalData = str_replace(["\r", "\n", " "], "", $originalData);
    $data = str_replace(["\r", "\n", " "], "", $data);
    if ($originalData !== $data) {
        $errors[] = $file . " not generate not the same expected contents as stored in the repository";
    }
}
foreach ($existingFiles as $file) {
    $errors[] = $file . " is required to be generated but has not been generated";
}
if ($errors) {
    echo implode("\n", $errors);
    exit(1);
} else {
    echo "Success";
}
