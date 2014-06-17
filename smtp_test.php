<?php
include'./incs/error_reporting.php';

@session_start();
require_once($_SESSION['fip']);		//7/28/10
//dump($_POST);

/* enter known good values here - as a convemience */

$temp = get_variable('smtp_acct');
//if (!(empty($temp))) {
    $temp_ar = explode("/",$temp);
    $server = 		isset($temp_ar[0])? $temp_ar[0]:"";
    $port =  		isset($temp_ar[1])? $temp_ar[1]:"";
    $security =  	isset($temp_ar[2])? $temp_ar[2]:""; // 7/5/10
    $user_acct =  	isset($temp_ar[3])? $temp_ar[3]:"";
    $pass =  		isset($temp_ar[4])? $temp_ar[4]:"";
//	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print gettext('SMTP Mail Transfer Test');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>	<!-- 3/15/11 -->
<STYLE>
BODY { BACKGROUND-COLOR: #EFEFEF; FONT-WEIGHT: normal; FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
</STYLE>
<SCRIPT>
/**
 *
 * @param {type} field
 * @returns {unresolved}
 */
    function validate_email(field) {
        apos=field.indexOf("@");
        dotpos=field.lastIndexOf(".");

        return (!(apos<1||dotpos-apos<2));
        }				// end function validate_email()
    String.prototype.trim = function () {				// 10/19/08

        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @returns {Boolean}
 */
    function validate() {
    //	alert(38);
        errormsg="";
        if (document.test.server.value.trim()=="") {errormsg += "\tServer name is required\n";}
        if (isNaN(parseInt(document.test.port.value))) {errormsg += "\tNumeric port no. is required\n";}
        if (!(validate_email(document.test.from_addr.value))) {errormsg += "\t\'From address\' is invalid\n";}
        if (!(validate_email(document.test.to_addr.value))) {errormsg += "\t\'To address\' is invalid\n";}
        if (errormsg=="") {document.test.submit(); }
        else {
            alert ("<?php print gettext('Please correct the following errors');?>\n"+ errormsg);

            return false;
            }
        }
</SCRIPT>
</HEAD>
<BODY>

<?php
if (empty($_POST)) {
    $temp = trim(get_variable('smtp_acct'));
    $caption = (empty($temp))? "" : "(your current setting is '{$temp}')";
?>

<FORM NAME = "test" METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>

<TABLE ALIGN = 'center' WIDTH='640px'>
<TR CLASS="even"><TD COLSPAN=2 ALIGN='center'><BR /><H3><?php print gettext('SMTP Mail Test');?></H3><?php print $caption;?></TD></TR>
<TR CLASS="odd"><TD></TD><TD><BR /><H4><?php print gettext('Enter values  from your email account');?></H4></TD></TR>
<TR CLASS="even"><TD><?php print gettext('Server name');?>: </TD>			<TD><INPUT TYPE = 'text' NAME='server' 		VALUE='<?php print $server;?>' SIZE= 24><I> ex: outgoing.verizon.net</TD></TR>
<TR CLASS="odd"><TD><?php print gettext('Port');?>:  </TD>				<TD><INPUT TYPE = 'text' NAME='port'  		VALUE='<?php print $port;?>' SIZE= 4><I> ex:587</TD></TR>
<TR CLASS="even"><TD><?php print gettext('Security');?>: </TD>	<TD>
                        <SPAN STYLE = 'margin-left: 24px;'><?php print gettext('None');?> &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = 'none' CHECKED></SPAN>
                        <SPAN STYLE = 'margin-left: 24px;'><?php print gettext('SSL');?> &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = 'ssl' ></SPAN>
                        <SPAN STYLE = 'margin-left: 24px;'><?php print gettext('TLS');?> &raquo; <INPUT TYPE = 'radio' NAME = 'security' VALUE = 'tls' ></SPAN>
        <SPAN STYLE = 'margin-left: 24px;'><I> <?php print gettext('ISP dependent (Gmail requires a secure transport)');?></SPAN></TD></TR> <!-- 7/5/10 -->

<TR CLASS="odd"><TD><?php print gettext('User account');?>: </TD>			<TD><INPUT TYPE = 'text' NAME='user_acct'  	VALUE='<?php print $user_acct;?>' SIZE= 24><I> ex:ashore3</TD></TR>
<TR CLASS="even"><TD><?php print gettext('Password');?>:  </TD>			<TD><INPUT TYPE = 'text' NAME='pass'  		VALUE='<?php print $pass;?>' SIZE= 12><I> ex:whatever</TD></TR>
</TABLE>
<BR />
<TABLE ALIGN = 'center' WIDTH='640px'>
<TR CLASS="odd"><TD></TD><TD><H4><?php print gettext('Enter your test message');?></H4></TD></TR>
<TR CLASS="even"><TD><?php print gettext('From name');?>: </TD>			<TD><INPUT TYPE = 'text' NAME='from_user'	VALUE='' SIZE= 24><I> <?php print gettext('anything');?></I></TD></TR>
<TR CLASS="odd"><TD><?php print gettext('From address');?>: </TD>			<TD><INPUT TYPE = 'text' NAME='from_addr'	VALUE='' SIZE= 24><I> <?php print gettext('a <B>valid</B> email address (your ISP\'s restriction may apply)');?></I></TD></TR>
<TR CLASS="even"><TD><?php print gettext('To address');?>: </TD>			<TD><INPUT TYPE = 'text' NAME='to_addr'		VALUE='' SIZE= 24><I> <?php print gettext('a <B>valid</B> email address');?></I></TD></TR>
<TR CLASS="odd"><TD><?php print gettext('Subject');?>:  </TD>				<TD><INPUT TYPE = 'text' NAME='subj'		VALUE='' SIZE= 24></TD></TR>
<TR CLASS="even" VALIGN='top'><TD><?php print gettext('Message');?>:  	</TD><TD><TEXTAREA NAME='msg' cols=40 rows=2></TEXTAREA><BR /></TD></TR>
<TR CLASS="odd"><TD></TD><TD><BR /><INPUT TYPE = reset VALUE='<?php print gettext('Reset');?>' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE = 'button' VALUE='<?php print gettext('Send');?>' onClick = "validate()" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE = 'button' VALUE='<?php print gettext('Cancel');?>' onClick = "self.close();"/></TD></TR>
</TABLE>
</FORM>
</BODY>
</HTML>

<?php
    }				// end if (empty($_POST))

else {
    $errors = FALSE;
    @set_time_limit(5);		// certain errors take longer

/**
 * myErrorHandler
 * Insert description here
 *
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function myErrorHandler($errno, $errstr, $errfile, $errline) {
        global $errors, $istest;
        if ($errno == 2048) { return;}
        if ($istest) {
            dump(__LINE__);
            dump($errno);
            dump($errstr);
            }
        $errors = TRUE;

        return FALSE;
        }

    require_once 'lib/swift_required.php';
    error_reporting(E_ALL);
    $old_error_handler = set_error_handler("myErrorHandler");

    $server = trim($_POST['server']);
    $fp = gethostbyname($server);
//	$fp = @fsockopen($server, trim($_POST['port']), $errno, $errstr, $timeout);
//	$fp = @fsockopen("outgoing.verizon.net", 587);
    if ($fp) {

        //Create the Transport the call setUsername() and setPassword()
        $transport = Swift_SmtpTransport::newInstance(trim($_POST['server']) , trim($_POST['port']) , trim($_POST['security']))
          ->setUsername(trim($_POST['user_acct']))
          ->setPassword(trim($_POST['pass']))
          ;

        //Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        //Create a message
        $temp_ar = explode("@", trim($_POST['to_addr']));

        $message = Swift_Message::newInstance(trim($_POST['subj']))
          ->setFrom(array($_POST['from_addr'] => trim($_POST['from_user'])))
          ->setTo(array(trim($_POST['to_addr']) => trim($temp_ar[0])))
          ->setBody(trim($_POST['msg']))
          ;

        //    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
        //Send the message
    //	$result = $mailer->send($message, $failures);
        $failures="";
        $caption = gettext("Sent");
        if (!($mailer->send($message, $failures))) {
            $errs = "";
            dump ($failures);
            foreach ($failures as $value) {
                $errs .= $value . " ";
                }
            $caption = sprintf(gettext('Mail to \'%s\' failed'), $errs);
            }
        else {
            if ($errors) {
                $caption = gettext('Mail delivery failed');
                }
            }
        }
    else {
        if ($istest) {
            dump($server);
            dump($fp);
            }
        $caption = gettext('Failed - server name error');
        }

?>
<CENTER><BR /><BR /><H2> <?php echo $caption; ?></H2><BR /><BR /></CENTER>
<FORM NAME='mail' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
<INPUT TYPE='button' VALUE = "<?php print gettext('Again');?>" onClick = "this.form.submit();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE='button' VALUE = "<?php print gettext('Close');?>" onClick = "self.close();" />
</FORM>
</CENTER>
</BODY>
</HTML>

<?php
    }				// end else {}
