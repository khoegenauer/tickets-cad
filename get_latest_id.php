<?php
/*
4/9/10 initial release 
6/11/10 disabled unit_flag_2 setting
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/1/10 - fix  to error_reporting(E_ALL);
*/
error_reporting(E_ALL);
//$a = $b;
@session_start();
require_once($_SESSION['fip']);		//7/28/10
get_current();
$me = $_SESSION['user_id'];
//$me = 1;
				// most recent chat invites other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0   OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$the_chat_id = ($row)? $row['id'] : "0";
//dump($the_chat_id);
//dump($row);
				// most recent ticket other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `_by` <> {$me} AND `status` = {$GLOBALS['STATUS_OPEN']} ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$the_tick_id = ($row)? $row['id'] : "0";

							// position updates?
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) ORDER BY `updated` DESC LIMIT 1";
$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
$row = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

if (!($row )) {				// latest unit status updates written by others 
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `user_id` != {$me} ORDER BY `updated` DESC LIMIT 1";		// get most recent
	$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
	$row =  (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
	}

if ($row) {
	$_SESSION['unit_flag_1'] = $row['id'];
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
	}
$the_unit_id = ($row)? $row['id'] : "0";
$the_updated = ($row)? $row['updated'] : "0";

$return = "{$the_chat_id}/{$the_tick_id}/{$the_unit_id}/{$the_updated}/";		// 4-elements slash-separated
//snap (__LINE__, $return);
//dump($return);

print $return;		// slash-separated string
?>