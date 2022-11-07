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
            rM.title AS albumName,
            rM.artworkPath AS artworkUrl
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
            rM.artworkPath AS artworkUrl,
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

$klein->respond('GET', '/library/[i:libraryId]/track/[i:fileId]/file', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToExecuteLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $res = DB::queryFirstRow('SELECT path FROM track WHERE id=%i', $request->fileId);

    if ($res === null || !is_file($res['path'])) {
        $response->code(404);
        return;
    }

    $fp = $res['path'];
    $fmime = mime_content_type($fp);
    $fsize = filesize($fp);
    $fptr = fopen($fp, 'rb');

    $response->header("Content-type", $fmime);
    setCors($request, $response);

    if (isset($request->headers()['Range'])) {
        $range = explode('=', $request->headers()['Range']);
        if ($range[0] !== 'bytes' || count($range) !== 2) {
            $response->code(416);
            $response->header('Content-Range', "bytes */$fsize");
            return;
        }

        $rangebytes = explode(',', $range[1]);
        $rangebyte = explode('-', $rangebytes[0]);
        if (count($rangebytes) !== 1 || count($rangebyte) !== 2 || (!is_numeric($rangebyte[0]) && $rangebyte[0] !== '') | (!is_numeric($rangebyte[1]) && $rangebyte[1] !== '')) {
            $response->code(416);
            $response->header('Content-Range', "bytes */$fsize");
            return;
        }

        $p_s = intval($rangebyte[0]);
        $p_e = min($p_s + 1048576, $fsize - 1);
        if (is_numeric($rangebyte[1]))
            $p_e = intval($rangebyte[1]);

        if ($p_s < 0 || $p_e >= $fsize) {
            $response->code(416);
            $response->header('Content-Range', "bytes */$fsize");
            return;
        }

        $r_l = $p_e - $p_s + 1;
        fseek($fptr, $p_s);

        $response->header('Content-Range', "bytes $p_s-$p_e/$fsize");
        $response->header("Content-Length", $r_l);

        $response->code(206);
        $response->body(fread($fptr, $r_l));
    } else {
        $response->header("Accept-Ranges", "bytes");
        $response->header("Content-Length", $fsize);
        $response->file($fp);
    }
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
            rM.artworkPath AS artworkUrl,
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
            rM.title AS albumName,
            rM.artworkPath AS artworkUrl
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
    }

    $ret = [
        'result' => count($res) > 50 ? array_slice($res, 0, 50) : $res,
        'next' => count($res) > 50 ? '/library/' . $request->libraryId . '/album?page=' . $page + 1 : null
    ];

    setCors($request, $response);
    $response->json($ret);
});
