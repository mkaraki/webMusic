<?php

require_once __DIR__ . '/library_random.php';

function checkUserHavePermissionToAccessLibrary(int $userId, int $libraryId): int
{
    global $useapcu;
    $acl = null;
    if ($useapcu && apcu_exists('/acl?user=' . $userId . '?lib=' . $libraryId))
        $acl = apcu_fetch('/acl?user=' . $userId . '?lib=' . $libraryId);
    else {
        $acl = DB::queryFirstRow('SELECT permission FROM accessList WHERE userid=%i AND libraryId = %i', $userId, $libraryId);
        apcu_store('/acl?user=' . $userId . '?lib=' . $libraryId, $acl, 259200);
    }

    if ($acl === null) {
        $guestAcl = null;
        if ($useapcu && apcu_exists('/acl?user=guest?lib=' . $libraryId))
            $guestAcl = apcu_fetch('/acl?user=guest?lib=' . $libraryId);
        else {
            $guestAcl = DB::queryFirstRow('SELECT permission FROM accessList WHERE userid IS NULL AND libraryId = %i', $libraryId);
            apcu_store('/acl?user=guest?lib=' . $libraryId, $guestAcl, 259200);
        }
        if ($guestAcl === null)
            return 0;
        else
            return $guestAcl['permission'];
    } else
        return $acl['permission'];
}

function checkUserHavePermissionToReadLibrary(int $userId, int $libraryId): bool
{
    return (checkUserHavePermissionToAccessLibrary($userId, $libraryId) & 0b100) > 0;
}

function checkUserHavePermissionToExecuteLibrary(int $userId, int $libraryId): bool
{
    return (checkUserHavePermissionToAccessLibrary($userId, $libraryId) & 0b001) > 0;
}

$klein->respond('GET', '/library', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;

    $availLibraries = DB::query(
        'SELECT
            l.id,
            l.name
        FROM
            library l,
            accessList a
        WHERE
            l.id = a.libraryId AND
            a.userid = %i AND
            a.permission > 0',
        $loggedUser
    );

    $guestAvailLibraries = DB::query(
        'SELECT
            l.id,
            l.name
        FROM
            library l,
            accessList a
        WHERE
            l.id = a.libraryId AND
            a.userid IS NULL AND
            a.permission > 0'
    );

    $ret = array_merge($availLibraries, $guestAvailLibraries);

    setCors($request, $response);
    $response->json($ret);
});

$klein->respond('GET', '/library/[i:libraryId]/check', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    $permission = checkUserHavePermissionToAccessLibrary($loggedUser, $request->libraryId);

    setCors($request, $response);
    if ($permission > 0) {
        $response->code(204);
        return;
    } else {
        $response->code(403);
        return;
    }
});

$klein->respond('GET', '/library/[i:libraryId]/track', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $page = $request->paramsGet()['page'] ?? 1;
    $res = DB::query(
        'SELECT
            t.id AS id,
            t.trackMbid AS mbid,
            t.title AS title,
            t.duration AS duration,
            t.diskNo AS diskNo,
            t.trackNo AS trackNo,
            rM.title AS albumName
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.libraryId = %i AND
                t.releaseId = rM.id
            ORDER BY albumName, diskNo, trackNo
            LIMIT %i, 51',
        $request->libraryId,
        ($page - 1) * 50
    );

    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistId, dispName, joinPhrase
                FROM artistMap
                WHERE trackId = %i
                ORDER BY mapNo',
            $res[$i]['id']
        );
        $res[$i]['artworkUrl'] = '/library/' . $request->libraryId . '/album/' . $res[$i]['mbid'] . '/artwork';
    }

    $ret = [
        'result' => count($res) > 50 ? array_slice($res, 0, 50) : $res,
        'next' => count($res) > 50 ? '/library/' . $request->libraryId . '/track?page=' . $page + 1 : null
    ];

    setCors($request, $response);
    $response->json($ret);
});

