<?php
namespace Analytics\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Db\TableGateway\TableGateway;

class FreshController extends AbstractActionController
{
    /**
     * GET /analytics/latest-attached-releases?q=...&page=1&per=10
     * NOTE: Spotify heavy calls yahan nahi chalenge. FE ko per-row stats_url diya jaata hai.
     */
  public function releaseAudioAction()
{
    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $rid = (int)$this->params()->fromQuery('id', 0);
    if ($rid <= 0) {
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'bad id']);
    }

    // 1) Read first track for this release/master
    $sql = "
        SELECT audio_file, audio_file_name, preview_start
        FROM tbl_track
        WHERE master_id = :rid
        ORDER BY volume ASC, order_id ASC, id ASC
        LIMIT 1
    ";
    $row = $adapter->createStatement($sql, [':rid'=>$rid])->execute()->current();
    if (!$row) {
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'no track found']);
    }

    $path = trim((string)($row['audio_file'] ?? ''));
    if ($path === '') {
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'no audio_file']);
    }

    // 2) Build a public URL
    $url = $this->toPublicAudioUrl($path);
    if (!$url) {
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'audio url not resolvable']);
    }

    // 3) Optional preview start → seconds
    $start = $this->parsePreviewStart((string)($row['preview_start'] ?? ''));

    return new \Zend\View\Model\JsonModel([
        'ok'    => true,
        'url'   => $url,
        'name'  => (string)($row['audio_file_name'] ?? basename($url)),
        'start' => $start,
    ]);
}

/** Convert DB path to public URL */
private function toPublicAudioUrl(string $p) : ?string
{
    $p = trim(str_replace('\\','/',$p));
    if ($p === '') return null;

    // Absolute http(s)
    if (preg_match('~^https?://~i', $p)) return $p;

    // Already absolute from webroot
    if ($p[0] === '/') {
        // ensure file exists if it looks like local
        $fs = getcwd() . $p;
        return $p; // even if not found, return as-is (could be proxied)
    }

    // Relative → try common locations
    $candidates = [
        '/public/uploads/audio/' . $p,
        '/public/uploads/'       . $p,
        '/' . ltrim($p,'/'),
    ];
    foreach ($candidates as $rel) {
        if (is_file(getcwd() . $rel)) return $rel;
    }
    // Fallback: assume audio folder
    return $candidates[0];
}

/** Parse preview_start like "35" or "0:35" → seconds (float) */
private function parsePreviewStart(string $s) : float
{
    $s = trim($s);
    if ($s === '') return 0.0;

    // mm:ss or hh:mm:ss
    if (preg_match('~^(?:(\d+):)?(\d{1,2}):(\d{2})(?:\.\d+)?$~', $s, $m)) {
        $h = (int)($m[1] ?? 0);
        $m2 = (int)$m[2];
        $sec = (int)$m[3];
        return (float)($h*3600 + $m2*60 + $sec);
    }
    if (preg_match('~^(\d{1,2}):(\d{2})(?:\.\d+)?$~', $s, $m)) {
        return (float)((int)$m[1]*60 + (int)$m[2]);
    }
    // numeric seconds
    if (is_numeric($s)) return max(0.0, (float)$s);

    return 0.0;
}

private function releaseAudioUrl(\Zend\Db\Adapter\Adapter $adapter, int $releaseId): string
{
    // 1) primary table & candidate columns (adjust if needed)
    $table = 'tbl_track';
    $candidates = [
        'audio_url','audio','audio_file','file_url','file','path',
        'wav','wav_file','wav_url','mp3','mp3_file','mp3_url',
        'track_url','track_file','source','src'
    ];

    // 2) find which columns exist
    $cols = [];
    try {
        $res = $adapter->createStatement("SHOW COLUMNS FROM `$table`")->execute();
        foreach ($res as $c) $cols[] = $c['Field'];
    } catch (\Throwable $e) {
        error_log("SHOW COLUMNS $table failed: ".$e->getMessage());
        return '';
    }

    $exists = array_values(array_intersect($candidates, $cols));
    if (!$exists) {
        // Fallback: some installs keep audio in tbl_release_files
        $altTable = 'tbl_release_files';
        try {
            $res = $adapter->createStatement("SHOW COLUMNS FROM `$altTable`")->execute();
            $altCols = []; foreach ($res as $c) $altCols[] = $c['Field'];
            $altCandidates = array_values(array_intersect($candidates, $altCols));
            if ($altCandidates) {
                $sql = "SELECT {$altCandidates[0]} AS audio FROM `$altTable` WHERE release_id=:rid ORDER BY id ASC LIMIT 1";
                $row = $adapter->createStatement($sql, [':rid'=>$releaseId])->execute()->current();
                $val = isset($row['audio']) ? trim((string)$row['audio']) : '';
                return $this->normalizeAudioPath($val);
            }
        } catch (\Throwable $e) {}
        return '';
    }

    // 3) build COALESCE(expr...) to choose first non-null column
    $coalesce = 'COALESCE(' . implode(',', array_map(function($c){ return "`$c`"; }, $exists)) . ')';

    // 4) fetch first track for this release (adjust key if needed)
    // Many schemas use master_id to link track -> release
    $sql = "SELECT $coalesce AS audio
            FROM `$table`
            WHERE master_id = :rid
            ORDER BY trackNo ASC, id ASC
            LIMIT 1";
    try {
        $row = $adapter->createStatement($sql, [':rid'=>$releaseId])->execute()->current();
        $val = isset($row['audio']) ? trim((string)$row['audio']) : '';
        return $this->normalizeAudioPath($val);
    } catch (\Throwable $e) {
        error_log("releaseAudioUrl query failed: ".$e->getMessage());
        return '';
    }
}

/** make absolute web path like /public/uploads/audio/....wav */
private function normalizeAudioPath(string $val): string
{
    if ($val === '') return '';
    // already absolute http(s)
    if (preg_match('~^https?://~i', $val)) return $val;

    // if it already starts with /public, keep it
    if ($val[0] === '/') return $val;

    // common storage roots
    if (stripos($val,'uploads/') === 0) return '/'.$val;
    if (stripos($val,'audio/')   === 0) return '/public/uploads/'.$val;

    // default: assume it sits under public/uploads/audio/
    return '/public/uploads/audio/'.ltrim($val,'/');
}
/**
 * GET /analytics/render-video?poster=...&audio=...&start=...&poster_id=...
 * Generates 15s MP4 on server via ffmpeg binary and returns it directly.
 */
