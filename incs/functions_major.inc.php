<?php
$iw_width = 	"300px";		// map infowindow with

/*
6/9/08  added  'Closed Calls' button
7/27/08 handle deleted status values
8/02/08 provide link to dispatch function
8/3/08  add assign data to unit IW's
8/6/08  added function do_tracks
8/15/08 mysql_fetch_array to mysql_fetch_assoc - performance
8/22/08 added usng position
8/24/08 revised sort order to include severity
8/25/08 added responders TITLE display
8/25/08 revised map control type to small - for TB
9/8/08  lat/lng to CG format
9/12/08 added USNG PHP functions
9/14/08 added js trim()
10/9/08 added check for div defined - IE JS pblm
10/14/08 changed reference to usng.js
10/15/08 changed 'Comments' to 'Disposition'
10/15/08 corrections re LL2NGS
10/16/08 added traffic functions
10/17/08 added hide_Units()
10/21/08 added edit link in infowindow
10/21/08 added  rand into link, istest as global
11/1/08 added prefix
11/06/08 sql error
11/6/08 missing table close tags corrected, timer for mini-map
11/29/08 added streetview
12/24/08 added GOverviewMapControl()
1/6/09  revised unit types for variable types
1/9/09  use icons subdir
1/10/09 dollar function added
1/17/09 caption changed to 'situation'
1/21/09 - drop aprs field fm unit types
1/23/09 tracks correction
1/25/09 do/don't show serial no.
1/27/09 revised sort order
1/29/09 revised icons array index
2/2/09 order sorts 'status=completed' last, unit status fix for non-existent keys.
2/11/09 added streetview function, removed redundant dollar function
2/12/09, 2/14/09 added persistence to show/hide units
2/13/09 added to_str() for no. units > 25
2/21/09 dropped infowindow from map
2/24/09 handle no-position units
3/2/09 corrected table caption
3/3/09 underline units sans position
3/16/09	get current aprs, instam updates
3/23/09 null added as possible value
3/23/09 is_float() replaces settype(), the latter not detecting 0, fix quotes
3/25/09 added time validation for remote sources, my_is_float()
4/2/09 correction for sidebar letters, added default zoom handling, closed ticket display interval
5/4/09 my_is_float() repl is_float
7/9/09 popups, per AH, COLOR='blue' correction
7/16/09	protocol display
7/27/09	'id' ambiguity resolved
7/29/09 Added Gtrack, Locatea and Google Latitude tracking sources, revised mobile speed icon display
7/29/09 Modified code to get tracking data, updated time and speed to fix errors. variable for updated and speed is now set before query result is unset. 
8/1/09 Added Facilities display
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/OSGB/UTM dependant on variable in tabs and sidebar.
8/3/09 Revised function popup_ticket to remove spurious listener.
8/7/09 Revised show/hide units and show hide incident markers
8/11/09 Revised code for incident popup to use function my_is_float to capture out units with no location
8/11/09 Added code to show responding units on incident details screen.
8/12/09 Revised MYSQL queries where there is an ambiguity between field names (description) in Ticket and In_types tables to correct ticket display
8/12/09	toUTM() parameters corrected
8/13/09	shorten() disposition, etc. 
8/19/09 drawCircle() added
9/29/09 Added Handling for Special Tickets
10/8/09 Index in list and on marker changed to part of name after / for both units and facilities
10/8/09 Added Display name to remove part of name after / in name field of sidebar and in infotabs for both units and facilities
10/21/09 Added hide/show for unavailable units in Situation map.
10/21/09 Added check for any closed or special incidents on the database before showing the buttons in the situation screen.
10/27/09 Added check for special incidents being due and bring to current situation screen if due and mark with * in list.
10/27/09 Added Booked date to Info Window tab 1 for ticket.
10/28/09 Added receiving facility to Info Window tab 1 for ticket
10/30/09 Added dispatch times and miles to ticket print, fixed action/patient print
10/30/09 Removed period after index in sidebar
11/06/09 Changed "Special" Incidents to "Scheduled" Incidents.
11/10/09 fixes to facilities display by AS
11/11/09 top/bottom anchors added
11/20/09 sort order handle, name
12/17/09 added unit status update functions
12/19/09 disable for guest priv's
1/1/10 style applied to <select>, relocated 'Closed incidents' link
1/2/10 $on_click implemented to release tr onclick event in favor of td onclick
1/7/10 re-arranged incident display, added 'call-taker' alias
1/27/10 map caption to page top
2/1/10 color-coded unit sidebar added, unit display order by # incidents assigned
2/6/10 function get_status_sel() moved to FIP
2/8/10 added units color-coding legend, calculate height allowed/required for units sidebar div
2/19/10 added offset handling for large closed lists 
3/2/10 add unit sidebar row hiding
3/8/10 revised SQL and JS for unit unavailable row show/hide, add show/hide delay
3/12/10 revise popup height
4/4/10 rewrite to_session ()
4/8/10 identify $chgd_unit
4/11/10 added count of units assigned and blink if none
5/11/10 disallow user/operator unit edit
5/15/10 added 'closed' handling
5/17/10 significant re-do of the ticket sidebar click handling
6/11/10 user-selectable unit sort added
6/25/10 responder SQL corrections for assigns count
6/26/10 handle 911 contact data
7/18/10 use responder position data, sidebar height
7/27/10 unit() user limitations added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/2/10 Revised alignment of ticket, responder and facility text in sidebar. Revised main query to specifically name street and city fields from tickets table to avoid confolict with responder fields of same name.
8/5/10 corrections applied re facilities  sidebar click handling
8/12/10 check $_POST for sort order
8/13/10 map.setUIToDefault();
8/27/10 use can_edit() in IW
8/30/10 dispatch status display added
9/29/10 use mysql2timestamp() for conversion
*/

//	{ -- dummy

