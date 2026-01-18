<?php
namespace Tickets\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Db\TableGateway\TableGateway;

class PublicController extends AbstractActionController
{
    public function feedbacksubmitAction()
    {
        $req = $this->getRequest();
        if (!$req->isPost()) {
            return new JsonModel(['ok'=>false,'error'=>'method_not_allowed']);
        }

        $p = $req->getPost()->toArray();

        // Honeypot
        if (!empty($p['website'] ?? '')) {
            return new JsonModel(['ok'=>false,'error'=>'bad_request']);
        }

        // Fields (names must match form)
        $tid     = (int)($p['tid'] ?? 0);
        $token   = trim($p['token'] ?? '');
        $name    = trim($p['name'] ?? '');
        $email   = trim($p['email'] ?? '');
        $title   = trim($p['title'] ?? '');
        $rating  = (int)($p['rating'] ?? 0);
        $comment = trim($p['comment'] ?? '');

        if ($tid<=0 || $token==='' || $rating<1 || $rating>5 || mb_strlen($comment)<10) {
            return new JsonModel(['ok'=>false,'error'=>'bad_request']);
        }

        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

        // 1) Verify token
        $row = $adapter->createStatement(
            "SELECT id, ticket_id, user_id, status, created_at,
                    COALESCE(expires_at, DATE_ADD(created_at, INTERVAL 14 DAY)) AS expires_at
             FROM tbl_ticket_feedback
             WHERE token = ? AND ticket_id = ?
             LIMIT 1",
            [$token, $tid]
        )->execute()->current();

        if (!$row) {
            return new JsonModel(['ok'=>false,'error'=>'invalid_token']);
        }

        $now = new \DateTime();
        $exp = new \DateTime($row['expires_at']);
        if ($row['status'] !== 'pending' || $now > $exp) {
            return new JsonModel(['ok'=>false,'error'=>'expired']);
        }

        // 2) Insert response
        $respGw = new TableGateway('tbl_ticket_feedback_responses', $adapter);
        try {
            $respGw->insert([
                'ticket_id'    => $tid,
                'user_id'      => (int)$row['user_id'],
                'user_name'    => mb_substr($name, 0, 150),
                'user_email'   => mb_substr($email, 0, 190),
                'ticket_title' => mb_substr($title, 0, 255),
                'rating'       => $rating,
                'comment'      => mb_substr($comment, 0, 5000),
                'token'        => $token,
                'submitted_at' => date('Y-m-d H:i:s'),
                'ip'           => $_SERVER['REMOTE_ADDR'] ?? null,
                'ua'           => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // e.g. duplicate token (unique constraint)
            return new JsonModel(['ok'=>false,'error'=>'duplicate']);
        }

        // 3) Mark token used
        $fbGw = new TableGateway('tbl_ticket_feedback', $adapter);
        $fbGw->update([
            'status'  => 'used',
            'used_at' => date('Y-m-d H:i:s'),
        ], ['token' => $token]);

        // (optional) history insert, email notify, etc.

        return new JsonModel(['ok'=>true]);
    }
}
