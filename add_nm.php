<?php

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
    header("Location: index.php");
    }
/*
if (!($_SESSION['internet'])) {				// 8/22/10
    header("Location: add_nm.php");
    }
*/
require_once 'incs/functions.inc.php';		//7/28/10
do_login(basename(__FILE__));
$gmaps = $_SESSION['internet'];
if ($istest) {print "_GET"; dump($_GET);}
if ($istest) {print "_POST"; dump($_POST);}

/*
10/28/07 added onLoad = "document.add.frm_lat.disabled..
11/38/07 added frame jump prevention
11/98/07 added map under image
5/29/08  added do_kml() call
8/11/08	 added problem-start lock/unlock
8/23/08	 added usng handling
8/23/08  corrected problem-end hskpng
9/9/08	 added lat/lng-to-CG format functions
10/4/08	 added function do_inc_name()
10/7/08	 set WRAP="virtual"
10/8/08 synopsis made non-mandatory
10/15/08 changed 'Comments' to 'Disposition'
10/16/08 changed ticket_id to frm_ticket_id
10/17/08 removed 10/16/08 change
10/19/08 added insert_id to description
12/6/08 allow user input of NGS values; common icon marker function
1/11/09 TBD as default, auto_route setting option
1/17/09 replaced ajax functions - for consistency
1/18/09 added script-specific CONSTANTS
1/19/09 added geocode function
1/21/09 show/hide butts
1/22/09 - serial no. to ticket description
1/25/09 serial no. pre-set
1/27/09 area code vaiable added
2/4/09  added function get_res_row()
2/10/09 added function sv_win()
2/11/09 added dollar function, streetview functions
3/3/09 cleaned trash as page bottom
3/10/09 intrusive space in ticket_id
4/30/09 $ replaces document.getElementById, USNG text underline
7/7/09	added protocol handling
7/16/09	zero to in_types_id
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
8/3/09 Added code to get locale variable and change USNG/UTM/UTM dependant on variable in tabs and sidebar.
8/13/09	'date' = now added to UPDATE
9/22/09 Added set Incident at a Facility functionality
9/29/09	'frequent fliers' added
10/1/09 added special ticket type - for pre-booked tickets
10/2/09	added locale check for WP lookup
10/6/09 Added Mouseover help text to all field labels.
10/6/09 Added Receiving Facility, added links button
10/12/09 Incident at facility menu is hidden by default - click radio button to show.
10/13/09 Added reverse geocoding - map click now returns address and location to form.
11/01/09 Added use of reverse_geo setting to switch off reverse geocoding if not required - default is off.
11/06/09 Changed "Special" incident type to "Scheduled".
11/06/09 Moved both Facility dropdown menus to the same area
12/16/09 added call-history operation
1/3/10 added '_by' field for multi-user call-taker id
3/13/10 present constituents 'miscellaneous'
3/18/10 corrections to facilities options list
3/24/10 made facilities input conditioned on existence, logging revised
4/21/10 provided for changed NOC/name values - per AF  email
4/27/10 try geo-code on failed phone lookup
5/6/10 accommodate embedded quotes
6/20/10 handle negative delta's, NULL forced, 'NULL' un-quoted
6/25/10 guest/member notification changed
6/26/10 911 field handling added
7/5/10 Revised reverse geocoding function - per AH
7/11/10 'NULL'  to 0
7/22/10 miscjs, google reverse geocode parse added
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/7/10 protocol reset house-keeping
8/13/10 map.setUIToDefault(), get_text settings
9/30/10 use '_by' as the match identifier, booking button name disambiguated
10/21/10 onload focus(), tabindex added
11/5/10 revised to prepare for callerid handling
11/13/10 incident numbering added
11/23/10 'state' size made locale-dependent
11/29/10 locale 2 handling added
12/1/10 get_text changes
12/18/10 set signals added
1/1/11 Titles array added, scheduled incidents revised
5/5/11 added get_new_colors()
6/4/2013 added broadcast()
10/11/2013 - corrected auto incident numbering - relocated else {} closure
*/

$api_key = get_variable('gmaps_api_key');

$current_facilities = array();												// 9/22/09
$query_f = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `id`";		// types in use
$result_f = mysql_query($query_f) or do_error($query_f, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row_f = stripslashes_deep(mysql_fetch_assoc($result_f))) {
    $current_facilities [$row_f['id']] = array ($row_f['name'], $row_f['lat'], $row_f['lng']);
    }
$facilities = mysql_affected_rows();		// 3/24/10

/**
 * get_res_row
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
function get_res_row() {				// writes empty ticket if none exists - returns a row - 11/5/10
    $by = $_SESSION['user_id'];			// 5/27/10

    $query  = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`
        WHERE `status`= '{$GLOBALS['STATUS_RESERVED']}'
        AND  `_by` = '{$by}' LIMIT 1";

    $result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) == 1) {							// any ?
        $row = stripslashes_deep(mysql_fetch_array($result));	// yes, return it
        }
    else {				// insert empty STATUS_RESERVED row
        $query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
                `id` , `in_types_id` , `contact` , `street` , `city` , `state` , `phone` , `lat` , `lng` , `date` ,
                `problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` ,
                `severity` , `updated`, `booked_date`, `_by`
            ) VALUES (
                NULL , 0, 0, NULL , NULL , NULL , NULL , NULL , NULL , NULL ,
                NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL, NULL, $by
            )";

        $result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
        }

    $result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    $row = stripslashes_deep(mysql_fetch_assoc($result));			// get the reserved row

    return $row;													// and return it - 11/5/10

    }						// end function get_res_row()

$get_add = ((empty($_GET) || ((!empty($_GET)) && (empty ($_GET['add'])))) ) ? "" : $_GET['add'] ;

    if ($get_add == 'true') {

/**
 * updt_ticket
 * Insert description here
 *
 * @param $id
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function updt_ticket($id) {							/* 1/25/09 */
            global $addrs, $NOTIFY_TICKET;

            $post_frm_meridiem_problemstart = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_problemstart'])))) ) ? "" : $_POST['frm_meridiem_problemstart'] ;
            $post_frm_meridiem_booked_date = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_meridiem_booked_date'])))) ) ? "" : $_POST['frm_meridiem_booked_date'] ; //10/1/09
            $post_frm_affected = ((empty($_POST) || ((!empty($_POST)) && (empty ($_POST['frm_affected'])))) ) ? "" : $_POST['frm_affected'] ;

            $_POST['frm_description'] 	= strip_html($_POST['frm_description']);		//clean up HTML tags
            $post_frm_affected 	 		= strip_html($post_frm_affected);
            $_POST['frm_scope']			= strip_html($_POST['frm_scope']);

            if (!get_variable('military_time')) {		//put together date from the dropdown box and textbox values
                if ($post_frm_meridiem_problemstart == 'pm') {
                    $post_frm_meridiem_problemstart	= ($post_frm_meridiem_problemstart + 12) % 24;
                    }
                }

            if (!get_variable('military_time')) {		//put together date from the dropdown box and textbox values
                if ($post_frm_meridiem_booked_date == 'pm') {
                    $post_frm_meridiem_booked_date	= ($post_frm_meridiem_booked_date + 12) % 24;
                    }
                }

            if (empty($post_frm_owner)) {$post_frm_owner=0;}
            $frm_problemstart = "$_POST[frm_year_problemstart]-$_POST[frm_month_problemstart]-$_POST[frm_day_problemstart] $_POST[frm_hour_problemstart]:$_POST[frm_minute_problemstart]:00$post_frm_meridiem_problemstart";

            if ($_POST['frm_status'] == 3) {
                $frm_booked_date = "$_POST[frm_year_booked_date]-$_POST[frm_month_booked_date]-$_POST[frm_day_booked_date] $_POST[frm_hour_booked_date]:$_POST[frm_minute_booked_date]:00$post_frm_meridiem_booked_date";
                } else {
//				$frm_booked_date = "NULL";
                $frm_booked_date = "";		// 6/20/10
                }

            if (!get_variable('military_time')) {			//put together date from the dropdown box and textbox values
                if ($post_frm_meridiem_problemstart == 'pm') {
                    $_POST['frm_hour_problemstart'] = ($_POST['frm_hour_problemstart'] + 12) % 24;
                    }
                if (isset($_POST['frm_meridiem_problemend'])) {
                    if ($_POST['frm_meridiem_problemend'] == 'pm') {
                        $_POST['frm_hour_problemend'] = ($_POST['frm_hour_problemend'] + 12) % 24;
                        }
                    }
                if (isset($_POST['frm_meridiem_booked_date'])) {	//10/1/09
                    if ($_POST['frm_meridiem_booked_date'] == 'pm') {
                        $_POST['frm_hour_booked_date'] = ($_POST['frm_hour_booked_date'] + 12) % 24;
                        }
                    }
                }
            $frm_problemend  = (isset($_POST['frm_year_problemend'])) ?  quote_smart("$_POST[frm_year_problemend]-$_POST[frm_month_problemend]-$_POST[frm_day_problemend] $_POST[frm_hour_problemend]:$_POST[frm_minute_problemend]:00") : "NULL";

            $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60))); // 6/20/10
            if (empty($post_frm_owner)) {$post_frm_owner=0;}

            $inc_num_ary = unserialize (get_variable('_inc_num'));					// 11/13/10
             $name_rev = $_POST['frm_scope'];
            if ($inc_num_ary[0] == 0) {											// no auto numbering scheme
                switch (get_variable('serial_no_ap')) {								// incident name revise -1/22/09

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
                    }				// end switch
                                                            // 8/23/08, 9/20/08, 8/13/09
                }		// end if()

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
//			$booked_date = empty($frm_booked_date)? "NULL" : quote_smart(trim($frm_booked_date)) ;	// 6/20/10
            $booked_date = (intval($frm_do_scheduled)==1)?  quote_smart(trim($frm_booked_date)): "NULL" ;	// 1/1/11
                                                                                                    // 6/26/10
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
                do_log($GLOBALS['LOG_FACILITY_INCIDENT_OPEN'], $id, '' ,0 ,$facility_id);	// - 7/11/10
                }
            if (intval($rec_facility_id) >  0) {
                do_log($GLOBALS['LOG_CALL_REC_FAC_SET'], $id, 0 ,0 ,0 ,$rec_facility_id);	// 6/20/10 - 7/11/10
                }

            $the_year = date("y");
            if ((((int) $inc_num_ary[0]) == 3) && (!($inc_num_ary[5] == $the_year))) {				// year style and change?
                $inc_num_ary[3] = 1;																// roll over and start at 1
                $inc_num_ary[5] = $the_year;
                }
            else {
                if (((int) $inc_num_ary[0])>0) {		// step to next no. if scheme in use
                    $inc_num_ary[3]++;				// do the deed for next use
                    }
                }			// end if/else - 10/11/2013
            $out_str = serialize ($inc_num_ary);
            $query = "UPDATE`$GLOBALS[mysql_prefix]settings` SET `value` = '$out_str' WHERE `name` = '_inc_num'";
            $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

            return $name_rev;
            }				// end function updt ticket()

        $ticket_name = updt_ticket(trim($_POST['ticket_id']));				// 1/25/09
