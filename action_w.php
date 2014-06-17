<?php

error_reporting(E_ALL);

session_start();
require_once './incs/functions.inc.php';
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);		// 8/27/10

$istest = FALSE;
if ((($istest)) && (!empty($_GET))) {dump ($_GET);}
if ((($istest)) && (!empty($_POST))) {dump ($_POST);}

$get_action = (empty($_GET['action']))? "form" : $_GET['action'];		// 10/21/08
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <HEAD><TITLE><?php print gettext('Tickets - Action Module');?></TITLE>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
    <META HTTP-EQUIV="Expires" CONTENT="0" />
    <META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
    <META HTTP-EQUIV="Script-date" CONTENT="8/24/08" />
    <LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT src="./js/multiSelect.js"></SCRIPT>
<SCRIPT>
/**
 *
 * @returns {undefined}
 */
    function ck_window() {		//  onLoad = "ck_window()"
        if (window.opener == null) { alert ("<?php print __LINE__;?>")}
        }		// end function ck_window()

    if (document.all && !document.getElementById) {		// accomodate IE
        document.getElementById = function (id) {
            return document.all[id];
            };
        }

    try {
        parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
        parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
        parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
        }
    catch(e) {
        }
/**
 *
 * @returns {Array}
 */
    function $() {									// 2/11/09
        var elements = new Array();
        for (var i = 0; i < arguments.length; i++) {
            var element = arguments[i];
            if (typeof element == 'string')
                element = document.getElementById(element);
            if (arguments.length == 1)
                return element;
            elements.push(element);
            }

        return elements;
        }
