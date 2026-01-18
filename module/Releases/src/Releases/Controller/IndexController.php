<?php
namespace Releases\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

// ✅ Load Composer autoloader only ONCE, ideally in index.php or Module.php
require_once realpath('/home/primebackstage/htdocs/www.primebackstage.in/rk/vendor/autoload.php');

// ✅ Use PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IndexController extends AbstractActionController
{
    protected $studentTable; 
    public function indexAction()
    {
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$cond='';
		if($_SESSION['user_id'] != '0' && $_SESSION["STAFFUSER"] == 0 )
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			
			$subUsers = $this->getSubUsers();
			
			$cond.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) OR created_by in (".$subUsers.") )";
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$cond.= $staff_cond;
		}
		
		$sql="select count(*) as cnt from tbl_release where 1=1 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$total_tracks = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='inreview' and in_process=0 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$in_review = $rowset[0]['cnt'];
		
		
		$sql="select count(*) as cnt from tbl_release where status='inreview' and in_process=1 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$in_process = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='delivered'  $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$live_tracks = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='taken out' $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$taken_down = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='draft' $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$draft = $rowset[0]['cnt'];


		$INFO['total_tracks'] = $total_tracks;
		$INFO['in_review'] = $in_review;
		$INFO['in_process'] = $in_process;
		$INFO['live_tracks'] = $live_tracks;
		$INFO['draft'] = $draft;
		$INFO['taken_down'] = $taken_down;
		$viewModel= new ViewModel(array(
			'INFO' => $INFO,
				
		));
		return   $viewModel;	
		
    }

private function ftpUploadStrict(string $localPath, string $remoteDir, string $remoteName, array $cfg): array
{
    $host = trim((string)($cfg['host'] ?? ''));
    $user = trim((string)($cfg['user'] ?? ''));
    $pass = rtrim((string)($cfg['pass'] ?? ''));
    $port = (int)($cfg['port'] ?? 21);
    $root = (string)($cfg['root'] ?? '/');

    if ($host==='' || $user==='' || $pass==='') {
        return ['ok'=>false,'msg'=>'FTP creds incomplete'];
    }

    if (!function_exists('ftp_connect')) {
        return ['ok'=>false,'msg'=>'php-ftp extension missing'];
    }
    $c = @ftp_connect($host, $port, 60);
    if (!$c) return ['ok'=>false,'msg'=>'FTP connect failed'];
    if (!@ftp_login($c, $user, $pass)) { @ftp_close($c); return ['ok'=>false,'msg'=>'FTP auth failed']; }

    ftp_pasv($c, true);
    @ftp_set_option($c, FTP_TIMEOUT_SEC, 600);
    @set_time_limit(0);

    // chdir to root (e.g. /IMPORT_BACKSTAGE)
    $root = rtrim(str_replace('\\','/',$root), '/');
    if ($root !== '' && $root !== '/') {
        if (!@ftp_chdir($c, $root)) { @ftp_close($c); return ['ok'=>false,'msg'=>"Cannot chdir to root: $root"]; }
    } else {
        @ftp_chdir($c, '/');
    }

    // ---- enter existing release folder: allow "ID_*" prefix match ----
    $want = trim($remoteDir, '/');
    if ($want === '') { @ftp_close($c); return ['ok'=>false,'msg'=>'Empty remote folder']; }

    // If remoteDir has multiple segments, handle step-by-step
    foreach (explode('/', $want) as $seg) {
        if ($seg === '') continue;

        // 1) try exact
        if (@ftp_chdir($c, $seg)) continue;

        // 2) fallback: list and find "SEG_*" (underscore/space/dash allowed)
        $list   = @ftp_nlist($c, '.');
        $picked = null;
        if (is_array($list)) {
            foreach ($list as $entry) {
                $name = basename($entry);
                if (preg_match('~^' . preg_quote($seg, '~') . '([ _-].*)?$~i', $name)) {
                    $picked = $name;
                    break;
                }
            }
        }
        if (!$picked || !@ftp_chdir($c, $picked)) {
            $here = @ftp_pwd($c);
            @ftp_close($c);
            return ['ok'=>false,'msg'=>"Remote folder not found: {$seg} (cwd: {$here}). Tip: folder usually looks like {$seg}_<title>"];
        }
    }

    // safe filename (no slashes)
    $remoteName = str_replace(['/', '\\'], '_', $remoteName);

    // upload
    $ok  = @ftp_put($c, $remoteName, $localPath, FTP_BINARY);
    $msg = $ok ? '' : 'Upload failed';
    @ftp_close($c);
    return ['ok'=>$ok,'msg'=>$msg];
}
/** filesystem-safe basename */
private function fsSafe(string $s): string
{
    $s = preg_replace('~[^\w\-\.\s\(\)\[\]]+~u', ' ', $s);
    $s = trim(preg_replace('~\s+~u', ' ', $s));
    return $s !== '' ? $s : 'file';
}

/**
 * Save Believe folder id (digits only).
 * Route: /releases/savebelievefolder  (POST: release_id, believe_folder)
 */
