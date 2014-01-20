<?php
/*
7/1/2013 - initial release
8/22/2013 - added logged-in test
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting ( E_ALL ^ E_DEPRECATED );

require_once '../../incs/functions.inc.php';
require_once '../incs/sp_functions.inc.php';
$now = now_ts() ;										// timestamp format

if ( ! ( is_logged_in ( false ) ) ) {		// check, but don't update expiry - 8/22/2013
    echo "13";
    exit (14);
    }
else {
    if ( ! ( array_key_exists ( "latitude", $_POST ) && 							// data absent
            ( is_ok_position ($_POST['latitude'] , $_POST['longitude'] ) ) ) ) {
        log_error("Invalid location data - " . basename(__FILE__));
        echo "20";
        exit (21);
        }
    else {																			// data present - OK?
        extract ($_POST);
        @session_start();
        if ( ! ( is_ok_position ( $_POST['latitude'] , $_POST['longitude'] ) ) ) {
            $err_key = "AJAX DATA ERROR: " . basename(__FILE__);
            if ( ! ( array_key_exists($err_key, $_SESSION['SP']) ) ) {				// limit to once per session
                $_SESSION['SP'][$err_key] = now_ts();
                $err_arg = "Invalid ajax position data: {$_POST['latitude']} / {$_POST['longitude']}";
                sp_do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);
                }
            echo "33";
            exit (34);
            }				// end error handling

        $_SESSION['SP']['latitude'] = $latitude;
        $_SESSION['SP']['longitude'] = $longitude;					// test for movement
        $me = $_SESSION['SP']['user_unit_id'] ;						// 'my' unit
        $query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
            `lat` = " . quote_smart($latitude) . ",
            `lng` = " . quote_smart($longitude) . "
            WHERE `id` = '{$me}' LIMIT 1";

        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

        if ( intval( @mysql_affected_rows($result) ) == 1 ) { 					// do we have movement?
            $query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
                `updated` = '{$now}' ,
                `lat` = " . quote_smart($latitude) . ",
                `lng` = " . quote_smart($longitude) . "
                WHERE `id` = '{$me}' LIMIT 1";

            $result = mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);

            $the_latitude = 	(is_ok_float ($latitude))? 		quote_smart($latitude) : 	"NULL";
            $the_longitude = 	(is_ok_float ($longitude))? 	quote_smart($longitude) : 	"NULL";
            $the_speed = 		(is_ok_int ($speed))? 			quote_smart($speed) : 		"NULL";
            $the_course = 		(is_ok_int ($course))? 			quote_smart($course) : 		"NULL";
            $the_altitude = 	(is_ok_int ($altitude))? 		quote_smart($altitude) : 	"NULL";

            $query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
                        `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `updated` ) VALUES (
                         '{$me}',
                         {$the_latitude} ,
                         {$the_longitude},
                         {$the_speed},
                         {$the_course},
                         {$the_altitude},
                         '{$now}') ";

            $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
            }

        $temp = explode ("/", get_variable("auto_refresh"));									// apply sit screen auto-refresh time in minutes
        if (intval($temp[0]) > 0 ) {
            $_SESSION['SP']['next_pos_update'] =  time() + ( $temp[0] * 60 ) ;	// integer - to next update
            }
        echo "80";
        exit (81);
        }
    }
exit();
