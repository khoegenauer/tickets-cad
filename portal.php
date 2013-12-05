<?php
/**
 * @package portal.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version 2013-09-10
 */
/*
9/10/13 - Major re-write to previous versions
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
$logged_in = $logged_out = false;
if (empty($_SESSION)) {
    $logged_out = true;
    header("Location: index.php");
    } else {
    $logged_in = true;
    }
require_once './incs/functions.inc.php';
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

if ($_SESSION['internet']) {				// 8/22/10
    $api_key = trim(get_variable('gmaps_api_key'));
    $key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
    } else {
    $api_key = "";
    $key_str = "";
    }

$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Service User Portal');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL="StyleSheet" HREF="./portal/css/stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>&libraries=geometry,weather&sensor=false"></SCRIPT>
<SCRIPT  TYPE="text/javascript"SRC="./js/epoly.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT> 	<!-- 8/1/11 -->
<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>	<!-- 1/29/2013 -->
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->
<SCRIPT TYPE="text/javascript" SRC="./js/domready.js"></script>
<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js"></script>
<SCRIPT>
var randomnumber;
var the_string;
var theClass = "background-color: #CECECE";
var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;
var request_lat;
var request_lng;
var the_color;
var fac_lat = [];
var fac_lng = [];
var fac_street = [];
var fac_city = [];
var fac_state = [];
var showall = "no";
var point;
var theLat;
var theLng;
var showhide = 1;
var summary_interval;
var msgs_interval;
var markers_interval;
var iwMaxWidth = 500;

window.onresize=function () {set_size();};

function set_size() {
    var viewportwidth;
    var viewportheight;
    if (typeof window.innerWidth != 'undefined') {
        viewportwidth = window.innerWidth;
        viewportheight = window.innerHeight;
        } else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
        viewportwidth = document.documentElement.clientWidth;
        viewportheight = document.documentElement.clientHeight;
        } else {
        viewportwidth = document.getElementsByTagName('body')[0].clientWidth;
        viewportheight = document.getElementsByTagName('body')[0].clientHeight;
        }
    var mapWidth = viewportwidth * .5;
    var mapHeight = viewportheight * .55;
    var listWidth = viewportwidth * .97;
    var listHeight = viewportheight * .4;
    var controlsWidth = viewportwidth * .35;
    var controlsHeight = viewportheight * .4;
    var bannerwidth = viewportwidth * .97;
    $('outer').style.width = viewportwidth + "px";
    $('outer').style.height = viewportheight + "px";
    $('map_outer').style.width = mapWidth + "px";
    $('map_outer').style.height = mapHeight + "px";
    $('map_canvas').style.width = mapWidth + "px";
    $('map_canvas').style.height = mapHeight + "px";
    $('requests_list').style.width = listWidth + "px";
    $('requests_list').style.height = listHeight + "px";
    $('tophalf').style.width = viewportwidth + "px";
    $('tophalf').style.height = viewportheight * .55 + "px";
    $('bottomhalf').style.width = viewportwidth + "px";
    $('bottomhalf').style.height = viewportheight * .4 + "px";
    $('controls').style.width = viewportwidth * .4 + "px";
    $('controls').style.height = viewportheight * .55 + "px";
    $('map_wrapper').style.width = viewportwidth * .5 + "px";
    $('map_wrapper').style.height = viewportheight * .55 + "px";
    $('banner').style.width = bannerwidth + "px";
    $('banner').style.height = "2em";
    $('list_header').style.width = bannerwidth + "px";
    $('list_header').style.height = "2em";
    }
/**
 * 
 * @returns {undefined}
 */
function out_frames() {		//  onLoad = "out_frames()"
    if (top.location != location) top.location.href = document.location.href;
    }		// end function out_frames()

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
 * @returns {@exp;AJAX@pro;responseText|Boolean}
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
function requests_get() {
    msgs_interval = window.setInterval('do_requests_loop()', 60000);
    }
/**
 *
 * @returns {undefined}
 */
function do_requests_loop() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&showall=" + showall + "&version=" + randomnumber;
    sendRequest (url, requests_cb2, "");
    }
/**
 *
 * @returns {undefined}
 */
function logged_in() {								// returns boolean
    var temp = <?php print $logged_in;?>;

    return temp;
    }
/**
 *
 * @param {type} val
 * @returns {Boolean}
 */
