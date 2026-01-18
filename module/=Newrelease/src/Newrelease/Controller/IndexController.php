<?php
namespace Newrelease\Controller;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\View\Model\JsonModel;
use Zend\Db\Adapter\Adapter;
use Application\Service\AppleMusic;
class IndexController extends AbstractActionController
{
  
    protected $studentTable;
    public function indexAction()
    {
		$customObj = $this->CustomPlugin();
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$request = $this->getRequest();
		
		$user_artist = $customObj->getUserArtist($adapter);
		$user_artist_cnt = 0;
		$user_artist_name = '';
		
		$user_artist_name = $user_artist;
		
		if($user_artist != '')
		{
			$user_artist = explode(',',$user_artist);
			$user_artist_name = $user_artist[0];
			$user_artist_cnt = count($user_artist);
		}
			
		if($_GET['edit'] > 0)
		{
			
			$iID = $request->getPost("KEY_ID");
			
			$projectTable = new TableGateway('tbl_release', $adapter);
			$rowset = $projectTable->select(array("id='".$_GET['edit']."'"));
			$rowset = $rowset->toArray();
			
			
			
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$labels = explode(',',$labels);
			if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0'  || $_SESSION['STAFFUSER'] == '1')
			{
				$SubTitle = '';
				if(trim($rowset[0]['version']) != '')
				{
					$SubTitle = '<small>('.$rowset[0]['version'].')</small>';
				}
				$viewModel= new ViewModel(array(
				'Title' => $rowset[0]['title'],
				'SubTitle' => $SubTitle,
				'import_flag' => $rowset[0]['import_flag'],
				'STATUS' => $rowset[0]['status'],
				'rejected_flag' => $rowset[0]['rejected_flag'],
				'reject_reason' => $rowset[0]['reject_reason'],
				'user_artist_cnt' => $user_artist_cnt,
				'user_artist_name' => $user_artist_name
				));
				return   $viewModel;
			}
			else
			{
				header("location: dashboard");
				exit;
			}
		}
		$viewModel= new ViewModel(array(
		'user_artist_cnt' => $user_artist_cnt,
		'user_artist_name' => $user_artist_name
		));
		return   $viewModel;
    }
	public function uploadsAction()
    {
    }
  
 

	public function uploadaudioAction()
{
    // Force JSON
    header('Content-Type: application/json; charset=utf-8');

    $request = $this->getRequest();
    if (!$request->isPost() || empty($_FILES['audio_upload'])) {
        echo json_encode([
            'status' => 'ERR',
            'msg'    => 'No file uploaded.'
        ]);
        exit;
    }

    /** @var \Zend\Db\Adapter\Adapter $adapter */
    $serviceLocator = $this->getServiceLocator();
    $adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');

    $file     = $_FILES['audio_upload'];
    $origName = $file['name'] ?? '';
    $ext      = strtoupper(pathinfo($origName, PATHINFO_EXTENSION));

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'ERR',
            'msg'    => 'Upload failed (PHP error code: '.($file['error'] ?? 'unknown').').'
        ]);
        exit;
    }

    // Track/Release row (to enforce old extension if already exists)
    $trackId = (int)($this->params()->fromPost('track_id', $this->params()->fromQuery('track_id', 0)));
    $projectTable = new \Zend\Db\TableGateway\TableGateway('tbl_release', $adapter);
    $rowset = $projectTable->select(['id' => $trackId])->toArray();

    // If an audio_file already exists, only allow the same extension
    if (!empty($rowset) && !empty($rowset[0]['audio_file'])) {
        $oldExt = strtoupper(pathinfo($rowset[0]['audio_file'], PATHINFO_EXTENSION));
        if ($ext !== $oldExt) {
            echo json_encode([
                'status'      => 'NO_OK',
                'msg'         => 'Allow only '.$oldExt.' audio file.',
                'format_info' => '<p style="color:#f00;">Mismatched extension. Expected '.$oldExt.', got '.$ext.'.</p>',
            ]);
            exit;
        }
    } else {
        // Otherwise only WAV or FLAC are allowed
        if (!in_array($ext, ['WAV', 'FLAC'], true)) {
            echo json_encode([
                'status'      => 'NO_OK',
                'msg'         => 'Allow only WAV or FLAC audio file.',
                'format_info' => '<p style="color:#f00;">The file format is not supported.</p>',
            ]);
            exit;
        }
    }

    // ---------- ffprobe (safe) ----------
    $ffprobeBin = is_executable('/usr/bin/ffprobe') ? '/usr/bin/ffprobe' : 'ffprobe';
    $cmd = escapeshellcmd($ffprobeBin)
         .' -v error -select_streams a:0 -show_streams -show_format -print_format json -i '
         .escapeshellarg($file['tmp_name']);

    $output = [];
    $returnCode = 0;
    exec($cmd, $output, $returnCode);
    $json = json_decode(implode("\n", $output), true);

    if ($returnCode !== 0 || empty($json) || empty($json['streams'][0])) {
        echo json_encode([
            'status'      => 'WRONG_FORMAT',
            'msg'         => 'Could not read audio stream (ffprobe).',
            'format_info' => '<p style="color:#f00;">Invalid or unreadable audio file.</p>',
        ]);
        exit;
    }

    // ---------- Extract fields ----------
    $stream       = $json['streams'][0];
    $format       = $json['format'] ?? [];
    $codec        = strtolower($stream['codec_name'] ?? '');
    $channels     = (int)($stream['channels'] ?? 0);
    $bitDepth     = (int)($stream['bits_per_sample'] ?? 0);
    $sampleRate   = (int)($stream['sample_rate'] ?? 0);
    $bitRate      = (int)($stream['bit_rate'] ?? 0); // may be missing for PCM
    $formatName   = strtoupper($format['format_name'] ?? 'UNKNOWN');
    $durationRaw  = isset($format['duration']) ? (string)$format['duration'] : '0';
    $durationNice = method_exists($this, 'format_duration')
                    ? $this->format_duration($durationRaw)
                    : $durationRaw.'s';

    // Helper: expected PCM bitrate = sr * bitDepth * channels (bps)
    $expectedPcmBr = ($sampleRate && $bitDepth && $channels) ? ($sampleRate * $bitDepth * $channels) : 0;
    $within = function($val, $target, $pct = 0.05) {
        if (!$val || !$target) return true; // if missing, don't fail strictly
        $lo = (int)floor($target * (1 - $pct));
        $hi = (int)ceil($target * (1 + $pct));
        return ($val >= $lo && $val <= $hi);
    };

    // ---------- Validate ----------
    $isValid = false;

    // WAV: only uncompressed PCM: pcm_s16le (16-bit/44.1kHz stereo) or pcm_s24le (24-bit, 44.1–192kHz stereo)
    if ($ext === 'WAV') {
        $isPcm16 = ($codec === 'pcm_s16le' && $bitDepth === 16 && $sampleRate === 44100 && $channels === 2);
        $isPcm24 = ($codec === 'pcm_s24le' && in_array($sampleRate, [44100,48000,88200,96000,192000], true) && $bitDepth === 24 && $channels === 2);

        // Disallow ADPCM/MP3-in-WAV/etc. Strict PCM gate + bitrate sanity (if present)
        if (($isPcm16 || $isPcm24) && $within($bitRate, $expectedPcmBr)) {
            $isValid = true;
        }
    }

    // FLAC: 16/24-bit, 44.1–192kHz, stereo (bitrate is VBR, so no strict check)
    if (!$isValid && $ext === 'FLAC' && $codec === 'flac') {
        if (in_array($bitDepth, [16, 24], true)
            && in_array($sampleRate, [44100,48000,88200,96000,192000], true)
            && $channels === 2) {
            $isValid = true;
        }
    }

    // ---------- Save or reject ----------
    if ($isValid) {
        $infoLine = sprintf(
            '%s<br>%s (%s) - %dbits - %dHz - %dch',
            htmlspecialchars($durationNice, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($formatName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($codec, ENT_QUOTES, 'UTF-8'),
            $bitDepth,
            $sampleRate,
            $channels
        );

        $newName   = date('YmdHis').mt_rand(1, 100).'.'.$ext;
        $destDir   = 'public/uploads/audio';
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }
        $destPath  = $destDir.'/'.$newName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode([
                'status'   => 'ERR',
                'message1' => 'Unable to save file![signature]',
                'format_info' => '<p>'.$infoLine.'</p>',
            ]);
            exit;
        }

        echo json_encode([
            'status'     => 'OK',
            'file_name'  => $newName,
            'format_info'=> '<p>'.$infoLine.'</p>',
        ]);
        exit;

    } else {
        // Build human message describing what we saw
        $seen = sprintf(
            '%s (%s) - %sbits - %sHz - %sch',
            strtoupper($formatName),
            $codec ?: 'unknown',
            $bitDepth ?: '??',
            $sampleRate ?: '??',
            $channels ?: '??'
        );

        echo json_encode([
            'status'      => 'WRONG_FORMAT',
            'msg'         => 'Audio must be WAV (PCM 16-bit/44.1kHz stereo or PCM 24-bit 44.1–192kHz stereo) '
                            .'or FLAC (16/24-bit, 44.1–192kHz, stereo).',
            'format_info' => '<p style="color:#f00;">Invalid:<br>'.$seen.'</p>',
        ]);
        exit;
    }
}

	// Function to format duration into HH:MM:SS format
