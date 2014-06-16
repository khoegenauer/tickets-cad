<?php
$version = "2.41K Beta - 06/12/14";
include'./incs/error_reporting.php';

if (!(file_exists("./incs/mysql.inc.php")))
{
    print gettext("This appears to be a new Tickets installation; file 'mysql.inc.inc' is absent. Please run <a href=\"install.php\">install.php</a> with valid database configuration information.");
    exit();
}

require_once './incs/functions.inc.php';


$cb_per_line = 22;
$cb_fixed_part = 60;
$cb_min = 96;
$cb_max = 300;


include './incs/count_responders.php';


function do_insert_day_colors($name,$value) {			//	3/15/11
    $query = "INSERT INTO `$GLOBALS[mysql_prefix]css_day` (name,value) VALUES('$name','$value')";
    $result = mysql_query($query) or die("DO_INSERT_DAY_COLORS($name,$value) " . gettext('failed, execution halted'));
    }

function do_insert_night_colors($name,$value) {			//	3/15/11
    $query = "INSERT INTO `$GLOBALS[mysql_prefix]css_night` (name,value) VALUES('$name','$value')";
    $result = mysql_query($query) or die("DO_INSERT_NIGHT_COLORS($name,$value) " . gettext('failed, execution halted'));
    }

if (!mysql_table_exists("css_day")) {			//	3/15/11
    $query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_day` (`id` bigint(8) NOT NULL auto_increment,`name` tinytext,`value` tinytext, PRIMARY KEY  (`id`),UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

    do_insert_day_colors('page_background', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('normal_text', '000000');			//	3/15/11
    do_insert_day_colors('header_text', '000000');			//	3/15/11
    do_insert_day_colors('header_background', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('titlebar_text', '000000');			//	3/15/11
    do_insert_day_colors('links', '000099');			//	3/15/11
    do_insert_day_colors('other_text', '000000');			//	3/15/11
    do_insert_day_colors('legend', '000000');			//	3/15/11
    do_insert_day_colors('row_light', 'DEE3E7');			//	3/15/11
    do_insert_day_colors('row_light_text', '000000');			//	3/15/11
    do_insert_day_colors('row_dark', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('row_dark_text', '000000');			//	3/15/11
    do_insert_day_colors('row_plain', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('row_plain_text', '000000');			//	3/15/11
    do_insert_day_colors('row_heading_background', '707070');			//	3/15/11
    do_insert_day_colors('row_heading_text', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('row_spacer', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('form_input_background', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('form_input_text', '000000');			//	3/15/11
    do_insert_day_colors('select_menu_background', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('select_menu_text', '000000');			//	3/15/11
    do_insert_day_colors('label_text', '000000');			//	3/15/11
} // end if !table_exists css_day

if (!mysql_table_exists("css_night")) {			//	3/15/11
    $query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_night` (`id` bigint(8) NOT NULL auto_increment,`name` tinytext,`value` tinytext,PRIMARY KEY  (`id`),UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

    do_insert_night_colors('page_background', '121212');			//	3/15/11
    do_insert_night_colors('normal_text', 'DAEDE2');			//	3/15/11
    do_insert_night_colors('header_text', 'DAEDE2');			//	3/15/11
    do_insert_night_colors('header_background', '2B2B2B');			//	3/15/11
    do_insert_night_colors('titlebar_text', 'FFFFFF');			//	3/15/11
    do_insert_night_colors('links', '3F23F7');			//	3/15/11
    do_insert_night_colors('other_text', 'FFFFFF');			//	3/15/11
    do_insert_night_colors('legend', 'ECFC05');			//	3/15/11
    do_insert_night_colors('row_light', 'BEC3C7');			//	3/15/11
    do_insert_night_colors('row_light_text', '04043D');			//	3/15/11
    do_insert_night_colors('row_dark', '9E9E9E');			//	3/15/11
    do_insert_night_colors('row_dark_text', '000000');			//	3/15/11
    do_insert_night_colors('row_plain', 'A3A3A3');			//	3/15/11
    do_insert_night_colors('row_plain_text', '000000');			//	3/15/11
    do_insert_night_colors('row_heading_background', '262626');			//	3/15/11
    do_insert_night_colors('row_heading_text', 'F0F0F0');			//	3/15/11
    do_insert_night_colors('row_spacer', 'F2E3F2');			//	3/15/11
    do_insert_night_colors('form_input_background', 'B5B5B5');			//	3/15/11
    do_insert_night_colors('form_input_text', '212422');			//	3/15/11
    do_insert_night_colors('select_menu_background', 'B5B5B5');			//	3/15/11
    do_insert_night_colors('select_menu_text', '151716');			//	3/15/11
    do_insert_night_colors('label_text', '000000');			//	3/15/11
} // end if !table_exists css_night


function do_caption($temp, $repl="") { 				// adds a 'captions' table entry - 12/4/10
    if ($repl == "") { $repl = $temp; }
    $caption = quote_smart($temp);
    $repl = quote_smart($repl);
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `capt` = $caption LIMIT 1;";	// 11/30/10
    $result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_affected_rows()==0) {
        $query = "INSERT INTO `$GLOBALS[mysql_prefix]captions` ( `capt`, `repl`) VALUES ( $caption, $repl);";
        $result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        }

    return;
    }

