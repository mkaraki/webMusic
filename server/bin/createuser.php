<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

if (count($argv) != 2) {
    die('Usage: createuser.php "username"');
}

$username = $argv[1];

$randpwd = base_convert(md5(uniqid()), 16, 36);
$pwdhash = password_hash($randpwd, PASSWORD_DEFAULT);

$q = DB::query('SELECT id FROM user WHERE username = %s0 LIMIT 1', $username);
if (count($q) > 0) {
    die('User already exists');
}

DB::insert('user', [
    'username' => $username,
    'passhash' => $pwdhash
]);

print('User generated with password: ' . $randpwd . "\n");
