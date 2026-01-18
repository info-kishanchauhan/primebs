<?php
namespace Team\Controller;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function inviteuserAction()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $en_key = "#&$sdfdfs789fs9w";

            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $staffTable = new TableGateway('tbl_staff', $adapter);

            $loginLink = 'https://www.primebackstage.in/login';

            try {
                $staffTable->insert([
                    'login_name'           => $data['username'],
                    'email'                => $data['email'],
                    'password'             => openssl_encrypt($data['password'], "AES-128-ECB", $en_key),
                    'first_name'           => '',
                    'last_name'            => '',
                    'company_name'         => '',
                    'address'              => '',
                    'city'                 => '',
                    'isoCountry'           => '',
                    'user_type'            => 'subuser',
                    'role'                 => $data['role'],
                    'client_id'            => 0,
                    'created_by'           => 0,
                    'status'               => 1,
                    'account_status'       => 'enabled',
                    'user_access'          => json_encode($data['pages'] ?? []),
                    'labels'               => '',
                    'artist'               => '',
                    'releasing_network'    => '',
                    'label_manager_email'  => '',
                    'royalty_rate_per'     => 0,
                    'created_at'           => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                echo json_encode(['status' => 'fail', 'error' => $e->getMessage()]);
                exit;
            }

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'support@primedigitalarena.in';
                $mail->Password = 'Razvi@78692786';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
                $mail->addAddress($data['email']);
                $mail->Subject = 'Your Login Access';
                $mail->Body = "Hi,\n\nYou’ve been invited to join the dashboard.\n\nLogin: {$data['username']}\nPassword: {$data['password']}\n\nLogin here: $loginLink";

                $mail->send();
            } catch (Exception $e) {
                echo json_encode(['status' => 'mail_fail', 'error' => $e->getMessage()]);
                exit;
            }

            echo json_encode(['status' => 'success']);
            exit;
        }

        echo json_encode(['status' => 'fail']);
        exit;
    }

    public function listAction()
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $staffTable = new TableGateway('tbl_staff', $adapter);
        $rows = $staffTable->select(['client_id' => 0])->toArray();

        $output = [];
        foreach ($rows as $row) {
            $output[] = [
                'id'       => $row['id'],
                'username' => $row['login_name'],
                'email'    => $row['email'],
                'role'     => $row['role'] ?? 'null',
                'status'   => $row['account_status'] == 'enabled' ? 'enabled' : 'disabled',
                'pages'    => !empty($row['user_access']) ? json_decode($row['user_access'], true) : [],
            ];
        }

        echo json_encode($output);
        exit;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $request->getPost('id');
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $staffTable = new TableGateway('tbl_staff', $adapter);
            $staffTable->delete(['id' => $id]);
            echo json_encode(['status' => 'success']);
            exit;
        }
        echo json_encode(['status' => 'fail']);
        exit;
    }

    public function toggleStatusAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $request->getPost('id');
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $staffTable = new TableGateway('tbl_staff', $adapter);
            $row = $staffTable->select(['id' => $id])->current();
            $newStatus = $row['status'] == 1 ? 0 : 1;
            $accountStatus = $newStatus == 1 ? 'enabled' : 'disabled';
            $staffTable->update(['status' => $newStatus, 'account_status' => $accountStatus], ['id' => $id]);
            echo json_encode(['status' => 'success']);
            exit;
        }
        echo json_encode(['status' => 'fail']);
        exit;
    }

    public function getAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $request->getPost('id');
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $staffTable = new TableGateway('tbl_staff', $adapter);
            $row = $staffTable->select(['id' => $id])->current();
            $row['pages'] = json_decode($row['user_access'], true) ?? [];
            echo json_encode($row);
            exit;
        }
        echo json_encode(['status' => 'fail']);
        exit;
    }

    public function resendAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $request->getPost('id');
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $staffTable = new TableGateway('tbl_staff', $adapter);
            $user = $staffTable->select(['id' => $id])->current();

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'support@primedigitalarena.in';
                $mail->Password = 'Razvi@78692786';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('support@primedigitalarena.in', 'Prime Digital Arena');
                $mail->addAddress($user['email']);
                $mail->Subject = 'Your Login Access';
                $mail->Body = "Hi,\n\nYour account is ready.\n\nLogin: {$user['login_name']}\nPassword: (same as set)\n\nLogin here: https://www.primebackstage.in/login";

                $mail->send();
                echo json_encode(['status' => 'success', 'message' => 'Email resent.']);
                exit;
            } catch (Exception $e) {
                echo json_encode(['status' => 'fail', 'message' => $e->getMessage()]);
                exit;
            }
        }
        echo json_encode(['status' => 'fail']);
        exit;
    }
}