function list_tickets($sort_by_field='',$sort_value='', $my_offset=0) {	// list tickets ===================================================
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, $routesfile;
	$time = microtime(true); // Gets microseconds

	global $istest, $iw_width, $units_side_bar_height, $do_blink;			// 2/8/10
//		$query = "SET @@global.sql_mode= 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';";
//		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	
	@session_start();		// 
	$captions = array(get_text("Current situation"), "Incidents closed today", "Incidents closed yesterday+", "Incidents closed this week", "Incidents closed last week", "Incidents closed last week+", "Incidents closed this month", "Incidents closed last month", "Incidents closed this year", "Incidents closed last year");
	$by_severity = array(0, 0, 0);				// counters // 5/2/10
	
	if (!(array_key_exists('func', $_GET))) {$func = 0;}
	else 									{extract ($_GET);}
	
	$cwi = get_variable('closed_interval');			// closed window interval in hours

	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	if (!isset($_GET['status'])) {
		$open = "Open";
	} else {
	$open = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_OPEN']))? "Open" : "";
	}

	$heading = $captions[($func)] . " - " . get_variable('map_caption');

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	@session_start(); 

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	unset($result);		
	$required = 48 + (mysql_affected_rows()*22);		// derived by trial and error - emphasis the latter = 7/18/10
	$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// see main for $units_side_bar_height value
?>
<TABLE BORDER=0>
	<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'><?php print $heading; ?> </FONT><SPAN ID='sev_counts' STYLE = 'margin-left: 40px'></SPAN></TD></TR>	<!-- 5/2/10 -->
	<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>
	<TR><TD align = 'left' VALIGN='TOP'  >
		<DIV ID = 'side_bar'></DIV>
		<DIV ID = 'side_bar_r' style="height: <?php print $the_height;?>px; overflow-y: scroll; overflow-x: hidden;"></DIV>
		<DIV STYLE = "height:12px">&nbsp;</DIV>
		<DIV ID = 'units_legend'></DIV>		
		<DIV ID = 'side_bar_f' style="height: <?php print $the_height;?>px; overflow-y: scroll; overflow-x: hidden;"></DIV>
		
		</TD>
		<TD></TD>
		<TD CLASS='td_label'>
			<DIV ID='map' STYLE='WIDTH: <?php print get_variable('map_width');?>PX; HEIGHT: <?php print get_variable('map_height');?>PX'></DIV>

			<BR /><CENTER><A HREF='#' onClick='doGrid()'><u>Grid</U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doTraffic()'><U>Traffic</U></A><BR /><BR />

		Units:<IMG STYLE = 'margin-left:12px;' SRC = './icons/sm_white.png' BORDER=0><IMG SRC = './icons/sm_black.png' BORDER=0>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 10/21/09 -->

		<SPAN ID="show_it" STYLE="display: none" onClick = "do_show_Units();"><U>Show</U></SPAN>
		<SPAN ID="hide_it" STYLE="display: ''" onClick = "do_hide_Units();"><U>Hide</U></SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<SPAN ID="hide_unavail" STYLE="display: ''" onClick = "hide_unit_stat_unavail();"><U>Hide unavailable</U></SPAN>	<!-- 10/21/09 -->

		<SPAN ID="show_unavail" STYLE="display: ''" onClick = "show_unit_stat_unavail();"><U>Show unavailable</U></SPAN>	<!-- 10/21/09 -->

		<BR /><BR />
		
		Facilities:<IMG  STYLE = 'margin-left:12px;' SRC = './icons/sm_shield_green.png' BORDER=0><IMG SRC = './icons/sm_square_red.png' BORDER=0>	<!-- 10/21/09 -->

		<SPAN  STYLE = 'margin-left:12px;' ID="hide_fac" STYLE="display: ''" onClick = "hide_Facilities();"><U>Hide</U></SPAN>
		<SPAN  STYLE = 'margin-left:12px;' ID="show_fac" STYLE="display: none" onClick = "show_Facilities();"><U>Show</U></SPAN>

		<BR /><BR />

		<SPAN ID="incidents" STYLE="display: inline-block">
		Incident Priority:<IMG STYLE = 'margin-left:12px;' SRC = './icons/sm_blue.png' BORDER=0><IMG SRC = './icons/sm_green.png' BORDER=0><IMG SRC = './icons/sm_red.png' BORDER=0>&nbsp;&nbsp;	<!-- 10/21/09 -->
		<A HREF="#" onClick = "hideGroup(1)">Typical: 	<IMG SRC = './icons/sm_blue.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp; <!-- 1/9/09 -->
		<A HREF="#" onClick = "hideGroup(2)">	High: 	<IMG SRC = './icons/sm_green.png' BORDER=0></A>&nbsp;&nbsp;&nbsp;&nbsp;
		<A HREF="#" onClick = "hideGroup(3)">Highest: 	<IMG SRC = './icons/sm_red.png' BORDER=0></A>
		</SPAN>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN ID="show_all_icon" STYLE="display: none"><A HREF="#" onClick = "show_All()">Show all: <IMG SRC = './markers/sm_white.png' BORDER=0></A></SPAN>
		</NOBR></CENTER><BR />

<BR />
		</TD>

		</CENTER><BR /></TD>
	</TR>

	<TR><TD COLSPAN='99'> </TD></TR>
	<TR><TD><IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#top';" STYLE = 'margin-left: 20px'></TD>
	<TD CLASS='td_label' COLSPAN=2 ALIGN='LEFT'>
		&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="mailto:shoreas@Gmail.com?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		

		</TD></TR></TABLE>
	<FORM NAME='unit_form' METHOD='get' ACTION="<?php print $_SESSION['unitsfile'];?>">				<!-- 7/28/10 -->
	<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' NAME='view' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE=''>
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='tick_form' METHOD='get' ACTION='<?php print $_SESSION['editfile'];?>'>				<!-- 11/27/09 7/28/10 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='sort_form' METHOD='post' ACTION='main.php'>				<!-- 6/11/10 -->
	<INPUT TYPE='hidden' NAME='order' VALUE=''>
	</FORM>

	<FORM NAME='facy_form_ed' METHOD='get' ACTION='<?php print $_SESSION['facilitiesfile'];?>'>		<!-- 8/3/10 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE='true'>
	</FORM>

<SCRIPT>
//================================= 7/18/10
	spe=500;
	NameOfYourTags="mi";
	swi=1;
	na=document.getElementsByName(NameOfYourTags);
	
	doBlink();
	
	function doBlink() {
		if (swi == 1) {
			sho="visible";
			swi=0;
			}
		else {
			sho="hidden";
			swi=1;
			}
	
		for(i=0;i<na.length;i++) {
			na[i].style.visibility=sho;
			}
		setTimeout("doBlink()", spe);
		}
	
	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	

	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function to_session(the_name, the_value) {									// generic session variable writer - 3/8/10, 4/4/10
		function local_handleResult(req) {			// the called-back function
			}			// end function local handleResult

		var params = "f_n=" + the_name;				// 1/20/09
		params += "&f_v=" + the_value;				// 4/4/10
		sendRequest ('do_session_get.php',local_handleResult, params);			// does the work via POST
		}


	function to_server(the_unit, the_status) {									// write unit status data via ajax xfer
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_un_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<?php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('Unit status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// e
			return AJAX.responseText;																				 
			} 
		else {
			alert ("<?php print __LINE__; ?>: failed");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

	var starting = false;
	
	function do_mail_win(the_name, the_addrs) {	
		if(starting) {return;}					// dbl-click catcher
		starting=true;
		var url = (isNull(the_name))? "do_unit_mail.php?" : "do_unit_mail.php?name=" + escape(the_name) + "&addrs=" + escape(the_addrs);	//
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=320,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()

	function do_fac_mail_win(the_name, the_addrs) {			// 3/8/10
		if(starting) {return;}					// dbl-click catcher
		starting=true;
		var url = (isNull(the_name))? "do_fac_mail.php?" : "do_fac_mail.php?name=" + escape(the_name) + "&addrs=" + escape(the_addrs);	//
		newwindow_mail=window.open(url, "mail_edit",  "titlebar, location=0, resizable=1, scrollbars, height=320,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (isNull(newwindow_mail)) {
			alert ("Email edit operation requires popups to be enabled -- please adjust your browser options.");
			return;
			}
		newwindow_mail.focus();
		starting = false;
		}		// end function do mail_win()


	function to_str(instr) {			// 0-based conversion - 2/13/09
		function ord( string ) {
		    return (string+'').charCodeAt(0);
			}

		function chr( ascii ) {
		    return String.fromCharCode(ascii);
			}
		function to_char(val) {
			return(chr(ord("A")+val));
			}

		var lop = (instr % 26);													// low-order portion, a number
		var hop = ((instr - lop)==0)? "" : to_char(((instr - lop)/26)-1) ;		// high-order portion, a string
		return hop+to_char(lop);
		}

	function sendRequest(url,callback,postData) {								// 2/14/09
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
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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

<?php
	$quick = ( (is_super() || is_administrator()) && (intval(get_variable('quick')==1)));				// 8/3/10
	print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var tr_id_fixed_part = "tr_id_";		// 3/2/10

if (GBrowserIsCompatible()) {

//	$("map").style.backgroundImage = "url(./markers/loading.jpg)";
	$("map").style.backgroundImage = "url('http://maps.google.com/staticmap?center=<?php echo get_variable('def_lat');?>,<?php echo get_variable('def_lng');?>&zoom=<?php echo get_variable('def_zoom');?>&size=<?php echo get_variable('map_width');?>x<?php echo get_variable('map_height');?>&key=<?php echo get_variable('gmaps_api_key');?> ')";

	var colors = new Array ('odd', 'even');

	function drawCircle(lat, lng, radius, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity) {		// 8/19/09
	
//		drawCircle(53.479874, -2.246704, 10.0, "#000080", 1, 0.75, "#0000FF", .5);

		var d2r = Math.PI/180;
		var r2d = 180/Math.PI;
		var Clat = radius * 0.014483;
		var Clng = Clat/Math.cos(lat * d2r);
		var Cpoints = [];
		for (var i=0; i < 33; i++) {
			var theta = Math.PI * (i/16);
			Cy = lat + (Clat * Math.sin(theta));
			Cx = lng + (Clng * Math.cos(theta));
			var P = new GPoint(Cx,Cy);
			Cpoints.push(P);
			}
		var polygon = new GPolygon(Cpoints, strokeColor, strokeWidth, strokeOpacity, fillColor, fillOpacity);
		map.addOverlay(polygon);
		}

	function hideGroup(color) {							// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				if (gmarkers[i].id == color) {
					gmarkers[i].show();
					}
				else {
					gmarkers[i].hide();			// 1/11/09
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("show_all_icon").style.display = "inline-block";
		$("incidents").style.display = "inline-block";

		}			// end function


	function show_All() {						// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
		$("show_all_icon").style.display = "none";
		$("allIcons").style.display = "inline-block";
		$("incidents").style.display = "inline-block";
		}			// end function


	function show_Units() {						// 8/7/09 Revised function to correct incorrect display
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0 - 2/12/09
			if (gmarkers[i]) {
				if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {
					gmarkers[i].show();
					}
				else {
//					gmarkers[i].hide();						// hide incidents - 1/8/09
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$('show_it').style.display='none';
		$('hide_it').style.display='inline';
		}

	function hide_Units () {								// 10/17/08
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				if ((gmarkers[i].id == 0) || (gmarkers[i].id == 4)) {			// 8/7/09 Revised function to correct incorrect display
					gmarkers[i].hide();
					}
				else {
					gmarkers[i].show();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()
		$("incidents").style.display = 		"inline-block";
		$("show_all_icon").style.display =	"inline-block";
		$("show_it").style.display=			"inline";				// 12/02/09
		$("hide_it").style.display=			"none";
		}				// end function hide_units ()
		
	function show_hide_rows(instr) {				// instr is '' or 'none' - 3/8/10
		for (i = 0; i< rowIds.length; i++) {
			var rowId = rowIds[i];					// row id - 3/3/10
			$(rowId).style.display = instr;			// hide each 'unavailable' row
			}
		}				// end function show_hide_rows()

	function hide_unit_stat_unavail() {								// 10/21/09
		for (var i = 0; i < gmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				if (gmarkers[i].stat == 1) {
					gmarkers[i].hide();
					}
				else {
					gmarkers[i].show();
					}
				}		// end if (gmarkers[i])
			} 	// end for ()

		show_hide_rows('none');								// hide 'unavailable' rows - 3/8/10

		$("incidents").style.display = 			"inline-block";
		$("show_all_icon").style.display =		"inline-block";
		$("show_unavail").style.display=		"inline";
		$("hide_unavail").style.display=		"none";
		to_session('show_hide_unit', "h");					// set session vbl
		}				// end function hide unit_stat_unavail ()		
		
	function show_unit_stat_unavail() {								// 10/21/09, 4/6/10
		for (var i = 0; i < gmarkers.length; i++) {					// traverse gmarkers array for icon type==0
			if (gmarkers[i]) {
				gmarkers[i].show();
				}
			} 	// end for ()
			
		show_hide_rows('');				// show 'unavailable' rows - 3/8/10
		
		$("incidents").style.display = "inline-block";
		$("show_all_icon").style.display	=	"inline-block";
		$("show_unavail").style.display		=	"none";
		setTimeout ('$("hide_unavail").style.display = "inline-block"', 250);		// 3/8/10
		to_session('show_hide_unit', "s");											// set session vbl
		}				// end function show unit_stat_unavail ()			

	function do_hide_Units() {						// 2/14/09
		var params = "f_n=show_hide_unit&v_n=h&sess_id=<?php print get_sess_key(__LINE__); ?>";					// flag 1, value h
		var url = "persist.php";
		sendRequest (url, h_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()

	function hide_Facilities() {								// 8/1/09
		for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (fmarkers[i]) {
					fmarkers[i].hide();
					}
			} 	// end for ()
		$("hide_fac").style.display = "none";
		$("show_fac").style.display = "inline-block";
		$("fac_table").style.display = "none";
		$("side_bar_f").style.display = "none";		
		to_session('show_hide_fac', "h");					// set session vbl
		}				// end function hide_Facilities ()

	function show_Facilities () {							// 8/1/09
		for (var i = 0; i < fmarkers.length; i++) {			// traverse gmarkers array for icon type==0
			if (fmarkers[i]) {
					fmarkers[i].show();
					}
			} 	// end for ()
		$("hide_fac").style.display = "inline-block";
		$("show_fac").style.display = "none";
		$("fac_table").style.display = "inline-block";
		$("side_bar_f").style.display = "inline-block";
		to_session('show_hide_fac', "s");					// set session vbl
		location.href = "#bottom";				// 11/11/09
		
		}				// end function show_Facilities ()

	function h_handleResult(req) {					// the 'called-back' persist function - hide
		hide_Units();
		}

	var starting = false;

	function do_mail_fac_win(id) {			// Facility email 9/22/09
		if(starting) {return;}					
		starting=true;	
		var url = "do_fac_mail.php?fac_id=" + id;	
		newwindow_in=window.open (url, 'Email_Window',  'titlebar, resizable=1, scrollbars, height=300,width=600,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300');
		if (isNull(newwindow_in)) {
			alert ("This requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_in.focus();
		starting = false;
		}

	function do_show_Units() {
		var params = "f_n=show_hide_unit&v_n=s&sess_id=<?php print get_sess_key(__LINE__); ?>";					// flag 1, value s
		var url = "persist.php";
		sendRequest (url, s_handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do show_Units()

	function s_handleResult(req) {					// the 'called-back' persist function - show
		show_Units();
		}

	function do_sel_update (in_unit, in_val) {							// 12/17/09
		to_server(in_unit, in_val);
		}

	function do_sidebar_unit (instr, id, sym, myclass, tip_str) {		// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		if (isNull(tip_str)) {
			side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[(id+1)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 2/6/10 moved onclick to TD
			}
		else {
			side_bar_html += "<TR ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(id+1)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
			}
		}		// end function do sidebar_unit ()

<?php		// 5/17/10
		if ((integer) $func > 0 ) 			{$js_func = "open_tick_window";}
		elseif (($quick) && (!is_guest())) 	{$js_func = "myclick_ed_tick";}
		else 								{$js_func = "myclick";}

?>	
	function open_tick_window (id) {				// 5/2/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}	

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		GEvent.trigger(gmarkers[id], "click");
		location.href = "#top";
		}

	function do_sidebar (instr, id, sym, myclass, tip_str) {		// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		side_bar_html += "<TR onClick = 'myclick(" + id + ");' ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[id%2] +"'>";
		side_bar_html += "<TD CLASS='" + myclass + "' ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
		}		// end function do sidebar ()

	function do_sidebar_t_ed (instr, line_no, rcd_id, letter, tip_str) {		// ticket edit, tip str added 1/3/10
		side_bar_html += "<TR onClick = '<?php print $js_func;?>(" + rcd_id + ")'; onMouseover=\"Tip('" + tip_str.replace("'", "") + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(line_no)%2] +"'>";		
		side_bar_html += "<TD CLASS='td_data'>" + letter + "</TD>" + instr +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		}

	function do_sidebar_u_iw (instr, id, sym, myclass) {						// constructs unit incident sidebar row - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='" + myclass + "'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 10/30/09 removed period
		}		// end function do sidebar ()

	function myclick_ed_tick(id) {				// Responds to sidebar click - edit ticket data
<?php
	$the_action = (is_guest()) ? "main.php" : $_SESSION['editfile'];				2/27/10
?>	
		document.tick_form.id.value=id;			// 11/27/09
		document.tick_form.action='<?php print $the_action; ?>';			// 11/27/09
		document.tick_form.submit();
		}

	function do_sidebar_u_ed (sidebar, line_no, on_click, letter) {					// unit edit 
		var tr_id = tr_id_fixed_part + line_no;
		side_bar_html += "<TR ID = '" + tr_id + "'  CLASS='" + colors[(line_no+1)%2] +"'>";
		side_bar_html += "<TD onClick = '" + on_click+ "' CLASS='td_data'>" + letter + "</TD>" + sidebar +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		}

	function myclick_nm(id) {				// Responds to sidebar click - view responder data
		document.unit_form.id.value=id;	// 11/27/09
		if (quick) {
			document.unit_form.edit.value="true";
			}
		else {
			document.unit_form.view.value="true";
			}
		document.unit_form.submit();
		}

	function do_sidebar_fac_ed (fac_instr, fac_id, fac_sym, myclass) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[fac_id%2] +"'>";
		side_bar_html += "<TD CLASS='" + myclass + "' onClick = fac_click_ed(" + fac_id + ");><B>" + (fac_sym) + "</B></TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_ed ()

	function do_sidebar_fac_iw (fac_instr, fac_id, fac_sym, myclass) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[fac_id%2] +"' WIDTH = '100%';>"
		side_bar_html += "<TD CLASS='" + myclass + "'><B>" + (fac_sym) + "</B></TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_iw ()

	function fac_click_iw(fac_id) {						// Responds to facilities sidebar click, triggers listener above 9/22/09
		GEvent.trigger(fmarkers[fac_id], "click");
		}

	function fac_click_ed(id) {							// Responds to facility sidebar click - edit data
		document.facy_form_ed.id.value=id;					// 11/27/09
		document.facy_form_ed.submit();
		}

	function createMarker(point, tabs, color, stat, id, sym) {					// Creates marker and sets up click event infowindow 10/21/09 added stat to hide unavailable units
		points = true;
		var icon = new GIcon(baseIcon);
		var icon_url = "./icons/gen_icon.php?blank=" + escape(icons[color]) + "&text=" + sym;				// 1/6/09
		icon.image = icon_url;

		var marker = new GMarker(point, icon);
		marker.id = color;				// for hide/unhide
		marker.stat = stat;				// 10/21/09

		GEvent.addListener(marker, "click", function() {					// here for both side bar and icon click

			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			marker.openInfoWindowTabsHtml(infoTabs[id]);

			setTimeout(function() {											// wait for rendering complete - 11/6/08
				if ($("detailmap")) {				// 10/9/08
					var dMapDiv = $("detailmap");
					var detailmap = new GMap2(dMapDiv);
					detailmap.addControl(new GSmallMapControl());
					detailmap.setCenter(point, 17);  						// larger # = closer
					detailmap.addOverlay(marker);
					}
				else {
//					alert($("detailmap"));
					}
				},3000);				// end setTimeout(...)

			});
		gmarkers[id] = marker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		if (!(map_is_fixed)){
			bounds.extend(point);
			}
		return marker;
		}				// end function create Marker()

	function createdummyMarker(point, tabs, id) {					// Creates dummymarker and sets up click event infowindow for "no maps" added tickets and units. 7/28/10 
		points = true;
		var icon = new GIcon(baseIcon);
		var icon_url = "./icons/question1.png";				// 7/28/10
		icon.image = icon_url;

		var dummymarker = new GMarker(point, icon);

		GEvent.addListener(dummymarker, "click", function() {

			map.closeInfoWindow();
			which = id;
			gmarkers[which].hide();
			dummymarker.openInfoWindowTabsHtml(infoTabs[id]);

			setTimeout(function() {
				if ($("detailmap")) {
					var dMapDiv = $("detailmap");
					var detailmap = new GMap2(dMapDiv);
					detailmap.addControl(new GSmallMapControl());
					detailmap.setCenter(point, 17);
					detailmap.addOverlay(dummymarker);
					}
				else {
//					alert($("detailmap"));
					}
				},3000);				// end setTimeout(...)

			});
		gmarkers[id] = dummymarker;							// marker to array for side_bar click function
		infoTabs[id] = tabs;							// tabs to array
		if (!(map_is_fixed)){
			bounds.extend(point);
			}
		return dummymarker;
		}				// end function create dummyMarker()
		
			var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			}
		grid = !grid;
		}			// end function doGrid

    var trafficInfo = new GTrafficOverlay();
    var toggleState = true;

	function doTraffic() {				// 10/16/08
		if (toggleState) {
	        map.removeOverlay(trafficInfo);
	     	}
		else {
	        map.addOverlay(trafficInfo);
	    	}
        toggleState = !toggleState;			// swap
	    }				// end function doTraffic()


	var icons=[];						// note globals
	icons[0] = 											 4;	// units white
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>+1] = 1;	// blue
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>+1] = 2;	// yellow
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  3;	// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+2] =  0;	// black

	var map;
	var center;
	var zoom = <?php echo get_variable('def_zoom'); ?>;
	
	var points = false;
<?php

$dzf = get_variable('def_zoom_fixed');
print "\tvar map_is_fixed = ";
print (($dzf==1) || ($dzf==3))? "true;\n":"false;\n";

$kml_olays = array();
$dir = "./kml_files";
$dh  = opendir($dir);
$i = 1;
$temp = explode ("/", $_SERVER['REQUEST_URI']);
$temp[count($temp)-1] = "kml_files";				//
$server_str = "http://" . $_SERVER['SERVER_NAME'] .":" .  $_SERVER['SERVER_PORT'] .  implode("/", $temp) . "/";
while (false !== ($filename = readdir($dh))) {
	if (!is_dir($filename)) {
	    echo "\tvar kml_" . $i . " = new GGeoXml(\"" . $server_str . $filename . "\");\n";
	    $kml_olays[] = "map.addOverlay(kml_". $i . ");";
	    $i++;
	    }
	}
?>

function do_add_note (id) {				// 8/12/09
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	noteWindow.focus();
	}

function do_sort_sub(sort_by){				// 6/11/10
	document.sort_form.order.value = sort_by;
	document.sort_form.submit();
	}
	
function do_track(callsign) {		
	if (parent.frames["upper"].logged_in()) {
//		if(starting) {return;}					// 6/6/08
//		starting=true;
		map.closeInfoWindow();
		var width = <?php print get_variable('map_width');?>+360;
		var spec ="titlebar, resizable=1, scrollbars, height=640,width=" + width + ",status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "track_u.php?source="+callsign;

		newwindow=window.open(url, callsign,  spec);
		if (isNull(newwindow)) {
			alert ("Track display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do track()

function do_popup(id) {					// added 7/9/09
	if (parent.frames["upper"].logged_in()) {
		map.closeInfoWindow();
		var mapWidth = <?php print get_variable('map_width');?>+32;
		var mapHeight = <?php print get_variable('map_height');?>+150;		// 3/12/10
		var spec ="titlebar, resizable=1, scrollbars, height=" + mapHeight + ", width=" + mapWidth + ", status=no,toolbar=no,menubar=no,location=0, left=100,top=300,screenX=100,screenY=300";
		var url = "incident_popup.php?id="+id;

		newwindow=window.open(url, id, spec);
		if (isNull(newwindow)) {
			alert ("Popup Incident display requires popups to be enabled. Please adjust your browser options.");
			return;
			}
//		starting = false;
		newwindow.focus();
		}
	}				// end function do popup()
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.4));?> >";
	side_bar_html += "<TR class='even'><TH colspan=99 align='center'>Click/Mouse-over for information </TH></TR>";
	side_bar_html += "<TR class='odd'><TD></TD><TD align='left' COLSPAN=2><B>Incident</B></TD><TD align='left'><B>Nature</B></TD><TD align='left'><B>&nbsp;Addr</B></TD><TD align='left'><B>P</B></TD><TD align='left'><B>A</B></TD><TD align='left'><B>U</B></TD><TD align='left'><B>&nbsp;&nbsp;As of</B></TD></TR>";
	var gmarkers = [];
	var fmarkers = [];
	var rowIds = [];		// 3/8/10
	var infoTabs = [];
	var facinfoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index

	$("show_unavail").style.display=			"none";				// 10/21/09
	$("hide_unavail").style.display=			"inline";

	map = new GMap2($("map"));		// create the map
	var geocoder = null;
	geocoder = new GClientGeocoder();

<?php
$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>

//	map.addControl(new GSmallMapControl());					// 8/25/08
	map.setUIToDefault();										// 8/13/10
	map.addControl(new GMapTypeControl());

	map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);

	mapBounds=new GLatLngBounds(map.getBounds().getSouthWest(), map.getBounds().getNorthEast());		// 4/4/09

	var bounds = new GLatLngBounds();						// create  bounding box
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>

	map.enableScrollWheelZoom();

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";		// ./markers/sm_shadow.png

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
	baseIcon.infoShadowAnchor = new GPoint(18, 25);
	GEvent.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		map.setCenter(center,zoom);
		map.addOverlay(gmarkers[which])
		});

<?php

	$order_by =  (!empty ($get_sortby))? $get_sortby: $_SESSION['sortorder']; // use default sort order?
																				//fix limits according to setting "ticket_per_page"
	$limit = "";
	if ($_SESSION['ticket_per_page'] && (check_for_rows("SELECT id FROM `$GLOBALS[mysql_prefix]ticket`") > $_SESSION['ticket_per_page']))	{
		if ($_GET['offset']) {
			$limit = "LIMIT $_GET[offset],$_SESSION[ticket_per_page]";
			}
		else {
			$limit = "LIMIT 0,$_SESSION[ticket_per_page]";
			}
		}
	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$time_back = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60) - ($cwi*3600));
	switch($func) {		
		case 0: 
			$where = "WHERE `status`='{$GLOBALS['STATUS_OPEN']}' OR 
				(`status`='{$GLOBALS['STATUS_SCHEDULED']}') OR 
				(`status`='{$GLOBALS['STATUS_CLOSED']}'  AND `problemend` >= '{$time_back}')";
			break;
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
			$the_start = get_start($func);		// mysql timestamp format 
			$the_end = get_end($func);
			$where = " WHERE `status`='{$GLOBALS['STATUS_CLOSED']}' AND `problemend` BETWEEN '{$the_start}' AND '{$the_end}' ";
			break;				
		default: print "error - error - error - error " . __LINE__;
		}				// end switch($func) 
	if ($sort_by_field && $sort_value) {					//sort by field?
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
			UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, in_types.type AS `type`, 
			in_types.id AS `t_id` FROM `$GLOBALS[mysql_prefix]ticket` 
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` ON `$GLOBALS[mysql_prefix]ticket`.`in_types_id`=in_types.id  
			WHERE $sort_by_field='$sort_value' $restrict_ticket ORDER BY $order_by";
		}
	else {					// 2/2/09, 8/12/09
		$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,
			UNIX_TIMESTAMP(booked_date) AS booked_date,	UNIX_TIMESTAMP(date) AS date, (`$GLOBALS[mysql_prefix]ticket`.`street`) AS ticket_street, (`$GLOBALS[mysql_prefix]ticket`.`state`) AS ticket_city, (`$GLOBALS[mysql_prefix]ticket`.`city`) AS ticket_state,
			UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.updated) AS updated,
			`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`,
			`$GLOBALS[mysql_prefix]in_types`.type AS `type`, `$GLOBALS[mysql_prefix]in_types`.`id` AS `t_id`,
			`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`, `$GLOBALS[mysql_prefix]ticket`.lat AS `lat`,
			`$GLOBALS[mysql_prefix]ticket`.lng AS `lng`, `$GLOBALS[mysql_prefix]facilities`.lat AS `fac_lat`,
			`$GLOBALS[mysql_prefix]facilities`.lng AS `fac_lng`, 
			`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
			(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
				WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `$GLOBALS[mysql_prefix]ticket`.`id`  
				AND `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' ) 
				AS `units_assigned`			
			FROM `$GLOBALS[mysql_prefix]ticket`			
			LEFT JOIN `$GLOBALS[mysql_prefix]in_types` 
				ON `$GLOBALS[mysql_prefix]ticket`.in_types_id=`$GLOBALS[mysql_prefix]in_types`.`id` 
			LEFT JOIN `$GLOBALS[mysql_prefix]facilities` 
				ON `$GLOBALS[mysql_prefix]ticket`.rec_facility=`$GLOBALS[mysql_prefix]facilities`.`id` 
			$where $restrict_ticket 
			ORDER BY `status` DESC, `severity` DESC, `$GLOBALS[mysql_prefix]ticket`.`id` ASC
			LIMIT 1000 OFFSET {$my_offset}";		// 2/2/09, 10/28/09, 2/21/10
		}

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$the_offset = (isset($_GET['frm_offset'])) ? (integer) $_GET['frm_offset'] : 0 ;
	$sb_indx = 0;				// note zero base!	

	$acts_ary = $pats_ary = array();				// 6/2/10
	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]action` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$acts_ary[$row['ticket_id']] = $row['the_count'];
		}
	
	$query = "SELECT `ticket_id`, COUNT(*) AS `the_count` FROM `$GLOBALS[mysql_prefix]patient` GROUP BY `ticket_id`";
	$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result_temp))) 	{
		$pats_ary[$row['ticket_id']] = $row['the_count'];
		}	
	
// ===========================  begin major while() for tickets==========
$temp  = (string) ( round((microtime(true) - $time), 3));
//	snap (__LINE__, $temp );
												
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) 	{		// 7/7/10
		$by_severity[$row['severity']] ++;															// 5/2/10

		if ($func > 0) {				// closed? - 5/16/10
			$onclick =  " open_tick_window({$row['tick_id']})";				
			}
		else {
			$onclick =  ($quick)? " myclick_ed_tick({$row['tick_id']}) ": "myclick({$sb_indx})";		// 1/2/10
			}

		if ((($do_blink)) && ($row['units_assigned']==0) && ($row['status']==$GLOBALS['STATUS_OPEN'])) {					// 4/11/10
			$blinkst = "<blink>";
			$blinkend ="</blink>";
			}
		else {$blinkst = $blinkend = "";
			}		
	
		$tip =  str_replace ( "'", "`", $row['contact'] . "/" .$row['ticket_street'] . "/" .$row['ticket_city'] . "/" .$row['ticket_state'] . "/" .$row['phone'] . "/" . $row['scope']);		// tooltip string - 1/3/10

		$sp = ($row['status'] == $GLOBALS['STATUS_SCHEDULED']) ? "*" : "";		
	
		print "\t\tvar scheduled = '$sp';\n";
?>
		var sym = (<?php print $sb_indx; ?>+1).toString();						// for sidebar
		var sym2= scheduled + (<?php print $sb_indx; ?>+1).toString();			// for icon
	
<?php
		$the_id = $row['tick_id'];		// 11/27/09
	
		if ($row['tick_descr'] == '') $row['tick_descr'] = '[no description]';	// 8/12/09
		if (get_variable('abbreviate_description'))	{	//do abbreviations on description, affected if neccesary
			if (strlen($row['tick_descr']) > get_variable('abbreviate_description')) {
				$row['tick_descr'] = substr($row['tick_descr'],0,get_variable('abbreviate_description')).'...';
				}
			}
		if (get_variable('abbreviate_affected')) {
			if (strlen($row['affected']) > get_variable('abbreviate_affected')) {
				$row['affected'] = substr($row['affected'],0,get_variable('abbreviate_affected')).'...';
				}
			}
		switch($row['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 				$severityclass='severity_normal'; break;
			}

		$A = array_key_exists ($the_id , $acts_ary)? $acts_ary[$the_id]: 0;		// 6/2/10
		$P = array_key_exists ($the_id , $pats_ary)? $pats_ary[$the_id]: 0;

		if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
			$strike = "<strike>"; $strikend = "</strike>";
			}
		else { $strike = $strikend = "";}
		
		$address_street=$row['ticket_street'] . " " . $row['ticket_city'];
		
		$sidebar_line = "<TD ALIGN='left' CLASS='$severityclass'  COLSPAN=2><NOBR>$strike" . $sp . shorten($row['scope'],20) . " $strikend</NOBR></TD>";	//10/27/09, 8/2/10
		$sidebar_line .= "<TD ALIGN='left' CLASS='$severityclass'><NOBR>$strike" . shorten($row['type'], 20) . " $strikend</NOBR></TD>";	// 8/2/10
		$sidebar_line .= "<TD ALIGN='left' CLASS='$severityclass'><NOBR>$strike" . shorten(($row['ticket_street'] . ' ' . $row['ticket_city']), 20) . " $strikend</NOBR></TD>";	// 8/2/10
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $P . " </TD><TD CLASS='td_data'> " . $A . " </NOBR></TD>";

		$sidebar_line .= "<TD CLASS='td_data'>{$blinkst}{$row['units_assigned']}{$blinkend}</TD>";
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($row['updated']) . "</NOBR></TD>";
	
		if (my_is_float($row['lat'])) {		// 6/21/10
			$street = empty($row['ticket_street'])? "" : $row['ticket_street'] . "<BR/>" . $row['ticket_city'] . " " . $row['ticket_state'] ;
			$todisp = (is_guest()|| is_unit())? "": "&nbsp;<A HREF='{$_SESSION['routesfile']}?ticket_id={$the_id}'><U>Dispatch</U></A>";	// 7/27/10
		
			$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08
		
			$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>$strike" . shorten($row['scope'], 48)  . "$strikend</B></TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
			if (is_int($row['booked_date'])){
				$tab_1 .= "<TR CLASS='odd'><TD>Booked Date:</TD><TD>" . format_date($row['booked_date']) . "</TD></TR>";	//10/27/09
				}
			$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>$address_street</TD></TR>";
	
			$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (intval(get_variable('delta_mins'))*60));
			$elapsed = my_date_diff($row['problemstart'], $end_date);		// 5/13/10
	
			$tab_1 .= "<TR CLASS='odd'><TD ALIGN='left'>Status:</TD><TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;($elapsed)</TD></TR>";	// 3/27/10
			$tab_1 .= (empty($row['fac_name']))? "" : "<TR CLASS='even'><TD>Receiving Facility:</TD><TD>" . shorten($row['fac_name'], 30)  . "</TD></TR>";	//3/27/10
			$utm = get_variable('UTM');
			if ($utm==1) {
				$coords =  $row['lat'] . "," . $row['lng'];																	// 8/12/09
				$tab_1 .= "<TR CLASS='even'><TD>UTM grid:</TD><TD>" . toUTM($coords) . "</TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><FONT SIZE='-1'>";
			$tab_1 .= 	$todisp . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>";		// 08/8/02
			if (!(is_guest() )) {
				if (can_edit()) {							//8/27/10
					$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['editfile']}?id=" . $the_id . $rand . "'><U>Edit</U></A><BR /><BR />";	
					}
				$tab_1 .= 	"&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_popup('" . $the_id  . "');><FONT COLOR='blue'><B><U>Popup</B></U></FONT></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;	// 7/7/09
				$tab_1 .= 	"<SPAN onClick = 'do_add_note (" . $the_id . ");'><FONT COLOR='blue'><B><U>Add note</B></U></FONT></SPAN><BR /><BR />" ;	// 7/7/09
				if (can_edit()) {							//8/27/10
					$tab_1 .= 	"<A HREF='patient.php?ticket_id=" . $the_id . $rand ."'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 7/9/09
					$tab_1 .= 	"<A HREF='action.php?ticket_id=" . $the_id . $rand ."'><U>Add Action</U></A>";
					}
				}
			$tab_1 .= 	"</FONT></TD></TR></TABLE>";			// 11/6/08
		
		
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";	// 8/12/09
			$tab_2 .= "<TR CLASS='even'>	<TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['tick_descr']), 48) . "</TD></TR>";	// str_replace("\r\n", " ", $my_string)
			$tab_2 .= "<TR CLASS='odd'>		<TD>Disposition:</TD><TD>" . shorten($row['comments'], 48) . "</TD></TR>";		// 8/13/09
			$tab_2 .= "<TR CLASS='even'>	<TD>911 contact:</TD><TD>" . shorten($row['nine_one_one'], 48) . "</TD></TR>";	// 6/26/10
		
			$locale = get_variable('locale');	// 08/03/09
			switch($locale) { 
				case "0":
				$tab_2 .= "<TR CLASS='odd'>	<TD>USNG:</TD><TD>" . LLtoUSNG($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "1":
				$tab_2 .= "<TR CLASS='odd'>	<TD>OSGB:</TD><TD>" . LLtoOSGB($row['lat'], $row['lng']) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				case "2":
				$coords =  $row['lat'] . "," . $row['lng'];							// 8/12/09
				$tab_2 .= "<TR CLASS='odd'>	<TD>UTM:</TD><TD>" . toUTM($coords) . "</TD></TR>";	// 8/23/08, 10/15/08, 8/3/09
				break;
			
				default:
				print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
				}
		
			$tab_2 .= "<TR>					<TD COLSPAN=2>" . show_assigns(0, $the_id) . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'>	<TD COLSPAN=2 ALIGN='center'>";
			$tab_2 .= $todisp . "&nbsp;&nbsp;<A HREF='main.php?id=" . $the_id . "'><U>Details</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";	// 08/8/02
			if (!(is_guest() && get_variable('guest_add_ticket')==0)) {
				$tab_2 .= 	"<BR /><A HREF='patient.php?ticket_id=" . $the_id . "'><U>Add Patient</U></A>&nbsp;&nbsp;&nbsp;&nbsp;";
				$tab_2 .= 	"<A HREF='action.php?ticket_id=" . $the_id . "'><U>Add Action</U></A>&nbsp;&nbsp;";
				}
			$tab_2 .= 	"</TD></TR></TABLE>";		// 11/6/08
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['scope'], 12));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("More ..", "<?php print str_replace($eols, " ", $tab_2);?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
		
<?php
			if (($row['lat'] == "0.999999") && ($row['lng'] == "0.999999")) {	// check for lat and lng values set in no maps state 7/28/10
?>			
				var point = new GLatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);	// for each ticket
				if (!(map_is_fixed)){																// 4/3/09
					bounds.extend(point);
					}
	
				var dummymarker = createdummyMarker(point, myinfoTabs, <?php print $sb_indx; ?>);	// (point,tabs, id) - plots dummy icon in default position for tickets added in no maps operation 7/28/10
				map.addOverlay(dummymarker);
				var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";	
<?php
				} else {
?>
			var point = new GLatLng(<?php print $row['lat'];?>, <?php print $row['lng'];?>);	// for each ticket
			if (!(map_is_fixed)){																// 4/3/09
				bounds.extend(point);
				}
	
			var marker = createMarker(point, myinfoTabs,<?php print $row['severity']+1;?>, 0, <?php print $sb_indx; ?>, sym2);	// (point,tabs, color, id, sym) - 1/6/09, 10/21/09 added 0 for stat display to avoid conflicts with unit marker hide by unavailable status
			map.addOverlay(marker);
			var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
			
<?php
				}		// end of check for no maps markes
			}		// end if (my_is_float($row['lat']))
			
		if (($quick) || ((integer) $func > 0 )) {		// 5/18/10
			print "\t\t	do_sidebar_t_ed (\"{$sidebar_line}\", ({$the_offset} + {$sb_indx}), {$row['tick_id']}, sym, \"{$tip}\");\n";
			}
		else {
			print "\t\t do_sidebar (\"{$sidebar_line}\", {$sb_indx}, ({$the_offset} + {$sb_indx}+1), the_class, \"{$tip}\");\n";
			}
		if (intval($row['radius']) > 0) {
			$color= (substr($row['color'], 0, 1)=="#")? $row['color']: "#000000";		// black default
?>	
//			drawCircle(				38.479874, 				-78.246704, 						50.0, 					"#000080",						 1, 		0.75,	 "#0000FF", 					.2);
			drawCircle(	<?php print $row['lat']?>, <?php print $row['lng']?>, <?php print $row['radius']?>, "<?php print $color?>", 1, 	0.75, "<?php print $color?>", .<?php print $row['opacity']?>);
<?php
			}				// end if (intval($row['radius']) 

			$sb_indx++;
			}				// end tickets while ($row = ...)
