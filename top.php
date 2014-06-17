<?php


include'./incs/error_reporting.php';
require_once './incs/functions.inc.php';		// 7/28/10
require_once './incs/browser.inc.php';			// 6/12/10
@session_start();

if (file_exists("./incs/modules.inc.php")) {	//	10/28/10
    require_once './incs/modules.inc.php';
    }

//$temp = intval(get_variable('auto_poll'));
//$poll_cycle_time = ($temp > 0)? ($temp * 1000) : 15000;	// 5/30/2013
$poll_cycle_time = 5000;	// five seconds to ms - 8/20/10

$browser = trim(checkBrowser(FALSE));						// 6/12/10
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<TITLE><?php print ucwords (LessExtension(basename(__FILE__)));?> </TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>

<STYLE type="text/css">
    table			{border-collapse:collapse;}
    table, td, th	{border:0px solid black;}
    .signal_r { margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 1px; border-STYLE: inset; border-color: #FF3366;
                    padding: 1px 0.5em;text-decoration: none;float: left;color: black;background-color: #FF3366;font-weight: bolder;}
	.signal_o { margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FFFFFF; border-width: 1px; border-STYLE: inset; border-color: #FF3366;
  				  padding: 1px 0.5em;text-decoration: none;float: left;color: black;background-color: #CC9900;font-weight: bolder;}
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
    .plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
    input		{background-color:transparent;}		/* Benefit IE radio buttons */
      </STYLE>
<link rel="stylesheet" type="text/css" href="/fvlogger/logger.css" />
<SCRIPT SRC="./js/misc_function.js"></SCRIPT>	<!-- 1/6/11 JSON call-->
<SCRIPT SRC='./js/md5.js'></SCRIPT>				<!-- 11/30/08 -->

<SCRIPT>
    var current_butt_id = "main";
    var internet = false;
    var is_messaging = 0;
<?php
if (file_exists("./incs/modules.inc.php")) {
    ?>
    var ticker_active = <?php print module_active("Ticker");?>;
<?php
    } else {
    ?>
    var ticker_active = 0;
<?php
    }
    ?>

    var NOT_STR = '<?php echo NOT_STR;?>';			// value if not logged-in, defined in functions.inc.php
/**
 *
 * @returns {unresolved}
 */
    String.prototype.trim = function () {
        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @returns {Array}
 */
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
/**
 *
 * @param {type} val
 * @returns {Boolean}
 */
    function isNull(val) {								// checks var stuff = null;

        return val === null;
        }
/**
 *
 * @returns {undefined}
 */
    function do_time() {		//4/5/10
        var today=new Date();
        today.setDate(today.getSeconds()+7.5);		// half-adjust
        var hours = today.getHours();
        var h=(hours < 10)?  "0" + hours : hours;
        var mins = today.getMinutes();
        var m=(mins < 10)?  "0" + mins : mins;
        $('time_of_day').innerHTML=h+":"+m;
        }

    var the_time = setInterval("do_time()", 15000);

    var is_initialized = false;
    var nmis_initialized = false;	//	10/23/12
    var mu_interval = null;
    var nm_interval = null;			//	10/23/12
    var msgs_interval = null;		//	10/23/12
    var emsgs_interval = null;		//	10/23/12
    var pos_interval = null;
    var file_interval = null;
    var lit=new Array();
	var lit_r = new Array();
	var lit_o = new Array();
	var unread_messages = 0;

    var chat_id = 0;				// new chat invite - 8/25/10
    var ticket_id = 0;				// new ticket
    var unit_id;					// 'moved' unit
    var updated;					// 'moved' unit date/time
    var dispatch;					// latest dispatch status change - date-time
    var new_msg = 0;				// New messages, 10/23/12
    var the_unit = 0;
    var the_status = 0;
    var the_time = 0;
/**
 *
 * @returns {undefined}
 */
    function do_msgs_loop() {		//	10/23/12
        var randomnumber=Math.floor(Math.random()*99999999);
        if (window.XMLHttpRequest) {
            xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", "./ajax/get_messages.php?version=" + randomnumber, true);
            xmlHttp.onreadystatechange = handleRequestStateChange2;
            xmlHttp.send(null);
            }
        }			// end function do_msgs_loop()
/**
 *
 * @returns {undefined}
 */
    function handleRequestStateChange2() {	//	10/23/12
        var the_resp;
        var the_val;
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) {
                var response = JSON.decode(xmlHttp.responseText);
                for (var key in response[0]) {
                    the_resp = key;
                    the_val = response[0][key];
                    un_stat_chg(the_resp, the_val);
                    }
                if (response[1]) {
                    var the_mess = response[1][0];
                    var the_stored = response[1][1];
                    if (the_stored != 0) {
                        show_msg("<?php gettext('There are');?> " + the_stored + " <?php gettext('new messages');?>");
                        msg_signal_r();								// light the msg button
						} else {
						msg_signal_r_off();								// unlight the msg button
                        }
                    }
                }
            }
        }
/**
 *
 * @returns {undefined}
 */
    function do_loop() {								// monitor for changes - 4/10/10, 6/10/11
        var randomnumber=Math.floor(Math.random()*99999999);
        sendRequest ('get_latest_id.php?version=' + randomnumber,get_latest_id_cb, "");
        }			// end function do_loop()
/**
 *
 * @returns {undefined}
 */
    function do_latest_msgs_loop() {	//	10/23/12
        var randomnumber=Math.floor(Math.random()*99999999);
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,get_latest_messages_cb, "");
        }
