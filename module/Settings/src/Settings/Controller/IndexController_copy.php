<?php
namespace Settings\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Adapter;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class IndexController extends AbstractActionController
{
    public $dbAdapter1;

    public function init() {}

    public function getStaffName($id)
    {
        if ($id == '') return '';

        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $staffTable = new TableGateway('tbl_staff', $dbAdapter);
        $rowset = $staffTable->select(array("id in (".$id.")"));
        $rowset = $rowset->toArray();

        $name = array();
        foreach ($rowset as $row) {
            $name[] = $row['first_name'].' '.$row['last_name'];
        }
        return implode(',', $name);
    }

    /* =========================
     * MCN Requests (existing)
     * ========================= */
    public function updatemcnAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = (int)$request->getPost('mcn_id');
            $status = trim($request->getPost('status'));
            $reason = trim($request->getPost('reason'));

            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new TableGateway('tbl_mcn_requests', $adapter);

            $updateData = ['status' => $status];
            if ($status == 'rejected') $updateData['rejection_reason'] = $reason;

            $table->update($updateData, ['id' => $id]);
        }

        return $this->redirect()->toUrl('/settings/bankinformation?tab=mcn');
    }

    public function deletemcnAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = (int)$request->getPost('mcn_id');
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new TableGateway('tbl_mcn_requests', $adapter);
            $table->delete(['id' => $id]);
        }

        return $this->redirect()->toUrl('/settings/bankinformation?tab=mcn');
    }

    public function viewmcnAction()
    {
        $request = $this->getRequest();
        $id = (int) $request->getQuery('id');

        // Admin-only check
        if (!isset($_SESSION['STAFFUSER']) || $_SESSION['STAFFUSER'] != '1') {
            return $this->redirect()->toUrl('/access-denied');
        }

        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $table = new \Zend\Db\TableGateway\TableGateway('tbl_mcn_requests', $adapter);

        $select = new \Zend\Db\Sql\Select();
        $select->from('tbl_mcn_requests')
            ->join('tbl_staff', 'tbl_mcn_requests.user_id = tbl_staff.id', [
                'first_name', 'last_name', 'company_name', 'email'
            ])
            ->where(['tbl_mcn_requests.id' => $id]);

        $row = $table->selectWith($select)->current();

        if (!$row) {
            echo "Invalid request ID."; exit;
        }

        $viewModel = new \Zend\View\Model\ViewModel(['row' => $row]);
        $viewModel->setTerminal(true); // for modal-only rendering
        return $viewModel;
    }

    /* =========================
     * Profile / Settings (existing)
     * ========================= */
    public function myaccountAction() {}

    public function profileAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $config = $this->getServiceLocator()->get('config');

        $projectTable = new TableGateway('tbl_staff', $adapter);
        $labelTable = new TableGateway('tbl_label', $adapter);
        $networkTable = new TableGateway('tbl_releasing_network', $adapter);

        $rowset = $projectTable->select(array("id='".$_SESSION['user_id']."' "));
        $rowset = $rowset->toArray();

        $labels = array();
        $rowset2 = $labelTable->select(array("id in (".$rowset[0]['labels'].") "));
        $rowset2 = $rowset2->toArray();
        foreach ($rowset2 as $row2) {
            $labels[] = $row2['name'];
        }

        if ($rowset[0]['releasing_network'] == '')
            $rowset[0]['releasing_network'] = 0;

        $network = array();
        $rowset2 = $networkTable->select(array("id in (".$rowset[0]['releasing_network'].") "));
        $rowset2 = $rowset2->toArray();
        foreach ($rowset2 as $row2) {
            $network[] = $row2['name'];
        }

        $rowset[0]['labels'] = implode(',', $labels);
        $rowset[0]['releasing_network'] = implode(',', $network);

        $viewModel = new ViewModel([
            'Info' => $rowset[0]
        ]);
        return $viewModel;
    }

    public function uploadlogoAction()
    {
        $request = $this->getRequest();
        $serviceLocator = $this->getServiceLocator();
        $adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');

        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;

        $config = $this->getServiceLocator()->get('config');
        $base_path = $config['BASE_PATH'];

        $projectTable = new TableGateway('tbl_settings', $adapter);

        if ($request->isPost()) {
            $file = $_FILES['attachment_file'];
            $filename = $_FILES['attachment_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            $filename = date('YmdHis').'.'.$ext;
            $myImagePath =  "public/uploads/$filename";

            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $aData=array();
                $aData['logo'] = $filename;
                $projectTable->update($aData);

                $_SESSION['LOGO'] = $filename;
                $result['status'] = 'OK';
                $result['message1'] = 'Done';
                $result['doc_file1'] = $filename;
            }
        }
        $result = json_encode($result);
        echo $result;
        exit;
    }

    public function uploadfavAction()
    {
        $request = $this->getRequest();
        $serviceLocator = $this->getServiceLocator();
        $adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');

        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;

        $config = $this->getServiceLocator()->get('config');
        $base_path = $config['BASE_PATH'];

        $projectTable = new TableGateway('tbl_settings', $adapter);

        if ($request->isPost()) {
            $file = $_FILES['attachment_file'];
            $filename = $_FILES['attachment_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            $filename = date('YmdHis').'.'.$ext;
            $myImagePath =  "public/uploads/$filename";

            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $aData=array();
                $aData['favicon'] = $filename;
                $projectTable->update($aData);

                $_SESSION['FAVICON'] = $filename;
                $result['status'] = 'OK';
                $result['message1'] = 'Done';
                $result['doc_file1'] = $filename;
            }
        }
        $result = json_encode($result);
        echo $result;
        exit;
    }

    /* =========================
     * Banking / Payout (existing)
     * ========================= */
    public function bankinformationAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $userID = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['STAFFUSER'] == '1' || $userID == 0);

        $isResubmitting = (isset($_GET['resubmit']) && $_GET['resubmit'] == '1');
        $forceReload    = (isset($_GET['reload']) && $_GET['reload'] == '1');

        // Fetch user info
        $staffTable = new \Zend\Db\TableGateway\TableGateway('tbl_staff', $adapter);
        $rowset = $staffTable->select(['id' => $userID])->toArray();
        $user = count($rowset) > 0 ? $rowset[0] : [];

        $methodRaw = strtolower(trim($user['payment_method'] ?? ''));
        $method = (strpos($methodRaw, 'payoneer') !== false) ? 'payoneer' :
                  ((strpos($methodRaw, 'paypal') !== false) ? 'paypal' :
                  ((strpos($methodRaw, 'bank') !== false) ? 'bank' : ''));

        $user['country'] = $user['country'] ?? ($user['isoCountry'] ?? '');
        $countryCode = strtolower($user['isoCountry'] ?? '');

        // Get latest payout
        $payoutSql = "
            SELECT p.*, s.id AS user_id
            FROM tbl_user_payout p
            LEFT JOIN tbl_staff s ON s.id = p.user_id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 1
        ";
        $stmt = $adapter->createStatement($payoutSql, [$userID]);
        $payoutData = $stmt->execute()->current();

        $isRejected = $payoutData && strtolower($payoutData['status']) === 'rejected';
        $submitted = false;

        if ($isResubmitting) {
            $table = new \Zend\Db\TableGateway\TableGateway('tbl_user_payout', $adapter);
            $lastRejected = $table->select(function($s) use ($userID) {
                $s->where(['user_id' => $userID, 'status' => 'rejected']);
                $s->order('id DESC')->limit(1);
            })->current();
            $payoutData = $lastRejected;
            $submitted = false;
        } elseif ($payoutData) {
            $submitted = true;
        }

        if (!$payoutData) {
            $submitted = false;
            $isResubmitting = false;
        }

        // Verification form condition
        $showVerificationForm = false;
        if (
            !$isAdmin &&
            $payoutData &&
            strtolower($payoutData['payment_method']) === 'bank' &&
            strtolower($payoutData['status']) === 'pending_verification' &&
            !empty($payoutData['test_amount']) &&
            (float)$payoutData['test_amount'] > 0 &&
            (int)$payoutData['is_verified'] === 0
        ) {
            $showVerificationForm = true;
        }

        // Submission History (user)
        $submissionHistory = [];
        if (!$isAdmin) {
            $sql = "
                SELECT p.*, s.payment_method AS method
                FROM tbl_user_payout p
                LEFT JOIN tbl_staff s ON s.id = p.user_id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
            ";
            $submissionHistory = iterator_to_array($adapter->query($sql, [$userID]));
        }

        // Admin Payout Listing
        $allPayouts = [];
        $totalPages = 1;
        $currentPage = (isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1);
        $perPage = 10;
        $offset = ($currentPage - 1) * $perPage;
        $search = trim($_GET['q'] ?? '');

        if ($isAdmin) {
            $whereClause = "1=1";
            $params = [];

            if (!empty($search)) {
                $whereClause .= " AND (
                    s.first_name LIKE ? OR
                    s.last_name LIKE ? OR
                    s.company_name LIKE ? OR
                    s.email LIKE ?
                )";
                $searchParam = '%' . $search . '%';
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            }

            $countSql = "
                SELECT COUNT(*) AS total
                FROM tbl_user_payout p
                LEFT JOIN tbl_staff s ON s.id = p.user_id
                WHERE $whereClause
            ";
            $countStmt = $adapter->createStatement($countSql);
            $totalRow = $countStmt->execute($params)->current();
            $totalRows = (int)($totalRow['total'] ?? 0);
            $totalPages = max(1, ceil($totalRows / $perPage));

            $dataSql = "
                SELECT p.*, s.id AS user_id, s.company_name,
                       CONCAT(s.first_name, ' ', s.last_name) AS full_name,
                       s.payment_method AS method
                FROM tbl_user_payout p
                LEFT JOIN tbl_staff s ON s.id = p.user_id
                WHERE $whereClause
                ORDER BY p.created_at DESC
                LIMIT $perPage OFFSET $offset
            ";
            $dataStmt = $adapter->createStatement($dataSql);
            $allPayouts = iterator_to_array($dataStmt->execute($params));
        }

        // Admin MCN Requests
        $mcnRequests = [];
        if ($isAdmin) {
            // columns probe
            $colsRes = $adapter->query("SHOW COLUMNS FROM tbl_mcn_requests", $adapter::QUERY_MODE_EXECUTE);
            $cols = [];
            foreach ($colsRes as $r) { $cols[] = $r['Field']; }
            $exists = function($name) use ($cols) { return in_array($name, $cols, true); };
            $pick = function(array $cands) use ($exists) {
                foreach ($cands as $c) if ($exists($c)) return $c;
                return null;
            };
            $map = [
                'id'                  => $pick(['id']),
                'user_id'             => $pick(['user_id']),
                'status'              => $pick(['status']),
                'created_at'          => $pick(['created_at','createdAt','created']),
                'channel_url'         => $pick(['channel_url','channel_link','url']),
                'channel_type'        => $pick(['channel_type','type']),
                'relationship'        => $pick(['relationship','relation']),
                'upload_type'         => $pick(['upload_type','uploadtype']),
                'upload_pattern'      => $pick(['upload_pattern','pattern']),
                'subscriber_count'    => $pick(['subscriber_count','subscribers']),
                'estimated_earnings'  => $pick(['estimated_earnings','estimated_revenue','revenue']),
                'watchtime_eligible'  => $pick(['watchtime_eligible','watchtime_ok','watchtime4000','watchtime']),
                'monetized'           => $pick(['monetized','is_monetized','monetization']),
                'music_only'          => $pick(['music_only','is_music_only','music']),
                'has_covers'          => $pick(['has_covers','cover_songs','has_cover_songs']),
                'has_strikes'         => $pick(['copyright_strikes','copyright_strike','has_strikes']),
                'strike_reason'       => $pick(['strike_reason','copyright_reason','strike_reason_text']),
                'watchtime_proof'     => $pick(['watchtime_proof','watchtime_screenshot','watchtime_image']),
                'screenshot'          => $pick(['screenshot','channel_screenshot','proof_screenshot']),
            ];
            $emit = function($col, $alias, $default) {
                if ($col) return "r.`{$col}` AS `{$alias}`";
                return "{$default} AS `{$alias}`";
            };
            $parts = [];
            $parts[] = $emit($map['id'],              'id',              "NULL");
            $parts[] = $emit($map['user_id'],         'user_id',         "NULL");
            $parts[] = $emit($map['status'],          'status',          "''");
            $parts[] = $emit($map['created_at'],      'created_at',      "NULL");
            $parts[] = $emit($map['channel_url'],     'channel_url',     "''");
            $parts[] = $emit($map['channel_type'],    'channel_type',    "''");
            $parts[] = $emit($map['relationship'],    'relationship',    "''");
            $parts[] = $emit($map['upload_type'],     'upload_type',     "''");
            $parts[] = $emit($map['upload_pattern'],  'upload_pattern',  "''");
            $parts[] = $emit($map['subscriber_count'],'subscriber_count',"0");
            $parts[] = $emit($map['estimated_earnings'],'estimated_earnings',"''");
            $parts[] = $emit($map['watchtime_eligible'],'watchtime_eligible',"''");
            $parts[] = $emit($map['monetized'],       'monetized',       "''");
            $parts[] = $emit($map['music_only'],      'music_only',      "''");
            $parts[] = $emit($map['has_covers'],      'has_covers',      "''");
            $parts[] = $emit($map['has_strikes'],     'has_strikes',     "''");
            $parts[] = $emit($map['strike_reason'],   'strike_reason',   "''");
            $parts[] = $emit($map['watchtime_proof'], 'watchtime_proof', "''");
            $parts[] = $emit($map['screenshot'],      'screenshot',      "''");

            $sql = "
                SELECT
                  ".implode(",\n                  ", $parts).",
                  u.first_name, u.last_name, u.company_name, u.email
                FROM tbl_mcn_requests r
                LEFT JOIN tbl_staff u ON u.id = r.user_id
                ORDER BY r.id DESC
            ";

            $mcnRequests = iterator_to_array($adapter->query($sql, []));
        }

        if ($forceReload) {
            $stmt = $adapter->createStatement($payoutSql, [$userID]);
            $payoutData = $stmt->execute()->current();
            if (!$isResubmitting) {
                $submitted = !empty($payoutData);
            }
        }

        return new \Zend\View\Model\ViewModel([
            'user' => $user,
            'payoutData' => $payoutData,
            'submitted' => $submitted,
            'allPayouts' => $allPayouts,
            'submissionHistory' => $submissionHistory,
            'isResubmitting' => $isResubmitting,
            'showVerificationForm' => $showVerificationForm,
            'countryCode' => $countryCode,
            'method' => $method,
            'isRejected' => $isRejected,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'searchQuery' => $search,
            'mcnRequests' => $mcnRequests
        ]);
    }

    public function deletebankAction()
    {
        $request = $this->getRequest();

        if ($request->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')) {
            $data = $request->getPost()->toArray();
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

            $table = new \Zend\Db\TableGateway\TableGateway('tbl_user_payout', $adapter);
            $table->delete(['id' => $data['id']]);

            return $this->redirect()->toUrl('/settings/bankinformation?reload=1');
        }

        return $this->redirect()->toUrl('/settings/bankinformation');
    }

    public function verifybankAction()
    {
        $request = $this->getRequest();
        if ($request->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')) {
            $data = $request->getPost()->toArray();

            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new \Zend\Db\TableGateway\TableGateway('tbl_user_payout', $adapter);
            $staffTable = new \Zend\Db\TableGateway\TableGateway('tbl_staff', $adapter);

            if (empty($data['id']) || empty($data['user_id'])) {
                return $this->redirect()->toUrl('/settings/bankinformation');
            }

            $updateData = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $status = isset($data['status']) ? strtolower(trim($data['status'])) : null;

            if ($status === 'pending_verification') {
                $updateData['status'] = 'pending_verification';
                $updateData['test_amount'] = (isset($data['test_amount']) && is_numeric($data['test_amount']))
                    ? (float)$data['test_amount']
                    : null;
                $updateData['is_verified'] = 0;
                $updateData['rejection_reason'] = null;
            } elseif ($status === 'rejected') {
                $updateData['status'] = 'rejected';
                $updateData['rejection_reason'] = !empty($data['rejection_reason']) ? $data['rejection_reason'] : null;
                $updateData['test_amount'] = null;
                $updateData['is_verified'] = 0;
            } elseif ($status === 'active') {
                $updateData['status'] = 'active';
                $updateData['rejection_reason'] = null;
                $updateData['test_amount'] = null;
                $updateData['is_verified'] = 1;
            }

            try {
                $table->update($updateData, ['id' => (int)$data['id']]);

                $kycStatus = ($status === 'active') ? 1 : 0;
                $staffTable->update(['kyc_verified' => $kycStatus], ['id' => (int)$data['user_id']]);

            } catch (\Exception $e) {
                error_log("verifybank ERROR: " . $e->getMessage());
            }
        }

        return $this->redirect()->toUrl('/settings/bankinformation?reload=1');
    }

    public function verifyamountAction()
    {
        $request = $this->getRequest();
        if ($request->isPost() && $_SESSION['user_id'] > 0 && $_SESSION['STAFFUSER'] == '0') {
            $data = $request->getPost()->toArray();
            $userID = $_SESSION['user_id'];

            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new \Zend\Db\TableGateway\TableGateway('tbl_user_payout', $adapter);

            $row = $table->select(['id' => (int)$data['id'], 'user_id' => $userID])->current();

            if ($row && $row['status'] == 'pending_verification') {
                if ((float)$row['test_amount'] == (float)$data['verify_amount']) {
                    $table->update([
                        'is_verified' => 1,
                        'status' => 'active',
                        'updated_at' => date('Y-m-d H:i:s')
                    ], ['id' => $row['id']]);
                    $_SESSION['VERIFY_SUCCESS'] = "Verification successful!";
                } else {
                    $_SESSION['VERIFY_ERROR'] = "Amount did not match. Please try again.";
                }
            }

            return $this->redirect()->toUrl('/settings/bankinformation');
        }

        return $this->redirect()->toUrl('/settings/bankinformation');
    }

    public function submitbankAction()
    {
        $request = $this->getRequest();
        if ($request->isPost() && $_SESSION['user_id'] > 0 && $_SESSION['STAFFUSER'] == '0') {
            $data = $request->getPost()->toArray();
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

            $insert = [
                'user_id' => $_SESSION['user_id'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $method = strtolower(trim($data['method']));

            if ($method === 'payoneer') {
                $insert['email'] = $data['email'];
                $insert['account_id'] = $data['account_id'];
                $insert['account_name'] = $data['account_name'];
            } elseif ($method === 'paypal') {
                $insert['email'] = $data['email'];
                $insert['account_name'] = $data['account_name'];
            } elseif ($method === 'bank') {
                $insert['bank_name'] = $data['bank_name'];
                $insert['account_number'] = $data['account_number'];
                $insert['ifsc'] = $data['ifsc'];
                $insert['holder_name'] = $data['holder_name'];
                $insert['country'] = $data['country'];
                $insert['state'] = $data['state'];
                $insert['pincode'] = $data['pincode'];
                $insert['mobile'] = $data['mobile'];
            }

            $table = new \Zend\Db\TableGateway\TableGateway('tbl_user_payout', $adapter);
            $table->insert($insert);

            return $this->redirect()->toUrl('/settings/bankinformation?success=1');

        }

        return $this->redirect()->toUrl('/settings/bankinformation');
    }

  
 private function isAdmin(): bool {
    $uid    = (int)($_SESSION['user_id'] ?? 0);
    $staff  = (int)($_SESSION['STAFFUSER'] ?? 0);
    $perm   = strtolower((string)($_SESSION['permission_type'] ?? ''));
    $role   = strtolower((string)($_SESSION['role'] ?? ''));

    // ✅ Any of these = admin
    return ($uid === 0)
        || ($staff === 1)
        || ($perm === 'admin')
        || ($role === 'admin')
        || ($role === 'superadmin');
}




private function currentUserId(): int
{
    // अपने प्रोजेक्ट के हिसाब से जो-जो keys आती हैं, सभी ट्राइ करें:
    foreach (['STAFFID','STAFFUSERID','user_id','USERID'] as $k) {
        if (!empty($_SESSION[$k])) return (int)$_SESSION[$k];
    }
    if (!empty($_SESSION['staff']['id'])) return (int)$_SESSION['staff']['id'];
    return 0;
}


    /* =========================
     * Agreements (NEW: Live DB)
     * ========================= */

    // User view (agreements.phtml)
  public function agreementsAction()
{
    // साधारण यूज़र view
    return new \Zend\View\Model\ViewModel(); // renders agreements.phtml
}



    // Admin view (Agreementsadmin.phtml)
  public function agreementsAdminAction()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId     = (int)($_SESSION['user_id'] ?? 0);
    $staffUser  = (int)($_SESSION['STAFFUSER'] ?? 0);
    $userAccess = $_SESSION['USER_ACCESS'] ?? [];

    // ✅ Allow Super Admin only (user_id = 0)
    if ($userId !== 0) {
        // Normal user → redirect
        return $this->redirect()->toUrl('/dashboard');
    }

    // ✅ If super admin but no permission list (optional fallback)
    if (!is_array($userAccess)) {
        $userAccess = [];
    }

    // ✅ If admin user (user_id=0) — allow access
    return new \Zend\View\Model\ViewModel(); // renders agreementsadmin.phtml
}




    // GET /settings/agreements/list
    public function agreementsListAction()
{
    $request = $this->getRequest();
    if (!$request->isGet()) {
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'GET only']);
    }

    $sl      = $this->getServiceLocator();
    /** @var \Zend\Db\Adapter\Adapter $adapter */
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $table   = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $adapter);

    $q      = trim((string)$this->params()->fromQuery('q',''));
    $deal   = trim((string)$this->params()->fromQuery('deal',''));
    $status = trim((string)$this->params()->fromQuery('status',''));
    $preset = trim((string)$this->params()->fromQuery('preset','all'));
    $page   = max(1, (int)$this->params()->fromQuery('page',1));
    $size   = max(1, min(200, (int)$this->params()->fromQuery('size',20)));

    $isAdmin = $this->isAdmin();
    $uid     = $this->currentUserId();

    $select = $table->getSql()->select()->order('id DESC');

    // 🔒 non-admin = अपनी ही entries
    if (!$isAdmin) {
        $select->where(['created_by' => $uid]);
    }

    if ($q !== '') {
        $select->where
            ->nest()
                ->like('label', "%{$q}%")
                ->or->like('rights', "%{$q}%")
                ->or->like('services', "%{$q}%")
            ->unnest();
    }
    if ($deal !== '')  $select->where(['deal' => $deal]);
    if ($status !== '') $select->where(['status' => $status]);

    if ($preset !== '' && $preset !== 'all') {
        $days = (int)$preset;
        if ($days > 0) {
            $select->where->greaterThanOrEqualTo('date', date('Y-m-d', strtotime("-{$days} days")));
        }
    }

    // Count
    $sql  = new \Zend\Db\Sql\Sql($adapter);
    $selC = clone $select;
    $selC->columns(['cnt' => new \Zend\Db\Sql\Expression('COUNT(*)')]);
    $count = (int)$sql->prepareStatementForSqlObject($selC)->execute()->current()['cnt'];

    // Paging
    $offset = ($page - 1) * $size;
    $select->limit($size)->offset($offset);

    $rows = iterator_to_array($table->selectWith($select));

    $data = array_map(function($r){
        $rights   = array_values(array_filter(array_map('trim', explode(',', (string)$r['rights']))));
        $services = array_values(array_filter(array_map('trim', explode(',', (string)$r['services']))));
        return [
            'id'       => (int)$r['id'],
            'label'    => (string)$r['label'],
            'usertype' => (string)$r['usertype'],
            'rights'   => $rights,
            'services' => $services,
            'deal'     => (string)$r['deal'],
            'revshare' => (float)$r['revshare'],
            'document' => (string)($r['document'] ?? ''), // relative path stored
            'status'   => (string)$r['status'],
            'date'     => (string)$r['date'],
            'expiry'   => (string)($r['expiry'] ?? ''),
        ];
    }, $rows);

    return new \Zend\View\Model\JsonModel([
        'ok'    => true,
        'page'  => $page,
        'size'  => $size,
        'total' => $count,
        'rows'  => $data,
    ]);
}



