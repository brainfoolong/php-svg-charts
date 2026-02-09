<?php

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);
ini_set("date.timezone", "UTC");

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
    'column-line-mixed-chart-grouped-stacked',
    'pie-chart-basic',
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
    $class = str_replace("\\", "/", $class);
    $className = trim(substr($class, 22), "\\/");
    $file = __DIR__ . "/../src/" . $className . ".php";
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SVG Charts - PHP Library for Dynamic Chart Generation</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            :root {
                --primary-bg: #0d1117;
                --secondary-bg: #161b22;
                --tertiary-bg: #21262d;
                --accent-blue: #0d47a1;
                --text-primary: #e6edf3;
                --text-secondary: #8b949e;
                --border-color: #30363d;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background-color: var(--primary-bg);
                color: var(--text-primary);
                line-height: 1.6;
                overflow-x: hidden;
            }

            /* Animation for fade-in */
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            /* Header Styles */
            header {
                background: linear-gradient(135deg, var(--primary-bg) 0%, rgba(13, 71, 161, 0.1) 100%);
                border-bottom: 1px solid var(--border-color);
                padding: 2rem 1rem;
                animation: fadeInDown 0.8s ease-out;
            }

            .header-container {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 2rem;
            }

            .logo {
                font-size: 2rem;
                font-weight: 700;
                background: linear-gradient(135deg, #0d47a1, #5c9cff);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .logo svg {
                width: 40px;
                height: 40px;
            }

            .header-content {
                flex: 1;
            }

            .header-content h1 {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
                color: var(--text-primary);
            }

            .header-content p {
                color: var(--text-secondary);
                font-size: 1rem;
            }

            /* Main Content */
            main {
                max-width: 1200px;
                margin: 0 auto;
                padding: 4rem 1rem;
            }

            .intro-section {
                text-align: center;
                margin-bottom: 4rem;
                animation: fadeInUp 0.8s ease-out 0.2s both;
            }

            .intro-section h2 {
                font-size: 2.5rem;
                margin-bottom: 1rem;
                background: linear-gradient(135deg, var(--text-primary) 0%, #5c9cff 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .intro-section p {
                color: var(--text-secondary);
                font-size: 1.1rem;
                max-width: 600px;
                margin: 0 auto;
            }

            /* Demo Grid */
            .demo-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
                gap: 2rem;
                margin-top: 3rem;
            }

            .demo-box {
                background: linear-gradient(135deg, var(--secondary-bg) 0%, rgba(13, 71, 161, 0.15) 100%);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.3s ease;
                animation: fadeInUp 0.8s ease-out both;
                display: flex;
                flex-direction: column;
            }

            /* Stagger animation for demo boxes */
            .demo-box:nth-child(1) { animation-delay: 0.3s; }
            .demo-box:nth-child(2) { animation-delay: 0.4s; }
            .demo-box:nth-child(3) { animation-delay: 0.5s; }
            .demo-box:nth-child(4) { animation-delay: 0.6s; }
            .demo-box:nth-child(5) { animation-delay: 0.7s; }
            .demo-box:nth-child(6) { animation-delay: 0.8s; }

            .demo-box:hover {
                border-color: var(--accent-blue);
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(13, 71, 161, 0.2);
            }

            .demo-image {
                width: 100%;
                height: 250px;
                background: linear-gradient(135deg, rgba(13, 71, 161, 0.3) 0%, rgba(92, 156, 255, 0.2) 100%) no-repeat center center;
                background-size: contain;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            .demo-content {
                padding: 2rem;
                gap: 10px;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .demo-box h3 {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
                color: var(--text-primary);
            }

            .demo-box p {
                color: var(--text-secondary);
                font-size: 0.95rem;
                flex: 1;
                margin-bottom: 0;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .header-container {
                    flex-direction: column;
                    text-align: center;
                    gap: 1rem;
                }

                .header-content h1 {
                    font-size: 1.4rem;
                }

                .header-content p {
                    font-size: 0.9rem;
                }

                .intro-section h2 {
                    font-size: 1.8rem;
                }

                .intro-section p {
                    font-size: 1rem;
                }

                .demo-grid {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }

                main {
                    padding: 2rem 1rem;
                }
            }

            @media (max-width: 480px) {
                header {
                    padding: 1.5rem 1rem;
                }

                .logo {
                    font-size: 1.5rem;
                }

                .intro-section h2 {
                    font-size: 1.4rem;
                }

                .intro-section p {
                    font-size: 0.95rem;
                }

                .demo-content {
                    padding: 1.5rem;
                }

                .demo-box h3 {
                    font-size: 1.2rem;
                }

                .demo-box p {
                    font-size: 0.85rem;
                }
            }


            a, a:any-link {
                color: #538add;
            }
            button {
                background: #0d47a1;
                color: white;
                font-weight: bold;
                border: 0;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                transition: .2s;
            }
            button:hover {
                background: #225ab1;
            }
            .hidden {
                display: none;
            }
            .code {
                font-family: monospace;
                font-size: 12px;
                line-height: 1.4;
                white-space: pre;
                overflow: auto;
                width: 100%;
            }
        </style>
    </head>
    <body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">
                PHP SVG Charts
            </div>
        </div>
    </header>

    <main>
        <section class="intro-section">
            <h2>Powerful Chart Generation with PHP</h2>
            <p>

                Simple. MIT Open-Source licensed. Generate SVG image charts in your backend, no javascript or browser needed.
                The main reason this library was built is to generate PDFs with charts in PHP only.
                <br/><br/>
                <a href="https://github.com/brainfoolong/php-svg-charts" target="_blank">More and download on GitHub</a>
            </p>
        </section>

        <section class="demo-grid">

            <?php
            $examples = [];
            $pdfs = [];
            $svgs = [];

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

                $output = call_user_func(Examples::$code)->toSvg(['id' => "php-charts-" . $file]);
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
                $svgs[$file] = $output;

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
                <div class="demo-box" data-file="<?= $file ?>">
                    <div class="demo-image" style="background-image:url('data:image/svg+xml;base64,<?= base64_encode($example['output']) ?>')">
                    </div>
                    <div class="demo-content">
                        <h3><?= $example['title'] ?></h3>
                        <p>
                            <?
                            if ($desc = $example['description']) {
                                echo $desc;
                            }
                            ?>
                        </p>
                        <button onclick="this.nextElementSibling.classList.toggle('hidden')">Show Code</button>
                        <div class="code hidden"><?= $example['code'] ?></div>
                    </div>
                </div>
                <?php
            }
            ?>

        </section>
    </main>
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
foreach ($svgs as $file => $data) {
    $outputData["svgs/" . $file . ".svg"] = $data;
}
if (PHP_SAPI !== 'cli') {
    echo $html;
    if (isset($_GET['saveDocs'])) {
        foreach ($outputData as $file => $data) {
            file_put_contents(__DIR__ . "/../docs/$file", $data);
        }
    }
}
