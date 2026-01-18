<?php
namespace Tickets\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;  
use Zend\Db\TableGateway\TableGateway;
use Zend\Http\Response;



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once($_SERVER['DOCUMENT_ROOT'] . '/public/phpmailernew/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/public/phpmailernew/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/public/phpmailernew/Exception.php');

class IndexController extends AbstractActionController
{
public function indexAction()
{
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // ---- Session / role ----
    $user_id = $_SESSION['user_id'] ?? 0;
    $isStaff = ($_SESSION['STAFFUSER'] ?? '0') === '1';

    // ---- Inputs ----
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? 10);
    $perPage = in_array($perPage, [10, 15, 20], true) ? $perPage : 10;
    $offset  = ($page - 1) * $perPage;

    $status  = $_GET['status'] ?? '';
    $q       = trim($_GET['q'] ?? '');

    // ---- WHERE + params (main list) ----
    $where  = "t.is_deleted = 0";
    $params = [];

    // Non-admin users only see their own tickets
    if ($user_id != 0 && !$isStaff) {
        $where   .= " AND t.user_id = ?";
        $params[] = $user_id;
    }

    if ($status !== '') {
        $where   .= " AND t.status = ?";
        $params[] = $status;
    }

    // 🔎 Search by id/title/description + staff email/company
    if ($q !== '') {
        $like = '%'.$q.'%';
        if (ctype_digit($q)) {
            $where   .= " AND (t.id = ? OR t.title LIKE ? OR t.description LIKE ? OR s.email LIKE ? OR s.Company_name LIKE ?)";
            $params[] = (int)$q;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        } else {
            $where   .= " AND (t.title LIKE ? OR t.description LIKE ? OR s.email LIKE ? OR s.Company_name LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
    }

    // ---- Count (filtered) ----
    // NOTE: s.* fields WHERE में हैं, इसलिए COUNT में भी JOIN रखें
    $countSql = "
        SELECT COUNT(*) AS total
        FROM tbl_support_tickets t
        LEFT JOIN tbl_staff s ON s.id = t.user_id
        WHERE $where
    ";
    $filteredTotal = (int)$adapter->createStatement($countSql, $params)->execute()->current()['total'];
    $totalPages    = (int)ceil($filteredTotal / $perPage);

    // ---- ORDER BY (Open/In progress top when no filter) ----
    // latest activity = last reply time (if any) else created_at
    $orderBy = ($status === '' || $status === null)
    ? "(CASE 
          WHEN t.status='Open' THEN 1
          WHEN t.status='In progress' THEN 2
          WHEN t.status='Closed' THEN 3
          ELSE 4
        END) ASC, last_activity DESC, t.id DESC"
    : "last_activity DESC, t.id DESC";


    // ---- Fetch paginated rows ----
    $sql = "
        SELECT
            t.*,
            s.Company_name AS user_name,
            s.email        AS user_email,
            EXISTS (
                SELECT 1
                FROM tbl_ticket_feedback_responses r
                WHERE r.ticket_id = t.id
            ) AS has_feedback,
            COALESCE(tr.last_reply_at, t.created_at) AS last_activity
        FROM tbl_support_tickets t
        LEFT JOIN tbl_staff s ON s.id = t.user_id
        LEFT JOIN (
            SELECT ticket_id, MAX(replied_at) AS last_reply_at
            FROM tbl_ticket_replies
            GROUP BY ticket_id
        ) tr ON tr.ticket_id = t.id
        WHERE $where
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset
    ";

    $statement = $adapter->createStatement($sql, $params);
    $result    = $statement->execute();

    $tickets = [];
foreach ($result as $row) {
    $row['history']       = $this->getHistory($adapter, $row['id']);
    $row['replies']       = $this->getReplies($adapter, $row['id']);
    $row['date']          = $row['created_at'] ?? date('Y-m-d');
    $row['has_feedback']  = (int)($row['has_feedback'] ?? 0);
    $row['last_activity'] = $row['last_activity'] ?? $row['created_at'];

    // 👉 last team member jisne status / reply kiya
    $lastAgentNick = '';
    if (!empty($row['history'])) {
        for ($i = count($row['history']) - 1; $i >= 0; $i--) {
            if (!empty($row['history'][$i]['by'])) {
                $lastAgentNick = $row['history'][$i]['by'];
                break;
            }
        }
    }
    $row['last_agent_nick'] = $lastAgentNick;

    $tickets[] = $row;
}

    // ---- Totals (unfiltered overall) ----
    $allWhere   = "is_deleted = 0";
    $allParams  = [];
    if ($user_id != 0 && !$isStaff) {
        $allWhere  .= " AND user_id = ?";
        $allParams[] = $user_id;
    }
    $allCountSql   = "SELECT COUNT(*) AS total FROM tbl_support_tickets WHERE $allWhere";
    $totalTickets  = (int)$adapter->createStatement($allCountSql, $allParams)->execute()->current()['total'];

    // ---- Status counts (unfiltered by q, but scoped to user if needed) ----
    $statusWhere  = "is_deleted = 0";
    $statusParams = [];
    if ($user_id != 0 && !$isStaff) {
        $statusWhere  .= " AND user_id = ?";
        $statusParams[] = $user_id;
    }
    $statusSql  = "SELECT status, COUNT(*) AS count FROM tbl_support_tickets WHERE $statusWhere GROUP BY status";
    $statusStmt = $adapter->createStatement($statusSql, $statusParams);
    $statusRes  = $statusStmt->execute();

    $statusCounts = ['Open' => 0, 'In progress' => 0, 'Closed' => 0];
    foreach ($statusRes as $r) {
        $key = (string)$r['status'];
        if (isset($statusCounts[$key])) {
            $statusCounts[$key] = (int)$r['count'];
        }
    }

    // ---- View ----
    return new ViewModel([
        'TICKETS_LIST'    => $tickets,
        'TOTAL_PAGES'     => $totalPages,
        'CURRENT_PAGE'    => $page,
        'FILTER_STATUS'   => $status,
        'PER_PAGE'        => $perPage,
        'STATUS_COUNTS'   => $statusCounts,
        'TOTAL_TICKETS'   => $totalTickets,
        'FILTERED_COUNT'  => $filteredTotal,
        'QUERY'           => $q,
    ]);
}

