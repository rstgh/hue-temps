<?php
declare(strict_types=1);

/** Hue exposes temperature, presence and light as separate resources. */
function normalizeSensors(array $resources): array
{
    $devices = [];
    foreach ($resources as $id => $sensor) {
        if (!is_array($sensor) || !in_array($sensor['type'] ?? '', ['ZLLPresence', 'ZLLTemperature', 'ZLLLightLevel'], true)) continue;
        // The MAC address identifies the physical device; endpoints can differ.
        $key = strtolower(explode('-', $sensor['uniqueid'] ?? "unknown-$id")[0]);
        if ($key === 'unknown') $key = "unknown-$id";
        $devices[$key][$sensor['type']] = $sensor;
        $devices[$key]['id'] = (string) ($devices[$key]['id'] ?? $id);
    }
    $result = [];
    foreach ($devices as $device) {
        $motion = $device['ZLLPresence'] ?? null;
        if (!$motion) continue;
        $temperature = $device['ZLLTemperature'] ?? null;
        $reachable = ($motion['config']['reachable'] ?? false) === true;
        $enabled = ($motion['config']['on'] ?? false) === true;
        if (!$enabled) continue;
        $raw = $temperature['state']['temperature'] ?? null;
        $validTemperature = $temperature && ($temperature['config']['reachable'] ?? false) && ($temperature['config']['on'] ?? false) && is_numeric($raw);
        $light = $device['ZLLLightLevel']['state']['lightlevel'] ?? null;
        $result[] = [
            'id' => $device['id'], 'name' => $motion['name'] ?? 'Motion sensor',
            'reachable' => $reachable, 'enabled' => $enabled,
            'motion' => $reachable && $enabled && ($motion['state']['presence'] ?? false) === true,
            'temperature' => $validTemperature ? round((float) $raw / 100, 2) : null,
            'battery' => isset($motion['config']['battery']) ? (int) $motion['config']['battery'] : null,
            'lux' => is_numeric($light) ? round(pow(10, ((float) $light - 1) / 10000)) : null,
            'updated' => ($motion['state']['lastupdated'] ?? 'none') !== 'none' ? $motion['state']['lastupdated'] . 'Z' : null,
        ];
    }
    return $result;
}
