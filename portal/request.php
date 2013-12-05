<?php
/**
 *
 *
 * @package request.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */
/*
9/10/13 - request.php - file for view and edit of portal user request
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
    header("Location: index.php");
    }
require_once '../incs/functions.inc.php';
do_login(basename(__FILE__));
$requester = get_owner($_SESSION['user_id']);
$id = (isset($_GET['id'])) ? $_GET['id'] : $_REQUEST['id'];
$only_view = ((isset($_GET['func'])) && ($_GET['func'] == "view")) ? TRUE : FALSE;
$can_edit = ((is_service_user()) && (!$only_view)) ? TRUE : FALSE;
$query = "SELECT *,
        `r`.`id` AS `request_id`,
        `a`.`id` AS `assigns_id`,
        `a`.`start_miles` AS `start_miles`,
        `a`.`end_miles` AS `end_miles`,
        `r`.`comments` AS `comments`,
        `a`.`comments` AS `assigns_comments`,
        `request_date` AS `request_date`,
        `tentative_date` AS `tentative_date`,
        `accepted_date` AS `accepted_date`,
        `declined_date` AS `declined_date`,
        `resourced_date` AS `resourced_date`,
        `completed_date` AS `completed_date`,
        `closed` AS `closed`,
        `_on` AS `_on`,
        `a`.`dispatched` AS `dispatched`,
        `a`.`clear` AS `clear`
        FROM `$GLOBALS[mysql_prefix]requests` `r`
        LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id`
        WHERE `r`.`id` = " . $id . " LIMIT 1";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$tentative_date = $row['tentative_date'];
$accepted_date = $row['accepted_date'];
$declined_date = $row['declined_date'];
$resourced_date = (($row['dispatched'] != "") || ($row['dispatched'] != NULL)) ? $row['dispatched'] : $row['resourced_date'];
if (($row['dispatched'] != "") && ($row['dispatched'] != NULL) && ($row['resourced_date'] == NULL)) {
    $update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `resourced_date` = '" . mysql_format_date($row['dispatched']) . " WHERE `id` = " . $id;
    }
$completed_date = (($row['clear'] != "") || ($row['clear'] != NULL)) ? $row['clear'] : $row['completed_date'];
if (($row['clear'] != "") && ($row['clear'] != NULL) && ($row['completed_date'] == NULL)) {
    $update = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `completed_date` = '" . mysql_format_date($row['clear']) . " WHERE `id` = " . $id;
    }
$closed_date = $row['closed'];
$updated_by = get_owner($row['_by']);

/**
 * get_contact_details
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
function get_contact_details($the_id) {
    $the_ret = array();
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `id` = " . $the_id . " LIMIT 1";
    $result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) == 1) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        $the_ret[] = (($row['name_f'] != "") && ($row['name_l'] != "")) ? $the_ret[] = $row['name_f'] . " " . $row['name_l'] : $the_ret[] = $row['user'];
        $the_ret[] = ($row['email'] != "") ? $row['email'] : gettext("Unknown");
        $the_ret[] = ($row['email_s'] != "") ? $row['email_s'] : gettext("Unknown");
        $the_ret[] = ($row['phone_p'] != "") ? $row['phone_p'] : gettext("Unknown");
        $the_ret[] = ($row['phone_s'] != "") ? $row['phone_s'] : gettext("Unknown");
        }

    return $the_ret;
    }

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

/**
 * get_facilityname
 * Insert description here
 *
 * @param $value
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_facilityname($value) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) != 0) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));

        return $row['name'];
        } else {
        return "";
        }
    }
/**
 *
 * @param type $value
 * @return string
 */
