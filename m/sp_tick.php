<?php
/*
Calls module
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');		// 7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 

@session_start();
if (empty($_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['user_unit_id'] ;				// possibly empty
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP <?php echo get_text("Incidents");?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 

<?php
	if ( ( isset($_POST['ticket_id'] ) && ( intval ( $_POST['ticket_id'] ) > 0 ) ) ) {
		$query = "SELECT `id`, `status` FROM `$GLOBALS[mysql_prefix]ticket` `t` 
			WHERE 	( ( `t`.`status`='{$GLOBALS['STATUS_OPEN']}' )  
			OR 		  ( `t`.`status`='{$GLOBALS['STATUS_SCHEDULED']}' ) )
			ORDER BY `severity` DESC, `problemstart` ASC";
		$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);			
		
		$sep = $_POST['id_str'] = "";
		$ctr = 0;
		while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// to comma sep'd string of id's
		 	$_POST['id_str'] .= $sep . $in_row['id'];
		 	if ($in_row['id'] == $_POST['ticket_id']) {$_POST['id'] = $ctr; }	// this one
		 	$ctr++;																// keep on keepin' on
		 	$sep = ",";
		 	}				// end while ($in_row ... )	
		}				// end if isset ($_POST['ticket_id'])

	if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 ) ) {		// ====================================================
																		/*	show the one record	 */
		$id_array = explode (",", $_POST['id_str']);
		$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