// ADD THIS inside IndexController class
public function agreementsUploadAction()
{
    $request = $this->getRequest();
    if (!$request->isPost() || empty($_FILES['file'])) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'No file']);
    }

    $file = $_FILES['file'];

    // Basic validations
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Upload error']);
    }
    // Limit ~10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'File too large (max 10MB)']);
    }

    // Accept PDF only
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Only PDF allowed']);
    }

    // Optional: server-side MIME check
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['application/pdf', 'application/x-pdf'];
    if (!in_array($mime, $allowed, true)) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Invalid file type']);
    }

    // Paths
    $safeBase = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $filename = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . '_' . $safeBase . '.pdf';

    $fsDir   = getcwd() . '/public/uploads/agreements'; // filesystem dir
    $fsPath  = $fsDir . '/' . $filename;
    $urlPath = '/uploads/agreements/' . $filename;      // browser URL to store in DB

    if (!is_dir($fsDir)) {
        @mkdir($fsDir, 0775, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $fsPath)) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Unable to save file']);
    }

    // chmod optional
    @chmod($fsPath, 0644);

    // Return JSON with URL path
    return new \Zend\View\Model\JsonModel([
        'ok' => true,
        'path' => $urlPath,
        'name' => $filename,
    ]);
}

    // POST /settings/agreements/save
    // POST /settings/agreements/save  (also matches /settings/agreementssave via the Segment route)
