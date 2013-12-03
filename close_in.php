<?php
/**
 * @package close_in.php
 * @author John Doe <john.doe@example.com>
 * @since 2009-08-20
 * @version 2013-10-24
 */
/*
8/20/09	initial release
2/15/10 corrections per jb email
4/5/10 opener.href steps added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/10/10 added clearing calls assigned this incident
12/1/10 get_text disposition added
12/18/10 set signals added
3/15/11 changed stylesheet.php to stylesheet.php
4/20/11 corrections re military time false
10/24/13 Added Auto Dispatch status to close incident script
*/
error_reporting(E_ALL);
@session_start();
require_once('incs/functions.inc.php');		//7/28/10

if($istest) {
//	dump(basename(__FILE__));
	print "GET<br />\n";
	dump($_GET);
	print "POST<br />\n";
	dump($_POST);
	}
$disposition = get_text("Disposition");				// 12/1/10

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print gettext('Close Incident');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css" />	<!-- 3/15/11 -->
</HEAD>
<?php

if (empty($_POST)) { 		// pass # 1
?>
<BODY onLoad = "if(document.frm_text) {document.frm_note.frm_text.focus() ;}"><CENTER>
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($_GET['ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);
		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			do_is_closed();
			}
		else {
			do_is_start($row);
			}
		}		// end if (empty($_POST))		
		
else {			// not empty then is finished
	$quick = (intval(get_variable('quick'))==1);				// 12/16/09
//	dump($quick);
	if ($quick) {
		do_is_finished();
?>
<BODY onLoad = "opener.parent.frames['upper'].show_msg ('Incident closed!');opener.location.href = 'main.php'; window.close();"> <!-- 4/5/10 -->
</BODY></HTML>
<?php
		}
	else{
?>
<BODY onLoad = "if(document.frm_text) {document.frm_note.frm_text.focus();}"><CENTER>
<?php
		$scope = do_is_finished();		// 2/15/10
?>
<H3><?php print gettext('Call');?><SPAN style = 'background-color:#DEE3E7'><?php print $scope; ?></SPAN><?php print gettext('closed');?></H3><BR /><BR />	<!-- 2/15/10 -->
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Finished');?>' onClick = "opener.location.href = 'main.php'; window.close();" />
</BODY>
</HTML>

<?php
		}				 // end if/else quick
	
	}		// end if/else

/**
 * do_is_closed
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function do_is_closed() {
	global $row;
?>		
<CENTER>
	<H3><?php print gettext("Call  {$row['scope']} is already closed");?></H3><BR /><BR />
	<INPUT TYPE = 'button' VALUE = '<?php print gettext('Cancel');?>' onClick = 'window.close();'/>	
	</BODY>
	</HTML>
<?php		
	}				// end function do_is_closed()
	
/**
 * do_is_start
 * Insert description here
 *
 * @param $in_row
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function do_is_start($in_row) {				// 3/22/10
	global $disposition;
?>
<SCRIPT>
/**
 * 
 * @returns {unresolved}
 */  
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
/**
 * 
 * @returns {Boolean}
 */
	function validate() {
		if(document.frm_note.frm_disp.value.trim().length == 0) {
			alert("<?php print $disposition;?> is required"); 
			return false;
			}
		else{document.frm_note.submit();}
		}		// end function
		
</SCRIPT>
	<H4><?php print gettext('Enter Incident Close Information');?></H4>
<?php
		$short_descr = substr($in_row['scope'], 0, 20);
		$sep = (empty($in_row['tick_street']))? "" : ", ";
		$short_addr =  substr("{$in_row['street']}{$sep}{$in_row['city']} {$in_row['state']}" , 0, 20);
?>
	<H5>( <?php print "{$short_descr} - {$short_addr}"?> )</H5>
	<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
	<TABLE ALIGN = 'center' CELLPADDING = 2 CELLSPACING = 0>
	
	<TR CLASS='even'><TD CLASS='td_label'  ALIGN='right'><?php print gettext('Run End');?>:&nbsp;</TD><TD>
<?php 
	print generate_date_dropdown('problemend',0, FALSE) . "</TD></TR>";
	print (empty($in_row['comments']))? "" : "<TR><TD></TD><TD>{$in_row['comments']}</TD></TR>";
	if (!(empty($in_row['description']))) {
?>		
	<TR CLASS='odd'><TD ALIGN='right' CLASS='td_label' ><?php print gettext('Synopsis');?>:&nbsp;</TD>
		<TD><?php print $in_row['description'];?></TD></TR>
<?php
	$capt = "Add'l";
		}
	else {$capt = "Synopsis";}
?>		
	<TR CLASS='odd'><TD ALIGN='right' CLASS='td_label' ><?php print $capt;?>:&nbsp;</TD>
<SCRIPT>
/**
 * 
 * @param {type} inval
 * @returns {undefined}
 */  
	function set_signal(inval) {				// 12/18/10
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.frm_note.frm_synopsis.value+=" " + temp_ary[1] + ' ';		
		document.frm_note.frm_synopsis.focus();		
		}		// end function set_signal()
/**
 * 
 * @param {type} inval
 * @returns {undefined}
 */
	function set_signal2(inval) {
		var temp_ary = inval.split("|", 2);		// inserted separator
		document.frm_note.frm_disp.value+=" " + temp_ary[1] + ' ';		
		document.frm_note.frm_disp.focus();		
		}		// end function set_signal()
</SCRIPT>

		<TD><TEXTAREA NAME='frm_synopsis' COLS=56 ROWS = 2></TEXTAREA>
			</TD></TR>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
			<TD></TD><TD CLASS="td_label"><?php print gettext('Signal');?> &raquo; 

				<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
			
			
	<TR CLASS='even'><TD ALIGN='right' CLASS='td_label' ><?php print $disposition;?>:&nbsp;</TD>
		<TD><TEXTAREA NAME='frm_disp' COLS=56 ROWS = 2><?php print $in_row['comments'];?></TEXTAREA>
			</TD></TR>
		<TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
			<TD></TD><TD CLASS="td_label"><?php print gettext('Signal');?> &raquo; 

				<SELECT NAME='signals2' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
				<OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
					print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
					}