/**
 *
 * @param {type} theForm
 * @param {type} theBool
 * @returns {undefined}
 */
    function do_asof(theForm, theBool) {							// 8/10/08
//		alert(56);
//		alert(theForm.name);
        theForm.frm_year_asof.disabled = theBool;
        theForm.frm_month_asof.disabled = theBool;
        theForm.frm_day_asof.disabled = theBool;
        theForm.frm_hour_asof.disabled = theBool;
        theForm.frm_minute_asof.disabled = theBool;
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_unlock(theForm) {									// 8/10/08
        document.getElementById("lock").style.visibility = "hidden";
        do_asof(theForm, false);
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_lock(theForm) {										// 8/10/08
        do_asof(theForm, true);
        document.getElementById("lock").style.visibility = "visible";
        }
/**
 *
 * @returns {unresolved}
 */
    String.prototype.trim = function () {
        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @param {type} str
 * @returns {Boolean}
 */
    function chknum(str) {
        var nums = str.trim().replace(/\D/g, "" );							// strip all non-digits

        return (nums == str.trim());
        }
/**
 *
 * @param {type} val
 * @param {type} lo
 * @param {type} hi
 * @returns {@exp;@call;chknum}
 */
    function chkval(val, lo, hi) {
        return  (chknum(val) && !((val> hi) || (val < lo)));}
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function datechk_r(theForm) {		// as-of vs now
        var start = new Date();
        start.setFullYear(theForm.frm_year_asof.value, theForm.frm_month_asof.value-1, theForm.frm_day_asof.value);
        start.setHours(theForm.frm_hour_asof.value, theForm.frm_minute_asof.value, 0,0);

        var end = new Date();

        return (start.valueOf() <= end.valueOf());
        }
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function validate(theForm) {
        var errmsg="";
        if (theForm.frm_description.value == "") {errmsg+= "\t<?php print gettext('Description is required');?>\n";}
        do_unlock(theForm) ;
        if (!chkval(theForm.frm_year_asof.value, <?php print date('Y')-1 . ", " . date('Y'); ?>)) {errmsg+= "\t<?php print gettext('As-of date error - Year');?>\n";}
        if (!chkval(theForm.frm_month_asof.value, 1,12)) {errmsg+= "\t<?php print gettext('As-of date error - Month');?>\n";}
        if (!chkval(theForm.frm_day_asof.value, 1,31)) {errmsg+= "\t<?php print gettext('As-of date error - Day');?>\n";}
        if (!chkval(theForm.frm_hour_asof.value, 0,23)) {errmsg+= "\t<?php print gettext('As-of time error - Hours');?>\n";}
        if (!chkval(theForm.frm_minute_asof.value, 0,59)) {errmsg+= "\t<?php print gettext('As-of time error - Minutes');?>\n";}
        if (!datechk_r(theForm)) {errmsg+= "\t<?php print gettext('As-of date/time error - future?');?>\n" ;}

        if (errmsg!="") {
            do_lock(theForm);
            alert ("<?php print gettext('Please correct the following and re-submit');?>:\n\n" + errmsg);

            return false;
            }
        else {
            theForm.submit();
            }
        }				// end function validate(theForm)
    </SCRIPT>
    </HEAD>
<?php
    print (($_SESSION['internet']) && ($get_action == "add")||($get_action == "update"))?
        "<BODY onLoad = 'do_notify(); ck_window();' onUnload='GUnload();'>\n":
        "<BODY onLoad = 'ck_window();'>\n";

    $do_yr_asof = false;		// js year housekeeping

    $optstyles = array ();		// see css

    $query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types`";				// 1/27/09
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
        $optstyles[$row['name']] = $row['name'];
        }
    unset($result);

    if ($get_action == 'add') {
        $now = mysql_format_date(time() - (get_variable('delta_mins')*60));

        if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]'"))
            print "<FONT CLASS='warn'>" . gettext('Invalid Ticket ID') . ": '$_GET[ticket_id]'</FONT>";
        elseif ($_POST['frm_description'] == '')
            print '<FONT CLASS="warn">' . gettext('Please enter Description.') . '</FONT><BR />';
        else {
            $responder = $sep = "";
            foreach ($_POST as $VarName=>$VarValue) {			// 3/20/10
                $temp = explode("_", $VarName);
                if (substr($VarName, 0, 7)=="frm_cb_") {
                    $responder .= $sep . $VarValue;		// space separator for multiple responders
                    $sep = " ";
                    }
                }
            $_POST['frm_description'] = strip_html($_POST['frm_description']); //fix formatting, custom tags etc.

            $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST['frm_meridiem_asof'] : "" ;

            $frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
                                                                                // 8/15/10
/*
             $query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE
                 `description` = '{$_POST['frm_description']}' AND
                 `ticket_id` = '{$_GET['ticket_id']}' AND
                 `user` = '{$_SESSION['user_id']}' AND
                 `action_type` = '{$GLOBALS['ACTION_COMMENT']}' AND
                 `updated` = '{$frm_asof}' AND
                 `responder` = '{$responder}' ";
*/													// 4/9/2014
	     		$query 	= "INSERT INTO `$GLOBALS[mysql_prefix]action`
	     			(`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES (
	     				addslashes({$_POST['frm_description']}),
	     				'{$_GET['ticket_id']}',
	     				'{$now}',
	     				{$_SESSION['user_id']},
	     				addslashes({$GLOBALS['ACTION_COMMENT']}),
	     				'{$frm_asof}',
	     				addslashes({$responder})
	     				)";


            $result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);
            if (mysql_affected_rows()==0) {		// not a duplicate - 8/15/10

                 $query 	= "INSERT INTO `$GLOBALS[mysql_prefix]action`
	     			(`description`,`ticket_id`,`date`,`user`,`action_type`, `updated`, `responder`) VALUES (
	     				'{$_POST['frm_description']}',
	     				'{$_GET['ticket_id']}',
	     				'{$now}',
	     				{$_SESSION['user_id']},
	     				{$GLOBALS['ACTION_COMMENT']},
	     				'{$frm_asof}',
	     				'{$responder}')";		// 8/24/08
                $result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename(__FILE__), __LINE__);

                $ticket_id = mysql_insert_id();								// just inserted action id
    //			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) 		// generic log table writer - 5/31/08, 10/6/09
                do_log($GLOBALS['LOG_ACTION_ADD'], $_GET['ticket_id'], 0,  mysql_insert_id());		// 3/18/10
                $query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE `id`='" . $_GET['ticket_id'] . "' LIMIT 1";
                $result = mysql_query($query) or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
                }		// end insert process

//			add_header($_GET['ticket_id']);
            print "<br /><FONT CLASS='header' STYLE = 'margin-left:200px;'>" . gettext('Action record has been added') . "</FONT><BR /><BR />";

//			show_ticket($_GET['ticket_id']);
            print "<BR /><BR /><INPUT TYPE='button' VALUE='" . gettext('Finished') . "' onClick = 'opener.location.reload(true); opener.parent.frames[\"upper\"].show_msg(\"" . gettext('Action added!') . "\"); window.close();' STYLE = 'margin-left:300px;' /><BR /><BR /><BR />";	//	01/22/11Added refresh of opener window.
//________________________________________________________________
            print "</BODY>";				// 10/19/08

            $addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_ACTION_CHG']);		// returns array or FALSE

            if ($addrs) {
?>
<SCRIPT>
/**
 *
 * @returns {unresolved}
 */
    function do_notify() {
        var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
        var theText= "TICKET - ACTION: ";
        var theId = '<?php print $_POST['frm_ticket_id'];?>';

//		mail_it ($to_str, $text, $ticket_id, $text_sel=1, $txt_only = FALSE)

        var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId +"&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
        sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
        }			// end function do notify()
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
    function handleResult(req) {				// the 'called-back' function
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
        req.setRequestHeader('User-Agent','XMLHTTP/1.0');
        if (postData)
            req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        req.onreadystatechange = function () {
            if (req.readyState != 4) return;
            if (req.status != 200 && req.status != 304) {
<?php
    if ($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>

                return;
                }
            callback(req);
            }
        if (req.readyState == 4) return;
        req.send(postData);
        }
/**
 *
 * @type Array
 */
    var XMLHttpFactories = [
        function () {return new XMLHttpRequest();	},
        function () {return new ActiveXObject("Msxml2.XMLHTTP");	},
        function () {return new ActiveXObject("Msxml3.XMLHTTP");	},
        function () {return new ActiveXObject("Microsoft.XMLHTTP");	}
        ];
/**
 *
 * @returns {Boolean}
 */
    function createXMLHTTPObject() {
        var xmlhttp = false;
        for (var i=0;i<XMLHttpFactories.length;i++) {
            try {
                xmlhttp = XMLHttpFactories[i]();
                }
            catch (e) {
                continue;
                }
            break;
            }

        return xmlhttp;
        }

</SCRIPT>
<?php

            }		// end if($addrs)
        else {
?>
<SCRIPT>
/**
 *
 * @returns {unresolved}
 */
    function do_notify() {
        return;
        }			// end function do notify()
</SCRIPT>
<?php
            }

        print "</HTML>";				// 10/19/08
        }		// end else ...
// ____________________________________________________
        exit();

        }		// 	end if($get_action == 'add')

    else if ($get_action == 'delete') {
        if (array_key_exists('confirm', ($_GET))) {
            do_log($GLOBALS['LOG_ACTION_DELETE'], $_GET['ticket_id'], 0, $_GET['id']);		// 8/7/08
//			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09

            $result = mysql_query("DELETE FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") or do_error('','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
            print '<FONT CLASS="header">' . gettext('Action deleted') . '</FONT><BR /><BR />';
            add_header($_GET['ticket_id']);
            show_ticket($_GET['ticket_id']);
            }
        else {
            $query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
            $result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
            $row = stripslashes_deep(mysql_fetch_assoc($result));

            print "<FONT CLASS='header'>" . gettext('Really delete action record') . " '" . shorten($row['description'], 24) . "' ? </FONT><BR /><BR />";
            print "<FORM NAME='delfrm' METHOD='post' ACTION='action_w.php?action=delete&id=$_GET[id]&ticket_id=" . $_GET['ticket_id'] . "&confirm=1'>";
            print "<INPUT TYPE='Submit' VALUE='" . gettext('Yes') . "'/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            print "<INPUT TYPE='Button' VALUE='" . gettext('Cancel') . "' onClick='history.back();'/></FORM>";
            }

        }				// end if ($get_action == 'delete')

    else if ($get_action == 'update') {		//update action and show ticket
        $responder = $sep = "";
        if (array_key_exists('frm_responder', ($_POST))) {
            for ($i=0; $i< count ($_POST['frm_responder']); $i++) {
                $responder .= $sep . $_POST['frm_responder'][$i];		// space separator for multiple responders
                $sep = " ";
                }
            }
        $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

        $frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
        $result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]action` SET `description`='$_POST[frm_description]', `responder` = '$responder', `updated` = '$frm_asof' WHERE `id`='$_GET[id]' LIMIT 1") or do_error('action_w.php::update action','mysql_query',mysql_error(),basename( __FILE__), __LINE__);
        $result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` =	'$frm_asof' WHERE id='$_GET[ticket_id]' LIMIT 1") 	or do_error('action_w.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
        $result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1") 			or do_error('action_w.php::update action','mysql_query',mysql_error(), basename(__FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_array($result));
        print '<BR /><BR /><FONT CLASS="header">' . gettext('Action updated') . '</FONT><BR /><BR />';
        add_header($_GET['ticket_id']);
        show_ticket($row['ticket_id']);
        }				// end if ($get_action == 'update')

    else if ($get_action == 'edit') {		//get and show action to update
        $query = "SELECT * FROM `$GLOBALS[mysql_prefix]action` WHERE `id`='$_GET[id]' LIMIT 1";
        $result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_array($result));
        $responders = explode(" ", $row['responder']);				// to array