function isNull(val) {								// checks var stuff = null;

    return val === null;
    }

var newwindow = null;
var starting;
/**
 *
 * @param {type} id
 * @returns {unresolved}
 */
function do_window(id) {				// 1/19/09
    if ((newwindow) && (!(newwindow.closed))) {newwindow.focus(); return;}		// 7/28/10
    if (logged_in()) {
        if (starting) {return;}						// 6/6/08
        starting=true;
        newwindow=window.open("./portal/request.php?id=" + id, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
        if (isNull(newwindow)) {
            alert ("<?php print gettext('Portal operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        newwindow.focus();
        starting = false;
        }
    }		// end function do_window()

var viewwindow = null;
var starting;
/**
 *
 * @param {type} id
 * @returns {unresolved}
 */
function do_viewwindow(id) {				// 1/19/09
    if ((viewwindow) && (!(viewwindow.closed))) {viewwindow.focus(); return;}		// 7/28/10
    if (logged_in()) {
        if (starting) {return;}						// 6/6/08
        starting=true;
        viewwindow=window.open("./portal/request.php?func=view&id=" + id, "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=100, screenX=100, screenY=100");
        if (isNull(viewwindow)) {
            alert ("<?php print gettext('Portal operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        viewwindow.focus();
        starting = false;
        }
    }		// end function do_window()

var newreq = null;
var starting;
/**
 *
 * @returns {unresolved}
 */
function do_newreq() {				// 1/19/09
    if ((newreq) && (!(newreq.closed))) {newreq.focus(); return;}		// 7/28/10
    if (logged_in()) {
        if (starting) {return;}						// 6/6/08
        starting=true;
        newreq=window.open("./portal/new_request.php", "view_request",  "titlebar, location=0, resizable=1, scrollbars=yes, height=700, width=600, status=0, toolbar=0, menubar=0, location=0, left=100, top=300, screenX=100, screenY=300");
        if (isNull(newreq)) {
            alert ("<?php print gettext('Portal operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        newreq.focus();
        starting = false;
        }
    }		// end function do_window()
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
function requests_cb2(req) {
    var the_requests=JSON.decode(req.responseText);
    if (the_requests[0][0] == "No Current Requests") {
        var columnWidth = (window.innerWidth * .97) / 8;
        width = "width: " + columnWidth + "px; ";
        } else {
        width = "";
        }
    theClass = "background-color: #CECECE";
    the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
    the_string += "<TR class='list_heading' style='text-align: left;'>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Service User');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Phone');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Contact');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Scope');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Comments');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'<?php print get_text('>Status');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Updated');?></TD>";
    the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('By');?></TD>";
    the_string += "</TR>";
    for (var key in the_requests) {
        if (the_requests[key][0] == "No Current Requests") {
            $('export_but').style.display = "none";
            the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
            the_string += "<TD COLSPAN=99 class='list_entry' width='100%'><?php print gettext('No Current Requests');?></TD></TR>";
            } else {
            $('export_but').style.display = "inline-block";
            var the_request_id = the_requests[key][0];
            if (the_requests[key][16] == "Open") {
                var the_onclick = "onClick='do_window(" + the_request_id + ");'";
                } else {
                var the_onclick = "onClick='do_viewwindow(" + the_request_id + ");'";
                }
            the_string += "<TR class='list_row' title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + ";' " + the_onclick + ">";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][2] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][3] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][4] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][13] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][15] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][16] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][25] + "</TD>";
            the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][26] + "</TD>";
            the_string += "</TR>";
            if (the_requests[key][16] == "Accepted") {
                the_color = 3;
                } else if (the_requests[key][16] == "Declined") {
                the_color = 2;
                } else {
                the_color = 4;
                }
            if ((the_requests[key][29] != .999999) && (the_requests[key][30] != .999999) && (the_color != 3)) {
                request_lat = the_requests[key][29];
                request_lng = the_requests[key][30];
                point = new google.maps.LatLng(request_lat, request_lng);
                var info = "<DIV class='infowindow-content'><H1>" + the_requests[key][13] + "</H1>";
                info += "<TABLE BORDER=1 WIDTH='80%'>";
                info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Service User');?></B></TD><TD class='td_data'>" + the_requests[key][2] + "</TD></TR>";
                info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + the_requests[key][16] + "</TD></TR>";
                info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Updated');?></B></TD><TD class='td_data'>" + the_requests[key][25] + "</TD></TR>";
                info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('By');?></B></TD><TD class='td_data'>" + the_requests[key][26] + "</TD></TR></TABLE></DIV>";
                createMarker(point, the_color, the_request_id, info);
                }
            }
        }
        the_string += "</TABLE>";
        $('all_requests').innerHTML = the_string;
    }
/**
 *
 * @param {type} showall
 * @returns {undefined}
 */
function get_requests() {
    var width = "";
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/list_requests.php?id=<?php print $_SESSION['user_id'];?>&showall=" + showall + "&version=" + randomnumber;
    sendRequest (url, requests_cb, "");
    function requests_cb(req) {
        var the_requests=JSON.decode(req.responseText);
        if (the_requests[0][0] == "No Current Requests") {
            var columnWidth = (window.innerWidth * .97) / 8;
            width = "width: " + columnWidth + "px; ";
            } else {
            width = "";
            }
        theClass = "background-color: #CECECE";
        the_string = "<TABLE cellspacing='0' cellpadding='1' style='width: 100%; table-layout: fixed;'>";
        the_string += "<TR class='list_heading' style='text-align: left;'>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Service User');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Phone');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Contact');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Scope');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Comments');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'<?php print get_text('>Status');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('Updated');?></TD>";
        the_string += "<TD class='list_heading' style='" + width + "'><?php print get_text('By');?></TD>";
        the_string += "</TR>";
        for (var key in the_requests) {
            if (the_requests[key][0] == "No Current Requests") {
                $('export_but').style.display = "none";
                the_string += "<TR style='" + theClass + "; border-bottom: 2px solid #000000;'>";
                the_string += "<TD COLSPAN=99 class='list_entry' width='100%'><?php print gettext('No Current Requests');?></TD></TR>";
                } else {
                $('export_but').style.display = "inline-block";
                var the_request_id = the_requests[key][0];
                if (the_requests[key][16] == "Open") {
                    var the_onclick = "onClick='do_window(" + the_request_id + ");'";
                    } else {
                    var the_onclick = "onClick='do_viewwindow(" + the_request_id + ");'";
                    }
                the_string += "<TR class='list_row' title='" + the_requests[key][13] + "' style='" + the_requests[key][17] + ";' " + the_onclick + ">";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][2] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][3] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][4] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][13] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][15] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][16] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][25] + "</TD>";
                the_string += "<TD class='list_entry' style='" + the_requests[key][17] + ";'>" + the_requests[key][26] + "</TD>";
                the_string += "</TR>";
                if (the_requests[key][16] == "Accepted") {
                    the_color = 3;
                    } else if (the_requests[key][16] == "Declined") {
                    the_color = 2;
                    } else {
                    the_color = 4;
                    }
                if ((the_requests[key][29] != .999999) && (the_requests[key][30] != .999999) && (the_color != 3)) {
                    request_lat = the_requests[key][29];
                    request_lng = the_requests[key][30];
                    point = new google.maps.LatLng(request_lat, request_lng);
                    var info = "<DIV class='infowindow-content'><H1>" + the_requests[key][13] + "</H1>";
                    info += "<TABLE BORDER=1 WIDTH='80%'>";
                    info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Service User');?></B></TD><TD class='td_data'>" + the_requests[key][2] + "</TD></TR>";
                    info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Status');?></B></TD><TD class='td_data'>" + the_requests[key][16] + "</TD></TR>";
                    info += "<TR class='even'><TD class='td_label'><B><?php print get_text('Updated');?></B></TD><TD class='td_data'>" + the_requests[key][25] + "</TD></TR>";
                    info += "<TR class='odd'><TD class='td_label'><B><?php print get_text('By');?></B></TD><TD class='td_data'>" + the_requests[key][26] + "</TD></TR></TABLE></DIV>";
                    createMarker(point, the_color, the_request_id, info);
                    }
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
function markers_get() {
    markers_interval = window.setInterval('do_markers_loop()', 60000);
    }
/**
 *
 * @returns {undefined}
 */
function do_markers_loop() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
    sendRequest (url, markers_cb2, "");
    }

function do_filelist() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/file_list.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
    sendRequest (url, file_cb, "");
    function file_cb(req) {
        var the_files=req.responseText;
        $('file_list').innerHTML = the_files;
        }
    }

/**
 *
 * @param {type} req
 * @returns {undefined}
 */
function markers_cb2(req) {
    var the_markers=JSON.decode(req.responseText);
    if (the_markers[0] != -1) {
        for (var key in the_markers) {
            var the_lat = the_markers[key].lat;
            var the_lng = the_markers[key].lng;
            var the_scope = the_markers[key].scope;
            var the_description = the_markers[key].description;
            var info_t = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Ticket');?></SPAN></CENTER><BR />";
            info_t += "<TABLE BORDER=1 WIDTH='80%'>";
            info_t += "<TR class='even'><TD class='td_label'><B><?php print get_text('Synopsis');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
            info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></DIV>";
            point = new google.maps.LatLng(the_lat, the_lng);
            createMarker(point, 2, "T", info_t);
            for (var elements in the_markers[key].responders) {
                var r_lat = the_markers[key].responders[elements].lat;
                var r_lng = the_markers[key].responders[elements].lng;
                var r_handle = the_markers[key].responders[elements].handle;
                var info_r = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Responder');?></SPAN></CENTER><BR />";
                info_r += "<TABLE BORDER=1 WIDTH='80%'>";
                info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('On Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
                info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Responder Handle');?></B></TD><TD class='td_data'>" + r_handle + "</TD></TR>";
                info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></DIV>";
                createMarker(point, 1, "R", info_r);
                }
            }
        }
    }
/**
 *
 * @returns {undefined}
 */
function get_the_markers() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/list_ticketsandresponders.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
    sendRequest (url, markers_cb, "");
    function markers_cb(req) {
        var the_markers=JSON.decode(req.responseText);
        if (the_markers[0] != -1) {
            for (var key in the_markers) {
                var the_lat = the_markers[key].lat;
                var the_lng = the_markers[key].lng;
                var the_scope = the_markers[key].scope;
                var the_description = the_markers[key].description;
                var info_t = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Ticket');?></SPAN></CENTER><BR />";
                info_t += "<TABLE BORDER=1 WIDTH='80%'>";
                info_t += "<TR class='even'><TD class='td_label'><B><?php print get_text('Synopsis');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
                info_t += "<TR class='odd'><TD class='td_label'><B><?php print get_text('Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></DIV>";
                point = new google.maps.LatLng(the_lat, the_lng);
                createMarker(point, 2, "T", info_t);
                for (var elements in the_markers[key].responders) {
                    var r_lat = the_markers[key].responders[elements].lat;
                    var r_lng = the_markers[key].responders[elements].lng;
                    var r_handle = the_markers[key].responders[elements].handle;
                    var info_r = "<DIV class='infowindow-content'><CENTER><SPAN style='text-align: center; width: 100%; font-size: 1.5em; font-weight: bold;'><?php print get_text('Responder');?></SPAN></CENTER><BR />";
                    info_r += "<TABLE BORDER=1 WIDTH='80%'>";
                    info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('On Job');?></B></TD><TD class='td_data'>" + the_scope + "</TD></TR>";
                    info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Responder Handle');?></B></TD><TD class='td_data'>" + r_handle + "</TD></TR>";
                    info_r += "<TR class='even'><TD class='td_label'><B><?php print get_text('Description');?></B></TD><TD class='td_data'>" + the_description + "</TD></TR></TABLE></DIV>";
                    point = new google.maps.LatLng(r_lat, r_lng);
                    createMarker(point, 1, "R", info_r);
                    }
                }
            }
        }
    markers_get();
    }
/**
 *
 * @returns {undefined}
 */
function summary_get() {
    summary_interval = window.setInterval('do_summary_loop()', 60000);
    }
/**
 *
 * @returns {undefined}
 */
function do_summary_loop() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/requests_summary?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
    sendRequest (url, summary_cb2, "");
    }
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
function summary_cb2(req) {
    var the_summary=JSON.decode(req.responseText);
    var the_output = "<TABLE style='font-size: 2.5em; text-align: center; border: 1px solid #707070;'>";
    the_output += "<TR style='font-size: 0.8em;'><TH style='background-color: #707070; border: 1px solid #707070;'>&nbsp;</TH><TH style='border: 1px solid #707070;'><?php print get_text('Week');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Month');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Year');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Total')?></TH><TR>";
    the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Requests');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[0] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[1] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[2] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[3] + "</TD></TR>";
    the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Accepted');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[4] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[5] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[6] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[7] + "</TD></TR>";
    the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Declined');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[8] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[9] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[10] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[11] + "</TD></TR>";
    the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Closed');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[12] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[13] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[14] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[15] + "</TD></TR>";
    the_output += "</TABLE>";
    $('summary_table').innerHTML = the_output;
    }
