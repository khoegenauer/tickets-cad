<?php
/*
*/
error_reporting(E_ALL);
require_once '../incs/functions.inc.php';
require_once '../incs/login.inc.php';

do_logout(TRUE);

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
$host = $_SERVER['HTTP_HOST'];
$temp = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$temp1 = explode ("/", $temp) ;
$temp2 = array_slice ( $temp1, 0, count ($temp1) -1 );
$path = implode("/", $temp2);

$url = $protocol. $host . $path;

redir($url);
