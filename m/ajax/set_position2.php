<?php
/*
6/26/2013 - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once('../../incs/functions.inc.php');
require_once('../incs/sp_functions.inc.php');

extract ($_GET);
@session_start();	

$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
	`lat` = " . quote_smart($latitude) . ",
	`lng` = " . quote_smart($longitude) . "
	WHERE `id` = " . quote_smart($_SESSION['user_unit_id']) . " LIMIT 1";

$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

//if (mysql_affected_rows($result) == 1 ) { 				// if any change
	$now = now_ts() ;									// timestamp 
	$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `updated` = '{$now}' 
		WHERE `id` = " . quote_smart($_SESSION['user_unit_id']) . " LIMIT 1"; 
	snap(__LINE__, $query);
	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);		
	
	$the_latitude = (is_ok_float ($latitude))? 		quote_smart($latitude) : "NULL";
	$the_longitude = (is_ok_float ($longitude))? 	quote_smart($longitude) : "NULL";

	$the_speed = (is_ok_int ($speed))? 				quote_smart($speed) : "NULL";
	snap (__LINE__, __LINE__);

/*
	$the_course = (is_ok_int ($course))? 			quote_smart($course) : "NULL";
	$the_altitude = (is_ok_int ($altitude))? 		quote_smart($altitude) : "NULL";
	$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
				`latitude`, `longitude`, `speed`, `course`, `altitude`, `updated` ) VALUES ( 
				 {$the_latitude} ,
				 {$the_longitude},
				 {$the_speed},
				 {$the_course},
				 {$the_altitude},
				 '{$now}') ";	 
	snap(__LINE__, $query);
	
	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	}

$temp = explode ("/", get_variable("auto_refresh"));									// apply sit screen auto-refresh time in minutes
if (intval($temp[0]) > 0 ) {						
	$_SESSION['next_pos_update'] = mysql_format_date ( $now() + ( $temp[0] * 60 ) );	// to next update
	}
*/
exit (0);
?>