/**
 *
 * @param {type} unit_id
 * @param {type} the_stat_id
 * @returns {undefined}
 */
    function un_stat_chg(unit_id, the_stat_id) {	//	10/23/12
        var the_stat_control = "frm_status_id_u_" + unit_id;
        if (typeof parent.frames["main"].change_status_sel == 'function') {
            parent.frames["main"].change_status_sel(the_stat_control, the_stat_id);
            }
        }

    var arr_lgth_good = 13;								// size of a valid returned array - 2/25/12, 10/23/12

    function get_latest_id_cb(req) {					// get_latest_id callback() - 8/16/10
        try {
            var the_id_arr=JSON.decode(req.responseText);	// 1/7/11
            }
        catch (e) {

            return;
            }

        try {
            var the_arr_lgth = the_id_arr.length;		// sanity check
            }
        catch (e) {
            alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
//			do_logout();				// 2/10/12
            return;
            }

        if (the_arr_lgth != arr_lgth_good) {
            alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
//			do_logout();				// 2/10/12
            }

        var temp = parseInt(the_id_arr[0]);				// new chat invite?
        if (temp != chat_id) {
            chat_id = temp;
            chat_signal();								// light the chat button
            }

        $("div_ticket_id").innerHTML = the_id_arr[1].trim();	// 2/19/12
        var temp =  parseInt(the_id_arr[1]);			// ticket?
        if (temp != ticket_id) {
            ticket_id = temp;
            tick_signal();								// light the ticket button
            }

        var temp =  parseInt(the_id_arr[2]);			// unit?
        var temp1 =  the_id_arr[3].trim();				// unit timestamp?
        if ((temp != unit_id) || (temp1 != updated)) {	//	10/23/12
            unit_id = temp;
            updated =  temp1;							// timestamp this unit
            $('unit_id').innerHTML = unit_id;			// unit id
            unit_signal();								// light the unit button
            }

        $("div_assign_id").innerHTML = the_id_arr[4].trim();			// 2/19/12
//		alert("201 " + the_id_arr[4].trim());
        if (the_id_arr[4].trim() != dispatch) {		// 1/21/11
            dispatch = the_id_arr[4].trim();
            unit_signal();								// sit scr to blue
            }

        if (the_id_arr[5].trim() != $("div_action_id").innerHTML) {		// 2/25/12
            misc_signal();													// situation button blue if ...
            $("div_action_id").innerHTML = the_id_arr[5].trim();
            }

        if (the_id_arr[6].trim() != $("div_patient_id").innerHTML) {		// 2/25/12
            misc_signal();													// situation button blue if ...
            $("div_patient_id").innerHTML = the_id_arr[6].trim();
            }

        if (the_id_arr[7] != $("div_requests_id").innerHTML) {	//		9/10/13
            if (the_id_arr[7] != "0") {
                $("div_requests_id").innerHTML = the_id_arr[7];
                $("reqs").style.display = "inline-block";
                $("reqs").innerHTML = "Open Requests = " + the_id_arr[7];
                } else if (the_id_arr[8] != "0") {
                $("div_requests_id").innerHTML = the_id_arr[7];
                $("reqs").style.display = "inline-block";
                $("reqs").innerHTML = "Requests";
                } else {
                $("div_requests_id").innerHTML = the_id_arr[7];
                $("reqs").style.display = "none";
                $("reqs").innerHTML = "";
                }
            }

        var temp2 =  parseInt(the_id_arr[9]);			// unit?	9/10/13
        var temp3 =  parseInt(the_id_arr[10]);			// status?	9/10/13
        var temp4 =  the_id_arr[11].trim();				// unit timestamp?	9/10/13
        if ((temp2 != the_unit) || (temp3 != the_status) || (temp4 != the_time)) {	//		9/10/13
            the_unit = temp2;	//		9/10/13
            the_status = temp3;	//		9/10/13
            the_time =  temp4;	// timestamp this unit, 	9/10/13
            un_stat_chg(the_unit, the_status);	//		9/10/13
            }
        }			// end function get_latest_id_cb()
/**
 *
 * @param {type} req
 * @returns {unresolved}
 */
	function get_latest_messages_cb(req) {					// get_latest_messages callback(), 10/23/12, 1/30/14
		var the_msg_arr=JSON.decode(req.responseText);
		var the_number = parseInt(the_msg_arr[0][0]);
		unread_messages = the_number;
		if(unread_messages != 0) {
			$("msg").innerHTML = "Msgs (" + unread_messages + ")";
			msg_signal_o();
			} else {
			$("msg").innerHTML = "Msgs";
			msg_signal_o_off();
			}
		new_msgs_get();
		}			// end function get_latest_messages_cb()

    function toHex(x) {
        hex="0123456789ABCDEF";almostAscii=' !"#$%&'+"'"+'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ['+'\\'+']^_`abcdefghijklmnopqrstuvwxyz{|}';r="";
        for (i=0;i<x.length;i++) {
            let=x.charAt(i);pos=almostAscii.indexOf(let)+32;
            h16=Math.floor(pos/16);h1=pos%16;r+=hex.charAt(h16)+hex.charAt(h1);
            };

        return r;
        };

    function mu_get() {								// set cycle
        if (mu_interval!=null) {return;}			// ????
        mu_interval = window.setInterval('do_loop()', <?php print $poll_cycle_time;?>);		// 4/7/10
        }			// end function mu get()

    function new_msgs_get() {								// set cycle, 10/23/12
        if (nm_interval!=null) {return;}			// ????
		nm_interval = window.setInterval('do_latest_msgs_loop()', 30000);
        }			// end function mu get()

    function messages_get() {								// set cycle, 10/23/12
        if (msgs_interval!=null) {return;}			// ????
		msgs_interval = window.setInterval('do_msgs_loop()', 30000);
        }			// end function mu get()

    function mu_init() {								// get initial values from server -  4/7/10
        var randomnumber=Math.floor(Math.random()*99999999);
        if (is_initialized) { return; }
        is_initialized = true;

        sendRequest ('get_latest_id.php?version=' + randomnumber,init_cb, "");
            function init_cb(req) {


                var the_id_arr=JSON.decode(req.responseText);				// 1/7/11

                if (the_id_arr.length != 13) {						// 2/25/12, 10/23/12

                    alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");
                    }
                else {
                    chat_id =  parseInt(the_id_arr[0]);
                    ticket_id = parseInt(the_id_arr[1]);
                    unit_id =  parseInt(the_id_arr[2]);
                    updated =  the_id_arr[3].trim();					// timestamp this unit
                    dispatch = the_id_arr[4].trim();					// 1/21/11
                    $("div_ticket_id").innerHTML = the_id_arr[1].trim();	// 2/19/12
                    $("div_assign_id").innerHTML = the_id_arr[4].trim();	// 2/19/12
                    $("div_action_id").innerHTML = the_id_arr[5].trim();	// 2/25/12
                    $("div_patient_id").innerHTML = the_id_arr[6].trim();	// 2/25/12
                    if (the_id_arr[7] != "0") {	//		9/10/13
                        $("div_requests_id").innerHTML = the_id_arr[7];
                        $("reqs").style.display = "inline-block";
                        $("reqs").innerHTML = "Open Requests = " + the_id_arr[7];
                        } else if (the_id_arr[8] != "0") {
                        $("div_requests_id").innerHTML = the_id_arr[7];
                        $("reqs").style.display = "inline-block";
                        $("reqs").innerHTML = "Requests";
                        } else {
                        $("div_requests_id").innerHTML = the_id_arr[7];
                        $("reqs").style.display = "none";
                        $("reqs").innerHTML = "";
                        }
                    }
                mu_get();				// start loop
				do_positions();	//	1/3/14
				do_conditions(); //	1/3/14
				var is_messaging = parseInt("<?php print get_variable('use_messaging');?>");
				if((is_messaging == 1) || (is_messaging == 2) || (is_messaging == 3)) {
                get_msgs();
					nm_init();
					}
                do_filelist();	//	9/10/13
                }				// end function init_cb()
        }				// end function mu_init()

    function nm_init() {								// get initial values from server -  10/23/12
        var randomnumber=Math.floor(Math.random()*99999999);
        if (nmis_initialized) { return; }
        nmis_initialized = true;
		sendRequest ('./ajax/list_message_totals.php?version=' + randomnumber,msg_cb, "");
            function msg_cb(req) {
                var the_msg_arr=JSON.decode(req.responseText);
				var the_number = parseInt(the_msg_arr[0][0]);
				unread_messages = the_number;
				if(unread_messages != 0) {
					$("msg").innerHTML = "Msgs (" + unread_messages + ")";
					msg_signal_o();
                    } else {
					$("msg").innerHTML = "Msgs";
					msg_signal_o_off();
                    }
                new_msgs_get();
                }			// end function msg_cb()
        }				// end function nm_init()


    function get_msgs() {	//	10/23/12
        var randomnumber=Math.floor(Math.random()*99999999);
          // call the server to execute the server side operation
        if (window.XMLHttpRequest) {
            xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", "./ajax/get_messages.php?version=" + randomnumber, true);
            xmlHttp.onreadystatechange = handleRequestStateChange;
            xmlHttp.send(null);
            }
        }

    function handleRequestStateChange() {	//	10/23/12
        var the_resp;
        var the_val;
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) {
                var response = JSON.decode(xmlHttp.responseText);
                for (var key in response[0]) {
                    the_resp = key;
                    the_val = response[0][key];
                    un_stat_chg(the_resp, the_val);
                    }
                if (response[1]) {
                    var the_mess = response[1][0];
                    var the_stored = response[1][1];
                    if (the_stored != 0) {
                        show_msg("<?php print gettext('There are');?> " + the_stored + " <?php print gettext('new messages');?>");
                        msg_signal_r();								// light the msg button
						} else {
						msg_signal_r_off();								// unlight the msg button
                        }
                    }
                }
            }
        messages_get();
        }

