<?php
namespace Analytics\Controller;
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
    protected $studentTable; 
public function indexAction()
{
    $sl        = $this->getServiceLocator();
    $adapter   = $sl->get('Zend\Db\Adapter\Adapter');
    $customObj = $this->CustomPlugin();
    $mysqli    = $customObj->dbconnection();

    // 1) Get last imported sales month
    $sql1  = "SELECT MAX(sales_month) AS last_month FROM tbl_analytics";
    $stmt1 = $adapter->createStatement($sql1);
    $res1  = $stmt1->execute();
    $rs1   = new \Zend\Db\ResultSet\ResultSet;
    $rs1->initialize($res1);
    $last  = $rs1->current();
    $lastMonthDate = $last ? $last['last_month'] : null;

    // 2) Build WHERE for that month + user-scope
    $whereParts = [];
    if ($lastMonthDate) {
        $whereParts[] = "a.sales_month = '{$lastMonthDate}'";
    } else {
        // safety: no data
        $whereParts[] = "1 = 0";
    }

    // user-based filters (only for non-admin, non-staff users)
    $isAdminUser = (string)($_SESSION['user_id'] ?? '0') === '0';
    $isStaff     = (string)($_SESSION['STAFFUSER'] ?? '0') === '1';

    if (!$isAdminUser && !$isStaff) {
        // label filter
        $labels = $customObj->getUserLabels($_SESSION['user_id']);
        if (!empty($labels)) {
            $whereParts[] = "a.label_id IN ($labels)";
        }

        // hide Hold
        $whereParts[] = "a.import_payment_status != 'Hold'";

        // artist filter via EXISTS on view_analytics (artist CSV checked here)
        $user_artist = $customObj->getUserArtist($adapter); // comma CSV of artist names
        if ($user_artist !== '') {
            $artist_array = array_map('trim', explode(',', $user_artist));
            $or_conditions = [];
            foreach ($artist_array as $artist_name) {
                if ($artist_name === '') continue;
                $escaped = mysqli_real_escape_string($mysqli, $artist_name);
                $or_conditions[] = "FIND_IN_SET('{$escaped}', v.releaseArtist) > 0";
            }
            if (!empty($or_conditions)) {
                $whereParts[] =
                    "EXISTS (SELECT 1 FROM view_analytics v
                              WHERE v.release_id = a.release_id
                                AND v.sales_month = a.sales_month
                                AND (" . implode(' OR ', $or_conditions) . "))";
            }
        }
    }

    $whereSQL = '';
    if (!empty($whereParts)) {
        $whereSQL = 'WHERE ' . implode(' AND ', $whereParts);
    }

    // 3) Top track by creation count (same as before; just WHERE enhance)
    $sqlTop = "
        SELECT
            a.release_id,
            r.title,
            r.releaseArtist   AS artist,
            r.cover_img       AS cover,
            SUM(a.creation)   AS creations
        FROM tbl_analytics a
        INNER JOIN tbl_release r ON r.id = a.release_id
        {$whereSQL}
        GROUP BY a.release_id, r.title, r.releaseArtist, r.cover_img
        ORDER BY creations DESC
        LIMIT 1
    ";

    $stmt2 = $adapter->createStatement($sqlTop);
    $res2  = $stmt2->execute();
    $rs2   = new \Zend\Db\ResultSet\ResultSet;
    $rs2->initialize($res2);
    $top   = $rs2->current();

    // 4) Map to clean array for view (unchanged)
    $topTrack = [
        'release_id' => $top['release_id'] ?? 0,
        'title'      => $top['title'] ?? '',
        'artist'     => $top['artist'] ?? '',
        'cover'      => $top['cover'] ?? '',
        'creations'  => isset($top['creations']) ? (float)$top['creations'] : 0,
        'note'       => 'Get detailed insights on its performance.',
        'link'       => $this->url('analytics', ['action' => 'index']),
    ];

    return new \Zend\View\Model\ViewModel([
        'last_month_raw' => $lastMonthDate,
        'topTrack'       => $topTrack
    ]);
}


  
  public function chartsAction()
{
    // die('charts route OK');
    return new ViewModel();
}

	public function testMailAction()
	{
		$customObj = $this->CustomPlugin();
		  $config = $this->getServiceLocator()->get('config');
		$customObj->sendSmtpEmail($config,'developeratjannattech@gmail.com','Payment Request Submission','Testing by rk');
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
	public function latestImportedMonth()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		
		$sql="select sales_month from tbl_analytics  order by sales_month desc limit 1";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		
		return $rowset[0]['sales_month'];
	}
	public function fullviewAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$release_id = $_GET['id'];
		
		$from_month =  date('Y-m-01',strtotime($_GET['from_month']));
		$to_month =  date('Y-m-01',strtotime($_GET['to_month']));
		
		$sWhere .=" and  sales_month >='".$from_month."' and sales_month <='".$to_month."' ";
		
		if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
		{
			$sWhere.=" and import_payment_status !='Hold' ";
		}
		
		$sql="select sum(streams)as tot_streams,store from view_analytics where release_id='".$release_id."'   $sWhere  group by  store";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();


		$color = [
			'#FF6384', // Light Pink/Red
			'#36A2EB', // Light Blue
			'#FFCE56', // Light Yellow
			'#4BC0C0', // Teal
			'#9966FF', // Purple
			'#FF9F40', // Orange
			'#E7E9ED', // Light Grey
			'#F7464A', // Red
			'#46BFBD', // Aqua
			'#FDB45C', // Yellow
			'#949FB1', // Grey
			'#4D5360', // Dark Grey
		];
		$store_data=array();
		$i=0;
		foreach($rowset as $row)
		{
			$store_data[] = ['name' => $row['store'], 'y' => intval($row['tot_streams']), 'color' => $color[$i]];
			$i++;
		}


		$start = new DateTime($from_month);
		$end = new DateTime($to_month);
		$end->modify('first day of next month');
		$interval = new DateInterval('P1M');
		$period = new DatePeriod($start, $interval, $end);
		
		$month_name = array();
		$month_data = array();
		foreach ($period as $date) {
			// Output the first day of the current month
			$running_month =  $date->format('Y-m-01') . "\n";
			
			$cond='';
			if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
			{
				$cond =" and import_payment_status !='Hold' ";
			}
			
			
			$sql="select sum(streams)as tot_streams from view_analytics where release_id='".$release_id."' and sales_month ='".$running_month."' $cond ";		        
			$optionalParameters=array();        
			$statement = $adapter->createStatement($sql, $optionalParameters);        
			$result = $statement->execute();        
			$resultSet = new ResultSet;        
			$resultSet->initialize($result);        
			$rowset=$resultSet->toArray();
			
			$month_name[] = $date->format('M Y');
			
			if($rowset[0]['tot_streams'] == 'null')
				$rowset[0]['tot_streams'] = 0;
			
			$month_data[] = intval($rowset[0]['tot_streams']);
			
		}
		
		$sql="select * from view_release where id='".$release_id."' ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		$info = $rowset[0];
		if($info['cover_img'] != '')
			$coverIMG = '../public/uploads/'.$info['cover_img'];
		else
			$coverIMG = '../public/img/no-image.png';
		
		if($info['digitalReleaseDate'] != '0000-00-00')
			$info['digitalReleaseDate'] = date('M-d, Y');
		else
			$info['digitalReleaseDate']='';
		
		$sql="select * from tbl_track where master_id='".$release_id."' ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		$ISRC = $rowset[0]['isrc'];
		
		$viewModel= new ViewModel(array(
			
			'store_data' => $store_data,
			'MONTH_NAME' => $month_name,
			'MONTH_DATA' => $month_data,
			'coverIMG' => $coverIMG,
			'ID' => $info['id'],
			'SongName' => $info['title'],
			'releaseTitle' => $info['title'],
			'UPC' => $info['upc'],
			'ISRC' => $ISRC,
			'releaseDate' => $info['digitalReleaseDate'],
			'releaseArtist' => $info['releaseArtist'],
        ));

        return $viewModel; 
	}
	public function viewAction()
{
    $customObj = $this->CustomPlugin();
    $request   = $this->getRequest();

    $sl      = $this->getServiceLocator(); 
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');

    // ===== Inputs =====
    $release_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $from_month = isset($_GET['from_month']) ? date('Y-m-01', strtotime($_GET['from_month'])) : null;
    $to_month   = isset($_GET['to_month'])   ? date('Y-m-01', strtotime($_GET['to_month']))   : null;

    // ===== Where =====
    $sWhere = '';
    if ($from_month && $to_month) {
        $sWhere .= " AND sales_month >= '".$from_month."' AND sales_month <= '".$to_month."' ";
    }
    if ($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0') {
        $sWhere .= " AND import_payment_status != 'Hold' ";
    }

    // ===== Totals (revenue/creation/view/streams) =====
    $sql = "SELECT SUM(revenue) AS revenue, SUM(creation) AS creation, SUM(view) AS view, SUM(streams) AS streams
            FROM view_analytics
            WHERE release_id = '".$release_id."' $sWhere";
    $statement = $adapter->createStatement($sql);
    $result    = $statement->execute();
    $rowset    = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();

    $total_revenue  = (float)($rowset[0]['revenue'] ?? 0);
    $TOTAL_STREAM   = (float)($rowset[0]['streams']  ?? 0);
    $TOTAL_CREATION = (float)($rowset[0]['creation'] ?? 0);
    $TOTAL_VIEW     = (float)($rowset[0]['view']     ?? 0);

    // Apply user rate %
    $user_rate     = $this->getUserRate($_SESSION['user_id']);
    $total_revenue = number_format(($total_revenue * $user_rate / 100), 2, '.', '');

    // ===== Top stores (limit 5) =====
    $sql = "SELECT SUM(revenue) AS revenue, store
            FROM view_analytics
            WHERE release_id = '".$release_id."' $sWhere
            GROUP BY store
            ORDER BY SUM(revenue) DESC
            LIMIT 5";
    $statement = $adapter->createStatement($sql);
    $result    = $statement->execute();
    $rowsTop   = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();

    $top_store = [];
    $color     = ['#4f46e5','#22c55e','#eab308','#ef4444','#06b6d4','#a855f7'];
    $i = 0;
    foreach ($rowsTop as $row) {
        $store_image = $this->getStoreImage($row['store']);
        $top_store[] = [
            'name'  => $row['store'],
            'y'     => (float)$row['revenue'],
            'color' => $color[$i % count($color)],
            'img'   => $store_image
        ];
        $i++;
    }

    // ===== Monthly streams series =====
    $month_name = [];
    $month_data = [];
    if ($from_month && $to_month) {
        $start = new \DateTime($from_month);
        $end   = new \DateTime($to_month);
        $end->modify('first day of next month');
        $period = new \DatePeriod($start, new \DateInterval('P1M'), $end);

        foreach ($period as $date) {
            $running_month = $date->format('Y-m-01'); // ❗ no "\n"

            $cond = '';
            if ($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0') {
                $cond = " AND import_payment_status != 'Hold' ";
            }

            $sql = "SELECT SUM(streams) AS tot_streams
                    FROM view_analytics
                    WHERE release_id = '".$release_id."' AND sales_month = '".$running_month."' $cond";
            $statement = $adapter->createStatement($sql);
            $result    = $statement->execute();
            $rmonth    = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();

            $month_name[] = $date->format('M Y');
            $val = $rmonth[0]['tot_streams'] ?? 0;
            $month_data[] = (int)$val;
        }
    }

    // ===== Release core info =====
    $sql = "SELECT * FROM view_release WHERE id = '".$release_id."' LIMIT 1";
    $statement = $adapter->createStatement($sql);
    $result    = $statement->execute();
    $relRows   = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();
    $info      = $relRows[0] ?? [];

    // Cover
    if (!empty($info['cover_img'])) {
        $coverIMG = '../public/uploads/'.$info['cover_img'];
    } else {
        $coverIMG = '../public/img/no-image.png';
    }

    // ===== ✅ Correct MAIN release date selection (ISO pass to view) =====
    // Priority: digitalReleaseDate -> original_release_date -> MIN(tbl_track.release_date) -> created_at
    $releaseDateISO = null;

    if (!empty($info['digitalReleaseDate']) && $info['digitalReleaseDate'] != '0000-00-00') {
        $releaseDateISO = date('Y-m-d', strtotime($info['digitalReleaseDate']));
    } elseif (!empty($info['original_release_date']) && $info['original_release_date'] != '0000-00-00') {
        $releaseDateISO = date('Y-m-d', strtotime($info['original_release_date']));
    } else {
        // Pull min track release_date (ignore 0000-00-00)
        $sql = "SELECT MIN(NULLIF(release_date,'0000-00-00')) AS min_rel
                FROM tbl_track
                WHERE master_id = '".$release_id."'";
        $statement = $adapter->createStatement($sql);
        $result    = $statement->execute();
        $tmin      = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();
        $minRel    = $tmin[0]['min_rel'] ?? null;

        if (!empty($minRel)) {
            $releaseDateISO = date('Y-m-d', strtotime($minRel));
        } elseif (!empty($info['created_at'])) {
            $releaseDateISO = date('Y-m-d', strtotime($info['created_at']));
        } else {
            $releaseDateISO = '';
        }
    }

    // ===== ISRC (first track) =====
    $sql = "SELECT isrc FROM tbl_track WHERE master_id = '".$release_id."' ORDER BY id ASC LIMIT 1";
    $statement = $adapter->createStatement($sql);
    $result    = $statement->execute();
    $tinfo     = (new \Zend\Db\ResultSet\ResultSet())->initialize($result)->toArray();
    $ISRC      = $tinfo[0]['isrc'] ?? '';

    // ===== ViewModel =====
    $viewModel = new \Zend\View\Model\ViewModel([
        'TOP_STORE'       => $top_store,
        'TOTAL_AMOUNT'    => $total_revenue,
        'MONTH_NAME'      => $month_name,
        'MONTH_DATA'      => $month_data,
        'coverIMG'        => $coverIMG,
        'ID'              => $info['id'] ?? $release_id,
        'SongName'        => $info['title'] ?? '',
        'releaseTitle'    => $info['title'] ?? '',
        'UPC'             => $info['upc'] ?? '',
        'ISRC'            => $ISRC,
        // Pass ISO date; view me: date('M d, Y', strtotime($this->releaseDate))
        'releaseDate'     => $releaseDateISO,
        'releaseArtist'   => $info['releaseArtist'] ?? '',
        'TOTAL_STREAM'    => $this->formatViewCount($TOTAL_STREAM),
        'TOTAL_CREATION'  => $this->formatViewCount($TOTAL_CREATION),
        'TOTAL_VIEW'      => $this->formatViewCount($TOTAL_VIEW),
        'TOTAL_REVENUE'   => '€ '.$total_revenue,
    ]);

    return $viewModel;
}

	public function saveImportAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$analyticTable = new TableGateway('tbl_analytics', $adapter);
		$reportTable = new TableGateway('tbl_user_hold_report', $adapter);
		
		$financialTable = new TableGateway('tbl_financial_report', $adapter);
		
		$myImagePath = "public/uploads/import/".$_POST['importfilehidden'];
		$sel_type = $_POST['sel_type'];
		$sales_month = $_POST['sel_month'];
		
		$sales_month =  date('Y-m-01',strtotime($sales_month));
		
		$rowset = $analyticTable->select(array("sales_month = '".$sales_month."' and  record_type='".$_POST['sel_type']."' "));
		$rowset = $rowset->toArray();
		$exist_sales_month = 'No';
		
		if(count($rowset) > 0)
			$exist_sales_month = 'Yes';
		
		$sales_month_release_id = array();
		$sales_month_release_id_import_status = array();
		
		foreach($rowset as $row)
		{
			$sales_month_release_id[] = $row['release_id'];
			$sales_month_release_id_import_status[$row['release_id']] = $row['import_payment_status'];
		}
		
		$data = $this->getData($myImagePath,$sel_type);
		$info = $data['info'];
		$other_release = $data['other_release'];
		$release_label_id = $data['release_label_id'];
		
		
		$label_month_payment_info = array();
		
			foreach ($info as $release_id => $stores) 
			{
					foreach ($stores as $store => $sales_types) 
					{
						$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
						$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
						$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
						$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
						$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
						$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
						$total_revenue = $stream_revenue + $creation_revenue + $view_revenue;
						$label_month_payment_info[$release_label_id[$release_id]] += $total_revenue;
						
						$aData = array();
						$aData['record_type'] = $_POST['sel_type'];
						$aData['release_id'] = $release_id;
						//$aData['file_sales_month'] = $file_sales_month;
						$aData['sales_month'] = $sales_month;
						$aData['store'] = $store;
						$aData['creation'] = $creation_qty;
						$aData['view'] = $view_qty;
						$aData['streams'] = $stream_qty;
						$aData['revenue'] = $total_revenue;
						$aData['label_id'] = $release_label_id[$release_id];
						
						$exist_flag = 0;
						if(count($sales_month_release_id) > 0)
						{
							if(in_array($release_id,$sales_month_release_id))
							{
								$exist_flag = 1;
							}
						}
						
						if($release_id > 0)
						{
							if($exist_flag == '1')
							{
								$_POST['revenue_'.$release_label_id[$release_id]] = $sales_month_release_id_import_status[$release_id];
								$analyticTable->update($aData,array("release_id = '".$release_id."' ","sales_month = '".$sales_month."' and  record_type='".$_POST['sel_type']."' "));
							}
							else
							{
								$aData['import_payment_status'] = $_POST['revenue_'.$release_label_id[$release_id]];
								$analyticTable->insert($aData);
							}
							$import_cnt++;
						}
					}
			}
			foreach ($other_release as $label_id => $stores) 
			{
					foreach ($stores as $store => $sales_types) 
					{
						$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
						$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
						$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
						$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
						$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
						$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
						$total_revenue = $stream_revenue + $creation_revenue + $view_revenue;
						$label_month_payment_info[$label_id] += $total_revenue;
						
						$aData = array();
						$aData['record_type'] = $_POST['sel_type'];
						$aData['release_id'] = 0;
						$aData['sales_month'] = $sales_month;
						$aData['store'] = $store;
						$aData['creation'] = $creation_qty;
						$aData['view'] = $view_qty;
						$aData['streams'] = $stream_qty;
						$aData['revenue'] = $total_revenue;
						$aData['label_id'] = $label_id;
						
						
						$rowset = $analyticTable->select($aData,array("release_id = '0' ","label_id='".$label_id."' ","sales_month = '".$sales_month."' and  record_type='".$_POST['sel_type']."'  "));
						$rowset = $rowset->toArray();
						if(count($rowset) > 0)
						{
							$_POST['revenue_'.$label_id] = $rowset[0]['import_payment_status'];
							$analyticTable->update($aData,array("id='".$rowset[0]['id']."'"));
						}
						else
						{
							$aData['import_payment_status'] = $_POST['revenue_'.$label_id];
							$analyticTable->insert($aData);
						}
					}
			}
			
		
		foreach($_POST as $key => $value)
		{
			if(substr($key,0,8) == 'revenue_')
			{
				$label = explode('_',$key);
				$label_id = $label[1];
			}
			if($value != 'Hold')
			{
				$user_id = $customObj->getAssignedUserforLabel($label_id);
				$period = date('M Y',strtotime($sales_month));
				
				$rowset20 = $financialTable->select(array("user_id='".$user_id."' and period='".$period."' and requested='2'"));
				$rowset20 = $rowset20->toArray();
				
				$uData = array();
				$uData['royalty_amount'] = 0;
				$financialTable->update($uData,array("id='".$rowset20[0]['id']."'"));
			}
		}
		foreach($_POST as $key => $value)
		{
			if(substr($key,0,8) == 'revenue_')
			{
				$label = explode('_',$key);
				$label_id = $label[1];
				
				if($value == 'Hold')
				{
					$user_id = $customObj->getAssignedUserforLabel($label_id);
					$royalty_amount= $label_month_payment_info[$label_id];
						
					$user_rate = $this->getUserRate($user_id);
					$royalty_amount= ($royalty_amount * $user_rate / 100);
					
					$royalty_amount= number_format($royalty_amount,2,'.','');
					
					
					$rowset = $reportTable->select(array("sales_month='".$sales_month."' and user_id='".$user_id."' and  label_id='".$label_id."' "));
					$rowset = $rowset->toArray();
					
					
					$rData = array();
					$rData['user_id'] = $user_id;
					$rData['label_id'] = $label_id;
					$rData['amount'] = $royalty_amount;
					$rData['status'] = $value;
					$rData['sales_month'] = $sales_month;
					
					
					if(count($rowset) > 0)
					{
						$reportTable->update($rData,array("id='".$rowset[0]['id']."' "));
					}
					else
					{
						$reportTable->insert($rData);
					}
					
				}
				
				if($value != 'Hold')
				{
					
					$user_id = $customObj->getAssignedUserforLabel($label_id);
					$period = date('M Y',strtotime($sales_month));
					
					if($user_id > 0)
					{
					
						$rowset20 = $financialTable->select(array("user_id='".$user_id."' and period='".$period."' and requested='2'"));
						$rowset20 = $rowset20->toArray();
						
						$rData = array();
						$rData['user_id'] = $user_id;
						$rData['requested'] = '2';
						$rData['period'] = $period;
						$rData['sales_month'] = $sales_month;
						$rData['status'] = 'success';
						$rData['payment_status'] = $value;
						$rData['report_type'] = 'Full Report';
						$rData['created_on'] = date('Y-m-d H:i:s');
						
						$royalty_amount= $label_month_payment_info[$label_id];
						
						$user_rate = $this->getUserRate($user_id);
						$royalty_amount= ($royalty_amount * $user_rate / 100);
						
						$royalty_amount= number_format($royalty_amount,2,'.','');
						
						if(count($rowset20) > 0)
						{
							$uData = array();
							$uData['royalty_amount'] = $rowset20[0]['royalty_amount'] + $royalty_amount;
							$financialTable->update($uData,array("id='".$rowset20[0]['id']."'"));
						}
						else
						{
							$rData['royalty_amount'] = $royalty_amount;
							$financialTable->insert($rData);
						}
					}
				}
				
			}
		}
		
		
		$customObj->saveTransaction($sales_month,$adapter);
		$result['status'] = 'OK';
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function getData($myImagePath,$sel_type)
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$analyticTable = new TableGateway('tbl_analytics', $adapter);
		$projectTable = new TableGateway('tbl_release', $adapter);
		$trackTable = new TableGateway('tbl_track', $adapter);
		
		$release_label_id=array();
		
		$info = array();
		$other_release = array();
		
		$label_map = $this->getAllLabels();
		
		if (($handle = fopen($myImagePath, "r")) !== FALSE) 
		{ 
			if($sel_type == 'believe')
			{
				$count=0;
				$upc_release_id = array();
				$upc_label_id = array();
				$label_name_id = array();
				
				$headerMap =array();
				
				while (($data = fgetcsv($handle, 10000, ';')) !== FALSE) 
				{
						$num = count($data);
						
						if($count==0)
						{
							foreach ($data as $index => $header) {
								$headerMap[strtolower($header)] = $index; // Map lowercase header to column index
							}
							$count++;
						}
						else
						{
							$store  = utf8_encode(trim($data[$headerMap['platform']]));
							
							if(strstr($store,"Amazon"))
							{
								$store ='Amazon Prime Music';
							}
							else if(strstr($store,"iTunes"))
							{
								$store ='iTunes';
							}
							
							
							
							//$file_sales_month =  utf8_encode(trim($data[$headerMap['sales month']]));
							$label  = utf8_encode(trim($data[$headerMap['label name']]));
							$artist  = utf8_encode(trim($data[$headerMap['artist name']]));
							$title  = utf8_encode(trim($data[$headerMap['release title']]));
							$track_title  = utf8_encode(trim($data[$headerMap['track title']]));
							$upc  = utf8_encode(trim($data[$headerMap['upc']]));
							$isrc  = utf8_encode(trim($data[$headerMap['isrc']]));
							$sales_type = utf8_encode(trim($data[$headerMap['sales type']]));
							$qty = utf8_encode(trim($data[$headerMap['quantity']]));
							$revenue = utf8_encode(trim($data[$headerMap['net revenue']]));
							
							
							if(strstr($sales_type,"Streams"))
								$sales_type = 'Stream';
							if(strstr($sales_type,"Revenue"))
								$sales_type = 'Creation';	
							
							$upc = str_replace('empty:','',$upc);
							$upc = str_replace('Empty:','',$upc);
							$upc = trim($upc);
							
							$store_name = strtolower($store);
							
							if($sales_type == 'Creation' || $sales_type == 'Stream' || $sales_type == 'View' )
							{
							
								if(strstr($store_name,"amazon") || strstr($store_name,"apple") || strstr($store_name,"deezer") || strstr($store_name,"hungama")|| strstr($store_name,"itunes")|| strstr($store_name,"jiosaavn")|| strstr($store_name,"netease")|| strstr($store_name,"pandora")|| strstr($store_name,"resso")|| strstr($store_name,"soundcloud")|| strstr($store_name,"spotify") || strstr($store_name,"tencent") || strstr($store_name,"tidal") || strstr($store_name,"toc ") || strstr($store_name,"trebel") || strstr($store_name,"ultimate") || strstr($store_name,"uma") || strstr($store_name,"wynk")   || strstr($store_name,"youtube audio") )
								{
									$sales_type = 'Stream';
								}
								else if(strstr($store_name,"believe") || strstr($store_name,"youtube official") || strstr($store_name,"youtube ugc")  )
								{
									$sales_type = 'View';
								}
								else if(strstr($store_name,"facebook")  || strstr($store_name,"instagram"))
								{
									if(strstr(strtolower($sales_type),'stream'))
										$sales_type = 'View';
									else if(strstr(strtolower($sales_type),'creation'))
										$sales_type = 'Creation';
								}
								else if(strstr($store_name,"douyin") || strstr($store_name,"snapchat") || strstr($store_name,"tiktok") || strstr($store_name,"youtube shorts"))
								{
									$sales_type = 'Creation';
								}
								else
								{
									$store ='Others';
								}
							}
							
							//$file_sales_month = $this->changeDateFormat($file_sales_month);
							
							if( ($sales_type == 'Creation' || $sales_type == 'Stream' || $sales_type == 'View' ) )
							{
								
								if (isset($upc_release_id[$upc]))
								{
									$release_id = $upc_release_id[$upc];
									$label_id = $upc_label_id[$upc];
									
									$release_label_id[$release_id] = $label_id;
								}
								else
								{
									$release_info = $this->getReleaseID($upc);
									$release_id = $release_info['release_id'];
									$upc_release_id[$upc] = $release_id;
									
									$label_id =  $release_info['label_id'];
									$upc_label_id[$upc] = $label_id;
									$release_label_id[$release_id] = $label_id;
								}
								if($release_id > 0 )
								{
									$info[$release_id][$store][$sales_type]['qty'] += $qty;
									$info[$release_id][$store][$sales_type]['revenue'] += $revenue;
									
								}
								else
								{
									$label_id = $label_map[strtolower(trim($label))] ?? 0;
									if($label_id > 0)
									{
										$other_release[$label_id][$store][$sales_type]['qty'] += $qty;
										$other_release[$label_id][$store][$sales_type]['revenue'] += $revenue;
									}
								}
								
							}
						}
						
						
				}
			}
			
			if($sel_type == 'orchard')
			{
				$count=0;
				$upc_release_id = array();
				$upc_label_id = array();
				$label_name_id = array();
				
				$conversion_rate = $this->convertUsdToEur();
				
				$headerMap =array();
				 
				while (($data = fgetcsv($handle, 10000, ';')) !== FALSE) 
				{
						$num = count($data);
						
						if($count==0)
						{
							foreach ($data as $index => $header) {
								$headerMap[strtolower($header)] = $index; // Map lowercase header to column index
							}
							$count++;
						}
						else
						{
							$store  = utf8_encode(trim($data[$headerMap['store']]));
							if(strstr($store,"YouTube"))
								$store ='YouTube';
							
							if(strstr($store,"Amazon"))
								$store ='Amazon Prime Music';
							
							//$file_sales_month =  utf8_encode(trim($data[$headerMap['transaction date']]));
							$label  = utf8_encode(trim($data[$headerMap['label imprint']]));
							$artist  = utf8_encode(trim($data[$headerMap['product artist']]));
							$title  = utf8_encode(trim($data[$headerMap['product']]));
							$version  = utf8_encode(trim($data[$headerMap['product version']]));
							$track_title  = utf8_encode(trim($data[$headerMap['track']]));
							$track_artist  = utf8_encode(trim($data[$headerMap['track artist']]));
							$track_version  = utf8_encode(trim($data[$headerMap['track version']]));
							$upc  = utf8_encode(trim($data[$headerMap['display upc']]));
							$isrc  = utf8_encode(trim($data[$headerMap['isrc']]));
							$sales_type = utf8_encode(trim($data[$headerMap['transaction type']]));
							
							//$file_sales_month = $this->changeDateFormat($file_sales_month);
							
							if(strstr($sales_type,"Streams"))
								$sales_type = 'Stream';
							if(strstr($sales_type,"Revenue"))
								$sales_type = 'Creation';	
							
							$qty = utf8_encode(trim($data[$headerMap['quantity']]));
							$revenue = utf8_encode(trim($data[$headerMap['net share account currency']]));
							
							$revenue = $revenue * $conversion_rate;
							
							$upc = str_replace('empty:','',$upc);
							$upc = str_replace('Empty:','',$upc);
							$upc = trim($upc);
							
							$store_name = strtolower($store);
							
							
							if( $sales_type == 'Creation' || $sales_type == 'Stream' || $sales_type == 'View' )
							{
							
								if(strstr($store_name,"amazon") || strstr($store_name,"apple") || strstr($store_name,"deezer") || strstr($store_name,"hungama")|| strstr($store_name,"itunes")|| strstr($store_name,"jiosaavn")|| strstr($store_name,"netease")|| strstr($store_name,"pandora")|| strstr($store_name,"resso")|| strstr($store_name,"soundcloud")|| strstr($store_name,"spotify") || strstr($store_name,"tencent") || strstr($store_name,"tidal") || strstr($store_name,"toc ") || strstr($store_name,"trebel") || strstr($store_name,"ultimate") || strstr($store_name,"uma") || strstr($store_name,"wynk")   || strstr($store_name,"youtube audio") )
								{
									$sales_type = 'Stream';
								}
								else if(strstr($store_name,"believe") || strstr($store_name,"youtube official") || strstr($store_name,"youtube ugc")  )
								{
									$sales_type = 'View';
								}
								else if(strstr($store_name,"facebook")  || strstr($store_name,"instagram"))
								{
									if(strstr(strtolower($sales_type),'stream'))
										$sales_type = 'View';
									else if(strstr(strtolower($sales_type),'creation'))
										$sales_type = 'Creation';
								}
								else if(strstr($store_name,"douyin") || strstr($store_name,"snapchat") || strstr($store_name,"tiktok") || strstr($store_name,"youtube shorts"))
								{
									$sales_type = 'Creation';
								}
								else
								{
									$store ='Others';
								}
							}
							if( ($sales_type == 'Creation' || $sales_type == 'Stream' || $sales_type == 'View') &&  $qty > 0)
							{
								if (isset($upc_release_id[$upc])) 
								{
									$release_id = $upc_release_id[$upc];
									$label_id = $upc_label_id[$upc];
									
									$release_label_id[$release_id] = $label_id;
								}
								else
								{
									$release_info = $this->getReleaseID($upc);
									$release_id = $release_info['release_id'];
									$upc_release_id[$upc] = $release_id;
									
									$label_id =  $release_info['label_id'];
									$upc_label_id[$upc] = $label_id;
									$release_label_id[$release_id] = $label_id;
								}
								
								if($release_id > 0 )
								{
									$info[$release_id][$store][$sales_type]['qty'] += $qty;
									$info[$release_id][$store][$sales_type]['revenue'] += $revenue;
								}
								else
								{
									$label_id = $label_map[strtolower(trim($label))] ?? 0;
									if($label_id > 0)
									{
										$other_release[$label_id][$store][$sales_type]['qty'] += $qty;
										$other_release[$label_id][$store][$sales_type]['revenue'] += $revenue;
									}
								}
							}
						}
				}
			}
			
			$data=array();
			$data['info'] = $info;
			$data['other_release'] = $other_release;
			$data['release_label_id'] = $release_label_id;
			return $data;
		
		}
	}
	public function importAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$analyticTable = new TableGateway('tbl_analytics', $adapter);
		$projectTable = new TableGateway('tbl_release', $adapter);
		$trackTable = new TableGateway('tbl_track', $adapter);
        if ($request->isPost()) {
            $file = $_FILES['catelogfile'];
            $filename = $_FILES['catelogfile']['name'];
			
			$sales_month = $_POST['sel_month'];
			$sales_month =  date('Y-m-01',strtotime($sales_month));
			
			$ext = pathinfo($filename, PATHINFO_EXTENSION); 
			
			if($ext != 'csv' && $ext != 'CSV')
			{
				$result['status'] = 'NOT';
				$result = json_encode($result);
				echo $result;
				exit;
			}
			
			
			/*$rowset = $analyticTable->select(array("sales_month = '".$sales_month."' and record_type='".$_POST['sel_type']."' "));
			$rowset = $rowset->toArray();
			
			if(count($rowset) > 0)
			{
				$result['status'] = 'EXIST';
				$result = json_encode($result);
				echo $result;
				exit;
			}*/
			
            
			$fileName1 = date('YmdHis').'.'.$ext;
			$myImagePath =  "public/uploads/import/$fileName1";
			
			$IMPORT_LIST='';
			$IGNORE_LIST='';
			
			$ignore_cnt=0;
			$import_cnt=0;
            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {

					$data = $this->getData($myImagePath,$_POST['sel_type']);
					$info = $data['info'];
					$other_release = $data['other_release'];
					$release_label_id = $data['release_label_id'];
					
					$label_month_payment_info = array();
					
					if($_POST['sel_type'] == 'believe')
					{
						foreach ($info as $release_id => $stores) 
						{
							foreach ($stores as $store => $sales_types) 
							{
								$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
								$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
								$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
								$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
								$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
								$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
								$total_revenue = $stream_revenue + $creation_revenue+$view_revenue;
							
								$label_month_payment_info[$release_label_id[$release_id]] += $total_revenue;
							}
						}
						foreach ($other_release as $label_id => $stores) 
						{
							foreach ($stores as $store => $sales_types) 
							{
								$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
								$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
								$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
								$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
								$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
								$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
								$total_revenue = $stream_revenue + $creation_revenue+$view_revenue;
								
								$label_month_payment_info[$label_id] += $total_revenue;
							}
						}
					}
					if($_POST['sel_type'] == 'orchard')
					{
						foreach ($info as $release_id => $stores) 
						{
							foreach ($stores as $store => $sales_types) 
							{
								$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
								$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
								$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
								$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
								$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
								$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
								$total_revenue = $stream_revenue + $creation_revenue+$view_revenue;
								
								$label_month_payment_info[$release_label_id[$release_id]] += $total_revenue;
							}
						}
						foreach ($other_release as $label_id => $stores) 
						{
							foreach ($stores as $store => $sales_types) 
							{
								$view_qty = isset($sales_types['View']['qty']) ? $sales_types['View']['qty'] : 0;
								$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
								$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
								$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
								$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
								$view_revenue = isset($sales_types['View']['revenue']) ? $sales_types['View']['revenue'] : 0;
								$total_revenue = $stream_revenue + $creation_revenue+$view_revenue;
								
								$label_month_payment_info[$label_id] += $total_revenue;
							}
						}
					}
		
					$summary_html='';
					foreach($label_month_payment_info as $label_id => $revenue)
					{
						$user_id = $customObj->getAssignedUserforLabel($label_id);
						$user_rate = $this->getUserRate($user_id);
						$royalty_amount= ($revenue * $user_rate / 100);
						
						$royalty_amount= number_format($royalty_amount,2,'.','').'  €';
					
						
						$label_info = $this->releaseInfo($label_id);
						
						$rowset = $analyticTable->select(array("label_id='".$label_id."' ","sales_month = '".$sales_month."' and  record_type='".$_POST['sel_type']."'  "));
						$rowset = $rowset->toArray();
						if(count($rowset) > 0)
						{
							$select = $rowset[0]['import_payment_status'];
						}
						else
						{
							$select = '<select name="revenue_'.$label_id.'" class="form-control"><option  value="Unpaid" selected>Unpaid</option><option  value="Paid">Paid</option><option  value="Hold">Hold</option></select>';
						}
						
						
						$summary_html .= '<tr><td>'.$label_info['label_name'].'</td><td>'.$label_info['company_name'].'</td><td>'.$sales_month.'</td><td>'.$royalty_amount.'</td><td>'.$select.'</td></tr>';
						
					}
					$result['summary'] = $summary_html;
					$result['file_name'] = $fileName1;
					$result['status'] = 'OK';
					$result['message1'] = 'Done';	
				}
        }
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function  convertUsdToEur($usd_amount=1) {
		// URL to fetch exchange rates from ECB
		$url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
		
		// Fetch the XML data
		$xml = simplexml_load_file($url);

		if ($xml === false) {
			return "Error: Unable to retrieve exchange rates";
		}

		// Parse the XML to find USD to EUR rate
		$usd_to_eur_rate = null;
		foreach ($xml->Cube->Cube->Cube as $rate) {
			if ($rate['currency'] == 'USD') {
				$usd_to_eur_rate = (float)$rate['rate'];
				break;
			}
		}

		if ($usd_to_eur_rate === null) {
			return "Error: Unable to find USD to EUR conversion rate";
		}

		// Convert the amount
		$eur_amount = $usd_amount / $usd_to_eur_rate;
		return $eur_amount;
	}
	
	public function releaseInfo($id)
	{
		  $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_label where id = '".$id."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray(); 

		  $info = array();
		  $info['label_name'] = $rowset[0]['name'];
		  $info['company_name'] = $this->company_name($rowset[0]['user_id']);
		  return $info;
	}
	public function company_name($id)
	{
		  $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select group_concat(company_name) as name from tbl_staff where id='".$id."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray(); 
		  
		  return $rowset[0]['name'];
	}
	public function getReleaseID($upc)
	{
		
		  $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_release where upc = '".$upc."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray();        
		 
		  $info = array();
		  $info['release_id'] = $rowset[0]['id'];
		  $info['label_id'] = $rowset[0]['labels'];
		  return $info;
		
	}
	public function formatViewCount($num) {
		
		$num = number_format($num,2,'.','');
		
		 if ($num >= 1000000) {
			$formatted = $num / 1000000;
			return (floor($formatted) == $formatted) ? number_format($formatted, 0) . 'M' : number_format($formatted, 1) . 'M'; // Format millions
		} elseif ($num >= 1000) {
			$formatted = $num / 1000;
			return (floor($formatted) == $formatted) ? number_format($formatted, 0) . 'K' : number_format($formatted, 1) . 'K'; // Format thousands
		}
		return strpos($num, '.00') !== false ? (string)(int)$num : (string)$num;
	}
	public function getGrandRevenue($id,$from_month,$to_month)
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		if($from_month == '')
			$from_month ='0000-00-00';
		if($to_month == '')
			$to_month ='0000-00-00';
		
		$cond='';
		if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
		{
			$cond =" and import_payment_status !='Hold' ";
		}
	
		$sql="select sum(revenue)as revenue from view_analytics where release_id='".$id."' and sales_month >='".$from_month."' and sales_month <='".$to_month."'  $cond ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();

		return $rowset[0]['revenue'];
	}
	public function getTotalAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$mysqli=$customObj->dbconnection();
		
		if($_POST['month'] == '1M')
		{
			$last_month = $this->latestImportedMonth();
			$_POST['from_month'] = $last_month;
			$_POST['to_month'] =$last_month;
		}
		if($_POST['month'] == '2M')
		{
			$last_month = $this->latestImportedMonth();
			$_POST['from_month'] = date('Y-m-01',strtotime($last_month." -1 month"));
			$_POST['to_month'] = $last_month;
		}
		if($_POST['month'] == '3M')
		{
			$last_month = $this->latestImportedMonth();
			$_POST['from_month'] = date('Y-m-01',strtotime($last_month." -2 month"));
			$_POST['to_month'] = $last_month;
		}
		if($_POST['from_month'] !='' && $_POST['to_month'] !='' )
		{
			$from_month =  date('Y-m-01',strtotime($_POST['from_month']));
			$to_month =  date('Y-m-01',strtotime($_POST['to_month']));
			
			$sWhere .=" and  sales_month >='".$from_month."' and sales_month <='".$to_month."' ";
		}
		else
		{
			$sWhere .=" and  1 = 0";
		}
		
		if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			
			$user_artist = $customObj->getUserArtist($adapter);
		
			$astist_cond = '';
			
			if($user_artist != '')
			{
				$artist_array = array_map('trim', explode(',', $user_artist));
				$or_conditions = [];

				foreach ($artist_array as $artist_name) {
					$escaped_artist = mysqli_real_escape_string($mysqli, $artist_name);
					$or_conditions[] = "FIND_IN_SET('$escaped_artist', view_analytics.releaseArtist) > 0";
				}

				if (!empty($or_conditions)) {
					$astist_cond = ' AND (' . implode(' OR ', $or_conditions) . ')';
				}
			}
		
			$sWhere.="  AND label_id in (".$labels.") $astist_cond and import_payment_status !='Hold' ";
		}
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$sWhere.= $staff_cond;
		}
		if($_POST['search'] != '')
		{
			$mysqli=$customObj->dbconnection();
			$search= (trim($_POST['search']));
			$search = mysqli_real_escape_string($mysqli, $search); // Escape special characters to prevent SQL injectio
			$sWhere.=" AND ( (title like '%".$search."%') or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%')  )";
		}
		
	
		 $sql="select sum(creation)as tot_creation, sum(view)as tot_view,sum(revenue)as tot_revenue,sum(streams)as tot_streams from view_analytics where 1=1  $sWhere  ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		
		$user_rate = 100;
	
		if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
		{
			$user_rate = $this->getUserRate($_SESSION['user_id']);
		}
		$rowset[0]['tot_revenue'] = ($rowset[0]['tot_revenue'] * $user_rate / 100);
		
		
		$result2['tot_creation'] = $this->formatViewCount($rowset[0]['tot_creation']);
		$result2['tot_view'] = $this->formatViewCount($rowset[0]['tot_view']);
		$result2['tot_revenue'] = number_format($rowset[0]['tot_revenue'],'2','.','').' €';
		$result2['tot_stream'] = $this->formatViewCount($rowset[0]['tot_streams']);
		$result2['DBStatus'] = 'OK';
        $result2 = json_encode($result2);
        echo $result2;
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
    $aColumns = array('id','cover_img','title','digitalReleaseDate','sum(creation)','sum(view)','"" as sales_month','sum(revenue)','sum(streams)','version','releaseArtist','record_type','release_id');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_analytics";
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
	$sl = $this->getServiceLocator();        
	$adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
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
                    ".($_GET['sSortDir_'.$i]==='asc' ? 'desc' : 'desc') .", ";
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
        $sWhere=" where 1=1";
    else
        $sWhere.=" AND  1=1";
	
	if($_GET['month'] == '1M')
	{
		$last_month = $this->latestImportedMonth();
		$_GET['from_month'] = $last_month;
		$_GET['to_month'] =$last_month;
	}
	if($_GET['month'] == '2M')
	{
		$last_month = $this->latestImportedMonth();
		$_GET['from_month'] = date('Y-m-01',strtotime($last_month." -1 month"));
		$_GET['to_month'] = $last_month;
	}
	if($_GET['month'] == '3M')
	{
		$last_month = $this->latestImportedMonth();
		$_GET['from_month'] = date('Y-m-01',strtotime($last_month." -2 month"));
		$_GET['to_month'] = $last_month;
	}
	
	if($_GET['from_month'] !='' && $_GET['to_month'] !='' )
	{
		
		$from_month =  date('Y-m-01',strtotime($_GET['from_month']));
		$to_month =  date('Y-m-01',strtotime($_GET['to_month']));
		
		
		$sWhere .=" and  sales_month >='".$from_month."' and sales_month <='".$to_month."' ";
	}
	else
	{
		$sWhere .=" and  1 = 0";
	}
	
	$sWhere .=" and release_id > 0";
	
	$labels='';
	if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
	{
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		
		$user_artist = $customObj->getUserArtist($adapter);
		
		$astist_cond = '';
		
		if($user_artist != '')
		{
			$artist_array = array_map('trim', explode(',', $user_artist));
			$or_conditions = [];

			foreach ($artist_array as $artist_name) {
				$escaped_artist = mysqli_real_escape_string($mysqli, $artist_name);
				$or_conditions[] = "FIND_IN_SET('$escaped_artist', view_analytics.releaseArtist) > 0";
			}

			if (!empty($or_conditions)) {
				$astist_cond = ' AND (' . implode(' OR ', $or_conditions) . ')';
			}
		}
	
		$sWhere.="  AND label_id in (".$labels.") $astist_cond ";
		
		$sWhere .=" and import_payment_status !='Hold' ";
	}
	if($_SESSION["STAFFUSER"] == 1 )
	{
		$staff_cond = $customObj->getStaffReleaseCond();
		$sWhere.= $staff_cond;
	}
	if($_GET['search'] != '')
	{
		
		$search = trim($_GET['search']);
		$search = mysqli_real_escape_string($mysqli, $search); // Escape special characters to prevent SQL injection
	
		$sWhere.=" AND ( (title like '%".$search."%') or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%')  )";
	}
	
	$sWhere .=" group by release_id";
	
	
	$user_rate = 100;
	
	if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
	{
		$user_rate = $this->getUserRate($_SESSION['user_id']);
	}
	
	$prev_data = array();
	if($_GET['from_month'] == $_GET['to_month'])
	{
		if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
		{
			$cond ="  AND label_id in (".$labels.") and import_payment_status !='Hold'  ";
		}
		
		if($_SESSION["STAFFUSER"] == 1 )
		{
			$staff_cond = $customObj->getStaffReleaseCond();
			$cond.= $staff_cond;
		}
		$prev_month = date('Y-m-01',strtotime($_GET['from_month']." -1 month"));
		
		 $sql="select release_id,sum(creation)as creation,sum(view)as view,sum(revenue)as revenue,sum(streams)as streams from view_analytics where sales_month >='".$prev_month."' and sales_month <='".$prev_month."'   $cond group by release_id ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();

		foreach($rowset as $row)
		{
			$prev_data[$row['release_id']]['view'] = $row['view'];
			$prev_data[$row['release_id']]['creation'] = $row['creation'];
			$prev_data[$row['release_id']]['revenue'] = ($row['revenue'] * $user_rate / 100);
			$prev_data[$row['release_id']]['streams'] = $row['streams'];
		}
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
	
	$up = '<div class="MuiAvatar-root MuiAvatar-rounded MuiAvatar-colorDefault css-v3nhrv"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-1yls13q up" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="NorthEastIcon"><path d="M9 5v2h6.59L4 18.59 5.41 20 17 8.41V15h2V5z"></path></svg></div>';
	
	$down = '<div class="MuiAvatar-root MuiAvatar-rounded MuiAvatar-colorDefault css-iph4ya"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-1yls13q down" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="SouthEastIcon"><path d="M19 9h-2v6.59L5.41 4 4 5.41 15.59 17H9v2h10z"></path></svg></div>';
	
	$null = '<div class="MuiAvatar-root MuiAvatar-rounded MuiAvatar-colorDefault css-1x5yup0"><svg class="MuiSvgIcon-root MuiSvgIcon-fontSizeMedium css-1yls13q null" focusable="false" aria-hidden="true" viewBox="0 0 24 24" data-testid="HorizontalRuleIcon"><path fill-rule="evenodd" d="M4 11h16v2H4z"></path></svg></div>';
	
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
			else if ( $aColumns[$i] == "digitalReleaseDate" )
            {
				if($aRow['digitalReleaseDate'] =='0000-00-00')
					$row[] ='';
				else
					$row[] = '<strong class="text-muted">'.date('M d, Y',strtotime($aRow['digitalReleaseDate'])).'<strong>';
			}
			else if ( $aColumns[$i] == "title" )
            {
				$artist = '<i>empty</i>';
				if($aRow['releaseArtist'] !='')
				{
					$artist = '<strong class="text-muted">By</strong> '.$aRow['releaseArtist'];
				}
				
				$row[] ='<a href="analytics/view?id='.$aRow['release_id'].'&from_month='.$_GET['from_month'].'&to_month='.$_GET['to_month'].'" target="_blank" ><b>'.$aRow['title'].'</b></a><br>'.$artist;
				
			}
			else if ( $aColumns[$i] == "sum(revenue)" )
            {
				$revenue = ($aRow['sum(revenue)'] * $user_rate / 100);
				$comp='';
				if(!empty($prev_data) && !empty ($prev_data[$aRow['release_id']]['revenue']))
				{
					if($prev_data[$aRow['release_id']]['revenue'] > 0)
					{
						if($prev_data[$aRow['release_id']]['revenue'] < $revenue)
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['revenue'],$revenue);
							$comp = $up.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
						else
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['revenue'],$revenue);
							$comp = $down.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
					}
					else
					{
						$comp = $null;
					}
				}
				else
				{
					$comp = $null;
				}
				
				$row[] = '<p class="bold_number">'.number_format($revenue,'2','.','').' €</p>'.$comp;
			}
			else if ( $aColumns[$i] == "sum(streams)" )
            {
				$comp='';
				if(!empty($prev_data) &&  !empty($prev_data[$aRow['release_id']]['streams']))
				{
					if($prev_data[$aRow['release_id']]['streams'] > 0)
					{
						if($prev_data[$aRow['release_id']]['streams'] < $aRow['sum(streams)'])
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['streams'],$aRow['sum(streams)']);
							$comp = $up.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
						else
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['streams'],$aRow['sum(streams)']);
							$comp = $down.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
					}
					else
					{
						$comp = $null;
					}
				}
				else
				{
					$comp = $null;
				}
				$row[] = '<p class="bold_number">'.$this->formatViewCount($aRow['sum(streams)']).'</p>'.$comp;
				
			}
			else if ( $aColumns[$i] == "sum(creation)" )
            {
				$comp='';
				if(!empty($prev_data) && !empty ($prev_data[$aRow['release_id']]['creation']))
				{
					if($prev_data[$aRow['release_id']]['creation'] > 0)
					{
						
						if($prev_data[$aRow['release_id']]['creation'] < $aRow['sum(creation)'])
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['creation'],$aRow['sum(creation)']);
							$comp = $up.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
						else
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['creation'],$aRow['sum(creation)']);
							$comp = $down.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
					}
					else
					{
						$comp = $null;
					}
				}
				else
				{
					$comp = $null;
				}
				$row[] = '<p class="bold_number">'.$this->formatViewCount($aRow['sum(creation)']).'</p>'.$comp;
				
			}
			else if ( $aColumns[$i] == "sum(view)" )
            {
				$comp='';
				if(!empty($prev_data) && !empty ($prev_data[$aRow['release_id']]['view']))
				{
					if($prev_data[$aRow['release_id']]['view'] > 0)
					{
						
						if($prev_data[$aRow['release_id']]['view'] < $aRow['sum(view)'])
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['view'],$aRow['sum(view)']);
							$comp = $up.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
						else
						{
							$up_down_per = $this->up_down_per($prev_data[$aRow['release_id']]['view'],$aRow['sum(view)']);
							$comp = $down.'<span class="MuiTypography-root MuiTypography-body-medium css-sw5ddh">'.$up_down_per.'</span>';
						}
					}
					else
					{
						$comp = $null;
					}
				}
				else
				{
					$comp = $null;
				}
				$row[] = '<p class="bold_number">'.$this->formatViewCount($aRow['sum(view)']).'</p>'.$comp;
				
			}
			else if ( $aColumns[$i] == "cover_img" )
{
    if($aRow['cover_img'] == '')
    {
        $row[] = '<img src="public/img/no-image.png" style="width:64px;height:64px;border-radius:8px;object-fit:cover;">';
    }
    else
    {
        $row[] = '<img src="public/uploads/thumb_'.$aRow['cover_img'].'" style="width:64px;height:64px;border-radius:8px;object-fit:cover;">';
    }
}

			else if ( strstr($aColumns[$i],"as sales_month") )
			{
				if($_GET['from_month'] == $_GET['to_month'])
					$row[] = $_GET['from_month'];
				else
					$row[] = $_GET['from_month'].' - '.$_GET['to_month'];
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
public function up_down_per($prev,$current)
{
	if($prev==0 || $current==0)
	{
		return '';
	}
	$percentage = (($current - $prev) / $prev) * 100;
	$percentage = (round($percentage));
	
	if($percentage < 100)
		return $percentage.'% vs. last month';
	else
		return 'x'.round($percentage/100).' vs. last month';
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
    $aColumns = array('id','"" as icon','store','"" as per','sum(streams)','sum(creation)','sum(view)','sum(revenue)','"" as sales_month','version','releaseArtist','record_type','release_id');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_analytics";
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
        $sWhere=" where  release_id='".$_GET['release_id']."' ";
    else
        $sWhere.=" AND   release_id='".$_GET['release_id']."' ";
	
	if($_GET['from_month'] !='' && $_GET['to_month'] !='' )
	{
		
		$from_month =  date('Y-m-01',strtotime($_GET['from_month']));
		$to_month =  date('Y-m-01',strtotime($_GET['to_month']));
		
		
		$sWhere .=" and  sales_month >='".$from_month."' and sales_month <='".$to_month."' ";
	}
	else
	{
		$sWhere .=" and  1 = 0";
	}
	if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
	{
		$sWhere .=" and import_payment_status !='Hold' ";
	}
	$sWhere .=" group by store";
	
	
	
    /*
     * SQL queries
     * Get data to display
     */
     $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sWhere
        order by SUM(revenue) DESC
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
	$si_no= $_GET['iDisplayStart']+1;
	
	$user_rate = 100;
	
	if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0')
	{
		$user_rate = $this->getUserRate($_SESSION['user_id']);
		
		
	}
	
	$grand_revenue = $this->getGrandRevenue($_GET['release_id'],$from_month,$to_month);
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
			else if ( strstr($aColumns[$i],"as icon") )
			{
				$img = '<img src="../public/img/no-image.png" width="30" style="border-radius:8px;">';
				
				if(strstr(strtolower($aRow['store']),"ugc"))
					$img = '<img src="../public/img/store2/youtube ugc.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"amazon"))
					$img = '<img src="../public/img/store2/amazon.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"apple"))
					$img = '<img src="../public/img/store2/apple.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"believe"))
					$img = '<img src="../public/img/store2/Believe Rights Services.jpg" width="30"  style="border-radius:2px;">';					
				else if(strstr(strtolower($aRow['store']),"douyin"))
					$img = '<img src="../public/img/store2/Douyin.png" width="30"  style="border-radius:2px;">';
				
				else if(strstr(strtolower($aRow['store']),"spotify"))
					$img = '<img src="../public/img/store2/spotify.png" width="30"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"saavn"))
					$img = '<img src="../public/img/store2/jiosaavn.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"hungama"))
					$img = '<img src="../public/img/store2/hungama.avif" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"itunes"))
					$img = '<img src="../public/img/store2/iTunes.png" width="30"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"resso"))
					$img = '<img src="../public/img/store2/Resso-tiktok.jpg" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"snapchat"))
					$img = '<img src="../public/img/store2/snapchat-logo.svg" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"soundcloud"))
					$img = '<img src="../public/img/store2/Soundcloud.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"uma"))
					$img = '<img src="../public/img/store2/UMA (Vkontakte).png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"tier"))
					$img = '<img src="../public/img/store2/YouTube Audio Tier.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"shorts"))
					$img = '<img src="../public/img/store2/Youtube_shorts_icon.png" width="28"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"facebook"))
					$img = '<img src="../public/img/store2/Facebook_instagram.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"tiktok"))
					$img = '<img src="../public/img/store2/tiktok.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"ultimate"))
					$img = '<img src="../public/img/store2/Ultimate Music.jpg" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"youtube official"))
					$img = '<img src="../public/img/store2/YouTube Official Content.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"wynk"))
					$img = '<img src="../public/img/store2/Wynk Music.png" width="30"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"other"))
					$img = '<img src="../public/img/store2/Others.png" width="30"  style="border-radius:2px;">';
				
				$row[] = '<div style="display:flex;width: 100%;justify-content: space-around;align-items: center;">'.$si_no.'. '.$img.'</div>';
			}
			else if ( strstr($aColumns[$i],"as per") )
			{
				if($aRow['sum(revenue)'] > 0)
					$per = ($aRow['sum(revenue)']/$grand_revenue*100);
				else
					$per = 0;
				
				$row[] = number_format($per,'2','.','').'%';
			}
			else if ( $aColumns[$i] == "cover_img" )
			{
				if($aRow['cover_img'] == '')
			    {
				    $row[] = '<img src="public/img/no-image.png" width="40" style="border-radius:8px;">';
				}
				else
				{
					 $row[] = '<img src="public/uploads/'.$aRow['cover_img'].'" width="40"  style="border-radius:8px;">';
				}
			}
			else if ( $aColumns[$i] == "sum(revenue)" )
            {
				if($_SESSION['user_id'] != '0'  && $_SESSION['STAFFUSER'] == '0' )
				{
					$row[] = $this->formatViewCount(($aRow['sum(revenue)'] * $user_rate / 100)).' €';
				}
				else
				{
					$row[] = $this->formatViewCount($aRow['sum(revenue)']).' €';
				}
			}
			else if ( $aColumns[$i] == "sum(streams)" )
            {
				
				$row[] = $this->formatViewCount($aRow['sum(streams)']);
				
			}
			else if ( $aColumns[$i] == "sum(creation)" )
            {
				
				$row[] = $this->formatViewCount($aRow['sum(creation)']);
				
			}
			else if ( $aColumns[$i] == "sum(view)" )
            {
				
				$row[] = $this->formatViewCount($aRow['sum(view)']);
				
			}
			
			else if ( strstr($aColumns[$i],"as sales_month") )
			{
				if($_GET['from_month'] == $_GET['to_month'])
					$row[] = $_GET['from_month'];
				else
					$row[] = $_GET['from_month'].' - '.$_GET['to_month'];
			}
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
                $row[] = $aRow[ $aColumns[$i] ];
            }
        }
        $output['aaData'][] = $row;
		$si_no++;
    }
    echo json_encode( $output );
}

