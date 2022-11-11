<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../_config.php';

use League\ColorExtractor\Color;
use League\ColorExtractor\Palette;

$releaseInformationCache = array();

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

function createArtistMetadataAndMap($creditArtists, $releaseId = null, $trackId = null)
{
    for ($i = 0; $i < count($creditArtists); $i++) {
        $creditArtist = $creditArtists[$i];

        $artistInfo = $creditArtist['artist'];
        $dbartist = DB::queryFirstRow('SELECT id FROM artistMetadata WHERE mbid = %s0', $artistInfo['id']);
        if ($dbartist === null) {
            DB::insert('artistMetadata', [
                'mbid' => $artistInfo['id'],
                'name' => $artistInfo['name'],
                'nameAlphabet' => $artistInfo['sort-name'],
                'namePhonic' => $artistInfo['sort-name'],
                'disambiguation' => $artistInfo['disambiguation'],
            ]);

            $dbartist = ['id' => DB::insertId()];
        }

        if (count(DB::query(
            'SELECT id FROM artistMap WHERE artistId = %s AND mapNo = %i AND releaseId = %? AND trackId = %?',
            $dbartist['id'],
            $i,
            $releaseId,
            $trackId
        )) == 0) {
            DB::insert('artistMap', [
                'artistId' => $dbartist['id'],
                'mapNo' => $i,
                'dispName' => $creditArtist['name'],
                'joinPhrase' => $creditArtist['joinphrase'],
                'releaseId' => $releaseId,
                'trackId' => $trackId,
                'type' => 1 # Type 1 = MusicBrainz Information
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
    '.ini',
    '.lrc',
    '.log',
    '.tmp',
    '.m3u',
    '.m3u8',
];

$libs = DB::query('SELECT id, basepath FROM library');
foreach ($libs as $libinfo) {
    $files = getFiles($libinfo['basepath']);

    $processing_i = 0;

    foreach ($files as $fp) {

        if ($processing_i++ >= 100)
            $releaseInformationCache = [];

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

        if (isset($metadata['error'])) {
            print('Skipped: ' . $fp . '. getID3 says: ' . $metadata['error'][0] . "\n");
            continue;
        }

        $metadata['tags']['id3v2'] = $metadata['tags']['id3v2'] ?? $metadata['tags']['id3v1'] ?? null;



        $primary_meta_provider = ($metadata['tags']['vorbiscomment'] ?? $metadata['tags']['id3v2']);
        $meta = [
            'title' => $primary_meta_provider['title'][0] ?? null,
            'album' => $primary_meta_provider['album'][0] ?? null,
            'artist' => $primary_meta_provider['artist'][0] ?? null,
            'track_number' => $primary_meta_provider['track_number'][0] ?? $primary_meta_provider['tracknumber'][0] ?? 0,
            'disk_number' => $primary_meta_provider['discnumber'][0] ?? 0,
            'album_artist' => $primary_meta_provider['albumartist'][0] ?? null,
            'mbid_release' => $primary_meta_provider['musicbrainz_albumid'][0] ?? null,
            'mbid_track' => $primary_meta_provider['musicbrainz_trackid'][0] ?? null,
        ];

        if (!is_numeric($meta['disk_number']))
            $meta['disk_number'] = 0;

        $mbid_release = $meta['mbid_release'];
        $mbid_track = $meta['mbid_track'];

        if (isset($metadata['id3v2']['TXXX'])) {
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
        }


        $id3HasArtistInfo = isset($meta['artist']);

        if (count(DB::query('SELECT id FROM track WHERE libraryId = %i AND path = %s', $libinfo['id'], $fp)) !== 0)
            continue;

        DB::startTransaction();

        $insertedTrack = null;
        $dbalbum = null;

        try {
            if ($mbid_release !== null) {
                $releaseInfo = null;
                if (isset($releaseInformationCache[$mbid_release]))
                    $releaseInfo = $releaseInformationCache[$mbid_release];
                else {
                    sleep(2);
                    $query_url = 'https://musicbrainz.org/ws/2/release/' . $mbid_release . '?inc=artist-credits+labels+discids+recordings&fmt=json';
                    $json = mbGet($query_url);
                    $releaseInfo = json_decode($json, true);
                    $releaseInformationCache[$mbid_release] = $releaseInfo;
                }

                if (($dbalbum = DB::queryFirstRow('SELECT id FROM releaseMetadata WHERE libraryId = %i AND releaseMbid = %s', $libinfo['id'], $mbid_release)) === null) {
                    $artworkPath = ($releaseInfo['cover-art-archive']['front'] ?? false) ?
                        'https://coverartarchive.org/release/' . $releaseInfo['id'] . '/front' :
                        null;

                    $artworkColor = null;

                    if (str_starts_with($artworkPath, 'http')) {
                        sleep(2);
                        $img = mbGet($artworkPath);
                        if (strlen($img) <= 30_000_000) {
                            $palette = Palette::fromContents($img);
                            $artworkColor = Color::fromIntToHex(current($palette->getMostUsedColors(1)), false);
                            $palette = null;
                        }
                    }

                    DB::insert('releaseMetadata', [
                        'libraryId' => $libinfo['id'],
                        'releaseMbid' => $releaseInfo['id'],
                        'title' => $releaseInfo['title'],
                        'titlePhonic' => $releaseInfo['title'],
                        'artworkPath' => $artworkPath,
                        'artworkColor' => $artworkColor,
                        'releaseDate' => $releaseInfo['date'],
                        'disambiguation' => $releaseInfo['disambiguation'],
                    ]);

                    $dbalbum = ['id' => DB::insertId()];

                    createArtistMetadataAndMap($releaseInfo['artist-credit'], $dbalbum['id'], null);
                }

                if ($mbid_track !== null) {
                    for ($diskno = 0; $diskno < count($releaseInfo['media']); $diskno++) {
                        for ($trackno = 0; $trackno < count($releaseInfo['media'][$diskno]['tracks']); $trackno++) {
                            $trackInfo = $releaseInfo['media'][$diskno]['tracks'][$trackno];
                            if ($mbid_track === $trackInfo['id']) {
                                DB::insert('track', [
                                    'libraryId' => $libinfo['id'],
                                    'trackMbid' => $trackInfo['id'],
                                    'recordingMbid' => $trackInfo['recording']['id'],
                                    'releaseId' => $dbalbum['id'],
                                    'title' => $trackInfo['title'],
                                    'duration' => floor($trackInfo['length'] / 1000),
                                    'diskNo' => $diskno + 1,
                                    'trackNo' => $trackno + 1,
                                    'path' => $fp,
                                ]);
                                $insertedTrack = ['id' => DB::insertId()];
                                createArtistMetadataAndMap($trackInfo['artist-credit'], null, $insertedTrack['id']);
                                $id3HasArtistInfo = false;
                            }
                        }
                    }
                }
            } else if (isset($meta['album'])) {
                $dbalbum = DB::queryFirstRow('SELECT id FROM releaseMetadata WHERE libraryId = %i AND title = %s', $libinfo['id'], $meta['album']);
                if ($dbalbum === null) {
                    DB::insert('releaseMetadata', [
                        'libraryId' => $libinfo['id'],
                        'title' => $meta['album'],
                    ]);
                    $dbalbum = ['id' => DB::insertId()];
                }
            }

            if ($insertedTrack === null) {
                DB::insert('track', [
                    'libraryId' => $libinfo['id'],
                    'releaseId' => $dbalbum['id'] ?? null,
                    'title' => $meta['title'],
                    'duration' => $metadata['playtime_seconds'],
                    'diskNo' => $meta['disk_number'],
                    'trackNo' => $meta['track_number'],
                    'path' => $fp,
                ]);
                $insertedTrack = ['id' => DB::insertId()];
            }

            if ($id3HasArtistInfo) {
                $searchedArtist = DB::queryFirstRow('SELECT id FROM artistMetadata WHERE name = %s', $meta['artist']);
                if ($searchedArtist === null) {
                    DB::insert('artistMetadata', [
                        'name' => $meta['artist']
                    ]);
                    $searchedArtist = ['id' => DB::insertId()];
                }
                DB::insert('artistMap', [
                    'artistId' => $searchedArtist['id'],
                    'trackId' => $insertedTrack['id'],
                    'dispName' => $meta['artist'],
                    'type' => 0 # Type 0 = Manual
                ]);
            }

            DB::commit();

            print("! $fp added.\n");
        } catch (Exception $ex) {
            DB::rollback();
            //throw $ex;
            print("@ Failed to add $fp: " . $ex->getMessage() . "\n");
        }
    }
}
