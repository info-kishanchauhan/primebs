<?php
namespace Withdrawrequest\Controller;
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
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$reqTable = new TableGateway('tbl_withdraw_request', $adapter);
		$rowset = $reqTable->select();
		$rowset = $rowset->toArray();
		
		$info = array();
		$info['request'] = 0;
		$info['onhold'] = 0;
		$info['paid'] = 0;
		$info['rejected'] = 0;
		foreach($rowset as $row)
		{
			if($row['status'] == 'Pending' || $row['status'] == 'Accepted')
				$info['request']++;
			if($row['status'] == 'Rejected' || $row['status'] == 'Declined')
				$info['rejected']++;
			if($row['status'] == 'On Hold')
				$info['onhold']++;
			if($row['status'] == 'Success')
				$info['paid']++;
		}
		
		
		$viewModel= new ViewModel(array(
			
			'INFO' => $info,
			
        ));
		return $viewModel;
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
	public function list3Action()
    {
        echo $this->fnGrid3();
        exit;
    }
	public function list4Action()
    {
        echo $this->fnGrid4();
        exit;
    }
	public function changeStatusAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_withdraw_request', $adapter);
            $transactionTable = new TableGateway('tbl_transaction', $adapter);
			$settingsTable = new TableGateway('tbl_settings', $adapter);
			
			$iMasterID = $request->getPost("KEY_ID");
			$aData = array();
			$aData['status'] = $request->getPost("pAction");
			$projectTable->update($aData,array("id='".$iMasterID."'"));
			
			if($aData['status'] == 'Success')
			{
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$labels = $customObj->getUserLabels($rowset[0]['user_id']);
				
				$this->executeUpdateQuery("UPDATE tbl_analytics AS A JOIN tbl_release AS R ON A.release_id = R.id SET A.payment_status = 'Paid' WHERE R.labels IN (".$labels.") AND A.payment_status='Unpaid' AND A.import_payment_status='Unpaid' AND sales_month <='".$rowset[0]['month_year']."' ");
				
				$rowset2 = $transactionTable->select(array("user_id='".$rowset[0]['user_id']."' order by id desc limit 1"));
				$rowset2 = $rowset2->toArray();
				
				$rowset3 = $settingsTable->select();
				$rowset3 = $rowset3->toArray();
				
				$invoice_no = $rowset3[0]['last_invoice_no']+1;
				$invoice_no = sprintf('%06d',$invoice_no);
				
				$tData = array();
				$tData['user_id'] =  $rowset[0]['user_id'];
				$tData['req_id'] = $rowset[0]['id'];
				$tData['invoice_no'] = $invoice_no;
				$tData['transaction_date'] =  date('Y-m-d');
				$tData['transaction_type'] =  'Payment';
				$tData['amount'] =  $rowset[0]['amount'];
				$tData['description'] =  $rowset[0]['description'];
				$tData['balance'] =  $rowset2[0]['balance'] - $tData['amount'];
				if($tData['balance'] < 0)
					$tData['balance'] = 0;
				$transactionTable->insert($tData);
				
				$sData = array();
				$sData['last_invoice_no'] = $rowset3[0]['last_invoice_no']+1;
				$settingsTable->update($sData);
			}
			
			$result['DBStatus'] = 'OK';
			$result = json_encode($result);
			echo $result;
			exit;
            
        }
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
    $aColumns = array('id','company_name','label_name','date_format(month_year,"%M %Y")','amount','status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_withdraw_request";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['withdrawrequest']       = $arrDBInfo['username'];
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
	
	
	
	 $sWhere.=" AND  status in ('Pending','Accepted') ";
	
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
			else if ( $aColumns[$i] == "amount" )
            {
				$row[] = '<p class="bold_number">'.number_format($aRow['amount'],'2','.','').' €</p>';
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
    $aColumns = array('id','company_name','label_name','date_format(month_year,"%M %Y")','amount','status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_withdraw_request";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['withdrawrequest']       = $arrDBInfo['username'];
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
	
	
	 $sWhere.=" AND  status='On Hold' ";
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
			else if ( $aColumns[$i] == "amount" )
            {
				$row[] = '<p class="bold_number">'.number_format($aRow['amount'],'2','.','').' €</p>';
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

public function fnGrid3()
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
    $aColumns = array('id','company_name','label_name','date_format(month_year,"%M %Y")','amount','status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_withdraw_request";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['withdrawrequest']       = $arrDBInfo['username'];
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
	
	
	$sWhere.=" AND  status='Success' ";
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
			
			else if ( $aColumns[$i] == "amount" )
            {
				$row[] = '<p class="bold_number">'.number_format($aRow['amount'],'2','.','').' €</p>';
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

public function fnGrid4()
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
    $aColumns = array('id','company_name','label_name','date_format(month_year,"%M %Y")','amount','status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_withdraw_request";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
    $gaSql['withdrawrequest']       = $arrDBInfo['username'];
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
	
	$sWhere.=" AND  status in ('Rejected','Declined') ";
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
			
			else if ( $aColumns[$i] == "amount" )
            {
				$row[] = '<p class="bold_number">'.number_format($aRow['amount'],'2','.','').' €</p>';
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

		 $sql="select name from tbl_label where id in (".$rowset[0]['labels'].") ";		        
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        
		 $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        
		 $rowset=$resultSet->toArray(); 
		 
		 $label=array();
		 foreach($rowset as $row)
		 {
			 $label[]=$row['name'];
		 }
		 return implode(' <br> ',$label);
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
	 public function executeUpdateQuery($sql)
	 {
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();    
	 }
 
}//End Class