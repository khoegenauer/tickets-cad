<?php
/* sp-specific functions
4/8/2013 initial release 
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );

$GLOBALS['TABLE_TICKET'] 	= 0;	
$GLOBALS['TABLE_RESPONDER'] = 1;
$GLOBALS['TABLE_FACILITY']  = 2;
$GLOBALS['TABLE_ASSIGN']   	= 3;
define("COL_WIDTH", 20);

function get_session_css ($day_night) {
	@session_start();	
	$_SESSION['css']['row_light'] = 				get_css("row_light", $day_night);
	$_SESSION['css']['row_light_text'] = 			get_css("row_light_text", $day_night);
	$_SESSION['css']['row_dark'] = 					get_css("row_dark", $day_night);
	$_SESSION['css']['row_dark_text'] = 			get_css("row_dark_text", $day_night);
	$_SESSION['css']['row_heading_background'] = 	get_css("row_heading_background", $day_night);
	$_SESSION['css']['page_background'] = 			get_css("page_background", $day_night);
	$_SESSION['css']['normal_text'] = 				get_css("normal_text", $day_night);
	}

function is_ok_int ($instr) { return ( strval ( intval ( $instr ) ) == $instr ) ; }

function is_ok_float ($instr) {return ( ( strval ( floatval ( $instr ) ) == $instr ) && ( abs (floatval ( $instr ) ) <= 180.0 ) && ( abs ( floatval ( $instr ) ) != 0.0 ) ) ;}

function is_ok_position ($inlat, $inlng) {		// evaluates lat and lng 
	return ( is_ok_float ($inlat) && is_ok_float ($inlng) && ( $inlat != $GLOBALS['NM_LAT_VAL'] ) && ($inlng != $GLOBALS['NM_LAT_VAL'] ) ) ;
	}

function get_severity_class($severity) {
	switch ($severity) {
	    case $GLOBALS['SEVERITY_NORMAL']: 	return "severity_normal" ;    	break;
	    case $GLOBALS['SEVERITY_MEDIUM']: 	return "severity_medium";    	break;
	    case $GLOBALS['SEVERITY_HIGH']: 	return "severity_high" ;    	break;
	    default:							return "ERROR ERROR ERROR";
	    }				// end switch ()
	}				// end function get severity_class
	
function get_status_str($status) {									// returns text
	switch ($status) {
	    case $GLOBALS['STATUS_RESERVED']: 	return "Reserved" ;
	    	break;
	    case $GLOBALS['STATUS_CLOSED']: 	return "Closed";
	    	break;
	    case $GLOBALS['STATUS_OPEN']: 		return "Open" ;
	    	break;
	    case $GLOBALS['STATUS_SCHEDULED']: 	return "Scheduled";
	    	break;
	    default:							return "ERROR ERROR ERROR";
		}			// end switch
	}		// end function get status

function get_response($tick_id) {									// returns string of handles
	$query = "SELECT `r`.`handle` FROM `$GLOBALS[mysql_prefix]assigns`  `a` 
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`a`.`responder_id` = `r`.`id`)
				WHERE (`a`.`ticket_id` = {$tick_id})
				ORDER BY `handle` ASC";

	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	$handle_str = ""; $sep = "";
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {		// each data row
		$handle_str .= $sep . $in_row['handle'];
		 $sep = ", ";
		}
	return $handle_str;
	}			// end  get response()

function sp_show_list($res, $hides, $proc, $top_row_txt = "" ) {					// returns table for display - 4/6/2013
	$div_height = $_SESSION['scr_height'] - 200;									// nav bars			
	$out_str = "<div style = 'height:{$div_height}px; width:auto; overflow: auto; text-align:center; '>\n";

	$in_row = stripslashes_deep(mysql_fetch_array($res));
	$do_severity = (array_key_exists ( "severity", $in_row ));		// apply text color?
	@mysql_data_seek($res, 0) ;										// reset for data iteration
	
	$out_str = "<br/><br/><br/><table id = 'table' class='tablesorter table-striped' border = 0  align='center'>\n";
	$out_str .= "<thead>\n<tr class = 'even' valign = 'bottom'><td colspan = 99 align = 'center'><b>{$top_row_txt}</b></td></tr>\n";
	$out_str .= "<tr class = 'odd'>";
	for ($i=1; $i< mysql_num_fields($res); $i++) {				// header row - skip id column
		if (!in_array(mysql_field_name($res, $i), $hides)) {
			$out_str .= "<th>" . ucfirst(mysql_field_name($res, $i)) . "</th>\n";		// field name
			}
		}
	$out_str .= "</tr>\n</thead>\n<tbody>\n";

	$i = 0;				// row index
 	while ($in_row = stripslashes_deep(mysql_fetch_array($res))) {		// each data row
		$severity_class_str = ($do_severity )? 	"class = '" . get_severity($in_row['severity']) . "'" : "";		// apply text color?
		$url = "navTo('{$proc}', {$i})";												// set for row click
		$out_str .= "<tr {$severity_class_str} onclick = \"{$url};\">\n";	
		for ($j=1; $j< mysql_num_fields($res); $j++) {
			if (!in_array(mysql_field_name($res, $j), $hides)) {					// hides?
				switch (mysql_field_name($res, $j)) {
					case "mine" :
						$display_val = ($in_row[$j]==1)? "<img src = './images/checked.png'>" : "";
						break;
					default:
						$display_val = htmlentities(shorten($in_row[$j], COL_WIDTH));					
					}	// end switch
			
				$out_str .= "\t<td>{$display_val}</td>\n";							// field value
				}
			}
		$out_str .= "</tr>\n";
		$i++;
		}						// end while ($in_row ... )
	$out_str .= "</tbody>\n</table></div>\n";
	return $out_str;
	}		// end function sp_show_list()

function report_error ($in_str) {		// call => report_error (basename(__FILE__) . __LINE__)
	@session_start();
	$err_msg = "error@" . $in_str;
	if (!(array_key_exists ( $err_msg, $_SESSION ))) {		// limit to once per session
		array_push ( $_SESSION, $err_msg );
		do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_msg);	
		}
	}		// end function report_error ()


function sp_show_actions ($the_id) {			/* list actions and patient data belonging to ticket */
	$print = "<TABLE BORDER='1' ID='patients'>";
																	/* list patients */
	$query = "SELECT *,
		`date` AS `date`, 
		`updated` AS `updated` 
		FROM `$GLOBALS[mysql_prefix]patient` `p` 
 		LEFT JOIN `$GLOBALS[mysql_prefix]insurance` `i` ON (`i`.`id` = `p`.`insurance_id` )
 		WHERE `ticket_id`='{$the_id}' ORDER BY `date`";

