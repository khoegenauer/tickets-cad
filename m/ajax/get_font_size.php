<?php
/*
9/1/2013  - initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting ( E_ALL ^ E_DEPRECATED );
require_once '../../incs/functions.inc.php';
@session_start();
if ( array_key_exists("SP", $_SESSION ) ) {
    echo strval(floatval($_SESSION['SP']['font_size'] ) );		// purify
    }
exit (0);
