<?php
require __DIR__ . '/../public/api/normalize.php';
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$resources = [
 '1' => ['type'=>'ZLLPresence','name'=>'Office','uniqueid'=>'aa:bb-02-0406','config'=>['on'=>true,'reachable'=>true,'battery'=>93],'state'=>['presence'=>true,'lastupdated'=>'2026-10-15T12:00:00']],
 '2' => ['type'=>'ZLLTemperature','uniqueid'=>'aa:bb-01-0402','config'=>['on'=>true,'reachable'=>true],'state'=>['temperature'=>-250]],
 '3' => ['type'=>'ZLLTemperature','uniqueid'=>'cc:dd-01-0402','config'=>['on'=>true,'reachable'=>true],'state'=>['temperature'=>2100]],
];
$results = normalizeSensors($resources);
check(count($results) === 1, 'Standalone temperature sensors must not become motion sensors');
check($results[0]['temperature'] === -2.5, 'Pair endpoints by MAC and convert hundredths to Celsius');
check($results[0]['motion'] === true, 'Presence must be represented');
check($results[0]['updated'] === '2026-10-15T12:00:00Z', 'Hue timestamps must be treated as UTC');
$resources['2']['config']['reachable'] = false;
check(normalizeSensors($resources)[0]['temperature'] === null, 'Unreachable temperature must be excluded');
$resources['1']['config']['on'] = false;
check(normalizeSensors($resources) === [], 'Disabled motion sensors must be excluded');
$resources['1']['config']['on'] = true;
$resources['1']['config']['reachable'] = false;
$offline = normalizeSensors($resources);
check(count($offline) === 1 && $offline[0]['reachable'] === false, 'Enabled offline motion sensors must remain visible');
check(normalizeSensors([]) === [], 'Empty bridge must return no sensors');
echo "Passed 8 sensor normalization checks.\n";
