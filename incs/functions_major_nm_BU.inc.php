<?php
/*
7/16/10  Initial Release for no internet operation - created from FMP
11/16/10 fixes to print, do_ticket etc. to conform to SQL from current FMP
11/29/10 locale case 2		
12/1/10 get_text disposition added
12/03/10 Completely revised show hide units. Added show hide by category and revised hide and show incidents to remove units from these functions - not used in No Maps version.
1/30/11 revised to  show booked date in sidebar for scheduled runs
3/19/11 revised units, facilities index length to 6 chars 
4/1/11 lift restriction re operators and non-owned incidents - pending incident-owner corrections
*/

@session_start();

$nature = get_text("Nature");			// 12/03/10
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");

function get_can_edit() {				// 5/1/11
	if (empty($_SESSION)) {return FALSE;}
	else	{
		switch ($_SESSION['level']) {
			case $GLOBALS['LEVEL_SUPER']:
			case $GLOBALS['LEVEL_ADMINISTRATOR']:
				return TRUE;
				break;
			case $GLOBALS['LEVEL_USER'] :
				return (trim(get_variable('oper_can_edit'))==1);
				break;
			case $GLOBALS['LEVEL_GUEST']:
			case $GLOBALS['LEVEL_MEMBER']:
			case $GLOBALS['LEVEL_UNIT']:
				return FALSE;	
			}		// end switch 
		}		// end if/else
	}		// end function 

if (!(function_exists ('get_lat'))) {
	function get_lat($in_lat) {					// 9/7/08
		if (empty($in_lat)) {return"";}			// 9/14/08
		$format = get_variable('lat_lng');
	
		switch ($format) {
			case 0:						// decimal
			    return $in_lat;
			    break;
			case 1:
	//			return ll2dms($in_lat);	// dms
				return lat2dms($in_lat);	// dms
				break;
			case 2:						// cg format
			    return lat2ddm($in_lat);
			    break;
			}
		}				// end function get_lat()
	}
	
if (!(function_exists ('get_lng'))) {
	
	function get_lng($in_lng) {					// 9/7/08
		if (empty($in_lng)) {return"";}			// 9/14/08
		$format = get_variable('lat_lng');
	
		switch ($format) {
			case 0:						// decimal
			    return $in_lng;
			    break;
			case 1:	
	//			return ll2dms($in_lng);		// dms
				return lng2dms($in_lng);	// dms
				break;
			case 2:						// cg format
			    return lng2ddm($in_lng);
			    break;
			}
		}				// end function get_lng()
	}

