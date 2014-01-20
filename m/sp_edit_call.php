<?php
/*
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);

@session_start();
if ( !array_key_exists('SP', $_SESSION) ) {
	header("Location: index.php");
	}

require_once('../incs/functions.inc.php');
require_once('./incs/sp_functions.inc.php');
$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Edit <?php echo get_text("Calls") . " #" . $the_id;?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<script src="./js/misc.js" type="text/javascript"></script> 
<?php
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

	if ( ! ( array_key_exists ( "update", $_POST ) ) ) {	


		$query = "SELECT * ,
					CONCAT_WS('/',`t`.`scope`, `y`.`type`, CONCAT_WS(' ', `t`.`street`,`t`.`city`)) AS `incident`,	
					`t`.`description`									AS `tick_descr`, 
					`t`.`comments`										AS `tick_comments`, 
					`a`.`comments`										AS `comments`, 
					CONCAT('', `t`.`severity` ) 						AS `severity`,
					`y`.`protocol`,
					`a`.`ticket_id`										AS `ticket_id`,
					SUBSTRING(CAST(`t`.`booked_date` AS CHAR),9,8 ) 	AS `booked`,	
					CONCAT_WS('/', `r`.`handle`, `uy`.`name`, `s`.`status_val`) 	AS `responder`
					FROM `$GLOBALS[mysql_prefix]assigns` `a`
					LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` 	ON (`r`.`id` = `a`.`responder_id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `a`.`ticket_id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` 		ON (`t`.`in_types_id` = `y`.`id`)
					LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `uy` 	ON (`r`.`type` = `uy`.`id`)
					WHERE `a`.`id` = {$the_id} LIMIT 1;";
		
		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		$row_in = stripslashes_deep(mysql_fetch_array($result));
		
?>
	<script>

		function showV(element){ $(element).style.visibility = 'visible';	}
		function hideV(element){ $(element).style.visibility = 'hidden';	}
		
		function dotick(inId) {
			document.tickForm.ticket_id.value = inId;
			document.tickForm.submit();;
			}
		function doresp(inId) {
			document.respForm.responder_id.value = inId;
			document.respForm.submit();;
			}
	</script>
	

<?php
//						set up for generate_date_dropdown() call
		$dispatched = 	(good_date_time($row_in['dispatched']))? 	mysql2timestamp($row_in['dispatched']) : 0;
		$responding = 	(good_date_time($row_in['responding']))? 	mysql2timestamp($row_in['responding']) : 0;
		$on_scene = 	(good_date_time($row_in['on_scene']))? 		mysql2timestamp($row_in['on_scene']) : 0;
		$u2fenr = 		(good_date_time($row_in['u2fenr']))? 		mysql2timestamp($row_in['u2fenr']) : 0;
		$u2farr = 		(good_date_time($row_in['u2farr']))? 		mysql2timestamp($row_in['u2farr']) : 0;
		$clear = 		(good_date_time($row_in['clear']))? 		mysql2timestamp($row_in['clear']) : 0;

	$can_edit = ( sp_is_super() || ( ( sp_is_unit() ) && ( $_SESSION['SP']['user_unit_id'] == $row_in['responder_id'] ) ) ) ;
?>
</head>
<body>			<!-- <?php echo __LINE__;?> -->
<center>
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
	echo "<div id = 'theTable' style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'>\n";	
?>
		<form name = "editForm" method="post" action="<?php echo basename(__FILE__);?>" onsubmit = "alert(164)">
		<input type = "hidden" name = "id" 		value = "<?php echo $_POST['id']; ?>" />	
		<input type = "hidden" name = "id_str" 	value = "<?php echo $_POST['id_str']; ?>" />
		<input type = "hidden" name = 'update' 	value='1' />		<!-- signifies 'do update' -->

		<input type = hidden name = "do_dispatched" value = 0 />
		<input type = hidden name = "do_responding" value = 0 />
		<input type = hidden name = "do_on_scene" value = 0 />
		<input type = hidden name = "do_u2fenr" value = 0 />
		<input type = hidden name = "do_u2farr" value = 0 />
		<input type = hidden name = "do_clear" value = 0 />
	
		<table border="1" align="center" style = 'margin-top:24px;'>
<?php
	if ( $can_edit) {echo "\n\t\t<tr valign='top'><th colspan='2' align='center'>Edit Call Data</th></tr>\n";}
//	dump(__LINE__);
//	dump($row_in);
?>		
		<tr valign="baseline" onclick = "dotick(<?php echo $row_in['ticket_id'];?>);">				<td align="right"><b>Ticket</b>:</td><td>		<?php echo $row_in['incident']; ?><img src = './images/go-right.png'  style = 'margin-left:100px;' /></td></tr>
		<tr valign="baseline" onclick = "doresp(<?php echo $row_in['responder_id'];?>);">			<td align="right"><b>Responder</b>:</td><td>	<?php echo $row_in['responder']; ?><img src = './images/go-right.png' style = 'margin-left:100px;' /></td></tr>
		<tr valign="baseline"><td align="right"><b>Response</b></td><td>							<?php echo get_response($row_in['ticket_id']); ?></td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Description");?>:</td><td>	<?php echo $row_in['tick_descr'];?> </td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Comments");?>:</td><td>		<?php echo $row_in['tick_comments'];?> </td></tr>
		
<?php

	if ( $can_edit) {
?>
		<tr><td colspan = 2 align = center><hr color = blue width= 75%></td></tr>
		<tr valign="baseline"><td align="right">Run comments:</td><td>		<input maxlength="64" size="64" type="text"	value="<?php echo $row_in['comments'];?>" name="comments" /> </td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Dispatched");?>:</td><td>	

<?php 							/*	dispatched	*/
	if (good_date_time($row_in['dispatched'])) {
		echo "\n<script>\n\tdocument.editForm.do_dispatched.value = 1;\n</script>\n";
		echo generate_date_dropdown ("dispatched", mysql2timestamp($row_in['dispatched']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_dispatched.value = 0;\n</script>\n";
		echo "<span id = 'dispatched' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("dispatched", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'dispatched_edit' onclick = 'document.editForm.do_dispatched.value  = 1; showV(\"dispatched\"); hideV(\"dispatched_edit\"); showV(\"dispatched_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'dispatched_undo' onclick = 'document.editForm.do_dispatched.value  = 0; hideV(\"dispatched\"); hideV(\"dispatched_undo\"); showV(\"dispatched_edit\")' style = 'visibility: hidden;' />\n";
		}
?>
			</td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Responding");?>:</td><td>
<?php							/*	responding	*/
	if (good_date_time($row_in['responding'])) {
		echo "\n<script>\n\tdocument.editForm.do_responding.value = 1;</script>\n";
		echo generate_date_dropdown ("responding", mysql2timestamp($row_in['responding']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_responding.value = 0;\n</script>\n";
		echo "<span id = 'responding' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("responding", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'responding_edit' onclick = 'do_responding = 1; showV(\"responding\"); hideV(\"responding_edit\"); showV(\"responding_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'responding_undo' onclick = 'do_responding = 0; hideV(\"responding\"); hideV(\"responding_undo\"); showV(\"responding_edit\")' style = 'visibility: hidden;' />\n";
		}
?>
			</td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("On-scene");?>:</td><td>
<?php							/*	on_scene	*/
	if (good_date_time($row_in['on_scene'])) {
		echo "\n<script>\n\tdocument.editForm.do_on_scene.value = 1;</script>\n";
		echo generate_date_dropdown ("on_scene", mysql2timestamp($row_in['on_scene']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_on_scene.value = 0;\n</script>\n";
		echo "<span id = 'on_scene' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("on_scene", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'on_scene_edit' onclick = 'do_on_scene = 1; showV(\"on_scene\"); hideV(\"on_scene_edit\"); showV(\"on_scene_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'on_scene_undo' onclick = 'do_on_scene = 0; hideV(\"on_scene\"); hideV(\"on_scene_undo\"); showV(\"on_scene_edit\")' style = 'visibility: hidden;' />\n";
		}

?>
			</td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Facility en-route");?>:</td><td>
<?php							/*	u2fenr	*/
	if (good_date_time($row_in['u2fenr'])) {
		echo "\n<script>\n\tdocument.editForm.do_u2fenr.value = 1;</script>\n";
		echo generate_date_dropdown ("u2fenr", mysql2timestamp($row_in['u2fenr']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_u2fenr.value = 0;\n</script>\n";
		echo "<span id = 'u2fenr' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("u2fenr", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'u2fenr_edit' onclick = 'do_u2fenr = 1; showV(\"u2fenr\"); hideV(\"u2fenr_edit\"); showV(\"u2fenr_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'u2fenr_undo' onclick = 'do_u2fenr = 0; hideV(\"u2fenr\"); hideV(\"u2fenr_undo\"); showV(\"u2fenr_edit\")' style = 'visibility: hidden;' />\n";
		}
?>
			</td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Facility arrive");?>:</td><td>		
<?php							/*	u2farr	*/
	if (good_date_time($row_in['u2farr'])) {
		echo "\n<script>\n\tdocument.editForm.do_u2farr.value = 1;</script>\n";
		echo generate_date_dropdown ("u2farr", mysql2timestamp($row_in['u2farr']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_u2farr.value = 0;\n</script>\n";
		echo "<span id = 'u2farr' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("u2farr", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'u2farr_edit' onclick = 'do_u2farr = 1; showV(\"u2farr\"); hideV(\"u2farr_edit\"); showV(\"u2farr_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'u2farr_undo' onclick = 'do_u2farr = 0; hideV(\"u2farr\"); hideV(\"u2farr_undo\"); showV(\"u2farr_edit\")' style = 'visibility: hidden;' />\n";
		}
?>
			</td></tr>
		<tr valign="baseline"><td align="right"><?php echo get_text ("Clear");?>:</td><td>
<?php								/*	clear	*/
/*
	if (good_date_time($row_in['clear'])) {
		echo generate_date_dropdown ("clear", mysql2timestamp($row_in['clear']), 0);
		}
	else {
		echo generate_date_dropdown ("clear", 0, 1);		// default is 'now'
		echo "<input type = 'checkbox' name = 'cb_clear' title = 'Check to allow edit' onchange = 'if (this.checked){do_clear(false); }  else {do_clear(true); } ' style = 'margin-left:40px;'>";
		}
*/
	if (good_date_time($row_in['clear'])) {
		echo "\n<script>\n\tdocument.editForm.do_clear.value = 1;</script>\n";
		echo generate_date_dropdown ("clear", mysql2timestamp($row_in['clear']), 0);
		}
	else {
		echo "\n<script>\n\tdocument.editForm.do_clear.value = 0;\n</script>\n";
		echo "<span id = 'clear' style = 'visibility: hidden;' >";
		echo generate_date_dropdown ("clear", 0);
		echo "</span>\n";
		echo "\t<img src = './images/edit.png' 		id = 'clear_edit' onclick = 'do_clear = 1; showV(\"clear\"); hideV(\"clear_edit\"); showV(\"clear_undo\");' 	style = 'visibility: visible; margin-left:10px;' />\n";
		echo "\t<img src = './images/cancel.png' 	id = 'clear_undo' onclick = 'do_clear = 0; hideV(\"clear\"); hideV(\"clear_undo\"); showV(\"clear_edit\")' style = 'visibility: hidden;' />\n";
		}
?>
			</td></tr>
		<tr valign="baseline"><td align="right">Start miles:</td><td>		<input maxlength=8 size=8 type= "text"	value="<?php echo $row_in['start_miles'];?>"  		name="start_miles" /> 		</td></tr>
		<tr valign="baseline"><td align="right">On scene miles:</td><td>	<input maxlength=8 size=8 type= "text"	value="<?php echo $row_in['on_scene_miles'];?>"  	name="on_scene_miles" /> 	</td></tr>
		<tr valign="baseline"><td align="right">End miles:</td><td>			<input maxlength=8 size=8 type= "text"	value="<?php echo $row_in['end_miles'];?>"  		name="end_miles" /> 		</td></tr>
		<tr valign="baseline"><td align="right">Miles:</td><td>				<input maxlength=8 size=8 type= "text"	value="<?php echo $row_in['miles'];?>"  			name="miles" /> 			</td></tr>
		<tr valign="baseline"><td align="right">As of:</td><td>				<?php echo $row_in['as_of'];?></td></tr>

		<tr><td colspan="2" align="center"><br />
			<input type="button"	value="Cancel" onclick = "javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="reset"		value="Reset" onclick = "document.canForm.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" style = 'width:200px;' name="sub_but" value="Next" onclick=" this.disabled=true; document.editForm.submit();"/> 		
			</td></tr>

<?php
		}		//end if ($can_edit) 
	else {		
?>
		<tr valign="baseline"><td align="right">Run comments:</td><td>			<?php echo $row_in['comments'];?></td></tr>
		<tr valign="baseline"><td align="right">Dispatched:</td><td>			<?php if (good_date_time($row_in['dispatched']))	{ echo $row_in['dispatched'];}?>	</td></tr>
		<tr valign="baseline"><td align="right">Responding:</td><td>			<?php if (good_date_time($row_in['responding']))	{ echo $row_in['responding'];}?>	</td></tr>
		<tr valign="baseline"><td align="right">On scene:</td><td>				<?php if (good_date_time($row_in['on_scene']))		{ echo $row_in['on_scene'];}?>		</td></tr>
		<tr valign="baseline"><td align="right">En-route to Facility:</td><td>	<?php if (good_date_time($row_in['u2fenr']))		{ echo $row_in['u2fenr'];}?>		</td></tr>
		<tr valign="baseline"><td align="right">Arrived at Facility:</td><td>	<?php if (good_date_time($row_in['u2farr']))		{ echo $row_in['u2farr'];}?>		</td></tr>	
		<tr valign="baseline"><td align="right">Clear:</td><td>					<?php if (good_date_time($row_in['clear'])) 		{ echo $row_in['clear'];}?>			</td></tr>
		<tr valign="baseline"><td align="right">Start miles:</td><td>			<?php echo $row_in['start_miles'];?>  	</td></tr>
		<tr valign="baseline"><td align="right">On scene miles:</td><td>		<?php echo $row_in['on_scene_miles'];?>	</td></tr>
		<tr valign="baseline"><td align="right">End miles:</td><td>				<?php echo $row_in['end_miles'];?>  	</td></tr>
		<tr valign="baseline"><td align="right">Miles:</td><td>					<?php echo $row_in['miles'];?>  		</td></tr>
		<tr valign="baseline"><td align="right">As of:</td><td>					<?php echo $row_in['as_of'];?>			</td></tr>


<?php
	}
?>
		</table>
		

		</form>
		</div>	<!-- /theTable -->
		
<?php
	}		// end - if ! (array_key_exists ( "update" ... ))
//				=================================================
else {		// do the update

			function int_or_null ($arg1) {
				return (strlen(trim($arg1)) > 0) ? trim($arg1) : "NULL";		// for MySQL usage
				}			

//			function date_sql($str) { 
//				return "$_POST['frm_year_{$str}']-$_POST['frm_month_{$str}']-$_POST['frm_day_{$str}'] $_POST['frm_hour_{$str}']:$_POST['frm_minute_{$str}']:00";
//				}
				
			$now = now_ts();
			$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
				`start_miles`= " . 		int_or_null($_POST['start_miles']) .", 		
				`on_scene_miles`= " . 	int_or_null($_POST['on_scene_miles']) .", 	
				`end_miles`= " . 		int_or_null($_POST['end_miles']) .", 		
				`miles`= " . 			int_or_null($_POST['miles']) . ", 
				`as_of` = '{$now}',
				`user_id` = {$_SESSION['SP']['user_id']}";

			if ($_POST['do_dispatched'] == 1 ) {
				$frm_dispatched = "{$_POST['frm_year_dispatched']}-{$_POST['frm_month_dispatched']}-{$_POST['frm_day_dispatched']} {$_POST['frm_hour_dispatched']}:{$_POST['frm_minute_dispatched']}:00";
				$query .= ",\n `dispatched`= " . quote_smart($frm_dispatched) ; 
				}
			if ($_POST['do_responding'] == 1 ) {
				$frm_responding = "{$_POST['frm_year_responding']}-{$_POST['frm_month_responding']}-{$_POST['frm_day_responding']} {$_POST['frm_hour_responding']}:{$_POST['frm_minute_responding']}:00";
				$query .= ",\n `responding`= " . quote_smart($frm_responding) ; 
				}
			if ($_POST['do_on_scene'] == 1 ) {
				$frm_on_scene = "$_POST[frm_year_on_scene]-$_POST[frm_month_on_scene]-$_POST[frm_day_on_scene] $_POST[frm_hour_on_scene]:$_POST[frm_minute_on_scene]:00";
				$query .= ",\n `on_scene`= " . quote_smart($frm_on_scene) ; 
				}
			if ($_POST['do_u2fenr'] == 1 ) {
				$frm_u2fenr = "$_POST[frm_year_u2fenr]-$_POST[frm_month_u2fenr]-$_POST[frm_day_u2fenr] $_POST[frm_hour_u2fenr]:$_POST[frm_minute_u2fenr]:00";
				$query .= ",\n `u2fenr`= " . quote_smart($frm_u2fenr) ; 
				}
			if ($_POST['do_u2farr'] == 1 ) {
				$frm_u2farr = "$_POST[frm_year_u2farr]-$_POST[frm_month_u2farr]-$_POST[frm_day_u2farr] $_POST[frm_hour_u2farr]:$_POST[frm_minute_u2farr]:00";
				$query .= ",\n `u2farr`= " . quote_smart($frm_u2farr) ; 
				}
			if ($_POST['do_u2farr'] == 1 ) {
				$frm_clear = "$_POST[frm_year_clear]-$_POST[frm_month_clear]-$_POST[frm_day_clear] $_POST[frm_hour_clear]:$_POST[frm_minute_clear]:00";
				$query .= ",\n `clear`= " . quote_smart($frm_clear) ; 
				}
			
			$query .= "\n WHERE `id`={$the_id}";

			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
/*
			$query = "SELECT `ticket_id`, `responder_id` FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id`={$the_id}";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
			$row = mysql_fetch_assoc($result);
			
			if ($_POST['document.editForm.do_dispatched.value '] == 1 ) 	{ do_log($GLOBALS['LOG_CALL_DISP'], 	$row['ticket_id'], $row['responder_id'], $the_id);
			if ($_POST['do_responding'] == 1 ) 	{ do_log($GLOBALS['LOG_CALL_RESP'], 	$row['ticket_id'], $row['responder_id'], $the_id);
			if ($_POST['do_on_scene'] == 1 ) 	{ do_log($GLOBALS['LOG_CALL_ONSCN'],	$row['ticket_id'], $row['responder_id'], $the_id);
			if ($_POST['do_u2fenr'] == 1 ) 		{ do_log($GLOBALS['LOG_CALL_U2FENR'],	$row['ticket_id'], $row['responder_id'], $the_id);
			if ($_POST['do_u2farr'] == 1 ) 		{ do_log($GLOBALS['LOG_CALL_U2FARR'],	$row['ticket_id'], $row['responder_id'], $the_id);
			if ($_POST['do_clear'] == 1 ) 		{ do_log($GLOBALS['LOG_CALL_CLR'],		$row['ticket_id'], $row['responder_id'], $the_id);
*/
?>
<script src = "./js/jquery.min.js"></script>
</head>			<!-- <?php echo __LINE__;?> -->
<body onload = '$( "#complete" ).fadeOut( 3000, function() {document.navForm.submit()});'>
<center><h1 id = "complete" style = 'margin-top:100px;'>Update applied</h1>

<?php
	}				// end else {}	- 	
?>
<form name = "respForm" method = post 	action = "sp_resp.php?rand=<?php echo time();?>">
<input type = hidden name = "responder_id" 	value = "" />			
</form>

<form name = "tickForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "ticket_id" value = "" />			
</form>


<form name = "canForm" method = post 	action = "<?php echo basename(__FILE__); ?>">		<!-- a psuedo-reset -->
<input type = hidden name = "id" 		value = "<?php echo $_POST['id']; ?>" />	
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str']; ?>" />
</form>

<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__); ?>">
<input type = hidden name = "id" 		value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>

<center>
<?php
		unset($result);
//		====================	BOTTOM	====================

	require_once('incs/footer.php');	
?>
</body>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</html>