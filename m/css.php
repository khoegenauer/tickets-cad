<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
@session_start();
require_once('../incs/functions.inc.php');		//7/28/10
require_once('css_default.php');		// 4/8/2013 
?>