<?php
namespace Analytics\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Db\TableGateway\TableGateway;

class StreamController extends AbstractActionController
{
    /**
     * GET /analytics/stream-fetch?id=123
     * Safe placeholder (unchanged).
     */
    public function fetchAction()
    {
        $sl      = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $id      = (int)$this->params()->fromQuery('id', 0);

        if ($id <= 0) {
            return new JsonModel([
                'ok'    => false,
                'error' => 'Missing or invalid release ID'
            ]);
        }

        $sql = "
            SELECT id, songName, trackArtist, isrc
            FROM tbl_track
            WHERE master_id = :rid
            ORDER BY id ASC
            LIMIT 1
        ";
        $trackRow = $adapter->createStatement($sql, [':rid' => $id])->execute()->current();

        if (!$trackRow) {
            return new JsonModel([
                'ok'     => false,
                'error'  => 'No track found for this release',
                'notice' => 'No spotify mapping yet'
            ]);
        }

        return new JsonModel([
            'ok'                 => true,
            'release_id'         => $id,
            'track_id'           => (string)$trackRow['id'],
            'track_title'        => (string)$trackRow['songName'],
            'track_artist'       => (string)$trackRow['trackArtist'],
            'streams'            => null,
            'monthly_listeners'  => null,
            'note'               => 'No Spotify ID mapped for this track yet'
        ]);
    }

    /**
     * GET /analytics/stream-update?limit=100[&key=SECRET][&dry_run=1][&force=1]
     * Refresh stale artist rows from Spotify API (unchanged from your version).
     */
    public function updateAction()
    {
        $sl      = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $cronKey = getenv('CRON_SECRET') ?: 'prime_cron_secret_123';
        $keyIn   = (string)$this->params()->fromQuery('key', '');
        if ($cronKey !== '' && hash_equals($cronKey, $keyIn) === false) {
            return new JsonModel(['ok' => false, 'error' => 'unauthorized']);
        }

        $limit = (int)$this->params()->fromQuery('limit', 100);
        if ($limit < 1)   $limit = 50;
        if ($limit > 500) $limit = 500;

        $dry   = ((int)$this->params()->fromQuery('dry_run', 0) === 1);
        $force = ((int)$this->params()->fromQuery('force', 0) === 1);

        $token = $this->getSpotifyAccessTokenCompat();
        if (!$token) {
            return new JsonModel([
                'ok'    => false,
                'error' => 'Spotify token unavailable (set SPOTIFY_CLIENT_ID/SECRET or keep hardcoded)'
            ]);
        }

        $staleWhere = $force
            ? "(spotify_id IS NOT NULL AND TRIM(spotify_id) <> '')"
            : "(spotify_id IS NOT NULL AND TRIM(spotify_id) <> '' AND (updated_at IS NULL OR updated_at < (NOW() - INTERVAL 2 DAY)))";

        $sql = "
            SELECT id, spotify_id
            FROM tbl_artist
            WHERE {$staleWhere}
            ORDER BY COALESCE(updated_at, '1970-01-01') ASC
            LIMIT {$limit}
        ";

        try {
            $rows = $adapter->query($sql, [])->toArray();
        } catch (\Throwable $e) {
            return new JsonModel([
                'ok' => false,
                'error' => 'DB query failed',
                'details' => $e->getMessage()
            ]);
        }

        if (!$rows) {
            return new JsonModel([
                'ok' => true,
                'checked' => 0,
                'updated' => 0,
                'failed'  => 0,
                'note' => $force ? 'No rows to process (force on)' : 'Nothing stale; all artists refreshed within last 48h'
            ]);
        }

        $checked = 0; $updated = 0; $failed = 0;

        $chunks = array_chunk($rows, 50);
        foreach ($chunks as $chunk) {
            $mapRowIdBySpId = [];
            $ids = [];
            foreach ($chunk as $r) {
                $spId = trim((string)$r['spotify_id']);
                if ($spId === '') continue;
                $ids[] = $spId;
                $mapRowIdBySpId[$spId] = (int)$r['id'];
            }
            if (!$ids) continue;

            $artists = $this->fetchArtistsBatch($ids, $token);
            $checked += count($ids);

            if (!is_array($artists)) {
                $failed += count($ids);
                continue;
            }

            foreach ($artists as $a) {
                if (!is_array($a)) { $failed++; continue; }
                $spId = (string)($a['id'] ?? '');
                if ($spId === '' || !isset($mapRowIdBySpId[$spId])) { $failed++; continue; }

                $rowId = $mapRowIdBySpId[$spId];

                $name       = (string)($a['name'] ?? '');
                $followers  = (int)   (($a['followers']['total'] ?? 0));
                $popularity = (int)   ($a['popularity'] ?? 0);
                $extUrl     = (string)($a['external_urls']['spotify'] ?? '');

                $imgBig = null; $imgSm = null;
                if (!empty($a['images']) && is_array($a['images'])) {
                    $imgBig = $a['images'][0]['url'] ?? null;
                    $last   = end($a['images']);
                    $imgSm  = ($last['url'] ?? $imgBig) ?: null;
                }

                $sqlUp = "
                    UPDATE tbl_artist
                    SET
                        name        = COALESCE(?, name),
                        image_url   = ?,
                        banner_url  = ?,
                        followers   = ?,
                        popularity  = ?,
                        ext_url     = ?,
                        link_status = 'linked',
                        updated_at  = NOW()
                    WHERE id = ?
                ";

                try {
                    if (!$dry) {
                        $adapter->query($sqlUp, [
                            $name !== '' ? $name : null,
                            $imgSm,
                            $imgBig,
                            $followers ?: null,
                            $popularity ?: null,
                            $extUrl ?: null,
                            $rowId
                        ]);
                    }
                    $updated++;
                } catch (\Throwable $e) {
                    $failed++;
                }
            }
        }

        return new JsonModel([
            'ok'      => true,
            'checked' => $checked,
            'updated' => $updated,
            'failed'  => $failed,
            'dry_run' => $dry,
            'force'   => $force
        ]);
    }

