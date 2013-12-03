<?php
/**
 * @package do_get_sess_vbl.php
 * @author John Doe <john.doe@example.com>
 * @since 2010-04-05
 * @version 2010-07-28
 */
/*
4/5/10 do_get_sess_vbl.php initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);	

@session_start();
require_once($_SESSION['fip']);		//7/28/10
$the_vbl = 'scr_width';
dump (get_sess_vbl ($the_vbl));

?>