//	{ -- dummy
function list_tickets($sort_by_field='',$sort_value='', $my_offset=0) {	// list tickets ===================================================

	$time = microtime(true); // Gets microseconds
	global $istest, $units_side_bar_height, $do_blink, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
	$can_edit = get_can_edit();

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder` LIMIT 1";		// 1/28/11
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$any_units = (mysql_affected_rows()==1);
	
	$query_sched = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE (
		`status`='{$GLOBALS['STATUS_OPEN']}' OR
		`status`='{$GLOBALS['STATUS_SCHEDULED']})' 
		 LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$any_open_tickets = (mysql_affected_rows()==1);
		
	$query_sched = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE (
		`status`='{$GLOBALS['STATUS_CLOSED']}')
		 LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$any_closed_tickets = (mysql_affected_rows()==1);
		
	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]facilities` LIMIT 1";	
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$any_facilities = (mysql_affected_rows()==1);

	unset ($result);	
	

	@session_start();		// 
	$captions = array("Current situation", "{$incidents} closed today", "{$incidents} closed yesterday+", "{$incidents} closed this week", "{$incidents} closed last week", "{$incidents} closed last week+", "{$incidents} closed this month", "{$incidents} closed last month", "{$incidents} closed this year", "{$incidents} closed last year", "Scheduled {$incidents}");
	$by_severity = array(0, 0, 0);				// counters // 5/2/10
	
	if (!(array_key_exists('func', $_GET))) {$func = 0;}
	else 									{extract ($_GET);}

//	include('startup.inc.php');
	$cwi = get_variable('closed_interval');			// closed window interval in hours

//	$get_status = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['status'])))) ) ? "" : $_GET['status'] ;
	$get_sortby = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['sortby'])))) ) ? "" : $_GET['sortby'] ;
	$get_offset = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['offset'])))) ) ? "" : $_GET['offset'] ;

	if (!isset($_GET['status'])) {
		$open = "Open";
	} else {
	$open = (isset($_GET['status']) && ($_GET['status']==$GLOBALS['STATUS_OPEN']))? "Open" : "";
	}

	$heading = $captions[($func)] . " - " . get_variable('map_caption');
	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol

	$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder`";		// 5/12/10
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	unset($result);		
	$required = 48 + (mysql_affected_rows()*22);		// derived by trial and error - emphasis the latter = 7/18/10
	$the_height = (integer)  min (round($units_side_bar_height * $_SESSION['scr_height']), $required );		// see main for $units_side_bar_height value
//	$col_width = (int) floor($_SESSION['scr_width'] * .48);

?>
<TABLE BORDER=0 ALIGN='center'>
	<TR CLASS='even'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header'><?php print $heading; ?> </FONT><SPAN ID='sev_counts' STYLE = 'margin-left: 40px'></SPAN></TD></TR>	<!-- 5/2/10 -->
	<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>
	<TR><TD VALIGN='TOP' width='auto' >
<!--<DIV ID = 'side_bar' style="position:fixed; top:50px; left:10px; width: <?php print $col_width;?>px;"></DIV> -->
		<DIV ID = 'side_bar' ></DIV>
		</TD>
		<TD></TD>
		<TD VALIGN='TOP' width='90%' >
		<DIV ID = 'side_bar_r'></DIV>
		<DIV ID = 'units_legend'></DIV>	
		<DIV ID = 'side_bar_f'></DIV></TD>
	</TR>

	<TR><TD COLSPAN='99'> </TD></TR>
	<TR><TD><IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#top';" STYLE = 'margin-left: 20px'></TD></TR>
	<TR><TD CLASS='td_label' COLSPAN='99' ALIGN='center'>
		<A HREF="mailto:info@TicketsCAD.org?subject=Question/Comment on Tickets Dispatch System"><u>Contact us</u>&nbsp;&nbsp;&nbsp;&nbsp;<IMG SRC="mail.png" BORDER="0" STYLE="vertical-align: text-bottom"></A>
		

		</TD></TR></TABLE>
		
	<FORM NAME='unit_form' METHOD='get' ACTION='<?php echo $_SESSION['unitsfile'];?>'>
	<INPUT TYPE='hidden' NAME='func' VALUE='responder'>
	<INPUT TYPE='hidden' NAME='view' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE=''>
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='tick_form' METHOD='get' ACTION='edit.php'>				<!-- 11/27/09 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	</FORM>

	<FORM NAME='sort_form' METHOD='post' ACTION='main.php'>				<!-- 6/11/10 -->
	<INPUT TYPE='hidden' NAME='order' VALUE=''>
	</FORM>

	<FORM NAME='facy_form' METHOD='get' ACTION='<?php echo $_SESSION['facilitiesfile'];?>'>		<!-- 11/27/09 -->
	<INPUT TYPE='hidden' NAME='id' VALUE=''>
	<INPUT TYPE='hidden' NAME='edit' VALUE=''>
	<INPUT TYPE='hidden' NAME='view' VALUE=''>
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

	function to_server(the_unit, the_status) {							// write unit status data via ajax xfer
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

	function get_chg_disp_tr() {								// 5/5/11
		var chg_disp_tr ="<TR><TD COLSPAN=99 ALIGN='center'>\n";
		chg_disp_tr +="\t\t<FORM NAME = 'frm_interval_sel' STYLE = 'display:inline' >\n";
		chg_disp_tr +="\t\t<SELECT NAME = 'frm_interval' onChange = 'do_listtype(this.value);'>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='99' SELECTED><?php print get_text("Change display"); ?></OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='0'><?php print get_text("Current situation"); ?></OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='1'><?php print $incidents;?> closed today</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='2'><?php print $incidents;?> closed yesterday+</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='3'><?php print $incidents;?> closed this week</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='4'><?php print $incidents;?> closed last week</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='5'><?php print $incidents;?> closed last week+</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='6'><?php print $incidents;?> closed this month</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='7'><?php print $incidents;?> closed last month</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='8'><?php print $incidents;?> closed this year</OPTION>\n";
		chg_disp_tr +="\t\t<OPTION VALUE='9'><?php print $incidents;?> closed last year</OPTION>\n";
		chg_disp_tr +="\t\t</SELECT>\n</FORM>\n";
		chg_disp_tr +="\t\t<SPAN ID = 'btn_go' onClick='document.to_listtype.submit()' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none'><U>Next</U></SPAN>";
		chg_disp_tr +="\t\t<SPAN ID = 'btn_can'  onClick='hide_btns_closed(); hide_btns_scheduled(); ' CLASS='conf_button' STYLE = 'margin-left: 10px; display:none'><U>Cancel</U></SPAN>";
		chg_disp_tr +="<br /><br /></TD></TR>\n";

		return chg_disp_tr;
		} 					// end function get chg_disp_tr()


<?php
	$quick = (!(is_guest()) && (intval(get_variable('quick')==1)));				// 11/27/09
	print ($quick)?  "var quick = true;\n": "var quick = false;\n";
?>
var tr_id_fixed_part = "tr_id_";		// 3/2/10

	var colors = new Array ('odd', 'even');

	function set_initial_pri_disp() {
		}

	function set_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
		}

	function set_fac_categories() {			//	12/03/10 - checks current session values and sets checkboxes and view states for hide and show.
		}


	function show_hide_rows(instr) {				// instr is '' or 'none' - 3/8/10
		for (i = 0; i< rowIds.length; i++) {
			var rowId = rowIds[i];					// row id - 3/3/10
			$(rowId).style.display = instr;			// hide each 'unavailable' row
			}
		}				// end function show_hide_rows()

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

	function s_handleResult(req) {					// the 'called-back' persist function - show
		show_Units();
		}

	function do_sel_update (in_unit, in_val) {							// 12/17/09
		to_server(in_unit, in_val);
		}

	function do_sidebar_unit (instr, id, sym, myclass, tip_str) {		// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		if (isNull(tip_str)) {
			side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[(id)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 2/6/10 moved onclick to TD
			}
		else {
			side_bar_html += "<TR ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(id+1)%2] +"'><TD CLASS='" + myclass + "' onClick = myclick(" + id + "); ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
			}
		}		// end function do sidebar_unit ()

<?php	
		$js_func = ($can_edit)? "myclick_ed_tick" : "open_tick_window" ;		// 4/28/11
		
?>	
	function open_tick_window (id) {				// 5/2/10
		var url = "single.php?ticket_id="+ id;
		var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=650, left=100,top=100,screenX=100,screenY=100');
		tickWindow.focus();
		}	

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
//		GEvent.trigger(gmarkers[id], "click");
		location.href = "#top";
		}

	function do_sidebar (instr, id, sym, myclass, tip_str) {		// sidebar_string, sidebar_index, row_class, icon_info, mouseover_str - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		side_bar_html += "<TR onClick = 'onclick_do_unit(" + id + ");' ID =  '" + tr_id + "' onMouseover=\"Tip('" + tip_str + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[id%2] +"'>";
		side_bar_html += "<TD CLASS='" + myclass + "' ALIGN = 'left'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 1/3/10 added tip param		
		}		// end function do sidebar ()

	function do_sidebar_t_ed (instr, line_no, rcd_id, letter, tip_str) {		// ticket edit, tip str added 1/3/10
		side_bar_html += "<TR onClick = '<?php print $js_func;?>(" + rcd_id + ")'; onMouseover=\"Tip('" + tip_str.replace("'", "") + "');\" onmouseout=\"UnTip();\" CLASS='" + colors[(line_no+1)%2] +"'>";		
		side_bar_html += "<TD CLASS='td_data'>" + letter + "</TD>" + instr +"</TR>\n";		// 2/13/09, 10/29/09 removed period
		}
	function do_sidebar_u_iw (instr, id, sym, myclass) {						// constructs unit incident sidebar row - 1/7/09
		var tr_id = tr_id_fixed_part + id;
		side_bar_html += "<TR ID = '" + tr_id + "' CLASS='" + colors[id%2] +"' onClick = myclick(" + id + ");><TD CLASS='" + myclass + "'>" + (sym) + "</TD>"+ instr +"</TR>\n";		// 10/30/09 removed period
		}		// end function do sidebar ()

		
	function myclick_ed_tick(id) {				// Responds to sidebar click - edit ticket data

<?php 
	$the_action = (is_guest()) ? "main.php" : "edit.php";				2/27/10
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

	function onclick_do_unit(id) {				// Responds to sidebar click - view/edit responder data
		document.unit_form.id.value=id;			// 11/27/09
<?php
			$the_func = ($can_edit)? "edit": "view";		// 4/30/11
?>
		document.unit_form.<?php print $the_func;?>.value="true";
		document.unit_form.submit();
		}

	function do_sidebar_fac_ed (fac_instr, fac_id, fac_sym, myclass, line_no) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[line_no%2] +"'>";
		side_bar_html += "<TD  onClick = fac_click_ed(" + fac_id + ");>" + (fac_sym) + "</TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac_iw ()

	function do_sidebar_fac_iw (fac_instr, fac_id, fac_sym, myclass) {					// constructs facilities sidebar row 9/22/09
		side_bar_html += "<TR CLASS='" + colors[fac_id%2] +"' WIDTH = '100%';>"
		side_bar_html += "<TD CLASS='" + myclass + "'>" + (fac_sym) + "</TD>";
		side_bar_html += fac_instr +"</TR>\n";		// 10/30/09 removed period
		location.href = "#top";
		}		// end function do sidebar_fac ()

	function fac_click_iw(fac_id) {						// Responds to facilities sidebar click, triggers listener above 9/22/09
		GEvent.trigger(fmarkers[fac_id], "click");
		location.href = "#top";
		}

	function fac_click_ed(id) {							// Responds to facility sidebar click - edit data
		document.facy_form.id.value=id;					// 11/27/09
		if (quick) {
			document.facy_form.edit.value="true";
			}
		else {
			document.facy_form.view.value="true";
			}
		document.facy_form.submit();
		}

	function fac_click_vw(id) {							// Responds to facility sidebar click - view data
		document.facy_form.id.value=id;					// 11/27/09
		document.facy_form.view.value="true";
		document.facy_form.submit();
		}

	var points = false;

function do_add_note (id) {				// 8/12/09
	var url = "add_note.php?ticket_id="+ id;
	var noteWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100');
	noteWindow.focus();
	}
	
function do_sort_sub(sort_by){				// 6/11/10
	document.sort_form.order.value = sort_by;
	document.sort_form.submit();
	}

function do_sched_jobs(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// flag 1, value h
	var url = "persist.php";
	sendRequest (url, cs_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	}				// end function do_listtype()

function do_curr_jobs(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// flag 1, value h
	var url = "persist.php";
	sendRequest (url, cs_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	}				// end function do_listtype()

function do_listtype(choice) {		// 11/29/10 - added Scheduled tickets to menu. 12/02/10 Added persistance for the list view
	var params = "f_n=list_type&v_n=" + choice + "&sess_id=<?php print get_sess_key(__LINE__); ?>";					// flag 1, value h
	var url = "persist.php";
	sendRequest (url, l_handleResult, params);	// ($to_str, $text, $ticket_id)
	document.to_listtype.func.value=choice;
	show_btns_closed()
	}				// end function do_listtype()
	
function l_handleResult(req) {					// the 'called-back' persist function - nill content for the tickets list type persistance
	}

function cs_handleResult(req) {					// the 'called-back' function for show current or scheduled
	document.to_listtype.submit();	
	}	
 
	var side_bar_html = "<TABLE border=0 CLASS='sidebar' STYLE='margin-left:40px;'>";
<?php
	if ($any_open_tickets) {				// 1/18/11
?>		
	side_bar_html += "<TR class='even' STYLE = 'height:20px;' VALIGN='baseline'><TD colspan=99 align='center'<I>Click/Mouse-over for information</I> ";	
	
<?php
		}		// end if ($any_open_tickets)
	
	$query_sched = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status`='{$GLOBALS['STATUS_SCHEDULED']}'";	//	11/29/10
	$result_sched = mysql_query($query_sched) or do_error($query_sched, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	11/29/10
	$num_sched = mysql_num_rows($result_sched);	//	11/29/10
	
	if (($num_sched != 0) && ($func != 10)) {	//	11/29/10
		$scheduled_link = ($num_sched >= 2) ? "&nbsp;&nbsp;&nbsp;&raquo;&nbsp;<U>Scheduled: ({$num_sched}) </U>  " : "&nbsp;&nbsp;&nbsp;&nbsp;Scheduled: ({$num_sched})";
?>
		side_bar_html +="\t\t<SPAN class='scheduled' onClick='do_sched_jobs(10);'><?php print $scheduled_link;?></SPAN>\n";
		side_bar_html +="<br /><br /></TD></TR>\n";		
<?php
	}	//	11/29/10	
	
	if (($num_sched != 0) && ($func == 10)) {	//	11/29/10
?>
		side_bar_html +="\t\t<SPAN class='scheduled' onClick='do_curr_jobs(0);'>&raquo;&nbsp;View current situation</SPAN>\n";
		side_bar_html +="<br /><br /></TD></TR>\n";	
<?php
	}	//	11/29/10		
	
?>
	side_bar_html += "</TH></TR>";	

<?php
	if ($any_open_tickets) {
?>	
	side_bar_html += "<TR class='odd'><TD></TD><TD align='left' COLSPAN=2><B><?php print $incident; ?></B></TD><TD align='left'><B><?php print $nature;?></B></TD><TD align='left'><B>&nbsp;Addr</B></TD><TD align='left'><B>P</B></TD><TD align='left'><B>A</B></TD><TD align='left'><B>U</B></TD><TD align='left'><B>&nbsp;&nbsp;As of</B></TD></TR>";
<?php
		}
?>		
	var rowIds = [];		// 3/8/10
	var which;
	var i = 0;			// sidebar/icon index

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
	$restrict_ticket = "";
//	$restrict_ticket = (get_variable('restrict_user_tickets') && !(is_administrator()))? " AND owner=$_SESSION[user_id]" : "";
	$time_back = mysql_format_date(time() - (get_variable('delta_mins')*60) - ($cwi*3600));

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
		case 10:
			$where = "WHERE `status`='{$GLOBALS['STATUS_SCHEDULED']}'";	//	11/29/10
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
	else {					// 2/2/09, 8/12/09, 1/30/11
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
			ORDER BY `status` DESC, `booked_date` ASC, `severity` DESC,`$GLOBALS[mysql_prefix]ticket`.`id` ASC
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

	$line_limit = 25;											// 5/5/11
	if (mysql_num_rows($result)> $line_limit) {

?>

		side_bar_html += get_chg_disp_tr();		// adds the "Chg display' row at top of tickets list - 5/6/11

<?php
		}				// end if (mysql_num_rows ... )
$col_width = (int) floor($_SESSION['scr_width'] * .015);
	
// ===========================  begin major while() for tickets==========

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
		
		$sidebar_line = "<TD CLASS='$severityclass'  COLSPAN=2><NOBR>$strike" . $sp . shorten($row['scope'],$col_width) . " $strikend</NOBR></TD>";	//10/27/09
		$sidebar_line .= "<TD CLASS='$severityclass'><NOBR>$strike" . shorten($row['type'], $col_width) . " $strikend</NOBR></TD>";
		$sidebar_line .= "<TD ALIGN='left' CLASS='$severityclass'><NOBR>$strike" . shorten(($row['ticket_street'] . ' ' . $row['ticket_city']), $col_width) . " $strikend</NOBR></TD>";	// 8/2/10
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . $P . " </TD><TD CLASS='td_data'> " . $A . " </NOBR></TD>";

		$sidebar_line .= "<TD CLASS='td_data'>{$blinkst}{$row['units_assigned']}{$blinkend}</TD>";
		$_date = ($row['status']== $GLOBALS['STATUS_SCHEDULED'])? $row['booked_date']  : $row['updated'] ; 		// 1/30/11
		$sidebar_line .= "<TD CLASS='td_data'><NOBR> " . format_sb_date($_date) . "</NOBR></TD>";	

		$street = empty($row['ticket_street'])? "" : $row['ticket_street'] . "<BR/>" . $row['ticket_city'] . " " . $row['ticket_state'] ;

		$todisp = (is_guest())? "": "&nbsp;<A HREF='routes.php?ticket_id={$the_id}'><U>Dispatch</U></A>";	// 8/2/08
		$rand = ($istest)? "&rand=" . chr(rand(65,90)) : "";													// 10/21/08	
?>
		var the_class = "emph";
<?php
		if (($quick) || ((integer) $func > 0 )) {		// 5/18/10
			print "\t\t	do_sidebar_t_ed (\"{$sidebar_line}\", ({$the_offset} + {$sb_indx}), {$row['tick_id']}, sym, \"{$tip}\");\n";
			}
		else {
			print "\t\t	do_sidebar_t_ed (\"{$sidebar_line}\", ({$the_offset} + {$sb_indx}), {$row['tick_id']}, sym, \"{$tip}\");\n";
			}
 
			$sb_indx++;
			}				// end tickets while ($row = ...)
//		if ($any_closed_tickets) {				// 1/28/11
		if (mysql_num_rows($result)<= $line_limit) {				// 5/6/11

?>

		side_bar_html += get_chg_disp_tr();		// adds the "Chg display' row at bottom of list 

<?php	
			}		// 		end if (mysql_num_rows($result)<= $line_limit)
			
//			}		// end if ($any_closed_tickets)
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
//		$sev_string = "Severities: normal ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]}), Medium ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]}), High ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})";
		$sev_string = "Severities: <SPAN CLASS='severity_normal'>Normal ({$by_severity[$GLOBALS['SEVERITY_NORMAL']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_medium'>Medium ({$by_severity[$GLOBALS['SEVERITY_MEDIUM']]})</SPAN>,&nbsp;&nbsp;<SPAN CLASS='severity_high'>High ({$by_severity[$GLOBALS['SEVERITY_HIGH']]})</SPAN>";

		unset($acts_ary, $pats_ary, $result_temp, $result_cl);
		
?>			

	side_bar_html +="</TABLE>\n";
	$("side_bar").innerHTML = side_bar_html;				// side_bar_html to incidents div 
	$('sev_counts').innerHTML = "<?php print $sev_string; ?>";			// 5/2/10
	

// ==========================================      RESPONDER start    ================================================

	side_bar_html ="<TABLE border=0 CLASS='sidebar' >\n";		// initialize units sidebar string
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

	if (!(empty($_POST)))						{$_SESSION['unit_flag_2'] =  $_POST['order'];}		// 6/11/10
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
	side_bar_html += "<TR CLASS = 'even' VALIGN='baseline'><TD COLSPAN=99 ALIGN='center' STYLE = 'height:20px;'>";
	side_bar_html += "<I>Sort:&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Unit &raquo; 	<input type = radio name = 'frm_order' value = 1 <?php print $checked[1];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Type &raquo; 	<input type = radio name = 'frm_order' value = 2 <?php print $checked[2];?> onClick = 'do_sort_sub(this.value);' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	side_bar_html += "Status &raquo; <input type = radio name = 'frm_order' value = 3 <?php print $checked[3];?> onClick = 'do_sort_sub(this.value);' />";
	side_bar_html += "</I></TD></TR>";
<?php	
		print "\n\t\tside_bar_html += \"<TR CLASS='odd'><TD></TD><TD>&nbsp;<B>Unit</B> ({$units_ct}) </TD>	<TD onClick = 'do_mail_win(null, null); ' ALIGN = 'center'><IMG SRC='mail_red.png' /></TD><TD>&nbsp; <B>Status</B></TD><TD COLSPAN=2><B>" . $incident . "</B></TD><TD><B>&nbsp;As of</B></TD></TR>\"\n" ;
		}

	$aprs = $instam = $locatea = $gtrack = $glat = FALSE;		//7/23/09

	$utc = gmdate ("U");				// 3/25/09