//		dump (__LINE__);
//		dump ($responders);
        $do_yr_asof = true;
?>
        <SPAN STYLE='margin-left:83px;'><FONT CLASS="header"><?php print gettext('Edit Action');?></FONT></SPAN><BR /><BR />
        <FORM METHOD="post" NAME='ed_frm' ACTION="action_w.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update">
        <TABLE BORDER="0"> <!-- 3/20/10 -->
        <TR CLASS='even' VALIGN='top'><TD rowspan=4><B><?php print gettext('Description');?>:</B> <font color='red' size='-1'>*</font></TD>
            <TD colspan=3><TEXTAREA ROWS="2" COLS="90" NAME="frm_description" WRAP="virtual"><?php print $row['description'];?></TEXTAREA>
            </TD></TR>
        <TR CLASS='odd' VALIGN='top'>
<?php
//						generate dropdown menu of responders -- if(in_array($rowtemp[id], $row[responder]))

//		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `name` ASC";		// 2/12/09

        $query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `y`.`id` AS `type_id`, `r`.`id` AS `unit_id`, `r`.`name` AS `unit_name`,
            `s`.`description` AS `stat_descr`,  `r`.`description` AS `unit_descr`,
            (SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) AS `nr_assigned`
            FROM `$GLOBALS[mysql_prefix]responder` `r`
            LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` ON ( `r`.`type` = y.id )
            LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id )
            ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC";											// 2/1/10, 3/15/10

//		dump($query);	//
        $result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
        $max = 24;
        $height =  (mysql_affected_rows()>$max) ? ($max * 30 ) : (mysql_affected_rows() + 1) * 30;
        print "<TR VALIGN='top'><TD COLSPAN=2>" . get_units_legend(). "</TD></TR>";
        $checked = (in_array("0", $responders))? "CHECKED" : "";	// NA is special case - 8/8/10
        print "<TD><DIV  style='width:auto;height:{$height}PX; overflow-y: auto; overflow-x: auto;' >
            <INPUT TYPE = 'checkbox' VALUE=0 NAME = 'frm_cb_0 />" . gettext('NA') . "<BR />\n";

        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 7/20/10
            $the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		//

            $checked = (in_array($row['unit_id'], $responders))? "CHECKED" : "";
//          $ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
//    		dump($ct_str);

            $the_name = "frm_cb_" . stripslashes ($row['unit_name']);
			print "\t<INPUT TYPE = 'checkbox' VALUE='{$row['unit_id']}' NAME = \"{$the_name}\" $checked />XX
                <SPAN STYLE='width:300px; display:inline; background-color:{$the_bg_color}; color:{$the_text_color};'>" .
                stripslashes ($row['unit_name']) . "&nbsp;</SPAN>{$ct_str}";
            print "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;<SPAN STYLE = 'width:200px; background-color:{$row['bg_color']}; color:{$row['text_color']};'>
				{$row['stat_descr']}</SPAN>ZZZZ<BR />\n";		// 7/20/10

            }
        unset ($row);
        print "\t</DIV></TD>\n";
?>
        <TD CLASS="td_label"><SPAN><?php print gettext('As of');?>: &nbsp;&nbsp;<SPAN>
        <INPUT SIZE=4 NAME="frm_year_asof" VALUE="" MAXLENGTH=4 />
        <INPUT SIZE=2 NAME="frm_month_asof" VALUE="" MAXLENGTH=2 />
        <INPUT SIZE=2 NAME="frm_day_asof" VALUE="" MAXLENGTH=2 />
        <INPUT SIZE=2 NAME="frm_hour_asof" VALUE="" MAXLENGTH=2 />:<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="" MAXLENGTH=2 />
        &nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.ed_frm);'/>
            <br /> <br /> <br />

            <INPUT TYPE="button" VALUE="<?php print gettext('Cancel');?>"	onClick="history.back();" STYLE = 'margin-left:20px' />
            <INPUT TYPE="button" VALUE="<?php print gettext('Form reset');?>" 	onClick="this.form.reset();init();" STYLE = 'margin-left:20px'/>
            <INPUT TYPE="button" VALUE="<?php print gettext('Next');?>"	onClick="return validate(this.form);" STYLE = 'margin-left:20px'/>
            </TD></TR>
        </TABLE></FORM><BR />
<?php
        }		// end if ($get_action == 'edit')

    else if ($get_action == 'form') {
        $do_yr_asof = true;
?>
        <SPAN STYLE='margin-left:60px;'><FONT CLASS="header"><?php print gettext('Add Action');?></FONT></SPAN><BR /><BR />
        <FORM METHOD="post" NAME="add_frm" onSubmit='return validate(this.form);' ACTION="action_w.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
        <TABLE BORDER="0" STYLE='margin-left:100px;'>
        <TR CLASS='even'><TD CLASS='td_label'><?php print gettext('Description');?>: <font color='red' size='-1'>*</font></TD>
            <TD colspan=2><TEXTAREA ROWS="2" COLS="90" NAME="frm_description"></TEXTAREA>
            </TD></TR>
<?php
//						generate dropdown menu of responders

        $query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `t`.`id` AS `type_id`, `r`.`id` AS `unit_id`, `r`.`name` AS `unit_name`,
            `s`.`description` AS `stat_descr`,  `r`.`description` AS `unit_descr`,
            (SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns`
                WHERE `$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id  AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
                AS `nr_assigned`
            FROM `$GLOBALS[mysql_prefix]responder` `r`
            LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )
            LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id )
            ORDER BY `nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC";											// 2/1/10, 3/15/10