/**
 *
 * @returns {undefined}
 */
function get_summary() {
    randomnumber=Math.floor(Math.random()*99999999);
    var url ="./portal/ajax/requests_summary.php?id=<?php print $_SESSION['user_id'];?>&version=" + randomnumber;
    sendRequest (url, summary_cb, "");
    function summary_cb(req) {
        var the_summary=JSON.decode(req.responseText);
        var the_output = "<TABLE style='font-size: 2.5em; text-align: center; border: 1px solid #707070;'>";
        the_output += "<TR style='font-size: 0.8em;'><TH style='background-color: #707070; border: 1px solid #707070;'>&nbsp;</TH><TH style='border: 1px solid #707070;'><?php print get_text('Week');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Month');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Year');?></TH><TH style='border: 1px solid #707070;'><?php print get_text('Total');?></TH><TR>";
        the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Requests');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[0] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[1] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[2] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[3] + "</TD></TR>";
        the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Accepted');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[4] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[5] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[6] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[7] + "</TD></TR>";
        the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Declined');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[8] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[9] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[10] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[11] + "</TD></TR>";
        the_output += "<TR><TD style='text-align: left; border: 1px solid #707070;'><?php print get_text('Closed');?></TD><TD style='border: 1px solid #707070;'>" + the_summary[12] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[13] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[14] + "</TD><TD style='border: 1px solid #707070;'>" + the_summary[15] + "</TD></TR>";
        the_output += "</TABLE>";
        $('summary_table').innerHTML = the_output;
        }
    summary_get();
    }
