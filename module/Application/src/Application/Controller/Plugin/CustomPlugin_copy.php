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
	public function emailTemplate($config,$message,$to,$from)
	{
		$con = $this->dbconnection();
		$res = $con->query('select * from tbl_settings ');
		$rec = mysqli_fetch_array($res);
		
		$logo = $config['URL'].'public/uploads/'.$rec['logo'];
		
		$html ='<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 30px;font-size:14px;">
		<div style="text-align: center; padding-bottom: 10px;">
				<img src="'.$logo.'" alt="Prime Backstage Logo" style="max-width: 150px;">
			</div>
		<div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
			';
			 
		$html .= $message;
			
		$html .='</div>
			<div style="text-align: left;max-width: 625px; margin:0px auto; color: #888888;">
				<p style="font-size:12px;margin:10px auto;">This email has been sent to '.$to.' by '.$from.'<br>Prime Digital Arena<br>
				<div style="display: flex; gap: 10px;">
					<a href="https://twitter.com" style="padding-right:15px;"><img src="'.$config['URL'].'public/img/twitter_icon.png" alt="Twitter" style="width: 34px; height: 34px;background-color: #007bff;border-radius: 100%;border: 1px solid #007bff;"></a>
					<a href="https://www.facebook.com" style="padding-right:15px;"><img src="'.$config['URL'].'public/img/facebook_icon.png" alt="Facebook" style="width: 34px; height: 34px;background-color: #007bff;border-radius: 100%;border: 1px solid #007bff;"></a>
					<a href="https://www.instagram.com" style="padding-right:15px;"><img src="'.$config['URL'].'public/img/instagram_icon.png" alt="Instagram" style="width: 34px; height: 34px;background-color: #007bff;border-radius: 100%;border: 1px solid #007bff;"></a>
					<a href="https://www.youtube.com" style="padding-right:15px;"><img src="'.$config['URL'].'public/img/youtube_icon.png" alt="Youtube" style="width: 34px; height: 34px;background-color: #007bff;border-radius: 100%;border: 1px solid #007bff;"></a>
					<a href="https://www.linkedin.com" style="padding-right:15px;"><img src="'.$config['URL'].'public/img/linkedin_icon.png" alt="LinkedIn" style="width: 34px; height: 34px;background-color: #007bff;border-radius: 100%;border: 1px solid #007bff;"></a>
				</div>
		<a href="https://www.primebackstage.in/frontend/privacy.php">Privacy Policy</a> | <a href="https://www.primebackstage.in/frontend/support.php">Support</a></p>
			</div>
			
		</body>';
		
		return $html;
	
	}
	
}
	
?>