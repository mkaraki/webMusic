<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$dbstruct = '';

if ($argc == 1)
    $dbstruct = file_get_contents(__DIR__ . '/../init.db/database.sql');
else if ($argc == 2)
    $dbstruct = file_get_contents(__DIR__ . '/../init.db/' . $argv[1] . '.sql');
else
    die('Invalid usage');

$dbstruct = str_replace(["\r\n", "\n", "\r"], " ", $dbstruct);
$dbstruct = preg_replace('/\s+/', ' ', $dbstruct);

$sqls = explode(';', $dbstruct);

foreach ($sqls as $sql) {
    if (empty($sql))
        continue;
    print('=> ' . $sql . "\n");
    DB::query($sql);
}
