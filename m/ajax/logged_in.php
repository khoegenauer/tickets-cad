<?php
/*
logged-in detector - returns 1 if true, otherwise 0
11/27/2013 - initial release
*/
error_reporting(E_ALL);	
@session_start();	

echo ( ( array_key_exists("SP", $_SESSION) ) &&  array_key_exists( "user_id", $_SESSION["SP"] ) ) ? 1 : 0;
exit ();
?>