public function feedbackviewAction()
{
    // Always JSON
    $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    $this->getEvent()->stopPropagation(true);
    $this->getResponse()->setContent('');

    $adapter  = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $ticketId = (int)$this->params()->fromQuery('ticket_id', 0);

    if ($ticketId <= 0) {
        echo json_encode(['success' => false, 'error' => 'bad_ticket']);
        return $this->getResponse();
    }

    // 1) Try latest response row (yahi modal me dikhana hai)
    $sql = "
        SELECT
            ticket_id,
            user_id,
            user_name,
            user_email,
            ticket_title,
            rating,
            comment,
            contact_ok,
            token,
            submitted_at
        FROM tbl_ticket_feedback_responses
        WHERE ticket_id = ?
        ORDER BY submitted_at DESC
        LIMIT 1
    ";
    $resp = $adapter->createStatement($sql, [$ticketId])->execute()->current();

    if (!$resp) {
        // 2) Fallback: agar abhi sirf token create hua ho (no response yet)
        $sql2 = "
            SELECT
                f.ticket_id,
                f.user_id,
                s.Company_name AS user_name,
                s.email        AS user_email,
                t.title        AS ticket_title,
                NULL AS rating,
                NULL AS comment,
                0    AS contact_ok,
                f.token,
                f.created_at   AS submitted_at
            FROM tbl_ticket_feedback f
            LEFT JOIN tbl_support_tickets t ON t.id = f.ticket_id
            LEFT JOIN tbl_staff s           ON s.id = f.user_id
            WHERE f.ticket_id = ?
            ORDER BY f.created_at DESC
            LIMIT 1
        ";
        $resp = $adapter->createStatement($sql2, [$ticketId])->execute()->current();
        if (!$resp) {
            echo json_encode(['success' => false, 'error' => 'not_found']);
            return $this->getResponse();
        }
    }

    // Normalize payload to what your JS fills in modal
    $data = [
        'ticket_id'    => (int)($resp['ticket_id'] ?? $ticketId),
        'name'         => $resp['user_name']   ?? '',
        'email'        => $resp['user_email']  ?? '',
        'rating'       => isset($resp['rating']) && $resp['rating'] !== null ? (int)$resp['rating'] : null,
        // Modal me tum "Category" label dikha rahe ho — yahan ticket ka title de diya
        'category'     => $resp['ticket_title'] ?? '',
        'message'      => $resp['comment']      ?? '',
        'contact_ok'   => isset($resp['contact_ok']) ? (int)$resp['contact_ok'] : 0,
        'submitted_at' => $resp['submitted_at'] ?? '',
        'token'        => $resp['token']        ?? '',
    ];

    echo json_encode(['success' => true, 'data' => $data]);
    return $this->getResponse();
}

/**
 * Admin "📝 Feedback" button -> sends a fancy feedback email to ticket owner.
 * POST: ticket_id
 */
  
  private function getCurrentAgentInfo($adapter)
{
    $userId  = $_SESSION['user_id'] ?? 0;
    $isStaff = $_SESSION['STAFFUSER'] ?? '0';

    // Default: master admin
    $info = [
        'id'   => 0,
        'nick' => 'Admin',
    ];

    // Team member (staff) login
    if ($isStaff === '1' && $userId > 0) {
        $staffTable = new TableGateway('tbl_staff', $adapter);
        $staffRow   = $staffTable->select(['id' => (int)$userId])->current();

        if ($staffRow) {
            if (!empty($staffRow['nick_name'])) {
                $info['nick'] = $staffRow['nick_name'];           // ✅ DM, TA, etc.
            } elseif (!empty($staffRow['first_name']) || !empty($staffRow['last_name'])) {
                $info['nick'] = trim(($staffRow['first_name'] ?? '') . ' ' . ($staffRow['last_name'] ?? ''));
            } elseif (!empty($staffRow['Company_name'])) {
                $info['nick'] = $staffRow['Company_name'];
            }
        }

        $info['id'] = (int)$userId;
    }

    return $info;
}

