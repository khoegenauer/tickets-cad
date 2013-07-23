<?php
/*
10/14/08 moved js includes here fm function_major
1/11/09  handle callboard frame
1/19/09 dollar function added
1/21/09 added show butts - re button menu
1/24/09 auto-refresh iff situation display and setting value
1/28/09 poll time added to top frame
3/16/09 added updates and auto-refresh if any mobile units
3/18/09 'aprs_poll' to 'auto_poll'
4/10/09 frames check for call board
7/16/09	protocol handling added
4/11/10 poll_id dropped
6/18/10 timeout test for yg
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
*/
error_reporting(E_ALL);			// 9/13/08
set_time_limit(0); 				// 6/18/10

@session_start();
require_once($_SESSION['fip']);		//7/28/10
require_once('./incs/full_scr.inc.php');	//
$api_key = get_variable('gmaps_api_key');		// empty($_GET)

//dump($_GET);
if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
//	snap(__LINE__, basename(__FILE__));
	do_login(basename(__FILE__));
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}

$remotes = get_current();								// returns array - 3/16/09
//snap(basename(__FILE__), __LINE__);
														// set auto-refresh if any mobile units														
$interval = intval(get_variable('auto_poll'));
$refresh = ((($remotes['aprs']) || ($remotes['instam'])) && ($interval>0))? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>\n": "";	//10/4/08
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<TITLE>Tickets - Full Screen Module</TITLE>
<?php print $refresh;?>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">

	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>
	<SCRIPT>
	if(document.all && !document.getElementById) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}
	function $() {									// 1/19/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')
				element = document.getElementById(element);
			if (arguments.length == 1)
				return element;
			elements.push(element);
			}
		return elements;
		}
	
	</SCRIPT>
	<SCRIPT SRC='./js/usng.js' TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
	<SCRIPT SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
	
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<style type="text/css">
.map {width:99%;height:80%;} 
</style>
</HEAD>

<BODY onUnload="GUnload();">
<?php
//require_once('./incs/links.inc.php');
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;

	full_scr();
?>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='func' VALUE='0'>
</FORM>
</BODY></HTML>
