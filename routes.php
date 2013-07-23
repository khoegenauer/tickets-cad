<?php
error_reporting(E_ALL);

$sortby_distance = TRUE;			// user: set to TRUE or FALSE to determine unit ordering

$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)
@session_start();			// 1/7/10

require_once($_SESSION['fip']);		//7/28/10
require_once($_SESSION['fmp']);		//8/25/10

$from_top = 40;				// buttons alignment, user-reviseable as needed
$sidebar_width = 500;		// pixels

$show_tick_left = FALSE;	// controls left-side vs. right-side appearance of incident details - 11/27/09

/*
5/23/08 per AD7PE - line 432
8/25/08 handling sgl quotes in unit names
8/25/08 TITLE to td's
9/23/08 small map control
10/7/08	added auto-mail feature
10/13/08 added onClick() directions
10/13/08 accommodate no location data
10/14/08 added graticule
10/16/08 changed ticket_id to frm_ticket_id - tbd
10/16/08 added traffic functions
10/17/08 allow map click for directions if error
10/25/08 pointer housekeeping when can't route
10/26/08 always accept click
11/8/08 commas as separator
1/21/09 added show butts - re button menu
1/29/09 icon letter to number
2/15/09 added do_mail_win() for mail text editing
2/25/09 handle empty lat/lng
3/30/09 drop htmlentities for utf-8 handling
4/27/09 addslashes vs htmlentities, for easy
5/22/09	Multi handling, 
6/3/09	checkbox relocated
6/14/09	guest handling corrected
7/7/09	float check corrected, div transparent
7/13/09	fetch_assoc, direcs array
7/24/09	pick up in_types for protocol display
8/2/09	floating div location revised
8/7/09	disallow multiple assigns unless 'multi' is set
8/10/09	`tick_descr` added to query to resolve 'description' ambiguity
8/17/09	 street view added, select only cleared units
10/6/09 Added multi point routes for receiving facility and mail route to unit capability, added links button
10/28/09 Mail Direcs button hidden on load, shown on select after timer
10/28/09 Add Loading Directions message in floating menu.
10/29/09 Added ticket scope to hidden form filed for passing to do_direcs_mail script
11/12/09 corrections for 'direcs' handling, array indexing
11/15/09 revised logic re identifying units with position data
11/23/09 'quick' operation restored
11/27/09 relocated incident information to underneath map, added address to floating div
12/09/09 Changed order of unit display to match that in situation screen.
1/7/10 session start correction, 'call_taker' alias added to query
4/24/10 added sort by responder proximity to incident
3/30/10 div height 100%, get_cd_str() corrected
5/6/10 'from_left' retired, sidebar width revised, per 5/6/10 msg
5/21/10 sql prefix correction
5/28/10 removed gratuitous unit status update
5/30/10 added status dispatch disallowed
6/25/10 added year check to NULL check three places
6/29/10 target added as correction two places
7/9/10 div height calc, form -> get, 'more' button added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
7/29/10 onload revised  for 'quick' and mail handling
8/9/10 corrections to resolve ambiguous address field names
8/25/10 require FMP added
8/30/10 to main.php vs. index
9/23/10 div position from left, top repaired
*/

do_login(basename(__FILE__));		// 
//snap(__LINE__, basename(__FILE__));
//print "GET";
if($istest) {
	print "GET<br />\n";
	dump($_GET);
	}
	
if (!(isset ($_SESSION['allow_dirs']))) {	
	$_SESSION['allow_dirs'] = 'true';			// note js-style LC
	}

//function get_left_margin ($sb_width) {
//	return min(($_SESSION['scr_width'] - 150), ($sb_width + get_variable('map_width') + 72));
//	}

$api_key = get_variable('gmaps_api_key');
$_GET = stripslashes_deep($_GET);
$eol = "< br />\n";

$u_types = array();												// 1/1/09
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
	$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
	}

$icons = $GLOBALS['icons'];				// 1/1/09
$sm_icons = $GLOBALS['sm_icons'];