public function format_duration($duration) {
    return gmdate("H:i:s", (int)$duration);
}
	public function updateisrcAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$trackTable = new TableGateway('tbl_track', $adapter);
		
		$aData = json_decode($request->getPost("FORM_DATA"));
		$aData = (array)$aData;
		$iMasterID=$aData['MASTER_KEY_ID'];
		unset($aData['MASTER_KEY_ID']);
		
		$duplicate=false;
		foreach($aData as $key => $value)
		{
			if(strstr($key,"generateByPrime_"))
			{
				$track_id = explode('_',$key);
				$track_id = $track_id[1];
				$iData=array();
				$iData['generate_isrc']=$aData[$key];
				if($value == '0')
				{
					$iData['isrc']=$aData['isrc_'.$track_id];
					$iData['isrc']=trim($iData['isrc']);
					
					$rowset = $trackTable->select(array("id !='".$track_id."' and isrc like '%".$iData['isrc']."%' "));
					$rowset = $rowset->toArray();
					
					if(count($rowset) > 0)
					{
						$iData['isrc']='';
						$duplicate=true;
					}
				}
				else
				{
					$iData['isrc']='';
				}
				
				$trackTable->update($iData,array("id='".$track_id."'"));
			}
		}
		if($duplicate)
			$result['DBStatus'] = 'EXIST';
		else
			$result['DBStatus'] = 'OK';
		$result = json_encode($result);
        echo $result;
        exit;
	}
	public function trackAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$user_artist = $customObj->getUserArtist($adapter);
		$user_artist_cnt = 0;
		$user_artist_name = '';
		
		$user_artist_name = $user_artist;
		
		if($user_artist != '')
		{
			$user_artist = explode(',',$user_artist);
			$user_artist_name = $user_artist[0];
			$user_artist_cnt = count($user_artist);
		}
		
		$releaseTable = new TableGateway('tbl_release', $adapter);
		$rowset = $releaseTable->select(array("id='".$_GET['edit']."'"));
		$rowset = $rowset->toArray();
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0'  || $_SESSION['STAFFUSER'] == '1')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
		
		$trackTable = new TableGateway('tbl_track', $adapter);
		$rowset2 = $trackTable->select(array("master_id='".$_GET['edit']."' group by volume order by volume asc"));
		$rowset2 = $rowset2->toArray();
		$TRACK_DATA='';
		
		if(count($rowset2) > 0)
		{
			foreach($rowset2 as $row2)
			{
				$TRACK_DATA.='<div class="volume-header clearfix" rel="'.$row2['volume'].'">
								<h4 style="float: left;">Volume '.$row2['volume'].'</h4>
								<div style="float: left; margin-left: 30px; margin-top: 5px;">
								<a class="btn btn-sm btn-default addTrackLink addNewTrackLink" volume="'.$row2['volume'].'"  href="javascript:;">
								<span class="glyphicon glyphicon-plus"></span> Add track</a></div>
							</div>			
								<ul id="release-tracks-volume-'.$row2['volume'].'" class="volume ui-sortable"  data-volume="'.$row2['volume'].'">';
								
				$rowset3 = $trackTable->select(array("master_id='".$_GET['edit']."' and Volume='".$row2['volume']."' and order_id != 0 order by order_id asc"));
				$rowset3 = $rowset3->toArray();
				foreach($rowset3 as $row3)
				{
					if($row3['songName'] == '')
						$row3['songName']='New Track';
					$TRACK_DATA.='<li class="track" id="track_'.$row3['id'].'">
											<div class="row">	
												<div class="col-md-1">
													<span class="glyphicon glyphicon-move handle"></span>&nbsp;&nbsp;&nbsp;&nbsp;
													<span class="glyphicon glyphicon-music"></span>&nbsp;&nbsp;&nbsp;&nbsp;
												</div>
												<div class="col-md-3">
													<span class="trackNumber">'.$row3['order_id'].'</span>.&nbsp;<span class="trackName">'.$row3['songName'].'</span>
													<div class="trackIsrc text-muted" style="display:block">'.$row3['isrc'].'</div>
												</div>
												<div class="col-md-2">'.$row3['trackArtist'].'</div>
												<div class="col-md-6" style="text-align: right;">
														<a track_id="'.$row3['id'].'" onclick="return false;" class="btn btn-sm btn-default editTrackLink">
														<span class="glyphicon glyphicon-pencil"></span>&nbsp;Edit								</a>&nbsp;
													<a track_id="'.$row3['id'].'" class="btn btn-sm btn-default deleteTrackLink" onclick="return false;"  style="">
														<span class="glyphicon glyphicon-remove"></span>&nbsp;Delete								</a>
													<a track_id="'.$row3['id'].'" class="btn btn-sm btn-default moveTrackLink" onclick="return false;" data-placement="left" href="#" data-original-title="" title="">
														<span class="glyphicon glyphicon-arrow-right"></span>&nbsp;Move to another volume								</a>
												</div>
											</div>
									</li>';
				}
				$TRACK_DATA.='</ul>';				
			}
		}
		else
		{
			$TRACK_DATA='<div class="volume-header clearfix" rel="1">
								<h4 style="float: left;">Volume 1</h4>
								<div style="float: left; margin-left: 30px; margin-top: 5px;">
								<a class="btn btn-sm btn-default addTrackLink addNewTrackLink" volume="1"  href="javascript:;">
								<span class="glyphicon glyphicon-plus"></span> Add track</a></div>
							</div>			
							<ul id="release-tracks-volume-1" class="volume ui-sortable"  data-volume="1"></ul>';
		}
		$rowset[0]['version_head'] ='';
		if(trim($rowset[0]['version']) != '')
		{
			$rowset[0]['version_head'] = '<small>('.$rowset[0]['version'].')</small>';
		}
				
		
		$viewModel= new ViewModel(array(
		
			'INFO' => $rowset[0],
			'TRACK_DATA' =>$TRACK_DATA,
			'user_artist_cnt' => $user_artist_cnt,
			'user_artist_name' => $user_artist_name
			
        ));
		
		
		return   $viewModel;
    }
	public function priceAction()
    {
		
    }
  public function appleDebugAction() {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // 1) config + key checks
        $cfg = $this->getServiceLocator()->get('config')['appleMusic'] ?? [];
        $keyPath = $cfg['private_key_path'] ?? '(unset)';
        $exists  = is_file($keyPath);
        $read    = $exists && is_readable($keyPath);

        // 2) token generate
        /** @var \Application\Service\AppleMusic $am */
        $am    = $this->getServiceLocator()->get(\Application\Service\AppleMusic::class);
        $token = $am->getDevToken();              // <-- yahin पर OpenSSL/JWT issue पकड़ेगा
        $tokOK = is_string($token) && strlen($token) > 20;

        // 3) tiny search (HTTP test)
        $sf   = $this->resolveStorefront(null);
        $rows = $this->appleSearchCore('artist', 'A R Rahman', 1, $sf);

        echo json_encode([
            'key_path'      => $keyPath,
            'key_exists'    => $exists,
            'key_readable'  => $read,
            'team_id'       => ($cfg['team_id'] ?? null),
            'key_id'        => ($cfg['key_id'] ?? null),
            'storefront'    => $sf,
            'token_ok'      => $tokOK,
            'result_count'  => is_array($rows) ? count($rows) : -1,
        ]);
    } catch (\Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
// ---- Storefront mapping (config + sane defaults merge) ----
private function storefrontMap(): array {
    $cfg = $this->getServiceLocator()->get('config');
    $map = $cfg['appleMusic']['storefront_alias_map'] ?? [];

    // defaults (only if missing in config)
    $defaults = [
        'india'=>'in','in'=>'in',
        'pakistan'=>'pk','pk'=>'pk',
        'uae'=>'ae','united arab emirates'=>'ae','dubai'=>'ae','ae'=>'ae',
        'saudi'=>'sa','saudi arabia'=>'sa','ksa'=>'sa','sa'=>'sa',
        'uk'=>'gb','united kingdom'=>'gb','england'=>'gb','gb'=>'gb',
        'us'=>'us','usa'=>'us','united states'=>'us','america'=>'us',
        'canada'=>'ca','ca'=>'ca','australia'=>'au','au'=>'au',
        'singapore'=>'sg','sg'=>'sg','malaysia'=>'my','my'=>'my',
        'indonesia'=>'id','id'=>'id','philippines'=>'ph','ph'=>'ph',
        'thailand'=>'th','th'=>'th','vietnam'=>'vn','vn'=>'vn',
        'japan'=>'jp','jp'=>'jp','korea'=>'kr','south korea'=>'kr','kr'=>'kr',
        'germany'=>'de','de'=>'de','france'=>'fr','fr'=>'fr',
        'italy'=>'it','it'=>'it','spain'=>'es','es'=>'es',
        'netherlands'=>'nl','nl'=>'nl','sweden'=>'se','se'=>'se',
        'norway'=>'no','no'=>'no','denmark'=>'dk','dk'=>'dk','poland'=>'pl','pl'=>'pl',
        'nigeria'=>'ng','ng'=>'ng','south africa'=>'za','za'=>'za',
        'mexico'=>'mx','mx'=>'mx','brazil'=>'br','br'=>'br','egypt'=>'eg','eg'=>'eg',
    ];
    return $map + $defaults;
}

private function userCountryFromDb(): ?string {
    if (empty($_SESSION['user_id'])) return null;
    /** @var Adapter $adapter */
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $stmt = $adapter->createStatement('SELECT country FROM tbl_staff WHERE id = ?', [(int)$_SESSION['user_id']]);
    $row  = $stmt->execute()->current();
    return $row['country'] ?? null;
}

private function resolveStorefront(?string $sfParam): string {
    $cfg     = $this->getServiceLocator()->get('config');
    $mode    = $cfg['appleMusic']['storefront_mode']   ?? 'auto';   // 'auto' | 'global'
    $global  = $cfg['appleMusic']['global_storefront'] ?? 'in';     // fallback
    $aliases = $this->storefrontMap();

    // normalize helper
    $pick = function($v) use ($aliases){
        $k = strtolower(trim((string)$v));
        return $aliases[$k] ?? '';
    };

    if ($mode === 'global') {
        return $global;
    }

    // AUTO: query ?sf=  -> user country -> global
    if ($sf = $pick($sfParam))       return $sf;
    if ($sf = $pick($this->userCountryFromDb())) return $sf;
    return $global;
}

  // ---- Apple catalog search (artist|track) -> unified result items ----
private function appleSearchCore(string $type, string $term, int $limit, string $sf): array {
    // Map UI type -> Apple 'types' param
    $t = strtolower($type);
    $appleTypes = ($t === 'track') ? 'songs' : 'artists';

    /** @var AppleMusic $am */
    $am = $this->getServiceLocator()->get(AppleMusic::class);
    $token = $am->getDevToken();

    $url = sprintf(
        'https://api.music.apple.com/v1/catalog/%s/search?term=%s&types=%s&limit=%d',
        rawurlencode($sf), rawurlencode($term), $appleTypes, max(1, min($limit, 25))
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$token, 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 12,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) return [];

    $json = json_decode($body, true);
    $bucket = ($t === 'track')
        ? ($json['results']['songs']['data'] ?? [])
        : ($json['results']['artists']['data'] ?? []);

    $out = [];
    foreach ($bucket as $d) {
        $attr = $d['attributes'] ?? [];
        $art  = $attr['artwork']['url'] ?? null;
        if ($art) $art = str_replace(['{w}','{h}'], [80,80], $art);

        if ($t === 'track') {
            // songs
            $name   = $attr['name'] ?? '';
            $artist = $attr['artistName'] ?? '';
            $label  = trim($name . ($artist ? ' — ' . $artist : ''));
            $url    = $attr['url'] ?? null;
        } else {
            // artists
            $label = $attr['name'] ?? '';
            $url   = $attr['url'] ?? null;
        }

        $out[] = [
            'id'    => $d['id'] ?? null,
            'name'  => $label,
            'url'   => $url,
            'art'   => $art,
            'type'  => $t, // for UI
        ];
    }
    return $out;
}
// GET /apple/search?q=Arijit&type=artist|track&limit=8&sf=uk
public function appleSearchAction(){
    $q     = trim($this->params()->fromQuery('q',''));
    $type  = strtolower($this->params()->fromQuery('type','artist')); // artist|track
    $limit = (int)$this->params()->fromQuery('limit', 8);
    $sfP   = $this->params()->fromQuery('sf', null);

    if ($q === '') return new JsonModel(['status'=>'ERROR','message'=>'Empty query']);

    $sf   = $this->resolveStorefront($sfP);
    $rows = $this->appleSearchCore($type, $q, $limit, $sf);

    return new JsonModel(['status'=>'OK','storefront_used'=>$sf,'data'=>$rows]);
}

// POST /apple/link (type=artist|track, id=<entityId>, apple_id, apple_url)
public function linkAppleAction(){
    $req = $this->getRequest();
    if(!$req->isPost()) return new JsonModel(['status'=>'ERROR','message'=>'POST only']);

    $entityType = strtolower($this->params()->fromPost('type','artist')); // artist|track
    $entityId   = (int)$this->params()->fromPost('id', 0);
    $appleId    = trim($this->params()->fromPost('apple_id',''));
    $appleUrl   = trim($this->params()->fromPost('apple_url',''));

    if(!$entityId || !$appleId || !$appleUrl){
        return new JsonModel(['status'=>'ERROR','message'=>'Missing fields']);
    }

    // Basic sanitize
    if (!preg_match('~^[0-9]+$~', $appleId)) {
        return new JsonModel(['status'=>'ERROR','message'=>'Bad apple_id']);
    }

    /** @var Adapter $adapter */
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // collision check (optional but useful)
    if ($entityType === 'artist') {
        $other = $adapter->createStatement(
            'SELECT id,name FROM tbl_artist WHERE apple_id = ? AND id <> ? LIMIT 1',
            [$appleId, $entityId]
        )->execute()->current();
        if ($other) {
            return new JsonModel(['status'=>'CONFLICT','message'=>'Apple ID already linked to another artist','other'=>$other]);
        }
        $sql  = 'UPDATE tbl_artist SET apple_id = ?, apple_url = ? WHERE id = ?';
        $adapter->createStatement($sql, [$appleId, $appleUrl, $entityId])->execute();
    } elseif ($entityType === 'track') {
        $other = $adapter->createStatement(
            'SELECT id,songName FROM tbl_track WHERE apple_id = ? AND id <> ? LIMIT 1',
            [$appleId, $entityId]
        )->execute()->current();
        if ($other) {
            return new JsonModel(['status'=>'CONFLICT','message'=>'Apple ID already linked to another track','other'=>$other]);
        }
        $sql  = 'UPDATE tbl_track SET apple_id = ?, apple_url = ? WHERE id = ?';
        $adapter->createStatement($sql, [$appleId, $appleUrl, $entityId])->execute();
    } else {
        return new JsonModel(['status'=>'ERROR','message'=>'Invalid type']);
    }

    return new JsonModel(['status'=>'OK','message'=>'Apple Music linked']);
}


  // ========== ARTIST LOOKUP (LOCAL) ==========
public function lookupArtistByNameAction()
{
    $name = trim((string)$this->params()->fromPost('name',
                   $this->params()->fromQuery('name','')));

    if ($name === '') {
        return $this->json(['exists' => false]);
    }

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $gw = new TableGateway('tbl_artist', $adapter);

    // case-insensitive compare (adjust if your collation is already CI)
    $row = $gw->select(function($sel) use ($name){
        $sel->where->expression('LOWER(name)=LOWER(?)', [$name]);
        $sel->limit(1);
    })->current();

    if (!$row) return $this->json(['exists' => false]);

    $spId   = (string)($row['spotify_id'] ?? '');
$spUrl  = $row['ext_url'] ?: ($spId ? ('https://open.spotify.com/artist/'.$spId) : '');

$apId   = (string)($row['apple_id']  ?? '');
$apUrl  = $row['apple_url'] ?: ($apId ? ('https://music.apple.com/artist/'.$apId) : '');

return $this->json([
    'exists'       => true,
    'id'           => (int)$row['id'],
    'name'         => $row['name'],
    // Spotify
    'has_spotify'  => ($spId !== ''),
    'spotify_url'  => $spUrl,
    // ✅ Apple
    'has_apple'    => ($apId !== ''),
    'apple_url'    => $apUrl,
]);
}

// ========== CREATE LOCAL ARTIST ==========
public function createLocalArtistAction()
{
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $name = trim((string)$this->params()->fromPost('name',''));
    if ($name === '') return $this->json(['status'=>'ERR','message'=>'name required']);

    $gw = new TableGateway('tbl_artist', $adapter);

    // already exists?
    $exists = $gw->select(['name'=>$name])->current();
    if ($exists) {
        return $this->json([
            'status'=>'OK',
            'artist'=>['id'=>(int)$exists['id'],'name'=>$exists['name']]
        ]);
    }

    // ⚠️ Only columns that actually exist in your table:
    $gw->insert([
        'name'       => $name,
        'created_at' => date('Y-m-d H:i:s'),
        // 'updated_at' optional; mat bhejo to bhi chalega
    ]);

    return $this->json([
        'status'=>'OK',
        'artist'=>['id'=>$gw->lastInsertValue,'name'=>$name]
    ]);
}

// ========== SEARCH ON SPOTIFY ==========
public function searchArtistSpotifyAction()
{
    try {
        $q = trim((string)$this->params()->fromQuery('q',''));
        if ($q==='') return $this->json(['items'=>[]]);

        $token = $this->getSpotifyToken(); // may throw
        $url = 'https://api.spotify.com/v1/search?type=artist&limit=20&q='.rawurlencode($q);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$token],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $res = curl_exec($ch);
        if ($res === false) return $this->json(['items'=>[], 'error'=>'cURL error: '.curl_error($ch)]);

        $j = json_decode($res,true);
        $items = [];
        foreach (($j['artists']['items'] ?? []) as $a) {
            $items[] = [
                'id'        => $a['id'],
                'name'      => $a['name'],
                'image'     => $a['images'][0]['url'] ?? null,
                'followers' => $a['followers']['total'] ?? null,
                'pop'       => $a['popularity'] ?? null,
            ];
        }
        return $this->json(['items'=>$items]);
    } catch (\Throwable $e) {
        return $this->json(['items'=>[], 'error'=>$e->getMessage()]);
    }
}


