<?php
namespace Releases\Controller;
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
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$cond='';
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			
			$subUsers = $this->getSubUsers();
			
			$cond.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) OR created_by in (".$subUsers.") )";
		}
		
		$sql="select count(*) as cnt from tbl_release where 1=1 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$total_tracks = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='inreview' and in_process=0 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$in_review = $rowset[0]['cnt'];
		
		
		$sql="select count(*) as cnt from tbl_release where status='inreview' and in_process=1 $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$in_process = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='delivered'  $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$live_tracks = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='taken out' $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$taken_down = $rowset[0]['cnt'];
		
		$sql="select count(*) as cnt from tbl_release where status='draft' $cond ";
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		$draft = $rowset[0]['cnt'];


		$INFO['total_tracks'] = $total_tracks;
		$INFO['in_review'] = $in_review;
		$INFO['in_process'] = $in_process;
		$INFO['live_tracks'] = $live_tracks;
		$INFO['draft'] = $draft;
		$INFO['taken_down'] = $taken_down;
		$viewModel= new ViewModel(array(
			'INFO' => $INFO,
				
		));
		return   $viewModel;	
		
    }
	public function deliveryreportAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$customObj = $this->CustomPlugin();
		
		$projectTable = new TableGateway('view_release', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['id']."' "));
		$rowset = $rowset->toArray();
      
      
    $title = trim($rowset[0]['title']);
$artist = trim($rowset[0]['releaseArtist']);
$apple_link = '';

