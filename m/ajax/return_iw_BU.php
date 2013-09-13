<?php
/*
4/18/2013 - initial release
6/23/2013 - added roadinfo handling
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once('../../incs/functions.inc.php');
require_once('../incs/sp_functions.inc.php');

$GLOBALS['TABLE_TICKET'] 	= 0;	
$GLOBALS['TABLE_RESPONDER'] = 1;
$GLOBALS['TABLE_FACILITY']  = 2;
$GLOBALS['TABLE_ASSIGN']   	= 3;
$GLOBALS['TABLE_ROAD']   	= 4;

extract ($_POST);
$the_id = intval($_POST['record_id']);
switch ($table_id) {

    case $GLOBALS['TABLE_TICKET']:
			$query = "SELECT 
				CONCAT_WS(' ',`street`,`city`, `state`) 	AS `location`,
				`t`.`scope`									AS `scope` ,
				`t`.`severity`  							AS `severity` , 
				`t`.`problemstart`							AS `start` ,
				CONCAT_WS(' / ',`scope`,`y`.`type`) 		AS `incident/type`,
				`y`.`protocol`								AS `response protocol`, 
				`t`.`id`  									AS `id`,
				`t`.`description`							AS `synopsis` ,
				`t`.`comments`								AS `disposition` ,
				`t`.`status`, 
				(SELECT COUNT( * )
						FROM `$GLOBALS[mysql_prefix]action`
						WHERE `$GLOBALS[mysql_prefix]action`.`ticket_id` = `t`.`id`
						) AS `actions`,(						
				SELECT COUNT( * )
						FROM `$GLOBALS[mysql_prefix]patient`
						WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id`
						) AS `patients`,
				`updated` AS `as of`				
				FROM `$GLOBALS[mysql_prefix]ticket` `t` 
				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
				WHERE (`t`.`id` = {$the_id})
				LIMIT 1	";

		
		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		if (!(mysql_num_rows ( $result )) == 1 ) {
			report_error ( basename(__FILE__) . __LINE__);
			}
		else {
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
			$hides = array("status", "tick_id", "lat", "lng", "_by", "in_types_id", "group", "id", "set_severity", "sort", "color" , "date", "street", "city", "state");		// hide these columns
			echo "\n<center><h2>" . get_text("Incident") . " " . "{$row['scope']} </h2>\n";
			$ret_str =  "<table border=0>";
			for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
				if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?	
					if ( ! (empty($row[$i]) ) ) {
						$fn = get_text(ucfirst(mysql_field_name($result, $i)));
						
						switch (mysql_field_name($result, $i) ) {
							case "severity":
								$the_severity_class = get_severity_class($row[$i]);
								$ret_str .= "<tr><td>{$fn}:</td><td class = '{$the_severity_class}'>" . get_severity($row[$i]) . "</td></tr>";		// get_status_str
								break;

							case "start":
							case "as of":
								$datestr = format_date(strval(strtotime($row[$i])));
								$ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
								break;

							case "responding":					
								$ret_str .= "<tr><td>{$fn}:</td><td>" . get_response($row[$i]) . "</td></tr>";		// string of handles
								break;							

							case "status":
								$the_status = get_status_str($row[$i]);
								$the_diff = my_date_diff ( $row["start"], mysql_format_date(now())) ;
								$ret_str .= "<tr><td>{$fn}:</td><td>{$the_status} ({$the_diff})</td></tr>";		
								break;

							default: 
								$ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>";
							};				// end switch ()
							
						
//						$ret_str .=  "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
						}
					}
				}
			$ret_str .=  "</table>\n";    
			}
		$ret_str .=  sp_show_actions ($the_id);
		break;
		
    case $GLOBALS['TABLE_RESPONDER']:
		$query = "SELECT *, CONCAT_WS(' ',`street`,`city`) AS `addr`, 
			`r`.`description` AS `resp_descr`,
			`y`.`description` AS `unit_type`
			FROM `$GLOBALS[mysql_prefix]responder` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` 	ON (`r`.`type` = `y`.`id`)
			WHERE (`r`.`id` = {$the_id})
			LIMIT 1	";
		
		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		if (!(mysql_num_rows ( $result )) == 1 ) {
			report_error ( basename(__FILE__) . __LINE__);
			}
		else {
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
			echo "\n<center><h2>" . get_text("Responder") ." " . "{$row['handle']}</h2>\n";	
			$hides = array("description", "un_status_id", "lat", "lng", "icon_str", "hide", "group", "bg_color", "text_color", "icon", "user_id", "type", "id", "sort", "_on", "_from", "_by");
		
			$ret_str = "<table border=0>";
			for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
				if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides 	
					if ( ! (empty($row[$i]) ) ) {
						$fn = get_text ( ucfirst(mysql_field_name($result, $i ) ) );
						$ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
						}
					}
				}
			$ret_str .= "</table>\n";
			}
		break;

    case $GLOBALS['TABLE_FACILITY']:
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` `f`
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` 	ON (`f`.`status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` 	ON (`f`.`type` = `t`.`id`)
			WHERE (`f`.`id` = {$the_id})
			LIMIT 1	";
		
		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		if (!(mysql_num_rows ( $result )) == 1 ) {
			report_error ( basename(__FILE__) . __LINE__);
			}
		else {			
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
			$hides = array("tick_id", "lat", "lng", "_by", "_from", "_on", "id" );						// hide these columns
			echo "\n<center><h2>" . get_text("Facility") ." " . "{$row['handle']}</h2>\n";
			
			$ret_str = "<table border=0>";

			for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
				if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?	
					if ( ! (empty($row[$i]) ) ) {
						$fn = get_text(ucfirst(mysql_field_name($result, $i)));
						$ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
						}
					}
				}
			$ret_str .= "</table>\n";
			}
		break;		
		
    case $GLOBALS['TABLE_ROAD']:				// 6/23/2013

		$query = "SELECT 
			`r`.`title`, 
			`r`.`description`, 
			`r`.`date`,
			`r`.`lat`,
			`r`.`lng`,
			`u`.`user` AS `by`
			FROM `$GLOBALS[mysql_prefix]roadinfo` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`u`.`id` = `r`.`_by`)
			WHERE `r`.`id` = {$the_id} LIMIT 1";				

		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		if (!(mysql_num_rows ( $result )) == 1 ) {
			report_error ( basename(__FILE__) . __LINE__);
			}
		else {			
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
			$hides = array("id", "lat", "lng", "", "_from", "_on", "icon" );						// hide these columns
			echo "\n<center><h2>" . get_text("Road information") . " {$row['title']}</h2>\n";			
			$ret_str = "<table border=0>";

			for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
				if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?	
					if ( ! (empty($row[$i]) ) ) {
						$fn = get_text(ucfirst(mysql_field_name($result, $i)));
						switch ( mysql_field_name ( $result, $i ) ) {
							case "date":
								$datestr = format_date(strval(strtotime($row[$i])));
								$ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
								break;	
							default: 
								$ret_str .=  "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";								
							}				// end switch ()
						}
					}
				}
			$ret_str .= "</table>\n";
			}
		break;		
		
    default:
		report_error ( basename(__FILE__) . __LINE__);
    	return "error " . __LINE__;
	}				// end switch()

	echo $ret_str;
?>
