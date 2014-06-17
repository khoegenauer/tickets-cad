<?php

//9/10/13 - requests.php - lists pending / open requests for Tickets user

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
    header("Location: index.php");
    }
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));
$requester = get_owner($_SESSION['user_id']);

/**
 * get_user_name
 * Insert description here
 *
 * @param $the_id
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_user_name($the_id) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
    $result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) == 1) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        $the_ret = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
        }

    return $the_ret;
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Service User Requests');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css" />
<style type="text/css">
.summ_td_label {text-align: left; border: 1px outset #FFFFFF; font-size: 12px; font-weight: bold; background-color: #707070; color: #FFFFFF; width: 15%;}
.summ_td_data {border: 1px outset #FFFFFF; font-size: 14px; background-color: #CECECE; color: #000000; width: 8%;}
</style>
<SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT>
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var the_onclick;
var showall = "no";

/**
 *
 * @returns {undefined}
 */
function ck_frames() {		// onLoad = "ck_frames()"
    if (self.location.href==parent.location.href) {
        self.location.href = 'index.php';
        }
    }		// end function ck_frames()
/**
 *
 * @param {type} the_id
 * @param {type} the_val
 * @returns {undefined}
 */
function do_sel_update(the_id, the_val) {							// 12/17/09
    status_update(the_id, the_val);
    }
/**
 *
 * @param {type} the_id
 * @param {type} the_val
 * @returns {Boolean}
 */
function status_update(the_id, the_val) {									// write unit status data via ajax xfer
    var querystr = "the_id=" + the_id;
    querystr += "&status=" + the_val
    ;

    var url = "up_status.php?" + querystr;			//
    var payload = syncAjax(url);						//
    if (payload.substring(0,1)=="-") {
        alert ("<?php print __LINE__;?>: msg failed ");

        return false;
        }
    else {
    get_requests();

        return true;
        }
    }		// end function status_update()
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
 * @param {type} where
 * @param {type} the_id
 * @returns {undefined}
 */
function go_there(where, the_id) {		//
    document.go.action = where;
    document.go.submit();
    }				// end function go there ()
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
    CngClass(the_id, 'hover');

    return true;
    }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
function do_plain(the_id) {
    CngClass(the_id, 'plain');

    return true;
    }
/**
 *
 * @param {type} url
 * @param {type} callback
 * @param {type} postData
 * @returns {unresolved}
 */
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
/**
 *
 * @param {type} strURL
 * @returns {Boolean|@exp;AJAX@pro;responseText}
 */
function syncAjax(strURL) {
    if (window.XMLHttpRequest) {
        AJAX=new XMLHttpRequest();
        }
    else {
        AJAX=new ActiveXObject("Microsoft.XMLHTTP");
        }
    if (AJAX) {
        AJAX.open("GET", strURL, false);
        AJAX.send(null);

        return AJAX.responseText;
        }
    else {
        alert("<?php echo 'error: ' . basename(__FILE__) . '@' .  __LINE__;?>");

        return false;
        }
    }
/**
 *
 * @returns {undefined}
 */