//	dump($query);

	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$caption = get_text("Patient") . ": &nbsp;&nbsp;";
	$actr=0;
	$genders = array("", "M", "F", "T", "U");
	while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$the_gender = $genders[$act_row['gender']];

		$tipstr = addslashes("Name: {$act_row['name']}<br> Fullname: {$act_row['fullname']}<br> DOB: {$act_row['dob']}<br> Gender: {$the_gender}<br>  Insurance_id: {$act_row['ins_value']}<br>    Facility_contact: {$act_row['facility_contact']}<br>    Date: {$act_row['date']}<br>Description:{$act_row['description']}");
	
		$print .= "<TR  valign = 'top' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\">\n
			\t<TD VALIGN='top' NOWRAP CLASS='td_label'>{$caption}</TD>\n";
		$print .= "<TD NOWRAP>{$act_row['name']}</TD>\n
			\t<TD NOWRAP>". format_date_2($act_row['updated']) . "</TD>\n";
		$print .= "\t<TD NOWRAP> by <B>". get_owner($act_row['user'])."</B>";
		
		$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'] ? "*" : "-")."</TD>\n
			\t<TD>" . shorten($act_row['description'], 24) . "</TD>\n";
			
		$print .= "</TR><TR>\n";
		$print .=  "\t<TD></TD><TD>Y({$genders[$act_row['gender']]}) - {$act_row['fullname']} -
					 Z{$act_row['dob']}</TD>\n
			</TR>\n
			<TR>
				\t<TD></TD><TD>A{$act_row['ins_value']} -
				B{$act_row['facility_contact']}</TD>\n
			</TR>\n";
		
		$caption = "";				// once only
		$actr++;
		}
																	/* list actions */
	$query = "SELECT *,
		`date` AS `date`,
		`updated` AS `updated` 
		FROM `$GLOBALS[mysql_prefix]action` 
		WHERE `ticket_id`='$the_id' 
		ORDER BY `date`";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if ((mysql_affected_rows() + $actr)==0) { 				// 8/6/08
		return "";
		}				
	else {
		$query = "SELECT `id`, `name`, `handle` FROM `$GLOBALS[mysql_prefix]responder`";
		$resp_result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$responderlist = array();
		$responderlist[0] = "NA";	
		while ($resp_row = stripslashes_deep(mysql_fetch_assoc($resp_result))){
			$responderlist[$resp_row['id']] = $resp_row['handle'];
			}
	
		$caption = "Actions: &nbsp;&nbsp;";
		$pctr=0;
		while ($act_row = stripslashes_deep(mysql_fetch_assoc($result))){
		$tipstr = addslashes($act_row['description']);		
			$print .= "<TR valign = 'top' onmouseout=\"UnTip();\" onmouseover=\"Tip('{$tipstr}');\" >
				<TD VALIGN='top' NOWRAP CLASS='td_label'>$caption</TD>";
			$responders = explode (" ", trim($act_row['responder']));	// space-separated list to array
			$sep = $respstring = "";
			for ($i=0 ;$i< count($responders);$i++) {				// build string of responder names
				if (array_key_exists($responders[$i], $responderlist)) {
					$respstring .= $sep . "&bull; " . $responderlist[$responders[$i]];
					$sep = "<BR />";
					}
				}

			$print .= "<TD CLASS='normal_text' NOWRAP>" . $respstring . "</TD><TD CLASS='normal_text' NOWRAP>". format_date_2($act_row['updated']) ."</TD>";	//	3/15/11
			$print .= "<TD CLASS='normal_text' NOWRAP>by <B>".get_owner($act_row['user'])."</B> ";	//	3/15/11
			$print .= ($act_row['action_type']!=$GLOBALS['ACTION_COMMENT'])? '*' : '-';
			$print .= "</TD><TD CLASS='normal_text' >" . nl2br($act_row['description']) . "</TD>";	//	3/15/11
			$caption = "";
			$pctr++;
			}				// end if/else (...)
		$print .= "</TABLE>\n";
		return $print;
		}				// end else
	}			// end function sp_show_actions
	
	function get_fac_sql ($the_id) {
		return	"SELECT 
				`f`.`handle` as facility,
				CONCAT_WS(' / ', `y`.`name`, `s`.`status_val`) 	AS `type/status`,
				CONCAT_WS(' ',`street`,`city`, `state`)			AS `addr`,
				CONCAT_WS('/',`beds_a`,`beds_o`) AS `beds A/O`,
				`beds_info` AS `beds info`,
				`f`.`description`,
				`f`.`capab`				AS `capability`,
				`f`.`other`,
				`f`.`contact_name`,
				`f`.`contact_email`,
				`f`.`contact_phone`,
				`f`.`security_contact`,
				`f`.`security_email`,
				`f`.`security_phone`,
				`f`.`opening_hours`,
				`f`.`access_rules`,
				`f`.`security_reqs`,
				`f`.`pager_p`,
				`f`.`pager_s`,
				`f`.`send_no`,
				`u`.`user`,
				`f`.`callsign`,
				`f`.`updated` 			AS `as of`
				FROM `$GLOBALS[mysql_prefix]facilities` `f`
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` 	ON (`f`.`status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `y` 	ON (`f`.`type` = `y`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 			ON (`f`.`user_id` = `u`.`id`)				
				WHERE (`f`.`id` = {$the_id})
				LIMIT 1";
				}				// end function
	
	function get_resp_sql ($the_id) {
			return "SELECT 
				`r`.`handle`									AS `handle`,
				`r`.`name`										AS `name`,
				CONCAT_WS(' / ', `y`.`name`, `s`.`status_val`) 	AS `type/status`,
				CONCAT_WS(' ',`street`,`city`, `state`) 		AS `location`,
				`r`.`description`								AS `description`,
				`r`.`capab`										AS `capabilities`,
				`r`.`contact_name`								AS `contact`,
				`r`.`contact_via`								AS `contact via`,
				`r`.`mobile`									AS `mobile`,
				`r`.`aprs`										AS `APRS`,
				`r`.`instam`									AS `instamapper`,
				`r`.`ogts`										AS `open GTS`,
				`r`.`locatea`									AS `locateA`,
				`r`.`gtrack`									AS `g tracker`,
				`r`.`t_tracker`									AS `t tracker`,
				`r`.`glat`										AS `latitude`,
				`r`.`callsign`									AS `callsign`
			
				FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` 	ON (`r`.`type` = `y`.`id`)
				WHERE (`r`.`id` = {$the_id})
				LIMIT 1	";
				}				// end function
	
	function get_tick_sql ($the_id) {
		return "SELECT 
			CONCAT_WS(' / ',`scope`,`y`.`type`) 		AS `incident/type`,
			CONCAT('*', `severity` ) 					AS `severity` , 
			CONCAT_WS(' ',`street`,`city`, `state`) 	AS `location`,
			`y`.`protocol`								AS `response protocol`, 
			`t`.`id`  									AS `responding`,
			`t`.`description`							AS `synopsis` ,
			`t`.`comments`								AS `disposition` ,
			`t`.`problemstart`							AS `start` ,
			`status`, 
			(SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]action`
					WHERE `$GLOBALS[mysql_prefix]action`.`ticket_id` = `t`.`id`
					) AS `actions`,						
			(SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]patient`
					WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id`
					) AS `patients`,
			`t`.`updated` AS `as of`
			FROM `$GLOBALS[mysql_prefix]ticket` `t` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
			WHERE (`t`.`id` = {$the_id})
			LIMIT 1	";
			}				// end function

	function it_is_time() {								// time to do the next position update?
		return 	( ( intval ($_SESSION['user_unit_id'] ) > 0 ) && 
				( now_ts() > $_SESSION['next_pos_update'] ) 
				) ;
		}				// end function
	