/**
 *
 * @param {type} lat
 * @returns {undefined}
 */
function do_lat(lat) {
    document.add.frm_lat.value=lat;			// 9/9/08
    }
/**
 *
 * @param {type} lng
 * @returns {undefined}
 */
function do_lng(lng) {
    document.add.frm_lng.value=lng;
    }
/**
 *
 * @param {type} text
 * @param {type} index
 * @returns {undefined}
 */
function do_fac_to_loc(text, index) {			// 9/22/09
    var curr_lat = fac_lat[index];
    var curr_lng = fac_lng[index];
    var curr_street = fac_street[index];
    var curr_city = fac_city[index];
    var curr_state = fac_state[index];
    do_lat(curr_lat);
    do_lng(curr_lng);
    pt_to_map(document.forms['add'], curr_lat, curr_lng);			// show it
    document.add.fac_street.value = curr_street;
    document.add.fac_city.value = curr_city;
    document.add.fac_state.value = curr_state;
    }					// end function do_fac_to_loc
/**
 *
 * @param {type} my_form
 * @param {type} lat
 * @param {type} lng
 * @returns {undefined}
 */
function pt_to_map(my_form, lat, lng) {						// 7/5/10
    myMarker.setMap(null);			// destroy predecessor
    theLat = lat;
    theLng = lng;
    var loc = <?php print get_variable('locale');?>;
    map.setCenter(new google.maps.LatLng(lat, lng), <?php print get_variable('def_zoom');?>);

    var iconImg = new Image();														// obtain icon dimensions
    iconImg.src ='./markers/crosshair.png';
    myIcon.anchor= new google.maps.Point(iconImg.width/2, iconImg.height/2);		// 8/11/12 - center offset = half icon width and height
    var dp_latlng = new google.maps.LatLng(lat, lng);

    myMarker = new google.maps.Marker({
        position: dp_latlng,
        icon: myIcon,
        draggable: true,
        map: map
        });
    myMarker.setMap(map);		// add marker with icon
    }				// end function pt_to_map ()