//		dump($query);
        $result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename(__FILE__), __LINE__);
        $max = 24;

        $height =  (mysql_affected_rows()>$max) ? ($max * 22 ) : (mysql_affected_rows() + 1) * 22;
        print "<TR><TD></TD><TD COLSPAN=2>" . get_units_legend(). "</TD></TR>";
        print "<TR CLASS='odd'><TD CLASS='td_label'></TD>";		// 8/8/10
        print "<TD><DIV  style='width:auto;height:{$height}PX; overflow-y: auto; overflow-x: auto;' >
            <INPUT TYPE = 'checkbox' VALUE=0 NAME = 'frm_cb_0' />" . gettext('NA') . "<BR />\n";
//    		$the_class = (array_key_exists($row['type'], $optstyles))?  $optstyles[$row['type']] : "";

        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $type_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 7/20/10
            $type_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];		//

            $ct_str = ($row['nr_assigned']==0) ? ""  : "&nbsp;({$row['nr_assigned']})" ;
            $the_name = "frm_cb_" . stripslashes ($row['unit_name']);
            print "\t<INPUT TYPE = 'checkbox' VALUE='{$row['unit_id']}' NAME = \"{$the_name}\" />
                <SPAN STYLE = 'width:300px; display:inline; background-color:{$type_bg_color}; color:{$type_text_color};'>" .
                stripslashes ($row['unit_name']) . "</SPAN> &nbsp; {$ct_str}";
