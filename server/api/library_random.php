<?php

$klein->respond('GET', '/library/[i:libraryId]/random/album', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

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
            ORDER BY RAND()
            LIMIT %i',
        $request->libraryId,
        $request->paramsGet()['count'] ?? 10
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

    setCors($request, $response);
    $response->json($res);
});


$klein->respond('GET', '/library/[i:libraryId]/random/track', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToReadLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

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
            ORDER BY RAND()
            LIMIT %i',
        $request->libraryId,
        $request->paramsGet()['count'] ?? 10
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

    setCors($request, $response);
    $response->json($res);
});