public function savebelievefolderAction()
{
    if (!$this->getRequest()->isPost()) return $this->getResponse()->setStatusCode(405);
    header('Content-Type: application/json; charset=utf-8');
    try {
        $rid = (int)$this->params()->fromPost('release_id', 0);
        $val = trim((string)$this->params()->fromPost('believe_folder', ''));
        if ($rid <= 0) throw new \Exception('Bad release id');
        if (!preg_match('/^\d{3,}$/', $val)) throw new \Exception('Invalid folder id');

        $tbl = new \Zend\Db\TableGateway\TableGateway('tbl_release', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $tbl->update(['believe_folder' => $val], ['id' => $rid]);

        echo json_encode(['status'=>'OK']); exit;
    } catch (\Throwable $e) {
        error_log('[savebelievefolder] '.$e->getMessage());
        echo json_encode(['status'=>'ERR','msg'=>$e->getMessage()]); exit;
    }
}

/**
 * Transfer one track’s WAV to Believe (existing folder only).
 * Route: /releases/transfertrack  (POST: track_id, file_url)
 * Uses ONLY the .wav href from the row (no DB audio).
 */
public function transfertrackAction()
{
    if (!$this->getRequest()->isPost()) return $this->getResponse()->setStatusCode(405);
    header('Content-Type: application/json; charset=utf-8');

    try {
        // --- inputs ---
        $trackId = (int)$this->params()->fromPost('track_id', 0);
        $fileUrl = trim((string)$this->params()->fromPost('file_url', ''));
        if ($trackId <= 0) throw new \Exception('Missing track_id');
        if ($fileUrl === '') throw new \Exception('Missing file_url');

        // --- config ---
        $sl     = $this->getServiceLocator();
        $config = $sl->get('config');
        $ftpCfg = $config['BELIEVE_FTP'] ?? null;
        if (!$ftpCfg) throw new \Exception('FTP config missing');

        // --- resolve local .wav from href only ---
        $pathPart = parse_url($fileUrl, PHP_URL_PATH);
        if (!$pathPart) $pathPart = $fileUrl; // handle relative
        if (!preg_match('~/(public/uploads/[^?#]+\.wav)$~i', $pathPart, $m)) {
            throw new \Exception('Link must point to a .wav under /public/uploads/');
        }
        $relPath = $m[1]; // e.g. public/uploads/audio/abc.wav
        $local   = rtrim($config['PATH'], '/').'/'.$relPath;

        $real = realpath($local);
        $root = realpath($config['PATH'].'public/uploads');
        if (!$real || !$root || strpos($real, $root) !== 0) {
            throw new \Exception('Invalid file path.');
        }
        if (!is_file($real) || filesize($real) < 4096) {
            throw new \Exception('WAV missing or too small.');
        }

        // --- fetch track/release (for naming + folder id) ---
        $adapter  = $sl->get('Zend\Db\Adapter\Adapter');
        $tTrack   = new \Zend\Db\TableGateway\TableGateway('tbl_track',   $adapter);
        $tRelease = new \Zend\Db\TableGateway\TableGateway('tbl_release', $adapter);

        $tr = $tTrack->select(['id'=>$trackId])->current();
        if (!$tr) throw new \Exception('Track not found');

        $rel = $tRelease->select(['id'=>(int)$tr['master_id']])->current();
        if (!$rel) throw new \Exception('Release not found');

        // --- Believe folder (must already exist; one per release) ---
        $remoteDir = trim((string)($rel['believe_folder'] ?? ''));
        if ($remoteDir === '' || !preg_match('/^\d{3,}$/', $remoteDir)) {
            echo json_encode([
                'status'     => 'NEED_FOLDER',
                'release_id' => (int)$tr['master_id'],
                'msg'        => 'Believe folder id required'
            ]);
            exit;
        }

        // --- Remote filename: keep original from link ---
$origName = basename($relPath);                   // e.g. "Assalam Mere Hussain.wav"
$remoteFile = preg_replace('~[\x00-\x1F\\\\/:*?"<>|]+~', '_', $origName);
// (basically only Windows/FTP reserved chars ko _ kar diya; baaki jaisa hai waisa)
        // --- Upload (STRICT: no mkdir) ---
        $res = $this->ftpUploadStrict($real, $remoteDir, $remoteFile, $ftpCfg);
        if (!$res['ok']) throw new \Exception($res['msg']);

        echo json_encode([
            'status'      => 'OK',
            'msg'         => 'Transferred',
            'remote_dir'  => $remoteDir,
            'remote_file' => $remoteFile
        ]); exit;

    } catch (\Throwable $e) {
        error_log('[transfertrack] '.$e->getMessage());
        echo json_encode(['status'=>'ERR','msg'=>$e->getMessage()]); exit;
    }
}

public function exportmetaAction()
{
    $config  = $this->getServiceLocator()->get('config');
    $request = $this->getRequest();
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    if (!$request->isPost()) {
        return $this->getResponse()->setStatusCode(405);
    }

    $ids = array_map('intval', (array)$request->getPost('ids', []));
    if (empty($ids)) { echo json_encode(['status'=>'ERROR','message'=>'No IDs provided.']); exit; }
    $idList = implode(',', $ids);

    try {
        $template = $config['PATH'].'public/templates/METADATA-NEW-TEMPLATE-EN.xlsx';
        if (!file_exists($template)) $template = $config['PATH'].'public/templates/METADATA-TEMPLATE.xlsx';
        if (!file_exists($template)) throw new \Exception('Template not found');

        $outputFile = 'METADATA-'.time().'.xlsx';
        $outputPath = $config['PATH'].'public/exports/'.$outputFile;
        if (!copy($template, $outputPath)) throw new \Exception('Failed to copy template.');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($outputPath);

        // ---------- helpers ----------
        $mapExplicit = ['0'=>'No','1'=>'Yes','2'=>'Cleaned'];
        $langMap = [
            'EN'=>'English','UR'=>'Urdu','HI'=>'Hindi','PA'=>'Punjabi','AR'=>'Arabic','FA'=>'Persian',
            'INS'=>'Instrumental','INSTR'=>'Instrumental','0'=>''
        ];
        $fmtDate = function($d){ if(!$d || $d==='0000-00-00')return ''; $ts=strtotime($d); return $ts?date('m/d/Y',$ts):''; };
        $splitNames = function($s){ $out=[]; foreach(preg_split('/,|&|;/u',(string)$s) as $p){ $p=trim($p); if($p!=='')$out[]=$p; } return $out; };
        $normalize = function($s){ if($s===null)return ''; $s=(string)$s; $s=str_replace(["\xC2\xA0","\xE2\x80\x8B","\xE2\x80\x8C","\xE2\x80\x8D"],' ',$s); $s=preg_replace('/\s+/u',' ',$s); return trim($s); };
        $looksLikeId = function($v){ $v=trim((string)$v); if($v==='')return false; if(ctype_digit($v))return true; return (bool)preg_match('/^[A-Za-z0-9]{16,}$/',$v); };

        // ---------- quick table-exists ----------
        $tableExists = function($name) use ($adapter){
            try{
                $stmt=$adapter->createStatement("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",[$name]);
                $res=$stmt->execute(); return ($res && $res->current());
            }catch(\Throwable $e){ return false; }
        };

        // ---------- get release-level VERSION as fallback ----------
        $releaseVersionById = [];
        try {
            $stmt = $adapter->createStatement("SELECT id, version FROM view_release WHERE id IN ($idList)");
            $res  = $stmt->execute();
            foreach ($res as $row) {
                $v = trim((string)$row['version']);
                if ($v!=='' && $v!=='0' && strtoupper($v)!=='NA') $releaseVersionById[(int)$row['id']] = $v;
            }
        } catch (\Throwable $e) { /* ignore */ }

        // ---------- fetch tracks (ordered) ----------
        $trackTable = new \Zend\Db\TableGateway\TableGateway('view_tracks', $adapter);
        $select = new \Zend\Db\Sql\Select($trackTable->getTable());
        // keep all columns from view_tracks + a few from tbl_track (join fallback)
        $select->join(
            ['tt'=>'tbl_track'],
            'tt.id = view_tracks.id',
            [
                'trackPrice'       => 'trackPrice',
                'preview_start'    => 'preview_start',
                't_version_join'   => 'version',
                't_remixer_join'   => 'remixer', // <-- NEW: bring remixer from tbl_track
            ],
            'left'
        );
        $select->where("view_tracks.master_id IN ($idList) AND view_tracks.order_id > 0");
        $select->order(['view_tracks.volume ASC','view_tracks.order_id ASC']);
        $tracks = $trackTable->selectWith($select)->toArray();
        if (!$tracks) throw new \Exception('No tracks found for given IDs.');

        // ---------- genre lookups ----------
        $genreNameById=[]; $subGenreNameById=[];
        $fetchMap = function($table,$idCol,$nameCol) use ($adapter){
            $map=[]; try{
                $stmt=$adapter->createStatement("SELECT `$idCol` AS id, `$nameCol` AS name FROM `$table`");
                foreach($stmt->execute() as $row){ $map[(string)$row['id']]=$row['name']; }
            }catch(\Throwable $e){}
            return $map;
        };
        if ($tableExists('tbl_genre'))     $genreNameById    = $fetchMap('tbl_genre','id','name');
        if ($tableExists('tbl_genres'))    $genreNameById    = $genreNameById ?: $fetchMap('tbl_genres','id','name');
        if ($tableExists('tbl_subgenre'))  $subGenreNameById = $fetchMap('tbl_subgenre','id','name');
        if ($tableExists('tbl_subgenres')) $subGenreNameById = $subGenreNameById ?: $fetchMap('tbl_subgenres','id','name');

        $resolveGenre = function($v) use ($genreNameById){ $s=trim((string)$v); return ($s!=='' && ctype_digit($s) && isset($genreNameById[$s]))?$genreNameById[$s]:$s; };
        $resolveSub   = function($v) use ($subGenreNameById){ $s=trim((string)$v); return ($s!=='' && ctype_digit($s) && isset($subGenreNameById[$s]))?$subGenreNameById[$s]:$s; };

        // ---------- per-release cache ----------
        $rel=[]; $trackCountByRelease=[];
        foreach ($tracks as $t) {
            $mid=$t['master_id'];
            if (!isset($rel[$mid])) {
                $title = $t['title'] ?? '';
                $rel[$mid] = [
                    'title'               => $title,
                    'title_norm'          => $normalize($title),
                    'label_name'          => $t['label_name'] ?? '',
                    'productionYear'      => $t['productionYear'] ?? '',
                    'pLine'               => $t['pLine'] ?? '',
                    'cLine'               => $t['cLine'] ?? '',
                    'mainGenre'           => $resolveGenre($t['mainGenre'] ?? ''),
                    'subgenre'            => $resolveSub($t['subgenre'] ?? ''),
                    'digitalReleaseDate'  => $fmtDate($t['digitalReleaseDate'] ?? ''),
                    'physicalReleaseDate' => $fmtDate($t['physicalReleaseDate'] ?? ''),
                    'upc'                 => $t['upc'] ?? '',
                    'pcn'                 => $t['pcn'] ?? '',
                    'format'              => '',
                ];
                $trackCountByRelease[$mid]=0;
            }
            $trackCountByRelease[$mid]++;
        }
        foreach ($trackCountByRelease as $mid=>$cnt) {
            $rel[$mid]['format']=($cnt<=1)?'SINGLE':(($cnt<=6)?'EP':'ALBUM');
        }

        // ---------- Metadatas sheet ----------
        $meta = $spreadsheet->getSheetByName('Metadatas') ?: $spreadsheet->getActiveSheet();
        $highest = $meta->getHighestRow();
        if ($highest > 2) { $meta->removeRow(3, $highest - 2); }

        $getTrackPrice = function(array $t, callable $normalize){
            foreach ([$t['trackPrice']??null,$t['track_price']??null,$t['track_price_tier']??null] as $v){
                $v=$normalize($v); if($v!=='' && $v!=='0') return $v;
            } return '';
        };

        // ✅ VERSION resolver (view -> any key containing "version" -> tbl_track.version -> view_release.version)
        $getVersion = function(array $t, int $masterId) use ($normalize,$releaseVersionById){
            // 1) common keys first
            $candidates = [
                'version','track_version','trackVersion','song_version','songVersion','ver',
                'v_version','TRACK_VERSION','t_version_join' // join fallback (tbl_track.version)
            ];
            // 2) plus any key that contains "version" (case-insensitive)
            foreach (array_keys($t) as $k) {
                if (stripos($k, 'version') !== false && !in_array($k, $candidates, true)) {
                    $candidates[] = $k;
                }
            }
            foreach ($candidates as $k) {
                if (array_key_exists($k, $t)) {
                    $v = $normalize($t[$k]);
                    if ($v !== '' && $v !== '0' && strtoupper($v) !== 'NA') return $v;
                }
            }
            // 3) release-level fallback
            if (isset($releaseVersionById[$masterId])) {
                $v = $normalize($releaseVersionById[$masterId]);
                if ($v !== '' && $v !== '0' && strtoupper($v) !== 'NA') return $v;
            }
            return '';
        };

        $r=3;
        foreach ($tracks as $t) {
            $m=$rel[$t['master_id']];
            $lyricsLang = $langMap[$t['idLyricsSelect'] ?? ''] ?? ($t['idLyricsSelect'] ?? '');
            $titleLang  = $langMap[$t['metadataLanguage'] ?? ''] ?? ($t['metadataLanguage'] ?? '');
            $exp        = $mapExplicit[$t['explicitContent'] ?? ''] ?? '';
            $isrc = trim((string)($t['isrc'] ?? '')); if ($isrc==='0' || strtoupper($isrc)==='NA') $isrc='';
            $trackPrice = $getTrackPrice($t,$normalize);
            $ver        = $getVersion($t, (int)$t['master_id']);   // <-- resolved version

            $meta->setCellValue("A$r",$t['order_id']);
            $meta->setCellValue("B$r",$t['songName']);
            $meta->setCellValue("C$r",$ver);                // ✅ Version (resolved)
            $meta->setCellValue("D$r",$t['volume']);
            $meta->setCellValue("E$r",$m['title_norm']);
            $meta->setCellValue("F$r",$m['label_name']);
            $meta->setCellValue("G$r",$m['productionYear']);
            $meta->setCellValue("H$r",$m['pLine']);
            $meta->setCellValue("I$r",$m['cLine']);
            $meta->setCellValue("J$r",$m['mainGenre']);
            $meta->setCellValue("K$r",$m['subgenre']);
            $meta->setCellValue("L$r",$t['trackType']);
            $meta->setCellValue("M$r",$lyricsLang);
            $meta->setCellValue("N$r",$titleLang);
            $meta->setCellValue("O$r",$exp);
            $meta->setCellValue("P$r",'');
            $meta->setCellValue("Q$r",'');
            if ($trackPrice!=='') {
                $meta->setCellValueExplicit("R$r",$trackPrice,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else { $meta->setCellValue("R$r",''); }
            $meta->setCellValue("S$r",$m['digitalReleaseDate']);
            $meta->setCellValue("T$r",$m['physicalReleaseDate']);
            $meta->setCellValue("U$r",$t['preview_start']);
            $meta->setCellValue("V$r",$isrc);
            $meta->setCellValue("W$r",$m['upc']);
            $meta->setCellValue("X$r",'');
            $meta->setCellValue("Y$r",'');
            $meta->setCellValue("Z$r",$m['pcn']);
            $meta->setCellValue("AA$r",'');
            $meta->setCellValue("AB$r",'');
            $meta->setCellValue("AC$r",'');
            $meta->setCellValue("AD$r",'');
            $meta->setCellValue("AE$r",'');
            $meta->setCellValue("AF$r",'');
            $r++;
        }

        // ======= Contributors =======
        $roleAliases = [
            'main artist'=>'Main artist','primary artist'=>'Main artist',
            'feat'=>'Featuring','featuring'=>'Featuring',
            'writer'=>'Author','lyricist'=>'Author',
            'composer'=>'Composer',
            'remixer'=>'Remixer','remix'=>'Remixer','remixed by'=>'Remixer', // more forgiving
            'producer'=>'Producer','arranger'=>'Arranger',
            'publisher'=>'Publisher','publishing'=>'Publisher','editor'=>'Publisher',
        ];
        $normalizeRole = function($role) use ($normalize,$roleAliases){
            $r=strtolower($normalize($role)); return $roleAliases[$r] ?? null;
        };

        $byTrackRoleNames=[]; $trackIds=array_values(array_unique(array_column($tracks,'id')));

        $loadSideTable=function($table,$colName) use ($adapter,$trackIds,&$byTrackRoleNames,$normalize,$normalizeRole,$looksLikeId){
            if (empty($trackIds)) return 0;
            $in=implode(',',array_map('intval',$trackIds));
            try{
                $stmt=$adapter->createStatement("SELECT track_id, role, `$colName` AS name FROM `$table` WHERE track_id IN ($in)");
                $res=$stmt->execute(); $cnt=0;
                foreach($res as $row){
                    $roleFixed=$normalizeRole($row['role']); if(!$roleFixed) continue;
                    $name=$normalize($row['name']); if($name==='' || $looksLikeId($name)) continue;
                    $tid=(int)$row['track_id'];
                    $byTrackRoleNames[$tid][$roleFixed][$name]=true; $cnt++;
                }
                return $cnt;
            } catch(\Throwable $e){ return 0; }
        };
        $loaded=0;
        if ($tableExists('view_track_contributors')) $loaded=$loadSideTable('view_track_contributors','contributor_name');
        if (!$loaded && $tableExists('tbl_track_contributor')) $loaded=$loadSideTable('tbl_track_contributor','name');
        if (!$loaded && $tableExists('tbl_track_people'))      $loaded=$loadSideTable('tbl_track_people','name');

        foreach ($tracks as $t) {
            $tid=(int)$t['id'];
            $addFrom=function($val,$roleFixed) use (&$byTrackRoleNames,$tid,$normalize,$splitNames,$looksLikeId){
                foreach($splitNames($val) as $nm){
                    $nm=$normalize($nm); if($nm==='' || $looksLikeId($nm)) continue;
                    $byTrackRoleNames[$tid][$roleFixed][$nm]=true;
                }
            };
            $maybe=function($field,$role) use ($t,$addFrom,$tid,&$byTrackRoleNames){
                if (empty($byTrackRoleNames[$tid][$role])) $addFrom($t[$field]??'',$role);
            };
            $maybe('trackArtist','Main artist');
            $maybe('featuring','Featuring');
            $maybe('composer','Composer');
            $maybe('author','Author');

            // ---- Remixer fix: prefer view_tracks.remixer; fallback to joined tbl_track.remixer
            if (empty($byTrackRoleNames[$tid]['Remixer'])) {
                $val = $t['remixer'] ?? ($t['t_remixer_join'] ?? '');
                $addFrom($val, 'Remixer');
            }

            $maybe('produceBy','Producer');
            $maybe('arranger','Arranger');

            if (empty($byTrackRoleNames[$tid]['Publisher'])) {
                $val=$t['editor']??''; if($val==='') $val=$t['publisher']??'';
                $addFrom($val,'Publisher');
            }
        }

        $byReleaseContributor=[];
        foreach ($tracks as $t) {
            $mid=$t['master_id']; $tid=(int)$t['id']; $idx=$t['volume'].'.'.$t['order_id'];
            if (empty($byTrackRoleNames[$tid])) continue;
            foreach ($byTrackRoleNames[$tid] as $roleFixed=>$namesSet) {
                foreach ($namesSet as $name=>$_) {
                    if ($name==='') continue;
                    $byReleaseContributor[$mid][$name][$roleFixed][$idx]=true;
                }
            }
        }

        $isSpotifyId=function($v){ return preg_match('/^[A-Za-z0-9]{22}$/',(string)$v)===1; };
        $extractSpotifyId=function($spotify_id,$ext_url) use ($isSpotifyId){
            $sid=trim((string)$spotify_id); if($isSpotifyId($sid)) return $sid;
            $url=trim((string)$ext_url);
            if($url!==''){
                if(preg_match('~open\.spotify\.com/artist/([A-Za-z0-9]{22})~',$url,$m)) return $m[1];
                if(preg_match('~spotify:artist:([A-Za-z0-9]{22})~',$url,$m)) return $m[1];
            }
            return '';
        };

        $allNamesL=[];
        foreach ($byReleaseContributor as $mid=>$people) {
            foreach ($people as $name=>$_roles) {
                $n=$normalize($name); if($n!=='') $allNamesL[strtolower($n)]=true;
            }
        }

        $spotifyByNameL=[];
        if (!empty($allNamesL) && $tableExists('tbl_artist')) {
            $keysLower=array_keys($allNamesL);
            foreach (array_chunk($keysLower,200) as $chunkLower) {
                $ph=implode(',',array_fill(0,count($chunkLower),'?'));
                $stmt=$adapter->createStatement(
                    "SELECT name, spotify_id, IFNULL(ext_url,'') AS ext_url
                     FROM tbl_artist
                     WHERE LOWER(name) IN ($ph)"
                );
                $res=$stmt->execute($chunkLower);
                foreach($res as $row){
                    $key=strtolower($normalize($row['name']??'')); if($key==='') continue;
                    $sid=$extractSpotifyId($row['spotify_id']??'',$row['ext_url']??''); if($sid!=='') $spotifyByNameL[$key]=$sid;
                }
            }
        }

        $ctr = $spreadsheet->getSheetByName('Contributors');
        if (!$ctr) throw new \Exception('Contributors sheet not found in template.');

        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ctr->getHighestColumn());
        $hdrCols = [];
        for ($c=1; $c<=$highestColIndex; $c++){
            $raw = (string)$ctr->getCellByColumnAndRow($c,1)->getValue();
            $norm = strtolower(preg_replace('/\s+/u',' ',trim(str_replace(["\r","\n"],' ',$raw))));
            if ($norm!=='') $hdrCols[$norm] = $c;
        }
        $findColByContains = function($needle) use ($hdrCols){
            $needle=strtolower($needle);
            foreach ($hdrCols as $norm=>$idx) {
                if (strpos($norm,$needle)!==false)
                    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx);
            } return null;
        };

        $colReleaseTitle = $findColByContains('release title') ?: 'A';
        $colContributor  = $findColByContains('contributor name') ?: 'B';
        $colSpotifyId    = $findColByContains('spotify');

        $ctrHighest = $ctr->getHighestRow();
        if ($ctrHighest > 1) { $ctr->removeRow(2, $ctrHighest - 1); }

        $rowC=2;
        foreach ($byReleaseContributor as $mid=>$people) {
            $releaseTitle = $rel[$mid]['title_norm'];
            foreach ($people as $name=>$roles) {
                $ctr->setCellValue($colReleaseTitle.$rowC,$releaseTitle);
                $ctr->setCellValue($colContributor.$rowC,$name);
                if ($colSpotifyId) {
                    $keyL=strtolower($normalize($name));
                    $sid=$spotifyByNameL[$keyL]??'';
                    $ctr->setCellValue($colSpotifyId.$rowC, $isSpotifyId($sid)?$sid:'');
                }
                $roleColFallback=['Main artist'=>'E','Featuring'=>'F','Composer'=>'G','Author'=>'H','Remixer'=>'I','Producer'=>'J','Arranger'=>'K','Publisher'=>'L'];
                foreach ($roles as $role=>$setIdx) {
                    $col=$findColByContains(strtolower($role).' on tracks') ?: ($roleColFallback[$role]??null);
                    if(!$col) continue;
                    $indices=array_keys($setIdx);
                    $indices=array_values(array_filter($indices,function($v){return preg_match('/^\d+\.\d+$/',$v);}));
                    $ctr->setCellValue($col.$rowC,implode(';',$indices));
                }
                $rowC++;
            }
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($outputPath);

        echo json_encode(['status'=>'OK','fileUrl'=>$config['URL'].'public/exports/'.$outputFile]); exit;

    } catch (\Exception $e) {
        echo json_encode(['status'=>'ERROR','message'=>$e->getMessage()]); exit;
    }
}


public function assignTeamAction()
{
	$request = $this->getRequest();
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $releaseTable = new TableGateway('tbl_release', $adapter);
	$viewTable = new TableGateway('view_release', $adapter);

    if ($request->isPost()) {
        $ids = $request->getPost('ids');
		$assign_team_id = $request->getPost('assign_team_id');
		$a_type = $request->getPost('a_type');
		
        if (!empty($ids) && is_array($ids)) {
            $idList = implode(',', array_map('intval', $ids));
			
			if($a_type != 'Confirm')
			{
				$rowset = $viewTable->select(array("id in (".$idList.") and assigned_team > 0 and assigned_team!='".$assign_team_id."'"));
				$rowset =  $rowset->toArray();
				
				if(count($rowset) > 0)
				{
					$list = '<br><table class="table dataTable no-footer table-hover table-bordered"><thead><tr><th>Release</th><th>Assigned Team</th></tr></thead>';
					foreach($rowset as $row)
					{
						$list .= '<tr><td>'.$row['title'].'</td><td>'.$row['assigned_team_name'].'</td></tr>';
					}
					$list .= '</table><br><div class="confMsg confMsg text-danger"><strong>The above releases are already assigned to another team. Do you still want to proceed?</strong>';
					echo json_encode(['status' => 'Exist','List' => $list]);
				}
				else
				{
					 $aData = array();
					 $aData['assigned_team'] = $assign_team_id;
					 $releaseTable->update($aData,array("id in (".$idList.") "));
					 echo json_encode(['status' => 'OK']);
				}
			}
			else
			{
					$aData = array();
					 $aData['assigned_team'] = $assign_team_id;
					 $releaseTable->update($aData,array("id in (".$idList.") "));
					 echo json_encode(['status' => 'OK']);
			}
            exit;
        }
    }
}

 public function uploadaudioAction()
{
    // make sure no extra output corrupts JSON
    if (function_exists('ob_get_level')) { while (ob_get_level()) { @ob_end_clean(); } }
    header('Content-Type: application/json; charset=utf-8');

    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new \Exception('Invalid method');
        }

        $sl      = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        // ---- inputs ----
        $trackId = isset($_POST['track_id']) ? (int)$_POST['track_id'] : 0;
        if ($trackId <= 0) throw new \Exception('Missing track_id');

        // accept either `audio_upload` or `audio_file`
        $fileField = null;
        if (!empty($_FILES['audio_upload'])) $fileField = 'audio_upload';
        if (!$fileField && !empty($_FILES['audio_file'])) $fileField = 'audio_file';
        if (!$fileField || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            $code = isset($_FILES[$fileField]['error']) ? $_FILES[$fileField]['error'] : -1;
            throw new \Exception('File upload error (code: ' . $code . ')');
        }

        $file     = $_FILES[$fileField];
        $origName = $file['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // ---- size guard (1 GB) ----
        if ($file['size'] > 1024*1024*1024) {
            echo json_encode(['status'=>'NO_OK','msg'=>'File too large. Max 1 GB.']); exit;
        }

        // ---- allow_replace check ----
        $trackTbl = new \Zend\Db\TableGateway\TableGateway('tbl_track', $adapter);
        $trackRow = $trackTbl->select(['id' => $trackId, 'allow_replace' => 1])->current();
        if (!$trackRow) {
            echo json_encode(['status'=>'NO_OK','msg'=>'Replace not allowed for this track.']); exit;
        }

        // ---- only WAV allowed to upload ----
        if ($ext !== 'wav') {
            echo json_encode([
                'status'=>'WRONG_FORMAT',
                'msg'   =>'Only WAV allowed.'
            ]); exit;
        }

        // ---- probe uploaded temp file ----
        $probeBin = '/usr/bin/ffprobe';
        $tmpPath  = $file['tmp_name'];
        $cmdProbe = sprintf(
            '%s -v error -show_format -show_streams -select_streams a:0 -print_format json -i %s',
            escapeshellcmd($probeBin), escapeshellarg($tmpPath)
        );
        $outProbe = []; $retProbe = 0; exec($cmdProbe, $outProbe, $retProbe);
        $info = @json_decode(implode("\n", $outProbe), true);
        if (!$info || $retProbe !== 0) throw new \Exception('Unable to analyze audio file.');

        $durationSec = isset($info['format']['duration']) ? (float)$info['format']['duration'] : 0.0;
        $formatName  = isset($info['format']['format_name']) ? $info['format']['format_name'] : 'Unknown';
        $stream0     = isset($info['streams'][0]) ? $info['streams'][0] : [];
        $bitDepth    = isset($stream0['bits_per_sample']) ? (int)$stream0['bits_per_sample'] : 0;
        $sampleRate  = isset($stream0['sample_rate']) ? (int)$stream0['sample_rate'] : 0;

        // ---- Believe spec: 16/44.1 OR 24/(44.1–192k) ----
        $validStandard = ($bitDepth === 16 && $sampleRate === 44100);
        $validHD       = ($bitDepth === 24 && in_array($sampleRate, [44100,48000,88200,96000,192000]));
        if (!$validStandard && !$validHD) {
            echo json_encode([
                'status'=>'WRONG_FORMAT',
                'msg'   =>'Audio must be WAV: 16-bit/44.1kHz OR 24-bit (44.1k–192k).'
            ]); exit;
        }

        // ---- destination dir ----
        $baseDir = '/home/primebackstage/htdocs/www.primebackstage.in/public/uploads/audio';
if (!is_dir($baseDir)) { @mkdir($baseDir, 0755, true); }
$baseDir = rtrim($baseDir, '/');

        $stamp   = date('YmdHis') . mt_rand(100,999);
        $wavName = 'track_'.$trackId.'_'.$stamp.'.wav';
        $wavPath = $baseDir . '/' . $wavName;

        if (!move_uploaded_file($tmpPath, $wavPath)) {
            echo json_encode(['status'=>'ERR','msg'=>'Unable to save WAV file.']); exit;
        }

        // ---- convert to MP3 (320 kbps, 44.1k, stereo) ----
        $ffmpegBin = '/usr/bin/ffmpeg';
        $mp3Name   = 'track_'.$trackId.'_'.$stamp.'.mp3';
        $mp3Path   = $baseDir . '/' . $mp3Name;

        $cmdConv = sprintf(
            '%s -y -i %s -vn -ar 44100 -ac 2 -b:a 320k %s 2>&1',
            escapeshellcmd($ffmpegBin), escapeshellarg($wavPath), escapeshellarg($mp3Path)
        );
        $convOut = []; $retConv = 0; exec($cmdConv, $convOut, $retConv);

        if ($retConv !== 0 || !file_exists($mp3Path) || filesize($mp3Path) < 2048) {
            @unlink($wavPath);
            throw new \Exception('Audio conversion failed.');
        }

        // ---- probe MP3 for final info ----
        $outMp3 = []; $retMp3 = 0;
        exec($cmdProbe = sprintf(
            '%s -v error -show_format -show_streams -select_streams a:0 -print_format json -i %s',
            escapeshellcmd($probeBin), escapeshellarg($mp3Path)
        ), $outMp3, $retMp3);
        $infoMp3 = @json_decode(implode("\n", $outMp3), true);
        $durHHMMSS = gmdate("H:i:s", (int)round($durationSec));
        $bd  = $infoMp3['streams'][0]['bits_per_sample'] ?? 'Unknown';
        $sr  = $infoMp3['streams'][0]['sample_rate'] ?? 'Unknown';
        $ch  = $infoMp3['streams'][0]['channels'] ?? 'Unknown';
        $fmt = 'MP3 - '.$bd.'bits - '.$sr.'Hz - '.$ch.'ch';

        // ---- DB update ----
        // ---- DB update ----
$update = [
    'audio_file'        => $mp3Name,
    'audio_file_name'   => $origName,
    'audio_format_info' => $durHHMMSS,
    'allow_replace'     => 0
];

try {
    $trackTbl->update($update, ['id' => $trackId]);
} catch (\Exception $e) {
    @unlink($mp3Path);
    @unlink($wavPath);
    throw new \Exception('DB update failed: '.$e->getMessage());
}

        // keep WAV if you want backup; else delete to save space:
        @unlink($wavPath);

        echo json_encode([
            'status'      => 'OK',
            'file_name'   => $mp3Name,
            'format_info' => '<p>'.$durHHMMSS.'<br>'.htmlspecialchars($fmt, ENT_QUOTES, 'UTF-8').'</p>',
            'duration'    => $durHHMMSS
        ]); exit;

    } catch (\Exception $e) {
        echo json_encode(['status'=>'ERR','msg'=>$e->getMessage()]); exit;
    }
}

// optional helper
public function format_duration($duration) {
    return gmdate("H:i:s", (int)$duration);
}

public function allowreplaceAction()
{
    $request = $this->getRequest();
    if ($request->isPost()) {
        $post = $request->getPost()->toArray();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $trackTbl = new \Zend\Db\TableGateway\TableGateway('tbl_track', $adapter);

        try {
            // ✅ Fix: use 'id' instead of 'release_id'
            $trackTbl->update(['allow_replace' => 1], ['id' => $post['KEY_ID']]);

            echo json_encode([
                'ERR_NO' => 0,
                'DATA' => ['DBStatus' => 'OK']
            ]);
            exit;
        } catch (\Exception $e) {
            echo json_encode([
                'ERR_NO' => 1,
                'DATA' => ['DBStatus' => 'DB Error: ' . $e->getMessage()]
            ]);
            exit;
        }
    }

    echo json_encode([
        'ERR_NO' => 1,
        'DATA' => ['DBStatus' => 'Invalid Request']
    ]);
    exit;
}
  public function movetoprocessingAction()
{
    $request = $this->getRequest();
    if (!$request->isPost()) {
        echo json_encode(['status' => 'FAIL', 'error' => 'Bad request']);
        exit;
    }

    $ids = $request->getPost('ids', []);
    if (!is_array($ids) || count($ids) === 0) {
        echo json_encode(['status' => 'FAIL', 'error' => 'No IDs']);
        exit;
    }

    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $releaseTable = new \Zend\Db\TableGateway\TableGateway('tbl_release', $adapter);
    $notesTable   = new \Zend\Db\TableGateway\TableGateway('tbl_internal_notes', $adapter);

    $nowDb    = date('Y-m-d H:i:s');
    $autoText = "✅ Good News – Will Be Live Soon";

    $updatedCount = 0;

    foreach ($ids as $rawId) {
        $releaseId = (int)$rawId;
        if ($releaseId <= 0) {
            continue;
        }

        // 1. read existing release row
        $releaseRow = $releaseTable->select(['id' => $releaseId])->current();
        if (!$releaseRow) {
            continue;
        }

        // 2. always update status + in_process
        $releaseTable->update(
            [
                'status'     => 'inreview',
                'in_process' => 1,
            ],
            ['id' => $releaseId]
        );

        // ---- DUPLICATE NOTE PROTECTION ----
        // fetch all existing note IDs for this release
        $existingCsv = isset($releaseRow['internal_notes'])
            ? trim((string)$releaseRow['internal_notes'])
            : '';

        // flag: is our autoText already present?
        $alreadyHasAutoNote = false;

        if ($existingCsv !== '') {
            // break csv -> ids
            $noteIds = array_filter(array_map('trim', explode(',', $existingCsv)));
            // ensure int only
            $noteIds = array_map('intval', $noteIds);
            $noteIds = array_filter($noteIds, function ($v) { return $v > 0; });

            if (!empty($noteIds)) {
                // get their rows
                $placeholders = implode(',', $noteIds);
                $sqlNotes = "SELECT id, notes FROM tbl_internal_notes WHERE id IN ($placeholders)";
                $rowsNotes = $adapter->query($sqlNotes, $adapter::QUERY_MODE_EXECUTE);

                foreach ($rowsNotes as $nRow) {
                    if (isset($nRow['notes']) && trim($nRow['notes']) === $autoText) {
                        $alreadyHasAutoNote = true;
                        break;
                    }
                }
            }
        }

        // if note already exists, skip making a new note
        if ($alreadyHasAutoNote) {
            $updatedCount++;
            continue;
        }

        // 3. find existing internal_note row (reuse instead of insert)
$existingNoteRow = $notesTable->select(['notes' => $autoText])->current();
if ($existingNoteRow) {
    $newNoteId = (int)$existingNoteRow['id'];   // reuse existing ID
} else {
    // create once if truly missing
    $noteInsertData = [
        'notes'      => $autoText,
        'color_code' => '#28a745',
        'created_on' => $nowDb,
    ];
    $notesTable->insert($noteInsertData);
    $newNoteId = $notesTable->getLastInsertValue();
}

        if (!$newNoteId) {
            // fallback max(id)
            $rowLast = $adapter->query(
                "SELECT MAX(id) AS max_id FROM tbl_internal_notes",
                $adapter::QUERY_MODE_EXECUTE
            )->current();
            $newNoteId = (int)$rowLast['max_id'];
        }

        // 4. append that id into release.internal_notes CSV
        $finalCsv = ($existingCsv !== '')
            ? ($existingCsv . ',' . $newNoteId)
            : (string)$newNoteId;

        $releaseTable->update(
            [
                'internal_notes' => $finalCsv,
                'note_date_time' => $nowDb,
            ],
            ['id' => $releaseId]
        );

        $updatedCount++;
    }

    echo json_encode([
        'status'  => 'OK',
        'updated' => $updatedCount,
        'time'    => $nowDb,
    ]);
    exit;
}


public function deliveryreportAction()
{
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $customObj = $this->CustomPlugin();

    // Get release details from view_release
    $projectTable = new TableGateway('view_release', $adapter);
    $rowset = $projectTable->select(["id='" . $_GET['id'] . "'"]);
    $rowset = $rowset->toArray();

    if (empty($rowset)) {
        return new ViewModel(['INFO' => [], 'has_ugc' => false]);
    }

    $release = $rowset[0];
    $title = trim($release['title']);
    $artist = trim($release['releaseArtist']);
    $upc = trim($release['upc']);
    $label_id = $release['labels'];
    $apple_link = '';

    // Get label name
    $labelTable = new TableGateway('tbl_label', $adapter);
    $labelRow = $labelTable->select(['id' => $label_id])->current();
    $label_name = $labelRow ? $labelRow['name'] : '';
    $release['label_name'] = $label_name;

    // Get Apple link from DB
    $releaseTable = new TableGateway('tbl_release', $adapter);
    $checkRow = $releaseTable->select(['id' => $release['id']])->current();
    if (!empty($checkRow['apple_link'])) {
        $apple_link = $checkRow['apple_link'];
    }

    // Fetch from API if missing
    if (empty($apple_link)) {
        $searchTerm = urlencode($title . ' ' . $artist);
        $apiUrlIN = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";
        $response = @file_get_contents($apiUrlIN);
        $result = json_decode($response, true);

        if (!$result || $result['resultCount'] == 0) {
            $apiUrlUS = "https://itunes.apple.com/search?term=$searchTerm&country=US&media=music&entity=album";
            $response = @file_get_contents($apiUrlUS);
            $result = json_decode($response, true);
        }

        if ($result && isset($result['results'][0]['collectionViewUrl'])) {
            $apple_link = $result['results'][0]['collectionViewUrl'];
            $releaseTable->update(['apple_link' => $apple_link], ['id' => $release['id']]);
        }
    }

    // Fallback if still empty
    if (empty($apple_link)) {
        $apple_link = "https://music.apple.com/in/search?term=" . urlencode($title);
    }

    // Assign links
    $release['apple_link'] = $apple_link;
    $release['youtube_link'] = 'https://www.youtube.com/results?search_query=' . urlencode($title . ' ' . $artist);
    $release['spotify_link'] = 'https://open.spotify.com/search/' . rawurlencode($title . ' ' . $artist);

// ✅ YouTube UGC (Content ID) check based on Title + Artist + UPC + Label ID(s)
$has_ugc = false;

// 🔍 Check if YouTube UGC exists for this release in analytics
$sql = "
    SELECT COUNT(*) AS total
    FROM tbl_analytics a
    JOIN tbl_release r ON a.release_id = r.id
    WHERE a.store = 'YouTube UGC'
      AND r.upc = ?
      AND r.title = ?
      AND r.releaseArtist = ?
      AND FIND_IN_SET(?, r.labels)
    LIMIT 1
";

$params = [
    $release['upc'],
    $release['title'],
    $release['releaseArtist'],
    $release['labels']
];

$result = $adapter->query($sql, $params)->current();

if (!empty($result) && $result['total'] > 0) {
    $has_ugc = true;
}

return new ViewModel([
    'INFO' => $release,
    'has_ugc' => $has_ugc
]);

}

	public function importapprovalAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$projectTable = new TableGateway('tbl_release', $adapter);
		
		$viewTable = new TableGateway('view_release', $adapter);
		
        if ($request->isPost()) {
            $file = $_FILES['approvefile'];
            $filename = $_FILES['approvefile']['name'];
			
			$ext = pathinfo($filename, PATHINFO_EXTENSION); 
			
			if($ext != 'csv' && $ext != 'CSV')
			{
				$result['status'] = 'NOT';
				$result = json_encode($result);
				echo $result;
				exit;
			}
            
			$fileName1 = date('YmdHis').'.'.$ext;
			$myImagePath =  "public/uploads/import/$fileName1";
			
			$IMPORT_LIST='';
			$IGNORE_LIST='';
			
			$ignore_cnt=1;
			$import_cnt=1;
            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {

				 
					if (($handle = fopen($myImagePath, "r")) !== FALSE) 
					{ 
							$count=0;
							 
							while (($data = fgetcsv($handle, 10000, ';')) !== FALSE) 
							{
								
									$num = count($data);
									
									if($count==0)
										$count++;
									else
									{
										$title  = addslashes(utf8_encode(trim($data[0])));
										$version  = utf8_encode(trim($data[1]));
										$artist  = utf8_encode(trim($data[2]));
										$label  = addslashes(utf8_encode(trim($data[3])));
										$release_date  = utf8_encode(trim($data[4]));
										$no_of_track  = utf8_encode(trim($data[5]));
										$upc  = utf8_encode(trim($data[6]));
										$pcn  = utf8_encode(trim($data[7]));
										
										$upc = explode(':',$upc);
										$upc = trim($upc[1]);
										
										$release_date = substr($release_date,0,10);
										$label_id = $this->getLabelID($label);
										
										if($title != '' && $upc != '' && strtoupper($upc) != 'EMPTY' && $label_id > 0)
										{
											$artist_condition = "";
											$artist = explode(',', $artist);
											$cond_array = array();

											// Loop through the artist array and build the condition
											foreach ($artist as $single_artist) {
												$single_artist = trim($single_artist); // Trim any extra spaces
												if (!empty($single_artist)) {
													$cond_array[] = "FIND_IN_SET(UPPER('" . strtoupper($single_artist) . "'), UPPER(releaseArtist))";
												}
											}

											// Only if we have valid conditions, create the final query part
											if (count($cond_array) > 0) {
												$artist_condition = " AND ( " . implode(" AND ", $cond_array) . " )";
											}
											
											$cond = '';
											
											if($pcn != '')
											{
												$cond = " AND pcn ='".$pcn."' ";
											}
											else
											{
												$cond = " and labels='".$label_id."' and UPPER(title)='".strtoupper($title)."'  and UPPER(version)='".strtoupper($version)."' $artist_condition ";
											}
											
											
											
											$aData=array();
											$aData['upc'] = $upc;
											$aData['status'] = 'delivered';
											$aData['in_process'] = 0;
											$affectedRows = $projectTable->update($aData,array("in_process='1' $cond "));
											
											if ($affectedRows > 0) {
												
											} else {
												/*$IGNORE_LIST .='<tr><td>'.$ignore_cnt.'</td><td>'.$title.'</td><td>'.$label.'</td><td>'.$artist.'</td><td>'.$upc.'</td><td>'.$pcn.'</td>';
												
												$IGNORE_LIST .='</tr>';
												$ignore_cnt++;*/
											}
												
										}
									}
									
									/*$count++;*/
							}
							$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'public/cron_file/convertaudioall.php');
							$customObj->start();
							
					}
					
					$rowset = $viewTable->select(array("in_process='1'"));
					$rowset = $rowset->toArray();
					if(count($rowset) > 0)
					{
						foreach($rowset as $row)
						{
							$IGNORE_LIST .='<tr><td>'.$ignore_cnt.'</td><td>'.$row['title'].'</td><td>'.$row['label_name'].'</td><td>'.$row['releaseArtist'].'</td><td>'.$row['upc'].'</td><td>'.$row['pcn'].'</td></tr>';
							
							$ignore_cnt++;
						}
					}
					else   //if($ignore_cnt == 1) 
					{
						$IGNORE_LIST="<tr><td colspan='6'>No record found</td></tr>";
					}
					
					$cond='';
					if($_SESSION['user_id'] != '0' && $_SESSION["STAFFUSER"] == 0 )
					{
						$labels = $customObj->getUserLabels($_SESSION['user_id']);
						
						$subUsers = $this->getSubUsers();
						
						$cond.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) OR created_by in (".$subUsers.") )";
					}
					if($_SESSION["STAFFUSER"] == 1 )
					{
						$staff_cond = $customObj->getStaffReleaseCond();
						$cond.= $staff_cond;
					}
		
		
					$sql="select count(*) as cnt from tbl_release where status='inreview' and in_process=1 $cond ";
					$optionalParameters=array();        
					$statement = $adapter->createStatement($sql, $optionalParameters);        
					$result5 = $statement->execute();        
					$resultSet = new ResultSet;        
					$resultSet->initialize($result5);        
					$rowset=$resultSet->toArray(); 
					$in_process = $rowset[0]['cnt'];
		
					$result['in_process'] = $in_process;
					$result['file_name'] = $fileName1;
					$result['IGNORE_LIST'] = $IGNORE_LIST;
					$result['status'] = 'OK';
					$result['message1'] = 'Done';	
				}
	
        }
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function viewAction()
    {
      
      $isAdmin = ($_SESSION['user_id'] === '0' || $_SESSION['STAFFUSER'] == 1);
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$customObj = $this->CustomPlugin();
		
		$projectTable = new TableGateway('view_release', $adapter);
		$rejectreasonTable = new TableGateway('tbl_reject_reason', $adapter);
		$notesTable =  new TableGateway('tbl_internal_notes', $adapter);
		$trackTable = new TableGateway('tbl_track', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['id']."' "));
		$rowset = $rowset->toArray();
		$status = $rowset[0]['status'];
		$in_process = $rowset[0]['in_process'];
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
		
		$rowset2 = $trackTable->select(array("master_id='".$_GET['id']."' and order_id > 0"));
		$rowset2 = $rowset2->toArray();
		$TOTAL_TRACK = count($rowset2);
		
		
		$rowset2 = $trackTable->select(array("master_id='".$_GET['id']."' and order_id > 0 group by volume order by volume asc"));
		$rowset2 = $rowset2->toArray();
		$TRACK_LIST='';
		
		
		
		if(count($rowset2) > 0)
		{
			foreach($rowset2 as $row2)
			{
				
			  
				$TRACK_LIST.='<div class="panel panel-default tracklist-volume-panel">
								<div class="panel-heading">Volume '.$row2['volume'].'</div>
								<table class="table table-hover" id="listCreator-662746bde753c">
									<thead>
										<tr>
											<th style="" class="column-checkbox column-checkbox">&nbsp;</th>
											<th style="" class="column-igt column-igt"> </th>
											<th style="" class="column-player column-player"></th>
											<th style="" class="column-track-number column-track-number">Track#</th>
											<th style="" class="column-track-title column-track-title">Track title</th>
											<th style="" class="column-track-version column-track-version">Version</th>
											<th style="" class="column-artist column-artist">Artist</th>
											<th style="" class="column-author column-author">Authors</th>
											<th style="" class="column-composer column-composer">Composers</th>
                                            <th style="" class="column-track-price">Track Price</th>

											<th style="" class="column-duration column-duration">Duration</th>
                                            <th class="column-isrc column-isrc">ISRC</th>
											
											<th class="column-preview">Preview</th>'
             . ($isAdmin ? '<th class="column-transfer">Transfer</th>' : '')
             . '</tr></thead><tbody>';
								
				$rowset3 = $trackTable->select(array("master_id='".$_GET['id']."' and volume='".$row2['volume']."' and order_id != 0 order by order_id asc"));
				$rowset3 = $rowset3->toArray();
				
				$explicitContent = $rowset3[0]['explicitContent'];
				if($explicitContent == '1')
					$explicitContent='Yes';
				if($explicitContent == '0')
					$explicitContent='No';
				if($explicitContent == '2')
					$explicitContent='Cleaned';
				
				$rowset[0]['trackType'] = $rowset3[0]['trackType'];
				$rowset[0]['idInstrumental'] = ($rowset3[0]['idInstrumental'] == '0')?'No':'Yes';
				$rowset[0]['produceBy'] = $rowset3[0]['produceBy'];
				$rowset[0]['editor'] = $rowset3[0]['editor'];
				$rowset[0]['remixer'] = $rowset3[0]['remixer'];
				$rowset[0]['explicitContent'] = $explicitContent;
				$rowset[0]['idLyricsSelect'] =($rowset3[0]['idLyricsSelect'] != '0')?$rowset3[0]['idLyricsSelect']:'';
				
				
				foreach($rowset3 as $row3)
				{
					if($status == 'delivered' || $status == 'taken out' || $_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1' ){} 
					else
					  $row3['isrc'] = '';
			  
					$duration ='';
					$audio = '';
					if($row3['audio_file'] == '')
					{
						$audio = '<td style="" align="center" class="column-player column-player"><span class="fa-stack" data-toggle="tooltip" data-placement="top" title="This track has not been digitized">
									<i class="fa fa-play-circle-o fa-stack-2x"></i>
									<i class="fa fa-ban fa-stack-2x text-danger"></i>
								</span></td>';
								
						$music = '<i class="fa fa-music fa-2x js-tracklist-music-icon"></i>';
					}
					else
					{
						
						$audio ='<td style="" class="column-player column-player"><div class="player-container player-cover"><div id="audioContainer_coverPlayer_'.$row3['id'].'"><audio src="../public/uploads/audio/'.$row3['audio_file'].'"></audio></div><span class="player-playback"><span class="player-playback-cover player-without-image" style="display:inline-block; height:26px; width: 26px;"><span class="player-playback-playpause btn-play button-playpause" style="display:inline-block; width:26px; height:26px; font-size:26px;"></span></span></span></div></td>';
						
						$duration = explode('<br>',$row3['audio_format_info']);
						$duration = str_replace('<p>','',$duration[0]);
						
						$music = '<a href="../public/uploads/audio/'.$row3['audio_file'].'" download="'.$row3['audio_file_name'].'"><i class="fa fa-audio-description fa-2x" data-toggle="tooltip" data-placement="top" title="" data-original-title="Download Audio File" ></i></a>';
					}
					if($row3['isrc'] == '')
						$row3['isrc'] = 'empty';
					
					
					$TRACK_LIST.='<tr class="js-track-row"
                  data-track-id="'.$row3['id'].'"
                  data-volume="'.$row2['volume'].'"
                  data-order="'.$row3['order_id'].'">

									<td style="" class="column-checkbox column-checkbox">
										<input class="hidden js-tracklist-checkbox" type="checkbox" id="checkbox-song-'.$row3['id'].'" value="'.$row3['id'].'">
										'.$music.'
									</td>
									<td style="" class="column-igt column-igt"></td>
									'.$audio.'
									<td style="" class="column-track-number column-track-number">'.$row3['order_id'].'</td>
									<td style="" class="column-track-title column-track-title track-title">
  '.$row3['songName'].'
  <input type="hidden" name="idTrack" value="'.$row3['id'].'">
</td>

									<td style="" class="column-track-version column-track-version">'.$row3['version'].'</td>
									<td style="" class="column-artist column-artist">'.$row3['trackArtist'].'</td>
									<td style="" class="column-author column-author">'.$row3['author'].'</td>
									<td style="" class="column-composer column-composer">'.$row3['composer'].'</td>
                                   <td style="" class="column-track-price">'.($row3['trackPrice'] !== '' ? htmlspecialchars((string)$row3['trackPrice'], ENT_QUOTES, 'UTF-8') : '-').'</td>

									<td style="" class="column-duration column-duration">'.$duration.'</td>';
									
									/*if($_SESSION['user_id'] == '0')
									{
										$TRACK_LIST.='<td class="isrc-container"><div style="" class="column-isrc updateIsrc" track_id="'.$row3['id'].'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="Change ISRC" >'.$row3['isrc'].'</div><span class="copy-isrc-icon" data-toggle="tooltip" data-placement="top" title="" data-original-title="Copy ISRC"  onclick="copyISRC(\'' . $row3['isrc'] . '\')">📋</span></td>';
									}
									else
									{*/
										$TRACK_LIST.='<td class="isrc-container" style="display:inline-flex;align-items: center;gap: 5px;"><div style="white-space:nowrap" class="column-isrc column-isrc ">'.$row3['isrc'].'</div><span class="copy-isrc-icon1"  onclick="copyISRC(\'' . $row3['isrc'] . '\')"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>   
</svg>
</span></td>';
							$TRACK_LIST .= '<td class="column-preview column-preview">'.$row3['preview_start'].'</td>';

if ($isAdmin) {
   $TRACK_LIST .=
  '<td class="column-transfer" style="white-space:nowrap;">'.
    '<button type="button" class="btn btn-xs btn-primary js-transfer-track" '.
            'data-track-id="'.$row3['id'].'" '.
            'title="Send WAV to Believe FTP" aria-label="Send WAV to Believe">'.
      '<i class="fa fa-exchange" aria-hidden="true"></i>'.
    '</button>'.
    '<span class="transfer-msg" style="margin-left:6px;font-size:17px;"></span>'.
  '</td>';

}

$TRACK_LIST .= '</tr>';

				}
				$TRACK_LIST.='</tbody></table></div>';				
			}
		
		}
		
		else
		{
			$TRACK_LIST='<div class="tracklist-no-track" style="text-align:center;">This release doesn\'t have any track.</div>';
		}
		
		$rowset[0]['created_on'] = date('d/m/Y',strtotime($rowset[0]['created_on'])); 
		
		if($rowset[0]['physicalReleaseDate'] == '0000-00-00')
			$rowset[0]['physicalReleaseDate'] = '';
		else
			$rowset[0]['physicalReleaseDate']= date('d/m/Y',strtotime($rowset[0]['physicalReleaseDate'])); 
		
		if($rowset[0]['digitalReleaseDate'] == '0000-00-00')
			$rowset[0]['digitalReleaseDate'] = '';
		else
			$rowset[0]['digitalReleaseDate']= date('d/m/Y',strtotime($rowset[0]['digitalReleaseDate'])); 
		
		if($rowset[0]['cover_img'] == '')
			$rowset[0]['cover_img']='../public/img/no-image.png';
		else
			$rowset[0]['cover_img']='../public/uploads/'.$rowset[0]['cover_img'];
		
		if($rowset[0]['productionYear'] == '0')
			$rowset[0]['productionYear']='';
		
		
		if($rowset[0]['upc'] == '')
			$rowset[0]['upc'] = 'empty';
		
		
		if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1')
		{
			$rowset[0]['upc'] = '<div style="" class="column-upc  updateUpc" track_id="'.$rowset[0]['id'].'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="Change UPC" >'.$rowset[0]['upc'].'</div>';
		}
		else
		{
			$rowset[0]['upc'] = '<div style="" class="column-upc">'.$rowset[0]['upc'].'</div>';
		}
		
		if($rowset[0]['have_content_id'] == '1')
			$rowset[0]['have_content_id'] = 'Yes';
		else
			$rowset[0]['have_content_id'] = 'No';
		
		if($rowset[0]['note_date_time'] != '0000-00-00 00:00:00')
			$rowset[0]['note_date_time'] = date('d M Y h:i A',strtotime($rowset[0]['note_date_time'])); 
		else
			$rowset[0]['note_date_time'] = '';
		
		
		$rowset27 = $rejectreasonTable->select();
		$rowset27 = $rowset27->toArray();
		$reason = array();
		foreach($rowset27 as $row)
		{
			$reason[$row['id']] = $row['description'];
		}
		
		$rowset28 = $notesTable->select();
		$rowset28 = $rowset28->toArray();
		
		if($rowset[0]['mainGenre'] == '0')
			$rowset[0]['mainGenre'] = '';
		if($rowset[0]['subgenre'] == '0')
			$rowset[0]['subgenre'] = '';
		
		
		$sql="select sales_month from tbl_analytics where release_id='".$rowset[0]['id']."'  order by sales_month desc limit 1";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset3=$resultSet->toArray();
		
		$latest_month = '';
		if(count($rowset3)>0)
		 $latest_month =  date('Y-m-01',strtotime($rowset3[0]['sales_month']));
		
		$rowset[0]['trend_link'] = '../analytics/view?id='.$rowset[0]['id'].'&from_month='.$latest_month.'&to_month='.$latest_month;
		
		
		$viewModel = new ViewModel(array(
			'STATUS' => $status,
			'in_process' => $in_process,
			'INFO' => $rowset[0],
			'TOTAL_TRACK' => $TOTAL_TRACK,
			'TRACK_LIST' => $TRACK_LIST,
			'REASON' => $reason,
			'NOTES' => $rowset28
		));
		return $viewModel;
    }
    public function listAction()
{

    echo $this->fnGrid();
    exit;
}
	public function uploadimgAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		
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
                $result['status'] = 'OK';
                $result['message1'] = 'Done';				
				$result['file_name'] = $filename; 
            } 
           
        $projectTable = new TableGateway('tbl_release', $adapter);
		$aData['cover_img'] = $filename;
		$projectTable->update($aData,array("id='".$_POST['release_id']."' "));
		
        $result = json_encode($result);
        echo $result;
        exit;
	}
    public function getrecAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_department', $adapter);
            $rowset = $projectTable->select(array('id' => $iID));
            $rowset = $rowset->toArray();
            foreach ($rowset as $record)
                $recs[] = $record;
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
		$config = $this->getServiceLocator()->get('config');
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$trackTable = new TableGateway('tbl_track', $adapter);
			$analyticsTable = new TableGateway('tbl_analytics', $adapter);
			
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				if( $iMasterID != '')
				{
					
					$rowset = $projectTable->select(array("id in (".$iMasterID.") "));
					$rowset = $rowset->toArray();
					if($rowset[0]['cover_img'] !='')
					{
						unlink($config['PATH'].'public/uploads/'.$rowset[0]['cover_img']);
						unlink($config['PATH'].'public/uploads/thumb_'.$rowset[0]['cover_img']);
					}
					
					$rowset = $trackTable->select(array("master_id in (".$iMasterID.") "));
					$rowset = $rowset->toArray();
					foreach($rowset as $row)
					{
						if($row['audio_file'] !='')
							unlink($config['PATH'].'public/uploads/audio/'.$row['audio_file']);
					}
					
					$projectTable->delete(array("id in (".$iMasterID.") "));
					$trackTable->delete(array("master_id in (".$iMasterID.") "));
					$analyticsTable->delete(array("release_id in (".$iMasterID.") "));
				}
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }

	public function saveNoteAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
          
                $iMasterID = $_POST['KEY_ID'];
				$notes = $_POST['notes'];
				
				if(is_array($notes))
					$notes = implode(',',$notes);
				$aData=array();
				$aData['internal_notes'] = $notes;
				$aData['note_date_time'] = date('Y-m-d H:i:s');
				
                $projectTable->update($aData,array("id='".$iMasterID."'"));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            
        }
	}
	public function updateIsrcAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
            $aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			
			$iMasterID = $aData['idTrack'];
			$isrc = trim($aData['isrc']);
			
			if($isrc != '')
			{
				$rowset = $projectTable->select(array("id !='".$iMasterID."' and isrc like '%".$isrc."%' "));
				$rowset = $rowset->toArray();
			
				
				if(count($rowset) > 0)
				{
					$result['DBStatus'] = 'EXIST';
					$result = json_encode($result);
					echo $result;
					exit;
				}
			}
			
			$aData=array();
			$aData['isrc'] = $isrc;
			$projectTable->update($aData,array("id='".$iMasterID."'"));
			$result['DBStatus'] = 'OK';
			$result = json_encode($result);
			echo $result;
			exit;
            
        }
	}
	public function updateUpcAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
            $aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
                $iMasterID = $aData['idRelease'];
				$upc = $aData['upc'];
				
				$aData=array();
				$aData['upc'] = $upc;
				
				if($upc != '')
				{
					$rowset = $projectTable->select(array("id !='".$iMasterID."' and upc ='".$upc."'  "));
					$rowset = $rowset->toArray();
					if(count($rowset) > 0)
					{
						$result['DBStatus'] = 'EXIST';
						$result = json_encode($result);
						echo $result;
						exit;
					}
				}
				$aData=array();
				$aData['upc'] = $upc;
				
                $projectTable->update($aData,array("id='".$iMasterID."'"));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            
        }
	}
	public function processingAction()
{
    $request    = $this->getRequest();
    $customObj  = $this->CustomPlugin();
    $config     = $this->getServiceLocator()->get('config');

    if (!$request->isPost()) {
        echo json_encode(['DBStatus' => 'FAIL', 'Message' => 'Bad request']);
        exit;
    }

    if ($request->getPost("pAction") != "Processing") {
        echo json_encode(['DBStatus' => 'FAIL', 'Message' => 'Invalid action']);
        exit;
    }

    $iMasterID = (int)$request->getPost("KEY_ID");
    if ($iMasterID <= 0) {
        echo json_encode(['DBStatus' => 'FAIL', 'Message' => 'Invalid release id']);
        exit;
    }

    // services / tables
    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $projectTable      = new \Zend\Db\TableGateway\TableGateway('tbl_release', $adapter);
    $notesTable        = new \Zend\Db\TableGateway\TableGateway('tbl_internal_notes', $adapter);
    $notificationTable = new \Zend\Db\TableGateway\TableGateway('tbl_notification', $adapter);
    $staffTable        = new \Zend\Db\TableGateway\TableGateway('tbl_staff', $adapter);

    // 1. get current release row
    $releaseRow = $projectTable->select(['id' => $iMasterID])->current();
    if (!$releaseRow) {
        echo json_encode(['DBStatus' => 'FAIL', 'Message' => 'Release not found']);
        exit;
    }

    $wasInProcess = (
        isset($releaseRow['in_process']) &&
        (int)$releaseRow['in_process'] === 1
    );

    $existingNotesCsv = isset($releaseRow['internal_notes'])
        ? trim((string)$releaseRow['internal_notes'])
        : '';

    // 2. force update status + in_process (UI side consistent with bulk)
    $projectTable->update(
        [
            'in_process' => 1,
            'status'     => 'inreview', // same wording as catalog page
        ],
        ['id' => $iMasterID]
    );

    // 3. if already in process earlier -> don't spam any new note
    if ($wasInProcess) {
        echo json_encode([
            'DBStatus' => 'OK',
            'Message'  => 'Already in process, note not duplicated'
        ]);
        exit;
    }

    // 4. first time going "Processing": ensure internal note exists once

    $nowDb    = date('Y-m-d H:i:s');
    $autoText = "✅ Good News – Will Be Live Soon";

    // --- A) build list of existing note IDs for this release
    $alreadyHasAutoNote = false;
    $noteIdsForRelease  = [];

    if ($existingNotesCsv !== '') {
        $noteIdsForRelease = array_filter(
            array_map('intval', array_map('trim', explode(',', $existingNotesCsv))),
            function ($v) { return $v > 0; }
        );

        if (!empty($noteIdsForRelease)) {
            $placeholders = implode(',', $noteIdsForRelease);
            $sqlNotes = "SELECT id, notes FROM tbl_internal_notes WHERE id IN ($placeholders)";
            $rowsNotes = $adapter->query($sqlNotes, $adapter::QUERY_MODE_EXECUTE);

            foreach ($rowsNotes as $nRow) {
                if (
                    isset($nRow['notes']) &&
                    trim($nRow['notes']) === $autoText
                ) {
                    $alreadyHasAutoNote = true;
                    break;
                }
            }
        }
    }

    // if release already linked to that note text → just set note_date_time (optional) and exit
    if ($alreadyHasAutoNote) {
        $projectTable->update(
            [
                // keep existing internal_notes CSV as-is
                'note_date_time' => $nowDb, // you can keep or remove this if you DON'T want timestamp bump
            ],
            ['id' => $iMasterID]
        );

        echo json_encode([
            'DBStatus' => 'OK',
            'Message'  => 'Note already linked on this release'
        ]);
        exit;
    }

    // --- B) does a global row for this text already exist in tbl_internal_notes?
    $existingGlobalNoteRow = $notesTable->select(['notes' => $autoText])->current();
    if ($existingGlobalNoteRow) {
        $newNoteId = (int)$existingGlobalNoteRow['id'];
    } else {
        // not found globally: create ONE
        $noteInsertData = [
            'notes'      => $autoText,
            'color_code' => '#28a745', // green-ish success style
            'created_on' => $nowDb,    // remove if column doesn't exist in your table
        ];
        $notesTable->insert($noteInsertData);

        $newNoteId = $notesTable->getLastInsertValue();
        if (!$newNoteId) {
            // fallback if getLastInsertValue not supported properly
            $rowLast = $adapter->query(
                "SELECT MAX(id) AS max_id FROM tbl_internal_notes",
                $adapter::QUERY_MODE_EXECUTE
            )->current();
            $newNoteId = (int)$rowLast['max_id'];
        }
    }

    // --- C) append that note id to release CSV (avoid duplicate id in CSV)
    $finalCsv = '';
    if ($existingNotesCsv === '') {
        $finalCsv = (string)$newNoteId;
    } else {
        // make array unique + then implode
        $noteIdsForRelease[] = $newNoteId;
        $noteIdsForRelease = array_unique($noteIdsForRelease);
        $finalCsv = implode(',', $noteIdsForRelease);
    }

    // --- D) save back to release
    $projectTable->update(
        [
            'internal_notes' => $finalCsv,
            'note_date_time' => $nowDb,
        ],
        ['id' => $iMasterID]
    );

    echo json_encode(['DBStatus' => 'OK']);
    exit;
}


	public function takedownAction()
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
			
            if ($request->getPost("pAction") == "TAKEDOWN") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$aData=array();
				$aData['status'] = 'taken out';
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
				$rowset3 = $rowset3->toArray();
				
				$content ='<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
						<tr>
							<td align="left">
								<img src="'.$config['URL'].'public/img/user.png" alt="User Logo" style="max-width: 40px; height: auto;">
							</td>
						</tr>
					</table>
					
					<h2 style="color: #333;">Hello,</h2>

					<p>As per your request, we have initiated the removal process for the following title:</p>
					
					<p><strong>Title:</strong> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' - '.$rowset[0]['upc'].'</p>
					
					<p>Please note that the complete removal of this content from all music stores may take approximately 7 to 14 days, depending on the processing times of each platform.</p>
					
					<br><br><br>
					
					<p><strong>Prime Content Management Team</strong></p>

					<p>Thanks and regards,<br>
					The Prime Digital Arena team</p>';		
				
				foreach($rowset3 as $row3)
				{
					$nData = array();
					$nData['user_id'] = $row3['id'];
					$nData['type'] = 'Release TakenDown';
					$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been takendown by admin.';
					$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
					$notificationTable->insert($nData);
					$customObj->sendSmtpEmail($config,$row3['email'],'Request Accepted Removal Process Initiated ',$content,$row3['label_manager_email']);
				}
				
				
					
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function approvedAction()
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
			$trackTable = new TableGateway('tbl_track', $adapter);
			
            if ($request->getPost("pAction") == "APPROVED") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				$title = $rowset[0]['title'];
				$artist = $rowset[0]['releaseArtist'];

				$searchTerm = urlencode($title . ' ' . $artist);
				$apiUrl = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";

				$appleUrl = '';
				try {
					$response = file_get_contents($apiUrl);
					$result = json_decode($response, true);

					if (isset($result['resultCount']) && $result['resultCount'] > 0) {
						$appleUrl = $result['results'][0]['collectionViewUrl'];
					}
				} catch (Exception $e) {
					$appleUrl = '';
				}
				
				$aData=array();
				$aData['status'] = 'delivered';
				$aData['in_process'] = 0;
                $aData['apple_link'] = $appleUrl;
				
				if($rowset[0]['import_flag'] == '1')
					$aData['import_flag'] = 2;
				
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'public/cron_file/convertaudio.php '.$iMasterID);
				$customObj->start();	
				
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
			
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function rejectedAction()
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
            if ($request->getPost("pAction") == "REJECTED") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$aData=array();
				$aData['status'] = 'draft';
				$aData['in_process'] = 0;
				$aData['rejected_flag'] = 1;
				$aData['assigned_team'] = 0;
				$aData['reject_reason'] = $request->getPost("reason");
				$aData['internal_notes'] = '';
				$aData['note_date_time'] = '0000-00-00 00:00:00';
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
				$rowset3 = $rowset3->toArray();
				
				
					 $content ='<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
						<tr>
							<td align="left">
								<img src="'.$config['URL'].'public/img/user.png" alt="User Logo" style="max-width: 40px; height: auto;">
							</td> 
							<td align="right" style="font-size: 14px; color: #666;">
								<p>User Mail: <a href="mailto:info@primedigitalarena.in" style="color: #007bff; text-decoration: none;">info@primedigitalarena.in</a></p>
								<p>'.date('M d, Y, H:i').'</p>
							</td>
						</tr>
					</table>
					
					<h2 style="color: #333;">Hello,</h2>

					<p>The following release could not be validated:</p>
					
					<p> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].'</p>
					
					<p>'.$aData['reject_reason'].'</p>
					
					
					<p>We remain at your disposal for any further questions.</p>
					
					<br><br><br>
					
					<p><strong>Prime Content Management Team</strong></p>

					<p>Thanks and regards,<br>
					The Prime Digital Arena team</p>';
					
					
				
				foreach($rowset3 as $row3)
				{
					$nData = array();
					$nData['user_id'] = $row3['id'];
					$nData['type'] = 'Release Rejected';
					$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been rejected.';
					$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
					$notificationTable->insert($nData);
					
					$customObj->sendSmtpEmail($config,$row3['email'],'Action needed on your last release : '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' ',$content,$row3['label_manager_email']);
				}
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
    public function saveAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_department', $adapter);
    if($request->getPost("pAction") == "ADD")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		unset($aData['MASTER_KEY_ID']);
        $aData['created_by']=$_SESSION['user_id'];
        $aData['created_on']=date("Y-m-d h:i:s");
        $projectTable->insert($aData);
		
		//$customObj->createlog("module='Releases',action='Releases ".$aData['name']." Added',action_id='".$iMasterID."' ");
        $result['DBStatus'] = 'OK';
    }
    else  if($request->getPost("pAction") == "EDIT")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
        $iMasterID=$aData['MASTER_KEY_ID'];
        unset($aData['MASTER_KEY_ID']);
        $aData['updated_by']=$_SESSION['user_id'];
        $projectTable->update($aData,array("id=".$iMasterID));
		
		//$customObj->createlog("module='Releases',action='Releases ".$aData['name']." Edited',action_id='".$iMasterID."' ");
        $result['DBStatus'] = 'OK';
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
	public function exportAction()
	{
		$customObj = $this->CustomPlugin();
		
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$type = $_GET['type'];
		$search = (trim($_GET['search']));
		$data='Release Title,Version,Artist,Primary artist,Composer,Author,Label,Release Date/Houe/Timezone,# of Track,UPC,Cat. #';
		$data.="\n";
		
		$sWhere='';
				
		if($type == 'draft')
		{
			$sWhere.=" AND  status='draft'";
		}
		if($type == 'review')
		{
			$sWhere.=" AND  status='inreview'";
		}
		if($type == 'inprocess')
		{
			$sWhere.=" AND  status='inreview' and in_process=1";
		}
		else
		{
			if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1')
			{
				$sWhere.=" AND in_process=0";
			}
		}
		if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$sWhere.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) )"; 
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$sWhere.= $staff_cond;
		}
		if($search != '')
		{
			$sWhere.=" AND ( (title like '%".$search."%') or ( version like '%".$search."%')  or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%') or ( isrc like '%".$search."%') )";
		}
		$sWhere.=" and import_flag != 1 ";
		
		$where = " 1=1 ".$sWhere." order by id desc";
		
		$projectTable = new TableGateway('view_release', $adapter);
		$rowset = $projectTable->select(array($where));
		$rowset = $rowset->toArray();
		
	
		
		foreach($rowset as $row)
		{
			$trackTable = new TableGateway('tbl_track', $adapter);
			$rowset3 = $trackTable->select(array(" master_id='".$row['id']."' and isrc !='' order by volume,order_id asc limit 1"));
			$rowset3 = $rowset3->toArray();
			
			if($row['status'] == 'delivered' || $row['status'] == 'taken out' || $_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1'){} 
			else
				$rowset3[0]['isrc'] = '';
		
			$digitalReleaseDate=$row['digitalReleaseDate'];
			
			if($digitalReleaseDate != '0000-00-00')
				$digitalReleaseDate=date('d/m/Y',strtotime($digitalReleaseDate));
			else
				$digitalReleaseDate='';
			
			$data .= implode(',', [
				$this->escapeCsvValue($row['title']),
				$this->escapeCsvValue($row['version']),
				$this->escapeCsvValue($row['releaseArtist']),
				$this->escapeCsvValue($rowset3[0]['trackArtist']),
				$this->escapeCsvValue($rowset3[0]['composer']),
				$this->escapeCsvValue($rowset3[0]['author']),
				$this->escapeCsvValue($row['label_name']),
				$this->escapeCsvValue($digitalReleaseDate),
				$this->escapeCsvValue($row['tot_tracks']),
				$this->escapeCsvValue($row['upc']),
				//$this->escapeCsvValue($rowset3[0]['isrc']),
				$this->escapeCsvValue($row[0]['pcn']),
			]) . "\n";
		}
		
		$file = date('Ymd')."_my_catelog_export.csv";
       	header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$file");	
		echo $data;
		exit;
	}
	public function escapeCsvValue($value) {
		if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
			$value = str_replace('"', '""', $value);
			return '"' . $value . '"';
		}
		return $value;
	}
