<?php
namespace Payments\Controller;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Datetime;
use DateInterval;
use DatePeriod;
class IndexController extends AbstractActionController
{
    public  $dbAdapter1;
    public function init()
    {
    }
	public function historyAction()
    {
    }
	public function invoiceAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$transactionTable = new TableGateway('tbl_transaction', $adapter);
		$rowset = $transactionTable->select(array("id='".$_REQUEST['id']."' "));
		$rowset = $rowset->toArray();
		$info  = $rowset[0];
		
		$reqTable = new TableGateway('tbl_withdraw_request', $adapter);
		$rowset = $reqTable->select(array("id='".$info['req_id']."' "));
		$rowset = $rowset->toArray();
		$info['payment_method'] = $rowset[0]['payment_method'];
		
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
	public function indexAction()
    {
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$reqTable = new TableGateway('tbl_withdraw_request', $adapter);
		$staffTable = new TableGateway('tbl_staff', $adapter);
		$rowset = $staffTable->select(array("id='".$_SESSION['user_id']."' "));
		$rowset = $rowset->toArray();
		$user = $rowset[0];
		
		if($user['payment_method'] == '0')
			$user['payment_method']='';
		
		$revenue = $customObj->getBalanceAmount($user,$adapter);
		$revenue = $revenue['amount'];
		
		$rowset3 = $reqTable->select(array("user_id='".$_SESSION['user_id']."' order by id desc limit 1"));
		$rowset3 = $rowset3->toArray();
		$request_type = 'New';
		
		if(count($rowset3) > 0)
		{
			if($rowset3[0]['status'] != 'Success' && $rowset3[0]['status'] != 'Rejected' && $rowset3[0]['status'] != 'Declined' )
				$request_type = 'No';
			if($rowset3[0]['status'] == 'Accepted' )
				$request_type = 'Sent';
			if($rowset3[0]['status'] == 'On Hold' )
				$request_type = 'On Hold';
		}
		
		$ALLOW_WITHDRAW ='No';
		
		$WITHDRAW_BALANCE = $customObj->getWithdrawBalanceAmount($user,$adapter);
		
		
		
		if( (date('d') <= 15 || date('mY') == '022025' )&& $WITHDRAW_BALANCE['amount'] >= 100)
		{
			$ALLOW_WITHDRAW = 'Yes';
		}
		
		$payoutTable = new TableGateway('tbl_user_payout', $adapter);
		$payoutRowset = $payoutTable->select(['user_id' => $_SESSION['user_id']]);
		$payoutRowset = $payoutRowset->toArray();
		if(count($payoutRowset)>0){}
		else
		{
			$ALLOW_WITHDRAW = 'NEED BANK INFO';
		}
		
		$viewModel= new ViewModel(array(
			
			'USERNAME' => strtoupper($user['first_name'].' '.$user['last_name']),
			'PAYMENT_METHOD' => $user['payment_method'],
			'BALANCE' => $revenue,
			'REQ_TYPE' => $request_type,
			'req_amount' => $rowset3[0]['amount'],
			'requested_date' => date('d/m/Y',strtotime($rowset3[0]['created_on'])),
			'ALLOW_WITHDRAW' => $ALLOW_WITHDRAW,
        ));
		return $viewModel;
    }
	public function sendPaymentReqAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$config = $this->getServiceLocator()->get('config');
		$URL = $config['URL'];
		
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $reqTable = new TableGateway('tbl_withdraw_request', $adapter);
			$transactionTable = new TableGateway('tbl_transaction', $adapter);
			$settingsTable = new TableGateway('tbl_settings', $adapter);
            $rowset3 = $reqTable->select(array("user_id='".$_SESSION['user_id']."' order by id desc limit 1"));
            $rowset3 = $rowset3->toArray();
			
			if(count($rowset3) > 0)
			{
				if($rowset3[0]['status'] != 'Success' && $rowset3[0]['status'] != 'Rejected' && $rowset3[0]['status'] != 'Declined' )
				{
					$result['DBStatus'] = 'EXIST';
					$result = json_encode($result);
					echo $result;
					exit;
				}
			}
			$staffTable = new TableGateway('tbl_staff', $adapter);
			$rowset = $staffTable->select(array("id='".$_SESSION['user_id']."' "));
			$rowset = $rowset->toArray();
			$user = $rowset[0];
			if($user['payment_method'] == '0')
				$user['payment_method']='';
			
