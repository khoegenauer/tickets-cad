<?php
/*
7/16/10 Initial Release for no internet operation - created from add.php
8/13/10 get_text settings

*/

@session_start();							// 4/4/10
require_once($_SESSION['fip']);

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

do_login(basename(__FILE__));
if($istest) {dump($_GET);}
if($istest) {dump($_POST);}

$current_facilities = array();												// 9/22/09
$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `id`";		// types in use
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_f = stripslashes_deep(mysql_fetch_assoc($result_f))) {
	$current_facilities [$row_f['id']] = array ($row_f['name'], $row_f['lat'], $row_f['lng']);
	}
$facilities = mysql_affected_rows();		// 3/24/10

function get_add_id() {				// 2/4/09
	$query  = "SELECT `id`, `contact` FROM `$GLOBALS[mysql_prefix]ticket` 
		WHERE `status`= '" . $GLOBALS['STATUS_RESERVED'] . "' AND  `contact` = " . quote_smart($_SERVER['REMOTE_ADDR']) . " LIMIT 1";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

	if (mysql_affected_rows() > 0) {
		$row = stripslashes_deep(mysql_fetch_array($result));
		unset($result);
		return $row['id'];				// return it
		}

	else {								// 7/16/09, 10/1/09
		$by = $_SESSION['user_id'];		// 5/27/10
		$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
				`id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , `severity` , `updated`, `booked_date`, `_by` 
			) VALUES (
				NULL , 0, " . quote_smart($_SERVER['REMOTE_ADDR']) . "	, NULL , NULL , NULL , NULL , NULL , NULL , NULL , 
				NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL, NULL, $by
			)";
			
		$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		return mysql_insert_id();
		}
	}

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;

	if ($get_add == 'true')	{

		function updt_ticket($id) {							/* 1/25/09 */
			global $addrs, $NOTIFY_TICKET;
	
			$post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
			$post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ; //10/1/09
			$post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;
	
			$_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
			$post_frm_affected 	 		= strip_html($post_frm_affected);
			$_POST['frm_scope']			= strip_html($_POST['frm_scope']);
	
			if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
					}
				}

			if (!get_variable('military_time'))	{		//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_booked_date == 'pm'){
					$post_frm_meridiem_booked_date	= ($post_frm_meridiem_booked_date + 12) % 24;
					}
				}

			if(empty($post_frm_owner)) {$post_frm_owner=0;}
			$frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

			if ($_POST['frm_status'] == 3) {
				$frm_booked_date = "$_POST[frm_year_booked_date]-$_POST[frm_month_booked_date]-$_POST[frm_day_booked_date] $_POST[frm_hour_booked_date]:$_POST[frm_minute_booked_date]:00$post_frm_meridiem_booked_date";
				} else {
//				$frm_booked_date = "NULL";
				$frm_booked_date = "";		// 6/20/10
				}	
	
			if (!get_variable('military_time'))	{			//put together date from the dropdown box and textbox values
				if ($post_frm_meridiem_problemstart == 'pm'){
					$_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
					}
				if (isset($_POST['frm_meridiem_problemend'])) {
					if ($_POST['frm_meridiem_problemend'] == 'pm'){
						$_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
						}
					}
				if (isset($_POST['frm_meridiem_booked_date'])) {	//10/1/09
					if ($_POST['frm_meridiem_booked_date'] == 'pm'){
						$_POST['frm_hour_booked_date'] = ($_POST['frm_hour_booked_date'] + 12) % 24;
						}
					}
				}
			$frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";
			
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
			if(empty($post_frm_owner)) {$post_frm_owner=0;}

			switch (get_variable('serial_no_ap')) {									// 1/22/09
			
				case 0:								/*  no serial no. */
				    $name_rev = $_POST['frm_scope'];
				    break;
				case 1:								/*  prepend  */
					$name_rev =  $id . "/" . $_POST['frm_scope'];
				    break;
				case 2:								/*  append  */
				    $name_rev = $_POST['frm_scope'] . "/" .  $id;
				    break;
				default:							/* error????  */
				    $name_rev = " error  error  error ";
				}
															// 8/23/08, 9/20/08, 8/13/09
			$facility_id = 		empty($_POST['frm_facility_id'])?		0 : trim($_POST['frm_facility_id']);				// 9/28/09
			$rec_facility_id = 	empty($_POST['frm_rec_facility_id'])?	0 : trim($_POST['frm_rec_facility_id']);				// 9/28/09


			if ($facility_id > 0) {			// 9/22/09

				$query_g = "SELECT * FROM $GLOBALS[mysql_prefix]facilities WHERE `id`= $facility_id LIMIT 1";	
				$result_g = mysql_query($query_g) or do_error($query_g, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
				$row_g = stripslashes_deep(mysql_fetch_array($result_g));
				$the_lat = $row_g['lat'];								// use facility location
				$the_lng = $row_g['lng'];

			} else {
				$the_lat = quote_smart(trim($_POST['frm_lat']));		// use incident location
				$the_lng = quote_smart(trim($_POST['frm_lng']));
			}
			if ((($the_lat == 0) && ($the_lng == 0)) || (($the_lat == "") && ($the_lng == ""))) {
					$the_lat = 0.999999;
					$the_lng = 0.999999;
				}
				
			// perform db update	//9/22/09 added facility capability, 10/1/09 added receiving facility
			@session_start();	
			$by = $_SESSION['user_id'];
			$booked_date = empty($frm_booked_date)? "NULL" : quote_smart(trim($frm_booked_date)) ;	// 6/20/10


			$query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET 
				`contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
				`street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
				`city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
				`state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
				`phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
				`facility`= " . 		quote_smart($facility_id ) . ",
				`rec_facility`= " . 	quote_smart($rec_facility_id) . ",
				`lat`= " . 			$the_lat . ",
				`lng`= " . 			$the_lng . ",
				`scope`= " . 		quote_smart(trim($name_rev)) . ",
				`owner`= " . 		quote_smart(trim($post_frm_owner)) . ",
				`severity`= " . 	quote_smart(trim($_POST['frm_severity'])) . ",
				`in_types_id`= " . 	quote_smart(trim($_POST['frm_in_types_id'])) . ",
				`status`=" . 		quote_smart(trim($_POST['frm_status'])) . ",
				`problemstart`=".	quote_smart(trim($frm_problemstart)) . ",
				`problemend`=".		$frm_problemend . ",
				`description`= " .	quote_smart(trim($_POST['frm_description'])) .",
				`comments`= " . 	quote_smart(trim($_POST['frm_comments'])) .",
				`nine_one_one`= " . quote_smart(trim($_POST['frm_nine_one_one'])) .",
				`booked_date`= " . 	$booked_date .",
				`date`='$now',
				`updated`='$now',
				`_by` = $by
				WHERE ID=$id";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

			do_log($GLOBALS['LOG_INCIDENT_OPEN'], $id);
			
			if (intval($facility_id) > 0) {	//9/22/09, 10/1/09, 3/24/10
//				do_log($GLOBALS['LOG_FACILITY_INCIDENT_OPEN'], $id, '' ,'NULL' ,$facility_id);
				do_log($GLOBALS['LOG_FACILITY_INCIDENT_OPEN'], $id, '' ,0 ,$facility_id);	// - 7/11/10
				}
			if (intval($rec_facility_id) >  0) {	
//				do_log($GLOBALS['LOG_CALL_REC_FAC_SET'], $id, 'NULL' ,'NULL' ,'NULL' ,$rec_facility_id);	// 6/20/10
				do_log($GLOBALS['LOG_CALL_REC_FAC_SET'], $id, 0 ,0 ,0 ,$rec_facility_id);	// 6/20/10 - 7/11/10
				}

//			$where = ($_POST['frm_severity']> $GLOBALS['SEVERITY_NORMAL'] )? "" : " WHERE `severities` = 3";	// 2/22/09
//			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]notify` $where";
//			$ticket_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
//			if (mysql_affected_rows()>0) {			// do mail
//			
//				}
//		
			return $name_rev;
			}				// end function updt ticket() 
			
		$ticket_name = updt_ticket(trim($_POST['ticket_id']));				// 1/25/09
?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<HEAD><TITLE>Tickets - Add Module</TITLE>
			<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
			<META HTTP-EQUIV="Expires" CONTENT="0" />
			<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
			<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
			<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
			<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
			<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
		<SCRIPT>
<?php
		$addrs = notify_user($_POST['ticket_id'],$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array of adddr's for notification, or FALSE
		if ($addrs) {				// any addresses?
//		snap(basename( __FILE__) . __LINE__, count($addrs));
?>	
	function do_notify() {

		var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
		var theText= "TICKET - New: ";
		var theId = '<?php print $_POST['ticket_id'];?>';
		
//		mail_it ($to_str, $text, $theId, $text_sel=1;, $txt_only = FALSE)

		var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08
		
		sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
		}			// end function do notify()
	
	function handleResult(req) {				// the 'called-back' function
<?php
		if($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
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
			try { xmlhttp = XMLHttpFactories[i](); }
			catch (e) { continue; }
			break;
			}
		return xmlhttp;
		}		// end function createXMLHTTPObject()
<?php
		}				// end if ($addrs)
	else {
?>
	function do_notify() {	// dummy
		return;
		}
<?php
	
		}				// end if/else ($addrs)

	$form_name = (intval(get_variable('auto_route'))==1)? "to_routes" : "to_main";	
?>

		</SCRIPT>
		</HEAD>
	<BODY onLoad = "do_notify();document.<?php print $form_name;?>.submit();"> <!-- <?php print __LINE__;?> -->
<?php
	$now = time() - (intval(get_variable('delta_mins')*60));		// 6/20/10
	
	print "<BR /><BR /><BR /><CENTER><FONT CLASS='header'>Ticket: '{$ticket_name}  ' Added by '{$_SESSION['user_id']}' at " . date(get_variable("date_format"),$now) . "</FONT></CENTER><BR /><BR />";
?>	
	<FORM NAME='to_main' METHOD='post' ACTION='main.php'>
	<CENTER><INPUT TYPE='submit' VALUE='Main' />
	</FORM>

	<FORM NAME='to_routes' METHOD='get' ACTION='routes_nm.php'>
	<INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print $_POST['ticket_id'];?>' />
	<INPUT TYPE='submit' VALUE='Routes' /></CENTER>
	</FORM>
<?php
//			list_tickets();
		}				// end if ($_GET['add'] ...
// ========================================================================================		
	else {
		if (is_guest() && !get_variable('guest_add_ticket')) {		// 6/25/10
			print '<FONT CLASS="warn">Guest/member users may not add tickets on this system.  Contact administrator for further information.</FONT>';
			exit();
			}
//	$query  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` ( `id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
//				`problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` , `severity` , `updated` )
//				VALUES (NULL , '', '', NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , '', NULL , '', NULL , '0', '0', '0', NULL);";
//		
//	$result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
//	$ticket_id = mysql_insert_id();

	$ticket_id = get_add_id();				// 2/4/09
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE>Tickets - Add Module</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />


<SCRIPT>
	function ck_frames() {		// onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';

			}
		}		// end function ck_frames()

	parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
	parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
	parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

	var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08		

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
	function isNullOrEmpty(str) {
		if (null == str || "" == str) {return true;} else { return false;}
		}

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};
	
	function chknum(val) { 
		return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));}
	
	function chkval(val, lo, hi) { 
		return  (chknum(val) && !((val> hi) || (val < lo)));}
	
	
	starting=false;						// 12/16/09
	function do_hist_win() {
		if(starting) {return;}	
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
<?php
	if (get_variable("locale") ==0) {				// USA only
?>
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
<?php
		}		// end locale check
?>		
		starting=true;	
		var url = "call_hist.php?frm_phone=" + goodno;
		newwindow_c_h=window.open(url, "Call_hist",  "titlebar, resizable=1, scrollbars, height=640,width=760,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
		if (isNullOrEmpty(newwindow_c_h)) {
			starting = false;
			alert ("Call history operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		newwindow_c_h.focus();
		starting = false;
		}		// function do hist_win()
			

	var request;
	var querySting;   				// will hold the POSTed data
	var tab1contents				// info window contents - first/only tab
	var grid = false;				// toggle
	
	function writeConsole(content) {
		top.consoleRef=window.open('','myconsole',
			'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1')
	 	top.consoleRef.document.writeln('<html><head><title>Console</title></head>'
			+'<body bgcolor=white onLoad="self.focus()">' +content +'</body></html>'
			)				// end top.consoleRef.document.writeln()
	 	top.consoleRef.document.close();
		}				// end function writeConsole(content)
	
	function getRes() {
		return window.screen.width + ' x ' + window.screen.height;
		}

	function URLEncode(plaintext ) {					// The Javascript escape and unescape functions do
														// NOT correspond with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
						"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
						"abcdefghijklmnopqrstuvwxyz" +	// guess
						"-_.!~*'()";					// RFC2396 Mark characters
		var HEX = "0123456789ABCDEF";
	
		var encoded = "";
		for (var i = 0; i < plaintext.length; i++ ) {
			var ch = plaintext.charAt(i);
		    if (ch == " ") {
			    encoded += "+";				// x-www-urlencoded, rather than %20
			} else if (SAFECHARS.indexOf(ch) != -1) {
			    encoded += ch;
			} else {
			    var charCode = ch.charCodeAt(0);
				if (charCode > 255) {
				    alert( "Unicode Character '"
	                        + ch
	                        + "' cannot be encoded using standard URL encoding.\n" +
					          "(URL encoding only supports 8-bit characters.)\n" +
							  "A space (+) will be substituted." );
					encoded += "+";
				} else {
					encoded += "%";
					encoded += HEX.charAt((charCode >> 4) & 0xF);
					encoded += HEX.charAt(charCode & 0xF);
					}
				}
			} 			// end for(...)
		return encoded;
		};			// end function
	
	function URLDecode(encoded ){   					// Replace + with ' '
	   var HEXCHARS = "0123456789ABCDEFabcdef";  		// Replace %xx with equivalent character
	   var plaintext = "";   							// Place [ERROR] in output if %xx is invalid.
	   var i = 0;
	   while (i < encoded.length) {
	       var ch = encoded.charAt(i);
		   if (ch == "+") {
		       plaintext += " ";
			   i++;
		   } else if (ch == "%") {
				if (i < (encoded.length-2)
						&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
						&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
					plaintext += unescape( encoded.substr(i,3) );
					i += 3;
				} else {
					alert( '-- invalid escape combination near ...' + encoded.substr(i) );
					plaintext += "%[ERROR]";
					i++;
				}
			} else {
				plaintext += ch;
				i++;
				}
		} 				// end  while (...)
		return plaintext;
		};				// end function URLDecode()
	
// *********************************************************************
	var the_form;
	function sendRequest(my_form, url,callback,postData) {		// ajax function set - 1/17/09
		the_form = my_form;
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
				alert('493: HTTP error ' + req.status);
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
	
// "Juan Wzzzzz;(123) 456-9876;1689 Abcd St;Abcdefghi;MD;16701;99.013297;-88.544775;"
//  1           2              3            4         5  6     7         8

	function ph_handleResult(req) {									// the called-back phone lookup function
		var result=req.responseText.split(";");						// parse semic-separated return string
		$('repeats').innerHTML = "(" + result[0].trim() + ")";		// prior calls this phone no. - 9/29/09 
		if (!(result.length>2)) {
<?php
	if (get_variable("locale") == 0) {				// USA only		// 10/2/09
?>
			alert("lookup failed");
<?php
		}
?>		
			}
		else {
			the_form.frm_contact.value=result[1].trim();	// name
			the_form.frm_phone.value=result[2].trim();		// phone
			the_form.frm_street.value=result[3].trim();		// street
			the_form.frm_city.value=result[4].trim();		// city
			the_form.frm_state.value=result[5].trim();		// state 
//			the_form.frm_zip.value=result[6].trim();		// frm_zip - unused
			if (result[9].length > 0) {								// misc constituents information - 3/13/10
				$('td_misc').innerHTML = '&nbsp;' + result[9].trim();
				$('tr_misc').style.display='';
				pt_to_map (the_form, result[7].trim(), result[8].trim());				// 1/19/09
				$("do_sv").style.display = "block";				// street view possible 2/11/09			
				}
			else if ((result[3].length>0) && (result[4].length>0) && (result[5].length>0)) {		// 4/27/10
				loc_lkup(the_form);				
				}
			}		// end else ...			
		}		// end function handleResult()

	function phone_lkup(){	
		var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
<?php
	if (get_variable("locale") == 0) {				// USA only
?>
		if (goodno.length<10) {
			alert("10-digit phone no. required - any format");
			return;}
<?php
		}		// end locale check
?>		
		var params = "phone=" + URLEncode(goodno)
		sendRequest (document.add, 'wp_lkup.php',ph_handleResult, params);		//1/17/09
		}
		

// *****************************************************************************
	var tbd = "TBD";									// 1/11/09
	var user_inc_name = false;							// 4/21/10

	function do_inc_name(str, indx) {								// 10/4/08, 7/7/09
//		if((document.add.frm_scope.value.trim()=="") || (document.add.frm_scope.value.trim()==tbd)) {	// 1/11/09
		if(!(user_inc_name)) {							// any user input? - 4/21/10
			document.add.frm_scope.value = str+"/";
			}
		if (protocols[indx]) {
//			$('proto_row').style.display = "block";
			$('proto_cell').innerHTML = protocols[indx];
			}
		else {
			$('proto_cell').innerHTML = "";		
			}
		}			// end function
	function datechk_s(theForm) {		// pblm start vs now
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
		var now = new Date();
		return (start.valueOf() <= now.valueOf());	
		}
	function datechk_e(theForm) {		// pblm end vs now
		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value, theForm.frm_minute_problemend.value, 0,0);
		var now = new Date();
		return (end.valueOf() <= now.valueOf());	
		}
	function datechk_r(theForm) {		// pblm start vs end
		var start = new Date();
		start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
		start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
	
		var end = new Date();
		end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
		end.setHours(theForm.frm_hour_problemend.value,theForm.frm_minute_problemend.value, 0,0);
		return (start.valueOf() <= end.valueOf());	
		}
		
	function validate(theForm) {	// 
		do_unlock_ps(theForm);								// 8/11/08
	
		var errmsg="";
		if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_CLOSED'];?>) && (!theForm.re_but.checked)) 
													{errmsg+= "\tRun end-date is required for Status=Closed\n";}
//		if (theForm.frm_in_types_id.value == 0)		{errmsg+= "\tNature of Incident is required\n";}			// 1/11/09
		if (theForm.frm_contact.value == "")		{errmsg+= "\tReported-by is required\n";}
		if (theForm.frm_scope.value == "")			{errmsg+= "\tIncident name is required\n";}
//		if (theForm.frm_description.value == "")	{errmsg+= "\tSynopsis is required\n";}
//			theForm.frm_lat.disabled=false;														// 9/9/08

		if (theForm.frm_lat.value == 0) {
			theForm.frm_lat.value == 0.999999
			theForm.frm_lng.value == 0.999999
			}

		if (theForm.frm_status.value==<?php print $GLOBALS['STATUS_SCHEDULED'];?>) {		//10/1/09
			if (theForm.frm_year_booked_date.value == "NULL") 		{errmsg+= "\tScheduled date time error - Hours\n";}
			if (theForm.frm_minute_booked_date.value == "NULL") 		{errmsg+= "\tScheduled date time error - Minutes\n";}
			}

//		theForm.frm_lat.disabled=true;
		if (!chkval(theForm.frm_hour_problemstart.value, 0,23)) 		{errmsg+= "\tRun start time error - Hours\n";}
		if (!chkval(theForm.frm_minute_problemstart.value, 0,59)) 		{errmsg+= "\tRun start time error - Minutes\n";}
		if (!datechk_s(theForm))										{errmsg+= "\tRun start time error - future date\n" ;}

		if (theForm.re_but.checked) {				// run end?
			do_unlock_pe(theForm);								// problemend values
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_e(theForm)){errmsg+= "\tRun start time error - future\n" ;}
			if (!datechk_r(theForm)){errmsg+= "\tRun start time error - future\n" ;}
		
			if (!chkval(theForm.frm_hour_problemend.value, 0,23)) 		{errmsg+= "\tRun end time error - Hours\n";}
			if (!chkval(theForm.frm_minute_problemend.value, 0,59)) 	{errmsg+= "\tRun end time error - Minutes\n";}
			}
		if (errmsg!="") {
			alert ("Please correct the following and re-submit:\n\n" + errmsg);
			return false;
			}
		else {
			do_unlock_ps(theForm);								// 8/11/08
			theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits
			return true;
			}
		}				// end function validate(theForm)
	
	function capWords(str){ 
		var words = str.split(" "); 
		for (var i=0 ; i < words.length ; i++){ 
			var testwd = words[i]; 
			var firLet = testwd.substr(0,1); 
			var rest = testwd.substr(1, testwd.length -1) 
			words[i] = firLet.toUpperCase() + rest 
	  	 	} 
		return( words.join(" ")); 
		} 
	
	function do_end(theForm) {			// enable run-end date/time inputs
		elem = $("runend1");
		elem.style.visibility = "visible";
<?php
		$show_ampm = (!get_variable('military_time')==1);
		if ($show_ampm){	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
			print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
			}
?>
		do_unlock_pe(theForm);								// problemend values
		}
	
	function do_reset(theForm) {				// disable run-end date/time inputs
		do_lock_ps(theForm);				// hskp problem start date
		do_lock_pe(theForm);				// hskp problem end date
		$("runend1").visibility = "hidden";
//		$("lock_p").style.visibility = "visible";	
		$("runend1").style.visibility = "hidden";	
		theForm.frm_lat.value=theForm.frm_lng.value="";
//		document.add.frm_ngs.disabled=true;									// 4/30/09	
//		$("USNG").style.textDecoration = '"none';
		$('booking1').style.visibility = 'hidden';
		$('td_misc').innerHTML ='';
		$('tr_misc').style.display='none';
		user_inc_name = false;							// no incident name input 4/21/10
		$('proto_cell').innerHTML = '';

		}		// end function reset()

	function do_problemstart(theForm, theBool) {							// 8/10/08
		theForm.frm_year_problemstart.disabled = theBool;
		theForm.frm_month_problemstart.disabled = theBool;
		theForm.frm_day_problemstart.disabled = theBool;
		theForm.frm_hour_problemstart.disabled = theBool;
		theForm.frm_minute_problemstart.disabled = theBool;
		if (theForm.frm_meridiem_problemstart) {theForm.frm_meridiem_problemstart.disabled = theBool;}
		}

	function do_problemend(theForm, theBool) {								// 8/10/08
		theForm.frm_year_problemend.disabled = theBool;
		theForm.frm_month_problemend.disabled = theBool;
		theForm.frm_day_problemend.disabled = theBool;
		theForm.frm_hour_problemend.disabled = theBool;
		theForm.frm_minute_problemend.disabled = theBool;
		if (theForm.frm_meridiem_problemend) {theForm.frm_meridiem_problemend.disabled = theBool;}
		}

	function do_booking(theForm) {			// 10/1/09 enable booked date entry
		elem = $("booking1");
		elem.style.visibility = "visible";
<?php
		$show_ampm = (!get_variable('military_time')==1);
		if ($show_ampm){	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
			print "\tdocument.add.frm_meridiem_booked_date.disabled = false;\n";
			}
?>
		do_booked_date(theForm, false);
		}

	function do_booked_date(theForm, theBool) {							// 10/1/09 Booked Date processing
		theForm.frm_year_booked_date.disabled = theBool;
		theForm.frm_month_booked_date.disabled = theBool;
		theForm.frm_day_booked_date.disabled = theBool;
		theForm.frm_hour_booked_date.disabled = theBool;
		theForm.frm_minute_booked_date.disabled = theBool;
		if (theForm.frm_meridiem_booked_date) {theForm.frm_meridiem_booked_date.disabled = theBool;}
		}

	function do_unlock_ps(theForm) {											// 8/10/08
		do_problemstart(theForm, false)
		$("lock_s").style.visibility = "hidden";		
		}

	function do_unlock_bd(theForm) {									// 9/29/09 Unlock booked date
		do_booked_date(theForm, false)
		$("lock_b").style.visibility = "hidden";		
		}
		
	function do_lock_ps(theForm) {												// 8/10/08
		do_problemstart(theForm, true)
		$("lock_s").style.visibility = "visible";
		}

	function do_unlock_pe(theForm) {											// 8/10/08 
		do_problemend(theForm, false)
//		$("lock_e").style.visibility = "hidden";		
		}
		
	function do_lock_pe(theForm) {												// 8/10/08
		do_problemend(theForm, true)
//		$("lock_e").style.visibility = "visible";
		}

	function do_unlock_pos(theForm) {											// 12/5/08
		document.add.frm_ngs.disabled=false;
		$("lock_p").style.visibility = "hidden";		
		$("USNG").style.textDecoration = "underline";							// 4/30/09		
		}
		
	var protocols = new Array();		// 7/7/09
	var fac_lat = [];
	var fac_lng = [];

<?php
		// Pulldown menu for use of Incident set at Facility 9/22/09, 3/18/10
	$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
	$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$pulldown = '<option value=0 selected>Incident at Facility</option>\n';	// 3/18/10
		while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
			$pulldown .= "<option value=\"{$row_fc['id']}\">{$row_fc['name']}</option>\n";
			print "\tfac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;\n";
			print "\tfac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;\n";

			}

		// Pulldown menu for use of receiving Facility 10/6/09, 3/18/10
	$query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";		
	$result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$pulldown2 = '<option value = 0 selected>Receiving facility</option>\n'; 	// 3/18/10
		while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
			$pulldown2 .= "<option value=\"{$row_rfc['id']}\">{$row_rfc['name']}</option>\n";
			print "\tfac_lat[" . $row_rfc['id'] . "] = " . $row_rfc['lat'] . " ;\n";
			print "\tfac_lng[" . $row_rfc['id'] . "] = " . $row_rfc['lng'] . " ;\n";

			}

	print "\n\tvar severities = new Array();\n";				// 6/25/10 - builds JS array of severities indexed to incident types 
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	print "\t severities.push(0);\n";		// the inserted "TBD" dummy
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		print "\t severities.push({$row['set_severity']});\n";
		}