function do_setting($which, $what) {				// 7/7/09
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";		// 5/25/09
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_affected_rows()==0) {
        $query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` ( `id` , `name` , `value` ) VALUES (NULL , '$which', '$what');";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        }
    unset ($result);

    return TRUE;
    }				// end function do_setting ()

function do_msg_setting($which, $what) {				// 5/25/13
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings` WHERE `name`= '$which' LIMIT 1";		// 5/25/09
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_affected_rows()==0) {
        $query = "INSERT INTO `$GLOBALS[mysql_prefix]msg_settings` ( `id` , `name` , `value` ) VALUES (NULL , '$which', '$what');";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        }
    unset ($result);

    return TRUE;
    }				// end function do_msg_setting ()


function update_setting($which, $what) {		//	3/15/11
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_affected_rows()!=0) {
        $query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '$which'";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        }
    unset ($result);

    return TRUE;
    }				// end function update_setting ()

function update_msg_settings($which, $what) {		//	3/15/11
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]msg_settings` WHERE `name`= '$which' LIMIT 1";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_affected_rows()!=0) {
        $query = "UPDATE `$GLOBALS[mysql_prefix]msg_settings` SET `value`= '$what' WHERE `name` = '$which'";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        }
    unset ($result);

    return TRUE;
    }				// end function update_msg_settings ()

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());

    return ((float) $usec + (float) $sec);
    }

$old_version = get_variable('_version');

if (!($version == $old_version))
{		// current? - 6/6/2013  ==================================================
  include './inc/version_update.inc'
}		// end (!($version ==...) ==================================================

function update_disp_stat($which, $what, $old)
{
  $query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' AND `value` = '$old' LIMIT 1";
  $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
  if ((mysql_affected_rows())!=0)
  {
    $query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`= '$what' WHERE `name` = '$which'";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
  }
  unset ($result);

  return TRUE;
}				// end function update_disp_stat ()

update_disp_stat ('disp_stat','D/R/O/FE/FA/Clear','D/R/O/Clear');		// 10/26/11

