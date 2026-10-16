<?php
declare(strict_types=1);
function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
ob_start();
require __DIR__ . '/../public/index.php';
$html = ob_get_clean();
check(str_contains($html, '<title>Home Temperature</title>'), 'Root page must render the Home dashboard');
check(str_contains($html, 'Your sensors'), 'Sensor section must be present');
check(!str_contains($html, 'Offline / disabled') && !str_contains($html, 'value="active"'), 'Removed filter options must remain absent');
preg_match_all('/\bid="([^"]+)"/', $html, $matches);
check(count($matches[1]) === count(array_unique($matches[1])), 'HTML IDs must be unique');
$javascript = file_get_contents(__DIR__ . '/../public/assets/dashboard.js');
preg_match_all('/\$\(\x27([^\x27]+)\x27\)/', $javascript, $references);
foreach (array_unique($references[1]) as $id) {
    check(in_array($id, $matches[1], true), "JavaScript element $id must exist in the PHP page");
}
check(str_contains($javascript, "fetch('/api/sensors.php'"), 'Browser must call the public PHP API directly');
check(!preg_match('/(?:src|href)="https?:\/\//', $html), 'Frontend must not depend on external assets');
check(!is_file(__DIR__ . '/../public/config.local.ini'), 'Private config must remain outside the document root');
echo "Passed frontend page, asset, DOM reference and routing checks.\n";
