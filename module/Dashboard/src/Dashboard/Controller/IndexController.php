<?php

namespace Dashboard\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\ResultSet\ResultSet;
use Datetime;

class IndexController extends AbstractActionController
{
    protected $studentTable;

public function indexAction()
{ 
    $sl        = $this->getServiceLocator();     
    $customObj = $this->CustomPlugin();
    $adapter   = $sl->get('Zend\Db\Adapter\Adapter');

    $user_id   = $_SESSION['user_id'];
    $user_type = $_SESSION['ROLE_NAME'];

    // Tables
    $releaseTable   = new TableGateway("view_release", $adapter);
    $newsTable      = new TableGateway("tbl_news", $adapter);
    $withdrawTable  = new TableGateway("tbl_withdraw_request", $adapter);
    $payoutTable    = new TableGateway('tbl_user_payout', $adapter);
    $staffTable     = new TableGateway('tbl_staff', $adapter);

    // ===== USER CONDITION FOR RELEASES =====
    $cond = '';
    if ($user_id > 0 && $_SESSION["STAFFUSER"] == 0) {
        $cond = " and user_id='" . $user_id . "' ";
    }
    if ($_SESSION["STAFFUSER"] == 1) {
        $staff_cond = $customObj->getStaffReleaseCond();
        $cond      .= $staff_cond;
    }

    // 🔁 Payout Details Alert Logic
    $payoutRowset = $payoutTable->select(['user_id' => $user_id])->toArray();
    $showBankNotice = true;
    if (!empty($payoutRowset)) {
        usort($payoutRowset, function ($a, $b) { return intval($b['id']) - intval($a['id']); });
        $latestPayout = $payoutRowset[0];
        $status       = strtolower(trim($latestPayout['status'] ?? ''));
        if ($status === 'active') $showBankNotice = false;
    }

    // ===== LATEST RELEASES =====
    $rowset = $releaseTable->select(array("status='delivered' $cond order by digitalReleaseDate desc limit 4"))->toArray();
    $latest_release = '';
    foreach ($rowset as $row) {
        if (!file_exists("public/uploads/thumb_" . $row['cover_img'])) {
            $this->create_thumbnail(
                "public/uploads/" . $row['cover_img'],
                "public/uploads/thumb_" . $row['cover_img'],
                150, 150
            ); 
        }
        $latest_release .= '
        <a href="releases/view?id=' . $row['id'] . '" class="svelte-p1z7ut">
            <div class="svelte-p1z7ut">
                <div class="o-new-release__release svelte-p1z7ut">
                    <div class="o-new-release__releaseImg svelte-p1z7ut">
                        <img alt="' . $row['title'] . '" src="public/uploads/thumb_' . $row['cover_img'] . '" style="width: 100%;">
                    </div>
                    <div class="o-new-release__releaseStreams svelte-p1z7ut">
                        <div class="o-new-release__releaseStreamsTitle svelte-p1z7ut">' . $row['tot_tracks'] . '</div>
                        <div class="o-new-release__releaseStreamsSubtitle svelte-p1z7ut">streams</div>
                    </div>
                </div>
            </div>
        </a>';
    }

    // ===== LATEST DRAFTS =====
    $rowset = $releaseTable->select(array("status='draft' $cond order by rejected_flag,digitalReleaseDate desc limit 5"))->toArray();
    $latest_draft = '';
    foreach ($rowset as $row) {
        $class = "";
        $link  = "newrelease/submission?edit=" . $row['id'];
        if ($row['rejected_flag'] == '1') { $class = " rejected_title"; $link  = "releases/view?id=" . $row['id']; }
        if ($row['cover_img'] == '') { $row['cover_img'] = 'no-image.png'; }
        $latest_draft .= '
        <div class="card-item">
            <img src="public/uploads/thumb_' . $row['cover_img'] . '" width="76">
            <div class="details ' . $class . '">
                <a href="' . $link . '"><h3>' . $row['title'] . '</h3></a>
                <p>' . $row['releaseArtist'] . '</p>
            </div>
            <div class="rel_date">
                <p>Release Date</p>
                <h3>' . date('d M', strtotime($row['physicalReleaseDate'])) . '</h3>
            </div>
        </div>';
    }
    if ($latest_draft == '') $latest_draft .= '<div class="card-item-news">No any draft found</div>';

    // ===== NEWS =====
    $rowset = $newsTable->select(array("1=1 order by news_date desc limit 5"))->toArray();
    $news = '';
    foreach ($rowset as $row) {
        $news .= '
        <div class="card-item-news">
            <div class="news_date">
                <h3 id="' . $row['id'] . '">' . date('d', strtotime($row['news_date'])) . '<br><span>' . strtoupper(date('M', strtotime($row['news_date']))) . '</span></h3>
            </div>
            <div class="details">
                <h3 id="' . $row['id'] . '"><a href="javascript:;">' . $row['name'] . '</a></h3>
            </div>
        </div>';
    }
    if ($news == '') $news .= '<div class="card-item-news">No any news found</div>';

    // ===== BALANCE + SETTLEMENT =====
    $rowsetStaff = $staffTable->select(array("id='" . $user_id . "'"));
    $user = $rowsetStaff->current();
    if ($user && $user['payment_method'] == '0') $user['payment_method'] = '';

    $amount_info  = $customObj->getBalanceAmount($user, $adapter);
    $bal_amount   = $amount_info['amount'];

    $rowset10 = $withdrawTable->select("user_id='" . $user_id . "' and status='Success' order by approved_date desc limit 1 ")->toArray();
    $LAST_SETTLEMENT_DATE   = '';
    $LAST_SETTLEMENT_AMOUNT = '';
    if (count($rowset10) > 0) {
        $LAST_SETTLEMENT_DATE   = date('d M Y', strtotime($rowset10[0]['approved_date']));
        $LAST_SETTLEMENT_AMOUNT = '€' . $rowset10[0]['amount'];
    }

    // ===== Admin-only dashboard summary counters =====
    $PENDING_RIGHTS = 0; $OPEN_TICKETS = 0; $PROCESS_TICKETS = 0;
    $PENDING_WITHDRAW_REQUESTS = 0; $unpaidPayouts  = 0;

    if ($_SESSION['STAFFUSER'] == '1' || $_SESSION['user_id'] == '0') {
        $row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_rights_requests WHERE status = 'Pending'");
        $PENDING_RIGHTS = $row[0]['cnt'] ?? 0;

        $row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_support_tickets WHERE LOWER(status) = 'open'");
        $OPEN_TICKETS = $row[0]['cnt'] ?? 0;

        $row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_support_tickets WHERE LOWER(status) = 'in progress'");
        $PROCESS_TICKETS = $row[0]['cnt'] ?? 0;

        $sqlPayout    = "SELECT COUNT(*) AS unpaid_count FROM tbl_withdraw_request WHERE status = 'unpaid'";
        $rowPayout    = $adapter->query($sqlPayout, [])->current();
        $unpaidPayouts = $rowPayout['unpaid_count'] ?? 0;

        $sql = "SELECT COUNT(*) AS cnt FROM tbl_withdraw_request WHERE status = 'Pending'";
        $row = $adapter->query($sql, [])->current();
        $PENDING_WITHDRAW_REQUESTS = (int)($row['cnt'] ?? 0);
    }

    // ===== INFRINGEMENT FLAG FOR CURRENT USER =====
    $hasInfringement = false;
    $tmp_uid = $_SESSION['user_id'] ?? 0;
    if ($tmp_uid) {
        $sql = "
            SELECT COUNT(*) AS count 
            FROM tbl_rights_requests 
            WHERE request_type = 'infringement' 
              AND added_by_admin = 1 
              AND status = 'Pending'
              AND user_id = ?
        ";
        $resultRow = $adapter->query($sql, [$tmp_uid])->current();
        $hasInfringement = ($resultRow && $resultRow['count'] > 0);
    }

    // ===== DASHBOARD METRICS (cached) =====
    $metrics = $this->getCachedMetrics($user_id);
    if ($metrics === null) {
        $metrics = $this->buildMetricsForUser($user_id);
        $this->saveCachedMetrics($user_id, $metrics);
    }
    $MONTH_NAME = $metrics['MONTH_NAME'];
    $MONTH_DATA = $metrics['MONTH_DATA'];
    $TOP_TRACK  = $metrics['TOP_TRACK'];
    $TOP_STORE  = $metrics['TOP_STORE'];

    /* ====== DASHBOARD: Payout Progress (hidden by default) ======
       Only show if the latest withdraw request is "open". */
    $SHOW_PAYOUT_PROGRESS = false;
    $P_STATUS_STEP        = 0;           // 1=requested, 2=sent, 3=processed
    $P_STATUS_LABEL       = '';
    $P_REQUESTED_DATE     = '';
    $P_AMOUNT             = 0.0;

    // Legacy widget compatibility:
    $REQ_TYPE             = '';          // requested | sent | on hold | processed
    $REQ_DATE_LEGACY      = '';          // dd/mm/YYYY
    $REQ_AMOUNT_LEGACY    = '';

    try {
        $lastReqArr = $withdrawTable
            ->select("user_id='".intval($user_id)."' ORDER BY id DESC LIMIT 1")
            ->toArray();

        if (!empty($lastReqArr)) {
            $lastReq = $lastReqArr[0];
            $stRaw   = strtolower(trim($lastReq['status'] ?? ''));

            $createdVal = $lastReq['created_on'] ?? ($lastReq['created_at'] ?? null);
            $P_REQUESTED_DATE = $createdVal ? date('d/m/Y', strtotime($createdVal)) : '';
            $P_AMOUNT         = (float)($lastReq['amount'] ?? 0);

            $closedStates = ['success','rejected','declined'];
            $openStates   = ['pending','accepted','on hold','unpaid','processing'];

            if (in_array($stRaw, $openStates, true)) {
                $SHOW_PAYOUT_PROGRESS = true;

                switch ($stRaw) {
                    case 'pending':
                        $P_STATUS_STEP = 1;  $P_STATUS_LABEL = 'Requested'; $REQ_TYPE = 'requested'; break;
                    case 'accepted':
                        $P_STATUS_STEP = 2;  $P_STATUS_LABEL = 'Sent';       $REQ_TYPE = 'sent';      break;
                    case 'on hold':
                        $P_STATUS_STEP = 2;  $P_STATUS_LABEL = 'On Hold';    $REQ_TYPE = 'on hold';   break;
                    case 'unpaid':
                    case 'processing':
                        $P_STATUS_STEP = 3;  $P_STATUS_LABEL = 'Processed';  $REQ_TYPE = 'processed'; break;
                }

                $REQ_DATE_LEGACY   = $P_REQUESTED_DATE;
                $REQ_AMOUNT_LEGACY = (string)$P_AMOUNT;
            }
        }
    } catch (\Throwable $e) {
        $SHOW_PAYOUT_PROGRESS = false; // never break the dashboard
    }

    // ===== RETURN TO VIEW =====
    return new ViewModel([
        'LATEST_RELEASE' => $latest_release,
        'LATEST_DRAFTS'  => $latest_draft,
        'MONTH_NAME'     => $MONTH_NAME,
        'MONTH_DATA'     => $MONTH_DATA,
        'News'           => $news,
        'TOP_TRACK'      => $TOP_TRACK,
        'TOP_STORE'      => $TOP_STORE,
        'BALANCE'        => $bal_amount,
        'LAST_SETTLEMENT_DATE'   => $LAST_SETTLEMENT_DATE,
        'LAST_SETTLEMENT_AMOUNT' => $LAST_SETTLEMENT_AMOUNT,
        'showBankNotice' => $showBankNotice,
        'HAS_INFRINGEMENT' => $hasInfringement,

        // ✅ Admin summary counters
        'PENDING_RIGHTS'            => $PENDING_RIGHTS,
        'OPEN_TICKETS'              => $OPEN_TICKETS,
        'PROCESS_TICKETS'           => $PROCESS_TICKETS,
        'PENDING_WITHDRAW_REQUESTS' => $PENDING_WITHDRAW_REQUESTS,

        // ✅ New dashboard progress flags
        'SHOW_PAYOUT_PROGRESS' => $SHOW_PAYOUT_PROGRESS,
        'P_STATUS_STEP'        => $P_STATUS_STEP,
        'P_STATUS_LABEL'       => $P_STATUS_LABEL,
        'P_REQUESTED_DATE'     => $P_REQUESTED_DATE,
        'P_AMOUNT'             => $P_AMOUNT,

        // ✅ Legacy keys so the existing widget connects
        'REQ_TYPE'       => $REQ_TYPE,          // '', 'requested', 'sent', 'on hold', 'processed'
        'requested_date' => $REQ_DATE_LEGACY,   // dd/mm/YYYY or ''
        'req_amount'     => $REQ_AMOUNT_LEGACY, // '' when hidden
    ]);
}



