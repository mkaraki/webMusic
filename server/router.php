<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/_config.php';

$klein = new \Klein\Klein();

$klein->respond('GET', '/hello-world', function () {
    return 'Hello World!';
});

$klein->respond('GET', '/artist', function ($request, $response) {
    $page = $request->paramsGet()['page'] ?? 1;
    $res = DB::query(
        'SELECT mbid, name, namePhonic, nameAlphabet, imagePath FROM artistMetadata
            ORDER BY mbid
            LIMIT %i, 51',
        ($page - 1) * 50
    );

    $ret = [
        'result' => count($res) > 50 ? array_slice($res, 0, 50) : $res,
        'next' => count($res) > 50 ? '/artist?page=' . $page + 1 : null
    ];

    $response->header('Access-Control-Allow-Origin', '*');
    $response->json($ret);
});

$klein->respond('GET', '/artist/[:mbid]', function ($request, $response) {
    $res = DB::queryFirstRow(
        'SELECT * FROM artistMetadata
            WHERE mbid=%s
            ORDER BY mbid
            LIMIT 1',
        $request->mbid
    );
    $response->header('Access-Control-Allow-Origin', '*');
    if ($res === null)
        $response->code(404);
    else
        $response->json($res);
});

$klein->respond('GET', '/track/[:mbid]', function ($request, $response) {
    $res = DB::queryFirstRow(
        'SELECT
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
                trackMetadata tM,
                releaseMetadata rM
            WHERE
                tM.trackMbid = %s AND
                tM.releaseMbid = rM.mbid
            LIMIT 1',
        $request->mbid
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

    $response->header('Access-Control-Allow-Origin', '*');
    $response->json($res);
});

$klein->respond('GET', '/library/[i:libraryId]/track', function ($request, $response) {
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

    $response->header('Access-Control-Allow-Origin', '*');
    $response->json($ret);
});

$klein->respond('GET', '/library/[i:libraryId]/track/[i:trackId]', function ($request, $response) {
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

    $response->header('Access-Control-Allow-Origin', '*');
    $response->json($res);
});

$klein->respond('GET', '/library/[i:libraryId]/track/[i:fileId]/file', function ($request, $response) {
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
    $response->header('Access-Control-Allow-Origin', '*');

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


$klein->respond('GET', '/library/[i:libraryId]/album', function ($request, $response) {
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

    $response->header('Access-Control-Allow-Origin', '*');
    $response->json($ret);
});


$klein->dispatch();