// ===========================  begin major while() for RESPONDER ==========

	$chgd_unit = $_SESSION['unit_flag_1'];					// possibly 0 - 4/8/10
	$_SESSION['unit_flag_1'] = 0;							// one-time only - 4/11/10

	while ($row = stripslashes_deep(mysql_fetch_array($result))) {	
		$got_point = FALSE;

		$name = $row['handle'];			//	10/8/09
		$index =  addslashes($row['icon_str']);	// 3/19/11
		
		print "\t\tvar sym = '$index';\n";				// for sidebar and icon 10/8/09		
												// 2/13/09
		$todisp = (is_guest())? "": "&nbsp;&nbsp;<A HREF='units_nm.php?func=responder&view=true&disp=true&id=" . $row['unit_id'] . "'><U>Dispatch</U></A>&nbsp;&nbsp;";		// 08/8/02
		$toedit = (is_guest() || is_user())? "" :"&nbsp;&nbsp;<A HREF='units_nm.php?func=responder&edit=true&id=" . $row['unit_id'] . "'><U>Edit</U></A>&nbsp;&nbsp;" ;	// 5/11/10

		$hide_unit = ($row['hide']=="y")? "1" : "0" ;		// 3/8/10

		$update_error = strtotime('now - 6 hours');				// set the time for silent setting

// NAME

		$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$row['icon']];		// 2/1/10
		$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$row['icon']];
		$arrow = ($chgd_unit == $row['unit_id'])? "<IMG SRC='rtarrow.gif' />" : "" ; 	// 4/8/10
		$on_click =  " onclick_do_unit({$row['unit_id']}) ";		// 1/2/10

		$sidebar_line = "<TD onClick = '{$on_click}'>{$arrow}<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>  {$row['handle']}</B></U></SPAN></TD>";

