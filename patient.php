<?php

include'./incs/error_reporting.php';

@session_start();
require_once 'incs/functions.inc.php';
do_login(basename(__FILE__));
if ((isset($_REQUEST['ticket_id'])) && 	(strlen(trim($_REQUEST['ticket_id']))>6)) {	shut_down();}			// 6/10/11
require_once($_SESSION['fmp']);		// 8/27/10
if ($istest) {
    print "GET<br />\n";
    dump($_GET);
    print "POST<br />\n";
    dump($_POST);
    }
$evenodd = array ("even", "odd");	// CLASS names for alternating table row colors
$get_action = (array_key_exists ( "action", $_REQUEST ))? $_REQUEST['action'] : "new" ;
$api_key = get_variable('gmaps_api_key');
$gmaps = $_SESSION['internet'];
//dump($get_action);

$fullname =	 		get_text("Full name");
$dateofbirth =	 	get_text("Date of birth");
$gender =	 		get_text("Gender");
$insurance =	 	get_text("Insurance");
$facilitycontact = 	get_text("Facility contact");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <HEAD><TITLE><?php print gettext('Tickets') . " - " . get_text("Patient") ." " . gettext('Module');?></TITLE>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
    <META HTTP-EQUIV="Expires" CONTENT="0">
    <META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
    <META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
    <META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
    <META HTTP-EQUIV="Script-date" CONTENT="8/16/08">
    <LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>
<?php
    if ($gmaps) {		// 8/4/11
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
		if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
			$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
			} else {
			$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
			}
?>
	<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT>
    <SCRIPT SRC="./js/graticule.js" type="text/javascript"></SCRIPT>
<?php
        }
    ?>
<SCRIPT>
/**
 *
 * @returns {undefined}
 */
function ck_frames() {		//  onLoad = "ck_frames()"
<?php	if (array_key_exists('in_win', $_GET)) {echo "\n return;\n";} ?>	// 6/10/11

    if (self.location.href==parent.location.href) {
        self.location.href = 'index.php';
        }
    else {
        parent.upper.show_butts();										// 1/21/09
        }
    }		// end function ck_frames()

    if (document.all && !document.getElementById) {		// accomodate IE
        document.getElementById = function (id) {
            return document.all[id];
            }
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
 * @returns {unresolved}
 */
    String.prototype.trim = function () {
        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @returns {undefined}
 */
    function do_cancel() {				// 6/10/11
<?php
    $can_str = (array_key_exists('in_win', $_GET))? "window.close()" : "history.back()";
    echo $can_str;
?>
        }				// end function do_cancel ()
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
        var yr = theForm.frm_year_asof.options[theForm.frm_year_asof.selectedIndex].value;
        var mo = theForm.frm_month_asof.options[theForm.frm_month_asof.selectedIndex].value;
        var da = theForm.frm_day_asof.options[theForm.frm_day_asof.selectedIndex].value;

        var start = new Date();
        start.setFullYear(yr, mo-1, da);
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
        if (theForm.frm_name.value == "") {errmsg+= "\t<?php print gettext('ID/Name is required');?>\n";}
        if (theForm.frm_gender_val.value==0) {errmsg+= "\t<?php echo $gender;?> <?php print gettext('required');?>\n";}
//      if (theForm.frm_ins_id.value==0) {errmsg+= "\t<?php echo $insurance;?> <?php print gettext('selection required');?>\n";}
        if (theForm.frm_description.value == "") {errmsg+= "\t<?php print gettext('Description is required');?>\n";}
        do_unlock(theForm) ;
        if (!chkval(theForm.frm_hour_asof.value, 0,23)) {errmsg+= "\t<?php print gettext('As-of time error - Hours');?>\n";}
        if (!chkval(theForm.frm_minute_asof.value, 0,59)) {errmsg+= "\t<?php print gettext('As-of time error - Minutes');?>\n";}
        if (!datechk_r(theForm)) {errmsg+= "\t<?php print gettext('As-of date/time error - future?');?>\n" ;}

        if (errmsg!="") {
            do_lock(theForm);
            alert ("<?php print gettext('Please correct the following and re-submit.');?>:\n\n" + errmsg);

            return false;
            }
        else {
<?php
        if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		// 7/2/2013
?>
            var theMessage = "<?php print gettext('New') . " " . get_text('Patient') . " " . gettext('record by') . " " . $_SESSION['user'];?>";
            broadcast(theMessage ) ;
<?php
    }			// end if (broadcast)
?>
            theForm.submit();
            }
        }				// end function validate(theForm)