    /* ================= Spotify helpers ================= */

    /**
     * App token with file cache (compatible with your setup).
     */
    private function getSpotifyAccessTokenCompat()
    {
        // ⚠️ your creds (leave as-is / or set via ENV)
        $hardCid = '2428679943a34c7c8a9d94fc8b68cf8b';
        $hardSec = '1af7bee2a0bb4feba0527e5e24b313f3';

        $cid = getenv('SPOTIFY_CLIENT_ID') ?: $hardCid;
        $sec = getenv('SPOTIFY_CLIENT_SECRET') ?: $hardSec;
        if ($cid === '' || $sec === '') return null;

        $root = realpath(__DIR__ . '/../../../..') ?: getcwd();
        $cacheDir  = rtrim($root, '/').'/data/cache';
        $cacheFile = $cacheDir . '/spotify_token.json';
        @mkdir($cacheDir, 0775, true);

        if (is_file($cacheFile)) {
            $j = json_decode(@file_get_contents($cacheFile), true);
            if (!empty($j['access_token']) && !empty($j['expires_at']) && $j['expires_at'] > time() + 20) {
                return $j['access_token'];
            }
        }

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'client_credentials']),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic '.base64_encode($cid.':'.$sec),
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hLen = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $hdrs = substr($resp, 0, (int)$hLen);
        $body = substr($resp, (int)$hLen);
        curl_close($ch);

        if ($code === 200 && $body) {
            $data = json_decode($body, true);
            if (!empty($data['access_token'])) {
                $out = [
                    'access_token' => $data['access_token'],
                    'expires_at'   => time() + ((int)($data['expires_in'] ?? 3600)),
                ];
                @file_put_contents($cacheFile, json_encode($out));
                return $out['access_token'];
            }
        }

        @file_put_contents($cacheDir.'/spotify_token_debug.log',
            date('c')." TOKEN_FAIL code=$code\n$hdrs\n$body\n\n", FILE_APPEND);

        return null;
    }

    /** Small curl helpers */
    private function spGET(string $url, string $token)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$token, 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, $body];
    }
    private function spGETjson(string $url, string $token)
    {
        list($c,$b) = $this->spGET($url, $token);
        $j = @json_decode((string)$b, true);
        return [$c, $j];
    }

    /**
     * GET /analytics/stream-playlists
     * Modes:
     *  - **Featured On** (artist scan): pass artist_id (auto verify=1, only_related=1)
     *  - Editorial fallback: owner=spotify lists when no artist_id
     */
