<?php
/*
Close incident
3/31/2013 initial release
11/17/2013 significant code clean-up
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');		// 7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 

// dump ($_POST);
@session_start();
if (! array_key_exists('SP', $_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['SP']['user_unit_id'] ;			// possibly empty
$disposition = get_text("Disposition");			// 12/1/10
$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id

//dump($_POST);
//dump($the_id);
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP Close Incident</title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 
	<script src = "./js/jquery.min.js"></script>
</HEAD>
<?php

if (!array_key_exists ( "update", $_POST ) ) { 		// pass # 1
?>
<!-- <?php echo __LINE__ ;?> -->
<BODY onLoad =  "if(document.frm_note.frm_text) {document.frm_note.frm_text.focus() ;}"><!-- <?php echo __LINE__;?> -->
<CENTER>
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($the_id)  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			do_is_closed();
			}
		else {
			do_is_start($row);
			}
		}		// end if (empty($_POST))		
		
else {			// not "update", $_POST then is finished
?>
<body onload = '$( "#complete" ).fadeOut( 3000, function() {document.tickForm.submit()});'>	<!-- <?php echo __LINE__ ;?> -->
<center>
<?php
		$scope = do_is_finished();		// 2/15/10
?>
<H1 id = 'complete' style = 'margin-top:100px;'>Call '<SPAN style = 'background-color:#DEE3E7'><?php print $scope; ?></SPAN>' closed</H1>

<BR /><BR />	<!-- 2/15/10 -->
<?php
	}		// end if/else if (!array_key_exists ( "update", $_POST ) ) 

function do_is_closed() {
	global $row;
?>		
<CENTER>
	<H3>Call '<?php print $row['scope'];?>' is already closed</H3><BR /><BR />
	</BODY><!-- <?php echo __LINE__;?> -->
	</HTML>
<?php		
	}				// end function do_is_closed()
	
function do_is_start($in_row) {				// 3/22/10
	global $disposition, $the_id;
?>
<SCRIPT>
	function validate() {
		var errstr = "";
		if(document.frm_note.frm_disp.value.trim().length == 0) { errstr += "<?php echo $disposition;?> is required\n"}; 
		if ( (  parseInt(document.frm_note.frm_hour_problemend.value) > 24 ) ||
			( parseInt(document.frm_note.frm_minute_problemend.value) > 60 ) )	errstr += "Date correction required";

		if (errstr != "")	{ alert(errstr); return false; }
		else				{ document.frm_note.submit();}
		}		// end function validate()
		
	function set_signal(inval) {				// 12/18/10
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.frm_note.frm_synopsis.value+=" " + temp_ary[1] + ' ';		
		document.frm_note.frm_synopsis.focus();		
		}		// end function set_signal()

	function set_signal2(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.frm_note.frm_disp.value+=" " + temp_ary[1] + ' ';		
		document.frm_note.frm_disp.focus();		
		}		// end function set_signal()
</SCRIPT>
	<H2>Enter Incident Close Information</H2>
<?php
	$short_descr = substr($in_row['scope'], 0, 20);
	$sep = (empty($in_row['tick_street']))? " " : ", ";
	$short_addr =  substr("{$in_row['street']}{$sep}{$in_row['city']} {$in_row['state']}" , 0, 32);
	print (empty($in_row['comments']))? "" : "<TR><TD></TD><TD>{$in_row['comments']}</TD></TR>";
	$capt = ( empty ( $in_row['description'] ) ) ? "Synopsis" : "Add'l";
?>		
	<H2>( <?php print "{$short_descr} - {$short_addr}"?> ...)</H2>
	<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	<TABLE ALIGN = 'center' CELLPADDING = 2 CELLSPACING = 0>	
	<TR><TD CLASS='td_label'  ALIGN='right'><b>Run End</b>:&nbsp;</TD>
		<TD><?php print generate_date_dropdown('problemend',0, FALSE) . ""; ?></TD></TR>		
	<TR><TD ALIGN='right' CLASS='td_label' ><b><?php print $capt;?></b>:&nbsp;</TD>
		<TD><TEXTAREA NAME='frm_synopsis' COLS=56 ROWS = 1></TEXTAREA></TD></TR>			
	<TR VALIGN = 'TOP'>		<!-- 11/15/10 -->
		<TD></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t\t\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
			
			
	<TR><TD ALIGN='right' CLASS='td_label' ><b><?php print $disposition;?></b>:&nbsp;</TD>
		<TD><TEXTAREA NAME='frm_disp' COLS=56 ROWS = 1><?php print $in_row['comments'];?></TEXTAREA>
			</TD></TR>
		<TR VALIGN = 'TOP'>		<!-- 11/15/10 -->
			<TD></TD><TD CLASS="td_label">Signal &raquo; 

				<SELECT NAME='signals2' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED>Select</OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t\t\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>				
<?php										// 

		$query = "SELECT *,
		UNIX_TIMESTAMP(as_of) AS as_of,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`,
		`t`.`scope` AS `theticket`,
		`t`.`description` AS `thetickdescr`,
		`t`.`status` AS `thestatus`,
		`t`.`_by` AS `call_taker`,
		`t`.`street` AS `tick_street`,
		`t`.`city` AS `tick_city`,
		`t`.`state` AS `tick_state`,
		`r`.`id` AS `theunitid`,
		`r`.`name` AS `theunit` ,
		`f`.`name` AS `thefacility`,
		`g`.`name` AS `the_rec_facility`,
		`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`$GLOBALS[mysql_prefix]assigns`.`facility_id` = `f`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `g` ON (`$GLOBALS[mysql_prefix]assigns`.`rec_facility_id` = `g`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status`  `s` ON ( `r`.`un_status_id` = s.id ) 
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = {$the_id} GROUP BY `r`.`id`";

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if ( intval (mysql_affected_rows() ) >0 ) {
			$clear_capt = "Clear: ";
			while ( $asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result))){
				print "<TR VALIGN = 'baseline'><TD ALIGN = 'right' CLASS='td_label'><b>{$clear_capt}</b></TD><TD>";			
				$clear_capt = "";					// 1st only
				print "<INPUT TYPE='checkbox' NAME= 'frm_ckbx_{$asgn_row['assign_id']}' VALUE= {$asgn_row['assign_id']} CHECKED> {$asgn_row['theunit']}";
				print "</TD></TR>\n";
				}				// end while ()
			}				// end if (mysql_affected_rows()>0)

	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>			
	<tr><td colspan = 2 ALIGN = 'center'><br />
	<input type = 'button' VALUE = 'Cancel'	onClick = 'navTo ("sp_tick.php", <?php echo $_POST['id'];?>)' />
	<input type = 'button' VALUE = 'Reset'	onClick = 'this.form.reset()' STYLE = 'margin-left:40px' />
	<input type = 'button' VALUE = 'Next'	onClick = 'validate()'  STYLE = 'margin-left:40px' />
	</td></tr>
	</table>
	<input type = 'hidden' NAME = 'update' 			value='1' />
	<input type = 'hidden' NAME = 'frm_ticket_id' 	value = "<?php echo $the_id; ?>" />
	<input type = 'hidden' NAME = "id" 				value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
	<input type = 'hidden' NAME = "id_str" 			value = "<?php echo $_POST['id_str'];?>" />
	</form>
<?php
	}		//end function do_is_start()

	function do_is_finished(){
		if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
			if ((array_key_exists('frm_meridiem_problemstart', $_POST)) && ($_POST['frm_meridiem_problemstart'] == 'pm')){		// 4/20/11
				$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
				}
			if (isset($_POST['frm_meridiem_problemend'])) {
				if ($_POST['frm_meridiem_problemend'] == 'pm'){
					$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
					}
				}
			}		// end if (!get_variable('military_time'))
			
		$frm_problemend  = (isset($_POST['frm_year_problemend'])) ? "{$_POST['frm_year_problemend']}-{$_POST['frm_month_problemend']}-{$_POST['frm_day_problemend']} {$_POST['frm_hour_problemend']}:{$_POST['frm_minute_problemend']}:00" : "NULL";
		$the_problemend  = quote_smart(trim($frm_problemend));
		$comments = 	quote_smart(trim($_POST['frm_disp']));
		$description = 	quote_smart(trim($_POST['frm_synopsis']));
		$the_id = quote_smart($_POST['frm_ticket_id']);
		$now = now_ts();
		$by = $_SESSION['SP']['user_id'];
		
		$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
			`problemend`= {$the_problemend},
			`comments`= 	concat(`comments`, {$comments}), 
			`description`=	concat(`description`, {$description}), 
			`updated`='$now',
			`_by` = $by,
			`status` = {$GLOBALS['STATUS_CLOSED']} 
			WHERE `id` = {$the_id} LIMIT 1";
			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
										
		foreach ($_POST as $VarName=>$VarValue) {			// set clear time each assign record - 8/10/10
			if (substr($VarName, 0, 8) == "frm_ckbx" ) {			
		
				$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
					`clear` = '{$now}',
					`as_of` = '{$now}'
					WHERE `id` = {$VarValue} LIMIT 1;";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$work_ary = explode("_", $VarName);			// see checkbox name construct above
				$assign_id = $work_ary[2];								
				sp_do_log($GLOBALS['LOG_CALL_CLR'], $_POST['frm_ticket_id'], $assign_id);				// write log record					
				}
			}		// end foreach () ...
		

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);

		sp_do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $_POST['frm_ticket_id'])	;
		unset($result);
		return $row['scope'];				// 2/15/10

		} 			// end function do_is_finished()
		
	require_once('incs/footer.php');	
	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;
		
?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_TICKET'];?>" />
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
	</BODY><!-- <?php echo __LINE__;?> -->
	</HTML>