//			dump($ct_str);
            print " - <SPAN STYLE = 'width:200px; background-color:{$row['bg_color']}; color:{$row['text_color']};'>
                {$row['stat_descr']}</SPAN><BR />\n";		// 7/20/10
            }
        print "</DIV></TD>";
?>
        <TD CLASS="td_label"><SPAN STYLE = 'margin-left:20px'><?php print gettext('As of');?>: &nbsp;&nbsp;</SPAN>
            <INPUT SIZE=4 NAME="frm_year_asof" VALUE="" MAXLENGTH=4 />
            <INPUT SIZE=2 NAME="frm_month_asof" VALUE="" MAXLENGTH=2 />
            <INPUT SIZE=2 NAME="frm_day_asof" VALUE="" MAXLENGTH=2 />
            <INPUT SIZE=2 NAME="frm_hour_asof" VALUE="" MAXLENGTH=2 />:<INPUT SIZE=2 NAME="frm_minute_asof" VALUE="" MAXLENGTH=2>
            <INPUT TYPE="hidden" NAME = "frm_ticket_id" VALUE = "<?php print $_GET['ticket_id'];?>" />
            &nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.add_frm);' />
            <br /> <br /> <br />
            <TR><TD COLSPAN=3 ALIGN='center'>

            <INPUT TYPE = 'button' VALUE = '<?php print gettext('Cancel');?>' onClick = 'window.close();' STYLE = 'margin-left:40px' />
            <INPUT TYPE="button" VALUE="<?php print gettext('Reset form');?>"	onClick="this.form.reset();init();"  STYLE = 'margin-left:20px' />
            <INPUT TYPE="button" VALUE="<?php print gettext('Next');?>"	onClick="return validate(this.form);"  STYLE = 'margin-left:20px' />
            </TD></TR>

        </TABLE><BR />
        </FORM>