// MAIL						
		if ((!is_guest()) && is_email($row['contact_via'])) {		// 2/1/10
			$mail_link = "\t<TD  CLASS='mylink' ALIGN='center'>"
				. "&nbsp; <IMG SRC='mail.png' BORDER=0 TITLE = 'click to email unit {$name}'"
				. " onclick = 'do_mail_win(\\\"{$name},{$row['contact_via']}\\\");'> "
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

		$tick_ct = (mysql_affected_rows()>1)? " (" . mysql_affected_rows() . ")": "";	// active dispatches
		$ass_td =  (mysql_affected_rows()>0)? 
			"<TD onMouseover=\\\"Tip('{$tip}')\\\" onmouseout=\\\"UnTip()\\\" onClick = '{$on_click}' COLSPAN=2 CLASS='$severityclass' >" .$tick_ct . shorten($row_assign['scope'], $col_width) . "</TD>":
			"<TD onClick = '{$on_click}' > na </TD>";

		$sidebar_line .= ($row_assign)? $ass_td : "<TD COLSPAN=2>na</TD>";

// AS OF
		$strike = $strike_end = "";										// any remote source?

		$the_time = $row['updated'];
//		$the_class = "td_data";
		$the_class = "";
				
		if (abs($utc - $the_time) > $GLOBALS['TOLERANCE']) {								// attempt to identify  non-current values
			$strike = "<STRIKE>";
			$strike_end = "</STRIKE>";
			} 
		else {
			$strike = $strike_end = "";
			}

		$sidebar_line .= "<TD onClick = '{$on_click}' CLASS='$the_class'> {$strike}" . format_sb_date($the_time) . "{$strike_end} </TD>";	// 6/17/08

		print "\t\tdo_sidebar_u_ed (\"{$sidebar_line}\",  {$sb_indx}, '{$on_click}', sym, \"{$tip}\");\n";		// (sidebar, line_no, on_click, letter)

	if ($row['hide']=="y") {						// 3/8/10		
?>
		var rowId = tr_id_fixed_part + <?php print $sb_indx; ?>;			// row index for row hide/show - 3/2/10
		rowIds.push(rowId);													// form is "tr_id_??" where ?? is the row no.
<?php
		}											// end if ($row['hide']=="y")
	$sb_indx++;				// zero-based
	}				// end  ==========  while() for RESPONDER ==========

	$source_legend = (($aprs)||($instam)||($gtrack)||($locatea)||($glat))? "<TD CLASS='emph' ALIGN='left'>Source time</TD>": "<TD></TD>";		// if any remote data/time 3/24/09

	print "\n\tside_bar_html+= \"<TR CLASS='\" + colors[i%2] +\"'><TD COLSPAN=7 ALIGN='center'>{$source_legend}</TD></TR>\";\n";