// ========== LINK SPOTIFY TO LOCAL ARTIST ==========
public function linkSpotifyToArtistAction()
{
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $gw = new TableGateway('tbl_artist', $adapter);

    // 1) Inputs (accept many parameter names)
    $artistId = (int)$this->params()->fromPost('artist_id',
                 $this->params()->fromQuery('artist_id', 0));

    $artistName = trim((string)$this->params()->fromPost('artist_name',
                    $this->params()->fromQuery('artist_name',
                    $this->params()->fromPost('name',
                    $this->params()->fromQuery('name', '')))));

    // spotify id or url from multiple keys
    $idOrUrl = '';
    foreach (['id_or_url','spotify_id','spotifyId','id','url'] as $k) {
        $v = (string)$this->params()->fromPost($k, $this->params()->fromQuery($k, ''));
        if ($v !== '') { $idOrUrl = $v; break; }
    }
    if ($idOrUrl === '') {
        return $this->json(['status'=>'ERR','message'=>'missing spotify id/url']);
    }

    // 2) Ensure we have a local artist id (create if needed)
    if ($artistId <= 0) {
        if ($artistName === '') {
            return $this->json(['status'=>'ERR','message'=>'artist_id or artist_name required']);
        }
        $artistId = $this->getOrCreateArtistIdByName($artistName, $adapter); // will insert if missing
        if ($artistId <= 0) {
            return $this->json(['status'=>'ERR','message'=>'failed to get/create local artist']);
        }
    }

    // 3) Must exist locally now
    $row = $gw->select(['id'=>$artistId])->current();
    if (!$row) {
        return $this->json(['status'=>'ERR','message'=>'Artist not found locally after create']);
    }

    // 4) Parse spotify id
    $spotifyId = $this->parseSpotifyId($idOrUrl);
    if ($spotifyId === '') {
        return $this->json(['status'=>'ERR','message'=>'bad spotify id/url']);
    }

    // 5) Unique spotify_id
    $other = $gw->select(['spotify_id'=>$spotifyId])->current();
    if ($other && (int)$other['id'] !== $artistId) {
        return $this->json([
            'status' => 'CONFLICT',
            'other'  => ['id'=>(int)$other['id'],'name'=>$other['name']]
        ]);
    }

    // 6) Verify on Spotify then update
    try {
        $sa = $this->fetchSpotifyArtist($spotifyId);
    } catch (\Throwable $e) {
        return $this->json(['status'=>'ERR','message'=>$e->getMessage()]);
    }

    $affected = $gw->update([
        'spotify_id' => $spotifyId,
        'ext_url'    => $sa['url'] ?? ('https://open.spotify.com/artist/'.$spotifyId),
        'image_url'  => $sa['image'] ?? null,
        'followers'  => $sa['followers'] ?? null,
        'popularity' => $sa['pop'] ?? null,
        'updated_at' => date('Y-m-d H:i:s'),
    ], ['id' => $artistId]);

    // 7) Verify actually saved
    $row = $gw->select(['id'=>$artistId])->current();
    if (!$row || (string)$row['spotify_id'] !== $spotifyId) {
        return $this->json(['status'=>'ERR','message'=>'DB update failed (spotify_id not set)']);
    }

    return $this->json([
        'status' => ($affected > 0 ? 'OK' : 'OK_ALREADY'),
        'artist' => [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'spotify_id'  => $row['spotify_id'],
            'spotify_url' => $row['ext_url'] ?: ('https://open.spotify.com/artist/'.$spotifyId),
        ],
        'received' => [
            'artist_id'  => $artistId,
            'artist_name'=> $artistName,
            'input'      => $idOrUrl
        ]
    ]);
}

  /**************************
 * SPOTIFY CONFIG (move to env in production)
 **************************/
private const SPOTIFY_CLIENT_ID     = '2428679943a34c7c8a9d94fc8b68cf8b';
private const SPOTIFY_CLIENT_SECRET = '1af7bee2a0bb4feba0527e5e24b313f3';

private function json($arr) {
    $this->getResponse()->getHeaders()->addHeaderLine('Content-Type','application/json');
    echo json_encode($arr);
    return $this->getResponse();
}

private function parseSpotifyId($v) {
    $v = trim((string)$v);
    if ($v === '') return '';
    // open.spotify.com/artist/<ID> , spotify.com/artist/<ID> , with extra path or ?si=...
    if (preg_match('~spotify\.com/artist/([A-Za-z0-9]{22})\b~i', $v, $m)) return $m[1];
    if (preg_match('~spotify:artist:([A-Za-z0-9]{22})~i', $v, $m))   return $m[1];
    if (preg_match('~^[A-Za-z0-9]{22}$~', $v))                       return $v;
    return '';
}
private function getSpotifyToken() {
    if (!empty($_SESSION['SPOTIFY_TOKEN']) && !empty($_SESSION['SPOTIFY_TOKEN_EXPIRES']) &&
        $_SESSION['SPOTIFY_TOKEN_EXPIRES'] > time()+60) {
        return $_SESSION['SPOTIFY_TOKEN'];
    }
    $ch = curl_init('https://accounts.spotify.com/api/token');
    $auth = base64_encode(self::SPOTIFY_CLIENT_ID . ':' . self::SPOTIFY_CLIENT_SECRET);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'client_credentials']),
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res = curl_exec($ch);
    if ($res === false) throw new \Exception('Spotify token error: '.curl_error($ch));
    $json = json_decode($res, true);
    if (empty($json['access_token'])) throw new \Exception('Spotify token empty');
    $_SESSION['SPOTIFY_TOKEN']         = $json['access_token'];
    $_SESSION['SPOTIFY_TOKEN_EXPIRES'] = time() + (int)$json['expires_in'];
    return $_SESSION['SPOTIFY_TOKEN'];
}

  
  // 👇 add inside class IndexController (e.g. above saveAction or near other private helpers)