public function agreementsSaveAction()
{
    $request = $this->getRequest();
    if (!$request->isPost()) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'POST only']);
    }

    // Detect admin/staff
    $isAdmin = (($_SESSION['user_id'] ?? null) == 0) || (($_SESSION['STAFFUSER'] ?? '0') === '1');

    // Incoming fields
    $id        = (int) $this->params()->fromPost('id', 0);
    $label     = trim((string) $this->params()->fromPost('label', ''));
    $usertype  = trim((string) $this->params()->fromPost('usertype', 'Label'));
    $rights    = trim((string) $this->params()->fromPost('rights', ''));
    $services  = trim((string) $this->params()->fromPost('services', ''));
    $deal      = trim((string) $this->params()->fromPost('deal', 'Exclusive'));
    $revshare  = (float) $this->params()->fromPost('revshare', 0);
    $document  = trim((string) $this->params()->fromPost('document', ''));
    $date      = trim((string) $this->params()->fromPost('date', date('Y-m-d')));
    $expiry    = trim((string) $this->params()->fromPost('expiry', ''));
    // NOTE: we read it but we won't trust it for normal users
    $statusIn  = trim((string) $this->params()->fromPost('status', ''));

    if ($label === '') {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Label is required']);
    }

    // 🔒 Enforce status on the server:
    // - Users: always pending_review
    // - Admins: use provided status (if any) else active
    if ($isAdmin) {
        $status = ($statusIn !== '' ? $statusIn : 'active');
    } else {
        $status = 'pending_review';
    }

    $sl      = $this->getServiceLocator();
    /** @var \Zend\Db\Adapter\Adapter $adapter */
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $table   = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $adapter);

    $payload = [
        'label'     => $label,
        'usertype'  => $usertype,
        'rights'    => $rights,
        'services'  => $services,
        'deal'      => $deal,
        'revshare'  => $revshare,
        'document'  => $document,
        'date'      => $date,
        'expiry'    => ($expiry !== '' ? $expiry : null),
        'status'    => $status,               // ✅ always set
        'updated_at'=> date('Y-m-d H:i:s'),
    ];

    if ($id > 0) {
        // Strict scope guard: only admin or owner can update
        if (!$isAdmin) {
            $ownerRow = $table->select(['id' => $id])->current();
            if (!$ownerRow || (int)$ownerRow['created_by'] !== (int)($_SESSION['user_id'] ?? 0)) {
                return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Permission denied']);
            }
        }
        $payload['updated_at'] = date('Y-m-d H:i:s');
        $table->update($payload, ['id' => $id]);
    } else {
        $payload['created_by'] = (int) ($_SESSION['user_id'] ?? 0);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $table->insert($payload);
        $id = (int) $table->getLastInsertValue();
    }

    return new \Zend\View\Model\JsonModel([
        'ok'     => true,
        'id'     => $id,
        'status' => $status, // handy for debugging on client
    ]);
}


    // POST /settings/agreements/renew
