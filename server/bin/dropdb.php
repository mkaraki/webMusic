<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$target_tables = [
    'user',
    'sessionToken',
    'library',
    'accessList',
    'artistMetadata',
    'releaseMetadata',
    'track',
    'artistMap'
];

foreach (array_reverse($target_tables) as $table) {
    $sql = "DROP TABLE $table";
    print(' => ' . $sql . "\n");
    try {
        DB::query($sql);
    } catch (\Throwable $e) {
        print(' <= ERROR: ' . $e->getMessage() . "\n");
    }
}