// for responder positions
	function do_positions() {	//	12/27/13
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			respxmlHttp = new XMLHttpRequest();
			respxmlHttp.open("GET", "./ajax/responder_data.php?version=" + randomnumber, true);
			respxmlHttp.onreadystatechange = readPositions;
			respxmlHttp.send(null);
			}
		}

	function readPositions() {	//	12/27/13
		if (respxmlHttp.readyState == 4) {
			if (respxmlHttp.status == 200) {
				var resp_positions = JSON.decode(respxmlHttp.responseText);
				for(var key in resp_positions) {
					var the_resp_id = resp_positions[key][0];
					var the_resp_lat = parseFloat(resp_positions[key][4]);
					var the_resp_lng = parseFloat(resp_positions[key][5]);
					if(typeof parent.frames["main"].set_marker_position == 'function') {
						parent.frames["main"].set_marker_position(the_resp_id, the_resp_lat, the_resp_lng);
						}
					}
				}
			}
		positions_get();
		}

	function positions_get() {			// set cycle, 12/27/13
		if (pos_interval!=null) {return;}			// ????
		pos_interval = window.setInterval('do_positions_loop()', 30000);
		}			// end function mu get()

	function do_positions_loop() {	//	12/27/13
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			respxmlHttp = new XMLHttpRequest();
			respxmlHttp.open("GET", "./ajax/responder_data.php?version=" + randomnumber, true);
			respxmlHttp.onreadystatechange = readPositions2;
			respxmlHttp.send(null);
			}
		}

	function readPositions2() {	//	12/27/13
		if (respxmlHttp.readyState == 4) {
			if (respxmlHttp.status == 200) {
				var resp_positions = JSON.decode(respxmlHttp.responseText);
				for(var key in resp_positions) {
					var the_resp_id = resp_positions[key][0];
					var the_resp_lat = parseFloat(resp_positions[key][4]);
					var the_resp_lng = parseFloat(resp_positions[key][5]);
					if(typeof parent.frames["main"].set_marker_position == 'function') {
						parent.frames["main"].set_marker_position(the_resp_id, the_resp_lat, the_resp_lng);
						}
					}
				}
			}
		}

// for road conditions
	function do_conditions() {	//	1/3/14
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			condxmlHttp = new XMLHttpRequest();
			condxmlHttp.open("GET", "./ajax/alertlist.php?version=" + randomnumber, true);
			condxmlHttp.onreadystatechange = readConditions;
			condxmlHttp.send(null);
			}
		}

	function readConditions() {	//	1/3/14
		if (condxmlHttp.readyState == 4) {
			if (condxmlHttp.status == 200) {
				var conditions = JSON.decode(condxmlHttp.responseText);
				for(var key in conditions) {
					if(conditions[key][0] != 0) {
					var the_condID = conditions[key][0];
					var the_condTitle = conditions[key][1];
					var the_condTypeTitle = conditions[key][2];
					var the_condAddress = conditions[key][3];
					var the_condDescription = conditions[key][4];
					var the_iconurl = "./rm/roadinfo_icons/" + conditions[key][5];
					var the_condDate = conditions[key][6];
					var the_condLat = conditions[key][7];
					var the_condLng = conditions[key][8];
					var info = "<TABLE class='infowin'>";
					info += "<TH COLSPAN=2 class='header'>" + the_condTitle + "</TH>";
					info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Type');?></B></TD><TD class='td_data'>" + the_condTypeTitle + "</TD></TR>";
					info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Address');?></B></TD><TD class='td_data'>" + the_condAddress + "</TD></TR>";
					info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Updated');?></B></TD><TD class='td_data'>" + the_condDate + "</TD></TR></TABLE>";
					if(typeof parent.frames["main"].createConditionMarker == 'function') {
						parent.frames["main"].createConditionMarker(the_condLat, the_condLng, the_condID, info, "roadinfo", the_iconurl)
						}
					}
				}
			}
			}
		conditions_get();
		}

	function conditions_get() {			// set cycle, 1/3/14
		if (pos_interval!=null) {return;}			// ????
		pos_interval = window.setInterval('do_conditions_loop()', 30000);
		}			// end function mu get()

	function do_conditions_loop() {	//	1/3/14
		var randomnumber=Math.floor(Math.random()*99999999);
	  	// call the server to execute the server side operation
		if (window.XMLHttpRequest) {
			condxmlHttp = new XMLHttpRequest();
			condxmlHttp.open("GET", "./ajax/responder_data.php?version=" + randomnumber, true);
			condxmlHttp.onreadystatechange = readConditions2;
			condxmlHttp.send(null);
			}
		}

	function readConditions2() {	//	1/3/14
		if (condxmlHttp.readyState == 4) {
			if (condxmlHttp.status == 200) {
				var conditions = JSON.decode(condxmlHttp.responseText);
				for(var key in conditions) {
					if(conditions[key][0] != 0) {
					var the_condID = conditions[key][0];
					var the_condTitle = conditions[key][1];
					var the_condTypeTitle = conditions[key][2];
					var the_condAddress = conditions[key][3];
					var the_condDescription = conditions[key][4];
					var the_iconurl = "./rm/roadinfo_icons/" + conditions[key][5];
					var the_condDate = conditions[key][6];
					var the_condLat = conditions[key][7];
					var the_condLng = conditions[key][8];
					var info = "<TABLE class='infowin'>";
					info += "<TH class='header'>" + the_condTitle + "</TH>";
					info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Alert Type');?></B></TD><TD class='td_data'>" + the_condTypeTitle + "</TD></TR>";
					info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Address');?></B></TD><TD class='td_data'>" + the_condAddress + "</TD></TR>";
					info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Updated');?></B></TD><TD class='td_data'>" + the_condDate + "</TD></TR></TABLE>";
					if(typeof parent.frames["main"].createConditionMarker == 'function') {
						parent.frames["main"].createConditionMarker(the_condLat, the_condLng, the_condID, info, "roadinfo", the_iconurl)
						}
					}
				}
			}
		}
		}
