<?php
/*
4/22/2013 - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once '../../incs/functions.inc.php';
require_once '../incs/sp_functions.inc.php';

extract ($_POST);		// var params = "the_column="+ column + "&record_id=" +record_id + "&function_id" + function_id;
//						- function_id reserved for possible 'reset' function
$query = "SELECT `ticket_id`, `responder_id`  FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = {$record_id} LIMIT 1";
$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
$row = mysql_fetch_assoc ($result);

//	do_set_time("dispatched", {$the_id}, 0)

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));
$the_set_value = ($function_id == 0)? "'{$now}'" : "NULL";				//  'undo' => NULL

$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET `{$the_column}` = {$the_set_value}, `as_of` = '{$now}' WHERE `id` = {$record_id} LIMIT 1";

$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
$log_vals = array(
    "dispatched" => 	$GLOBALS['LOG_CALL_DISP'],
    "responding" => 	$GLOBALS['LOG_CALL_RESP'],
    "on_scene" => 		$GLOBALS['LOG_CALL_ONSCN'],
    "u2fenr" => 		$GLOBALS['LOG_CALL_U2FENR'],
    "u2farr" => 		$GLOBALS['LOG_CALL_U2FARR'],
    "clear" => 			$GLOBALS['LOG_CALL_CLR']
    );

sp_do_log($log_vals[$the_column], $row['ticket_id'], $row['responder_id'], $record_id);

echo substr($now, 8, 8);					//	2012-11-03 14:13:45  => 03 14:13
exit (0);