/**
 *
 * @param {type} my_form
 * @returns {Boolean}
 */
function loc_lkup(my_form) {		   						// 7/5/10
    if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
        alert ("<?php print gettext('City and State are required for location lookup.');?>");

        return false;
        }
    var geocoder = new google.maps.Geocoder();
    var myAddress = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();
    geocoder.geocode( { 'address': myAddress}, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) { pt_to_map (my_form, results[0].geometry.location.lat(), results[0].geometry.location.lng());}
        else 											{ alert("Geocode lookup failed: " + status);}
        });				// end geocoder.geocode()
    }				// end function loc_lkup()

// maps v3 stuff
var map;
var myMarker;
var lat_var;
var lng_var;
var zoom_var;
var icon_file = "./markers/crosshair.png";
/**
 *
 * @returns {undefined}
 */
function load() {
    var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);
    switch (<?php echo get_variable('maptype');?>) {
            case (2): the_type= google.maps.MapTypeId.SATELLITE; 	break;
            case (3): the_type= google.maps.MapTypeId.TERRAIN; 		break;
            case (4): the_type= google.maps.MapTypeId.HYBRID; 		break;
            default:  the_type= google.maps.MapTypeId.ROADMAP;
            }		// end switch

    var mapOptions = {
        zoom: <?php print get_variable('def_zoom');?>,
        center: myLatlng,
        panControl: true,
        zoomControl: true,
        scaleControl: true,
        mapTypeId: the_type
        }

    map = new google.maps.Map($('map_canvas'), mapOptions);				//
    doTraffic();
    doWeather();
    }			// end function load()

