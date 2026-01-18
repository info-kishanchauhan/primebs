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
    $sl = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $userId = $_SESSION['user_id'] ?? 0;
    $staffTable = new TableGateway('tbl_staff', $adapter);
    $userRow = $staffTable->select(['id' => $userId])->current();
      
       // Detect method based on payment_system field
    $paymentSystemRaw = strtolower(trim($userRow['payment_system'] ?? ''));

    // Optional: get payout method in lowercase
    $methodRaw = strtolower(trim($userRow['payment_method'] ?? ''));
    if (strpos($methodRaw, 'payoneer') !== false) {
        $method = 'payoneer';
    } elseif (strpos($methodRaw, 'paypal') !== false) {
        $method = 'paypal';
    } elseif (strpos($methodRaw, 'bank') !== false || strpos($methodRaw, 'transfer') !== false) {
        $method = 'bank';
    } else {
        $method = '';
    }

    return new ViewModel([
        'user' => $userRow,
        'method' => $method,
      'payment_system' => $userRow['payment_system'] ?? '—'
      
    ]);
}
public function autostatementsAction()
{
    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    $userId     = $_SESSION['user_id'] ?? 0;
    $staffTable = new TableGateway('tbl_staff', $adapter);
    $userRow    = $staffTable->select(['id' => $userId])->current();

    // payout method detect (same logic as indexAction)
    $methodRaw = strtolower(trim($userRow['payment_method'] ?? ''));
    if (strpos($methodRaw, 'payoneer') !== false) {
        $method = 'payoneer';
    } elseif (strpos($methodRaw, 'paypal') !== false) {
        $method = 'paypal';
    } elseif (strpos($methodRaw, 'bank') !== false || strpos($methodRaw, 'transfer') !== false) {
        $method = 'bank';
    } else {
        $method = '';
    }

    return new ViewModel([
        'user'           => $userRow,
        'method'         => $method,
        'payment_system' => $userRow['payment_system'] ?? '—',
    ]);
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
public function invoiceAction()
{
    $customObj = $this->CustomPlugin();
    $request   = $this->getRequest();

    $sl      = $this->getServiceLocator();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    // 1) ID from query
    $id = (int)$this->params()->fromQuery('id', 0);
    if ($id <= 0) {
        return $this->notFoundAction();
    }

    // 2) Table name
    $tableName = 'tbl_financial_report';

    $reportTable = new \Zend\Db\TableGateway\TableGateway($tableName, $adapter);
    $rowset      = $reportTable->select(['id' => $id]);
    $row         = $rowset->current();

    if (!$row) {
        return $this->notFoundAction();
    }

    $info = (array) $row;

    // Expected columns: id, user_id, period, report_type, royalty_amount, payment_status, created_on, sales_month...
    $info['invoice_no']   = 'PDA' . str_pad($info['id'], 6, '0', STR_PAD_LEFT);
    $info['period_label'] = $info['period']      ?? '';
    $info['report_type']  = $info['report_type'] ?? '';
    $info['amount']       = (float)($info['royalty_amount'] ?? 0);

    if (!empty($info['generation_date'])) {
        $info['invoice_date'] = substr($info['generation_date'], 0, 10);
    } elseif (!empty($info['created_on'])) {
        $info['invoice_date'] = substr($info['created_on'], 0, 10);
    } else {
        $info['invoice_date'] = date('Y-m-d');
    }

    $info['description'] = sprintf(
        'Royalty Statement – %s',
        $info['period_label'],
        $info['report_type']
    );

    // 3) User info (tbl_staff)
    $userTable = new \Zend\Db\TableGateway\TableGateway('tbl_staff', $adapter);
    $uRow      = $userTable->select(['id' => $info['user_id']])->current();

    $royalty_rate = 0;
    $u = []; // safe default

    if ($uRow) {
        $u = (array)$uRow;

        $royalty_rate           = (float)($u['royalty_rate_per'] ?? 0);
        $info['user_name']      = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        $info['user_email']     = $u['email'] ?? '';
        $info['address']        = trim(($u['address'] ?? '') . ' ' . ($u['city'] ?? '') . ' ' . ($u['isoCountry'] ?? ''));
        // NOTE: currency yahan set nahi kar rahe, neeche detect hoga
        $info['payment_method'] = $u['payment_method'] ?? ($u['payment_system'] ?? 'Royalty Statement');
    } else {
        $info['user_name']      = 'Client Name';
        $info['user_email']     = '';
        $info['address']        = '';
        // currency bhi neeche detect hogi
        $info['payment_method'] = 'Royalty Statement';
    }

    // 3.1) CURRENCY DETECTION FROM TEXT
    // Rule:
    //  - agar report + user ke text me kahin bhi "inr" (case-insensitive) mil gaya -> currency = "INR"
    //  - warna: user ki currency (agar hai) else default "€"

    $allText = '';

    // saare report-info scalar values concat
    foreach ($info as $v) {
        if (is_scalar($v)) {
            $allText .= ' ' . $v;
        }
    }

    // user row ke scalar values bhi concat
    if ($uRow) {
        foreach ($u as $v) {
            if (is_scalar($v)) {
                $allText .= ' ' . $v;
            }
        }
    }

    $allTextLower = strtolower($allText);

    if (strpos($allTextLower, 'inr') !== false) {
        // kahin bhi "inr" likha hai -> force INR
        $info['currency'] = 'INR';
    } else {
        // otherwise: use user.currency if available, else €
        $userCurrency = '';
        if ($uRow && !empty($u['currency'])) {
            $userCurrency = trim((string)$u['currency']);
        }

        if ($userCurrency === '') {
            $userCurrency = '€';
        }
        $info['currency'] = $userCurrency;
    }

    // 4) Gross / charge calculation (tumhara existing logic)
    $info['charge_rate'] = '0%';
    $info['total']       = $info['amount'];

    if ($royalty_rate > 0) {
        $info['total']       = number_format((100 * $info['amount'] / $royalty_rate), 2, '.', '');
        $info['charge_rate'] = (100 - $royalty_rate) . '%';
    }

    // 5) HTML invoice
    $html = <<<EOD
<style>
    .header {
        text-align: center;
        font-size: 45px;
        font-weight: bold;
        line-height:32px;
    }
    .info-table {
        width: 100%;
        font-size: 12px;
        margin-top: 80px;
    }
    .info-table td {
        padding: 5px;
        vertical-align: top;
    }
    .invoice-box {
        background-color: #38B29C;
        color: #ffffff;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 4px;
        display:inline-block;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 40px;
        font-size: 12px;
    }
    .items-table th, .items-table td {
        padding: 6px;
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

<div class="header"><br><br>Invoice<br><br></div>

<table class="info-table" style="line-height:18px;">
    <tr>
        <td width="70%">
            <strong>Invoice No:</strong> {$info['invoice_no']}<br>
            <strong>Period:</strong> {$info['period_label']}<br>
            <strong>Report Type:</strong> {$info['report_type']}<br><br>
            <strong>Payment Info:</strong><br>
            Name: {$info['user_name']}<br>
            Payment: {$info['payment_method']}<br>
            Email: {$info['user_email']}<br>
            Address: {$info['address']}
        </td>
        <td width="30%" style="line-height:16px;">
            <strong>Invoice Date:</strong> {$info['invoice_date']}<br><br>
            <span class="invoice-box">Prime Digital Arena</span><br>
            Thane West, 400615<br>
            Maharashtra, India<br>
            <strong>TMR:</strong> 4248131/35<br>
        </td>
    </tr>
</table>
<br><br>
<table class="items-table" style="line-height:18px;">
    <thead>
        <tr>
            <th class="invoice-box" width="65%">Description</th>
            <th class="invoice-box" width="17%">Gross</th>
            <th class="invoice-box" width="18%">Net Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="65%">{$info['description']}</td>
            <td width="17%">{$info['amount']} {$info['currency']}</td>
            <td width="18%">{$info['amount']} {$info['currency']}</td>
        </tr>
    </tbody>
</table>

<br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<table class="info-table" style="line-height:18px;">
    <tr>
        <td width="67%">
           <div class="footer">
                For any requests, please contact your local support team:<br>
                <a href="mailto:support@primedigitalarena.in">support@primedigitalarena.in</a><br><br>
                Very best regards,<br>
                Royalty Accounting Team<br><br><br><br>

                <table class="totals-table" style="line-height:20px;" width="60%">
                    <tr>
                        <td><br><img src="public/img/prime-digital-arena.png" width="50" style="float:left;"></td>
                        <td><img src="public/img/believe-distribution-services.png" width="90"></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </td>
       <td width="33%"><br><br>
            <table class="totals-table" style="line-height:20px;">
                <tr>
                    <td><br><strong>Gross:</strong></td>
                    <td align="right">{$info['total']} {$info['currency']}</td>
                </tr>
                <tr>
                    <td><strong>Fees:</strong></td>
                    <td align="right">-{$info['charge_rate']}</td>
                </tr>
                <tr>
                    <td><strong>Net Total:</strong></td>
                    <td align="right">{$info['amount']} {$info['currency']}</td>
                </tr>
                <tr><td colspan="2"></td></tr>
                <tr><td colspan="2"></td></tr>
                <tr>
                    <td colspan="2"><a href="https://www.primedigitalarena.com">www.primedigitalarena.com</a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
EOD;

    $viewModel = new \Zend\View\Model\ViewModel([
        'printDetails' => $html,
        'FILE_NAME'    => 'Invoice-' . $info['invoice_no'],
    ]);
    $viewModel->setTerminal(true);
    return $viewModel;
}



	public function usercsvreportAction()
	{
		$customObj = $this->CustomPlugin();
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$projectTable = new TableGateway('tbl_financial_report', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['id']."' "));
		$rowset = $rowset->toArray();
		
		$user_id = $rowset[0]['user_id'];
		$sales_month = $rowset[0]['sales_month'];
		
		$labels = $customObj->getAssignedLabelsUser($user_id);
		
		$data= "Reporting Month, Sales Month, Platform, Country / Region,Label Name, Artist Name, Release Title, Track Title, UPC,ISRC,Release Type, Stream, Creation, Client Payment Currency,Net Revenue";
		$data.="\n";
		
		
		$projectTable = new TableGateway('view_analytics', $adapter);
		$rowset = $projectTable->select("sales_month='".$sales_month."' and import_payment_status !='Hold' and label_id in (".$labels.")");
		$rowset = $rowset->toArray();
		
		$user_rate = $this->getUserRate($user_id);
		
		foreach($rowset as $row)
		{
			$row['revenue'] = ($row['revenue'] * $user_rate / 100);
			 
			$s_month =  date('F Y',strtotime($row['sales_month']));
			$data .= implode(',', [
					$this->escapeCsvValue($s_month),
					$this->escapeCsvValue($s_month),
					$this->escapeCsvValue($row['store']),
					$this->escapeCsvValue($row['label_name']),
					$this->escapeCsvValue($row['releaseArtist']),
					$this->escapeCsvValue($row['title']),
					$this->escapeCsvValue($row['title']),
					$this->escapeCsvValue($row['upc']),
					$this->escapeCsvValue($row['track_isrc']),
					$this->escapeCsvValue('Music Release'),
					$this->escapeCsvValue($rows['streams']),
					$this->escapeCsvValue($row['creation']),
					$this->escapeCsvValue('EUR'),
					$this->escapeCsvValue($row['revenue']),
				]) . "\n";
				
				
		
		}
		
		$file = date('Ymd').".csv";
       	header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$file");	
		echo $data;
		exit;
	}
	public function escapeCsvValue($value) {
		if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
			$value = str_replace('"', '""', $value);
			return '"' . $value . '"';
		}
		return $value;
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
			{
				$period = $record['period'];
				if(strstr($period," To "))
				{
					$period = explode(" To ",$period);
					$record['period_from'] = $period[0];
					$record['period_to'] = $period[1];
				}
				else
				{
					$record['period_from'] = $period;
					$record['period_to'] = $period;
				}
				
				
                $recs[] = $record;
				
			}
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
			
			
			$rData = array();
			$rData['user_id'] = $_SESSION['user_id'];
			$rData['requested'] = 1;			
			$rData['period'] = $period;		
			$rData['report_type'] = $_POST['summaryReport'];		
			$rData['status'] = 'processing';		
			$rData['created_on'] = date('Y-m-d H:i:s');	
			$rData['gen_json'] = json_encode($aData);	
			
			$projectTable->insert($rData);
			$id= $projectTable->lastInsertValue;
			
			
			$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'public/cron_file/generate_report.php '.$id);
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
	public function changeStatusAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_user_hold_report', $adapter);
			$analyticsTable = new TableGateway('tbl_analytics', $adapter);
			$financialTable = new TableGateway('tbl_financial_report', $adapter);
           
			$iMasterID = $request->getPost("KEY_ID");
			$status = $request->getPost("status");
			
			$rowset = $projectTable->select(array('id' => $iMasterID));
			$rowset = $rowset->toArray();
			
			if($rowset[0]['label_id'] != '0')
			{
				$uData = array();
				$uData['import_payment_status'] = $status;
				$analyticsTable->update($uData,array("sales_month ='".$rowset[0]['sales_month']."' and label_id='".$rowset[0]['label_id']."' "));
				
				$user_id = $customObj->getAssignedUserforLabel($rowset[0]['label_id']);
				if($user_id > 0 && $_SESSION['STAFFUSER'] == '0')
				{
					$rowset2 = $financialTable->select(array("user_id='".$user_id."' and sales_month='".$rowset[0]['sales_month']."' and requested='2'"));
					$rowset2 = $rowset2->toArray();
					
					if(count($rowset2) > 2)
					{
						$rowset23 = $this->executeQuery("select sum(revenue) as royalty_amount from tbl_analytics where sales_month ='".$rowset[0]['sales_month']."' and label_id='".$rowset[0]['label_id']."' ");
						$royalty_amount= $rowset23[0]['royalty_amount'];
						
						$user_rate = $this->getUserRate($user_id);
						$royalty_amount= number_format(($royalty_amount * $user_rate / 100),2,'0','');
						
						$rData = array();
						$rData['payment_status'] = $status;
						$rData['royalty_amount'] = $rowset2[0]['royalty_amount'] + $royalty_amount;
						$financialTable->update($rData);
					}
					else
					{
						$period = date('M Y',strtotime($rowset[0]['sales_month']));
					
						$rData = array();
						$rData['user_id'] = $user_id;
						$rData['requested'] = '2';
						$rData['period'] = $period;
						$rData['sales_month'] = $rowset[0]['sales_month'];
						$rData['report_type'] = 'Full Report';
						$rData['status'] = 'success';
						$rData['payment_status'] = $status;
						$rData['created_on'] = date('Y-m-d H:i:s');
						
						$rowset23 = $this->executeQuery("select sum(revenue) as royalty_amount from tbl_analytics where sales_month ='".$rowset[0]['sales_month']."' and label_id='".$rowset[0]['label_id']."' ");
						$royalty_amount= $rowset23[0]['royalty_amount'];
						
						$user_rate = $this->getUserRate($user_id);
						$rData['royalty_amount'] = number_format(($royalty_amount * $user_rate / 100),2,'0','');
						$financialTable->insert($rData);
					}
				}
				
				if($status == 'Unpaid')
				{
					$customObj->saveTransaction($rowset[0]['sales_month'],$adapter);
				}
			}
			
			$aData = array();
			$aData['status'] = $status;
			$projectTable->update($aData,array("id=" . $iMasterID));
			
			
			$result['DBStatus'] = 'OK';
			$result = json_encode($result);
			echo $result;
			exit;
            
        }
	}
	public function executeQuery($sql)
	{
		$sl = $this->getServiceLocator();        
		$adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
		
		
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();  
		
		return $rowset;
	}
	 public function getUserRate($user_id)
	 {
		$customObj = $this->CustomPlugin();
		$user_rate= $customObj->getUserRate($user_id);
		return $user_rate;
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
			
			if($request->getPost("pAction") == "ADD")
			{	
				$aData = json_decode($request->getPost("FORM_DATA"));
				$aData = (array)$aData;
				unset($aData['MASTER_KEY_ID']);
				
				$period_from = $aData['period_from'];
				$period_to = $aData['period_to'];
				
				unset($aData['period_from']);
				unset($aData['period_to']);
				
				$period = $period_from;
				if($period_from != $period_to)
					$period = $period_from.' To '.$period_to;
				
				$aData['period'] = $period;
				
				$aData['csv_file'] = $aData['filehidden1'];
				$aData['pdf_file'] = $aData['filehidden2'];
				unset($aData['filehidden1']);
				unset($aData['filehidden2']);
	
				$aData['status'] = 'success';
				$aData['created_on']=date("Y-m-d h:i:s");
				$projectTable->insert($aData);
				
				
				if($aData['payment_status'] == 'Paid')
				{
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
					
					$customObj->sendSmtpEmail($config,$rowset3[0]['email'],'Greetings! Your Financial Statement is Ready!',$content,$rowset3[0]['label_manager_email']);
				}
					
			}
			else  if($request->getPost("pAction") == "EDIT")
			{
				$aData = json_decode($request->getPost("FORM_DATA"));
				$aData = (array)$aData;
				$iMasterID=$aData['MASTER_KEY_ID'];
				unset($aData['MASTER_KEY_ID']);
				
				$period_from = $aData['period_from'];
				$period_to = $aData['period_to'];
				
				unset($aData['period_from']);
				unset($aData['period_to']);
				
				$period = $period_from;
				if($period_from != $period_to)
					$period = $period_from.' To '.$period_to;
				
				$aData['period'] = $period;
				
				$aData['csv_file'] = $aData['filehidden1'];
				$aData['pdf_file'] = $aData['filehidden2'];
				unset($aData['filehidden1']);
				unset($aData['filehidden2']);
				
				$rowset8 = $projectTable->select(array("id='".$iMasterID."' "));
				$rowset8 = $rowset8->toArray();
				
				$projectTable->update($aData,array("id='".$iMasterID."'"));
				
				
				if($rowset8[0]['payment_status'] != 'Paid' && $aData['payment_status'] == 'Paid')
				{
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
					
					
					$customObj->sendSmtpEmail($config,$rowset3[0]['email'],'Greetings! Your Financial Statement is Ready!',$content,$rowset3[0]['label_manager_email']);
				}
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
    $aColumns = array('id','period','report_type','royalty_amount','date_format(created_on,"%d-%m-%Y<br>%h:%i:%s")','payment_status','status','csv_file','pdf_file','user_id','requested');
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
	
	 
	
	if($_SESSION['user_id'] != 0  && $_SESSION['STAFFUSER'] == '0')
	{
		 $sWhere.=" AND  user_id ='".$_SESSION['user_id']."' and payment_status !='Hold' ";
		 $sWhere.=" AND ( requested = '0' OR requested = '2' )";
	}
	else
	{
		$sWhere.=" AND  requested = 0";
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
			else if( $aColumns[$i] == 'royalty_amount')
			{
				if($aRow['requested'] == '2')
				{
					$row[] = $aRow[ $aColumns[$i] ].' €';
				}
				else
				{
					$row[] = $aRow[ $aColumns[$i] ];
				}
			}
			else if( $aColumns[$i] == 'report_type')
			{
				$report_type = $aRow['report_type'];
				$labels = $this->getLabels($aRow['user_id']);
				
				$report_type = '<span data-toggle="tooltip" data-placement="top" data-html="true" title="" data-original-title="'.$labels.'">'.$report_type.'</span>';
					
				$row[] = $report_type;
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
    $aColumns = array('id','period','report_type','royalty_amount','date_format(created_on,"%d-%m-%Y<br>%h:%i:%s")','status','csv_file','pdf_file','month_wise_revenue','selected_info');
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
	
	 $sWhere.=" AND  requested = 1";
	
	
	$sWhere.=" AND  user_id ='".$_SESSION['user_id']."' ";

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
			else if( $aColumns[$i] == 'royalty_amount')
			{
				$royalty_amount = $aRow['royalty_amount'];
				if($aRow['month_wise_revenue'] != '')
					$royalty_amount = '<span data-toggle="tooltip" data-placement="top" data-html="true" title="" data-original-title="'.$aRow['month_wise_revenue'].'">'.$royalty_amount.'</span>';
					
				 $row[] = $royalty_amount;
			}
			else if( $aColumns[$i] == 'report_type')
			{
				$report_type = $aRow['report_type'];
				if($aRow['selected_info'] != '')
					$report_type = '<span data-toggle="tooltip" data-placement="top" data-html="true" title="" data-original-title="'.$aRow['selected_info'].'">'.$report_type.'</span>';
					
				 $row[] = $report_type;
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
    $aColumns = array('id','company_name','label_name','date_format(sales_month,"%M %Y")','amount','status');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_user_hold_report";
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
	
	 $sWhere.=" AND  status='Hold' ";
	
	if($_SESSION['user_id'] != 0  && $_SESSION['STAFFUSER'] == '0' )
	{
		 $sWhere.=" AND  user_id ='".$_SESSION['user_id']."' ";
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
			else if( $aColumns[$i] == 'amount')
			{
				
				 $row[] = $aRow['amount'].' €';
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
 
}//End Class