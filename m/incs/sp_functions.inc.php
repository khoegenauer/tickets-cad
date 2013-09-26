<?php
/* sp-specific functions
4/8/2013 initial release 
7/25/2013 - added  function gcd()
7/30/2013 - added function get_unit_status ()
8/12/2013 - added sp_is_guest
*/

/*
	$query = "
		(SELECT '2' AS `which`, ... AND ... )
	UNION 
		(SELECT '1' AS `which`, ... AND ...)
	UNION 
		(SELECT '0' AS `which`, ... AND ...)
	UNION
		(SELECT '3' AS `which`, ... AND ...)
 		";
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );
$ini_arr = parse_ini_file ("sp.ini");

@session_start();	

$GLOBALS['TABLE_TICKET'] 	= 		0;	
$GLOBALS['TABLE_RESPONDER'] = 		1;
$GLOBALS['TABLE_FACILITY']  = 		2;
$GLOBALS['TABLE_ASSIGN']   	= 		3;
$GLOBALS['TABLE_ROAD']   	= 		4;
$GLOBALS['TABLE_CLOSED']   	= 		5;
$GLOBALS['ME']   			= 		6;
$GLOBALS['TABLE_RESPONDER_HIDE'] = 	7;

//$GLOBALS['FONT_SIZE']   	= ".8";
$GLOBALS['FONT_SIZE']   	= $ini_arr['def_fontsize'];

define("COL_WIDTH", 24);

function sp_is_super(){		
	return ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_SUPER']);		
	}
function sp_is_administrator(){		/* is user admin or super? */
	return (($_SESSION['SP']['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_SUPER']));		// 5/11/10
	}
function sp_is_admin(){		/* is user admin but not super? */
	return (($_SESSION['SP']['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']));		
	}	
function sp_is_guest(){				/* is user guest? */
	return (($_SESSION['SP']['level'] == $GLOBALS['LEVEL_GUEST']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_MEMBER']));				// 6/25/10
	}
function sp_is_member(){				/* is user member? */
	return (($_SESSION['SP']['level'] == $GLOBALS['LEVEL_MEMBER']));			
	}
function sp_is_user(){					/* is user operator/dispatcher? */
	return ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_USER']);		
	}
function sp_is_unit(){					/* is user unit? */			
	return ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_UNIT']);
	}
function sp_is_statistics(){					/* is user statistics? */			
	return ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_STATISTICS']);	
	}
function sp_is_service_user(){					/* is user service user? */			
	return ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_SERVICE_USER']);
	}
function sp_see_buttons() {
	return (($_SESSION['SP']['level'] == $GLOBALS['LEVEL_ADMINISTRATOR']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_SUPER']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_UNIT']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_USER']) || ($_SESSION['SP']['level'] == $GLOBALS['LEVEL_MEMBER']));		// 10/11/12
	}
function sp_may_email() {
	return (!(sp_is_guest()) || (sp_is_member() || sp_is_unit())) ;						// members, units  allowed
	}

function my_gcd ( $in_lat1, $in_lon1, $in_lat2, $in_lon2) {				// great circle distance - miles
	$rad = doubleval(pi()/180.0);

	$lon1 = doubleval($in_lon1)*$rad; $lat1 = doubleval($in_lat1)*$rad;
	$lon2 = doubleval($in_lon2)*$rad; $lat2 = doubleval($in_lat2)*$rad;
	
	$theta = $lon2 - $lon1;
	$dist = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos ($theta));
	if ($dist < 0) { $dist += pi(); }
	$dist = $dist * 6371.2;
	$miles = doubleval($dist * 0.621);
	return round ( $miles, 1 );		// 1 decimal precision	- $inches = doubleval($miles*63360)
	}

function gcd ( $lat1, $lon1, $lat2, $lon2) {				// great circle distance - 7/25/2013
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	return $dist;
	}
function good_date_time_sp($date) {						//  2/15/09 - 00 00:00
	return (is_string ($date) && (strlen($date)==19) && (!($date=="0000-00-00 00:00:00")) && (!($date=="00 00:00")));	}


function get_session_css ($day_night) {
	@session_start();	
	$_SESSION['SP']['css']['row_light'] = 				get_css("row_light", $day_night);
	$_SESSION['SP']['css']['row_light_text'] = 			get_css("row_light_text", $day_night);
	$_SESSION['SP']['css']['row_dark'] = 					get_css("row_dark", $day_night);
	$_SESSION['SP']['css']['row_dark_text'] = 			get_css("row_dark_text", $day_night);
	$_SESSION['SP']['css']['row_heading_background'] = 	get_css("row_heading_background", $day_night);
	$_SESSION['SP']['css']['page_background'] = 			get_css("page_background", $day_night);
	$_SESSION['SP']['css']['normal_text'] = 				get_css("normal_text", $day_night);
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
	    case $GLOBALS['STATUS_RESERVED']: 	return "Reserved" ;	break;
	    case $GLOBALS['STATUS_CLOSED']: 	return "Closed";	break;
	    case $GLOBALS['STATUS_OPEN']: 		return "Open" ;		break;
	    case $GLOBALS['STATUS_SCHEDULED']: 	return "Scheduled";	break;
	    default:							return "ERROR ERROR ERROR";
		}			// end switch
	}		// end function get status

function get_response($tick_id) {									// returns string of handles
	$query = "SELECT `r`.`handle`, `r`.`id` FROM `$GLOBALS[mysql_prefix]assigns`  `a` 
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`a`.`responder_id` = `r`.`id`)
				WHERE (`a`.`ticket_id` = {$tick_id})
				ORDER BY `handle` ASC";

	$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	$return_str = $sep = "";
	while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {		// each data row
		$link_span = "<span class='link' onclick = \"do_resp({$in_row['id']})\">{$in_row['handle']}</span>";	// to function do_resp()
		$return_str .= "{$sep}{$link_span}";
		$sep = ", ";
		}
	return $return_str;
	}			// end  get response()