public function sendfeedbackAction()
{
    // Only admin/staff can send
    if (!($this->getRequest()->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1'))) {
        return $this->redirect()->toUrl('/tickets');
    }

    $ticketId = (int)$this->params()->fromPost('ticket_id', 0);
    if ($ticketId <= 0) {
        return $this->redirect()->toUrl('/tickets?feedback=bad_ticket');
    }

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // Fetch ticket + user info
    $row = $adapter->createStatement("
        SELECT t.id, t.title, t.user_id, t.message_id,
               s.email AS user_email, s.Company_name AS user_name
        FROM tbl_support_tickets t
        LEFT JOIN tbl_staff s ON s.id = t.user_id
        WHERE t.id = ? AND t.is_deleted = 0
        LIMIT 1
    ", [$ticketId])->execute()->current();

    if (!$row || empty($row['user_email'])) {
        return $this->redirect()->toUrl('/tickets?feedback=no_recipient');
    }

    $userEmail   = $row['user_email'];
    $userName    = $row['user_name'] ?: 'Customer';
    $ticketTitle = $row['title'] ?: '(no title)';

    // Generate one-time token + feedback link
    $token = bin2hex(random_bytes(16));
    $feedbackUrl = $this->buildFeedbackUrl($token, $ticketId, $userName, $userEmail, $ticketTitle);


    // Save token row (create table once using SQL below)
    $fb = new \Zend\Db\TableGateway\TableGateway('tbl_ticket_feedback', $adapter);
    $fb->insert([
        'ticket_id'   => $ticketId,
        'user_id'     => (int)$row['user_id'],
        'token'       => $token,
        'status'      => 'pending',
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    // Send email
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@primedigitalarena.in';
        $mail->Password   = 'Razvi@78692786';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
        $mail->addAddress($userEmail, $userName);
        $mail->isHTML(true);

        // Keep thread headers consistent with your system
        $baseMsgId = $row['message_id'] ?: "<ticket-{$ticketId}@primebackstage.in>";
        $mail->MessageID = "<support".uniqid()."@primebackstage.in>";
        $mail->addCustomHeader('In-Reply-To', $baseMsgId);
        $mail->addCustomHeader('References',  $baseMsgId);

        // Subject
        $mail->Subject = "We’d love your feedback - Ticket #{$ticketId}";

        // HTML body (Payoneer-like layout, PDA branding)
        $mail->Body = $this->renderFeedbackEmail_PayoneerStyle($userName, $ticketId, $ticketTitle, $feedbackUrl);

        $mail->send();

        // Add history entry
        $hist = new \Zend\Db\TableGateway\TableGateway('tbl_ticket_history', $adapter);
        $hist->insert([
            'ticket_id'  => $ticketId,
            'status'     => 'Feedback requested',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->redirect()->toUrl('/tickets?feedback=sent');
    } catch (\Exception $e) {
        error_log("Feedback mail error: ".$e->getMessage());
        return $this->redirect()->toUrl('/tickets?feedback=failed');
    }
}

/** Build the public feedback URL (tokenized) */
private function buildFeedbackUrl(string $token, int $ticketId, string $name, string $email, string $title): string
{
    // Always send to the public HTML page (no login)
    $base = 'https://primedigitalarena.com/feedback.html';

    // Prefill query params for the form
    $q = http_build_query([
        'tid'   => $ticketId,
        'token' => $token,
        'name'  => $name,
        'email' => $email,
        'title' => $title,
    ]);

    return $base . '?' . $q;
}


private function renderFeedbackEmail_PayoneerStyle(string $userName, int $ticketId, string $ticketTitle, string $feedbackUrl): string
{
    $safeName  = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($ticketTitle, ENT_QUOTES, 'UTF-8');
    $safeUrl   = htmlspecialchars($feedbackUrl, ENT_QUOTES, 'UTF-8');

    $ASSETS_BASE = 'https://www.primebackstage.in/public/img';

    // Logos
    $logoLight   = $ASSETS_BASE . '/pda-mail-logo.png';
    $logoDark    = $ASSETS_BASE . '/pda-mail-logo-dark.png';

    // Images
    $heroImg     = $ASSETS_BASE . '/feedback-hero.jpg';
    $thumbImg    = $ASSETS_BASE . '/feedb.jpg';

    // Footer banner (plain) + composite (text baked-in) + believe logo
    $footerBannerPlain   = $ASSETS_BASE . '/footbannern.jpg';
    $footerBannerPartner = $ASSETS_BASE . '/footbanner_partner_believe.jpg';
    $believeLogo         = $ASSETS_BASE . '/Believe.png';

    // Socials
    $fb = $ASSETS_BASE . '/ic-fb.png';
    $ig = $ASSETS_BASE . '/ic-ig.png';
    $x  = $ASSETS_BASE . '/ic-x.jpg';
    $yt = $ASSETS_BASE . '/ic-yt.png';
    $ln = $ASSETS_BASE . '/ic-ln.png';

    $year = date('Y');

    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="x-apple-disable-message-reformatting">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>We'd love your feedback</title>
<style>
  body { margin:0; padding:0; background:#f3f5f9; -webkit-text-size-adjust:100%; }
  img { border:0; outline:none; display:block; }
  a { text-decoration:none; color:#2563eb; }
  .wrap{width:640px;margin:0 auto;}
  .px{padding-left:24px;padding-right:24px;}

  /* Button (bulletproof) */
  .btn{ background:#4f46e5; border-radius:30px; font-weight:700; font-size:15px; color:#ffffff; }
  .btn a{ color:#ffffff !important; display:inline-block; padding:12px 28px; }

  /* Desktop/Mobile toggles */
  .show-desktop { display:table; }
  .show-mobile  { display:none; }

  /* Dark-mode friendly logo swap (for clients that support it) */
  @media (prefers-color-scheme: dark) {
    .logo-light{display:none !important;}
    .logo-dark{display:block !important;}
  }

  /* Mobile */
  @media (max-width:600px){
    .wrap{width:100% !important}
    .px{padding-left:18px !important; padding-right:18px !important}
    .stack td{display:block !important; width:100% !important; text-align:left !important}
    .cta-full{width:100% !important; display:block !important}
    .btn a{display:block !important; text-align:center !important; padding:14px 18px !important}
    .two-col td.right{padding-top:8px !important; text-align:left !important}

    /* Toggle footer variants */
    .show-desktop { display:none !important; }
    .show-mobile  { display:table !important; width:100% !important; }
  }
</style>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;">
  <!-- preheader -->
  <div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#f3f5f9;">
    Tell us how we did on Ticket #$ticketId — it takes less than a minute.
  </div>

  <center style="width:100%;background:#f3f5f9;">
    <!-- top gradient bar -->
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640">
      <tr><td style="height:6px;background:linear-gradient(90deg,#6b44cb,#9f1ac6,#5b46b3);border-radius:0 0 6px 6px;"></td></tr>
    </table>

    <!-- card -->
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640" bgcolor="#ffffff" style="background:#ffffff;margin:18px auto;border-radius:12px;overflow:hidden;box-shadow:0 8px 30px rgba(2,6,23,0.06);">
      <!-- logo (white & dark variants) -->
      <tr><td class="px" bgcolor="#ffffff" style="padding:22px 24px 14px;text-align:center;background:#ffffff;">
        <span class="logo-light" style="display:inline-block;filter:none !important;background:#ffffff;border-radius:8px;padding:6px 8px;">
          <img src="$logoLight" width="140" alt="Prime Digital Arena" style="height:auto;margin:0 auto;">
        </span>
        <span class="logo-dark" style="display:none;filter:none !important;background:#000;border-radius:8px;padding:6px 8px;">
          <img src="$logoDark" width="140" alt="Prime Digital Arena" style="height:auto;margin:0 auto;">
        </span>
      </td></tr>

      <!-- hero -->
      <tr><td><img src="$heroImg" width="640" alt="" style="width:100%;height:auto;"></td></tr>

      <!-- intro -->
      <tr><td class="px" bgcolor="#ffffff" style="padding:22px 24px;color:#0f172a;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
        <div style="display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;color:#374151;background:#eef2ff;font-weight:600;">Hi $safeName,</div>
        <h1 style="font-size:22px;line-height:1.35;margin:12px 0 8px;font-weight:800;color: #414141;">Stay in control of your support experience</h1>
        <p style="font-size:14px;line-height:1.7;margin:0 0 14px;">
          Please tell us <strong>how your issue was resolved</strong> and how satisfied you are with our support.
        </p>
        <table role="presentation" width="100%" bgcolor="#f8fafc" style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
          <tr><td style="padding:14px 16px;font-size:13px;color:#374151; ">
            <strong>Ticket #$ticketId</strong><br>
            <span style="color:#6b7280;">$safeTitle</span>
          </td></tr>
        </table>
      </td></tr>

      <!-- how it works + thumbnail (with fallback link) -->
      <tr><td class="px" bgcolor="#ffffff" style="padding:8px 24px 6px;">
        <p style="font: 13px / 1.5 Inter, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    color: #475569;
    font-family: 'open sans', Arial, Helvetica, sans-serif;
    margin: 0 0 10px;
    text-align: center;
">We'd love to get feedback on the service you're getting with us at Prime Digital Arena. We do review feedback here every week and make changes.
        We would be very grateful if you could take a moment to answer one simple question by clicking either link below.</p>
      </td></tr>
      

      <!-- CTA button -->
      <tr><td class="px" bgcolor="#ffffff" align="center" style="padding:10px 24px 12px;">
        <table role="presentation" cellpadding="0" cellspacing="0" class="cta-full">
          <tr>
            <td class="btn" align="center">
              <a href="$safeUrl" target="_blank">Share Your Feedback</a>

            </td>
          </tr>
        </table>
      </td></tr>

      <!-- thanks | ticket id -->
      <tr>
        <td class="px" bgcolor="#ffffff" style="padding:6px 24px 18px;">
          <table role="presentation" width="100%" class="two-col stack" cellpadding="0" cellspacing="0">
            <tr>
              <td class="left" style="font:13px Inter,Segoe UI,Roboto,Helvetica,Arial;color:#111827;">
                <strong>Thanks</strong><br>The Prime Digital Arena team
              </td>
              <td class="right" style="font:13px Inter,Segoe UI,Roboto,Helvetica,Arial;color:#111827;text-align:right;">
                <span style="color:#6b7280;">Ticket ID:</span> <strong>#$ticketId</strong>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- divider -->
      <tr><td style="height:1px;background:#eef2f7;"></td></tr>

      <!-- ===== FOOTER VARIANT A: REAL OVERLAY (DESKTOP & MOST CLIENTS) ===== -->
      <tr class="show-desktop">
        <td style="padding:0;">
          <table role="presentation" align="center" width="640" cellpadding="0" cellspacing="0" border="0" class="wrap" style="width:640px;margin:0 auto;">
            <tr>
              <td background="$footerBannerPlain" bgcolor="#1d1d1f" width="640" height="180" valign="middle" align="center"
                  style="background:url('$footerBannerPlain') center / cover no-repeat #1d1d1f; width:640px; height:180px; text-align:center;">
                <!--[if gte mso 9]>
                  <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false"
                          style="width:640px;height:180px;">
                    <v:fill type="frame" src="$footerBannerPlain" color="#1d1d1f"/>
                    <v:textbox inset="0,0,0,0">
                <![endif]-->
                
                <!--[if gte mso 9]></v:textbox></v:rect><![endif]-->
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- ===== FOOTER VARIANT B: COMPOSITE IMAGE (MOBILE GMAIL SAFE) ===== -->
      <tr class="show-mobile">
        <td style="padding:0;background:#ffffff;">
          <img src="$footerBannerPartner" width="640" alt="Distribution Partner — believe." style="display:block;width:100%;max-width:640px;height:auto;border:0;">
        </td>
      </tr>

      <!-- Social icons (common) -->
      <tr>
        <td style="padding:12px 16px;text-align:center;background:#ffffff;">
          <table role="presentation" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:0 6px;"><a href="https://www.facebook.com/PrimeDigitalArena/"><img src="$fb" width="24" alt="Facebook" style="display:block;"></a></td>
              <td style="padding:0 6px;"><a href="https://www.instagram.com/pda_india/"><img src="$ig" width="24" alt="Instagram" style="display:block;"></a></td>
              <td style="padding:0 6px;"><a href="https://x.com/PDA_India"><img src="$x"  width="24" alt="X"        style="display:block;"></a></td>
              <td style="padding:0 6px;"><a href="https://www.youtube.com/@PrimeDigitalArena_in"><img src="$yt" width="24" alt="YouTube"  style="display:block;"></a></td>
              <td style="padding:0 6px;"><a href="#"><img src="$ln" width="24" alt="LinkedIn" style="display:block;"></a></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- security + copyright -->
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640" style="width:640px;margin:10px auto 24px;">
      <tr><td style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:11px;color:#6b7280;text-align:center;line-height:1.6;">
        <strong>Security Reminder</strong><br>
        Be cautious of unexpected emails requesting personal details. If unsure, contact support from your dashboard.<br>
        © $year Prime Digital Arena
      </td></tr>
    </table>
  </center>
</body>
</html>
HTML;
}

  
public function mergeAction()
{
    $this->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    $this->getEvent()->stopPropagation(true);
    $this->getResponse()->setContent('');

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $input = json_decode(file_get_contents('php://input'), true);
    $mergeIds = $input['ids'] ?? [];

    if (count($mergeIds) < 2) {
        echo json_encode(['success' => false, 'message' => 'Need at least 2 ticket IDs.']);
        return $this->getResponse();
    }

    sort($mergeIds);
    $primaryId = array_shift($mergeIds);

    // ✅ Get primary ticket info
    $primary = $adapter->query("SELECT * FROM tbl_support_tickets WHERE id = ?", [$primaryId])->current();
    $title = $primary['title'] ?? 'Support Request';
    $userId = $primary['user_id'] ?? 0;

    // ✅ Get user email
    $staff = $adapter->query("SELECT email FROM tbl_staff WHERE id = ?", [$userId])->current();
    $email = $staff['email'] ?? null;

    // ✅ Prepare merged ticket list for mail
    $mergedDetails = [];

    foreach ($mergeIds as $mergedId) {
        if ($mergedId == $primaryId) continue;

        // Mark as deleted
        $adapter->query("UPDATE tbl_support_tickets SET is_deleted = 1 WHERE id = ?", [$mergedId]);

        // Add merge history
        $status = "Merged from Ticket #" . (int)$mergedId;
        $adapter->query(
            "INSERT INTO tbl_ticket_history (ticket_id, status, updated_at) VALUES (?, ?, NOW())",
            [$primaryId, $status]
        );

        // Fetch title for email
        $row = $adapter->query("SELECT title FROM tbl_support_tickets WHERE id = ?", [$mergedId])->current();
        $mergedTitle = $row['title'] ?? '(no title)';
        $mergedDetails[] = "• #$mergedId – " . htmlspecialchars($mergedTitle);
    }

    $mergedHtmlList = implode("<br>", $mergedDetails);
    $reply = "The following tickets were merged into Ticket #$primaryId:<br>$mergedHtmlList<br><br>If you have any follow-up questions, please reply to this ticket.";

    // ✅ Send merge notification email
    if ($email) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@primedigitalarena.in';
        $mail->Password = 'Razvi@78692786';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->MessageDate = date('r');
        $mail->MessageID = "<merge-" . uniqid() . "@primebackstage.in>";
        $mail->Subject = "Your tickets are now merged – continue under Ticket #$primaryId";

        $mail->Body = "
<div lang='en-us' style='width:100%!important;margin:0;padding:0'>
  <div style='padding:20px 24px;font-family:\"Inter\", \"Lucida Grande\", Verdana, Arial, sans-serif;font-size:14px;color:#444444;line-height:1.7;'>
    <p style='margin-bottom:20px;'>
      <img width='95' src='https://primebackstage.in/public/img/maillogo.png' alt='Prime Help Desk Logo' style='display:inline!important;vertical-align:middle;margin-bottom:10px' />
    </p>
    <p style='margin:0 0 14px 0;'>Hi,</p>
    <p style='margin:0 0 14px 0;'>We’ve merged your support tickets under the main ticket:<br><strong>{$title}</strong></p>
    <p style='margin:20px 0 8px 0;'>Merge details:</p>
    <blockquote style='margin: 0 0 20px 0; padding: 15px 20px; background: #f7f7f7; border-left: 4px solid #ccc; border-radius: 4px;'>{$reply}</blockquote>
    <p style='margin:0 0 14px 0;'>Feel free to reply if you have further questions.</p>
    <p>Visit our <a href='https://www.primebackstage.in/faq' style='color:#1a73e8;text-decoration:none;' target='_blank'>Help Center</a> anytime.</p>
    <p style='margin-top:30px;'>Regards,<br><strong>Prime Digital Arena Team</strong></p>
  </div>
  <div style='padding:10px 24px;font-family:\"Lucida Grande\",Verdana,Arial,sans-serif;font-size:12px;color:#aaaaaa;margin:10px 0 14px 0;padding-top:10px;border-top:1px solid #eeeeee;'>
    This email is a service from <strong>Prime Desk</strong>. Delivered by <a href='https://www.primedigitalarena.in' style='color:#444;text-decoration:none;' target='_blank'>Prime Digital Arena</a>
  </div>
  <span style='color:#ffffff' aria-hidden='true'>[PDA-AUTOREPLY-ID]</span>
</div>
";
        $mail->send();
    }

    echo json_encode(['success' => true, 'primary_id' => $primaryId]);
    return $this->getResponse();
}

   public function threadAction()
{
    $ticket_id = (int) $this->params()->fromRoute('id', 0);
    if ($ticket_id <= 0) {
        return $this->redirect()->toUrl('/tickets');
    }

    // ❌ yeh mat karo:
    // $isAdmin = ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1');
    // if ($isAdmin) {
    //     $ticketTable = new \Zend\Db\TableGateway\TableGateway('tbl_support_tickets', $adapter);
    //     $ticketTable->update(['new_reply' => 0], ['id' => $ticket_id]);
    // }

    return new \Zend\View\Model\ViewModel([
        'ticket_id' => $ticket_id,
    ]);
}

  public function markReadAction()
{
    // ✅ Admin-only + POST-only
    $isAdmin = ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1');
    if (!$isAdmin || !$this->getRequest()->isPost()) {
        return $this->getResponse()->setStatusCode(403);
    }

    $ticketId = (int) $this->params()->fromPost('ticket_id', 0);
    if ($ticketId <= 0) {
        return $this->getResponse()->setStatusCode(400);
    }

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $tbl = new \Zend\Db\TableGateway\TableGateway('tbl_support_tickets', $adapter);

    // Only clear if currently NEW
    $tbl->update(['new_reply' => 0], ['id' => $ticketId, 'new_reply' => 1]);

    return $this->getResponse()->setStatusCode(204); // No Content
}

  public function forwardAction()
{
    $request = $this->getRequest();

    // ✅ Only admin/staff allowed + only POST
    $isAdmin = ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1');
    if (!$request->isPost() || !$isAdmin) {
        return $this->redirect()->toUrl('/tickets');
    }

    // ---------- 1. Read POST data ----------
    $data         = $request->getPost()->toArray();
    $ticketId     = $data['ticket_id']           ?? null;
    $recipient    = trim((string)($data['forward_email']    ?? ''));
    $ccRaw        = trim((string)($data['forward_cc']       ?? ''));
    $subject      = trim((string)($data['forward_subject']  ?? ''));
    $message      = trim((string)($data['forward_message']  ?? ''));

    // 🔁 NEW: old attachments that admin CHOSE to include
    // frontend should send:
    // <input type="hidden" name="orig_attachment[]" value="/public/uploads/ticket_attachments/fileA.pdf">
    $allowedExistingFiles = $data['orig_attachment'] ?? [];
    if (!is_array($allowedExistingFiles)) {
        $allowedExistingFiles = ($allowedExistingFiles !== '') ? [$allowedExistingFiles] : [];
    }

    if (!$ticketId || !$recipient || !$subject || !$message) {
        return $this->redirect()->toUrl('/tickets?error=missing_fields');
    }

    // ---------- 2. Mailer setup ----------
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@primedigitalarena.in';
    $mail->Password   = 'Razvi@78692786';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    $mail->setFrom('info@primedigitalarena.in', 'Prime Digital Arena');

    // ---- To ----
    $mail->addAddress($recipient);
    $seen = [ strtolower($recipient) => true ];

    // ---- CC (user can type comma / semicolon / space separated list) ----
    if ($ccRaw !== '') {
        $ccList = array_filter(array_map('trim', preg_split('/[,\;\s]+/', $ccRaw) ?: []));
        foreach ($ccList as $addr) {
            if ($addr === '') continue;
            $key = strtolower($addr);
            if (isset($seen[$key])) continue;
            if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($addr);
                $seen[$key] = true;
            }
        }
    }

    // ---- Always CC internal team (avoid duplicate) ----
    $teamCc = 'team@primedigitalarena.in';
    if (!isset($seen[strtolower($teamCc)])) {
        $mail->addCC($teamCc);
        $seen[strtolower($teamCc)] = true;
    }

    $mail->isHTML(true);

    // ---------- 3. Thread headers ----------
    if (!empty($ticketId)) {
        $threadId = "<ticket-{$ticketId}@primebackstage.in>";
        $mail->MessageID = $threadId;
        $mail->addCustomHeader('In-Reply-To', $threadId);
        $mail->addCustomHeader('References',  $threadId);
    }

    $mail->Subject = $subject;

    // sanitize + keep line breaks
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    $mail->Body = "
    <div lang='en-us' style='width:100%!important;margin:0;padding:0'>
      <div style='padding:10px 20px;line-height:1.6;
                  font-family:\"Inter\",\"Lucida Grande\",Verdana,Arial,sans-serif;
                  font-size:14px;color:#444444;'>

        <p style='margin-bottom:10px;'>Hi,</p>
        <p>We hope you are doing well.</p>

        <p><strong>Details:</strong><br>{$safeMessage}</p>

        <p style='margin:20px 0;'>
          We trust you will be able to process this request at the earliest.
        </p>

        <!-- Signature -->
        <p style='margin:0 0 6px 0;'>Best regards,</p>
        <p style='margin:0;'>Warm regards,<br><strong>Prime Digital Arena Team</strong></p>

      </div>

      <div style='padding:10px 20px;line-height:1.5;
                  font-family:\"Lucida Grande\",Verdana,Arial,sans-serif;
                  font-size:12px;color:#aaaaaa;margin:10px 0 14px 0;
                  padding-top:10px;border-top:1px solid #eeeeee'>
        This email is a service from <strong>Prime Desk</strong>.
        Delivered by 
        <a href='https://www.primedigitalarena.com'
           style='color:#444;text-decoration:none;' target='_blank'>
           Prime Digital Arena
        </a>
      </div>

      <span style='color:#ffffff' aria-hidden='true'>[PDA-AUTOREPLY-ID]</span>
    </div>
    ";

    // ---------- 4. Attachments ----------

    // (A) Brand new attachment chosen in forward modal
    if (!empty($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
        $mail->addAttachment(
            $_FILES['attachment']['tmp_name'],
            $_FILES['attachment']['name']
        );
    }

    // (B) Only the EXISTING ticket attachments that admin approved (orig_attachment[])
    // NOTE: purana code yahan pe DB se hamesha attach कर देता था → ab woh hata diya
    foreach ($allowedExistingFiles as $relativePath) {
        $relativePath = trim((string)$relativePath);
        if ($relativePath === '') {
            continue;
        }

        // convert relative (e.g. "/public/uploads/ticket_attachments/file.pdf")
        // to absolute disk path
        $absPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;

        if (file_exists($absPath)) {
            $mail->addAttachment($absPath, basename($absPath));
        }
    }

    // ---------- 5. Send / redirect ----------
    try {
        $mail->send();
        return $this->redirect()->toUrl('/tickets?forwarded=1');
    } catch (\Exception $e) {
        error_log("Forward mail error: " . $e->getMessage());
        return $this->redirect()->toUrl('/tickets?error=forward_failed');
    }
}


    public function submitAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toUrl('/tickets');
        }

        $data = $request->getPost()->toArray();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $user_id = $_SESSION['user_id'] ?? 0;

        $title = trim($data['title']);
        $priority = trim($data['priority']);
        $description = trim($data['description']);

        // File Upload Handling
$attachmentPath = '';
if (!empty($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/ticket_attachments/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['attachment']['name'];
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$filename = 'ticket_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;

    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
        $attachmentPath = '/public/uploads/ticket_attachments/' . $filename;
    }
}

        if ($title && $priority && $description) {
            // Generate duplicate ticket hash
            $ticketHash = md5($title . $description . $user_id);

            // Check for duplicate
            $checkStmt = $adapter->createStatement("SELECT id FROM tbl_support_tickets WHERE hash = ? AND user_id = ? AND created_at >= NOW() - INTERVAL 5 MINUTE");
            $existing = $checkStmt->execute([$ticketHash, $user_id])->current();

            if ($existing) {
                return $this->redirect()->toUrl('/tickets?duplicate=1');
            }

           // Insert ticket
            $tickets = new TableGateway('tbl_support_tickets', $adapter);
            $tickets->insert([
                'title' => $title,
                'priority' => $priority,
                'description' => $description,
                'attachment' => $attachmentPath,
                'status' => 'Open',
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'is_deleted' => 0,
                'hash' => $ticketHash
            ]);


            $ticketId = $adapter->getDriver()->getLastGeneratedValue();
            $messageId = "<ticket-{$ticketId}@primebackstage.in>";
            $adapter->query("UPDATE tbl_support_tickets SET message_id = ? WHERE id = ?", [ $messageId, $ticketId ]);

            $history = new TableGateway('tbl_ticket_history', $adapter);
            $history->insert([
                'ticket_id' => $ticketId,
                'status' => 'Open',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->sendTicketEmail($title, $description, $priority, $ticketId);
            $this->sendUserConfirmation($user_id, $ticketId, $title);

            return $this->redirect()->toUrl('/tickets?success=1');
        }

        return $this->redirect()->toUrl('/tickets?error=1');
    }


   public function replyAction()
{
    $request = $this->getRequest();

    if ($request->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1' )) {

        $data    = $request->getPost()->toArray();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $ticketId = (int)($data['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            return $this->redirect()->toUrl('/tickets');
        }

        // 👉 Kaun reply kar raha hai? (Admin / Team)
        $sessionUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $isStaff       = $_SESSION['STAFFUSER'] ?? '0';

        // default admin
        $agentId   = 0;
        $agentNick = 'Admin';

        if ($isStaff === '1' && $sessionUserId > 0) {
            // Team member
            $staffTable = new TableGateway('tbl_staff', $adapter);
            $staffRow   = $staffTable->select(['id' => $sessionUserId])->current();

            if ($staffRow) {
                if (!empty($staffRow['nick_name'])) {
                    $agentNick = $staffRow['nick_name'];    // DM, TA, etc.
                } elseif (!empty($staffRow['first_name']) || !empty($staffRow['last_name'])) {
                    $agentNick = trim(($staffRow['first_name'] ?? '') . ' ' . ($staffRow['last_name'] ?? ''));
                } elseif (!empty($staffRow['company_name'])) {
                    $agentNick = $staffRow['company_name'];
                } else {
                    $agentNick = 'Support Team';
                }
            }

            $agentId = $sessionUserId;
        }

        // ✅ Step 1: Insert admin/team reply into tbl_ticket_replies
        // NOTE: user_id ko 0 hi rakh rahe hain takki front-end me "Admin" bubble logic na toote.
        $replyTable = new TableGateway('tbl_ticket_replies', $adapter);
        $replyTable->insert([
            'ticket_id'  => $ticketId,
            'user_id'    => 0, // admin side reply (UI ke liye)
            'reply_text' => $data['reply_text'],
            'replied_at' => date('Y-m-d H:i:s'),
        ]);

        // ✅ Step 2: Update main ticket status to "In progress"
        $ticketTable = new TableGateway('tbl_support_tickets', $adapter);
        $ticketTable->update(
            ['status' => 'In progress'],
            ['id' => $ticketId]
        );

        // ✅ Step 3: Insert into ticket history (yahi se badge ke liye nick_name aayega)
        $historyTable = new TableGateway('tbl_ticket_history', $adapter);
        $historyTable->insert([
            'ticket_id'       => $ticketId,
            'status'          => 'In progress',
            'updated_by'      => $agentId,     // 0 = Admin, >0 = team member ID
            'updated_by_name' => $agentNick,   // e.g. "DM"
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // ✅ Step 4: Upload attachment if provided
        $attachmentPath = '';
        if (!empty($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/ticket_attachments/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = $_FILES['attachment']['name'];
            $ext          = pathinfo($originalName, PATHINFO_EXTENSION);
            $filename     = 'reply_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            $targetPath   = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachmentPath = '/public/uploads/ticket_attachments/' . $filename;
            }
        }

        // ✅ Step 5: Send reply email
        $ticketRow = $ticketTable->select(['id' => $ticketId])->current();
        if ($ticketRow) {
            $this->sendReplyEmail(
                $ticketRow['user_id'],
                $ticketRow['id'],
                $ticketRow['title'],
                $data['reply_text'],
                $attachmentPath
            );
        }

        return $this->redirect()->toUrl('/tickets?reply=1');
    }

    return $this->redirect()->toUrl('/tickets');
}


    public function deleteAction()
{
    $request = $this->getRequest();
    if ($request->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1') ) {
        $data = $request->getPost()->toArray();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $table = new TableGateway('tbl_support_tickets', $adapter);
        $table->update(['is_deleted' => 1], ['id' => $data['ticket_id']]);

        // 👇 preserve pagination and filters
        $page = $data['page'] ?? 1;
        $status = $data['status'] ?? '';
        $perPage = $data['per_page'] ?? '';
        
        $url = "/tickets?deleted=1&page=$page";
        if ($status) $url .= "&status=" . urlencode($status);
        if ($perPage) $url .= "&per_page=" . urlencode($perPage);

        return $this->redirect()->toUrl($url);
    }

    return $this->redirect()->toUrl('/tickets');
}


    public function updatestatusAction()
{
    $request = $this->getRequest();
    if ($request->isPost() && ($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')) {
        $data    = $request->getPost()->toArray();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        // 👉 current agent info
        $agent = $this->getCurrentAgentInfo($adapter);

        $table = new TableGateway('tbl_support_tickets', $adapter);
        $table->update(
            ['status' => $data['status']],
            ['id' => (int)$data['ticket_id']]
        );

        $history = new TableGateway('tbl_ticket_history', $adapter);
        $history->insert([
            'ticket_id'       => (int)$data['ticket_id'],
            'status'          => $data['status'],
            'updated_by'      => $agent['id'],
            'updated_by_name' => $agent['nick'],
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // ✅ preserve filters correctly
        $page         = $data['page'] ?? 1;
        $filterStatus = $data['filter_status'] ?? '';
        $perPage      = $data['per_page'] ?? '';
        $q            = $data['q'] ?? '';

        $url = "/tickets?updated=1&page=$page";
        if ($filterStatus) $url .= "&status=" . urlencode($filterStatus);
        if ($perPage)      $url .= "&per_page=" . urlencode($perPage);
        if ($q !== '')     $url .= "&q=" . urlencode($q);

        return $this->redirect()->toUrl($url);
    }

    return $this->redirect()->toUrl('/tickets');
}



    private function getHistory($adapter, $ticket_id)
{
    $sql = "SELECT status, updated_at AS date, updated_by_name
            FROM tbl_ticket_history
            WHERE ticket_id = ?
            ORDER BY updated_at ASC";

    $statement = $adapter->createStatement($sql, [$ticket_id]);
    $result    = $statement->execute();

    $history = [];
    foreach ($result as $row) {
        $history[] = [
            'status' => $row['status'],
            'date'   => date('d M Y', strtotime($row['date'])),
            'by'     => $row['updated_by_name'] ?? '',   // 👈 yahi nick_name
        ];
    }

    return $history;
}


    private function getReplies($adapter, $ticket_id)
{
    $sql = "SELECT r.id, r.reply_text, r.replied_at, r.user_id
            FROM tbl_ticket_replies r
            WHERE r.ticket_id = ? ORDER BY r.replied_at ASC";
    $statement = $adapter->createStatement($sql, [$ticket_id]);
    $result = $statement->execute();

    $replies = [];
    foreach ($result as $row) {
        // 🔄 Get any attachment for this reply
        $attachSql = "SELECT file_path FROM tbl_ticket_attachments WHERE reply_id = ?";
        $attachments = [];
        $attachResult = $adapter->createStatement($attachSql, [$row['id']])->execute();
        foreach ($attachResult as $a) {
            $attachments[] = $a['file_path'];
        }

        $replies[] = [
            'reply_text' => $row['reply_text'],
            'replied_at' => date('d M Y h:i A', strtotime($row['replied_at'])),
            'sender' => ($row['user_id'] == 0) ? 'Admin' : 'User',
            'attachments' => $attachments
        ];
    }
    return $replies;
}


    private function sendTicketEmail($title, $desc, $priority, $id)
{
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    $ticketRow = $adapter->createStatement("SELECT attachment FROM tbl_support_tickets WHERE id = ?", [$id])->execute()->current();

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'support@primedigitalarena.in';
    $mail->Password = 'Razvi@78692786';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
    $mail->addAddress('Team@primedigitalarena.in');
    $mail->isHTML(true);

    // Threading headers
    $messageId = "<ticket-{$id}@primebackstage.in>";
    $mail->MessageID = $messageId;
    $mail->addCustomHeader('In-Reply-To', $messageId);
    $mail->addCustomHeader('References', $messageId);

    $mail->Subject = "Support Ticket Received for Team: #$id";
    $mail->Body = "
<div style='font-family: Inter, sans-serif; background-color: #f9f9f9; padding: 30px; color: #1f2937;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; padding: 30px; box-shadow: 0 0 15px rgba(0,0,0,0.05);'>
    
    <h2 style='font-size: 20px; font-weight: 600; margin-bottom: 16px;'>New Ticket Submitted</h2>
    <p style='margin-bottom: 6px;'><strong>Title:</strong> $title</p>
    <p style='margin-bottom: 6px;'><strong>Priority:</strong> $priority</p>
    <p style='margin-bottom: 12px;'><strong>Description:</strong><br>$desc</p>
    <p style='margin-bottom: 0;'><strong>Ticket Reference:</strong> #$id</p>

    <div style='margin-top: 25px; font-size: 13px; color: #6b7280;'>
      <p>This message was automatically generated by the Prime Help Desk system. Please review this ticket at your earliest convenience.</p>
    </div>

    <hr style='margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;'>
    <div style='font-size: 12px; color: #9ca3af;'>
      <p>This email is from <strong>Prime Digital Arena</strong>. <a href='#' style='color: #9ca3af;'>Unsubscribe</a></p>
    </div>
  </div>
</div>";

    // ✅ Attach if file exists
    if (!empty($ticketRow['attachment'])) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $ticketRow['attachment'];
        if (file_exists($filePath)) {
            $mail->addAttachment($filePath);
        }
    }

    $mail->send();
}

  
public function sendemailAction()
{
    $request = $this->getRequest();

    if ($request->isPost()) {
        $to       = $request->getPost('email_to');
        $cc       = $request->getPost('email_cc');
        $bcc      = $request->getPost('email_bcc');
        $subject  = $request->getPost('subject');
        $body     = $request->getPost('body');
        $ticketId = (int) $request->getPost('ticket_id', 0);

        if (empty($to) || empty($subject) || empty($body)) {
            return $this->redirect()->toUrl('/tickets?mail=empty')->setStatusCode(400);
        }

  $attachments = [];

if (!empty($_FILES['attachment']) && !empty($_FILES['attachment']['name'][0])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/email_attachments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    foreach ($_FILES['attachment']['name'] as $index => $name) {
        $tmpName = $_FILES['attachment']['tmp_name'][$index];

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext); // safe ext
        $fileHash = is_uploaded_file($tmpName) ? sha1_file($tmpName) : sha1($name); // fallback if not temp file

        $filename = $fileHash . '.' . $safeExt;
        $targetPath = $uploadDir . $filename;

        if (!file_exists($targetPath)) {
            if (is_uploaded_file($tmpName)) {
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $attachments[] = $targetPath;
                }
            }
        } else {
            // Duplicate already exists, include it anyway
            $attachments[] = $targetPath;
        }
    }
}


       $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $from = $request->getPost('email_from');  // what user selected (may be alias)
    $allowedFrom = ['support@primedigitalarena.in', 'info@primedigitalarena.in', 'payout@primedigitalarena.in'];

    if (!in_array($from, $allowedFrom)) {
        return $this->redirect()->toUrl('/tickets?mail=invalid_from')->setStatusCode(400);
    }

    // Map alias to real SMTP account
    $smtpUser = match ($from) {
        'support@primedigitalarena.in' => 'support@primedigitalarena.in',
        'info@primedigitalarena.in'    => 'info@primedigitalarena.in',
        'payout@primedigitalarena.in'  => 'info@primedigitalarena.in', // ✅ alias mapped to real
        default                        => 'support@primedigitalarena.in'
    };

    $smtpPasswords = [
        'support@primedigitalarena.in' => 'Razvi@78692786',
        'info@primedigitalarena.in'    => 'Razvi@78692786'
    ];

    $fromName = match ($from) {
        'support@primedigitalarena.in' => 'Prime Digital Arena',
        'info@primedigitalarena.in'    => 'Prime Digital Arena',
        'payout@primedigitalarena.in'  => 'Prime Digital Arena',
        default                        => 'Prime Digital Arena'
    };

    // Setup SMTP with real mailbox
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser; // ✅ real mailbox, not alias
    $mail->Password   = $smtpPasswords[$smtpUser];
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    // Show alias as sender
    $mail->setFrom($from, $fromName); // ✅ shows alias or actual sender
    $mail->addAddress($to);

    if (!empty($cc))  $mail->addCC($cc);
    if (!empty($bcc)) $mail->addBCC($bcc);

    foreach ($attachments as $file) {
        if (file_exists($file)) {
            $mail->addAttachment($file);
        }
    }



            // Gmail Threading Setup
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $ticketTable = new TableGateway('tbl_support_tickets', $adapter);
            $ticketRow = $ticketTable->select(['id' => $ticketId])->current();

            $baseThreadId = "<ticket-$ticketId@primebackstage.in>";

            if (empty($ticketRow['message_id'])) {
                $mail->MessageID = $baseThreadId;
                $mail->addCustomHeader('In-Reply-To', $baseThreadId);
                $mail->addCustomHeader('References', $baseThreadId);
                $ticketTable->update(['message_id' => $baseThreadId], ['id' => $ticketId]);
            } else {
                $mail->MessageID = "<ticket-$ticketId-" . uniqid() . "@primebackstage.in>";
                $mail->addCustomHeader('In-Reply-To', $ticketRow['message_id']);
                $mail->addCustomHeader('References', $ticketRow['message_id']);
            }

            // Email Body
            $escapedBody = nl2br(htmlspecialchars($body));
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "
<div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #111827; line-height: 1.6;'>
  <div>$escapedBody</div>

  <br><br>
  <p style='margin: 0;'>Thanks and regards,</p>
  <p style='margin: 0;'>The Prime Digital Arena team</p>

  <div style='font-size: 11px; color: #9ca3af; border-top:1px solid #e5e7eb; padding-top:12px; margin-top:20px;'>
    This email is a service from <strong>Prime Desk</strong>. Delivered by 
    <a href='https://www.primedigitalarena.com' style='color:#6b7280; text-decoration:none;'>Prime Digital Arena</a>
  </div>

  <span style='display:none; font-size:0; color:#ffffff;'>[PDA-THREAD-ID:$ticketId]</span>
</div>";

            $mail->send();

            return $this->redirect()->toUrl('/tickets?mail=sent');
        } catch (\Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return $this->redirect()->toUrl('/tickets?mail=failed');
        }
    }

    return $this->redirect()->toUrl('/tickets');
}

   private function sendUserConfirmation($user_id, $ticketId, $title)
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $email = $adapter->createStatement("SELECT email FROM tbl_staff WHERE id = ?", [$user_id])->execute()->current()['email'];

        if ($email) {
     
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'support@primedigitalarena.in';
            $mail->Password = 'Razvi@78692786';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
          $mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

            $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
            $mail->addAddress($email);
            $mail->isHTML(true);

            // Threading headers if ticket ID exists
            if (!empty($ticketId)) {
                $messageId = "<ticket-{$ticketId}@primebackstage.in>";
                $mail->MessageID = $messageId;
                $mail->addCustomHeader('In-Reply-To', $messageId);
                $mail->addCustomHeader('References', $messageId);
            }
            $mail->Subject = "Ticket #$ticketId: $title";
$mail->Body = "
<div lang='en-us' style='width:100%!important;margin:0;padding:0'>

  <div style='padding:10px 20px;line-height:1.6;font-family:\"Inter\", \"Lucida Grande\", Verdana, Arial, sans-serif;font-size:14px;color:#444444;'>

    <p>
      <img width='95' src='https://primebackstage.in/public/img/maillogo.png' alt='Prime Help Desk Logo' style='display:inline!important;vertical-align:middle;margin-bottom:12px' />
    </p>

    <p style='margin-bottom:10px;'>Hi,</p>

    <p>We've received your support request and our team will be reviewing it shortly.</p>

    <p style='margin:16px 0;'>Your ticket reference number is <strong>#$ticketId</strong>.</p>

    <p>You submitted the following topic:</p>
    <blockquote style='margin: 12px 0 20px 0; padding: 12px 16px; background: #f7f7f7; border-left: 4px solid #ccc; font-style: italic; border-radius: 4px;'>
      {$title}
    </blockquote>

    <p>We monitor tickets Monday to Friday and aim to respond as quickly as possible.</p>

    <p>While you wait, feel free to visit our <a href='https://www.primebackstage.in/faq' style='color:#1a73e8;text-decoration:none;' target='_blank'>Help Center</a> for quick answers.</p>

    <p style='margin-top:30px;'>Warm regards,<br><strong>Prime Digital Arena Team</strong></p>

  </div>

  <div style='padding:10px 20px;line-height:1.5;font-family:\"Lucida Grande\",Verdana,Arial,sans-serif;font-size:12px;color:#aaaaaa;margin:10px 0 14px 0;padding-top:10px;border-top:1px solid #eeeeee'>
    This email is a service from <strong>Prime Desk</strong>. Delivered by <a href='https://www.primedigitalarena.com' style='color:#444;text-decoration:none;' target='_blank'>Prime Digital Arena</a>
  </div>

  <span style='color:#ffffff' aria-hidden='true'>[PDA-NEW-TICKET-ID]</span>
</div>
";
            $mail->send();
        }
    }

    private function sendReplyEmail($user_id, $ticketId, $title, $reply, $attachment = null)
{
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    
    // Get recipient email
    $email = $adapter->createStatement("SELECT email FROM tbl_staff WHERE id = ?", [$user_id])->execute()->current()['email'];

    // 🔁 Get original ticket message ID for threading
    $ticketRow = $adapter->createStatement("SELECT message_id FROM tbl_support_tickets WHERE id = ?", [$ticketId])->execute()->current();
    $originalId = $ticketRow['message_id'] ?? "<ticket-{$ticketId}@primebackstage.in>";

    if ($email) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@primedigitalarena.in';
        $mail->Password = 'Razvi@78692786';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
      
      $mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

        $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->MessageDate = date('r'); // RFC 2822 format

        // 🧠 Threading headers setup
        $mail->MessageID = "<reply-" . uniqid() . "@primebackstage.in>"; // Unique ID for this reply
        $mail->addCustomHeader('In-Reply-To', $originalId);
        $mail->addCustomHeader('References', $originalId);
        $mail->Subject = "Re: Ticket #$ticketId: $title";

        // Format reply content
        $reply = nl2br(htmlspecialchars($reply));
            $mail->Body = "
<div lang='en-us' style='width:100%!important;margin:0;padding:0'>

  <div style='padding:20px 24px;font-family:\"Inter\", \"Lucida Grande\", Verdana, Arial, sans-serif;font-size:14px;color:#444444;line-height:1.7;'>

    <p style='margin-bottom:20px;'>
      <img width='95' src='https://primebackstage.in/public/img/maillogo.png' alt='Prime Help Desk Logo' style='display:inline!important;vertical-align:middle;margin-bottom:10px' />
    </p>

    <p style='margin:0 0 14px 0;'>Hi,</p>

    <p style='margin:0 0 14px 0;'>
      We’ve responded to your support request titled:<br>
      <strong>{$title}</strong>
    </p>

    <p style='margin:20px 0 8px 0;'>Our reply is below:</p>

    <blockquote style='margin: 0 0 20px 0; padding: 15px 20px; background: #f7f7f7; border-left: 4px solid #ccc; border-radius: 4px;'>
      {$reply}
    </blockquote>

    <p style='margin:0 0 14px 0;'>We monitor tickets from Monday to Friday and will follow up if further updates are required.</p>

    <p>In the meantime, feel free to explore our <a href='https://www.primebackstage.in/faq' style='color:#1a73e8;text-decoration:none;' target='_blank'>Help Center</a> for answers to common questions.</p>

    <p style='margin-top:30px;'>Regards,<br><strong>Prime Digital Arena Team</strong></p>

  </div>

  <div style='padding:10px 24px;font-family:\"Lucida Grande\",Verdana,Arial,sans-serif;font-size:12px;color:#aaaaaa;margin:10px 0 14px 0;padding-top:10px;border-top:1px solid #eeeeee;'>
    This email is a service from <strong>Prime Desk</strong>. Delivered by <a href='https://www.primedigitalarena.com' style='color:#444;text-decoration:none;' target='_blank'>Prime Digital Arena</a>
  </div>

  <span style='color:#ffffff' aria-hidden='true'>[PDA-AUTOREPLY-ID]</span>
</div>
";
      
       if (!empty($attachment)) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $attachment;
    if (file_exists($filePath)) {
        $mail->addAttachment($filePath);
    }
}

            $mail->send();
        }
    }
}