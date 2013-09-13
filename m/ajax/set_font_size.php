<?php
/*
9/1/2013  - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );
require_once('../../incs/functions.inc.php');
@session_start();	
snap(basename(__FILE__), $_POST['font_size']);
if ( array_key_exists("SP", $_SESSION ) ) { 
	$_SESSION['SP']['font_size'] = strval(floatval($_POST['font_size'] ) );		// purify
	echo "";
	}
exit (0);
?>
