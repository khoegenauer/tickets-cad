<?php
/*
6/26/2013 - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once('../../incs/functions.inc.php');

extract ($_POST);
$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
	`lat` = " . quote_smart($lat) . ",
	`lng` = " . quote_smart($lng) . "
	WHERE `id` = " . quote_smart($unit_id) . " LIMIT 1";

snap(__LINE__, $query);

$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

if (mysql_affected_rows($result) == 1 ) { 				// if any change
	$now = now_ts() ;									// timestamp format
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '{$now}' 
		WHERE `id` = " . quote_smart($_POST['unit_id']) . " LIMIT 1"; 
	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);		
	
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
				`latitude`, `longitude`, `speed`, `course`, `altitude`, `updated` ) VALUES ( " .
				 quote_smart($lat) . "," .
				 quote_smart($lng) . ",
				 quote_smart($speed) . ",
				 quote_smart($course) . ",
				 quote_smart($altitude) . ",
				 '{$now}') ";	 
	snap(__LINE__, $query);
	
	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	}

$temp = explode ("/", get_variable("auto_refresh"));									// apply sit screen auto-refresh time in minutes
if (intval($temp[0]) > 0 ) {						
	$_SESSION['next_pos_update'] = mysql_format_date ( $now() + ( $temp[0] * 60 ) );	// to next update
	}
exit (0);
?>
