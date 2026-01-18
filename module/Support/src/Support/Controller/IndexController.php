<?php
namespace Support\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
public function indexAction()
{
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $user_id = $_SESSION['user_id'] ?? 0;
    $range = (int)($_GET['range'] ?? 90);
    $range = in_array($range, [90, 180, 365]) ? $range : 90;
    $dateFrom = date('Y-m-d', strtotime("-$range days"));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? 10);
    $perPage = in_array($perPage, [10, 15, 20]) ? $perPage : 10;
    $offset = ($page - 1) * $perPage;

    $where = "1=1";

    if ($user_id != 0 && $_SESSION['STAFFUSER'] == '0') {
    $where .= " AND r.user_id = " . (int)$user_id;
    // ✅ User table: show only open items → Pending + In Process
    $where .= " AND r.status IN ('Pending', 'In Process')";
} else {
    // Admin main table: keep only Pending (In Process admin ke alag block me aa raha hai)
    $where .= " AND r.status = 'Pending'";
}


    // Total count for pagination
    $countSql = "
        SELECT COUNT(*) AS total
        FROM tbl_rights_requests r
        LEFT JOIN tbl_staff s ON r.user_id = s.id
        WHERE $where
    ";
    $total = (int) $adapter->query($countSql)->execute()->current()['total'];
    $totalPages = ($total > 0) ? ceil($total / $perPage) : 1;

    $page = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
    $offset = ($page - 1) * $perPage;

    // Main rights requests (pending, takedown, rejected)
    $sql = "
        SELECT r.*, s.Company_name AS staff_name
        FROM tbl_rights_requests r
        LEFT JOIN tbl_staff s ON r.user_id = s.id
        WHERE $where
        ORDER BY r.id DESC
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $adapter->query($sql);
    $rights = $stmt->execute();

    $rightsArr = [];
    $counts = [
        'conflict'     => 0,
        'dispute'      => 0,
        'takedown'     => 0,
        'ugc'          => 0,
        'release'      => 0,
        'infringement' => 0
    ];

    // ✅ RESOLVED ISSUES LOAD MORE LOGIC
    $resolvedOffset = (int)($_GET['resolved_offset'] ?? 0);
    $resolvedLimit = 4;

    $resolvedSql = "
        SELECT 
            r.id,
            r.release_upc,
            r.release_title,
            r.release_artist,
            r.release_image,
            r.status,
            r.closed_at,
            r.request_type,
            r.created_at,
            COALESCE(r.closed_at, r.created_at) AS resolved_date
        FROM tbl_rights_requests r
        WHERE r.status != 'Pending'
          AND r.user_id = $user_id
          AND COALESCE(r.closed_at, r.created_at) >= '$dateFrom'
        ORDER BY resolved_date DESC 
        LIMIT 10
    ";

    $resolvedIssues = iterator_to_array($adapter->query($resolvedSql)->execute());

    // ✅ Handle AJAX "load more" for resolved
    if ($this->getRequest()->isXmlHttpRequest() && isset($_GET['load_resolved'])) {
        $view = new ViewModel([
            'RESOLVED_ISSUES' => $resolvedIssues
        ]);
        $view->setTerminal(true);
        $view->setTemplate('support/index/resolved-cards.phtml'); // your partial
        return $view;
    }
$inProcessArr = [];
$isAdmin = (($_SESSION['user_id'] ?? 0) == 0 || ($_SESSION['STAFFUSER'] ?? '0') === '1');