var icons=[];
icons[0] = "white.png";		// white
icons[1] = "red.png";	// red
icons[2] = "blue.png";	// blue
icons[3] = "yellow.png";	// yellow
icons[4] = "black.png";	// black
var bounds = new google.maps.LatLngBounds();
/**
 *
 * @returns {undefined}
 */
function closeIW() {
    }
/**
 *
 * @param {type} point
 * @param {type} color
 * @param {type} sym
 * @param {type} html
 * @returns {unresolved}
 */
function createMarker(point, color, sym, html) {
    var iconStr = sym;
    var image_file = "./portal/markers/gen_icon.php?blank=" + color + "&text=" + iconStr;
    var marker = new google.maps.Marker({position: point, map: map, icon: image_file});
    var infoBox = new InfoBox({
        content: html,
        maxWidth: 150,
        pixelOffset: new google.maps.Size(-140, 0),
        zIndex: null,
        boxStyle: {
            background: "#CECECE",
            border: "3px outset #707070",
            textAlign: "left",
            fontSize: "8pt",
            opacity: 0.9,
            width: "300px"
            },

        closeBoxMargin: "10px 2px 2px 2px",
        closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
        infoBoxClearance: new google.maps.Size(1, 1)
    });

    // var infowindow = new google.maps.InfoWindow({
        // content: html,
        // maxWidth: iwMaxWidth
        // });

    google.maps.event.addListener(marker, 'click', function () {
        infoBox.open(map, marker);
    });

    // google.maps.event.addListener(marker, 'click', function () {
        // infowindow.open(map, marker);
        // });

    google.maps.event.addListener(infoBox, 'closeclick', closeIW);
    bounds.extend(point);
    map.fitBounds(bounds);

    return marker;
    }				// end function create Marker()

var trafficInfo = new google.maps.TrafficLayer();
trafficInfo.setMap(map);
var toggleState = true;
/**
 *
 * @returns {undefined}
 */
function doTraffic() {
    if (toggleState) {
        trafficInfo.setMap(null);
        }
    else {
        trafficInfo.setMap(map);
        }
    toggleState = !toggleState;
    }				// end function doTraffic()

var weatherLayer = new google.maps.weather.WeatherLayer({
  temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
});

var cloudLayer = new google.maps.weather.CloudLayer();

var toggleWeather = true;
/**
 *
 * @returns {undefined}
 */
function doWeather() {
    if (toggleWeather) {
        weatherLayer.setMap(null);
        cloudLayer.setMap(null);
        }
    else {
        weatherLayer.setMap(map);
        cloudLayer.setMap(map);
        }
    toggleWeather = !toggleWeather;
    }				// end function doWeather()
/**
 *
 * @returns {unresolved}
 */
function GUnload() {
    return;
    }
/**
 *
 * @returns {undefined}
 */
function do_logout() {
    document.gout_form.submit();
    }
/**
 *
 * @returns {undefined}
 */