?>
	function do_set_severity (in_val) {				// 6/26/10
		if(severities[in_val]>0) {document.add.frm_severity.selectedIndex = severities[in_val]};
		}

</SCRIPT>
</HEAD>

<BODY onload="ck_frames();do_lock_pe(document.add)">   <!-- <?php print __LINE__;?> -->		<!-- // 8/23/08 -->
<?php
require_once('./incs/links.inc.php');
?>
<TABLE BORDER="0" ID = "outer" >
<TR><TD>
<TABLE BORDER="0"></TD><TD>
<TR><TD ALIGN='center' COLSPAN='2'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'>New Call</FONT></FONT><BR />
<FONT SIZE=-1>(mouseover caption for help information)</FONT></FONT><BR /><BR /></TD></TR>
<FORM METHOD="post" ACTION="add_nm.php?add=true" NAME="add" onSubmit="return validate(document.add)">
<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="Location - type in location in fields, click location on map or use *Located at Facility* menu below "><?php print get_text("Location"); ?></A>:</TD>
	<TD><INPUT SIZE="61" TYPE="text" NAME="frm_street" VALUE="" MAXLENGTH="61"></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="City - defaults to default city set in configuration. Type in City if required"><?php print get_text("City");?></A>:
	&nbsp;&nbsp;&nbsp;&nbsp;</TD>
	<TD><INPUT SIZE="32" TYPE="text" NAME="frm_city" VALUE="<?php print get_variable('def_city'); ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value)">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="State - US State or non-US Country code e.g. UK for United Kingdom">St</A>:&nbsp;&nbsp;
		<INPUT SIZE="2" TYPE="text" NAME="frm_state" VALUE="<?php print get_variable('def_st'); ?>" MAXLENGTH="2"></TD></TR>
