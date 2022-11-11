<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/_config.php';

$klein = new \Klein\Klein();

require_once(__DIR__ . '/api/login.php');

function setCors($request, $response, bool $auth = true)
{
    $response->header('Access-Control-Allow-Origin', 'http://127.0.0.1:5173');
    if ($auth)
        $response->header('Access-Control-Allow-Credentials', 'true');
}

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

    setCors($request, $response, false);
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

    setCors($request, $response, false);
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

    setCors($request, $response, false);
    $response->json($res);
});


$klein->respond('/app/', function ($request, $response) {
    $path = __DIR__ . '/public/index.html';
    if (!is_file($path)) {
        $response->code(404);
        return;
    }

    $content = file_get_contents($path);
    $response->body($content);
});

$klein->respond('/app/[**:path]', function ($request, $response) {
    $basepath = realpath(__DIR__ . '/public');
    $path = $basepath . '/' . $request->path;
    if (!str_starts_with(realpath($path), $basepath)) {
        $response->code(403);
        return;
    }
    if (!is_file($path)) {
        $response->code(404);
        return;
    }

    $content = file_get_contents($path);
    switch (end(explode('.', $path))) {
        case "js":
            $response->header('Content-Type', 'application/javascript');
            break;

        case "css":
            $response->header('Content-Type', 'text/css');
            break;

        default:
            $response->header('Content-Type', mime_content_type($path));
            break;
    }
    $response->body($content);
});

$klein->respond('/[|app:entry]', function ($request, $response) {
    $response->redirect('/app/');
});

require_once(__DIR__ . '/api/library.php');

$klein->dispatch();
