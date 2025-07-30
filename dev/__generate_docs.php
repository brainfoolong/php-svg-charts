<?php

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);

$files = [
    'line-chart-basic',
    'line-chart-custom-labels',
    'line-chart-multi',
    'line-chart-multi-separate',
    'line-chart-many',
    'chart-annotations',
    'column-chart-basic',
    'column-chart-stacked',
    'column-line-mixed-chart',
    'column-line-mixed-chart-grouped-stacked'
];

$ini = [
    'highlight.bg' => '',
    'highlight.comment' => '#ff0050',
    'highlight.default' => 'white',
    'highlight.html' => 'orange',
    'highlight.keyword' => '#8ad3ff',
    'highlight.string' => '#00ffa3'
];
foreach ($ini as $key => $value) {
    ini_set($key, $value);
}

spl_autoload_register(function ($class) {
    $className = substr($class, 22);
    $file = __DIR__ . "/../src/" . $className . ".php";
    var_dump($file);
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

register_shutdown_function(function () {
    if (error_get_last()) {
        exit(1);
    }
});

require __DIR__ . "/vendor/autoload.php";

class Examples
{

    public static string $title;

    public static string $description = '';

    public static Closure $code;

}

ob_start();
echo '<!DOCTYPE html>';
?>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>PHP SVG Charts - Documentation</title>
        <style>
            :root {
                --gradientBorder-gradient: linear-gradient(180deg, #93335b00, #93335bbd, #93335b17), linear-gradient(15deg, #93335b1f 50%, #93335bbd);
                --start: rgba(0, 0, 0, .93);
            }
            body {
                font-family: monospace;
                background: #0d0d17;
                color: white;
                font-size: 16px;
                text-align: center;
                padding: 20px;
                box-sizing: border-box;
            }
            .svg {
                white-space: pre;
                background: rgba(0, 0, 0, 0.5);
                border-radius: 5px;
                margin-top: 20px;
            }
            .svg svg {
                width: 100%;
            }
            h2 {
                margin: 0;
                padding: 0 0 5px;
            }
            .examples {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(min(calc(100vw - 80px), 800px), auto)); /* use bigger than 33% to have max 2 columns */
                column-gap: 10px;
                row-gap: 10px;
            }
            .example {
                box-sizing: border-box;
                padding: 20px;
                position: relative;
            }
            .desc {
                padding: 2px;
            }
            .example:before {
                content: "";
                pointer-events: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                position: absolute;
                inset: 0;
                padding: 2px;
                border-radius: 5px;
                background: var(--gradientBorder-gradient);
                mask: linear-gradient(var(--start), #000) content-box, linear-gradient(var(--start), #000);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
            }
            a, a:any-link {
                color: #ff002f;
            }
            button {
                background: #c3233c;
                color: white;
                font-weight: bold;
                border: 0;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                transition: .2s;
            }
            button:hover {
                background: #df3c56;
            }
            .tabs {
                display: flex;
                gap: 10px;
                margin: 10px 0;
            }
            .tabs button {
                flex: 1 1 auto;
            }
            .code {
                font-family: monospace;
                font-size: 0.9rem;
                padding: 10px;
                white-space: pre;
                overflow: auto;
                max-width: 100%;
                text-align: left;
                background: #0d2835;
                border-radius: 10px;
            }
            .code pre {
                padding: 0;
                margin: 0;
            }
            .hidden {
                display: none !important;
            }
        </style>
    </head>
    <body>
    <h1>
        PHP SVG Charts
    </h1>
    <a href="https://github.com/brainfoolong/php-svg-charts" target="_blank">Download and more on Github</a>
    <p>
        Simple. MIT Open-Source licensed. Generate SVG image charts in your backend, no javascript or browser needed.<br/>
        The main reason this library was built is to generate PDFs with charts in PHP only.
    </p>
    <br/>
    <div class="examples">
        <?php
        $examples = [];
        $pdfs = [];

        foreach ($files as $file) {
            if (isset($_GET['single']) && $_GET['single'] !== $file) {
                continue;
            }
            Examples::$title = '';
            Examples::$description = '';
            Examples::$code = function () {};
            $path = __DIR__ . "/../examples/" . $file . ".php";
            require $path;
            $method = new ReflectionFunction(Examples::$code);
            $lines = file($path);
            $code = [];
            foreach ($lines as $line) {
                if (str_starts_with($line, 'use ')) {
                    $code[] = trim($line);
                }
            }
            $code[] = "";
            for ($i = $method->getStartLine(); $i < $method->getEndLine() - 2; $i++) {
                $code[] = rtrim(mb_substr($lines[$i], 4));
            }
            $code = highlight_string("<?php\n" . trim(implode("\n", $code), "\n\r {}"), true);

            $output = $method->invoke(null)->toSvg(['id' => "php-charts-" . $file]);
            $output = trim($output);
            $mpdf = new Mpdf();
            $mpdf->WriteHTML(
                '
                    <h2>PDF Example ' . Examples::$title . '</h2>
                    ' . $output . '
                '
            );
            $pdfOut = $mpdf->Output($file, Destination::STRING_RETURN);
            $pdfOut = preg_replace("~^(/CreationDate|/ModDate) .*~m", "$1 (D:20250729134318+02'00')", $pdfOut);
            $pdfOut = preg_replace("~^(/ID) .*~m", "/ID [<phpsvgcharts><phpsvgcharts>]", $pdfOut);
            $pdfs[$file] = $pdfOut;

            $examples[$file] = [
                'title' => Examples::$title,
                'description' => trim(Examples::$description),
                'code' => $code,
                'output' => $output,
            ];
        }
        ksort($examples, SORT_NUMERIC);
        foreach ($examples as $example) {
            ?>
            <div class="example" data-file="<?= $file ?>">
                <h2><?= $example['title'] ?></h2>
                <?php
                if ($desc = $example['description']) {
                    ?>
                    <div class="desc">
                        <?= nl2br($desc) ?>
                    </div>
                    <?php
                }
                ?>
                <div class="tabs">
                    <button onclick="this.parentNode.nextElementSibling.classList.toggle('hidden')">Show Code</button>
                </div>
                <div class="code hidden"><?= $example['code'] ?></div>
                <div class="svg"><?= $example['output'] ?></div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="tooltip">

    </div>
    </body>
    </html>
<?php

$html = ob_get_contents();
ob_end_clean();
$outputData = [];
$outputData['index.html'] = $html;
foreach ($pdfs as $file => $data) {
    $outputData["pdfs/" . $file . ".pdf"] = $data;
}
if (PHP_SAPI !== 'cli') {
    echo $html;
    if (isset($_GET['saveDocs'])) {
        foreach ($outputData as $file => $data) {
            file_put_contents(__DIR__ . "/../docs/$file", $data);
        }
    }
}