function get_facilitydetails($value) {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) != 0) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        $return = array();
        $return['street'] = $row['street'];
        $return['city'] = $row['city'];
        $return['state'] = $row['state'];
        } else {
        $return['street'] = "";
        $return['city'] = "";
        $return['state'] = "";
        }

    return $return;
    }
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <HEAD><TITLE><?php print gettext('Tickets - Service User Request');?></TITLE>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
    <META HTTP-EQUIV="Expires" CONTENT="0" />
    <META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
    <LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
    <SCRIPT SRC="../js/misc_function.js" TYPE="text/javascript"></SCRIPT>
    <SCRIPT TYPE="text/javascript" SRC="../js/domready.js"></script>
    <SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $key_str;?>&libraries=geometry,weather&sensor=false"></SCRIPT>
    <SCRIPT>
    var randomnumber;
    var the_string;
    var theClass = "background-color: #CECECE";
    var fac_lat = [];
    var fac_lng = [];
    var fac_street = [];
    var fac_city = [];
    var fac_state = [];
    var rec_fac_lat = [];
    var rec_fac_lng = [];
    var rec_fac_street = [];
    var rec_fac_city = [];
    var rec_fac_state = [];

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
        function () {return new XMLHttpRequest()	},
        function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
        function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
        function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
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
    function do_edit() {
        $('view').style.display = 'none';
        $('edit').style.display = 'inline';
        }

function validate(theForm) {
    var err_msg = "";
    var street = theForm.frm_street.value;
    var city = theForm.frm_city.value;
    var state = theForm.frm_state.value;
    var theDescription = theForm.frm_description.value;
    var requestDate = theForm.frm_year_request_date.value + "-" + theForm.frm_month_request_date.value + "-" + theForm.frm_day_request_date.value + " " + theForm.frm_hour_request_date.value + " " + theForm.frm_minute_request_date.value + ":00";
    var thePhone = (theForm.frm_phone.value != "") ? theForm.frm_phone.value : "none";
    var ToAddress = theForm.frm_toaddress.value;
    var dest_address_array = ToAddress.split(",");
    if (dest_address_array[0] == "") {
        ToAddress = "";
        }
    var thePatient = theForm.frm_patient.value;
    var origFac = theForm.frm_orig_fac.value;
    var recFac = theForm.frm_rec_fac.value;
    var theScope = theForm.frm_scope.value;
    var theComments = theForm.frm_comments.value;
    if (thePatient == "") { err_msg += "\t<?php print gettext('Name of person required');?>\n"; }
    if (theScope == "") { err_msg += "\t<?php print gettext('Request title required');?>\n"; }
    if (street == "") { err_msg += "\t<?php print gettext('Street address required');?>\n"; }
    if (city == "") { err_msg += "\t<?php print gettext('City is required');?>\n"; }
    if (state == "") { err_msg += "\t<?php print gettext('State required, for UK State is UK');?>\n"; }
    if (theDescription == "") { err_msg += "\t<?php print gettext('Description of job required');?>\n"; }
    if (requestDate == "") { err_msg += "\t<?php print gettext('Request date required');?>\n"; }
    if (err_msg != "") {
        alert ("<?php print gettext('Please correct the following and re-submit:');?>\n\n" + err_msg);

        return;
        } else {
        var geocoder = new google.maps.Geocoder();
        var myAddress = theForm.frm_street.value.trim() + ", " +theForm.frm_city.value.trim() + " "  +theForm.frm_state.value.trim();
        geocoder.geocode( { 'address': myAddress}, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            theForm.frm_lat.value = results[0].geometry.location.lat();
            theForm.frm_lng.value = results[0].geometry.location.lng();
            }
        });				// end geocoder.geocode()
        theForm.submit();
        }
    }