public function renderVideoAction()
{
    $req = $this->getRequest();

    /*
     * STEP 0: Read inputs (POST JSON preferred, else fallback GET query)
     */
    $posterWithTextData = '';
    $posterUrl          = '';
    $audioUrl           = '';
    $startSec           = 0.0;
    $clipLen            = 30.0;
    $posterId           = 0;

    if ($req->isPost()) {
        // frontend is now sending JSON { poster_with_text, poster, audio, start, len, poster_id }
        $raw  = $req->getContent();
        $json = @json_decode($raw, true);

        if (is_array($json)) {
            $posterWithTextData = (string)($json['poster_with_text'] ?? '');
            $posterUrl          = (string)($json['poster'] ?? '');
            $audioUrl           = (string)($json['audio'] ?? '');
            $startSec           = (float)($json['start'] ?? 0);
            $clipLen            = (float)($json['len']   ?? 30);
            $posterId           = (int)($json['poster_id'] ?? 0);
        }
    } else {
        // old GET style fallback
        $posterWithTextData = (string)$req->getQuery('poster_with_text', '');
        $posterUrl          = (string)$req->getQuery('poster', '');
        $audioUrl           = (string)$req->getQuery('audio', '');
        $startSec           = (float)$req->getQuery('start', 0);
        $clipLen            = (float)$req->getQuery('len', 30);
        $posterId           = (int)$req->getQuery('poster_id', 0);
    }

    // safety caps
    if ($clipLen <= 0)   { $clipLen = 30.0; }
    if ($clipLen > 30.0) { $clipLen = 30.0; }
    if ($startSec < 0)   { $startSec = 0.0; }

    if ($posterUrl === '' && $posterWithTextData === '') {
        // we have nothing to render visually
        return $this->plainError(400, "Missing poster image");
    }

    // IMPORTANT: absolute server root for site
    $SITE_ROOT = '/home/primebackstage/htdocs/www.primebackstage.in';

    /*
     * STEP 1: prepare temp workspace
     */
    $tmpDir  = sys_get_temp_dir() . '/story_' . uniqid();
    @mkdir($tmpDir, 0777, true);

    $imgFile = $tmpDir . '/poster.png';
    $audFile = $tmpDir . '/audio.mp3';
    $outFile = $tmpDir . '/out.mp4';

    /*
     * STEP 2: Build poster frame (prefer client composited WITH TEXT)
     *
     * Case A: poster_with_text is a dataURL like data:image/jpeg;base64,AAAA...
     * Case B: no overlay provided → fallback to old posterUrl logic
     */
    $posterOk = false;

    if ($posterWithTextData !== '' && strpos($posterWithTextData, 'data:image') === 0) {
        // decode base64 and write directly
        if (preg_match('#^data:image/[^;]+;base64,(.+)$#', $posterWithTextData, $m)) {
            $bin = base64_decode($m[1]);
            if ($bin !== false && strlen($bin) > 1000) {
                file_put_contents($imgFile, $bin);
                $posterOk = true;
            }
        }
        // if decode failed we'll fall back to posterUrl below
    }

    if (!$posterOk) {
        // Fallback to original logic using posterUrl (without overlay text)
        if ($posterUrl === '') {
            $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
            return $this->plainError(400, "Poster URL empty and no composited data provided");
        }

        if (preg_match('~^/public/~', $posterUrl)) {
            // direct static file from uploads → copy
            $localPath = $SITE_ROOT . $posterUrl;
            if (!@copy($localPath, $imgFile)) {
                $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
                return $this->plainError(500, "Poster copy failed from $localPath");
            }
            $posterOk = true;
        } else {
            // "render internally" path you already had
            $imgTmp = $this->renderPosterInternally($posterUrl, $tmpDir);
            if (!$imgTmp || !is_file($imgTmp) || filesize($imgTmp) < 1000) {
                $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
                return $this->plainError(500, "Poster internal render failed for $posterUrl");
            }
            @rename($imgTmp, $imgFile);
            $posterOk = true;
        }
    }

    // sanity check poster
    if (!$posterOk || !is_file($imgFile) || filesize($imgFile) < 1000) {
        $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
        return $this->plainError(500, "Poster file invalid after fetch/decode");
    }

    /*
     * STEP 3: resolve audio (optional)
     */
    $hasAudio = false;
    if ($audioUrl !== '') {
        if (preg_match('~^/public/~', $audioUrl)) {
            // local file path
            $localAudio = $SITE_ROOT . $audioUrl;
            if (@copy($localAudio, $audFile) && filesize($audFile) > 1000) {
                $hasAudio = true;
            }
        } else {
            // external URL / CDN mp3, etc.
            if ($this->downloadToFile($audioUrl, $audFile, 8) && filesize($audFile) > 1000) {
                $hasAudio = true;
            }
        }
    }

    /*
     * STEP 4: ffmpeg command
     *
     * - loop poster.png as video background
     * - cut audio from startSec for clipLen seconds
     * - force 1080x1920 (vertical story), yuv420p
     * - duration always clipLen (capped 30s)
     */
    $ffmpegBin = '/usr/bin/ffmpeg';
    if (!is_executable($ffmpegBin)) {
        $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
        return $this->plainError(500, "ffmpeg not executable at $ffmpegBin");
    }

    if ($hasAudio) {
        // with audio: seek audio ($startSec), limit length ($clipLen)
        // we loop image for $clipLen
        $cmd = sprintf(
            '%s -y ' .
            '-loop 1 -framerate 30 -t %s -i %s ' .
            '-ss %s -t %s -i %s ' .
            '-c:v libx264 -vf "scale=1080:1920,format=yuv420p" -pix_fmt yuv420p ' .
            '-c:a aac -b:a 192k -shortest -movflags +faststart %s 2>&1',
            $ffmpegBin,
            escapeshellarg($clipLen),
            escapeshellarg($imgFile),
            escapeshellarg($startSec),
            escapeshellarg($clipLen),
            escapeshellarg($audFile),
            escapeshellarg($outFile)
        );
    } else {
        // no audio: create silent stereo so Instagram won't mute weird
        $cmd = sprintf(
            '%s -y ' .
            '-loop 1 -framerate 30 -t %s -i %s ' .
            '-f lavfi -t %s -i anullsrc=channel_layout=stereo:sample_rate=44100 ' .
            '-c:v libx264 -vf "scale=1080:1920,format=yuv420p" -pix_fmt yuv420p ' .
            '-c:a aac -b:a 192k -shortest -movflags +faststart %s 2>&1',
            $ffmpegBin,
            escapeshellarg($clipLen),
            escapeshellarg($imgFile),
            escapeshellarg($clipLen),
            escapeshellarg($outFile)
        );
    }

    // run ffmpeg with timeout (20s same as you had)
    $outTxt = $this->runWithTimeout($cmd, 20);

    if (!is_file($outFile) || filesize($outFile) < 1024) {
        $errMsg = "ffmpeg failed\n" . substr($outTxt, -1500);
        $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
        return $this->plainError(500, $errMsg);
    }

    /*
     * STEP 5: return MP4
     * filename now always -30s.mp4
     */
    $resp = $this->getResponse();
    $resp->getHeaders()
        ->addHeaderLine('Content-Type', 'video/mp4')
        ->addHeaderLine(
            'Content-Disposition',
            'attachment; filename="poster-' . $posterId . '-30s.mp4"'
        )
        ->addHeaderLine('Content-Length', filesize($outFile));

    $resp->setContent(file_get_contents($outFile));

    // cleanup temp
    $this->cleanupFiles([$imgFile,$audFile,$outFile,$tmpDir]);
    return $resp;
}

/**
 * This function "renders" the poster image using the same logic as renderAction(),
 * but WITHOUT doing HTTP back into Apache/PHP (which was freezing you).
 * It returns a path to a PNG file we can feed ffmpeg.
 *
 * posterUrl looks like:
 *   https://www.primebackstage.in/analytics/render?tpl=launch_story&title=...&artist=...&cover=/public/uploads/20251018215454.jpg&id=7748
 */