if (!mysql_table_exists("region"))
{
  $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region` (`id` bigint(8) NOT NULL AUTO_INCREMENT,`group_name` varchar(60) NOT NULL,`category` int(2) DEFAULT NULL,`description` varchar(60) DEFAULT NULL,`owner` int(2) NOT NULL DEFAULT '1',`def_area_code` varchar(4) DEFAULT NULL,`def_city` varchar(20) DEFAULT NULL,`def_lat` double DEFAULT NULL,`def_lng` double DEFAULT NULL,`def_st` varchar(20) DEFAULT NULL,`def_zoom` int(2) NOT NULL DEFAULT '10',`boundary` int(4) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
  $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
  $query = "INSERT INTO `$GLOBALS[mysql_prefix]region` (`id`, `group_name`, `category`, `description`, `owner`, `def_area_code`, `def_city`, `def_lat`, `def_lng`, `def_st`, `def_zoom`, `boundary`) VALUES (0, 'General', 4, 'General - group 0', 1, '', '', NULL, NULL, '10', 10, 0);";
  $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
}

if (!table_exists("region_type")) {	//	6/10/11
        $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region_type` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(16) NOT NULL,
            `description` varchar(48) NOT NULL,
            `_on` datetime NOT NULL,
            `_from` varchar(16) NOT NULL,
            `_by` int(7) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11

        $query = "INSERT INTO `$GLOBALS[mysql_prefix]region_type` (`id`, `name`, `description`, `_on`, `_from`, `_by`) VALUES
            (1, 'EMS', 'Medical Services', '2011-06-17 14:21:39', '127.0.0.1', 1),
            (2, 'Security', 'Security Services', '2011-06-17 14:21:55', '127.0.0.1', 1),
            (3, 'Fire', 'Fire Services', '2011-06-17 14:22:10', '127.0.0.1', 1),
            (4, 'General', 'General Use', '2011-06-17 14:22:10', '127.0.0.1', 1);";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11
}