public function agreementsRenewAction()
{
    if (!$this->getRequest()->isPost())
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'POST only']);

    $id     = (int)$this->params()->fromPost('id',0);
    $expiry = trim((string)$this->params()->fromPost('expiry',''));
    if ($id<=0 || $expiry==='') return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Missing id/expiry']);

    $isAdmin = $this->isAdmin();
    $uid     = $this->currentUserId();

    $table = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));

    // Ownership check for non-admins
    if (!$isAdmin) {
        $row = $table->select(['id'=>$id])->current();
        if (!$row || (int)$row['created_by'] !== $uid) {
            return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Permission denied']);
        }
        // ✅ Non-admin: mark for review only; don't change expiry yet
        $table->update(['status'=>'pending_review'], ['id'=>$id]);
        return new \Zend\View\Model\JsonModel(['ok'=>true, 'queued'=>true, 'status'=>'pending_review']);
    }

    // ✅ Admin: apply expiry immediately
    $table->update(['expiry'=>$expiry, 'status'=>'active'], ['id'=>$id]);
    return new \Zend\View\Model\JsonModel(['ok'=>true, 'status'=>'active']);
}


public function agreementsTerminateAction()
{
    if (!$this->getRequest()->isPost())
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'POST only']);

    $id = (int)$this->params()->fromPost('id',0);
    if ($id<=0) return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Missing id']);

    $isAdmin = $this->isAdmin();
    $uid     = $this->currentUserId();

    $table = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    if (!$isAdmin) {
        $row = $table->select(['id'=>$id])->current();
        if (!$row || (int)$row['created_by'] !== $uid)
            return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Permission denied']);
    }
    $table->update(['status'=>'terminated'], ['id'=>$id]);
    return new \Zend\View\Model\JsonModel(['ok'=>true]);
}

