<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

if (count($argv) != 3) {
    die('Usage: grantlib.php "username" "library id"');
}

$userinfo = DB::queryFirstRow('SELECT id FROM user WHERE username=%s', $argv[1]);

if ($userinfo === null)
    die('No specified user exists');

DB::insert('accessList', [
    'userid' => $userinfo['id'],
    'libraryId' => intval($argv[2])
]);

print("Ok.\n");