if (!table_exists("allocates")) {	//	6/10/11
        $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocates` (
            `id` bigint(8) NOT NULL auto_increment,
            `group` int(4) NOT NULL default '1',
            `type` tinyint(1) NOT NULL default '1',
            `al_as_of` datetime default NULL,
            `al_status` int(4) default NULL,
            `resource_id` int(4) default NULL,
            `sys_comments` varchar(64) default NULL,
            `user_id` int(4) NOT NULL default  '0',
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

        $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];
            $tick_stat = $row['status'];
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 1, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];	// 4/13/11
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 2, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];	// 4/13/11
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 3, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";	// 4/13/11
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 4, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }
}	//	End if "Allocates does not exist"

$temp = explode(" ", get_variable('_version'));
$disp_version = $temp[0];

if ((count_responders()== 0) && (get_variable('title_string') == "") && ((!empty($_GET)) && ($_GET['first_start'] == "yes"))) {	//	5/11/12 For quick start routine
    print '<BR /><BR /><BR /><B>' . gettext('Do you wish to use the Tickets Quick start routine?');
    print '<BR /><BR /><A style="cursor: pointer;" onClick="document.quick.submit();"><< ' . gettext('Yes Please') . ' >></A>&nbsp;&nbsp;&nbsp;<A style="cursor: pointer;" HREF="index.php"><< No just start Tickets >></A>';
    print "<FORM NAME='quick' METHOD='POST' ACTION='quick_start.php'>";
    print "<INPUT TYPE='hidden' NAME='run_quick' VALUE='yes'/></FORM>";
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <HEAD>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
    <META HTTP-EQUIV="Expires" CONTENT="0" />
    <META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
    <META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
    <META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
    <TITLE>Tickets <?php print $disp_version;?></TITLE>
    <LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>
    <link rel="shortcut icon" href="favicon.ico" />
</HEAD>

<?php			// 7/14/09
//	cache buster and logout from statistics module.
$_SESSION = array();	//	6/14/13
$noforward_string = "";
// Mobile redirect
if((!isset($_POST) || (!array_key_exists('noautoforward', $_POST))) && ((!isset($_SESSION)) || ((array_key_exists('noautoforward', $_SESSION)) && ($_SESSION['noautoforward'] == FALSE)))) {	//	1/30/14
	if(get_variable('use_responder_mobile') == "1") {	//	8/1/13
    $text = $_SERVER['HTTP_USER_AGENT'];
    $var[0] = 'Mozilla/4.';
    $var[1] = 'Mozilla/3.0';
    $var[2] = 'AvantGo';
    $var[3] = 'ProxiNet';
    $var[4] = 'Danger hiptop 1.0';
    $var[5] = 'DoCoMo/';
    $var[6] = 'Google CHTML Proxy/';
    $var[7] = 'UP.Browser/';
    $var[8] = 'SEMC-Browser/';
    $var[9] = 'J-PHONE/';
    $var[10] = 'PDXGW/';
    $var[11] = 'ASTEL/';
    $var[12] = 'Mozilla/1.22';
    $var[13] = 'Handspring';
    $var[14] = 'Windows CE';
    $var[15] = 'PPC';
    $var[16] = 'Mozilla/2.0';
    $var[17] = 'Blazer/';
    $var[18] = 'Palm';
    $var[19] = 'WebPro/';
    $var[20] = 'EPOC32-WTL/';
    $var[21] = 'Tungsten';
    $var[22] = 'Netfront/';
    $var[23] = 'Mobile Content Viewer/';
    $var[24] = 'PDA';
    $var[25] = 'MMP/2.0';
    $var[26] = 'Embedix/';
    $var[27] = 'Qtopia/';
    $var[28] = 'Xiino/';
    $var[29] = 'BlackBerry';
    $var[30] = 'Gecko/20031007';
    $var[31] = 'MOT-';
    $var[32] = 'UP.Link/';
    $var[33] = 'Smartphone';
    $var[34] = 'portalmmm/';
    $var[35] = 'Nokia';
    $var[36] = 'Symbian';
    $var[37] = 'AppleWebKit/413';
    $var[38] = 'UPG1 UP/';
    $var[39] = 'RegKing';
    $var[40] = 'STNC-WTL/';
    $var[41] = 'J2ME';
    $var[42] = 'Opera Mini/';
    $var[43] = 'SEC-';
    $var[44] = 'ReqwirelessWeb/';
    $var[45] = 'AU-MIC/';
    $var[46] = 'Sharp';
    $var[47] = 'SIE-';
    $var[48] = 'SonyEricsson';
    $var[49] = 'Elaine/';
    $var[50] = 'SAMSUNG-';
    $var[51] = 'Panasonic';
    $var[52] = 'Siemens';
    $var[53] = 'Sony';
    $var[54] = 'Verizon';
    $var[55] = 'Cingular';
    $var[56] = 'Sprint';
    $var[57] = 'AT&T;';
    $var[58] = 'Nextel';
    $var[59] = 'Pocket PC';
    $var[60] = 'T-Mobile';
    $var[61] = 'Orange';
    $var[62] = 'Casio';
    $var[63] = 'HTC';
    $var[64] = 'Motorola';
    $var[65] = 'Samsung';
    $var[66] = 'NEC';
    $var[67] = 'Mobi';

    $result = count($var);

    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $extra = 'rm/index.php';
    $url = "http://" . $host . $uri . "/" . $extra;

    for ($i=0;$i<$result;$i++) {
        $ausg = stristr($text, $var[$i]);
        if ((strlen($ausg)>0) && (!stristr($text, 'MSIE'))) {
            echo '<meta http-equiv="refresh" content="', 0, ';URL=', $url, '">';
            exit;
            }
        }
    //	End of Mobile redirect
    }
	} else {
	$noforward_string = "&noaf=1";	//	1/30/14
	}

if (isset($_POST['logout'])) {
    $buster = strval(rand()) . "&logout=1";
    } else {
    $buster = strval(rand());
    }
if (get_variable('call_board') == 2) {
?>
    <FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, 0 ,*" BORDER="<?php print get_variable('frameborder');?>" BORDERCOLOR="#ff0000">
    <FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
    <FRAME SRC='board.php?stuff=<?php print $buster;?>' ID = 'what' NAME='calls' SCROLLING='AUTO' />	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?><?php print $noforward_string;?>" NAME="main" />	<!-- 1/30/14 -->
<?php
    }
else  {
?>
    <FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, *" BORDER="<?php print get_variable('frameborder');?>">
    <FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?><?php print $noforward_string;?>" NAME="main" />	<!-- 1/30/14 -->
<?php
    }
?>
    <NOFRAMES>
    <BODY>
        <?php print gettext('Tickets requires a frames-capable browser.');?>
    </BODY>
    </NOFRAMES>
</FRAMESET>
</HTML>
<?php

    $query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
    $result = mysql_query($query);
?>
