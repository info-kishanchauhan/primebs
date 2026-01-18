<?php

namespace Forgotpwd\Controller;

use Zend\Db\TableGateway\TableGateway;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class IndexController extends AbstractActionController
{
    protected $studentTable;

    public function indexAction()
    {
        $this->layout('layout/layout_login');
    }
	public function resetAction()
    {
       $this->layout('layout/layout_login');
    }
	public function resetpwdlinkAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($request->getPost("FORM_DATA"));
		$aData = (array)$aData;
		
		$config = $this->getServiceLocator()->get('config');
			
		$URL = $config['URL'];
		
		$serviceLocator = $this->getServiceLocator();
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		
		$login_name =$_REQUEST['email'];
		
		$projectTable = new TableGateway('tbl_staff', $adapter);
		
		$rowset = $projectTable->select(array("login_name='".$login_name."' OR  email='".$login_name."'"));	
		$rowset = $rowset->toArray();
		$row= $rowset[0];
		
		
		
		if(count($rowset)>0)
		{
			$content ='<div style="font-size:24px;font-weight:bold;text-align:center;color:#000000;margin-top:20px;margin-bottom:20px">
			Password Reset
      </div><div style="text-align: left; color: #333333;">
				<p style="color: #666666;">Hello '.$row['login_name'].',</p>
                <p style="color: #666666;">You recently requested to reset your password. Click the button below to reset it:</p>
                <p style="text-align: left; margin-top: 30px;"><a href="'.$URL.'forgotpwd/reset?client=PDA'.$row['id'].'2024" style="display: inline-block; padding: 12px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                <p style="color: #666666;">If you didn\'t request this, you can ignore this email.</p>
				
				<p>Regards,<br>Prime Digital Arena</p>';
					
					
			$customObj->sendSmtpEmail($config,$row['email'],'Reset Password',$content);
		}
		 
		$result1['DBStatus'] = 'OK';	
		$result1 = json_encode($result1);
		echo $result1;	
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
			
			$id = $_POST['client'];
			$id = substr($id, 3, -4); // Extract the ID part in between
			
			if($id == '0' && $_POST['client'] != 'PDA02024')
			{
				
			}
			else
			{
				//$encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($en_key), $_POST['password'], MCRYPT_MODE_CBC, md5(md5($en_key))));
				$encoded = openssl_encrypt($_POST['password'], "AES-128-ECB", $en_key);
				$new_Data['password'] = $encoded;
				
				$projectTable->update($new_Data,array("id='".$id."'"));
			
			}
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



}//End Class