function get_requests() {
    randomnumber=Math.floor(Math.random()*99999999);
  	var url ="./ajax/list_requests.php?showall=" + showall + "&version=" + randomnumber;
    sendRequest (url, requests_cb, "");
    function requests_cb(req) {
        var the_requests=JSON.decode(req.responseText);
		if(the_requests[0] == "No Current Requests") {
			var columnWidth = (window.innerWidth * .93) / 10;
			width = "width: " + columnWidth + "px; ";
			} else {
			width = "";
			}
		the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed; color: #FFFFFF;'>";
		the_string += "<TR class='heading' style='" + width + "font-weight: bold; font-size: 12px;'>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('ID');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Patient');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Phone');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Contact');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Scope');?></TD>";
		the_string += "<TD class='heading' style='width: 25%; font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Description');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Status');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Requested');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Updated');?></TD>";
		the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('By');?></TD>";
		the_string += "</TR>";
        theClass = "background-color: #CECECE";
        for (var key in the_requests) {
            if (the_requests[key][0] == "<?php print gettext('No Current Requests');?>") {
                the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
                the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'><?php print gettext('No Current Requests');?></TD></TR>";
                } else {
                var the_request_id = the_requests[key][0];
                if ((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
                    the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
                    } else {
                    the_onclick = "";
                    }
                the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px;'>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
				the_string += "<TD style='width: 25%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
				the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
                the_string += "</TR>";
                }
            }
            the_string += "</TABLE>";
            $('all_requests').innerHTML = the_string;
            requests_get();
        }
    }
/**
 *
 * @returns {undefined}
 */
function requests_get() {
    msgs_interval = window.setInterval('do_requests_loop()', 10000);
    }
/**
 *
 * @returns {undefined}
 */
function do_requests_loop() {
    randomnumber=Math.floor(Math.random()*99999999);
  	var url ="./ajax/list_requests.php?showall=" + showall + "&version=" + randomnumber;
    sendRequest (url, requests_cb2, "");
    }
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
function requests_cb2(req) {
	var the_requests=JSON.decode(req.responseText);
	if(the_requests[0] == "No Current Requests") {
		var columnWidth = (window.innerWidth * .93) / 10;
		width = "width: " + columnWidth + "px; ";
		} else {
		width = "";
		}
	the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed; color: #FFFFFF;'>";
	the_string += "<TR class='heading' style='" + width + "font-weight: bold; font-size: 12px;'>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('ID');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Patient');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Phone');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Contact');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Scope');?></TD>";
	the_string += "<TD class='heading' style='width: 25%; font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Description');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Status');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Requested');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('Updated');?></TD>";
	the_string += "<TD class='heading' style='" + width + "font-weight: bold; font-size: 12px; border-right: 1px solid #FFFFFF;'><?php print get_text('By');?></TD>";
    the_string += "</TR>";
    theClass = "background-color: #CECECE";
    for (var key in the_requests) {
        if (the_requests[key][0] == "<?php print gettext('No Current Requests');?>") {
            the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
            the_string += "<TD COLSPAN=99 width='100%' style='font-weight: bold; font-size: 16px; text-align: center;'><?php print gettext('No Current Requests');?></TD></TR>";
            } else {
            var the_request_id = the_requests[key][0];
            if ((the_requests[key][16] == 'Open') || (the_requests[key][16] == 'Tentative')) {
                the_onclick = "onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\"";
                } else {
                the_onclick = "";
                }
            the_string += "<TR title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + "; border-bottom: 2px solid #000000; height: 12px;'>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_request','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][0] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][2] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][3] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][4] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][13] + "</TD>";
			the_string += "<TD style='width: 25%; " + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap: sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][14] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' " + the_onclick + ">" + the_requests[key][16] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][18] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][25] + "</TD>";
			the_string += "<TD style='" + the_requests[key][17] + " white-space: normal; word-wrap: break-word; -ms-word-wrap : sWrap; border-right: 1px solid #707070;' onClick=\"window.open('request.php?id=" + the_request_id + "','view_message','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300')\">" + the_requests[key][26] + "</TD>";
            the_string += "</TR>";
            }
        }
        the_string += "</TABLE>";
        $('all_requests').innerHTML = the_string;
    }
/**
 *
 * @returns {undefined}
 */
function summary_get() {
	summary_interval = window.setInterval('do_summary_loop()', 10000);
	}
/**
 *
 * @returns {undefined}
 */
function do_requests_loop() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb2, "");
	}
/**
 *
 * @returns {undefined}
 */