?>
	$("side_bar_r").innerHTML = side_bar_html;										// side_bar_html to responders div	
	
// ====================================  Add Facilities to Map 8/1/09 ================================================
//	side_bar_html ="<TABLE border=0 CLASS='sidebar' WIDTH = <?php print max(320, intval($_SESSION['scr_width']* 0.4));?> >\n";
	side_bar_html ="<TABLE border=0 CLASS='sidebar' >\n";

	var icons=[];	
	var g=0;

<?php

	$query_fac = "SELECT *,UNIX_TIMESTAMP(updated) AS updated, 
		`$GLOBALS[mysql_prefix]facilities`.id AS `fac_id`, 
		`$GLOBALS[mysql_prefix]facilities`.description AS `facility_description`,
		`$GLOBALS[mysql_prefix]fac_types`.name AS `fac_type_name`, 
		`$GLOBALS[mysql_prefix]facilities`.name AS `facility_name`
		FROM `$GLOBALS[mysql_prefix]facilities` 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` ON `$GLOBALS[mysql_prefix]facilities`.type = `$GLOBALS[mysql_prefix]fac_types`.id 
		LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` ON `$GLOBALS[mysql_prefix]facilities`.status_id = `$GLOBALS[mysql_prefix]fac_status`.id 
		ORDER BY `handle` ASC";

	$result_fac = mysql_query($query_fac) or do_error($query_fac, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
	$mail_str = (may_email())? "do_fac_mail_win();": "";		// 7/2/10
	$temp = max(320, intval($_SESSION['scr_width']* 0.4));
	print "\n\t\tside_bar_html += \"<TR CLASS='even' colspan='99'><TABLE ID='fac_table' STYLE='display: inline-block; width: 650px'>\"";
	print (mysql_affected_rows()==0)? "" : "\n\t\tside_bar_html += \"<TR><TH ALIGN='center' COLSPAN='99'><BR>Facilities</TR>\"\n";
	print (mysql_affected_rows()==0)?
		"\n\t\tside_bar_html += \"<TR CLASS='even'><TH ALIGN='left'><I><B><br />No Facilities!</I></B></TH></TR>\"\n" :
		"\n\t\tside_bar_html += \"<TR CLASS='even'><TD></TD><TD ALIGN='left'>&nbsp;<B>Facility</B> (" . mysql_affected_rows() . ") </TD><TD ALIGN='right'><IMG SRC='mail_red.png' BORDER=0 onClick = '{$mail_str}'/></TD><TD ALIGN='center'><B>Type</B></TD><TD ALIGN='right'><B>Status</B><TD ALIGN='center'><B>As of</B></TD></TR>\"\n";	// 7/2/10


//  ===========================  begin major while() for FACILITIES ==========
	
	while($row_fac = mysql_fetch_array($result_fac)){
		$fac_id=($row_fac['fac_id']);
		$fac_type=($row_fac['icon']);	
		$fac_name = addslashes($row_fac['facility_name']);			//		10/8/09
		$fac_handle = addslashes($row_fac['handle']);				//		10/8/09
		$fac_index =  addslashes($row_fac['icon_str']);
		
		$on_click =  ($can_edit)? "fac_click_ed({$fac_id})" : "fac_click_vw({$fac_id})";		// 8/24/10		

		print "\t\tvar fac_sym = '$fac_index';\n";			//	 for sidebar and icon 10/8/09

		$the_bg_color = 	$GLOBALS['FACY_TYPES_BG'][$row_fac['icon']];		// 2/8/10
		$the_text_color = 	$GLOBALS['FACY_TYPES_TEXT'][$row_fac['icon']];		// 2/8/10			

		$sidebar_fac_line = "<TD onClick = '{$on_click}' TITLE = '{$fac_name}' ALIGN='left'><SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};' >{$fac_handle}</SPAN></TD>";

// MAIL					
		if ((may_email()) && ((is_email($row_fac['contact_email'])) || (is_email($row_fac['security_email']))) ) {		// 7/2/10

			$mail_link = "\t<TD CLASS='mylink' ALIGN='center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
				. "<IMG SRC='mail.png' BORDER=0 TITLE = 'click to email facility {$fac_handle}'"
				. " onclick = 'do_mail_win(\\\"{$fac_handle},{$row_fac['contact_email']}\\\");'> "
				. "</TD>";		// 4/26/09
				}
		else {
			$mail_link = "\t<TD ALIGN='center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>na</b></TD>";
			}
		$sidebar_fac_line .= $mail_link;

		$sidebar_fac_line .= "<TD ALIGN='left'  onClick = '{$on_click};' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . addslashes(shorten($row_fac['fac_type_name'],$col_width)) .          "</TD>";
		$sidebar_fac_line .= "<TD ALIGN='left'  onClick = '{$on_click};' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . addslashes($row_fac['status_val']) .          "</TD>";
		$sidebar_fac_line .= "<TD onClick = '{$on_click};' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . format_sb_date($row_fac['updated']) . "</TD>";

?>
		var fac_icon = "td_label";
		do_sidebar_fac_ed ("<?php print $sidebar_fac_line;?>", <?php print $row_fac['fac_id'];?>, fac_sym, fac_icon, g);		
		g++;
<?php
	$sb_indx++;				// zero-based - 6/30/10
	}	// end while
?>
	side_bar_html += "</TD></TR>\n";

	<?php	
// =====================================End of functions to show facilities========================================================================

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 1 ";		// 10/21/09

		$result_ct = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_closed = mysql_num_rows($result_ct); 
		unset($result_ct);

	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `status` = 3 ";		// 10/21/09
		$result_scheduled = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$num_scheduled = mysql_num_rows($result_scheduled); 
		unset($result_scheduled);
?>
	side_bar_html +="<TR><TD COLSPAN='99'></TD></TR><TR><TD COLSPAN=99><TABLE ALIGN='center'>";
<?php
	if ($any_facilities) {
?>	
	side_bar_html +="<?php print get_facilities_legend();?>";		// legend row
<?php
		}
?>		
	side_bar_html +="</TABLE></TD></TR></TABLE>\n";
	$("side_bar_f").innerHTML = side_bar_html;	//side_bar_html to facilities div

	side_bar_html = "";


</SCRIPT>
<?php
	echo "Time Elapsed: ".round((microtime(true) - $time), 3)."s";

	}				// end function list_tickets() ===========================================================


