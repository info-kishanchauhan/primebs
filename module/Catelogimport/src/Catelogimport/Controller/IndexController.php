<?php
namespace Catelogimport\Controller;
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
	public function importAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
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
			
			$ignore_cnt=1;
			$import_cnt=1;
            if (!move_uploaded_file($file['tmp_name'], $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {

				 
					if (($handle = fopen($myImagePath, "r")) !== FALSE) 
					{ 
							$count=0;
							 
							while (($data = fgetcsv($handle, 10000, ';')) !== FALSE) 
							{
								
									$num = count($data);
									
									if($count==0)
										$count++;
									else
									{
										$title  = utf8_encode(trim($data[0]));
										$version  = utf8_encode(trim($data[1]));
										$artist  = utf8_encode(trim($data[2]));
										$label  = utf8_encode(trim($data[3]));
										$release_date  = utf8_encode(trim($data[4]));
										$no_of_track  = utf8_encode(trim($data[5]));
										$upc  = utf8_encode(trim($data[6]));
										$upc = explode(':',$upc);
										$upc = trim($upc[1]);
										$pcn = utf8_encode(trim($data[7]));
										$store = utf8_encode(trim($data[9]));
										
										$release_date = substr($release_date,0,10);
										
										
										$label_id = $this->getLabelID($label);
										
										if($title != '')
										{
											$duplicate = $this->checkUpcDuplicate($upc);
											
											if($duplicate > 0 || $store  == '0' || $store == '')
											{
												$IGNORE_LIST .='<tr><td>'.$ignore_cnt.'</td><td>'.$title.'</td><td>'.$label.'</td><td>'.$artist.'</td><td>'.$no_of_track.'</td><td>'.$upc.'</td>';
												
												$IGNORE_LIST .='</tr>';
												$ignore_cnt++;
											}
											else
											{
												if($upc == 'empty')
													$upc='';
												
												$IMPORT_LIST .='<tr><td>'.$import_cnt.'</td><td>'.$title.'</td><td>'.$label.'</td><td>'.$artist.'</td><td>'.$no_of_track.'</td><td>'.$upc.'</td></tr>';
												
												$import_cnt++;
												
												$rData = array();
												$rData['title'] = $title;
												$rData['import_flag'] = 1;
												$rData['version'] = $version;
												$rData['releaseArtist'] = $artist;
												$rData['labels'] = $label_id;
												$rData['digitalReleaseDate'] = $release_date;
												$rData['upc'] = $upc;
												$rData['pcn'] = $pcn;
												$rData['created_on'] = date('Y-m-d H:i:s');
												$projectTable->insert($rData);
												
												$master_id = $projectTable->lastInsertValue;
												for($i=1;$i<=$no_of_track;$i++)
												{
													$tData=array();
													$tData['master_id'] = $master_id;
													$tData['volume'] = $i;
													$tData['order_id'] = $i;
													$tData['songName'] = $title;
													$tData['version'] = $version;
													$tData['primaryTrackType'] = 'Music';
													$tData['trackArtist'] = $artist;
													
													$trackTable->insert($tData);
												}
											}
										}

									}
									
									/*$count++;*/
							}
							
					}
					
					
					if($import_cnt == 1)
					{
						$IMPORT_LIST="<tr><td colspan='6'>No record found</td></tr>";
					}
					if($ignore_cnt == 1)
					{
						$IGNORE_LIST="<tr><td colspan='6'>No record found</td></tr>";
					}
					
					$result['IMPORT_LIST'] = $IMPORT_LIST;
					$result['IGNORE_LIST'] = $IGNORE_LIST;
					$result['file_name'] = $fileName1;
					$result['status'] = 'OK';
					$result['message1'] = 'Done';	
				}
	
        }
        $result = json_encode($result);
        echo $result;
        exit;
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
	public function checkUpcDuplicate($upc)
	{
		if($upc == 'empty')
			return 0;
		
		  $sl = $this->getServiceLocator();       
		  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		  $sql="select * from tbl_release where upc = '".$upc."'  ";		        
		  $optionalParameters=array();        
		  $statement = $adapter->createStatement($sql, $optionalParameters);        
		  $result = $statement->execute();        
		  $resultSet = new ResultSet;        
		  $resultSet->initialize($result);        
		  $rowset=$resultSet->toArray();        
		 
		  return count($rowset);
		
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
    $aColumns = array('id','cover_img','title','label_name','digitalReleaseDate','tot_tracks','"" as isrc','upc','"" as r_status','"" as r_title','releaseArtist');
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    /* DB table to use */
    $sTable = "view_release";
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
	if($_GET['type'] == 'review')
	{
		$sOrder = " order by digitalReleaseDate asc";
	}
	else
	{
		$sOrder = " ORDER BY FIELD(status, 'draft', 'inreview', 'delivered','taken out'),digitalReleaseDate ";
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
	
	 $sWhere.=" AND  import_flag=1 and status !='delivered' ";
	if($_SESSION['user_id'] != '0' && $_SESSION['STAFFUSER'] == '0')
	{
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
	
		$sWhere.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) )";
	}
	if($_SESSION["STAFFUSER"] == 1 )
	{
		$staff_cond = $customObj->getStaffReleaseCond();
		$sWhere.= $staff_cond;
	}
	
	if($_GET['search'] != '')
	{ 
		$search= (trim($_GET['search']));
		
		$sWhere.=" AND ( (title like '%".$search."%') or ( version like '%".$search."%')  or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%')  )";
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
			else if ( $aColumns[$i] == "digitalReleaseDate" )
            {
				if($aRow['digitalReleaseDate'] =='0000-00-00')
					$row[] ='';
				else
					$row[] = '<strong class="text-muted">'.date('d/m/Y',strtotime($aRow['digitalReleaseDate'])).'<strong>';
			}
			else if ( $aColumns[$i] == "title" )
            {
				$artist = '<i>empty</i>';
				if($aRow['releaseArtist'] !='')
				{
					$artist = '<strong class="text-muted">By</strong> '.$aRow['releaseArtist'];
				}
				$row[] ='<a href="releases/view?id='.$aRow['id'].'" target="_blank" >'.$aRow['title'].'</a><br>'.$artist;
			}
			else if ( $aColumns[$i] == "upc" )
			{
				$upc_no='<i>empty</i>';
				if($aRow['upc'] !='')
				{
					$upc_no = $aRow['upc'];
				}
				$row[] = '<strong class="text-muted">UPC : </strong>'.$upc_no;
			}
			
			else if ( $aColumns[$i] == "cover_img" )
			{
				if($aRow['cover_img'] == '')
			    {
				    $row[] = '<img src="public/img/no-image.png" width="40">';
				}
				else
				{
					 $row[] = '<img src="public/uploads/thumb_'.$aRow['cover_img'].'" width="40">';
				}
			}
			else if ( $aColumns[$i] == "tot_tracks" )
			{
				 $row[] = '<strong class="text-muted">'.$aRow['tot_tracks'].' Track'.'<strong>';
			}
			else if (strstr($aColumns[$i],"r_status") )
			{
				 $row[] = $aRow['status'];
			}
			else if (strstr($aColumns[$i],"r_title") )
			{
				 $row[] = $aRow['title'];
			}
			else if (strstr($aColumns[$i],"isrc") )
			{
				 $row[] = $this->getTrackIsrc($aRow['id']);
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
 
}//End Class