private function renderPosterInternally(string $posterUrl, string $tmpDir)
{
    $u = parse_url($posterUrl);
    if (empty($u['query'])) return null;
    parse_str($u['query'], $q);

    $tpl    = strtolower(trim((string)($q['tpl']    ?? 'launch_story')));
    $title  = trim((string)($q['title']  ?? ''));
    $artist = trim((string)($q['artist'] ?? ''));
    $cover  = trim((string)($q['cover']  ?? ''));

    // use same template registry
    $TEMPLATES = $this->promoTemplates();
    if (!isset($TEMPLATES[$tpl])) {
        $tpl = 'launch_story';
    }
    $meta = $TEMPLATES[$tpl];

    $basePath = getcwd() . '/module/Analytics/view/analytics/promo/' . $meta['file'];
    if (!is_file($basePath)) {
        return null;
    }

    $box         = $meta['box'];
    $mode        = $meta['mode']        ?? 'cover';
    $rotate      = (float)($meta['rotate'] ?? 0);
    $frameBorder = isset($meta['frame_border']) ? (int)$meta['frame_border'] : 6;
    $frameRadius = isset($meta['frame_radius']) ? (int)$meta['frame_radius'] : 2;

    // === base layer ===
    $base = @imagecreatefrompng($basePath);
    if (!$base) return null;
    imagesavealpha($base, true);

    // === album art ===
    $coverIm = $this->loadImageAny($cover);
    if (!$coverIm) {
        $coverIm = imagecreatetruecolor($box['w'], $box['h']);
        $bg = imagecolorallocate($coverIm, 236, 238, 240);
        imagefilledrectangle($coverIm, 0, 0, $box['w'], $box['h'], $bg);
    }

    $resized = $this->resizeIntoBox($coverIm, $box['w'], $box['h'], $mode);

    // white frame tile
    $framed = $this->buildFramedTile($resized, $box['w'], $box['h'], $frameBorder, $frameRadius);

    // paste framed tile (maybe rotated)
    $pasteRect = $box;
    if (abs($rotate) > 0.0001) {
        $transparent = imagecolorallocatealpha($framed, 0, 0, 0, 127);
        $rot = imagerotate($framed, $rotate, $transparent);
        imagesavealpha($rot, true);

        $rw = imagesx($rot); $rh = imagesy($rot);

        $rx = (int) round($box['x'] - ($rw - $box['w']) / 2);
        $ry = (int) round($box['y'] - ($rh - $box['h']) / 2);

        imagecopy($base, $rot, $rx, $ry, 0, 0, $rw, $rh);
        $pasteRect = ['x'=>$rx,'y'=>$ry,'w'=>$rw,'h'=>$rh];
        imagedestroy($rot);
    } else {
        $fx = $box['x'] - $frameBorder;
        $fy = $box['y'] - $frameBorder;
        imagecopy($base, $framed, $fx, $fy, 0, 0, imagesx($framed), imagesy($framed));
        $pasteRect = ['x'=>$fx,'y'=>$fy,'w'=>imagesx($framed),'h'=>imagesy($framed)];
    }

    imagedestroy($framed);
    imagedestroy($resized);
    imagedestroy($coverIm);

    // soft glow behind tile
    $wBase = imagesx($base); $hBase = imagesy($base);
    $overlay = imagecreatetruecolor($wBase, $hBase);
    imagesavealpha($overlay, true);
    $trans = imagecolorallocatealpha($overlay, 0, 0, 0, 127);
    imagefill($overlay, 0, 0, $trans);

    $fade  = 35;
    $glow  = imagecolorallocatealpha($overlay, 0, 0, 0, 110);
    imagefilledrectangle(
        $overlay,
        max(0, $pasteRect['x'] - $fade),
        max(0, $pasteRect['y'] - $fade),
        min($wBase, $pasteRect['x'] + $pasteRect['w'] + $fade),
        min($hBase, $pasteRect['y'] + $pasteRect['h'] + $fade),
        $glow
    );
    imagecopymerge($base, $overlay, 0, 0, 0, 0, $wBase, $hBase, 12);
    imagedestroy($overlay);

    // write png to tmp dir
    $pngOut = rtrim($tmpDir,'/').'/poster_tmp.png';
    @imagepng($base, $pngOut, 6);
    imagedestroy($base);

    if (!is_file($pngOut) || filesize($pngOut) < 1000) {
        return null;
    }
    return $pngOut;
}
/** small helper: run shell command with ~timeout seconds */
private function runWithTimeout($cmd, $timeoutSec = 20)
{
    $desc = [1 => ['pipe','w'], 2 => ['pipe','w']];
    $proc = proc_open($cmd, $desc, $pipes);
    $buf = '';
    if (!is_resource($proc)) return $buf;

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $start = time();
    while (true) {
        $buf .= stream_get_contents($pipes[1]);
        $buf .= stream_get_contents($pipes[2]);

        $status = proc_get_status($proc);
        if (!$status['running']) break;
        if ((time() - $start) > $timeoutSec) {
            proc_terminate($proc);
            break;
        }
        usleep(200000);
    }
    fclose($pipes[1]); fclose($pipes[2]);
    proc_close($proc);
    return $buf;
}

/** download external file with curl if we ever need it */
private function downloadToFile($url, $dest, $timeoutSec = 8)
{
    $ch = curl_init($url);
    $fh = fopen($dest, 'w');
    curl_setopt_array($ch, [
        CURLOPT_FILE            => $fh,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_CONNECTTIMEOUT  => $timeoutSec,
        CURLOPT_TIMEOUT         => $timeoutSec,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_USERAGENT       => 'CreativeEditorBot/1.0',
    ]);
    $ok = curl_exec($ch);
    curl_close($ch);
    fclose($fh);
    return (bool)$ok;
}

/** plain text error response back to frontend */
private function plainError($code, $msg)
{
    $r = $this->getResponse();
    $r->setStatusCode($code);
    $r->getHeaders()->addHeaderLine('Content-Type','text/plain; charset=utf-8');
    $r->setContent($msg);
    return $r;
}