private function getOrCreateArtistIdByName($name, \Zend\Db\Adapter\Adapter $adapter): int
{
    $name = trim(preg_replace('/\s+/', ' ', (string)$name));
    if ($name === '') return 0;

    $gw  = new \Zend\Db\TableGateway\TableGateway('tbl_artist', $adapter);
    $row = $gw->select(['name' => $name])->current();
    if ($row) return (int)$row['id'];

    // ⚠️ Table ke real columns use karo
    $gw->insert([
        'name'       => $name,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return (int)$gw->lastInsertValue;
}

private function fetchSpotifyArtist($id) {
    $token = $this->getSpotifyToken();
    $ch = curl_init('https://api.spotify.com/v1/artists/'.$id);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$token],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res = curl_exec($ch);
    if ($res === false) throw new \Exception('Spotify artist error: '.curl_error($ch));
    $j = json_decode($res, true);
    if (empty($j['id'])) throw new \Exception('Artist not found on Spotify');
    return [
        'id'        => $j['id'],
        'name'      => $j['name'] ?? '',
        'image'     => (!empty($j['images'][0]['url']) ? $j['images'][0]['url'] : null),
        'followers' => $j['followers']['total'] ?? null,
        'pop'       => $j['popularity'] ?? null,
        'url'       => $j['external_urls']['spotify'] ?? ('https://open.spotify.com/artist/'.$j['id']),
    ];
}

public function artistSuggestionListAction()
{
    $customObj = $this->CustomPlugin();
    $sl       = $this->getServiceLocator();
    $adapter  = $sl->get('Zend\Db\Adapter\Adapter');
    $request  = $this->getRequest();

    // input
    $query = trim((string)($request->getPost('query', '')));
    if (mb_strlen($query) > 100) $query = mb_substr($query, 0, 100);

    // treat ALL_LIST like "show everything"
    $wantAll = (strcasecmp($query, 'ALL_LIST') === 0);
    if ($wantAll) $query = '';

    // gather local names
    $user_artist = (string)$customObj->getUserArtist($adapter);
    $artists = [];

    if ($user_artist === '') {
        // non-staff: distinct names from releases
        $like = ($wantAll ? '%' : '%'.$query.'%');
        $stmt = $adapter->createStatement(
            "SELECT DISTINCT releaseArtist AS name
               FROM tbl_release
              WHERE releaseArtist LIKE ?
              LIMIT 200",
            [$like]
        );
        $rowset = (new \Zend\Db\ResultSet\ResultSet)->initialize($stmt->execute())->toArray();

        foreach ($rowset as $row) {
            foreach (explode(',', (string)$row['name']) as $nm) {
                $nm = trim($nm);
                if ($nm !== '' && ($query==='' || stripos($nm,$query)!==false)) $artists[] = $nm;
            }
        }
    } else {
        // staff: CSV list from tbl_staff.artist
        $stmt = $adapter->createStatement(
            "SELECT artist AS name FROM tbl_staff WHERE id=? LIMIT 1",
            [@$_SESSION['user_id']]
        );
        $rowset = (new \Zend\Db\ResultSet\ResultSet)->initialize($stmt->execute())->toArray();

        foreach ($rowset as $row) {
            foreach (explode(',', (string)$row['name']) as $nm) {
                $nm = trim($nm);
                if ($nm !== '' && ($wantAll || $query==='' || stripos($nm,$query)!==false)) $artists[] = $nm;
            }
        }
    }

    // de-dupe / sort / cap
    $artists = array_values(array_unique($artists, SORT_STRING));
    sort($artists, SORT_NATURAL | SORT_FLAG_CASE);
    $artists = array_slice($artists, 0, 50);

    // Spotify + Apple status for these names (from tbl_artist)
    $status = [];
    if (!empty($artists)) {
        $ph = implode(',', array_fill(0, count($artists), '?'));
        $res = $adapter->createStatement(
            "SELECT id, name, spotify_id, apple_id, apple_url
               FROM tbl_artist
              WHERE name IN ($ph)"
        )->execute($artists);

        foreach ($res as $r) {
            $status[$r['name']] = [
                'id'          => (int)$r['id'],
                'has_spotify' => !empty($r['spotify_id']),
                'sp_id'       => $r['spotify_id'] ?: null,
                'sp_url'      => !empty($r['spotify_id']) ? ('https://open.spotify.com/artist/'.$r['spotify_id']) : null,
                'has_apple'   => (!empty($r['apple_id']) || !empty($r['apple_url'])),
                'ap_url'      => !empty($r['apple_url']) ? $r['apple_url'] : null,
            ];
        }
    }

    // output
    if (count($artists) > 0) {
        foreach ($artists as $nm) {
            $safe = htmlspecialchars($nm, ENT_QUOTES, 'UTF-8');

            $aid     = !empty($status[$nm]['id'])          ? (int)$status[$nm]['id']     : 0;
            $hasSp   = !empty($status[$nm]['has_spotify']);
            $spUrl   = !empty($status[$nm]['sp_url'])      ? $status[$nm]['sp_url']      : '#';
            $hasAp   = !empty($status[$nm]['has_apple']);
            $apUrl   = !empty($status[$nm]['ap_url'])      ? $status[$nm]['ap_url']      : '#';

            echo '<li class="suggestion-item ap-local-item"'
               .      ' data-name="'.$safe.'"'
               .      ' data-artist-id="'.$aid.'"'
               .      ' data-linked-spotify="'.($hasSp?'1':'0').'"'
               .      ' data-linked-apple="'.($hasAp?'1':'0').'"'
               .      ($hasAp && $apUrl ? ' data-apple-url="'.htmlspecialchars($apUrl, ENT_QUOTES, 'UTF-8').'"' : '')
               .  '>'
               .   '<span class="ap-title">'.$safe.'</span>';
          
          

            if ($hasSp) {
                echo ' <a class="ap-mini-spotify ap-mini--linked" href="'.$spUrl.'" target="_blank" title="Open on Spotify">'
                   .      '<span class="ap-ic-spotify mini"></span>'
                   .    '</a>';
            }

            if ($hasAp) {
                echo ' <a class="ap-mini-apple ap-mini--linked" href="'.$apUrl.'" target="_blank" title="Open on Apple Music">'
                   .      '<span class="ap-ic-apple mini"></span>'
                   .    '</a>';
            }

            echo '</li>';
        }
        exit;
    }

    // don't show "Add ..." for ALL_LIST
    if ($query !== '' && strtoupper($query) !== 'ALL_LIST') {
        $safeQ = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        echo '<li class="suggestion-item suggestion-add" data-name="'.$safeQ.'">➕ Add “'.$safeQ.'” as new artist</li>';
    } else {
        echo '<li class="suggestion-item" style="color:#6b7280">No suggestions</li>';
    }

    exit;
}



	public function updatereleasedateAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
       
		$iID = $request->getPost("KEY_ID");
		$projectTable = new TableGateway('tbl_release', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['release_id']."'"));
		$rowset = $rowset->toArray();
		
		$release_date = $rowset[0]['digitalReleaseDate'];
		$aData=array();
		$aData['digitalReleaseDate'] = date('Y-m-d',strtotime($release_date." +7 days"));
		$projectTable->update($aData,array("id='".$_GET['release_id']."'"));
		
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
		echo $result;
		exit;
            
	}
	public function submitReleaseAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$config = $this->getServiceLocator()->get('config');
       
		$iID = $request->getPost("release_id");
		$projectTable = new TableGateway('tbl_release', $adapter);
		$viewTable = new TableGateway('view_release', $adapter);
		$staffTable = new TableGateway('tbl_staff', $adapter);
		$notificationTable = new TableGateway('tbl_notification', $adapter);
		$settingTable = new TableGateway('tbl_settings', $adapter);
		$trackTable = new TableGateway('tbl_track', $adapter);
		$rowset = $projectTable->select(array("id='".$iID."'"));
		$rowset = $rowset->toArray();
		
		if($rowset[0]['rejected_flag'] == '0')
		{
			$rowset11 = $trackTable->select(array("master_id='".$iID."' and order_id > 0 and generate_isrc=1 "));
			$rowset11 = $rowset11->toArray();
			foreach($rowset11 as $row11)
			{
				$tData=array();
				$tData['isrc'] = $this->generateISRC(); 
				$trackTable->update($tData,array("id='".$row11['id']."' "));
				
				$sData=array();
				$sData['last_isrc'] = $tData['isrc'];
				$settingTable->update($sData);
			}
		}
		
		$aData=array();
		$aData['status']='inreview';
		$aData['rejected_flag'] = 0;
		
		if($rowset[0]['pcn'] == '')
		{
			$aData['pcn'] = $this->generatePCN();  
			
			$sData=array();
			$sData['last_pcn'] = $aData['pcn'];
			$settingTable->update($sData);
		}
			
		$projectTable->update($aData,array("id='".$iID."'"));
		
		$rowset2 = $viewTable->select(array("id='".$iID."'"));
		$rowset2 = $rowset2->toArray();
		
		if($rowset[0]['upc_no'] != '')
			$rowset[0]['upc_no'] = ' - '.$rowset[0]['upc_no'];
				
		$content ='<h2 style="color: #333;">Hello,</h2>
		<p>New Release created by '.$rowset2[0]['label_name'].'</p>
		
		<p><strong>Title:</strong> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].$rowset[0]['upc_no'].'</p>
		
		<p>Thanks and regards,<br>
		The Prime Digital Arena team</p>';
		
		if($rowset2[0]['user_id'] > 0  && $_SESSION['STAFFUSER'] == '0')
		{
			$nData = array();
			$nData['user_id'] = '0';
			$nData['type'] = 'New Release';
			$nData['title'] = 'New Release <b>'.$rowset[0]['title'].'</b> created by '.$rowset2[0]['label_name'];
			$nData['url'] = $config['URL'].'releases?new='.$iID;
			$notificationTable->insert($nData);
		
			$rowset3 = $staffTable->select(array("id='0' "));
			$rowset3 = $rowset3->toArray();
			$customObj->sendSmtpEmail($config,$rowset3[0]['email'],'New Release created.',$content,$rowset3[0]['label_manager_email']);
		}
		else
		{
			$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset2[0]['labels'].",labels) "));
			$rowset3 = $rowset3->toArray();
			
		
			foreach($rowset3 as $row3)
			{
				$nData = array();
				$nData['user_id'] = $row3['id'];
				$nData['type'] = 'New Release';
				$nData['title'] = 'New Release <b>'.$rowset[0]['title'].'</b> created by '.$rowset2[0]['label_name'];
				$nData['url'] = $config['URL'].'releases?new='.$iID;
				$notificationTable->insert($nData);
				
				$customObj->sendSmtpEmail($config,$row3['email'],'New Release created.',$content,$row3['label_manager_email']);
			}
		}
		
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
		echo $result;
		exit;
	}
	public function releasedateAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
       
		$iID = $request->getPost("KEY_ID");
		$projectTable = new TableGateway('tbl_release', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['edit']."'"));
		$rowset = $rowset->toArray();
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0'  || $_SESSION['STAFFUSER'] == '1')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
            
		if($rowset[0]['digitalReleaseDate'] == '0000-00-00')
			$digitalReleaseDate= '';
		else
			$digitalReleaseDate = date('d-m-Y',strtotime($rowset[0]['digitalReleaseDate']));
		
		$minDate = date('Y-m-d');
		
		if($rowset[0]['digitalReleaseDate'] != '0000-00-00')
		{
			if(strtotime($rowset[0]['digitalReleaseDate']) > strtotime($minDate))
			{
				
			}
			else
			{
				$minDate = $rowset[0]['digitalReleaseDate'];
			}
		}
		$SubTitle = '';
		if(trim($rowset[0]['version']) != '')
		{
			$SubTitle = '<small>('.$rowset[0]['version'].')</small>';
		}
				
		$viewModel= new ViewModel(array(
			'Title' => $rowset[0]['title'],
			'SubTitle' => $SubTitle,
			'import_flag' => $rowset[0]['import_flag'],
			'digitalReleaseDate' => $digitalReleaseDate,
			'minDate' => $minDate
        ));
		
		
		return   $viewModel;
    }
	public function submissionAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        $customObj = $this->CustomPlugin();
		$iID = $request->getPost("KEY_ID");
		$projectTable = new TableGateway('view_release', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['edit']."'"));
		$rowset = $rowset->toArray();
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0'  || $_SESSION['STAFFUSER'] == '1')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
		
		$settingTable = new TableGateway('tbl_settings', $adapter);
		$rowset2 = $settingTable->select();
		$rowset2 = $rowset2->toArray();
		$settings = $rowset2[0];
		
		$trackTable = new TableGateway('tbl_track', $adapter);
		
		$info=array();
		
		$STATUS= $rowset[0]['status'];
		$empty = '<span class="text-muted text-italic alpha50"><em>(empty)</em></span>';
		
		$release_info_error_count=0;
		$all_track_error=0;
		
		foreach($rowset as $row)
		{
			if($row['releasing_network'] == '0')
			{
				$release_info_error_count++;
			}
			if($row['albumFormat'] == '0')
			{
				$release_info_error_count++;
			}
			if($row['physicalReleaseDate'] == '0000-00-00')
			{
				$release_info_error_count++;
			}
			if($row['pLine'] == '')
			{
				$release_info_error_count++;
			}
			if($row['cLine'] == '')
			{
				$release_info_error_count++;
			}
			if($row['productionYear'] == '0')
			{
				$release_info_error_count++;
			}
			if (!isset($row3['trackPrice']) || $row3['trackPrice'] === '0' || $row3['trackPrice'] === '') {
    $track_error_count++;
}

			if($row['pcn'] != '')
			{
				$rowset31 = $projectTable->select(array("id !='".$_GET['edit']."' and pcn='".$row['pcn']."' "));
				$rowset31 = $rowset31->toArray();
				if(count($rowset31) > 0)
					$release_info_error_count++;
			}
			

			$ARTIST_LIST='';
			if($row['title'] == '')
			{
				$row['title']=$empty;
				$release_info_error_count++;
			}
			if($row['version'] == '')
				$row['version']=$empty;
			if($row['releaseArtist'] == '')
			{
				$row['releaseArtist']=$empty;
				$release_info_error_count++;
			}
			else
			{
				$artist = explode(',',$row['releaseArtist']);
				
				for($i=0;$i<count($artist);$i++)
				{
					$ARTIST_LIST .= '<div class="row artist">
											<div class="">
												<p><span style="font-size:19px;">'.$artist[$i].'</b></span>
											</div>
									</div>';
				}
				
			}
			if($row['label_name'] == '')
			{
				$row['label_name']=$empty;
				$release_info_error_count++;
			}
			if($row['mainGenre'] == '0' || $row['mainGenre'] == '')
			{
				$row['mainGenre']=$empty;
				$release_info_error_count++;
			}
			if($row['subgenre'] == '0' || $row['subgenre'] == '')
			{
				$row['subgenre']=$empty;
				$release_info_error_count++;
			}
			if($row['cover_img'] == '')
			{
				$release_info_error_count++;
			}
			
			$release_date_error = 'no';
			if($row['digitalReleaseDate'] == '0000-00-00')
			{
				$row['digitalReleaseDate']=$empty;
				$release_date_error = 'yes';
			}
			else
			{
				
				if(strtotime(date('Y-m-d')) > strtotime($row['digitalReleaseDate']))
				{
					$release_date_error = 'yes';
					
				}
				$row['recom_release_day']= date('l',strtotime($row['digitalReleaseDate']." +7 days"));
				$row['recom_release_date'] = date('d/m/Y',strtotime($row['digitalReleaseDate']." +7 days"));
				$row['digitalReleaseDate'] = date('d/m/Y',strtotime($row['digitalReleaseDate']));
			}
				
			
			
			if($row['cover_img'] == '')
				$row['cover_img']='../public/img/blank.jpg';
			else
				$row['cover_img']='../public/uploads/'.$row['cover_img'];
			
			$rec=array();
			$rec['title'] = $row['title'];
			$rec['version'] = $row['version'];
			$rec['artist'] = $row['releaseArtist'];
			$rec['label'] = $row['label_name'];
			$rec['mainGenre'] = $row['mainGenre'];
			$rec['subgenre'] = $row['subgenre'];
			$rec['tot_tracks'] = $row['tot_tracks'];
			$rec['cover_img'] = $row['cover_img'];
			$rec['digitalReleaseDate'] = $row['digitalReleaseDate'];
			$rec['recom_release_date'] = $row['recom_release_date'];
			$rec['recom_release_day'] = $row['recom_release_day'];
          $rec['cLine'] = $row['cLine'] ?? ($row['cline'] ?? ($row['c_line'] ?? ''));
$rec['pLine'] = $row['pLine'] ?? ($row['pline'] ?? ($row['p_line'] ?? ''));
          $rec['productionYear']      = (string)($row['productionYear'] ?? '');
$rec['pcn']                 = (string)($row['pcn'] ?? ''); // Producer Catalogue No.
$rec['physicalReleaseDate'] = !empty($row['physicalReleaseDate']) && $row['physicalReleaseDate'] !== '0000-00-00'
                                ? date('d/m/Y', strtotime($row['physicalReleaseDate']))
                                : '';

			
			$rec['release_info_error_count'] = $release_info_error_count;
			
			$rec['release_date_error'] = $release_date_error;
			
			$info[] = $rec;
		}
		if($info[0]['tot_tracks'] >= 1)
		{
			$rowset2 = $trackTable->select(array("master_id='".$_GET['edit']."' group by volume order by volume asc"));
			$rowset2 = $rowset2->toArray();
			
			$all_isrc_error=false;
			
			$TRACK_LIST='';
			$ISRC_DATA = '';
			
			
			$generate_isrc_no='';
			$generate_isrc_yes='';
			
			$all_track_error=0;
					
			foreach($rowset2 as $row2)
			{
				if($row2['order_id'] > 0)
				{
					$TRACK_LIST.='<tr class="active"><td colspan="8"><strong>Volume '.$row2['volume'].'</strong></td></tr> ';
					$ISRC_DATA.='<tr class="text-info active"><td colspan="3"><strong> Volume '.$row2['volume'].' </strong></td></tr>';
				}
								
				$rowset3 = $trackTable->select(array("master_id='".$_GET['edit']."' and volume='".$row2['volume']."' and order_id != 0 order by order_id asc"));
				$rowset3 = $rowset3->toArray();
				foreach($rowset3 as $row3)
				{
					$track_error_count=0;
					
					$isrc_style='';
					
					if($row3['trackType'] == '')
						$track_error_count++;
					if($row3['songName'] == '')
						$track_error_count++;
					if($row3['trackArtist'] == '')
						$track_error_count++;
					if($row3['author'] == '' || (substr_count($row3['author'], ' ') < 1))
						$track_error_count++;
					if($row3['composer'] == '' ||  (substr_count($row3['composer'], ' ') < 1))
						$track_error_count++;
					if($row3['pLine'] == '')
						$track_error_count++;
					if($row3['productionYear'] == '0')
						$track_error_count++;
					if($row3['explicitContent'] == '')
						$track_error_count++;
					if($row3['metadataLanguage'] == '' || $row3['metadataLanguage'] == '0')
						$track_error_count++;
					if($row3['idLyricsSelect'] == '' || $row3['idLyricsSelect'] == '0')
						$track_error_count++;
					
					if($row3['audio_file'] == '')
						$track_error_count++;
					
					
					if($row3['generate_isrc'] == '0' )
					{
						
						if($row3['isrc'] == '')
						{
							$all_isrc_error=true;
							$track_error_count++;
							
							$isrc_style=' has-error';
						}
						
						$generate_isrc_no='checked';
						$generate_isrc_yes='';
					}
					else
					{
						$isrc_style=' hide';
						$generate_isrc_no='';
						$generate_isrc_yes='checked';
					}
					
					$audio = '';
					if($row3['audio_file'] == '')
					{
							$audio .='<td>
										<div class=" has-warning" rel="">---
										</div>
									</td>
									<td>
										<div class="relatedUploadedFileMarker has-warning" rel="32232241">
											
											<span class="label label-warning"><span class="glyphicon glyphicon-warning-sign"></span></span>
											
										</div>
									</td>';
					}
					else
					{
						$audio .='<td>
										<div class=" has-success" rel=""><b>'.$row3['audio_file_name'].'</b>
										</div>
									</td>
									<td>
										<div class="relatedUploadedFileMarker has-success" rel="32232241">
											
											
											<span class="label label-success "><span class="glyphicon glyphicon-ok"></span></span>
										</div>
									</td>';
					}
					$__price_raw = isset($row3['trackPrice']) ? trim((string)$row3['trackPrice']) : '';
if ($__price_raw === '') {
  $priceCell = '<td><span class="text-muted">—</span></td>';
} else {
  // yahan simple print; chaaho to currency format kar denge
  $priceCell = '<td>'.htmlspecialchars($__price_raw, ENT_QUOTES, 'UTF-8').'</td>';
}
					if($row3['isrc'] == '')
					{
						$isrc= '<td><span class="isrc-noIsrc " rel=""><span class="text-muted">Ask to generate an ISRC</span></span></td>';
					}
					else
					{
						$isrc= '<td><span class="isrc-hasIsrc " rel="32232241">'.$row3['isrc'].'</span></td>';
					}
					$TRACK_LIST.='<tr class="submission-tracks-line">
									<td>'.$row3['order_id'].'</td>
									<td><span class="glyphicon glyphicon-music"></span></td>
									'.$audio.'
									<td>
										<div><strong>'.$row3['songName'].'</strong></div>
										<div><em></em></div>
										<div>'.$row3['trackArtist'].'</div>
									</td>
                                    '.$priceCell.' 
									'.$isrc.'
									<td>';
									if($track_error_count > 0)
									{
										$TRACK_LIST.='<a class="label label-danger label-trackError" href="track?edit='.$_GET['edit'].'&track_id='.$row3['id'].'&showError=1"><span class="glyphicon glyphicon-warning-sign"></span> '.$track_error_count.' Error(s)</a>';
										
										$all_track_error=1;
									}
									else
									{
										$TRACK_LIST.='<a class="label label-info label-trackError" href="track?edit='.$_GET['edit'].'"><span class="glyphicon glyphicon-pencil"></span> Edit track</a>';
									}
								
							$TRACK_LIST.='</td>
								</tr>';
								
					
					
					
					$ISRC_DATA .='<tr>

									<td class="v-a-mid">
									   <strong> '.$row3['order_id'].' </strong> &nbsp; <label class="">'.$row3['songName'].'</label>
									   <br>
									</td>
									<td class="v-a-mid" >
										<div class="form-group td-isrc '.$isrc_style.' ">
											<input type="text" class="input-isrc inputBehavior- form-control" data-song-id="'.$row3['id'].'" name="isrc_'.$row3['id'].'" id="isrc_'.$row3['id'].'" placeholder="XX-0X0-00-00000" value="'.$row3['isrc'].'">
										</div>
									</td>
									<td>
										Ask to generate an ISRC 
										<input type="radio" id="GeneratedIsrcRequiredYes_'.$row3['id'].'" class="generateByBelieve inputBehavior-" data-song-id="'.$row3['id'].'" name="generateByPrime_'.$row3['id'].'" value="1" '.$generate_isrc_yes.'>
										<label class="" for="GeneratedIsrcRequiredYes_'.$row3['id'].'" >Yes</label>
										
										<input type="radio" id="GeneratedIsrcRequiredNo_'.$row3['id'].'" class="generateByBelieve inputBehavior-" data-song-id="'.$row3['id'].'" name="generateByPrime_'.$row3['id'].'" value="0" '.$generate_isrc_no.'>
										<label class="" for="GeneratedIsrcRequiredNo_'.$row3['id'].'" >No</label>
									</td>
								</tr>';
				} 
					
			}
			$TRACK_LIST.='</tbody></table>';
			
			$TRACKS =' <table class="table table-">
			<thead>
				<tr class="muted"><th>#</th>
					<th><span class="track-icon"></span></th>
					<th>Asset(s)</th>
					<th>&nbsp;&nbsp;</th>
					<th>Title & Artist(s)</th>
                    <th>Track Price</th>  
					<th>ISRC Code(s)</th>';
					
					if($all_isrc_error)
					{
						$TRACKS .='<th><a href="javascript:;" class="isrcConfirmationRelease label label-danger"><span class="glyphicon glyphicon-warning-sign"></span> Edit all ISRC codes</a></th>';
						
						$popin_notice = '';
						$popin_allvalid = 'hide';
					}
					else
					{
						$TRACKS .='<th><a href="javascript:;" class="isrcConfirmationRelease ">Edit all ISRC codes</a></th>';
						$popin_notice = 'hide';
						$popin_allvalid = '';
					}
					
	$TRACKS .='</tr>
			</thead>

			<tbody>';
			
			$info[0]['assets_info_error_count'] = 0;
		}
		else
		{
			$info[0]['assets_info_error_count'] = 1;
			$TRACK_LIST = '<div class="submission-header-content">You must Create Track to your release! </div>';
		}
		
		
		
		$TRACKS .=  $TRACK_LIST;
		
		
		
		$viewModel= new ViewModel(array(
			'STATUS' => $STATUS,
			'INFO' => $info[0],
			'Settings' => $settings,
			'Tracks' => $TRACKS,
			'ISRC_DATA' => $ISRC_DATA,
			'popin_notice' => $popin_notice,
			'popin_allvalid' => $popin_allvalid,
			'all_track_error' => $all_track_error,
			'ARTIST_LIST' => $ARTIST_LIST,
        ));
		
		return   $viewModel;
    }
	public function successAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
       
		$iID = $request->getPost("KEY_ID");
		$projectTable = new TableGateway('view_release', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['edit']."'"));
		$rowset = $rowset->toArray();
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0'  || $_SESSION['STAFFUSER'] == '1')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
		
		$rowset[0]['digitalReleaseDate'] = date('d/m/Y',strtotime($rowset[0]['digitalReleaseDate']));
		
		$viewModel= new ViewModel(array(
		
			'Info' => $rowset[0],
			
        ));
		
		return   $viewModel;
    }
    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }
    public function getrecAction()
{
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $request = $this->getRequest();
    $recs = array();

    if ($request->isPost()) {
        $iID = $request->getPost("KEY_ID");
        $projectTable = new TableGateway('tbl_release', $adapter);
        $rowset = $projectTable->select(array('id' => $iID));
        $rowset = $rowset->toArray();

        foreach ($rowset as $record) {
            // --- existing formatting ---
            $date = explode('-', $record['physicalReleaseDate']);
            $record['physicalReleaseDate'] = $date[2] . '-' . $date[1] . '-' . $date[0];
            if ($record['physicalReleaseDate'] == '00-00-0000') $record['physicalReleaseDate'] = '';

            // CSV -> arrays
            $record['releaseArtist'] = explode(',', (string)$record['releaseArtist']);
            $record['featuring']     = explode(',', (string)$record['featuring']);

            /* ========== ENRICH: local IDs + Spotify/Apple URLs ========== */
            // 1) Clean names (order preserve)
            $names = array_values(array_filter(array_map('trim', (array)$record['releaseArtist']), 'strlen'));

            // 2) Fetch from tbl_artist (Spotify + Apple fields)
            $byName = [];
            if (!empty($names)) {
                $ph   = implode(',', array_fill(0, count($names), '?'));
                $stmt = $adapter->createStatement(
                    "SELECT id, name, spotify_id, apple_id, apple_url
                       FROM tbl_artist
                      WHERE name IN ($ph)"
                );
                $res  = $stmt->execute($names);
                foreach ($res as $r) {
                    $byName[$r['name']] = [
                        'id'          => (int)$r['id'],
                        'spotify_url' => !empty($r['spotify_id']) ? ('https://open.spotify.com/artist/'.$r['spotify_id']) : null,
                        // Apple: prefer stored apple_url (we saved this at link time)
                        'apple_url'   => !empty($r['apple_url']) ? $r['apple_url'] : null,
                    ];
                }
            }

            // 3) Build normalized artist objects
            $artists = [];
            foreach ($names as $nm) {
                $hit = $byName[$nm] ?? null;
                $artists[] = [
                    'name'        => $nm,
                    'id'          => $hit ? $hit['id'] : 0,
                    'spotify_url' => $hit['spotify_url'] ?? null,
                    'apple_url'   => $hit['apple_url']   ?? null,
                ];
            }

            // 4) Arrays for frontend
            $record['releaseArtist']       = array_column($artists, 'name');         // ["Arijit Singh", ...]
            $record['releaseArtistIds']    = array_column($artists, 'id');           // [123, 0, ...]
            $record['releaseSpotifyUrls']  = array_column($artists, 'spotify_url');  // ["https://...", null, ...]
            $record['releaseAppleUrls']    = array_column($artists, 'apple_url');    // ["https://music.apple.com/..", null, ...]

            /* ========== /ENRICH ========== */

            $recs[] = $record;
        }

        $result['data'] = $recs;
        $result['recordsTotal'] = count($recs);
        $result['DBStatus'] = 'OK';
        echo json_encode($result);
        exit;
    }
}

	public function getTrackAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_track', $adapter);
			$releaseTable = new TableGateway('tbl_release', $adapter);
            $rowset = $projectTable->select(array('id' => $iID));
            $rowset = $rowset->toArray();
            foreach ($rowset as $record)
			{
				$record['trackArtist'] = explode(',',$record['trackArtist']);
				$record['featuring'] = explode(',',$record['featuring']);
				$record['author'] = explode(',',$record['author']);
				$record['composer'] = explode(',',$record['composer']);
				$record['produceBy'] = explode(',',$record['produceBy']);
					
                $recs[] = $record;
			}
			
			
			$rowset2 = $releaseTable->select(array("id='".$rowset[0]['master_id']."'"));
			$rowset2 = $rowset2->toArray();
			
			if($recs[0]['songname'] == '')
				$recs[0]['songname'] = $rowset2[0]['title'];
			if($recs[0]['trackArtist'] == '')
				$recs[0]['trackArtist'] = $rowset2[0]['releaseArtist'];
			if($recs[0]['featuring'] == '')
				$recs[0]['featuring'] = $rowset2[0]['featuring'];
			if($recs[0]['version'] == '')
				$recs[0]['version'] = $rowset2[0]['version'];
			if($recs[0]['pLine'] == '')
				$recs[0]['pLine'] = $rowset2[0]['pLine'];
			if($recs[0]['productionYear'] == '')
				$recs[0]['productionYear'] = $rowset2[0]['productionYear'];
			
			
			$recs[0]['track_id'] = $recs[0]['id'];
            $result['data'] = $recs;
            $result['recordsTotal'] = count($recs);
            $result['DBStatus'] = 'OK';
            $result = json_encode($result);
            echo $result;
            exit;
        }
	}
    public function  deleteAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_department', $adapter);
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$aData = array('deleted_flag' => '1');
				//$projectTable->update($aData,array("id=".$iMasterID));
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				$customObj->createlog("module='Newrelease',action='Newrelease ".$info." Deleted',action_id='".$iMasterID."' ");
               $projectTable->delete(array("id=" . $iMasterID));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }

	public function convertImage($imagePath) {
		// Define the desired dimensions
		$width = 3000;
		$height = 3000;

		// Prepare the FFmpeg command to scale and pad the image
		$command = "ffmpeg -i " . escapeshellarg($imagePath) . " -vf \"scale='if(gt(a,1),3000,-1)':'if(gt(1,a),3000,-1)',pad=3000:3000:(3000-iw)/2:(3000-ih)/2\" -y " . escapeshellarg($imagePath);

		// Execute the command
		$output = shell_exec($command);

		// Check for errors
		if ($output === null) {
			//echo "Image conversion failed.";
		} else {
			//echo "Image converted successfully: {$imagePath}.";
		}
	}
	public function uploadimgAction()
	{
		$config = $this->getServiceLocator()->get('config');
		
		$file = $_FILES['file'];

		// File properties
		$file_name = $file['name'];
		$file_tmp = $file['tmp_name'];
		$file_size = $file['size'];
		$file_error = $file['error'];

		$ext = pathinfo($file_name, PATHINFO_EXTENSION); 		
		$filename = date('YmdHis').'.'.$ext;
		$myImagePath =  "public/uploads/$filename";
		
		

			if (!move_uploaded_file($file_tmp, $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
				/*list($width, $height) = getimagesize($file_tmp);
				if($width != '3000' && $height != '3000')
					$this->convertImage($myImagePath);*/
				
				$thumbnail_path = "public/uploads/thumb_".$filename;
				$this->create_thumbnail($myImagePath, $thumbnail_path, 150, 150); // Create a 150x150 thumbnail
				
                $result['status'] = 'OK';
                $result['message1'] = 'Done';				
				$result['file_name'] = $filename; 
            } 
           
        
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function releasedatesaveAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			$iMasterID=$aData['MASTER_KEY_ID'];
			unset($aData['MASTER_KEY_ID']);
			
			$date = explode('-',$aData['digitalReleaseDate']);
			$aData['digitalReleaseDate'] = $date[2].'-'.$date[1].'-'.$date[0];
			$projectTable->update($aData,array("id=".$iMasterID));
				
			$result['DBStatus'] = 'OK';
		}
		$result = json_encode($result);
        echo $result;
        exit;
		
	}
	public function deleteTrackAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
			
			$track_id=$_POST['track_id'];
			
			$rowset = $projectTable->select(array("id='".$track_id."'"));
			$rowset = $rowset->toArray();
			$master_id = $rowset[0]['master_id'];
			$volume = $rowset[0]['volume'];
			
			$projectTable->delete("id=".$track_id);
			
			
			$this->changeTrackOrder($master_id,$volume);
				
			$result['DBStatus'] = 'OK';
		}
		$result = json_encode($result);
        echo $result;
        exit;
	}
	public function  changeTrackOrder($master_id,$volume)
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $projectTable = new TableGateway('tbl_track', $adapter);
		$rowset = $projectTable->select(array("master_id='".$master_id."'","volume='".$volume."' order by order_id asc"));
		$rowset = $rowset->toArray();
		$order_id=1;
		
		if(count($rowset) > 0)
		{
			foreach($rowset as $row)
			{
				$aData=array();
				$aData['order_id'] = $order_id;
				$projectTable->update($aData,array("id='".$row['id']."' "));
				$order_id++;
			}
		}
		else
		{
			/* $sql="Update tbl_track set volume=(volume-1) where volume > '".$volume."' and master_id='".$master_id."' ";		        
			 $optionalParameters=array();        
			 $statement = $adapter->createStatement($sql, $optionalParameters);        
			 $result = $statement->execute();        
			 $resultSet = new ResultSet;        
			 $resultSet->initialize($result);        
			 $rowset=$resultSet->toArray();*/
			 
				$order_id = 0;
				$aData=array();
				$aData['master_id']=$master_id;
				$aData['volume']=$volume;
				$aData['order_id']=$order_id;
				$projectTable->insert($aData);
				
				$track_id = $projectTable->lastInsertValue;
				
				$this->addDefaultInfo($master_id,$track_id);
		}
	}
	public function moveTrackAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
			
			$aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			
			$track_id = explode('_',$aData['idTrack']);
			$track_id = $track_id[1];
			$moveFrom = $aData['moveFrom'];
			$moveTo = $aData['moveTo'];
			
			$rowset = $projectTable->select(array("id='".$track_id."'"));
			$rowset = $rowset->toArray();
			$master_id = $rowset[0]['master_id'];
			$volume = $rowset[0]['volume'];
			
			$projectTable->delete(array("master_id='".$master_id."' and volume='".$moveTo."' and order_id='0'  "));
			
			$aData = array();
			$aData['order_id'] = 999999;
			$aData['volume'] = $moveTo;
			$projectTable->update($aData,array("id='".$track_id."'"));
			
			$this->changeTrackOrder($master_id,$moveTo);
			$this->changeTrackOrder($master_id,$moveFrom);  
			
				
			$result['DBStatus'] = 'OK';
		}
		$result = json_encode($result);
        echo $result;
        exit;
	}
	public function updateTrackOrderAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$projectTable = new TableGateway('tbl_track', $adapter);
		$trackOrder = json_decode($_GET['trackOrder'], true);
		
		foreach($trackOrder as $track_id => $order_id)
		{
			$aData = array();
			$aData['order_id'] = $order_id;
			$projectTable->update($aData,array("id='".$track_id."'"));
		}
		
		$result['DBStatus'] = 'OK';
		
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function saveTrackAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
			$aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			
			$aData['audio_file'] = $aData['audio_hidden'];
			$aData['audio_file_name'] = substr($aData['audio_hidden'],15);
			
			$trackArtist = $aData['trackArtist[]'];
			if(is_array($trackArtist))
			{
				$trackArtist = array_filter($trackArtist, function($value) {
					return !empty($value);
				});
				$trackArtist = implode(',',$trackArtist);
			}
			$aData['trackArtist'] = $trackArtist;
			
			$featuring = $aData['featuring[]'];
			if(is_array($featuring))
			{
				$featuring = array_filter($featuring, function($value) {
					return !empty($value);
				});
				$featuring = implode(',',$featuring);
			}
			$aData['featuring'] = $featuring;
			
			$author = $aData['author[]'];
			if(is_array($author))
			{
				$author = array_filter($author, function($value) {
					return !empty($value);
				});
				$author = implode(',',$author);
			}
			$aData['author'] = $author;
			
			$composer = $aData['composer[]'];
			if(is_array($composer))
			{
				$composer = array_filter($composer, function($value) {
					return !empty($value);
				});
				$composer = implode(',',$composer);
			}
			$aData['composer'] = $composer;
			
			$produceBy = $aData['produceBy[]'];
			if(is_array($produceBy))
			{
				$produceBy = array_filter($produceBy, function($value) {
					return !empty($value);
				});
				$produceBy = implode(',',$produceBy);
			}
			$aData['produceBy'] = $produceBy;
			
			$track_id = $aData['track_id'];
			unset($aData['trackArtist[]']);
			unset($aData['featuring[]']);
			unset($aData['author[]']);
			unset($aData['composer[]']);
			unset($aData['produceBy[]']);
			unset($aData['audio_hidden']);
			unset($aData['track_id']);
			
			
			if($aData['isrc'] != '')
			{
				$aData['isrc'] = trim($aData['isrc']);
				$rowset7 = $projectTable->select(array("id !='".$track_id."' and isrc like '%".$aData['isrc']."%' "));
				$rowset7 = $rowset7->toArray();
				if(count($rowset7) > 0)
				{
					$result['DBStatus'] = 'EXIST';
					$result = json_encode($result);
					echo $result;
					exit;
				}
			}
					
			$projectTable->update($aData,array("id='".$track_id."' "));
			$result['DBStatus'] = 'OK';
		}
		else
        {
            $result['DBStatus'] = 'ERR';
        }
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function checkDuplicatePcnAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
       
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$projectTable = new TableGateway('tbl_release', $adapter);
		
		$rowset = $projectTable->select(array("id != '".$_POST['id']."' and pcn ='".$_POST['pcn']."' "));
		$rowset = $rowset->toArray();
		
		if(count($rowset) > 0)
			$result['DBStatus'] = 'EXIST';
		else
			$result['DBStatus'] = 'OK';
		
        $result = json_encode($result);
        echo $result;
        exit;
	}
    public function saveAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		$config = $this->getServiceLocator()->get('config');
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
				if($request->getPost("pAction") == "ADD")
				{
					$aData = json_decode($request->getPost("FORM_DATA"));
					$aData = (array)$aData;
					unset($aData['MASTER_KEY_ID']);
					$aData['status'] = 'draft';
					$aData['created_by']=$_SESSION['user_id'];
					$aData['created_on']=date("Y-m-d h:i:s");
					$aData['cover_img'] = $aData['filehidden'];
					
					$new_label_name = $aData['new_label_name'];
					unset($aData['new_label_name']);
					
					if($aData['labels'] == 'create-new')
					{
						$aData['labels'] = $this->createLabel($new_label_name);
					}
					
					if($_SESSION['user_id'] != '0')
						$aData['user_id'] = $_SESSION['user_id'];
					
					$releaseArtist = $aData['releaseArtist[]'];
					if(is_array($releaseArtist))
					{
						$releaseArtist = array_filter($releaseArtist, function($value) {
							return !empty($value);
						});
						$releaseArtist = implode(',',$releaseArtist);
					}
					$aData['releaseArtist'] = $releaseArtist;
                  $firstName = '';
if (!empty($aData['releaseArtist'])) {
    $tmp = explode(',', $aData['releaseArtist']);
    $firstName = trim($tmp[0] ?? '');
}
if ($firstName !== '') {
    $aData['primary_artist_id'] = $this->getOrCreateArtistIdByName($firstName, $adapter);
} else {
    $aData['primary_artist_id'] = null; // blank case
}
					
					$featuring = $aData['featuring[]'];
					if(is_array($featuring))
					{
						$featuring = array_filter($featuring, function($value) {
							return !empty($value);
						});
						$featuring = implode(',',$featuring);
					}
					$aData['featuring'] = $featuring;
					
					$date = explode('-',$aData['physicalReleaseDate']);
					$aData['physicalReleaseDate'] = $date[2].'-'.$date[1].'-'.$date[0];
					
					if($aData['pcn'] != '')
						$aData['pcn'] = trim($aData['pcn']);
					
					unset($aData['releaseArtist[]']);
					unset($aData['featuring[]']);
					unset($aData['filehidden']);
					$projectTable->insert($aData);
					$result['DBStatus'] = 'OK';
					$result['MY_ID'] = $projectTable->lastInsertValue;
				}
				else  if($request->getPost("pAction") == "EDIT")
				{
					$aData = json_decode($request->getPost("FORM_DATA"));
					$aData = (array)$aData;
					$iMasterID=$aData['MASTER_KEY_ID'];
					unset($aData['MASTER_KEY_ID']);
					
					$aData['cover_img'] = $aData['filehidden'];
					
					$new_label_name = $aData['new_label_name'];
					unset($aData['new_label_name']);
					
					if($aData['pcn'] != '')
						$aData['pcn'] = trim($aData['pcn']);
					
					if($aData['labels'] == 'create-new')
					{
						$aData['labels'] = $this->createLabel($new_label_name);
					}
					
					if($_SESSION['user_id'] != '0')
						$aData['user_id'] = $_SESSION['user_id'];
					
					$releaseArtist = $aData['releaseArtist[]'];
					if(is_array($releaseArtist))
					{
						$releaseArtist = array_filter($releaseArtist, function($value) {
							return !empty($value);
						});
						$releaseArtist = implode(',',$releaseArtist);
					}
					$aData['releaseArtist'] = $releaseArtist;
					
					$featuring = $aData['featuring[]'];
					if(is_array($featuring))
					{
						$featuring = array_filter($featuring, function($value) {
							return !empty($value);
						});
						$featuring = implode(',',$featuring);
					}
					$aData['featuring'] = $featuring;
					
					$date = explode('-',$aData['physicalReleaseDate']);
					$aData['physicalReleaseDate'] = $date[2].'-'.$date[1].'-'.$date[0];
					
					unset($aData['releaseArtist[]']);
					unset($aData['featuring[]']);
					unset($aData['filehidden']);
					$projectTable->update($aData,array("id=".$iMasterID));
					
					
					if($_REQUEST['approved'] == 'true')
					{
						$rowset = $projectTable->select(array("id='".$iMasterID."'"));
						$rowset = $rowset->toArray();
				
						$aData=array();
						$aData['status'] = 'delivered';
						$aData['in_process'] = 0;
						
						if($rowset[0]['import_flag'] == '1')
							$aData['import_flag'] = 2;
						
						$projectTable->update($aData,array("id=" . $iMasterID));
						
						$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
						$rowset3 = $rowset3->toArray();
						
						
						
							$content ='<h2 style="color: #333;">Hello,</h2>
							<p>We’re happy to let you know that we’ve approved the following Release:</p>
							
							<p><strong>Title:</strong> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' - '.$rowset[0]['upc'].'</p>
							
							<p>For digital products, please keep in mind that delivery of your products to stores will begin within 7 days and can be tracked in the delivery reports listed for each product in your catalogue. Your product should go live in no time but may take up to 4-6 weeks depending on turnaround times of each store.</p>
							
							<p>For physical products, the product information has been sent along to the appropriate sales agents and/or warehouses.</p>
							
							<br><br><br>
							<p><strong>Prime Content Management Team</strong></p>

							<p>Thanks and regards,<br>
							The Prime Digital Arena team</p>';
							
							
						
						foreach($rowset3 as $row3)
						{
							$nData = array();
							$nData['user_id'] = $row3['id'];
							$nData['type'] = 'Release Approved';
							$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been approved.';
							$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
							$notificationTable->insert($nData);
							
							$customObj->sendSmtpEmail($config,$row3['email'],'Congratulations Your Release has been approved.',$content,$row3['label_manager_email']);
						}
					}
					//$customObj->createlog("module='Newrelease',action='Newrelease ".$aData['name']." Edited',action_id='".$iMasterID."' ");
					$result['DBStatus'] = 'OK';
					$result['MY_ID'] =$iMasterID;
				}
        }
        else
        {
            $result['DBStatus'] = 'ERR';
        }
        $result = json_encode($result);
        echo $result;
        exit;
    }
	public function createLabel($name)
	{
		if($name == '')
			return 0;
		
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		
		$projectTable = new TableGateway('tbl_label', $adapter);
		$aData = array();
		$aData['name'] = $name;
		$aData['user_id'] = $_SESSION['user_id'];
		$aData['created_by'] = $_SESSION['user_id'];
		$projectTable->insert($aData);
		
		return $projectTable->lastInsertValue;
	}
    public function validateduplicateAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $tableName=$request->getPost('tableName');
            $ID=$request->getPost('KEY_ID');
            $fieldName=$request->getPost('fieldName');
            $sql = "select * from $tableName where $fieldName='".$ID."'";
            $optionalParameters = array();
            $statement = $adapter->createStatement($sql, $optionalParameters);
            $result = $statement->execute();
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            $rowset = $resultSet->toArray();
            if(count($rowset)>0) {
                $result1['recordsTotal'] = count($rowset);
                $result1['DBStatus'] = 'ERR';
                $result1 = json_encode($result1);
                echo $result1;
            }
        }
        exit;
    }
	
	public function generateISRC()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$projectTable = new TableGateway('tbl_settings', $adapter);
		$rowset = $projectTable->select();
		$rowset = $rowset->toArray();
		
		$last_isrc = $rowset[0]['last_isrc'];
		
		$new_isrc = $this->checkISRC($last_isrc);
		
		return $new_isrc;
	}
	public function generatePCN()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$projectTable = new TableGateway('tbl_settings', $adapter);
		$rowset = $projectTable->select();
		$rowset = $rowset->toArray();
		
		$last_pcn = $rowset[0]['last_pcn'];
		
		$new_pcn = $this->checkPCN($last_pcn);
		
		return $new_pcn;
	}
	public function checkISRC($last_isrc)
	{
		$last_digit = substr($last_isrc,-5);
		$year = substr($last_isrc, 7, 2);
		
		$code = 'IN-P3F';
		
		if($last_digit == '99999')
		{
			$year+=1;
			$last_digit = '00000'; // Reset last digits
		}
		else
		{
			if($year < date('y'))
			{
				$year = date('y');
				 $last_digit = '00000'; // Reset last digits
			}
		}
		
		$last_digit += 1;
		$last_digit = sprintf('%05d',$last_digit);
		
		$isrc = $code.'-'.$year.'-'.$last_digit;
		
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$projectTable = new TableGateway('tbl_track', $adapter);
		$rowset = $projectTable->select(array("isrc = '".$isrc."' "));
		$rowset = $rowset->toArray();
		if(count($rowset) > 0)
		{
			return $this->checkISRC($isrc);
		}
		else
		{
			return $isrc;
		}		
	}
	
	public function checkPCN($last_pcn)
	{
		$last_digit = substr($last_pcn,3,5);
		$year = substr($last_pcn,-2);
		
		$code = 'PDA';
		
		if($last_digit == '99999')
		{
			$year+=1;
			$last_digit = '00000'; // Reset last digits
		}
		else
		{
			if($year < date('y'))
			{
				$year = date('y');
				 $last_digit = '00000'; // Reset last digits
			}
		}
		
		$last_digit += 1;
		$last_digit = sprintf('%05d',$last_digit);
		
		$pcn = $code.''.$last_digit.''.$year;
		
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$projectTable = new TableGateway('tbl_release', $adapter);
		$rowset = $projectTable->select(array("pcn = '".$pcn."' "));
		$rowset = $rowset->toArray();
		if(count($rowset) > 0)
		{
			return $this->checkPCN($pcn);
		}
		else
		{
			return $pcn;
		}		
	}
