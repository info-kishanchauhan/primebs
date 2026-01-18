<?php

namespace Dashboard\Controller;

use Zend\Db\TableGateway\TableGateway;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Datetime;
use DatePeriod;
use DateInterval;

class IndexController extends AbstractActionController
{
    protected $studentTable;

    public function indexAction()
{ 
    $sl = $this->getServiceLocator();     
    $customObj = $this->CustomPlugin();
    $adapter = $sl->get('Zend\Db\Adapter\Adapter');
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['ROLE_NAME'];

    // Tables
    $releaseTable = new TableGateway("view_release", $adapter);
    $newsTable = new TableGateway("tbl_news", $adapter);
    $analyticsTable = new TableGateway("view_analytics", $adapter);
    $withdrawTable = new TableGateway("tbl_withdraw_request", $adapter);

    $cond = '';
    if ($user_id > 0 && $_SESSION["STAFFUSER"] == 0)
        $cond = " and user_id='" . $user_id . "' ";
	
	if($_SESSION["STAFFUSER"] == 1 )
	{
		$staff_cond = $customObj->getStaffReleaseCond();
		$cond.= $staff_cond;
	}

    // 🔁 Payout Details Alert Logic
    $payoutTable = new TableGateway('tbl_user_payout', $adapter);
    $payoutRowset = $payoutTable->select(['user_id' => $user_id]);
    $payoutRowset = $payoutRowset->toArray();

    $showBankNotice = true;
    if (!empty($payoutRowset)) {
        usort($payoutRowset, function ($a, $b) {
            return intval($b['id']) - intval($a['id']);
        });
        $latestPayout = $payoutRowset[0];
        $status = strtolower(trim($latestPayout['status']));
        if ($status === 'active') {
            $showBankNotice = false;
        }
    }

    // LATEST RELEASES
    $rowset = $releaseTable->select(array("status='delivered' $cond order by digitalReleaseDate desc limit 4"));
    $rowset = $rowset->toArray();
    $latest_release = '';
    foreach ($rowset as $row) {
        if (!file_exists("public/uploads/thumb_" . $row['cover_img'])) {
            $this->create_thumbnail("public/uploads/" . $row['cover_img'], "public/uploads/thumb_" . $row['cover_img'], 150, 150); 
        }
        $latest_release .= '<a href="releases/view?id=' . $row['id'] . '" class="svelte-p1z7ut">
            <div class="svelte-p1z7ut">
                <div class="o-new-release__release svelte-p1z7ut">
                    <div class="o-new-release__releaseImg svelte-p1z7ut">
                        <img alt="' . $row['title'] . '" src="public/uploads/thumb_' . $row['cover_img'] . '" style="width: 100%;">
                    </div>
                    <div class="o-new-release__releaseStreams svelte-p1z7ut">
                        <div class="o-new-release__releaseStreamsTitle svelte-p1z7ut">' . $row['tot_tracks'] . '</div>
                        <div class="o-new-release__releaseStreamsSubtitle svelte-p1z7ut">streams</div>
                    </div>
                </div>
            </div>
        </a>';
    }

    // LATEST DRAFTS
    $rowset = $releaseTable->select(array("status='draft' $cond order by rejected_flag,digitalReleaseDate desc limit 5"));
    $rowset = $rowset->toArray();
    $latest_draft = '';
    foreach ($rowset as $row) {
        $class = "";
        $link = "newrelease/submission?edit=" . $row['id'];
        if ($row['rejected_flag'] == '1') {
            $class = " rejected_title";
            $link = "releases/view?id=" . $row['id'];
        }
        if ($row['cover_img'] == '')
            $row['cover_img'] = 'no-image.png';

        $latest_draft .= '<div class="card-item">
            <img src="public/uploads/thumb_' . $row['cover_img'] . '" width="76">
            <div class="details ' . $class . '">
                <a href="' . $link . '"><h3>' . $row['title'] . '</h3></a>
                <p>' . $row['releaseArtist'] . '</p>
            </div>
            <div class="rel_date">
                <p>Release Date</p>
                <h3>' . date('d M', strtotime($row['physicalReleaseDate'])) . '</h3>
            </div>
        </div>';
    }

    if ($latest_draft == '') {
        $latest_draft .= '<div class="card-item-news">No any draft found</div>';
    }

    // NEWS
    $rowset = $newsTable->select(array("1=1 order by news_date desc limit 5"));
    $rowset = $rowset->toArray();
    $news = '';
    foreach ($rowset as $row) {
        $news .= '<div class="card-item-news">
            <div class="news_date">
                <h3 id="' . $row['id'] . '">' . date('d', strtotime($row['news_date'])) . '<br><span>' . strtoupper(date('M', strtotime($row['news_date']))) . '</span></h3>
            </div>
            <div class="details">
                <h3 id="' . $row['id'] . '"><a href="javascript:;">' . $row['name'] . '</a></h3>
            </div>
        </div>';
    }

    if ($news == '') {
        $news .= '<div class="card-item-news">No any news found</div>';
    }

    // STREAMING CHART DATA
    $last12Months = [];
    $MONTH_DATA = [];
    $currentDate = new DateTime('first day of this month');
    $currentDate->modify('-7 months');

    $label_cond = '';
    if ($_SESSION['user_id'] != '0' && $_SESSION["STAFFUSER"] == '0') {
        $labels = $customObj->getUserLabels($_SESSION['user_id']);
        $label_cond .= " AND labels in (" . $labels . ") ";
    }
	if($_SESSION["STAFFUSER"] == 1 )
	{
		$staff_cond = $customObj->getStaffReleaseCond();
		$label_cond.= $staff_cond;
	}
    for ($i = 0; $i < 6; $i++) {
        $month_name = $currentDate->format('Y-m-01');
        $last12Months[] = $currentDate->format('M Y');
        $currentDate->modify('+1 month');

        $rowset2 = $this->executeQuery("select sum(streams) as tot from view_analytics where sales_month='" . $month_name . "' $label_cond ");
        $MONTH_DATA[] = $rowset2[0]['tot'];
    }

    $rowset3 = $this->executeQuery("select sales_month from view_analytics order by sales_month desc limit 1 ");
    $latest_month = $rowset3[0]['sales_month'];

    if ($latest_month == '')
        $latest_month = '0000-00-00';

    $rowset5 = $this->executeQuery("select sum(streams) as tot from view_analytics where  sales_month='" . $latest_month . "' $label_cond ");
    $total_stream = $rowset5[0]['tot'];

    $rowset4 = $this->executeQuery("select *,sum(streams) as tot from view_analytics where  sales_month='" . $latest_month . "' and release_id > 0  $label_cond group by release_id order by sum(streams) desc limit 3 ");

    $top_track = '';
    foreach ($rowset4 as $row4) {
        $per = $row4['tot'] > 0 ? number_format(($row4['tot'] * 100 / $total_stream), 1, '.', '') : 0;
        $top_track .= '<div class="card-item">
            <img src="public/uploads/thumb_' . $row4['cover_img'] . '" width="76">
            <div class="track_details">
                <a href="analytics"><h3>' . $row4['title'] . '</h3></a>
                <p>' . $per . '% streams of your catalog</p>
            </div>
            <div class="track_info">
                <h2>' . $this->formatViewCount($row4['tot']) . '</h2>
                <p>Streams</p>
            </div>
        </div>';
    }

    // TOP STORES
    $top_store = [];
    $i = 0;
    $rowset6 = $this->executeQuery("select sum(streams) as tot_streams, store from view_analytics where sales_month='" . $latest_month . "' $label_cond group by store order by sum(streams) desc limit 4");
    foreach ($rowset6 as $row) {
        $store_image = $this->getStoreImage($row['store']);
        $top_store[] = ['name' => $row['store'], 'y' => intval($row['tot_streams']), 'color' => $color[$i], 'img' => $store_image];
        $i++;
    }

    // BALANCE + SETTLEMENT
    $staffTable = new TableGateway('tbl_staff', $adapter);
    $rowset = $staffTable->select(array("id='" . $user_id . "'"));
    $user = $rowset->current();
    if ($user['payment_method'] == '0') $user['payment_method'] = '';

    $amount_info = $customObj->getBalanceAmount($user, $adapter);
    $bal_amount = $amount_info['amount'];

    $rowset10 = $withdrawTable->select("user_id='" . $user_id . "' and status='Success' order by approved_date desc limit 1 ");
    $rowset10 = $rowset10->toArray();
    $LAST_SETTLEMENT_DATE = '';
    $LAST_SETTLEMENT_AMOUNT = '';
    if (count($rowset10) > 0) {
        $LAST_SETTLEMENT_DATE = date('d M Y', strtotime($rowset10[0]['approved_date']));
        $LAST_SETTLEMENT_AMOUNT = '€' . $rowset10[0]['amount'];
    }
// ✅ Admin-only dashboard summary counters
$PENDING_RIGHTS = 0;
$OPEN_TICKETS = 0;
$PROCESS_TICKETS = 0;


if ($_SESSION['STAFFUSER'] == '1' || $_SESSION['user_id'] == '0') {
    // Rights Requests
    $row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_rights_requests WHERE status = 'Pending'");
    $PENDING_RIGHTS = $row[0]['cnt'];

   // Open Tickets
$row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_support_tickets WHERE LOWER(status) = 'open'");
$OPEN_TICKETS = $row[0]['cnt'];

// In Progress Tickets
$row = $this->executeQuery("SELECT COUNT(*) AS cnt FROM tbl_support_tickets WHERE LOWER(status) = 'in progress'");
$PROCESS_TICKETS = $row[0]['cnt'];



    $sqlPayout = "SELECT COUNT(*) AS unpaid_count FROM tbl_withdraw_request WHERE status = 'unpaid'";
$rowPayout = $adapter->query($sqlPayout, [])->current();
$unpaidPayouts = $rowPayout['unpaid_count'] ?? 0;

}
$PENDING_WITHDRAW_REQUESTS = 0;

if ($_SESSION['STAFFUSER'] == '1' || $_SESSION['user_id'] == '0') {
    $sql = "SELECT COUNT(*) AS cnt FROM tbl_withdraw_request WHERE status = 'Pending'";
    $row = $adapter->query($sql, [])->current();
    $PENDING_WITHDRAW_REQUESTS = (int)($row['cnt'] ?? 0);
}


     $user_id = $_SESSION['user_id'] ?? 0;
$hasInfringement = false;

if ($user_id) {
    $sql = "
        SELECT COUNT(*) AS count 
        FROM tbl_rights_requests 
        WHERE request_type = 'infringement' 
        AND added_by_admin = 1 
        AND status = 'Pending'
        AND user_id = ?
    ";

    $result = $adapter->query($sql, [$user_id])->current();
    $hasInfringement = ($result && $result['count'] > 0);
}
 
   return new ViewModel([
    'LATEST_RELEASE' => $latest_release,
    'LATEST_DRAFTS' => $latest_draft,
    'MONTH_NAME' => $last12Months,
    'MONTH_DATA' => $MONTH_DATA,
    'News' => $news,
    'TOP_TRACK' => $top_track,
    'TOP_STORE' => $top_store,
    'BALANCE' => $bal_amount,
    'LAST_SETTLEMENT_DATE' => $LAST_SETTLEMENT_DATE,
    'LAST_SETTLEMENT_AMOUNT' => $LAST_SETTLEMENT_AMOUNT,
    'showBankNotice' => $showBankNotice,
    'HAS_INFRINGEMENT' => $hasInfringement,

    // ✅ Admin summary counters
    'PENDING_RIGHTS' => $PENDING_RIGHTS,
    'OPEN_TICKETS' => $OPEN_TICKETS,
    'PROCESS_TICKETS' => $PROCESS_TICKETS,
    'PENDING_WITHDRAW_REQUESTS' => $PENDING_WITHDRAW_REQUESTS

     
]);

}

