<?php

/*
11/18/2013 initial release
dump($_POST);
dump($_POST);
*/
if ( !defined ( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');
require_once('incs/sp_functions.inc.php');
@session_start();
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP Logs</title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src = "./js/misc.js" type="text/javascript"></script> 
	<script src = "./js/jquery.min.js"></script>

<SCRIPT>

DomReady.ready(function() {			//
	$('listId').style.display = 'block';
//	$("#complete" ).fadeOut( 4000, function() {alert(41)});	
	setTimeout(function(){$('complete').style.display = 'none'},3000);
	});		

function showForm(){
	document.getElementById('formId').style.display = 'block';
	document.getElementById('listId').style.display = 'none';
	document.getElementById('formButton').style.display = 'none';
	document.getElementById('formId').style.display = 'block';
	document.log_form.frm_comment.focus();
	}

function showList(){
	document.getElementById('formId').style.display = 'none';
	document.getElementById('formButton').style.display = 'block';
	document.getElementById('listId').style.display = 'block';
	}
<?php
if (array_key_exists ( "update", $_POST ) ) { 		// pass # 2
	sp_do_log($GLOBALS['LOG_COMMENT'], $ticket_id=0, $responder_id=0, strip_tags(trim($_POST['frm_comment'])), $_SESSION['SP']['user_id']  );	
	}		
?>
</script>
</head>
<body>		<!-- <?php echo __LINE__ ; ?> -->
<center>
<?php		
	require_once('incs/header.php');	

	if (array_key_exists ( "update", $_POST ) ) { 		// announce update
?>
<div id = 'complete' style = 'margin-top:100px;'>
<H2>Log entry posted</H2>
</div>
<?php
	}		// end if update
?>

<input id = 'formButton' type = 'button' value = 'Add log entry' onclick = 'showForm();' style = 'position: fixed; top:100px;right:200px' />

<br /><br />

<div id='formId' style = 'margin-top:100px; display:none'>
<form name="log_form" method = "post" action="<?php print basename(__FILE__); ?>">
<input type = 'hidden' NAME = "update" 		value=1 />
<input type = 'hidden' name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = 'hidden' name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<table>
<tr class = 'even' ><th colspan=2>Add log entry</TH></TR>
<tr class = 'odd'><td><textarea name="frm_comment" cols="64" rows="1" wrap="virtual" placeholder="Enter yr stuff here ..."></textarea></td></tr>
<tr class = 'even'><td colspan=2 align='center'>
	<input type = 'button' value='Cancel' 	onClick="showList();" />
	<input type = 'button' value='Reset' 	onClick="document.log_form.reset()" style = 'margin-left:20px;' />
	<input type = 'button' value='Next' 	onClick="document.log_form.submit()"  style = 'margin-left:20px;'/>
	</td></tr></table>
</form>
</div>

<?php
		require_once('../incs/log_codes.inc.php');				// returns $types array - 4/19/11
		$ini_array = parse_ini_file("./incs/sp.ini");
		if ( ! array_key_exists ( "log_age_show", $ini_array ) ) { $interval = 48; }
		
		else { $interval = intval ( $ini_array['log_age_show'] ) ; }	
		$query = "
			SELECT *, 
			`u`.`user` AS `thename`, 
			`r`.`handle`, 
			`t`.`street` AS `tickname`,
			`$GLOBALS[mysql_prefix]log`.`info` AS `loginfo` 
			FROM `$GLOBALS[mysql_prefix]log`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 		ON (`$GLOBALS[mysql_prefix]log`.`who`= `u`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]log`.`responder_id` = `r`.`id`)
			LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]log`.`ticket_id` = `t`.`id`)
			WHERE `code` IN (
			{$GLOBALS['LOG_INCIDENT_OPEN']}, 
			{$GLOBALS['LOG_INCIDENT_CLOSE']}, 
			{$GLOBALS['LOG_INCIDENT_CHANGE']}, 
			{$GLOBALS['LOG_ACTION_ADD']}, 
			{$GLOBALS['LOG_PATIENT_ADD']}, 
			{$GLOBALS['LOG_INCIDENT_DELETE']}, 
			{$GLOBALS['LOG_ACTION_DELETE']}, 
			{$GLOBALS['LOG_PATIENT_DELETE']}, 
			{$GLOBALS['LOG_UNIT_STATUS']}, 
			{$GLOBALS['LOG_UNIT_COMPLETE']}, 
			{$GLOBALS['LOG_UNIT_CHANGE']}, 
			{$GLOBALS['LOG_UNIT_TO_QUARTERS']}, 
			{$GLOBALS['LOG_CALL_EDIT']}, 
			{$GLOBALS['LOG_CALL_DISP']}, 
			{$GLOBALS['LOG_CALL_RESP']}, 
			{$GLOBALS['LOG_CALL_ONSCN']}, 
			{$GLOBALS['LOG_CALL_CLR']}, 
			{$GLOBALS['LOG_CALL_RESET']}, 
			{$GLOBALS['LOG_CALL_REC_FAC_SET']}, 
			{$GLOBALS['LOG_CALL_REC_FAC_CHANGE']}, 
			{$GLOBALS['LOG_CALL_REC_FAC_UNSET']}, 
			{$GLOBALS['LOG_CALL_REC_FAC_CLEAR']}, 
			{$GLOBALS['LOG_CALL_U2FENR']}, 
			{$GLOBALS['LOG_CALL_U2FARR']},
			{$GLOBALS['LOG_COMMENT']}
			)
			AND `when` > ( NOW() - INTERVAL {$interval} HOUR )
			ORDER BY `$GLOBALS[mysql_prefix]log`.`when` DESC
			;";
		$result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
		$i = 1;
?>		
<div id = 'listId' style = 'margin-top:20px; display:block;'>
<table border=1>
<tr><th colspan=5>Log as of <?php echo substr(now_ts(), 0, 16) ;?><br/><br/></th></tr>
<?php
		$curr_date_time_str = $curr_date_str = "";
		while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){			// main loop - top
				if (empty($row['tickname'])) {
					$the_ticket = ($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";
					}
				else {
					$the_ticket =$row['tickname'] ;
					}

				if ($curr_date_str == substr($row['when'], 5, 5) ) {
					$date_time_str = substr($row['when'], 11, 5);
					}
				else {
					$curr_date_str = substr($row['when'], 5, 5);				
					$date_time_str = substr($row['when'], 5, 11);
					}

				echo  "<TR><TD ALIGN = 'right'>{$date_time_str}</TD>";
				echo  "<TD>{$types[$row['code']]}</TD>";
				if ($row['code'] == $GLOBALS['LOG_COMMENT']) {
					echo  "<TD COLSPAN=2>{$row['loginfo']}</TD>";			// 1/21/09
					}
				else {
					echo  "<TD>{$row['handle']}</TD>";			// 5/29/12
					echo  "<TD>{$the_ticket}</TD>";
					}
				echo  "<TD>{$row['user']}</TD>";
				echo  "</TR>\n";
				$i++;
			}		// end while($row = ...)
?>			
<tr><td colspan=99 align='center'>
	<br /><b>End of Log Report</b><br /><br /></td></tr>
</table>
</div>
<?php

require_once('incs/footer.php');	

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_TICKET'];?>" />
</form>
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