    /* =========================
       CACHE HELPERS
       ========================= */

    private function getCachedMetrics($user_id) {
        $sl        = $this->getServiceLocator();     
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');
        $cacheTable = new TableGateway('tbl_dashboard_cache', $adapter);

        $rowset = $cacheTable->select(['user_id' => $user_id])->toArray();
        if (empty($rowset)) {
            return null; // no cache yet
        }

        $row = $rowset[0];

        // how fresh?
        $updatedAt = new \DateTime($row['updated_at']);
        $now       = new \DateTime();
        $ageMin    = ($now->getTimestamp() - $updatedAt->getTimestamp()) / 60;

        // if older than 30 mins, rebuild
        if ($ageMin > 1440) {
            return null;
        }

        $data = json_decode($row['metrics_json'], true);
        if (!is_array($data)) {
            return null; // corrupt json -> rebuild
        }

        return $data;
    }

    private function saveCachedMetrics($user_id, $metricsArr) {
        $sl        = $this->getServiceLocator();     
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');
        $cacheTable = new TableGateway('tbl_dashboard_cache', $adapter);

        $json = json_encode($metricsArr);

        // UPSERT style
        $exists = $cacheTable->select(['user_id' => $user_id])->current();
        $data = [
            'user_id'      => $user_id,
            'metrics_json' => $json,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($exists) {
            $cacheTable->update($data, ['user_id' => $user_id]);
        } else {
            $cacheTable->insert($data);
        }
    }

    private function buildMetricsForUser($user_id) {
        $sl        = $this->getServiceLocator();     
        $customObj = $this->CustomPlugin();
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');

        $isStaff = ($_SESSION["STAFFUSER"] == 1);

        // ===== label_cond logic (who can see what) =====
        $label_cond = '';
        if ($user_id != '0' && !$isStaff) {
            $labels = $customObj->getUserLabels($user_id);
            $label_cond .= " AND labels in (" . $labels . ") ";
        }
        if ($isStaff) {
            $staff_cond = $customObj->getStaffReleaseCond();
            $label_cond .= $staff_cond;
        }

        // ===== 6 month chart =====
        $lastMonths = [];
        $monthData  = [];

        $cursorDate = new \DateTime('first day of this month');
        $cursorDate->modify('-7 months'); // same logic you had

        for ($i = 0; $i < 6; $i++) {
            $monthYmd = $cursorDate->format('Y-m-01');
            $lastMonths[] = $cursorDate->format('M Y');

            $rowset2 = $this->executeQuery("
                SELECT SUM(streams) AS tot 
                FROM view_analytics 
                WHERE sales_month='" . $monthYmd . "' $label_cond
            ");
            $monthData[] = (int)($rowset2[0]['tot'] ?? 0);

            $cursorDate->modify('+1 month');
        }

        // ===== latest month =====
        $rowset3 = $this->executeQuery("SELECT sales_month FROM view_analytics ORDER BY sales_month DESC LIMIT 1");
        $latest_month = $rowset3[0]['sales_month'] ?? '0000-00-00';

        $rowset5 = $this->executeQuery("
            SELECT SUM(streams) AS tot 
            FROM view_analytics 
            WHERE sales_month='" . $latest_month . "' $label_cond
        ");
        $total_stream = (int)($rowset5[0]['tot'] ?? 0);

        // ===== top tracks (top 3 releases for latest month) =====
        $rowset4 = $this->executeQuery("
            SELECT *,
                   SUM(streams) AS tot
            FROM view_analytics
            WHERE sales_month='" . $latest_month . "'
              AND release_id > 0
              $label_cond
            GROUP BY release_id
            ORDER BY SUM(streams) DESC
            LIMIT 3
        ");

        $top_track_html = '';
        foreach ($rowset4 as $row4) {
            $per = ($total_stream > 0)
                ? number_format(($row4['tot'] * 100 / $total_stream), 1, '.', '')
                : 0;

            $top_track_html .= '
            <div class="card-item">
                <img src="public/uploads/thumb_' . $row4['cover_img'] . '" width="76">
                <div class="track_details">
                    <a href="analytics"><h3>' . $row4['title'] . '</h3></a>
                    <p>' . $per . '% streams of your catalog</p>
                </div>
                <div class="track_info">
                    <h2>' . $this->formatViewCount($row4['tot']) . '</h2>
                    <p>Streams</p>
                </div>
            </div>';
        }

        // ===== top stores (top 4 stores for latest month) =====
        // define colors because original code referenced $color without showing it
        $colorSet = [
            "#4a90e2", "#50e3c2", "#f5a623", "#bd10e0",
            "#9013fe", "#7ed321", "#f8e71c", "#d0021b"
        ];

        $rowset6 = $this->executeQuery("
            SELECT SUM(streams) AS tot_streams, store
            FROM view_analytics
            WHERE sales_month='" . $latest_month . "'
            $label_cond
            GROUP BY store
            ORDER BY SUM(streams) DESC
            LIMIT 4
        ");

        $top_store_arr = [];
        $i = 0;
        foreach ($rowset6 as $row) {
            $store_image = $this->getStoreImage($row['store']);
            $top_store_arr[] = [
                'name'  => $row['store'],
                'y'     => (int)$row['tot_streams'],
                'color' => $colorSet[$i % count($colorSet)],
                'img'   => $store_image
            ];
            $i++;
        }

        // final package that will be cached
        return [
            'MONTH_NAME'   => $lastMonths,      // ["May 2025", "Jun 2025", ...]
            'MONTH_DATA'   => $monthData,       // [1234, 5678, ...]
            'TOP_TRACK'    => $top_track_html,  // rendered HTML cards
            'TOP_STORE'    => $top_store_arr,   // array for donut/list UI
            'LATEST_MONTH' => $latest_month
        ];
    }


    /* =========================
       OTHER ACTIONS / UTILS
       ========================= */

    public function maintenanceAction()
    {
        $file = 'maintenance.flag';
        if (file_exists($file)) {
            unlink($file); // Turn OFF
            $status = false;
        } else {
            file_put_contents($file, '1'); // Turn ON
            $status = true;
        }
         
        echo json_encode(['mode' => $status ? 'ON' : 'OFF']);
        exit;
    }
	
    public function getStoreImage($store)
    {
        $s = strtolower($store);
        if(strstr($s,"ugc"))
            $img = "../public/img/store2/youtube ugc.png";
        else if(strstr($s,"amazon"))
            $img = "../public/img/store2/amazon.png";
        else if(strstr($s,"apple"))
            $img = "../public/img/store2/apple.png";
        else if(strstr($s,"believe"))
            $img = "../public/img/store2/Believe Rights Services.jpg";					
        else if(strstr($s,"spotify"))
            $img = "../public/img/store2/spotify.png";	
        else if(strstr($s,"saavn"))
            $img = "../public/img/store2/jiosaavn.png";
        else if(strstr($s,"hungama"))
            $img = "../public/img/store2/hungama.avif";
        else if(strstr($s,"itunes"))
            $img = "../public/img/store2/iTunes.png";	
        else if(strstr($s,"resso"))
            $img = "../public/img/store2/Resso-tiktok.jpg";
        else if(strstr($s,"snapchat"))
            $img = "../public/img/store2/snapchat-logo.svg";
        else if(strstr($s,"soundcloud"))
            $img = "../public/img/store2/Soundcloud.png";
        else if(strstr($s,"uma"))
            $img = "../public/img/store2/UMA (Vkontakte).png";
        else if(strstr($s,"tier"))
            $img = "../public/img/store2/YouTube Audio Tier.png";
        else if(strstr($s,"shorts"))
            $img = "../public/img/store2/Youtube_shorts_icon.png";
        else if(strstr($s,"facebook"))
            $img = "../public/img/store2/Facebook_instagram.png";
        else if(strstr($s,"tiktok"))
            $img = "../public/img/store2/tiktok.png";
        else if(strstr($s,"wynk"))
            $img = "../public/img/store2/Wynk Music.png";
        else if(strstr($s,"other"))
            $img = "../public/img/store2/Others.png";
        else if(strstr($s,"youtube official"))
            $img = "../public/img/store2/YouTube Official Content.png";
        else if(strstr($s,"douyin"))
            $img = "../public/img/store2/Douyin.png";
        else if(strstr($s,"ultimate"))
            $img = "../public/img/store2/Ultimate Music.jpg";
        else
            $img = "../public/img/store2/Others.png";

        return $img;
    }

    public function updatenotificationviewAction()
    {
        $sl        = $this->getServiceLocator();        
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');
        $user_id   = $_SESSION['user_id'];
        $user_type = $_SESSION['ROLE_NAME'];

        $projectTable = new TableGateway("tbl_notification_history",$adapter);

        $uData['read_flag'] = 1;
        $projectTable->update($uData, array(
            "user_id"   => $user_id,
            "user_type" => $user_type
        ));

        $result = [
            'DBStatus' => 'OK'
        ];
        echo json_encode($result);
        exit;
    }
	
    public function getNotificationAction()
    {
        $sl        = $this->getServiceLocator();        
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');
        $user_id   = $_SESSION['user_id'];
        $user_type = $_SESSION['ROLE_NAME'];

        $staffTable = new TableGateway("tbl_staff",$adapter);
        $rowset     = $staffTable->select(array("id='".$user_id."' "))->toArray();
        $last_logout_time = $rowset[0]['last_logout'] ?? '1970-01-01 00:00:00';
		
        $rowset = $this->executeQuery("
            SELECT * 
            FROM tbl_notification 
            WHERE user_id='".$user_id."' 
              AND datetime >= '".$last_logout_time."'
        ");
		
        $data = "";
        foreach($rowset as $row) {
            $data .= '<a href="'.$row['url'].'"><i class="material-icons">check_circle</i>'.$row['title'].'</a>';
        }
		
        $result = [
            'tot_new'  => count($rowset),
            'NotiData' => $data,
            'DBStatus' => 'OK'
        ];
        echo json_encode($result);
        exit;
    }

    public function executeQuery($sql)
    {
        $sl        = $this->getServiceLocator();       
        $adapter   = $sl->get('Zend\Db\Adapter\Adapter');   
        $optionalParameters = array();        
        $statement = $adapter->createStatement($sql, $optionalParameters);        
        $result    = $statement->execute();        
        $resultSet = new ResultSet;        
        $resultSet->initialize($result);        
        $rowset    = $resultSet->toArray();
        return $rowset;        
    }

    public function formatViewCount($num)
    {
        $num = number_format($num, 2, '.', '');
		
        if ($num >= 1000000) {
            $formatted = $num / 1000000;
            return (floor($formatted) == $formatted)
                ? number_format($formatted, 0) . 'M'
                : number_format($formatted, 1) . 'M';
        } elseif ($num >= 1000) {
            $formatted = $num / 1000;
            return (floor($formatted) == $formatted)
                ? number_format($formatted, 0) . 'K'
                : number_format($formatted, 1) . 'K';
        }
        return (strpos($num, '.00') !== false)
            ? (string)(int)$num
            : (string)$num;
    }

    public function readNotificationAction()
    {
        $sl      = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		
        $id = $request->getPost('id');
		
        $projectTable = new TableGateway("tbl_notification_history",$adapter);
        $aData['close_flag'] = 1;
        $projectTable->update($aData, array("id='".$id."'"));
		
        $result = [
            'DBStatus' => 'OK'
        ];
        echo json_encode($result);
        exit;
    }

    public function getnewsAction()
    {
        $sl      = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		
        $id = $request->getPost('id');
		
        $projectTable = new TableGateway("tbl_news",$adapter);
        $rowset = $projectTable->select(array("id='".$id."' "))->toArray();
        $row    = $rowset[0];
		
        $INFO = '
            <div class="card-item-news-popup">
                <div class="news_date">
                    <h3>'.date('d',strtotime($row['news_date'])).'<br><span>'.strtoupper(date('M',strtotime($row['news_date']))).'</span></h3>
                </div>
                <div class="details">
                    <h3>'.$row['name'].'</h3>
                </div>		
            </div>
            <div class="content" style="height: 400px;overflow: auto;">
                <p>'.$row['description'].'</p>
            </div>';
		
        $result = [
            'INFO'     => $INFO,
            'DBStatus' => 'OK'
        ];
        echo json_encode($result);
        exit;
    }

    public function create_thumbnail($source_path, $target_path, $thumb_width, $thumb_height) 
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
            $new_width  = $thumb_height * $aspect_ratio;
            $new_height = $thumb_height;
        } else {
            $new_width  = $thumb_width;
            $new_height = $thumb_width / $aspect_ratio;
        }

        $x = ($thumb_width - $new_width) / 2;
        $y = ($thumb_height - $new_height) / 2;

        // Resize and copy
        imagecopyresampled(
            $thumbnail,
            $source_image,
            $x, $y,
            0, 0,
            $new_width, $new_height,
            $width, $height
        );

        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $target_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $target_path);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $target_path);
                break;
        }

        imagedestroy($source_image);
        imagedestroy($thumbnail);

        return $target_path;
    }
} // End Class