public function agreementsDeleteAction()
{
    if (!$this->getRequest()->isPost())
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'POST only']);

    $id = (int)$this->params()->fromPost('id',0);
    if ($id<=0) return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Missing id']);

    $isAdmin = $this->isAdmin();
    $uid     = $this->currentUserId();

    $table = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    if (!$isAdmin) {
        $row = $table->select(['id'=>$id])->current();
        if (!$row || (int)$row['created_by'] !== $uid)
            return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Permission denied']);
    }
    $table->delete(['id'=>$id]);
    return new \Zend\View\Model\JsonModel(['ok'=>true]);
}
public function agreementsStatusAction()
{
    if (!$this->getRequest()->isPost())
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'POST only']);

    if (!$this->isAdmin())
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Admin only']);

    $id     = (int)$this->params()->fromPost('id',0);
    $status = trim((string)$this->params()->fromPost('status',''));
    if ($id<=0 || $status==='') return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Missing id/status']);

    $allowed = ['active','rejected','pending_verification','pending_review','terminated'];
    if (!in_array($status,$allowed,true))
        return new \Zend\View\Model\JsonModel(['ok'=>false,'error'=>'Invalid status']);

    $table = new \Zend\Db\TableGateway\TableGateway('tbl_agreements', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    $table->update(['status'=>$status], ['id'=>$id]);

    return new \Zend\View\Model\JsonModel(['ok'=>true]);
}
  // REPLACE your adminUsersListAction() with this:
public function adminUsersListAction()
{
    if (!$this->isAdmin()) {
        return new \Zend\View\Model\JsonModel(['ok' => false, 'error' => 'Admin only']);
    }

    /** @var \Zend\Db\Adapter\Adapter $adapter */
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // incoming filters (UI se aate hain)
    $q        = trim((string)$this->params()->fromQuery('q',''));
    $role     = trim((string)$this->params()->fromQuery('role',''));   // ← ab isse user_type treat karenge
    // NOTE: status filter hataya gaya (aapne user list se status nikal diya hai)

    // dynamic WHERE + binds
    $where = "1=1";
    $bind  = [];

    if ($q !== '') {
        $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.company_name LIKE ? OR s.email LIKE ? OR s.isoCountry LIKE ?)";
        $k = '%'.$q.'%';
        array_push($bind, $k,$k,$k,$k,$k);
    }
    if ($role !== '') {
        // role ko user_type ke roop me treat karna
        $where .= " AND LOWER(COALESCE(s.user_type,'')) = LOWER(?)";
        $bind[] = $role;
    }

    // ✅ only users who have agreements + new columns (company_name, user_type, country) + NO status
   $sql = "
    SELECT
      s.id,
      CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) AS name,
      s.company_name,
      s.email,
      s.user_type,
      s.isoCountry AS country,
      s.created_on AS joined,
      COUNT(a.id) AS agreements_count,
      SUM(
        CASE
          WHEN LOWER(a.status) IN ('pending_review','pending review','needs_moderation','needs moderation')
            THEN 1 ELSE 0
        END
      ) AS pending_count
    FROM tbl_staff s
    INNER JOIN tbl_agreements a ON a.created_by = s.id
    WHERE $where
    GROUP BY s.id
    ORDER BY s.id DESC
    LIMIT 1000