<?php
        }				// end if ($get_action == 'form')

//				 common to all
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>"/>
</FORM>
</BODY>
<?php
    if ($do_yr_asof) { 		// for ADD and EDIT only
?>
<SCRIPT LANGUAGE="Javascript">
init();
/**
 *
 * @returns {undefined}
 */
function init() {
    do_unlock(document.forms[0]);
    var now = new Date();
    if (now.getYear()>2000) {
        document.forms[0].frm_year_asof.value= now.getYear() - 2000;
        }
    else {
        if (now.getYear()>100) {
            document.forms[0].frm_year_asof.value=now.getYear() - 100;
            }
        else {
            document.forms[0].frm_year_asof.value=now.getYear();
            }
        }
    document.forms[0].frm_year_asof.value=parseInt(document.forms[0].frm_year_asof.value)+ 2000;
    document.forms[0].frm_month_asof.value=now.getMonth()+1;
    document.forms[0].frm_day_asof.value=now.getDate();
    document.forms[0].frm_hour_asof.value=now.getHours();
    document.forms[0].frm_minute_asof.value=now.getMinutes() ;
    if (document.forms[0].frm_hour_asof.value<10) { document.forms[0].frm_hour_asof.value = "0" + document.forms[0].frm_hour_asof.value; }
    if (document.forms[0].frm_minute_asof.value<10) { document.forms[0].frm_minute_asof.value = "0" + document.forms[0].frm_minute_asof.value; }
    do_lock(document.forms[0]);
    }
</SCRIPT>
<?php
        }		// end 	if ($do_yr_asof)

?>
</HTML>
<?php
//function new_notify_user($ticket_id,$action) {								// 10/20/08
//	if (get_variable('allow_notify') != '1') return FALSE;					//should we notify?
//
//	$addrs = array();													//
//
//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` WHERE (`ticket_id`='$ticket_id' OR `ticket_id`=0)  AND `on_action` = '1'";	// all notifies for given ticket - or any ticket 10/22/08
//	print __LINE__;
//	dump ($query);
//	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		//is it the right action?
//		dump ($row['email_address']);
////		if (($action == $GLOBALS['NOTIFY_ACTION'] AND $row['on_action']) OR ($action == $GLOBALS['NOTIFY_TICKET'] AND $row['on_ticket'])) {
//			if (is_email($row['email_address'])) {
//				print __LINE__;
//				array_push($addrs, $row['email_address']);
//				}		// save for emailing
////			}
//		}
//	dump($addrs);
//	return (empty($addrs))? FALSE: $addrs;
//	}
//
?>
