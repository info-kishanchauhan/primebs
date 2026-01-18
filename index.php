<?php
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();
define('RUNNING_FROM_ROOT', true);

if (strstr($_SERVER['REQUEST_URI'], "releases")) {
	/*error_reporting(E_ALL);
	ini_set("error_log", "error.log");
	ini_set('display_errors',true);  */
}



date_default_timezone_set('Asia/Kolkata');
require 'public/PHPMailer/PHPMailerAutoload.php';
require 'vendor/HtmlExcel/HtmlExcel.php';
require 'public/html2pdf/config/lang/eng.php';
require 'public/html2pdf/tcpdf.php';

$user     	= "root";
$password   = "1234";
$db        = "primebs";
$server    = "localhost";

$mysqli = @mysqli_connect($server, $user, $password, $db)
	or die("Couldn't connect to SQL Server on $mysqli");


if (!strstr($_SERVER['REQUEST_URI'], "login") && !strstr($_SERVER['REQUEST_URI'], "forgotpwd")) {
	if (@$_SESSION['user_id'] == "") {

		header("location: /login");
		exit;
	} else {
		if ($_SESSION['user_id'] > 0) {
			$file = 'maintenance.flag';
			if (file_exists($file)) {
				header("location: maintenance.php");
				exit;
			}
			$query = $mysqli->query("select * from tbl_staff WHERE id='" . $_SESSION['user_id'] . "' ");
			$rec = mysqli_fetch_array($query);
			$_SESSION["USER_ACCESS"] = explode(',', $rec['user_access']);
			$_SESSION["PERMISSION_TYPE"] = $rec['permission_type'];
			$_SESSION['LIVE_STATUS'] = $rec['on_off_status'];
			if ($rec['status'] == '1' || $rec['sub_status'] == '1') {
				$_SESSION['user_id'] = "";
				header("location: /login");
				exit;
			}
			$mysqli->close();
		}
	}
}


include 'public/index.php';