?>
			</SELECT>
			</TD></TR>
			
			
<?php										// 8/10/10
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
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = {$_GET['ticket_id']} GROUP BY `r`.`id`";

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_affected_rows()>0) {
			$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
			$i=1;
			$clear_capt = "Clear: ";
			while ( $asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result))){
				print "<TR CLASS='{$evenodd[($i)%2]}' VALIGN = 'baseline'><TD ALIGN = 'right' CLASS='td_label'>{$clear_capt}</TD><TD>";			
				$clear_capt = "";					// 1st only
				print "<INPUT TYPE='checkbox' NAME= 'frm_ckbx_{$asgn_row['assign_id']}' VALUE= {$asgn_row['assign_id']} CHECKED /> {$asgn_row['theunit']}";
				print "</TD></TR>\n";
				$i++;
				}				// end while ()
			}				// end if (mysql_affected_rows()>0)

	$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors

?>			
	<TR CLASS='<?php print $evenodd[($i)%2]?>'><TD></TD><TD ALIGN = 'center'>
	<INPUT TYPE = 'button' VALUE = '<?php print gettext('Cancel');?>' onClick = 'window.close();' />
	<INPUT TYPE = 'button' VALUE = '<?php print gettext('Reset');?>' onClick = 'this.form.reset();' STYLE = 'margin-left:20px' />
	<INPUT TYPE = 'button' VALUE = '<?php print gettext('Next');?>' onClick = 'validate();'  STYLE = 'margin-left:20px' />
	</TD></TR>
	</TABLE>
	<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
	</FORM>
	</BODY>
	</HTML>
<?php
	}		//end function do_is_start()

/**
 * do_is_finished
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
	function do_is_finished(){
		$use_status_update = get_variable("use_disp_autostat");		//	10/24/13
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
		$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
		$by = $_SESSION['user_id'];
		
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
				//	Get Responder ID for auto dispatch status
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `id` = {$VarValue} LIMIT 1";	//	10/24/13
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	//	10/24/13
				$row = mysql_fetch_assoc($result);	//	10/24/13
				$un_id = $row['responder_id'];	//	10/24/13
				//	Clear assigns entry
				$query = "UPDATE `$GLOBALS[mysql_prefix]assigns` SET 
					`clear` = '{$now}',
					`as_of` = '{$now}'
					WHERE `id` = {$VarValue} LIMIT 1;";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$work_ary = explode("_", $VarName);			// see checkbox name construct above
				$assign_id = $work_ary[2];	
				//	Do auto dispatch status if switched on.
				if($use_status_update == "1") {	//	10/24/13
					auto_disp_status(6, $un_id);
					}					
				do_log($GLOBALS['LOG_CALL_CLR'], $_POST['frm_ticket_id'], $assign_id);				// write log record					
				}
			}		// end foreach () ...
		

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);

		do_log($GLOBALS['LOG_INCIDENT_CLOSE'], $_POST['frm_ticket_id'])	;
		$addrs = notify_user($the_id, $GLOBALS['NOTIFY_TICKET_CHG']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?	8/28/13
?>	
<SCRIPT>
/**
 * 
 * @returns {undefined}
 */
			function do_notify() {

				var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
				var theText= ' New <?php print get_text("Incident");?>: ';
				var theId = '<?php print $_POST['ticket_id'];?>';
				
		//		mail_it ($to_str, $text, $theId, $text_sel=1;, $txt_only = FALSE)

				var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
				sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
				}			// end function do notify()
/**
 * 
 * @param {type} req
 * @returns {undefined}
 */
			function handleResult(req) {				// the 'called-back' function
<?php

			if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
			}
/**
 * 
 * @param {type} url
 * @param {type} callback
 * @param {type} postData
 * @returns {unresolved}
 */
			function sendRequest(url,callback,postData) {
				var req = createXMLHTTPObject();
				if (!req) return;
				var method = (postData) ? "POST" : "GET";
				req.open(method,url,true);
				if (postData)
					req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
				req.onreadystatechange = function () {
					if (req.readyState != 4) return;
					if (req.status != 200 && req.status != 304) {
<?php
				if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
						return;
						}
					callback(req);
					}
				if (req.readyState == 4) return;
				req.send(postData);
				}
	
			var XMLHttpFactories = [
				function () {return new XMLHttpRequest();	},
				function () {return new ActiveXObject("Msxml2.XMLHTTP");	},
				function () {return new ActiveXObject("Msxml3.XMLHTTP");	},
				function () {return new ActiveXObject("Microsoft.XMLHTTP");	}
				];
			
			function createXMLHTTPObject() {
				var xmlhttp = false;
				for (var i=0;i<XMLHttpFactories.length;i++) {
					try { xmlhttp = XMLHttpFactories[i](); }
					catch (e) { continue; }
					break;
					}
				return xmlhttp;
				}
<?php
		}				// end if ($addrs)
?>
</SCRIPT>
<?php
		unset($result);
		return $row['scope'];				// 2/15/10

		} 			// end function do_is_finished()
		
//	 window.opener.parent.frames["main"].location="edit.php?ticket_id={$_GET['ticket_id']};
		
?>
