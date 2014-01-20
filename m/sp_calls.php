<?php
//$me = 6;		// responder id
/*
Calls module
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');		// 7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 
$ini_arr = parse_ini_file ("incs/sp.ini");

/*
array(2) {
  ["id"]=>
  string(1) "9"
  ["id_str"]=>
  string(35) "17,8,6,7,9,10,11,12,13,5,14,4,15,16"
}
*/

function get_calls_list_sql ($me_param) {
	global $ini_arr;
	$query_core = " 
		`t`.`severity`,
		`r`.`handle` 							AS `unit`,
		`s`.`status_val` 						AS `status`,
		`t`.`scope` 							AS `incident`,
		CONCAT_WS(' ',`t`.`street`,`t`.`city`) 	AS `addr`,
		SUBSTRING(CAST(`t`.`problemstart` AS CHAR),9,8 ) AS `start`,
		SUBSTRING(CAST(`a`.`dispatched` AS CHAR),9,8 ) AS `disp`,
		SUBSTRING(CAST(`a`.`responding` AS CHAR),9,8 ) AS `resp`,
		SUBSTRING(CAST(`a`.`on_scene` AS CHAR),9,8 ) AS `scene`,
		SUBSTRING(CAST(`a`.`u2fenr` AS CHAR),9,8 ) AS `u2fenr`,
		SUBSTRING(CAST(`a`.`u2farr` AS CHAR),9,8 ) AS `u2farr`,	
		SUBSTRING(CAST(`a`.`clear` AS CHAR),9,8 ) AS `clear`,	
		SUBSTRING(CAST(`a`.`as_of` AS CHAR),9,8 ) AS `as of`,
		`a`.`as_of`
		FROM `$GLOBALS[mysql_prefix]assigns` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`r`.`id` = `a`.`responder_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `a`.`ticket_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)";

	$hrs_closed = 	(array_key_exists('hrs_closed', $ini_arr)) ? $ini_arr['hrs_closed']:  24;	// 
	
	return "
		SELECT `a`.`id`, 0 AS `mine`, {$query_core} WHERE (`a`.`responder_id` =   {$me_param} AND  (`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ))
		UNION
		SELECT `a`.`id`, 1 AS `mine`, {$query_core} WHERE (`a`.`responder_id` <>  {$me_param} AND  (`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'))
		UNION
		SELECT `a`.`id`, 2 AS `mine`, {$query_core} WHERE (`a`.`clear` IS NOT NULL AND `a`.`clear` > ( NOW() - INTERVAL {$hrs_closed} HOUR))		
		ORDER BY `mine` ASC, `severity` DESC, `as_of` ASC
		";	
	}		// end function get_calls_list_sql ()


function get_calls_single_sql ($the_id) {
	$query_core = " 
		CONCAT_WS('/',`t`.`scope`, `y`.`type`, CONCAT_WS(' ', `t`.`street`,`t`.`city`)) AS `incident name/type/location`,	
		CONCAT('', `t`.`severity` ) 					AS `severity`,
		`y`.`protocol`,
		`t`.`description`,
		`t`.`comments` 									AS `ticket comments`,
		SUBSTRING(CAST(`t`.`booked_date` AS CHAR),9,8 ) AS `booked for`,	
		SUBSTRING(CAST(`t`.`problemstart` AS CHAR),9,8 )AS `start`,
		CONCAT_WS('/', `r`.`handle`, `uy`.`name`, `s`.`status_val`) 	AS `unit handle/type/status`,
		`t`.`id` 										AS `ticket_id`,
		`a`.`ticket_id` 								AS `response`,
		`a`.`id` 										AS `assign_id`,
		SUBSTRING(CAST(`a`.`dispatched` AS CHAR),9,8 ) 	AS `dispatched`,
		SUBSTRING(CAST(`a`.`responding` AS CHAR),9,8 ) 	AS `responding`,
		SUBSTRING(CAST(`a`.`on_scene` AS CHAR),9,8 ) 	AS `on scene`,
		SUBSTRING(CAST(`a`.`u2fenr` AS CHAR),9,8 ) 		AS `enroute to facility`,
		SUBSTRING(CAST(`a`.`u2farr` AS CHAR),9,8 ) 		AS `arrived at facility`,	
		SUBSTRING(CAST(`a`.`clear` AS CHAR),9,8 ) 		AS `clear`,	
		`a`.`comments` 									AS `run comments`,
		CONCAT_WS('/', `a`.`start_miles`, `a`.`on_scene_miles`, `a`.`end_miles`, `a`.`miles`) 
														AS `miles start/on-scene/end/total`,
		SUBSTRING(CAST(`a`.`as_of` AS CHAR),9,8 ) 		AS `as of`
		FROM `$GLOBALS[mysql_prefix]assigns` `a`
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`r`.`id` = `a`.`responder_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `a`.`ticket_id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` 		ON (`t`.`in_types_id` = `y`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `uy` 	ON (`r`.`type` = `uy`.`id`)";
	
	return "SELECT 	 {$query_core} 
		WHERE (`a`.`id` = {$the_id}
		AND	(`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) )
		LIMIT 1";
		}		// end function


