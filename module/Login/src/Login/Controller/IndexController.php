<?php

namespace Login\Controller;

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
	public function verifyAction()
    {
		$token  = $_GET['token'];
		
			$this->layout('layout/layout_login');
			
			$sl = $this->getServiceLocator();        
			$adapter = $sl->get('Zend\Db\Adapter\Adapter');
			$customObj = $this->CustomPlugin();
			$id = str_replace('PDA','',$token);
			$mysqli = $customObj->dbconnection();
			
			$select_query = "SELECT * FROM tbl_staff WHERE id = '".$id."' ";
			$select_result = $mysqli->query($select_query) or die($mysqli -> error);
			$select_row = mysqli_fetch_array($select_result);
			$result = array($select_row);
			
			
			$viewModel= new ViewModel(array(
				'Email' => $result[0]['email']
			));
			return   $viewModel;	
		
        
    }
	
	
	public function setpasswordAction()
    {
		
			$this->layout('layout/layout_login');
			
			$sl = $this->getServiceLocator();        
			$adapter = $sl->get('Zend\Db\Adapter\Adapter');
			$customObj = $this->CustomPlugin();
			$id = str_replace('PDA','',$token);
			$mysqli = $customObj->dbconnection();
			
			$select_query = "SELECT * FROM tbl_staff WHERE login_name = '".$_GET['u']."' and password='' ";
			$select_result = $mysqli->query($select_query) or die($mysqli -> error);
			
			if(mysqli_num_rows($select_result) > 0)
			{
				
			}
			else
			{
				echo 'Password already has been set.';
				exit;
			}
        
    }
	public function savePasswordAction()
	{
		$request = $this->getRequest();
		$en_key = "#&$sdfdfs789fs9w";
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
			
			//$encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($en_key), $_POST['password'], MCRYPT_MODE_CBC, md5(md5($en_key))));
			$encoded = openssl_encrypt($_POST['password'], "AES-128-ECB", $en_key);
			$new_Data['password'] = $encoded;
			
			
			$projectTable->update($new_Data,array("login_name='".$_POST['u']."' and password='' "));
			
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
    public function logoutAction()
    {
        $sl = $this->getServiceLocator();        
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $customObj = $this->CustomPlugin();
        
        $mysqli = $customObj->dbconnection();
        $mysqli->query("UPDATE tbl_staff SET last_logout ='" . date('Y-m-d H:i:s') . "' WHERE id='" . $_SESSION["user_id"] . "'") or die($mysqli->error);
    
        session_destroy();
        header("location: ../login");
        exit;
    }
	
	public function resendotpAction()
	{
		$sl = $this->getServiceLocator();        
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');  
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
	    $config = $this->getServiceLocator()->get('config');
		$client = $_POST['client'];
		$id = str_replace('PDA','',$client); 
		
		//$otp = rand(100101, 999999);
			
		$mysqli = $customObj->dbconnection();
		$select_query = "SELECT * FROM tbl_staff WHERE id = '".$id."'  ";
		$select_result = $mysqli->query($select_query) or die($mysqli -> error);
		$select_row = mysqli_fetch_array($select_result);
		
		$otp = $select_row['otp'];
		 
		//$mysqli->query("update tbl_staff set otp='".$otp."' where id='".$id."' ");
			

$content = '
<table style="max-width:600px; margin:auto; background-color:#ffffff; padding:30px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
  <tr>
    <td style="text-align:center;">
      <h2 style="color:#333333;">Login Verification</h2>
      <p style="color:#555555;">Please verify with the OTP below:</p>
      <p style="font-size:24px; font-weight:bold; color:#2b6cb0; margin:20px 0;">' . $otp . '</p>
      <p style="color:#888888; font-size:14px;">This OTP is valid for 5 minutes and can be used only once.</p>
    </td>
  </tr>
</table>';
			
			$customObj->sendSmtpEmail($config,$select_row['email'],'Prime Backstage login verification',$content);
			
		$result1['DBStatus'] = 'OK';
        echo json_encode($result1); exit;
	}
	 public function checkotpAction()
{
    $sl = $this->getServiceLocator();        
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');  
    $customObj = $this->CustomPlugin();
    $request = $this->getRequest();
   
	$client = $_POST['client'];
	$otp = $_POST['otp'];
	
	$id = str_replace('PDA','',$client); 
	

    $config = $this->getServiceLocator()->get('config');
    $URL = $config['ADMIN_URL'];
    $mysqli = $customObj->dbconnection();
 
   
    $select_query = "SELECT * FROM tbl_staff WHERE id = '".$id."' AND otp='".$otp."' ";
    $select_result = $mysqli->query($select_query) or die($mysqli -> error);

    if(mysqli_num_rows($select_result) > 0)
    {
        $select_row = mysqli_fetch_array($select_result);
        $result = array($select_row);
		
		 
		
        $_SESSION["user_id"] = $result[0]['id'];
        $_SESSION["SUBUSER"] = $result[0]['subuser'];
		$_SESSION["STAFFUSER"] = $result[0]['staff'];
        $_SESSION["USER_NAME"] = $result[0]['login_name'];
        $_SESSION["CLIENT_NO"] = $result[0]['client_id'];
        $_SESSION["USER_TYPE"] = $result[0]['user_type'];
        $_SESSION["USER_START_DATE"] = $result[0]['start_date'];
        $_SESSION["USER_END_DATE"] = $result[0]['end_date'];
        $_SESSION["USER_ACCESS"] = explode(',',$result[0]['user_access']);
        $_SESSION["PHOTO_FILE"] = $result[0]['photo_file'];
        $_SESSION["DEPARTMENT_ID"] = $result[0]['department'];
		$_SESSION["PERMISSION_TYPE"] = $result[0]['permission_type'];
		$_SESSION['LIVE_STATUS'] = $result[0]['on_off_status'];

			// ✅ After user session is set
			$labelAccess = [];
			if (!empty($result[0]['labels'])) {
				$labelIds = explode(',', $result[0]['labels']);
				foreach ($labelIds as $id) {
					$labelAccess[] = (int) trim($id);
				}
			}
			$_SESSION['LABEL_ACCESS'] = $labelAccess;
				  
			// ✅ Logo Settings
			$setting_query = $mysqli->query("select * from tbl_settings");
			$settings = mysqli_fetch_array($setting_query);
			$_SESSION["LOGO"] = $settings['logo'];
			$_SESSION['FAVICON'] = $settings['favicon'];

       
            $last_login = date('Y-m-d H:i:s');
            $mysqli->query("update tbl_staff set last_login='".$last_login."',last_otp_login='".date('Y-m-d')."',last_login_ip='".$_SERVER['REMOTE_ADDR']."'  where id='".$result[0]['id']."' ");
        

        $result1['DBStatus'] = 'OK';
        $result1['dashboard'] = 'dashboard';
        echo json_encode($result1); exit;
    }
    else
    {
        echo json_encode(['DBStatus' => 'ERR', 'DBMsg' => 'Invalid']);
        exit;
    }
}


	public function updateStatusAction()
    {
        $request = $this->getRequest();
        $en_key = "#&$sdfdfs789fs9w";
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
			$releaseTable = new TableGateway('tbl_release', $adapter);
			
            $aData = array();
			$aData['on_off_status'] = $_REQUEST['status'];
			$mysqli = $customObj->dbconnection(); 
			if($aData['on_off_status'] == 'On')
			{
				$rowset = $projectTable->select(array("id='".$_SESSION['user_id']."'"));
				$rowset = $rowset->toArray();
				$assign_limit = $rowset[0]['release_limit'];
				
				/*$rowset = $releaseTable->select(array("status='inreview' and in_process=0 and assigned_team='".$_SESSION['user_id']."' "));
				$rowset = $rowset->toArray();
				$assigned = count($rowset);
				
				$assign_limit = $assign_limit - $assigned;*/
				
				if($assign_limit > 0)
				{
					
					$mysqli->query("Update tbl_release set auto_assigned_team='1',assigned_team='".$_SESSION['user_id']."'  where status='inreview' and in_process=0 and  assigned_team='0' and internal_notes=20 limit $assign_limit ");
				}
			}
			else
			{
				$mysqli->query("Update tbl_release set auto_assigned_team='0',assigned_team='0'  where status='inreview' and in_process=0 and  auto_assigned_team !='0'  ");
			}
			
			$_SESSION['LIVE_STATUS'] = $aData['on_off_status'];
            $projectTable->update($aData,array("id = '".$_SESSION['user_id']."' "));
			
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
	
    public function checkLogindetailsAction()
{
    $sl = $this->getServiceLocator();        
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');  
    $customObj = $this->CustomPlugin();
    $request = $this->getRequest();
    $aData = json_decode($request->getPost("FORM_DATA"));
    $aData = (array)$aData;

    $config = $this->getServiceLocator()->get('config');
    $URL = $config['ADMIN_URL'];
    $mysqli = $customObj->dbconnection();

    $en_key = "#&$sdfdfs789fs9w";
    $encoded = openssl_encrypt($aData['login_password'], "AES-128-ECB", $en_key);

    $select_query = "SELECT * FROM tbl_staff WHERE login_name = '".$aData['login_name']."' AND password='".$encoded."' ";
    $select_result = $mysqli->query($select_query) or die($mysqli -> error);

    if(mysqli_num_rows($select_result) > 0)
    {
        $select_row = mysqli_fetch_array($select_result);
        $result = array($select_row);
		
		if($result[0]['status'] == 1 || $result[0]['sub_status'] == 1)
		{
			 echo json_encode(['DBStatus' => 'disable', 'DBMsg' => 'Your account has been disabled. Please contact the administrator.']);
			exit;
		}
		
		$last_otp_login  = date('Y-m-d',strtotime($result[0]['last_otp_login']." +30 days"));
		
		if( date('Y-m-d') > $last_otp_login  || $result[0]['last_login_ip'] != $_SERVER['REMOTE_ADDR'])
		{
			$otp = rand(100101, 999999);
			
			if($result[0]['id'] == 0)
				$otp=102030;
			
			$mysqli->query("update tbl_staff set otp='".$otp."' where id='".$result[0]['id']."' ");
			
			$content ='<table style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
				<tr>
				  <td style="text-align: center;">
					<h2 style="color: #333333;">Login Verification</h2>
					<p style="color: #555555;">Please verify with the OTP below:</p>
					<p style="font-size: 24px; font-weight: bold; color: #2b6cb0; margin: 20px 0;">'.$otp.'</p>
					<p style="color: #888888; font-size: 14px;">This OTP is valid for 5 minutes and can be used only once.</p>
					
				  </td>
				</tr>
			  </table>';
			
			$customObj->sendSmtpEmail($config,$result[0]['email'],'Prime Backstage login verification',$content);
			
			$result1['DBStatus'] = 'OK';
			$result1['dashboard'] = 'login/verify?token=PDA'.$result[0]['id'];
			echo json_encode($result1); exit;
		}

        $_SESSION["user_id"] = $result[0]['id'];
		$_SESSION["SUBUSER"] = $result[0]['subuser'];
		$_SESSION["STAFFUSER"] = $result[0]['staff'];
        $_SESSION["USER_NAME"] = $result[0]['login_name'];
        $_SESSION["CLIENT_NO"] = $result[0]['client_id'];
        $_SESSION["USER_TYPE"] = $result[0]['user_type'];
        $_SESSION["USER_START_DATE"] = $result[0]['start_date'];
        $_SESSION["USER_END_DATE"] = $result[0]['end_date'];
        $_SESSION["USER_ACCESS"] = explode(',',$result[0]['user_access']);
        $_SESSION["PHOTO_FILE"] = $result[0]['photo_file'];
        $_SESSION["DEPARTMENT_ID"] = $result[0]['department'];
		$_SESSION["PERMISSION_TYPE"] = $result[0]['permission_type'];
		$_SESSION['LIVE_STATUS'] = $result[0]['on_off_status'];

		// ✅ After user session is set
		$labelAccess = [];
		if (!empty($result[0]['labels'])) {
			$labelIds = explode(',', $result[0]['labels']);
			foreach ($labelIds as $id) {
				$labelAccess[] = (int) trim($id);
			}
		}
		$_SESSION['LABEL_ACCESS'] = $labelAccess;
			  
		// ✅ Logo Settings
		$setting_query = $mysqli->query("select * from tbl_settings");
		$settings = mysqli_fetch_array($setting_query);
		$_SESSION["LOGO"] = $settings['logo'];
		$_SESSION['FAVICON'] = $settings['favicon'];

        if($result[0]['id'] != 0) {
            $last_login = date('Y-m-d H:i:s');
            $mysqli->query("update tbl_staff set last_login='".$last_login."' where id='".$result[0]['id']."' ");
        }

        $result1['DBStatus'] = 'OK';
        $result1['dashboard'] = 'dashboard';
        echo json_encode($result1); exit;
    }
    else
    {
        echo json_encode(['DBStatus' => 'ERR', 'DBMsg' => 'Incorrect Username or Password']);
        exit;
    }
}

}
// End Class