?>
			<script>
		    DomReady.ready(function() {
				var timer = setInterval(function(){getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
		    	var id_array = document.navForm.id_str.value.split(",");		
				});
		
//			do_set_time("dispatched", {$the_id}, 0)
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
		echo "\n<br/><br/><center><h2>Selected " . get_text("Incident") . " (of {$the_count} )</h2>\n";
		
		$div_height = $_SESSION['scr_height'] - 200;								// nav bars			
		$div_width = floor($_SESSION['scr_width'] * .6) ;							// allow for nav arrows		
		echo "\n<div id = 'inner_div' style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'>";	
				
		echo "<table id = 'inner' border=1>\n";

		$query = "SELECT 
			CONCAT_WS(' / ',`scope`,`y`.`type`) 	AS `incident/type`,
			CONCAT('*', `severity` ) 					AS `severity` , 
			CONCAT_WS(' ',`street`,`city`, `state`) 	AS `location`,
			`y`.`protocol`								AS `response protocol`, 
			`t`.`id`  									AS `responding`,
			`t`.`description`							AS `synopsis` ,
			`t`.`comments`								AS `disposition` ,
			`t`.`problemstart`							AS `start_str` ,
			`status`, 
			`t`.`scope`									AS `close incident` ,
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

		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		$hides = array("tick_id", "lat", "lng", "start_str");						// hide these columns
		$row = stripslashes_deep(mysql_fetch_array($result, MYSQL_BOTH)) ;
		for ($i=0; $i< mysql_num_fields($result); $i++) {						// each field
			if (!(substr(mysql_field_name($result, $i ), 0, 1) == "_")) {  		// meta-data?

				if ( ( !empty($row[$i] ) ) && ( !in_array(mysql_field_name($result, $i), $hides ) ) )  {			
					$fn = get_text ( ucfirst(mysql_field_name($result, $i ) ) );
//					echo "<tr><td>{$fn}:</td>";

					switch (mysql_field_name($result, $i) ) {
						case "severity":
							$the_severity_class = get_severity_class($row[$i]);
							echo "<tr><td>{$fn}:</td><td class = '{$the_severity_class}'>" . get_severity($row[$i]) . "</td></tr>\n";		// get_status_str
							break;

						case "responding":					
							$temp = get_response($row[$i]);
							if ( ! ( empty ( $temp ) ) ) {
								echo "<tr><td>{$fn}:</td><td>{$temp}</td></tr>\n";		// string of handles
								}
							break;							

						case "status":
							$the_status = get_status_str($row[$i]);
							$the_diff = my_date_diff ( $row["start_str"], mysql_format_date(now())) ;
							echo "<tr><td>{$fn}:</td><td>{$the_status} ({$the_diff})</td></tr>\n";		
							break;

						case "as of":	
						case "start_str":	
							$datestr = format_date(strval(strtotime($row[$i])));
							echo "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
							break;

						case "synopsis":
						case "disposition":
							$the_rand_str = "?rand=" . time();
							$the_onclick_str = 		"document.navForm.action = \"sp_add_note.php?{$the_rand_str}\";";
							$the_onclick_str .= 	"document.navForm.ticket.value={$the_id};";
							$the_onclick_str .= 	"document.navForm.submit();";
							echo "<tr onclick = '{$the_onclick_str}'><td>{$fn}:</td>
								<td><span>{$row[$i]}</span>
									<span style = 'margin-left:20px; font-weight:bold;'><img src = './images/go-next.png'/></span></td>
								</tr>\n";		
							break;

						case "close incidentZZZ":
							$the_rand_str = "?rand=" . time();
							$the_onclick_str = 		"document.navForm.action = \"sp_close_in.php?{$the_rand_str}\";";
							$the_onclick_str .= 	"document.navForm.ticket.value={$the_id};";
							$the_onclick_str .= 	"document.navForm.submit();";
							echo "<tr onclick = '{$the_onclick_str}'><td>{$fn}:</td>
								<td><span>{$row[$i]}</span>
									<span style = 'margin-left:20px; font-weight:bold;'><img src = './images/go-next.png'/></span></td>
								</tr>\n";		
							break;
						case "actions":
						case "patients":
							$the_status = get_status_str($row[$i]);
							$the_rand_str = "?rand=" . time();
							$the_onclick_str = 		"document.navForm.action = \"sp_act.php{$the_rand_str}\";";
							$the_onclick_str .= 	"document.navForm.submit();";
							echo "<tr onclick = '{$the_onclick_str}'><td>{$fn}:</td>
								<td><span>{$row[$i]}</span>
									<span style = 'margin-left:20px; font-weight:bold;'><img src = './images/go-next.png'/></span></td>
								</tr>\n";		
							break;

						default: 
							echo "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
						};				// end switch ()
						
					}			
				}				// end meta-data?
			}		// end for ($i...) each row element
		echo "</table></div>\n";
		
		$id_array = explode (",", $_POST['id_str']);
		}
//	==============================================	MIDDLE	=======================================================		
else {								// list
		
	$query = "SELECT `t`.`id`,
			CONCAT_WS(' ',`street`,`city`) AS `addr`,
			 `status`, `lat`, `lng`, `scope` , `t`.`description` AS `description` ,
			 `severity` , `comments` ,
			(SELECT COUNT( * )
				FROM `$GLOBALS[mysql_prefix]assigns`
				WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`
				AND `clear` IS NULL
				OR DATE_FORMAT( `clear` , '%y' ) = '00')
				) AS `u`, (
				SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]action`
					WHERE `$GLOBALS[mysql_prefix]action`.`ticket_id` = `t`.`id`
					) AS `a`,(						
				SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]patient`
					WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id`
					) AS `p`,
			SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `as of`				
			FROM `$GLOBALS[mysql_prefix]ticket` `t` 
			WHERE 	( (`t`.`status`='{$GLOBALS['STATUS_OPEN']}')  
			OR 		(`t`.`status`='{$GLOBALS['STATUS_SCHEDULED']}') )
			ORDER BY `severity` DESC, `problemstart` ASC";
		
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
		    	document.getElementById("fi").style.display = "none";
				var timer = setInterval(function(){getLocation(<?php echo $_SESSION['user_unit_id'];?>)}, (60*1000)) ;		// get position one per minute
				});
		</script>	
		</head>
		<body><center>
<?php
			require_once('incs/header.php');	
			
			if ( mysql_num_rows($result) == 0 ) {
				echo "<div id = 'list' style = 'text-align:center; margin-top:100px;'>h2>No current " . get_text("Incidents") . "</h2></div>\n";		
				}
			else {					
				$hides = array("the_group", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
				$top_row = get_text("Incidents") . " - <i>click for details</i>";
				echo "<br/>\n<div id = 'list' >\n";
				echo sp_show_list($result, $hides, basename(__FILE__) , $top_row) . "\n" ;	
				echo "<br/><br/>";			// show bottom rows
				}
		
			} 		// end else {}


	require_once('incs/footer.php');	
	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
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
