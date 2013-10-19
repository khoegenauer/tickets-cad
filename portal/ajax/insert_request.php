<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require_once('../../incs/functions.inc.php');
$ret_arr = array();
function get_requester_details($the_id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $the_id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		if($row['email'] == "") {
			if($row['email_s'] == "") {
				$the_ret[0] = "";
				} else {
				$the_ret[0] = $row['email_s'];
				}
			} else {
				$the_ret[0] = $row['email'];
			}
		} else {
		$the_ret[0] = "";
		}
	return $the_ret;
	}

function get_facname($id) {
	$the_ret = array();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id . " LIMIT 1";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	if(mysql_num_rows($result) == 1) {
		$row = stripslashes_deep(mysql_fetch_assoc($result));
		$the_ret[0] = ($row['name'] != "") ? $row['name'] : "NA";
		$street = ($row['street'] != "") ? $row['street'] : "";
		$the_ret[1] = ($street != "") ? $street . ", " . $row['city'] . ", " . $row['state']: "";
		$the_ret[2] = "Phone: " . $row['contact_phone'];
		} else {
		$the_ret[0] = "";
		$the_ret[1] = "";
		$the_ret[2] = "";
		}
	return $the_ret;
	}

$theDetails = get_requester_details($_SESSION['user_id']);
$the_email = $theDetails[0];
if($_GET['frm_patient'] == "") {
	$ret_arr[0] = 999;
	} else {
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
	$where = $_SERVER['REMOTE_ADDR'];
	$scope = $_GET['frm_scope'];
	$comments = $_GET['frm_comments'];
	$street = $_GET['frm_street'];
	$city = $_GET['frm_city'];
	$state = $_GET['frm_state'];	
	$lat = ($_GET['frm_lat'] != "") ? $_GET['frm_lat'] : '0';
	$lng = ($_GET['frm_lng'] != "") ? $_GET['frm_lng'] : '0';	
	$description = $_GET['frm_description'];
	$request_date = $_GET['frm_request_date'];
	$request_date = mysql_format_date(strtotime($request_date));
	$userName = $_GET['frm_username'];
	$comments = $_GET['frm_comments'];
	$phone = $_GET['frm_phone'];
	$toAddress = urldecode($_GET['frm_toaddress']);
	$patient = $_GET['frm_patient'];
	$origFac = ($_GET['frm_orig_fac'] != "") ? $_GET['frm_orig_fac'] : '0';
	$recFac = ($_GET['frm_rec_fac'] != "") ? $_GET['frm_rec_fac'] : '0';	
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
				`org`,
				`contact`, 
				`street`, 
				`city`, 
				`state`, 
				`the_name`, 
				`phone`, 
				`to_address`,
				`orig_facility`,
				`rec_facility`, 
				`scope`, 
				`description`, 
				`comments`, 
				`lat`,
				`lng`,
				`request_date`, 
				`status`, 
				`accepted_date`,
				`declined_date`, 
				`resourced_date`, 
				`completed_date`, 
				`closed`, 
				`requester`, 
				`_by`, 
				`_on`, 
				`_from` 
				) VALUES (
				" . 0 . ",
				'" . addslashes($userName) . "',
				'" . addslashes($street) . "',	
				'" . addslashes($city) . "',	
				'" . addslashes($state) . "',	
				'" . addslashes($patient) . "',
				'" . addslashes($phone) . "',
				'" . addslashes($toAddress) . "',				
				" . $origFac . ",					
				" . $recFac . ",	
				'" . addslashes($scope) . "',	
				'" . addslashes($description) . "',					
				'" . addslashes($comments) . "',		
				'" . $lat . "',		
				'" . $lng . "',				
				'" . $request_date . "',
				'Open',
				NULL,
				NULL,
				NULL,
				NULL,
				NULL,
				" . $_SESSION['user_id'] . ",
				" . $_SESSION['user_id'] . ",				
				'" . $now . "',
				'" . $where . "')";
	$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
	if($result) {
		do_log($GLOBALS['LOG_NEW_REQUEST'], $_SESSION['user_id']);	
		$the_summary = "New Request from " . $userName . "\r\n";
		$the_summary .= get_text('Scope') . ": " . $_GET['frm_scope'] . "\r\n\r\n";	
		$the_summary .= get_text('Patient') . " name: " . $_GET['frm_patient'] . "\r\n";
		$the_summary .= get_text('Street') . ": " . $street . ", ";	
		$the_summary .= get_text('City') . ": " . $city . ", ";	
		$the_summary .= get_text('State') . ": " . $state . "\r\n";	
		$the_summary .= get_text('Contact Phone') . ": " . $phone . "\r\n";
		$orig_Fac = ($_GET['frm_orig_fac'] != "0") ? get_facname($_GET['frm_orig_fac']) : "";
		$rec_Fac =  ($_GET['frm_rec_fac'] != "0") ? get_facname($_GET['frm_rec_fac']) : "";
		$the_summary .= ((is_array($orig_Fac)) && ($orig_Fac[0] != "")) ? "Originating Facility " . $orig_Fac[0] . "\nAddress: " . $orig_Fac[1] . "\nPhone " . $orig_Fac[2] . "\r\n" : "";
		$the_summary .= ((is_array($rec_Fac)) && ($rec_Fac[0] != "")) ? "Receiving Facility " . $rec_Fac[0] . "\nAddress: " . $rec_Fac[1] . "\nPhone " . $rec_Fac[2] . "\r\n" : "";
		$the_summary .= get_text('Description') . "\r\n" . $description . "\r\n";	
		$the_summary .= get_text('Comments') . "\r\n" . $_GET['frm_comments'] . "\r\n";	
		$the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($request_date)) . "\r\n";		
		$addrs = notify_newreq($_SESSION['user_id']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
			$to_str = implode("|", $addrs);
			$smsg_to_str = "";
			$subject_str = "New " . get_text('Service User') . " Request";
			$text_str = "A new request has been loaded by " . $userName . " Dated " . $now . ". \r\nPlease log on to Tickets and check\n\n"; 
			$text_str .= "Request Summary\r\n" . $the_summary;
			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
			}				// end if/else ($addrs)	
		if ($the_email != "") {				// any addresses?
			$to_str = $the_email;
			$smsg_to_str = "";
			$subject_str = "Your request " . $scope . " has been registered";
			$text_str = "Your Request " . $scope . " has been registered\r\n"; 
			$text_str .= "Request Summary\n\n" . $the_summary;
			do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);	
			}				// end if/else ($the_email)	
		$ret_arr[0] = 100;
		} else {
		$ret_arr[0] = 999;
		}
	}
print json_encode($ret_arr);
?>