<?php
namespace Analytics\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Predicate\Between;

class ArtistController extends AbstractActionController
{
    /* ====== basic services ====== */
    protected function db(): Adapter
    {
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }

    /* ====== accept id in any case (id, ID, Id, iD) ====== */
    private function getArtistIdFromQuery(): int
    {
        $all = $this->params()->fromQuery(null, []);
        foreach (['id','ID','Id','iD'] as $k) {
            if (isset($all[$k])) return (int)$all[$k];
        }
        return 0;
    }


    /* ====== tiny safety helpers ====== */
    private function toArray($v): array
    {
        if ($v instanceof \Traversable) $v = iterator_to_array($v);
        if ($v === null) return [];
        if (is_array($v)) return $v;
        if (is_string($v)) {
            $j = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($j)) return $j;
            return ($v === '') ? [] : [$v];
        }
        return [$v];
    }
    private function nullable($v){ if (is_string($v)) $v = trim($v); return ($v === '' || $v === null) ? null : $v; }
    private function nullableInt($v){ if ($v === '' || $v === null) return null; return (int)$v; }
    private function enum($val, array $allowed, $fallback){ return in_array($val, $allowed, true) ? $val : $fallback; }

    /* ====== dynamic table helpers ====== */
    private function artistTable(): string { return 'tbl_artist'; }
    private function primaryArtistTableExists(): bool
    {
        try {
            $db  = $this->db();
            $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_primary_artist' LIMIT 1";
            $rs = $db->getDriver()->createStatement($sql)->execute();
            return (bool)$rs->current();
        } catch (\Throwable $e) { return false; }
    }
    private function columnExists(string $table, string $col): bool
    {
        try {
            $db  = $this->db();
            $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
            $rs = $db->getDriver()->createStatement($sql, [$table,$col])->execute();
            return (bool)$rs->current();
        } catch (\Throwable $e) { return false; }
    }

    /** Map tbl_artist.id -> one/more tbl_release.primary_artist_id */
    private function mapToPrimaryArtistIds(int $artistId): array
    {
        if ($artistId <= 0) return [];

        $db   = $this->db();
        $sqlZ = new Sql($db);

        // 1) Direct relationship: tbl_artist.primary_artist_id
        if ($this->columnExists('tbl_artist','primary_artist_id')) {
            $sel = $sqlZ->select('tbl_artist')->columns(['primary_artist_id'])->where(['id'=>$artistId])->limit(1);
            $row = $sqlZ->prepareStatementForSqlObject($sel)->execute()->current();
            $pid = (int)($row['primary_artist_id'] ?? 0);
            if ($pid > 0) return [$pid];
        }

        // 2) Mapping table (optional): tbl_artist_map(artist_id, primary_artist_id)
        if ($this->columnExists('tbl_artist_map','artist_id') && $this->columnExists('tbl_artist_map','primary_artist_id')) {
            $sel = $sqlZ->select('tbl_artist_map')->columns(['primary_artist_id'])->where(['artist_id'=>$artistId]);
            $ids = [];
            foreach ($sqlZ->prepareStatementForSqlObject($sel)->execute() as $r) {
                $v = (int)$r['primary_artist_id']; if ($v>0) $ids[]=$v;
            }
            $ids = array_values(array_unique($ids));
            if ($ids) return $ids;
        }

        // 3) Fallback: name-based join against tbl_primary_artist
        if ($this->primaryArtistTableExists()) {
            // get artist name
            $selA = $sqlZ->select('tbl_artist')->columns(['name'])->where(['id'=>$artistId])->limit(1);
            $rowA = $sqlZ->prepareStatementForSqlObject($selA)->execute()->current();
            $name = trim((string)($rowA['name'] ?? ''));
            if ($name !== '') {
                // exact match first
                $stmt = $db->getDriver()->createStatement(
                    "SELECT id FROM tbl_primary_artist WHERE name = ? LIMIT 10",
                    [$name]
                );
                $ids = [];
                foreach ($stmt->execute() as $r) { $v=(int)$r['id']; if ($v>0) $ids[]=$v; }
                // looser match (remove spaces / lower)
                if (!$ids) {
                    $stmt = $db->getDriver()->createStatement(
                        "SELECT id FROM tbl_primary_artist WHERE REPLACE(LOWER(name),' ','') = REPLACE(LOWER(?),' ','') LIMIT 10",
                        [$name]
                    );
                    foreach ($stmt->execute() as $r) { $v=(int)$r['id']; if ($v>0) $ids[]=$v; }
                }
                $ids = array_values(array_unique($ids));
                if ($ids) return $ids;
            }
        }

        // 4) Worst-case: no mapping
        return [];
    }

    /* ====== range helpers ====== */
   

    /* ====== common fetchers ====== */
    private function fetchArtist(int $id): ?array
    {
        if ($id <= 0) return null;
        $sql = new Sql($this->db());
        $sel = $sql->select($this->artistTable())->where(['id'=>$id])->limit(1);
        $row = $sql->prepareStatementForSqlObject($sel)->execute()->current();
        return $row ?: null;
    }

    /** Pull ISRCs via primary_artist_id mapping */
    private function getArtistIsrcs(int $artistId): array
    {
        $pids = $this->mapToPrimaryArtistIds($artistId);
        if (!$pids) return [];

        $sql = new Sql($this->db());
        $sel = $sql->select(['t'=>'tbl_track'])
            ->columns(['isrc'])
            ->join(['r'=>'tbl_release'],'t.master_id=r.id',[])
            ->where((new In('r.primary_artist_id', $pids)))
            ->where("t.isrc IS NOT NULL AND t.isrc <> ''");

        $rows = $sql->prepareStatementForSqlObject($sel)->execute();
        $isrcs = [];
        foreach ($rows as $r) $isrcs[] = $r['isrc'];
        return array_values(array_unique($isrcs));
    }

    private function artistRollup(int $artistId): array
    {
        $isrcs = $this->getArtistIsrcs($artistId);
        if (!$isrcs) return ['all_time_streams'=>0,'monthly_listeners'=>0];
        $sql = new Sql($this->db());
        $sel = $sql->select(['a'=>'tbl_analytics'])
            ->columns(['s'=>new Expression('SUM(a.streams)')])
            ->where(new In('a.isrc',$isrcs));
        $row = $sql->prepareStatementForSqlObject($sel)->execute()->current();
        return [
            'all_time_streams'  => (int)($row['s'] ?? 0),
            'monthly_listeners' => 0,
        ];
    }

    /* ====== pages ====== */
    public function indexAction()
    {
        return $this->redirect()->toUrl($this->url()->fromRoute('artists/edit'));
    }

    public function pageAction()
    {
        $id = $this->getArtistIdFromQuery();

        $artist = $this->fetchArtist($id);
        if (!$artist) {
            $artist = [
                'id'        => $id,
                'name'      => 'Artist Name',
                'image_url' => '/public/img/demo/artist-placeholder.png',
                'country'   => 'IN',
            ];
        }

        if (!empty($artist['id'])) {
            $kpi = $this->artistRollup((int)$artist['id']);
            $artist['all_time_streams']  = $kpi['all_time_streams'];
            $artist['monthly_listeners'] = $kpi['monthly_listeners'];
        } else {
            $artist['all_time_streams']  = 0;
            $artist['monthly_listeners'] = 0;
        }

        $view = new ViewModel([
            'artist'  => $artist,
            'period'  => 'Last Reported Quarter',
            'account' => ['id' => '—'],
        ]);
        $view->setTemplate('analytics/index/artist');
        return $view;
    }

  
    /* ====== editor ====== */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id <= 0) $id = $this->getArtistIdFromQuery();

        $artist = $this->fetchArtist($id);
        return new ViewModel([
            'artist' => $artist ?: [
                'id'=>0,'name'=>'','spotify_id'=>'','apple_id'=>'','image_url'=>'','banner_url'=>'',
                'country'=>'','followers'=>'','popularity'=>'','ext_url'=>'','apple_url'=>'',
                'link_status'=>'pending','bio'=>'','socials_json'=>'',
            ],
            'isNew'=>!$artist,
        ]);
    }

    public function saveAction()
    {
        $req = $this->getRequest();
        if (!$req->isPost()) {
            return new JsonModel(['ok' => false, 'error' => 'Invalid method']);
        }

        $in = $req->getPost()->toArray();
        $id = (int)($in['id'] ?? 0);

        /* ---------- Smart Apple Field Logic ---------- */
        $appleInput = trim((string)($in['apple_id'] ?? ''));
        $appleId = null;
        $appleUrl = null;

        if ($appleInput !== '') {
            if (is_numeric($appleInput)) {
                $appleId = $appleInput;
                $appleUrl = "https://music.apple.com/artist/" . $appleInput;
            } else {
                $appleUrl = $appleInput;
                if (preg_match('/\/artist\/[^\/]+\/(\d+)/', $appleInput, $m)) {
                    $appleId = $m[1];
                }
            }
        }

        /* ---------- Bio word limit check (max 20 words) ---------- */
        $bio = trim((string)($in['bio'] ?? ''));
        $wordCount = str_word_count($bio);
        if ($wordCount > 20) {
            return new JsonModel([
                'ok' => false,
                'error' => 'Bio can contain a maximum of 20 words (currently '.$wordCount.').'
            ]);
        }

        /* ---------- Build payload ---------- */
        $payload = [
            'name'        => trim((string)($in['name'] ?? '')),
            'spotify_id'  => $this->nullable($in['spotify_id'] ?? null),
            'apple_id'    => $this->nullable($appleId ?: null),
            'apple_url'   => $this->nullable($appleUrl ?: null),
            'banner_url'  => $this->nullable($in['banner_url'] ?? null),
            'country'     => $this->nullable($in['country'] ?? null),
            'followers'   => $this->nullableInt($in['followers'] ?? null),
            'popularity'  => $this->nullableInt($in['popularity'] ?? null),
            'ext_url'     => $this->nullable($in['ext_url'] ?? null),
            'link_status' => $this->enum(($in['link_status'] ?? 'pending'),
                              ['pending','linked','needs_review'], 'pending'),
            'bio'         => $bio,
        ];

        if (!empty($in['socials_json'])) $payload['socials_json'] = $in['socials_json'];

        if ($payload['name'] === '') {
            return new JsonModel(['ok' => false, 'error' => 'Artist name is required']);
        }

        try {
            $sql = new Sql($this->db());
            $table = $this->artistTable();

            if ($id > 0) {
                $update = $sql->update($table)->set($payload)->where(['id' => $id]);
                $sql->prepareStatementForSqlObject($update)->execute();
            } else {
                $insert = $sql->insert($table)->values($payload);
                $res = $sql->prepareStatementForSqlObject($insert)->execute();
                $id = (int)$res->getGeneratedValue();
            }

            return new JsonModel(['ok' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return new JsonModel(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ====== popup: artists by track ====== */
    public function byTrackAction()
    {
        $all = $this->params()->fromQuery(null, []);
        $trackId = 0;
        foreach (['id','ID','track_id','TRACK_ID'] as $k) {
            if (isset($all[$k])) { $trackId = (int)$all[$k]; break; }
        }
        if ($trackId <= 0) return new JsonModel(['ok'=>false,'error'=>'track_id required']);

        try {
            $sql = new Sql($this->db());
            $sel = $sql->select(['ta'=>'tbl_track_artists'])
                ->columns([])
                ->join(['a'=>$this->artistTable()], 'a.id=ta.artist_id', ['id','name','image_url','spotify_id','apple_id','link_status'])
                ->where(['ta.track_id'=>$trackId])
                ->order(['a.name ASC']);

            $rows = $sql->prepareStatementForSqlObject($sel)->execute();
            $out  = [];
            foreach ($rows as $r) $out[] = $r;

            return new JsonModel(['ok'=>true,'rows'=>$out]);
        } catch (\Throwable $e) {
            return new JsonModel(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }

       }

    // Convenience aliases if you want to expose in routes:
  
