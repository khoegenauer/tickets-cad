<?php

/*
1/3/10 complete re-write to support button light-up for multi-user operation
1/11/10 added do_set_sess_exp() 
4/1/10 JSON replaces eval
4/5/10 do_time, cb width calc, cb script rename, syncAjax() {
4/7/10 $cycle added, 'mu_init' to 'get_latest.php', unit position change now tracked
4/10/10 replaced JSON return with tab-sep'd string
4/11/10 removed poll value references
4/15/10 fullscreen=no
5/12/10 show/hide Board button
6/12/10 browser id, audible alarms added for new ticket, chat invite
7/3/10 changed Card to SOP's
7/21/10 hide cb frame on logout
7/27/10 Unit login handling added
7/28/10 window focus added, logout moved to top row
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/8/10 implment version no. as hyphen-separated string
8/16/10 convert synch ajax to asynch
8/20/10 'term' => 'mobile'
8/21/10 light up active module/button
8/24/10 emd card handling cleanup
8/25/10 server variables handling cleaned up
8/27/10 chat error detection
*/

error_reporting(E_ALL);
require_once('./incs/functions.inc.php');		//7/28/10
require_once('./incs/browser.inc.php');			// 6/12/10
$temp = intval(get_variable('auto_poll'));
$poll_cycle_time = ($temp > 0)? ($temp * 1000) : 15000;	// seconds to ms - 8/20/10

$browser = trim(checkBrowser(FALSE));						// 6/12/10
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE><?php print ucwords (LessExtension(basename(__FILE__)));?> </TITLE>
<META NAME="Author" CONTENT="" />
<META NAME="Keywords" CONTENT="" />
<META NAME="Description" CONTENT="" />
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />

<STYLE type="text/css">
	BODY			{background-color: #EFEFEF; margin: 0; padding: 0; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;}
	table			{border-collapse:collapse;}
	table, td, th	{border:0px solid black;}
	.signal_r { margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 1px; border-STYLE: inset; border-color: #FF3366;
  				  padding: 1px 0.5em;text-decoration: none;float: left;color: black;background-color: #FF3366;font-weight: bolder;}
	.signal_b { margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 1px; border-STYLE: inset; border-color: #00CCFF;
  				  padding: 1px 0.5em;text-decoration: none;float: left;color: black;background-color: #00CCFF;font-weight: bolder;}
	.signal_w { margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 2px; border-STYLE: inset; border-color: #3366FF;
  				  padding: 1px 0.5em;text-decoration: none;float: left;color: white;background-color: #3366FF;font-weight: bolder;}
	.hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #DEE3E7;font-weight: bolder;}
	.plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: #EFEFEF;font-weight: bolder;}
	.message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}

	.hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder;}
	.plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;

  	</STYLE>
<link rel="stylesheet" type="text/css" href="/fvlogger/logger.css" /> 
<SCRIPT>
	var current_butt_id = "main";

	var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function $() {									// 1/21/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}
	
	function isNull(val) {								// checks var stuff = null;
		return val === null;
		}

	function do_time() {		//4/5/10
		var today=new Date();
		today.setDate(today.getSeconds()+7.5);		// half-adjust
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		$('time_of_day').innerHTML=h+":"+m;
		}

	var the_time = setInterval("do_time()", 15000);
	
	var is_initialized = false;
	var mu_interval = null;
	var lit=new Array();

	var chat_id = 0;				// new chat invite - 8/25/10
	var ticket_id = 0;				// new ticket
	var unit_id;					// 'moved' unit
	var updated;					// 'moved' unit date/time


	function do_loop() {								// monitor for changes - 4/10/10	
		sendRequest ('get_latest_id.php',get_latest_id_cb, "");	
		}			// end function do_loop()		

	function get_latest_id_cb(req) {					// get_latest_id callback() - 8/16/10
		var the_id_str=req.responseText;				// object to string	
		var the_id_arr=the_id_str.split("/");			// parse slash-separated string		

		if ((the_id_str.toLowerCase().indexOf("notice", 0) < 0) && (the_id_str.toLowerCase().indexOf("error", 0) < 0)){		// 8/27/10
			var temp = the_id_arr[0].trim();				// new chat invite?
			if (temp > chat_id) {
				chat_id = temp;
				chat_signal();								// light the chat button
				}
			}
	
		var temp =  the_id_arr[1].trim();				// ticket?
		if (temp > ticket_id) {
//			alert("136 " + temp + " : " + ticket_id);
			ticket_id = temp;
			tick_signal();								// light the ticket button
			}
	
		var temp =  the_id_arr[2].trim();				// unit?
		var temp1 =  the_id_arr[3].trim();				// unit timestamp?
		
		if ((temp != unit_id) || (temp1 != updated)) {
			unit_id = temp;
			updated =  temp1;							// timestamp this unit
			$('unit_id').innerHTML = unit_id;			// unit id
			unit_signal();								// light the unit button
			}
		}			// end function get_latest_id_cb()		

	function toHex(x) {
		hex="0123456789ABCDEF";almostAscii=' !"#$%&'+"'"+'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ['+'\\'+']^_`abcdefghijklmnopqrstuvwxyz{|}';r="";
		for(i=0;i<x.length;i++){
			let=x.charAt(i);pos=almostAscii.indexOf(let)+32;
			h16=Math.floor(pos/16);h1=pos%16;r+=hex.charAt(h16)+hex.charAt(h1);
			};
		return r;
		};

	function mu_init() {								// get initial values from server -  4/7/10

		function mu_get() {								// set cycle

			if (mu_interval!=null) {return;}			// ????
			mu_interval = window.setInterval('do_loop()', <?php print $poll_cycle_time;?>);		// 4/7/10
			}			// end function mu get()

		if (is_initialized) { return; }
		is_initialized = true;

		the_id_str = syncAjax("get_latest_id.php");			// note synch call
		the_id_arr = the_id_str.split("/");					// into array

		chat_id =  the_id_arr[0].trim();	
		ticket_id = the_id_arr[1].trim();		
		unit_id =  the_id_arr[2].trim();		
		updated =  the_id_arr[3].trim();					// timestamp this unit

		mu_get();				// start loop
		}
	
	function do_set_sess_exp() {			// set session expiration  - 1/11/10
		sendRequest ('set_cook_exp.php',set_cook_exp_handleResult, "");	
		}
		
	function set_cook_exp_handleResult() {
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

	function syncAjax(strURL) {							// synchronous ajax function - 4/5/10
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// form name
//			alert ("103 " + AJAX.responseText);
			return AJAX.responseText;																				 
			} 
		else {
			alert ("201: failed")
			return false;
			}																						 
		}		// end function sync Ajax()
	

	function do_audible() {	// 6/12/10
		try 		{document.getElementsByTagName('audio')[0].play();}
		catch (e) 	{}		// ignore	
		}				// end function do_audible()
		
	function get_line_count() {							// 4/5/10
		var url = "do_get_line_ct.php";
		var payload = syncAjax(url);						// does the work
		return payload;
		}		// end function get line_count()
	
	function chat_signal() {									// light the button
		CngClass("chat", "signal_r");
		lit["chat"] = true;
		do_audible();				// 6/12/10
		}
		
	function unit_signal() {										// light the units button and - if not already lit red - the situation button
//		CngClass("resp", "signal_b");								//  
		if (lit["main"]) {return; }									// already lit - possibly red
		CngClass("main", "signal_b");
		lit["main"] = true;
		}
		
	function tick_signal() {										// light the button
		CngClass("main", "signal_r");
		lit["main"] = true;
		do_audible();				// 6/12/10
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function do_hover (the_id) {
		if (the_id == current_butt_id) {return true;}				// 8/21/10
		if (lit[the_id]) {return true;}
		CngClass(the_id, 'hover');
		return true;
		}
	function do_lo_hover (the_id) {
		CngClass(the_id, 'lo_hover');
		return true;
		}
	function do_plain (the_id) {				// 8/21/10
		if (the_id == current_butt_id) {return true;}
		if (lit[the_id] ) {return true;}
		CngClass(the_id, 'plain');
		return true;
		}
	function do_lo_plain (the_id) {
		CngClass(the_id, 'lo_plain');
		return true;
		}
	function do_signal (the_id) {		// lights the light
		lit[the_id] = true;
		CngClass(the_id, 'signal');
		return true;
		}
	function do_off_signal (the_id) {
		CngClass(the_id, 'plain')
		return true;
		}

	function light_butt(btn_id) {				// 8/24/10 -     
		CngClass(btn_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == btn_id)) {	
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = btn_id;				// 
		}				// end function light_butt()

	function go_there (where, the_id) {		//
		CngClass(the_id, 'signal_w')			// highlight this button
		if(!(current_butt_id == the_id)) {	
			do_off_signal (current_butt_id);	// clear any prior one if different
			}
		current_butt_id = the_id;				// 8/21/10
		lit[the_id] = false;
		document.go.action = where;
		document.go.submit();
		}				// end function go there ()

	function show_msg (msg) {	
		$('msg_span').innerHTML = msg;			
		setTimeout("$('msg_span').innerHTML =''", 3000);	// show for 3 seconds
		}
	
	function logged_in() {								// returns boolean
		var temp = $("whom").innerHTML==NOT_STR;
		return !temp;
		}

	function do_logout() {						// 10/27/08
		$("user_id").innerHTML  = 0;
		$('time_of_day').innerHTML="";

		clearInterval(mu_interval);
		mu_interval = null;
		$('whom').innerHTML=NOT_STR; 
		is_initialized = false;

		try {						// close() any open windows
			newwindow_c.close();
			}
		catch(e) {
			}
		try {
			newwindow_sl.close();
			}
		catch(e) {
			}
		try {
			newwindow_cb.close();
			}
		catch(e) {
			}
		try {
			newwindow_fs.close();
			}
		catch(e) {
			}
		try {
			newwindow_em.close();
			}
		catch(e) {
			}

		newwindow_sl = newwindow_cb = newwindow_c = newwindow_fs = newwindow_em = null;

		hide_butts();		// hide buttons

<?php if (get_variable('call_board') == 2) { ?>

		parent.document.getElementById('the_frames').setAttribute('rows', '<?php print (get_variable('framesize') + 25);?>, 0, *'); // 7/21/10

<?php } ?>

//		alert(383);
		$('gout').style.display = 'none';		// hide the logout button
		document.gout_form.submit();			// send logout 
		}
	
	function hide_butts() {						// 10/27/08
		setTimeout(" $('buttons').style.visibility = 'hidden';" , 1000);
		}

	function show_butts() {						// 10/27/08
		$("buttons").style.visibility = "visible";
		}

//	============== module window openers ===========================================

	var newwindow_sl = null;
	var starting;
	
	function do_sta_log() {				// 1/19/09
		light_butt('log') ;
		if ((newwindow_sl) && (!(newwindow_sl.closed))) {newwindow_sl.focus(); return;}		// 7/28/10	
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;	
			do_set_sess_exp();		// session expiration update
			newwindow_sl=window.open("log.php", "sta_log",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=600,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (isNull(newwindow_sl)) {
				alert ("Station log operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_sl.focus();
			starting = false;
			}
		}		// end function do sta_log()
	
	
	var newwindow_cb = null;
	
	function do_callBoard() {
		light_butt('call');
		if ((newwindow_cb) && (!(newwindow_cb.closed))) {newwindow_cb.focus(); return;}		// 7/28/10
		if (logged_in()) {
			if(starting) {return;}						// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			var the_height = 60 + (16 * get_line_count());
			var the_width = (2.0 * Math.floor((Math.floor(.90 * screen.width) / 2.0)));

			newwindow_cb=window.open("board.php", "callBoard",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+", width="+the_width+", status=0,toolbar=0,menubar=0,location=0, left=20,top=300,screenX=20,screenY=300");

			if (isNull(newwindow_cb)) {
				alert ("Call Board operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_cb.focus();
			starting = false;
			}
		}		// end function do callBoard()
	
	var newwindow_c = null;

	function chat_win_close() {				// called from chat.pgp
		newwindow_c = null;
		}
		
	function do_chat() {
		light_butt('chat') ;
		if ((newwindow_c) && (!(newwindow_c.closed))) {newwindow_c.focus(); return;}		// 7/28/10
	
		if (logged_in()) {
			if(starting) {return;}					// 6/6/08
			starting=true;
			do_set_sess_exp();		// session expiration update
			try {
				newwindow_c.focus();
				}
			catch(e) {
				}

			newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
			if (isNull(newwindow_c)) {
				alert ("Chat operation requires popups to be enabled. Please adjust your browser options - or else turn off the Chat option setting.");
				return;
				}
			newwindow_c.focus();
			starting = false;
			CngClass("chat", "plain");

			}
		}
	
	var newwindow_fs = null;
	
	function do_full_scr() {                            //9/7/09
		light_butt('full');
		if ((newwindow_fs) && (!(newwindow_fs.closed))) {newwindow_fs.focus(); return;}		// 7/28/10
	
		if (logged_in()) {
			if(starting) {return;}                        // 4/15/10 fullscreen=no
			do_set_sess_exp();		// session expiration update
	
			if(window.focus() && newwindow_fs) {newwindow_fs.focus()}    // if already exists
			starting=true;
	
			params  = 'width='+screen.width;
			params += ', height='+screen.height;
			params += ', top=0, left=0', scrollbars = 1
			params += ', fullscreen=no';
			newwindow_fs=window.open("full_scr.php", "full_scr", params);
			if (isNull(newwindow_fs)) {
				alert ("This operation requires popups to be enabled. Please adjust your browser options.");
				return;
				}
			newwindow_fs.focus();
			starting = false;
			}
		}        // end function do full_scr()

	function do_emd_card(filename) {
		light_butt('card') ;
		try {
			newwindow_em=window.open(filename, "emdCard",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
			}
		catch (e) {
			}
		try {
			newwindow_em.focus();;
			}
		catch (e) {
			}
		if (isNull(newwindow_em)) {
			alert ("SOP Doc's operation requires popups to be enabled. Please adjust your browser options.");
			return;
			}
		starting = false;
		}

<?php
@session_start();
$start_up_str = (empty($_SESSION))? "": " mu_init(); mu_get()"; 
$the_whom = (empty($_SESSION))? NOT_STR: $_SESSION['user']; 
$the_level = (empty($_SESSION))? "na": get_level_text($_SESSION['level']); 
print "\n\t var the_whom = '{$the_whom}'\n";
print "\t var the_level ='{$the_level}'\n"; 
?>
	function top_init() {					// initialize display
		CngClass('main', 'signal_w');		// light up 'sit' button - 8/21/10
		$("whom").innerHTML  =	the_whom;
		$("level").innerHTML =	the_level;
		do_time();
		}		// end function top_init() 

	function do_log (instr) {
		$('log_div').innerHTML += instr + "<br />";
		}

	</SCRIPT>
</HEAD>

<!--<BODY> -->
<BODY onLoad = "top_init();">
	<TABLE ALIGN='left'>
		<TR VALIGN='top'>
			<TD ROWSPAN=2><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
			<TD>
<?php
	$temp = get_variable('_version');				// 8/8/10
	$version_ary = explode ( "-", $temp, 2);
?>
				<SPAN><FONT SIZE="3">ickets <?php print trim($version_ary[0]) . " on <B>". get_variable('host')."</B></FONT>"; ?></SPAN>
						<SPAN STYLE = 'margin-left: 8px'><?php print get_text("Logged in"); ?>: </SPAN>
						<SPAN ID="whom" STYLE = 'margin-left: 4px'><?php print NOT_STR ; ?></SPAN>&nbsp;&nbsp;&nbsp;&nbsp; 
						<?php print get_text("Perm's"); ?>: <SPAN ID="level">na</SPAN>&nbsp;&nbsp;&nbsp;
						
<?php
	$temp = get_variable('auto_poll');

	$dir = "./emd_cards";
	
	if (file_exists ($dir)) {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if ((strlen($filename)>2) && (get_ext($filename)=="pdf"))  {
			    $card_file = $filename;						// at least one pdf, use first encountered
			    break;
			    }
			}
	
		$card_addr=(!empty($card_file))? $dir . "/" . $filename  : "";
		}
	
?>
				<SPAN ID='user_id' STYLE="display:none">0</SPAN><!-- default value - 5/29/10 -->
				<SPAN ID='unit_id' STYLE="display:none"></SPAN>	<!-- unit that has just moved - 4/7/10 -->
				<?php print get_text("Module"); ?>: <SPAN ID="script">login</SPAN>&nbsp;&nbsp;&nbsp;&nbsp;
				<?php print get_text("Time"); ?>: <b><SPAN ID="time_of_day" ></SPAN></b>&nbsp;&nbsp;&nbsp;&nbsp;

				<SPAN ID = 'gout' CLASS = 'hover_lo' onClick = "do_logout()" STYLE="display:none;" ><?php print get_text("Logout"); ?></SPAN> <!-- 7/28/10 -->
				
<?php
		if ($_SERVER['HTTP_HOST'] == "127.0.0.1") { print "&nbsp;&nbsp;&nbsp;&nbsp;DB:&nbsp;{$mysql_db}&nbsp;&nbsp;&nbsp;&nbsp;";}
?>	

				<SPAN ID='msg_span' CLASS = 'message'></SPAN>
				<br />
			</TD></TR>
		<TR><TD ID = 'buttons' STYLE = "visibility:hidden">
			<SPAN ID = 'main'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick ="go_there('main.php', this.id);"><?php print get_text("Situation"); ?></SPAN>
			<SPAN ID = 'add'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('add.php', this.id);"><?php print get_text("New"); ?></SPAN>
			<SPAN ID = 'resp'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('units.php', this.id);"><?php print get_text("Units"); ?></SPAN>
			<SPAN ID = 'facy'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('facilities.php', this.id);"><?php print get_text("Fac's"); ?></SPAN>
			<SPAN ID = 'srch'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('search.php', this.id);"><?php print get_text("Search"); ?></SPAN>
			<SPAN ID = 'reps'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('reports.php', this.id);"><?php print get_text("Reports"); ?></SPAN>
			<SPAN ID = 'conf'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('config.php', this.id);"><?php print get_text("Config"); ?></SPAN>
<?php
	if (!(empty($card_addr))) {
?>
			<SPAN ID = 'card'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting = false; do_emd_card('<?php print $card_addr; ?>')"><?php print get_text("SOP's"); ?></SPAN>	<!-- 7/3/10 -->
<?php
			}
		if (!intval(get_variable('chat_time')==0)) { 		
?>
			<SPAN ID = 'chat'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_chat();"><?php print get_text("Chat"); ?></SPAN>
<?php
			}
		$call_disp_attr = (get_variable('call_board')==1)?  "inline" : "none"; 
?>			
			<SPAN ID = 'help'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('help.php', this.id);"><?php print get_text("Help"); ?></SPAN>
			<SPAN ID = 'log'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "do_sta_log()"><?php print get_text("Log"); ?></SPAN>
			<SPAN ID = 'full'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false; do_full_scr()"><?php print get_text("Full scr"); ?></SPAN>			
			<SPAN ID = 'links'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "light_butt('links'); parent.main.$('links').style.display='inline';"><?php print get_text("Links"); ?></SPAN>
			<SPAN ID = 'call'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "starting=false;do_callBoard()" STYLE = 'display:<?php print $call_disp_attr; ?>'><?php print get_text("Board"); ?></SPAN> <!-- 5/12/10 -->
<!-- ================== -->
			<SPAN ID = 'term'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
				onClick = "go_there('mobile.php', this.id);"><?php print get_text("Mobile"); ?></SPAN>	<!-- 7/27/10 -->
<!-- ================== -->
						</TD>
		</TR>
			
	</TABLE>
	<FORM NAME="go" action="#" TARGET = "main"></FORM>
	
	<FORM NAME="gout_form" action="main.php" TARGET = "main">
	<INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
	</FORM>
	<P>
		<DIV ID = "log_div"></DIV>
<!-- <button onclick = 'alert(getElementById("user_id"))'>Test</button> -->
<?php
	$the_wav_file = get_variable('sound_wav');		// browser-specific cabilities as of 6/12/10
	$the_mp3_file = get_variable('sound_mp3');

	$temp = explode (" ", $browser);
	switch (trim($temp[0])) {		
	    case "firefox" :
			print (empty($the_wav_file))? "\n": "\t\t<audio src=\"./sounds/{$the_wav_file}\" preload></audio>\n";
			break;
	    case "chrome" :
	    case "safari" :
			print (empty($the_mp3_file))? "\n":  "\t\t<audio src=\"./sounds/{$the_mp3_file}\" preload></audio>\n";
			break;
	    default:
		}	// end switch
?>
<!--  example frame manipulation
<button onClick = "alert(parent.document.getElementById('the_frames').getAttribute('rows'));">Get</button>
<button onClick = "parent.document.getElementById('the_frames').setAttribute('rows', '600, 100, *');">Set</button>
-->
</BODY>
</HTML>