function toggle_closed() {
    if (showall == "yes") {
        showall = "no";
        $('showhide_but').innerHTML = "<?php print get_text('Show Closed');?>";
        get_requests();
        } else {
        showall = "yes";
        $('showhide_but').innerHTML = "<?php print get_text('Hide Closed');?>";
        get_requests();
        }
    }
/**
 *
 * @returns {undefined}
 */
function do_unload() {
    window.clearInterval(summary_interval);
    window.clearInterval(msgs_interval);
    window.clearInterval(markers_interval);
    }
<?php
$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>" . gettext('Receiving Facility') . "</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
        $rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
        }
$rec_fac_menu .= "<SELECT>";

$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$orig_fac_menu = "<SELECT NAME='frm_orig_fac' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
$orig_fac_menu .= "<OPTION VALUE=0 selected>" . gettext('Originating Facility') . "</OPTION>";
while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
        $orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
        $street = ($row_fc2['street'] != "") ? $row_fc2['street'] : "Empty";
        $city = ($row_fc2['city'] != "") ? $row_fc2['city'] : "Empty";
        $state = ($row_fc2['state'] != "") ? $row_fc2['state'] : "Empty";
        print "\tfac_lat[" . $row_fc2['id'] . "] = " . $row_fc2['lat'] . " ;\n";
        print "\tfac_lng[" . $row_fc2['id'] . "] = " . $row_fc2['lng'] . " ;\n";
        print "\tfac_street[" . $row_fc2['id'] . "] = '" . $street . "' ;\n";
        print "\tfac_city[" . $row_fc2['id'] . "] = '" . $city . "' ;\n";
        print "\tfac_state[" . $row_fc2['id'] . "] = '" . $state . "' ;\n";
        }
$orig_fac_menu .= "<SELECT>";

?>
</SCRIPT>
</HEAD>
<!-- <BODY onLoad = "ck_frames();"> -->

<?php