			$amount_info = $customObj->getWithdrawBalanceAmount($user,$adapter);
			$amount = $amount_info['amount'];
			
			
			if($amount < 100)
			{
				$result['DBStatus'] = 'NOT_ALLOW';
				$result = json_encode($result);
				echo $result;
				exit;
			}
			
            $aData = array();
			$aData['user_id'] = $user['id'];
			$aData['amount'] = $amount;
			$aData['month_year'] = $amount_info['month_year'];
			$aData['payment_method'] = $user['payment_method'];
			$aData['created_on'] = date('Y-m-d H:i:s');
            $reqTable->insert($aData);
			
			$r_id = $reqTable->lastInsertValue;
			$uData = array();
			$uData['payment_id'] = 'PDA'.sprintf('%03d',$r_id).'ALD'.date('Y');
			$aData['description'] = str_replace('Payment','Payment '.$uData['payment_id'],$amount_info['description']);
			$reqTable->update($uData,array("id='".$r_id."'"));
			
			
			$rowset2 = $transactionTable->select(array("user_id='".$user['id']."' order by id desc limit 1"));
			$rowset2 = $rowset2->toArray();
			
			$rowset3 = $settingsTable->select();
			$rowset3 = $rowset3->toArray();
			
			$invoice_no = $rowset3[0]['last_invoice_no']+1;
			$invoice_no = 'PDA'.date('y').sprintf('%03d',$invoice_no);
			
			
			$tData = array();
			$tData['user_id'] = $user['id'];
			$tData['req_id'] = $r_id;
			$tData['invoice_no'] = $invoice_no;
			$tData['transaction_date'] =  date('Y-m-d');
			$tData['transaction_type'] =  'Pending Payment';
			$tData['amount'] = $amount;
			$tData['description'] =  $aData['description'];
			$tData['balance'] =  $rowset2[0]['balance'];
			if($tData['balance'] < 0)
				$tData['balance'] = 0;
			$transactionTable->insert($tData);
			
			$sData = array();
			$sData['last_invoice_no'] = $rowset3[0]['last_invoice_no']+1;
			$settingsTable->update($sData);
			
			$content ='
					
					<h2 style="color: #333;">Dear Admin,</h2>

					<p>'.$user['company_name'].' has requested a payment for an amount of €'.$amount.' on '.date('d-m-Y').'.  </p>
					<br>
					<h3><b>Payment Details</b></h3>
					<p><strong>Payment ID:</strong> '.$uData['payment_id'].'</p>
					<p><strong>Requested Amount:</strong> €'.$amount.'</p>
					<p><strong>Requested On:</strong> '.date('d-m-Y').'</p>
					
					<br>
					<p>Please review and process the request accordingly. </p>
					<div style="text-align: left; margin-top: 15px;">
						<a href="'.$URL.'withdrawrequest" style="min-width:120px;background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 30px; display: inline-block;text-align:center;">Review Payment Request</a>
					</div>
					<br>
					<p>If any further information is required, please let us know.  </p>
					<p>Best regards,<br>
					<b>Prime Digital Arena</b></p>';		 
			
			
			$rowset3 = $staffTable->select(array("id='0' "));
			$rowset3 = $rowset3->toArray();
			
			$customObj->sendSmtpEmail($config,$rowset3[0]['email'],'Payment Request Submission',$content);
			
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
    $aColumns = array('id','description','transaction_type','description','amount','balance');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "tbl_transaction";
    $config = $this->getServiceLocator()->get('config');
    $arrDBInfo=$config['db'];
    /* Database connection information */
   
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
	
	 $sWhere.=" AND  user_id='".$_SESSION['user_id']."' ";
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
			else if ( $aColumns[$i] == "description" )
            {
				$row[] = '<span class="bold_number">'.$aRow['description'].' </span>';
			}
			else if ( $aColumns[$i] == "amount" )
            {
				if($aRow['transaction_type'] != 'Royalties')
					$row[] = '<span class="bold_number">-'.number_format($aRow['amount'],'2','.','').' €</span>';
				else
					$row[] = '<span class="bold_number credit">+'.number_format($aRow['amount'],'2','.','').' €</span>';
			}
			else if ( $aColumns[$i] == "balance" )
            {
				if($aRow['transaction_type'] == 'Pending Payment')
				{	
					$bal = $aRow['balance'] -  $aRow['amount'];
					$row[] = '<span class="">'.number_format($bal,'2','.','').' €</span>';
				}
				else
					$row[] = '<span class="">'.number_format($aRow['balance'],'2','.','').' €</span>';
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

}//End Class