// Step 1️⃣: DB check
$releaseTable = new \Zend\Db\TableGateway\TableGateway('tbl_release', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
$checkRow = $releaseTable->select(['id' => $rowset[0]['id']])->current();
if (!empty($checkRow['apple_link'])) {
    $apple_link = $checkRow['apple_link'];
}

// Step 2️⃣: API if missing
if (empty($apple_link)) {
    $searchTerm = urlencode($title . ' ' . $artist);

    // Try IN first
    $apiUrlIN = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";
    $response = @file_get_contents($apiUrlIN);
    $result = json_decode($response, true);

    // If not found, try US
    if (!$result || $result['resultCount'] == 0) {
        $apiUrlUS = "https://itunes.apple.com/search?term=$searchTerm&country=US&media=music&entity=album";
        $response = @file_get_contents($apiUrlUS);
        $result = json_decode($response, true);
    }

    // If found, use it
    if ($result && isset($result['results'][0]['collectionViewUrl'])) {
        $apple_link = $result['results'][0]['collectionViewUrl'];
        // ✅ Update in DB
        $releaseTable->update(['apple_link' => $apple_link], ['id' => $rowset[0]['id']]);
    }
}

// Step 3️⃣: Fallback if nothing found
if (empty($apple_link)) {
    $apple_link = "https://music.apple.com/in/search?term=" . urlencode($title);
}

		$rowset[0]['apple_link'] = $apple_link;
		
		$rowset[0]['youtube_link'] = 'https://www.youtube.com/results?search_query='.str_replace(' ','+',$rowset[0]['title']).'+'.str_replace(' ','+',$rowset[0]['releaseArtist']);
		$rowset[0]['spotify_link']='https://open.spotify.com/search/'.str_replace(' ','%20',$rowset[0]['title']).'%20'.str_replace(' ','%20',$rowset[0]['releaseArtist']);
      


      
		$viewModel = new ViewModel(array(
			
			'INFO' => $rowset[0],
			
		));
		return $viewModel;
	}
	public function importapprovalAction()
	{
		$customObj = $this->CustomPlugin();
		$request = $this->getRequest();
		$aData = json_decode($this->request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		$sl = $this->getServiceLocator(); 
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$projectTable = new TableGateway('tbl_release', $adapter);
		
		$viewTable = new TableGateway('view_release', $adapter);
		
        if ($request->isPost()) {
            $file = $_FILES['approvefile'];
            $filename = $_FILES['approvefile']['name'];
			
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
										$title  = addslashes(utf8_encode(trim($data[0])));
										$version  = utf8_encode(trim($data[1]));
										$artist  = utf8_encode(trim($data[2]));
										$label  = addslashes(utf8_encode(trim($data[3])));
										$release_date  = utf8_encode(trim($data[4]));
										$no_of_track  = utf8_encode(trim($data[5]));
										$upc  = utf8_encode(trim($data[6]));
										$pcn  = utf8_encode(trim($data[7]));
										
										$upc = explode(':',$upc);
										$upc = trim($upc[1]);
										
										$release_date = substr($release_date,0,10);
										$label_id = $this->getLabelID($label);
										
										if($title != '' && $upc != '' && strtoupper($upc) != 'EMPTY' && $label_id > 0)
										{
											$artist_condition = "";
											$artist = explode(',', $artist);
											$cond_array = array();

											// Loop through the artist array and build the condition
											foreach ($artist as $single_artist) {
												$single_artist = trim($single_artist); // Trim any extra spaces
												if (!empty($single_artist)) {
													$cond_array[] = "FIND_IN_SET(UPPER('" . strtoupper($single_artist) . "'), UPPER(releaseArtist))";
												}
											}

											// Only if we have valid conditions, create the final query part
											if (count($cond_array) > 0) {
												$artist_condition = " AND ( " . implode(" AND ", $cond_array) . " )";
											}
											
											$cond = '';
											
											if($pcn != '')
											{
												$cond = " AND pcn ='".$pcn."' ";
											}
											else
											{
												$cond = " and labels='".$label_id."' and UPPER(title)='".strtoupper($title)."'  and UPPER(version)='".strtoupper($version)."' $artist_condition ";
											}
											
											
											
											$aData=array();
											$aData['upc'] = $upc;
											$aData['status'] = 'delivered';
											$aData['in_process'] = 0;
											$affectedRows = $projectTable->update($aData,array("in_process='1' $cond "));
											
											if ($affectedRows > 0) {
												
											} else {
												/*$IGNORE_LIST .='<tr><td>'.$ignore_cnt.'</td><td>'.$title.'</td><td>'.$label.'</td><td>'.$artist.'</td><td>'.$upc.'</td><td>'.$pcn.'</td>';
												
												$IGNORE_LIST .='</tr>';
												$ignore_cnt++;*/
											}
											$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'public/cron_file/convertaudioall.php');
											$customObj->start();	
										}
									}
									
									/*$count++;*/
							}
							
					}
					
					$rowset = $viewTable->select(array("in_process='1'"));
					$rowset = $rowset->toArray();
					if(count($rowset) > 0)
					{
						foreach($rowset as $row)
						{
							$IGNORE_LIST .='<tr><td>'.$ignore_cnt.'</td><td>'.$row['title'].'</td><td>'.$row['label_name'].'</td><td>'.$row['releaseArtist'].'</td><td>'.$row['upc'].'</td><td>'.$row['pcn'].'</td></tr>';
							
							$ignore_cnt++;
						}
					}
					else   //if($ignore_cnt == 1) 
					{
						$IGNORE_LIST="<tr><td colspan='6'>No record found</td></tr>";
					}
					$result['file_name'] = $fileName1;
					$result['IGNORE_LIST'] = $IGNORE_LIST;
					$result['status'] = 'OK';
					$result['message1'] = 'Done';	
				}
	
        }
        $result = json_encode($result);
        echo $result;
        exit;
	}
	public function viewAction()
    {
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$customObj = $this->CustomPlugin();
		
		$projectTable = new TableGateway('view_release', $adapter);
		$rejectreasonTable = new TableGateway('tbl_reject_reason', $adapter);
		$notesTable =  new TableGateway('tbl_internal_notes', $adapter);
		$trackTable = new TableGateway('tbl_track', $adapter);
		$rowset = $projectTable->select(array("id='".$_GET['id']."' "));
		$rowset = $rowset->toArray();
		$status = $rowset[0]['status'];
		$in_process = $rowset[0]['in_process'];
		
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		$labels = explode(',',$labels);
		
		if( (in_array($rowset[0]['labels'],$labels) || $_SESSION['user_id'] == $rowset[0]['created_by']) || $_SESSION['user_id'] == '0')
		{
		}
		else
		{
			header("location: ../dashboard");
			exit;
		}
		
		$rowset2 = $trackTable->select(array("master_id='".$_GET['id']."' and order_id > 0"));
		$rowset2 = $rowset2->toArray();
		$TOTAL_TRACK = count($rowset2);
		
		
		$rowset2 = $trackTable->select(array("master_id='".$_GET['id']."' and order_id > 0 group by volume order by volume asc"));
		$rowset2 = $rowset2->toArray();
		$TRACK_LIST='';
		
		
		
		if(count($rowset2) > 0)
		{
			foreach($rowset2 as $row2)
			{
				
			  
				$TRACK_LIST.='<div class="panel panel-default tracklist-volume-panel">
								<div class="panel-heading">Volume '.$row2['volume'].'</div>
								<table class="table table-hover" id="listCreator-662746bde753c">
									<thead>
										<tr>
											<th style="" class="column-checkbox column-checkbox">&nbsp;</th>
											<th style="" class="column-igt column-igt"> </th>
											<th style="" class="column-player column-player"></th>
											<th style="" class="column-track-number column-track-number">Track#</th>
											<th style="" class="column-track-title column-track-title">Track title</th>
											<th style="" class="column-track-version column-track-version">Version</th>
											<th style="" class="column-artist column-artist">Artist</th>
											<th style="" class="column-author column-author">Authors</th>
											<th style="" class="column-composer column-composer">Composers</th>
											<th style="" class="column-duration column-duration">Duration</th>
											<th style="" class="column-isrc column-isrc">ISRC</th>
											<th style="" class="column-preview column-preview">Preview</th>
										</tr>
									</thead>
									<tbody>
								';
								
				$rowset3 = $trackTable->select(array("master_id='".$_GET['id']."' and volume='".$row2['volume']."' and order_id != 0 order by order_id asc"));
				$rowset3 = $rowset3->toArray();
				
				$explicitContent = $rowset3[0]['explicitContent'];
				if($explicitContent == '1')
					$explicitContent='Yes';
				if($explicitContent == '0')
					$explicitContent='No';
				if($explicitContent == '2')
					$explicitContent='Cleaned';
				
				$rowset[0]['trackType'] = $rowset3[0]['trackType'];
				$rowset[0]['idInstrumental'] = ($rowset3[0]['idInstrumental'] == '0')?'No':'Yes';
				$rowset[0]['produceBy'] = $rowset3[0]['produceBy'];
				$rowset[0]['editor'] = $rowset3[0]['editor'];
				$rowset[0]['remixer'] = $rowset3[0]['remixer'];
				$rowset[0]['explicitContent'] = $explicitContent;
				$rowset[0]['idLyricsSelect'] =($rowset3[0]['idLyricsSelect'] != '0')?$rowset3[0]['idLyricsSelect']:'';
				
				
				foreach($rowset3 as $row3)
				{
					if($status == 'delivered' || $status == 'taken out' || $_SESSION['user_id'] == '0'){} 
					else
					  $row3['isrc'] = '';
			  
					$duration ='';
					$audio = '';
					if($row3['audio_file'] == '')
					{
						$audio = '<td style="" align="center" class="column-player column-player"><span class="fa-stack" data-toggle="tooltip" data-placement="top" title="This track has not been digitized">
									<i class="fa fa-play-circle-o fa-stack-2x"></i>
									<i class="fa fa-ban fa-stack-2x text-danger"></i>
								</span></td>';
								
						$music = '<i class="fa fa-music fa-2x js-tracklist-music-icon"></i>';
					}
					else
					{
						
						$audio ='<td style="" class="column-player column-player"><div class="player-container player-cover"><div id="audioContainer_coverPlayer_'.$row3['id'].'"><audio src="../public/uploads/audio/'.$row3['audio_file'].'"></audio></div><span class="player-playback"><span class="player-playback-cover player-without-image" style="display:inline-block; height:26px; width: 26px;"><span class="player-playback-playpause btn-play button-playpause" style="display:inline-block; width:26px; height:26px; font-size:26px;"></span></span></span></div></td>';
						
						$duration = explode('<br>',$row3['audio_format_info']);
						$duration = str_replace('<p>','',$duration[0]);
						
						$music = '<a href="../public/uploads/audio/'.$row3['audio_file'].'" download="'.$row3['audio_file_name'].'"><i class="fa fa-download fa-2x js-tracklist-music-icon" data-toggle="tooltip" data-placement="top" title="" data-original-title="Download Music" ></i></a>';
					}
					if($row3['isrc'] == '')
						$row3['isrc'] = 'empty';
					
					
					$TRACK_LIST.='<tr>
									<td style="" class="column-checkbox column-checkbox">
										<input class="hidden js-tracklist-checkbox" type="checkbox" id="checkbox-song-'.$row3['id'].'" value="'.$row3['id'].'">
										'.$music.'
									</td>
									<td style="" class="column-igt column-igt"></td>
									'.$audio.'
									<td style="" class="column-track-number column-track-number">'.$row3['order_id'].'</td>
									<td style="" class="column-track-title column-track-title">'.$row3['songName'].'</td>
									<td style="" class="column-track-version column-track-version">'.$row3['version'].'</td>
									<td style="" class="column-artist column-artist">'.$row3['trackArtist'].'</td>
									<td style="" class="column-author column-author">'.$row3['author'].'</td>
									<td style="" class="column-composer column-composer">'.$row3['composer'].'</td>
									<td style="" class="column-duration column-duration">'.$duration.'</td>';
									
									/*if($_SESSION['user_id'] == '0')
									{
										$TRACK_LIST.='<td class="isrc-container"><div style="" class="column-isrc updateIsrc" track_id="'.$row3['id'].'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="Change ISRC" >'.$row3['isrc'].'</div><span class="copy-isrc-icon" data-toggle="tooltip" data-placement="top" title="" data-original-title="Copy ISRC"  onclick="copyISRC(\'' . $row3['isrc'] . '\')">📋</span></td>';
									}
									else
									{*/
										$TRACK_LIST.='<td class="isrc-container" style="display:inline-flex;align-items: center;gap: 5px;"><div style="white-space:nowrap" class="column-isrc column-isrc ">'.$row3['isrc'].'</div><span class="copy-isrc-icon1"  onclick="copyISRC(\'' . $row3['isrc'] . '\')"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>   
</svg>
</span></td>';
									//}
							$TRACK_LIST.='<th style="" class="column-preview column-preview">'.$row3['preview_start'].'</th></tr>';
				}
				$TRACK_LIST.='</tbody></table></div>';				
			}
		
		}
		
		else
		{
			$TRACK_LIST='<div class="tracklist-no-track" style="text-align:center;">This release doesn\'t have any track.</div>';
		}
		
		$rowset[0]['created_on'] = date('d/m/Y',strtotime($rowset[0]['created_on'])); 
		
		if($rowset[0]['physicalReleaseDate'] == '0000-00-00')
			$rowset[0]['physicalReleaseDate'] = '';
		else
			$rowset[0]['physicalReleaseDate']= date('d/m/Y',strtotime($rowset[0]['physicalReleaseDate'])); 
		
		if($rowset[0]['digitalReleaseDate'] == '0000-00-00')
			$rowset[0]['digitalReleaseDate'] = '';
		else
			$rowset[0]['digitalReleaseDate']= date('d/m/Y',strtotime($rowset[0]['digitalReleaseDate'])); 
		
		if($rowset[0]['cover_img'] == '')
			$rowset[0]['cover_img']='../public/img/no-image.png';
		else
			$rowset[0]['cover_img']='../public/uploads/'.$rowset[0]['cover_img'];
		
		if($rowset[0]['productionYear'] == '0')
			$rowset[0]['productionYear']='';
		
		
		if($rowset[0]['upc'] == '')
			$rowset[0]['upc'] = 'empty';
		
		
		if($_SESSION['user_id'] == '0')
		{
			$rowset[0]['upc'] = '<div style="" class="column-upc  updateUpc" track_id="'.$rowset[0]['id'].'"  data-toggle="tooltip" data-placement="top" title="" data-original-title="Change UPC" >'.$rowset[0]['upc'].'</div>';
		}
		else
		{
			$rowset[0]['upc'] = '<div style="" class="column-upc">'.$rowset[0]['upc'].'</div>';
		}
		
		if($rowset[0]['have_content_id'] == '1')
			$rowset[0]['have_content_id'] = 'Yes';
		else
			$rowset[0]['have_content_id'] = 'No';
		
		if($rowset[0]['note_date_time'] != '0000-00-00 00:00:00')
			$rowset[0]['note_date_time'] = date('d M Y h:i A',strtotime($rowset[0]['note_date_time'])); 
		else
			$rowset[0]['note_date_time'] = '';
		
		
		$rowset27 = $rejectreasonTable->select();
		$rowset27 = $rowset27->toArray();
		$reason = array();
		foreach($rowset27 as $row)
		{
			$reason[$row['id']] = $row['description'];
		}
		
		$rowset28 = $notesTable->select();
		$rowset28 = $rowset28->toArray();
		
		if($rowset[0]['mainGenre'] == '0')
			$rowset[0]['mainGenre'] = '';
		if($rowset[0]['subgenre'] == '0')
			$rowset[0]['subgenre'] = '';
		
		
		$sql="select sales_month from tbl_analytics where release_id='".$rowset[0]['id']."'  order by sales_month desc limit 1";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset3=$resultSet->toArray();
		
		$latest_month = '';
		if(count($rowset3)>0)
		 $latest_month =  date('Y-m-01',strtotime($rowset3[0]['sales_month']));
		
		$rowset[0]['trend_link'] = '../analytics/view?id='.$rowset[0]['id'].'&from_month='.$latest_month.'&to_month='.$latest_month;
		
		
		$viewModel = new ViewModel(array(
			'STATUS' => $status,
			'in_process' => $in_process,
			'INFO' => $rowset[0],
			'TOTAL_TRACK' => $TOTAL_TRACK,
			'TRACK_LIST' => $TRACK_LIST,
			'REASON' => $reason,
			'NOTES' => $rowset28
		));
		return $viewModel;
    }
    public function listAction()
{

    echo $this->fnGrid();
    exit;
}
	public function uploadimgAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
		
		$file = $_FILES['file'];

		// File properties
		$file_name = $file['name'];
		$file_tmp = $file['tmp_name'];
		$file_size = $file['size'];
		$file_error = $file['error'];

		$ext = pathinfo($file_name, PATHINFO_EXTENSION); 		
		$filename = date('YmdHis').'.'.$ext;
		$myImagePath =  "public/uploads/$filename";

			if (!move_uploaded_file($file_tmp, $myImagePath)) {
                $result['status'] = 'ERR';
                $result['message1'] = 'Unable to save file![signature]';
            } else {
                $result['status'] = 'OK';
                $result['message1'] = 'Done';				
				$result['file_name'] = $filename; 
            } 
           
        $projectTable = new TableGateway('tbl_release', $adapter);
		$aData['cover_img'] = $filename;
		$projectTable->update($aData,array("id='".$_POST['release_id']."' "));
		
        $result = json_encode($result);
        echo $result;
        exit;
	}
    public function getrecAction()
    {
        $sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        $recs=array();
        if ($request->isPost()) {
            $iID = $request->getPost("KEY_ID");
            $projectTable = new TableGateway('tbl_department', $adapter);
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
		$config = $this->getServiceLocator()->get('config');
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$trackTable = new TableGateway('tbl_track', $adapter);
			$analyticsTable = new TableGateway('tbl_analytics', $adapter);
			
            if ($request->getPost("pAction") == "DELETE") {
                $iMasterID = $request->getPost("KEY_ID");
				
				if( $iMasterID != '')
				{
					
					$rowset = $projectTable->select(array("id in (".$iMasterID.") "));
					$rowset = $rowset->toArray();
					if($rowset[0]['cover_img'] !='')
					{
						unlink($config['PATH'].'public/uploads/'.$rowset[0]['cover_img']);
						unlink($config['PATH'].'public/uploads/thumb_'.$rowset[0]['cover_img']);
					}
					
					$rowset = $trackTable->select(array("master_id='".$iMasterID."' "));
					$rowset = $rowset->toArray();
					foreach($rowset as $row)
					{
						if($row['audio_file'] !='')
							unlink($config['PATH'].'public/uploads/audio/'.$row['audio_file']);
					}
					
					$projectTable->delete(array("id='".$iMasterID."'"));
					$trackTable->delete(array("master_id='".$iMasterID."'"));
					$analyticsTable->delete(array("release_id='".$iMasterID."'"));
				}
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function saveNoteAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
          
                $iMasterID = $_POST['KEY_ID'];
				$notes = $_POST['notes'];
				
				if(is_array($notes))
					$notes = implode(',',$notes);
				$aData=array();
				$aData['internal_notes'] = $notes;
				$aData['note_date_time'] = date('Y-m-d H:i:s');
				
                $projectTable->update($aData,array("id='".$iMasterID."'"));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            
        }
	}
	public function updateIsrcAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_track', $adapter);
            $aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
			
			$iMasterID = $aData['idTrack'];
			$isrc = trim($aData['isrc']);
			
			if($isrc != '')
			{
				$rowset = $projectTable->select(array("id !='".$iMasterID."' and isrc like '%".$isrc."%' "));
				$rowset = $rowset->toArray();
			
				
				if(count($rowset) > 0)
				{
					$result['DBStatus'] = 'EXIST';
					$result = json_encode($result);
					echo $result;
					exit;
				}
			}
			
			$aData=array();
			$aData['isrc'] = $isrc;
			$projectTable->update($aData,array("id='".$iMasterID."'"));
			$result['DBStatus'] = 'OK';
			$result = json_encode($result);
			echo $result;
			exit;
            
        }
	}
	public function updateUpcAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
            $aData = json_decode($request->getPost("FORM_DATA"));
			$aData = (array)$aData;
                $iMasterID = $aData['idRelease'];
				$upc = $aData['upc'];
				
				$aData=array();
				$aData['upc'] = $upc;
				
				if($upc != '')
				{
					$rowset = $projectTable->select(array("id !='".$iMasterID."' and upc ='".$upc."'  "));
					$rowset = $rowset->toArray();
					if(count($rowset) > 0)
					{
						$result['DBStatus'] = 'EXIST';
						$result = json_encode($result);
						echo $result;
						exit;
					}
				}
				$aData=array();
				$aData['upc'] = $upc;
				
                $projectTable->update($aData,array("id='".$iMasterID."'"));
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            
        }
	}
	public function processingAction()
	{
		$request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "Processing") {
                $iMasterID = $request->getPost("KEY_ID");
				
				
				$aData=array();
				$aData['in_process'] = 1;
                $projectTable->update($aData,array("id=" . $iMasterID));
			
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
	}
	public function takedownAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
			
            if ($request->getPost("pAction") == "TAKEDOWN") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$aData=array();
				$aData['status'] = 'taken out';
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
				$rowset3 = $rowset3->toArray();
				
				$content ='<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
						<tr>
							<td align="left">
								<img src="'.$config['URL'].'public/img/user.png" alt="User Logo" style="max-width: 40px; height: auto;">
							</td>
						</tr>
					</table>
					
					<h2 style="color: #333;">Hello,</h2>

					<p>As per your request, we have initiated the removal process for the following title:</p>
					
					<p><strong>Title:</strong> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' - '.$rowset[0]['upc'].'</p>
					
					<p>Please note that the complete removal of this content from all music stores may take approximately 7 to 14 days, depending on the processing times of each platform.</p>
					
					<br><br><br>
					
					<p><strong>Prime Content Management Team</strong></p>

					<p>Thanks and regards,<br>
					The Prime Digital Arena team</p>';		
				
				foreach($rowset3 as $row3)
				{
					$nData = array();
					$nData['user_id'] = $row3['id'];
					$nData['type'] = 'Release TakenDown';
					$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been takendown by admin.';
					$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
					$notificationTable->insert($nData);
					$customObj->sendSmtpEmail($config,$row3['email'],'Request Accepted Removal Process Initiated ',$content,$row3['label_manager_email']);
				}
				
				
					
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function approvedAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
			$trackTable = new TableGateway('tbl_track', $adapter);
			
            if ($request->getPost("pAction") == "APPROVED") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
              $title = $rowset[0]['title'];
