<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$dbstruct = file_get_contents(__DIR__ . '/../init.db/database.sql');
$dbstruct = str_replace(["\r\n", "\n", "\r"], " ", $dbstruct);
$dbstruct = preg_replace('/\s+/', ' ', $dbstruct);

$sqls = explode(';', $dbstruct);

foreach ($sqls as $sql) {
    if (empty($sql))
        continue;
    print('=> ' . $sql . "\n");
    DB::query($sql);
}
