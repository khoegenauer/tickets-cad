<?php
/**
 * @package get_latest_id.php
 * @author John Doe <john.doe@example.com>
 * @since 2010-04-09
 * @version 2013-07-15
 */
/*
4/9/10 initial release
6/11/10 disabled unit_flag_2 setting
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
9/1/10 - fix  to error_reporting(E_ALL);
1/6/11 - json encode added
6/10/11 Added groups capability - restricts
2/10/12 added error handling
2/21/12 corrections two places to disambiguate 'id'
2/25/12 added act_id and pat_id to the returned array  -AS
5/29/2013 error handling upgrades several lines, notably error_out()
5/30/2013 relocated get_current() call to after json data return;	// update remotes position
6/23/2013 correction to obtain affected row count
7/15/13 Replaced mysql_affected_rows with mysql_num_rows
*/
error_reporting(E_ALL);
@session_start();
require_once './incs/functions.inc.php';		//7/28/10

/**
 * error_out
 * Insert description here
 *
 * @param $err_arg
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function error_out($err_arg) {							// 2/10/12
    do_log($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message

    return;
    }				// end function error_out()

//	if (empty($_SESSION)) {error_out(basename(__FILE__) . "@"  . __LINE__);}
$me = array_key_exists('user_id', $_SESSION)? $_SESSION['user_id'] :  1;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '{$me}';";	// 4/18/11
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;	// 2/10/12
$al_groups = array();
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	// 4/18/11
    $al_groups[] = $row['group'];
    }

if (isset($_SESSION['viewed_groups'])) {		//	6/10/11
    $curr_viewed= explode(",",$_SESSION['viewed_groups']);
    }
                // most recent chat invites other than written by 'me'
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]chat_invites` WHERE `_by` <> {$me}  AND (`to` = 0 OR `to` = {$me}) ORDER BY `id` DESC LIMIT 1";		// broadcasts
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;		// 6/23/2013

$the_chat_id = ($row)? $row['id'] : "0";

                // most recent ticket other than written by 'me'

if (!isset($curr_viewed)) {
    if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 1";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($al_groups as $grp) {
            $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 1";
        }
    } else {
    if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 1";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($curr_viewed as $grp) {
            $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 1";
        }
    }
                                            // 2/21/12
    $query = "SELECT *, `t`.`id` AS `the_ticket_id` FROM `$GLOBALS[mysql_prefix]ticket` `t`
             LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`
            WHERE `t`.`_by` <> {$me} AND `t`.`status` = {$GLOBALS['STATUS_OPEN']} $where2 ORDER BY `t`.`id` DESC LIMIT 1";		// broadcasts
    $result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;				// 2/10/12
    $row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

    $the_tick_id = ($row)? $row['the_ticket_id'] : "0";		// 2/21/12

                            // position updates?

if (!isset($curr_viewed)) {
    if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 2";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($al_groups as $grp) {
            $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 2";
        }
    } else {
    if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 2";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($curr_viewed as $grp) {
            $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 2";
        }
    }

$query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
        LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
        WHERE  `callsign` > '' AND (`aprs` = 1 OR  `instam` = 1 OR  `locatea` = 1 OR  `gtrack` = 1 OR  `glat` = 1 ) $where2 ORDER BY `r`.`updated` DESC LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;			// 2/10/12
$row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
//	Changed 8/3/12
if ($row) {	//	Latest unit Status update written by current user.
    $_SESSION['unit_flag_1'] = $row['the_responder_id'];			// 2/21/12
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
    } else {				// latest unit status updates written by others
    if (!isset($curr_viewed)) {
        if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
            $where2 = "AND `a`.`type` = 2";
            } else {
            $x=0;
            $where2 = "AND (";
            foreach ($al_groups as $grp) {
                $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
                $where2 .= "`a`.`group` = '{$grp}'";
                $where2 .= $where3;
                $x++;
                }
            $where2 .= "AND `a`.`type` = 2";
            }
        } else {
        if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
            $where2 = "AND `a`.`type` = 2";
            } else {
            $x=0;
            $where2 = "AND (";
            foreach ($curr_viewed as $grp) {
                $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
                $where2 .= "`a`.`group` = '{$grp}'";
                $where2 .= $where3;
                $x++;
                }
            $where2 .= "AND `a`.`type` = 2";
            }
        }

                                        // 2/21/12
    $query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
    LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
    WHERE `r`.`user_id` != {$me} $where2 ORDER BY `r`.`updated` DESC LIMIT 1";		// get most recent
    $result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
    $row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;
    }

if ($row) {
    $_SESSION['unit_flag_1'] = $row['the_responder_id'];			// 2/21/12
//	$_SESSION['unit_flag_2'] = $me;		// 6/11/10
    }

                        //	9/10/13 Most recent status updates
if (!isset($curr_viewed)) {
    if (count($al_groups == 0)) {
        $where2 = "AND `a`.`type` = 2";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($al_groups as $grp) {
            $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 2";
        }
    } else {
    if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.
        $where2 = "AND `a`.`type` = 2";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($curr_viewed as $grp) {
            $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 2";
        }
    }

$query = "SELECT *, `r`.`id` AS `the_responder_id` FROM `$GLOBALS[mysql_prefix]responder` `r`
LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `r`.`id` = `a`.`resource_id`
WHERE `r`.`user_id` != {$me} $where2 ORDER BY `r`.`status_updated` DESC LIMIT 1";		// get most recent
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;
$row2 =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

if ($row2) {		//	9/10/13
    $status_updated = $row2['the_responder_id'];
    $status_updated_time = $row2['status_updated'];
    $status_updated_time = $row2['un_status_id'];
    }

                        // 1/21/11 - get most recent dispatch

if (!isset($curr_viewed)) {
    if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 1";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($al_groups as $grp) {
            $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 1";
        }
    } else {
    if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	5/30/13
        $where2 = "AND `a`.`type` = 1";
        } else {
        $x=0;
        $where2 = "AND (";
        foreach ($curr_viewed as $grp) {
            $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
            $where2 .= "`a`.`group` = '{$grp}'";
            $where2 .= $where3;
            $x++;
            }
        $where2 .= "AND `a`.`type` = 1";
        }
    }

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` `as`
        LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON `as`.`ticket_id` = `t`.`id`
        LEFT JOIN `$GLOBALS[mysql_prefix]allocates` `a` ON `t`.`id` = `a`.`resource_id`
        WHERE `as`.`user_id` != {$me} $where2 ORDER BY `as`.`as_of` DESC LIMIT 1";		// get most recent
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$assign_row = (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

// 2/25/12 - AS

$query = "SELECT `updated` FROM `$GLOBALS[mysql_prefix]action` WHERE `updated` = ( SELECT MAX(`updated`) FROM `$GLOBALS[mysql_prefix]action` ) LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$act_row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT `updated` FROM `$GLOBALS[mysql_prefix]patient` WHERE `updated` = ( SELECT MAX(`updated`) FROM `$GLOBALS[mysql_prefix]patient` ) LIMIT 1";
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$pat_row =  (mysql_num_rows($result)>0)? stripslashes_deep(mysql_fetch_assoc($result)): FALSE;

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `status` = 'Open'";	//	10/23/12
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$the_reqs = (mysql_affected_rows() > 0) ? mysql_num_rows($result) : "0";

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `closed` = '' OR `closed` IS NULL";	//	10/23/12
$result = mysql_query($query) or error_out(basename(__FILE__) . "@"  . __LINE__) ;		// 2/10/12
$the_reqs2 = (mysql_affected_rows() > 0) ? mysql_num_rows($result) : "0";

$the_act_id = ($act_row)? $act_row['updated'] : "0";		// action item
$the_pat_id = ($pat_row)? $pat_row['updated'] : "0";		// patient item

$the_unit_id = ($row)? $row['the_responder_id'] : "0";	//	10/23/12
$the_updated = ($row)? $row['updated'] : "0";

$status_updated = ($row2)? $row2['the_responder_id'] : "0";		//	9/10/13
$the_status =($row2)? $row2['un_status_id'] : "0";		//	9/10/13
$status_updated_time = ($row2)? $row2['status_updated'] : "0";		//	9/10/13

$the_dispatch_change = ($assign_row)? $assign_row['as_of']: "";
$the_hash = md5($the_chat_id . $the_tick_id . $the_unit_id . $the_updated . $the_dispatch_change . $the_act_id . $the_pat_id . $the_reqs . $the_reqs2 . $status_updated . $the_status . $status_updated_time);	//	10/23/12
$ret_arr = array ($the_chat_id, $the_tick_id, $the_unit_id, $the_updated, $the_dispatch_change, $the_act_id, $the_pat_id, $the_reqs, $the_reqs2, $status_updated, $the_status, $status_updated_time, $the_hash);	//	10/23/12
get_current();								// update remotes position - 5/30/2013
print json_encode($ret_arr);				// 1/6/11
exit();