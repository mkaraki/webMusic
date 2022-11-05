<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

if (count($argv) != 2) {
    die('Usage: createlib.php "library path"');
}

$dir = $argv[1];

if (!is_dir($dir)) {
    die('Directory not exists');
}

$q = DB::query('SELECT id FROM library WHERE basepath = %s0 LIMIT 1', $dir);
if (count($q) > 0) {
    die('Library with specified directory already exists');
}

DB::insert('library', [
    'basepath' => $dir,
]);

print("Library added.\n");
