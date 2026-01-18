<?php
namespace Financialreport\Controller;
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
    }
    public function listAction()
    {
        echo $this->fnGrid();
        exit;
    }
	public function getReportStatusAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$en_key = "#&$sdfdfs789fs9w";
      
		$projectTable = new TableGateway('tbl_financial_report', $adapter);
		$rowset = $projectTable->select(array("status != 'success' "));
		$rowset = $rowset->toArray();
		
		if(count($rowset) > 0)
			$status = 'Processing';
		else
			$status = 'success';
			
		
		$result['status'] = $status;
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
		echo $result;
		exit;
        
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
            $projectTable = new TableGateway('tbl_financial_report', $adapter);
            $rowset = $projectTable->select(array('id' => $iID));
            $rowset = $rowset->toArray();
			
            foreach ($rowset as $record)
                $recs[] = $record;
				
			
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
            $projectTable = new TableGateway('tbl_financial_report', $adapter);
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$rowset = $projectTable->select(array('id' => $iMasterID));
				$rowset = $rowset->toArray();
				$csv_file= $rowset[0]['csv_file'];
				$pdf_file= $rowset[0]['pdf_file'];
				
				
				
                $projectTable->delete(array("id=" . $iMasterID));
				
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function uploadcsvAction()
	{
		$request = $this->getRequest();
        //Db Adaptor
        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		
		$aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		
		$config = $this->getServiceLocator()->get('config');
		$base_path = $config['BASE_PATH'];
        
        if ($request->isPost()) { 
            $file = $_FILES['attachment_file'];
            $filename = $_FILES['attachment_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION); 
			
			$filename = date('YmdHis').'.'.$ext;
            $myImagePath =  "public/uploads/$filename";
            
			if(strtoupper($ext) != 'CSV')
			{
				$result['status'] = 'NO_OK';
				$result = json_encode($result);
				echo $result;
				exit;	
			}
			
            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $result['status'] = 'OK';
                $result['message1'] = 'Done';				
				$result['doc_file1'] = $filename; 
				
            } 
        } 
        $result = json_encode($result);
        echo $result;
        exit;
	}
    
	public function uploadpdfAction()
	{
		$request = $this->getRequest();
        //Db Adaptor
        $serviceLocator = $this->getServiceLocator();
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		
		$aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		
		$config = $this->getServiceLocator()->get('config');
		$base_path = $config['BASE_PATH'];
        
        if ($request->isPost()) { 
            $file = $_FILES['attachment_file'];
            $filename = $_FILES['attachment_file']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION); 
			
			$filename = date('YmdHis').'.'.$ext;
            $myImagePath =  "public/uploads/$filename";
            
			if(strtoupper($ext) != 'PDF')
			{
				$result['status'] = 'NO_OK';
				$result = json_encode($result);
				echo $result;
				exit;	
			}
			
            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $result['status'] = 'OK';
                $result['message1'] = 'Done';				
				$result['doc_file1'] = $filename; 
				
            } 
        } 
        $result = json_encode($result);
        echo $result;
        exit;
	}
    public function generateReportAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$en_key = "#&$sdfdfs789fs9w";
		
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_financial_report', $adapter);
			$aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			
			print_r($aData);
			
			$report_type = $aData['reportType'];
			$fromDate = $aData['fromDate'];
			$toDate = $aData['toDate'];
			$single_report = $aData['dedicatedReport'];
			$multiple_report = $aData['splitReport'];
			
			$period = 'From '.$fromDate.' To '.$toDate;
			if($fromDate == $toDate)
				$period = $fromDate;
			
			if(strstr($fromDate,'Jan') && strstr($fromDate,'Mar'))
				$period = 'Q1 '.date('Y',strtotime($fromDate));
			if(strstr($fromDate,'Apr') && strstr($fromDate,'Jun'))
				$period = 'Q2 '.date('Y',strtotime($fromDate));
			if(strstr($fromDate,'Jul') && strstr($fromDate,'Sep'))
				$period = 'Q3 '.date('Y',strtotime($fromDate));
			if(strstr($fromDate,'Oct') && strstr($fromDate,'Dec'))
				$period = 'Q4 '.date('Y',strtotime($fromDate));
			
			
			if($report_type == 'single')
			{
				 
			}
			else
			{
				
			}
			
			$id=3;
				
			
			$rData = array();
			$rData['period'] = $period;		
			$rData['report_type'] = $_POST['summaryReport'];		
			$rData['status'] = 'processing';		
			$rData['created_on'] = date('Y-m-d H:i:s');	
			$rData['gen_json'] = json_encode($aData);	
			
			//$projectTable->insert($rData);
			$id= $projectTable->lastInsertValue;
			$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'/public/cron_file/generate_report.php '.$id);
			$customObj->start();	
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
	public function saveAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$en_key = "#&$sdfdfs789fs9w";
		
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_financial_report', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
			
				
					$aData = json_decode($request->getPost("FORM_DATA"));
					$aData = (array)$aData;
					unset($aData['MASTER_KEY_ID']);
					
					$aData['csv_file'] = $aData['filehidden1'];
					$aData['pdf_file'] = $aData['filehidden2'];
					unset($aData['filehidden1']);
					unset($aData['filehidden2']);
		
					$aData['status'] = 'success';
					$aData['created_on']=date("Y-m-d h:i:s");
					$projectTable->insert($aData);
					
					
					$nData = array();
					$nData['user_id'] = $aData['user_id'];
					$nData['type'] = 'Financial Report';
					$nData['title'] = 'Financial Report for '.$aData['period'].' has been successfully generated ';
					$nData['url'] = $config['URL'].'financialreport?new='.$projectTable->lastInsertValue;
					$notificationTable->insert($nData);
					
					$rowset3 = $staffTable->select(array("id='".$aData['user_id']."' "));
					$rowset3 = $rowset3->toArray();
					
					 
					$content ='
    
					<hr style="border-top: 1px solid #ddd;">

					<h2 style="color: #333;">Hi '.$rowset3[0]['first_name'].',</h2>

					<p>We hope this email finds you well. Your Financial statement report is ready for this month, '.$aData['period'].'.</p>

					<p>If you selected bank transfer as a payment method, you should receive your payment soon.</p>
					<br>
					<p style="text-align:center"><a href="'.$config['URL'].'public/uploads/'.$aData['pdf_file'].'" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;" download>Download Your Report</a></p>
					<br>
					<p>Please note that payments for the previous month have already been sent, and payment for the earnings in this statement is currently being processed.</p>
					
					<p>For any concerns regarding these statements, please contact <a href="mailto:support@primedigitalarena.in" style="color: #007bff; text-decoration: none;">support@primedigitalarena.in</a>.</p>

					<p>If you haven\'t filled in your payment information, please do so by filling it in your account profile <a href="'.$config['URL'].'/settings/bankinformation" style="color: #007bff; text-decoration: none;">here</a>.</p>
					
					<br><br><br>

					<p>Good luck!</p> 

					<p>Regards,<br>Prime Digital Arena</p>';
					
					
					$customObj->sendSmtpEmail($config,$rowset3[0]['email'],'Greetings! Your Financial Statement is Ready!',$content);
				
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
    $aColumns = array('id','period','report_type','user_id','royalty_amount','date_format(created_on,"%d-%m-%Y<br>%h:%i:%s")','status','csv_file','pdf_file');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_financial_report";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['financialreport']       = $arrDBInfo['username'];
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
	
	if($_SESSION['user_id'] != 0)
	{
		 $sWhere.=" AND  user_id ='".$_SESSION['user_id']."' ";
	}
	if($_GET['notification'] > 0)
	{
		$sWhere.=" AND  id= '".$_GET['notification']."' ";
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
			else if( $aColumns[$i] == 'user_id')
			{
				 $row[] = $this->getLabels($aRow['user_id']);
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
	public function getLabels($id)
	{
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select labels from tbl_staff where id ='".$id."' ";		        
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        
		 $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        
		 $rowset=$resultSet->toArray(); 
		 
		 if($rowset[0]['labels'] == '')
			 $rowset[0]['labels']=0;

		 $sql="select group_concat(name)as name from tbl_label where id in (".$rowset[0]['labels'].") ";		        
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        
		 $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        
		 $rowset=$resultSet->toArray(); 
		 
		 return $rowset[0]['name'];
	}
 public function getStaffAction()    
 {        
	 $sl = $this->getServiceLocator();        
	 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
	 $sql="select id,name from tbl_financial_report where order by name desc";		        
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