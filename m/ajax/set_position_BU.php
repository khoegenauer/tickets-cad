<?php
/*
6/26/2013 - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once('../../incs/functions.inc.php');

$now = mysql_format_date(time() - (get_variable('delta_mins')*60));

$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
	`lat` = " . quote_smart($_POST['lat']) . ",
	`lng` = " . quote_smart($_POST['lng']) . ",
	`updated` = '{$now}' 
	WHERE `id` = " . quote_smart($_POST['unit_id']) . " LIMIT 1";

$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
			`latitude`, `longitude`, `updated` ) VALUES ( " .
			 quote_smart($_POST['lat']) . "," .
			 quote_smart($_POST['lng']) . ",
			 '{$now}') ";	 

$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

exit (0);
?>