$artist = $rowset[0]['releaseArtist'];

$searchTerm = urlencode($title . ' ' . $artist);
$apiUrl = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";

$appleUrl = '';
try {
    $response = file_get_contents($apiUrl);
    $result = json_decode($response, true);

    if (isset($result['resultCount']) && $result['resultCount'] > 0) {
        $appleUrl = $result['results'][0]['collectionViewUrl'];
    }
} catch (Exception $e) {
    $appleUrl = '';
}
				
				$aData=array();
				$aData['status'] = 'delivered';
				$aData['in_process'] = 0;
                $aData['apple_link'] = $appleUrl;
				
				if($rowset[0]['import_flag'] == '1')
					$aData['import_flag'] = 2;
				
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$customObj->setCmd('php '.$_SERVER['DOCUMENT_ROOT'].'public/cron_file/convertaudio.php '.$iMasterID);
				$customObj->start();	
				
				$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
				$rowset3 = $rowset3->toArray();
				
				
				$content ='<h2 style="color: #333;">Hello,</h2>
				<p>We’re happy to let you know that we’ve approved the following Release:</p>
				
				<p><strong>Title:</strong> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' - '.$rowset[0]['upc'].'</p>
				
				<p>For digital products, please keep in mind that delivery of your products to stores will begin within 7 days and can be tracked in the delivery reports listed for each product in your catalogue. Your product should go live in no time but may take up to 4-6 weeks depending on turnaround times of each store.</p>
				
				<p>For physical products, the product information has been sent along to the appropriate sales agents and/or warehouses.</p>
				
				<br><br><br>
				<p><strong>Prime Content Management Team</strong></p>

				<p>Thanks and regards,<br>
				The Prime Digital Arena team</p>';
				
					
					
				
				foreach($rowset3 as $row3)
				{
					$nData = array();
					$nData['user_id'] = $row3['id'];
					$nData['type'] = 'Release Approved';
					$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been approved.';
					$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
					$notificationTable->insert($nData);
					
					$customObj->sendSmtpEmail($config,$row3['email'],'Congratulations Your Release has been approved.',$content,$row3['label_manager_email']);
				}
			
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
	public function rejectedAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
		$config = $this->getServiceLocator()->get('config');
		
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_release', $adapter);
			$notificationTable = new TableGateway('tbl_notification', $adapter);
			$staffTable = new TableGateway('tbl_staff', $adapter);
            if ($request->getPost("pAction") == "REJECTED") {
                $iMasterID = $request->getPost("KEY_ID");
				
				$rowset = $projectTable->select(array("id='".$iMasterID."'"));
				$rowset = $rowset->toArray();
				
				$aData=array();
				$aData['status'] = 'draft';
				$aData['in_process'] = 0;
				$aData['rejected_flag'] = 1;
				$aData['reject_reason'] = $request->getPost("reason");
                $projectTable->update($aData,array("id=" . $iMasterID));
				
				$rowset3 = $staffTable->select(array("FIND_IN_SET(".$rowset[0]['labels'].",labels) "));
				$rowset3 = $rowset3->toArray();
				
				
					 $content ='<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
						<tr>
							<td align="left">
								<img src="'.$config['URL'].'public/img/user.png" alt="User Logo" style="max-width: 40px; height: auto;">
							</td> 
							<td align="right" style="font-size: 14px; color: #666;">
								<p>User Mail: <a href="mailto:info@primedigitalarena.in" style="color: #007bff; text-decoration: none;">info@primedigitalarena.in</a></p>
								<p>'.date('M d, Y, H:i').'</p>
							</td>
						</tr>
					</table>
					
					<h2 style="color: #333;">Hello,</h2>

					<p>The following release could not be validated:</p>
					
					<p> '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].'</p>
					
					<p>'.$aData['reject_reason'].'</p>
					
					
					<p>We remain at your disposal for any further questions.</p>
					
					<br><br><br>
					
					<p><strong>Prime Content Management Team</strong></p>

					<p>Thanks and regards,<br>
					The Prime Digital Arena team</p>';
					
					
				
				foreach($rowset3 as $row3)
				{
					$nData = array();
					$nData['user_id'] = $row3['id'];
					$nData['type'] = 'Release Rejected';
					$nData['title'] = 'Your Release <b>'.$rowset[0]['title'].'</b> has been rejected.';
					$nData['url'] = $config['URL'].'releases?new='.$iMasterID;
					$notificationTable->insert($nData);
					
					$customObj->sendSmtpEmail($config,$row3['email'],'Action needed on your last release : '.$rowset[0]['title'].' - '.$rowset[0]['releaseArtist'].' ',$content,$row3['label_manager_email']);
				}
				
                $result['DBStatus'] = 'OK';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }
    }
    public function saveAction()
    {
        $request = $this->getRequest();
		$customObj = $this->CustomPlugin();
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $adapter = $sl->get('Zend\Db\Adapter\Adapter');
            $projectTable = new TableGateway('tbl_department', $adapter);
    if($request->getPost("pAction") == "ADD")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
		unset($aData['MASTER_KEY_ID']);
        $aData['created_by']=$_SESSION['user_id'];
        $aData['created_on']=date("Y-m-d h:i:s");
        $projectTable->insert($aData);
		
		//$customObj->createlog("module='Releases',action='Releases ".$aData['name']." Added',action_id='".$iMasterID."' ");
        $result['DBStatus'] = 'OK';
    }
    else  if($request->getPost("pAction") == "EDIT")
    {
        $aData = json_decode($request->getPost("FORM_DATA"));
        $aData = (array)$aData;
        $iMasterID=$aData['MASTER_KEY_ID'];
        unset($aData['MASTER_KEY_ID']);
        $aData['updated_by']=$_SESSION['user_id'];
        $projectTable->update($aData,array("id=".$iMasterID));
		
		//$customObj->createlog("module='Releases',action='Releases ".$aData['name']." Edited',action_id='".$iMasterID."' ");
        $result['DBStatus'] = 'OK';
    }
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
	public function exportAction()
	{
		$customObj = $this->CustomPlugin();
		
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$type = $_GET['type'];
		$search = (trim($_GET['search']));
		$data='Release Title,Version,Artist,Primary artist,Composer,Author,Label,Release Date/Houe/Timezone,# of Track,UPC,ISRC,Cat. #';
		$data.="\n";
		
		$sWhere='';
				
		if($type == 'draft')
		{
			$sWhere.=" AND  status='draft'";
		}
		if($type == 'review')
		{
			$sWhere.=" AND  status='inreview'";
		}
		if($type == 'inprocess')
		{
			$sWhere.=" AND  status='inreview' and in_process=1";
		}
		else
		{
			if($_SESSION['user_id'] == '0')
			{
				$sWhere.=" AND in_process=0";
			}
		}
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$sWhere.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) )"; 
		}
		if($search != '')
		{
			$sWhere.=" AND ( (title like '%".$search."%') or ( version like '%".$search."%')  or ( label_name like '%".$search."%')  or ( upc like '%".$search."%') or ( releaseArtist like '%".$search."%') or ( isrc like '%".$search."%') )";
		}
		$sWhere.=" and import_flag != 1 ";
		
		$where = " 1=1 ".$sWhere." order by id desc";
		
		$projectTable = new TableGateway('view_release', $adapter);
		$rowset = $projectTable->select(array($where));
		$rowset = $rowset->toArray();
		
		
		
		foreach($rowset as $row)
		{
			$trackTable = new TableGateway('tbl_track', $adapter);
			$rowset3 = $trackTable->select(array(" master_id='".$row['id']."' and isrc !='' order by volume,order_id asc limit 1"));
			$rowset3 = $rowset3->toArray();
			
			if($row['status'] == 'delivered' || $row['status'] == 'taken out' || $_SESSION['user_id'] == '0'){} 
			else
				$rowset3[0]['isrc'] = '';
		
			$digitalReleaseDate='';
			if($digitalReleaseDate != '0000-00-00')
				$digitalReleaseDate=date('d/m/Y',strtotime($digitalReleaseDate));
			
			$data .= implode(',', [
				$this->escapeCsvValue($row['title']),
				$this->escapeCsvValue($row['version']),
				$this->escapeCsvValue($row['releaseArtist']),
				$this->escapeCsvValue($rowset3[0]['trackArtist']),
				$this->escapeCsvValue($rowset3[0]['composer']),
				$this->escapeCsvValue($rowset3[0]['author']),
				$this->escapeCsvValue($row['label_name']),
				$this->escapeCsvValue($digitalReleaseDate),
				$this->escapeCsvValue($row['tot_tracks']),
				$this->escapeCsvValue($row['upc']),
				$this->escapeCsvValue($rowset3[0]['isrc']),
				$this->escapeCsvValue($row[0]['pcn']),
			]) . "\n";
		}
		
		$file = date('Ymd')."_my_catelog_export.csv";
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
    $aColumns = array('id','"" as chkbx','status','cover_img','title','label_name','digitalReleaseDate','tot_tracks','isrc','upc','"" as delivery_status','"" as r_status','"" as r_title','releaseArtist','rejected_flag','reject_reason','version','pcn','in_process','internal_notes');
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
		$sOrder = " ORDER BY FIELD(status, 'draft', 'inreview', 'delivered','taken out'),digitalReleaseDate desc";
	}
    /*
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
  
  $sWhere = "";
if (!empty($_POST['search']['value'])) {
    $search_value = $_POST['search']['value'];
    $sWhere .= "WHERE (title LIKE '%$search_value%' OR artist LIKE '%$search_value%' OR upc LIKE '%$search_value%')";
}


  
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
	
	if($_GET['type'] == 'draft')
	{
		$sWhere.=" AND  (status='draft' || status='rejected' )";
	}
	if($_GET['type'] == 'review')
	{
		$sWhere.=" AND  status='inreview'";
	}
	if($_GET['type'] == 'inprocess')
	{
		$sWhere.=" AND  status='inreview' and in_process=1";
	}
	else
	{
		if($_SESSION['user_id'] == '0')
		{
			$sWhere.=" AND in_process=0";
		}
	}
	if($_SESSION['user_id'] != '0')
	{
		$labels = $customObj->getUserLabels($_SESSION['user_id']);
		
		$subUsers = $this->getSubUsers();
		
		
		$sWhere.="  AND ( created_by = '".$_SESSION['user_id']."' OR (status in ('delivered','taken out') AND labels in (".$labels.") ) OR created_by in (".$subUsers.") )";
	}
	if($_GET['notification'] > 0)
	{
		$sWhere.=" AND  id= '".$_GET['notification']."' ";
	}
	if (isset($_GET['search'])) {
		$search = trim($_GET['search']);
		$search = mysqli_real_escape_string($mysqli, $search); // Escape special characters to prevent SQL injection

		$sWhere .= " AND ( 
			title LIKE '%$search%' OR 
			version LIKE '%$search%' OR 
			label_name LIKE '%$search%' OR 
			upc LIKE '%$search%' OR 
			pcn LIKE '%$search%' OR 
			releaseArtist LIKE '%$search%' OR 
			isrc LIKE '%$search%' 
		)";
	}
	$sWhere.=" and import_flag != 1 ";
	
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
				$version = $aRow['version'];
				if(trim($version) != '')
				{
					$version = " ( ".$version.") "; 
				}
				if($aRow['releaseArtist'] !='')
				{
					$artist = '<strong class="text-muted">By</strong> '.$aRow['releaseArtist'];
				}
				$row[] ='<a href="releases/view?id='.$aRow['id'].'" target="_blank" >'.$aRow['title'].'</a>'.$version.'<br>'.$artist;
			}
			else if ( $aColumns[$i] == "upc" )
			{
				$upc_no='<i>empty</i>';
				if($aRow['upc'] !='')
				{
					$upc_no = $aRow['upc'];
				}
				$cat='<i>empty</i>';
				if($aRow['pcn'] !='')
				{
					$cat = $aRow['pcn'];  
				}
				
				$row[] = '<strong class="text-muted">UPC : </strong>'.$upc_no.'<br><strong class="text-muted">Cat# : </strong>'.$cat;
			}
			else if ( $aColumns[$i] == "status" )
            {
               if($aRow['status'] == 'delivered')
			   {
				    $row[] = '<div href="#" class="" title="This release is delivered" data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;" class="fa fa-check"></i></div>';
			   }
               if($aRow['status'] == 'draft' && $aRow['rejected_flag'] == '0')
			   {
				    $row[] = '<div href="#" class="" title="This release needs to be finished" data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;" class="fa fa-industry"></i></div>';
			   }
			   if($aRow['status'] == 'draft' && $aRow['rejected_flag'] == '1')
			   {
				    $row[] = '<div href="#" class="" title="This release is rejected.'.$aRow['reject_reason'].'" data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;color:#d97878;" class="fa fa-industry"></i></div>';
			   }
			   
			   
			   if($aRow['status'] == 'inreview')
			   {
				   $note_color='<div style="width:8px;height:8px;border-radius:100%;float: left;margin-top: 5px;margin-right: 5px;position: absolute;"></div>';
				   if($aRow['in_process'] == '0' && $aRow['internal_notes'] != '' )
					    $note_color=$this->getNoteColor($aRow['internal_notes']);
					
					
				    $row[] =  $note_color.'<div href="#" class="" title="This release is in review." data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;" class="fa fa-clock-o"></i></div>'; 
			   }
			  
			   if($aRow['status'] == 'rejected')
			   {
				    $row[] = '<div href="#" class="" title="This release is rejected. Please contact your Label Manager for more information" data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;" class="fa fa-ban"></i></div>';
			   }
			   if($aRow['status'] == 'taken out')
			   {
				    $row[] = '<div href="#" class="" title="This release was taken down. Please contact your Label Manager for more information" data-toggle="tooltip" data-placement="bottom"><i style="font-size: 20px; vertical-align:middle;" class="fa fa-ban"></i></div>';
			   }
            }
			else if ( $aColumns[$i] == "cover_img" )
			{
				if($aRow['cover_img'] == '')
			    {
				    $row[] = '<img src="public/img/no-image.png" width="40">';
				}
				else
				{
					if (!file_exists("public/uploads/thumb_".$aRow['cover_img'])) 
					{
						if (file_exists("public/uploads/".$aRow['cover_img'])) 
							$this->create_thumbnail("public/uploads/".$aRow['cover_img'], "public/uploads/thumb_".$aRow['cover_img'], 150, 150); 
					}
					
					 $row[] = '<img src="public/uploads/thumb_'.$aRow['cover_img'].'" width="40">';
				}
			}
			else if ( $aColumns[$i] == "tot_tracks" )
			{
				 $row[] = '<strong class="text-muted">'.$aRow['tot_tracks'].' Track'.'<strong>';
			}
			else if (strstr($aColumns[$i],"delivery_status") )
			{
				if($aRow['status'] == 'delivered')
				{
					$youtube_link = 'https://www.youtube.com/results?search_query='.str_replace(' ','+',$aRow['title']).'+'.str_replace(' ','+',$aRow['releaseArtist']);
					$spotify='https://open.spotify.com/search/'.str_replace(' ','%20',$aRow['title']).'%20'.str_replace(' ','%20',$aRow['releaseArtist']);
                   
				if (empty($aRow['apple_link'])) {
					$title = trim($aRow['title']);
					$artist = trim($aRow['releaseArtist']);
					$apple_link = '';

					// Step 1️⃣: Check if DB already has it (optional if already checked above)
					$releaseTable = new \Zend\Db\TableGateway\TableGateway('tbl_release', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
					$checkRow = $releaseTable->select(['id' => $aRow['id']])->current();
					if (!empty($checkRow['apple_link'])) {
						$apple_link = $checkRow['apple_link'];
					}

					// Step 2️⃣: If still empty, try Apple API (India first)
					if (empty($apple_link)) {
						$searchTerm = urlencode($title . ' ' . $artist);
						$apiUrlIN = "https://itunes.apple.com/search?term=$searchTerm&country=IN&media=music&entity=album";
						$response = @file_get_contents($apiUrlIN);
						$result = json_decode($response, true);

						// Step 3️⃣: If IN fails, try US region
						if (!$result || $result['resultCount'] == 0) {
							$apiUrlUS = "https://itunes.apple.com/search?term=$searchTerm&country=US&media=music&entity=album";
							$response = @file_get_contents($apiUrlUS);
							$result = json_decode($response, true);
						}

						// Step 4️⃣: If found, update DB
						if ($result && isset($result['results'][0]['collectionViewUrl'])) {
							$apple_link = $result['results'][0]['collectionViewUrl'];
							$releaseTable->update(['apple_link' => $apple_link], ['id' => $aRow['id']]);
						}
					}

					// Step 5️⃣: Final fallback if nothing found
					if (empty($apple_link)) {
						$apple_link = "https://music.apple.com/in/search?term=" . urlencode($title);
					}

					// Set final result into row
					$aRow['apple_link'] = $apple_link;
				}
                  
					 $row[] = '<div class="d_status_wrapper">
								  <div class="d_status">
									<span class="delivery_report_icon"></span> 
									<span class="delivery_trigger_text">Completed</span> 
									<i class="material-icons" style="font-size:14px;margin-left:5px;">link</i>
								  </div>

								 <div class="delivery-popover">
									<div class="p-title">Delivery Links</div>
									
									<div class="delivery-link">
									  <img src="https://www.primebackstage.in/public/img/store2/YouTube%20Official%20Content.png" alt="YouTube" class="service-icon">
									  <a href="'.$youtube_link.'" target="_blank" class="link-text" title="'.$youtube_link.'">'.$youtube_link.'</a>
									  <button class="copy-btn" data-copy="'.$youtube_link.'">COPY</button>
									</div>
									
									<div class="delivery-link">
									  <img src="https://www.primebackstage.in/public/img/store2/spotify.png" alt="Spotify" class="service-icon spotify-icon">
									  <a href="'.$spotify.'" target="_blank" class="link-text" title="'.$spotify.'">'.$spotify.'</a>
									  <button class="copy-btn" data-copy="'.$spotify.'">COPY</button>
									</div>
                                    
                                    
<div class="delivery-link">
  <img src="https://www.primebackstage.in/public/img/store2/apple.png" alt="Apple Music" class="service-icon">
  <a href="'.$apple_link.'" target="_blank" class="link-text" title="'.$apple_link.'">'.$apple_link.'</a>
  <button class="copy-btn" data-copy="'.$apple_link.'">COPY</button>
</div>
    
									
									
									<a href="releases/deliveryreport?id='.$aRow['id'].'"  target="_blank" class="view-delivery-report">VIEW DELIVERY REPORT</a>
								  </div>
								</div>
								<a href="releases/deliveryreport?id='.$aRow['id'].'" target="_blank">View Delivery Report</a>';
				}
				else if($aRow['status'] == 'inreview')
				{
					 $row[] = 'In Review';
				}
				else
					$row[] = 'Incomplete';
				 
			}
			else if (strstr($aColumns[$i],"r_status") )
			{
				 $row[] = $aRow['status'];
			}
			else if (strstr($aColumns[$i],"r_title") )
			{
				 $row[] = $aRow['title'];
			}
			else if (strstr($aColumns[$i],"chkbx") )
			{
				if( $aRow['status'] == 'draft' || ( $aRow['status'] == 'taken out' && $_SESSION['user_id'] == '0') )
					$row[] = '<input type="checkbox" class="form-check-input rls_chkbx" data-id="'.$aRow['id'].'"  >';
				else
					$row[] = '';
			}
			//else if (strstr($aColumns[$i],"isrc") )
			else if ($aColumns[$i] == "isrc")
			{
				if($aRow['status'] == 'delivered' || $aRow['status'] == 'taken out' || $_SESSION['user_id'] == '0') 
				  $row[] = $aRow['isrc']; //$this->getTrackIsrc($aRow['id']); 
			    else
				  $row[] = '';
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
	
 public function getdepartmentAction()    
 {        
 $sl = $this->getServiceLocator();        
 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
 $sql="select id,name from tbl_department where deleted_flag=0 order by id desc";		        
 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
 $result = $statement->execute();        $resultSet = new ResultSet;        
 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
 $result1['DBData'] = $rowset;        
 $result1['recordsTotal'] = count($rowset);        
 $result1['DBStatus'] = 'OK';        
 $result = json_encode($result1);        echo $result;        exit;     
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
 public function getNoteColor($id)
 {
	  $sl = $this->getServiceLocator();       
	  $adapter = $sl->get('Zend\Db\Adapter\Adapter');       
	  $sql="select * from tbl_internal_notes where id in(".$id.")  ";		        
	  $optionalParameters=array();        
	  $statement = $adapter->createStatement($sql, $optionalParameters);        
	  $result = $statement->execute();        
	  $resultSet = new ResultSet;        
	  $resultSet->initialize($result);        
	  $rowset=$resultSet->toArray();
	  
	  return '<div style="width:8px;height:8px;border-radius:100%;background-color:'.$rowset[0]['color_code'].';float: left;margin-top: 5px;margin-right: 5px;position: absolute;"></div>';
 }
 public function getSubUsers()
 {
		$sl = $this->getServiceLocator();       
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');       
		$sql="select * from tbl_staff where created_by = '".$_SESSION['user_id']."'  ";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray(); 
		
		if(count($rowset) > 0)
		{
			$users = array();
			foreach($rowset as $row)
				$users[] = $row['id'];
				
			$users = implode(',',$users);
			
			return $users;
		}
		else
			return 11111111111111111111;
		
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
	public  function create_thumbnail($source_path, $target_path, $thumb_width, $thumb_height) 
	{
		// Get image dimensions and type
		list($width, $height, $type) = getimagesize($source_path);

		// Create a new image resource based on the original image type
		switch ($type) {
			case IMAGETYPE_JPEG:
				$source_image = imagecreatefromjpeg($source_path);
				break;
			case IMAGETYPE_PNG:
				$source_image = imagecreatefrompng($source_path);
				break;
			case IMAGETYPE_GIF:
				$source_image = imagecreatefromgif($source_path);
				break;
			default:
				die("Unsupported image type");
		}

		// Create a blank canvas for the thumbnail
		$thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);

		// Maintain aspect ratio
		$aspect_ratio = $width / $height;
		if ($thumb_width / $thumb_height > $aspect_ratio) {
			$new_width = $thumb_height * $aspect_ratio;
			$new_height = $thumb_height;
		} else {
			$new_width = $thumb_width;
			$new_height = $thumb_width / $aspect_ratio;
		}

		$x = ($thumb_width - $new_width) / 2;
		$y = ($thumb_height - $new_height) / 2;

		// Resize and copy the original image to the thumbnail
		imagecopyresampled(
			$thumbnail,
			$source_image,
			$x, $y,
			0, 0,
			$new_width, $new_height,
			$width, $height
		);

		// Save the thumbnail
		switch ($type) {
			case IMAGETYPE_JPEG:
				imagejpeg($thumbnail, $target_path, 90); // Save as JPEG
				break;
			case IMAGETYPE_PNG:
				imagepng($thumbnail, $target_path); // Save as PNG
				break;
			case IMAGETYPE_GIF:
				imagegif($thumbnail, $target_path); // Save as GIF
				break;
		}

		// Clean up
		imagedestroy($source_image);
		imagedestroy($thumbnail);

		return $target_path;
	}
	
	public function getReleasesAction()    
	{  
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select id, CONCAT(title, ' (', upc, ')') as name from tbl_release where  status='delivered' $cond ";		        
		 $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;     
	}
	public function getArtistAction()    
	{  
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select id,releaseArtist  from tbl_release where  status='delivered' $cond ";		        
		 $optionalParameters=array();        
		 $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        
		 $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        
		 $rowset=$resultSet->toArray();      

		 $artists_arr = array();
		 $art_array = array();
		 foreach($rowset as $row)
		 {
			$artists = explode(',',$row['releaseArtist']);
			foreach($artists as $artist)
			{ 
				$artist = trim($artist);
			    if(!in_array($artist,$art_array))
				{
					$art['id'] = $artist;
					$art['name'] = $artist;
					$artists_arr[] = $art;
					
					$art_array[] = $artist;
				}
			} 
		 }
		 $result1['DBData'] = $artists_arr;        
		 $result1['recordsTotal'] = count($artists_arr);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;     
	}
	public function getTrackAction()    
	{   
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select id,songName as name  from view_tracks  where  status='delivered' $cond ";		        
		  $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;         
	}
	
	public function getStoreAction()    
	{   
		$customObj = $this->CustomPlugin();
		$cond="";
		if($_SESSION['user_id'] != '0')
		{
			$labels = $customObj->getUserLabels($_SESSION['user_id']);
			$cond=" and labels in (".$labels.") ";
		}
		
		 $sl = $this->getServiceLocator();        
		 $adapter = $sl->get('Zend\Db\Adapter\Adapter');        
		 $sql="select store as id,store as name  from view_analytics  where 1=1 $cond group by store ";		        
		  $optionalParameters=array();        $statement = $adapter->createStatement($sql, $optionalParameters);        
		 $result = $statement->execute();        $resultSet = new ResultSet;        
		 $resultSet->initialize($result);        $rowset=$resultSet->toArray();        
		 $result1['DBData'] = $rowset;        
		 $result1['recordsTotal'] = count($rowset);        
		 $result1['DBStatus'] = 'OK';        
		 $result = json_encode($result1);        echo $result;        exit;         
	}
}//End Class