/**
 *
 * @param {type} inval
 * @returns {undefined}
 */
    function set_signal(inval) {
        var temp_ary = inval.split("|", 2);		// inserted separator
        if (document.patientAdd) {
            var lh_sep = (document.patientAdd.frm_description.value.trim().length>0)? " " : "";
            document.patientAdd.frm_description.value+=lh_sep + temp_ary[1] + ' ';
            document.patientAdd.frm_description.focus();
            }
        else {
        var lh_sep = (document.patientEd.frm_description.value.trim().length>0)? " " : "";
            document.patientEd.frm_description.value+= lh_sep + temp_ary[1] + ' ';
            document.patientEd.frm_description.focus();
            }
        }		// end function set_signal()
/**
 *
 * @param {type} theForm
 * @param {type} theBool
 * @returns {undefined}
 */
    function do_asof(theForm, theBool) {							// 8/10/08
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
        do_asof(theForm, false);
        document.getElementById("lock").style.visibility = "hidden";
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
 * @param {type} the_form
 * @returns {undefined}
 */
    function do_reset(the_form) {
        do_lock(the_form);
        the_form.reset();
        the_form.frm_ins_id.value="";
        the_form.frm_gender_val.value=0;
        }

    </SCRIPT>
<?php				// 7/3/2013
    if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
        require_once './incs/socket2me.inc.php';		// 5/22/2013
        }
?>
    </HEAD>