$klein->respond('GET', '/library/[i:libraryId]/track/[i:trackId]', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $page = $request->paramsGet()['page'] ?? 1;
    $res = DB::queryFirstRow(
        'SELECT
            t.id AS id,
            t.trackMbid AS mbid,
            t.title AS title,
            t.duration AS duration,
            t.diskNo AS diskNo,
            t.trackNo AS trackNo,
            rM.title AS albumName
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.libraryId = %i AND
                t.releaseId = rM.id AND
                t.id = %i
            LIMIT 1',
        $request->libraryId,
        $request->trackId
    );

    if ($res === null) {
        $response->code(404);
        return;
    }

    $res['artworkUrl'] = '/library/' . $request->libraryId . '/track/' . $res['id'] . '/artwork';

    $res['artist'] = DB::query(
        'SELECT mapNo AS sequence, artistId, dispName, joinPhrase
                FROM artistMap
                WHERE trackId = %i
                ORDER BY mapNo',
        $res['id']
    );

    $res['artistString'] = '';
    foreach ($res['artist'] as $ar) {
        $res['artistString'] .= $ar['dispName'] . $ar['joinPhrase'];
    }

    setCors($request, $response);
    $response->json($res);
});

$klein->respond('GET', '/library/[i:libraryId]/album/[i:id]', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return;
    }

    $res = DB::queryFirstRow(
        'SELECT
            t.releaseId AS id,
            rM.title AS albumName
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.libraryId = %i AND
                t.releaseId = rM.id AND
                t.releaseId = %i
            GROUP BY rM.id
            ORDER BY rM.id',
        $request->libraryId,
        $request->id
    );

    if ($res == null) {
        $response->code(404);
        return;
    }

    $res['artworkUrl'] = '/library/' . $request->libraryId . '/album/' . $res['id'] . '/artwork';

    $res['artist'] = DB::query(
        'SELECT mapNo AS sequence, artistId, dispName, joinPhrase
                FROM artistMap
                WHERE releaseId = %i
                ORDER BY mapNo',
        $res['id']
    );

    $tracks = DB::query(
        'SELECT
            t.id AS id,
            t.trackMbid AS mbid,
            t.title AS title,
            t.duration AS duration,
            t.diskNo AS diskNo,
            t.trackNo AS trackNo
            FROM
                track t
            WHERE
                t.libraryId = %i AND
                t.releaseId = %s
            ORDER BY diskNo, trackNo',
        $request->libraryId,
        $request->id
    );

    for ($i = 0; $i < count($tracks); $i++) {
        $tracks[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistId, dispName, joinPhrase
                FROM artistMap
                WHERE trackId = %i
                ORDER BY mapNo',
            $tracks[$i]['id']
        );
    }

    $res['track'] = $tracks;

    setCors($request, $response);
    $response->json($res);
});


$klein->respond('GET', '/library/[i:libraryId]/album', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $page = $request->paramsGet()['page'] ?? 1;
    $res = DB::query(
        'SELECT
            t.releaseId AS id,
            rM.title AS albumName
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.libraryId = %i AND
                t.releaseId = rM.id
            GROUP BY t.releaseId
            ORDER BY t.releaseId
            LIMIT %i, 51',
        $request->libraryId,
        ($page - 1) * 50
    );

    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistId, dispName, joinPhrase
                FROM artistMap
                WHERE releaseId = %i
                ORDER BY mapNo',
            $res[$i]['id']
        );

        $res[$i]['artworkUrl'] = '/library/' . $request->libraryId . '/album/' . $res[$i]['id'] . '/artwork';
    }

    $ret = [
        'result' => count($res) > 50 ? array_slice($res, 0, 50) : $res,
        'next' => count($res) > 50 ? '/library/' . $request->libraryId . '/album?page=' . $page + 1 : null
    ];

    setCors($request, $response);
    $response->json($ret);
});

require_once(__DIR__ . '/library_file_bridge.php');
