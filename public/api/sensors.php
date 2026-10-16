<?php
declare(strict_types=1);
require __DIR__ . '/normalize.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); header('Allow: GET'); echo json_encode(['error' => 'Method not allowed']); exit;
}
$configPath = dirname(__DIR__, 2) . '/config.local.ini';
$config = is_file($configPath) ? @parse_ini_file($configPath, false, INI_SCANNER_RAW) : [];
if ($config === false) {
    http_response_code(503); echo json_encode(['error' => 'The server configuration could not be read. Check config.local.ini syntax.']); exit;
}
$host = getenv('HUE_BRIDGE_URL') ?: ($config['bridge'] ?? 'http://192.168.1.10');
$user = getenv('HUE_USERNAME') ?: ($config['username'] ?? '');
if (!$user) {
    http_response_code(503); echo json_encode(['error' => 'Bridge credentials are not configured on the PHP server.']); exit;
}
$handle = curl_init(rtrim($host, '/') . '/api/' . rawurlencode($user) . '/sensors');
curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 6, CURLOPT_FOLLOWLOCATION => false]);
$body = curl_exec($handle);
$status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
curl_close($handle);
$data = is_string($body) ? json_decode($body, true) : null;
if ($status !== 200 || !is_array($data) || isset($data[0]['error'])) {
    http_response_code(502); echo json_encode(['error' => 'Cannot read the Hue bridge. Check its connection and API credentials.']); exit;
}
$sensors = normalizeSensors($data);
$values = array_column(array_filter($sensors, fn($s) => $s['reachable'] && $s['enabled'] && $s['temperature'] !== null), 'temperature');
echo json_encode(['sensors' => $sensors, 'average' => count($values) ? round(array_sum($values) / count($values), 2) : null, 'updatedAt' => gmdate('c')], JSON_THROW_ON_ERROR);