?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <HEAD><TITLE><?php print gettext('Tickets - Add Module');?></TITLE>
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
/**
 *
 * @returns {undefined}
 */
    function get_new_colors() {				// 5/5/11
        window.location.href = '<?php print basename(__FILE__);?>';
        }

/**
 *
 * @returns {unresolved}
 */
    function do_notify() {

        var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
        var theText= "TICKET - New: ";
        var theId = '<?php print $_POST['ticket_id'];?>';

//		mail_it ($to_str, $text, $theId, $text_sel=1;, $txt_only = FALSE)

        var params = "frm_to="+ escape(theAddresses) + "&frm_text=" + escape(theText) + "&frm_ticket_id=" + theId + "&text_sel=1";		// ($to_str, $text, $ticket_id)   10/15/08

        sendRequest ('mail_it.php',handleResult, params);	// ($to_str, $text, $ticket_id)   10/15/08
        }			// end function do notify()
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
    function handleResult(req) {				// the 'called-back' function
<?php
        if ($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
        }
/**
 *
 * @param {type} my_form
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
<?php
    if ($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>

                return;
                }
            callback(req);
            }
        if (req.readyState == 4) return;
        req.send(postData);
        }
/**
 *
 * @type Array|Array
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
            try { xmlhttp = XMLHttpFactories[i](); }
            catch (e) { continue; }
            break;
            }

        return xmlhttp;
        }
<?php
        }				// end if ($addrs)
    else {
?>
/**
 *
 * @returns {unresolved}
 */
  function do_notify() {	// dummy

        return;
        }
<?php

        }				// end if/else ($addrs)

    $form_name = (intval(get_variable('auto_route'))==1)? "to_routes" : "to_main";
?>

        </SCRIPT>
        </HEAD>
    <BODY onLoad = "do_notify();document.<?php print $form_name;?>.submit();">
<?php
    $now = time() - (intval(get_variable('delta_mins')*60));		// 6/20/10

    print "<BR /><BR /><BR /><CENTER><FONT CLASS='header'>Ticket: '{$ticket_name}  ' Added by '{$_SESSION['user_id']}' at " . date(get_variable("date_format"),$now) . "</FONT></CENTER><BR /><BR />";
?>
    <FORM NAME='to_main' METHOD='post' ACTION='main.php'>
    <CENTER><INPUT TYPE='submit' VALUE='<?php print gettext('Main');?>' />
    </FORM>

    <FORM NAME='to_routes' METHOD='get' ACTION='routes.php'>
    <INPUT TYPE='hidden' NAME='ticket_id' VALUE='<?php print $_POST['ticket_id'];?>' />
    <INPUT TYPE='submit' VALUE='<?php print gettext('Routes');?>' /></CENTER>
    </FORM>
<?php
        }				// end if ($_GET['add'] ...
//					==============================================
    else {
        if (is_guest() && !get_variable('guest_add_ticket')) {		// 6/25/10
            print '<FONT CLASS="warn">' . gettext('Guest/member users may not add tickets on this system.  Contact administrator for further information.') . '</FONT>';
            exit();
            }

    $res_row = get_res_row();				// 11/5/10

    $ticket_id = $res_row['id'];
//	$hints = get_hints("a");

    $nature = get_text("Nature");				// 12/1/10	{$nature}
    $disposition = get_text("Disposition");		// 	{$disposition}
    $patient = get_text("Patient");				// 	{$patient}
    $incident = get_text("Incident");			// 	{$incident}
    $incidents = get_text("Incidents");			// 	{$incidents}


    $titles = array();				// 1/1/11
    $titles["a1"] = gettext("Location - type in location in fields, click location on map or use *Located at Facility* menu below");
    $titles["a2"] = gettext("City - defaults to default city set in configuration. Type in City if required");
    $titles["a3"] = gettext("State - US State or non-US Country code e.g. UK for United Kingdom");
    $titles["a4"] = gettext("Phone number - for US only, you can use the lookup button to get the callers name and location using the White Pages");
    $titles["a5"] = gettext("{$incident}  {$nature} or Type - Available types are set in in_types table in the configuration");
    $titles["a6"] = gettext("{$incident}  Priority - Normal, Medium or High. Affects order and coloring of {$incidents} on Situation display");
    $titles["a7"] = gettext("{$incident} Protocol - this will show automatically if a protocol is set for the {$incident} Type in the configuration");
    $titles["a8"] = gettext("Synopsis - Details about the {$incident}, ensure as much detail as possible is completed");
    $titles["a9"] = gettext("911 contact information");
    $titles["a10"] = gettext("Caller reporting the {$incident}");
    $titles["a11"] = gettext("{$incident} Name - Partially completed and prepend or append incident ID depending on setting. Type in an easily identifiable name.");
    $titles["a12"] = gettext("Scheduled Date. Must be set if {$incident} Status is *Scheduled*. Sets date and time for a future booked {$incident}, mainly used for non immediate {$patient} transport. Click on Radio button to show date fields.");
    $titles["a13"] = gettext("Use the first dropdown menu to select the Facility where the {$incident} is located at, use the second dropdown menu to select the facility where persons from the {$incident} will be received");
    $titles["a14"] = gettext("Run-start, {$incident}  start time. Defaults to current date and time or edit by clicking padlock icon to enable date & time fields");
    $titles["a15"] = gettext("{$incident}  Status - Open or Closed or set to Scheduled for future booked calls");
    $titles["a16"] = gettext("Run-end, {$incident}  end time. When {$incident} is closed, click on radio button which will enable date & time fields");
    $titles["a17"] = gettext("Disposition - additional comments about {$incident}");
    $titles["a18"] = gettext("{$incident} Lat/Lng - set by clicking on the map for the location or by selecting location with the address fields.");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Add Module');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL="StyleSheet" HREF="default.css" TYPE="text/css" />
<?php
    if ($gmaps) {
		$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
		if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
			$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
			} else {
			$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
			}
?>
<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT>
<SCRIPT SRC="./js/graticule.js" type="text/javascript"></SCRIPT>
<?php
        }