";

    $stmt = $adapter->createStatement($sql);
    $result = $stmt->execute($bind);
    $rows = iterator_to_array($result);

    // JSON payload — status/role/account hata diya; naya schema bhej rahe
    $users = array_map(function($r){
    return [
        'id'               => (int)$r['id'],
        'name'             => trim((string)$r['name']),
        'company_name'     => (string)($r['company_name'] ?? ''),
        'email'            => (string)($r['email'] ?? ''),
        'user_type'        => (string)($r['user_type'] ?? 'User'),
        'country'          => (string)($r['country'] ?? ''),
        'joined'           => ($r['joined'] ?: date('Y-m-d')),
        'agreements_count' => (int)$r['agreements_count'],
        'pending_count'    => (int)$r['pending_count'],   // <-- NEW
    ];
}, $rows);

    return new \Zend\View\Model\JsonModel([
        'ok'    => true,
        'total' => count($users),
        'users' => $users
    ]);
}



// ✅ ADMIN AGREEMENTS LIST: show all user entries
public function adminAgreementsListAction()
{
    if (!$this->isAdmin()) return new JsonModel(['ok'=>false,'error'=>'Admin only']);

    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $userId = (int)$this->params()->fromQuery('user_id', 0);
    $where  = $userId > 0 ? "WHERE a.created_by = ?" : "";
    $bind   = $userId > 0 ? [$userId] : [];

    $sql = "
      SELECT a.id,a.label,a.usertype,a.rights,a.services,a.deal,a.revshare,
             a.document,a.status,a.date,a.expiry,
             s.id AS user_id, CONCAT(s.first_name,' ',s.last_name) AS user_name,
             s.company_name, s.email
      FROM tbl_agreements a
      LEFT JOIN tbl_staff s ON s.id = a.created_by
      $where
      ORDER BY a.id DESC
    ";
    $rows = iterator_to_array($adapter->createStatement($sql)->execute($bind));

    $out  = array_map(function($r){
        return [
          'id'       => (int)$r['id'],
          'label'    => $r['label'],
          'usertype' => $r['usertype'],
          'rights'   => $r['rights']   ? array_map('trim', explode(',', $r['rights']))   : [],
          'services' => $r['services'] ? array_map('trim', explode(',', $r['services'])) : [],
          'deal'     => $r['deal'],
          'revshare' => (float)$r['revshare'],
          'document' => (string)$r['document'],
          'status'   => ucfirst($r['status']),
          'date'     => $r['date'],
          'expiry'   => $r['expiry'],
          'user_name'=> $r['user_name'],
          'company'  => $r['company_name'],
          'email'    => $r['email'],
        ];
    }, $rows);

    return new JsonModel(['ok'=>true,'page'=>1,'size'=>count($out),'total'=>count($out),'rows'=>$out]);
}
      private function sendAgreementRejectionEmail(string $toEmail, string $agreementTitle, string $reason, int $agreementId): bool
    {
        if ($toEmail === '') return false;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'support@primedigitalarena.in'; // move to env later
            $mail->Password   = 'Razvi@78692786';               // move to env later
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
            $mail->addAddress($toEmail);
            $mail->isHTML(true);

            // optional headers
            $mail->MessageDate = date('r');
            $mail->MessageID   = "<agreement-reject-" . uniqid() . "@primebackstage.in>";

            $mail->Subject = "Agreement Rejected – {$agreementTitle}";

            $reasonHtml = $reason !== '' ? nl2br(htmlspecialchars($reason)) : 'No reason provided.';
            $viewUrl    = "https://www.primebackstage.in/settings?tab=agreements#id-{$agreementId}";

            $mail->Body = "
<div lang='en-us' style='width:100%!important;margin:0;padding:0'>

  <div style='padding:20px 24px;font-family:\"Inter\", \"Lucida Grande\", Verdana, Arial, sans-serif;font-size:14px;color:#444;line-height:1.7;'>

    <p style='margin-bottom:20px;'>
      <img width='120' src='https://primebackstage.in/public/img/maillogo.png' alt='Prime Backstage' style='display:inline!important;vertical-align:middle;margin-bottom:10px' />
    </p>

    <p style='margin:0 0 14px;'>Hi,</p>

    <p style='margin:0 0 14px;'>
      Your agreement titled:<br>
      <strong>".htmlspecialchars($agreementTitle)."</strong><br>
      has been <strong style='color:#b91c1c'>rejected</strong>.
    </p>

    <p style='margin:20px 0 8px 0;'>Reason provided by our team:</p>

    <blockquote style='margin:0 0 20px 0;padding:15px 20px;background:#f7f7f7;border-left:4px solid #e11d48;border-radius:4px;'>
      {$reasonHtml}
    </blockquote>

    <p style='margin:0 0 12px;'>Please review the reason, update your document or details, and resubmit.</p>

    <p style='margin:0 0 18px;'>
      <a href='{$viewUrl}' target='_blank' style='display:inline-block;padding:10px 14px;background:#111827;color:#fff;text-decoration:none;border-radius:8px;font-weight:600'>
        Open Agreement
      </a>
    </p>

    <p>For general guidance, visit our 
      <a href='https://www.primebackstage.in/faq' style='color:#1a73e8;text-decoration:none;' target='_blank'>Help Center</a>.
    </p>

    <p style='margin-top:30px;'>Regards,<br><strong>Prime Digital Arena Team</strong></p>
  </div>

  <div style='padding:10px 24px;font-family:\"Lucida Grande\",Verdana,Arial,sans-serif;font-size:12px;color:#aaa;margin:10px 0 14px 0;padding-top:10px;border-top:1px solid #eee;'>
    This email is a service from <strong>Prime Backstage</strong>. Delivered by 
    <a href='https://www.primedigitalarena.com' style='color:#444;text-decoration:none;' target='_blank'>Prime Digital Arena</a>
  </div>

  <span style='color:#ffffff' aria-hidden='true'>[PDA-AGREEMENT-REJECT]</span>
</div>";

            $mail->send();
            return true;

        } catch (\Throwable $e) {
            // TODO: log $e->getMessage()
            return false;
        }
    }

    /* =========================
     * Settings (existing)
     * ========================= */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $config = $this->getServiceLocator()->get('config');

        $projectTable = new TableGateway('tbl_settings', $adapter);

        $rowset = $projectTable->select();
        $rowset = $rowset->toArray();

        $viewModel= new ViewModel([
            'Settings' => $rowset[0]
        ]);
        return $viewModel;
    }

    public function saveProfileAction()
    {
        $request = $this->getRequest();

        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $projectTable = new TableGateway('tbl_staff', $dbAdapter);

        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
        unset($aData['MASTER_KEY_ID']);
        unset($aData['logo_file']);
        unset($aData['favicon_file']);
        unset($aData['account']);

        $status=$projectTable->update($aData,array("id='".$_SESSION['user_id']."' "));

        $result['DBStatus'] = 'OK';
        $result = json_encode($result);
        echo $result;
        exit;
    }

    public function updateMyaccountAction()
    {
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $serviceLocator = $this->getServiceLocator();
            $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_myaccount', $dbAdapter);

            $row_set = $projectTable->select(array('id' => 1));
            $row_set = $row_set->toArray();

            if(!(count($row_set) > 0))
            {
                $data1 = array();
                $data1['user_id']=$_SESSION['UID'];
                $status=$projectTable->insert($data1);
            }
            $name = $request->getPost("name");
            $value = $request->getPost("value");

            $data[$name]=$value;

            $status=$projectTable->update($data,array('id' => 1));
            if($status)
                $result['DBStatus'] = 'OK';
            else
                $result['DBStatus'] = 'ERR';
            $result = json_encode($result);
            echo $result;
        }
        exit;
    }

    public function checkOldPasswordAction()
    {
        $request = $this->getRequest();
        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $projectTable = new TableGateway('tbl_staff', $dbAdapter);

        if($_SESSION['ROLE_NAME'] == 'Parents')
            $projectTable = new TableGateway('tbl_parents', $dbAdapter);
        $en_key = "#&$sdfdfs789fs9w";
        $row_set = $projectTable->select(array('id' => $_SESSION['user_id']));
        $row_set = $row_set->toArray();

        if ($request->isPost()) {
            $tableName=$request->getPost('tableName');
            $ID=$request->getPost('KEY_ID');
            $fieldName=$request->getPost('fieldName');

            if((count($row_set) > 0))
            {
                $decoded = openssl_decrypt($row_set[0]['password'], "AES-128-ECB", $en_key);

                if($decoded == $ID)
                {
                    $result1['DBStatus'] = 'OK';
                }
                else
                {
                    $result1['recordsTotal'] = count($row_set);
                    $result1['DBStatus'] = 'ERR';
                }
            }
            else
            {
                $result1['recordsTotal'] = count($row_set);
                $result1['DBStatus'] = 'ERR';
            }

            $result1 = json_encode($result1);
            echo $result1;
        }
        exit;
    }

    public function uploadprofileAction()
    {
        $request = $this->getRequest();
        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $projectTable = new TableGateway('tbl_staff', $dbAdapter);

        if($_SESSION['ROLE_NAME'] == 'Parents')
            $projectTable = new TableGateway('tbl_parents', $dbAdapter);
        if ($request->isPost()) {
            $file = $_FILES['fle_file'];
            $filename = $_FILES['fle_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if(strtoupper($ext) != 'DOC' && strtoupper($ext) != 'DOCX' && strtoupper($ext) != 'PDF' && strtoupper($ext) != 'JPG' && strtoupper($ext) != 'PNG' && strtoupper($ext) != 'JPEG')
            {
                $result['status'] = 'NO_OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }

            $filename=date('YmdHis');
            $myImagePath =  $filename.".".$ext;

            if (!move_uploaded_file($file['tmp_name'], "public/uploads/".$_SESSION['GLOBAL_SCHOOL_CODE']."/".$myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $result['status'] = 'OK';
                $result['message1'] = 'uploaded';
            }
            $data['photo_file']=$myImagePath;
            $status=$projectTable->update($data,array('id' => $_SESSION['user_id']));
            if($status)
                $result['DBStatus'] = 'OK';
            else
                $result['DBStatus'] = 'ERR';
        }
        $result = json_encode($result);
        echo $result;
        exit;
    }

    public function resize($in_file, $out_file, $new_width, $new_height=FALSE)
    {
        $image = null;
        $extension = strtolower(preg_replace('/^.*\./', '', $in_file));
        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($in_file);
                break;
            case 'png':
                $image = imagecreatefrompng($in_file);
                break;
            case 'gif':
                $image = imagecreatefromgif($in_file);
                break;
        }
        if(!$image || !is_resource($image)) return false;
        $width = imagesx($image);
        $height = imagesy($image);
        if($new_height === FALSE)
        {
            $new_height = (int)(($height * $new_width) / $width);
        }
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $ret = imagejpeg($new_image, $out_file, 80);
        imagedestroy($new_image);
        imagedestroy($image);
        return $ret;
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $en_key = "#&$sdfdfs789fs9w";
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_settings', $adapter);

            $aData = json_decode($request->getPost("FORM_DATA"));
            $aData = (array)$aData;

            unset($aData['MASTER_KEY_ID']);

            $projectTable->update($aData);
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

    public function changePasswordAction()
    {
        $request = $this->getRequest();
        $en_key = "#&$sdfdfs789fs9w";
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);

            $encoded = openssl_encrypt($_POST['password'], "AES-128-ECB", $en_key);
            $new_Data['password'] = $encoded;

            $projectTable->update($new_Data,array("id='".$_SESSION['user_id']."'"));

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

} // End Class
