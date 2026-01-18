<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Db\TableGateway\TableGateway;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
		
    }
public function clearNotificationsAction() {
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $userId = $_SESSION['user_id']; // adjust based on your session
    $sql = "DELETE FROM tbl_notifications WHERE user_id = ?";
    $adapter->query($sql, [$userId]);

    echo json_encode(['status' => 'OK']);
    exit;
}

public function markAllReadAction() {
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $userId = $_SESSION['user_id'];
    $sql = "UPDATE tbl_notifications SET is_read = 1 WHERE user_id = ?";
    $adapter->query($sql, [$userId]);

    echo json_encode(['status' => 'OK']);
    exit;
}

    public function validateduplicateAction()
    {

        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $request = $this->getRequest();

        if ($request->isPost()) {

            $tableName=$request->getPost('tableName');
            $ID=$request->getPost('KEY_ID');
            $fieldName=$request->getPost('fieldName');


            $sql = "select * from $tableName where $fieldName=$ID";

            $optionalParameters = array();
            $statement = $adapter->createStatement($sql, $optionalParameters);

            $result = $statement->execute();
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            $rowset = $resultSet->toArray();

            if(count($rowset)>0) {

                $result1['recordsTotal'] = count($rowset);
                $result1['DBStatus'] = 'ERR';

                $result1 = json_encode($result1);
                echo $result1;
            }
        }
        exit;

    }


	
}