function do_colname ($instr) {			// splits on underscore
	$temp = explode ("_", $instr);
	return ucfirst($temp[(count($temp)-1)]);
	}


function sp_show_list($res, $hides, $proc, $top_row_txt = "" ) {					// returns table for display - 4/6/2013
	$div_height = $_SESSION['SP']['scr_height'] - 200;									// nav bars			
	$out_str = "<div id = 'sp_show_list' style = 'border:5px solid blue; height:{$div_height}px; width:auto; overflow: auto; text-align:center; '><!-- " . basename(__FILE__) . __LINE__ . " -->\n";

	$in_row = stripslashes_deep(mysql_fetch_array($res));
	$do_severity = (array_key_exists ( "severity", $in_row ));		// apply text color?
	@mysql_data_seek($res, 0) ;										// reset for data iteration
	
	$out_str = "<br/><br/><br/>\n<table id = 'table' class='tablesorter table-striped' border = 2  align='center' style = 'width:98%;'>\n";
	$out_str .= "<thead>\n<tr class = 'even' valign = 'bottom'><th colspan = 99 align = 'center'><b>{$top_row_txt}</b></th></tr>\n";
	$out_str .= "<tr class = 'odd'>";
	for ($i=1; $i< mysql_num_fields($res); $i++) {				// header row - skip id column
		if (!in_array(mysql_field_name($res, $i), $hides)) {
			$out_str .= "<th>" . do_colname ( mysql_field_name($res, $i)) . "</th>\n";		// field name
			}
		}
	$out_str .= "</tr>\n</thead>\n<tbody>\n";

	$i = 0;				// row index
 	while ($in_row = stripslashes_deep(mysql_fetch_array($res))) {					// each data row
// 		dump($in_row);
		$severity_class_str = ($do_severity )? 	"class = '" . get_severity($in_row['severity']) . "'" : "";		// apply text color?
		$url = "navTo('{$proc}', {$i})";												// set for row click
		$out_str .= "<tr {$severity_class_str} onclick = \"{$url};\">\n";	
		for ($j=1; $j< mysql_num_fields($res); $j++) {
			if (!in_array(mysql_field_name($res, $j), $hides)) {					// hides?
				switch (mysql_field_name($res, $j)) {
					case "unit_status" :						// 
						$temp = get_unit_status ($in_row['id']);					// returns array
						if ( count ( $temp ) ==2 ) 	$display_val = "<b>{$temp[0]}</b> ({$temp[1]})";	// incident id
						else 						$display_val = $temp[0];
						break;
					case "unit_tr" :
						$temp = get_tracking_type($in_row);							// returns array
						$display_val = $temp[1];
						break;
//					case "problem_start" :
//						$display_val = format_date_2 ($in_row['problem_start']) ;
//						break;
					case "mine" :
						$display_val = ($in_row[$j]==1)? "<img src = './images/checked.png'>" : "";
						break;
					case "severity" :
						$display_val = get_severity($in_row['severity']);
						break;
					case "scheduled" :		// 
						$display_val = ( good_date_time_sp ( $in_row['scheduled'] ) )? format_date_2 ( $in_row['scheduled'] ) : "" ;
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
//	$out_str .= "</tbody>\n</table></div><!-- " . basename(__FILE__) . __LINE__ . " -->\n";
	$out_str .= "</tbody>\n</table><!-- " . basename(__FILE__) . __LINE__ . " -->\n";
	return $out_str;
	}		// end function sp_show_list()

function report_error ($in_str) {		// call => report_error (basename(__FILE__) . __LINE__)
	@session_start();
	$err_msg = "error@" . $in_str;
	if (!(array_key_exists ( $err_msg, $_SESSION['SP'] ))) {		// limit to once per session
		array_push ( $_SESSION['SP'], $err_msg );
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
					 {$act_row['dob']}</TD>\n
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
	if ( ( intval ( mysql_affected_rows() ) + $actr ) > 0 )  { 				//
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
		}				// end if got actions
	else {
		return "";
		}
	}			// end function sp_show_actions
	
	function get_fac_sql ($the_id) {		//  get all/except the selected facility
		return	"
			SELECT 
			 CONCAT_WS('/',`f`.`handle`,`t`.`name`) AS `facility`, 
			`f`.`id` AS `id`, 
			`s`.`status_val` AS `status`,
			CONCAT_WS(' ',`street`,`city`, `state`) AS `addr`,
			CONCAT_WS('/',`beds_a`,`beds_o`) 		AS `beds A/O`, `beds_info` AS `beds info`,
			`f`.`capab` AS `capability`,
			`f`.`other`,
			`f`.`contact_name` 						AS `contact`,
			`f`.`contact_email` 					AS `email`,
			`f`.`contact_phone` 					AS `phone`,
			`f`.`security_contact` 					AS `sec'y contact`,
			`f`.`security_email` 					AS `sec'y email`,
			`f`.`security_phone` 					AS `sec'y phone`,
			`f`.`opening_hours`,
			`f`.`access_rules`,
			`f`.`security_reqs`,
			`f`.`pager_p`,
			`f`.`pager_s`,
			`f`.`callsign`,
			`f`.`send_no`,
			`t`.`icon` AS icon,
			 1 AS `map`,
			`t`.name AS type, 
			`f`.`updated` 							AS `as of`			
			FROM `$GLOBALS[mysql_prefix]facilities`  `f`
			LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` 	ON ( `f`.`id` = a.resource_id )			
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` 	ON `f`.type = `t`.id 
			LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` 	ON `f`.status_id = `s`.id 
			WHERE ( `f`.`id` = '{$the_id}' ) LIMIT 1				
			";
			}				// end function
	

	function get_resp_sql ($the_id) {				//  get all/except the selected responder
		$radius =  ( trim(get_variable("locale") ) ==0 ) ?  3959: 6371;
		$pos_array = ( ( is_ok_position ( $_SESSION['SP'] ['latitude'] , $_SESSION['SP'] ['longitude'] ) ) ) ?
			array ( $_SESSION['SP'] ['latitude'], $_SESSION['SP'] ['longitude']):  
			array ( get_variable('def_lat'), get_variable('def_lng') ) ;
		
		$unit_text = get_text("Units");		
		return "SELECT 
			`r`.`id`										AS `id`,
			CONCAT_WS(' / ', `r`.`handle`, `y`.`name`) 		AS `{$unit_text}`,
			1												AS `unit_status`,
			`r`.`name`										AS `name`,
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
			`r`.`glat`										AS `g Latitude`,
			`r`.`callsign`									AS `callsign`,
			`r`.`lat`										AS `lat`,
			`r`.`lng`										AS `lng`,				
			1												AS `map`,

			(SELECT  COUNT(*) FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = '{$the_id}'  
				AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) )
															AS `dispatched`,
			( ROUND ( {$radius} * acos (
				cos(radians(  {$pos_array[0]} ) ) *
				cos(radians(`lat`) ) *
				cos(radians(`lng`) - radians( {$pos_array[1]} ) ) +
				sin(radians( {$pos_array[0]} ) ) *
				sin(radians(`lat`) ) ) , 1 ) )				AS `dist`, 			
			
			`s`.`bg_color`									AS `bg_color`,				
			`s`.`text_color`								AS `text_color`,				
			`r`.`updated` 									AS `as of`			
			FROM `$GLOBALS[mysql_prefix]responder` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` 	ON (`r`.`type` = `y`.`id`)
			WHERE (`r`.`id` = {$the_id})
			LIMIT 1	";
			}				// end function
	
	function get_tick_sql ($the_id) {			// get all/except the selected incident
		return "SELECT 
			`t`.`id`  									AS `id`,
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
					) 									AS `actions`,						
			(SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]patient`
					WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id`
					) 									AS `patients`,
			1											AS `map`,		
			1											AS `nearby`,		
			`t`.`lat`									AS `lat`,
			`t`.`lng`									AS `lng`,
			`t`.`updated` 								AS `as of`
			FROM `$GLOBALS[mysql_prefix]ticket` `t` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON (`t`.`in_types_id` = `y`.`id`)
			WHERE (`t`.`id` = {$the_id})
			LIMIT 1	";
			}				// end function

	function it_is_time() {								// time to do the next position update?
		return 	( ( intval ($_SESSION['SP']['user_unit_id'] ) > 0 ) && 
				( time() > $_SESSION['SP']['next_pos_update'] ) 
				) ;
		}				// end function
	

	function get_unit_status ($in_id) {				// given a unit id, returns 2-element array if on dispatch, else a 1-element array
		$query = "SELECT  `a`.`id`,`a`.`dispatched`,`a`.`responding`,`a`.`on_scene`,`a`.`u2fenr`,`a`.`u2farr`,`a`.`clear`,`t`.`scope` 
			FROM `$GLOBALS[mysql_prefix]assigns` `a`
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`a`.`ticket_id` = `t`.`id`)					
			WHERE `a`.`responder_id` = '{$in_id}'  
			AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
			AND ( `dispatched`  > NOW() - INTERVAL 25 YEAR
				OR `responding` > NOW() - INTERVAL 25 YEAR
				OR `on_scene`  	> NOW() - INTERVAL 25 YEAR
				OR `responding` > NOW() - INTERVAL 25 YEAR
				OR `u2fenr`  	> NOW() - INTERVAL 25 YEAR
				OR `u2farr`  	> NOW() - INTERVAL 25 YEAR )
			ORDER BY `as_of` DESC LIMIT 1";					// obtain most recent				
					
		$result = mysql_query($query) or do_error( $query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows ( $result ) > 0 ) {
			$status_row = mysql_fetch_assoc($result);

			$tags_arr = explode("/", get_variable('disp_stat'));		
			if (count ($tags_arr) < 6) {$tags_arr =  explode("/", "D/R/O/FE/FA/Clear");}	// force default if invalid user setting
			extract ($status_row);	
			if (is_date($dispatched))	{$status =  $tags_arr[0];}
			if (is_date($responding))	{$status =  $tags_arr[1];}
			if (is_date($on_scene)) 	{$status =  $tags_arr[2];}
			if (is_date($u2fenr)) 		{$status =  $tags_arr[3];}
			if (is_date($u2farr)) 		{$status =  $tags_arr[4];}		
			return array ( $status, $scope );
			}			// end if (mysql_num_rows > 0)
		else {
			$query = "SELECT `s`.`status_val`	FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = `s`.`id` )
				WHERE `r`.`id` = '{$in_id}' LIMIT 1";
			$result = mysql_query($query) or do_error( $query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
			$status_row = mysql_fetch_assoc($result);
			return array ( $status_row['status_val'] );		// one-element array	
			}				// end if/else
		}				// end function get_unit_status ()

	function get_tracking_type ($in_row) {							// given row returns array
		if (intval($in_row['APRS']) == 1 ) 			return array ( "APRS", 			"Ap");
		if (intval($in_row['instamapper']) == 1 ) 	return array ( "Instamapper", 	"In");
		if (intval($in_row['open GTS']) == 1 ) 		return array ( "OGTS", 			"OG");
		if (intval($in_row['t tracker']) == 1 ) 	return array ( "T Tracker", 	"Tt");
		if (intval($in_row['locateA']) == 1 ) 		return array ( "LocateA", 		"Lo");
		if (intval($in_row['g tracker']) == 1 ) 	return array ( "Gtrack", 		"Gt");
		if (intval($in_row['g Latitude']) == 1 ) 	return array ( "Latitude", 		"La");
		}

	function get_mobile_time ($in_row) {							// given row returns datetime or empty string
//		dump($in_row);
		if ( empty ($in_row['callsign'] ) ) return "";
		$query = "SELECT `source`, `updated`,
			SUBSTRING(CAST(`t`.`updated` AS CHAR),9,8 ) 	AS `last`
			FROM `$GLOBALS[mysql_prefix]tracks` `t`		
			WHERE `source` = '{$in_row['callsign']}'		
			ORDER BY `updated` DESC LIMIT 1";			// obtain most recent									
		$result = mysql_query($query) or do_error( $query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows ( $result ) > 0 ) {
			$row = mysql_fetch_assoc($result);
			return $row ['last'];
			}
		else {return "";}
		}				// end get_mobile_time ()
		

function sp_get_status_sel($unit_in, $status_val_in, $tbl_in) {		// returns select list as click-able string - 8/12/2013
	switch ($tbl_in) {
		case ("u") :
			$tablename = "responder";
			$link_field = "un_status_id";
			$status_table = "un_status";
			$status_field = "status_val";
			break;
		case ("f") :
			$tablename = "facilities";
			$link_field = "status_id";
			$status_table = "fac_status";
			$status_field = "status_val";
			break;
		default:
			print "ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ";	
			}

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$tablename}`, `$GLOBALS[mysql_prefix]{$status_table}` WHERE `$GLOBALS[mysql_prefix]{$tablename}`.`id` = $unit_in 
		AND `$GLOBALS[mysql_prefix]{$status_table}`.`id` = `$GLOBALS[mysql_prefix]{$tablename}`.`{$link_field}` LIMIT 1" ;	

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {				// 2/7/10
		$init_bg_color = "transparent";
		$init_txt_color = "black";	
		}
	else {
		$row = stripslashes_deep(mysql_fetch_assoc($result)); 
		$init_bg_color = $row['bg_color'];
		$init_txt_color = $row['text_color'];
		}

	$guest = sp_is_guest();
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]{$status_table}` ORDER BY `group` ASC, `sort` ASC, `{$status_field}` ASC";	
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$dis = ($guest)? " DISABLED": "";								// 9/17/08
	$the_grp = strval(rand());			//  force initial OPTGROUP value
	$i = 0;
	$outstr = ($tbl_in == "u") ? "\t\t<SELECT CLASS='sit' id='frm_status_id_u_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color};' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; ' >" :
	"\t\t<SELECT CLASS='sit' id='frm_status_id_f_" . $unit_in . "' name='frm_status_id' {$dis} STYLE='background-color:{$init_bg_color}; color:{$init_txt_color}; width: 90%;' ONCHANGE = 'this.style.backgroundColor=this.options[this.selectedIndex].style.backgroundColor; this.style.color=this.options[this.selectedIndex].style.color; ' >";	// 12/19/09, 1/1/10. 3/15/11
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_st))) {
		if ($the_grp != $row['group']) {
			$outstr .= ($i == 0)? "": "\t</OPTGROUP>";
			$the_grp = $row['group'];
			$outstr .= "\t\t<OPTGROUP LABEL='$the_grp'>";
			}
		$sel = ($row['id']==$status_val_in)? " SELECTED": "";
		$outstr .= "\t\t\t<OPTION VALUE=" . $row['id'] . $sel ." STYLE='background-color:{$row['bg_color']}; color:{$row['text_color']};'  onMouseover = 'style.backgroundColor = this.backgroundColor;'>$row[$status_field] </OPTION>";		
		$i++;
		}		// end while()
	$outstr .= "\t\t</OPTGROUP>\t\t</SELECT>";
	return $outstr;
	}				// function sp_get_status_sel()

function is_logged_in ( $update ) {		// updates session expiry
	@session_start();
	if ( ( 	array_key_exists('SP', $_SESSION ) )
			&& ( array_key_exists( 'expires', $_SESSION['SP'] ) )
			&& ( $_SESSION['SP']['expires'] > now_ts() ) )
		{ 								// passes time check			
		if ( $update) {	
			$expiry = mysql_format_date(expires()) ;		// now() + $GLOBALS['SESSION_TIME_LIMIT']
			$_SESSION['SP']['expires'] = $expiry;
			}				// end if ( $update) 
		  return true;		// either way
		  }					// end passes time check
  	else { 				// fails time check
  		return false; 
  		}
	}		// end function is_logged_in ()

	