if ((!isset($_SESSION)) && (empty($_POST))) {
    print "Not Logged in";
} elseif ((isset($_SESSION)) && (empty($_POST))) {
    $onload_str = "load();";
    $now = time() - (intval(get_variable('delta_mins')*60));
?>

    <BODY onLoad="out_frames(); set_size(); location.href = '#top'; get_requests(); get_the_markers(); do_filelist(); get_summary(); <?php print $onload_str;?>;" onUnload='do_unload();'>
        <FORM NAME="go" action="#" TARGET = "main"></FORM>
        <DIV id='outer' style='text-align: center; margin: 10px;'>
            <DIV id='tophalf' style='width: 100%; font-size: 1em; z-index: 998;'>
                <DIV id='banner' style='background-color: #707070; vertical-align: middle;'><SPAN class='heading' style='font-size: 1.5em; vertical-align: middle;'>Tickets <?php print get_text('Service User');?> <?php print get_text('Portal');?></SPAN>
                    <SPAN ID='gout' CLASS='plain' style='float: right; font-size: 1em; vertical-align: middle;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="do_logout();"><?php print get_text('Logout');?></SPAN>
                </DIV>
                <DIV id='controls' style='position: absolute; top: 50px; left: 2%;'>
                    <TABLE WIDTH='100%' HEIGHT='100%' style='font-size: 1em; border: 3px outset #707070;'>
                        <TR style='font-size: 1em;'>
                            <TD WIDTH='50%' style='font-size: 1em; border: 3px outset #707070; vertical-align: top;'>
                                <CENTER>
                                <TABLE style='width: 100%;'>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'><SPAN id='sub_but' CLASS ='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_newreq();"><?php print get_text('New Request');?></SPAN></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'><SPAN ID='upload_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.open('./portal/import_requests.php','Import Requests','width=600,height=600,titlebar=1, location=0, resizable=1, scrollbars=yes, height=600,width=600,status=0,toolbar=0,menubar=0,location=0, right=100,top=300,screenX=500,screenY=300');" TITLE='<?php print gettext('Import Request from CSV File');?>'><?php print get_text('Import');?></SPAN></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'><SPAN ID='export_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="window.location.href='./portal/csv_export.php';"><?php print get_text('Export Requests to CSV');?></SPAN></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'><SPAN ID='showhide_but' CLASS='plain' style='font-size: 1em; vertical-align: middle; width: 150px;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick="toggle_closed();"><?php print get_text('Show Closed');?></SPAN></TD>
                                    </TR>
                                </TABLE><BR /><BR />
                                <DIV style='border: 2px outset #707070;'>
                                    <DIV class='heading' style='font-size: 1.1em;'><?php print get_text('Useful Documents');?></DIV><BR />
                                    <DIV id='file_list' style='font-size: 1em; height: 100%; overflow-y: auto;'></DIV>
                                </DIV>
                                </CENTER>
                            </TD>
                            <TD WIDTH='50%' style='font-size: 1em; border: 3px outset #707070; text-align: left;'>
                                <TABLE WIDTH='100%'>
                                    <TR CLASS="heading" style='font-size: 1em;'>
                                        <TD CLASS='heading' style='font-size: 1.1em;'><?php print get_text('Contact Us');?></TD>
                                    <TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'>&nbsp;</TD>
                                    </TR>
                                        <TD style='font-size: 1em;'><?php print get_text('Telephone');?>: <?php print get_variable('portal_contact_phone');?></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'><?php print get_text('Email');?>: <?php print get_variable('portal_contact_email');?></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'>&nbsp;</TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'>&nbsp;</TD>
                                    </TR>
                                    <TR class='heading' style='font-size: 1.1em;'>
                                        <TD class='heading' style='font-size: 1.1em;'><?php print gettext('Your Request Statistics');?> - <?php print get_user_name($_SESSION['user_id']);?></TD>
                                    </TR>
                                    <TR style='font-size: 1em;'>
                                        <TD style='font-size: 1em;'>&nbsp;</TD>
                                    </TR>
                                    <TR>
                                        <TD id='summary_table' ALIGN='center'></TD>
                                    </TR>
                                </TABLE>
                            </TD>
                        </TR>
                    </TABLE>
                </DIV>
                <DIV id='map_wrapper' style='position: absolute; top: 50px; right: 2%;'>
<?php
                    if (get_variable('map_in_portal') == "1") {
?>
                        <DIV id='map_outer'>
                            <DIV id='map_canvas'></DIV>
                            <DIV id='map_controls'>
                                <CENTER><A HREF='#' onClick='doTraffic();'><U><?php print gettext('Traffic');?></U></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='#' onClick='doWeather();'><U><?php print get_text('Weather');?></U></A>
                            </DIV>
                        </DIV>
<?php
                        }
?>
                </DIV>
            </DIV>
            <DIV id='bottomhalf'>
                <DIV id='requests_list' style='position: absolute; bottom: 15%; max-height: 15%;'>
                    <DIV id='color_key'>
                        <SPAN id='open' style='background-color: #FFFF00; color: #000000;'><?php print get_text('Open');?></SPAN>
                        <SPAN id='open' style='background-color: #CC9900; color: #000000;'><?php print get_text('Tentative');?></SPAN>
                        <SPAN id='open' style='background-color: #33CCFF; color: #000000;'><?php print get_text('Accepted');?></SPAN>
                        <SPAN id='open' style='background-color: #00FF00; color: #000000;'><?php print get_text('Resourced');?></SPAN>
                        <SPAN id='open' style='background-color: #FFFFFF; color: #00FF00;'><?php print get_text('Completed');?></SPAN>
                        <SPAN id='open' style='background-color: #FF0000; color: #FFFF00;'><?php print get_text('Declined');?></SPAN>
                        <SPAN id='open' style='background-color: #000000; color: #FFFFFF;'><?php print get_text('Closed');?></SPAN>
                        <SPAN id='open' style='background-color: #FF0000; color: #FFFF00;'><?php print get_text('Cancelled');?></SPAN>
                    </DIV>
                    <DIV id='list_header' class='heading' style='font-size: 16px; border: 1px outset #000000; vertical-align: middle; height: 18px;'><?php print get_text('Current Requests');?></DIV>
                    <DIV id='the_bottom' style='border: 2px outset #CECECE; padding: 10px; height: 100%; overflow-y: scroll;'>
                        <DIV ID='all_requests' style='width: 100%;'></DIV>
                    </DIV>
                </DIV>
            </DIV>
        <FORM METHOD='POST' NAME="gout_form" action="index.php">
        <INPUT TYPE='hidden' NAME = 'logout' VALUE = 1 />
        </FORM>
        </DIV>
        </BODY>
<?php
    }
?>
</HTML>