public function getStoreImage($store)
{
	if(strstr(strtolower($store),"ugc"))
		$img = "../public/img/store2/youtube ugc.png";
	else if(strstr(strtolower($store),"amazon"))
		$img = "../public/img/store2/amazon.png";
	else if(strstr(strtolower($store),"apple"))
		$img = "../public/img/store2/apple.png";
	else if(strstr(strtolower($store),"believe"))
		$img = "../public/img/store2/Believe Rights Services.jpg";					
	else if(strstr(strtolower($store),"spotify"))
		$img = "../public/img/store2/spotify.png";	
	else if(strstr(strtolower($store),"saavn"))
		$img = "../public/img/store2/jiosaavn.png";
	else if(strstr(strtolower($store),"hungama"))
		$img = "../public/img/store2/hungama.avif";
	else if(strstr(strtolower($store),"itunes"))
		$img = "../public/img/store2/iTunes.png";	
	else if(strstr(strtolower($store),"resso"))
		$img = "../public/img/store2/Resso-tiktok.jpg";
	else if(strstr(strtolower($store),"snapchat"))
		$img = "../public/img/store2/snapchat-logo.svg";
	else if(strstr(strtolower($store),"soundcloud"))
		$img = "../public/img/store2/Soundcloud.png";
	else if(strstr(strtolower($store),"uma"))
		$img = "../public/img/store2/UMA (Vkontakte).png";
	else if(strstr(strtolower($store),"tier"))
		$img = "../public/img/store2/YouTube Audio Tier.png";
	else if(strstr(strtolower($store),"shorts"))
		$img = "../public/img/store2/Youtube_shorts_icon.png";
	else if(strstr(strtolower($store),"facebook"))
		$img = "../public/img/store2/Facebook_instagram.png";
	else if(strstr(strtolower($store),"tiktok"))
		$img = "../public/img/store2/tiktok.png";
	else if(strstr(strtolower($store),"wynk"))
		$img = "../public/img/store2/Wynk Music.png";
	else if(strstr(strtolower($store),"other"))
		$img = "../public/img/store2/Others.png";
	else if(strstr(strtolower($store),"youtube official"))
		$img = "../public/img/store2/YouTube Official Content.png";
	else if(strstr(strtolower($store),"douyin"))
		$img = "../public/img/store2/Douyin.png";
	else if(strstr(strtolower($store),"ultimate"))
		$img = "../public/img/store2/Ultimate Music.jpg";
	
	return $img;
}
public   function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }
	
 public function getTrackIsrc($id)    
 {        
 $sl = $this->getServiceLocator();        
 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
 $sql="select isrc from tbl_track where master_id='".$id."' and isrc !='' order by volume,order_id asc limit 1 ";		        
 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
 $result = $statement->execute();        $resultSet = new ResultSet;        
 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
 
	return $rowset[0]['isrc'];
 }
 public function getUserRate($user_id)
 {
	$customObj = $this->CustomPlugin();
	$user_rate= $customObj->getUserRate($user_id);
	return $user_rate;
 }
 public function getLabelID($label_name)
 {
	$label_name = trim($label_name);
	$sl = $this->getServiceLocator();        
	$adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
	
	$sql="select * from tbl_label where LOWER(name) ='".strtolower($label_name)."' ";		        
	$optionalParameters=array();        
	$statement = $adapter->createStatement($sql, $optionalParameters);        
	$result = $statement->execute();        
	$resultSet = new ResultSet;        
	$resultSet->initialize($result);        
	$rowset=$resultSet->toArray();  
	

	if(count($rowset)>0)
	   return $rowset[0]['id'];
	 else
		return 0;
 }
 public function getAllLabels()
{
    $sl = $this->getServiceLocator();        
    $adapter = $sl->get('Zend\Db\Adapter\Adapter'); 

    $sql = "SELECT id, name FROM tbl_label";
    $statement = $adapter->createStatement($sql);
    $result = $statement->execute();        
    $resultSet = new ResultSet;        
    $resultSet->initialize($result);        
    $rowset = $resultSet->toArray();

    $label_map = [];
    foreach ($rowset as $row) {
        $label_map[strtolower(trim($row['name']))] = $row['id'];
    }

    return $label_map;
}
 public function changeDateFormat($date)
 {
	 if(strstr($date,'/'))
	 {
		 $date = str_replace('/','-',$date);
	 }
	 $date_array = explode('-',$date);
	 if(count($date_array) == 2)
	 {
		$date = date('Y-m-01',strtotime($date));
	 }
	 return $date;
 }
}//End Class