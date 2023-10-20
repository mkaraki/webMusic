<?php
/*
 * List access list for library
 * Usage: listacl.php
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

$aclist = DB::query(
    'SELECT
        l.id AS id,
        l.name AS name,
        a.userid AS user,
        a.permission AS permission,
        u.username AS username
    FROM
        library AS l,
        accessList AS a,
        user AS u
    WHERE
        l.id = a.libraryId AND
        u.id = a.userid'
);

foreach ($aclist as $acl) {
    print($acl['id'] . "\t");
    print($acl['name'] ?? 'Unnamed Library' . "\t");
    print(($acl['user'] == 0 ? 'Everyone' : $acl['username']) . "\t");
    print(($acl['permission'] & 0b100) > 0 ? 'r' : '-');
    print(($acl['permission'] & 0b010) > 0 ? 'w' : '-');
    print(($acl['permission'] & 0b001) > 0 ? 'x' : '-');
    print("\n");
}
