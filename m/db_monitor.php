<?php
/*
Uses server-sent events technique to push changed information to browser
*/
error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
@session_start();											//
//header("Content-Type: text/event-stream\n\n");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); 				// recommended to prevent caching of event data.

$id = 0;			// now look for updates

do { sleep (5);} 						// loop waiting for login
while 	( ! ( array_key_exists ( 'SP', $_SESSION ) ) );

sendMsg($id , "Hello world");

// now get latest values

$query = "SELECT MAX(`id`) AS `max_id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE status != '$GLOBALS[STATUS_RESERVED]'";
$result_max = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_num_rows($result_max)==1) {
    $row_tick = mysql_fetch_assoc ( $result_max );
    $_SESSION['SP']['last_ticket'] = $row_tick['max_id'];
    }
else {$_SESSION['SP']['last_ticket'] = 0;}

$query = "SELECT MAX(`when`) AS `last_disp` FROM `$GLOBALS[mysql_prefix]log` WHERE (`code` IN
                ('{$GLOBALS['LOG_CALL_DISP']}', '{$GLOBALS['LOG_CALL_RESP']}', '{$GLOBALS['LOG_CALL_ONSCN']}',
                '{$GLOBALS['LOG_CALL_U2FENR']}', '{$GLOBALS['LOG_CALL_U2FARR']}')
                )";

$result_max = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_num_rows($result_max)==1) {
        $row_disp = mysql_fetch_assoc ( $result_max );
        $_SESSION['SP']['last_disp'] = $row_disp['last_disp'];		// note ts time format
        }
else {$_SESSION['SP']['last_dispatch'] = 0;}

/**
 * sendMsg
 * Insert Description
 *
 * @param type $id
 * @param type $msg
 */
function sendMsg($id, $msg) {
    echo "id: $id" . PHP_EOL;
    echo "data: $msg" . PHP_EOL;
    echo PHP_EOL;
    ob_flush();
    flush();
    }

while (array_key_exists ( 'SP', $_SESSION ) ) {
    $query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` != '$GLOBALS[STATUS_RESERVED]' AND `id` > '{$_SESSION['SP']['last_ticket']}' LIMIT 1";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result)==1) {
        $row = mysql_fetch_assoc ( $result );
        $_SESSION['SP']['last_ticket'] = $row['id'];		// new 'latest'
        $id++;
        sendMsg($id , $row['id']);							// to browser
        }

    $query = "SELECT MAX(`when`) AS `last_disp` FROM `$GLOBALS[mysql_prefix]log` WHERE (`code` IN
                    ('{$GLOBALS['LOG_CALL_DISP']}', '{$GLOBALS['LOG_CALL_RESP']}', '{$GLOBALS['LOG_CALL_ONSCN']}',
                     '{$GLOBALS['LOG_CALL_U2FENR']}', '{$GLOBALS['LOG_CALL_U2FARR']}')
                    ) AND (`when`) > '{$_SESSION['SP']['last_disp']}' ";				// excludes LOG_CALL_CLR

    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result)==1) {
        $row = mysql_fetch_assoc ( $result );
        $_SESSION['SP']['last_ticket'] = $row['id'];		// new 'latest'
        $id++;
        sendMsg($id , $row['id']);							// to browser
        }
    sleep (10);
    }				// end while (array_key_exists ...)