/**
 *
 * @param {type} req_id
 * @returns {undefined}
 */
    function do_cancel(req_id) {
        randomnumber=Math.floor(Math.random()*99999999);
        $('view').style.display="none";
        $('edit').style.display = 'none';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Cancelling request');?><BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
        var url ="./ajax/cancel_request.php?id=" + req_id + "&version=" + randomnumber;
        sendRequest (url, requests_cb, "");
        function requests_cb(req) {
            var the_response=JSON.decode(req.responseText);
            if (the_response[0] == 999) {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('Request could not be cancelled, please try again.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                } else {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('The Request has been cancelled and the controllers have been informed. You will receive an email confirmation.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                }
            }
        window.opener.get_requests();
        }

/**
 *
 * @param {type} id
 * @returns {undefined}
 */
    function accept(id) {
        randomnumber=Math.floor(Math.random()*99999999);
        $('view').style.display = 'none';
        $('edit').style.display = 'none';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Accepting request.');?><BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
        var url ="./ajax/insert_ticket.php?id=" + id + "&version=" + randomnumber;
        sendRequest (url, requests_cb, "");
        function requests_cb(req) {
            var the_response=JSON.decode(req.responseText);
            if (the_response[0] == 0) {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('Could not insert new Ticket, please try again.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                } else {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('A New Ticket has been inserted. click the link below to view.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'><?php print gettext('Go to Ticket');?></SPAN>";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                }
            }
        window.opener.get_requests();
        }
/**
 *
 * @param {type} the_id
 * @param {type} the_val
 * @returns {Boolean}
 */
    function status_update(the_id, the_val) {									// write unit status data via ajax xfer
        $('view').style.display="none";
        $('edit').style.display = 'none';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Updating Status');?><BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
        var querystr = "the_id=" + the_id;
        querystr += "&status=" + the_val;
        var url = "up_status.php?" + querystr;			//
        alert(url);
        var payload = syncAjax(url);						//
        if (payload.substring(0,1)=="-") {
            $('view').style.display="inline_block";
            $('waiting').style.display='none';
            $('waiting').innerHTML = "";
            alert ("<?php print gettext('Could not update status');?>");

            return false;
            }
        else {
            $('waiting').style.display='none';
            $('result').style.display = 'inline-block';
            var the_link = "<?php print gettext('Status has been updated.');?><BR /><BR /><BR /><BR />";
            the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
            $('done').innerHTML = the_link;
            }
        window.opener.get_requests();
        }		// end function status_update()
/**
 *
 * @param {type} id
 * @returns {undefined}
 */
    function tentative(id) {
        randomnumber=Math.floor(Math.random()*99999999);
        $('view').style.display="none";
        $('edit').style.display = 'none';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Tentatively accepting request');?><BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
        var url ="./ajax/insert_ticket_tentative.php?id=" + id + "&version=" + randomnumber;
        sendRequest (url, requests_cb, "");
        function requests_cb(req) {
            var the_response=JSON.decode(req.responseText);
            if (the_response[0] == 0) {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('Could not insert new Ticket, please try again.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                } else {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('A New Ticket has been inserted. click the link below to view.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='the_but' class='plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.opener.parent.frames[\"main\"].location=\"../edit.php?id=" + the_response[0] + "\"; window.close();'><?php print gettext('Go to Ticket');?></SPAN>";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                }
            }
        window.opener.get_requests();
        }
/**
 *
 * @param {type} id
 * @returns {undefined}
 */
    function decline(id) {
        randomnumber=Math.floor(Math.random()*99999999);
        $('view').style.display="none";
        $('edit').style.display = 'none';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Declining request.');?><BR /><IMG style='vertical-align: middle;' src='../images/progressbar3.gif'/>";
        var url ="./ajax/decline.php?id=" + id + "&version=" + randomnumber;
        sendRequest (url, requests_cb, "");
        function requests_cb(req) {
            var the_response=JSON.decode(req.responseText);
            if (the_response[0] == 200) {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('There was an error, please try again.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                } else {
                $('waiting').style.display='none';
                $('result').style.display = 'inline-block';
                var the_link = "<?php print gettext('The request has been declined.');?><BR /><BR /><BR /><BR />";
                the_link += "<SPAN id='finish' class = 'plain' style='float: none;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'><?php print gettext('Close');?></SPAN>";
                $('done').innerHTML = the_link;
                }
            }
        window.opener.get_requests();
        }
/**
 *
 * @returns {undefined}
 */
    function startup() {
        $('edit').style.display = 'none';
        $('result').style.display = 'none';
        $('view').style.display = 'inline';
        }
/**
 *
 * @param {type} lat
 * @returns {undefined}
 */
    function do_lat(lat) {
        document.edit_frm.frm_lat.value=lat;			// 9/9/08
        }
/**
 *
 * @param {type} lng
 * @returns {undefined}
 */
    function do_lng(lng) {
        document.edit_frm.frm_lng.value=lng;
        }
/**
 *
 * @param {type} text
 * @param {type} index
 * @returns {undefined}
 */
    function do_fac_to_loc(text, index) {			// 9/22/09
        var theFaclat = fac_lat[index];
        var theFaclng = fac_lng[index];
        var theFacstreet = fac_street[index];
        var theFaccity = fac_city[index];
        var theFacstate = fac_state[index];
        do_lat(theFaclat);
        do_lng(theFaclng);
        document.edit_frm.frm_street.value = theFacstreet;
        document.edit_frm.frm_city.value = theFaccity;
        document.edit_frm.frm_state.value = theFacstate;
        }					// end function do_fac_to_loc
/**
 *
 * @param {type} text
 * @param {type} index
 * @returns {undefined}
 */
    function do_rec_fac_to_loc(text, index) {			// 9/22/09
        var recFaclat = rec_fac_lat[index];
        var recFaclng = rec_fac_lng[index];
        var recFacstreet = rec_fac_street[index];
        var recFaccity = rec_fac_city[index];
        var recFacstate = rec_fac_state[index];
        do_lat(recFaclat);
        do_lng(recFaclng);
        document.edit_frm.frm_toaddress = recFacstreet + ", " + recFaccity + ", " + recFacstate;
        }					// end function do_fac_to_loc

    </SCRIPT>
    </HEAD>
    <SCRIPT>
    <!-- <BODY onLoad = "ck_frames();"> -->

<?php

$query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$rec_fac_menu = "<SELECT NAME='frm_rec_fac' onChange='do_rec_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
$rec_fac_menu .= "<OPTION VALUE=0 selected>" . gettext('Receiving Facility') . "</OPTION>";
while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
        $sel = ($row_fc['id'] == $row['rec_facility']) ? "SELECTED" : "";
        $rec_fac_menu .= "<OPTION VALUE=" . $row_fc['id'] . " " . $sel . ">" . shorten($row_fc['name'], 30) . "</OPTION>";
        $rf_street = ($row_fc['street'] != "") ? $row_fc['street'] : "Empty";
        $rf_city = ($row_fc['city'] != "") ? $row_fc['city'] : "Empty";
        $rf_state = ($row_fc['state'] != "") ? $row_fc['state'] : "Empty";
        print "\trec_fac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;\n";
        print "\trec_fac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;\n";
        print "\trec_fac_street[" . $row_fc['id'] . "] = '" . $rf_street . "' ;\n";
        print "\trec_fac_city[" . $row_fc['id'] . "] = '" . $rf_city . "' ;\n";
        print "\trec_fac_state[" . $row_fc['id'] . "] = '" . $rf_state . "' ;\n";
        }
$rec_fac_menu .= "<SELECT>";

$query_fc2 = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
$result_fc2 = mysql_query($query_fc2) or do_error($query_fc2, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$orig_fac_menu = "<SELECT NAME='frm_orig_fac' onChange='do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());'>";
$orig_fac_menu .= "<OPTION VALUE=0 selected>" . gettext('Receiving Facility') . "</OPTION>";
while ($row_fc2 = mysql_fetch_array($result_fc2, MYSQL_ASSOC)) {
        $sel = ($row_fc2['id'] == $row['orig_facility']) ? "SELECTED" : "";
        $orig_fac_menu .= "<OPTION VALUE=" . $row_fc2['id'] . " " . $sel . ">" . shorten($row_fc2['name'], 30) . "</OPTION>";
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
<?php
$status_array = array('Open', 'Accepted', 'Resourced', 'Complete');
$status_sel = "<SELECT NAME='frm_status'>";
foreach ($status_array AS $val) {
    $sel = ($val == $row['status']) ? "SELECTED": "";
    $status_sel .= "<OPTION VALUE='" . $val . "' " . $sel . ">" . $val . "</OPTION>";
    }
$status_sel .= "</SELECT>";

$rec_facility = ($row['rec_facility'] != 0) ? get_facilityname($row['rec_facility']) : "Not Set";
$orig_facility = ($row['orig_facility'] != 0) ? get_facilityname($row['orig_facility']) : "Not Set";
$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
$now = time() - (intval(get_variable('delta_mins')*60));
$the_details = get_contact_details($row['requester']);
$contact_email_p = $the_details[1];
$contact_email_s = $the_details[2];
$contact_phone_p = $the_details[3];
$contact_phone_s = $the_details[4];

if (!empty($_POST)) {
    $meridiem_request_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_request_date'])))) ) ? "" : $_POST['frm_meridiem_request_date'] ;
    $request_date = "$_POST[frm_year_request_date]-$_POST[frm_month_request_date]-$_POST[frm_day_request_date] $_POST[frm_hour_request_date]:$_POST[frm_minute_request_date]:00$meridiem_request_date";
    $query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET
        `street` = " . quote_smart(trim($_POST['frm_street'])) . ",
        `city` = " . quote_smart(trim($_POST['frm_city'])) . ",
        `state` = " . quote_smart(trim($_POST['frm_state'])) . ",
        `the_name` = " . quote_smart(trim($_POST['frm_patient'])) . ",
        `phone` = " . quote_smart(trim($_POST['frm_phone'])) . ",
        `to_address` = " . quote_smart(trim($_POST['frm_toaddress'])) . ",
        `orig_facility` = " . quote_smart(trim($_POST['frm_orig_fac'])) . ",
        `rec_facility` = " . quote_smart(trim($_POST['frm_rec_fac'])) . ",
        `scope` = " . quote_smart(trim($_POST['frm_scope'])) . ",
        `description` = " . quote_smart(trim($_POST['frm_description'])) . ",
        `comments` = " . quote_smart(trim($_POST['frm_comments'])) . ",
        `request_date` = " . quote_smart(trim($request_date)) . ",
        `status` = " . quote_smart(trim($_POST['frm_status'])) . "
        WHERE `id` = " . $_POST['id'];
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    do_log($GLOBALS['LOG_EDIT_REQUEST'], $_POST['id']);

?>
    <BODY>
        <CENTER>
        <DIV id='confirmation'>
            <BR /><BR /><BR />
            <DIV><?php print gettext('Request Updated');?></DIV>
            <BR /><BR />
            <SPAN id='finish_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.get_requests(); window.close();"><?php print gettext('Finish');?></SPAN>
        </DIV>
        </CENTER>
    </BODY>
    </HTML>
<?php
    } else {
    $orig_fac_details = get_facilitydetails($row['orig_facility']);
    $rec_fac_details = get_facilitydetails($row['rec_facility']);
?>
    <BODY onLoad="startup(); location.href = '#top';">

    <DIV id='view' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
        <DIV id='banner' class='heading' style='font-size: 20px; position: relative; top: 5%; width: 100%; border: 1px outset #000000;'><?php print gettext('Tickets Service User Request');?></DIV><BR /><BR />
        <DIV id='leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
            <DIV id='left_scroller' style='position: relative; top: 0px; left: 0px; height: 90%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
                <TABLE style='width: 100%;'>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print gettext('Requested By');?></TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print gettext('Request Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print format_date($row['request_date']);?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['status'];?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Service User');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['the_name'];?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['street'];?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['city'];?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['state'];?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Destination Address');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['to_address'];?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['phone'];?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Email');?></TD><TD class='td_data' style='text-align: left;'><?php print $contact_email_p;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_facility;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_facility;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['scope'];?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['description'];?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Comments');?></TD><TD class='td_data' style='text-align: left;'><?php print $row['comments'];?></TD>
                    </TR>
                    <TR class='spacer'>
                        <TD class='spacer' COLSPAN=99></TD>
                    </TR>
                    <TR class='heading'>
                        <TD COLSPAN='2' class='heading' style='text-align: left;'>Status Times and Dates</TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Tentative Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $tentative_date;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Accepted Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $accepted_date;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Declined Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $declined_date;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Resourced Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $resourced_date;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Completed Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $completed_date;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Closed Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print $closed_date;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Updated by');?></TD><TD class='td_data' style='text-align: left;'><?php print $updated_by;?></TD>
                    </TR>
                    <TR class='spacer'>
                        <TD class='spacer' COLSPAN=99></TD>
                    </TR>
                </TABLE>
            </DIV><BR /><BR />
<?php
    if ($can_edit) {
?>
            <SPAN id='edit_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_edit();"><?php print gettext('Edit');?></SPAN>
            <SPAN id='req_can_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "do_cancel(<?php print $row['request_id'];?>);"><?php print gettext('Cancel Request');?></SPAN>
<?php
    }
    if ((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Declined'))) {
?>
            <SPAN id='tent_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "tentative(<?php print $id;?>);"><?php print gettext('Tentatively Accept and open Ticket');?></SPAN>
<?php
    }
    if ((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Declined'))) {
?>
            <SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "accept(<?php print $id;?>);"><?php print gettext('Accept and open Ticket');?></SPAN>
<?php
    }
    if ((!is_service_user()) && ($row['status'] == 'Tentative')) {
?>
            <SPAN id='accept_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "status_update(<?php print $id;?>, 'Accepted');"><?php print gettext('Accept');?></SPAN>
<?php
    }
    if ((!is_service_user()) && (($row['status'] == 'Open') || ($row['status'] == 'Tentative'))) {
?>
            <SPAN id='decline_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "decline(<?php print $id;?>);"><?php print gettext('Decline');?></SPAN>
<?php
    }
?>
            <SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.close();"><?php print gettext('Close');?></SPAN><BR /><BR />
        </DIV>
    </DIV>
    <DIV id='edit' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
        <DIV id='edit_banner' class='heading' style='font-size: 20px; position: relative; top: 5%; width: 100%; border: 1px outset #000000;'><?php print gettext('Edit Tickets Service User Request');?></DIV><BR /><BR />
        <DIV id='edit_leftcol' style='position: fixed; left: 2%; top: 8%; width: 96%; height: 90%;'>
            <DIV id='edit_left_scroller' style='position: relative; top: 0px; left: 0px; height: 90%; overflow-y: auto; overflow-x: hidden; border: 1px outset #000000;'>
                <FORM NAME='edit_frm' METHOD='POST' ACTION = "<?php print basename( __FILE__); ?>">
                <TABLE style='width: 100%;'>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print gettext('Requested By');?></TD><TD class='td_data' style='text-align: left;'><?php print get_user_name($row['requester']);?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print gettext('Request Date and Time');?></TD><TD class='td_data' style='text-align: left;'><?php print generate_date_dropdown('request_date',strtotime($row['request_date']),FALSE);?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Status');?></TD><TD class='td_data' style='text-align: left;'><?php print $status_sel;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Patient');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_patient' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['the_name'];?>"></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Street');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_street' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['street'];?>"></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('City');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_city' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['city'];?>"></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('State');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_state' TYPE='TEXT' SIZE='4' MAXLENGTH='4' VALUE="<?php print $row['state'];?>"></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Destination Address');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_toaddress' TYPE='TEXT' SIZE='24' MAXLENGTH='128' VALUE="<?php print $row['to_address'];?>"></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Phone');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_phone' TYPE='TEXT' SIZE='16' MAXLENGTH='16' VALUE="<?php print $row['phone'];?>"></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Originating Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $orig_fac_menu;?></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Receiving Facility');?></TD><TD class='td_data' style='text-align: left;'><?php print $rec_fac_menu;?></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Scope');?></TD><TD class='td_data' style='text-align: left;'><INPUT NAME='frm_scope' TYPE='TEXT' SIZE='24' MAXLENGTH='64' VALUE="<?php print $row['scope'];?>"></TD>
                    </TR>
                    <TR class='odd'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Description');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_description" COLS="45" ROWS="2" WRAP="virtual"><?php print $row['description'];?></TEXTAREA></TD>
                    </TR>
                    <TR class='even'>
                        <TD class='td_label' style='text-align: left;'><?php print get_text('Comments');?></TD><TD class='td_data' style='text-align: left;'><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"><?php print $row['comments'];?></TEXTAREA></TD>
                    </TR>
                    <TR class='spacer'>
                        <TD class='spacer' COLSPAN=99></TD>
                    </TR>
                </TABLE>
                <INPUT NAME='requester' TYPE='hidden' SIZE='24' VALUE="<?php print $_SESSION['user_id'];?>">
                <INPUT NAME='id' TYPE='hidden' SIZE='24' VALUE="<?php print $id;?>">
                <INPUT NAME='frm_lat' TYPE='hidden' SIZE='10' VALUE="<?php print $row['lat'];?>" />
                <INPUT NAME='frm_lng' TYPE='hidden' SIZE='10' VALUE="<?php print $row['lng'];?>" />
            </DIV><BR /><BR />
            <SPAN id='sub_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "validate(document.edit_frm);"><?php print gettext('Update');?></SPAN>
            <SPAN id='close_but' CLASS ='plain' style='float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "window.opener.get_requests(showall); window.close();"><?php print gettext('Cancel');?></SPAN><BR /><BR />
            </FORM>
        </DIV>
    </DIV>
    <DIV id='waiting' style='display: none; text-align: center;'></DIV>
    <DIV id='result' style='position: absolute; width: 95%; text-align: center; margin: 10px;'>
        <DIV id='done'></DIV>
    </DIV>
<?php
}
?>
</BODY>
</HTML>
