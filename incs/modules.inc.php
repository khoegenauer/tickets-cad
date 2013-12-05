<?php
/**
 * This is the main modules include file. It includes the helper file of all modules that are installed and enabled.
 *
 * @package modules.inc.php
 * @author John Doe <john.doe@example.com>
 * @since 2010-09-15
 * @version 2010-09-15 - New File
 */

//require_once('functions.inc.php');
require_once 'mysql.inc.php';

/**
 * get_modules
 * Insert description here
 *
 * @param $calling_file
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_modules($calling_file) {
    global $handle;
    $query 		= "SELECT COUNT(*) FROM `$GLOBALS[mysql_prefix]modules`";
    $result 	= mysql_query($query);
    $num_rows 	= @mysql_num_rows($result);
    if ($num_rows) {
        $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_status`=1 AND `affecting_files` LIKE '%{$calling_file}%'";
        $result2 = mysql_query($query2) or do_error('mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        $numb_rows = @mysql_num_rows($result2);
        while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {
            $name = $row2['mod_name']; $status=$row2['mod_status'] ;
            $inc_path="./modules/" . $name . "/helper.php";
            $display="get_display_" . $name;
            include($inc_path);
            $display($calling_file);
            }
        }
    }

/**
 * module_active
 * Insert description here
 *
 * @param $module
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function module_active($module) {
    global $handle;
    $query 		= "SELECT * FROM `$GLOBALS[mysql_prefix]modules` WHERE `mod_name`='{$module}' ";
    $result 	= mysql_query($query);
    $num_rows 	= @mysql_num_rows($result);
    if ($num_rows > 0) {
    while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
        $name = $row['mod_name'];
        $status = $row['mod_status'] ;

        return $status;
        }
    } else {
        $status=0;

        return $status;
        }
    }