public function fnGrid()
{
    /*
        * Script:    DataTables server-side script for PHP and MySQL
        * Copyright: 2010 - Allan Jardine, 2012 - Chris Wright
        * License:   GPL v2 or BSD (3-point)
        */
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = array('id','name','descriptions','used_flag' );
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_department";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['user']       = $arrDBInfo['username'];
    $gaSql['password']   = $arrDBInfo['password'];
    $gaSql['db']         = $arrDBInfo['db'];
    $gaSql['server']     = $arrDBInfo['host'];
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    /*
     * Local functions
     */
    $customObj = $this->CustomPlugin();
   $mysqli=$customObj->dbconnection();
    /*
     * Paging
     */
    $sLimit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
            intval( $_GET['iDisplayLength'] );
    }
    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
            }
        }
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = "";
    if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
            {
                $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string($_GET['sSearch_'.$i])."%' ";
        }
    }
	
	//Add deleted_flag
    if($sWhere=="")
        $sWhere=" where deleted_flag=0";
    else
        $sWhere.=" AND  deleted_flag=0";
    /*
     * SQL queries
     * Get data to display
     */
    $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
    ";
    $rResult = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    /* Data set length after filtering */
    $sQuery = "
        SELECT FOUND_ROWS()
    ";
    $rResultFilterTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
    /*
     * Output
     */
    $output = array(
        "sEcho" => intval(@$_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
    while ( $aRow = mysqli_fetch_array( $rResult ) )
    {
        $row = array();
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
            }
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        $output['aaData'][] = $row;
    }
    echo json_encode( $output );
}
public   function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }
 public function getsubgenreAction()    
 {        
	$SUBGENRE = array();
	$SUBGENRE['African'] = ['African','African - Afrikaans','African - Afro Bashment','African - Afro Pop','African - Afro Soul','African - Afro Trap','African - Afro Urbain','African - Afrobeat','African - Assiko','African - Bend-Skin','African - Benga','African - Bikutsi','African - Bongo-Flava','African - Coupé Décalé','African - Gospel','African - Gqom','African - Highlife / Hiplife','African - Jùjú music','African - Kizomba','African - Kuduro','African - Kwaito','African - Makossa','African - Mandingue','African - Maskandi','African - Mbalax','African - Ndombolo','African - RPK / Rap','African - Rumba Congolaise','African - Shangaan Electro','African - Soukouss','African - Taarab','African - Yoruba','African - Zouglou'];
	
	$SUBGENRE['Alternative'] = ["Alternative","Alternative - Electronic / EBM","Alternative - Emo Punk","Alternative - Gothic","Alternative - Grunge","Alternative - Indie Pop","Alternative - Indie Rock","Alternative - Latin","Alternative - Latin Ska","Alternative - New Wave","Alternative - Pop Punk","Alternative - Post Punk","Alternative - Punk","Alternative - Ska"];
	
	$SUBGENRE['Arabic'] = ["Arabic","Arabic - Algerian Raï","Arabic - Amazigh","Arabic - Arabesque","Arabic - Egyptian Chaabiat","Arabic - Egyptian Folk El Kaf Aswani","Arabic - Egyptian Folk Nubian","Arabic - Egyptian Folk Saidi","Arabic - Egyptian Indie","Arabic - Egyptian Mahraganat","Arabic - Egyptian Musicals","Arabic - Egyptian Operette","Arabic - Egyptian Patriotic","Arabic - Egyptian Plays","Arabic - Egyptian Satire","Arabic - Egyptian Soundtrack","Arabic - Egyptian Tarab Classical","Arabic - Egyptian Tarab Pop","Arabic - Egyptian TV Shows","Arabic - Electronic Dance Music","Arabic - Indie","Arabic - Iraqi Folk Dabka","Arabic - Iraqi Folk Khachaba","Arabic - Iraqi Folk Maqam","Arabic - Iraqi Folk Mawaweel","Arabic - Iraqi Folk Radeh","Arabic - Iraqi Indie","Arabic - Iraqi Musicals","Arabic - Iraqi Operette","Arabic - Iraqi Patriotic","Arabic - Iraqi Plays","Arabic - Iraqi Satire","Arabic - Iraqi Soundtrack","Arabic - Iraqi Tarab Classical","Arabic - Iraqi Tarab Pop","Arabic - Iraqi TV Shows","Arabic - Islamic","Arabic - Islamic Anasheed","Arabic - Islamic Chant","Arabic - Islamic Quran","Arabic - Islamic Ramadan","Arabic - Islamic Recital","Arabic - Islamic Speeches","Arabic - Khaleeji","Arabic - Khaleeji Folk Shakshaka","Arabic - Khaleeji Folk Swahili","Arabic - Khaleeji Indie","Arabic - Khaleeji Musicals","Arabic - Khaleeji Operette","Arabic - Khaleeji Patriotic","Arabic - Khaleeji Plays","Arabic - Khaleeji Satire","Arabic - Khaleeji Shayla","Arabic - Khaleeji Soundtrack","Arabic - Khaleeji Tarab Classical","Arabic - Khaleeji Tarab Pop","Arabic - Khaleeji TV Shows","Arabic - Kurdi","Arabic - Lebanese Folk Ataba & Mijana","Arabic - Lebanese Folk Dabke","Arabic - Lebanese Folk Zajal","Arabic - Lebanese Indie","Arabic - Lebanese Musicals","Arabic - Lebanese Operette","Arabic - Lebanese Patriotic","Arabic - Lebanese Plays","Arabic - Lebanese Satire","Arabic - Lebanese Soundtrack","Arabic - Lebanese Tarab Classical","Arabic - Lebanese Tarab Pop","Arabic - Lebanese TV Shows","Arabic - Levantine Folk Bedouin","Arabic - Levantine Folk New Dabke Syrian","Arabic - Levantine Folk Qoudud & Muwashahat","Arabic - Levantine Indie","Arabic - Levantine Musicals","Arabic - Levantine Operette","Arabic - Levantine Patriotic","Arabic - Levantine Plays","Arabic - Levantine Satire","Arabic - Levantine Soundtrack","Arabic - Levantine Tarab Classical","Arabic - Levantine Tarab Pop","Arabic - Levantine TV Shows","Arabic - Maalaya Folk Dagni","Arabic - Moroccan Chaabi","Arabic - Moroccan Raï","Arabic - Moroccan Sahraoui","Arabic - North African","Arabic - North African Folk Andalusi","Arabic - North African Folk Gnawa","Arabic - North African Folk Mezwed","Arabic - North African Folk Rif","Arabic - North African Indie","Arabic - North African Musicals","Arabic - North African Operette","Arabic - North African Patriotic","Arabic - North African Plays","Arabic - North African Raï Classical","Arabic - North African Satire","Arabic - North African Soundtrack","Arabic - North African Tarab Classical","Arabic - North African Tarab Pop","Arabic - North African TV Shows","Arabic - Raï","Arabic - Soufi","Arabic - Sudanese Folk Swahili","Arabic - Sudanese Indie","Arabic - Sudanese Musicals","Arabic - Sudanese Operette","Arabic - Sudanese Patriotic","Arabic - Sudanese Plays","Arabic - Sudanese Satire","Arabic - Sudanese Soundtrack","Arabic - Sudanese Tarab Classical","Arabic - Sudanese Tarab Pop","Arabic - Sudanese TV Shows","Arabic - Yemeni Folk Hadramawti","Arabic - Yemeni Folk Swahili","Arabic - Yemeni Indie","Arabic - Yemeni Musicals","Arabic - Yemeni Operette","Arabic - Yemeni Patriotic","Arabic - Yemeni Plays","Arabic - Yemeni Satire","Arabic - Yemeni Soundtrack","Arabic - Yemeni Tarab Classical","Arabic - Yemeni Tarab Pop","Arabic - Yemeni TV Shows"];
	
	$SUBGENRE['Asian'] = ["Asian","Asian - Chinese","Asian - Chinese / Tibetan Native","Asian - Chinese Folk","Asian - Filipino / Bikol","Asian - Filipino / Cebuano","Asian - Filipino / Chavacano","Asian - Filipino / Ilocano","Asian - Filipino / Kapampangan","Asian - Filipino / llonggo","Asian - Filipino / Pangasinan","Asian - Filipino / Tagalog","Asian - Filipino / Waray","Asian - Hong Kongese","Asian - Indonesian","Asian - Indonesian / Christian Pop","Asian - Indonesian / Keroncong","Asian - Indonesian / Koplo","Asian - Indonesian / Religious","Asian - Japanese","Asian - Korean","Asian - Malaysian","Asian - Malaysian / Chinese New Year","Asian - Malaysian / Christmas","Asian - Malaysian / Deepavali","Asian - Malaysian / Hari Raya","Asian - Malaysian / Traditional","Asian - OPM / 70’s Pinoy Music","Asian - OPM / 80’s Pinoy Music","Asian - OPM / 90’s Pinoy Music","Asian - OPM / Bisrock","Asian - OPM / Cebuano","Asian - OPM / Chavacano","Asian - OPM / Folk Songs / Country Music","Asian - OPM / Harana & Kundiman","Asian - OPM / Hiligaynon","Asian - OPM / Ilocano","Asian - OPM / Kapampangan","Asian - OPM / Pinoy Acoustic","Asian - OPM / Pinoy Ballad","Asian - OPM / Pinoy Love Songs","Asian - Pinoy Rock","Asian - Taiwanese","Asian - Taiwanese Folk","Asian - Thaï","Asian - Thaï / Folk Music","Asian - Thaï / Luk-Krung","Asian - Thaï / Luk-Thung","Asian - Thaï / Mo-Lam","Asian - Thaï / Song For Life","Asian - Vietnamese","Asian - Vietnamese / Bài tròi","Asian - Vietnamese / Bolero","Asian - Vietnamese / Ca Trù","Asian - Vietnamese / Cải Lương","Asian - Vietnamese / Chèo","Asian - Vietnamese / Đình Huế","Asian - Vietnamese / Đờn Ca Tài Tử","Asian - Vietnamese / Hò Huế","Asian - Vietnamese / Lý","Asian - Vietnamese / Nhạc Cung","Asian - Vietnamese / Quan Họ","Asian - Vietnamese / Trầu Văn","Asian - Vietnamese / Tuồng","Asian - Vietnamese / Xẩm"];

	$SUBGENRE['Blues'] = ["Blues","Blues - Contemporary Blues","Blues - Traditional Blues"];
	$SUBGENRE['Brazilian'] =  ["Brazilian","Brazilian - Arrocha","Brazilian - Axe","Brazilian - Baiao","Brazilian - Baile Funk","Brazilian - Bossa Nova","Brazilian - Brega","Brazilian - Calypso","Brazilian - Choro","Brazilian - Forro","Brazilian - Frevo","Brazilian - Lambada","Brazilian - MPB","Brazilian - Pagode","Brazilian - Samba","Brazilian - Sertanejo"];
	
	$SUBGENRE['Children Music'] = ["Children Music","Children Music - Holiday","Children Music - Lullabies","Children Music - Stories"];
	$SUBGENRE['Christian & Gospel'] = ["Christian & Gospel","Christian & Gospel - Arabic","Christian & Gospel - Arabic / Byzantine Chant","Christian & Gospel - Arabic / Christmas","Christian & Gospel - Arabic / Taranim & Tarateel","Christian & Gospel - Brazilian Gospel","Christian & Gospel - Christian","Christian & Gospel - Gospel","Christian & Gospel - Praise & Worship"];
	$SUBGENRE['Classical'] = ["Classical","Classical - Ballet","Classical - Baroque","Classical - Chamber","Classical - Choral","Classical - Concerto","Classical - Contemporary Era","Classical - Crossover","Classical - Early Music","Classical - Electronic","Classical - High Classical","Classical - Impressionist","Classical - Medieval","Classical - Minimalism","Classical - Modern Compositions","Classical - Opera","Classical - Orchestral","Classical - Religious","Classical - Renaissance","Classical - Romantic","Classical - Solo Instrumental","Classical - Solo Piano"];
	$SUBGENRE['Country'] = ["Country","Country - Contemporary","Country - Pop","Country - Traditional"];
	$SUBGENRE['Dance'] = ["Dance","Dance - Acid House","Dance - Afro House","Dance - Amapiano","Dance - Bass House","Dance - Big Room","Dance - Breaks","Dance - Classic House","Dance - Deep House","Dance - Detroit Techno","Dance - Disco Polo","Dance - DJ Mix","Dance - Down Beat / Trip Hop","Dance - Drum & Bass","Dance - Drum & Bass / Jungle","Dance - Dub","Dance - EDM / Commercial","Dance - Electro","Dance - Electro House","Dance - Funky / Groove / Jackin' House","Dance - Future House","Dance - Garage / Bassline / Grime","Dance - Hard Dance","Dance - Hard Techno","Dance - Hardcore","Dance - House","Dance - House - Organic / Downtempo","Dance - Indie Dance","Dance - Latin House","Dance - Mainstage","Dance - Melodic House & Techno","Dance - Minimal / Deep Tech","Dance - New Trance","Dance - Nu Disco / Disco","Dance - Progressive House","Dance - Psytrance","Dance - Rave","Dance - Soulful House","Dance - Tech House","Dance - Techno","Dance - Techno - Deep","Dance - Techno - Driving","Dance - Trance","Dance - Tropical House"];
	$SUBGENRE['Easy Listening'] = ["Easy Listening","Easy Listening - Lounge","Easy Listening - Swing","Easy Listening - Vocal"];
	$SUBGENRE['Electronic'] = ["Electronic","Electronic - Ambient","Electronic - Deep Dubstep / Grime","Electronic - Dubstep","Electronic - Electronica / Downtempo","Electronic - Experimental / Noise","Electronic - Lounge / Chillout","Electronic - Phonk","Electronic - Trap / Future Bass"];
	
	$SUBGENRE['Hip Hop/Rap'] = ["Hip Hop/Rap","Hip Hop/Rap - African Drill","Hip Hop/Rap - African Hip Hop","Hip Hop/Rap - Algerian Hip Hop","Hip Hop/Rap - Alternative","Hip Hop/Rap - Arabic Hip Hop","Hip Hop/Rap - Brazilian Hip Hop","Hip Hop/Rap - Canadian / Indigenous Hip Hop","Hip Hop/Rap - Chinese Hip Hop","Hip Hop/Rap - Cloud Rap / Sad Rap","Hip Hop/Rap - DJ Mix","Hip Hop/Rap - Dutch Hip Hop","Hip Hop/Rap - French Hip Hop","Hip Hop/Rap - French Trap","Hip Hop/Rap - German Hip Hop","Hip Hop/Rap - Grime","Hip Hop/Rap - Italian Hip Hop","Hip Hop/Rap - Jazz Hip Hop","Hip Hop/Rap - Khaleeji Hip Hop","Hip Hop/Rap - Latin Hip Hop","Hip Hop/Rap - Latin Trap","Hip Hop/Rap - LoFi","Hip Hop/Rap - Moroccan Hip Hop","Hip Hop/Rap - North African Hip Hop","Hip Hop/Rap - Old School Hip Hop","Hip Hop/Rap - Pop Urbaine","Hip Hop/Rap - Raggaeton","Hip Hop/Rap - Russian Hip Hop","Hip Hop/Rap - Swedish Hip Hop","Hip Hop/Rap - Taiwanese Hip Hop","Hip Hop/Rap - Thaï Hip Hop","Hip Hop/Rap - Trap","Hip Hop/Rap - Tunisian Hip Hop","Hip Hop/Rap - Turkish Hip Hop","Hip Hop/Rap - UK Hip Hop"];
	
	$SUBGENRE['Indian'] = ["Indian","Indian - Assamese","Indian - Assamese Soundtrack","Indian - Bengali","Indian - Bengali Soundtrack","Indian - Bhangra","Indian - Bhojpuri","Indian - Bhojpuri Soundtrack","Indian - Bollywood","Indian - Carnatic Classical","Indian - Carnatic Classical Instrumental","Indian - Children Song","Indian - Classical","Indian - Classical / Instrumental","Indian - Classical / Vocal","Indian - Devotional & Spiritual","Indian - Dialogue","Indian - DJ","Indian - Folk","Indian - Fusion","Indian - Gazal","Indian - Gujarati","Indian - Gujarati Soundtrack","Indian - Haryanvi","Indian - Haryanvi Soundtrack","Indian - Hindi","Indian - Hindi Non Soundtrack","Indian - Hindi Soundtrack","Indian - Hindustani Classical","Indian - Hindustani Classical Instrumental","Indian - Indigenous","Indian - Kannada","Indian - Kannada Soundtrack","Indian - Konkani","Indian - Malayalam","Indian - Malayalam Soundtrack","Indian - Mappila","Indian - Marathi","Indian - Marathi Soundtrack","Indian - Odia","Indian - Odia Soundtrack","Indian - Poetry","Indian - Pop & Fusion","Indian - Punjabi","Indian - Punjabi Soundtrack","Indian - Rabindra Sangeet","Indian - Rajasthani","Indian - Rajasthani Soundtrack","Indian - Regional Indian","Indian - Regional Indian Soundtrack","Indian - Sanskrit","Indian - Sanskrit Soundtrack","Indian - Speech","Indian - Sufi","Indian - Tamil","Indian - Tamil Soundtrack","Indian - Telugu","Indian - Telugu Soundtrack","Indian - Traditional","Indian - Urdu","Indian - Urdu Soundtrack"];
	
	$SUBGENRE['Jazz'] = ["Jazz","Jazz - Bebop","Jazz - Big Band","Jazz - Classic","Jazz - Contemporary","Jazz - Dixie / Rag Time","Jazz - Free Jazz","Jazz - Fusion","Jazz - Jazz Funk","Jazz - Latin Jazz","Jazz - Nu Jazz / Acid Jazz","Jazz - Oriental Jazz","Jazz - Smooth Jazz","Jazz - Swing","Jazz - Traditional","Jazz - World"];
	
	$SUBGENRE['Latin'] = ["Latin","Latin - Argentine Cuarteto","Latin - Argentine Cumbia","Latin - Argentine Folklore","Latin - Baladas","Latin - Boleros","Latin - Bossa Nova","Latin - Caribbean","Latin - Caribbean / Kompa","Latin - Caribbean / Traditional / Biguine / Mazurka","Latin - Caribbean / Zouk","Latin - Cuban","Latin - Ranchera","Latin - Reggaeton","Latin - Regional Mexicano","Latin - Salsa / Merengue","Latin - Son Jarocho","Latin - Tango","Latin - Tropical","Latin - Urban"];
	
	$SUBGENRE['Metal'] = ["Metal","Metal - Black Metal","Metal - Death Metal","Metal - Deathcore","Metal - Djent","Metal - Doom","Metal - Folk Metal","Metal - Goth","Metal - Grindcore","Metal - Hard Rock","Metal - Hardcore","Metal - Heavy metal","Metal - Industrial","Metal - Metalcore","Metal - Nu Metal","Metal - Post Black","Metal - Power Metal","Metal - Prog Folk","Metal - Proggressive Metal","Metal - Sludge","Metal - Symphonic Metal","Metal - Thrash Metal"];
	
	$SUBGENRE['Pop'] = ["Pop","Pop - Alternative","Pop - Arabic","Pop - Batak","Pop - Brazilian","Pop - Canadian / Indigenous","Pop - Cantopop","Pop - Chinese Ethno Pop","Pop - Colombian","Pop - Contemporary / Adult","Pop - Dance","Pop - Dangdut","Pop - Dutch","Pop - Egyptian","Pop - Electropop","Pop - Folk","Pop - French","Pop - French / Variété Française","Pop - French Zouk","Pop - German","Pop - Hokkien / Tawainese","Pop - Hyperpop","Pop - Indie","Pop - Indonesian","Pop - Iraqi","Pop - Islamic Pop / Arabic","Pop - Islamic Pop / Indonesian","Pop - Italo","Pop - J-Pop","Pop - Jawa","Pop - K-Pop","Pop - Khaleeji","Pop - Latin","Pop - Lebanese","Pop - Levantine","Pop - Malaysian Indo","Pop - Mandopop","Pop - Manele","Pop - Mexican","Pop - Minang","Pop - Mizrahi","Pop - North African","Pop - Pinoy","Pop - R&B","Pop - Rock","Pop - Russian Chanson","Pop - Schlager","Pop - Sertanejo Universitario","Pop - Singer Songwriter","Pop - Spanish","Pop - Sudanese","Pop - Sunda","Pop - Swedish","Pop - Thai","Pop - Turkish","Pop - Yemeni"];
	
	$SUBGENRE['R&B/Soul'] = [ "R&B/Soul", "R&B/Soul - Arabic", "R&B/Soul - Contemporary", "R&B/Soul - Disco", "R&B/Soul - Funk & Soul", "R&B/Soul - Hip Hop", "R&B/Soul - Latin"];
	
	$SUBGENRE['Reggae'] = ["Reggae","Reggae - Brazilian Reggae","Reggae - Caribbean Dancehall","Reggae - Caribbean Reggae","Reggae - Dancehall","Reggae - Dub","Reggae - Roots"];
	$SUBGENRE['Relaxation'] = ["Relaxation","Relaxation - Bali Spa","Relaxation - Meditation","Relaxation - World"];
	$SUBGENRE['Rock'] =  ["Rock","Rock - Alternative","Rock - Brit-Pop Rock","Rock - Classic","Rock - Experimental","Rock - Folk Rock","Rock - Garage","Rock - German Rock","Rock - J-Rock","Rock - Latin Rock","Rock - Noise Rock","Rock - Post Rock","Rock - Progressive","Rock - Psychedelic","Rock - Rock 'n' Roll","Rock - Rockabilly","Rock - Russian Rock","Rock - Shoegazing","Rock - Singer / Songwriter"];
	$SUBGENRE['Various'] = ["Various","Various - Audiobook","Various - Audiobook / Children","Various - Audiobook / Comedy","Various - Audiobook / Documentation / Discovery","Various - Audiobook / Fiction","Various - Audiobook / Guide","Various - Audiobook / Historical","Various - Audiobook / Novel","Various - Audiobook / Theatre","Various - Comedy","Various - Fitness & Workout","Various - Holiday","Various - Holiday / Christmas","Various - Karaoke","Various - Radio Show","Various - Radio Show - Hörspiele","Various - Sound Effects","Various - Soundtrack","Various - Soundtrack - Anime","Various - Soundtrack / Children","Various - Soundtrack / Movie","Various - Soundtrack / Musical","Various - Soundtrack / TV","Various - Speeches / Spoken Word"];
	$SUBGENRE['World Music / Regional Folklore'] = ["World Music / Regional Folklore","World Music / Regional Folklore - Australian","World Music / Regional Folklore - Cajun / Creole","World Music / Regional Folklore - Eastern European","World Music / Regional Folklore - Ethnic","World Music / Regional Folklore - Farsi","World Music / Regional Folklore - Flamenco","World Music / Regional Folklore - French","World Music / Regional Folklore - German / Volksmusik","World Music / Regional Folklore - Greek","World Music / Regional Folklore - Indian Ocean / Maloya","World Music / Regional Folklore - Indian Ocean / Séga","World Music / Regional Folklore - Irish / Celtic","World Music / Regional Folklore - Italian","World Music / Regional Folklore - Jewish Music","World Music / Regional Folklore - Klezmer","World Music / Regional Folklore - Mediterranean","World Music / Regional Folklore - Mizrahi","World Music / Regional Folklore - Nordic","World Music / Regional Folklore - North American","World Music / Regional Folklore - Pacific Island","World Music / Regional Folklore - Polish","World Music / Regional Folklore - Russian Folk","World Music / Regional Folklore - Russian Poetry","World Music / Regional Folklore - Spanish","World Music / Regional Folklore - Sufi & Ghazals","World Music / Regional Folklore - Turkish / Fantezi","World Music / Regional Folklore - Turkish / Halk","World Music / Regional Folklore - Turkish / Sanat","World Music / Regional Folklore - Turkish / Özgün","World Music / Regional Folklore - Western Europe",
    "World Music / Regional Folklore - Worldbeat"];
	
	$genre = $_REQUEST['genre'];
	
	
	foreach($SUBGENRE[$genre] as $value)
	{
		$info =array();
		$info['id'] = $value;
		$info['name'] = $value;
		$rowset[] = $info;
	}

	
	 $result1['DBData'] = $rowset;        
	 $result1['recordsTotal'] = count($rowset);        
	 $result1['DBStatus'] = 'OK';        
	 $result = json_encode($result1);        echo $result;        exit;     
 }
 
	public function addDefaultInfo($master_id,$track_id)
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$trackTable = new TableGateway('tbl_track', $adapter);
		$releaseTable = new TableGateway('tbl_release', $adapter);
		$rowset = $releaseTable->select(array("id='".$master_id."'"));
		$rowset = $rowset->toArray();
		
		$aData=array();
		$aData['songname'] = $rowset[0]['title'];
		$aData['trackArtist'] = $rowset[0]['releaseArtist'];
		$aData['featuring'] = $rowset[0]['featuring'];
		$aData['version'] = $rowset[0]['version'];
		$aData['pLine'] = $rowset[0]['pLine'];
		$aData['productionYear'] = $rowset[0]['productionYear'];
		
		$trackTable->update($aData,array("id='".$track_id."' "));
		
	}
	public function  addTrackAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
			
			$master_id = $request->getPost("master_id");
			$volume = $request->getPost("volume");
			
			
			$rowset = $projectTable->select(array("master_id = '".$master_id."' and volume='".$volume."' "));
			$rowset = $rowset->toArray();
			$order_id = 0;
			
			if(count($rowset) == 1 && $rowset[0]['order_id'] == 0)
			{
				$order_id = 1;
				
				$aData=array();
				$aData['master_id']=$master_id;
				$aData['volume']=$volume;
				$aData['order_id']=1;
				$projectTable->update($aData,array("master_id = '".$master_id."' and volume='".$volume."'  and order_id=0 "));
				
				$track_id = $rowset[0]['id'];
			}
			else
			{
				$order_id = count($rowset)+1;
				$aData=array();
				$aData['master_id']=$master_id;
				$aData['volume']=$volume;
				$aData['order_id']=$order_id;
				$projectTable->insert($aData);
				
				$track_id = $projectTable->lastInsertValue;
				
				$this->addDefaultInfo($master_id,$track_id);
			}
			
			
			
			$rowset = $projectTable->select(array("master_id = '".$master_id."' order by volume desc limit 1 "));
			$rowset = $rowset->toArray();
			
			$add_new_volume=false;
			if($volume == $rowset[0]['volume'])
			{
				$aData=array();
				$aData['master_id']=$master_id;
				$aData['volume']=$volume+1;
				$projectTable->insert($aData);
				$next_track_id = $projectTable->lastInsertValue;
				$this->addDefaultInfo($master_id,$next_track_id);
			
				$add_new_volume=true;
			}
			
			$result['track_id'] = $track_id;
			$result['add_new_volume'] = $add_new_volume;
			
			$new_volume = $volume+1;
			
			
			$result['add_new_volume_html'] = '<div class="volume-header clearfix" rel="'.$new_volume.'">
							<h4 style="float: left;">Volume '.$new_volume.'</h4>
							<div style="float: left; margin-left: 30px; margin-top: 5px;">
							<a class="btn btn-sm btn-default addTrackLink addNewTrackLink" volume="'.$new_volume.'"  href="javascript:;">
							<span class="glyphicon glyphicon-plus"></span> Add track</a></div>
						</div>
						<ul id="release-tracks-volume-'.$new_volume.'" class="volume ui-sortable"  data-volume="'.$new_volume.'"></ul>
						';
						
			$result['add_new_track_html'] = '<li class="track" id="track_'.$track_id.'">
										<div class="row">	
											<div class="col-md-1">
												<span class="glyphicon glyphicon-move handle"></span>&nbsp;&nbsp;&nbsp;&nbsp;
												<span class="glyphicon glyphicon-music"></span>&nbsp;&nbsp;&nbsp;&nbsp;
											</div>
											<div class="col-md-3">
												<span class="trackNumber">'.$order_id.'</span>.&nbsp;<span class="trackName">New track</span>
												<div class="trackIsrc text-muted" style="display:block"></div>
											</div>
											<div class="col-md-2"></div>
											<div class="col-md-6" style="text-align: right;">
													<a track_id="'.$track_id.'" onclick="return false;" class="btn btn-sm btn-default editTrackLink">
													<span class="glyphicon glyphicon-pencil"></span>&nbsp;Edit								</a>&nbsp;
												<a track_id="'.$track_id.'" class="btn btn-sm btn-default deleteTrackLink" onclick="return false;"  style="">
													<span class="glyphicon glyphicon-remove"></span>&nbsp;Delete								</a>
												<a track_id="'.$track_id.'" class="btn btn-sm btn-default moveTrackLink" onclick="return false;" data-placement="left" href="#" data-original-title="" title="">
													<span class="glyphicon glyphicon-arrow-right"></span>&nbsp;Move to another volume								</a>
											</div>
										</div>
								</li>';
			$result['DBStatus'] = 'OK';
			$result = json_encode($result);
			echo $result;
			exit;
            
        }
    }
	
	public  function create_thumbnail($source_path, $target_path, $thumb_width, $thumb_height) 
	{
		// Get image dimensions and type
		list($width, $height, $type) = getimagesize($source_path);

		// Create a new image resource based on the original image type
		switch ($type) {
			case IMAGETYPE_JPEG:
				$source_image = imagecreatefromjpeg($source_path);
				break;
			case IMAGETYPE_PNG:
				$source_image = imagecreatefrompng($source_path);
				break;
			case IMAGETYPE_GIF:
				$source_image = imagecreatefromgif($source_path);
				break;
			default:
				die("Unsupported image type");
		}

		// Create a blank canvas for the thumbnail
		$thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);

		// Maintain aspect ratio
		$aspect_ratio = $width / $height;
		if ($thumb_width / $thumb_height > $aspect_ratio) {
			$new_width = $thumb_height * $aspect_ratio;
			$new_height = $thumb_height;
		} else {
			$new_width = $thumb_width;
			$new_height = $thumb_width / $aspect_ratio;
		}

		$x = ($thumb_width - $new_width) / 2;
		$y = ($thumb_height - $new_height) / 2;

		// Resize and copy the original image to the thumbnail
		imagecopyresampled(
			$thumbnail,
			$source_image,
			$x, $y,
			0, 0,
			$new_width, $new_height,
			$width, $height
		);

		// Save the thumbnail
		switch ($type) {
			case IMAGETYPE_JPEG:
				imagejpeg($thumbnail, $target_path, 90); // Save as JPEG
				break;
			case IMAGETYPE_PNG:
				imagepng($thumbnail, $target_path); // Save as PNG
				break;
			case IMAGETYPE_GIF:
				imagegif($thumbnail, $target_path); // Save as GIF
				break;
		}

		// Clean up
		imagedestroy($source_image);
		imagedestroy($thumbnail);

		return $target_path;
	}
 
}//End Class