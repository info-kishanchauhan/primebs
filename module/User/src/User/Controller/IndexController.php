<?php
namespace User\Controller;
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
		if($_SESSION['USER_TYPE'] != 'Enterprise' && $_SESSION['user_id'] != 0 && $_SESSION['STAFFUSER'] == '0' )
		{
			header('location:dashboard');
			exit;
		}
		
		
    }
    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }
	 public function list2Action()
    {
        echo $this->fnGrid2();
        exit;
    }
	public function transactioncAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$customObj = $this->CustomPlugin();
		$customObj->saveTransaction('2024-03-01',$adapter);
	}
    public function getrecAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$en_key = "#&$sdfdfs789fs9w";
		
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_staff', $adapter);
            $rowset = $projectTable->select(array('id' => $iID));
            $rowset = $rowset->toArray();
			
            foreach ($rowset as $record)
			{
				$record['user_access'] = explode(',',$record['user_access']);
				$record['labels'] = json_encode(explode(',',$record['labels']));
				$record['artist'] = json_encode(explode(',',$record['artist']));
				$record['releasing_network'] = json_encode(explode(',',$record['releasing_network']));
				
				$start_date = explode('-',$record['start_date']);
				$end_date = explode('-',$record['end_date']);
				
				$record['start_date'] = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
				$record['end_date'] = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];
					
                $recs[] = $record;
			}
				
			//$recs[0]['password'] = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($en_key), base64_decode($recs[0]['password']), MCRYPT_MODE_CBC, md5(md5($en_key))), "\0"); 
			
			if($recs[0]['password'] != '')
			{
				$recs[0]['password'] = openssl_decrypt($recs[0]['password'], "AES-128-ECB", $en_key);
				$recs[0]['confm_password'] = $recs[0]['password'];
			}
			
			$projectTable = new TableGateway('tbl_staff', $adapter);
            $rowset = $projectTable->select(array('created_by' => $iID));
            $rowset = $rowset->toArray();
			
			$recs[0]['subusercnt'] = count($rowset);
            $result['data'] = $recs;
            $result['recordsTotal'] = count($recs);
            $result['DBStatus'] = 'OK';
            $result = json_encode($result);
            echo $result;
            exit;
        }
    }
    public function  deleteAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				
				$aData = array('deleted_flag' => '1');
				$projectTable->delete(array("id=".$iMasterID));
				
				
				
				
                //$projectTable->delete(array("id=" . $iMasterID));
				
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	
	public function  disableAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "DISABLE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				
				$aData = array('status' => '1');
				$projectTable->update($aData,array("id=".$iMasterID));
				$projectTable->update($aData,array("created_by=".$iMasterID));
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function  disable2Action()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "DISABLE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				
				$aData = array('sub_status' => '1');
				$projectTable->update($aData,array("id=".$iMasterID));
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function  enableAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "ENABLE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				
				$aData = array('status' => '0');
				$projectTable->update($aData,array("id=".$iMasterID));
				$projectTable->update($aData,array("created_by=".$iMasterID));
				
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function  enable2Action()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "ENABLE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$info= $rowset[0]['name'];
				
				$aData = array('sub_status' => '0');
				$projectTable->update($aData,array("id=".$iMasterID));
				
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function resendAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		$en_key = "#&$sdfdfs789fs9w";
		
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$config = $this->getServiceLocator()->get('config');
		
		$URL = $config['URL'];
		$projectTable = new TableGateway('tbl_staff', $adapter);
		
		$rowset = $projectTable->select(array("id='".$_REQUEST['KEY_ID']."' "));
		$rowset = $rowset->toArray();
		$aData = $rowset[0];
		
		if($aData['password'] != '')
		{
		
			$password = openssl_decrypt($aData['password'], "AES-128-ECB", $en_key);
			
			$content ='<div style="font-size:20px;font-weight:bold;text-align:center;color:#000000;margin-top:20px;margin-bottom:20px">
			Your login details
			</div><div style="text-align: left; color: #333333;">
			<p>Hello '.$aData['first_name'].',<br>Your account has been created.<br>Please find your login details below:</p>
			<div style="margin-top: 20px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
				<p style="margin: 0; padding: 5px 0;"><span style="font-weight: bold;">Username:</span> <div style="border-radius:8px;border:solid 1px #c4c4c4;font-size:24px;padding:8px 18px 8px 18px;color:#2b2b2b"> '.$aData['login_name'].'</div></p>
				<p style="margin: 0; padding: 5px 0;"><span style="font-weight: bold;">Password:</span> <div style="border-radius:8px;border:solid 1px #c4c4c4;font-size:24px;padding:8px 18px 8px 18px;color:#2b2b2b"> '.$password.'</div></p>
			</div>
			<p>Once logged in, you will be able to set a personalized and secure password.</p>
			<p>With these login details, you can now connect to:</p>
			<p><img src="'.$URL.'public/uploads/'.$_SESSION['LOGO'].'" style="width:100px;"></p>
			<p>Your account on Prime\'s Backstage.</p>
			<p><b>Log in Backstage:</b></p>
			<div style="text-align: left; margin-top: 15px;">
				<a href="'.$URL.'login" style="width:120px;background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 30px; display: inline-block;text-align:center;">Log In</a>
			</div>';
			
			$customObj->sendSmtpEmail($config,$aData['email'],'Prime Backstage login details',$content);
		}
		else
		{
			$content = '
									<div style="font-size:20px; font-weight:bold; text-align:center; color:#000000; margin-top:20px; margin-bottom:20px;">
									  Your login details
									</div>

									<div style="text-align:left; color:#333333;">
									  <p>Hello '.$aData['first_name'].',</p>
									  <p>Your account has been created.<br>Please find your login details below:</p>

									  <div style="margin-top:20px; background-color:#f9f9f9; padding:10px; border-radius:5px;">
										<div style="margin-bottom:10px;">
										  <span style="font-weight:bold;">Username:</span>
										  <div style="border-radius:8px; border:1px solid #c4c4c4; font-size:20px; padding:8px 18px; color:#2b2b2b; margin-top:5px;">
											'.$aData['login_name'].'
										  </div>
										</div>
										<div style="margin-top:20px;">
										  <span style="font-weight:bold;">Set your password:</span>
										  <div style="text-align:center; margin-top:10px;">
											<a href="'.$URL.'login/setpassword?u='.urlencode($aData['login_name']).'" style="background-color:#28a745; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:30px; font-size:16px; display:inline-block;">
											  Set Password
											</a>
										  </div>
										</div>
									  </div>

									  <p style="text-align:center;">Your account on Prime\'s Backstage.</p>

									  <div style="text-align:center; margin-top:20px;">
										<a href="'.$URL.'login" style="background-color:#007bff; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:30px; font-size:16px; display:inline-block;">
										  Log In
										</a>
									  </div>
									</div>';
									
									$customObj->sendSmtpEmail($config,$aData['email'],'Prime Backstage login details',$content);
		}
		
		$result['DBStatus'] = 'OK';
        $result = json_encode($result);
        echo $result;
        exit;
	}
    public function saveAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$en_key = "#&$sdfdfs789fs9w";
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
			$config = $this->getServiceLocator()->get('config');
			
			$URL = $config['URL'];
            $projectTable = new TableGateway('tbl_staff', $adapter);
			
				if($request->getPost("pAction") == "ADD")
				{
					$aData = json_decode($request->getPost("FORM_DATA"));
					$aData = (array)$aData;
					unset($aData['MASTER_KEY_ID']);
					
					//$aData['password'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($en_key), $aData['password'], MCRYPT_MODE_CBC, md5(md5($en_key))));
					
					
					$password = $aData['password'];
					if($password != '')
						$aData['password'] = openssl_encrypt($aData['password'], "AES-128-ECB", $en_key);
					
					$start_date = explode('-',$aData['start_date']);
					$end_date = explode('-',$aData['end_date']);
					
					$aData['start_date'] = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
					$aData['end_date'] = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];
					
					$labels = $aData['labels'];
					if(is_array($labels))
						$labels=implode(',',$labels);
					$aData['labels']=$labels;
					
					if($aData['labels'] == '')
						unset($aData['labels']);
					
					
					$artist = $aData['artist'];
					if(is_array($artist))
						$artist=implode(',',$artist);
					$aData['artist']=$artist;
					
					if($aData['artist'] == '')
						unset($aData['artist']);
					
					if($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')
					{
						$releasing_network = $aData['releasing_network'];
						if(is_array($releasing_network))
							$releasing_network=implode(',',$releasing_network);
						$aData['releasing_network']=$releasing_network;
					}
					
					$user_access = $aData['user_access[]'];
					if(is_array($user_access))
						$user_access=implode(',',$user_access);
					$aData['user_access']=$user_access;
					
					if($aData['user_access'] == '')
						unset($aData['user_access']);
					
					unset($aData['user_access[]']);
					unset($aData['confm_password']);
					$aData['created_by']=$_SESSION['user_id'];
					$aData['created_on']=date("Y-m-d h:i:s");
					
					if($aData['created_by'] > 0)
						$aData['subuser'] = 1;
					
					$aData['last_otp_login'] = date('Y-m-d');
					
					$projectTable->insert($aData);
					$last_id = $projectTable->lastInsertValue;
					$uData = array();
					$uData['client_id'] =  sprintf('%06d',$last_id );
					$projectTable->update($uData,array("id='".$last_id."'"));
					
					
					if($aData['subuser'] == 1)
					{
						$content = '
									<div style="font-size:20px; font-weight:bold; text-align:center; color:#000000; margin-top:20px; margin-bottom:20px;">
									  Your login details
									</div>

									<div style="text-align:left; color:#333333;">
									  <p>Hello '.$aData['first_name'].',</p>
									  <p>Your account has been created.<br>Please find your login details below:</p>

									  <div style="margin-top:20px; background-color:#f9f9f9; padding:10px; border-radius:5px;">
										<div style="margin-bottom:10px;">
										  <span style="font-weight:bold;">Username:</span>
										  <div style="border-radius:8px; border:1px solid #c4c4c4; font-size:20px; padding:8px 18px; color:#2b2b2b; margin-top:5px;">
											'.$aData['login_name'].'
										  </div>
										</div>
										<div style="margin-top:20px;">
										  <span style="font-weight:bold;">Set your password:</span>
										  <div style="text-align:center; margin-top:10px;">
											<a href="'.$URL.'login/setpassword?u='.urlencode($aData['login_name']).'" style="background-color:#28a745; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:30px; font-size:16px; display:inline-block;">
											  Set Password
											</a>
										  </div>
										</div>
									  </div>

									  <p style="text-align:center;">Your account on Prime\'s Backstage.</p>

									  <div style="text-align:center; margin-top:20px;">
										<a href="'.$URL.'login" style="background-color:#007bff; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:30px; font-size:16px; display:inline-block;">
										  Log In
										</a>
									  </div>
									</div>';
									
									$customObj->sendSmtpEmail($config,$aData['email'],'Prime Backstage login details',$content);
					}
					else
					{
					
					$content ='<div style="font-size:20px;font-weight:bold;text-align:center;color:#000000;margin-top:20px;margin-bottom:20px">
        Your login details
      </div><div style="text-align: left; color: #333333;">
					<p>Hello '.$aData['first_name'].',<br>Your account has been created.<br>Please find your login details below:</p>
					<div style="margin-top: 20px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
						<p style="margin: 0; padding: 5px 0;"><span style="font-weight: bold;">Username:</span> <div style="border-radius:8px;border:solid 1px #c4c4c4;font-size:24px;padding:8px 18px 8px 18px;color:#2b2b2b"> '.$aData['login_name'].'</div></p>
						<p style="margin: 0; padding: 5px 0;"><span style="font-weight: bold;">Password:</span> <div style="border-radius:8px;border:solid 1px #c4c4c4;font-size:24px;padding:8px 18px 8px 18px;color:#2b2b2b"> '.$password.'</div></p>
					</div>
					<p>Once logged in, you will be able to set a personalized and secure password.</p>
					<p>With these login details, you can now connect to:</p>
					<p><img src="'.$URL.'public/uploads/'.$_SESSION['LOGO'].'" style="width:100px;"></p>
					<p>Your account on Prime\'s Backstage.</p>
					<p><b>Log in Backstage:</b></p>
					<div style="text-align: left; margin-top: 15px;">
						<a href="'.$URL.'login" style="width:120px;background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 30px; display: inline-block;text-align:center;">Log In</a>
					</div>';
					
					$customObj->sendSmtpEmail($config,$aData['email'],'Prime Backstage login details',$content);
					
					}
				}
				else  if($request->getPost("pAction") == "EDIT")
				{
					$aData = json_decode($request->getPost("FORM_DATA"));
					$aData = (array)$aData;
					$iMasterID=$aData['MASTER_KEY_ID'];
					unset($aData['MASTER_KEY_ID']);
					
					//$aData['password'] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($en_key), $aData['password'], MCRYPT_MODE_CBC, md5(md5($en_key))));
					
					if($aData['password'] != '')
						$aData['password'] = openssl_encrypt($aData['password'], "AES-128-ECB", $en_key);
					
					$start_date = explode('-',$aData['start_date']);
					$end_date = explode('-',$aData['end_date']);
					
					$aData['start_date'] = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
					$aData['end_date'] = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];
					
					$labels = $aData['labels'];
					if(is_array($labels))
						$labels=implode(',',$labels);
					$aData['labels']=$labels;
					
					$artist = $aData['artist'];
					if(is_array($artist))
						$artist=implode(',',$artist);
					$aData['artist']=$artist;
					
					if($_SESSION['user_id'] == 0 || $_SESSION['STAFFUSER'] == '1')
					{
						$releasing_network = $aData['releasing_network'];
						if(is_array($releasing_network))
							$releasing_network=implode(',',$releasing_network);
						$aData['releasing_network']=$releasing_network;
					}
					
					$user_access = $aData['user_access[]'];
					if(is_array($user_access))
						$user_access=implode(',',$user_access);
					$aData['user_access']=$user_access;
					
					unset($aData['user_access[]']);
					unset($aData['confm_password']);
					$projectTable->update($aData,array("id=".$iMasterID));
					
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
    public function validateduplicateAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $tableName=$request->getPost('tableName');
            $ID=$request->getPost('KEY_ID');
            $fieldName=$request->getPost('fieldName');
            $sql = "select * from $tableName where $fieldName='".$ID."'";
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
public function fnGrid()
{
    /*
        * Script:    DataTables server-side script for PHP and MySQL
        * Copyright: 2010 - Allan Jardine, 2012 - Chris Wright
        * License:   GPL v2 or BSD (3-point)
        */
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = array('id','login_name','email','last_login','user_access','status','sub_status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_staff";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['user']       = $arrDBInfo['username'];
    $gaSql['password']   = $arrDBInfo['password'];
    $gaSql['db']         = $arrDBInfo['db'];
    $gaSql['server']     = $arrDBInfo['host'];
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    /*
     * Local functions
     */
    /*
     * MySQL connection
     */
    $customObj = $this->CustomPlugin();
   $mysqli=$customObj->dbconnection();
    /*
     * Paging
     */
    $sLimit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
            intval( $_GET['iDisplayLength'] );
    }
    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
            }
        }
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = "";
    if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
            {
                $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string($_GET['sSearch_'.$i])."%' ";
        }
    }
	
	//Add deleted_flag
    if($sWhere=="")
        $sWhere=" where id!=0";
    else
        $sWhere.=" AND  id!=0";
	
	
	if($_SESSION['STAFFUSER'] == '1' || $_SESSION['user_id'] == '0')
	{
		$sWhere.=" AND  subuser=0 and staff=0 ";
	}
	else
		$sWhere.=" AND  ( created_by ='".$_SESSION['user_id']."' ) ";
	
	
	
	if($_GET['user_type'] != '0' )
	{
		$sWhere.=" AND  user_type ='".$_GET['user_type']."' ";
	}
	if($_GET['loginStatus'] != '0' )
	{
		if($_GET['loginStatus'] == 'Pending Connection')
			$sWhere.=" AND last_login IS NULL ";
		if($_GET['loginStatus'] == 'Confirmed')
			$sWhere.=" and date_format(last_login,'%Y-m-%d') > '1970-01-01' ";
	}
	$cond=array();
	if($_GET['accessLevel'] != '' )
	{
		
		$sWhere.= ' and (';
		
		foreach($_GET['accessLevel'] as $level)
		{
			$cond[]= " FIND_IN_SET('".$level."',user_access) ";
		}
		
		$sWhere.= implode(' OR ',$cond).')';
	}
	if($_GET['mysearch'] != '' )
	{
		$sWhere.=" AND ((login_name like '%".$_GET['mysearch']."%' ) OR (email like '%".$_GET['mysearch']."%' ))  ";
	}
    /*
     * SQL queries
     * Get data to display
     */
      $sQuery = "
       SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
    ";
    $rResult = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    /* Data set length after filtering */
    $sQuery = "
        SELECT FOUND_ROWS()
    ";
    $rResultFilterTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
    /*
     * Output
     */
    $output = array(
        "sEcho" => intval(@$_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
    while ( $aRow = mysqli_fetch_array( $rResult ) )
    {
        $row = array();
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
            }
			else if ( $aColumns[$i] == "last_login" )
            {
				if($aRow['last_login'] != '0000-00-00 00:00:00' && $aRow['last_login'] != null)
					$row[] = '<b>Confirmed</b><br>&nbsp;';
				else
					$row[]='<b>Pending Connection</b><br>&nbsp;';
			}
			else if ( $aColumns[$i] == "login_name" )
            {
				$row[] = '<b>'.$aRow['login_name'].'</b>';
			}
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        $output['aaData'][] = $row;
    }
    echo json_encode( $output );
}