if ($isAdmin) {
    $inProcSql = "
        SELECT r.*, s.Company_name AS staff_name
        FROM tbl_rights_requests r
        LEFT JOIN tbl_staff s ON r.user_id = s.id
        WHERE r.status = 'In Process'
        ORDER BY r.id DESC
        LIMIT 200
    ";
    $stmtIn = $adapter->query($inProcSql);
    $inProc = $stmtIn->execute();
    foreach ($inProc as $row) {
        $inProcessArr[] = [
            'id'            => (int)$row['id'],
            'release_upc'   => (string)$row['release_upc'],
            'release_title' => (string)$row['release_title'],
            'release_artist'=> (string)$row['release_artist'],
            'request_type'  => (string)$row['request_type'],
            'release_image' => (string)$row['release_image'],
            'status'        => (string)$row['status'],
            'user_name'     => (string)$row['staff_name'],
            'created_at'    => (string)$row['created_at'],
        ];
    }
}
    // COUNTS
    $globalCountWhere = "status != 'Approved' AND created_at >= '$dateFrom'";
    if ($user_id != 0 && $_SESSION['STAFFUSER'] == '0') {
        $globalCountWhere .= " AND user_id = $user_id";
    }

    $globalCountSql = "
        SELECT LOWER(TRIM(request_type)) AS type, COUNT(*) AS total
        FROM tbl_rights_requests
        WHERE $globalCountWhere
        GROUP BY LOWER(TRIM(request_type))
    ";

    foreach ($adapter->query($globalCountSql)->execute() as $row) {
        $typeRaw = strtolower(trim($row['type']));
        $mappedType = match ($typeRaw) {
            'claim ugc', 'claim_ugc', 'claim ugc video', 'monetize' => 'ugc',
            'release claim', 'release_claim' => 'release',
            'copyright infringement', 'copyright' => 'infringement',
            default => $typeRaw
        };
        if (isset($counts[$mappedType])) {
            $counts[$mappedType] = (int)$row['total'];
        }
    }

    // Attach YTAF
    foreach ($rights as $r) {
        $r['yt_ugc_status'] = $this->isYtafEnabledForRelease($r['release_upc'], $adapter) ? 'Active' : 'Disabled';
        $rightsArr[] = $r;
    }

    // ✅ AJAX check for full rights table
    $isAjax = $this->getRequest()->isXmlHttpRequest();

    if ($isAjax) {
        $viewModel = new ViewModel([
            'RIGHTS_LIST'   => $rightsArr,
            'CURRENT_PAGE'  => $page,
            'TOTAL_PAGES'   => $totalPages,
            'PER_PAGE'      => $perPage,
        ]);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('support/index/rightstable.phtml');
        return $viewModel;
    }

    // ✅ Normal render
    return new ViewModel([
        'RIGHTS_LIST'      => $rightsArr,
        'RIGHTS_COUNTS'    => $counts,
        'CURRENT_PAGE'     => $page,
        'DATE_RANGE'       => $range,
        'TOTAL_PAGES'      => $totalPages,
        'PER_PAGE'         => $perPage,
        'RESOLVED_ISSUES'  => $resolvedIssues,
      'IN_PROCESS_LIST'  => $inProcessArr
    ]);
}


    private function isYtafEnabledForRelease($upc, $adapter)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM tbl_analytics a
            JOIN tbl_release r ON a.release_id = r.id
            WHERE r.upc = ? AND a.store = 'YouTube UGC'
        ";
        $res = $adapter->query($sql, [$upc])->current();
        return ($res['total'] ?? 0) > 0;
    }

   public function saverequestAction()
{
    $request = $this->getRequest();
    if ($request->isPost()) {
        try {
            $data = $request->getPost();

            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $rightsTable = new TableGateway('tbl_rights_requests', $adapter);

            $currentUserId = $_SESSION['user_id'] ?? 0;
            $targetUserId = $currentUserId;

            if ((int)$currentUserId === 0) {
                $sql = "
                    SELECT s.id AS user_id
                    FROM tbl_release r
                    LEFT JOIN tbl_label l ON r.labels = l.id
                    LEFT JOIN tbl_staff s ON FIND_IN_SET(r.labels, s.labels)
                    WHERE r.upc = ?
                    ORDER BY r.id DESC
                    LIMIT 1
                ";
                $userRow = $adapter->query($sql, [$data['release_upc']])->current();
                if ($userRow && $userRow['user_id'] > 0) {
                    $targetUserId = $userRow['user_id'];
                }
            }

            // ✅ Insert the request
            $rightsTable->insert([
                'user_id'        => $targetUserId,
                'platform'       => $data['platform'] ?? '',
                'request_type'   => $data['request_type'] ?? '',
                'release_title'  => $data['release_title'] ?? '',
                'release_image'  => $data['release_image'] ?? '',
                'release_upc'    => $data['release_upc'] ?? '',
                'release_artist' => $data['release_artist'] ?? '',
                 'album'          => $data['album'] ?? '', // ✅ ✅ ✅ Add this line
                'youtube_links'  => $data['youtube_links'] ?? '',
                'status'         => 'Pending',
                'created_at'     => date('Y-m-d H:i:s'),
                'added_by_admin' => ($currentUserId == 0) ? 1 : 0,
                'mail_sent'      => 0
            ]);

            $insertedId = $rightsTable->getLastInsertValue();

            // ✅ Send Email Directly – Based on who submitted
            $controllerManager = $sl->get('ControllerManager');
            $emailController = $controllerManager->get('Support\Controller\Email');

            if ((int)$currentUserId === 0) {
                $emailController->sendAdminRightsNoticeDirect($insertedId);
            }

            // Always send user mail
            $emailController->sendRightsNotificationDirect($insertedId);

            echo json_encode(['status' => 'success']);
            exit;

        } catch (\Exception $e) {
            error_log("🔥 saverequest error: " . $e->getMessage());
            echo json_encode(['status' => 'fail', 'error' => 'Server error, please try again.']);
            exit;
        }
    }

    echo json_encode(['status' => 'fail']);
    exit;
}
    public function updateStatusAction()
{
    $request = $this->getRequest();
    header('Content-Type: application/json');

    if (!$request->isPost()) {
        echo json_encode(['status' => 'error', 'msg' => 'invalid']);
        return $this->getResponse();
    }

    $id     = (int)$request->getPost('id');
    $raw    = (string)$request->getPost('status');
    $status = strtolower(trim($raw));
    $reason = (string)$request->getPost('reject_reason', '');

    $adapter     = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $rightsTable = new \Zend\Db\TableGateway\TableGateway('tbl_rights_requests', $adapter);

    $row = $rightsTable->select(['id' => $id])->current();
    if (!$row) {
        echo json_encode(['status' => 'error', 'msg' => 'not found']);
        return $this->getResponse();
    }

    // -------- 1) Semantic normalize (do NOT decide DB text yet) --------
    // map to one of: pending | in_process | approved | rejected | resolved | takedown | confirmed
    $semantic = null;
    switch ($status) {
        case 'back_to_pending':
        case 'pending':
            $semantic = 'pending'; break;

        case 'in_process':
        case 'processing':
        case 'in process':
            $semantic = 'in_process'; break;

        case 'approved':
            $semantic = 'approved'; break;

        case 'rejected':
            $semantic = 'rejected'; break;

        case 'resolved':
            $semantic = 'resolved'; break;

        case 'confirmed':
            $semantic = 'confirmed'; break;

        case 'takedown':
            $semantic = 'takedown'; break;

        default:
            echo json_encode(['status' => 'error', 'msg' => 'bad status']);
            return $this->getResponse();
    }

    // -------- 2) Decide DB status text (preserve OLD special rules) --------
    $isAdminGenerated = (isset($row['added_by_admin']) && (int)$row['added_by_admin'] === 1);
    // Default
    $dbStatus = 'Pending';

    if ($isAdminGenerated) {
        // 🟢 OLD RULES: admin-generated
        //  - approved -> Resolved
        //  - rejected -> Takedown
        if ($semantic === 'approved' || $semantic === 'resolved' || $semantic === 'confirmed') {
            $dbStatus = 'Resolved';
        } elseif ($semantic === 'rejected') {
            $dbStatus = 'Takedown';
        } elseif ($semantic === 'in_process') {
            $dbStatus = 'In Process';
        } elseif ($semantic === 'takedown') {
            $dbStatus = 'Takedown';
        } else { // pending etc.
            $dbStatus = 'Pending';
        }
    } else {
        // 🔵 OLD RULES: normal user request (status as-is, with clean casing)
        //  - approved should remain Approved (NOT Resolved)
        if ($semantic === 'approved') {
            $dbStatus = 'Approved';
        } elseif ($semantic === 'resolved' || $semantic === 'confirmed') {
            $dbStatus = 'Resolved';
        } elseif ($semantic === 'rejected') {
            $dbStatus = 'Rejected';
        } elseif ($semantic === 'in_process') {
            $dbStatus = 'In Process';
        } elseif ($semantic === 'takedown') {
            $dbStatus = 'Takedown';
        } else { // pending
            $dbStatus = 'Pending';
        }
    }

    // -------- 3) Build update payload (final/open + reason + closed_at) --------
    $data = ['status' => $dbStatus];

    // Final statuses (align with OLD list): approved, rejected, resolved, takedown, confirmed
    $finalStatuses = ['Approved','Rejected','Resolved','Takedown','Confirmed'];
    $isFinal = in_array($dbStatus, $finalStatuses, true);

    // reject_reason rules:
    if ($dbStatus === 'Rejected' || $dbStatus === 'Takedown') {
        $data['reject_reason'] = ($reason !== '') ? $reason : null;
    } else {
        $data['reject_reason'] = null;
    }

    // closed_at rules:
    if ($isFinal) {
        $data['closed_at'] = date('Y-m-d H:i:s');
    } else {
        // Pending / In Process → open state
        $data['closed_at'] = null;
    }

    $rightsTable->update($data, ['id' => $id]);

    echo json_encode([
        'status'     => 'success',
        'db_status'  => $dbStatus,
        'final'      => $isFinal ? 1 : 0,
        'id'         => $id
    ]);
    return $this->getResponse();
}



    public function deleteRequestAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $request->getPost('id');

            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $rightsTable = new TableGateway('tbl_rights_requests', $adapter);

            $rightsTable->delete(['id' => $id]);
            echo json_encode(['status' => 'success']);
            exit;
        }
        echo json_encode(['status' => 'fail']);
        exit;
    }

