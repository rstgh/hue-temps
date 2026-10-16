<?php
declare(strict_types=1);
// Run isolated requests without contacting the real bridge or loading cURL.
function request(string $method, mixed $body, int $status = 200): array {
    $endpoint = dirname(__DIR__) . '/public/api/sensors.php';
    $code = '<?php ' .
        'putenv("HUE_USERNAME=test-placeholder"); putenv("HUE_BRIDGE_URL=http://bridge.test");' .
        '$_SERVER["REQUEST_METHOD"]=' . var_export($method, true) . ';' .
        'define("CURLOPT_RETURNTRANSFER",1); define("CURLOPT_CONNECTTIMEOUT",2); define("CURLOPT_TIMEOUT",3); define("CURLOPT_FOLLOWLOCATION",4); define("CURLINFO_HTTP_CODE",5);' .
        'function curl_init($url){return true;} function curl_setopt_array($h,$options){} function curl_close($h){}' .
        'function curl_exec($h){return ' . var_export(json_encode($body), true) . ';}' .
        'function curl_getinfo($h,$option){return ' . $status . ';}' .
        'register_shutdown_function(function(){fwrite(STDERR,(string)(http_response_code() ?: 200));});' .
        'require ' . var_export($endpoint, true) . ';';
    $process = proc_open([PHP_BINARY, '-n'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Could not start isolated API request');
    fwrite($pipes[0], $code); fclose($pipes[0]);
    $response = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $responseStatus = stream_get_contents($pipes[2]); fclose($pipes[2]);
    if (proc_close($process) !== 0) throw new RuntimeException('API process failed: ' . $responseStatus);
    return [(int)$responseStatus, json_decode($response, true, 512, JSON_THROW_ON_ERROR)];
}
function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$resources = [
    '1' => ['type'=>'ZLLPresence','name'=>'Office <test>','uniqueid'=>'aa:bb-02-0406','config'=>['on'=>true,'reachable'=>true],'state'=>['presence'=>true]],
    '2' => ['type'=>'ZLLTemperature','uniqueid'=>'aa:bb-01-0402','config'=>['on'=>true,'reachable'=>true],'state'=>['temperature'=>2150]],
    '3' => ['type'=>'ZLLLightLevel','uniqueid'=>'aa:bb-01-0400','state'=>['lightlevel'=>20001]],
    '4' => ['type'=>'ZLLPresence','uniqueid'=>'cc:dd-02-0406','config'=>['on'=>false,'reachable'=>true]],
];
[$status, $body] = request('GET', $resources);
check($status === 200 && count($body['sensors']) === 1, 'API must return enabled motion devices only');
check($body['average'] === 21.5 && $body['sensors'][0]['lux'] === 100, 'API must supply temperature average and converted Lux');
check(isset($body['updatedAt']), 'API must timestamp the snapshot');
[$status, $body] = request('POST', []);
check($status === 405 && isset($body['error']), 'API must reject non-GET requests');
[$status, $body] = request('GET', [['error'=>['description'=>'unauthorized user']]]);
check($status === 502 && isset($body['error']), 'Hue credential errors must be explicit failures');
[$status, $body] = request('GET', null, 503);
check($status === 502 && isset($body['error']), 'Bridge failures must not turn into sample data');
[$status, $body] = request('GET', []);
check($status === 200 && $body['sensors'] === [] && $body['average'] === null, 'Empty bridges must return an empty snapshot');
echo "Passed 7 API response checks.\n";
