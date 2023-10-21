<?php
/*
 * Library Flusher
 * Usage: clearlib.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$target_tables = [
    'artistMap',
    'artistMetadata',
    'track',
    'releaseMetadata',
];

foreach ($target_tables as $table) {
    $sql = "DELETE FROM $table";
    print(' => ' . $sql . "\n");
    try {
        DB::query($sql);
    } catch (\Throwable $e) {
        print(' <= ERROR: ' . $e->getMessage() . "\n");
    }
}
