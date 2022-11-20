<?php
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
        $response->header("Cache-Control: max-age=604600, private, immutable");

        $response->code(206);
        $response->body(fread($fptr, $r_l));
    } else {
        $response->header("Accept-Ranges", "bytes");
        $response->header("Content-Length", $fsize);
        $response->header("Cache-Control", "max-age=604600, private, immutable");
        $response->file($fp);
    }
});

$klein->respond('GET', '/library/[i:libraryId]/track/[i:fileId]/lyric', function ($request, $response) {
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

    $pinfo = pathinfo($res['path']);
    $lrcbasepath = $pinfo['dirname'] . '/' . $pinfo['filename'] . '.';

    $retjson = array(
        "lines" => array()
    );

    if (is_file($lrcbasepath . 'lrc')) {
        $file = str_replace(array("\r\n", "\r"), "\n", file_get_contents($lrcbasepath . 'lrc'));
        foreach (explode("\n", $file) as $line) {
            $regex = '/^\[(\d{2}):(\d{2})\.(\d{2})\](.*)$/';
            if (preg_match($regex, $line)) {
                $data = preg_replace($regex, "$1\0$2\0$3\0$4", $line);
                $binData = explode("\0", $data);
                $time = ($binData[2] * 10) + ($binData[1] * 1000) + ($binData[0] * 60 * 1000);

                # Set end time in previous item
                if (count($retjson['lines']) > 0) {
                    end($retjson['lines']);
                    $lastlineId = key($retjson['lines']);
                    $retjson['lines'][$lastlineId]['endtime'] = $time;

                    # Set end time in previous last section
                    end($retjson['lines'][$lastlineId]['sections']);
                    $lastsectionId = key($retjson['lines'][$lastlineId]['sections']);
                    $retjson['lines'][$lastlineId]['sections'][$lastsectionId]['endtime'] = $time;
                }

                $retjson['lines'][] = array(
                    'time' => $time,
                    'endtime' => $time + 120_000,
                    'sections' => array(
                        array(
                            'time' => $time,
                            'endtime' => $time + 120_000,
                            'text' => $binData[3],
                        )
                    )
                );
            }
        }
    } else {
        $response->code(404);
        return;
    }

    setCors($request, $response);
    $response->header("Cache-Control", "max-age=604600, private");
    $response->json($retjson);
});

$klein->respond('GET', '/library/[i:libraryId]/track/[i:fileId]/artwork', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToExecuteLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $res = DB::queryFirstRow(
        'SELECT
            t.path AS path,
            rM.artworkPath AS fallback
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.id = %i AND
                t.releaseId = rM.id',
        $request->fileId
    );


    if ($res === null) {
        $response->code(404);
        return;
    }

    $parent = dirname($res['path']);
    $possible_cover_filenames = [
        'cover.jpg', 'cover.jpeg', 'cover.png', 'cover.bmp', 'cover.gif', 'cover.webp'
    ];
    $artworkPath = null;
    foreach ($possible_cover_filenames as $tryfname) {
        if (is_file($parent . '/' . $artworkPath)) {
            $artworkPath = $parent . '/' . $artworkPath;
            break;
        }
    }

    if ($artworkPath !== null) {
        $response->header("Cache-Control", "max-age=604600, private");
        $response->file($artworkPath);
        return;
    }

    $gid3 = new getID3;
    $metadata = $gid3->analyze($res['path']);

    if (!empty($metadata['comments']['picture'])) {
        $firstPic = array_values($metadata['comments']['picture'])[0];
        $response->header('Content-Type', $firstPic['image_mime']);
        $response->header("Cache-Control", "max-age=604600, private");
        $response->body($firstPic['data']);
        return;
    }

    $response->redirect($res['fallback'], 302);
});

$klein->respond('GET', '/library/[i:libraryId]/album/[i:id]/artwork', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    if (!checkUserHavePermissionToExecuteLibrary($loggedUser, $request->libraryId)) {
        $response->code(403);
        return null;
    }

    $res = DB::queryFirstRow(
        'SELECT
            t.path AS path,
            rM.artworkPath AS fallback
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.releaseId = %i AND
                t.releaseId = rM.id',
        $request->id
    );

    if ($res === null) {
        $response->code(404);
        return;
    }

    $parent = dirname($res['path']);
    $possible_cover_filenames = [
        'cover.jpg', 'cover.jpeg', 'cover.png', 'cover.bmp', 'cover.gif', 'cover.webp'
    ];
    $artworkPath = null;
    foreach ($possible_cover_filenames as $tryfname) {
        if (is_file($parent . '/' . $artworkPath)) {
            $artworkPath = $parent . '/' . $artworkPath;
            break;
        }
    }

    if ($artworkPath !== null) {
        $response->header("Cache-Control", "max-age=604600, private");
        $response->file($artworkPath);
        return;
    }

    $gid3 = new getID3;
    $metadata = $gid3->analyze($res['path']);

    if (!empty($metadata['comments']['picture'])) {
        $firstPic = array_values($metadata['comments']['picture'])[0];
        $response->header('Content-Type', $firstPic['image_mime']);
        $response->header("Cache-Control", "max-age=604600, private");
        $response->body($firstPic['data']);
        return;
    }

    $response->redirect($res['fallback'], 302);
});