/**
 *
 * @returns {undefined}
 */
    function do_set_sess_exp() {			// set session expiration  - 1/11/10
        var randomnumber=Math.floor(Math.random()*99999999);
        sendRequest ('set_cook_exp.php?version=' + randomnumber,set_cook_exp_handleResult, "");
        }

    function set_cook_exp_handleResult() {
        }

    function sendRequest(url,callback,postData) {
        var req = createXMLHTTPObject();
        if (!req) return;
        var method = (postData) ? "POST" : "GET";
        req.open(method,url,true);
//		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
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
/**
 *
 * @type Array
 */
    var XMLHttpFactories = [
        function () {return new XMLHttpRequest();	},
        function () {return new ActiveXObject("Msxml2.XMLHTTP");	},
        function () {return new ActiveXObject("Msxml3.XMLHTTP");	},
        function () {return new ActiveXObject("Microsoft.XMLHTTP");	}
        ];
/**
 *
 * @returns {Boolean}
 */
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

            return AJAX.responseText;
            }
        else {
            alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");

            return false;
            }
        }		// end function sync Ajax()

/**
 *
 * @returns {undefined}
 */
    function do_audible() {	// 6/12/10
        try {document.getElementsByTagName('audio')[0].play();}
        catch (e) 	{}		// ignore
        }				// end function do_audible()
/**
 *
 * @returns {Boolean}
 */
    function get_line_count() {							// 4/5/10
        var url = "do_get_line_ct.php";
        var payload = syncAjax(url);						// does the work

        return payload;
        }		// end function get line_count()
/**
 *
 * @returns {undefined}
 */
    function chat_signal() {									// light the button
        CngClass("chat", "signal_r");
        lit["chat"] = true;
        do_audible();				// 6/12/10
        }
/**
 *
 * @returns {unresolved}
 */
    function unit_signal() {										// light the units button and - if not already lit red - the situation button
        if (lit["main"]) {return; }									// already lit - possibly red
        CngClass("main", "signal_b");
        lit["main"] = true;
        }
/**
 *
 * @returns {unresolved}
 */
    function msg_signal() {										// light the msg button, 10/23/12
        if (lit["msg"]) {return; }									// already lit - possibly red
        CngClass("msg", "signal_b");
        lit["msg"] = true;
        }
/**
 *
 * @returns {unresolved}
 */
	function msg_signal_r() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_r["msg"]) {return; }									// already lit - possibly red
		CngClass("msg", "signal_r");
		lit_r["msg"] = true;
		do_audible();				// 1/20/14
		}

	function msg_signal_r_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_r["msg"]) {return; }									// not lit ignore
		if(unread_messages != 0) {
			CngClass("msg", "signal_o");
			lit_o["msg"] = true;
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_r["msg"] = false;
		lit_o["msg"] = true;
		}

	function msg_signal_o() {										// light the msg button, 10/23/12, 1/30/14
		if (lit_o["msg"]) {return; }									// already lit - possibly red
		if (lit_r["msg"]) {return; }
		CngClass("msg", "signal_o");
		lit_o["msg"] = true;
		}

	function msg_signal_o_off() {										// light the msg button, 10/23/12, 1/30/14
		if (!lit_o["msg"]) {return; }									// not lit ignore
		if (lit_r["msg"]) {
			CngClass("msg", "signal_r");
			} else {
			if(lit["msg"]) {
				CngClass("msg", "signal_b");
				lit_o["msg"] = false;
				} else {
				CngClass("msg", "plain");
				lit_o["msg"] = false;
				}
			}
		lit_o["msg"] = false;
		}
/**
 *
 * @returns {undefined}
 */
    function tick_signal() {										// red light the button
        CngClass("main", "signal_r");
        lit["main"] = true;
        do_audible();				// 6/12/10
        }
                                                                    // 2/25/12
/**
 *
 * @returns {unresolved}
 */
    function misc_signal() {										// blue light to situation button if not already lit
        if (lit["main"]) {return; }									// already lit - possibly red
        CngClass("main", "signal_b");
        lit["main"] = true;
        }
