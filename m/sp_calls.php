<?php
//$me = 6;		// responder id
/*
Calls module
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');		//7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 

@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['user_unit_id'] ;		// possibly empty
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title><?php echo get_text("Calls");?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 

<?php
//		================================	TOP		=====================================================
	if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 )  ) {		/* show detail */

		function set_click_td ($rcd_id, $cell_id, $the_str) {
			$click_str = "onclick = \"do_set_time('{$the_str}', {$rcd_id}, '{$cell_id}', 0)\"";
			return "<td id = '{$cell_id}' {$click_str} class='click'><span style = 'margin-left:20px; font-weight:bold;'>Set</span><span style = 'margin-left:50px; font-weight:bold; '><img src = './images/go-next.png'/></span></td></tr>\n";
			}				// end set_click_td()

$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id

$query_core = " 
	CONCAT_WS('/',`t`.`scope`, `y`.`type`, CONCAT_WS(' ', `t`.`street`,`t`.`city`)) AS `incident name/type/location`,	
	CONCAT('', `t`.`severity` ) 					AS `severity`,
	`y`.`protocol`,
	CONCAT_WS('/', `r`.`handle`, `uy`.`name`, `s`.`status_val`) 	AS `unit handle/type/status`,
	`t`.`description`,
	`t`.`comments` 									AS `ticket comments`,
	`t`.`id` 										AS `ticket_id`,
	`a`.`ticket_id` 								AS `response`,
	SUBSTRING(CAST(`t`.`booked_date` AS CHAR),9,8 ) AS `booked for`,	
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

$query = "SELECT 	 {$query_core} 
	WHERE (`a`.`id` = {$the_id}
	AND	(`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ))
	LIMIT 1";
/*
dump(__LINE__);
dump(__LINE__);
dump(__LINE__);
dump(__LINE__);
dump($query);
*/
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
	
</script>
<?php
if (intval(get_variable('broadcast'))==1) {	
	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
</head>
<body>

<?php
		
	require_once('incs/header.php');	
 	$id_array = explode (",", $_POST['id_str']);
 	$larrow = (intval($_POST['id'] == 0))? "" : "&laquo;&nbsp;" ;	// suppress display if at origin
	$rarrow = (intval($_POST['id']) == count ($id_array) -1 )? "" : "&nbsp;&raquo;" ;	// suppress display if at end
?>
		<div style='float:left; '>
			<div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 50px; top:125px; margin-left:100px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
		</div>
		<div style='float:right; '>
			<div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
		</div>
<?php	
	$the_count =  count ($id_array);
	echo "\n<br /><br /><center><h2>Selected " . get_text("Call") . " (of {$the_count} )</h2>\n";
	
	$hides = array("ticket_id", "lat", "lng", "protocol");						// hide these columns

	$row = stripslashes_deep(mysql_fetch_array($result)) ;

	$div_height = $_SESSION['scr_height'] - 120;								// allow for nav bars			
	$div_width = floor($_SESSION['scr_width'] * .6) ;							// allow for nav arrows		
	echo "<div style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'>";	

	echo "<table border=1>\n";
	for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
		if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?
			$fn = get_text(ucfirst(mysql_field_name($result, $i)));
		
			if ( ! ( empty($row[$i ]) ) ) {				// if (mysql_field_type($result, $i) == "int" && ! ($row[$i]==0))

				echo "<tr valign='baseline'><td>{$fn}:</td>";						// fieldname column only
				switch (mysql_field_name($result, $i)) {		// special handling
				
					case "incident name/type/location":
						echo "<td onclick = \"do_ticket({$row['ticket_id']});\">{$row[$i]}
							<span style = 'margin-left:40px; font-weight:bold; font-size: 1.5em;'><img src = './images/go-next.png'/></span></td></tr>\n";
						break;

					case "severity":
						$the_severity_class = get_severity_class($row[$i]);
						echo "<td>" . get_severity(intval($row[$i])) . " ({$row['protocol']})</td></tr>\n";
						break;

					case "response":					
						echo "<td>" . get_response($row[$i]) . "</td></tr>\n";		// string of handles
						break;
						
					default:
						echo "<td>$row[$i]</td></tr>\n";		// remaining fields
					}		// end switch ()				
				}				// end not empty
				
			else {				// if empty 
			
				echo "<tr><td>{$fn}:</td>";						// fieldname column only
				switch (mysql_field_name($result, $i)) {		// now do clickable fields
					case "dispatched" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fd{$the_id}", "dispatched" );
							}
						break;
					case "responding" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fr{$the_id}", "responding" );
							}
						break;
					case "on scene" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fs{$the_id}", "on_scene" );
							}
						break;		
					case "enroute to facility" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fe{$the_id}", "u2fenr" );
							}
						break;
					case "arrived at facility" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fa{$the_id}", "u2farr" );
							}
						break;
					case "clear" :
						if (! ( @good_date_time ($row[$i] ) ) ) {
							echo set_click_td ($the_id, "fc{$the_id}", "clear" );
							}
						break;
					case "severity":
						echo "<td onclick = \"do_ticket({$row['ticket_id']});\">" . get_severity(intval($row[$i])) . " ({$row['protocol']}) </td></tr>";
						break;

					default:
						echo "<td></td></tr>\n";		// the remaining fields
					}		// end switch ()				
				}		// end empty value				
			}		// end ! in $hides			
		}		// end for ($i...) each row element

	echo "</table></div>\n";
	
	$id_array = explode (",", $_POST['id_str']);
	}
//		=========================================	MIDDLE		===================================================	
else {								/*  list    list    list    list    list    list    list    list    list    list    list  */

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
	SUBSTRING(CAST(`a`.`as_of` AS CHAR),9,8 ) AS `as of`,
	`a`.`as_of`
	FROM `$GLOBALS[mysql_prefix]assigns` `a`
	LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`r`.`id` = `a`.`responder_id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `a`.`ticket_id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)";

$query = "
	SELECT `a`.`id`, 1 AS `mine`, {$query_core} WHERE (`a`.`responder_id` =   {$me} AND  (`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ))
	UNION
	SELECT `a`.`id`, 0 AS `mine`, {$query_core} WHERE (`a`.`responder_id` <>  {$me} AND  (`a`.`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'))
	ORDER BY `mine` DESC, `severity` DESC, `as_of` ASC
	";

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
    	document.getElementById("fc").style.display = "none";
		var timer = setInterval(function(){getLocation(<?php echo $_SESSION['user_unit_id'];?>)}, (60*1000)) ;		// get position one per minute
		});
</script>
</head>
<body><center>
<?php
	require_once('incs/header.php');	
	if ( mysql_num_rows($result) == 0 ) {
		echo "<div style = 'text-align:center; margin-top:100px;'><h2>No current " . get_text("Calls") . "</h2></div>";		
		}
	else {
//		echo "<div style = 'text-align:center; margin-top:60px;'><h2>Current " . get_text("Calls") . " - <i>click for details</i></h2></div>\n";	
		$hides = array("severity", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
		$top_row = get_text("Calls") . " - <i>click for details</i>";
		echo sp_show_list($result, $hides, basename(__FILE__) , $top_row) . "\n" ;			// special handling for 'mine' calls
		echo "<br/><br/>";			// show bottom rows
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

<form name = "respForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "resp_id" 	value = "" />			
</form>

<form name = "tickForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "ticket_id" 	value = "" />			
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
