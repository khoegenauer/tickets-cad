<?php

error_reporting(E_ALL);				// 9/13/08
$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
$do_blink = TRUE;					// or FALSE , only - 4/11/10

session_start();						// 

require_once('./incs/functions.inc.php');

$the_inc = ((array_key_exists('internet', ($_SESSION))) && ($_SESSION['internet']))? './incs/functions_major.inc.php' : './incs/functions_major_nm.inc.php';
require_once($the_inc);
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
11/11/09 'top' and 'bottom' anchors added - 
12/26/09 handle 'log_in' $_GET variable
1/3/10 wz tooltips added for usage in FMP
1/8/10 added do_init logic - called ONLY from index.php
1/23/10 refresh meta removed
3/27/10 $zoom_tight added
4/10/10 hide 'board' button if setting = 0
4/11/10 do_blink added, poll_id dropped
6/24/10 compression added
7/18/10 redundant $() removed
7/20/10 cb frame resize/refresh added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 links incl relocated
8/25/10 hide top buttons if ..., $_POST logout test
8/29/10 dispatch status style added
11/29/10 added to_listtype form when adding scheduled list type select dropdown
3/15/11	Added reference to stylesheet.php for revisable day night colors
3/19/11 added top term button value
4/22/11 gunload correction
*/

if (isset($_GET['logout'])) {
	do_logout();
	exit();
	}
else {		// 
	do_login(basename(__FILE__));	
	$do_mu_init = (array_key_exists('log_in', $_GET))? "parent.frames['upper'].mu_init();" : "";	// start multi-user function
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
	print "SESSION<BR/>\n";	//	3/15/11
	if (!empty($_SESSION)) {
		dump ($_SESSION);
		}
	}
														// set auto-refresh if any mobile units														
$temp = get_variable('auto_poll');				// 1/28/09
$poll_val = ($temp==0)? "none" : $temp ;
$day_night = ((array_key_exists('day_night', ($_SESSION))) && ($_SESSION['day_night']))? $_SESSION['day_night'] : 'Day';	//	3/15/11

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Main Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<STYLE>
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
	</STYLE>
<?php 
@session_start();	
if ($_SESSION['internet']) {				// 8/22/10
	$api_key = get_variable('gmaps_api_key');	
?>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $api_key; ?>"></SCRIPT>

<?php } ?>
	
	<SCRIPT>
<?php
if ( get_variable('call_board') == 2) {		// 7/20/10
	$cb_per_line = 22;						// adjust as needed
	$cb_fixed_part = 60;
	$cb_min = 96;
	$cb_max = 300;
	
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ";	// 6/15/09
	$result = @mysql_query($query);
	$lines = mysql_affected_rows();
	unset($result);
	$height = (($lines*$cb_per_line ) + $cb_fixed_part);
	$height = ($height<$cb_min)? $cb_min: $height;		// vs min
	$height = ($height>$cb_max)? $cb_max: $height;		// vs max
?>
frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');	// get current configuration
var rows = frame_rows.split(",", 4);
rows[1] = <?php print $height ;?>;						// new cb frame height, re-use top
frame_rows = rows.join(",");
parent.document.getElementById('the_frames').setAttribute('rows', frame_rows);
parent.calls.location.href = 'board.php';							// 7/21/10



<?php
	}		// end if ( get_variable('call_board') == 2) 
	
if (!($_SESSION['internet'])) {				// 8/25/10 
?>
	parent.frames["upper"].$("full").style.display  = "none";		// hide 'full screen' button
<?php
	}
if (is_guest()) {													// 8/25/10
?>	
	parent.frames["upper"].$("add").style.display  = "none";		// hide 'new' button
<?php
	}