public function fnGrid()
{
	$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
  $trackTbl = new TableGateway('tbl_track', $adapter);
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
    $aColumns = array('id','"" as chkbx','status','cover_img','title','label_name','digitalReleaseDate','tot_tracks','upc','"" as delivery_status','assigned_team','"" as r_status','"" as r_title','releaseArtist','rejected_flag','reject_reason','version','pcn','in_process','internal_notes');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_release";
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
    /*
     * MySQL connection
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
	if($_GET['type'] == 'review')
	{
		$sOrder = " order by digitalReleaseDate asc";
	}
	else
	{
		$sOrder = " ORDER BY 
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM tbl_track t 
            WHERE t.master_id = view_release.id AND t.allow_replace = 1
        ) THEN 0
        ELSE 1
    END,
    FIELD(status, 'draft','inreview','delivered','taken out'),
    digitalReleaseDate DESC";
	}
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
  
  $sWhere = "";
if (!empty($_POST['search']['value'])) {
    $search_value = $_POST['search']['value'];
    $sWhere .= "WHERE (title LIKE '%$search_value%' OR artist LIKE '%$search_value%' OR upc LIKE '%$search_value%')";
}


  
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
        $sWhere=" where 1=1";
    else
        $sWhere.=" AND  1=1";
	
	if($_GET['type'] == 'draft')
	{
		$sWhere.=" AND  (status='draft' || status='rejected' )";
	}
	if($_GET['type'] == 'review')
	{
		$sWhere.=" AND  status='inreview'";
	}
	if($_GET['type'] == 'inprocess')
	{
		$sWhere.=" AND  status='inreview' and in_process=1";
	}
	else
	{
		if($_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1' )
		{
			$sWhere.=" AND in_process=0";
		}
	}
	if($_SESSION["STAFFUSER"] == 1 )
	{
		$staff_cond = $customObj->getStaffReleaseCond();
		$sWhere.= $staff_cond;
	}
	if($_SESSION['user_id'] != '0' &&  $_SESSION['STAFFUSER'] == '0')
	{
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		
		$user_artist = $customObj->getUserArtist($adapter);
		
		$astist_cond = '';
		
		if($user_artist != '')
		{
			$artist_array = array_map('trim', explode(',', $user_artist));
			$or_conditions = [];

			foreach ($artist_array as $artist_name) {
				$escaped_artist = mysqli_real_escape_string($mysqli, $artist_name);
				$or_conditions[] = "FIND_IN_SET('$escaped_artist', view_release.releaseArtist) > 0";
			}

			if (!empty($or_conditions)) {
				$astist_cond = ' AND (' . implode(' OR ', $or_conditions) . ')';
			}
		}
		$subUsers = $this->getSubUsers();
		
		$sWhere.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.")  $astist_cond ) OR created_by in (".$subUsers.") )";
	}
	if($_GET['notification'] > 0)
	{
		$sWhere.=" AND  id= '".$_GET['notification']."' ";
	}
	if (isset($_GET['search'])) {
		$search = trim($_GET['search']);
		$search = mysqli_real_escape_string($mysqli, $search); // Escape special characters to prevent SQL injection

		$sWhere .= " AND ( 
			title LIKE '%$search%' OR 
			version LIKE '%$search%' OR 
			label_name LIKE '%$search%' OR 
			upc LIKE '%$search%' OR 
			pcn LIKE '%$search%' OR 
			releaseArtist LIKE '%$search%' 
		)";
	}
	if ($_GET['filter_date'] != '') {
		$filter_date = explode(' to ',$_GET['filter_date']);
		$from_date = explode('-',$filter_date[0]);
		$to_date = explode('-',$filter_date[1]);
		
		$from_date = $from_date[2].'-'.$from_date[1].'-'.$from_date[0];
		$to_date = $to_date[2].'-'.$to_date[1].'-'.$to_date[0];
		
		$sWhere.=" AND  digitalReleaseDate >= '".$from_date."' and digitalReleaseDate <= '".$to_date."' ";
	}
	if ($_GET['filter_status'] != '') {
		$statuses = explode(',',$_GET['filter_status']);
		$status = array();
		$rejected = false;
		for($i=0;$i<count($statuses);$i++)
		{
			if($statuses[$i] == 'rejected')
				$rejected = true;
			else
				$status[] = "'".$statuses[$i]."'";
		}
		$status = implode(',',$status);
		
		$status_cond='';
		if($status != '')
		{
			$status_cond= " status in (".$status.") ";
		}
		
		$cond = '';
		if($rejected)
		{
			if($status_cond != '')
				$cond = " OR (status = 'draft' and rejected_flag=1)";
			else
				$cond = " (status = 'draft' and rejected_flag=1)";
		}
		$sWhere.=" AND  ( $status_cond $cond )";
	}
	
	$sWhere.=" and import_flag != 1 ";
	
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
      $row = array();
     $fixTrackId = 0;
    $fixRow = $trackTbl->select([
        'master_id'     => (int)$aRow['id'],
        'allow_replace' => 1
    ])->current();
    if ($fixRow) {
        $fixTrackId = (int)$fixRow['id'];
    }
     if ($aRow['status'] == 'delivered' && $fixTrackId > 0) {
        $row['DT_RowClass'] = 'row-correction';
    }
     // ✅ In Review
    if ($aRow['status'] == 'inreview') {
        $row['DT_RowClass'] = 'row-inreview';
    }
      
      
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
            }
			else if ( $aColumns[$i] == "digitalReleaseDate" )
            {
				if($aRow['digitalReleaseDate'] =='0000-00-00')
					$row[] ='';
				else
					$row[] = '<strong class="text-muted">'.date('d/m/Y',strtotime($aRow['digitalReleaseDate'])).'<strong>';
			}
			else if ( $aColumns[$i] == "title" )
            {
				$artist = '<i>empty</i>';
				$version = $aRow['version'];
				if(trim($version) != '')
				{
					$version = " ( ".$version.") "; 
				}
				if($aRow['releaseArtist'] !='')
				{
					$artist = '<strong class="text-muted">By</strong> '.$aRow['releaseArtist'];
				}
				$row[] ='<a href="releases/view?id='.$aRow['id'].'" target="_blank" >'.$aRow['title'].'</a>'.$version.'<br>'.$artist;
			}
			else if ( $aColumns[$i] == "upc" )
			{
				$upc_no='<i>empty</i>';
				if($aRow['upc'] !='')
				{
					$upc_no = $aRow['upc'];
				}
				$cat='<i>empty</i>';
				if($aRow['pcn'] !='')
				{
					$cat = $aRow['pcn'];  
				}
				
				$row[] = '<strong class="text-muted">UPC : </strong>'.$upc_no.'<br><strong class="text-muted">Cat# : </strong>'.$cat;
			}
				else if ($aColumns[$i] == "status") {

    // ✅ Delivered + Correction -> ONLY Repair Icon (no check icon)
    if ($aRow['status'] === 'delivered' && !empty($fixTrackId) && (int)$fixTrackId > 0) {
        $row[] = '<div title="Correction Needed" data-toggle="tooltip" data-placement="bottom">
            <button class="repair-btn js-user-replace-audio"
                    data-track-id="'.$fixTrackId.'"
                    style="background:none;border:0;cursor:pointer;padding:0;">
              <i class="fa fa-wrench" aria-hidden="true" style="font-size:20px;color:#444;vertical-align:middle;"></i>
            </button>
        </div>';

    // ✅ Delivered (no correction) -> ONLY Check Icon
    } elseif ($aRow['status'] === 'delivered') {
        $row[] = '<div title="This release is delivered" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-check" style="font-size:20px;vertical-align:middle;"></i>
        </div>';

    // 🔧 Draft (not rejected)
    } elseif ($aRow['status'] === 'draft' && $aRow['rejected_flag'] == '0') {
        $row[] = '<div title="This release needs to be finished" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-industry" style="font-size:20px;vertical-align:middle;"></i>
        </div>';

    // ❌ Draft (rejected flag on row)
    } elseif ($aRow['status'] === 'draft' && $aRow['rejected_flag'] == '1') {
        $row[] = '<div title="This release is rejected. '.$aRow['reject_reason'].'" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-industry" style="font-size:20px;vertical-align:middle;color:#d97878;"></i>
        </div>';

    // 🕒 In review
    } elseif ($aRow['status'] === 'inreview') {

    // check admin
    $isAdmin = (
        (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') ||
        (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 0)
    );

    $note_html = '';

    if ($isAdmin) {
        // only build dot for admin
        $note_html = '<div style="width:8px;height:8px;border-radius:100%;float:left;margin-top:5px;margin-right:5px;position:absolute;"></div>';

        if ($aRow['in_process'] == '0' && !empty($aRow['internal_notes'])) {
            $note_html = $this->getNoteColor($aRow['internal_notes']);
        }
    }

    // always show clock icon for everyone
    $row[] = $note_html . '<div title="This release is in review." data-toggle="tooltip" data-placement="bottom">
        <i class="fa fa-clock-o" style="font-size:20px;vertical-align:middle;"></i>
    </div>';
}
 elseif ($aRow['status'] === 'rejected') {
        $row[] = '<div title="This release is rejected. Please contact your Label Manager for more information" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-ban" style="font-size:20px;vertical-align:middle;"></i>
        </div>';

    // ⛔ Taken out
    } elseif ($aRow['status'] === 'taken out') {
        $row[] = '<div title="This release was taken down. Please contact your Label Manager for more information" data-toggle="tooltip" data-placement="bottom">
            <i class="fa fa-ban" style="font-size:20px;vertical-align:middle;"></i>
        </div>';

    } else {
        $row[] = '';
    }
}

			else if ( $aColumns[$i] == "cover_img" )
			{
				if($aRow['cover_img'] == '')
			    {
				    $row[] = '<img src="public/img/no-image.png" width="40">';
				}
				else
				{
					if (!file_exists("public/uploads/thumb_".$aRow['cover_img'])) 
					{
						if (file_exists("public/uploads/".$aRow['cover_img'])) 
							$this->create_thumbnail("public/uploads/".$aRow['cover_img'], "public/uploads/thumb_".$aRow['cover_img'], 150, 150); 
					}
					
					 $row[] = '<img src="public/uploads/thumb_'.$aRow['cover_img'].'" width="40">';
				}
			}
			else if ( $aColumns[$i] == "assigned_team" )
{
    // only admins can see the team
    $isAdmin = (
        (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') ||
        (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 0)
    );

    if ($isAdmin) {
        if ($aRow['assigned_team'] > 0) {
            $row[] = $this->getAssignedTeam($aRow['assigned_team']);
        } else {
            $row[] = '';
        }
    } else {
        // normal user view: always blank
        $row[] = '';
    }
}

			
			else if ( $aColumns[$i] == "tot_tracks" )
			{
				 $row[] = '<strong class="text-muted">'.$aRow['tot_tracks'].' Track'.'<strong>';
			}
			else if (strstr($aColumns[$i],"delivery_status") )
			{
				if($aRow['status'] == 'delivered')
				{
					$youtube_link = 'https://www.youtube.com/results?search_query='.str_replace(' ','+',$aRow['title']).'+'.str_replace(' ','+',$aRow['releaseArtist']);
					$spotify='https://open.spotify.com/search/'.str_replace(' ','%20',$aRow['title']).'%20'.str_replace(' ','%20',$aRow['releaseArtist']);
                   
				if (empty($aRow['apple_link'])) {
					$title = trim($aRow['title']);
					$artist = trim($aRow['releaseArtist']);
					$apple_link = '';

					// Step 1️⃣: Check if DB already has it (optional if already checked above)
					$releaseTable = new \Zend\Db\TableGateway\TableGateway('tbl_release', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
					$checkRow = $releaseTable->select(['id' => $aRow['id']])->current();
					if (!empty($checkRow['apple_link'])) {
						$apple_link = $checkRow['apple_link'];
					}

					// Step 2️⃣: If still empty, try Apple API (India first)
					if (empty($apple_link)) {
						$searchTerm = urlencode($title . ' ' . $artist);
						$apiUrlIN = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";
						$response = @file_get_contents($apiUrlIN);
						$result = json_decode($response, true);

						// Step 3️⃣: If IN fails, try US region
						if (!$result || $result['resultCount'] == 0) {
							$apiUrlUS = "https://itunes.apple.com/search?term=$searchTerm&country=US&media=music&entity=album";
							$response = @file_get_contents($apiUrlUS);
							$result = json_decode($response, true);
						}

						// Step 4️⃣: If found, update DB
						if ($result && isset($result['results'][0]['collectionViewUrl'])) {
							$apple_link = $result['results'][0]['collectionViewUrl'];
							$releaseTable->update(['apple_link' => $apple_link], ['id' => $aRow['id']]);
						}
					}

					// Step 5️⃣: Final fallback if nothing found
					if (empty($apple_link)) {
						$apple_link = "https://music.apple.com/in/search?term=" . urlencode($title);
					}

					// Set final result into row
					$aRow['apple_link'] = $apple_link;
				}
                  
					 $row[] = '<div class="d_status_wrapper">
								  <div class="d_status">
									<span class="delivery_report_icon"></span> 
									<span class="delivery_trigger_text">Completed</span> 
									<i class="material-icons" style="font-size:14px;margin-left:5px;">link</i>
								  </div>

								 <div class="delivery-popover">
									<div class="p-title">Delivery Links</div>
									
									<div class="delivery-link">
									  <img src="https://www.primebackstage.in/public/img/store2/YouTube%20Official%20Content.png" alt="YouTube" class="service-icon">
									  <a href="'.$youtube_link.'" target="_blank" class="link-text" title="'.$youtube_link.'">'.$youtube_link.'</a>
									  <button class="copy-btn" data-copy="'.$youtube_link.'">COPY</button>
									</div>
									
									<div class="delivery-link">
									  <img src="https://www.primebackstage.in/public/img/store2/spotify.png" alt="Spotify" class="service-icon spotify-icon">
									  <a href="'.$spotify.'" target="_blank" class="link-text" title="'.$spotify.'">'.$spotify.'</a>
									  <button class="copy-btn" data-copy="'.$spotify.'">COPY</button>
									</div>
                                    
                                    
<div class="delivery-link">
  <img src="https://www.primebackstage.in/public/img/store2/apple.png" alt="Apple Music" class="service-icon">
  <a href="'.$apple_link.'" target="_blank" class="link-text" title="'.$apple_link.'">'.$apple_link.'</a>
  <button class="copy-btn" data-copy="'.$apple_link.'">COPY</button>
</div>
    
									
									
									<a href="releases/deliveryreport?id='.$aRow['id'].'"  target="_blank" class="view-delivery-report">VIEW DELIVERY REPORT</a>
								  </div>
								</div>
								<a href="releases/deliveryreport?id='.$aRow['id'].'" target="_blank">View Delivery Report</a>';
				
                
                } else if ($aRow['status'] == 'inreview') {
        // 🟠 Submitted
        $row[] = '
        <div class="delivery-cell">
        <div class="delivery-pill is-submitted">
            <i class="fa fa-globe" aria-hidden="true"></i>
            <span>No Links</span>
          </div>
          <div class="delivery-pill is-submitted">
            <i class="fa fa-lock" aria-hidden="true"></i>
            <span>Submitted</span>
          </div>
          
        </div>';

    } else {
        // ⚪ 0 Stores (music icon)
        $row[] = '
        <div class="delivery-cell">
        <div class="delivery-pill is-empty">
            <i class="fa fa-globe" aria-hidden="true"></i>
            <span>No Links</span>
          </div>
          <div class="delivery-pill is-empty">
            <i class="fa fa-unlock" aria-hidden="true"></i>
            <span>0 Stores</span>
          </div>
          
          
        </div>';
    }

 
            
            }
         
			else if (strstr($aColumns[$i],"r_status") )
			{
				 $row[] = $aRow['status'];
			}
			else if (strstr($aColumns[$i],"r_title") )
			{
				 $row[] = $aRow['title'];
			}
			
			//else if (strstr($aColumns[$i],"isrc") )
			else if ($aColumns[$i] == "isrc")
			{
				if($aRow['status'] == 'delivered' || $aRow['status'] == 'taken out' || $_SESSION['user_id'] == '0' || $_SESSION['STAFFUSER'] == '1') 
				  $row[] = $aRow['isrc']; //$this->getTrackIsrc($aRow['id']); 
			    else
				  $row[] = '';
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
	
 public function getdepartmentAction()    
 {        
 $sl = $this->getServiceLocator();        
 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
 $sql="select id,name from tbl_department where deleted_flag=0 order by id desc";		        
 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
 $result = $statement->execute();        $resultSet = new ResultSet;        
 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
 $result1['DBData'] = $rowset;        
 $result1['recordsTotal'] = count($rowset);        
 $result1['DBStatus'] = 'OK';        
 $result = json_encode($result1);        echo $result;        exit;     
 }
 public function getTrackIsrc($id)    
 {        
 $sl = $this->getServiceLocator();        
 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
 $sql="select isrc from tbl_track where master_id='".$id."' and isrc !='' order by volume,order_id asc limit 1 ";		        
 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
 $result = $statement->execute();        $resultSet = new ResultSet;        
 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
 
	return $rowset[0]['isrc'];
 }
 public function getNoteColor($id)
 {
	  $sl = $this->getServiceLocator();       
	  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
	  $sql="select * from tbl_internal_notes where id in(".$id.")  ";		        
	  $optionalParameters=array();        
	  $statement = $adapter->createStatement($sql, $optionalParameters);        
	  $result = $statement->execute();        
	  $resultSet = new ResultSet;        
	  $resultSet->initialize($result);        
	  $rowset=$resultSet->toArray();
	  
	  return '<div style="width:6px;height:6px;border-radius:100%;background-color:'.$rowset[0]['color_code'].';float: left;margin-top: 5px;margin-right: 5px;position: absolute;"></div>';
 }
 public function getSubUsers()
 {
		$sl = $this->getServiceLocator();       
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		$sql="select * from tbl_staff where created_by = '".$_SESSION['user_id']."'  ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		
		if(count($rowset) > 0)
		{
			$users = array();
			foreach($rowset as $row)
				$users[] = $row['id'];
				
			$users = implode(',',$users);
			
			return $users;
		}
		else
			return 11111111111111111111;
		
 }
 public function getAssignedTeam($id)
 {
		  $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_staff where id = '".$id."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray();        
		  
		  $html='';
		  if($rowset[0]['photo'] != '')
		  {
			  $html = '<img src="public/uploads/'.$rowset[0]['photo'].'" style="border-radius:100%;border: 1px solid #d3d3d3;padding: 2px;width:40px;height:40px;margin:0 auto;">';
		  }
		  else
		  {
			 $html = '<img src="public/img/user-pic.png"  style="border-radius:100%;border: 1px solid #d3d3d3;padding: 2px;width:40px;height:40px;margin:0 auto;">';
		  }
		  $html.=$rowset[0]['nick_name'];
		  
		  return $html;
		 
 }
 public function getLabelID($label)
	{
		 $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_label where name = '".$label."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray();        
		 
		 if(count($rowset)>0)
		   return $rowset[0]['id'];
	     else
			return 0;
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
	
	public function getReleasesAction()    
	{  
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$cond.= $staff_cond;
		}
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select id, CONCAT(title, ' (', upc, ')') as name from tbl_release where  status='delivered' $cond ";		        
		 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;     
	}
	public function getArtistAction()    
	{  
		$customObj = $this->CustomPlugin();
		$mysqli=$customObj->dbconnection();
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');  
		 
		$cond="";
		if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			
			$user_artist = $customObj->getUserArtist($adapter);
		
			$astist_cond = '';
			
			if($user_artist != '')
			{
				$artist_array = array_map('trim', explode(',', $user_artist));
				$or_conditions = [];

				foreach ($artist_array as $artist_name) {
					$escaped_artist = mysqli_real_escape_string($mysqli, $artist_name);
					$or_conditions[] = "FIND_IN_SET('$escaped_artist', tbl_release.releaseArtist) > 0";
				}

				if (!empty($or_conditions)) {
					$astist_cond = ' AND (' . implode(' OR ', $or_conditions) . ')';
				}
			}
			
			$cond=" and labels in (".$labels.") $astist_cond ";
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$cond.= $staff_cond;
		}
		
		      
		 $sql="select id,releaseArtist  from tbl_release where  status='delivered' $cond ";		        
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        
		 $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        
		 $rowset=$resultSet->toArray();      

		 $artists_arr = array();
		 $art_array = array();
		 foreach($rowset as $row)
		 {
			$artists = explode(',',$row['releaseArtist']);
			foreach($artists as $artist)
			{ 
				$artist = trim($artist);
			    if(!in_array($artist,$art_array))
				{
					$art['id'] = $artist;
					$art['name'] = $artist;
					$artists_arr[] = $art;
					
					$art_array[] = $artist;
				}
			} 
		 }
		 $result1['DBData'] = $artists_arr;        
		 $result1['recordsTotal'] = count($artists_arr);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;     
	}
	public function getTrackAction()    
	{   
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$cond.= $staff_cond;
		}
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select id,songName as name  from view_tracks  where  status='delivered' $cond ";		        
		  $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;         
	}
	
	public function getStoreAction()    
	{   
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select store as id,store as name  from view_analytics  where 1=1 $cond group by store ";		        
		  $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;         
	}

    /**
     * Return Spotify linkage map for given artist names.
     * Usage: GET /releases/artistSpotifyMap?names[]=A&names[]=B
     * Response: JSON { "A": {"has":true,"id":"...","url":"..."} , ... }
     */
   public function artistSpotifyMapAction()
{
    try {
        $request = $this->getRequest();
        $names = [];
        if ($request->isPost()) {
            $names = (array)$this->params()->fromPost('names', []);
        } else {
            $names = (array)$this->params()->fromQuery('names', []);
        }

        // sanitize & unique
        $clean = [];
        foreach ($names as $n) {
            $n = trim((string)$n);
            if ($n !== '') {
                $clean[] = $n;
            }
        }
        $clean = array_values(array_unique($clean, SORT_STRING));

        $map = [];
        if (!empty($clean)) {
            $ph = implode(',', array_fill(0, count($clean), '?'));
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');

            // 🔹 Added spotify_requested column here
            $sql = "SELECT name, spotify_id, IFNULL(spotify_requested,0) AS spotify_requested 
                    FROM tbl_artist 
                    WHERE name IN ($ph)";
            $statement = $adapter->createStatement($sql, $clean);
            $result = $statement->execute();

            foreach ($result as $row) {
                $nm = (string)$row['name'];
                $spid = trim((string)($row['spotify_id'] ?? ''));
                $requested = (int)($row['spotify_requested'] ?? 0);
                $map[$nm] = [
                    'has'       => $spid !== '',
                    'id'        => $spid ?: null,
                    'url'       => $spid ? ('https://open.spotify.com/artist/' . $spid) : null,
                    'requested' => $requested,
                ];
            }
        }

        // ensure every requested name has an entry
        foreach ($clean as $nm) {
            if (!isset($map[$nm])) {
                $map[$nm] = [
                    'has' => false,
                    'id'  => null,
                    'url' => null,
                    'requested' => 0
                ];
            }
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
        $response->setContent(json_encode($map));
        return $response;
    } catch (\Throwable $e) {
        $response = $this->getResponse();
        $response->setStatusCode(500);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');
        $response->setContent(json_encode(['error' => true, 'message' => $e->getMessage()]));
        return $response;
    }
}


}//End Class