<?php
    print (($get_action == "add")||($get_action == "update"))? "<BODY onLoad = 'do_notify(); ck_frames();' onUnload='GUnload();'>\n": "<BODY onLoad = 'ck_frames();'>\n";
    if ($get_action == 'add') {		/* update ticket */
        $now = mysql_format_date(time() - (get_variable('delta_mins')*60));

        if ($_GET['ticket_id'] == '' OR $_GET['ticket_id'] <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$_GET[ticket_id]' LIMIT 1"))
            print "<FONT CLASS='warn'>Invalid Ticket ID: '$_GET[ticket_id]'</FONT>";
        elseif ($_POST['frm_description'] == '')
            print '<FONT CLASS="warn">Please enter Description.</FONT><BR />';
        else {
            $_POST['frm_description'] = strip_html($_POST['frm_description']); 				//fix formatting, custom tags etc.

            $post_frm_meridiem_asof = empty($_POST['frm_meridiem_asof'])? "" : $_POST['frm_meridiem_asof'] ;
            $frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$post_frm_meridiem_asof";
                                                            //  8/15/10
             $query 	= "SELECT * FROM  `$GLOBALS[mysql_prefix]patient` WHERE
                 `description` =	'" . addslashes($_POST['frm_description']) . "' AND
                 `ticket_id` =	'{$_GET['ticket_id']}' AND
                 `user` =		'{$_SESSION['user_id']}' AND
                 `action_type` =	'{$GLOBALS['ACTION_COMMENT']}' AND
                 `name` = 		'" . addslashes($_POST['frm_name']) . "' AND
                 `updated` =		'{$frm_asof}' LIMIT 1";

            $result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
            if (mysql_affected_rows()==0) {		// not a duplicate - 8/15/10

                if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
                    $ins_data = "
                        `fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
                        `dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
                        `gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
                        `insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",
                        `facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",
                        `facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",";
                    }
                else { $ins_data = "";}

                 $query 	= "INSERT INTO `$GLOBALS[mysql_prefix]patient` SET
                     {$ins_data}
                     `description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
                     `ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
                     `date`= " .  		quote_smart(addslashes(trim($now))) . ",
                     `user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
                     `action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
                     `name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ",
                     `updated` = " .  	quote_smart(addslashes(trim($frm_asof)));

                $result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
                do_log($GLOBALS['LOG_PATIENT_ADD'], $_GET['ticket_id'], 0, mysql_insert_id());		// 3/18/10
//				($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) 		// generic log table writer - 5/31/08, 10/6/09

                $result = mysql_query("UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'  LIMIT 1") or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
                }
            print "<BR><BR><FONT CLASS='header'>" . get_text("Patient") . " " . gettext('record has been added') . "</FONT><BR /><BR />";
            add_header($_GET['ticket_id']);
            show_ticket($_GET['ticket_id']);
//			notify_user($_GET['ticket_id'],$NOTIFY_ACTION);
            print "</BODY>";				// 10/19/08

            $addrs = notify_user($_GET['ticket_id'],$GLOBALS['NOTIFY_PERSON_CHG']);		// returns array or FALSE
            if ($addrs) {
?>
<SCRIPT>
/**
 *
 * @returns {unresolved}
 */
    function do_notify() {
        var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
        var theText= "<?php print gettext('TICKET - PATIENT');?>: ";
        var theId = '<?php print $_GET['ticket_id'];?>';
//			 mail_it ($to_str, $text, $ticket_id, $text_sel=1;, $txt_only = FALSE)

        var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId) + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
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
// ________________________________________________________
            exit();

        }			// end if ($get_action == 'add')

    else if ($get_action == 'delete') {
        if (array_key_exists('confirm', ($_GET))) {
            do_log($GLOBALS['LOG_PATIENT_DELETE'], $_GET['ticket_id'], 0, $_GET['id']);		// 3/18/10
//			($code, $ticket_id=0, $responder_id=0, $info="", $facility_id=0, $rec_facility_id=0, $mileage=0) {		// generic log table writer - 5/31/08, 10/6/09
            $query = "DELETE FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
            $result = mysql_query($query) or do_error('',$query,mysql_error(), basename( __FILE__), __LINE__);
            print '<FONT CLASS="header">' . get_text("Patient") . ' ' . gettext('record deleted') . '</FONT><BR /><BR />';
            $col_width= max(320, intval($_SESSION['scr_width']* 0.45));
            add_header($_GET['ticket_id']);				// 8/16/08
            show_ticket($_GET['ticket_id']);
            }
        else {
            $query = "SELECT * FROM `$GLOBALS[mysql_prefix]patient` WHERE `id`='$_GET[id]' LIMIT 1";
            $result = mysql_query($query)or do_error($query,$query, mysql_error(), basename(__FILE__), __LINE__);
            $row = stripslashes_deep(mysql_fetch_assoc($result));
            print "<FONT CLASS='header'>" . gettext('Really delete') . " " . get_text("Patient") . " " . gettext('record') . " ' " .shorten($row['description'], 24) . "' ?</FONT><BR /><BR />";
            print "<FORM METHOD='post' ACTION='patient.php?action=delete&id=$_GET[id]&ticket_id=$_GET[ticket_id]&confirm=1'><INPUT TYPE='Submit' VALUE='" . gettext('Yes') . "'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            print "<INPUT TYPE='button' VALUE='" . gettext('Cancel') . "'  onClick='do_cancel();'></FORM>";
            }
        }
    else if ($get_action == 'update') {		//update patient record and show ticket

        $frm_meridiem_asof = array_key_exists('frm_meridiem_asof', ($_POST))? $_POST[frm_meridiem_asof] : "" ;

        $frm_asof = "$_POST[frm_year_asof]-$_POST[frm_month_asof]-$_POST[frm_day_asof] $_POST[frm_hour_asof]:$_POST[frm_minute_asof]:00$frm_meridiem_asof";
//		$query = "UPDATE `$GLOBALS[mysql_prefix]patient` SET `description`='$_POST[frm_description]' , `name`='$_POST[frm_name]', `updated` = '$frm_asof' WHERE id='$_GET[id]' LIMIT 1";
        $now = mysql_format_date(now());

        if ((array_key_exists ('frm_fullname', $_POST))) {		// 6/22/11
            $ins_data = "
                `fullname`	= " . 			quote_smart(addslashes(trim($_POST['frm_fullname']))) . ",
                `dob`	= " .				quote_smart(addslashes(trim($_POST['frm_dob']))) . ",
                `gender`	= " .			quote_smart(addslashes(trim($_POST['frm_gender_val']))) . ",
                `insurance_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_ins_id']))) . ",
                `facility_id`	=" . 		quote_smart(addslashes(trim($_POST['frm_facility_id']))) . ",
                `facility_contact` = " .	quote_smart(addslashes(trim($_POST['frm_fac_cont']))) . ",";
            }
        else { $ins_data = "";}
        $query 	= "UPDATE `$GLOBALS[mysql_prefix]patient` SET
            {$ins_data}
            `description`= " .  quote_smart(addslashes(trim($_POST['frm_description']))) . ",
            `ticket_id`= " .  	quote_smart(addslashes(trim($_GET['ticket_id']))) .	",
            `date`= " .  		quote_smart(addslashes(trim($frm_asof))) . ",
            `user`= " .  		quote_smart(addslashes(trim($_SESSION['user_id']))) . ",
            `action_type` = " . quote_smart(addslashes(trim($GLOBALS['ACTION_COMMENT']))) .	",
            `name` = " .  		quote_smart(addslashes(trim($_POST['frm_name']))) . ",
            `updated` = " .  	quote_smart(addslashes(trim($now))) . "
            WHERE id= " . 		quote_smart($_GET['id']) . " LIMIT 1";

        $result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

        $query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `updated` = '$frm_asof' WHERE id='$_GET[ticket_id]'";
        $result = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);

        $result = mysql_query("SELECT ticket_id FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]'") or do_error('patient.php::update patient record','mysql_query',mysql_error(), basename( __FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_assoc($result));

        print '<br><br><FONT CLASS="header">' . get_text("Patient") . ' ' . gettext('record updated') . '</FONT><BR /><BR />';
        add_header($_GET['ticket_id']);				// 8/16/08
        show_ticket($row['ticket_id']);
        }
    else if ($get_action == 'edit') {		//get and show action to update
        $query = "SELECT *, UNIX_TIMESTAMP(date) AS `date` FROM `$GLOBALS[mysql_prefix]patient` WHERE id='$_GET[id]' LIMIT 1";	// 8/11/08
        $result = mysql_query($query) or do_error($query,mysql_error(), basename( __FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_assoc($result));
//		dump($row);
//		dump(stripslashes($row['description']));
?>
        <FONT CLASS="header"><?php print gettext('Edit') . " " . get_text("Patient") . " " . gettext('Record');?></FONT><BR /><BR />
        <FORM METHOD='post' NAME='patientEd' onSubmit='return validate(document.patientEd);' ACTION="patient.php?id=<?php print $_GET['id'];?>&ticket_id=<?php print $_GET['ticket_id'];?>&action=update"><TABLE BORDER="0">

        <TR CLASS='even' ><TD><B><?php print get_text("Patient ID");?>: <font color='red' size='-1'>*</font></B></TD><TD><INPUT TYPE="text" NAME="frm_name" value="<?php print $row['name'];?>" size="32"></TD></TR>
<?php
    $checks = array("", "", "", "", "");		// gender checks
    $row_gender = ($row['gender'] != 0) ? $row['gender'] : 4;	//	7/12/13
    $checks[intval($row_gender)] = "CHECKED";	//	7/12/13

    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
    $result = mysql_query($query);
    if (@mysql_num_rows($result) > 0) {
        $ins_sel_str = "<SELECT CLASS='sit' name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";

        while ($row_ins = stripslashes_deep(mysql_fetch_assoc($result))) {
            $sel = (($row['insurance_id'] != 0) && (intval($row['insurance_id']) == intval($row_ins['id'])))? "SELECTED": "";	//	7/12/13
            $ins_sel_str .= "\t\t\t<OPTION VALUE={$row_ins['id']} {$sel}>{$row_ins['ins_value']}</OPTION>\n";
            }		// end while()
        $ins_sel_str .= "</SELECT>\n";
?>
        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
            <TD><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='<?php print $row['fullname'];?>' SIZE = '64' /></TD></TR>
        <TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
            <TD><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='<?php print $row['dob'];?>' SIZE = '24' /></TD></TR>
        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $gender;?>:  <font color='red' size='-1'>*</font></B>&nbsp;&nbsp;</TD>
            <TD>
                &nbsp;&nbsp;
                <?php print gettext('M');?>&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[1];?> />
                &nbsp;&nbsp;<?php print gettext('F');?>&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[2];?> />
                &nbsp;&nbsp;<?php print gettext('T');?>&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[3];?>/>
                &nbsp;&nbsp;<?php print gettext('U');?>&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' <?php echo $checks[4];?>/>
            </TD></TR>
        <TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
            <TD><?php echo $ins_sel_str;?></TD></TR>
        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $facilitycontact;?>: &nbsp;&nbsp;</TD>
            <TD><INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='<?php print $row['facility_contact'];?>' SIZE = '64' /></TD></TR>
<?php
        }		// end 	if($num_rows>0)
?>
        <TR CLASS='odd'  VALIGN='top'><TD><B><?php print gettext('Description');?>:</B> <font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="8" COLS="45" NAME="frm_description" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD></TR>
        <TR VALIGN = 'TOP' CLASS='odd'>		<!-- 11/15/10 -->
            <TD ALIGN='right' CLASS="td_label"><?php print gettext('Signal');?>: </TD><TD>

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
<?php
            print "\n<TR CLASS='even'><TD CLASS='td_label'>" . gettext('As of') . ":</TD><TD>";
            print  generate_date_dropdown("asof",$row['date'], TRUE);
            print "&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientEd);'></TD></TR>\n";

?>

        <TR CLASS='odd' ><TD></TD><TD ALIGN='center'><INPUT TYPE="button" VALUE="<?php print gettext('Cancel');?>" onClick="do_cancel();"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <INPUT TYPE="Reset" VALUE="<?php print gettext('Reset');?>"  onClick = "do_lock(this.form); this.form.reset();"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <INPUT TYPE="Submit" VALUE="<?php print gettext('Submit');?>"/></TD></TR>
        </TABLE><BR />
            <INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = <?php print $row['gender'];?> />
            <INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = <?php print $row['insurance_id'];?> />
        </FORM>

<?php
        }
    else {
        $user_level = is_super() ? 9999 : $_SESSION['user_id'];
        $regions_inuse = get_regions_inuse($user_level);	//	5/4/11
        $group = get_regions_inuse_numbers($user_level);	//	5/4/11

        $query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 4/13/11
        $result = mysql_query($query);	// 4/13/11
        $al_groups = array();
        $al_names = "";
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	// 4/13/11
            $al_groups[] = $row['group'];
            if (!(is_super())) {
                $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 4/13/11
                $result2 = mysql_query($query2);	// 4/13/11
                while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {	// 4/13/11
                    $al_names .= $row2['group_name'] . ", ";
                    }
                } else {
                    $al_names = "ALL. Superadmin Level";
                }
            }

        if (isset($_SESSION['viewed_groups'])) {	//	5/4/11
            $curr_viewed= explode(",",$_SESSION['viewed_groups']);
            } else {
            $curr_viewed = $al_groups;
            }

        $curr_names="";	//	5/4/11
        $z=0;	//	5/4/11
        foreach ($curr_viewed as $grp_id) {	//	5/4/11
            $counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
            $curr_names .= get_groupname($grp_id);
            $curr_names .= $counter;
            $z++;
            }

        $regs_string = "<FONT SIZE='-1'>Showing " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	5/4/11

        if (!isset($curr_viewed)) {
            if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
                $where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
                } else {
                $x=0;	//	6/10/11
                $where2 = "WHERE (";	//	6/10/11
                foreach ($al_groups as $grp) {	//	6/10/11
                    $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
                    $where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
                    $where2 .= $where3;
                    $x++;
                    }
                $where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
                }
            } else {
            if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
                $where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
                } else {
                $x=0;	//	6/10/11
                $where2 = "WHERE (";	//	6/10/11
                foreach ($curr_viewed as $grp) {	//	6/10/11
                    $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
                    $where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
                    $where2 .= $where3;
                    $x++;
                    }
                $where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
                }
            }

        $query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`
            LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
            $where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
        $result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
        $pulldown = '<option value = 0 selected>Select</option>\n'; 	// 3/18/10
            while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
                $pulldown .= "<option value=\"{$row_fc['id']}\">" . shorten($row_fc['name'], 20) . "</option>\n";
                }
?>
        <TABLE BORDER="0">
        <TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print gettext('Add') . " " . get_text("Patient") . gettext('Record');?></FONT></TD></TR>	<!-- 5/4/11 -->
        <TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>				<!-- 5/4/11 -->
        <FORM METHOD="post" NAME='patientAdd' onSubmit='return validate(document.patientAdd);'  ACTION="patient.php?ticket_id=<?php print $_GET['ticket_id'];?>&action=add">
        <TR CLASS='even'><TD class='td_label'><B><?php print get_text("Patient ID");?>:</B> <font color='red' size='-1'>*</font></TD><TD><INPUT TYPE="text" NAME="frm_name" value="" size="32"/></TD></TR>
<?php

    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]insurance` ORDER BY `sort_order` ASC, `ins_value` ASC";
    $result = mysql_query($query);
    if (@mysql_num_rows($result) > 0) {
        $ins_sel_str = "<SELECT name='frm_insurance' onChange = 'this.form.frm_ins_id.value = this.options[this.selectedIndex].value;'>\n";
        $ins_sel_str .= "\t\t\t<OPTION VALUE=0 SELECTED >Select</OPTION>\n";		// 7/27/11

        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $ins_sel_str .= "\t\t\t<OPTION VALUE={$row['id']}>{$row['ins_value']}</OPTION>\n";
            }		// end while()
        $ins_sel_str .= "</SELECT>\n";