?>

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php

	function logged_in() {								// returns boolean
		var temp = parent.frames["upper"].$("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function $() {									// 1/21/09, 7/18/10
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}
	
<?php
	if (array_key_exists('log_in', $_GET)) {			// 12/26/09- array_key_exists('internet', $_SESSION)
?>

	parent.frames["upper"].mu_init ();					// start polling
	if (parent.frames.length == 3) {										// 1/20/09, 4/10/09
		parent.calls.location.href = 'board.php';							// 1/11/09
		}
<?php
		}
$temp = get_unit();															// 3/19/11
$term_str = ($temp )? $temp : "Mobile" ;

?>
/*
//	parent.frames["upper"].location.reload( true );
	if(document.all && !(document.getElementById)) {		// accomodate IE							
		document.getElementById = function(id) {							
			return document.all[id];							
			}							
		}		
*/		
	try {
		parent.frames["upper"].$("manual").style.display  = "inline";								// manual link - 5/27/11
		parent.frames["upper"].$("gout").style.display  = "inline";									// logout button
		parent.frames["upper"].$("user_id").innerHTML  = "<?php print $_SESSION['user_id'];?>";	
		parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";			// user name
		parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename(__FILE__));?>";				// module name
//		parent.frames["upper"].$("poll_id").innerHTML  = "<?php print $poll_val;?>";
		parent.frames["upper"].$("main_body").style.backgroundColor  = "<?php print get_css('page_background', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("main_body").style.color  = "<?php print get_css('normal_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("tagline").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("user_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("unit_id").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("script").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_of_day").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("whom").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("level").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("perms_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("modules_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11
		parent.frames["upper"].$("time_txt").style.color  = "<?php print get_css('titlebar_text', $day_night);?>";	//	3/15/11

		parent.frames["upper"].$("term").innerHTML  = "<?php print $term_str;?>";				// responder or 'Mobile' name - 3/19/11

		}
	catch(e) {
		}
		
	function get_new_colors() {													// 5/3/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}

	function ck_frames() {		//  onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			parent.upper.do_day_night("<?php print $_SESSION['day_night'];?>")
			}
		}		// end function ck_frames()
<?php																	// 4/10/10
	if (intval(get_variable('call_board')) == 0) {						// hide the button
		print "\t parent.frames['upper'].$('call').style.display = 'none';";
		}
?>		
/* *
 * Concatenates the values of a variable into an easily readable string
 * by Matt Hackett [scriptnode.com]
 * @param {Object} x The variable to debug
 * @param {Number} max The maximum number of recursions allowed (keep low, around 5 for HTML elements to prevent errors) [default: 10]
 * @param {String} sep The separator to use between [default: a single space ' ']
 * @param {Number} l The current level deep (amount of recursion). Do not use this parameter: it's for the function's own use
 */
	function print_r(x, max, sep, l) {
		l = l || 0;
		max = max || 10;
		sep = sep || ' ';
		if (l > max) {
			return "[WARNING: Recursion limit exceeded]\n";
			}
		var
			i,
			r = '',
			t = typeof x,
			tab = '';
		if (x === null) {
			r += "(null)\n";
			} 
		else if (t == 'object') {
			l++;
			for (i = 0; i < l; i++) {
				tab += sep;
				}
			if (x && x.length) {
				t = 'array';
				}
			r += '(' + t + ") :\n";
			for (i in x) {
				try {
					r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
					} 
				catch(e) {
					return "[ERROR: " + e + "]\n";
					}
				}
			} 
		else {
			if (t == 'string') {
				if (x == '') {
					x = '(empty)';
					}
				}
			r += '(' + t + ') ' + x + "\n";
			}
		return r;
		};
//	var_dump = print_r;
	function show_btns_closed() {						// 4/30/10
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_interval.selectedIndex=0;
		}
	function show_btns_scheduled() {						// 4/30/10
		$('btn_scheduled').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_scheduled() {
		$('btn_scheduled').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_sched.selectedIndex=0;
		}
	</SCRIPT>

<?php if ($_SESSION['internet']) {	?>
	<SCRIPT SRC='./js/usng.js' TYPE='text/javascript'></SCRIPT>		<!-- 10/14/08 -->
	<SCRIPT SRC='./js/graticule.js' type='text/javascript'></SCRIPT>
<?php } ?>
	
	
<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">	<!-- 3/15/11 -->
</HEAD>
<?php
	$gunload = ($_SESSION['internet'])? " onUnload='GUnload();'" : "" ;				// 4/22/11
	$set_showhide = ((array_key_exists('print', ($_GET)) || (array_key_exists('id', ($_GET)))))? "" : "set_initial_pri_disp(); set_categories(); set_fac_categories();";	//	3/15/11
//	dump($_SESSION);	
?>

<BODY onLoad = "<?php print $set_showhide;?>; ck_frames(); location.href = '#top'; <?php print $do_mu_init;?>" <?php print $gunload;?>>	<!-- 3/15/11 -->
<?php
include("./incs/links.inc.php");		// 8/13/10
?>
<DIV ID='to_bottom' style="position: fixed; top: 20px; left: 20px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></div>

<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

<A NAME="top" /> <!-- 11/11/09 -->
<?php
	$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
	$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
	$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
	$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;

	if ($get_print) {
		show_ticket($get_id,'true');
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_id) {
		add_header($get_id);
		show_ticket($get_id);
		print "<BR /><P ALIGN='left'>";
		}
	else if ($get_sort_by_field && $get_sort_value) {
		list_tickets($get_sort_by_field, $get_sort_value);
		}
	else {
		list_tickets();
		}
?>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' /> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<INPUT TYPE='hidden' NAME='func' VALUE='' /> <!-- 11/28/10 not now used - replaced with form to_listtype -->
</FORM> <!-- 11/28/10 not now used - replaced with form to_listtype -->
<FORM NAME='to_listtype' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 11/29/10 not now used - replaced with form to_listtype -->

<INPUT TYPE='hidden' NAME='func' VALUE='' />
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
</FORM>
<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
<INPUT TYPE='hidden' NAME='func' VALUE='1' />
</FORM>
<!--
<span onclick = "parent.top.calls.location.reload(true)">Test1</span>
<br />
<span onclick = "parent.top.calls.document.page_refresh_form.submit()">Test2</span>
<br />
<span onclick = "alert(parent.$('what').rows)">Test3</span>
-->
<br /><br />
<A NAME="bottom" /> <!-- 11/11/09 -->
</BODY></HTML>
