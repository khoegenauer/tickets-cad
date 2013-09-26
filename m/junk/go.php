<?php
/*
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');
//				$host  = $_SERVER['HTTP_HOST'];
//				$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
do_logout(TRUE);
$SCRIPT_URI = "http://www.kolshalomannapolis.org/ticketscad/sp/phpinfo.php ";

				$url = "http://127.0.0.1/tickets_06_13_2013_V240B/index.php";
				redir($url);
//				header("Location: http://$host$uri/$extra");								// to top of calling script

/*
require_once('../incs/login.inc.php');
do_login("../../index.php");
*/
?>
