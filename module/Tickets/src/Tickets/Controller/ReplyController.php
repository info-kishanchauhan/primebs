<?php
namespace Tickets\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ReplyController extends AbstractActionController
{
    public function viewAction()
    {
        $ticketId = (int) $this->params()->fromRoute('id', 0);
        if ($ticketId <= 0) {
            return $this->redirect()->toUrl('/tickets');
        }

        $adapter     = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $ticketTable = new TableGateway('tbl_support_tickets', $adapter);

        // ✅ Sirf admin/staff par NEW badge clear karo
        $isAdmin = (($_SESSION['user_id'] ?? null) == 0) || (($_SESSION['STAFFUSER'] ?? '0') === '1');
        if ($isAdmin) {
            $ticketTable->update(['new_reply' => 0], ['id' => $ticketId, 'new_reply' => 1]);
        }

        // ✅ TICKET DETAILS + user_name / user_email (JOIN)
        $sql = "
            SELECT
                t.*,
                s.Company_name AS user_name,
                s.email        AS user_email
            FROM tbl_support_tickets t
            LEFT JOIN tbl_staff s ON s.id = t.user_id
            WHERE t.id = ?
            LIMIT 1
        ";
        $ticket = $adapter->createStatement($sql, [$ticketId])->execute()->current();
        if (!$ticket) {
            return $this->redirect()->toUrl('/tickets');
        }

        $replyTable  = new TableGateway('tbl_ticket_replies', $adapter);
        $attachTable = new TableGateway('tbl_ticket_attachments', $adapter);

        $replies = [];

        // absolute paths for attachment URL mapping
        $basePathAbs = '/home/primebackstage/htdocs/www.primebackstage.in';
        $docrootAbs  = $basePathAbs . '/public';

        // helper: server path -> web path
        $toWebPath = function (string $filePath) use ($docrootAbs, $basePathAbs): string {
            $web = trim(str_replace($docrootAbs, '', $filePath));
            if ($web === $filePath) {
                $web = trim(str_replace($basePathAbs, '', $filePath));
            }
            if ($web === '') return '';
            if ($web[0] !== '/') {
                $web = '/' . ltrim($web, '/');
            }
            return $web;
        };

        // ✅ Replies fetch:
        //    NOTE: we ALSO fetch original_from_email so we can decide bubble name
        $sqlReplies = "
            SELECT
                r.id,
                r.ticket_id,
                r.user_id,
                r.reply_text,
                r.replied_at,
                r.original_from_email
            FROM tbl_ticket_replies r
            WHERE r.ticket_id = ?
            ORDER BY r.replied_at ASC
        ";
        $stmtReplies = $adapter->createStatement($sqlReplies, [$ticketId]);
        $result      = $stmtReplies->execute();

        // ticket owner info (for comparison)
        $ticketOwnerEmail = strtolower(trim($ticket['user_email'] ?? ''));
        $ticketOwnerName  = trim($ticket['user_name'] ?? 'User');

        foreach ($result as $row) {

            $replyId    = (int)$row['id'];
            $replyUser  = (int)$row['user_id']; // 0 => PDA/admin, others => external or owner
            $replyText  = (string)$row['reply_text'];
            $replyAtRaw = (string)$row['replied_at'];

            // may be NULL if old rows
            $fromEmail = isset($row['original_from_email'])
                ? trim((string)$row['original_from_email'])
                : '';

            // === Pull attachments for this reply ===
            $attsRows = $attachTable->select(function (Select $s) use ($replyId, $ticketId) {
                $where = new Where();
                $where->nest()->equalTo('reply_id', $replyId)->unnest()
                      ->or
                      ->nest()->isNull('reply_id')->and->equalTo('ticket_id', $ticketId)->unnest();
                $s->where($where)->order('uploaded_at ASC');
            });

            $attachments = [];
            foreach ($attsRows as $att) {
                $filePath = (string)$att['file_path'];
                $fileName = !empty($att['file_name'])
                    ? (string)$att['file_name']
                    : basename($filePath);

                $webPath = $toWebPath($filePath);
                if ($webPath !== '' && $webPath[0] !== '/') {
                    $webPath = '/' . $webPath;
                }

                $attachments[] = [
                    'id'          => (int)$att['id'],
                    'file_name'   => $fileName,
                    'file_path'   => $filePath,
                    'web_url'     => $webPath,
                    'uploaded_at' => isset($att['uploaded_at'])
                        ? (string)$att['uploaded_at']
                        : null,
                ];
            }

            // =========================
            // WHO SHOWS AS SENDER CHIP?
            // =========================
            // rule:
            //   if user_id == 0 → admin bubble ("PDA Team")
            //   else
            //       if original_from_email == ticket owner's email
            //            show ticket owner's display name (company_name)
            //       else
            //            show that external email (like someone@believedigital.com)
            //
            $isAdminBubble = false;
            $displayName   = '';

            if ($replyUser === 0) {
                // PDA / internal agent
                $isAdminBubble = true;
                $displayName   = 'PDA Team';
            } else {
                // Non-admin incoming mail
                if (
                    $fromEmail !== '' &&
                    $ticketOwnerEmail !== '' &&
                    strtolower($fromEmail) === $ticketOwnerEmail
                ) {
                    // same as ticket opener
                    $displayName = ($ticketOwnerName !== '')
                        ? $ticketOwnerName
                        : $ticketOwnerEmail;
                } else {
                    // different sender (forwarded partner, Believe, label, etc.)
                    if ($fromEmail !== '') {
                        $displayName = $fromEmail;
                    } else {
                        // fallback if we don't have original_from_email (old data)
                        $displayName = ($ticketOwnerName !== '')
                            ? $ticketOwnerName
                            : 'User';
                    }
                }
            }

            $replies[] = [
                'id'                  => $replyId,
                'is_admin'            => $isAdminBubble,
                'display_name'        => $displayName,
                'reply_text'          => $replyText,
                'replied_at'          => $replyAtRaw, // keep raw; view will format
                'attachments'         => $attachments,
                'original_from_email' => $fromEmail,
            ];
        }

        $ticket_attachments = []; // (fill if you have root ticket attachments separately)

        return (new ViewModel([
            'ticket'             => $ticket,
            'replies'            => $replies,
            'ticket_attachments' => $ticket_attachments,
            'isAdmin'            => $isAdmin,
        ]))->setTemplate('tickets/thread/view');
    }
}
