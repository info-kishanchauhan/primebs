<?php
namespace Support\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\TableGateway\TableGateway;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('/home/primebackstage/htdocs/www.primebackstage.in/public/phpmailernew/PHPMailer.php');
require_once('/home/primebackstage/htdocs/www.primebackstage.in/public/phpmailernew/SMTP.php');
require_once('/home/primebackstage/htdocs/www.primebackstage.in/public/phpmailernew/Exception.php');

class EmailController extends AbstractActionController
{
    public function sendRightsNotificationDirect($id)
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $row = $adapter->query("
            SELECT r.*, s.email AS user_email
            FROM tbl_rights_requests r
            LEFT JOIN tbl_staff s ON r.user_id = s.id
            WHERE r.id = ?
            LIMIT 1
        ", [$id])->current();

        if (!$row) return;

        try {
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
            $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

            $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
            $mail->addAddress($row['user_email']);
            $mail->isHTML(true);

            $mail->Subject = 'We’ve received your Rights Request – ' . htmlspecialchars($row['release_title']);

            $mail->Body = "
            <div style='background: #f3f4f6; padding: 30px; font-family: Inter, sans-serif; color: #111827;'>
              <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>
                <div style='background: #6366f1; padding: 20px 30px;'>
                  <h2 style='color: #ffffff; margin: 0; font-size: 22px;'>New Rights Manager Request</h2>
                </div>
                <div style='padding: 30px; font-size: 14px; line-height: 1.6;'>
                  <p><strong>Platform:</strong> {$row['platform']}</p>
                  <p><strong>Request Type:</strong> {$row['request_type']}</p>
                  <p><strong>Release Title:</strong> {$row['release_title']}</p>
                  <p><strong>Artist:</strong> {$row['release_artist']}</p>
                  <p><strong>UPC:</strong> {$row['release_upc']}</p>
                  <p><strong>YouTube Links:</strong><br><span style='color: #1d4ed8;'>" . nl2br($row['youtube_links']) . "</span></p>
                  <div style='margin-top: 30px;'>
                    <a href='https://www.primebackstage.in' target='_blank' style='display: inline-block; background: #6366f1; color: #ffffff; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px;'>View All Requests</a>
                  </div>
                </div>
                <div style='background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280;'>
                  This notification was sent by <strong>Prime Digital Arena</strong> | <a href='https://www.primebackstage.in' style='color: #6b7280; text-decoration: underline;'>Visit Help Center</a>
                </div>
              </div>
            </div>";

            if ($mail->send()) {
                $adapter->query("UPDATE tbl_rights_requests SET mail_sent = 1 WHERE id = ?", [$id]);
            }
        } catch (\Exception $e) {
            error_log("❌ User Email Error: " . $e->getMessage());
        }
    }

    public function sendAdminRightsNoticeDirect($id)
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        $row = $adapter->query("SELECT * FROM tbl_rights_requests WHERE id = ? LIMIT 1", [$id])->current();
        if (!$row || $row['added_by_admin'] != 1) return;

        $info = $adapter->query("SELECT Company_name AS label_name, email AS user_email FROM tbl_staff WHERE id = ? LIMIT 1", [$row['user_id']])->current();
        $userEmail = $info['user_email'] ?? 'team@primedigitalarena.in';

        try {
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
            $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

            $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
            $mail->addAddress($userEmail);
            $mail->addCC('legal@primedigitalarena.in');
            $mail->isHTML(true);

            $mail->Subject = 'URGENT – ' . htmlspecialchars($row['release_upc']) . ' / ' . htmlspecialchars($row['release_title']) . ' – RIGHTS ISSUE from ' . htmlspecialchars($row['platform']);

            $mail->Body = '
<p style="font-family: Arial, sans-serif; font-size: 14px;">Dear partner,</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
We are contacting you to inform you about a <strong>Copyright Infringement</strong> claim received from <strong>' . htmlspecialchars($row['platform']) . '</strong> related to your content listed below:
</p>

<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px; margin: 12px 0; text-align: center;">
  <thead style="background-color: #f2f2f2;">
    <tr>
      <th>Territories Asserted</th>
      <th>Album Artist</th>
      <th>Album Name</th>
      <th>Track Artist</th>
      <th>Track Title</th>
      <th>UPC</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>World</td>
      <td>' . htmlspecialchars($row['release_artist']) . '</td>
      <td>' . htmlspecialchars($row['release_title']) . '</td>
      <td>' . htmlspecialchars($row['release_artist']) . '</td>
      <td>' . htmlspecialchars($row['release_title']) . '</td>
      <td>' . htmlspecialchars($row['release_upc']) . '</td>
    </tr>
  </tbody>
</table>

<p style="font-family: Arial, sans-serif; font-size: 14px;"><strong>Claimant & Content Details:</strong></p>

<table cellpadding="6" cellspacing="0" border="0" width="100%" style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; border-collapse: collapse;">
  <tr>
    <td>' . (!empty($row['youtube_links']) ? nl2br(htmlspecialchars($row['youtube_links'])) : 'N/A') . '</td>
  </tr>
</table>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
In application of the DMCA (in the US) and the E-commerce Directive (in Europe) and other local copyright laws:
</p>

<ul style="font-family: Arial, sans-serif; font-size: 14px;">
  <li>If the claim was received by Spotify or Deezer, please note that an immediate preventive takedown of the content on its platform has been performed by the DMS.</li>
  <li>If the claim was received by Apple, please note that a preventive takedown would be performed by the DMS after 5 business days if no response from you to confirm your rights on the listed content.</li>
  <li>If the claim was received by any other store, the content has not been taken down from the platform.</li>
</ul>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Spotify is expecting us to formally confirm it controls the exclusive rights related to the concerned content, in order to determine the next appropriate actions.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Please note that, in any case, Prime has also performed a preventive takedown of the content on all the DSP’s.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;"><strong>Therefore, you must urgently confirm if you still hold the exclusive rights for that content (for each line).</strong></p>

<p style="font-family: Arial, sans-serif; font-size: 14px;"><strong>If you indeed hold the exclusive rights, could you please:</strong></p>

<ol style="font-family: Arial, sans-serif; font-size: 14px;">
  <li>Sign the attached Indemnification letter and send it back by email (through your Label manager or directly to <a href="mailto:support@primedigitalarena.in">support@primedigitalarena.in</a>).</li>
  <li>Reach out to the claimant to obtain the claim retraction. If the claimant is not responding, please escalate with all related emails and documents.</li>
  <li>The DSP will then decide if the content should be reinstated on the platform or not.</li>
</ol>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
If you do not control the rights, please get back to us and we will take down the content from all DSPs.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Your response is expected within <strong>three business days</strong> from this email, to be sent to <a href="mailto:support@primedigitalarena.in">support@primedigitalarena.in</a>.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Without a formal answer from you with the documentation set forth above, the content won’t be reinstated on digital platforms, in order to protect you, PDA, and the digital platforms against any further claim or legal actions.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
After receiving your response, we will follow up with confirmation as soon as the DSP decides.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Your Label Manager remains at your disposal if you have any questions.
</p>

<p style="font-family: Arial, sans-serif; font-size: 14px;">
Many thanks,<br><br>
<strong>Content Infringement Team</strong><br>
Prime Digital Arena
</p>
';

            if ($mail->send()) {
                $adapter->query("UPDATE tbl_rights_requests SET admin_rights_notice_sent = 1 WHERE id = ?", [$id]);
            }
        } catch (\Exception $e) {
            error_log("❌ Admin Email Error: " . $e->getMessage());
        }
    }
}
