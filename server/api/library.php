<?php

function checkUserHavePermissionToAccessLibrary(int $userId, int $libraryId): int
{
    $acl = DB::queryFirstRow('SELECT permission FROM accessList WHERE userid=%i AND libraryId = %i', $userId, $libraryId);

    if ($acl === null)
        return 0;
    else
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
            track.id AS id,
            tM.trackMbid AS mbid,
            tM.title AS title,
            tM.duration AS duration,
            tM.diskNo AS diskNo,
            tM.trackNo AS trackNo,
            tM.releaseMbid AS releaseMbid,
            rM.title AS albumName
            FROM
                track track,
                trackMetadata tM,
                releaseMetadata rM
            WHERE
                track.libraryId = %i AND
                track.trackMbid = tM.trackMbid AND
                tM.releaseMbid = rM.mbid
            ORDER BY albumName, diskNo, trackNo
            LIMIT %i, 51',
        $request->libraryId,
        ($page - 1) * 50
    );

    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistMbid, dispName, joinPhrase
                FROM artistMap
                WHERE trackMbid = %s
                ORDER BY mapNo',
            $res[$i]['mbid']
        );
        $res[$i]['artworkUrl'] = 'http://localhost:8080/library/' . $request->libraryId . '/album/' . $res[$i]['mbid'] . '/artwork';
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
            track.id AS id,
            tM.trackMbid AS mbid,
            tM.title AS title,
            tM.duration AS duration,
            tM.diskNo AS diskNo,
            tM.trackNo AS trackNo,
            tM.releaseMbid AS releaseMbid,
            rM.title AS albumName,
            rM.artworkColor AS artworkColor
            FROM
                track track,
                trackMetadata tM,
                releaseMetadata rM
            WHERE
                track.libraryId = %i AND
                track.trackMbid = tM.trackMbid AND
                tM.releaseMbid = rM.mbid AND
                track.id = %i
            LIMIT 1',
        $request->libraryId,
        $request->trackId
    );

    if ($res === null) {
        $response->code(404);
        return;
    }

    $res['artworkUrl'] = 'http://localhost:8080/library/' . $request->libraryId . '/track/' . $res['id'] . '/artwork';

    $res['artist'] = DB::query(
        'SELECT mapNo AS sequence, artistMbid, dispName, joinPhrase
                FROM artistMap
                WHERE trackMbid = %s
                ORDER BY mapNo',
        $res['mbid']
    );

    setCors($request, $response);
    $response->json($res);
});

$klein->respond('GET', '/library/[i:libraryId]/album/[:mbid]', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return;
    }

    $res = DB::queryFirstRow(
        'SELECT
            tM.releaseMbid AS mbid,
            rM.title AS albumName,
            rM.artworkColor AS artworkColor
            FROM
                track track,
                trackMetadata tM,
                releaseMetadata rM
            WHERE
                track.libraryId = %i AND
                track.trackMbid = tM.trackMbid AND
                tM.releaseMbid = rM.mbid AND
                tM.releaseMbid = %s
            GROUP BY rM.mbid
            ORDER BY tM.releaseMbid',
        $request->libraryId,
        $request->mbid
    );

    if ($res == null) {
        $response->code(404);
        return;
    }

    $res['artworkUrl'] = 'http://localhost:8080/library/' . $request->libraryId . '/album/' . $res['mbid'] . '/artwork';

    $res['artist'] = DB::query(
        'SELECT mapNo AS sequence, artistMbid, dispName, joinPhrase
                FROM artistMap
                WHERE releaseMbid = %s
                ORDER BY mapNo',
        $res['mbid']
    );

    $tracks = DB::query(
        'SELECT
            track.id AS id,
            tM.trackMbid AS mbid,
            tM.title AS title,
            tM.duration AS duration,
            tM.diskNo AS diskNo,
            tM.trackNo AS trackNo
            FROM
                track track,
                trackMetadata tM
            WHERE
                track.libraryId = %i AND
                track.trackMbid = tM.trackMbid AND
                tM.releaseMbid = %s
            ORDER BY diskNo, trackNo',
        $request->libraryId,
        $request->mbid
    );

    for ($i = 0; $i < count($tracks); $i++) {
        $tracks[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistMbid, dispName, joinPhrase
                FROM artistMap
                WHERE trackMbid = %s
                ORDER BY mapNo',
            $tracks[$i]['mbid']
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
            tM.releaseMbid AS mbid,
            rM.title AS albumName
            FROM
                track track,
                trackMetadata tM,
                releaseMetadata rM
            WHERE
                track.libraryId = %i AND
                track.trackMbid = tM.trackMbid AND
                tM.releaseMbid = rM.mbid
            GROUP BY rM.mbid
            ORDER BY tM.releaseMbid
            LIMIT %i, 51',
        $request->libraryId,
        ($page - 1) * 50
    );

    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['artist'] = DB::query(
            'SELECT mapNo AS sequence, artistMbid, dispName, joinPhrase
                FROM artistMap
                WHERE releaseMbid = %s
                ORDER BY mapNo',
            $res[$i]['mbid']
        );

        $res[$i]['artworkUrl'] = 'http://localhost:8080/library/' . $request->libraryId . '/album/' . $res[$i]['mbid'] . '/artwork';
    }

    $ret = [
        'result' => count($res) > 50 ? array_slice($res, 0, 50) : $res,
        'next' => count($res) > 50 ? '/library/' . $request->libraryId . '/album?page=' . $page + 1 : null
    ];

    setCors($request, $response);
    $response->json($ret);
});

require_once(__DIR__ . '/library_file_bridge.php');
