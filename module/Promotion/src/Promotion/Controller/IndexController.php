<?php
namespace Promotion\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class IndexController extends AbstractActionController
{
    protected $studentTable;
  public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // ✅ Yahi karna tha
        }
    }
public function indexAction()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    session_start();

$isAdmin = isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == 1;
$userId = $isAdmin ? 0 : ($_SESSION['user_id'] ?? 0);

// ✅ Allow if either admin or user is authenticated
if (!$isAdmin && $userId == 0) {
    return $this->redirect()->toUrl('/login');
}



    // ✅ Use dummy userRow for admin
    if ($isAdmin) {
        $userRow = [
            'id' => 0,
            'company_name' => 'Admin',
            'email' => 'admin@domain.com',
            'payment_method' => 'admin'
        ];
    } else {
        $staffTable = new TableGateway('tbl_staff', $adapter);
        $userRow = $staffTable->select(['id' => $userId])->current();
    }

    $settingsTable = new TableGateway('tbl_settings', $adapter);
    $settingsRow = $settingsTable->select()->current();
    $promotionForm = $settingsRow['promotion_form'] ?? '';

    $mcnTable = new TableGateway('tbl_mcn_requests', $adapter);

    // ✅ Admin sees all entries
    if ($isAdmin) {
        $mcnResultSet = $mcnTable->select(); // All entries
    } else {
        $mcnResultSet = $mcnTable->select(['user_id' => $userId]);
    }

    $mcnChannels = [];
    foreach ($mcnResultSet as $row) {
        $mcnChannels[] = $row;
    }

    return new ViewModel([
        'user'          => $userRow,
        'channelCount'  => count($mcnChannels),
        'network'       => 'Believe Music',
        'INFO'          => $promotionForm,
        'mcnChannels'   => $mcnChannels,
    ]);
}


public function attachmcnAction()
{
    return new ViewModel();
}

public function cancelmcnAction()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $request = $this->getRequest();
    $id = (int) ($_GET['id'] ?? 0);
    $userId = $_SESSION['user_id'] ?? 0;

    if ($id <= 0 || $userId <= 0) {
        return $this->redirect()->toUrl('/promotion/index?error=invalid');
    }

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $table = new TableGateway('tbl_mcn_requests', $adapter);

    // Ensure the request belongs to the user and is still pending
    $row = $table->select(['id' => $id, 'user_id' => $userId])->current();
    if (!$row || strtolower($row['status']) !== 'pending') {
        return $this->redirect()->toUrl('/promotion/index?error=notallowed');
    }

    // ✅ Delete the entry
    try {
        $table->delete(['id' => $id]);
        $_SESSION['mcn_cancelled'] = true;
    } catch (\Exception $e) {
        error_log("❌ MCN cancel failed: " . $e->getMessage());
        return $this->redirect()->toUrl('/promotion/index?error=cancel_failed');
    }

    return $this->redirect()->toUrl('/promotion/index?cancel=success');
}  
public function youtubeAction()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = $_SESSION['user_id'] ?? 0;
    $isAdmin = isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == 1;

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $mcnTable = new \Zend\Db\TableGateway\TableGateway('tbl_mcn_requests', $adapter);

    // ✅ Admin gets all MCNs
    if ($isAdmin) {
        $mcnResultSet = $mcnTable->select();
    } else {
        $mcnResultSet = $mcnTable->select(['user_id' => $userId]);
    }

    $mcnChannels = iterator_to_array($mcnResultSet);

    return new ViewModel([
        'mcnChannels' => $mcnChannels
    ]);
}


  
public function submitmcnAction()
{
    $request = $this->getRequest();

    if (!$request->isPost()) {
        return $this->redirect()->toUrl('/promotion/index');
    }

    $postData = $request->getPost()->toArray();
    $fileData = $request->getFiles()->toArray();

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $table = new TableGateway('tbl_mcn_requests', $adapter);

    $userId = $_SESSION['user_id'] ?? 0;
    $now = date('Y-m-d H:i:s');

    // Upload directory
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/mcn/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Watch Time Screenshot Upload
    $watchtimeProof = '';
    if (!empty($fileData['watchtime_proof']['name'])) {
        $ext = pathinfo($fileData['watchtime_proof']['name'], PATHINFO_EXTENSION);
        $watchtimeProof = uniqid('watch_') . '.' . $ext;
        move_uploaded_file($fileData['watchtime_proof']['tmp_name'], $uploadDir . $watchtimeProof);
    }

    // Revenue Screenshot Upload
    $analyticsScreenshot = '';
    if (!empty($fileData['screenshot']['name'])) {
        $ext = pathinfo($fileData['screenshot']['name'], PATHINFO_EXTENSION);
        $analyticsScreenshot = uniqid('revenue_') . '.' . $ext;
        move_uploaded_file($fileData['screenshot']['tmp_name'], $uploadDir . $analyticsScreenshot);
    }

    // Insert into DB
    try {
        $table->insert([
            'user_id'             => $userId,
            'channel_url'         => trim($postData['channel_url'] ?? ''),
            'channel_type'        => $postData['channel_type'] ?? '',
            'relationship'        => $postData['relationship'] ?? '',
            'upload_type'         => $postData['upload_type'] ?? '',
            'upload_pattern'      => $postData['upload_pattern'] ?? '',
            'subscribers'         => (int) ($postData['subscribers'] ?? 0),
            'watchtime_eligible'  => $postData['watchtime_eligible'] ?? '',
            'watchtime_proof'     => $watchtimeProof,
            'monetized'           => $postData['monetized'] ?? '',
            'revenue'             => (float) ($postData['revenue'] ?? 0),
            'screenshot'          => $analyticsScreenshot,
            'music_only'          => $postData['music_only'] ?? '',
            'has_covers'          => $postData['has_covers'] ?? '',
            'has_strikes'         => $postData['has_strikes'] ?? '',
            'strike_reason'       => $postData['strike_reason'] ?? '',
            'status'              => 'pending',
            'created_at'          => $now
        ]);
    } catch (\Exception $e) {
        error_log("❌ MCN insert failed: " . $e->getMessage());
        return $this->redirect()->toUrl('/promotion/index?error=1');
    }

    $_SESSION['mcn_success'] = true;
return $this->redirect()->toUrl('/promotion/index');
}


    public function generateimageAction()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $prompt = $_POST['prompt'] ?? '';
        if (!$prompt) {
            echo json_encode(['error' => 'Prompt missing']);
            return;
        }

        $apiKey = 'sk-proj-...'; // Replace with your real API key

        $postData = [
            'prompt' => $prompt,
            'n' => 1,
            'size' => '512x512'
        ];

        $jsonData = json_encode($postData);

        $logPath = $_SERVER['DOCUMENT_ROOT'] . '/logs/ai_debug.log';
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - PROMPT: $prompt\n", FILE_APPEND);

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) {
            file_put_contents($logPath, "❌ CURL ERROR: $curlError\n", FILE_APPEND);
            echo json_encode(['error' => $curlError]);
            return;
        }

        file_put_contents($logPath, "✅ HTTP $httpCode\n$result\n\n", FILE_APPEND);

        header('Content-Type: application/json');
        echo $result;
    }

    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }

    public function fatal_error($sErrorMessage = '')
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        die($sErrorMessage);
    }
}
