<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$target_tables = [
    'user',
    'library',
    'accessList',
    'artistMetadata',
    'releaseMetadata',
    'trackMetadata',
    'track',
    'artistMap'
];

foreach (array_reverse($target_tables) as $table) {
    $sql = "DROP TABLE $table";
    print(' => ' . $sql . "\n");
    try {
        DB::query($sql);
    } catch (Exception $e) {
        print(' <= ERROR: ' . $e->getMessage() . "\n");
    }
}