$temp  = (string) ( round((microtime(true) - $time), 3));
//		snap (__LINE__, $temp );

?>
		side_bar_html +="<TR><TD COLSPAN=99 ALIGN='center'>\n";
//		side_bar_html +="\t\t<SPAN STYLE =  'margin-left: 60px'><U>Change display</U>&nbsp;&raquo;&nbsp;</SPAN>\n";
		side_bar_html +="\t\t<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >\n";
		side_bar_html +="\t\t<SELECT NAME = 'frm_interval' onChange = 'document.to_closed.func.value=this.value; show_btns_closed();'>\n";
		side_bar_html +="\t\t<OPTION VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='0'><?php print get_text("Current situation"); ?></OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='1'>Incidents closed today</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='2'>Incidents closed yesterday+</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='3'>Incidents closed this week</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='4'>Incidents closed last week</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='5'>Incidents closed last week+</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='6'>Incidents closed this month</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='7'>Incidents closed last month</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='8'>Incidents closed this year</OPTION>\n";
		side_bar_html +="\t\t<OPTION VALUE='9'>Incidents closed last year</OPTION>\n";
		side_bar_html +="\t\t</SELECT>\n</FORM>\n";
		side_bar_html +="\t\t<SPAN ID = 'btn_go' onClick='document.to_closed.submit()' STYLE = 'margin-left: 10px; display:none'><U>Next</U></SPAN>";
		side_bar_html +="\t\t<SPAN ID = 'btn_can'  onClick='hide_btns_closed(); ' STYLE = 'margin-left: 10px; display:none'><U>Cancel</U></SPAN>";
		side_bar_html +="<br /><br /></TD></TR>\n";