?>
        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $fullname;?>: &nbsp;&nbsp;</TD>
            <TD><INPUT TYPE = 'text' NAME = 'frm_fullname' VALUE='' SIZE = '64' /></TD></TR>
        <TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $dateofbirth;?>: &nbsp;&nbsp;</TD>
            <TD><INPUT TYPE = 'text' NAME = 'frm_dob' VALUE='' SIZE = '24' /></TD></TR>
        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php echo $gender;?>:  <font color='red' size='-1'>*</font></B>&nbsp;&nbsp;</TD>
            <TD class='td_label'>
                &nbsp;&nbsp;M&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 1 onClick = 'this.form.frm_gender_val.value=this.value;' />
                &nbsp;&nbsp;F&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 2 onClick = 'this.form.frm_gender_val.value=this.value;' />
                &nbsp;&nbsp;T&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 3 onClick = 'this.form.frm_gender_val.value=this.value;' />
                &nbsp;&nbsp;U&nbsp;&raquo;&nbsp;<input type = radio name = 'frm_gender' value = 4 onClick = 'this.form.frm_gender_val.value=this.value;' />
            </TD></TR>
        <TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><?php echo $insurance;?>: <font color='red' size='-1'>*</font></B> &nbsp;&nbsp;</TD>
            <TD CLASS='td_data'><?php echo $ins_sel_str;?></TD></TR>

        <TR CLASS='odd'>
            <TD CLASS="td_label"><?php print gettext('Facility');?>:</TD><TD COLSPAN='2' class='td_label'>
                <SELECT NAME="frm_facility_id"  tabindex=11 onChange="this.options[selectedIndex].value.trim();"><?php print $pulldown; ?></SELECT>&nbsp;&nbsp;&nbsp;
            <?php echo $facilitycontact;?>:&nbsp;&nbsp;<INPUT TYPE = 'text' NAME = 'frm_fac_cont' VALUE='' SIZE = '32' /></TD></TR>