/**
 *
 * @param {type} obj
 * @param {type} the_class
 * @returns {Boolean}
 */
    function CngClass(obj, the_class) {
        $(obj).className=the_class;

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_hover(the_id) {
        if (the_id == current_butt_id) {return true;}				// 8/21/10
        if (lit[the_id]) {return true;}
        CngClass(the_id, 'hover');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_lo_hover(the_id) {
        CngClass(the_id, 'lo_hover');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_plain(the_id) {				// 8/21/10
        if (the_id == current_butt_id) {return true;}
        if (lit[the_id]) {return true;}
        CngClass(the_id, 'plain');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_lo_plain(the_id) {
        CngClass(the_id, 'lo_plain');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_signal(the_id) {		// lights the light
        lit[the_id] = true;
        CngClass(the_id, 'signal');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_off_signal(the_id) {
        CngClass(the_id, 'plain');

        return true;
        }
/**
 *
 * @param {type} btn_id
 * @returns {undefined}
 */
    function light_butt(btn_id) {				// 8/24/10 -
        CngClass(btn_id, 'signal_w');			// highlight this button
        if (!(current_butt_id == btn_id)) {
            do_off_signal (current_butt_id);	// clear any prior one if different
            }
        current_butt_id = btn_id;				//
        }				// end function light_butt()
/**
 *
 * @param {type} where
 * @param {type} the_id
 * @returns {undefined}
 */
    function go_there(where, the_id) {		//
        CngClass(the_id, 'signal_w');			// highlight this button
        if (!(current_butt_id == the_id)) {
            do_off_signal (current_butt_id);	// clear any prior one if different
            }
        current_butt_id = the_id;				// 8/21/10
        lit[the_id] = false;
        document.go.action = where;
        document.go.submit();
        }				// end function go there ()
/**
 *
 * @param {type} msg
 * @returns {undefined}
 */
    function show_msg(msg) {
        $('msg_span').innerHTML = msg;
        setTimeout("$('msg_span').innerHTML =''", 3000);	// show for 3 seconds
        }
/**
 *
 * @returns {Boolean}
 */
    function logged_in() {								// returns boolean
        var temp = $("whom").innerHTML==NOT_STR;

        return !temp;
        }
/**
 *
 * @returns {undefined}
 */
    function do_logout() {						// 10/27/08
        $("user_id").innerHTML  = 0;
        $('time_of_day').innerHTML="";

        clearInterval(mu_interval);
        mu_interval = null;
        clearInterval(nm_interval);	//	10/23/12
        nm_interval = null;	//	10/23/12
        clearInterval(msgs_interval);	//	10/23/12
        msgs_interval = null;	//	10/23/12
        clearInterval(emsgs_interval);	//	10/23/12
        emsgs_interval = null;	//	10/23/12
        clearInterval(file_interval);	//	10/25/13
        file_interval = null;	//	10/25/13
        $('whom').innerHTML=NOT_STR;
        is_initialized = false;
        nmis_initialized = false;	//	10/23/12

        if (ticker_active == 1) {
            clearInterval(ticker_interval);
            var ticker_interval = null;
            ticker_is_initialized = false;
        }

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

        $('gout').style.display = 'none';		// hide the logout button
        document.gout_form.submit();			// send logout
        }
/**
 *
 * @returns {undefined}
 */
    function hide_butts() {						// 10/27/08, 3/15/11
        setTimeout(" $('buttons').style.display = 'none';" , 500);
        $("daynight").style.display = "none";				// 5/2/11
        $("main_body").style.backgroundColor  = "<?php print get_css('page_background', 'Day');?>";
        $("main_body").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("tagline").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("user_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("unit_id").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("script").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("time_of_day").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("whom").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("level").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("logged_in_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("perms_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("modules_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        $("time_txt").style.color  = "<?php print get_css('titlebar_text', 'Day');?>";
        try {
            $('manual').style.display = 'none';		// hide the manual link	- possibly absent
            }
        catch(e) {
            }
        }
/**
 *
 * @returns {undefined}
 */
    function show_butts() {						// 10/27/08
        $("buttons").style.display = "inline";
        $("daynight").style.display = "inline";
        $("has_form_row").style.display = "none";		// 5/26/2013
        $("has_message_row").style.display = "none";

        }

    function do_filelist() {	//	9/10/13, 10/25/13
        randomnumber=Math.floor(Math.random()*99999999);
        var url ="./ajax/gen_file_list.php?version=" + randomnumber;
        sendRequest (url, genfile_cb, "");
        function genfile_cb(req) {
            var the_files=JSON.decode(req.responseText);
            if (the_files[0] != "") {
                $('files').style.display = "inline-block";
                $('file_list').innerHTML = the_files[0];
                $('file_list2').innerHTML = the_files[1];
                } else {
                $('files').style.display = "none";
                }
            }
        do_filelist2();
        }

    function do_filelist2() {	//	10/25/13
        if (file_interval!=null) {return;}
        file_interval = window.setInterval('file_loop()', 60000);
        }

    function file_loop() {	//	10/25/13
        randomnumber=Math.floor(Math.random()*99999999);
        var url ="./ajax/gen_file_list.php?version=" + randomnumber;
        sendRequest (url, genfile_cb, "");
        function genfile_cb(req) {
            var the_files=JSON.decode(req.responseText);
            if (the_files[0] != "") {
                $('files').style.display = "inline-block";
                $('file_list').innerHTML = the_files[0];
                $('file_list2').innerHTML = the_files[1];
                } else {
                $('files').style.display = "none";
                }
            }
        }

//	============== module window openers ===========================================

    function open_FWindow(theFilename) {										// 9/10/13
        var url = theFilename;
        var ofWindow = window.open(url, 'ViewFileWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100');
        setTimeout(function () { ofWindow.focus(); }, 1);
        }

    var newwindow_sl = null;
    var starting;
/**
 *
 * @returns {unresolved}
 */
    function do_sta_log() {				// 1/19/09
        light_butt('log') ;
        if ((newwindow_sl) && (!(newwindow_sl.closed))) {newwindow_sl.focus(); return;}		// 7/28/10
        if (logged_in()) {
            if (starting) {return;}						// 6/6/08
            starting=true;
            do_set_sess_exp();		// session expiration update
            newwindow_sl=window.open("log.php", "sta_log",  "titlebar, location=0, resizable=1, scrollbars, height=240,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
            if (isNull(newwindow_sl)) {
                alert ("<?php print gettext('Station log operation requires popups to be enabled. Please adjust your browser options.');?>");

                return;
                }
            newwindow_sl.focus();
            starting = false;
            }
        }		// end function do sta_log()

    var newwindow_msg = null;
/**
 *
 * @returns {unresolved}
 */
    function do_mess() {				// 10/23/12
        light_butt('msg') ;
        if ((newwindow_msg) && (!(newwindow_msg.closed))) {newwindow_msg.focus(); return;}		// 10/23/12
        if (logged_in()) {
            if (starting) {return;}
            starting=true;
            do_set_sess_exp();		// session expiration update
            newwindow_msg=window.open("messages.php", "messages",  "titlebar, location=0, resizable=1, scrollbars=no, height=600,width=950,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300");
            if (isNull(newwindow_msg)) {
                alert ("<?php print gettext('Viewing messages requires popups to be enabled. Please adjust your browser options.');?>");

                return;
                }
            newwindow_msg.focus();
            starting = false;
            }
        }		// end function do sta_log()

    var newwindow_cb = null;
/**
 *
 * @returns {unresolved}
 */
    function do_callBoard() {
        light_butt('call');
        if ((newwindow_cb) && (!(newwindow_cb.closed))) {newwindow_cb.focus(); return;}		// 7/28/10
        if (logged_in()) {
            if (starting) {return;}						// 6/6/08
            starting=true;
            do_set_sess_exp();		// session expiration update
            var the_height = 60 + (16 * get_line_count());
            var the_width = (2.0 * Math.floor((Math.floor(.90 * screen.width) / 2.0)));

            newwindow_cb=window.open("board.php", "callBoard",  "titlebar, location=0, resizable=1, scrollbars, height="+the_height+", width="+the_width+", status=0,toolbar=0,menubar=0,location=0, left=20,top=300,screenX=20,screenY=300");

            if (isNull(newwindow_cb)) {
                alert ("<?php print gettext('Call Board operation requires popups to be enabled. Please adjust your browser options.');?>");

                return;
                }
            newwindow_cb.focus();
            starting = false;
            }
        }		// end function do callBoard()

    var newwindow_c = null;
/**
 *
 * @returns {undefined}
 */
    function chat_win_close() {				// called from chat.pgp
        newwindow_c = null;
        }
/**
 *
 * @returns {unresolved}
 */
    function do_chat() {
        light_butt('chat') ;
        if ((newwindow_c) && (!(newwindow_c.closed))) {newwindow_c.focus(); return;}		// 7/28/10

        if (logged_in()) {
            if (starting) {return;}					// 6/6/08
            starting=true;
            do_set_sess_exp();		// session expiration update
            try {
                newwindow_c.focus();
                }
            catch(e) {
                }

            newwindow_c=window.open("chat.php", "chatBoard",  "titlebar, resizable=1, scrollbars, height=480,width=800,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
            if (isNull(newwindow_c)) {
                alert ("<?php print gettext('Chat operation requires popups to be enabled. Please adjust your browser options - or else turn off the Chat option setting.');?>");

                return;
                }
            newwindow_c.focus();
            starting = false;
            CngClass("chat", "plain");
            }
        }

    var newwindow_fs = null;
/**
 *
 * @returns {unresolved}
 */
    function do_full_scr() {                            //9/7/09
        light_butt('full');
        if ((newwindow_fs) && (!(newwindow_fs.closed))) {newwindow_fs.focus(); return;}		// 7/28/10

        if (logged_in()) {
            if (starting) {return;}                        // 4/15/10 fullscreen=no
            do_set_sess_exp();		// session expiration update

            if (window.focus() && newwindow_fs) {newwindow_fs.focus();}    // if already exists
            starting=true;

            params  = 'width='+screen.width;
            params += ', height='+screen.height;
            params += ', top=0, left=0', scrollbars = 1;
            params += ', fullscreen=no';
            newwindow_fs=window.open("full_scr.php", "full_scr", params);
            if (isNull(newwindow_fs)) {
                alert ("<?php print gettext('This operation requires popups to be enabled. Please adjust your browser options.');?>");

                return;
                }
            newwindow_fs.focus();
            starting = false;
            }
        }        // end function do full_scr()
/**
 *
 * @param {type} filename
 * @returns {unresolved}
 */
    function do_emd_card(filename) {
        light_butt('card');
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
            alert ("<?php print gettext('SOP Doc\'s operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        starting = false;
        }

<?php
$start_up_str = 	(array_key_exists('user', $_SESSION))? "": " mu_init();";
$the_userid = 		(array_key_exists('user_id', $_SESSION))? $_SESSION['user_id'] : "na"; 	//	7/16/13
$the_whom = 		(array_key_exists('user', $_SESSION))? $_SESSION['user']: NOT_STR;
$the_level = 		(array_key_exists('level', $_SESSION))? get_level_text($_SESSION['level']):"na";

$day_night = (array_key_exists('day_night', $_SESSION)) ? $_SESSION['day_night'] : 'Day';
print "\n\t var the_whom = '{$the_whom}'\n";
print "\t var the_level ='{$the_level}'\n";

/**
 * get_daynight
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_daynight() {
    $day_night = ((array_key_exists('day_night', $_SESSION)) && ($_SESSION['day_night'])) ? $_SESSION['day_night'] : 'Day';

    return $day_night;
    }
?>
/**
 *
 * @param {type} which
 * @returns {undefined}
 */
    function do_day_night(which) {
        for (i=0;i<document.day_night_form.elements.length;i++) {
            if ((document.day_night_form.elements[i].type=='radio') && (document.day_night_form.elements[i].name=='frm_daynight')) {
                if (document.day_night_form.elements[i].value == which) {
                    document.day_night_form.elements[i].checked = true;
                    document.day_night_form.elements[i].disabled = true;
                    }
                else {
                    document.day_night_form.elements[i].checked = false;
                    document.day_night_form.elements[i].disabled = false;
                    }
                }				// end if (type=='radio')
            }
        }		// end function do_day_night()
/**
 *
 * @returns {undefined}
 */
    function top_init() {					// initialize display
        CngClass('main', 'signal_w');		// light up 'sit' button - 8/21/10
        $("whom").innerHTML  =	the_whom;
        $("level").innerHTML =	the_level;
        do_time();
<?php												// 5/4/11
        if (empty($_SESSION)) {						// pending login
            $day_checked = $night_checked = "";
            $day_disabled = $night_disabled= "DISABLED";
            }
        else {				// logged-in
            if ($start_up_str == 'Day') {	//	7/16/13	Revised to fix error on initial startup
                $day_checked = "CHECKED";			// allow only 'night'
                $day_disabled = "DISABLED";
                $night_checked = "";
                $night_disabled = "";
                }
            else {
                $day_checked = "";					//  allow only 'day'
                $day_disabled = "";
                $night_checked = "CHECKED";
                $night_disabled = "DISABLED";
                }
?>
        var current_user_id = "<?php print $the_userid;?>";
            show_butts();														// navigation buttons
            $("gout").style.display  = "inline";								// logout button
            $("user_id").innerHTML  = "<?php print $the_userid;?>";		//	7/16/13
            $("whom").innerHTML  = "<?php print $the_whom;?>";			// user name, 7/1613
            $("level").innerHTML = "<?php print $the_level;?>";		//	7/16/13
            mu_init();			// start polling
<?php
            }				// end if/else (empty($_SESSION))
?>
        }		// end function top_init()
/**
 *
 * @param {type} instr
 * @returns {undefined}
 */
    function do_log(instr) {
        $('log_div').innerHTML += instr + "<br />";
        }
/**
 *
 * @returns {undefined}
 */
    function get_new_colors() {										// 5/4/11 - a simple refresh
        window.location.href = '<?php print basename(__FILE__);?>';
        }
/**
 *
 * @param {type} which
 * @returns {undefined}
 */
    function set_day_night(which) {			// 5/2/11
        sendRequest ('./ajax/do_day_night_swap.php', day_night_callback, "");
            function day_night_callback(req) {
                var the_ret_val = req.responseText;
                try {
                    parent.frames["main"].get_new_colors();			// reloads main frame
                    }
                catch (e) {
                    }
                window.clearInterval(mu_interval);
                window.clearInterval(nm_interval);	//	10/23/12
                window.clearInterval(msgs_interval);	//	10/23/12
                window.clearInterval(emsgs_interval);	//	10/23/12
                window.clearInterval(file_interval);	//	10/23/12
                get_new_colors();								// reloads top
                }									// end function day_night_callback()
        }
/**
 *
 * @param {type} filename
 * @returns {undefined}
 */
    function do_manual(filename) {							// launches Tickets manual page -  5/27/11
        try {
            newwindow_em=window.open(filename, "Manual",  "titlebar, resizable=1, scrollbars, height=640,width=800,status=0,toolbar=0,menubar=0,location=0, left=20,top=20,screenX=20,screenY=20");
            }
        catch (e) {
            }
        try {
            newwindow_em.focus();;
            }
        catch (e) {
            }
        }		// end do_manual()
/**
 *
 * @returns {undefined}
 */
        function do_files() {	//	9/10/13
            hide_butts();								// hide buttons
            $("file_buts_row").style.display = "inline-block";
            }
/**
 *
 * @returns {undefined}
 */
        function hide_files() {	//	9/10/13
            $("file_buts_row").style.display = "none";
            show_butts();
            }
/**
 *
 * @returns {undefined}
 */
        function can_has() {							// cancel HAS function - return to normal display
            $("has_form_row").style.display = "none";
            show_butts();								// show buttons
            }
/**
 *
 * @returns {undefined}
 */
        function end_message_show() {
            setTimeout(function () {
                $("has_message_row").style.display = $("has_form_row").style.display = "none";
                $("has_form_row").style.display = "none";
                show_butts();								// show buttons
                }, 1000);			// end setTimeout()
            }					// end function

<?php				// 7/2/2013
        if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		//
?>
/**
 *
 * @returns {undefined}
 */
        function do_broadcast() {
            $("has_form_row").style.display = "inline-block";
            $("has_message_row").style.display = "none";
            document.has_form.has_text.focus();
            }
/**
 *
 * @param {type} inStr
 * @returns {unresolved}
 */
        function has_check(inStr) {
            if (inStr.trim().length == 0) { alert("<?php print gettext('Value required - try again.');?>"); return;}
            else {
                var msg =  $("whom").innerHTML + " sends: " + inStr.trim(); // identify sender

                broadcast(msg); 				// send it
                setTimeout(function () {
                    CngClass("has_text", "heading");
                    document.has_form.has_text.value = "              Sent!";		// note spaces
                    setTimeout(function () {
                        document.has_form.has_text.value = "";
                        $("has_form_row").style.display = "none";		// hide the form row
                        CngClass("has_text", "");
                        show_butts();								// back to normal
                        }, 3000);
                    }, 1000);
                }		// end else{}
            }		// end function has_check()
/**
 *
 * @returns {undefined}
 */
        function hide_has_message_row() {
            $("msg_span").style.display = "none";
            show_butts();								// show buttons
            }
/**
 *
 * @param {type} in_message
 * @returns {undefined}
 */
        function show_has_message(in_message) {
            hide_butts();											// make room
            $("has_message_text").innerHTML = in_message;			// the message text
            CngClass("has_message_text", "heading");
            $("has_message_row").style.display = "inline-block";	// include button
            }

<?php
        }			// end if (broadcast && internet )
?>

    </SCRIPT>
<?php							// 7/2/2013
    if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
        require_once './incs/socket2me.inc.php';		// 5/24/2013
        }
?>

</HEAD>
<BODY ID="main_body" onLoad = "top_init();">	<!-- 3/15/11, 10/23/12 -->
<DIV ID = "div_ticket_id" STYLE="display:none;"></DIV>
<DIV ID = "div_assign_id" STYLE="display:none;"></DIV>
<DIV ID = "div_action_id" STYLE="display:none;"></DIV> <!-- 2/25/12 -->
<DIV ID = "div_patient_id" STYLE="display:none;"></DIV>
<DIV ID = "div_requests_id" STYLE="display:none;"></DIV>	<!-- 10/23/12 -->

    <TABLE ALIGN='left'>
        <TR VALIGN='top'>
            <TD ROWSPAN=4><IMG SRC="<?php print get_variable('logo');?>" BORDER=0 /></TD>
            <TD>
<?php

    $temp = get_variable('_version');				// 8/8/10
    $version_ary = explode ( "-", $temp, 2);
    if (get_variable('title_string')=="") {
        $title_string = "<FONT SIZE='3'>ickets " . trim($version_ary[0]) . " " . gettext('on') . " <B>" . get_variable('host') . "</B></FONT>";
        } else {
        $title_string = "<FONT SIZE='3'><B>" .get_variable('title_string') . "</B></FONT>";
        }
?>
                <SPAN ID="tagline" CLASS="titlebar_text"><?php print $title_string; ?></SPAN>	<!-- 3/15/11 -->
                <SPAN ID="logged_in_txt" STYLE = 'margin-left: 8px;' CLASS="titlebar_text"><?php print get_text("Logged in"); ?>:</SPAN>	<!-- 3/15/11 -->
                <SPAN ID="whom" CLASS="titlebar_text"><?php print NOT_STR ; ?></SPAN>
                <SPAN ID="perms_txt" CLASS="titlebar_text">:<SPAN ID="level" CLASS="titlebar_text"><?php print gettext('N/A');?></SPAN>&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->

<?php
    $temp = get_variable('auto_poll');

    $dir = "./emd_cards";

    if (file_exists ($dir)) {
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if ((strlen($filename)>2) && (get_ext($filename)=="pdf")) {
                $card_file = $filename;						// at least one pdf, use first encountered
                break;
                }
            }

        $card_addr=(!empty($card_file))? $dir . "/" . $filename  : "";
        }

?>
                <SPAN ID='user_id' STYLE="display:none" CLASS="titlebar_text">0</SPAN><!-- default value - 5/29/10, 3/15/11 -->
                <SPAN ID='unit_id' STYLE="display:none" CLASS="titlebar_text"></SPAN><!-- unit that has just moved - 4/7/10, 3/15/11 -->
                <SPAN ID='modules_txt' CLASS="titlebar_text"><?php print get_text("Module"); ?>: </SPAN><SPAN ID="script" CLASS="titlebar_text"><?php print gettext('login');?></FONT></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
                <SPAN ID='daynight' CLASS="titlebar_text"  STYLE = 'display:none'>
                    <FORM NAME = 'day_night_form' STYLE = 'display: inline-block'>
                                            <!-- set in  above -->
                    <INPUT TYPE="radio" NAME="frm_daynight" VALUE="Day" <?php print "{$day_disabled} {$day_checked}" ;?> 		onclick = ' set_day_night(this.value);'/><?php print gettext('Day');?>&nbsp;&nbsp;&nbsp;&nbsp;
                    <INPUT TYPE="radio" NAME="frm_daynight" value="Night" <?php print "{$night_disabled}  {$night_checked}" ;?> onclick = 'set_day_night(this.value);'/><?php print gettext('Night');?>&nbsp;&nbsp;&nbsp;&nbsp;

                    </FORM>
                </SPAN>
                <SPAN ID='time_txt' CLASS="titlebar_text"><?php print get_text("Time"); ?>: </SPAN><b><SPAN ID="time_of_day" CLASS="titlebar_text"></SPAN></b></FONT>&nbsp;&nbsp;&nbsp;&nbsp;	<!-- 3/15/11 -->
<?php				// 5/26/11
    $dir = "./manual";

    if (file_exists ($dir)) {
        $dh  = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if ((strlen($filename)>2) && (get_ext($filename)=="pdf")) {
                $manual_file = $filename;						// at least one pdf, use first encountered
                break;
                }
            }

        $manual_addr=(!empty($manual_file))? $dir . "/" . $filename  : "";
        }

    if (!(empty($manual_addr))) {
?>

                <SPAN ID='manual' CLASS="titlebar_text" onClick = "do_manual('<?php echo $manual_addr;?>');" STYLE="display:none;"  ><U><?php print gettext('Manual');?></U></SPAN>
<?php
            }
?>
                <SPAN ID = 'gout' CLASS = 'hover_lo' onClick = "do_logout();" STYLE="display:none;" ><?php print get_text("Logout"); ?></SPAN> <!-- 7/28/10 -->

<?php
        if ($_SERVER['HTTP_HOST'] == "127.0.0.1") { print "&nbsp;&nbsp;&nbsp;&nbsp;DB:&nbsp;{$mysql_db}&nbsp;&nbsp;&nbsp;&nbsp;";}
?>

                <SPAN ID='msg_span' CLASS = 'message'></SPAN>
                <br />
            </TD></TR>
        <TR><TD ID = 'buttons' STYLE = "display: inline;">
            <SPAN ID = 'main'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick ="go_there('main.php', this.id);"><?php print get_text("Situation"); ?></SPAN>
            <SPAN ID = 'add'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "go_there('add.php', this.id);"><?php print get_text("New"); ?></SPAN>
            <SPAN ID = 'resp'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "go_there('units.php', this.id);"><?php print get_text("Units"); ?></SPAN>
            <SPAN ID = 'facy'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "go_there('facilities.php', this.id);"><?php print get_text("Fac's"); ?></SPAN>
<?php
if ((get_variable('use_messaging') == 1) || (get_variable('use_messaging') == 2) || (get_variable('use_messaging') == 3)) {		//	10/23/12
?>
            <SPAN ID = 'msg'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "starting=false; do_mess();"><?php print get_text("Msgs"); ?></SPAN>
<?php
    }
?>
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
                onClick = "starting = false; do_emd_card('<?php print $card_addr; ?>');"><?php print get_text("SOP's"); ?></SPAN>	<!-- 7/3/10 -->
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
                onClick = "starting=false; do_full_scr();"><?php print get_text("Full scr"); ?></SPAN>
            <SPAN ID = 'links'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "light_butt('links'); parent.main.$('links').style.display='inline';"><?php print get_text("Links"); ?></SPAN>
            <SPAN ID = 'call'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "starting=false;do_callBoard();" STYLE = 'display:<?php print $call_disp_attr; ?>'><?php print get_text("Board"); ?></SPAN> <!-- 5/12/10 -->
<!-- ================== -->
            <SPAN ID = 'term' CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "go_there('mobile.php', this.id);"><?php print get_text("Mobile"); ?></SPAN>	<!-- 7/27/10 -->
<!-- ================== -->
            <SPAN ID = 'files'  CLASS = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "do_files();"><?php print get_text("Files");?></SPAN>	<!-- 9/10/13 -->
<!-- ================== -->
            <SPAN ID = 'reqs'  CLASS = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "go_there('./portal/requests.php', this.id);"></SPAN>	<!-- 10/23/12 -->
<?php
        if (intval(get_variable('ics_top')==1)) { 		// 5/21/2013
?>

<!-- ================== -->			<!-- 5/13/2013 -->
            <SPAN ID = 'ics'  CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "starting=false;window.open('ics213.php', 'ics213');"><?php print get_text("ICS-FORMS"); ?></SPAN> <!-- 5/13/2013 -->
<?php
            }		// end if (ics_top)

        if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		// 6/3/2013 -7/2/2013
?>
            <SPAN ID = 'has_button' CLASS = 'plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);"
                onClick = "do_broadcast();"><?php echo get_text("HAS"); ?></SPAN> <!-- 5/24/2013 -->
<?php
    }			// end if (broadcast && internet )
?>
            </TD>
            </TR>
        <TR ID='file_buts_row' WIDTH='100%' STYLE="display: none; text-align: center;">	<!-- 9/10/13 -->
            <TD ALIGN=CENTER WIDTH='100%'><CENTER>
                <SPAN STYLE = "margin-left:150px; ">
                    <SPAN id='file_list' style='display: inline; float: none;'></SPAN>
                    <SPAN id='can_files' class='plain' style='float: right;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='hide_files();'>Cancel</SPAN>
                    <SPAN id='file_list2' style='display: inline; float: right;'></SPAN>
                </SPAN>
            </TD></CENTER>
        </TR>
        <TR ID = 'has_form_row' STYLE = "display:none;">
            <TD ALIGN=CENTER>
                <SPAN ID = "has_span" >
                <FORM NAME = 'has_form' METHOD = post ACTION = "javascript: void(0)">
                <INPUT TYPE = 'text' NAME = 'has_text' ID = 'has_text' CLASS = '' size=90 value = "" STYLE = "margin-left:6px;" placeholder="<?php print gettext('enter your broadcast message');?>" />
                <BUTTON VALUE="Send" onclick = "has_check(this.form.has_text.value.trim());" STYLE = "margin-left:16px;"><?php print gettext('Send');?></BUTTON>
                <BUTTON VALUE="Cancel" onclick = "can_has();" STYLE = "margin-left:24px;"><?php print gettext('Cancel');?></BUTTON>
                </FORM>
                </SPAN>
            </TD>
            </TR>

        <TR ID = 'has_message_row' STYLE = "display: none;">
            <TD ALIGN=CENTER>
                <SPAN ID = "msg_span" STYLE = "margin-left:50px; " >
                    <SPAN ID = "has_message_text"></SPAN>
                    <BUTTON VALUE="OK" onclick = "end_message_show();"  STYLE = "margin-left:20px"><?php print gettext('OK');?></BUTTON>
                </SPAN>
            </TD>
            </TR>

    </TABLE>

    <FORM NAME="go" action="#" TARGET = "main"></FORM>

    <FORM NAME="gout_form" action="main.php" TARGET = "main">
    <INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
    </FORM>
    <P>
        <DIV ID = "log_div"></DIV>
<!-- <button onclick = 'alert(getElementById("user_id"));'><?php print gettext('Test');?></button> -->
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
<button onClick = "alert(parent.document.getElementById('the_frames').getAttribute('rows'));"><?php print gettext('Get');?></button>
<button onClick = "parent.document.getElementById('the_frames').setAttribute('rows', '600, 100, *');"><?php print gettext('Set');?></button>
-->
<DIV ID='test' style="position: fixed; top: 20px; left: 20px; height: 20px; width: 100px;" onclick = "location.href = '#bottom';">
    <h3></h3></DIV>
<!-- <button onclick = "show_has_message('asasasasas ERERERERER ');"><?php print gettext('Test');?></button> -->

</BODY>
</HTML>
