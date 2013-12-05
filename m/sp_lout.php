<?php
/*
4/15/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/7/09
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once '../incs/functions.inc.php';
@session_start();
$the_id = array_key_exists ('user_id', $_SESSION['SP'])? $_SESSION['SP']['user_id'] : 0;	// possibly already logged out
do_log($GLOBALS['LOG_SIGN_OUT'], 0, 0, $the_id);								// log this logout
$_SESSION['SP'] = array();					// empty it
header("Location: ./index.php"); 			// Redirect to this root
exit;										// ensure no further script operation
