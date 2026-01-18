<?php 
namespace Application\Controller\Plugin;
 
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet; 
 

class CustomPlugin extends AbstractPlugin {
	
	private $pid;
    private $command;
    private $debugger=true;
    private $msg=array();
    
    /*
    * @Param $cmd: Pass the linux command want to run in background 
    */
	public function __construct($cmd=null){
      
 		$this->wid_pid_array = array();
 	    if(!empty($cmd))
        {
            $this->command=$cmd;
            $this->do_process();
        }
        else{
            $this->msg['error']="Please Provide the Command Here";
        }
    }
    
    public function setCmd($cmd){
        $this->command = $cmd;
        return true;
    }
    
    public function setProcessId($pid){
        $this->pid = $pid;
        return true;
    }
    public function getProcessId(){
        return $this->pid;
    } 
    public function status(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);        
        if (!isset($op[1]))return false;
        else return true;
    }
    
    public function showAllPocess(){
        $command = 'ps -ef '.$this->pid;
        exec($command,$op);
        return $op;
    }
    
    public function start(){
        if ($this->command != '')
        $this->do_process();
        else return true;
    }
    public function stop(){
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false)return true;
        else return false;
    }
    
    //do the process in background
    public function do_process(){
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
        exec($command ,$pross);
        $this->pid = (int)$pross[0];
    }
	
	
	
    public function setMySqlTimeZone($config){
      
	  	
    }
	public function saveTransaction($sales_month,$adapter)
	{
		$rowset = $this->executeQuery('select * from tbl_staff',$adapter);
		foreach($rowset as $row)
		{
			$labels = $this->getAssignedLabelsUser($row['id']);
			if($row['payment_system'] == 'Monthly')
			{
				$rowset2 = $this->executeQuery("select sum(revenue)as revenue from view_analytics where sales_month ='".$sales_month."' and label_id in (".$labels.") and payment_status='Unpaid' and import_payment_status='Unpaid' ",$adapter);
				$amount = $rowset2[0]['revenue'];
				
				$user_rate=100;
				if(strstr($row['user_access'],"Royalty Rate"))
				{
					$user_rate=$row['royalty_rate_per'];
				}
				$amount =  ($amount * $user_rate)/100;
				$amount = number_format($amount,'2','.','');
				
				$description = date('F Y',strtotime($sales_month));
				
				$rowset3  = $this->executeQuery("Select balance from tbl_transaction where user_id='".$row['id']."' order by id desc limit 1",$adapter);
				$balance = $rowset3[0]['balance'] + $amount;
				
				$rowset4 =  $this->executeQuery("Select * from tbl_transaction where user_id='".$row['id']."' and transaction_date='".$sales_month."' and transaction_type='Royalties'",$adapter);
				
				if(count($rowset4) > 0)
				{
					$balance = $rowset4[0]['balance'] - $rowset4[0]['amount'] + $amount;
					$insert_transaction = $this->executeQuery("Update tbl_transaction set user_id='".$row['id']."',transaction_date='".$sales_month."',transaction_type='Royalties',amount='".$amount."',balance='".$balance."' ",$adapter);
				}
				else
				{
					$insert_transaction = $this->executeQuery("Insert into tbl_transaction set user_id='".$row['id']."',transaction_date='".$sales_month."',transaction_type='Royalties',amount='".$amount."',description='".$description."',balance='".$balance."' ",$adapter);
				}
				
				
			}
			else
			{
				$month = date('m',strtotime($sales_month));
				$year = date('Y',strtotime($sales_month));
				if($month == '03' || $month == '06' || $month == '09' || $month == '12' )
				{
					if($month == '03')
					{
						$start_month = date($year.'-01-01');
						$end_month = date($year.'-03-31');
						$description = 'Q1 '.$year;
					}
					if($month == '06')
					{
						$start_month = date($year.'-04-01');
						$end_month = date($year.'-06-30');
						$description = 'Q2 '.$year;
					}
					if($month == '09')
					{
						$start_month = date($year.'-07-01');
						$end_month = date($year.'-09-30');
						$description = 'Q3 '.$year;
					}
					if($month == '12')
					{
						$start_month = date($year.'-10-01');
						$end_month = date($year.'-12-31');
						$description = 'Q4 '.$year;
					}
					
					$rowset2 = $this->executeQuery("select sum(revenue)as revenue from view_analytics where sales_month >='".$start_month."' and sales_month <= '".$end_month."' and label_id in (".$labels.") and payment_status='Unpaid' and import_payment_status='Unpaid' ",$adapter);
					$amount = $rowset2[0]['revenue'];
					
					$user_rate=100;
					if(strstr($row['user_access'],"Royalty Rate"))
					{
						$user_rate=$row['royalty_rate_per'];
					}
					$amount =  ($amount * $user_rate)/100;
					$amount = number_format($amount,'2','.','');
					
					$rowset3  = $this->executeQuery("Select balance from tbl_transaction where user_id='".$row['id']."' order by id desc limit 1",$adapter);
					$balance = $rowset3[0]['balance'] + $amount;
					
					$rowset4 =  $this->executeQuery("Select * from tbl_transaction where user_id='".$row['id']."' and transaction_date='".$sales_month."' and transaction_type='Royalties'",$adapter);
					
					if(count($rowset4) > 0)
					{
						$balance = $rowset4[0]['balance'] - $rowset4[0]['amount'] + $amount;
						$insert_transaction = $this->executeQuery("Update tbl_transaction set user_id='".$row['id']."',transaction_date='".$sales_month."',transaction_type='Royalties',amount='".$amount."',balance='".$balance."' ",$adapter);
					}
					else
					{
						$insert_transaction = $this->executeQuery("Insert into tbl_transaction set user_id='".$row['id']."',transaction_date='".$sales_month."',transaction_type='Royalties',amount='".$amount."',description='".$description."',balance='".$balance."' ",$adapter);
					}
				}
			}
		}
	}
	public function getUserArtist($adapter)
	{
		$rowset = $this->executeQuery("select artist from tbl_staff where id='".$_SESSION['user_id']."' ",$adapter);
		
		if($rowset[0]['artist'] == '')
			return '';
		
		
		return $rowset[0]['artist'];
	}
	public function executeQuery($sql,$adapter)
	{
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		
		return $rowset;
	}
	public function getWithdrawBalanceAmount($user,$adapter)
	{
		if($user['labels'] == '')
			$user['labels'] = 9999999999999;
		
		if($user['payment_system'] == 'Monthly')
		{
			$end_month = date('Y-m-t',strtotime(date('Y-m-01')." -2 months"));
		}
		else
		{
			if(date('m') == '08' || date('m') == '09' || date('m') == '10')
			{
				$start_month = date('Y-04-01');
				$end_month = date('Y-06-30');
				$sql="select * from tbl_analytics where sales_month >= '".$start_month."' and sales_month <='".$end_month."' group by sales_month";		        
				$optionalParameters=array();        
				$statement = $adapter->createStatement($sql, $optionalParameters);        
				$result = $statement->execute();        
				$resultSet = new ResultSet;        
				$resultSet->initialize($result);        
				$rowset=$resultSet->toArray();
				if(count($rowset) != '3')
					$end_month = date('Y-03-31');
				
			}
			if(date('m') == '11' || date('m') == '12' || date('m') == '01')
			{
				$start_month = date('Y-07-01');
				if(date('m') == '01')
					$end_month = (date('Y')-1).'-'.'09-30';
				else
					$end_month = date('Y-09-30');
				
				$sql="select * from tbl_analytics where sales_month >= '".$start_month."' and sales_month <='".$end_month."' group by sales_month";		        
				$optionalParameters=array();        
				$statement = $adapter->createStatement($sql, $optionalParameters);        
				$result = $statement->execute();        
				$resultSet = new ResultSet;        
				$resultSet->initialize($result);        
				$rowset=$resultSet->toArray();
				if(count($rowset) != '3')
					$end_month = date('Y-06-30');
			}
			if(date('m') == '02' || date('m') == '03' || date('m') == '04')
			{
				$start_month = (date('Y')-1).'-'.'10-01';
				$end_month = (date('Y')-1).'-'.'12-31';
				
				$sql="select * from tbl_analytics where sales_month >= '".$start_month."' and sales_month <='".$end_month."' group by sales_month";		        
				$optionalParameters=array();        
				$statement = $adapter->createStatement($sql, $optionalParameters);        
				$result = $statement->execute();        
				$resultSet = new ResultSet;        
				$resultSet->initialize($result);        
				$rowset=$resultSet->toArray();
				if(count($rowset) != '3')
					$end_month = (date('Y')-1).'-'.'09-30';
			}
			if(date('m') == '05' || date('m') == '06' || date('m') == '07')
			{
				$start_month = date('Y-01-01');
				$end_month = date('Y-03-31');
				
				$sql="select * from tbl_analytics where sales_month >= '".$start_month."' and sales_month <='".$end_month."' group by sales_month";		        
				$optionalParameters=array();        
				$statement = $adapter->createStatement($sql, $optionalParameters);        
				$result = $statement->execute();        
				$resultSet = new ResultSet;        
				$resultSet->initialize($result);        
				$rowset=$resultSet->toArray();
				if(count($rowset) != '3')
					$end_month = (date('Y')-1).'-'.'12-31';
			}
		}	
		
		$label_id = $this->getAssignedLabelsUser($user['id']);
		
		$sql="select sum(revenue)as revenue,sales_month from view_analytics where sales_month <='".$end_month."' and label_id in (".$label_id.") and payment_status='Unpaid' and import_payment_status='Unpaid' group by sales_month order by sales_month asc";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		
		$revenue = 0;
		$description = 'Payment of Royalties for ';
		$desc_array = array();
		foreach($rowset as $row)
		{
			$revenue += $row['revenue'];
			if($user['payment_system'] == 'Monthly')
			{
				$desc_array[] = date('F Y',strtotime($row['sales_month']));
			}
			else
			{
				$q1 = array('01','02','03');
				$q2 = array('04','05','06');
				$q3 = array('07','08','09');
				$q4 = array('10','11','12');
				
				$month = date('m',strtotime($row['sales_month']));
				$year = date('Y',strtotime($row['sales_month']));
				
				if(in_array($month,$q1))
				{
					$info = "Q1 ".$year;
				}
				if(in_array($month,$q2))
				{
					$info = "Q2 ".$year;
				}
				if(in_array($month,$q3))
				{
					$info = "Q3 ".$year;
				}
				if(in_array($month,$q4))
				{
					$info = "Q4 ".$year;
				}
				
				if(!in_array($info,$desc_array))
					$desc_array[] = $info;
			}
		}
		$description .= implode(' > ',$desc_array);
		$user_rate=100;
		if(strstr($user['user_access'],"Royalty Rate"))
		{
			$user_rate=$user['royalty_rate_per'];
		}
		$revenue =  ($revenue * $user_rate)/100;
		$revenue = number_format($revenue,'2','.','');
		$info = array();
		$info['amount'] = $revenue;
		$info['month_year'] = $end_month;
		$info['description'] = $description;
		return $info;
	}
	public function getBalanceAmount($user,$adapter)
	{
		if($user['payment_system'] == 'Monthly')
		{
			$end_month = date('Y-m-t',strtotime(date('Y-m-01')." -2 months"));
		}
		else
		{
			if(date('m') == '08' || date('m') == '09' || date('m') == '10')
			{
				$end_month = date('Y-06-30');
			}
			if(date('m') == '11' || date('m') == '12' || date('m') == '01')
			{
				if(date('m') == '01')
					$end_month = (date('Y')-1).'-'.'09-30';
				else
					$end_month = date('Y-09-30');
			}
			if(date('m') == '02' || date('m') == '03' || date('m') == '04')
			{
				$end_month = (date('Y')-1).'-'.'12-31';
			}
			if(date('m') == '05' || date('m') == '06' || date('m') == '07')
			{
				$end_month = date('Y-03-31');
			}
		}			
		
		$label_id = $this->getAssignedLabelsUser($user['id']);
		
		
		$sql="select sum(revenue)as revenue,sales_month from view_analytics where sales_month <='".$end_month."' and label_id in (".$label_id.") and payment_status='Unpaid' and import_payment_status='Unpaid' group by sales_month order by sales_month asc";		        
		$optionalParameters=array();        
		$statement = $adapter->createStatement($sql, $optionalParameters);        
		$result = $statement->execute();        
		$resultSet = new ResultSet;        
		$resultSet->initialize($result);        
		$rowset=$resultSet->toArray();
		
		$revenue = 0;
		$description = 'Payment of Royalties for ';
		$desc_array = array();
		foreach($rowset as $row)
		{
			$revenue += $row['revenue'];
			
			if($user['payment_system'] == 'Monthly')
			{
				$desc_array[] = date('F Y',strtotime($row['sales_month']));
			}
			else
			{
				$q1 = array('01','02','03');
				$q2 = array('04','05','06');
				$q3 = array('07','08','09');
				$q4 = array('10','11','12');
				
				$month = date('m',strtotime($row['sales_month']));
				$year = date('Y',strtotime($row['sales_month']));
				
				if(in_array($month,$q1))
				{
					$info = "Q1 ".$year;
				}
				if(in_array($month,$q2))
				{
					$info = "Q2 ".$year;
				}
				if(in_array($month,$q3))
				{
					$info = "Q3 ".$year;
				}
				if(in_array($month,$q4))
				{
					$info = "Q4 ".$year;
				}
				
				if(!in_array($info,$desc_array))
					$desc_array[] = $info;
			}
		}
		$description .= implode(' > ',$desc_array);
		
		$user_rate=100;
		if(strstr($user['user_access'],"Royalty Rate"))
		{
			$user_rate=$user['royalty_rate_per'];
		}
		$revenue =  ($revenue * $user_rate)/100;
		$revenue = number_format($revenue,'2','.','');
		
		$info = array();
		$info['amount'] = $revenue;
		$info['month_year'] = $end_month;
		$info['description'] = $description;
		return $info;
	}
	public function dbconnection()
	{
		$user     	= "primebs";
		$password   = "mJYwFtL4QNuzvPuz2PNZ"; 
		$db        = "primebs";
		$server    = "localhost"; 
			
		$mysqli = @mysqli_connect($server, $user, $password,$db)
		or die("Couldn't connect to SQL Server on $myServer");
		
		return $mysqli;
	}
	public function getUserArtistName($artist_id)
	{
		if($artist_id == 0)
			return '';
		
		$con = $this->dbconnection();
		$res = $con->query("select * from tbl_label where id='".$artist_id."' ");
		$rec = mysqli_fetch_array($res);
		return $rec['name'];
	}
	public function getStaffReleaseCond()
	{
		$cond ='';
		
		if($_SESSION['STAFFUSER'] == '1')
		{
			$con = $this->dbconnection();
			$res = $con->query("select * from tbl_staff where id='".$_SESSION['user_id']."' ");
			$rec = mysqli_fetch_array($res);
			
			if($rec['permission_type'] == 'Controlled Access')
			{
				$cond = " and assigned_team='".$_SESSION['user_id']."' ";
			}
			else
			{
				$labels = $rec['labels'];
				if($labels != '')
				{
					$cond .= " AND labels in (".$labels.") ";
				}
				$user_artist = $rec['artist'];
				if($user_artist != '')
				{
					$artist_array = array_map('trim', explode(',', $user_artist));
					$or_conditions = [];

					foreach ($artist_array as $artist_name) {
						$escaped_artist = mysqli_real_escape_string($con, $artist_name);
						$or_conditions[] = "FIND_IN_SET('$escaped_artist', releaseArtist) > 0";
					}

					if (!empty($or_conditions)) {
						$astist_cond = ' AND (' . implode(' OR ', $or_conditions) . ')';
					}
					
					$cond .= $astist_cond;
				}
			}
		}
		return $cond;
	}
	public function getUserLabels($user_id)
	{
		if($user_id == 0  || $_SESSION['STAFFUSER'] == '1')
			return '';
		
		$con = $this->dbconnection();
		$res = $con->query("select * from tbl_staff where id='".$user_id."' ");
		$rec = mysqli_fetch_array($res);
		
		$res2 = $con->query("select * from tbl_label where created_by='".$user_id."' ");
		$labels = array();
		while($rec2 = mysqli_fetch_array($res2))
		{
			$labels[] = $rec2['id'];
		}
		
		$labels=implode(',',$labels);
		
		if($labels != '')
			$rec['labels'] .=','.$labels;
		
		if($rec['labels'] == '')
			$rec['labels']='11111111111111';
		
		
		
		return $rec['labels'];
	}
	
	public function getAssignedLabelsUser($user_id)
	{
		if($user_id == 0  || $_SESSION['STAFFUSER'] == '1')
			return '11111111';
		
		$con = $this->dbconnection();
		$res = $con->query("select group_concat(id)as labels from tbl_label where user_id='".$user_id."' ");
		$rec = mysqli_fetch_array($res);
		
		if($rec['labels'] == '')
			$rec['labels']='11111111111111';
		
		return $rec['labels'];
	}
	public function getAssignedUserforLabel($label_id)
	{
		
		$con = $this->dbconnection();
		$res = $con->query("select user_id from tbl_label where id='".$label_id."' ");
		$rec = mysqli_fetch_array($res);
		
	
		return $rec['user_id'];
	}
	
	public function getUserRate($user_id) 
	{
		
		if($user_id == 0  || $_SESSION['STAFFUSER'] == '1')
		{
			return 100;
		}	
		
		$con = $this->dbconnection();
		$res = $con->query("select * from tbl_staff where id='".$user_id."' ");
		$rec = mysqli_fetch_array($res);
		
		if($rec['subuser'] == 0)
		{
			$user_rate=100;
	
			if(strstr($rec['user_access'],"Royalty Rate"))
			{
				$user_rate = $rec['royalty_rate_per'];
			}
			
			return $user_rate;
		}
		else
		{
			$res2 = $con->query("select * from tbl_staff where id='".$rec['created_by']."' ");
			$rec2 = mysqli_fetch_array($res2);
			
			$user_rate=100;
		
			if(strstr($rec2['user_access'],"Royalty Rate"))
			{
				$user_rate=$rec2['royalty_rate_per'];
			}
			
			$sub_user_rate=100;
		
			if(strstr($rec['user_access'],"Royalty Rate"))
			{
				$sub_user_rate=$rec['royalty_rate_per'];
			}
			
			
			return  $user_rate * ($sub_user_rate / 100);
		
		}
	}
	
	public function sendSmtpEmail($config,$to,$subject,$content,$cc='')
	{
			$SMTP_DETAILS = $config['SMTP_DETAILS'];
			
			//if($subject == 'Payment Request Submission' || $subject == 'Payment Request Accepted – Processing Underway' || $subject == 'Payment Request Rejected – Action Required' || $subject == 'Payment Successfully Processed')
				//$SMTP_DETAILS = $config['SMTP_DETAILS2'];
	
		    $mail = new \PHPMailer();
		
			$mail->isSMTP(); 
			//$mail->SMTPDebug  = 1; 
			$mail->Host = $SMTP_DETAILS['host'];
			$mail->SMTPAuth = true;                          
			//Provide username and password     
			$mail->Username = $SMTP_DETAILS['username'];                 
			$mail->Password = $SMTP_DETAILS['password'];

			$mail->SMTPSecure = "ssl";                           
			//Set TCP port to connect to
			$mail->Port = $SMTP_DETAILS['port'];

			$mail->IsHTML(true);
			$mail->SetFrom($mail->Username,"Prime Digital Arena"); //Name is optional
			$mail->Subject   = $subject; 
			$mail->Body     =  $this->emailTemplate($config,$content,$to,$SMTP_DETAILS['username']);

			$mail->IsHTML(true);
			$mail->AddAddress($to);
			/*if($cc != '')
				$mail->addCC($cc);*/
			
			if(!$mail->send()) {
				//echo 'Message could not be sent.';
				//echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
			else
			{
				//echo 'Mail sent '.$to."<br>";
			}
	}
public function emailTemplate($config, $message, $to, $from)
{
    // --- SETTINGS / ASSETS (from your snippet) ---
    $ASSETS_BASE = 'https://www.primebackstage.in/public/img';

    // Logos
    $logoLight = $ASSETS_BASE . '/pda-mail-logo.png';
    $logoDark  = $ASSETS_BASE . '/pda-mail-logo-dark.png';

    // Optional images (kept, but header/footer only)
    $footerBannerPlain   = $ASSETS_BASE . '/footbannern.jpg';
    $footerBannerPartner = $ASSETS_BASE . '/footbanner_partner_believe.jpg';

    // Socials
    $fb = $ASSETS_BASE . '/ic-fb.png';
    $ig = $ASSETS_BASE . '/ic-ig.png';
    $x  = $ASSETS_BASE . '/ic-x.jpg';
    $yt = $ASSETS_BASE . '/ic-yt.png';
    $ln = $ASSETS_BASE . '/ic-ln.png';

    $year = date('Y');

    // --- (optional) fetch panel logo from DB settings, fallback to $logoLight ---
    $con  = $this->dbconnection();
    $rec  = mysqli_fetch_array($con->query('SELECT * FROM tbl_settings'));
    if (!empty($rec['logo'])) {
        $panelLogo = rtrim($config['URL'], '/') . '/public/uploads/' . $rec['logo'];
        // Use your panel logo in light mode if present
        $logoLight = $panelLogo;
    }

    // ---- SHELL: header + footer preserved, center card injects $message ----
    $html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="x-apple-disable-message-reformatting">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Prime Digital Arena</title>
<style>
  body { margin:0; padding:0; background:#f3f5f9; -webkit-text-size-adjust:100%; }
  img { border:0; outline:none; display:block; }
  a { text-decoration:none; color:#2563eb; }
  .wrap{width:640px;margin:0 auto;}
  .px{padding-left:24px;padding-right:24px;}

  /* Button helper (if your \$message has CTAs, looks good) */
  .btn{ background:#4f46e5; border-radius:30px; font-weight:700; font-size:15px; color:#ffffff; }
  .btn a{ color:#ffffff !important; display:inline-block; padding:12px 28px; }

  /* Desktop/Mobile toggles */
  .show-desktop { display:table; }
  .show-mobile  { display:none; }

  /* Dark-mode friendly logo swap (for supported clients) */
  @media (prefers-color-scheme: dark) {
    .logo-light{display:none !important;}
    .logo-dark{display:block !important;}
    body { background:#0b1020; }
  }

  /* Mobile */
  @media (max-width:600px){
    .wrap{width:100% !important}
    .px{padding-left:18px !important; padding-right:18px !important}
    .stack td{display:block !important; width:100% !important; text-align:left !important}
    .cta-full{width:100% !important; display:block !important}
    .btn a{display:block !important; text-align:center !important; padding:14px 18px !important}

    /* Toggle footer variants */
    .show-desktop { display:none !important; }
    .show-mobile  { display:table !important; width:100% !important; }
  }
</style>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;">
  <!-- preheader (blank generic so existing flows unaffected) -->
  <div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#f3f5f9;">
    Prime Digital Arena
  </div>

  <center style="width:100%;background:#f3f5f9;">
    <!-- top gradient bar (HEADER) -->
<table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640">
  <tr>
    <td style="height:18px; line-height:18px; font-size:0;">&nbsp;</td>
  </tr>
</table>

    <!-- card -->
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640" bgcolor="#ffffff" style="background:#ffffff;margin:1px auto;border-radius:12px;overflow:hidden;box-shadow:0 8px 30px rgba(2,6,23,0.06);">
      <!-- logo (light & dark variants) -->
      <tr><td class="px" bgcolor="#ffffff" style="padding:22px 24px 14px;text-align:center;background:#ffffff;">
        <span class="logo-light" style="display:inline-block;filter:none !important;background:#ffffff;border-radius:8px;padding:6px 8px;">
          <img src="{$logoLight}" width="140" alt="Prime Digital Arena" style="height:auto;margin:0 auto;">
        </span>
        <span class="logo-dark" style="display:none;filter:none !important;background:#000;border-radius:8px;padding:6px 8px;">
          <img src="{$logoDark}" width="140" alt="Prime Digital Arena" style="height:auto;margin:0 auto;">
        </span>
      </td></tr>
      
      <tr>
  <td style="height:4px;background:linear-gradient(90deg,#6b44cb,#9f1ac6,#5b46b3);"></td>
</tr>

      <!-- CONTENT AREA: inject existing \$message AS-IS -->
      <tr>
        <td class="px" bgcolor="#ffffff" style="padding:18px 24px 22px;color:#0f172a;font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
          {$message}
        </td>
      </tr>

      <!-- divider -->
      <tr><td style="height:1px;background:#eef2f7;"></td></tr>

      <!-- ===== FOOTER VARIANT A: plain banner (desktop/most clients) ===== -->
      <tr class="show-desktop">
        <td style="padding:0;">
          <table role="presentation" align="center" width="640" cellpadding="0" cellspacing="0" border="0" class="wrap" style="width:640px;margin:0 auto;">
            <tr>
              <td background="{$footerBannerPlain}" bgcolor="#1d1d1f" width="640" height="120" valign="middle" align="center"
                  style="background:url('{$footerBannerPlain}') center / cover no-repeat #1d1d1f; width:640px; height:100px; text-align:center;">
                <!--[if gte mso 9]>
                  <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false"
                          style="width:640px;height:180px;">
                    <v:fill type="frame" src="{$footerBannerPlain}" color="#1d1d1f"/>
                    <v:textbox inset="0,0,0,0">
                <![endif]-->
                <!-- text baked in image (no overlay text) -->
                <!--[if gte mso 9]></v:textbox></v:rect><![endif]-->
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- ===== FOOTER VARIANT B: composite image (mobile Gmail safe) ===== -->
      <tr class="show-mobile">
        <td style="padding:0;background:#ffffff;">
          <img src="{$footerBannerPartner}" width="640" alt="Distribution Partner — believe." style="display:block;width:100%;max-width:640px;height:auto;border:0;">
        </td>
      </tr>

      <!-- Social icons -->
      <tr>
        <td style="padding:12px 16px;text-align:center;background:#ffffff;">
          <table role="presentation" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:0 6px;"><a href="https://www.facebook.com/PrimeDigitalArena/"><img src="{$fb}" width="24" alt="Facebook"></a></td>
              <td style="padding:0 6px;"><a href="https://www.instagram.com/pda_india/"><img src="{$ig}" width="24" alt="Instagram"></a></td>
              <td style="padding:0 6px;"><a href="https://x.com/PDA_India"><img src="{$x}"  width="24" alt="X"></a></td>
              <td style="padding:0 6px;"><a href="https://www.youtube.com/@PrimeDigitalArena_in"><img src="{$yt}" width="24" alt="YouTube"></a></td>
              <td style="padding:0 6px;"><a href="#"><img src="{$ln}" width="24" alt="LinkedIn"></a></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- security + legal -->
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" class="wrap" width="640" style="width:640px;margin:10px auto 24px;">
      <tr><td style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:11px;color:#6b7280;text-align:center;line-height:1.6;">
        This email was sent to <strong>{$to}</strong> by <strong>{$from}</strong><br>
        <a href="https://primedigitalarena.com/privacy" style="color:#4f46e5;text-decoration:none;">Privacy Policy</a> ·
        <a href="https://primedigitalarena.com/Terms" style="color:#4f46e5;text-decoration:none;">Support</a><br>
        © {$year} Prime Digital Arena
      </td></tr>
    </table>
  </center>
</body>
</html>
HTML;

    return $html;
}

	
}
	
?>