public function playlistsAction()
{
    $this->getEvent()->getViewModel()->setTerminal(true);
    if (ob_get_level() > 0) { @ob_end_clean(); }
    @ob_start();

    $res = $this->getResponse();
    $res->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    if (!isset($_SESSION['user_id']) && !$isAjax) {
        $res->setStatusCode(401);
        echo json_encode(['ok'=>false,'error'=>'unauthorized']);
        $out = @ob_get_clean(); echo $out; return $res;
    }

    try {
        /** @var \Zend\Db\Adapter\Adapter $db */
        $db  = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $aid         = (int)$this->params()->fromQuery('artist_id', 0);
        $mode        = strtolower(trim((string)$this->params()->fromQuery('mode','scan_top')));
        $marketIn    = strtoupper(trim((string)$this->params()->fromQuery('market','GLOBAL')));
        $marketTop   = ($marketIn === 'GLOBAL' || $marketIn === '') ? 'US' : $marketIn;

        // 🔧 performance-friendly defaults (FAST)
        $maxTracks   = max(3,  min(10, (int)$this->params()->fromQuery('max_tracks', 6)));
        $maxPl       = max(8,  min(36, (int)$this->params()->fromQuery('max_playlists', 24)));
        $perPlTracks = max(30, min(80, (int)$this->params()->fromQuery('per_playlist_tracks', 50)));
        $ownerFilter = strtolower(trim((string)$this->params()->fromQuery('owner', 'spotify'))); // spotify | any
        $useCache    = ((int)$this->params()->fromQuery('cache', 1) === 1);
        $ttlSecs     = max(300, min(86400, (int)$this->params()->fromQuery('cache_ttl', 21600))); // default 6h

        // ⏱️ hard global time budget ~3.5s
        $t0 = microtime(true);
        $deadline = $t0 + 3.5;

        if ($aid <= 0) {
            echo json_encode(['ok'=>false,'error'=>'missing_artist_id']); 
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // ============ CACHE: short-circuit if fresh ============
        $cacheRow = null;
        if ($useCache) {
            $cacheRow = $db->getDriver()->createStatement(
                "SELECT rows_json, UNIX_TIMESTAMP(updated_at) AS ts
                   FROM tbl_cache_sp_playlists
                  WHERE artist_id=? AND market=? AND mode=? LIMIT 1"
            )->execute([$aid, $marketIn ?: 'GLOBAL', $mode])->current();

            if ($cacheRow && (time() - (int)$cacheRow['ts']) <= $ttlSecs) {
                echo $cacheRow['rows_json']; // already full JSON (ok/rows/note)
                $out = @ob_get_clean(); echo $out; return $res;
            }
        }

        // helper: add market
        $withMarket = function(string $base) use ($marketIn): string {
            if ($marketIn && $marketIn !== 'GLOBAL') {
                return $base . (strpos($base,'?')===false ? '?' : '&') . 'market=' . rawurlencode($marketIn);
            }
            return $base;
        };

        // token
        $token = $this->getSpotifyAccessTokenCompat();
        if (!$token) {
            $payload = json_encode(['ok'=>true,'rows'=>[], 'note'=>'no_token'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            echo $payload;
            // write empty to cache to avoid hammering
            if ($useCache) {
                @$db->getDriver()->createStatement(
                    "REPLACE INTO tbl_cache_sp_playlists (artist_id,market,mode,rows_json,updated_at) VALUES (?,?,?,?,NOW())"
                )->execute([$aid, $marketIn ?: 'GLOBAL', $mode, $payload]);
            }
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // ======= resolve artist name + spotify_id =======
        $row = $db->getDriver()->createStatement(
            "SELECT name, spotify_id FROM tbl_artist WHERE id=? LIMIT 1"
        )->execute([$aid])->current();

        $artistName = $row ? trim((string)$row['name']) : '';
        $spArtistId = $row ? trim((string)$row['spotify_id']) : '';

        // normalize ID (url/uri -> id)
        if ($spArtistId !== '') {
            if (preg_match('~open\.spotify\.com/artist/([A-Za-z0-9]+)~', $spArtistId, $m)) $spArtistId = $m[1];
            if (preg_match('~^spotify:artist:([A-Za-z0-9]+)$~', $spArtistId, $m))      $spArtistId = $m[1];
            $spArtistId = preg_replace('~[^A-Za-z0-9]~', '', $spArtistId);
        }

        // resolve by search if empty (respect time budget)
        if ($spArtistId === '' && $artistName !== '' && microtime(true) < $deadline) {
            list($cs, $js) = $this->spGETjson(
                'https://api.spotify.com/v1/search?type=artist&limit=1&q='.rawurlencode($artistName),
                $token
            );
            if ($cs === 200 && !empty($js['artists']['items'][0]['id'])) {
                $spArtistId = (string)$js['artists']['items'][0]['id'];
                @$db->getDriver()->createStatement(
                    "UPDATE tbl_artist SET spotify_id=? WHERE id=?"
                )->execute([$spArtistId, $aid]);
            }
        }

        if ($spArtistId === '') {
            $payload = json_encode(['ok'=>true,'rows'=>[], 'note'=>'no_spotify_id_resolved'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            echo $payload;
            if ($useCache) {
                @$db->getDriver()->createStatement(
                    "REPLACE INTO tbl_cache_sp_playlists (artist_id,market,mode,rows_json,updated_at) VALUES (?,?,?,?,NOW())"
                )->execute([$aid, $marketIn ?: 'GLOBAL', $mode, $payload]);
            }
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // ======= fetch top-tracks (fast, multi-market) =======
        $tryMarkets = array_values(array_unique(array_filter([$marketTop,'IN','US','GB','DE','FR'])));
        $topTracks  = []; $ttStatus = [];
        foreach ($tryMarkets as $mk) {
            if (microtime(true) >= $deadline) break;
            list($cTop, $jTop) = $this->spGETjson("https://api.spotify.com/v1/artists/{$spArtistId}/top-tracks?market=".$mk, $token);
            $ttStatus[] = [$mk,$cTop];
            if ($cTop !== 200 || empty($jTop['tracks'])) continue;

            foreach ($jTop['tracks'] as $t) {
                if (empty($t['id'])) continue;
                $topTracks[] = [
                    'id'   => (string)$t['id'],
                    'name' => (string)($t['name'] ?? ''),
                ];
                if (count($topTracks) >= $maxTracks) break;
            }
            if ($topTracks) break; // got tracks for some market
        }

        if (!$topTracks) {
            $payload = json_encode([
                'ok'=>true,'rows'=>[], 'tracks'=>[],
                'note'=>['mode'=>'scan_top','reason'=>'no_top_tracks','artist_id'=>$spArtistId,'markets_tried'=>$tryMarkets,'statuses'=>$ttStatus]
            ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            echo $payload;
            if ($useCache) {
                @$db->getDriver()->createStatement(
                    "REPLACE INTO tbl_cache_sp_playlists (artist_id,market,mode,rows_json,updated_at) VALUES (?,?,?,?,NOW())"
                )->execute([$aid, $marketIn ?: 'GLOBAL', $mode, $payload]);
            }
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // ======= Fast search+verify with strict query and owner filter =======
        $rows   = [];
        $seenPl = [];
        $spotifyOnly = ($ownerFilter !== 'any');

        foreach ($topTracks as $t) {
            if (microtime(true) >= $deadline) break;

            // strict query reduces junk: track:"..." artist:"..."
            $q = 'track:"'.str_replace('"','',$t['name']).'"';
            if ($artistName !== '') $q .= ' artist:"'.str_replace('"','',$artistName).'"';

            list($cS, $jS) = $this->spGETjson(
                'https://api.spotify.com/v1/search?type=playlist&limit=8&q='.rawurlencode($q),
                $token
            );
            if ($cS !== 200) continue;

            foreach (($jS['playlists']['items'] ?? []) as $pl) {
                if (microtime(true) >= $deadline) break 2;

                $pid = $pl['id'] ?? null; if (!$pid || isset($seenPl[$pid])) continue;

                // owner filter
                $ownerId   = (string)($pl['owner']['id'] ?? '');
                $ownerName = (string)($pl['owner']['display_name'] ?? $ownerId);
                $isSpotify = (mb_strtolower($ownerId,'UTF-8') === 'spotify');
                if ($spotifyOnly && !$isSpotify) continue;

                // verify (FAST): only first 50 items
                $nextPT   = $withMarket("https://api.spotify.com/v1/playlists/{$pid}/tracks?fields=items(track(id)),next,total&limit=50");
                $scanned  = 0;
                $foundAny = false;

                while ($nextPT && $scanned < $perPlTracks && microtime(true) < $deadline) {
                    list($cP, $jP) = $this->spGETjson($nextPT, $token);
                    if ($cP !== 200) break;

                    $items = (array)($jP['items'] ?? []);
                    foreach ($items as $it) {
                        $trk = $it['track'] ?? null;
                        if (!empty($trk['id']) && $trk['id'] === $t['id']) { $foundAny = true; break; }
                    }
                    if ($foundAny) break;

                    $scanned += count($items);
                    // FAST: do not chase next page unless we still have budget
                    if ($scanned >= $perPlTracks || microtime(true) >= $deadline) break;
                    $nextPT = $jP['next'] ?? null;
                    // optional: avoid full crawl — just 1 extra page max
                    if ($nextPT) $nextPT = null; // comment this line if you want one more page
                }

                if (!$foundAny) { $seenPl[$pid] = 1; continue; }

                $rows[] = [
                    'id'               => (string)$pid,
                    'name'             => (string)($pl['name'] ?? ''),
                    'description'      => (string)($pl['description'] ?? ''),
                    'owner'            => $ownerName,
                    'owner_id'         => $ownerId,
                    'owner_is_spotify' => $isSpotify ? 1 : 0,
                    'image'            => (string)($pl['images'][0]['url'] ?? ''),
                    'tracks'           => (int)($pl['tracks']['total'] ?? 0),
                    'spotify_url'      => (string)($pl['external_urls']['spotify'] ?? ('https://open.spotify.com/playlist/'.$pid)),
                    'type'             => $isSpotify ? 'editorial_owner_spotify' : 'playlist',
                    'related'          => 1,
                    'match'            => 'track_id',
                    'via_track'        => ['id'=>$t['id'], 'name'=>$t['name']]
                ];
                $seenPl[$pid] = 1;

                if (count($rows) >= $maxPl) break 2;
            }
        }

        // sort: spotify-owned first, then by size
        usort($rows, function($a,$b){
            if ($a['owner_is_spotify'] !== $b['owner_is_spotify']) return $a['owner_is_spotify'] ? -1 : 1;
            return ($b['tracks'] <=> $a['tracks']);
        });

        $payload = json_encode([
            'ok'   => true,
            'rows' => $rows,
            'note' => [
                'mode'      => 'scan_top_fast',
                'market'    => $marketIn,
                'owner'     => $ownerFilter,
                't_taken'   => round(microtime(true) - $t0, 3),
                'deadline'  => ($deadline - microtime(true) <= 0) ? 'hit' : 'ok',
                'returned'  => count($rows)
            ]
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        echo $payload;

        // write cache
        if ($useCache) {
            @$db->getDriver()->createStatement(
                "REPLACE INTO tbl_cache_sp_playlists (artist_id,market,mode,rows_json,updated_at) VALUES (?,?,?,?,NOW())"
            )->execute([$aid, $marketIn ?: 'GLOBAL', $mode, $payload]);
        }

        $out = @ob_get_clean(); echo $out; return $res;

    } catch (\Throwable $e) {
        $res->setStatusCode(500);
        echo json_encode(['ok'=>false,'error'=>'server_error','detail'=>$e->getMessage()]);
        $out = @ob_get_clean(); echo $out; return $res;
    }
}

public function toptracksAction()
{
    // --- hard JSON output, no stray HTML ---
    $this->getEvent()->getViewModel()->setTerminal(true);
    if (ob_get_level() > 0) { @ob_end_clean(); }
    @ob_start();

    $res = $this->getResponse();
    $res->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

    // optional auth (same style as playlistsAction)
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    if (!isset($_SESSION['user_id']) && !$isAjax) {
        $res->setStatusCode(401);
        echo json_encode(['ok'=>false,'error'=>'unauthorized']);
        $out = @ob_get_clean(); echo $out; return $res;
    }

    try {
        /** @var \Zend\Db\Adapter\Adapter $db */
        $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $aid       = (int)$this->params()->fromQuery('artist_id', 0);
        $limit     = max(1, min(10, (int)$this->params()->fromQuery('limit', 10)));
        $marketIn  = strtoupper(trim((string)$this->params()->fromQuery('market','GLOBAL')));
        $marketTop = ($marketIn === 'GLOBAL' || $marketIn === '') ? 'US' : $marketIn;

        if ($aid <= 0) {
            echo json_encode(['ok'=>false,'error'=>'missing_artist_id']); 
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // --- get spotify token
        $token = $this->getSpotifyAccessTokenCompat();
        if (!$token) {
            echo json_encode(['ok'=>true,'rows'=>[], 'note'=>'no_token']); 
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // --- resolve artist name + spotify_id from DB
        $row = $db->getDriver()->createStatement(
            "SELECT name, spotify_id FROM tbl_artist WHERE id=? LIMIT 1"
        )->execute([$aid])->current();

        $artistName = $row ? trim((string)$row['name']) : '';
        $spArtistId = $row ? trim((string)$row['spotify_id']) : '';

        // normalize ID (url/uri -> id)
        if ($spArtistId !== '') {
            if (preg_match('~open\.spotify\.com/artist/([A-Za-z0-9]+)~', $spArtistId, $m)) $spArtistId = $m[1];
            if (preg_match('~^spotify:artist:([A-Za-z0-9]+)$~', $spArtistId, $m))      $spArtistId = $m[1];
            $spArtistId = preg_replace('~[^A-Za-z0-9]~', '', $spArtistId);
        }

        // resolve by search if empty
        if ($spArtistId === '' && $artistName !== '') {
            list($cs, $js) = $this->spGETjson(
                'https://api.spotify.com/v1/search?type=artist&limit=1&q='.rawurlencode($artistName), $token
            );
            if ($cs === 200 && !empty($js['artists']['items'][0]['id'])) {
                $spArtistId = (string)$js['artists']['items'][0]['id'];
                // best-effort save
                if ($spArtistId !== '') {
                    @$db->getDriver()->createStatement(
                        "UPDATE tbl_artist SET spotify_id=? WHERE id=?"
                    )->execute([$spArtistId, $aid]);
                }
            }
        }

        if ($spArtistId === '') {
            echo json_encode(['ok'=>true,'rows'=>[], 'note'=>'no_spotify_id_resolved']);
            $out = @ob_get_clean(); echo $out; return $res;
        }

        // --- fetch top tracks (multi-market fallback)
        $try = array_values(array_unique(array_filter([$marketTop,'IN','US','GB','DE','FR'])));
        $tracks = []; $statuses = [];
        foreach ($try as $mk) {
            list($cTop, $jTop) = $this->spGETjson(
                "https://api.spotify.com/v1/artists/{$spArtistId}/top-tracks?market=".$mk, $token
            );
            $statuses[] = [$mk, $cTop];
            if ($cTop !== 200 || empty($jTop['tracks'])) continue;

            foreach ($jTop['tracks'] as $t) {
                if (empty($t['id'])) continue;

                // --- album details extraction
                $alb        = (array)($t['album'] ?? []);
                $albumType  = strtolower((string)($alb['album_type'] ?? '')); // "single" | "album" | "compilation"
                $albumId    = (string)($alb['id'] ?? '');
                $albumUrl   = (string)($alb['external_urls']['spotify'] ?? '');
                $albumName  = (string)($alb['name'] ?? '');
                $albumImg   = (string)($alb['images'][0]['url'] ?? '');
                $relDate    = (string)($alb['release_date'] ?? '');
                $totalTrk   = (int)   ($alb['total_tracks'] ?? 0);

                // human label (+ EP heuristic)
                $albumLabel = ($albumType === 'single') ? 'Single' : (($albumType === 'album') ? 'Album' : ucfirst($albumType));
                if ($albumType === 'single' && $totalTrk >= 3) {
                    $albumLabel = 'EP';
                }

                $tracks[] = [
                    'id'          => (string)$t['id'],
                    'name'        => (string)($t['name'] ?? ''),
                    'artists'     => array_values(array_filter(array_map(function($a){
                                        return (string)($a['name'] ?? '');
                                     }, (array)($t['artists'] ?? [])))),
                    'image'       => (string)($t['album']['images'][0]['url'] ?? ''), // legacy main image (album art)
                    'duration_ms' => (int)($t['duration_ms'] ?? 0),
                    'preview_url' => (string)($t['preview_url'] ?? ''),   // 🔊 direct play
                    'spotify_url' => (string)($t['external_urls']['spotify'] ?? ''),
                    'popularity'  => (int)($t['popularity'] ?? 0),

                    // ⭐ full album blob
                    'album' => [
                        'id'           => $albumId,
                        'name'         => $albumName,
                        'type'         => $albumType,     // "single" | "album" | "compilation"
                        'type_label'   => $albumLabel,    // "Single" / "EP" / "Album" / ...
                        'release_date' => $relDate,
                        'total_tracks' => $totalTrk,
                        'image'        => $albumImg,
                        'spotify_url'  => $albumUrl,
                    ],

                    // (optional) convenience: top-level type for quick badges
                    'album_type'       => $albumType,
                    'album_type_label' => $albumLabel,
                ];

                if (count($tracks) >= $limit) break;
            }
            if ($tracks) break;
        }

        echo json_encode([
            'ok'    => true,
            'rows'  => $tracks,
            'note'  => ['markets_tried'=>$try,'statuses'=>$statuses]
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        $out = @ob_get_clean(); echo $out; return $res;
    } catch (\Throwable $e) {
        $res->setStatusCode(500);
        echo json_encode(['ok'=>false,'error'=>'server_error','detail'=>$e->getMessage()]);
        $out = @ob_get_clean(); echo $out; return $res;
    }
}

    /** Batch artists (unchanged from your version) */
    private function fetchArtistsBatch(array $artistIds, string $token)
    {
        if (!$artistIds) return [];
        $idsParam = implode(',', array_map('rawurlencode', $artistIds));
        $url = 'https://api.spotify.com/v1/artists?ids=' . $idsParam;

        $attempts = 0;
        while ($attempts < 3) {
            $attempts++;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPGET        => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$token],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hdrSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($resp, 0, (int)$hdrSize);
            $body    = substr($resp, (int)$hdrSize);
            curl_close($ch);

            if ($code === 200 && $body) {
                $json = json_decode($body, true);
                return (is_array($json) && !empty($json['artists'])) ? $json['artists'] : [];
            }

            if ($code === 429) {
                $retryAfter = 1;
                if (preg_match('~Retry-After:\s*(\d+)~i', $headers, $m)) {
                    $retryAfter = max(1, (int)$m[1]);
                }
                sleep(min($retryAfter, 5));
                continue;
            }

            if ($code === 401) return null;
            if ($code >= 500) { usleep(300000); continue; }
            return null;
        }
        return null;
    }
}
