<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

if (count($argv) != 3) {
    die('Usage: lbsetkey.php "username" "ListenBrainz api key"');
}

$userinfo = DB::queryFirstRow('SELECT id FROM user WHERE username=%s', $argv[1]);

if ($userinfo === null)
    die('No specified user exists');

DB::query('UPDATE user SET listenBrainzKey = %s WHERE username = %s', $argv[2], $argv[1]);

print("Ok.\n");
