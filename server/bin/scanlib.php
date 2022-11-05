<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;

function mbGet($url)
{
    return file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", [
                'User-Agent: webMusic/0.1 (https://twitter.com/arakimk)'
            ])
        ]
    ]));
}

function getFiles($dir)
{
    $toret = [];

    foreach (scandir($dir) as $fn) {
        $lowerfn = strtolower($fn);

        // No parent/current/useless directory/file
        if (
            $fn === '.'  ||
            $fn === '..' ||
            $lowerfn === '.ds_store' ||
            $lowerfn === 'thumbs.db'
        ) continue;

        $fpath = $dir . DIRECTORY_SEPARATOR . $fn;

        if (is_dir($fpath))
            $toret = array_merge($toret, getFiles($fpath));
        else if (is_file($fpath))
            $toret[] = $fpath;
    }
    return $toret;
}

function createArtistMetadataAndMap($creditArtists, $releaseMbid = null, $trackMbid = null)
{
    for ($i = 0; $i < count($creditArtists); $i++) {
        $creditArtist = $creditArtists[$i];

        $artistInfo = $creditArtist['artist'];
        $dbartist = DB::query('SELECT mbid FROM artistMetadata WHERE mbid = %s0', $artistInfo['id']);
        if (count($dbartist) < 1) {

            DB::insert('artistMetadata', [
                'mbid' => $artistInfo['id'],
                'name' => $artistInfo['name'],
                'nameAlphabet' => $artistInfo['sort-name'],
                'namePhonic' => $artistInfo['sort-name'],
                'disambiguation' => $artistInfo['disambiguation'],
            ]);
        }

        if (count(DB::query(
            'SELECT id FROM artistMap WHERE artistMbid = %s AND mapNo = %i AND releaseMbid = %? AND trackMbid = %?',
            $artistInfo['id'],
            $i,
            $releaseMbid,
            $trackMbid
        )) == 0) {
            DB::insert('artistMap', [
                'artistMbid' => $artistInfo['id'],
                'mapNo' => $i,
                'dispName' => $creditArtist['name'],
                'joinPhrase' => $creditArtist['joinphrase'],
                'releaseMbid' => $releaseMbid,
                'trackMbid' => $trackMbid
            ]);
        }
    }
}

$gid3 = new getID3;

$blacklisted_exts = [
    '.nfo',
    '.jpeg',
    '.jpg',
    '.png',
    '.webp',
    '.gif',
    '.bmp',
    '.ini'
];

$libs = DB::query('SELECT id, basepath FROM library');
foreach ($libs as $libinfo) {
    $files = getFiles($libinfo['basepath']);

    foreach ($files as $fp) {
        $lfp = strtolower($fp);

        $discontinue = false;
        foreach ($blacklisted_exts as $blacklisted_ext) {
            if (str_ends_with($lfp, $blacklisted_ext)) {
                $discontinue = true;
                break;
            }
        }

        if ($discontinue === true)
            continue;

        $metadata = $gid3->analyze($fp);

        if (!isset(
            $metadata['id3v2'],
            $metadata['id3v2']['TXXX']
        )) {
            print("Skipped: $fp (No required id3 tag. Do you have MusicBrainz id information?)\n");
            continue;
        }

        $mbid_release = null;
        $mbid_track = null;

        foreach ($metadata['id3v2']['TXXX'] as $txxx_tags) {
            switch ($txxx_tags['description']) {
                case "MusicBrainz Album Id":
                    $mbid_release = mb_convert_encoding($txxx_tags['data'], 'utf-8', $txxx_tags['encoding']);
                    break;

                case "MusicBrainz Release Track Id":
                    $mbid_track = mb_convert_encoding($txxx_tags['data'], 'utf-8', $txxx_tags['encoding']);
                    break;
            }
        }

        if (!isset(
            $mbid_release,
            $mbid_track
        )) {
            print("Skipped: $fp (No MusicBrainz metadata)\n");
            continue;
        }

        if (count(DB::query('SELECT id FROM track WHERE libraryId = %i AND trackMbid = %s', $libinfo['id'], $mbid_track)) !== 0)
            continue;

        DB::startTransaction();

        try {
            $dbalbum = DB::query('SELECT mbid FROM releaseMetadata WHERE mbid = %s0', $mbid_release);
            if (count($dbalbum) < 1) {
                sleep(2);
                $query_url = 'https://musicbrainz.org/ws/2/release/' . $mbid_release . '?inc=artist-credits+labels+discids+recordings&fmt=json';
                $json = mbGet($query_url);
                $releaseInfo = json_decode($json, true);

                $artworkPath = ($releaseInfo['cover-art-archive']['front'] ?? false) ?
                    'https://coverartarchive.org/release/' . $releaseInfo['id'] . '/front' :
                    null;

                $artworkColor = null;

                if (str_starts_with($artworkPath, 'http')) {
                    sleep(2);
                    $img = mbGet($artworkPath);
                    $palette = Palette::fromContents($img);

                    $artworkColor = Color::fromIntToHex(current($palette->getMostUsedColors(1)), false);
                }

                DB::insert('releaseMetadata', [
                    'mbid' => $releaseInfo['id'],
                    'title' => $releaseInfo['title'],
                    'titlePhonic' => $releaseInfo['title'],
                    'artworkPath' => $artworkPath,
                    'artworkColor' => $artworkColor,
                    'releaseDate' => $releaseInfo['date'],
                    'disambiguation' => $releaseInfo['disambiguation'],
                ]);

                createArtistMetadataAndMap($releaseInfo['artist-credit'], $releaseInfo['id'], null);

                for ($diskno = 0; $diskno < count($releaseInfo['media']); $diskno++) {
                    for ($trackno = 0; $trackno < count($releaseInfo['media'][$diskno]['tracks']); $trackno++) {
                        $trackInfo = $releaseInfo['media'][$diskno]['tracks'][$trackno];
                        if (count(DB::query(
                            'SELECT trackMbid FROM trackMetadata WHERE releaseMbid = %s AND recordingMbid = %s AND diskNo = %i AND trackNo = %i',
                            $releaseInfo['id'],
                            $trackInfo['recording']['id'],
                            $diskno + 1,
                            $trackno + 1,
                        )) < 1) {
                            DB::insert('trackMetadata', [
                                'trackMbid' => $trackInfo['id'],
                                'recordingMbid' => $trackInfo['recording']['id'],
                                'releaseMbid' => $releaseInfo['id'],
                                'title' => $trackInfo['title'],
                                'duration' => floor($trackInfo['length'] / 1000),
                                'diskNo' => $diskno + 1,
                                'trackNo' => $trackno + 1,
                            ]);
                        }

                        createArtistMetadataAndMap($trackInfo['artist-credit'], null, $trackInfo['id']);
                    }
                }
            }

            DB::insert('track', [
                'path' => $fp,
                'libraryId' => $libinfo['id'],
                'trackMbid' => $mbid_track
            ]);

            DB::commit();

            print("! $fp added.\n");
        } catch (Exception $ex) {
            DB::rollback();
            //throw $ex;
            print("@ Failed to add $fp: " . $ex->getMessage());
        }
    }
}