?>
<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC='./js/jscoord.js' TYPE="text/javascript"></SCRIPT>		<!-- coordinate conversion 12/10/10 -->
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT> <!-- 7/22/10 -->

<SCRIPT>
/**
 *
 * @returns {unresolved}
 */
    function ck_frames() {		// onLoad = "ck_frames()"

        return;
        if (self.location.href==parent.location.href) {
            self.location.href = 'index.php';
            }
/*	document.onkeypress=function (e) {
         var e=window.event || e
         alert("CharCode value: "+e.charCode)
         alert("Character: "+String.fromCharCode(e.charCode))
        }
*/
        }		// end function ck_frames()

    parent.frames["upper"].$("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
    parent.frames["upper"].$("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
    parent.frames["upper"].$("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";

    var lat_lng_frmt = <?php print get_variable('lat_lng'); ?>;				// 9/9/08
/**
 *
 * @returns {Array}
 */
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
/**
 *
 * @param {type} str
 * @returns {Boolean}
 */
    function isNullOrEmpty(str) {
        if (null == str || "" == str) {return true;} else { return false;}
        }
    var starting = false;
/**
 *
 * @param {type} theForm
 * @returns {unresolved}
 */
    function sv_win(theForm) {				// 2/11/09
        if (starting) {return;}				// dbl-click proof
        starting = true;

        var thelat = theForm.frm_lat.value;
        var thelng = theForm.frm_lng.value;
        var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
        newwindow_sl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
        if (!(newwindow_sl)) {
            alert ("<?php print gettext('Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.');?>");

            return;
            }
        newwindow_sl.focus();
        starting = false;
        }		// end function sv win()

/**
 *
 * @returns {unresolved}
 */
    String.prototype.trim = function () {
        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @param {type} val
 * @returns {unresolved}
 */
    function chknum(val) {
        return ((val.trim().replace(/\D/g, "")==val.trim()) && (val.trim().length>0));}
/**
 *
 * @param {type} val
 * @param {type} lo
 * @param {type} hi
 * @returns {@exp;@call;chknum}
 */
    function chkval(val, lo, hi) {
        return  (chknum(val) && !((val> hi) || (val < lo)));}


    starting=false;						// 12/16/09
/**
 *
 * @returns {unresolved}
 */
    function do_hist_win() {
        if (starting) {return;}
        var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
<?php
    if (get_variable("locale") ==0) {				// USA only
?>
        if (goodno.length<10) {
            alert("<?php print gettext('10-digit phone no. required - any format');?>");

            return;}
<?php
        }		// end locale check
?>
        starting=true;
        var url = "call_hist.php?frm_phone=" + goodno;
        newwindow_c_h=window.open(url, "Call_hist",  "titlebar, resizable=1, scrollbars, height=640,width=760,status=0,toolbar=0,menubar=0,location=0, left=50,top=150,screenX=100,screenY=300");
        if (isNullOrEmpty(newwindow_c_h)) {
            starting = false;
            alert ("<?php print gettext('Call history operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        newwindow_c_h.focus();
        starting = false;
        }		// function do hist_win()
/**
 *
 * @param {type} inlat
 * @param {type} inlng
 * @returns {unresolved}
 */
    function do_coords(inlat, inlng) { 										 //9/14/08
        if ((inlat.length==0)||(inlng.length==0)) {return;}
        var str = inlat + ", " + inlng + "\n";
        str += ll2dms(inlat) + ", " +ll2dms(inlng) + "\n";
        str += lat2ddm(inlat) + ", " +lng2ddm(inlng);
        alert(str);
        }
/**
 *
 * @param {type} inval
 * @returns {String}
 */
    function ll2dms(inval) {				// lat/lng to degr, mins, sec's - 9/9/08
        var d = new Number(Math.abs(inval));
        d  = Math.floor(d);
        var mi = (Math.abs(inval)-d)*60;	// fraction * 60
        var m = Math.floor(mi);				// min's as fraction
        var si = (mi-m)*60;					// to sec's
        var s = si.toFixed(1);

        return d + '\260 ' + Math.abs(m) +"' " + Math.abs(s) + '"';
        }
/**
 *
 * @param {type} inlat
 * @returns {String}
 */
    function lat2ddm(inlat) {				//  lat to degr, dec.min's - 9/9/089/7/08
        var x = new Number(Math.abs(inlat));
        var degs  = Math.floor(x);				// degrees
        var mins = ((Math.abs(x-degs)*60).toFixed(1));
        var nors = (inlat>0.0)? " N":" S";

        return degs + '\260'  + mins +"'" + nors;
        }
/**
 *
 * @param {type} inlng
 * @returns {String}
 */
    function lng2ddm(inlng) {				//  lng to degr, dec.min's - 9/9/089/7/08
        var x = new Number(Math.abs(inlng));
        var degs  = Math.floor(x);				// degrees
        var mins = ((Math.abs(x-degs)*60).toFixed(1));
        var eorw = (inlng>0.0)? " E":" W";

        return degs + '\260' + mins +"'" + eorw;
        }
/**
 *
 * @param {type} inlat
 * @returns {String}
 */
    function do_lat_fmt(inlat) {				// 9/9/08
        switch (lat_lng_frmt) {
            case 0:
                return inlat;
                  break;
            case 1:
                return ll2dms(inlat);
                  break;
            case 2:
                return lat2ddm(inlat);
                 break;
            default:
                alert ( "error 518");
            }
        }
/**
 *
 * @param {type} inlng
 * @returns {String}
 */
    function do_lng_fmt(inlng) {
        switch (lat_lng_frmt) {
            case 0:
                return inlng;
                  break;
            case 1:
                return ll2dms(inlng);
                  break;
            case 2:
                return lng2ddm(inlng);
                 break;
            default:
                alert ("error 534");
            }
        }

    var map;						// note globals
    var geocoder = null;
    var rev_coding_on;	// 11/01/09
//	geocoder = new GClientGeocoder();
    var request;
    var querySting;   				// will hold the POSTed data
    var tab1contents;				// info window contents - first/only tab
    var grid = false;				// toggle
    var thePoint;
    var baseIcon;
    var cross;
/**
 *
 * @param {type} content
 * @returns {undefined}
 */
    function writeConsole(content) {
        top.consoleRef=window.open('','myconsole',
            'width=800,height=250' +',menubar=0' +',toolbar=0' +',status=0' +',scrollbars=1' +',resizable=1');
         top.consoleRef.document.writeln('<html><head><title><?php print gettext('Console');?></title></head>'
            +'<body bgcolor=white onLoad="self.focus();">' +content +'</body></html>'
            );				// end top.consoleRef.document.writeln()
         top.consoleRef.document.close();
        }				// end function writeConsole(content)
/**
 *
 * @returns {String}
 */
    function getRes() {
        return window.screen.width + ' x ' + window.screen.height;
        }
/**
 *
 * @returns {undefined}
 */
    function toglGrid() {						// toggle
        grid = !grid;
        if (!grid) {							// check prior value
            map.clearOverlays();
            }
        else {
            map.closeInfoWindow();
            map.addOverlay(new LatLonGraticule());
            }
        if (thePoint) {map.addOverlay(new GMarker(thePoint));}	// restore it
        }		// end function toglGrid()
/**
 *
 * @returns {undefined}
 */
    function clearmap() {
<?php
    if (!($gmaps)) {
        print"\n\t return;\n";
        }
?>
        map.clearOverlays();
        load(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>, <?php echo get_variable('def_zoom'); ?>);
        if (grid) {map.addOverlay(new LatLonGraticule());}
        }
/**
 *
 * @param {type} lat
 * @param {type} lng
 * @param {type} zoom
 * @returns {undefined}
 */
    function do_marker(lat, lng, zoom) {		// 9/16/08 - 12/6/08
        map.clearOverlays();
        var center = isNullOrEmpty(lat)?  GLatLng(map.getCenter()) : new GLatLng(lat, lng);
        var myzoom = isNullOrEmpty(zoom)? map.getZoom(): zoom;
        map.setCenter(center, myzoom);
        thisMarker  = new GMarker(center, {icon: cross});				// 9/16/08
        map.addOverlay(thisMarker);
        }

/**
 *
 * @returns {undefined}
 */
    function domap() {										// called from phone, addr lookups
        map = new GMap2($('map'));
<?php
$maptype = get_variable('maptype');	// 08/02/09

    switch ($maptype) {
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
        print gettext("ERROR in") . ": " . basename(__FILE__) . " " . __LINE__ . "<BR />";
    }
?>

        $("map").style.backgroundImage = "url(./markers/loading.jpg)";
//		map.addControl(new GSmallMapControl());
        map.setUIToDefault();										// 8/13/10

        map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
//		map.addMapType(G_SATELLITE_3D_MAP);

        map.setCenter(new GLatLng(document.add.frm_lat.value, document.add.frm_lng.value), <?php echo get_variable('def_zoom'); ?>);			// larger # => tighter zoom
        map.addControl(new GOverviewMapControl());
        map.enableScrollWheelZoom();
        do_marker(null, null, null)	;		// 12/6/08

        var sep = (document.add.frm_street.value=="")? "": ", ";
        var tab1contents = "<B>" + document.add.frm_contact.value + "</B>" +
            "<BR/>"+document.add.frm_street.value + sep +
            document.add.frm_city.value +" " +
            document.add.frm_state.value;

        GEvent.addListener(map, "click", function (marker, point) {		// lookup
            if (marker) {
                map.removeOverlay(marker);
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=false;
                document.add.frm_lat.value=document.add.frm_lng.value="";
//				document.add.frm_lat.disabled=document.add.frm_lat.disabled=true;
                if (grid) {map.addOverlay(new LatLonGraticule());}

                }
            if (point) {
                map.clearOverlays();
                do_lat (point.lat());				// display
                do_lng (point.lng());
                do_grids(document.add);
                map.addOverlay(new GMarker(point));	// GLatLng.
                map.openInfoWindowHtml(point,tab1contents);
                if (grid) {map.addOverlay(new LatLonGraticule());}
                }
                getAddress(marker, point);				// 10/13/09
            });				// end GEvent.addListener()
        if (grid) {map.addOverlay(new LatLonGraticule());}
        $("lock_p").style.visibility = "visible";
        }				// end function do map()
/**
 *
 * @param {type} the_lat
 * @param {type} the_lng
 * @param {type} the_zoom
 * @returns {undefined}
 */
    function load(the_lat, the_lng, the_zoom) {				// onLoad function - 4/28/09
<?php
    if (!($gmaps)) {
        print "\n\t return;\n";
        }
?>
        if (GBrowserIsCompatible()) {
/**
 *
 * @param {type} lng
 * @param {type} lat
 * @param {type} radius
 * @returns {undefined}
 */
            function drawCircle(lng,lat,radius) { 			// drawCircle(-87.628092,41.881906,2);
                var cColor = "#3366ff";
                var cWidth = 2;
                var Cradius = radius;
                var d2r = Math.PI/180;
                var r2d = 180/Math.PI;
                var Clat = (Cradius/3963)*r2d;
                var Clng = Clat/Math.cos(lat*d2r);
                var Cpoints = [];
                for (var i=0; i < 33; i++) {
                    var theta = Math.PI * (i/16);
                    Cx = lng + (Clng * Math.cos(theta));
                    Cy = lat + (Clat * Math.sin(theta));
                    var P = new GPoint(Cx,Cy);				// note long, lat order
                    Cpoints.push(P);
                    };
                map.addOverlay(new GPolyline(Cpoints,cColor,cWidth));
                }
            map = new GMap2($('map'));
<?php
$maptype = get_variable('maptype');	// 08/02/09

    switch ($maptype) {
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

//			map.addControl(new GSmallMapControl());
            map.setUIToDefault();										// 8/13/10
            map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
            baseIcon = new GIcon();				//
            baseIcon.iconSize=new GSize(32,32);
            baseIcon.iconAnchor=new GPoint(16,16);
            cross = new GIcon(baseIcon, "./markers/crosshair.png", null);

            do_marker(the_lat, the_lng, the_zoom);		// 12/6/08

            GEvent.addListener(map, "click", function (marker, point) {
                if (marker) {									// undo it
                    map.removeOverlay(marker);
                    thePoint = "";
                    document.add.frm_lat.value=document.add.frm_lng.value="";
                    if (grid) {map.addOverlay(new LatLonGraticule());}
                    }
                if (point) {
                    $("do_sv").style.display = "block";
                    map.clearOverlays();
                    do_lat (point.lat().toFixed(6));				// display
                    do_lng (point.lng().toFixed(6));
                    do_grids(document.add);
                    do_marker(point.lat(), point.lng(), null);		// 12/6/08
                    thePoint = point;
                    if (grid) {map.addOverlay(new LatLonGraticule());}
                    }
                getAddress(marker, point);
                });
                 document.add.show_lat.disabled=document.add.show_lng.disabled=true;

<?php
            do_kml();
?>
            }			// end if (GBrowserIsCompatible())

        }			// end function load()
/**
 *
 * @param {type} plaintext
 * @returns {String}
 */
    function URLEncode(plaintext) {					// The Javascript escape and unescape functions do
                                                        // NOT correspond with what browsers actually do...
        var SAFECHARS = "0123456789" +					// Numeric
                        "ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
                        "abcdefghijklmnopqrstuvwxyz" +	// guess
                        "-_.!~*'()";					// RFC2396 Mark characters
        var HEX = "0123456789ABCDEF";

        var encoded = "";
        for (var i = 0; i < plaintext.length; i++) {
            var ch = plaintext.charAt(i);
            if (ch == " ") {
                encoded += "+";				// x-www-urlencoded, rather than %20
            } else if (SAFECHARS.indexOf(ch) != -1) {
                encoded += ch;
            } else {
                var charCode = ch.charCodeAt(0);
                if (charCode > 255) {
                    alert( "<?php print gettext('Unicode Character');?> '"
                            + ch
                            + "' <?php print gettext('cannot be encoded using standard URL encoding.');?>\n" +
                              "(<?php print gettext('URL encoding only supports 8-bit characters.');?>)\n" +
                              "<?php print gettext('A space (+) will be substituted.');?>" );
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
/**
 *
 * @param {type} encoded
 * @returns {String}
 */
    function URLDecode(encoded) {   					// Replace + with ' '
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
                    alert( '-- <?php print gettext('invalid escape combination near');?> ...' + encoded.substr(i) );
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
/**
 *
 * @param {type} lat
 * @returns {undefined}
 */
    function do_lat(lat) {
        document.add.frm_lat.value=lat;			// 9/9/08
        document.add.show_lat.disabled=false;				// permit read/write
        document.add.show_lat.value=do_lat_fmt(document.add.frm_lat.value);
        document.add.show_lat.disabled=true;
        }
/**
 *
 * @param {type} lng
 * @returns {undefined}
 */
    function do_lng(lng) {
        document.add.frm_lng.value=lng;
        document.add.show_lng.disabled=false;
        document.add.show_lng.value=do_lng_fmt(document.add.frm_lng.value);
        document.add.show_lng.disabled=true;
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_grids(theForm) {								//12/13/10
        if (theForm.frm_ngs.value) {do_usng(theForm) ;}
        if (theForm.frm_utm) {do_utm (theForm);}
        if (theForm.frm_osgb) {do_osgb (theForm);}
        }
/**
 *
 * @returns {undefined}
 */
    function do_usng(theForm) {								// 8/23/08, 12/5/10
        theForm.frm_ngs.value = LLtoUSNG(theForm.frm_lat.value, theForm.frm_lng.value, 5);	// US NG
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_utm(theForm) {
        var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
        var utm_out = ll_in.toUTMRef().toString();
        temp_ary = utm_out.split(" ");
        theForm.frm_utm.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_osgb(theForm) {
        var ll_in = new LatLng(parseFloat(theForm.frm_lat.value), parseFloat(theForm.frm_lng.value));
        var osgb_out = ll_in.toOSRef();
        theForm.frm_osgb.value = osgb_out.toSixFigureString();
        }

// *********************************************************************
    var the_form;
/**
 *
 * @param {type} my_form
 * @param {type} url
 * @param {type} callback
 * @param {type} postData
 * @returns {unresolved}
 */
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
                alert('813: HTTP error ' + req.status);

                return;
                }
            callback(req);
            }
        if (req.readyState == 4) return;
        req.send(postData);
        }
/**
 *
 * @type Array|Array|Array|Array
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

// "Juan Wzzzzz;(123) 456-9876;1689 Abcd St;Abcdefghi;MD;16701;99.013297;-88.544775;"
//  1           2              3            4         5  6     7         8
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
    function handleResult(req) {									// the called-back phone lookup function
        var result=req.responseText.split(";");						// parse semic-separated return string
        $('repeats').innerHTML = "(" + result[0].trim() + ")";		// prior calls this phone no. - 9/29/09
        if (!(result.length>2)) {
<?php
    if (get_variable("locale") ==0) {				// USA only		// 10/2/09
?>
            alert("<?php print gettext('lookup failed');?>");
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
/**
 *
 * @returns {unresolved}
 */
    function phone_lkup() {
        var goodno = document.add.frm_phone.value.replace(/\D/g, "" );		// strip all non-digits - 1/18/09
<?php
    if (get_variable("locale") ==0) {				// USA only
?>
        if (goodno.length<10) {
            alert("<?php print gettext('10-digit phone no. required - any format');?>");

            return;}
<?php
        }		// end locale check
?>
        var params = "phone=" + URLEncode(goodno);
        sendRequest (document.add, 'wp_lkup.php',handleResult, params);		//1/17/09
        }

// *********************************************************************
/**
 *
 * @param {type} my_form
 * @param {type} lat
 * @param {type} lng
 * @returns {undefined}
 */
        function pt_to_map(my_form, lat, lng) {				// 1/19/09
            map.clearOverlays();								// 4/27/10

            my_form.frm_lat.value=lat;
            my_form.frm_lng.value=lng;

            my_form.show_lat.value=do_lat_fmt(my_form.frm_lat.value);
            my_form.show_lng.value=do_lng_fmt(my_form.frm_lng.value);

            my_form.frm_ngs.value=LLtoUSNG(my_form.frm_lat.value, my_form.frm_lng.value, 5);

            map.setCenter(new GLatLng(my_form.frm_lat.value, my_form.frm_lng.value), <?php print get_variable('def_zoom');?>);
            var marker = new GMarker(map.getCenter());		// marker to map center
            var myIcon = new GIcon();
            myIcon.image = "./markers/sm_red.png";
            map.removeOverlay(marker);

            map.addOverlay(marker, myIcon);
            }				// end function pt_to_map ()

// *********************************************************************
/**
 *
 * @param {type} my_form
 * @returns {Boolean}
 */
    function loc_lkup(my_form) {		   // added 1/19/09 -- getLocations(address,  callback -- not currently used )
        if ((my_form.frm_city.value.trim()==""  || my_form.frm_state.value.trim()=="")) {
            alert ("City and State are required for location lookup.");

            return false;
            }
        var geocoder = new GClientGeocoder();
//				"1521 1st Ave, Seattle, WA"
        var address = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();

        if (geocoder) {
            geocoder.getLatLng(
                address,
                function (point) {
                    if (!point) {
                        alert(address + " not found");
                        }
                    else {
                        pt_to_map (my_form, point.lat(), point.lng());
                        }
                    }
                );
            }
        }				// end function addrlkup()

// **************************************************** Reverse Geocoder 10/13/09, 7/5/10

    var geocoder;
    var address;
    var rev_coding_on = '<?php print get_variable('reverse_geo');?>';		// 7/5/10
/**
 *
 * @param {type} overlay
 * @param {type} latlng
 * @returns {undefined}
 */
    function getAddress(overlay, latlng) {		//7/5/10
        var geocoder = new GClientGeocoder();
        if (rev_coding_on == 1) {
            if (latlng != null) {
                geocoder.getLocations(latlng, function (response) {
                map.clearOverlays();
                if (response.Status.code != 200) {
                    alert("<?php print __LINE__;?>: address unavailable");
                } else {
                    place = response.Placemark[0];
                    point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
                     locality = response.Placemark[0].AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality;
                    marker = new GMarker(point);
                    map.addOverlay(marker);
                    results = pars_goog_addr(place.address);

                    document.add.frm_street.value = results[0];		// 7/22/10
                    document.add.frm_city.value = results[1] ;
                    document.add.frm_state.value = results[2];
                    document.add.frm_street.focus();		// 7/22/10

                    }
                });
                }
            }
        }				// end function getAddress()

// *****************************************************************************
    var tbd = "TBD";									// 1/11/09
    var user_inc_name = false;							// 4/21/10
/**
 *
 * @param {type} str
 * @param {type} indx
 * @returns {undefined}
 */
    function do_inc_name(str, indx) {								// 10/4/08, 7/7/09
//		if ((document.add.frm_scope.value.trim()=="") || (document.add.frm_scope.value.trim()==tbd)) {	// 1/11/09
        if (!(user_inc_name)) {							// any user input? - 4/21/10
            document.add.frm_scope.value += str+"/";	// 11/13/10
            }
        if (protocols[indx]) {
//			$('proto_row').style.display = "block";
            $('proto_cell').innerHTML = protocols[indx];
            }
        else {
            $('proto_cell').innerHTML = "";
            }
        }			// end function
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function datechk_s(theForm) {		// pblm start vs now
        var start = new Date();
        start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
        start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);
        var now = new Date();

        return (start.valueOf() <= now.valueOf());
        }
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function datechk_e(theForm) {		// pblm end vs now
        var end = new Date();
        end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
        end.setHours(theForm.frm_hour_problemend.value, theForm.frm_minute_problemend.value, 0,0);
        var now = new Date();

        return (end.valueOf() <= now.valueOf());
        }
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function datechk_r(theForm) {		// pblm start vs end
        var start = new Date();
        start.setFullYear(theForm.frm_year_problemstart.value, theForm.frm_month_problemstart.value-1, theForm.frm_day_problemstart.value);
        start.setHours(theForm.frm_hour_problemstart.value, theForm.frm_minute_problemstart.value, 0,0);

        var end = new Date();
        end.setFullYear(theForm.frm_year_problemend.value, theForm.frm_month_problemend.value-1, theForm.frm_day_problemend.value);
        end.setHours(theForm.frm_hour_problemend.value,theForm.frm_minute_problemend.value, 0,0);

        return (start.valueOf() <= end.valueOf());
        }
/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function validate(theForm) {	//
        do_unlock_ps(theForm);								// 8/11/08

        var errmsg="";
        if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_CLOSED'];?>) && (!theForm.re_but.checked))
                                                    {errmsg+= "\t<?php print gettext('Run end-date is required for Status=Closed');?>\n";}
        if ((theForm.frm_status.value==<?php print $GLOBALS['STATUS_OPEN'];?>) && (theForm.re_but.checked))
                                                    {errmsg+= "\t<?php print gettext('Run end-date not allowed for Status=Open');?>\n";}	// 9/30/10
        if (theForm.frm_in_types_id.value == 0) {errmsg+= "\t<?php print gettext('Nature of Incident is required');?>\n";}			// 1/11/09
        if (theForm.frm_contact.value == "") {errmsg+= "\t<?php print gettext('Reported-by is required');?>\n";}
        if (theForm.frm_scope.value == "") {errmsg+= "\t<?php print gettext('Incident name is required');?>\n";}
//		if (theForm.frm_description.value == "") {errmsg+= "\t<?php print gettext('Synopsis is required');?>\n";}
//		theForm.frm_lat.disabled=false;														// 9/9/08
<?php
    if ($gmaps) {
?>
        if ((theForm.frm_lat.value == 0) || (theForm.frm_lng.value == 0)) {errmsg+= "\t<?php print gettext('Map position is required');?>\n";}
<?php
            }
?>
        if (theForm.frm_status.value==<?php print $GLOBALS['STATUS_SCHEDULED'];?>) {		//10/1/09
            if (theForm.frm_year_booked_date.value == "NULL") {errmsg+= "\t<?php print gettext('Scheduled date time error - Hours');?>\n";}
            if (theForm.frm_minute_booked_date.value == "NULL") {errmsg+= "\t<?php print gettext('Scheduled date time error - Minutes');?>\n";}
            }

//		theForm.frm_lat.disabled=true;
        if (!chkval(theForm.frm_hour_problemstart.value, 0,23)) {errmsg+= "\t<?php print gettext('Run start time error - Hours');?>\n";}
        if (!chkval(theForm.frm_minute_problemstart.value, 0,59)) {errmsg+= "\t<?php print gettext('Run start time error - Minutes');?>\n";}
        if (!datechk_s(theForm)) {errmsg+= "\t<?php print gettext('Run start time error - future date');?>\n" ;}

        if (theForm.re_but.checked) {				// run end?
            do_unlock_pe(theForm);								// problemend values
            if (!datechk_e(theForm)) {errmsg+= "\t<?php print gettext('Run start time error - future');?>\n" ;}
            if (!datechk_e(theForm)) {errmsg+= "\t<?php print gettext('Run start time error - future');?>\n" ;}
            if (!datechk_r(theForm)) {errmsg+= "\t<?php print gettext('Run start time error - future');?>\n" ;}

            if (!chkval(theForm.frm_hour_problemend.value, 0,23)) {errmsg+= "\t<?php print gettext('Run end time error - Hours');?>\n";}
            if (!chkval(theForm.frm_minute_problemend.value, 0,59)) {errmsg+= "\t<?php print gettext('Run end time error - Minutes');?>\n";}
            }
        if (errmsg!="") {
            alert ("<?php print gettext('Please correct the following and re-submit');?>:\n\n" + errmsg);

            return false;
            }
        else {
            do_unlock_ps(theForm);								// 8/11/08
            theForm.frm_phone.value=theForm.frm_phone.value.replace(/\D/g, "" ); // strip all non-digits

            return true;
<?php						// 6/4/2013
        if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) { 		// 7/2/2013
?>																						/*	5/22/2013 */
            var theMessage = "New  <?php print get_text('Incident');?> (" + theForm.frm_scope.value + ") " + theAddr  + " by <?php echo $_SESSION['user'];?>";
            broadcast(theMessage ) ;
<?php
    }			// end if (broadcast)
?>
            theForm.submit();
            }
        }				// end function validate(theForm)
/**
 *
 * @param {type} text
 * @param {type} index
 * @returns {undefined}
 */
    function do_fac_to_loc(text, index) {			// 9/22/09
            var curr_lat = fac_lat[index];
            var curr_lng = fac_lng[index];
            do_lat(curr_lat);
            do_lng(curr_lng);
            load(curr_lat, curr_lng, <?php echo get_variable('def_zoom'); ?>);			// show it
            document.add.frm_lat.disabled=true;
            document.add.frm_lng.disabled=true;
    }					// end function do_fac_to_loc
/**
 *
 * @param {type} str
 * @returns {@exp;words@call;join}
 */
    function capWords(str) {
        var words = str.split(" ");
        for (var i=0 ; i < words.length ; i++) {
            var testwd = words[i];
            var firLet = testwd.substr(0,1);
            var rest = testwd.substr(1, testwd.length -1);
            words[i] = firLet.toUpperCase() + rest;
               }

        return( words.join(" "));
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_end(theForm) {			// enable run-end date/time inputs
        elem = $("runend1");
        elem.style.visibility = "visible";
<?php
        $show_ampm = (!get_variable('military_time')==1);
        if ($show_ampm) {	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
            print "\tdocument.add.frm_meridiem_problemend.disabled = false;\n";
            }
?>
        do_unlock_pe(theForm);								// problemend values
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_reset(theForm) {				// disable run-end date/time inputs
        clearmap();
        do_lock_ps(theForm);				// hskp problem start date
        do_lock_pe(theForm);				// hskp problem end date
        $("runend1").visibility = "hidden";
        $("lock_p").style.visibility = "visible";
        $("runend1").style.visibility = "hidden";
        theForm.frm_lat.value=theForm.frm_lng.value="";
        theForm.frm_do_scheduled.value=0;				// 1/1/11

        try {document.add.frm_ngs.disabled=true; }		// 4/30/09
            catch (err) {}
        try {$("USNG").style.textDecoration = '"none';}
            catch (err) {}
        $('booking1').style.visibility = 'hidden';
        $('td_misc').innerHTML ='';
        $('tr_misc').style.display='none';
        user_inc_name = false;							// no incident name input 4/21/10
        $('proto_cell').innerHTML = "";					// 8/7/10

        }		// end function reset()
/**
 *
 * @param {type} theForm
 * @param {type} theBool
 * @returns {undefined}
 */
    function do_problemstart(theForm, theBool) {							// 8/10/08
        theForm.frm_year_problemstart.disabled = theBool;
        theForm.frm_month_problemstart.disabled = theBool;
        theForm.frm_day_problemstart.disabled = theBool;
        theForm.frm_hour_problemstart.disabled = theBool;
        theForm.frm_minute_problemstart.disabled = theBool;
        if (theForm.frm_meridiem_problemstart) {theForm.frm_meridiem_problemstart.disabled = theBool;}
        }
/**
 *
 * @param {type} theForm
 * @param {type} theBool
 * @returns {undefined}
 */
    function do_problemend(theForm, theBool) {								// 8/10/08
        theForm.frm_year_problemend.disabled = theBool;
        theForm.frm_month_problemend.disabled = theBool;
        theForm.frm_day_problemend.disabled = theBool;
        theForm.frm_hour_problemend.disabled = theBool;
        theForm.frm_minute_problemend.disabled = theBool;
        if (theForm.frm_meridiem_problemend) {theForm.frm_meridiem_problemend.disabled = theBool;}
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_booking(theForm) {			// 10/1/09 enable booked date entry
        theForm.frm_do_scheduled.value=1;	// 1/1/11
        for (i=0;i<theForm.frm_status.options.length; i++) {
            if (theForm.frm_status.options[i].value == <?php print $GLOBALS['STATUS_SCHEDULED'];?>) {
                theForm.frm_status.options[i].selected = true;
                break;
                }
            }
        elem = $("booking1");
        elem.style.visibility = "visible";
<?php
        $show_ampm = (!get_variable('military_time')==1);
        if ($show_ampm) {	//put am/pm optionlist if not military time
//			dump (get_variable('military_time'));
            print "\tdocument.add.frm_meridiem_booked_date.disabled = false;\n";
            }
?>
        do_booked_date(theForm, false);
        }
/**
 *
 * @param {type} theForm
 * @param {type} theBool
 * @returns {undefined}
 */
    function do_booked_date(theForm, theBool) {							// 10/1/09 Booked Date processing
        theForm.frm_year_booked_date.disabled = theBool;
        theForm.frm_month_booked_date.disabled = theBool;
        theForm.frm_day_booked_date.disabled = theBool;
        theForm.frm_hour_booked_date.disabled = theBool;
        theForm.frm_minute_booked_date.disabled = theBool;
        if (theForm.frm_meridiem_booked_date) {theForm.frm_meridiem_booked_date.disabled = theBool;}
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_unlock_ps(theForm) {											// 8/10/08
        do_problemstart(theForm, false);
        $("lock_s").style.visibility = "hidden";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_unlock_bd(theForm) {									// 9/29/09 Unlock booked date
        do_booked_date(theForm, false);
        $("lock_b").style.visibility = "hidden";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_lock_ps(theForm) {												// 8/10/08
        do_problemstart(theForm, true);
        $("lock_s").style.visibility = "visible";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_unlock_pe(theForm) {											// 8/10/08
        do_problemend(theForm, false);
//		$("lock_e").style.visibility = "hidden";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_lock_pe(theForm) {												// 8/10/08
        do_problemend(theForm, true);
//		$("lock_e").style.visibility = "visible";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_unlock_pos(theForm) {											// 12/5/08
        document.add.frm_ngs.disabled=false;
        $("lock_p").style.visibility = "hidden";
        try { $("USNG").style.textDecoration = "underline";	}						// 4/30/09
        catch (e) { }
        }
/**
 *
 * @returns {undefined}
 */
    function do_usng() {														// 12/5/08
        if (document.add.frm_ngs.value.trim().length>6) {do_usng_conv();}
        }
/**
 *
 * @returns {undefined}
 */
    function do_usng_conv() {			// usng to LL array			- 12/4/08
        tolatlng = new Array();
        USNGtoLL(document.add.frm_ngs.value, tolatlng);
        var point = new GLatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
        map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);

        var marker = new GMarker(point);
        document.add.frm_lat.value = point.lat(); document.add.frm_lng.value = point.lng();
        do_lat (point.lat());
        do_lng (point.lng());
//		do_ngs_utm(document.add);
        do_grids(document.add);			// 12/13/10
        load(point.lat(), point.lng(), <?php echo get_variable('def_zoom'); ?>);			// show it

        }				// end function

    var protocols = new Array();		// 7/7/09
    var fac_lat = [];
    var fac_lng = [];

<?php
        // Pulldown menu for use of Incident set at Facility 9/22/09, 3/18/10
    $query_fc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
    $result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $pulldown = '<option value=0 selected>' . gettext('Incident at Facility') . '</option>\n';	// 3/18/10
        while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
            $pulldown .= "<option value=\"{$row_fc['id']}\">{$row_fc['name']}</option>\n";
            print "\tfac_lat[" . $row_fc['id'] . "] = " . $row_fc['lat'] . " ;\n";
            print "\tfac_lng[" . $row_fc['id'] . "] = " . $row_fc['lng'] . " ;\n";

            }

        // Pulldown menu for use of receiving Facility 10/6/09, 3/18/10
    $query_rfc = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` ORDER BY `name` ASC";
    $result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $pulldown2 = '<option value = 0 selected>' . gettext('Receiving facility') . '</option>\n'; 	// 3/18/10
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
/**
 *
 * @param {type} in_val
 * @returns {undefined}
 */
    function do_set_severity(in_val) {				// 6/26/10
        if (severities[in_val]>0) {document.add.frm_severity.selectedIndex = severities[in_val];}
        }

<?php
    if (!($gmaps)) {
?>
/**
 *
 * @returns {unresolved}
 */
    function GUnload() {				// dummy

        return;
        }
<?php
        }
?>

</SCRIPT>
</HEAD>

<BODY onLoad="ck_frames();do_lock_pe(document.add); document.add.frm_street.focus(); load(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>, <?php echo get_variable('def_zoom'); ?>);" onUnload="GUnload();">  <!-- <?php print __LINE__;?> -->		<!-- // 8/23/08 -->
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>

<?php
require_once './incs/links.inc.php';

$city = (!(empty($res_row['city'])))?	$res_row['city']  : get_variable('def_city');		// 11/5/10
$st =   (!(empty($res_row['state'])))?	$res_row['state'] : get_variable('def_st') ;
$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10

$inc_num_ary = unserialize (get_variable('_inc_num'));											// 11/13/10
switch ((int) $inc_num_ary[0]) {
    case 0:			// none
           $inc_name="";													// empty
        break;
    case 1: 		// number only
        $inc_name = (string) $inc_num_ary[3]. $inc_num_ary[2] . " " ;					// number and trailing separator if any
        break;

    case 2:			// labeled
        $inc_name = $inc_num_ary[1]. $inc_num_ary[2] . (string) $inc_num_ary[3] . " "   ;		// label, separator, number
        break;

    case 3:			// year
        $inc_name = $inc_num_ary[5]  . $inc_num_ary[2] . (string) $inc_num_ary[3] . " " ;		// year, separator, number
        break;

    default:
        alert("ERROR @ " + "<?php print __LINE__;?>");
    }

$do_inc_nature = (bool) ($inc_num_ary[4]==1)? "true": "false" ;		//

print "\n<SCRIPT>\n\t var do_inc_nature={$do_inc_nature};\n</SCRIPT>\n";

?>
<TABLE BORDER="0" ID = "outer" >
<TR><TD>
<TABLE BORDER="0">
<TR><TD ALIGN='center' COLSPAN='3'><FONT CLASS='header'><FONT SIZE=-1><FONT COLOR='green'><?php print gettext('New Call');?></FONT></FONT><BR />
    <FONT SIZE=-1>(<?php print gettext('mouseover caption for help information');?>)</FONT></FONT><BR /><BR /></TD>
    </TR>
<FORM METHOD="post" ACTION="<?php print basename(__FILE__);?>?add=true" NAME="add" onSubmit="return validate(document.add);">
<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a1"];?>');"><?php print get_text("Location"); ?></A>:</TD>
    <TD></TD>
    <TD><INPUT NAME="frm_street" tabindex=1 SIZE="72" TYPE="text" VALUE="<?php print $res_row['street'];?>" MAXLENGTH="96"/></TD>
    </TR>
<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a2"];?>');"><?php print get_text("City");?></A>:</TD>
    <TD ALIGN='center' ><BUTTON type="button" onClick="Javascript:loc_lkup(document.add);return false;"><img src="./markers/glasses.png" alt="<?php print gettext('Lookup location.');?>" /></BUTTON>&nbsp;&nbsp;</TD>
    <TD><INPUT NAME="frm_city" tabindex=2 SIZE="32" TYPE="text" VALUE="<?php print $city; ?>" MAXLENGTH="32" onChange = "this.value=capWords(this.value);"/>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF="#" TITLE="<?php print $titles["a3"];?>"><?php print get_text("St"); ?></A>:&nbsp;&nbsp;
        <INPUT NAME="frm_state" tabindex=3 SIZE="<?php print $st_size;?>" TYPE="text" VALUE="<?php print $st; ?>" MAXLENGTH="<?php print $st_size;?>"/></TD>
    </TR>
<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a4"];?>');"><?php print get_text("Phone");?></A>:</TD>
    <TD ALIGN='center' ><BUTTON type="button" onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="<?php print gettext('Lookup phone no.');?>" /></button>&nbsp;&nbsp;</TD>
    <TD><INPUT NAME="frm_phone"  tabindex=4 SIZE="16" TYPE="text" VALUE="<?php print get_variable('def_area_code');?>"  MAXLENGTH="16"/>&nbsp;<SPAN ID='repeats'></SPAN></TD>
    </TR>
<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a5"];?>');"><?php print $nature;?></A>:</TD>
    <TD></TD>
    <TD>
        <SELECT NAME="frm_in_types_id"  tabindex=5 onChange="do_set_severity(this.selectedIndex); do_inc_name(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());">	<!--  10/4/08 -->
        <OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>				<!-- 1/11/09 -->
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

    <SPAN CLASS="td_label" STYLE='margin-left:20px' onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a6"];?>');"><?php print get_text("Priority");?></SPAN>:

    <SELECT NAME="frm_severity" tabindex=6>
    <OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
    <OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
    <OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
    </SELECT>

    </TD>
    </TR>
<TR>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a7"];?>');"><?php print get_text("Protocol");?></A>:</SPAN></TD>
    <TD></TD>
    <TD ID='proto_cell'></TD>
    </TR>
<SCRIPT>
/**
 *
 * @param {type} inval
 * @returns {undefined}
 */
    function set_signal(inval) {				// 12/18/10
        var lh_sep = (document.add.frm_description.value.trim().length>0)? " " : "";
        var temp_ary = inval.split("|", 2);		// inserted separator
        document.add.frm_description.value+= lh_sep + temp_ary[1] + ' ';
        document.add.frm_description.focus();
        }		// end function set_signal()
/**
 *
 * @param {type} inval
 * @returns {undefined}
 */
    function set_signal2(inval) {				// 12/18/10
        var lh_sep = (document.add.frm_comments.value.trim().length>0)? " " : "";
        var temp_ary = inval.split("|", 2);		// inserted separator
        document.add.frm_comments.value+= lh_sep  + temp_ary[1] + ' ';
        document.add.frm_comments.focus();
        }		// end function set_signal()
</SCRIPT>
<TR CLASS='even' VALIGN="top">
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a8"];?>');"><?php print get_text("Synopsis");?></A>: </TD>
    <TD></TD>
    <TD><TEXTAREA NAME="frm_description"  tabindex=7 COLS="48" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
    </TR>

<TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
    <TD></TD>
    <TD></TD>
    <TD CLASS="td_label"><?php print gettext('Signal');?> &raquo;

                <SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
                <OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
                $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
                $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
                    print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT>
            </TD>
    </TR>

<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a9"];?>');"><?php print get_text("911 Contacted"); ?></A>:&nbsp;</TD>
    <TD></TD>
    <TD><INPUT NAME="frm_nine_one_one"  tabindex=8 SIZE="56" TYPE="text" VALUE="" MAXLENGTH="96" /></TD>
    </TR>

<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a10"];?>');"><?php print get_text("Reported by");?></A>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
    <TD></TD>
    <TD><INPUT NAME="frm_contact"  tabindex=9 SIZE="56" TYPE="text" VALUE="TBD" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"/></TD>
    </TR>
<TR CLASS='odd' ID = 'tr_misc' STYLE = 'display:none'>
    <TD CLASS="td_label"><?php print gettext('Additional');?>:</TD>
    <TD></TD>
    <TD ID='td_misc' CLASS="td_label"></TD>
    </TR> <!-- 3/13/10 -->

<?php
    if (empty($inc_name)) {

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
            }				// end switch()
        }				// end if (empty($inc_name))
?>

    <TR CLASS='odd'>
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a11"];?>');"><?php print get_text("Incident name");?></A>: <font color='red' size='-1'>*</font></TD>
        <TD></TD>
<?php
        if (!(empty($inc_name))) {				// 11/13/10
?>
        <TD><INPUT NAME="frm_scope" tabindex=10 SIZE="56" TYPE="text" VALUE="<?php print $inc_name;?>" MAXLENGTH="61" /></TD>
    </TR>
<?php
            }
        else {
?>
        <TD><?php print $prepend;?> <INPUT NAME="frm_scope" tabindex=10 SIZE="56" TYPE="text" VALUE="TBD" MAXLENGTH="61" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}" onkeypress='user_inc_name = true;'/><?php print $append;?></TD>
    </TR>	<!-- 1/11/09 -->
<?php
        }										// end else {} 11/13/10
?>

    <TR CLASS='even'><TD COLSPAN=3 ALIGN='center'><HR SIZE=1 COLOR=BLUE WIDTH='60%' /></TD>
    </TR>

    <TR CLASS='even' valign="middle">
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a12"];?>');"><?php print get_text("Scheduled Date");?></A>:
             </TD>
        <TD ALIGN='center' ><input type="radio" name="book_but" onClick ="do_booking(this.form);" /><!-- 9/30/10 -->
            </TD>
        <TD><SPAN style = "visibility:hidden" ID = "booking1"><?php print generate_date_dropdown('booked_date',0, TRUE);?></SPAN>
            </TD>
        </TR>

<?php
    if ($facilities > 0) {				// any? - 3/24/10
?>
    <TR CLASS='odd'>
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a13"];?>');">Facility?</A>:&nbsp;&nbsp;&nbsp;&nbsp;</TD>	 <!-- 9/22/09 -->
        <TD></TD>
        <TD>
            <SELECT NAME="frm_facility_id"  tabindex=11 onChange="do_fac_to_loc(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());"><?php print $pulldown; ?></SELECT>&nbsp;&nbsp;&nbsp;&nbsp;
            <SELECT NAME="frm_rec_facility_id" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}">
            <?php print $pulldown2; ?></SELECT>
        </TD>
    </TR>
<?php
        }		// end if ($facilities > 0)
    else {
?>
    <INPUT TYPE = 'hidden' NAME = 'frm_facility_id' VALUE=''/>
    <INPUT TYPE = 'hidden' NAME = 'frm_rec_facility_id' VALUE=''/>
<?php
    }

?>
<!--
    <TR CLASS='odd'>
        <TD CLASS="td_label"><?php print gettext('Affected');?>:</TD>
        <TD></TD>
        <TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48"/></TD>
    </TR>
-->
    <TR CLASS='even' VALIGN='bottom'>
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a14"];?>');"><?php print get_text("Run Start");?></A>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
        <TD ALIGN='center' ><img id='lock_s' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_ps(document.add);' /></TD>
        <TD>
<?php print generate_date_dropdown('problemstart',0,TRUE);?>
        <SPAN CLASS="td_label" STYLE='margin-left:12px' onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a15"];?>');"><?php print get_text("Status");?>:</SPAN>
        <SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected><?php print gettext('Open');?></OPTION>
        <OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'><?php print gettext('Closed');?></OPTION>
        <OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED']; ?>'><?php print gettext('Scheduled');?></OPTION></SELECT>

        </TD>
    </TR>
    <TR CLASS='odd' valign="middle">
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a16"];?>');"><?php print get_text("Run End");?></A>:
        </TD>
        <TD ALIGN='center' ><input type="radio" name="re_but" onClick ="do_end(this.form);" /></TD>
        <TD>
            <SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
        </TD>
    </TR>
    <TR CLASS='even' VALIGN="top">
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["a17"];?>');"><?php print $disposition;?></A>:</TD>
        <TD></TD>
        <TD><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
        </TR>
    <TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
        <TD></TD>
        <TD></TD>
        <TD CLASS="td_label"><?php print gettext('Signal');?> &raquo;

            <SELECT NAME='signals' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
                <OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
                $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
                $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
                    print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT>
            </TD>
    </TR>
<?php
    if ($gmaps) {
?>

    <TR CLASS='odd'>
        <TD CLASS="td_label">
            <SPAN ID="pos" onClick = 'javascript: do_coords(document.add.frm_lat.value, document.add.frm_lng.value );'>
            <U><A HREF="#" TITLE="<?php print $titles["a18"];?>"><?php print $incident;?> <?php print gettext('Lat/Lng');?></A></U></SPAN>:
                <font color='red' size='-1'>*</font>
        </TD>
        <TD ALIGN='center' ><img id='lock_p' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.add);'/></TD>
        <TD><INPUT SIZE="11" TYPE="text" NAME="show_lat" VALUE="" />
            <INPUT SIZE="11" TYPE="text" NAME="show_lng" VALUE="" />&nbsp;&nbsp;
<?php
$locale = get_variable('locale');	// 08/03/09
    switch ($locale) {
        case "0":
?>
            <B><SPAN ID = 'USNG' onClick = "do_usng();"><?php print gettext('USNG');?></SPAN></B>:&nbsp;<INPUT SIZE="19" TYPE="text" NAME="frm_ngs" VALUE="" DISABLED />
            <!-- 9/13/08, 12/3/08 -->

<?php
        break;

        case "1":		// UK
?>
            <B><SPAN ID = 'OSGB' ><?php print gettext('OSGB');?>:</SPAN></B>&nbsp;<INPUT SIZE="19" TYPE="text" NAME="frm_osgb" VALUE="" DISABLED />
            <!-- 9/13/08, 12/3/08 --><?php
        break;

        default:		// ROW
?>
            <B><SPAN ID = 'UTM'><?php print gettext('UTM');?>:</SPAN></B>&nbsp;<INPUT SIZE="19" TYPE="text" NAME="frm_utm" VALUE="" DISABLED />
             <!-- 9/13/08, 12/3/08 -->

<?php
        }			// end switch($locale)
?>
      </TD></TR>
<?php
    }		// end if ($gmaps)
?>
    <TR CLASS='even'><TD COLSPAN="3" ALIGN="center"><BR />
        <INPUT TYPE="button" VALUE="<?php print gettext('History');?>"  onClick="do_hist_win();"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>"  onClick="history.back();"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onclick= "do_reset(this.form);" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <INPUT TYPE="submit" VALUE="<?php print get_text("Next"); ?>"/></TD>
    </TR>	<!-- 8/11/08 -->
    <TR CLASS='odd'>
        <TD COLSPAN="3" ALIGN="center"><br /><IMG SRC="glasses.png" BORDER="0"/>: <?php print gettext('Lookup');?> </TD>
        </TR>

        <INPUT TYPE="hidden" NAME="frm_lat" VALUE=""/>				<!-- // 9/9/08 -->
        <INPUT TYPE="hidden" NAME="frm_lng" VALUE=""/>
        <INPUT TYPE="hidden" NAME="ticket_id" VALUE="<?php print $ticket_id;?>"/>	<!-- 1/25/09, 3/10/09 -->
        <INPUT TYPE='hidden' NAME="frm_do_scheduled" VALUE=0 />	<!-- 1/1/11 -->

    </FORM></TABLE>
    </TD>
<?php
        if ($gmaps) {
?>

    <TD>

    <TABLE ID='four' border=0><TR><TD id='three' ALIGN='center'><div id='map' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px'></div>
    <BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />
        <SPAN ID='do_grid' onclick = "toglGrid();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u><?php print gettext('Grid');?></U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <SPAN ID='do_sv' onClick = "sv_win(document.add);" style='display:none'><u><?php print gettext('Street view');?></U></SPAN> <!-- 2/11/09 -->

    </TD></TR></TABLE>
    </TD>
<?php
    }
?>
    </TR>
    </TABLE>

<?php
//	dump($_SESSION['user_id']);
    } //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>
</BODY></HTML>
