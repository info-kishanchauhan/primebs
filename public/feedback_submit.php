<?php



// /public/feedback_submit.php  (simple insert version)
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit;
}

$tid     = (int)($_POST['tid'] ?? $_POST['ticket_id'] ?? 0);
$token   = trim($_POST['token'] ?? '');
$name    = trim($_POST['name'] ?? $_POST['user_name'] ?? '');
$email   = trim($_POST['email'] ?? $_POST['user_email'] ?? '');
$title   = trim($_POST['title'] ?? $_POST['ticket_title'] ?? '');
$rating  = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? $_POST['feedback'] ?? '');
$hp      = trim($_POST['website'] ?? ''); // honeypot

if ($hp !== '' || $tid<=0 || $rating<1 || $rating>5 || mb_strlen($comment)<10) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'bad_request']); exit;
}

// --- DB creds (aapke hi) ---
$DB_HOST='localhost';
$DB_NAME='primebs';
$DB_USER='root';
$DB_PASS='1234';

mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if ($db->connect_errno) {
    echo json_encode(['ok'=>false,'error'=>'db_connect']); exit;
}
$db->set_charset('utf8mb4');

// table ensure (simple)
$db->query("CREATE TABLE IF NOT EXISTS tbl_ticket_feedback_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  user_id INT DEFAULT 0,
  user_name VARCHAR(150) DEFAULT '',
  user_email VARCHAR(190) DEFAULT '',
  ticket_title VARCHAR(255) DEFAULT '',
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT,
  token VARCHAR(190) DEFAULT '',
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip VARCHAR(64) NULL,
  ua VARCHAR(255) NULL,
  KEY idx_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

$stmt = $db->prepare("INSERT INTO tbl_ticket_feedback_responses
  (ticket_id,user_id,user_name,user_email,ticket_title,rating,comment,token,submitted_at,ip,ua)
  VALUES (?,?,?,?,?,?,?,?,NOW(),?,?)");
$uid = 0; // abhi token verify nahi kar rahe
$stmt->bind_param('iisssissss', $tid,$uid,$name,$email,$title,$rating,$comment,$token,$ip,$ua);


if (!$stmt->execute()) {
    echo json_encode(['ok'=>false,'error'=>'db_insert']); exit;
}

echo json_encode(['ok'=>true]); // ✅ success
