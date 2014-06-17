<?php

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require_once 'incs/functions.inc.php';		//7/28/10
$evenodd = array ("even", "odd");	// CLASS names for alternating tbl row colors

function html_mail($to, $subject, $html_message, $from_address, $from_display_name='')
{
    $from = get_variable('email_from');
    $from = is_email($from)? $from : "info@ticketscad.org";
    $headers = "From: {$from_display_name}<{$from}>\n";

    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $temp = get_variable('email_reply_to');
    if (is_email($temp)) {
        $headers .= "Reply-To: {$temp}\r\n";
        }

    $temp = @mail($to, $subject, $html_message, $headers); // boolean
}

function template_213($do_form = TRUE)
{
    global $item;
	$out_str = "<!DOCTYPE html>
<HTML>
<HEAD>
    <META HTTP-EQUIV=\"CONTENT-TYPE\" CONTENT=\"text/html; charset=windows-1252\">
    <TITLE>ICS-213 " . gettext('GENERAL MESSAGE') . "</TITLE>
    <META NAME=\"CHANGEDBY\" CONTENT=\"Arnie Shore\">
    <META NAME=\"CHANGED\" CONTENT=\"20071223;14270000\">
    <META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">
    <META HTTP-EQUIV=\"Expires\" CONTENT=\"0\">
    <META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"NO-CACHE\">
    <META HTTP-EQUIV=\"Pragma\" CONTENT=\"NO-CACHE\">

    <STYLE TYPE=\"text/css\">--
    <!--
        @page { size: 8.5in 11in; margin: 0.5in }
        P { margin-bottom: 0.08in; direction: ltr; color: #000000; text-align: left; widows: 0; orphans: 0 }
        P.western { font-family: \"Arial, sans-serif; font-size: 10pt; so-language: en-US; margin-left: 0.01in; margin-top: 0.04in;}
        P.cjk { font-family: \"Times New Roman\", serif; font-size: 10pt; so-language: zxx }
        P.ctl { font-family: \"Times\", \"Times New Roman\", serif; font-size: 10pt; so-language: ar-SA }
        A.sdfootnotesym-western { font-size: 8pt }
        A.sdfootnotesym-cjk { font-size: 8pt }
    -->
    </STYLE>
<SCRIPT type=\"text/javascript\">
/**
 *
 */
    function validate(theForm) {						// form contents validation
        var errmsg='';
        if (theForm.f1.value.trim()=='') {errmsg+=\"" . gettext('TO is required.') . "\\n\";}
        if (theForm.f3.value.trim()=='') {errmsg+=\"" . gettext('FROM is required.') . "\\n\";}
        if (theForm.f5.value.trim()=='') {errmsg+=\"" . gettext('SUBJECT is required.') . "\\n\";}
        if (theForm.f8.value.trim()=='') {errmsg+=\"" . gettext('MESSAGE is required.') . "\\n\";}
        if (errmsg!='') {
            alert ('" . gettext('Please correct the following and re-submit') . ":\\n\\n' + errmsg);

            return false;
            }
        else {			// good to go!

            return true;
            }
        }				// end function validate(theForm)

</SCRIPT>

</HEAD>
<BODY LANG=\"en-US\" TEXT=\"#000000\" BGCOLOR=\"#ffffff\" DIR=\"LTR\">
<P CLASS=\"western\" ALIGN=LEFT STYLE=\"margin-bottom: 0in\">
    <TABLE DIR='LTR' BORDER=1 BORDERCOLOR='#000000' CELLPADDING=0 CELLSPACING=0 STYLE='width: 20.32cm;'>";
    if ($do_form) {
        $out_str .= "\n<FORM NAME = 'ics213_form' METHOD = 'post' ACTION = '" . basename(__FILE__) . "' >\n";
        $out_str .= "\n<INPUT TYPE = 'hidden' NAME = 'frm_add_str' VALUE = '{$_POST['frm_add_str']}'/>\n";
        $end_form = "\n</FORM>";
        }
    else {
        $end_form = "";
        }
//	dump($end_form);
    $out_str .= "<INPUT TYPE = 'hidden' NAME = 'step' VALUE = 2>
        <COL WIDTH=46*>
        <COL WIDTH=54*>
        <COL WIDTH=23*>
        <COL WIDTH=9*>
        <COL WIDTH=44*>
        <COL WIDTH=79*>
        <TR>
            <TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#f2f2f2\">
                <P CLASS=\"western\" ALIGN=CENTER >&nbsp;" . gettext('GENERAL MESSAGE') . "</FONT></P>
            </TD>
        </TR>
        <TR VALIGN=TOP>
            <TD COLSPAN=3 WIDTH=48% HEIGHT=30>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;<B>" . gettext('TO') . "</B></FONT></FONT><FONT SIZE=1 STYLE=\"font-size: 8pt\">:&nbsp;{$item[1]}</FONT></FONT></P>
            </TD>
            <TD COLSPAN=3 WIDTH=52%>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('POSITION') . ":&nbsp;{$item[2]}</FONT></FONT></P>
            </TD>
        </TR>
        <TR VALIGN=TOP>
            <TD COLSPAN=3 WIDTH=48% HEIGHT=30>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('FROM') . ":&nbsp;{$item[3]}
                    </FONT></FONT></P>
            </TD>
            <TD COLSPAN=3 WIDTH=52%>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('POSITION') . ":&nbsp;{$item[4]}</FONT></FONT></P>
            </TD>
        </TR>
        <TR VALIGN=TOP>
            <TD COLSPAN=3 WIDTH=48% HEIGHT=30>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('SUBJECT') . ":&nbsp;{$item[5]}
                    </FONT></FONT></P>
            </TD>
            <TD COLSPAN=2 WIDTH=28%>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('DATE') . ":&nbsp;{$item[6]}</FONT></FONT></P>
            </TD>
            <TD WIDTH=24%>
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('TIME') . ":&nbsp;{$item[7]}</FONT></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#e5e5e5\">
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('MESSAGE') . ":</FONT></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=6 WIDTH=100% HEIGHT=100 VALIGN=TOP>
                <P CLASS=\"western\" >
                {$item[8]}
                <BR>
                </P>
            </TD>
        </TR>
        <TR VALIGN=TOP>
            <TD COLSPAN=4 WIDTH=52% HEIGHT=27>
                <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\">
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('SIGNATURE') . ":&nbsp;{$item[9]}</FONT></FONT></P>
                <P CLASS=\"western\" >
                </P>
            </TD>
            <TD COLSPAN=2 WIDTH=48%>
                <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\">
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('POSITION') . ":&nbsp;{$item[10]}</FONT></FONT></P>
                <P CLASS=\"western\" >
                </P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=6 WIDTH=100% VALIGN=TOP BGCOLOR=\"#e5e5e5\">
                <P CLASS=\"western\" >
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('REPLY') . ":</FONT></FONT></P>
            </TD>
        </TR>
        <TR>
            <TD COLSPAN=6 WIDTH=100% HEIGHT=100 VALIGN=TOP>
                <P CLASS=\"western\" >
                {$item[11]}<BR>
                </P>
            </TD>
        </TR>
        <TR VALIGN=TOP>
            <TD WIDTH=30%>
                <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\">
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('DATE') . ":&nbsp;{$item[12]}</FONT></FONT></P>
                <P CLASS=\"western\" >
                </P>
            </TD>
            <TD WIDTH=20%>
                <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\">
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('TIME') . ":&nbsp;{$item[13]}</FONT></FONT></P>
                <P CLASS=\"western\" >
                </P>
            </TD>
            <TD COLSPAN=4 WIDTH=50%>
                <P CLASS=\"western\" STYLE=\"margin-left: 0.01in; margin-top: 0.04in; margin-bottom: 0.04in\">
                <FONT SIZE=1 STYLE=\"font-size: 8pt\">&nbsp;" . gettext('SIGNATURE/POSITION') . ":&nbsp;{$item[14]}</FONT></FONT></P>
                <P CLASS=\"western\" >
                </P>
            </TD>
        </TR>
    {$end_form}</TABLE><BR />";

    if ($do_form) {
        $out_str .= "<SPAN ID = 'do_form' ALIGN='center' STYLE = 'MARGIN-LEFT:250px;'>
            <INPUT TYPE = 'button' VALUE= '" . gettext('Submit') . "' onclick = \"if (validate(document.ics213_form)) {document.ics213_form.submit();}\" />
            <INPUT TYPE = 'reset' VALUE= '" . gettext('Reset') . "' STYLE = 'MARGIN-LEFT:40px;' onclick = \"document.ics213_form.reset();\"></SPAN><BR /><BR />";
			<INPUT TYPE = 'button' VALUE= '" . gettext('Cancel') . "' STYLE = 'MARGIN-LEFT:40px;' onclick = \"window.close();\"></SPAN><BR /><BR />";
        }				// end if ($do_form)
    $out_str .=  "</BODY></HTML>";

    return $out_str;
    }							// end function template_213 ()


$step = (array_key_exists ("step", $_POST))? $_POST['step']: 0 ;
switch ($step) {
    case 0:								/*  collect addresses */
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?php print LessExtension(basename(__FILE__));?> </TITLE>
<META NAME="Description" CONTENT="Email to units" />
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="6/13/09" />
<LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css" />	<!-- 3/15/11 -->
<script type="application/javascript">
/**
 *
 * @returns {unresolved}
 */
    String.prototype.trim = function () {
        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @returns {Array}
 */
    function $() {
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
 * @returns {Boolean}
 */
    function do_mail_str(in_action) {
        sep = "";
        for (i=0;i<document.mail_form.elements.length; i++) {
            if ((document.mail_form.elements[i].type =='checkbox') && (document.mail_form.elements[i].checked)) {		// frm_add_str
                document.mail_form.frm_add_str.value += sep + document.mail_form.elements[i].value;
                sep = "|";
                }
            }
        if (document.mail_form.frm_add_str.value.trim()=="") {
            alert ("Addressees required");

            return false;
            }
		document.mail_form.action = in_action;
        document.mail_form.submit();
		return true;
        }
/**
 *
 * @returns {undefined}
 */
    function do_clear() {
        for (i=0;i<document.mail_form.elements.length; i++) {
            if (document.mail_form.elements[i].type =='checkbox') {
                document.mail_form.elements[i].checked = false;
                }
            }		// end for ()
        $('clr_spn').style.display = "none";
        $('chk_spn').style.display = "block";
        }		// end function do_clear
/**
 *
 * @returns {undefined}
 */
    function do_check() {
        for (i=0;i<document.mail_form.elements.length; i++) {
            if (document.mail_form.elements[i].type =='checkbox') {
                document.mail_form.elements[i].checked = true;
                }
            }		// end for ()
        $('clr_spn').style.display = "block";
        $('chk_spn').style.display = "none";
        }		// end function do_clear

    </SCRIPT>
    </HEAD>
    <BODY><CENTER><BR /><BR />
<?php
	$i=0;		// 3/6/2014
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]contacts`
        ORDER BY `organization` ASC,`name` ASC" ;
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

    if (mysql_affected_rows()>0) {

/**
 * do_row
 * Insert description here
 *
 * @param $i
 * @param $addr
 * @param $name
 * @param $org
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_row($i, $addr, $name, $org) {
        global $evenodd;
        $return_str = "<TR CLASS= '{$evenodd[($i)%2]}'>";
        $js_i = $i+1;
        $return_str .= "\t\t<TD>&nbsp;<INPUT TYPE='checkbox' CHECKED NAME='cb{$js_i}' VALUE='{$addr}'>";
        $return_str .= "&nbsp;{$addr} / {$name} / {$org}</TD></TR>\n";

        return $return_str;
        }				// end function do_row()

?>
    <P>
        <TABLE ALIGN='center'>
        <TR CLASS = 'even'><TH><?php print gettext('ICS Form to Contacts');?></TH></TR>
        <TR CLASS = 'odd'><TD ALIGN = 'center'><BR />
            <SPAN ID='clr_spn' STYLE = 'display:block' onClick = 'do_clear();'>&raquo; <U><?php print gettext('Un-check all');?></U></SPAN>
            <SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check();'>&raquo; <U><?php print gettext('Check all');?></U></SPAN><BR />
        </TD></TR>

        <FORM NAME='mail_form' METHOD='post' ACTION='void(0)'>
        <INPUT TYPE='hidden' NAME='step' VALUE='1'/>
        <INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''/>	<!-- for pipe-delim'd addr string -->
<?php
    while ($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)) {
																				// count valid addresses
        if (is_email($row['email'])) { echo do_row($i, $row['email'], $row['name'], $row['organization']);$i++;}
        if (is_email($row['mobile'])) { echo do_row($i, $row['mobile'], $row['name'], $row['organization']);$i++;}
        if (is_email($row['other'])) { echo do_row($i, $row['other'], $row['name'], $row['organization']);$i++;}
        }		// end while()

?>
        <TR CLASS='<?php print $evenodd[($i)%2]; ?>'><TD ALIGN='center' COLSPAN=3><BR /><BR />&nbsp;
<!--
			<INPUT TYPE='button' 	VALUE='ICS205' 		onClick = "do_205();" />
			<INPUT TYPE='button' 	VALUE='ICS205-A' 	onClick = "do_205a();"  style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS213' 		onClick = "do_213();"   style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS214' 		onClick = "do_214();"   style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS205' 		onClick = "this.form.action = 'ics205.php'; this.form.submit();" />
			<INPUT TYPE='button' 	VALUE='ICS205-A' 	onClick = "this.form.action = 'ics205a.php'; this.form.submit();"  style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS213' 		onClick = "this.form.action = 'ics213.php'; this.form.submit();"   style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS214' 		onClick = "this.form.action = 'ics214.php'; this.form.submit();"   style = "margin-left:20px;" />
3/8/2014
-->
			<INPUT TYPE='button' 	VALUE='ICS205' 		onClick = "do_mail_str('ics205.php');" />
			<INPUT TYPE='button' 	VALUE='ICS205-A' 	onClick = "do_mail_str('ics205a.php');"  style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS213' 		onClick = "do_mail_str('ics213.php');"   style = "margin-left:20px;" />
			<INPUT TYPE='button' 	VALUE='ICS214' 		onClick = "do_mail_str('ics214.php');"   style = "margin-left:20px;" />
			<p>
			<INPUT TYPE='reset' 	VALUE='<?php print gettext('Reset');?>' />
			<INPUT TYPE='button' 	VALUE='<?php print gettext('Cancel');?>' onClick = 'window.close();' style = "margin-left:60px;" />
			</p>
            </TD></TR>
            </TABLE></FORM>

<?php
            }		// end if(mysql_affected_rows()>0)
        if (($i==0) || (mysql_affected_rows()==0)) {
?>
    <H3><?php print gettext('No Contact e-mail addresses!');?></H3><BR /><BR />
    <INPUT TYPE='button' VALUE='<?php print gettext('Cancel');?>' onClick = 'window.close();'/><BR /><BR />
<?php
            }
        else {
            }
// ------------------------------
        break;		// end case 0
    case 1:								/* present form to user */
        $query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = {$_SESSION['user_id']} LIMIT 1";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_assoc($result));

        $the_date = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
        $the_time =  date( "H:i",(time()- (intval(get_variable('delta_mins')*60)) ));
        $the_from = "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";
        $temp = $row['name_l'].$row['name_f'].$row['name_mi'];
        $the_from = (empty($temp))? "" : "{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";

//		$the_from = (empty($row['name_l'].$row['name_f'].$row['name_mi']))? "" :
//								"{$row['name_l']}, {$row['name_f']} {$row['name_mi']}";

/**
 * in_str
 * Insert description here
 *
 * @param $name
 * @param $size
 * @param $tabindex
 * @param $data
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function in_str($name, $size, $tabindex, $data = "") {
            return "<input type=text id='f{$name}'  name='f{$name}' size={$size} maxlength={$size} value='{$data}' tabindex={$tabindex} />";
            }

/**
 * in_text
 * Insert description here
 *
 * @param $name
 * @param $cols
 * @param $rows
 * @param $tabindex
 * @param $data
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function in_text($name, $cols, $rows, $tabindex, $data = "") {
            return "<textarea id='f{$name}'  name='f{$name}' cols={$cols} rows={$rows} tabindex={$tabindex}>{$data}</textarea>";
            }

        $item[1] =  in_str  (1, 36, 1); // $name, $size, $tabindex
        $item[2] =  in_str  (2, 36, 2);
        $item[3] =  in_str  (3, 36, 3, $the_from);
        $item[4] =  in_str  (4, 36, 4);
        $item[5] =  in_str  (5, 36, 5);
        $item[6] =  in_str  (6, 16, 6, $the_date);
        $item[7] =  in_str  (7, 12, 7, $the_time);
        $item[8] =  in_text (8, 90, 4, 8); // $name, $cols, $rows, $tabindex
//		$item[8] =  $_POST['frm_add_str']; // $name, $cols, $rows, $tabindex
        $item[9] =  in_str  (9, 36, 9, $the_from);
        $item[10] = in_str  (10, 32, 10);
        $item[11] = in_text (11, 90, 4, 11);
        $item[12] = in_str  (12, 16, 12);
        $item[13] = in_str  (13, 8, 13);
        $item[14] = in_str  (14, 34, 14);

        echo template_213(TRUE);
        break;		// end case 1

    case 2:								/*  process form and address data */

//		dump($_POST);
/*
        $item[1] =  quote_smart(trim($_POST['f1']));
        $item[2] =  quote_smart(trim($_POST['f2']));
        $item[3] =  quote_smart(trim($_POST['f3']));
        $item[4] =  quote_smart(trim($_POST['f4']));
        $item[5] =  quote_smart(trim($_POST['f5']));
        $item[6] =  quote_smart(trim($_POST['f6']));
        $item[7] =  quote_smart(trim($_POST['f7']));
        $item[8] =  quote_smart(trim($_POST['f8']));
        $item[9] =  quote_smart(trim($_POST['f9']));
        $item[10] = quote_smart(trim($_POST['f10']));
        $item[11] = quote_smart(trim($_POST['f11']));
        $item[12] = quote_smart(trim($_POST['f12']));
        $item[13] = quote_smart(trim($_POST['f13']));
        $item[14] = quote_smart(trim($_POST['f14']));

        $query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]ics213` (
             `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `f10`, `f11`, `f12`, `f13`, `f14`
            ) VALUES (
             {$item[1]}, {$item[2]}, {$item[3]}, {$item[4]}, {$item[5]}, {$item[6]}, {$item[7]}, {$item[8]}, {$item[9]}, {$item[10]}, {$item[11]}, {$item[12]}, {$item[13]}, {$item[14]}
            )";
        $result	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
*/
        $item[1] =  trim($_POST['f1']); 	// to
        $item[2] =  trim($_POST['f2']); 	// position
        $item[3] =  trim($_POST['f3']); 	// from
        $item[4] =  trim($_POST['f4']); 	// position
        $item[5] =  trim($_POST['f5']); 	// subject
        $item[6] =  trim($_POST['f6']); 	// date
        $item[7] =  trim($_POST['f7']); 	// time
        $item[8] =  trim($_POST['f8']); 	// message
        $item[9] =  trim($_POST['f9']); 	// signature
        $item[10] = trim($_POST['f10']); 	// position
        $item[11] = trim($_POST['f11']); 	// reply
        $item[12] = trim($_POST['f12']); 	// date
        $item[13] = trim($_POST['f13']); 	// time
        $item[14] = trim($_POST['f14']); 	// signature/position

        $html_message = template_213(FALSE);

        $to_array = explode ("|", $_POST['frm_add_str']);
        $to = $sep = "";
        for ($i=0; $i < count($to_array); $i++) {
            $to .= "{$sep}{$to_array[$i]}";
            $sep = ",";
            }		// end for ()
        $subject ="ICS-213 Message - {$item[5]}";		// subject, per form data
        $temp = get_variable('email_from');
        $from_address = (is_email($temp))? $temp: "ticketscad.org";
        $from_display_name=get_variable('title_string');
        $temp = shorten(strip_tags(get_variable('title_string')), 30);
        $from_display_name = str_replace ( "'", "", $temp);
        $result = html_mail ($to, $subject, $html_message, $from_address, $from_display_name);

        do_log($GLOBALS['LOG_ICS213_MESSAGE_SEND'], 0, 0, $item[5], 0, 0,0);	// subject line as info column
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>ICS-213 <?php print gettext('Mail sent');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<SCRIPT>
</SCRIPT>
</HEAD>
<BODY onLoad = "setTimeout('window.close()',2500);">
<DIV style = 'margin-left:400px; margin-top100px;'><H2>ICS-213 <?php print gettext('MAIL SENT - window closing');?> ... </H2></DIV>
</BODY>
</HTML>

<?php

        break;							/* end process form and address data */

    default:							/* error????  */
        echo gettext('Error') . gettext('Error') . gettext('Error') . __LINE__;
    }				// end switch
