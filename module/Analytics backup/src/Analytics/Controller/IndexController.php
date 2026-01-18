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
		
		$last_month = '';
		if(count($rowset)>0)
		 $last_month =  date('F Y',strtotime($rowset[0]['sales_month']));
		
		$viewModel= new ViewModel(array(
			
			'last_month' => $last_month,
			
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
	public function viewAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$release_id = $_GET['id'];
		
		$from_month =  date('Y-m-01',strtotime($_GET['from_month']));
		$to_month =  date('Y-m-01',strtotime($_GET['to_month']));
		
		$sWhere .=" and  sales_month >='".$from_month."' and sales_month <='".$to_month."' ";
		
		$sql="select sum(streams)as tot_streams,store from view_analytics where release_id='".$release_id."'  $sWhere  group by  store";		        
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
			
			$sql="select sum(streams)as tot_streams from view_analytics where release_id='".$release_id."' and sales_month ='".$running_month."' ";		        
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
			
			$ext = pathinfo($filename, PATHINFO_EXTENSION); 
			
			if($ext != 'csv' && $ext != 'CSV')
			{
				$result['status'] = 'NOT';
				$result = json_encode($result);
				echo $result;
				exit;
			}
            
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

				 
					if (($handle = fopen($myImagePath, "r")) !== FALSE) 
					{ 
						$sales_month = $_POST['sel_month'];
						$sales_month =  date('Y-m-01',strtotime($sales_month));
						
						$rowset = $analyticTable->select(array("sales_month = '".$sales_month."' "));
						$rowset = $rowset->toArray();
						$sales_month_release_id = array();
						foreach($rowset as $row)
						{
							$sales_month_release_id[] = $row['release_id'];
						}
						if($_POST['sel_type'] == 'believe')
						{
							$count=0;
							
							$info = array();
							$upc_release_id = array();
							$upc_label_id = array();
							$label_name_id = array();
							
							$headerMap =array();
							
							$label_month_payment_info = array();
							 
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
										if(strstr($store,"YouTube"))
											$store ='YouTube';
										
										if(strstr($store,"Amazon"))
											$store ='Amazon';
										
										if(strstr($store,"iTunes"))
											$store ='iTunes';
										
										if(strstr($store,"iTunes"))
											$store ='iTunes';
										
										$label  = utf8_encode(trim($data[$headerMap['label name']]));
										$artist  = utf8_encode(trim($data[$headerMap['artist name']]));
										$title  = utf8_encode(trim($data[$headerMap['release title']]));
										$track_title  = utf8_encode(trim($data[$headerMap['track title']]));
										$upc  = utf8_encode(trim($data[$headerMap['upc']]));
										$isrc  = utf8_encode(trim($data[$headerMap['isrc']]));
										$sales_type = utf8_encode(trim($data[$headerMap['sales type']]));
										$qty = utf8_encode(trim($data[$headerMap['quantity']]));
										$revenue = utf8_encode(trim($data[$headerMap['net revenue']]));
										
										$upc = str_replace('empty:','',$upc);
										$upc = str_replace('Empty:','',$upc);
										$upc = trim($upc);
										
										
										
										if( ($sales_type == 'Creation' || $sales_type == 'Stream') && $upc != '')
										{
											if(in_array($upc,$upc_release_id))
											{
												$release_id = $upc_release_id[$upc];
											}
											else
											{
												$release_info = $this->getReleaseID($upc);
												$release_id = $release_info['release_id'];
												$upc_release_id[$upc] = $release_id;
												$upc_label_id[$upc] = $release_info['label_id'];
											}
											
											if($release_id > 0 ){
												
												$info[$release_id][$store][$sales_type]['qty'] += $qty;
												$info[$release_id][$store][$sales_type]['revenue'] += $revenue;
												
												
											}
											/*else
											{ 
												
												if(in_array($label,$label_name_id))
												{
													$label_id = $label_name_id[$label];
												}
												else
												{
													$label_id = $this->getLabelID($label);
													$label_name_id[$label] = $label_id;
												}
												
												$rData = array();
												$rData['analytic_flag'] = 1;
												$rData['status'] = 'delivered';
												$rData['title'] = $title;
												$rData['releaseArtist'] = $artist;
												$rData['labels'] = $label_id;
												$rData['upc'] = $upc;
												$projectTable->insert($rData);
												$master_id = $projectTable->lastInsertValue;
												
												$tData=array();
												$tData['master_id'] = $master_id;
												$tData['volume'] = 1;
												$tData['order_id'] = 1;
												$tData['songName'] = $track_title;
												$tData['trackArtist'] = $artist;
												$tData['isrc'] = $isrc;
												$trackTable->insert($tData);
												
												$release_id  = $master_id;
											}
											
											$info[$release_id][$store][$sales_type]['qty'] += $qty;
											$info[$release_id][$store][$sales_type]['revenue'] += $revenue;*/
										}
									}
							}
							
							
							foreach ($info as $release_id => $stores) 
							{
								foreach ($stores as $store => $sales_types) 
								{
									
									$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
									$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
									$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
									$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
									$total_revenue = $stream_revenue + $creation_revenue;
									

									$aData = array();
									$aData['record_type'] = $_POST['sel_type'];
									$aData['release_id'] = $release_id;
									$aData['sales_month'] = $sales_month;
									$aData['store'] = $store;
									$aData['creation'] = $creation_qty;
									$aData['streams'] = $stream_qty;
									$aData['revenue'] = $total_revenue;
									
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
											$analyticTable->update($aData,array("release_id = '".$release_id."' ","sales_month = '".$sales_month."' "));
										}
										else
										{
											$analyticTable->insert($aData);
										}
										$import_cnt++;
									}
								}
							}
							
							
						}
						if($_POST['sel_type'] == 'orchard')
						{
							$count=0;
							
							$info = array();
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
											$store ='Amazon';
										
										if(strstr($store,"iTunes"))
											$store ='iTunes';
										
										if(strstr($store,"iTunes"))
											$store ='iTunes';
										
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
										
										if( ($sales_type == 'Creation' || $sales_type == 'Stream') && $upc != '' && $qty > 0)
										{
											if(in_array($upc,$upc_release_id))
											{
												$release_id = $upc_release_id[$upc];
											}
											else
											{
												$release_info = $this->getReleaseID($upc);
												$release_id = $release_info['release_id'];
												$upc_release_id[$upc] = $release_id;
												$upc_label_id[$upc] = $release_info['label_id'];
											}
											
											if($release_id > 0 ){
												
												$info[$release_id][$store][$sales_type]['qty'] += $qty;
												$info[$release_id][$store][$sales_type]['revenue'] += $revenue;
											}
											/*else
											{ 
												
												if(in_array($label,$label_name_id))
												{
													$label_id = $label_name_id[$label];
												}
												else
												{
													$label_id = $this->getLabelID($label);
													$label_name_id[$label] = $label_id;
												}
												
												$rData = array();
												$rData['analytic_flag'] = 1;
												$rData['status'] = 'delivered';
												$rData['title'] = $title;
												$rData['version'] = $version;
												$rData['releaseArtist'] = $artist;
												$rData['labels'] = $label_id;
												$rData['upc'] = $upc;
												$projectTable->insert($rData);
												$master_id = $projectTable->lastInsertValue;
												
												$tData=array();
												$tData['master_id'] = $master_id;
												$tData['volume'] = 1;
												$tData['order_id'] = 1;
												$tData['songName'] = $track_title;
												$tData['trackArtist'] = $track_artist;
												$tData['version'] = $track_version;
												$tData['isrc'] = $isrc;
												$trackTable->insert($tData);
												
												$release_id  = $master_id;
											}
											
											$info[$release_id][$store][$sales_type]['qty'] += $qty;
											$info[$release_id][$store][$sales_type]['revenue'] += $revenue;*/
										}
									}
							}
							
							foreach ($info as $release_id => $stores) 
							{
								foreach ($stores as $store => $sales_types) 
								{
									$creation_qty = isset($sales_types['Creation']['qty']) ? $sales_types['Creation']['qty'] : 0;
									$stream_qty = isset($sales_types['Stream']['qty']) ? $sales_types['Stream']['qty'] : 0;
									$stream_revenue = isset($sales_types['Stream']['revenue']) ? $sales_types['Stream']['revenue'] : 0;
									$creation_revenue = isset($sales_types['Creation']['revenue']) ? $sales_types['Creation']['revenue'] : 0;
									$total_revenue = $stream_revenue + $creation_revenue;
									

									$aData = array();
									$aData['record_type'] = $_POST['sel_type'];
									$aData['release_id'] = $release_id;
									$aData['sales_month'] = $sales_month;
									$aData['store'] = $store;
									$aData['creation'] = $creation_qty;
									$aData['streams'] = $stream_qty;
									$aData['revenue'] = $total_revenue;
									
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
											$analyticTable->update($aData,array("release_id = '".$release_id."' ","sales_month = '".$sales_month."' "));
										}
										else
										{
											$analyticTable->insert($aData);
										}
									}
									$import_cnt++;
								}
							}
							
						}
					}
					
					$result['file_name'] = $fileName1;
					$result['status'] = 'OK';
					if($import_cnt == 0)
					{
						$result['status'] = 'NOT_OK';
					}
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
	public function getLabelID($label)
	{
		$sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_label where name = '".$label."'  ";		        
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
	public function getGrandStream($id,$from_month,$to_month)
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		
	
		$sql="select sum(streams)as tot_streams from view_analytics where release_id='".$id."' and sales_month >='".$from_month."' and sales_month <='".$to_month."'  ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();

		return $rowset[0]['tot_streams'];
	}
	public function getTotalAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		
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
		
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
		
			$sWhere.="  AND labels in (".$labels.") ";
		}
		if($_POST['search'] != '')
		{
			$search= (trim($_POST['search']));
		
			$sWhere.=" AND ( (title like '%".$search."%') or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%')  )";
		}
		
	
		$sql="select sum(creation)as tot_creation,sum(revenue)as tot_revenue,sum(streams)as tot_streams from view_analytics where 1=1  $sWhere  ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();

		$result2['tot_creation'] = $this->formatViewCount($rowset[0]['tot_creation']);
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
    $aColumns = array('id','cover_img','title','digitalReleaseDate','sum(creation)','"" as sales_month','sum(revenue)','sum(streams)','version','releaseArtist','record_type','release_id');
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
	
	
	$labels='';
	if($_SESSION['user_id'] != '0')
	{
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		
	
		$sWhere.="  AND labels in (".$labels.") ";
	}
	if($_GET['search'] != '')
	{
		$search= (trim($_GET['search']));
	
		$sWhere.=" AND ( (title like '%".$search."%') or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%')  )";
	}
	
	$sWhere .=" group by release_id";
	
	
	$user_rate = 100;
	
	if($_SESSION['user_id'] != '0')
	{
		$user_rate = $this->getUserRate();
	}
	
	$prev_data = array();
	if($_GET['from_month'] == $_GET['to_month'])
	{
		
	
		if($_SESSION['user_id'] != '0')
		{
			$cond ="  AND labels in (".$labels.") ";
		}
		$prev_month = date('Y-m-01',strtotime($_GET['from_month']." -1 month"));
		
		$sql="select release_id,sum(creation)as creation,sum(revenue)as revenue,sum(streams)as streams from view_analytics where sales_month >='".$prev_month."' and sales_month <='".$prev_month."'  $cond group by release_id ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();

		foreach($rowset as $row)
		{
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
			else if ( $aColumns[$i] == "cover_img" )
			{
				if($aRow['cover_img'] == '')
			    {
				    $row[] = '<img src="public/img/no-image.png" width="40" style="border-radius:8px;">';
				}
				else
				{
					 $row[] = '<img src="public/uploads/thumb_'.$aRow['cover_img'].'" width="40"  style="border-radius:8px;">';
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
    $aColumns = array('id','"" as icon','store','"" as per','sum(streams)','sum(creation)','sum(revenue)','"" as sales_month','version','releaseArtist','record_type','release_id');
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
	
	if($_SESSION['user_id'] != '0')
	{
		$user_rate = $this->getUserRate();
	}
	
	$grand_stream = $this->getGrandStream($_GET['release_id'],$from_month,$to_month);
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
				$img = '<img src="../public/img/no-image.png" width="40" style="border-radius:8px;">';
				
				if(strstr(strtolower($aRow['store']),"youtube"))
					$img = '<img src="../public/img/store/youtube.png" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"amazon"))
					$img = '<img src="../public/img/store/amazon.svg" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"apple"))
					$img = '<img src="../public/img/store/apple.svg" width="40"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"spotify"))
					$img = '<img src="../public/img/store/spotify.svg" width="40"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"saavn"))
					$img = '<img src="../public/img/store/savan.svg" width="40"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"iTunes"))
					$img = '<img src="../public/img/store/itunes.svg" width="40"  style="border-radius:2px;">';	
				else if(strstr(strtolower($aRow['store']),"deezer"))
					$img = '<img src="../public/img/store/deezer.svg" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"boom"))
					$img = '<img src="../public/img/store/boom.svg" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"facebook"))
					$img = '<img src="../public/img/store/facebook.jpg" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"tiktok"))
					$img = '<img src="../public/img/store/tiktok.jpg" width="40"  style="border-radius:2px;">';
				else if(strstr(strtolower($aRow['store']),"wynk"))
					$img = '<img src="../public/img/store/wynk.webp" width="40"  style="border-radius:2px;">';
				
				$row[] = '<div style="display:flex;width: 100%;justify-content: space-around;align-items: center;">'.$si_no.'. '.$img.'</div>';
			}
			else if ( strstr($aColumns[$i],"as per") )
			{
				$per = ($aRow['sum(streams)']/$grand_stream*100);
				
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
				if($_SESSION['user_id'] != '0')
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
 public function getUserRate()
 {
	$sl = $this->getServiceLocator();        
	$adapter = $sl->get('Zend\Db\Adapter\Adapter'); 
	
	$sql="select * from tbl_staff where id='".$_SESSION['user_id']."' ";		        
	$optionalParameters=array();        
	$statement = $adapter->createStatement($sql, $optionalParameters);        
	$result = $statement->execute();        
	$resultSet = new ResultSet;        
	$resultSet->initialize($result);        
	$rowset=$resultSet->toArray();  
	$user_rate=100;
	
	if(strstr($rowset[0]['user_access'],"Royalty Rate"))
	{
		$user_rate=$rowset[0]['royalty_rate_per'];
	}

	return $user_rate;
 }
}//End Class