function get_icon_legend (){			// returns legend string - 1/1/09
	global $u_types, $sm_icons;
	$query = "SELECT DISTINCT `type` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `handle` ASC, `name` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$print = "";											// output string
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$type_data = $u_types[$row['type']];
		$print .= "\t\t" .$type_data[0] . " &raquo; <IMG SRC = './icons/" . $sm_icons[$type_data[1]] . "' BORDER=0 />&nbsp;&nbsp;&nbsp;\n";
		}
	return $print;
	}			// end function get_icon_legend ()

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<HEAD><TITLE>Tickets - Routes Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> 
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
    <STYLE TYPE="text/css">
		body 				{font-family: Verdana, Arial, sans serif;font-size: 11px;margin: 2px;}
		table 				{border-collapse: collapse; }
		table.directions th {background-color:#EEEEEE;}	  
		img 				{color: #000000;}
		span.even 			{background-color: #DEE3E7;}
		span.warn			{display:none; background-color: #FF0000; color: #FFFFFF; font-weight: bold; font-family: Verdana, Arial, sans serif; }

		span.mylink			{margin-right: 32PX; text-decoration:underline; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_1		{margin-right: 32PX; text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		span.other_2		{margin-right: 8PX;  text-decoration:none; font-weight: bold; font-family: Verdana, Arial, sans serif;}
		.disp_stat	{ FONT-WEIGHT: bold; FONT-SIZE: 9px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}

		.box {
			background-color: transparent;
			border: none;
			color: #000000;
			padding: 0px;
			position: absolute;
			}
		.bar {
			background-color: #DEE3E7;
			color: transparent;
			cursor: move;
			font-weight: bold;
			padding: 2px 1em 2px 1em;
			}
		.content {
			padding: 1em;
			}
	</STYLE>

<SCRIPT>
	try {	
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
//			alert ("332 " + AJAX.responseText);
			return AJAX.responseText;																				 
			} 
		else {
			alert ("158: failed");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	function docheck(in_val){				// JS boolean  - true/false
		document.routes_Form.frm_allow_dirs.value = in_val;	
		url = "do_session_get.php?the_name=allow_dirs&the_value=" + in_val.trim();
		syncAjax(url);			// note asynch call
		}
		
	function isNull(arg) {
		return arg===null;
		}

	function $() {									// 2/11/09
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

	String.prototype.trim = function () {									// added 6/10/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

</SCRIPT>	
<script type="text/javascript">//<![CDATA[
//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************
// Determine browser and version.
function Browser() {
	var ua, s, i;
	this.isIE		= false;
	this.isNS		= false;
	this.version = null;
	ua = navigator.userAgent;
	s = "MSIE";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isIE = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	s = "Netscape6/";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = parseFloat(ua.substr(i + s.length));
		return;
		}
	// Treat any other "Gecko" browser as NS 6.1.
	s = "Gecko";
	if ((i = ua.indexOf(s)) >= 0) {
		this.isNS = true;
		this.version = 6.1;
		return;
		}
	}
var browser = new Browser();
var dragObj = new Object();		// Global object to hold drag information.
dragObj.zIndex = 0;
function dragStart(event, id) {
	var el;
	var x, y;
	if (id)										// If an element id was given, find it. Otherwise use the element being
		dragObj.elNode = document.getElementById(id);	// clicked on.
	else {
		if (browser.isIE)
			dragObj.elNode = window.event.srcElement;
		if (browser.isNS)
			dragObj.elNode = event.target;
		if (dragObj.elNode.nodeType == 3)		// If this is a text node, use its parent element.
			dragObj.elNode = dragObj.elNode.parentNode;
		}
	if (browser.isIE) {			// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.cursorStartX = x;		// Save starting positions of cursor and element.
	dragObj.cursorStartY = y;
	dragObj.elStartLeft	= parseInt(dragObj.elNode.style.left, 10);
	dragObj.elStartTop	 = parseInt(dragObj.elNode.style.top,	10);
	if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
	if (isNaN(dragObj.elStartTop))	dragObj.elStartTop	= 0;
	dragObj.elNode.style.zIndex = ++dragObj.zIndex;		// Update element's z-index.
	if (browser.isIE) {									// Capture mousemove and mouseup events on the page.
		document.attachEvent("onmousemove", dragGo);
		document.attachEvent("onmouseup",	 dragStop);
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS) {
		document.addEventListener("mousemove", dragGo,	 true);
		document.addEventListener("mouseup",	 dragStop, true);
		event.preventDefault();
		}
	}
function dragGo(event) {
	var x, y;
	if (browser.isIE) {	// Get cursor position with respect to the page.
		x = window.event.clientX + document.documentElement.scrollLeft
			+ document.body.scrollLeft;
		y = window.event.clientY + document.documentElement.scrollTop
			+ document.body.scrollTop;
		}
	if (browser.isNS) {
		x = event.clientX + window.scrollX;
		y = event.clientY + window.scrollY;
		}
	dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";	// Move drag element by the same amount the cursor has moved.
	dragObj.elNode.style.top	= (dragObj.elStartTop	+ y - dragObj.cursorStartY) + "px";
	if (browser.isIE) {
		window.event.cancelBubble = true;
		window.event.returnValue = false;
		}
	if (browser.isNS)
		event.preventDefault();
	}
function dragStop(event) {
	if (browser.isIE) {	// Stop capturing mousemove and mouseup events.
		document.detachEvent("onmousemove", dragGo);
		document.detachEvent("onmouseup",	 dragStop);
		}
	if (browser.isNS) {
		document.removeEventListener("mousemove", dragGo,	 true);
		document.removeEventListener("mouseup",	 dragStop, true);
		}
	}
//]]></script>
<?php
if((array_key_exists('func', $_REQUEST)) && ($_REQUEST['func'] == "do_db")) {	// 		new, populate 10/2/08

	extract($_REQUEST);
	$the_ticket_id = (integer) $_REQUEST["frm_ticket_id"];
	$addrs = array();													// 10/7/08
	$now = mysql_format_date(time() - (get_variable('delta_mins')*60)); 
	$assigns = explode ("|", $_REQUEST['frm_id_str']);		// pipe sep'd id's in frm_id_str
	for ($i=0;$i<count($assigns); $i++) {		//10/6/09 added facility and receiving facility
		$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]assigns` (`as_of`, `status_id`, `ticket_id`, `responder_id`, `comments`, `user_id`, `dispatched`, `facility_id`, `rec_facility_id`)
						VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
							quote_smart($now),
							quote_smart($frm_status_id),
							quote_smart($frm_ticket_id),
							quote_smart($assigns[$i]),
							quote_smart($frm_comments),
							quote_smart($frm_by_id),
							quote_smart($now),
							quote_smart($frm_facility_id),
							quote_smart($frm_rec_facility_id));
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//										remove placeholder inserted by 'add'		
		$query = "DELETE FROM `$GLOBALS[mysql_prefix]assigns` WHERE `ticket_id` = " . quote_smart($frm_ticket_id) . " AND `responder_id` = 0 LIMIT 1";
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

							// apply status update to unit status

		$query = "SELECT `id`, `contact_via` FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = " . quote_smart($assigns[$i])  ." LIMIT 1";		// 10/7/08
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		$row_addr = stripslashes_deep(mysql_fetch_assoc($result));
		if (is_email($row_addr['contact_via'])) {array_push($addrs, $row_addr['contact_via']); }		// to array for emailing to unit

		do_log($GLOBALS['LOG_UNIT_STATUS'], $frm_ticket_id, $assigns[$i], $frm_status_id);
		if ($frm_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		if ($frm_rec_facility_id != 0) {
			do_log($GLOBALS['LOG_FACILITY_DISP'], $frm_ticket_id, $assigns[$i], $frm_status_id);
			}
		}
?>	
<SCRIPT>
	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
//				alert('HTTP error ' + req.status);
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}
	
	
	function handleResult(req) {				// the 'called-back' function
												// onto floor!
		}

	var starting = false;						// 2/15/09

	function do_mail_win(addrs, ticket_id) {	
		if(starting) {return;}					// dbl-click catcher
//		alert(" <?php print __LINE__; ?> " +addrs);
		starting=true;	
		var url = "mail_edit.php?ticket_id=" + ticket_id + "&addrs=" + addrs + "&text=";	// no text
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=360,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()

<?php 
	$temp = get_variable('call_board');		// refresh call board
	switch ($temp) {
		case 1 :		// window
//			print "\n alert(305);\n";
//			print "\n\tparent.top.calls.newwindow_cb.do_refresh();\n";
			break;
		case 2 :		// frame
//			print "\n alert(309);\n";
			print "\n\tparent.top.calls.do_refresh();\n";
			break;
		default :
			print "\n alert(306);\n";	
		}	// end switch ($temp)
	
?>	

</SCRIPT>
</HEAD>

<?php
								// 7/29/10
	$addr_str = urlencode( implode("|", array_unique($addrs)));
	$mail_str = (empty($addr_str))? "" :  "do_mail_win('{$addr_str}', '{$_REQUEST['frm_ticket_id']}');";
	$quick_str = ((get_variable('quick'))==1)? "document.more_form.submit();" : "";
	$extra =  (((empty($mail_str)) && (empty($quick_str))))? "" : " onLoad = \"{$mail_str}{$quick_str}\"";

	print "\n<BODY{$extra}> <!-- " . __LINE__ . " --> \n";		
?>	
<SCRIPT>
if (window.opener && !window.opener.closed) {
	window.opener.parent.frames['upper'].show_msg ('Email sent!');
	}
else {	
	parent.frames['upper'].show_msg ('Email sent!');
	}
</SCRIPT>
	<CENTER><BR><BR><BR><BR><H3>Call Assignments made to:<BR /><?php print substr((str_replace ( "\n", ", ", $_REQUEST['frm_name_str'])) , 0, -2);?><BR><BR> <!-- 11/8/08 -->
<?php print (intval(get_variable("call_board")) == 1)? "See Call Board": "";?>	
	</H3>
	<NOBR>
	<FORM NAME='more_form' METHOD = 'get' ACTION = "<?php print basename(__FILE__); ?>" style="display: inline;"><!-- 7/9/10 -->
	<INPUT TYPE='button' VALUE='More' onClick = "document.more_form.submit()" />
	<INPUT TYPE = 'hidden' NAME = 'ticket_id' VALUE="<?php print $_GET['frm_ticket_id'];?>">
	</FORM>
	<FORM NAME='cont_form' METHOD = 'get' ACTION = "main.php" STYLE = 'margin-left:20px; display: inline;'><!-- 8/30/10  -->
	<INPUT TYPE='button' VALUE='Finished' onClick = "document.cont_form.submit()" />
	</FORM>
	</NOBR>
	</BODY></HTML>
<?php		

	}		// end if ("do_db")

//	=============================  major split =============================== 7/9/10

else {	 
	require_once ('./incs/routes_inc.php');		// 7/8/10

	$the_ticket_id = (integer) $_REQUEST['ticket_id'];
?>

<SCRIPT SRC="http://maps.google.com/maps?file=api&amp;v=2.s&amp;key=<?php echo $api_key; ?>"></SCRIPT>
<SCRIPT SRC="./js/usng.js"></SCRIPT>		<!-- 10/14/08 -->
<SCRIPT SRC="./js/graticule.js"></SCRIPT>
	

<SCRIPT>
	parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	String.prototype.parseDeg = function() {
		if (!isNaN(this)) return Number(this);								// signed decimal degrees without NSEW
		
		var degLL = this.replace(/^-/,'').replace(/[NSEW]/i,'');			// strip off any sign or compass dir'n
		var dms = degLL.split(/[^0-9.,]+/);									// split out separate d/m/s
		for (var i in dms) if (dms[i]=='') dms.splice(i,1);					// remove empty elements (see note below)
		switch (dms.length) {												// convert to decimal degrees...
			case 3:															// interpret 3-part result as d/m/s
				var deg = dms[0]/1 + dms[1]/60 + dms[2]/3600; break;
			case 2:															// interpret 2-part result as d/m
				var deg = dms[0]/1 + dms[1]/60; break;
			case 1:															// decimal or non-separated dddmmss
				if (/[NS]/i.test(this)) degLL = '0' + degLL;	// - normalise N/S to 3-digit degrees
				var deg = dms[0].slice(0,3)/1 + dms[0].slice(3,5)/60 + dms[0].slice(5)/3600; break;
			default: return NaN;
			}
		if (/^-/.test(this) || /[WS]/i.test(this)) deg = -deg; // take '-', west and south as -ve
		return deg;
		}
	Number.prototype.toRad = function() {  // convert degrees to radians
		return this * Math.PI / 180;
		}

	Number.prototype.toDeg = function() {  // convert radians to degrees (signed)
		return this * 180 / Math.PI;
		}
	Number.prototype.toBrng = function() {  // convert radians to degrees (as bearing: 0...360)
		return (this.toDeg()+360) % 360;
		}
	function brng(lat1, lon1, lat2, lon2) {
		lat1 = lat1.toRad(); lat2 = lat2.toRad();
		var dLon = (lon2-lon1).toRad();
	
		var y = Math.sin(dLon) * Math.cos(lat2);
		var x = Math.cos(lat1)*Math.sin(lat2) -
						Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
		return Math.atan2(y, x).toBrng();
		}

	distCosineLaw = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // earth's mean radius in km
		var d = Math.acos(Math.sin(lat1.toRad())*Math.sin(lat2.toRad()) +
				Math.cos(lat1.toRad())*Math.cos(lat2.toRad())*Math.cos((lon2-lon1).toRad())) * R;
		return d;
		}
    var km2feet = 3280.83;

	function min(inArray) {				// returns index of least float value in inArray
		var minsofar =  40076.0;		// initialize to earth circumference (km)
		var j=-1;
		for (var i=1; i< inArray.length; i++){											// 11/12/09
			if ((lats[i]) &&  (parseFloat(inArray[i]) < parseFloat(minsofar))) { 		// 11/12/09
				j=i;
				minsofar=inArray[i];
				}
			}
		return (j>0) ? j: false;
		}		// end function min()

	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
function doReset() {
	document.reLoad_Form.submit();
	}	// end function doReset()
	
<?php
	$addrs = FALSE;												// notifies address array doesn't exist
	if (array_key_exists ( "email", $_GET)) {						// 10/23/08
		$addrs = notify_user(0,$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array or FALSE
		}				// end if (array_key_exists())

	$dispatches_disp = array();										// unit id to ticket descr	- 5/23/09
	$dispatches_act = array();										// actuals
	
	$query = "SELECT *, `$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,  `t`.`scope` AS `theticket`,
		`r`.`id` AS `theunit_id` 
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		AND ((`clear` IS NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";				// 6/25/10

	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if(!(empty($row['theunit_id']))) {
			$dispatches_act[$row['theunit_id']] = (empty($row['clear']))? $row['ticket_id']:"";	// blank = unit unassigned

			if ($row['multi']==1) {
				$dispatches_disp[$row['theunit_id']] = "**";					// identify as multiple - 5/22/09
				}
			else {
				$dispatches_disp[$row['theunit_id']] = (empty($row['clear']))? $row['theticket']:"";	// blank = unit unassigned
				}		// end if/else(...)
			}
		}		// end while (...)

//										8/10/09, 10/6/09, 1/7/10, 8/9/10
	$query = "SELECT *,
		UNIX_TIMESTAMP(problemstart) AS problemstart,
		UNIX_TIMESTAMP(problemend) AS problemend,
		UNIX_TIMESTAMP(booked_date) AS booked_date,		
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
		`rf`.`name` AS `rec_fac_name`,
		`rf`.`lat` AS `rf_lat`,
		`rf`.`lng` AS `rf_lng`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` 
		FROM `$GLOBALS[mysql_prefix]ticket`  
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)		
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`) 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`={$the_ticket_id} LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$row_ticket = stripslashes_deep(mysql_fetch_array($result));
	$facility = $row_ticket['facility'];
	$rec_fac = $row_ticket['rec_facility'];
	$lat = $row_ticket['lat'];
	$lng = $row_ticket['lng'];
	
	print "var thelat = " . $lat . ";\nvar thelng = " . $lng . ";\n";		// set js-accessible location data
//	unset ($result);

	if ($rec_fac > 0) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id`=" . $rec_fac . "";			// 10/6/09
		$result_rfc = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$row_rec_fac = stripslashes_deep(mysql_fetch_array($result_rfc));
		$rf_lat = $row_rec_fac['lat'];
		$rf_lng = $row_rec_fac['lng'];
		$rf_name = $row_rec_fac['name'];		
		
		unset ($result_rfc);
		} else {
//		print "var thereclat;\nvar thereclng;\n";		// set js-accessible location data for receiving facility
	}

	if(empty($_SESSION)) {session_start();}		// 

?>
var the_position;
function get_position () {
	var myDiv = document.getElementById('side_bar');
	var side_bar_width = myDiv.offsetWidth; 		
	var myDiv = document.getElementById('map_canvas');
	var map_width = myDiv.offsetWidth; 		
	the_position = side_bar_width + map_width + 10;
	}

</SCRIPT>
</HEAD>
<BODY onLoad = "get_position(); do_notify(); ck_frames()" onUnload="GUnload()">
<A NAME='top'>
	<DIV ID='to_bottom' style="position:fixed; top:2px; left:20px; height: 12px; width: 10px;" onclick = "location.href = '#page_bottom';"><IMG SRC="markers/down.png"  BORDER=0></div>

	<TABLE BORDER =0 ID= 'main' STYLE='display:block'>
	<TR><TD VALIGN='top' STYLE = 'height: 1px;'>
<?php
		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		unset($result);		
		$required = 96 + (mysql_affected_rows()*22);		// 7/9/10
		$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// set the max
?>
		<DIV ID='side_bar' style="height: <?php print $the_height; ?>px;  overflow-y: auto; overflow-x: auto;"></DIV><!-- 5/12/10 -->
<?php
	$the_width = get_variable('map_width');

	if ($show_tick_left) { 				// 11/27/09
		print "\n<BR>\n<DIV ID='the_ticket' STYLE='width: " .  get_variable('map_width') . "'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
		</TD>
		<TD VALIGN="top" ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
			<BR />
			<SPAN CLASS = "mylink" onClick ='doGrid()'>Grid</SPAN>
			<SPAN CLASS = "mylink" onClick ='doTraffic()'>Traffic</SPAN>
			<SPAN CLASS = "mylink" onClick = "sv_win('<?php print $row_ticket['lat'];?>','<?php print $row_ticket['lng'];?>' );">Street view</SPAN> <!-- 8/17/09 -->
			<SPAN CLASS = "warn" ID = "loading_2">Loading Directions, Please wait........</SPAN>
			<SPAN CLASS = "even" ID = "directions_ok_no">&nbsp;
			<SPAN CLASS = "other_1">Directions&nbsp;&raquo;</SPAN>
			<SPAN CLASS = "other_2">
<?php
		$checked_ok = ($_SESSION['allow_dirs'] =='true')? " CHECKED ": "";
		$checked_no = ($_SESSION['allow_dirs'] =='true')? "": " CHECKED ";
?>
				OK: <INPUT TYPE='radio' name='frm_dir' VALUE = true  <?php print $checked_ok; ?> onClick = "docheck(this.value);" />&nbsp;&nbsp;
				No: <INPUT TYPE='radio' name='frm_dir' VALUE = false <?php print $checked_no; ?> onClick = "docheck(this.value);" /></SPAN>
				&nbsp;</SPAN>
			<BR />
			<BR />
<?php
		print get_icon_legend ();
?>
			<BR /><BR />
<?php
	if (!($show_tick_left)) {				// 11/27/09
		print "\n<DIV ID='the_ticket' STYLE='width: " .  get_variable('map_width') . "'>\n";	
		print do_ticket($row_ticket, $the_width, FALSE, FALSE); 
		print "\n</DIV>\n";		
		}
?>
			<DIV ID="directions" STYLE="width: <?php print get_variable('map_width');?>"></DIV>
		</TD></TR></TABLE><!-- end outer -->
	<DIV ID='bottom' STYLE='display:none'>
	<CENTER>
	<H3>Dispatching ... please wait ...</H3><BR /><BR /><BR />
	</DIV>
		
	<FORM NAME='can_Form' ACTION="main.php" ><!-- 8/30/10 -->
	<INPUT TYPE='hidden' NAME = 'id' VALUE = "<?php print $_GET['ticket_id'];?>" />	
	</FORM>	

	<FORM NAME='routes_Form' METHOD='get' ACTION="<?php print basename( __FILE__); ?>"> <!-- 7/9/10 -->
	<INPUT TYPE='hidden' NAME='func' 			VALUE='do_db' />
	<INPUT TYPE='hidden' NAME='frm_ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />
	<INPUT TYPE='hidden' NAME='frm_by_id' 		VALUE= "<?php print $_SESSION['user_id'];?>" />
	<INPUT TYPE='hidden' NAME='frm_id_str' 		VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_name_str' 	VALUE= "" />
	<INPUT TYPE='hidden' NAME='frm_status_id' 	VALUE= "1" />
	<INPUT TYPE='hidden' NAME='frm_facility_id' 	VALUE= "<?php print $facility;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_rec_facility_id' VALUE= "<?php print $rec_fac;?>" /> <!-- 10/6/09 -->
	<INPUT TYPE='hidden' NAME='frm_comments' 	VALUE= "New" />
	<INPUT TYPE='hidden' NAME='frm_allow_dirs' VALUE = <?php print $_SESSION['allow_dirs']; ?> />	<!-- 11/21/09 -->
	</FORM>
	<!-- 8/2/09 -->
<?php
$from_left = round (0.5 * $_SESSION['scr_width']);
?>
	<DIV STYLE="position:fixed; width:60px; height:auto; top:<?php print $from_top;?>px; left:<?php print $from_left;?>px; background-color: transparent; text-align:left">	<!-- 5/17/09, 7/7/09 -->
		
<?php
			function get_addr(){				// returns incident address 11/27/09
				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id`={$_GET['ticket_id']} LIMIT 1";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(FILE__), __LINE__);
				$row = stripslashes_deep(mysql_fetch_array($result));
				return "{$row['street']}<br />{$row['city']}<br /> {$row['state']}"; 
				}		// end function get_addr()

			$thefunc = (is_guest())? "guest()" : "validate()";		// disallow guest attempts
			$nr_units = 1;
			$addr = get_addr();
?>
		<div id='boxB' class='box' style='left:<?php print $from_left;?>px;top:<?php print $from_top;?>px; position:fixed;' > <!-- 9/23/10 -->
		<div class="bar" style="width:12em;"
			 onmousedown="dragStart(event, 'boxB')">Drag me</div><!-- drag bar -->
			 <div style = 'height:20px;'/>&nbsp;</div>

<?php
			print "<SPAN ID='mail_button' STYLE='display: none'>";	//10/6/09
			print "<FORM NAME='email_form' METHOD = 'post' ACTION='do_direcs_mail.php' target='_blank' onsubmit='return mail_direcs(this);'>";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_direcs' VALUE='' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_u_id' VALUE='' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_mail_subject' VALUE='Directions to Incident' />";	//10/6/09
			print "<INPUT TYPE='hidden' NAME='frm_scope' VALUE='' />"; // 10/29/09
			print "<INPUT TYPE='submit' value='Mail Direcs' ID = 'mail_dir_but' />";	//10/6/09
			print "</FORM>";	
			print "<INPUT TYPE='button' VALUE='Reset' onClick = 'doReset()' />";
			print "</SPAN>";			
			print "<INPUT TYPE='button' VALUE='Cancel'  onClick='history.back();' />";
			if ($nr_units>0) {			
				print "<BR /><INPUT TYPE='button' value='DISPATCH\nUNITS' onClick = '" . $thefunc . "' />\n";	// 6/14/09
				}
			print "<BR /><BR /><SPAN STYLE='display: 'inline-block'><NOBR><H3>to:<BR /><I>{$addr}</I></H3></NOBR></SPAN>\n";
?>
		</div>	 <!-- end of outer -->
<?php
			print "<SPAN ID=\"loading\" STYLE=\"display: 'inline-block'\">";
			print "<TABLE BGCOLOR='red' WIDTH='80%'><TR><TD><FONT COLOR='white'><B>Loading Directions, Please wait........</B></FONT></TD></TR></TABLE>";		// 10/28/09
			print "</SPAN>";

?>
	</DIV>
		<IMG SRC='markers/up.png' BORDER=0  onclick = "location.href = '#top';" STYLE = "margin-left: 40px" />

		<A NAME="page_bottom" /> <!-- 5/13/10 -->	
		<FORM NAME='reLoad_Form' METHOD = 'get' ACTION="<?php print basename( __FILE__); ?>">
		<INPUT TYPE='hidden' NAME='ticket_id' 	VALUE='<?php print $_GET['ticket_id']; ?>' />	<!-- 10/25/08 -->
		</FORM>
	</BODY>

<?php
			if ($addrs) {				// 10/21/08
?>			
<SCRIPT>
	function do_notify() {
//		alert(352);
		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "ATTENTION - New Ticket: ";
		var theId = '<?php print $_GET['ticket_id'];?>';
		
//		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + escape(theId);		// ($to_str, $text, $ticket_id)   10/15/08
		var params = "frm_to="+ theAddresses + "&frm_text=" + theText + "&frm_ticket_id=" + theId ;		// ($to_str, $text, $ticket_id)   10/15/08
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function  - ignore returned data
		}

	function sendRequest(url,callback,postData) {
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
		req.open(method,url,true);
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
//				alert('HTTP error ' + req.status);
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp;
		}
	
</SCRIPT>
<?php

			}		// end if($addrs) 
		else {
?>		
<SCRIPT>
	function do_notify() {
//		alert(414);
		return;
		}			// end function do notify()
</SCRIPT>
<?php		
//	print __LINE__;
			}
	$unit_id = (array_key_exists('unit_id', $_GET))? $_GET['unit_id'] : "" ;
	print do_list($unit_id);
	print "</HTML> \n";

	}			// end if/else !empty($---)

?>