<?php
		
		if ($sb_indx == 0) {
			$txt_str = ($func>0)? "closed tickets this period!": "current tickets!";
			print "\n\t\tside_bar_html += \"<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><I><B>No {$txt_str}</B></I></TD></TR>\";";
			print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD COLSPAN='99' ><BR /><BR /></TD></TR>\";";
			}
		$limit = 1000;
		$link_str = "";
		$query= "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = '{$GLOBALS['STATUS_CLOSED']}'";
		$result_cl = mysql_query($query) or do_error($query,'mysql_query',mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_affected_rows() > $limit) {
			$sep = ", ";
			$rcds = mysql_affected_rows();
			for ($j=0; $j < (ceil($rcds / $limit)); $j++) {
				$sep = ($j==ceil($rcds / $limit)-1) ? "" : ", ";
				$temp = (string)($j * $limit);
				$link_str .= "<SPAN onClick = 'document.to_closed.frm_offset.value={$temp}; document.to_closed.submit();'><U>" . ($j+1) . "K</U></SPAN>{$sep}";
				}				
			}
		$sev_string = "" . get_text("Severities") . ": <SPAN CLASS='severity_normal'>" . get_text("Normal") . " ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_medium'>" . get_text("Medium") . " ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_high'>" . get_text("High") . " ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})</SPAN>";

		unset($acts_ary, $pats_ary, $result_temp, $result_cl);

?>			

	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML = side_bar_html;				// side_bar_html to incidents div 
	$('sev_counts').innerHTML = "<?php print $sev_string; ?>";			// 5/2/10
	

// ==========================================      RESPONDER start    ================================================

	side_bar_html ="<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.4));?> >\n";		// initialize units sidebar string
	points = false;
	i++;
	var j=0;

<?php

	$u_types = array();												// 1/1/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]unit_types` ORDER BY `id`";		// types in use
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$u_types [$row['id']] = array ($row['name'], $row['icon']);		// name, index, aprs - 1/5/09, 1/21/09
		}
	unset($result);

	$assigns = array();					// 8/3/08
	$tickets = array();					// ticket id's

	$query = "SELECT `$GLOBALS[mysql_prefix]assigns`.`ticket_id`, `$GLOBALS[mysql_prefix]assigns`.`responder_id`, `$GLOBALS[mysql_prefix]ticket`.`scope` AS `ticket` FROM `$GLOBALS[mysql_prefix]assigns` LEFT JOIN `$GLOBALS[mysql_prefix]ticket` ON `$GLOBALS[mysql_prefix]assigns`.`ticket_id`=`$GLOBALS[mysql_prefix]ticket`.`id`";

	$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_as = stripslashes_deep(mysql_fetch_array($result_as))) {
		$assigns[$row_as['responder_id']] = $row_as['ticket'];
		$tickets[$row_as['responder_id']] = $row_as['ticket_id'];
		}
	unset($result_as);

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
	$status_vals = array();											// build array of $status_vals
	$status_vals[''] = $status_vals['0']="TBD";

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]un_status` ORDER BY `id`";
	$result_st = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row_st = stripslashes_deep(mysql_fetch_array($result_st))) {
		$temp = $row_st['id'];
		$status_vals[$temp] = $row_st['status_val'];
		$status_hide[$temp] = $row_st['hide'];
		}

	unset($result_st);

	$assigns_ary = array();				// construct array of responder_id's on active calls
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ((`clear` IS  NULL) OR (DATE_FORMAT(`clear`,'%y') = '00')) ";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$assigns_ary[$row['responder_id']] = TRUE;
		}
	$order_values = array(1 => "`nr_assigned` DESC,  `handle` ASC, `r`.`name` ASC", 2 => "`type_descr` ASC, `handle` ASC",  3 => "`stat_descr` ASC, `handle` ASC" , 4 => "`handle` ASC");	// 6/24/10

//	if (!(empty($_POST)))						{$_SESSION['unit_flag_2'] =  $_POST['order'];}		// 6/11/10
	if (array_key_exists ('order' , $_POST))	{$_SESSION['unit_flag_2'] =  $_POST['order'];}		// 8/12/10
	elseif (empty ($_SESSION['unit_flag_2'])) 	{$_SESSION['unit_flag_2'] = 1;}

	$order_str = $order_values[$_SESSION['unit_flag_2']];											// 6/11/10
																									// 6/25/10
	$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated`, `t`.`id` AS `type_id`, `r`.`id` AS `unit_id`, `r`.`name` AS `name`,
		`s`.`description` AS `stat_descr`,  `r`.`description` AS `unit_descr`, `t`.`description` AS `type_descr`,
		(SELECT  COUNT(*) as numfound FROM `$GLOBALS[mysql_prefix]assigns` 
			WHERE (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = unit_id ) 
			AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )) 
			AS `nr_assigned` 
		FROM `$GLOBALS[mysql_prefix]responder` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
		LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 		
		ORDER BY {$order_str}";											// 2/1/10, 3/8/10, 6/11/10

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$units_ct = mysql_affected_rows();			// 1/4/10
	if ($units_ct==0){
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TH></TH><TH ALIGN='center' COLSPAN=99><I><B>No units!</I></B></TH></TR>\"\n";
		}
	else {
		$checked = array ("", "", "", "");
		$checked[$_SESSION['unit_flag_2']] = " CHECKED";
?>	
	side_bar_html += "<TR CLASS = 'even'><TD COLSPAN=99 ALIGN='center'>";
	side_bar_html += "<I><B>Sort</B>:&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Unit &raquo; 	<input type = radio name = 'frm_order' value = 1 <?php print $checked[1];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Type &raquo; 	<input type = radio name = 'frm_order' value = 2 <?php print $checked[2];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Status &raquo; <input type = radio name = 'frm_order' value = 3 <?php print $checked[3];?> onClick = 'do_sort_sub(this.value);' />";
	side_bar_html += "</I></TD></TR>";
<?php	
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD></TD><TD><B>Unit</B> ({$units_ct}) </TD>	<TD onClick = 'do_mail_win(null, null); ' ALIGN = 'center'><IMG SRC='mail_red.png' /></TD><TD>&nbsp; <B>Status</B></TD><TD ALIGN='left' COLSPAN='2'><B>Incident</B></TD><TD><B>M</B></TD><TD><B>&nbsp;As of</B></TD></TR>\"\n" ;
		}

	$aprs = $instam = $locatea = $gtrack = $glat = FALSE;		//7/23/09


	$bulls = array( 0 => "", 1 => "red", 2 => "green", 3 => "white", 4 => "black");
	$utc = gmdate ("U");				// 3/25/09