public function fetchReleasesAction()
{
    $customObj = $this->CustomPlugin();
    $request   = $this->getRequest();

    $query  = (string)$request->getQuery('q', '');
    $page   = max(1, (int)$request->getQuery('page', 1));
    $limit  = 50;
    $offset = ($page - 1) * $limit;

    $adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $placeholders = $customObj->getUserLabels($_SESSION['user_id'] ?? 0);

    // label filter
    $cond = '';
    if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0 && (string)($_SESSION['STAFFUSER'] ?? '0') === '0') {
        $cond = $placeholders !== '' ? " AND r.labels IN ($placeholders) " : " AND r.labels IN (0) ";
    }

    try {
        $params = [];
        $where  = "WHERE 1=1 $cond";

        if ($query !== '') {
            // 🔎 search ONLY on release fields: title / UPC / artist (NOT track title)
            // Artist: hum LEFT JOIN se t.trackArtist ko allow kar rahe hain (artist search ke liye),
            // lekin track **title** kahin use nahi kar rahe.
            $like   = '%' . $query . '%';
            $where .= " AND (r.title LIKE ? OR r.upc LIKE ? OR t.trackArtist LIKE ?)";
            $params = [$like, $like, $like];
        }

        // 🧾 Release list (DISTINCT by r.id). Artist display ke liye MIN(t.trackArtist).
        $sql = "
            SELECT
                r.id        AS master_id,
                r.title     AS title,
                MIN(t.trackArtist) AS artist,
                r.upc       AS upc,
                r.cover_img AS cover
            FROM tbl_release r
            LEFT JOIN tbl_track t
                   ON t.master_id = r.id
                  AND t.order_id > 0
            $where
            GROUP BY r.id, r.title, r.upc, r.cover_img
            ORDER BY r.id DESC
            LIMIT $limit OFFSET $offset
        ";

        $stmt   = $adapter->createStatement($sql, $params);
        $result = $stmt->execute();

        $data = [];
        foreach ($result as $row) {
            // 🎵 Tracks (sirf display/select ke liye; search ko affect nahi karte)
            $trackSql  = "
                SELECT songName AS title, isrc
                FROM tbl_track
                WHERE master_id = ? AND order_id > 0
                ORDER BY order_id ASC
            ";
            $trackStmt = $adapter->createStatement($trackSql, [$row['master_id']]);
            $trackRes  = $trackStmt->execute();

            $albumTracks = [];
            foreach ($trackRes as $t) {
                $albumTracks[] = [
                    'title' => $t['title'],
                    'isrc'  => $t['isrc']
                ];
            }

            $data[] = [
                'title'        => $row['title'],
                'artist'       => $row['artist'] ?: '',
                'upc'          => $row['upc'],
                'cover'        => !empty($row['cover']) ? '/public/uploads/' . $row['cover'] : '/public/img/default_cover.png',
                'album_tracks' => $albumTracks
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    } catch (\Throwable $e) {
        // 🛡️ safe fallback
        error_log('fetchReleasesAction error: ' . $e->getMessage());
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }
}

  
  public function flagEntryAction()
{
    $request = $this->getRequest();

    if ($request->isPost()) {
        $id = (int) $request->getPost('id');

        try {
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new TableGateway('tbl_rights_requests', $adapter);

            $row = $table->select(['id' => $id])->current();

            if ($row) {
                $newStatus = ($row['flag_status'] === 'flagged') ? 'none' : 'flagged';
                $table->update(['flag_status' => $newStatus], ['id' => $id]);

                return new JsonModel([
                    'status' => 'success',
                    'new_status' => $newStatus
                ]);
            } else {
                return new JsonModel([
                    'status' => 'error',
                    'message' => 'Record not found'
                ]);
            }

        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ]);
        }
    }

    return new JsonModel([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}
    public function getrecAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_label', $adapter);
            $rowset = $projectTable->select(['id' => $iID])->toArray();

            $result = [
                'data'          => $rowset,
                'recordsTotal'  => count($rowset),
                'DBStatus'      => 'OK'
            ];
            echo json_encode($result);
            exit;
        }
    }

    public function rightsWizardAction()
    {
        return new ViewModel();
    }

    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }
}