@session_start();
if (! array_key_exists('SP', $_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title><?php echo get_text("Calls");?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<script src="./js/misc.js" type="text/javascript"></script> 

<?php

	if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 )  ) {		

		/* =================	 DETAIL DETAIL DETAIL DETAIL DETAIL DETAIL DETAIL =================== */
		
			function get_call_sql ($in_str) {
				return  " SELECT 	 
				CONCAT_WS('/',`t`.`scope`, `y`.`type`, CONCAT_WS(' ', `t`.`street`,`t`.`city`)) AS `{$in_str}`,	
				CONCAT('', `t`.`severity` ) 					AS `severity`,
				`y`.`protocol`,
				SUBSTRING(CAST(`t`.`booked_date` AS CHAR),9,8 ) AS `booked for`,	
				`t`.`description`,
				`t`.`comments` 									AS `ticket comments`,
				`t`.`id` 										AS `ticket_id`,
				CONCAT_WS('/', `r`.`handle`, `uy`.`name`, `s`.`status_val`) 	AS `unit handle/type/status`,
				`a`.`ticket_id` 								AS `response`,
				SUBSTRING(CAST(`t`.`problemstart` AS CHAR),9,8 )AS `start`,
				SUBSTRING(CAST(`a`.`dispatched` AS CHAR),9,8 ) 	AS `dispatched`,
				SUBSTRING(CAST(`a`.`responding` AS CHAR),9,8 ) 	AS `responding`,
				SUBSTRING(CAST(`a`.`on_scene` AS CHAR),9,8 ) 	AS `on scene`,
				SUBSTRING(CAST(`a`.`u2fenr` AS CHAR),9,8 ) 		AS `enroute to facility`,
				SUBSTRING(CAST(`a`.`u2farr` AS CHAR),9,8 ) 		AS `arrived at facility`,	
				SUBSTRING(CAST(`a`.`clear` AS CHAR),9,8 ) 		AS `clear`,	
				`a`.`comments` 									AS `run comments`,
				CONCAT_WS('/', `a`.`start_miles`, `a`.`on_scene_miles`, `a`.`end_miles`, `a`.`miles`) 
																AS `miles start/on-scene/end/total`,
				SUBSTRING(CAST(`a`.`as_of` AS CHAR),9,8 ) 		AS `information as of`
				FROM `$GLOBALS[mysql_prefix]assigns` `a`
				LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`r`.`id` = `a`.`responder_id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `a`.`ticket_id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` 		ON (`t`.`in_types_id` = `y`.`id`)
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `uy` 	ON (`r`.`type` = `uy`.`id`)";
				}				// end function


		function get_sidelinks () {		// returns 2-element array of strings
			global $id_array;
			$out_arr = array("", "");
			if ( $_POST['id'] > 0 ) {		// if not at array origin then a prior one exists

				$query = get_call_sql ("left_one") . "
					WHERE `a`.`id` = {$id_array[($_POST['id']-1)]} LIMIT 1";
				
				$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				$in_row = stripslashes_deep(mysql_fetch_array($result));
				$out_arr[0] = $in_row['left_one'];
				}
			if ( $_POST['id'] < count ($id_array)-1 ) {		// then not at end
				$query = get_call_sql ("right_one") . "
					WHERE `a`.`id` = {$id_array[($_POST['id']+1)]} LIMIT 1";
				
				$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				$in_row = stripslashes_deep(mysql_fetch_array($result));
				$out_arr[1] = $in_row['right_one'];
				}
			return $out_arr;			
			}

	function set_click_td ($rcd_id, $cell_id, $the_str) {
		$disabled = ( ( sp_is_guest() ) || ( sp_is_member() ) );							// disallow update if true			
		$click_str = ($disabled)?  "" : "onclick = \"do_set_time('{$the_str}', {$rcd_id}, '{$cell_id}', 0)\"";

		return "<td id = '{$cell_id}' {$click_str} class='click' {$disabled}><span style = 'margin-left:20px; font-weight:bold;'>Set</span><span style = 'margin-left:50px; font-weight:bold; '><img src = './images/go-left.png'/></span></td></tr>\n";
		}				// end set_click_td()

$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
$query = get_calls_single_sql ($the_id) ;
$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
?>
	<script>
	
    DomReady.ready(function() {
    	var id_array = document.navForm.id_str.value.split(",");		
		var timer = setInterval(function(){getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
		});

//	ex:		 do_set_time ("dispatched", {$the_id}, 0)
	function do_set_time (column, record_id, cell_id, function_id) {		// ajax call to set selected dispatch time
		function the_callback(req) {
			document.getElementById(cell_id).innerHTML=req.responseText;
			CngClass(cell_id, "bright");									// highlight for 2 seconds
			setTimeout ( function() {  CngClass(cell_id, "plain")}, 3000);
			}		// end function the_callback()

		var params = "the_column="+ column + "&record_id=" +record_id + "&function_id=" + function_id;		// 
		var url = "./ajax/set_disp_times.php";
 		sendRequest(url,the_callback, params);		//  issue AJAX request
 		}		// end function do set_time

	function do_ticket (the_id) {
		document.tickForm.ticket_id.value = the_id;
		document.tickForm.submit();
		}		// end function do_ticket ()
	
	function do_resp (the_id) {
		document.respForm.responder_id.value = the_id;
		document.respForm.submit();
		}		// end function do_resp ()
	
	function do_assign (the_id) {
		document.assignForm.assign_id.value = the_id;
		document.assignForm.submit();
		}		// end function do_resp ()
</script>
<?php
if (intval(get_variable('broadcast'))==1) {	
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		

</head>
<body>				<!-- <?php echo __LINE__; ?> -->

<?php
		
	require_once('incs/header.php');	
 	$id_array = explode (",", $_POST['id_str']);
	$link_arr = get_sidelinks ();
	$left_side_str = shorten ($link_arr[0], 16);
	$right_side_str = shorten ($link_arr[1], 16);
		 	
	$larrow = (intval($_POST['id'] == 0))? "" : "&laquo;&nbsp; <span style = 'font-size: 50%;'>{$left_side_str}</span>" ;					// suppress display if at origin
	$rarrow = (intval($_POST['id']) == count ($id_array) -1 )? "" : "<span style = 'font-size: 50%;'>{$right_side_str}</span>&nbsp;&raquo;" ;	// suppress display if at end

?>
		<div style='float:left; '>
			<div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 0px; top:125px; margin-left:10px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
		</div>
		<div style='float:right; '>
			<div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
		</div>
<?php	
	$the_count =  count ($id_array);
	$the_nth = intval($_POST['id']) + 1;
	echo "\n<br /><br /><center><h2>Selected " . get_text("Call") . " (# {$the_nth} of {$the_count} )</h2>\n";	
	$hides = array("assign_id", "ticket_id", "lat", "lng", "protocol");						// hide these columns
	$row = stripslashes_deep(mysql_fetch_array($result)) ;

//	dump($row);
	$div_height = $_SESSION['SP']['scr_height'] - 120;								// allow for nav bars			
	$div_width = floor($_SESSION['SP']['scr_width'] * .6) ;							// allow for nav arrows		
	echo "<div style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'>";	

	echo "<table border=1>\n";
	for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field

		if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?
			$fn = get_text(ucfirst(mysql_field_name($result, $i)));
		
			if ( ! ( empty($row[$i] ) ) ) {				// if (mysql_field_type($result, $i) == "int" && ! ($row[$i]==0))
				echo "<tr valign='baseline'><td>{$fn}:</td>";						// fieldname column only
				switch (mysql_field_name($result, $i)) {		// special handling
				
					case "incident name/type/location":
						echo "<td onclick = \"do_ticket({$row['ticket_id']});\">{$row[$i]}
							<span style = 'margin-left:40px; font-weight:bold; font-size: 1.5em;'><img src = './images/go-right.png'/></span></td></tr>\n";
						break;
					case "severity":
						$the_severity_class = get_severity_class($row[$i]);
						echo "<td>" . get_severity(intval($row[$i])) . " ({$row['protocol']})</td></tr>\n";
						break;
					case "response":					
						echo "<td>" . get_response($row[$i]) . "<img src = './images/go-right.png' style = 'margin-left:10px;'/></td></tr>\n";		// string of handles
						break;
					case "dispatched" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fd{$the_id}", "dispatched" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;
					case "responding" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fr{$the_id}", "responding" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;
					case "on scene" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fs{$the_id}", "on_scene" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;		
					case "enroute to facility" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fe{$the_id}", "u2fenr" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;
					case "arrived at facility" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fa{$the_id}", "u2farr" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;
					case "clear" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fc{$the_id}", "clear" );
							}
						else { echo "<td>$row[$i]</td></tr>\n";	}
						break;

					default:
						echo "<td>$row[$i]</td></tr>\n";		// remaining fields
					}		// end switch ()				
				}				// end not empty
				
			else {				// if empty 
			
				echo "<tr><td>{$fn}:</td>";						// fieldname column only

				switch (mysql_field_name($result, $i)) {		// now do clickable fields
					case "dispatched" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fd{$the_id}", "dispatched" );
							}
						break;
					case "responding" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fr{$the_id}", "responding" );
							}
						break;
					case "on scene" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fs{$the_id}", "on_scene" );
							}
						break;		
					case "enroute to facility" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fe{$the_id}", "u2fenr" );
							}
						break;
					case "arrived at facility" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fa{$the_id}", "u2farr" );
							}
						break;
					case "clear" :
						if (! ( @good_date_time_sp ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fc{$the_id}", "clear" );
							}
						break;
//					case "severity":
//						echo "<td onclick = \"do_ticket({$row['ticket_id']});\">" . get_severity(intval($row[$i])) . " ({$row['protocol']}) </td></tr>";
//						break;

					default:
						echo "<td></td></tr>\n";		// the remaining fields
					}		// end switch ()				
				}		// end empty value				
			}		// end ! in $hides			
		}		// end for ($i...) each row element

//	do_ticket({$row['ticket_id']})
	echo "<tr><td colspan=2 align = 'center'><br /><input type = 'button' value = 'to Edit' onclick = 'do_assign({$row['assign_id']});' /></td></tr>";
	echo "</table></div>\n";
	$id_array = explode (",", $_POST['id_str']);
	}

else {			/*		========	LIST    LIST    LIST    LIST    LIST    LIST    	========	*/

	$query = get_calls_list_sql ($me);				// 9/2/2013
//	dump ($query);
	$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
	
	$sep = $_POST['id_str'] = "";
	while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// to comma sep'd string of id's
	 	$_POST['id_str'] .= $sep . $in_row['id'];
	 	$sep = ",";
	 	}
	@mysql_data_seek($result, 0) ;		// reset for pass 2
	
?>
<script>
    DomReady.ready(function() {

<?php
	@session_start();
	if ( (  intval ( $_SESSION['SP']['do_map'] ) == 1 )  && ( array_key_exists ( "map", $_GET  ) ) ) { 
		$_GET = array() ;		// once only
		echo "\n\t var sp_map = window.open('sp_map.php','_blank');\n";
		}
?>
    	document.getElementById("fc").style.display = "none";
		var timer = setInterval(function(){getLocation(<?php echo $_SESSION['SP']['user_unit_id'];?>)}, (60*1000)) ;		// get position one per minute
		});
</script>
</head>
<body>					<!-- <?php echo __LINE__; ?> -->
<center>
<?php
	require_once('incs/header.php');	
	if ( mysql_num_rows($result) == 0 ) {
		echo "<div style = 'text-align:center; margin-top:100px;'><h2>No current " . get_text("Calls") . "</h2></div>";		
		}
	else {
		$hides = array("severity", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
		$top_row = get_text("Calls") . " - <i>click/tap for details</i>";
//		echo sp_show_list($result, $hides, basename(__FILE__) , $top_row) . "\n" ;			// special handling for 'mine' calls
		echo sp_show_list($result, $hides, "sp_edit_call.php" , $top_row) . "\n" ;			// onClick to edit
		echo "<br/>";			// show bottom rows
		}
	} 		// end else {} =========================	 BOTTOM		====================================================
?>
<?php
require_once('incs/footer.php');	
$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>

<form name = "respForm" method = post 	action = "sp_resp.php?rand=<?php echo time();?>">
<input type = hidden name = "responder_id" 	value = "" />			
</form>

<form name = "tickForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "ticket_id" value = "" />			
</form>

<form name = "assignForm" method = post action = "sp_edit_call.php?rand=<?php echo time();?>">
<input type = hidden name = "assign_id" value = "" />		
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />				<!-- array index of target assign record (the nth) -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>


<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</body>
</html>
