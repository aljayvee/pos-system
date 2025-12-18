<?php
// config/version.php
// Dynamically read version info from version.json to prevent mismatches
$jsonPath = base_path('version.json');
$v = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

return [
    'major'       => $v['major'] ?? 1,
    'minor'       => $v['minor'] ?? 0,
    'revision'    => $v['revision'] ?? 0,
    'build'       => $v['build'] ?? '0',
    'full'        => $v['full'] ?? 'unknown',
    'update_type' => $v['update_type'] ?? 'Stable',
    'changelog'   => $v['changelog'] ?? ''
];