// ===========================  begin major while() for RESPONDER ==========

	$chgd_unit = $_SESSION['unit_flag_1'];					// possibly 0 - 4/8/10
	$_SESSION['unit_flag_1'] = 0;							// one-time only - 4/11/10
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// 7/7/10
		$latitude = $row['lat'];		// 7/18/10		
		$longitude = $row['lng'];		// 7/18/10

		$on_click =  ((!(my_is_float($row['lat']))) || ($quick))? " myclick_nm({$row['unit_id']}) ": "myclick({$sb_indx})";		// 1/2/10
		$got_point = FALSE;

		$name = $row['name'];			//	10/8/09
		$temp = explode("/", $name );
		$index =  (strlen($temp[count($temp) -1])<3)? substr($temp[count($temp) -1] ,0,strlen($temp[count($temp) -1])): substr($temp[count($temp) -1] ,-3 ,strlen($temp[count($temp) -1]));		
		
		print "\t\tvar sym = '$index';\n";				// for sidebar and icon 10/8/09		
												// 2/13/09
		$todisp = ((is_guest()) || (is_unit()))? "": "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";		// 08/8/02
		$toedit = ((is_guest()) || (is_user()) || (is_unit()) )? "" :"&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&edit=true&id=" . $row['unit_id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 7/27/10
		$totrack  = ((intval($row['mobile'])==0)||(empty($row['callsign'])))? "" : "&nbsp;&nbsp;<SPAN onClick = do_track('" .$row['callsign']  . "');><B><U>Tracks</B></U>&nbsp;&nbsp;</SPAN>" ;
		$tofac = (is_guest())? "": "<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&dispfac=true&id=" . $row['unit_id'] . "'><U>To Facility</U></A>&nbsp;&nbsp;";	// 08/8/02

		$hide_unit = ($row['hide']=="y")? "1" : "0" ;		// 3/8/10

		if ($row['aprs']==1) {				// get most recent aprs position data
			$query = "SELECT *,UNIX_TIMESTAMP(packet_date) AS `packet_date`, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks`
				WHERE `source`= '$row[callsign]' ORDER BY `packet_date` DESC LIMIT 1";		// newest
			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_aprs = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$aprs_updated = $row_aprs['updated'];
			$aprs_speed = $row_aprs['speed'];
			if (($row_aprs) && (my_is_float($row_aprs['latitude']))) {
				$latitude = $row_aprs['latitude'];  $longitude = $row_aprs['longitude'];			// 7/7/10
				echo "\t\tvar point = new GLatLng(" . $row_aprs['latitude'] . ", " . $row_aprs['longitude'] ."); // 677\n";
				$got_point = TRUE;

				}
			unset($result_tr);
			}
		else { $row_aprs = FALSE; }

		if ($row['instam']==1) {			// get most recent instamapper data
			$temp = explode ("/", $row['callsign']);			// callsign/account no. 3/22/09

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_instam = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$instam_updated = $row_instam['updated'];
			$instam_speed = $row_instam['speed'];
			if (($row_instam) && (my_is_float($row_instam['latitude']))) {											// 4/29/09
				$latitude = $row_instam['latitude'];  $longitude = $row_instam['longitude'];			// 7/7/10
				echo "\t\tvar point = new GLatLng(" . $row_instam['latitude'] . ", " . $row_instam['longitude'] ."); // 724\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_instam = FALSE; }

		if ($row['locatea']==1) {			// get most recent locatea data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_locatea = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$locatea_updated = $row_locatea['updated'];
			$locatea_speed = $row_locatea['speed'];
			if (($row_locatea) && (my_is_float($row_locatea['latitude']))) {
				$latitude = $row_locatea['latitude'];  $longitude = $row_locatea['longitude'];			// 7/7/10
				echo "\t\tvar point = new GLatLng(" . $row_locatea['latitude'] . ", " . $row_locatea['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_locatea = FALSE; }

		if ($row['gtrack']==1) {			// get most recent gtrack data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_gtrack = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$gtrack_updated = $row_gtrack['updated'];
			$gtrack_speed = $row_gtrack['speed'];
			if (($row_gtrack) && (my_is_float($row_gtrack['latitude']))) {
				$latitude = $row_gtrack['latitude'];  $longitude = $row_gtrack['longitude'];			// 7/7/10
				echo "\t\tvar point = new GLatLng(" . $row_gtrack['latitude'] . ", " . $row_gtrack['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_gtrack = FALSE; }

		if ($row['glat']==1) {			// get most recent latitude data		// 7/23/09
			$temp = explode ("/", $row['callsign']);			// callsign/account no.

			$query = "SELECT *, UNIX_TIMESTAMP(updated) AS `updated` FROM `$GLOBALS[mysql_prefix]tracks_hh`
				WHERE `source` LIKE '$temp[0]%' ORDER BY `updated` DESC LIMIT 1";		// newest

			$result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_glat = (mysql_affected_rows()>0)? stripslashes_deep(mysql_fetch_assoc($result_tr)) : FALSE;
			$glat_updated = $row_glat['updated'];
			if (($row_glat) && (my_is_float($row_glat['latitude']))) {
				$latitude = $row_glat['latitude'];  $longitude = $row_glat['longitude'];			// 7/7/10
				echo "\t\tvar point = new GLatLng(" . $row_glat['latitude'] . ", " . $row_glat['longitude'] ."); // 687\n";
				$got_point = TRUE;
				}
			unset($result_tr);
			}
		else { $row_glat = FALSE; }

		if (!($got_point) && ((my_is_float($row['lat'])))) {
			echo "\t\tvar point = new GLatLng(" . $row['lat'] . ", " . $row['lng'] .");	// 753\n";
			$got_point= TRUE;
			}

		$the_bull = "";											// define the bullet
		$update_error = strtotime('now - 6 hours');				// set the time for silent setting

		if ($row['aprs']==1) {
			if ($row_aprs) {
				$spd = 2;										// default
				if($aprs_speed == 0) {$spd = 1;}			// stopped
				if($aprs_speed >= 50) {$spd = 3;}		// fast
				}
			else {
				$spd = 0;				// no data
				}
			$the_bull = "<FONT COLOR=" . $bulls[$spd] .">AP</FONT>";
			}			// end aprs

		if ($row['instam']==1) {
			if ($instam_speed>=50) {$the_bull = "<FONT COLOR = 'white'>IN</FONT>";}
			if ($instam_speed<50) {$the_bull = "<FONT COLOR = 'green'>IN</FONT>";}
			if ($instam_speed==0) {$the_bull = "<FONT COLOR = 'red'>IN</FONT>";}
			if ($instam_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'>IN</FONT>";}
			}

		if ($row['locatea']==1) {
			if ($locatea_speed>=50) {$the_bull = "<FONT COLOR = 'white'>LO</FONT>";}		// 7/23/09
			if ($locatea_speed<50) {$the_bull = "<FONT COLOR = 'green'>LO</FONT>";}
			if ($locatea_speed==0) {$the_bull = "<FONT COLOR = 'red'>LO</FONT>";}
			if ($locatea_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'>LO</FONT>";}
			}

		if ($row['gtrack']==1) {
			if ($gtrack_speed>=50) {$the_bull = "<FONT COLOR = 'white'>GT</FONT>";}		// 7/23/09
			if ($gtrack_speed<50) {$the_bull = "<FONT COLOR = 'green'>GT</FONT>";}
			if ($gtrack_speed==0) {$the_bull = "<FONT COLOR = 'red'>GT</FONT>";}
			if ($gtrack_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'>GT</FONT>";}
			}
		if ($row['glat']==1) {
			$the_bull = "<FONT COLOR = 'green'>GL</FONT>";		// 7/23/09
			if ($glat_updated < $update_error) {$the_bull = "<FONT COLOR = 'black'>GL</FONT>";}
			}
						// end bullet stuff
// NAME

		$name = $row['name'];		//	10/8/09
		$temp = explode("/", $name );
		$display_name = $temp[0];

		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
		$arrow = ($chgd_unit == $row['unit_id'])? "<IMG SRC='rtarrow.gif' />" : "" ; 	// 4/8/10
		$sidebar_line = "<TD ALIGN='left' onClick = '{$on_click}'>{$arrow}<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>  " . shorten($display_name, 16) . "</B></U></SPAN></TD>";

//		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
//		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
//		$arrow = ($chgd_unit == $row['unit_id'])? "<IMG SRC='rtarrow.gif' />" : "" ; 	// 4/8/10
//		$sidebar_line = "<TD onClick = '{$on_click}'>{$arrow}<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>  " . shorten($display_name, 16) . "</B></U></SPAN></TD>";

// MAIL						
		if ((!is_guest()) && is_email($row['contact_via'])) {		// 2/1/10
			$mail_link = "\t<TD  CLASS='mylink' ALIGN='center'>"
				. "&nbsp;<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit {$display_name}'"
				. " onclick = 'do_mail_win(\\\"{$display_name},{$row['contact_via']}\\\");'> "
				. "&nbsp;</TD>";		// 4/26/09
				}
		else {
			$mail_link = "\t<TD ALIGN='center'>na</TD>";
			}
		$sidebar_line .= $mail_link;
// STATUS
		$sidebar_line .= "<TD>" . get_status_sel($row['unit_id'], $row['un_status_id'], "u") . "</TD>";		// status


// DISPATCHES 3/16/09

		if(!(array_key_exists ($row['unit_id'] , $assigns_ary))) {			// this unit assigned? - 6/4/10
			$row_assign = FALSE; }
		else {																// 6/25/10
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns`  
				LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ($GLOBALS[mysql_prefix]assigns.ticket_id = t.id)
				WHERE `responder_id` = '{$row['unit_id']}' AND ( `clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )";
	
			$result_as = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row_assign = (mysql_affected_rows()==0)?  FALSE : stripslashes_deep(mysql_fetch_assoc($result_as)) ;
			unset($result_as);
			}
		$tip = (!$row_assign)?
	   		"":  
			str_replace ( "'", "`",    ("{$row_assign['contact']}/{$row_assign['street']}/{$row_assign['city']}/{$row_assign['phone']}/{$row_assign['scope']}   "));

		switch($row_assign['severity'])		{		//color tickets by severity
		 	case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
			case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
			default: 							$severityclass='severity_normal'; break;
			}

		switch (mysql_affected_rows()) {		// 8/30/10
			case 0:
				$the_disp_stat="";
				break;			
			case 1:
				$the_disp_stat =  get_disp_status ($row_assign) . "&nbsp;";
				break;
			default:							// multiples
			    $the_disp_stat = "<SPAN CLASS='disp_stat'>&nbsp;" . mysql_affected_rows() . "&nbsp;</SPAN>&nbsp;";
			    break;
			}						// end switch()
	
		$ass_td =  (mysql_affected_rows()>0)? 
			"<TD ALIGN='left' onMouseover=\\\"Tip('{$tip}')\\\" onmouseout=\\\"UnTip()\\\" onClick = '{$on_click}' COLSPAN=2 CLASS='$severityclass' >{$the_disp_stat}" . shorten($row_assign['scope'], 24) . "</TD>":
			"<TD onClick = '{$on_click}' > na </TD>";

		$sidebar_line .= ($row_assign)? $ass_td : "<TD COLSPAN=2>na</TD>";

//  MOBILITY
		$sidebar_line .= "<TD onClick = '{$on_click}' CLASS='td_data'> " . $the_bull . "</TD>";

// as of
		$strike = $strike_end = "";										// any remote source?
		if ((($row['instam']==1) && $row_instam ) || (($row['aprs']==1) && $row_aprs ) || (($row['locatea']==1) && $row_locatea ) || (($row['gtrack']==1) && $row_gtrack ) || (($row['glat']==1) && $row_glat )) {
			$the_class = "emph";
			if ($row['aprs']==1) {															// 3/24/09
				$the_time = $aprs_updated;
				$instam = TRUE;				// show footer legend
				}
			if ($row['instam']==1) {															// 3/24/09
				$the_time = $instam_updated;
				$instam = TRUE;				// show footer legend
				}
			if ($row['locatea']==1) {															// 7/23/09
				$the_time = $locatea_updated;
				$locatea = TRUE;				// show footer legend
				}
			if ($row['gtrack']==1) {															// 7/23/09
				$the_time = $gtrack_updated;
				$gtrack = TRUE;				// show footer legend
				}
			if ($row['glat']==1) {																// 7/23/09
				$the_time = $glat_updated;
				$glat = TRUE;				// show footer legend
				}
		} else {
			$the_time = $row['updated'];
//			$the_class = "td_data";
			$the_class = "";
			}

		if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
			$strike = "<STRIKE>";
			$strike_end = "</STRIKE>";
			} 
		else {
			$strike = $strike_end = "";
			}

//		$sidebar_line .= "<TD onClick = '{$on_click}' CLASS='$the_class'> {$strike}" . format_sb_date($the_time) . "{$strike_end} {$row['nr_assigned']}</TD>";	// 6/17/08
		$sidebar_line .= "<TD onClick = '{$on_click}' CLASS='$the_class'> {$strike}" . format_sb_date($the_time) . "{$strike_end} </TD>";	// 6/17/08

// tab 1

		if (((my_is_float($row['lat']))) || ($row_aprs) || ($row_instam) || ($row_locatea) || ($row_gtrack) || ($row_glat)) {						// 5/4/09
			$temptype = $u_types[$row['type']];
			$the_type = $temptype[0];																	// 1/1/09

			$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['name'], 48) . "</B> - " . $the_type . "</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Description:</TD><TD>" . shorten(str_replace($eols, " ", $row['unit_descr']), 32) . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>Status:</TD><TD> {$row['stat_descr']}</TD></TR>";
			$tab_1 .= "<TR CLASS='odd'><TD>Contact:</TD><TD>" . $row['contact_name']. " Via: " . $row['contact_via'] . "</TD></TR>";
			$tab_1 .= "<TR CLASS='even'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
			if (array_key_exists($row['unit_id'], $assigns)) {
				$tab_1 .= "<TR CLASS='even'><TD CLASS='emph'>Dispatched to:</TD><TD CLASS='emph'><A HREF='main.php?id=" . $tickets[$row['unit_id']] . "'>" . shorten($assigns[$row['unit_id']], 20) . "</A></TD></TR>";
				}
			$tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $tofac . $todisp . $totrack . $toedit . "&nbsp;&nbsp;<A HREF='{$_SESSION['unitsfile']}?func=responder&view=true&id=" . $row['unit_id'] . "'><U>View</U></A></TD></TR>";	// 08/8/02
			$tab_1 .= "</TABLE>";

// tab 2
		$tabs_done=FALSE;
		if ($row_aprs) {		// three tabs if APRS data
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_aprs['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_aprs['course'] . ", Speed:  " . $row_aprs['speed'] . ", Alt: " . $row_aprs['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>Closest city: </TD><TD>" . $row_aprs['closest_city'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Status: </TD><TD>" . $row_aprs['status'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_aprs['packet_date']) . " $strike_end (UTC)</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;

?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("APRS <?php print addslashes(substr($row_aprs['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}	// end if ($row_aprs)

		if ($row_instam) {		// three tabs if instam data
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_instam['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_instam['course'] . ", Speed:  " . $row_instam['speed'] . ", Alt: " . $row_instam['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_instam['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Instam <?php print addslashes(substr($row_instam['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_instam)

		if ($row_locatea) {		// three tabs if locatea data		7/23/09
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_locatea['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_locatea['course'] . ", Speed:  " . $row_locatea['speed'] . ", Alt: " . $row_locatea['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_locatea['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("LocateA <?php print addslashes(substr($row_locatea['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if ($row_gtrack) {		// three tabs if gtrack data		7/23/09
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_2 .="<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . $row_gtrack['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>Course: </TD><TD>" . $row_gtrack['course'] . ", Speed:  " . $row_gtrack['speed'] . ", Alt: " . $row_gtrack['altitude'] . "</TD></TR>";
			$tab_2 .= "<TR CLASS='even'><TD>As of: </TD><TD> $strike " . format_date($row_gtrack['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Gtrack <?php print addslashes(substr($row_gtrack['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if ($row_glat) {		// three tabs if glat data			7/23/09
			$tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$tab_2 .="<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . $row_glat['source'] . "</B></TD></TR>";
			$tab_2 .= "<TR CLASS='odd'><TD>As of: </TD><TD> $strike " . format_date($row_glat['updated']) . " $strike_end</TD></TR></TABLE>";
			$tabs_done=TRUE;
//			print __LINE__;
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("G Lat <?php print addslashes(substr($row_glat['source'], -3)); ?>", "<?php print $tab_2;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>") // 830
				];
<?php
			}	// end if ($row_gtrack)

		if (!($tabs_done)) {	// else two tabs
?>
			var myinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(shorten($row['name'], 10));?>", "<?php print $tab_1;?>"),
				new GInfoWindowTab("Zoom", "<div id='detailmap' class='detailmap'></div>")
				];
<?php
			}		// end if(!($tabs_done))
		
		}		// end position data available

	if ((!(my_is_float($row['lat']))) || ($quick)) {		// 11/27/09
		print "\t\tdo_sidebar_u_ed (\"{$sidebar_line}\",  {$sb_indx}, '{$on_click}', sym, \"{$tip}\");\n";		// (sidebar, line_no, on_click, letter)
		}
	else {
?>
		var the_class = ((map_is_fixed) && (!(mapBounds.containsLatLng(point))))? "emph" : "td_label";
		do_sidebar_unit ("<?php print $sidebar_line; ?>",  <?php print $sb_indx; ?>, sym, the_class, "<?php print $tip;?>");		// (instr, id, sym, myclass, tip)  - 1/3/10
<?php
		}

	if (my_is_float($latitude)) {		// map position?
		$the_color = ($row['mobile']=="1")? 0 : 4;		// icon color black, white		-- 4/18/09

		if (($latitude == "0.999999") && ($longitude == "0.999999")) {	// check for no maps added points 7/28/10
			$dummylat = get_variable('def_lat');
			$dummylng = get_variable('def_lng');			
			echo "\t\tvar point = new GLatLng(" . $dummylat . ", " . $dummylng ."); // 677\n";
?>
			var dummymarker = createdummyMarker(point, myinfoTabs, <?php print $sb_indx; ?>);	// 859  - 7/28/10. Plots dummy icon in default position for units added in no maps operation
			map.addOverlay(dummymarker);
<?php
		} else {
?>
			var marker = createMarker(point, myinfoTabs, <?php print $the_color;?>, <?php print $hide_unit;?>,  <?php print $sb_indx; ?>, sym); // 7/28/10
			map.addOverlay(marker);
<?php
			} // end check for no maps added points
		}				// end if (my_is_float())
	if ($row['hide']=="y") {						// 3/8/10		
?>
		var rowId = tr_id_fixed_part + <?php print $sb_indx; ?>;			// row index for row hide/show - 3/2/10
		rowIds.push(rowId);													// form is "tr_id_??" where ?? is the row no.
<?php
		}											// end if ($row['hide']=="y")
	$sb_indx++;				// zero-based
	}				// end  ==========  while() for RESPONDER ==========
$temp  = (string) ( round((microtime(true) - $time), 3));
//	snap (__LINE__, $temp );

	$source_legend = (($aprs)||($instam)||($gtrack)||($locatea)||($glat))? "<TD CLASS='emph' ALIGN='left'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09

	print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN=7 ALIGN='center'>{$source_legend}</TD></TR>\";\n";

?>
	$("side_bar_r").innerHTML = side_bar_html;										// side_bar_html to responders div	
	
	var legends = "<TABLE ALIGN='center'><TR CLASS='even'><TD COLSPAN='99' ALIGN='center'>&nbsp;&nbsp;<B>M</B>obility:&nbsp;&nbsp; stopped: <FONT COLOR='red'>&bull;</FONT>&nbsp;&nbsp;&nbsp;moving: <FONT COLOR='green'>&bull;</FONT>&nbsp;&nbsp;&nbsp;fast: <FONT COLOR='white'>&bull;</FONT>&nbsp;&nbsp;&nbsp;silent: <FONT COLOR='black'>&bull;</FONT>&nbsp;&nbsp;</TD></TR>";
	legends+= "<TR CLASS='" + colors[(i)%2] +"'><TD COLSPAN='99' ALIGN='center'><?php print get_units_legend();?></TD></TR>";

	$("units_legend").innerHTML = legends + "</TABLE>";

<?php
	print ($_SESSION['show_hide_unit'] == "s")? "\t show_unit_stat_unavail();\n" : "\t hide_unit_stat_unavail();\n"; 	//3/8/10, 4/4/10
?>
// ==================================== Add Facilities to Map 8/1/09 ================================================
	side_bar_html ="<TABLE border='0' COLSPAN=99 ALIGN='left' ID='fac_table' CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.39));?> >\n";
	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

	$("hide_fac").style.display = "none";
	$("show_fac").style.display = "inline-block";

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
	}

<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, 
		`$GLOBALS[mysql_prefix]facilities`.description AS facility_description,
		`$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id 
		ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";

	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$temp = max(320, intval($_SESSION['scr_width']* 0.4));
	print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TH COLSPAN=99 ALIGN='center'><BR>Facilities</TH></TR>\"\n";
	$mail_str = (may_email())? "do_fac_mail_win();": "";		// 7/2/10
	print (mysql_affected_rows()==0)?
		"\n\t\tside_bar_html += \"<TR CLASS='even'><TH ALIGN='center'><I><B>No Facilities!</I></B></TH></TR>\"\n" :
		"\n\t\tside_bar_html += \"<TR CLASS='even'><TD colspan=2></TD><TD ALIGN='left'><B>Facility</B></TD><TD ALIGN='left'><IMG SRC='mail_red.png' BORDER=0 onClick = '{$mail_str}'/></TD><TD ALIGN='center'><B>Type</B></TD><TD ALIGN='left'><B>Status</B></TD><TD ALIGN='left'><B>&nbsp;As of</B></TD></TR>\"\n";	// 7/2/10

// ===========================  begin major while() for FACILITIES ==========
	
	$quick = (!(is_guest()) && (intval(get_variable('quick')==1)));				// 11/27/09		
	$sb_indx = 0;																// for fac's only 8/5/10

	while($row_fac = mysql_fetch_assoc($result_fac)){		// 7/7/10
		$fac_id=($row_fac['fac_id']);
		$fac_type=($row_fac['icon']);
	
		$fac_name = $row_fac['facility_name'];		//		10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));	//	 11/10/09

		$on_click= ($quick)? "fac_click_ed({$fac_id})" : $clickevent="fac_click_iw({$sb_indx})";	// 8/5/10
			
		print "\t\tvar fac_sym = '$fac_index';\n";			//	 for sidebar and icon 10/8/09
		
		$toroute = (is_guest() || is_unit())? "": "&nbsp;<A HREF='{$_SESSION['routesfile']}?ticket_id=" . $fac_id . "'><U>Dispatch</U></A>";// 11/10/09, 7/27/10
	
		if(is_guest() || is_unit()) {		// 7/27/10
			$facedit = $toroute = $facmail = "";
			}
		else {
			$facedit = "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&edit=true&id=" . $row_fac['fac_id'] . "'><U>Edit</U></A>" ;
			$facmail = "&nbsp;&nbsp;&nbsp;&nbsp;<SPAN onClick = do_mail_fac_win('" .$row_fac['fac_id']  . "');><U><B>Email</B></U></SPAN>" ;
			$toroute = "&nbsp;<A HREF='{$_SESSION['facroutesfile']}?fac_id=" . $fac_id . "'><U>Route To Facility</U></A>";//	 8/2/08
			}
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
			$f_disp_name = $row_fac['facility_name'];	//		10/8/09
			$f_disp_temp = explode("/", $f_disp_name );
			$facility_display_name = $f_disp_temp[0];

			$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row_fac['icon']];		// 2/8/10
			$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']];		// 2/8/10			
	
			$sidebar_fac_line = "&nbsp;&nbsp;&nbsp;&nbsp;<TD onClick = '{$on_click}' TITLE = '" . addslashes($facility_display_name) . "' ALIGN='left'><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};' >" . addslashes(shorten($facility_display_name, 38)) ."</SPAN></TD>";

// MAIL						
			if ((may_email()) && ((is_email($row_fac['contact_email'])) || (is_email($row_fac['security_email']))) ) {		// 7/2/10

				$mail_link = "\t<TD CLASS='mylink' ALIGN='left'>"
					. "<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email facility {$f_disp_temp[0]}'"
					. " onclick = 'do_mail_win(\\\"{$f_disp_temp[0]},{$row_fac['contact_email']}\\\");'> "
					. "</TD>";		// 4/26/09
					}
			else {
				$mail_link = "\t<TD ALIGN='left'>na</TD>";
				}
			$sidebar_fac_line .= $mail_link;

			$sidebar_fac_line .= "<TD ALIGN='left'  onClick = '{$on_click};' >&nbsp;&nbsp;&nbsp;" . addslashes(shorten($row_fac['fac_type_name'],16)) ."</TD>";
			$sidebar_fac_line .= "<TD ALIGN='left'  onClick = '{$on_click};' >" . addslashes($row_fac['status_val']) ."</TD>";
			$sidebar_fac_line .= "<TD onClick = '{$on_click};' >&nbsp;" . format_sb_date($row_fac['updated']) . "</TD>";
	
			$fac_tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'> " . format_date($row_fac['updated']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $facedit . $facmail . "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
//			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'>" . $toroute . $facedit ."&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$_SESSION['facilitiesfile']}?func=responder&view=true&id=" . $row_fac['fac_id'] . "'><U>View</U></A></TD></TR>";
			$fac_tab_1 .= "</TABLE>";
	
			$fac_tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE>";
			
			?>
//			var fac_sym = (g + 1).toString();			//	 11/12/09
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
<?php
			if(($row_fac['lat']==0.999999) && ($row_fac['lng']==0.999999)) {	// check for facilities entered in no maps mode 7/28/10
	
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/question1.png\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . get_variable('def_lat') . "," . get_variable('def_lng') . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
			} else {
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
				}

?>
				if (fac_marker.isHidden()) {
					fac_marker.show();
				} else {
					fac_marker.hide();
				}
<?php
				}//	 end if my_is_float
?>
			if(quick) {					//	 set up for facility edit - 11/27/09
				do_sidebar_fac_ed ("<?php print $sidebar_fac_line;?>", <?php print $row_fac['fac_id'];?>, fac_sym, fac_icon);		
				}
			else {				//	 set up for facility infowindow
				do_sidebar_fac_iw ("<?php print $sidebar_fac_line;?>", g, fac_sym, fac_icon);
				}
			g++;
<?php
	$sb_indx++;				// zero-based - 6/30/10
	}	// end while()
//	dump($sidebar_fac_line);
$temp  = (string) ( round((microtime(true) - $time), 3));
//	snap (__LINE__, $temp );

?>
	side_bar_html += "</TD></TR>\n";
<?php

// =====================================End of functions to show facilities========================================================================

	for ($i = 0; $i<count($kml_olays); $i++) {				// emit kml overlay calls
		echo "\t\t" . $kml_olays[$i] . "\n";
		}
?>
	if (!(map_is_fixed)){
		if (!points) {		// any?
			map.setCenter(new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
			}
		else {
			center = bounds.getCenter();
			zoom = map.getBoundsZoomLevel(bounds);
			map.setCenter(center,zoom);
			}			// end if/else (!points)
	}				// end if (!(map_is_fixed))

<?php


//	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `problemend` IS NOT NULL ";		// 10/21/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 1 ";		// 10/21/09

		$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_closed = mysql_num_rows($result_ct); 
		unset($result_ct);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 3 ";		// 10/21/09
		$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_scheduled = mysql_num_rows($result_scheduled); 
		unset($result_scheduled);

	if(!empty($addon)) {
		print "\n\tside_bar_html +=\"" . $addon . "\"\n";
		}

	if(empty($open)) {									// 6/9/08  added button
		print "\n\tvar current_button = \"<INPUT TYPE='button' VALUE='" . get_text("Current situation") . "' onClick = 'document.to_all.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + current_button + \"</TD></TR>\";\n";
		}

	if((empty($scheduled)) && ($num_scheduled > 0)) {								// 9/29/09  added button for scheduled incidents, 10/21/09 added check for scheduled incidents on the database
		print "\n\tvar scheduled_button = \"<INPUT TYPE='button' VALUE='Scheduled Incidents' onClick = 'document.to_scheduled.submit()'>\"\n";
		print "\n\tside_bar_html+= \"<TR><TD COLSPAN=99 ALIGN='center'><BR>\" + scheduled_button + \"</TD></TR>\";\n";
		}

?>
	side_bar_html +="<TR><TD COLSPAN='99'></TD></TR><TR><TD COLSPAN=99><TABLE ALIGN='center'>";

	side_bar_html +="<?php print get_facilities_legend();?>";		// legend row
	side_bar_html +="</TABLE></TD></TR></TABLE>\n";
	$("side_bar_f").innerHTML = side_bar_html;	//side_bar_html to facilities div

	side_bar_html = "";

<?php
	print ($_SESSION['show_hide_fac'] == "s")? "\t\t show_Facilities();\n" : "\t\t hide_Facilities();\n"; 	//3/8/10

	switch ($_SESSION['show_hide_unit']) {		// persistence flags 2/14/09
		case NULL:						// default 3/23/09
		case " ":						//
		case "s":
			print "\tshow_Units();\n";
		    break;
		case "h":
			print "\thide_Units();\n";
		    break;
		default:
		    echo "error" . __LINE__ . "\n";
		}
?>


// =============================================================================================================
	}		// end if (GBrowserIsCompatible())
else {
	alert("Sorry, browser compatibility problem. Contact your tech support group.");
	}
</SCRIPT>
<?php
	echo "Time Elapsed: ".round((microtime(true) - $time), 3)."s";

	}				// end function list_tickets() ===========================================================


//	} { -- dummy

function show_ticket($id,$print='false', $search = FALSE) {								/* show specified ticket */
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, $routesfile;
	global $iw_width, $istest, $zoom_tight;																		// 3/27/10
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}


	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
										// 1/7/10
	$query = "SELECT *,
		`problemstart` AS `my_start`,
		FROM_UNIXTIME(UNIX_TIMESTAMP(problemstart)) AS `test`,
		UNIX_TIMESTAMP(problemstart) AS problemstart,
		UNIX_TIMESTAMP(problemend) AS problemend,
		UNIX_TIMESTAMP(date) AS date,
		UNIX_TIMESTAMP(booked_date) AS booked_date,		
		UNIX_TIMESTAMP(`$GLOBALS[mysql_prefix]ticket`.`updated`) AS updated,		
		`$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
		`$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
		`$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
		`$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,		
		`$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,		
		`$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
		`$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
		`$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,		
		`rf`.`name` AS `rec_fac_name`,
		`$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,		
		`$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng`,		 
		`$GLOBALS[mysql_prefix]ticket`.`id` AS `tick_id`
		FROM `$GLOBALS[mysql_prefix]ticket` 
		LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)	
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON `$GLOBALS[mysql_prefix]facilities`.id = `$GLOBALS[mysql_prefix]ticket`.facility 
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` rf ON `rf`.id = `$GLOBALS[mysql_prefix]ticket`.rec_facility 
		WHERE `$GLOBALS[mysql_prefix]ticket`.`ID`= $id $restrict_ticket";			// 7/16/09, 8/12/09


	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));

    $locale = get_variable('locale');    // 10/29/09
    switch($locale) {
        case "0":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
        break;

        case "1":
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);    // 8/23/08, 10/15/08, 8/3/09
        break;
   
        case "2":
        $coords =  $row['lat'] . "," . $row['lng'];                                    // 8/12/09
        $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);    // 8/23/08, 10/15/08, 8/3/09
        break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
        }


	if ($print == 'true') {				// 1/7/10

		print "<TABLE BORDER='0'ID='left' width='800px'>\n";		//
		print "<TR CLASS='print_TD'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>" . get_text("Incident") . ": <I>" . $row['scope'] . "</B>" . $tickno . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> 
					<TD ALIGN='left'>" . get_severity($row['severity']);
		print 		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nature:&nbsp;&nbsp;" . get_type($row['in_types_id']);
		print "</TD></TR>\n";
	
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left'>{$row['protocol']}</TD></TR>\n";		// 7/16/09
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>	
				<TD ALIGN='left'>" .  $row['tick_street'] . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("City") . ":</TD>		
				<TD ALIGN='left'>" .  $row['tick_city'];
		print 		"&nbsp;&nbsp;" .  $row['tick_state'] . "</TD></TR>\n";
		print "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>
				<TD ALIGN='left'>" .  nl2br($row['tick_descr']) . "</TD></TR>\n";	//	8/12/09

		print "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>
				<TD ALIGN='left'>" .  nl2br($row['nine_one_one']) . "</TD></TR>\n";	//	8/12/09

		$end_date = (intval($row['problemend'])> 1)? $row['problemend']:  (time() - (intval(get_variable('delta_mins'))*60));

		$elapsed = my_date_diff($end_date, $end_date);
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Status") . ":</TD>	
				<TD ALIGN='left'>" . get_status($row['status']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$elapsed}</TD></TR>\n";
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>
				<TD ALIGN='left'>" . $row['contact'] . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>		
				<TD ALIGN='left'>" . format_phone ($row['phone']) . "</TD></TR>\n";
		$by_str = ($row['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($row['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
		print "<TR CLASS='print_TD'><TD ALIGN='left'>" . get_text("Written") . ":</TD>	
				<TD ALIGN='left'>" . format_date($row['date']) . $by_str;
		print 		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date($row['updated']) . "</TD></TR>\n";
		print empty($row['booked_date']) ? "" : "<TR CLASS='print_TD'><TD ALIGN='left'>Scheduled date:</TD>	
				<TD ALIGN='left'>" . format_date($row['booked_date']) . "</TD></TR>\n";	// 10/6/09	
		print "<TR CLASS='print_TD' ><TD ALIGN='left' COLSPAN='2'>&nbsp;
				<TD ALIGN='left'></TR>\n";			// separator
		print empty($row['fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>Incident at Facility:</TD>	
				<TD ALIGN='left'>" .  $row['fac_name'] . "</TD></TR>\n";	// 8/1/09, 3/27/10
		print empty($row['rec_fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>Receiving Facility:</TD>	
				<TD ALIGN='left'>" .  $row['rec_fac_name'] . "</TD></TR>\n";	// 10/6/09	
		print empty($row['comments'])? "" : "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>Disposition:</TD>
				<TD ALIGN='left'>" .  nl2br($row['comments']) . "</TD></TR>\n";	
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD>				
				<TD ALIGN='left'>" . format_date($row['problemstart']);
		$elapsed_str = (!(empty($closed)))? $elapsed : "" ;				
		print	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($row['problemend']) . "&nbsp;&nbsp;{$elapsed_str}</TD></TR>\n";
	
		$locale = get_variable('locale');	// 08/03/09
		switch($locale) { 
			case "0":
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($row['lat'], $row['lng']);
				break;
	
			case "1":
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($row['lat'], $row['lng']);	// 8/23/08, 10/15/08, 8/3/09
				break;
		
			case "2":
				$coords =  $row['lat'] . "," . $row['lng'];									// 8/12/09
				$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
				break;
	
			default:
			print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
		}
	
		print "<TR CLASS='print_TD'><TD ALIGN='left' >" . get_text("Position") . ": </TD>		
				<TD ALIGN='left'>" . get_lat($row['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($row['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08
	
		print "<TR><TD colspan=2 ALIGN='left'>";
		print show_log ($row[0]);				// log
		print "</TD></TR>";
	
		print "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $row['lat'] . "</SPAN><SPAN ID='oldlng'>" . $row['lng'] . "</SPAN></TD></TR>";
		print "</TABLE>\n";

		print show_actions($row['tick_id'], "date", FALSE, FALSE);		// lists actions and patient data, print - 10/30/09

// =============== 10/30/09 

		function my_to_date($in_date) {			// date_time format to user's spec
//			$temp = mktime(substr($in_date,11,2),substr($in_date,14,2),substr($in_date,17,2),substr($in_date,5,2),substr($in_date,8,2),substr($in_date,0,4));
			$temp = mysql2timestamp($d1);		// 9/29/10
			return (good_date_time($in_date)) ?  date(get_variable("date_format"), $temp): "";		// 
			}
/*
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `facility_id` IS NOT NULL LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$facilities = mysql_affected_rows()>0;		// set boolean in order to avoid waste space

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE `start_miles` IS NOT NULL  LIMIT 1";
		$result_temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		$miles = mysql_affected_rows()>0;		// set boolean in order to avoid waste space
		unset($result_temp);

		$query = "SELECT *,
		UNIX_TIMESTAMP(as_of) AS as_of,
		`$GLOBALS[mysql_prefix]assigns`.`id` AS `assign_id` ,
		`$GLOBALS[mysql_prefix]assigns`.`comments` AS `assign_comments`,
		`u`.`user` AS `theuser`,
		`t`.`scope` AS `theticket`,
		`t`.`description` AS `thetickdescr`,
		`t`.`status` AS `thestatus`,
		`t`.`_by` AS `call_taker`,
		`r`.`id` AS `theunitid`,
		`r`.`name` AS `theunit` ,
		`f`.`name` AS `thefacility`,
		`g`.`name` AS `the_rec_facility`,
		`$GLOBALS[mysql_prefix]assigns`.`as_of` AS `assign_as_of`
		FROM `$GLOBALS[mysql_prefix]assigns` 
		LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`$GLOBALS[mysql_prefix]assigns`.`user_id` = `u`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`$GLOBALS[mysql_prefix]assigns`.`responder_id` = `r`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`$GLOBALS[mysql_prefix]assigns`.`facility_id` = `f`.`id`)
		LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `g` ON (`$GLOBALS[mysql_prefix]assigns`.`rec_facility_id` = `g`.`id`)
		WHERE `$GLOBALS[mysql_prefix]assigns`.`ticket_id` = $id
		ORDER BY `theunit` ASC ";																// 5/25/09, 1/16/08

		$asgn_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		if (mysql_affected_rows()>0) {
			print "<P><TABLE  CLASS='print_TD' BORDER = 1 CELLPADDING = 2 STYLE = 'border-collapse: collapse;'>\n";
			print "<TR><TH>Unit</TH><TH>D</TH><TH>R</TH><TH>E</TH>";
			print ($facilities)? "<TH>FE</TH><TH>FA</TH>": "";
			print "<TH>C</TH>";
			print ($miles)? "<TH>M/S</TH><TH>M/E</TH>": "";
			print "</TR>";
			
			while ( $asgn_row = stripslashes_deep(mysql_fetch_array($asgn_result))){
				print "<TR>";			
				print "<TD>" . shorten($asgn_row['theunit'], 24) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['dispatched']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['responding']) . "</TD>";
				print "<TD>" . my_to_date($asgn_row['on_scene']) . "</TD>";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2fenr']) . "</TD>": "";
				print ($facilities)? "<TD>" . my_to_date($asgn_row['u2farr']) . "</TD>": "";
				print "<TD>" . my_to_date($asgn_row['clear']) . "</TD>";
				print ($miles)? "<TD>" . my_to_date($asgn_row['start_miles']) . "</TD>": "";
				print ($miles)? "<TD>" . my_to_date($asgn_row['end_miles']) . "</TD>": "";
				print "</TR>\n";				
				}		// end while () $asgn_row = ...
			print "</TABLE>\n";
			}				// end if (mysql_affected_rows()>0 
*/
		
// ==============

		print "\n</BODY>\n</HTML>";
		return;
		}		// end if ($print == 'true')
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
	<TR VALIGN="top"><TD CLASS="print_TD" ALIGN="left">
<?php

	print do_ticket($row, max(320, intval($_SESSION['scr_width']* 0.4)), $search) ;				// 2/25/09
	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		<BR />
		<SPAN ID='grid_id' onClick='doGrid()'><U>Grid</U></SPAN>
		<SPAN ID='do_sv' onClick = 'sv_win(document.sv_form)' STYLE = 'margin-left: 20px' ><u>Street view</U></SPAN>";
	print ($zoom_tight)? "<SPAN  onClick= 'zoom_in({$row['lat']}, {$row['lng']}, {$zoom_tight});' STYLE = 'margin-left:20px'><U>Zoom</U></SPAN>\n" : "";	// 3/27/10	
		
	print "</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n
		<TR><TD ALIGN='center'><BR /><FONT SIZE='-1'>Click map point for distance information.</FONT></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];

	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR>\n";
//	print "<TR><TD ALIGN='left'>";
//	print show_log ($id);				// log as a table
//	print "</TD></TR></TABLE>\n";
	print "<TR><TD><IMG SRC='markers/up.png' BORDER=0  onclick = \"location.href = '#top';\" STYLE = 'margin-left: 20px'></TD></TR>\n";
	print "</TABLE>\n";


?>
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT> 
	<SCRIPT>
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var starting = false;

	function sv_win(theForm) {				// 2/11/09
		if(starting) {return;}				// dbl-click proof
		starting = true;

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lng.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sl)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sl.focus();
		starting = false;
		}		// end function sv win()

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			grid = false;
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			grid = true;
			}
		}

	function zoom_in (in_lat, in_lng, in_zoom) {				// 3/27/10
		map.setCenter(new GLatLng(in_lat, in_lng), in_zoom );
		var marker = new GMarker(map.getCenter());				// marker to map center
		var myIcon = new GIcon();
		myIcon.image = "./markers/sm_red.png";
		map.addOverlay(marker, myIcon);		 
		}				// end function zoom in ()		 		


	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	var thisMarker = false;

	var map;
	var icons=[];						// note globals	- 1/29/09
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./icons/blue.png";		// normal
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./icons/green.png";	// green
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./icons/red.png";		// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./icons/white.png";	// white - not in use

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
//	baseIcon.shadowSize = new GSize(37, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);
//	baseIcon.infoShadowAnchor = new GPoint(18, 25);

	map = new GMap2($("map"));		// create the map
<?php
$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":
?>
		map.setMapType(G_SATELLITE_MAP);
<?php
		break;
	
		case "3":
?>
		map.setMapType(G_PHYSICAL_MAP);
<?php
		break;
	
		case "4":
?>
		map.setMapType(G_HYBRID_MAP);
<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
//	map.addControl(new GSmallMapControl());
	map.setUIToDefault();									// 8/13/10
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());				// 12/24/08
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php 
}
if(($lat==0.999999) && ($lng==0.999999)) {	// check for facilities entered in no maps mode 7/28/10	
?>
	map.setCenter(new GLatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>),14);
	var icon = new GIcon(baseIcon);
	var icon_url = "./icons/question1.png";				// 7/28/10
	icon.image = icon_url;
	var point = new GLatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);	// 1147
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom();
<?php } else { ?>
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),14);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1147
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom();
<?php } ?>
	
// ====================================Add Responding Units to Map 8/1/09================================================

	var icons=[];	
	icons[1] = "./icons/white.png";		// normal
	icons[2] = "./icons/black.png";	// green

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var unit_icon = new GIcon(baseIcon);
	unit_icon.image = icons[1];

function createMarker(unit_point, number) {		// Show this markers index in the info window when clicked
	var unit_marker = new GMarker(unit_point, unit_icon);	
	var html = number;
	GEvent.addListener(unit_marker, "click", function() {unit_marker.openInfoWindowHtml(html);});
	return unit_marker;
	}


<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
	$responder_id=($row['responder_id']);
	if ($row['clear'] == NULL) {

		$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
		$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
		while($row_unit = mysql_fetch_array($result_unit)){
		$unit_id=($row_unit['id']);
		$mobile=($row_unit['mobile']);
		if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {

		if ($mobile == 1) {
			echo "var unit_icon = new GIcon(baseIcon);\n";
			echo "var unit_icon_url = \"./icons/gen_icon.php?blank=0&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "map.addOverlay(unit_marker);\n";
			echo "\n";
		} else {
			echo "var unit_icon = new GIcon(baseIcon);\n";
			echo "var unit_icon_url = \"./icons/gen_icon.php?blank=4&text=RU\";\n";
			echo "unit_icon.image = unit_icon_url;\n";
			echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
			echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
			echo "map.addOverlay(unit_marker);\n";
			echo "\n";
		}	// end inner if
		}	// end middle if
		}	// end outer if
		}	// end inner while
	}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>

	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
}


<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){

		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
	
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['icon']);
	
		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {
	
			$fac_tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row_fac['updated']) . "</TD></TR>";
			$fac_tab_1 .= "</TABLE>";
	
			$fac_tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE>";
			
?>
	//		var fac_sym = (g+1).toString();
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
<?php
	
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
			}	// end if my_is_float
	
?>
			g++;
<?php
		}	// end while

}
// ============================== End of functions to show facilities =======================================

//	$street = empty($row['ticket_street'])? "" : $row['ticket_street'] . "<BR/>" . $row['ticket_city'] . " " . $row['ticket_state'] ;  2/21/09

//	$tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
//	$tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . shorten($row['scope'], 48)  . "</B></TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>As of:</TD><TD>" . format_date($row['updated']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Reported by:</TD><TD>" . shorten($row['contact'], 32) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='odd'><TD>Phone:</TD><TD>" . format_phone ($row['phone']) . "</TD></TR>";
//	$tab_1 .= "<TR CLASS='even'><TD>Addr:</TD><TD>" . $street . " </TD></TR>";
//	$tab_1 .= "</TABLE>";		// 11/6/08

	do_kml();			// kml functions

?>
//	map.openInfoWindowHtml(point, "<?php // print $tab_1;?>");

	GEvent.addListener(map, "click", function(marker, point) {
		if (point) {
			var baseIcon = new GIcon();
			baseIcon.iconSize=new GSize(32,32);
			baseIcon.iconAnchor=new GPoint(16,16);
			var cross = new GIcon(baseIcon, "./markers/crosshair.png", null);		// 10/13/08

			map.clearOverlays();
			var thisMarker = new GMarker(point, cross);
			map.addOverlay(thisMarker);
			$("newlat").innerHTML = point.lat().toFixed(6);
			$("newlng").innerHTML = point.lng().toFixed(6);

			var nlat = $("newlat").innerHTML ;
			var nlng = $("newlng").innerHTML ;
			var olat = $("oldlat").innerHTML ;
			var olng = $("oldlng").innerHTML ;

			var km=distCosineLaw(parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng));
			var dist = ((km * km2feet).toFixed(0)).toString();
			var dist1 = dist/5280;
			var dist2 = (dist>5280)? ((dist/5280).toFixed(2) + " mi") : dist + " ft" ;

			$("range").innerHTML	= dist2;
			$("brng").innerHTML	= (brng (parseFloat(olat), parseFloat(olng), parseFloat(nlat), parseFloat(nlng)).toFixed(0)) + ' degr';
			$("newusng").innerHTML= LLtoUSNG(nlat, nlng, 5);
			$("pointl1").style.display = "block";
			$("pointl2").style.display = "block";

			var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1196
			map.addOverlay(new GMarker(point, icon));
			var polyline = new GPolyline([
			    new GLatLng(nlat, nlng),
			    new GLatLng(olat, olng)
				], "#FF0000", 2);
			map.addOverlay(polyline);
			}
		} )

	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}

	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}

	function do_coords(inlat, inlng) {  //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	</SCRIPT>
<?php

	}				// end function show_ticket() =======================================================
//	} {		-- dummy

function do_ticket($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, $routesfile;
	global $iw_width;

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>" . get_text("Incident") . ": <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nature:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
	$print .= "</TD></TR>\n";

	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("Synopsis") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['tick_descr'])) . "</TD></TR>\n";	//	8/12/09
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Protocol") . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
	$print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . get_text("911 Contacted") . ":</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['nine_one_one'])) . "</TD></TR>\n";	//	6/26/10
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Reported by") . ":</TD>	<TD ALIGN='left'>" . highlight($search,$theRow['contact']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Phone") . ":</TD>			<TD ALIGN='left'>" . format_phone ($theRow['phone']) . "</TD></TR>\n";
	$end_date = (intval($theRow['problemend'])> 1)? $theRow['problemend']:  (time() - (intval(get_variable('delta_mins'))*60));
	$elapsed = my_date_diff($theRow['problemstart'], $end_date);
	$elaped_str = (intval($theRow['problemend'])> 1)? "" : "&nbsp;&nbsp;&nbsp;&nbsp;({$elapsed})";	
	$print .= "<TR CLASS='odd'><TD ALIGN='left'>" . get_text("Status") . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "{$elaped_str}</TD></TR>\n";
	$by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
	$print .= "<TR CLASS='even'><TD ALIGN='left'>" . get_text("Written") . ":</TD>		<TD ALIGN='left'>" . format_date($theRow['date']) . $by_str;
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updated:&nbsp;&nbsp;" . format_date($theRow['updated']) . "</TD></TR>\n";
	$print .=  empty($theRow['booked_date']) ? "" : "<TR CLASS='odd'><TD ALIGN='left'>Scheduled date:</TD>		<TD ALIGN='left'>" . format_date($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09

	$print .= "<TR CLASS='even' ><TD ALIGN='left' COLSPAN='2'>&nbsp;	<TD ALIGN='left'></TR>\n";			// separator
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>Incident at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09

	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>Disposition:</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("Run Start") . ":</TD>					<TD ALIGN='left'>" . format_date($theRow['problemstart']);
	$elaped_str = (intval($theRow['problemend'])> 1)?  $elapsed : "";
	$print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;End:&nbsp;&nbsp;" . format_date($theRow['problemend']) . "&nbsp;&nbsp;({$elaped_str})</TD></TR>\n";

	$locale = get_variable('locale');	// 08/03/09
	switch($locale) { 
		case "0":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;USNG&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
		break;

		case "1":
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;OSGB&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
		break;
	
		case "2":
		$coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
		$grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;UTM&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}

	$print .= "<TR CLASS='odd'><TD ALIGN='left' onClick = 'javascript: do_coords(" .$theRow['lat'] . "," . $theRow['lng']. ")'><U>" . get_text("Position") . "</U>: </TD>
		<TD ALIGN='left'>" . get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type . "</TD></TR>\n";		// 9/13/08

	$print .= "<TR><TD colspan=2 ALIGN='left'>";
	$print .= show_log ($theRow[0]);				// log
	$print .="</TD></TR>";

	$print .= "<TR STYLE = 'display:none;'><TD colspan=2><SPAN ID='oldlat'>" . $theRow['lat'] . "</SPAN><SPAN ID='oldlng'>" . $theRow['lng'] . "</SPAN></TD></TR>";
	$print .= "</TABLE>\n";

	$print .= show_assigns(0, $theRow[0]);				// 'id' ambiguity - 7/27/09
	$print .= show_actions($theRow[0], "date", FALSE, FALSE);

	return $print;
	}		// end function do ticket(


//	} -- dummy

function popup_ticket($id,$print='false', $search = FALSE) {								/* 7/9/09 - show specified ticket */
//	global {$_SESSION['fip']}, $fmp, {$_SESSION['editfile']}, {$_SESSION['addfile']}, {$_SESSION['unitsfile']}, {$_SESSION['facilitiesfile']}, $routesfile;
	global $istest, $iw_width;


	if($istest) {
		print "GET<br />\n";
		dump($_GET);
		print "POST<br />\n";
		dump($_POST);
		}

	if ($id == '' OR $id <= 0 OR !check_for_rows("SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id='$id'")) {	/* sanity check */
		print "Invalid Ticket ID: '$id'<BR />";
		return;
		}

	$restrict_ticket = ((get_variable('restrict_user_tickets')==1) && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";

	$query = "SELECT *,UNIX_TIMESTAMP(problemstart) AS problemstart,UNIX_TIMESTAMP(problemend) AS problemend,UNIX_TIMESTAMP(date) AS date,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr` FROM `$GLOBALS[mysql_prefix]ticket` WHERE ID='$id' $restrict_ticket";	// 8/12/09

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">No such ticket or user access to ticket is denied</FONT>";
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_assoc($result));
?>
	<TABLE BORDER="0" ID = "outer" ALIGN="left">
<?php

	print "<TD ALIGN='left'>";
	print "<TABLE ID='theMap' BORDER=0><TR CLASS='odd' ><TD  ALIGN='center'>
		<DIV ID='map' STYLE='WIDTH:" . get_variable('map_width') . "px; HEIGHT: " . get_variable('map_height') . "PX'></DIV>
		</TD></TR>";	// 11/29/08

	print "<FORM NAME='sv_form' METHOD='post' ACTION=''><INPUT TYPE='hidden' NAME='frm_lat' VALUE=" .$row['lat'] . ">";		// 2/11/09
	print "<INPUT TYPE='hidden' NAME='frm_lng' VALUE=" .$row['lng'] . "></FORM>";

	print "<TR ID='pointl1' CLASS='print_TD' STYLE = 'display:none;'>
		<TD ALIGN='center'><B>Range:</B>&nbsp;&nbsp; <SPAN ID='range'></SPAN>&nbsp;&nbsp;<B>Brng</B>:&nbsp;&nbsp;
			<SPAN ID='brng'></SPAN></TD></TR>\n
		<TR ID='pointl2' CLASS='print_TD' STYLE = 'display:none;'>
			<TD ALIGN='center'><B>Lat:</B>&nbsp;<SPAN ID='newlat'></SPAN>
			&nbsp;<B>Lng:</B>&nbsp;&nbsp; <SPAN ID='newlng'></SPAN>&nbsp;&nbsp;<B>NGS:</B>&nbsp;<SPAN ID = 'newusng'></SPAN></TD></TR>\n";
	print "</TABLE>\n";
	print "</TD></TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
	$lat = $row['lat']; $lng = $row['lng'];
	print "</TABLE>\n";


?>
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT SRC="../js/graticule.js" type="text/javascript"></SCRIPT>
	<SCRIPT>


	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	var the_grid;
	var grid = false;
	function doGrid() {
		if (grid) {
			map.removeOverlay(the_grid);
			grid = false;
			}
		else {
			the_grid = new LatLonGraticule();
			map.addOverlay(the_grid);
			grid = true;
			}
		}

	String.prototype.trim = function () {				// 9/14/08
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

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
	var thisMarker = false;

	var map;
	var icons=[];						// note globals	- 1/29/09
	icons[<?php print $GLOBALS['SEVERITY_NORMAL'];?>] = "./icons/blue.png";		// normal
	icons[<?php print $GLOBALS['SEVERITY_MEDIUM'];?>] = "./icons/green.png";	// green
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>] =  "./icons/red.png";		// red
	icons[<?php print $GLOBALS['SEVERITY_HIGH']; ?>+1] =  "./icons/white.png";	// white - not in use

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	map = new GMap2($("map"));		// create the map
<?php
$maptype = get_variable('maptype');	// 08/02/09

	switch($maptype) { 
		case "1":
		break;

		case "2":?>
		map.setMapType(G_SATELLITE_MAP);<?php
		break;
	
		case "3":?>
		map.setMapType(G_PHYSICAL_MAP);<?php
		break;
	
		case "4":?>
		map.setMapType(G_HYBRID_MAP);<?php
		break;

		default:
		print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
	}
?>
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());				// 12/24/08
<?php if (get_variable('terrain') == 1) { ?>
	map.addMapType(G_PHYSICAL_MAP);
<?php } ?>
	map.setCenter(new GLatLng(<?php print $lat;?>, <?php print $lng;?>),11);
	var icon = new GIcon(baseIcon);
	icon.image = icons[<?php print $row['severity'];?>];
	var point = new GLatLng(<?php print $lat;?>, <?php print $lng;?>);	// 1147
	map.addOverlay(new GMarker(point, icon));
	map.enableScrollWheelZoom();

// ====================================Add Active Responding Units to Map =========================================================================
	var icons=[];						// note globals	- 1/29/09
	icons[1] = "./icons/white.png";		// normal
	icons[2] = "./icons/black.png";	// green

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(20, 34);
	baseIcon.iconAnchor = new GPoint(9, 34);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var unit_icon = new GIcon(baseIcon);
	unit_icon.image = icons[1];

function createMarker(unit_point, number) {
	var unit_marker = new GMarker(unit_point, unit_icon);
	// Show this markers index in the info window when it is clicked
	var html = number;
	GEvent.addListener(unit_marker, "click", function() {unit_marker.openInfoWindowHtml(html);});
	return unit_marker;
}


<?php
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]assigns` WHERE ticket_id='$id'";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	while($row = mysql_fetch_array($result)){
		$responder_id=($row['responder_id']);
		if ($row['clear'] == NULL) {
	
			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE id='$responder_id'";
			$result_unit = mysql_query($query_unit) or do_error($query_unit, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
			while($row_unit = mysql_fetch_array($result_unit)){
				$unit_id=($row_unit['id']);
				$mobile=($row_unit['mobile']);
				if ((my_is_float($row_unit['lat'])) && (my_is_float($row_unit['lng']))) {
			
					if ($mobile == 1) {
						echo "var unit_icon = new GIcon(baseIcon);\n";
						echo "var unit_icon_url = \"./icons/gen_icon.php?blank=0&text=RU\";\n";						// 4/18/09
						echo "unit_icon.image = unit_icon_url;\n";
						echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "map.addOverlay(unit_marker);\n";
						echo "\n";
					} else {
						echo "var unit_icon = new GIcon(baseIcon);\n";
						echo "var unit_icon_url = \"./icons/gen_icon.php?blank=4&text=RU\";\n";						// 4/18/09
						echo "unit_icon.image = unit_icon_url;\n";
						echo "var unit_point = new GLatLng(" . $row_unit['lat'] . "," . $row_unit['lng'] . ");\n";
						echo "var unit_marker = createMarker(unit_point, '" . addslashes($row_unit['name']) . "', unit_icon);\n";
						echo "map.addOverlay(unit_marker);\n";
						echo "\n";
						}	// end if/else ($mobile)
					}	// end ((my_is_float()) - responding units
				}	// end outer if
			}	// end inner while
		}	//	end outer while

// =====================================End of functions to show responding units========================================================================
// ====================================Add Facilities to Map 8/1/09================================================
?>
	var icons=[];	
	var g=0;

	var fmarkers = [];

	var baseIcon = new GIcon();
	baseIcon.shadow = "./markers/sm_shadow.png";

	baseIcon.iconSize = new GSize(30, 30);
	baseIcon.iconAnchor = new GPoint(15, 30);
	baseIcon.infoWindowAnchor = new GPoint(9, 2);

	var fac_icon = new GIcon(baseIcon);
	fac_icon.image = icons[1];

function createfacMarker(fac_point, fac_name, id, fac_icon) {
	var fac_marker = new GMarker(fac_point, fac_icon);
	// Show this markers index in the info window when it is clicked
	var fac_html = fac_name;
	fmarkers[id] = fac_marker;
	GEvent.addListener(fac_marker, "click", function() {fac_marker.openInfoWindowHtml(fac_html);});
	return fac_marker;
}


<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, `$GLOBALS[mysql_prefix]facilities`.id AS fac_id, `$GLOBALS[mysql_prefix]facilities`.description AS facility_description, `$GLOBALS[mysql_prefix]fac_types`.name AS fac_type_name, `$GLOBALS[mysql_prefix]facilities`.name AS facility_name FROM `$GLOBALS[mysql_prefix]facilities` LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id ORDER BY `$GLOBALS[mysql_prefix]facilities`.type ASC";
	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);	while($row_fac = mysql_fetch_array($result_fac)){

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	while($row_fac = mysql_fetch_array($result_fac)){
	
		$fac_name = $row_fac['facility_name'];			//	10/8/09
		$fac_temp = explode("/", $fac_name );
		$fac_index =  (strlen($fac_temp[count($fac_temp) -1])<3)? substr($fac_temp[count($fac_temp) -1] ,0,strlen($fac_temp[count($fac_temp) -1])): substr($fac_temp[count($fac_temp) -1] ,-3 ,strlen($fac_temp[count($fac_temp) -1]));		
		
		print "\t\tvar fac_sym = '$fac_index';\n";				// for sidebar and icon 10/8/09
	
		$fac_id=($row_fac['id']);
		$fac_type=($row_fac['icon']);
	
		$f_disp_name = $row_fac['facility_name'];		//	10/8/09
		$f_disp_temp = explode("/", $f_disp_name );
		$facility_display_name = $f_disp_temp[0];
	
		if ((my_is_float($row_fac['lat'])) && (my_is_float($row_fac['lng']))) {

			$fac_tab_1 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_1 .= "<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($facility_display_name, 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD COLSPAN=2 ALIGN='center'><B>" . addslashes(shorten($row_fac['fac_type_name'], 48)) . "</B></TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Description:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['facility_description'])) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Status:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['status_val']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>Contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_name']). "&nbsp;&nbsp;&nbsp;Email: " . addslashes($row_fac['contact_email']) . "</TD></TR>";
			$fac_tab_1 .= "<TR CLASS='odd'><TD ALIGN='right'>Phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['contact_phone']) . " </TD></TR>";
			$fac_tab_1 .= "<TR CLASS='even'><TD ALIGN='right'>As of:&nbsp;</TD><TD ALIGN='left'>" . format_date($row_fac['updated']) . "</TD></TR>";
			$fac_tab_1 .= "</TABLE>";

			$fac_tab_2 = "<TABLE CLASS='infowin'  width='{$iw_width}' >";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security contact:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_contact']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Security email:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_email']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security phone:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['security_phone']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Access rules:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['access_rules'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Security reqs:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['security_reqs'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Opening hours:&nbsp;</TD><TD ALIGN='left'>" . addslashes(str_replace($eols, " ", $row_fac['opening_hours'])) . "</TD></TR>";
			$fac_tab_2 .= "<TR CLASS='odd'><TD ALIGN='right'>Prim pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_p']) . " </TD></TR>";
			$fac_tab_2 .= "<TR CLASS='even'><TD ALIGN='right'>Sec pager:&nbsp;</TD><TD ALIGN='left'>" . addslashes($row_fac['pager_s']) . " </TD></TR>";
			$fac_tab_2 .= "</TABLE>";
			
			?>
//			var fac_sym = (g+1).toString();
			var myfacinfoTabs = [
				new GInfoWindowTab("<?php print nl2brr(addslashes(shorten($row_fac['facility_name'], 10)));?>", "<?php print $fac_tab_1;?>"),
				new GInfoWindowTab("More ...", "<?php print str_replace($eols, " ", $fac_tab_2);?>")
				];
			<?php

			if(($row_fac['lat']==0.999999) && ($row_fac['lng']==0.999999)) {	// check for facilities entered in no maps mode 7/28/10
			
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/question1.png\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . get_variable('def_lat') . "," . get_variable('def_lng') . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
			} else {
				echo "var fac_icon = new GIcon(baseIcon);\n";
				echo "var fac_type = $fac_type;\n";
				echo "var fac_icon_url = \"./icons/gen_fac_icon.php?blank=$fac_type&text=\" + (fac_sym) + \"\";\n";
				echo "fac_icon.image = fac_icon_url;\n";
				echo "var fac_point = new GLatLng(" . $row_fac['lat'] . "," . $row_fac['lng'] . ");\n";
				echo "var fac_marker = createfacMarker(fac_point, myfacinfoTabs, g, fac_icon);\n";
				echo "map.addOverlay(fac_marker);\n";
				echo "\n";
				}

		}	// end if my_is_float - facilities

?>
		g++;
<?php
	}	// end while

}
// =====================================End of functions to show facilities========================================================================
	do_kml();			// kml functions

?>
	function lat2ddm(inlat) {				// 9/7/08
		var x = new Number(inlat);
		var y  = (inlat>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var nors = (inlat>0.0)? " N":" S";
		return Math.abs(y) + '\260 ' + z +"'" + nors;
		}

	function lng2ddm(inlng) {
		var x = new Number(inlng);
		var y  = (inlng>0)?  Math.floor(x):Math.round(x);
		var z = ((Math.abs(x-y)*60).toFixed(1));
		var eorw = (inlng>0.0)? " E":" W";
		return Math.abs(y) + '\260 ' + z +"'" + eorw;
		}


	function do_coords(inlat, inlng) {  //9/14/08
		if(inlat.toString().length==0) return;								// 10/15/08
		var str = inlat + ", " + inlng + "\n";
		str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
		str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
		alert(str);
		}

	function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
		var d = new Number(inval);
		d  = (inval>0)?  Math.floor(d):Math.round(d);
		var mi = (inval-d)*60;
		var m = Math.floor(mi)				// min's
		var si = (mi-m)*60;
		var s = si.toFixed(1);
		return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
		}

	</SCRIPT>
<?php
	}				// end function popup_ticket() =======================================================