/** cleanup tmp dir/files */
private function cleanupFiles(array $items)
{
    foreach ($items as $p) {
        if (is_file($p)) @unlink($p);
    }
    $last = end($items);
    if ($last && is_dir($last)) @rmdir($last);
}

  public function creativeEditorAction()
{
    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $id     = (int) $this->params()->fromQuery('id', 0);
    $tpl    = trim((string)$this->params()->fromQuery('tpl',''));
    $title  = trim((string)$this->params()->fromQuery('title',''));
    $artist = trim((string)$this->params()->fromQuery('artist',''));
    $cover  = trim((string)$this->params()->fromQuery('cover',''));

    $release = [];
    if ($id > 0) {
        $sql = "SELECT id, title, releaseArtist, upc, cover_img, label_name,
                       digitalReleaseDate, physicalReleaseDate
                FROM view_release
                WHERE id = :id
                LIMIT 1";
        $stmt = $adapter->createStatement($sql, [':id' => $id]);
        $row  = $stmt->execute()->current();

        if ($row) {
            $dateRaw = $row['digitalReleaseDate'] ?: $row['physicalReleaseDate'];
            $release = [
                'id'        => (int)$row['id'],
                'title'     => (string)$row['title'],
                'artist'    => (string)$row['releaseArtist'],
                'upc'       => (string)$row['upc'],
                'label'     => (string)($row['label_name'] ?? ''),
                'date'      => $dateRaw ? date('d M Y', strtotime($dateRaw)) : '',
                'cover'     => '/public/uploads/'.($row['cover_img'] ?: 'no-image.png'),
                'audio_url_preview' => '',
                'audio_url'         => '',
            ];

            if ($title  === '') $title  = $release['title'];
            if ($artist === '') $artist = $release['artist'];
            if ($cover  === '') $cover  = $release['cover'];
        }
    }

    // === NEW: pull audio URL from DB ===
    $audioUrl = $id > 0 ? $this->releaseAudioUrl($adapter, $id) : '';

    $poster = [
        'id'        => $id,
        'audio_url' => $audioUrl,
    ];

    $vm = new \Zend\View\Model\ViewModel([
        'poster'   => $poster,
        'release'  => $release,
        'tpl'      => $tpl,
        'q_title'  => $title,
        'q_artist' => $artist,
        'q_cover'  => $cover,
    ]);
    $vm->setTemplate('analytics/index/creative-editor.phtml');
    return $vm;
}
public function latestAttachedReleasesAction()
{
    $sl       = $this->getServiceLocator();
    $custom   = $this->CustomPlugin();
    $adapter  = $sl->get('Zend\Db\Adapter\Adapter');

    $user_id  = (int)($_SESSION['user_id'] ?? 0);
    $isStaff  = (int)($_SESSION['STAFFUSER'] ?? 0);

    // Pagination
    $page   = max(1, (int)$this->params()->fromQuery('page', 1));
    $per    = max(1, (int)$this->params()->fromQuery('per', 10));
    $offset = ($page - 1) * $per;

    // Search
    $q = trim((string)$this->params()->fromQuery('q', ''));
    $searchSql = '';
    if ($q !== '') {
        $qSafe = addslashes($q);
        $searchSql = " AND (title LIKE '%{$qSafe}%' OR releaseArtist LIKE '%{$qSafe}%' OR upc LIKE '%{$qSafe}%') ";
    }

    // Scope filter
    $cond = '';
    if ($user_id > 0 && $isStaff === 0) {
        $cond = " AND user_id='" . $user_id . "' ";
        $labelsCsv = $custom->getUserLabels($user_id);
        if (!empty($labelsCsv)) {
            $cond .= " AND labels IN (" . $labelsCsv . ") ";
        }
    }
    if ($isStaff === 1) {
        $cond .= $custom->getStaffReleaseCond();
    }

    // Window: last 60 days
    $since = date('Y-m-d', strtotime('-60 days'));

    // Count total
    $sqlCount = "SELECT COUNT(*) AS cnt
                 FROM view_release
                 WHERE status IN ('delivered','approved','live')
                 $cond
                 $searchSql
                 AND (digitalReleaseDate >= '{$since}' OR physicalReleaseDate >= '{$since}')";
    $rowCount = $adapter->query($sqlCount, [])->current();
    $total    = (int)($rowCount['cnt'] ?? 0);

    // Page rows
    $sql = "SELECT id, title, releaseArtist, upc, status, tot_tracks, cover_img,
                   digitalReleaseDate, physicalReleaseDate
            FROM view_release 
            WHERE status IN ('delivered','approved','live')
            $cond
            $searchSql
            AND (digitalReleaseDate >= '{$since}' OR physicalReleaseDate >= '{$since}')
            ORDER BY digitalReleaseDate DESC
            LIMIT $offset,$per";
    $rows = $adapter->query($sql, [])->toArray();

    $final = [];
    foreach ($rows as $r) {
        $releaseId = (int)($r['id'] ?? 0);

        // --- ENSURE PLACEHOLDER IN tbl_spotify_streams ---
        // 1) see if there is already a row for this release
        $rowStat = $adapter->query(
            "SELECT id, stream_count, monthly_listeners, last_checked
             FROM tbl_spotify_streams
             WHERE release_id = ?
             ORDER BY last_checked DESC
             LIMIT 1",
            [$releaseId]
        )->current();

        if (!$rowStat) {
            // 2) get first track info from tbl_track for context
            $trackRow = $adapter->createStatement("
                SELECT id, songName, trackArtist
                FROM tbl_track
                WHERE master_id = :rid
                ORDER BY id ASC
                LIMIT 1
            ", [':rid' => $releaseId])->execute()->current();

            $trackId    = $trackRow ? (int)$trackRow['id'] : 0;
            $trackTitle = $trackRow ? (string)$trackRow['songName'] : '';
            $trackArt   = $trackRow ? (string)$trackRow['trackArtist'] : '';

            // 3) insert placeholder row so DB always has something
            // NOTE: columns spotify_id / artist_id might NOT exist in your table.
            // we'll only touch columns we KNOW exist (adjust list to match your table schema!)
            $adapter->query(
                "INSERT INTO tbl_spotify_streams
                    (release_id, track_id, track_title, artist_name,
                     stream_count, monthly_listeners,
                     last_checked)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $releaseId,
                    $trackId,
                    $trackTitle,
                    $trackArt,
                    0, // default streams
                    0  // default listeners
                ]
            );

            // re-fetch after insert so we can show it below
            $rowStat = $adapter->query(
                "SELECT id, stream_count, monthly_listeners, last_checked
                 FROM tbl_spotify_streams
                 WHERE release_id = ?
                 ORDER BY last_checked DESC
                 LIMIT 1",
                [$releaseId]
            )->current();
        }

        // now we are guaranteed to have $rowStat (either old or placeholder)
        $streamsVal   = $rowStat ? (int)$rowStat['stream_count'] : 0;
        $listenersVal = $rowStat ? (int)$rowStat['monthly_listeners'] : 0;
        $checkedVal   = $rowStat ? (string)$rowStat['last_checked'] : null;

        // ---- build normal release row for FE ----
        $title  = trim((string)($r['title'] ?? 'Untitled'));
        $artist = trim((string)($r['releaseArtist'] ?? '—'));
        $tracks = (int)($r['tot_tracks'] ?? 0);
        $upc    = trim((string)($r['upc'] ?? ''));
        $dateDb = !empty($r['digitalReleaseDate'])
                    ? $r['digitalReleaseDate']
                    : ($r['physicalReleaseDate'] ?? '');
        $dateFmt = $dateDb ? date('d M Y', strtotime($dateDb)) : '';

        $status = ucfirst(str_replace('_',' ', (string)($r['status'] ?? '')));
        $cover  = trim((string)($r['cover_img'] ?? ''));
        if ($cover === '') {
            $cover = 'no-image.png';
        }

        $thumb     = $this->ensureThumb("public/uploads/".$cover, "public/uploads/thumb_".$cover, 250, 250);
        $cover_url = '/' . ltrim(str_replace(['\\'], ['/',], $thumb), '/');

        $final[] = [
            'id'        => $releaseId,
            'title'     => $title,
            'artist'    => $artist,
            'date'      => $dateFmt,
            'upc'       => $upc,
            'status'    => $status,
            'tracks'    => $tracks,
            'cover_url' => $cover_url,
            'view_url'  => "/analytics/poster/".$releaseId,

            // spotify numbers (now guaranteed 0+)
            'spotify_streams'   => number_format($streamsVal),
            'spotify_listeners' => number_format($listenersVal),
            'spotify_checked'   => $checkedVal,

            // FE helpers
            'stats_ready'       => 1, // we already gave values
            'stats_url'         => "/analytics/release-stats?id=".$releaseId,
        ];
    }

    return new JsonModel([
        'ok'    => true,
        'rows'  => $final,
        'total' => $total,
        'page'  => $page,
        'per'   => $per
    ]);
}


/**
 * Fetch Spotify-ish stats we MAY already have in tbl_spotify_streams.
 * If nothing stored yet, return nulls safely so FE shows "—".
 */
private function getSpotifyStats($adapter, $releaseId)
{
    $sql = "SELECT stream_count,
                   monthly_listeners,
                   last_checked
            FROM tbl_spotify_streams 
            WHERE release_id = ?
            ORDER BY last_checked DESC
            LIMIT 1";

    $row = $adapter->query($sql, [$releaseId])->current();

    if (!$row) {
        // no record yet -> all null
        return [
            'streams'   => null,
            'listeners' => null,
            'checked'   => null,
        ];
    }

    return [
        'streams'   => number_format((int)$row['stream_count']),
        'listeners' => number_format((int)$row['monthly_listeners']),
        'checked'   => $row['last_checked'],
    ];
}

/**
 * GET /analytics/release-stats?id=123
 * Returns per-release Spotify stats asynchronously (for skeleton UI).
 */
