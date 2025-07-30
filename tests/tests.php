<?php

$outputData = [];
require __DIR__ . '/../dev/__generate_docs.php';

$existingFiles = ["index.html" => "index.html"];
$pdfs = scandir(__DIR__ . "/../docs/pdfs");
foreach ($pdfs as $pdf) {
    if (str_ends_with($pdf, ".pdf")) {
        $existingFiles["pdfs/$pdf"] = "pdfs/$pdf";
    }
}
$errors = [];
foreach ($outputData as $file => $data) {
    if (!isset($existingFiles[$file])) {
        $errors[] = $file . " is being generated but not exist in expected stored files";
    } else {
        unset($existingFiles[$file]);
    }
    $originalData = file_get_contents(__DIR__ . "/../docs/$file", $data);
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
