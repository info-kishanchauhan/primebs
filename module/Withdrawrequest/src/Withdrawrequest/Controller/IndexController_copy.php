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
	public function invoiceAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		
		$transactionTable = new TableGateway('tbl_transaction', $adapter);
		$rowset = $transactionTable->select(array("req_id='".$_REQUEST['id']."' "));
		$rowset = $rowset->toArray();
		$info  = $rowset[0];
		
		$reqTable = new TableGateway('tbl_withdraw_request', $adapter);
		$rowset = $reqTable->select(array("id='".$_REQUEST['id']."' "));
		$rowset = $rowset->toArray();
		$info['payment_method'] = $rowset[0]['payment_method'];
		$info['amount'] = $rowset[0]['amount'];
		
		$userTable = new TableGateway('tbl_staff', $adapter);
		$rowset = $userTable->select(array("id='".$info['user_id']."' "));
		$rowset = $rowset->toArray();
		$royalty_rate = $rowset[0]['royalty_rate_per'];
		$info['user_name'] = $rowset[0]['first_name'].' '.$rowset[0]['last_name'];
		$info['user_email'] = $rowset[0]['email'];
		$info['address'] = $rowset[0]['address'].' '.$rowset[0]['city'].' '.$rowset[0]['isoCountry'];
		$info['charge_rate'] = '0%';
		$info['total'] = $info['amount'];
		if($royalty_rate > 0)
		{
			$info['total'] = number_format((100 * $info['amount'] / $royalty_rate),'2','.','');
			$info['charge_rate'] = 100 - $royalty_rate.'%';
		}
		$html = <<<EOD
			<style>
				.header {
					text-align: center;
					font-size: 68px;
					font-weight: bold;
					line-height:32px;
				}
				.info-table {
					width: 100%;
					font-size: 12px;
					margin-top: 150px;
				}
				.info-table td {
					padding: 5px;
					vertical-align: top;
				}
				.invoice-box {
					background-color: #38B29C;
					color: #ffffff;
					font-size: 12px;
					padding: 20px;
					border-radius: 5px;
				}
				.items-table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 100px;
					font-size: 12px;
				}
				.items-table th, .items-table td {
					padding: 5px;
					text-align: left;
				}
				.items-table th {
					background-color: #38B29C;
					color: white;
					
				}
				.totals-table {
					width: 100%;
					font-size: 12px;
				}
				.totals-table td {
				   
					padding: 5px;
				}
				.footer {
					font-size: 10px;
					text-align: left;
				}
				a{
					color:#000;
					font-weight:bold;
					text-decoration:none;
				}
			</style>

			<!-- Header -->
			<div class="header"><br><br>Invoice<br><br></div>


			<table class="info-table" style="line-height:22px;">
				<tr>
					<td width="70%">
						<strong>Invoice No:</strong> {$info['invoice_no']}<br><strong>Payment Info:</strong><br>Name: {$info['user_name']}<br>Payment: {$info['payment_method']}<br>Email: {$info['user_email']}<br>Address: {$info['address']}
					</td>
					<td width="30%" style="line-height:18px;">
							<strong>Invoice Date:</strong> 2024-11-28<br><strong class="invoice-box" >&nbsp;Prime Digital Arena&nbsp;</strong> <br>Thane West, 400165<br>Maharashtra, India<br><strong>TMR:</strong> 4248131/35<br>
					</td>
				</tr>
			</table>
			<br><br>
			<!-- Items Table -->
			<table class="items-table" style="line-height:22px;">
				<thead>
					<tr>
						<th class="invoice-box" width="65%">Description</th>
						<th class="invoice-box" width="17%">Amount</th>
						<th class="invoice-box" width="18%">Total</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td  width="65%">{$info['description']}</td>
						<td width="17%">{$info['total']} €</td>
						<td width="18%">{$info['total']} €</td>
					</tr>
					
				</tbody>
			</table>
			<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
			<table class="info-table" style="line-height:20px">
				<tr>
					<td width="67%">
					   <div class="footer">For any requests, please contact your local support team: 
							<a href="mailto:support@primedigitalarena.in">support@primedigitalarena.in</a><br><br>
							Very best regards,<br>
							Royalty Accounting Team<br>
							<br>
							<br>
							<br>
							
							<table class="totals-table" style="line-height:25px;" width="60%">
							<tr>
							<td><br><img src="public/img/prime-digital-arena.png" width="50" style="float:left;"></td><td>  <img src="public/img/believe-distribution-services.png" width="90"> </td><td> <img src="public/img/the-orchard.png" width="80"></td>
							</tr>
							</table>
							
						</div>
					</td> 
				   <td width="33%">
						<table class="totals-table" style="line-height:25px;">
							<tr>
								<td><br><strong>Gross Amount:</strong></td><td align="right">{$info['total']} €</td>
							</tr>
							<tr>
								<td><strong>Charge Rate:</strong></td><td align="right">-{$info['charge_rate']}</td>
							</tr>
							<tr>
								<td><strong>Net Amount:</strong></td><td align="right">{$info['amount']} €</td>
							</tr>
							<tr><td  colspan="2"></td></tr>
							<tr><td  colspan="2"></td></tr>
							
							<tr>
								<td colspan="2"><a href="https://www.primedigitalarena.in">www.primedigitalarena.in</a></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- Totals -->


			<!-- Footer -->

			EOD;

		$viewModel= new ViewModel(array(
		
			'printDetails' => $html,
			'FILE_NAME' => 	'Invoice'
		));	

		$viewModel->setTerminal(true);	
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
			
			$config = $this->getServiceLocator()->get('config');
			$URL = $config['URL'];
		
			$iMasterID = $request->getPost("KEY_ID");
			$aData = array();
			$aData['status'] = $request->getPost("pAction");
			$aData['approved_date'] = date('Y-m-d');
			$projectTable->update($aData,array("id='".$iMasterID."'"));
			
			$rowset8 = $projectTable->select(array("id='".$iMasterID."' "));
			$rowset8 = $rowset8->toArray();
			
			$staffTable = new TableGateway('tbl_staff', $adapter);
			$rowset7 = $staffTable->select(array("id='".$rowset8[0]['user_id']."' "));
			$rowset7 = $rowset7->toArray();
			$user = $rowset7[0];
			
			if($aData['status'] == 'Accepted')
			{
					$content ='
					
					<h2 style="color: #333;">Dear '.$user['company_name'].',</h2>

					<p>Your payment request '.$rowset8[0]['payment_id'].' for an amount of €'.$rowset8[0]['amount'].' on '.date('d-m-Y',strtotime($rowset8[0]['created_on'])).' has been received.</p>
					
					<p>Our team will review and confirm your request before sending it to the payout provider on '.date('d-m-Y').'.</p>
					<div style="text-align: left; margin-top: 15px;">
						<a href="'.$URL.'payments" style="min-width:120px;background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 30px; display: inline-block;text-align:center;">Track your request</a>
					</div>
					<br>
					<p>Once processed, you will receive further updates regarding your payout status.</p>
					<p>Best regards,<br>
					<b>Prime Digital Arena</b></p>';		 
			
					$customObj->sendSmtpEmail($config,$user['email'],'Payment Request Accepted – Processing Underway',$content,$user['label_manager_email']);
			}
			if($aData['status'] == 'Rejected' || $aData['status'] == 'Declined')
			{
				
				$tData = array();
				$tData['transaction_type'] =  'Rejected';
				$transactionTable->update($tData,array("req_id = '".$iMasterID."' "));
				
				
					$content ='
					
					<h2 style="color: #333;">Dear '.$user['company_name'].',</h2>

					<p>We regret to inform you that your most recent payment request has been rejected.</p>
					
					<p>We were unable to successfully process your payment.</p>
					
					<p>To proceed with your Prime Digital Arena payout '.$rowset8[0]['payment_id'].', please contact your Label Manager for further assistance.</p>
					
					<p>We will retry the payment once the issue has been resolved. If the payment is not completed within the next 15 days, you will need to submit a new request in the next payment cycle.</p>
					<p>Best regards,<br>
					<b>Prime Digital Arena</b></p>';		 
			
					$customObj->sendSmtpEmail($config,$user['email'],'Payment Request Rejected – Action Required',$content,$user['label_manager_email']);
			}
			
			if($aData['status'] == 'Success')
			{
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$labels = $customObj->getAssignedLabelsUser($rowset[0]['user_id']);
				
				$this->executeUpdateQuery("UPDATE tbl_analytics AS A JOIN tbl_release AS R ON A.release_id = R.id SET A.payment_status = 'Paid' WHERE R.labels IN (".$labels.") AND A.payment_status='Unpaid' AND A.import_payment_status='Unpaid' AND sales_month <='".$rowset[0]['month_year']."' ");
				
				$this->executeUpdateQuery("UPDATE tbl_financial_report set payment_status='Paid' where requested='2' and user_id='".$rowset[0]['user_id']."' and sales_month <='".$rowset[0]['month_year']."' ");
				
				/*$rowset2 = $transactionTable->select(array("user_id='".$rowset[0]['user_id']."' order by id desc limit 1"));
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
				$settingsTable->update($sData);*/
				
				$tData = array();
				$tData['transaction_type'] =  'Payment';
				$tData['balance'] =  $rowset2[0]['balance'] - $rowset[0]['amount'];
				if($tData['balance'] < 0)
					$tData['balance'] = 0;
				$transactionTable->update($tData,array("req_id = '".$rowset[0]['id']."' "));
				
				$content ='
					
					<h2 style="color: #333;">Dear '.$user['company_name'].',</h2>

					<p>Your payment request '.$rowset8[0]['payment_id'].' for an amount of €'.$rowset8[0]['amount'].' on '.date('d-m-Y',strtotime($rowset8[0]['created_on'])).' has been successfully processed.</p>
					
					<p>The payment has been sent to the payout provider, and you should receive it shortly.  </p>
					
					<p>You can check your payment status by logging into your dashboard.</p>
					<div style="text-align: left; margin-top: 15px;">
						<a href="'.$URL.'login" style="min-width:120px;background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 30px; display: inline-block;text-align:center;">Login to Dashboard</a>
					</div><br>
					<p>If you have any questions or require further assistance, please feel free to contact us.  </p>
					<p>Best regards,<br>
					<b>Prime Digital Arena</b></p>';		 
			
					$customObj->sendSmtpEmail($config,$user['email'],'Payment Successfully Processed',$content,$user['label_manager_email']);
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