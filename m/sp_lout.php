<?php
/*
4/15/2013 initial release
11/12/2013 destroy session array entry
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
$start = time();
require_once('../incs/functions.inc.php');
require_once('incs/sp_functions.inc.php');		// 11/23/2013
@session_start();
$the_id = array_key_exists ('user_id', $_SESSION['SP'])? $_SESSION['SP']['user_id'] : 0;	// possibly already logged out
snap (__LINE__, time() - $start);
sp_do_log($GLOBALS['LOG_SIGN_OUT'], 0, 0, $the_id);								// log this logout	
snap (__LINE__, time() - $start);
unset ( $_SESSION['SP'] );					// destroy it - 11/12/2013
snap (__LINE__, time() - $start);
header("Location: ./index.php"); 			// Redirect to this root
exit;										// ensure no further script operation
?>