	public function maintenanceAction()
	{
		$file = 'maintenance.flag';
		if (file_exists($file)) {
			unlink($file); // Turn OFF
			$status = false;
		} else {
			file_put_contents($file, '1'); // Turn ON
			$status = true;
		}
		 
		echo json_encode(['mode' => $status ? 'ON' : 'OFF']);
		exit;
		
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
	public function updatenotificationviewAction()
	{
		$sl = $this->getServiceLocator();        
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$user_id = $_SESSION['user_id'];
		$user_type = $_SESSION['ROLE_NAME'];
		$projectTable = new TableGateway("tbl_notification_history",$adapter);
		$uData['read_flag'] = 1;
		$projectTable->update($uData,array("user_id" => $user_id,"user_type" => $user_type));
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
        echo $result;
        exit;
	}
	
	public function getNotificationAction()
	{
		$sl = $this->getServiceLocator();        
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		$user_id = $_SESSION['user_id'];
		$user_type = $_SESSION['ROLE_NAME'];
		$staffTable = new TableGateway("tbl_staff",$adapter);
		$rowset = $staffTable->select(array("id='".$user_id."' "));
		$rowset = $rowset->toArray();
		$last_logout_time = $rowset[0]['last_logout'];
		
		$projectTable = new TableGateway("tbl_notification",$adapter);
		
		$rowset = $this->executeQuery("select * from tbl_notification where user_id='".$user_id."' and datetime >= '".$last_logout_time."' ");
		
		$data = "";
		foreach($rowset as $row)
		{
			$data .='<a href="'.$row['url'].'"><i class="material-icons">check_circle</i>'.$row['title'].'</a>';
		}
		
		
		
		$result['tot_new'] = count($rowset);
		$result['NotiData'] = $data;
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
        echo $result;
        exit;
		
	}
	public  function executeQuery($sql)
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
	public function readNotificationAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $request = $this->getRequest();
		
		$id = $request->getPost('id');
		
		$projectTable = new TableGateway("tbl_notification_history",$adapter);
		
		$aData['close_flag'] = 1;
		
		$projectTable->update($aData,array("id='".$id."'"));
		
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
        echo $result;
        exit;
		
	}
	public function getnewsAction()
	{
		$sl = $this->getServiceLocator();
        $adapter = $sl->get('Zend\Db\Adapter\Adapter');

        $request = $this->getRequest();
		
		$id = $request->getPost('id');
		
		$projectTable = new TableGateway("tbl_news",$adapter);
		$rowset =$projectTable->select(array("id='".$id."' "));
		$rowset = $rowset->toArray();
		$row= $rowset[0];
		
		$INFO='<div class="card-item-news-popup">
				<div class="news_date">
					<h3>'.date('d',strtotime($row['news_date'])).'<br><span>'.strtoupper(date('M',strtotime($row['news_date']))).'</span></h3>
				</div>
				<div class="details">
					<h3>'.$row['name'].'</h3>
				</div>		
			</div>
			<div class="content" style="height: 400px;overflow: auto;">
			<p>'.$row['description'].'</p>
			</div>';
		
		
		$result['INFO'] = $INFO;
		$result['DBStatus'] = 'OK';
		$result = json_encode($result);
        echo $result;
        exit;
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
}//End Class