function get_summary() {
	randomnumber=Math.floor(Math.random()*99999999);
	var url ="./ajax/requests_wallboard.php?version=" + randomnumber;
	sendRequest (url, summary_cb, "");
	function summary_cb(req) {
		var the_summary=JSON.decode(req.responseText);
		var theColor = "style='background-color: #CECECE; color: #000000;'";
		if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var numOpen = "<TD class='summ_td_label'>Requests Open (not accepted): </TD><TD class='summ_td_data' " + theColor + ">" + the_summary[0] + "</TD>";
		if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var numAcc = "<TD class='summ_td_label'>Requests Accepted (not resourced): </TD><TD class='summ_td_data'>" + the_summary[1] + "</TD>";
		var numComp = "<TD class='summ_td_label'>Requests Completed: </TD><TD class='summ_td_data'>" + the_summary[3] + "</TD>";
		if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
		var totTent = "<TD class='summ_td_label'>Requests Tentative: </TD><TD class='summ_td_data' " + theColor + ">" + the_summary[7] + "</TD>";
		var totCan = "<TD class='summ_td_label'>Requests Cancelled: </TD><TD class='summ_td_data'>" + the_summary[8] + "</TD>";
		var totDec = "<TD class='summ_td_label'>Requests Declined: </TD><TD class='summ_td_data'>" + the_summary[9] + "</TD>";
		var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
		summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
		summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
		summaryText += "</TABLE>";
		$('theSummary').innerHTML = summaryText;
		summary_get();
		}
	}
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
function summary_cb2(req) {
	var the_summary=JSON.decode(req.responseText);
	var theColor = "style='background-color: #CECECE; color: #000000;'";
	if(the_summary[0] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var numOpen = "<TD class='summ_td_label'>Requests Open (not accepted): </TD><TD class='summ_td_data' " + theColor + ">" + the_summary[0] + "</TD>";
	if(the_summary[1] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var numAcc = "<TD class='summ_td_label'>Requests Accepted (not resourced): </TD><TD class='summ_td_data'>" + the_summary[1] + "</TD>";
	var numComp = "<TD class='summ_td_label'>Requests Completed: </TD><TD class='summ_td_data'>" + the_summary[3] + "</TD>";
	if(the_summary[7] > 5) { theColor = "style='background-color: red; color: yellow; font-weight: bold;'"; }
	var totTent = "<TD class='summ_td_label'>Requests Tentative: </TD><TD class='summ_td_data' " + theColor + ">" + the_summary[7] + "</TD>";
	var totCan = "<TD class='summ_td_label'>Requests Cancelled: </TD><TD class='summ_td_data'>" + the_summary[8] + "</TD>";
	var totDec = "<TD class='summ_td_label'>Requests Declined: </TD><TD class='summ_td_data'>" + the_summary[9] + "</TD>";
	var summaryText = "<TABLE style='width: 100%; background-color: #FFFFFF;'>";
	summaryText += "<TR>" + numOpen + numAcc + numComp + "</TR>";
	summaryText += "<TR>" + totTent + totCan + totDec + "</TR>";
	summaryText += "</TABLE>";
	$('theSummary').innerHTML = summaryText;
	}
/**
 *
 * @returns {undefined}
 */
function hide_closed() {
	showall = "no";
	$('hideBut').style.display = "none";
	$('showBut').style.display = "inline-block";
	get_requests();
	}
/**
 *
 * @returns {undefined}
 */
function show_closed() {
	showall = "yes";
	$('showBut').style.display = "none";
	$('hideBut').style.display = "inline-block";
	get_requests();
	}

/**
 *
 * @returns {undefined}
 */
function do_logout() {
    document.gout_form.submit();			// send logout
    }
/**
 *
 * @returns {undefined}
 */
function stop_timers() {
	window.clearInterval(msgs_interval);
	window.clearInterval(summary_interval);
	}
</SCRIPT>
</HEAD>
<!-- <BODY onLoad = "ck_frames();"> -->
<BODY onLoad="ck_frames(); location.href = '#top'; get_requests(); get_summary();" onUnload = "stop_timers();";>
	<DIV id='the_banner' class='heading' style='position: fixed; left: 2%; top: 2%; width:92%; border: 2px outset #CECECE; padding: 10px; text-align: center; font-size: 28px; height: 50px;'>Requests
	<SPAN id='showBut' class='plain' onMouseOver='do_hover();' onMouseOut='do_plain();' onClick='show_closed();'>Show All</SPAN>
	<SPAN id='hideBut' class='plain' style='display: none;' onMouseOver='do_hover();' onMouseOut='do_plain();' onClick='hide_closed();'>Hide Closed</SPAN>
	<SPAN ID='export_but' CLASS='plain' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.location.href='csv_export.php'"><?php print get_text('Export Requests to CSV');?></SPAN>
	<SPAN id='theSummary' style='height: 20px; width: 60%; float: right;'></SPAN>
	</DIV>
	<DIV id='the_list' style='position: fixed; left: 2%; top: 100px; width: 92%; height: 80%; max-height: 90%; border: 2px outset #CECECE; padding: 10px;'>
		<DIV ID='all_requests' style='width: 99%; height: 100%; overflow-y: auto;'></DIV>
    </DIV>
</BODY>
</HTML>
