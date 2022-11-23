<?php
$klein->respond('POST', '/history', function ($request, $response) {
    $loggedUser = loginAndExtendTokenExpireWithKlein($request, $response);
    if ($loggedUser === null) return;
    setCors($request, $response);

    $res = DB::queryFirstRow(
        'SELECT
            t.id AS id,
            t.recordingMbid AS recordingMbid,
            t.title AS title,
            rM.title AS albumName,
            rM.releaseMbid AS releaseMbid
            FROM
                track t,
                releaseMetadata rM
            WHERE
                t.releaseId = rM.id AND
                t.id = %i
            LIMIT 1',
        $_POST['id']
    );

    if ($res === null) {
        $response->code(400);
        return;
    }

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


    $userKeys = DB::queryFirstRow('SELECT listenBrainzKey FROM user WHERE id = %i', $loggedUser);
    if ($userKeys['listenBrainzKey'] !== null) {
        file_get_contents('https://api.listenbrainz.org/1/submit-listens', false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => array(
                    'Content-type: application/json; charset=UTF-8',
                    'Authorization: Token ' . $userKeys['listenBrainzKey']
                ),
                'content' => json_encode(array(
                    'listen_type' => 'single',
                    'payload' => array(array(
                        'listened_at' => time(),
                        'track_metadata' => array(
                            'additional_info' => array(
                                'release_mbid' => $res['releaseMbid'] ?? null,
                                'recording_mbid' => $res['recordingMbid'] ?? null
                            ),
                            'track_name' => $res['title'],
                            'release_name' => $res['albumName'],
                            'artist_name' => $res['artistString'],
                        )
                    ))
                ))
            )
        )));
    }
});