public function releaseStatsAction()
{
    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $id = (int)$this->params()->fromQuery('id', 0);
    if ($id <= 0) return new JsonModel(['ok'=>false,'error'=>'bad id']);

    $row = $adapter->query("SELECT releaseArtist FROM view_release WHERE id={$id} LIMIT 1", [])->current();
    if (!$row) return new JsonModel(['ok'=>false,'error'=>'not found']);

    $token = $this->getSpotifyAccessToken();
    if (!$token) return new JsonModel(['ok'=>false,'error'=>'spotify token unavailable']);

    // 1-day cache for whole release stats
    $cacheKey = "relstats_{$id}";
    if ($cached = $this->getCache($cacheKey)) {
        return new JsonModel(['ok'=>true,'stats_ready'=>1] + $cached);
    }

    $artist = (string)($row['releaseArtist'] ?? '');
    $stats  = $this->computeReleaseSpotifyStats($adapter, $id, $artist, $token);
    $this->setCache($cacheKey, $stats, 86400);

    return new JsonModel(['ok'=>true,'stats_ready'=>1] + $stats);
}

    /**
     * /analytics/render?tpl=story_teaser&title=...&artist=...&cover=...&dl=1
     */
    public function renderAction()
    {
        $tpl    = strtolower(trim((string)$this->params()->fromQuery('tpl', 'launch_story')));
        $title  = trim((string)$this->params()->fromQuery('title', ''));
        $artist = trim((string)$this->params()->fromQuery('artist', ''));
        $cover  = trim((string)$this->params()->fromQuery('cover', ''));
        $dl     = (int)$this->params()->fromQuery('dl', 0);

        $TEMPLATES = $this->promoTemplates();

        // fallback local registry (optional)
        $TEMPLATES = [
            'launch_story' => ['file'=>'outnow_storyteaser.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
            'outsoon_story' => ['file'=>'outsoon_storyteaser.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
            'outsoon_story2' => ['file'=>'outsoon_storyteaser2.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
            'outsoon_story1' => ['file'=>'outsoon_storyteaser1.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
          'launch_story1' => ['file'=>'outnow_storyteaser1.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
            'launch_story2' => ['file'=>'outnow_storyteaser2.png','box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0],
          'launch_story3'  => ['file'=>'outnow_storyteaser3.png',  'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'frame_border'=>6,'frame_radius'=>2],
        'outsoon_story3' => ['file'=>'outsoon_storyteaser3.png', 'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'frame_border'=>6,'frame_radius'=>2],
            'post_outnow'   => ['file'=>'post_outnow.png','box'=>['x'=>110,'y'=>285,'w'=>525,'h'=>525],'mode'=>'cover','rotate'=>-56],
            'post_outsoon'  => ['file'=>'post_outsoon.png','box'=>['x'=>110,'y'=>285,'w'=>525,'h'=>525],'mode'=>'cover','rotate'=>-56],
        ];

        if (!isset($TEMPLATES[$tpl])) $tpl = 'launch_story';
        $meta = $TEMPLATES[$tpl];

        $basePath = getcwd() . '/module/Analytics/view/analytics/promo/' . $meta['file'];
        if (!is_file($basePath)) {
            return $this->getResponse()->setStatusCode(404)->setContent('Template not found');
        }

        $box    = $meta['box'];
        $mode   = $meta['mode']   ?? 'cover';
        $rotate = (float)($meta['rotate'] ?? 0);

        // cache
        $cacheDir = getcwd() . '/data/cache/promo';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
        $cacheKey  = md5($tpl.'|'.$cover.'|'.$title.'|'.$artist.'|'.implode(',', $box).'|'.$mode.'|'.$rotate);
        $cacheFile = $cacheDir . "/$cacheKey.png";
        if (is_file($cacheFile)) return $this->servePng($cacheFile, $tpl, $title, $dl);

        // base
        $base = @imagecreatefrompng($basePath);
        if (!$base) return $this->getResponse()->setStatusCode(500)->setContent('Failed to open template');
        imagesavealpha($base, true);

        // cover
        $coverIm = $this->loadImageAny($cover);
        if (!$coverIm) {
            $coverIm = imagecreatetruecolor($box['w'], $box['h']);
            $bg = imagecolorallocate($coverIm, 236, 238, 240);
            imagefilledrectangle($coverIm, 0, 0, $box['w'], $box['h'], $bg);
        }

        // resize into box
        // resize into box
$resized = $this->resizeIntoBox($coverIm, $box['w'], $box['h'], $mode);

// === NEW: white framed tile (rounded corners) ===
// Border thickness & corner radius (px) – feel free to tweak per template
$frameBorder = isset($meta['frame_border']) ? (int)$meta['frame_border'] : 6;
$frameRadius = isset($meta['frame_radius']) ? (int)$meta['frame_radius'] : 2;

// build a framed image (white rounded rect with transparent outside)
// returns an image of size: (box.w + 2*border) x (box.h + 2*border)
$framed = $this->buildFramedTile($resized, $box['w'], $box['h'], $frameBorder, $frameRadius);

// paste (rotation optional)
$pasteRect = $box;
if (abs($rotate) > 0.0001) {
    // rotate the whole framed tile so the white border rotates together
    $transparent = imagecolorallocatealpha($framed, 0, 0, 0, 127);
    $rot = imagerotate($framed, $rotate, $transparent);
    imagesavealpha($rot, true);

    $rw = imagesx($rot); $rh = imagesy($rot);

    // center the rotated tile over the intended box
    $rx = (int) round($box['x'] - ($rw - $box['w']) / 2);
    $ry = (int) round($box['y'] - ($rh - $box['h']) / 2);

    imagecopy($base, $rot, $rx, $ry, 0, 0, $rw, $rh);
    $pasteRect = ['x'=>$rx,'y'=>$ry,'w'=>$rw,'h'=>$rh];
    imagedestroy($rot);
} else {
    // no rotation: draw framed tile offset by border, so inner image aligns with box
    $fx = $box['x'] - $frameBorder;
    $fy = $box['y'] - $frameBorder;
    imagecopy($base, $framed, $fx, $fy, 0, 0, imagesx($framed), imagesy($framed));
    $pasteRect = ['x'=>$fx,'y'=>$fy,'w'=>imagesx($framed),'h'=>imagesy($framed)];
}

// cleanups
imagedestroy($framed);


        // edge blend
        $wBase = imagesx($base); $hBase = imagesy($base);
        $overlay = imagecreatetruecolor($wBase, $hBase);
        imagesavealpha($overlay, true);
        $trans = imagecolorallocatealpha($overlay, 0, 0, 0, 127);
        imagefill($overlay, 0, 0, $trans);
        $fade  = 35;
        $glow  = imagecolorallocatealpha($overlay, 0, 0, 0, 110);
        imagefilledrectangle(
            $overlay,
            max(0, $pasteRect['x'] - $fade),
            max(0, $pasteRect['y'] - $fade),
            min($hBase, $pasteRect['y'] + $pasteRect['h'] + $fade),
            min($wBase, $pasteRect['x'] + $pasteRect['w'] + $fade),
            $glow
        );
        imagecopymerge($base, $overlay, 0, 0, 0, 0, $wBase, $hBase, 12);
        imagedestroy($overlay);

        // save + serve
        @imagepng($base, $cacheFile, 6);
        imagedestroy($base); imagedestroy($resized); imagedestroy($coverIm);
        return $this->servePng($cacheFile, $tpl, $title, $dl);
    }
/**
 * Build a white rounded-rectangle frame and place the inner image inside it.
 * Output is transparent PNG with size (innerW + 2*border) × (innerH + 2*border).
 */
private function buildFramedTile($innerIm, $innerW, $innerH, $border = 18, $radius = 26)
{
    $outerW = $innerW + 2 * $border;
    $outerH = $innerH + 2 * $border;

    $dst = imagecreatetruecolor($outerW, $outerH);
    imagesavealpha($dst, true);

    // full transparency background
    $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $trans);

    // white frame shape (rounded rect)
    $white = imagecolorallocatealpha($dst, 255, 255, 255, 0);
    $this->filledRoundedRect($dst, 0, 0, $outerW - 1, $outerH - 1, $radius, $white);

    // place inner image inside with offset = border
    imagecopy($dst, $innerIm, $border, $border, 0, 0, $innerW, $innerH);

    return $dst;
}

/**
 * Draw a filled rounded rectangle on $im from (x1,y1) to (x2,y2) with radius $r and color $col.
 */
private function filledRoundedRect($im, $x1, $y1, $x2, $y2, $r, $col)
{
    $w = $x2 - $x1 + 1;
    $h = $y2 - $y1 + 1;
    $r = max(0, min($r, (int)floor(min($w, $h) / 2)));

    // center rects
    imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $col);
    imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $col);

    // corners (filled circles)
    imagefilledellipse($im, $x1 + $r, $y1 + $r, 2*$r, 2*$r, $col); // TL
    imagefilledellipse($im, $x2 - $r, $y1 + $r, 2*$r, 2*$r, $col); // TR
    imagefilledellipse($im, $x1 + $r, $y2 - $r, 2*$r, 2*$r, $col); // BL
    imagefilledellipse($im, $x2 - $r, $y2 - $r, 2*$r, 2*$r, $col); // BR
}

    // ---------- template registry ----------
    private function promoTemplates()
{
    return [
        'launch_story'   => ['file'=>'outnow_storyteaser.png',   'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Launch','displayBottom'=>'Story','size'=>'1080×1920'],
        'outsoon_story'  => ['file'=>'outsoon_storyteaser.png',  'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Teaser','displayBottom'=>'Story','size'=>'1080×1920'],
        'outsoon_story2' => ['file'=>'outsoon_storyteaser2.png', 'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Teaser','displayBottom'=>'Story','size'=>'1080×1920'],
        'outsoon_story1' => ['file'=>'outsoon_storyteaser1.png', 'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Teaser','displayBottom'=>'Story','size'=>'1080×1920'],
        'launch_story2'  => ['file'=>'outnow_storyteaser2.png',  'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Launch','displayBottom'=>'Story','size'=>'1080×1920'],
'launch_story1'  => ['file'=>'outnow_storyteaser1.png',  'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Launch','displayBottom'=>'Story','size'=>'1080×1920'],
        // NEW v3 entries:
        'launch_story3'  => ['file'=>'outnow_storyteaser3.png',  'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Launch','displayBottom'=>'Story','size'=>'1080×1920'],
        'outsoon_story3' => ['file'=>'outsoon_storyteaser3.png', 'box'=>['x'=>125,'y'=>125,'w'=>830,'h'=>830],'mode'=>'cover','rotate'=>0, 'displayTop'=>'Teaser','displayBottom'=>'Story','size'=>'1080×1920'],

        'post_outnow'    => ['file'=>'post_outnow.png',          'box'=>['x'=>110,'y'=>285,'w'=>525,'h'=>525],'mode'=>'cover','rotate'=>-56,'displayTop'=>'Launch','displayBottom'=>'Post','size'=>'1080×1080'],
        'post_outsoon'   => ['file'=>'post_outsoon.png',         'box'=>['x'=>110,'y'=>285,'w'=>525,'h'=>525],'mode'=>'cover','rotate'=>-56,'displayTop'=>'Teaser','displayBottom'=>'Post','size'=>'1080×1080'],
    ];
}


    public function creativeTemplatesAction()
    {
        $all = $this->promoTemplates();
        $out = [];
        foreach ($all as $slug => $m) {
            $out[] = [
                'slug'   => $slug,
                'top'    => (string)($m['displayTop'] ?? ''),
                'bottom' => (string)($m['displayBottom'] ?? ''),
                'size'   => (string)($m['size'] ?? ''),
            ];
        }
        return new JsonModel(['ok'=>true,'rows'=>$out, 'count'=>count($out)]);
    }

    // ----------------- Spotify helpers + caching -----------------

    /** Basic POST (x-www-form-urlencoded) via cURL */
    private function http_post_form($url, array $fields, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => http_build_query($fields),
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 8,
            CURLOPT_TIMEOUT         => 12,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) error_log('curl POST error: '.curl_error($ch));
        curl_close($ch);
        return [$code, $body];
    }

    /** Basic GET via cURL */
    private function http_get($url, array $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET         => true,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 8,
            CURLOPT_TIMEOUT         => 12,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === false) error_log('curl GET error: '.curl_error($ch));
        curl_close($ch);
        return [$code, $body];
    }

    /** Access token using Client Credentials (cached to filesystem) */
    private function getSpotifyAccessToken()
    {
        $cid = getenv('SPOTIFY_CLIENT_ID') ?: '2428679943a34c7c8a9d94fc8b68cf8b';
        $sec = getenv('SPOTIFY_CLIENT_SECRET') ?: '1af7bee2a0bb4feba0527e5e24b313f3';
        if (!$cid || !$sec) return null;

        $cacheFile = getcwd() . '/data/cache/spotify_token.json';
        if (is_file($cacheFile)) {
            $j = json_decode(@file_get_contents($cacheFile), true);
            if (!empty($j['access_token']) && !empty($j['expires_at']) && $j['expires_at'] > time() + 20) {
                return $j['access_token'];
            }
        }

        list($code, $body) = $this->http_post_form(
            'https://accounts.spotify.com/api/token',
            ['grant_type' => 'client_credentials'],
            [
                'Authorization: Basic '.base64_encode($cid.':'.$sec),
                'Content-Type: application/x-www-form-urlencoded',
            ]
        );

        if ($code === 200) {
            $data = json_decode($body, true);
            if (!empty($data['access_token'])) {
                $out = [
                    'access_token' => $data['access_token'],
                    'expires_at'   => time() + ((int)($data['expires_in'] ?? 3600)),
                ];
                @mkdir(dirname($cacheFile), 0775, true);
                @file_put_contents($cacheFile, json_encode($out));
                return $out['access_token'];
            }
        } else {
            error_log("Spotify token HTTP $code: $body");
        }
        return null;
    }

    /** Clean track name for better matching */
    private function normalizeTitle($s){
        $s = preg_replace('~\((feat\.|featuring|with)[^)]+\)~i', '', $s);
        $s = preg_replace('~\((remaster(ed)?|radio edit|live|version|mono|stereo)[^)]+\)~i', '', $s);
        $s = preg_replace('~[-–]\s*(remaster(ed)?|radio edit|live|version).*~i', '', $s);
        $s = preg_replace('~\s+~', ' ', $s);
        return trim($s);
    }

    /** Find Spotify track id (ISRC first, fallback title+artist) */
    private function spotifyTrackIdByISRC($isrc, $title, $artist, $token)
    {
        if (!$token) return null;

        // by ISRC
        if ($isrc) {
            $url = 'https://api.spotify.com/v1/search?q=isrc:'.rawurlencode(trim($isrc)).'&type=track&limit=1';
            list($c,$b) = $this->http_get($url, ['Authorization: Bearer '.$token]);
            if ($c === 200) {
                $d = json_decode($b, true);
                if (!empty($d['tracks']['items'][0]['id'])) return $d['tracks']['items'][0]['id'];
            }
        }

        // by title+artist
        $t = $this->normalizeTitle((string)$title);
        $a = trim((string)$artist);
        if ($t) {
            $q   = $a ? sprintf('track:"%s" artist:"%s"', $t, $a) : sprintf('track:"%s"', $t);
            $url = 'https://api.spotify.com/v1/search?q='.rawurlencode($q).'&type=track&limit=5';
            list($c,$b) = $this->http_get($url, ['Authorization: Bearer '.$token]);
            if ($c === 200) {
                $d = json_decode($b, true);
                $items = $d['tracks']['items'] ?? [];
                if ($items) {
                    $bestId=null; $bestScore=-1;
                    foreach ($items as $it) {
                        $nm = $this->normalizeTitle($it['name'] ?? '');
                        $ar = $it['artists'][0]['name'] ?? '';
                        similar_text(mb_strtolower($t), mb_strtolower($nm), $p1);
                        similar_text(mb_strtolower($a), mb_strtolower($ar), $p2);
                        $score = $p1 + ($a ? $p2 : 0);
                        if ($score > $bestScore) { $bestScore=$score; $bestId=$it['id'] ?? null; }
                    }
                    return $bestId;
                }
            } else {
                error_log("Spotify search HTTP $c: $b");
            }
        }
        return null;
    }

    /** Fetch track info (popularity, explicit, markets_count, isrc, etc.) with caching */
    private function getSpotifyTrackInfo($spotifyTrackId, $token)
    {
        if (!$spotifyTrackId || !$token) return null;

        $cacheKey = 'spotify_track_'.$spotifyTrackId;
        $cached = $this->getCache($cacheKey);
        if ($cached) return $cached;

        $url = 'https://api.spotify.com/v1/tracks/'.$spotifyTrackId;
        list($c,$b) = $this->http_get($url, ['Authorization: Bearer '.$token]);
        if ($c === 200) {
            $d = json_decode($b, true);
            $out = [
                'id'             => $spotifyTrackId,
                'name'           => $d['name'] ?? '',
                'popularity'     => isset($d['popularity']) ? (int)$d['popularity'] : null,
                'isrc'           => $d['external_ids']['isrc'] ?? null,
                'preview_url'    => $d['preview_url'] ?? null,
                'album'          => $d['album']['name'] ?? '',
                'explicit'       => (bool)($d['explicit'] ?? false),
                'markets_count'  => isset($d['available_markets']) ? count($d['available_markets']) : 0,
            ];
            $this->setCache($cacheKey, $out, 3600);
            return $out;
        }
        error_log("Spotify tracks/$spotifyTrackId HTTP $c: $b");
        return null;
    }

    /** Aggregate per-release stats */
    private function computeReleaseSpotifyStats($adapter, $releaseId, $fallbackArtist, $token)
    {
        $tracks = $this->fetchReleaseTracks($adapter, $releaseId, $fallbackArtist);
        if (!$tracks) {
            return [
                'popularity_avg'   => null,
                'markets_avg'      => null,
                'explicit_any'     => null,
                'best_track'       => null,
                'streams_note'     => 'No tracks found for this release.',
                'popularity_label' => null,
                'markets_label'    => null,
            ];
        }

        $sumPop = 0; $n = 0;
        $sumMarkets = 0;
        $explicitAny = false;
        $best = ['name'=>null,'popularity'=>-1,'spotify_id'=>null];

        foreach ($tracks as $t) {
            $spId = $this->spotifyTrackIdByISRC(
                strtoupper(trim((string)($t['isrc'] ?? ''))),
                (string)($t['title'] ?? ''),
                (string)($t['artist'] ?? $fallbackArtist),
                $token
            );
            if (!$spId) continue;

            $info = $this->getSpotifyTrackInfo($spId, $token);
            if (!$info) continue;

            $pop     = (int)($info['popularity'] ?? 0);
            $markets = (int)($info['markets_count'] ?? 0);
            $exp     = (bool)($info['explicit'] ?? false);

            $sumPop     += $pop;
            $sumMarkets += $markets;
            $explicitAny = $explicitAny || $exp;
            $n++;

            if ($pop > $best['popularity']) {
                $best = [
                    'name'       => $info['name'] ?? ($t['title'] ?? ''),
                    'popularity' => $pop,
                    'spotify_id' => $info['id'] ?? $spId,
                ];
            }
        }

        if ($n === 0) {
            return [
                'popularity_avg'   => 0,
                'markets_avg'      => 0,
                'explicit_any'     => 0,
                'best_track'       => null,
                'streams_note'     => 'No matching tracks on Spotify.',
                'popularity_label' => '0 / 100',
                'markets_label'    => '0 markets',
            ];
        }

        $avgPop = (int)round($sumPop / $n);
        $avgMrk = (int)round($sumMarkets / $n);

        return [
            'popularity_avg'   => $avgPop,
            'markets_avg'      => $avgMrk,
            'explicit_any'     => $explicitAny ? 1 : 0,
            'best_track'       => $best, // {name, popularity, spotify_id}
            'streams_note'     => 'Spotify public API stream counts nahi deti; popularity score (0–100) dikhaya ja raha hai.',
            'popularity_label' => $avgPop.' / 100',
            'markets_label'    => $avgMrk.' markets',
        ];
    }

    // -------- simple file cache helpers --------
    private function getCache($key)
    {
        $cacheDir = getcwd() . '/data/cache/spotify/';
        $file = $cacheDir . md5($key) . '.json';
        if (!is_file($file)) return null;
        $j = json_decode(@file_get_contents($file), true);
        if (!$j || empty($j['expires']) || $j['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $j['value'] ?? null;
    }

    private function setCache($key, $value, $ttl = 3600)
    {
        $cacheDir = getcwd() . '/data/cache/spotify/';
        @mkdir($cacheDir, 0775, true);
        $file = $cacheDir . md5($key) . '.json';
        $obj = ['expires' => time() + $ttl, 'value' => $value];
        @file_put_contents($file, json_encode($obj));
    }

    // --------------- DB helpers ----------------
    // Tracks for release (title, isrc, artist) — mapped to your schema
    private function fetchReleaseTracks($adapter, $releaseId, $fallbackArtist = '')
    {
        try {
            // check if `isrc` column exists (optional)
            $hasIsrc = false;
            try {
                $cols = [];
                $res = $adapter->createStatement("SHOW COLUMNS FROM tbl_track")->execute();
                foreach ($res as $c) { $cols[] = $c['Field']; }
                $hasIsrc = in_array('isrc', $cols, true);
            } catch (\Throwable $e) {}

            // map your columns: master_id -> release, songName -> title, trackArtist -> artist
            $sql = "
                SELECT
                    songName    AS title,
                    ".($hasIsrc ? "IFNULL(isrc,'')" : "''")." AS isrc,
                    trackArtist AS artist
                FROM tbl_track
                WHERE master_id = :rid
            ";

            $stmt = $adapter->createStatement($sql, [':rid' => $releaseId]);
            $result = $stmt->execute();

            $out = [];
            foreach ($result as $row) {
                $out[] = [
                    'title'  => (string)($row['title'] ?? ''),
                    'isrc'   => (string)($row['isrc'] ?? ''),  // may be empty; title+artist search chalega
                    'artist' => (string)($row['artist'] ?? $fallbackArtist),
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            error_log("fetchReleaseTracks error: ".$e->getMessage());
            return [];
        }
    }

    // ---------------- imaging utils -----------------
    private function resizeIntoBox($im, $bw, $bh, $mode='cover')
    {
        $sw = imagesx($im); $sh = imagesy($im);
        if ($sw <= 0 || $sh <= 0) return $im;

        $scaleCover   = max($bw / $sw, $bh / $sh);
        $scaleContain = min($bw / $sw, $bh / $sh);

        if ($mode === 'contain') {
            $tw = (int)round($sw * $scaleContain);
            $th = (int)round($sh * $scaleContain);
            $dst = imagecreatetruecolor($bw, $bh);
            imagesavealpha($dst, true);
            $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $trans);
            imagecopyresampled($dst, $im, (int)(($bw-$tw)/2), (int)(($bh-$th)/2), 0, 0, $tw, $th, $sw, $sh);
            return $dst;
        }

        $tw = (int)round($sw * $scaleCover);
        $th = (int)round($sh * $scaleCover);
        $tmp = imagecreatetruecolor($tw, $th);
        imagesavealpha($tmp, true);
        $trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
        imagefill($tmp, 0, 0, $trans);
        imagecopyresampled($tmp, $im, 0, 0, 0, 0, $tw, $th, $sw, $sh);

        $dst = imagecreatetruecolor($bw, $bh);
        imagesavealpha($dst, true);
        $trans2 = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $trans2);
        $sx = (int)(($tw - $bw)/2);
        $sy = (int)(($th - $bh)/2);
        imagecopy($dst, $tmp, 0, 0, $sx, $sy, $bw, $bh);
        imagedestroy($tmp);
        return $dst;
    }

    private function servePng($file, $tpl, $title, $dl)
    {
        $resp = $this->getResponse();
        $resp->getHeaders()->addHeaderLine('Content-Type', 'image/png');
        if ($dl) {
            $name = $tpl . '-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($title ?: 'creative')) . '.png';
            $resp->getHeaders()->addHeaderLine('Content-Disposition', 'attachment; filename="'.$name.'"');
        }
        $resp->setContent(file_get_contents($file));
        return $resp;
    }

    private function loadImageAny($src)
    {
        if ($src === '') return null;

        if (preg_match('~^https?://~i', $src)) {
            $ctx = stream_context_create([
                'http' => ['timeout' => 8],
                'ssl'  => ['verify_peer'=>false, 'verify_peer_name'=>false],
            ]);
            $blob = @file_get_contents($src, false, $ctx);
            return $blob ? @imagecreatefromstring($blob) : null;
        }

        $path = ($src[0] === '/') ? (getcwd().$src) : $src;
        if (!is_file($path)) return null;

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'png')  return @imagecreatefrompng($path);
        if ($ext === 'gif')  return @imagecreatefromgif($path);
        if ($ext === 'jpg' || $ext === 'jpeg') return @imagecreatefromjpeg($path);

        return @imagecreatefromstring(@file_get_contents($path)) ?: null;
    }

    /** GET /analytics/poster/:id — simple poster/creative page */
    public function posterAction()
{
    $sl      = $this->getServiceLocator();
    $custom  = $this->CustomPlugin();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $isStaff = (int)($_SESSION['STAFFUSER'] ?? 0);
    $id      = (int)$this->params()->fromRoute('id', 0);

    if ($id <= 0) return $this->notFoundAction();

    $cond = " id = {$id} ";
    if ($user_id > 0 && $isStaff === 0) {
        $cond .= " AND user_id = '{$user_id}' ";
        $labelsCsv = $custom->getUserLabels($user_id);
        if (!empty($labelsCsv)) {
            $cond .= " AND labels IN (" . $labelsCsv . ") ";
        }
    }
    if ($isStaff === 1) {
        $cond .= $custom->getStaffReleaseCond();
    }

    $sql  = "SELECT id, title, releaseArtist, upc, status, tot_tracks, cover_img,
                    digitalReleaseDate, physicalReleaseDate, label_name
             FROM view_release WHERE {$cond} LIMIT 1";
    $row  = $adapter->query($sql, [])->current();
    if (!$row) return $this->notFoundAction();

    // ---- COVER URL ----
    $cover    = $row['cover_img'] ?: 'no-image.png';
    $thumb    = "public/uploads/".$cover;
    $coverUrl = '/' . ltrim(str_replace('\\','/',$thumb), '/');

    $dateRaw = $row['digitalReleaseDate'] ?: $row['physicalReleaseDate'];
    $dateFmt = $dateRaw ? date('d M Y', strtotime($dateRaw)) : '';

    // =========================================
    // 1) FETCH TRACKS FOR THIS RELEASE
    // =========================================
    $tracksRows = $adapter->createStatement("
        SELECT id, songName AS title, trackArtist AS artists_raw
        FROM tbl_track
        WHERE master_id = :rid
        ORDER BY volume ASC, order_id ASC, id ASC
    ", [':rid' => $id])->execute();

    $tracks = [];
    $nameBucket = []; // unique artist names (lowercase => original)
    foreach ($tracksRows as $t) {
        $artistsRaw = trim((string)($t['artists_raw'] ?? ''));
        $names = [];
        if ($artistsRaw !== '') {
            $names = array_values(array_filter(array_map('trim', explode(',', $artistsRaw))));
            foreach ($names as $nm) { if ($nm !== '') $nameBucket[mb_strtolower($nm)] = $nm; }
        }
        $tracks[] = [
            'id'      => (int)$t['id'],
            'title'   => (string)$t['title'],
            'artists' => $names, // names for now; will convert to {id,name,image_url}
        ];
    }

    // =========================================
    // 2) RESOLVE ARTIST NAMES -> tbl_artist IDs
    // =========================================
    $artistMap = []; // lcname => ['id'=>..,'name'=>..,'image_url'=>..]
    if (!empty($nameBucket)) {
        $place = implode(',', array_fill(0, count($nameBucket), '?'));
        $lookup = $adapter->createStatement(
            "SELECT id, name, image_url FROM tbl_artist WHERE name IN ($place)"
        );
        $lookupRes = $lookup->execute(array_values($nameBucket));
        foreach ($lookupRes as $ar) {
            $lc = mb_strtolower($ar['name']);
            $artistMap[$lc] = [
                'id'        => (int)$ar['id'],
                'name'      => (string)$ar['name'],
                'image_url' => (string)($ar['image_url'] ?? ''),
            ];
        }
    }

    // convert tracks[*].artists (names) → array of {id,name,image_url}
    foreach ($tracks as &$t) {
        $out = [];
        foreach ($t['artists'] as $nm) {
            $hit = $artistMap[mb_strtolower($nm)] ?? null;
            if ($hit) {
                $out[] = $hit;
            } else {
                $out[] = ['id'=>0,'name'=>$nm,'image_url'=>''];
            }
        }
        $t['artists'] = $out;
    }
    unset($t);

    // =========================================
    // 3) ALSO TRY PRIMARY RELEASE ARTIST ID (optional)
    // =========================================
    $releaseArtistRow = null;
    $ra = trim((string)($row['releaseArtist'] ?? ''));
    if ($ra !== '') {
        $releaseArtistRow = $adapter->query(
            "SELECT id, name, image_url FROM tbl_artist WHERE name = ? LIMIT 1",
            [$ra]
        )->current();
    }

    $vm = new ViewModel([
        'release' => [
            'id'     => (int)$row['id'],
            'title'  => (string)$row['title'],
            'artist' => (string)$row['releaseArtist'],
            'artist_id'    => $releaseArtistRow ? (int)$releaseArtistRow['id'] : 0,
            'artist_image' => $releaseArtistRow ? (string)$releaseArtistRow['image_url'] : '',
            'upc'    => (string)$row['upc'],
            'status' => ucfirst(str_replace('_',' ',(string)$row['status'])),
            'tracks' => (int)$row['tot_tracks'],
            'date'   => $dateFmt,
            'label'  => (string)($row['label_name'] ?? ''),
            'cover'  => $coverUrl,
        ],
        // 👇 this powers your modal: $this->tracks is now available in the view
        'tracks'  => $tracks,
    ]);
    $vm->setTemplate('analytics/fresh/poster.phtml');
    return $vm;
}

    /** Util: create/use thumbnail and return its path */
    private function ensureThumb($fullPath, $thumbPath, $w=250, $h=250)
    {
        if (is_file($thumbPath)) return $thumbPath;

        if (!is_file($fullPath)) {
            $fallback = "public/uploads/no-image.png";
            return is_file($fallback) ? $fallback : $fullPath;
        }

        if (function_exists('imagecreatetruecolor')) {
            try {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                switch ($ext) {
                    case 'jpg':
                    case 'jpeg': $src = @imagecreatefromjpeg($fullPath); break;
                    case 'png':  $src = @imagecreatefrompng($fullPath);  break;
                    case 'gif':  $src = @imagecreatefromgif($fullPath);  break;
                    default:     $src = @imagecreatefromstring(file_get_contents($fullPath));
                }
                if ($src) {
                    $dst = imagecreatetruecolor($w, $h);
                    $ow = imagesx($src); $oh = imagesy($src);
                    $srcRatio = $ow / $oh; $dstRatio = $w / $h;
                    if ($srcRatio > $dstRatio) {
                        $nh = $oh; $nw = (int)($oh * $dstRatio);
                        $sx = (int)(($ow - $nw) / 2); $sy = 0;
                    } else {
                        $nw = $ow; $nh = (int)($ow / $dstRatio);
                        $sx = 0; $sy = (int)(($oh - $nh) / 2);
                    }
                    imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $w, $h, $nw, $nh);
                    $dir = dirname($thumbPath);
                    if (!is_dir($dir)) @mkdir($dir, 0775, true);
                    @imagejpeg($dst, $thumbPath, 88);
                    imagedestroy($src); imagedestroy($dst);
                    if (is_file($thumbPath)) return $thumbPath;
                }
            } catch (\Exception $e) {}
        }

        return $fullPath;
    }
}