<?php
        }		// end 	if($num_rows>0)
?>

        <TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
            <TD ALIGN='right' CLASS="td_label"><?php print gettext('Signal');?>: </TD><TD>

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

        <TR CLASS='even' ><TD class='td_label'><B><?php print gettext('Description');?>: </B><font color='red' size='-1'>*</font></TD><TD><TEXTAREA ROWS="6" COLS="62" NAME="frm_description" WRAP="virtual"></TEXTAREA></TD></TR> <!-- 10/19/08 -->

        <TR CLASS='odd' VALIGN='bottom'><TD CLASS="td_label"><?php print gettext('As of');?>: &nbsp;&nbsp;</TD><TD><?php print generate_date_dropdown('asof',0,TRUE);?>&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock' border=0 src='unlock.png' STYLE='vertical-align: middle' onClick = 'do_unlock(document.patientAdd);'></TD></TR>

        <TR CLASS='odd'><TD></TD><TD><INPUT TYPE="button" VALUE="<?php print gettext('Cancel');?>"  onClick="do_cancel();"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <INPUT TYPE="Reset" VALUE="<?php print gettext('Reset');?>" onClick = "do_reset(this.form);"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <INPUT TYPE="button" VALUE="<?php print gettext('Next');?>" onclick = "validate(this.form);"/></TD></TR>
        </TABLE><BR />
            <INPUT TYPE = 'hidden' NAME = 'frm_ins_id' VALUE = 0 />
            <INPUT TYPE = 'hidden' NAME = 'frm_gender_val' VALUE = 0 />
        </FORM>
<SCRIPT>
    $('region_flags').innerHTML = "<?php print $regs_string; ?>";			// 5/2/10
</SCRIPT>
<?php
        }
?>
<FORM NAME='can_Form' ACTION="main.php">
<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>"/>
</FORM>
</HTML>
