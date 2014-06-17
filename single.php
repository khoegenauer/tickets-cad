<?php
include'./incs/error_reporting.php';
@session_start();
require_once($_SESSION['fip']);		// 7/28/10
require_once($_SESSION['fmp']);		// 7/28/10, 8/10/10
    $query = "SELECT *,
        `problemstart` AS `problemstart`,
        `problemend` AS `problemend`,
        `booked_date` AS `booked_date`,
        `date` AS `date`,
        `$GLOBALS[mysql_prefix]ticket`.`updated` AS updated,
         `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
         `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
         `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
         `$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
         `$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
         `$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
         `$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,
         `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
         `rf`.`name` AS `rec_fac_name`,
         `rf`.`lat` AS `rf_lat`,
         `rf`.`lng` AS `rf_lng`,
         `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
         `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`
        LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)
        LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
        LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`)
        WHERE `$GLOBALS[mysql_prefix]ticket`.`id`={$_GET['ticket_id']} LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    $row_ticket = stripslashes_deep(mysql_fetch_array($result));
    @session_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Incident Module');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
<LINK REL="StyleSheet" HREF="stylesheet.php" TYPE="text/css" />	<!-- 3/15/11 -->
</HEAD>
<BODY>
<?php
    $the_width = 600;
    if (!(empty($row_ticket))) {								// 4/30/10
        print do_ticket($row_ticket, $the_width, FALSE, FALSE);
        }
    else {
        print "<CENTER><H3>" . gettext('No data for Ticket #') . "{$_GET['ticket_id']} </H3>";
        }
?>
<BR /><CENTER>
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Finished');?>' onClick = 'self.close();' />
<?php if (!(is_guest())) { ?>
    <INPUT TYPE = 'button' STYLE = 'margin-left: 200px;' VALUE = '<?php print gettext('Edit');?>' onClick = 'window.opener.parent.frames["main"].location="edit.php?id=<?php print $_GET['ticket_id'];?>";' />
<?php } ?>
</CENTER>
</BODY></HTML>