//	} { -- dummy

function show_ticket($id,$print='false', $search = FALSE) {								/* show specified ticket */
	global $istest,  $nature, $disposition, $patient, $incident, $incidents;			// 12/3/10
	
	$can_edit = get_can_edit();
	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$id})</I>" : "";			// 1/25/09

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
		WHERE `$GLOBALS[mysql_prefix]ticket`.`id`= $id $restrict_ticket";			// 7/16/09, 8/12/09


	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (!mysql_num_rows($result)){	//no tickets? print "error" or "restricted user rights"
		print "<FONT CLASS=\"warn\">Internal error " . basename(__FILE__) ."/" .  __LINE__  .".  Notify developers of this message.</FONT>";	// 8/18/09
		exit();
		}

	$row = stripslashes_deep(mysql_fetch_array($result));

	if ($print == 'true') {				// 1/7/10 - 11/16/10

		print "<TABLE BORDER='0'ID='left' width='800px'>\n";		//
		print "<TR CLASS='print_TD'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$patient}: <I>" . $row['scope'] . "</B>" . $tickno . "</TD></TR>\n";
		print "<TR CLASS='print_TD' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> 
					<TD ALIGN='left'>" . get_severity($row['severity']);
		print 		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($row['in_types_id']);
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
		print empty($row['fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>" . $incident . " at Facility:</TD>	
				<TD ALIGN='left'>" .  $row['fac_name'] . "</TD></TR>\n";	// 8/1/09, 3/27/10
		print empty($row['rec_fac_name'])? "" : "<TR CLASS='print_TD' ><TD ALIGN='left'>Receiving Facility:</TD>	
				<TD ALIGN='left'>" .  $row['rec_fac_name'] . "</TD></TR>\n";	// 10/6/09	
		print empty($row['comments'])? "" : "<TR CLASS='print_TD'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>
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

	print "</TR>";
	print "<TR CLASS='odd' ><TD COLSPAN='2' CLASS='print_TD'>";
//  $lat = $row['lat']; $lng = $row['lng'];

//	print show_actions($row['id'], "date", FALSE, TRUE);		/* lists actions and patient data belonging to ticket */

	print "</TD></TR>\n";
	print "<TR><TD><IMG SRC='markers/up.png' BORDER=0  onclick = \"location.href = '#top';\" STYLE = 'margin-left: 20px'></TD></TR>\n";
	print "</TABLE>\n";


?>
	<SCRIPT SRC='../js/usng.js' TYPE='text/javascript'></SCRIPT>
	<SCRIPT>
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}
	</SCRIPT>
<?php

	}				// end function show_ticket() =======================================================
//	} {		-- dummy


function do_ticket($theRow, $theWidth, $search=FALSE, $dist=TRUE) {						// returns table - 6/26/10 - 11/16/10
//	global $disposition;
	global $iw_width, $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10

	$tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#{$theRow['id']})</I>" : "";			// 1/25/09

	switch($theRow['severity'])		{		//color tickets by severity
	 	case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
		case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
		default: $severityclass='severity_normal'; break;
		}
	$print = "<TABLE BORDER='0' ID='left' width='" . $theWidth . "'>\n";		//
	$print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>" . $incident . ": <I>" . highlight($search,$theRow['scope']) . "</B>" . $tickno . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Addr") . ":</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['tick_street']) . "</TD></TR>\n";
	$print .= "<TR CLASS='even' ><TD ALIGN='left'>" . get_text("City") . ":</TD>			<TD ALIGN='left'>" . highlight($search, $theRow['tick_city']);
	$print .=	"&nbsp;&nbsp;" . highlight($search, $theRow['tick_state']) . "</TD></TR>\n";
	$print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . get_text("Priority") . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
	$print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
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
	$print .= empty($theRow['fac_name']) ? "" : "<TR CLASS='odd' ><TD ALIGN='left'>" . $incident . " at Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['fac_name']) . "</TD></TR>\n";	// 8/1/09
	$print .= empty($theRow['rec_fac_name']) ? "" : "<TR CLASS='even' ><TD ALIGN='left'>Receiving Facility:</TD>		<TD ALIGN='left'>" . highlight($search, $theRow['rec_fac_name']) . "</TD></TR>\n";	// 10/6/09

	$print .= empty($theRow['comments'])? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . highlight($search, nl2br($theRow['comments'])) . "</TD></TR>\n";
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