<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="Phone number"><?php print get_text("Phone");?></A>: &nbsp;&nbsp;&nbsp;&nbsp;
		<button style = 'margin-left:2px' type="button" onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="Lookup phone no." />
		</button>&nbsp;&nbsp;</TD>
	<TD><INPUT SIZE="16" TYPE="text" NAME="frm_phone" VALUE="<?php print get_variable('def_area_code');?>"  MAXLENGTH="16">&nbsp;<SPAN ID='repeats'></SPAN> <!-- 1/27/09 -->
	</TD></TR>
<TR CLASS='odd'>
	<TD CLASS="td_label"><A HREF="#" TITLE="Incident Nature or Type - Available types are set in in_types table in the configuration"><?php print get_text("Nature");?></A>:</TD>	
	<TD>
		<SELECT NAME="frm_in_types_id" onChange="do_set_severity (this.selectedIndex); do_inc_name(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());">	<!--  10/4/08 -->
		<OPTION VALUE=0 SELECTED>TBD</OPTION>				<!-- 1/11/09 -->
<?php
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]in_types` ORDER BY `group` ASC, `sort` ASC, `type` ASC";
		$temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
		$the_grp = strval(rand());			//  force initial optgroup value
		$i = 0;
		while ($temp_row = stripslashes_deep(mysql_fetch_array($temp_result))) {
			if ($the_grp != $temp_row['group']) {
				print ($i == 0)? "": "</OPTGROUP>\n";
				$the_grp = $temp_row['group'];
				print "<OPTGROUP LABEL='{$temp_row['group']}'>\n";
				}

			print "\t<OPTION VALUE=' {$temp_row['id']}'  CLASS='{$temp_row['group']}' title='{$temp_row['description']}'> {$temp_row['type']} </OPTION>\n";
			if (!(empty($temp_row['protocol']))) {				// 7/7/09 - note string key
				$temp = addslashes($temp_row['protocol']);
				print "\n<SCRIPT>protocols[{$temp_row['id']}] = '{$temp}';</SCRIPT>\n";		// 7/16/09, 5/6/10
				}
			$i++;
			}		// end while()
		print "\n</OPTGROUP>\n";
?>
	</SELECT>
	<SPAN CLASS="td_label" STYLE='margin-left:20px'><A HREF="#" TITLE="Incident Priority - Normal, Medium or High. Affects order and coloring of Incidents on Situation display"><?php print get_text("Priority");?></A>: 
	<SELECT NAME="frm_severity">
	<OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
	<OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
	<OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
	</SELECT>
	</TD></TR>
<TR><TD  CLASS="td_label"><SPAN><A HREF="#" TITLE="Incident Protocol - this will show automatically if a protocol is set for the Incident Type in the configuration"><?php print get_text("Protocol");?></A>:</SPAN></TD><TD ID='proto_cell'></TD></TR>
<TR CLASS='even' VALIGN="top"><TD CLASS="td_label"><A HREF="#" TITLE="Synopsis - Details about the Incident, ensure as much detail as possible is completed"><?php print get_text("Synopsis");?></A>: </TD><TD><TEXTAREA NAME="frm_description" COLS="48" ROWS="2" WRAP="virtual"></TEXTAREA></TD></TR>
<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="911 contact information"><?php print get_text("911 Contacted");?></A>:&nbsp;</TD>
	<TD><INPUT SIZE="56" TYPE="text" NAME="frm_nine_one_one" VALUE="" MAXLENGTH="96" ></TD></TR>
<TR CLASS='even'><TD CLASS="td_label"><A HREF="#" TITLE="Caller reporting the incident"><?php print get_text("Reported by");?></A>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
	<TD><INPUT SIZE="56" TYPE="text" NAME="frm_contact" VALUE="TBD" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"></TD></TR>
<TR CLASS='odd' ID = 'tr_misc' STYLE = 'display:none'><TD CLASS="td_label">Add'l:</TD><TD ID='td_misc' CLASS="td_label"></TD></TR> <!-- 3/13/10 -->
<?php 
		switch (get_variable('serial_no_ap')) {									// 1/22/09
		
			case 0:								/*  no serial no. */
			    $prepend = $append = "";
			    break;
			case 1:								/*  prepend  */
				$prepend = $ticket_id . "/";
				$append = "";
			    break;
			case 2:								/*  append  */
				$prepend = "";
				$append = "/" . $ticket_id;
			    break;
			default:							/* error????  */
			    $prepend = $append = " error ";			    
			}
?>
	<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="Incident Name - Partially completed and prepend or append incident ID depending on setting. Type in an easily identifiable name."><?php print get_text("Incident name");?></A>: <font color='red' size='-1'>*</font></TD>
		<TD><?php print $prepend;?> <INPUT SIZE="56" TYPE="text" NAME="frm_scope" VALUE="TBD" MAXLENGTH="61" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}" onkeypress='user_inc_name = true;'/><?php print $append;?></TD></TR>	<!-- 1/11/09 -->
	<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='60%' /></TD></TR>
	<TR CLASS='even' valign="middle"><TD CLASS="td_label">
		<A HREF="#" TITLE="Scheduled Date. Must be set if Incident Status is *Scheduled*. Sets date and time for a future booked incident, mainly used for non immediate patient transport. Click on Radio button to show date fields."><?php print get_text("Scheduled Date");?></A>:
		 &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="re_but" onClick ="do_booking(this.form);" /></TD><TD>	<!-- 9/29/09 -->
		<SPAN style = "visibility:hidden" ID = "booking1"><?php print generate_date_dropdown('booked_date',0, TRUE);?></SPAN>
		</TD></TR>
<?php
	if ($facilities > 0) {				// any? - 3/24/10
?>	
	<TR CLASS='odd'><TD CLASS="td_label"><A HREF="#" TITLE="Use the first dropdown menu to select the Facility where the incident is located at, use the second dropdown menu to select the facility where persons from the incident will be received">Facility?</A>:&nbsp;&nbsp;&nbsp;&nbsp;</TD>	 <!-- 9/22/09 -->
		<TD>
			<SELECT NAME="frm_facility_id"><?php print $pulldown; ?></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
			<SELECT NAME="frm_rec_facility_id" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}">
			<?php print $pulldown2; ?></SELECT>
		</TD></TR>
<?php
		}		// end if ($facilities > 0)
	else {
?>
	<INPUT TYPE = 'hidden' NAME = 'frm_facility_id' VALUE=''>
	<INPUT TYPE = 'hidden' NAME = 'frm_rec_facility_id' VALUE=''>
<?php
	}
?>		
	<!--
	<TR CLASS='odd'><TD CLASS="td_label">Affected:</TD><TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48"></TD></TR>
	-->
	<TR CLASS='even' VALIGN='bottom'><TD CLASS="td_label"><A HREF="#" TITLE="Run-start, Incident start time. Defaults to current date and time or edit by clicking padlock icon to enable date & time fields"><?php print get_text("Run Start");?></A>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img id='lock_s' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_ps(document.add);'></TD><TD>
<?php print generate_date_dropdown('problemstart',0,TRUE);?>
		<SPAN CLASS="td_label" STYLE='margin-left:12px' ><A HREF="#" TITLE="Incident Status - Open or Closed or set to Scheduled for future booked calls"><?php print get_text("Status");?></A>:</SPAN>
		<SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected>Open</OPTION>
		<OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'>Closed</OPTION>
		<OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED']; ?>'>Scheduled</OPTION></SELECT>		
		</TD></TR>
	<TR CLASS='odd' valign="middle"><TD CLASS="td_label"><A HREF="#" TITLE="Run-end, Incident end time. When Incident is closed, click on radio button which will enable date & time fields"><?php print get_text("Run End");?></A>: &nbsp;&nbsp;<input type="radio" name="re_but" onClick ="do_end(this.form);" /></TD><TD>
		<SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
		</TD></TR>
	<TR CLASS='even' VALIGN="top"><TD CLASS="td_label"><A HREF="#" TITLE="Disposition - additional comments about incident"><?php print get_text("Disposition");?></A>:</TD>
		<TD><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD></TR>
	<TR CLASS='even'><TD COLSPAN="3" ALIGN="center"><BR />
		<INPUT TYPE="button" VALUE="History"  onClick="do_hist_win();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="button" VALUE="Cancel"  onClick="history.back();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="reset" VALUE="Reset" onclick= "do_reset(this.form);" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<INPUT TYPE="submit" VALUE="Submit"></TD></TR>	<!-- 8/11/08 -->
		<INPUT TYPE="hidden" NAME="frm_lat" VALUE="">				<!-- // 9/9/08 -->
		<INPUT TYPE="hidden" NAME="frm_lng" VALUE="">
		<INPUT TYPE="hidden" NAME="ticket_id" VALUE="<?php print $ticket_id;?>">	<!-- 1/25/09, 3/10/09 -->
	</FORM></TABLE>
	</TD></TR>
	</TABLE>
	
<?php 
//	dump($_SESSION['user_id']);
	} //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>	
</BODY></HTML>