public function fnGrid2()
{
    /*
        * Script:    DataTables server-side script for PHP and MySQL
        * Copyright: 2010 - Allan Jardine, 2012 - Chris Wright
        * License:   GPL v2 or BSD (3-point)
        */
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = array('id','login_name','email','last_login','user_access','sub_status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_staff";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['user']       = $arrDBInfo['username'];
    $gaSql['password']   = $arrDBInfo['password'];
    $gaSql['db']         = $arrDBInfo['db'];
    $gaSql['server']     = $arrDBInfo['host'];
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    /*
     * Local functions
     */
    /*
     * MySQL connection
     */
    $customObj = $this->CustomPlugin();
   $mysqli=$customObj->dbconnection();
    /*
     * Paging
     */
    $sLimit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
            intval( $_GET['iDisplayLength'] );
    }
    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
            }
        }
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $sWhere = "";
    if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
            {
                $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string( $_GET['sSearch'] )."%' OR ";
            }
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= $aColumns[$i]." LIKE '%". $mysqli -> real_escape_string($_GET['sSearch_'.$i])."%' ";
        }
    }
	
	//Add deleted_flag
    if($sWhere=="")
        $sWhere=" where id!=0";
    else
        $sWhere.=" AND  id!=0";
	
	
	
	$sWhere.=" AND  created_by ='".$_GET['main_user']."' and subuser=1 "; 
	
	
	
    /*
     * SQL queries
     * Get data to display
     */
     $sQuery = "
       SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
        $sOrder
        $sLimit
    ";
    $rResult = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    /* Data set length after filtering */
    $sQuery = "
        SELECT FOUND_ROWS()
    ";
    $rResultFilterTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(".$sIndexColumn.")
        FROM   $sTable
    ";
    $rResultTotal = $mysqli->query($sQuery) or $this->fatal_error( 'MySQL Error: ' . $mysqli -> errno );
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
    /*
     * Output
     */
    $output = array(
        "sEcho" => intval(@$_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
    while ( $aRow = mysqli_fetch_array( $rResult ) )
    {
        $row = array();
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
                $row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
            }
			else if ( $aColumns[$i] == "last_login" )
            {
				if($aRow['last_login'] != '0000-00-00 00:00:00' && $aRow['last_login'] != null)
					$row[] = '<b>Confirmed</b><br>&nbsp;';
				else
					$row[]='<b>Pending Connection</b><br>&nbsp;';
			}
			else if ( $aColumns[$i] == "login_name" )
            {
				$row[] = '<b>'.$aRow['login_name'].'</b>';
			}
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        $output['aaData'][] = $row;
    }
    echo json_encode( $output );
}
	public   function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }
	
 public function getStaffAction()    
 {        
	 $sl = $this->getServiceLocator();        
	 $adapter = $sl->get('Zend\Db\Adapter\Adapter');       

	 $cond = '';
	 if($_SESSION['user_id'] > 0 && $_SESSION['STAFFUSER'] == '0')
		 $cond = " and created_by = '".$_SESSION['user_id']."' ";
	 
	 $sql="select id,concat(first_name,' ',last_name)as name from tbl_staff where id != 0 $cond order by first_name asc";		        
	 $optionalParameters=array();        
	 $statement = $adapter->createStatement($sql, $optionalParameters);        
	 $result = $statement->execute();        
	 $resultSet = new ResultSet;        
	 $resultSet->initialize($result);        
	 $rowset=$resultSet->toArray();        
	 $result1['DBData'] = $rowset;        
	 $result1['recordsTotal'] = count($rowset);        
	 $result1['DBStatus'] = 'OK';        
	 $result = json_encode($result1);        
	 echo $result;        
	 exit;     
 }
 public function getCompanyAction()    
 {        
	 $sl = $this->getServiceLocator();        
	 $adapter = $sl->get('Zend\Db\Adapter\Adapter');    

	 $cond = '';
	 if($_SESSION['user_id'] > 0 && $_SESSION['STAFFUSER'] == '0')
		 $cond = " and created_by = '".$_SESSION['user_id']."' ";
	 
	 $sql="select id,company_name as name from tbl_staff where id != 0 and company_name != '' $cond order by company_name asc";		        
	 $optionalParameters=array();        
	 $statement = $adapter->createStatement($sql, $optionalParameters);        
	 $result = $statement->execute();        
	 $resultSet = new ResultSet;        
	 $resultSet->initialize($result);        
	 $rowset=$resultSet->toArray();        
	 $result1['DBData'] = $rowset;        
	 $result1['recordsTotal'] = count($rowset);        
	 $result1['DBStatus'] = 'OK';        
	 $result = json_encode($result1);        
	 echo $result;        
	 exit;     
 }
}//End Class