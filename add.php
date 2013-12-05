<?php
/**
 *
 * @package add.php
 * @author John Doe <john.doe@example.com>
 * @since 2007-10-28
 * @version 2013-11-29
 */

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
if (empty($_SESSION)) {
    header("Location: index.php");
    }

require_once 'incs/functions.inc.php';		//7/28/10
do_login(basename(__FILE__));
$gmaps = $_SESSION['internet'];
$in_win = array_key_exists ("mode", $_GET);		// in

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
1/19/10 revised to accommodate both maps and no-maps option
1/21/11 corrections to booked-date handling
1/24/11 corrections to locale/grid handling
1/29/11	changed coordinates test to string-length
2/1/11 added table 'hints' as hints source
2/11/11 condition signals on non-empty table
2/12/11 facility names shortened
2/19/11 draggable button bar replaces tr
2/27/11 corrected 'append incident nature to incident name'
3/2/11 added  base64_decode to serialize/unserialize
3/15/11 changed default.css to stylesheet.php to cater for day / night capability
3/17/11 Revised form to not use default city if places exist in the places table
4/23/11 revisions for USNG handling
5/22/11 corrected reverse geo-location lookup
6/9/11 action and patient buttons, cancel function added, caller id
6/10/11 added changes required to support regional capability (Ticket region assignment).
6/23/11 revised target for action and patient buttons
11/22/2012 'nearby' capability added
12/1/2012 show 'nearby' only if internet/maps
5/22/2013 added broadcast call
6/2/2013 - reverse geocode added
7/3/2013 - socket2me conditioned on internet and broadcast settings, enforce reverse-geo values size limits
9/10/13 - Added "address about" and "To address" fields. Also Location warnings capability
10/11/2013 - corrected auto incident numbering - relocated else {} closure
*/

if (empty($_GET)) {
    if (mysql_table_exists("$GLOBALS[mysql_prefix]caller_id")) {				// 6/9/11
        $cid_calls = $cid_name = $cid_phone = $cid_street = $cid_city = $cid_state = $cid_lat = $cid_lng = "";
        $query = "SELECT * FROM `$GLOBALS[mysql_prefix]caller_id` WHERE `status` = 0 LIMIT 1;";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
        if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
            $row = stripslashes_deep(mysql_fetch_array($result));
            $query = "UPDATE `$GLOBALS[mysql_prefix]caller_id` SET `status` = 1 WHERE `id` = " . quote_smart($row['id']);
    //		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
            $lookup_vals = explode (";", $row['lookup_vals']);
    //		dump($lookup_vals);
            $cid_calls = $lookup_vals[0];
            $cid_name = $lookup_vals[1];
            $cid_phone = $lookup_vals[2];
            $cid_street = $lookup_vals[3];
            $cid_city = $lookup_vals[4];
            $cid_state = $lookup_vals[5];
            $cid_lat = $lookup_vals[7];
            $cid_lng = $lookup_vals[8];
            $cid_id = $row['id'];			// id of caller id record
            }
        }				// end if(empty())
    }

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
                `id` , `in_types_id` , `contact` , `street` , `address_about` , `city` , `state` , `phone` , `to_address` , `lat` , `lng` , `date` ,
                `problemstart` , `problemend` , `scope` , `affected` , `description` , `comments` , `status` , `owner` ,
                `severity` , `updated`, `booked_date`, `_by`
            ) VALUES (
                NULL , 0, 0, NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL ,
                NULL , NULL , '', NULL , '', NULL , '" . $GLOBALS['STATUS_RESERVED'] . "', '0', '0', NULL, NULL, $by
            )";	//	9/10/13

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
            global $addrs, $NOTIFY_TICKET;	//	8/28/13

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

            if (intval($_POST['frm_status']) == 3) {		// 1/21/11
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

//			$inc_num_ary = unserialize (get_variable('_inc_num'));					// 11/13/10
            $temp = get_variable('_inc_num');										// 3/2/11
            snap( __LINE__ , $temp );
            $inc_num_ary = (strpos($temp, "{")>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));
             $name_rev = $_POST['frm_scope'];
            snap( __LINE__ , $inc_num_ary[0] );

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
            $portal_user = 		empty($_POST['frm_portal_user'])?		0:  trim($_POST['frm_portal_user']);				// 9/10/13
            $groups = "," . implode(',', $_POST['frm_group']) . ",";	//	6/10/11
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

            if ((strlen($the_lat) < 3 ) && (strlen($the_lng) < 3)) {	// 1/29/11
                $the_lat = $the_lng = 0.999999;
                }

            // perform db update	//9/22/09 added facility capability, 10/1/09 added receiving facility
            @session_start();
            $by = $_SESSION['user_id'];
//			$booked_date = empty($frm_booked_date)? "NULL" : quote_smart(trim($frm_booked_date)) ;	// 6/20/10
            $booked_date = (intval(trim($_POST['frm_do_scheduled'])==1))?  quote_smart($frm_booked_date): "NULL" ;	// 1/2/11, 1/19/10
//			die;
            // 6/26/10
            $query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET
                `portal_user`= " . 	quote_smart(trim($portal_user)) . ",
                `contact`= " . 		quote_smart(trim($_POST['frm_contact'])) .",
                `street`= " . 		quote_smart(trim($_POST['frm_street'])) .",
                `address_about`= " . 		quote_smart(trim($_POST['frm_address_about'])) .",
                `city`= " . 		quote_smart(trim($_POST['frm_city'])) .",
                `state`= " . 		quote_smart(trim($_POST['frm_state'])) . ",
                `phone`= " . 		quote_smart(trim($_POST['frm_phone'])) . ",
                `to_address`= " . 		quote_smart(trim($_POST['frm_to_address'])) . ",
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
                WHERE ID=$id";	//	9/10/13
            $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

            $tick_stat = $_POST['frm_status'];	// 6/10/11
            $prob_start = quote_smart(trim($frm_problemstart));	// 6/10/11

//	If portal user is set, insert an associated request if one does not already exist for this Ticket	9/10/13

            $where = $_SERVER['REMOTE_ADDR'];		//	9/10/13
            if (($portal_user != NULL) && ($portal_user != 0)) {		//	9/10/13
                $query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `ticket_id` = " . $id;		//	9/10/13
                $result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);			//	9/10/13
                if (mysql_affected_rows() == 0) {		//	9/10/13
                    $query = "INSERT INTO `$GLOBALS[mysql_prefix]requests` (
                    `org`,
                    `contact`,
                    `street`,
                    `city`,
                    `state`,
                    `the_name`,
                    `phone`,
                    `to_address`,
                    `orig_facility`,
                    `rec_facility`,
                    `scope`,
                    `description`,
                    `comments`,
                    `lat`,
                    `lng`,
                    `request_date`,
                    `status`,
                    `accepted_date`,
                    `declined_date`,
                    `resourced_date`,
                    `completed_date`,
                    `closed`,
                    `requester`,
                    `ticket_id`,
                    `_by`,
                    `_on`,
                    `_from`
                    ) VALUES (
                    " . 0 . ",
                    '" . get_owner($_POST['frm_portal_user']) . "',
                    " . quote_smart(trim($_POST['frm_street'])) . ",
                    " . quote_smart(trim($_POST['frm_city'])) . ",
                    " . quote_smart(trim($_POST['frm_state'])) . ",
                    " . quote_smart(trim($_POST['frm_contact'])) . ",
                    " . quote_smart(trim($_POST['frm_phone'])) . ",
                    " . quote_smart(trim($_POST['frm_to_address'])) . ",
                    " . quote_smart($facility_id ) . ",
                    " . quote_smart($rec_facility_id ) . ",
                    " . quote_smart(trim($name_rev)) . ",
                    " . quote_smart(trim($_POST['frm_description'])) . ",
                    " . quote_smart(trim($_POST['frm_comments'])) . ",
                    " . $the_lat . ",
                    " . $the_lng . ",
                    " . quote_smart(trim($frm_problemstart)) . ",
                    'Accepted',
                    '" . $now . "',
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    " . $portal_user . ",
                    " . $id . ",
                    " . $_SESSION['user_id'] . ",
                    '" . $now . "',
                    '" . $where . "')";
                    $result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);		//	9/10/13
                }
            }

//	end of insert request associated with Ticket

//	9/10/13 File Upload support
            $print = "";
            if ((isset($_FILES['frm_file'])) && ($_FILES['frm_file']['name'] != "")) {
                $nogoodFile = false;
                $blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py");
                foreach ($blacklist as $file) {
                    if (preg_match("/$file\$/i", $_FILES['frm_file']['name'])) {
                        $nogoodFile = true;
                        }
                    }
                if (!$nogoodFile) {
                    $exists = false;
                    $existing_file = "";
                    $upload_directory = "./files/";
                    if (!(file_exists($upload_directory))) {
                        mkdir ($upload_directory, 0770);
                        }
                    chmod($upload_directory, 0770);
                    $filename = rand(1,999999);
                    $realfilename = $_FILES["frm_file"]["name"];
                    $file = $upload_directory . $filename;

//	Does the file already exist in the files table

                    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]files` WHERE `orig_filename` = '" . $realfilename . "'";
                    $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
                    if (mysql_affected_rows() == 0) {	//	file doesn't exist already
                        if (move_uploaded_file($_FILES['frm_file']['tmp_name'], $file)) {	// If file uploaded OK
                            if (strlen(filesize($file)) < 20000000) {
                                $print .= "";
                                } else {
                                $print .= gettext("Attached file is too large!");
                                }
                            } else {
                            $print .= gettext("Error uploading file");
                            }
                        } else {
                        $row = stripslashes_deep(mysql_fetch_assoc($result));
                        $exists = true;
                        $existing_file = $row['filename'];	//	get existing file name
                        }

                    $from = $_SERVER['REMOTE_ADDR'];
                    $filename = ($existing_file == "") ? $filename : $existing_file;	//	if existing file, use this file and write new db entry with it.
                    $query_insert  = "INSERT INTO `$GLOBALS[mysql_prefix]files` (
                            `title` , `filename` , `orig_filename`, `ticket_id` , `responder_id` , `facility_id`, `type`, `filetype`, `_by`, `_on`, `_from`
                        ) VALUES (
                            '" . $_POST['frm_file_title'] . "', '" . $filename . "', '" . $realfilename . "', " . $id . ", 0,
                            0, 0, '" . $_FILES['frm_file']['type'] . "', $by, '" . $now . "', '" . $from . "'
                        )";
                    $result_insert	= mysql_query($query_insert) or do_error($query_insert,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
                    if ($result_insert) {	//	is the database insert successful
                        $dbUpdated = true;
                        } else {	//	problem with the database insert
                        $dbUpdated = false;
                        }
                    }
                } else {	// Problem with the file upload
                $fileUploaded = false;
                }

//	End of file upload
            foreach ($_POST['frm_group'] as $grp_val) {	// 6/10/11
                if (test_allocates($id, $grp_val, 1)) {
                    $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                            ($grp_val, 1, '$now', $tick_stat, $id, 'Allocated to Group' , $by)";
                    $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                    }
                }

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

            $out_str = base64_encode(serialize ($inc_num_ary));						// 3/2/11

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
            <LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
            <SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>	<!-- 9/14/12 -->
            <SCRIPT SRC="./js/jscolor/jscolor.js"></SCRIPT>	<!-- 9/14/12 -->
        <SCRIPT>
<?php
        $addrs = notify_user($_POST['ticket_id'],$GLOBALS['NOTIFY_TICKET_CHG']);		// returns array of adddr's for notification, or FALSE
        if ($addrs) {				// any addresses?	8/28/13
?>
/**
 *
 * @returns {unresolved}
 */
    function do_notify() {

        var theAddresses = '<?php print implode("|", array_unique($addrs));?>';		// drop dupes
        var theText= ' New <?php print get_text("Incident");?>: ';
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
////		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
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
<?php				// 7/3/2013
    if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
        require_once './incs/socket2me.inc.php';		// 5/22/2013
        }
?>

        </HEAD>
    <BODY onLoad = "do_notify();document.<?php print $form_name;?>.submit();">
<?php
    $now = time() - (intval(get_variable('delta_mins')*60));		// 6/20/10
    print "<BR /><BR /><BR /><CENTER><FONT CLASS='header'>Ticket: '{$ticket_name}  ' Added by '{$_SESSION['user_id']}' at " . date(get_variable("date_format"),$now) . "</FONT></CENTER><BR /><BR />";
?>
    <FORM NAME='to_main' METHOD='post' ACTION='main.php'>
    <CENTER><INPUT TYPE='submit' VALUE='<?php print gettext('Main');?>' />
    </FORM>

    <FORM NAME='to_routes' METHOD='get' ACTION='<?php print $_SESSION['routesfile'];?>'>
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


    $titles = array();				// 2/1/11

    $query = "SELECT `tag`, `hint` FROM `$GLOBALS[mysql_prefix]hints`";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    while ($row =stripslashes_deep(mysql_fetch_assoc($result))) {
        $titles[trim($row['tag'])] = trim($row['hint']);
        }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Add Module');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">			<!-- 3/15/11 -->
    <STYLE TYPE="text/css">
    #suggest{background:#fff; width:150px;	}
    #suggest div{ background:#ddd; color:#000; padding-left:4px; cursor:hand; text-align:left;position:relative;	}
    #suggest div.over{ color:#000; background:#fff;	}
    </STYLE>


<?php
    if (($gmaps) && (!($in_win))) {
        $api_key = get_variable('gmaps_api_key');
?>
<SCRIPT TYPE="text/javascript" SRC="http://maps.google.com/maps/api/js?sensor=false"></SCRIPT>
<SCRIPT SRC="./js/graticule_V3.js" type="text/javascript"></SCRIPT>
<?php
        }
?>
<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT SRC='./js/jscoord.js' TYPE="text/javascript"></SCRIPT>			<!-- coordinate conversion 12/10/10 -->
<SCRIPT SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/geotools2.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>	<!-- 11/8/11 -->
<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT> 	<!-- 7/22/10 -->
<SCRIPT SRC="./js/suggest.js" TYPE="text/javascript"></SCRIPT>			<!-- 2/20/11 -->
<SCRIPT SRC="./js/gmaps_v3_init.js"	TYPE="text/javascript" ></SCRIPT>

<SCRIPT>
/**
 *
 * @returns {undefined}
 */
    function get_new_colors() {				// 5/4/11
        window.location.href = '<?php print basename(__FILE__);?>';
        }
/**
 *
 * @returns {undefined}
 */
    function ck_frames() {		// onLoad = "ck_frames()"
<?php if ($in_win) {echo "\n\t\t return;\n"; }?>
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
                alert ( "error <?php print __LINE__;?>");
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
                alert ("error <?php print __LINE__;?>");
            }
        }
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

    var map;						// note globals
//	var geocoder = null;
//	var rev_coding_on;	// 11/01/09
//	geocoder = new GClientGeocoder();
    var request;
//	var querySting;   				// will hold the POSTed data
    var tab1contents;				// info window contents - first/only tab
    var thePoint;
    var baseIcon;
    var cross;

    var grid_bool = false;
/**
 *
 * @returns {undefined}
 */
    function toglGrid() {						// toggle
        grid_bool = !grid_bool;
        if (grid_bool) { grid = new Graticule(map_obj); }
        else 			{ grid.setMap(null); }
        }		// end function toglGrid()
/**
 *
 * @returns {undefined}
 */
    function clearmap() {
<?php
    if ((!($gmaps)) || ($in_win)) {
        print "\n\t return;\n";
        }
?>
        map.clearOverlays();
        load(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>, <?php echo get_variable('def_zoom'); ?>);
//		if (grid) {map.addOverlay(new LatLonGraticule());}
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
        thisMarker  = new google.maps.Marker(center, {icon: cross});				// 9/16/08
        thisMarker.setMap(map);
        }

//										some globals
        var map_obj = null;				// the map object - note GLOBAL
        var myMarker;					// the marker object
        var lat_var;					// see init.js
        var lng_var;
        var zoom_var;

        var icon_file = "./markers/crosshair.png";
/**
 *
 * @param {type} the_lat
 * @param {type} the_lng
 * @param {type} the_zoom
 * @returns {undefined}
 */
    function load(the_lat, the_lng, the_zoom) {				// onLoad function - 4/28/09
<?php
    if ((!($gmaps)) || ($in_win)) {
        print "\n\t return;\n";
        }
?>
/**
 *
 * @param {type} in_obj
 * @returns {undefined}
 */
    function call_back(in_obj) {				// callback function - from gmaps_v3_init()
//		alert(756);
        do_lat(in_obj.lat);			// set form values
        do_lng(in_obj.lng);
        do_ngs();
<?php								// 6/2/2013
    if (intval(get_variable('reverse_geo'))==1) {
?>
        if (do_rev_geo) {
            codeLatLng (in_obj.lat, in_obj.lng);				// 6/2/2013
            }
        else {
            do_rev_geo = true;
            }

<?php
        }	// end 'reverse_geo'?
?>
        }			// end callback function

        map_obj = gmaps_v3_init(call_back, 'map_canvas',
            <?php echo get_variable('def_lat');?>,
            <?php echo get_variable('def_lng');?>,
            <?php echo get_variable('def_zoom');?>,
            icon_file,
            <?php echo get_variable('maptype');?>,
            false);

<?php
//			do_kml();
?>
        var do_rev_geo = true;

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
 * @returns {undefined}
 */
    function do_ngs() {											// LL to USNG - 6/2/2013
        var loc = <?php print get_variable('locale');?>;
        document.forms[0].frm_ngs.disabled=false;
        if (loc == 0) { document.forms[0].frm_ngs.value = LLtoUSNG(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value, 5);			}
        if (loc == 1) { document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);			}
        if (loc == 2) { document.forms[0].frm_ngs.value = LLtoOSGB(document.forms[0].frm_lat.value, document.forms[0].frm_lng.value);			}
        document.forms[0].frm_ngs.disabled=true;
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_grids(theForm) {								// 12/13/10
<?php															// 1/24/11
            $locale = intval(trim(get_variable("locale")));
            switch ($locale) {
                case "0":
                    echo "\n\t\t do_usng(theForm);\n";
                    break;

                case "1":
                    echo "\n\t\t do_osgb(theForm);\n";

                    break;
                default:																	// 8/10/09
                    echo "\n\t\t do_utm(theForm);\n";
                }		// end switch
?>
        }
/**
 *
 * @param {type} theForm
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
        theForm.frm_ngs.value = (temp_ary.length == 3)? temp_ary[0] + " " +  parseInt(temp_ary[1]) + " " + parseInt(temp_ary[2]) : "";
        }
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function do_osgb(theForm) {
        theForm.frm_ngs.value = LLtoOSGB(theForm.frm_lat.value, theForm.frm_lng.value);
        }
/**
 *
 * @param {type} the_form
 * @returns {undefined}
 */
    function do_cancel(the_form) {			// 6/9/11
        var params = "ticket_id=" + the_form.ticket_id.value;
        sendRequest (the_form, 'cancel_add.php',handleResult_can, params);	// (my_form, url,callback,postData))   10/15/08
        }			// end function do cancel()
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
    function handleResult_can(req) {				// the called-back function
<?php
        if ($istest) {print "\t\t\talert('HTTP error ' + req.status + '" . __LINE__ . "');\n";}
?>
<?php
    if ($in_win) {
?>
    window.close();
<?php
        }
    else {
?>
        parent.frames['upper'].light_butt('main');			// top frame button
        history.back();
<?php
        } 		// end else
?>
        }			// end function handleResult_can(req)

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
////		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
        if (postData)
            req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        req.onreadystatechange = function () {
            if (req.readyState != 4) return;
            if (req.status != 200 && req.status != 304) {
                alert('<?php print __LINE__;?>: HTTP error ' + req.status);

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
    function pt_to_map(my_form, lat, lng) {						// 7/5/10
        if (myMarker) { myMarker.setMap(null);}			// destroy predecessor
        my_form.frm_lat.value=lat;
        my_form.frm_lng.value=lng;

        my_form.show_lat.value=do_lat_fmt(lat);
        my_form.show_lng.value=do_lng_fmt(lng);

        var loc = <?php print get_variable('locale');?>;
        if (loc == 0) { my_form.frm_ngs.value=LLtoUSNG(lat, lng, 5); }
        if (loc == 1) { my_form.frm_ngs.value=LLtoOSGB(lat, lng, 5); }
        if (loc == 2) { my_form.frm_ngs.value=LLtoUTM(lat, lng, 5); }

        map_obj.setCenter(new google.maps.LatLng(lat, lng), <?php print get_variable('def_zoom');?>);

        var iconImg = new Image();														// obtain icon dimensions
        iconImg.src ='./markers/crosshair.png';
        myIcon.anchor= new google.maps.Point(iconImg.width/2, iconImg.height/2);		// 8/11/12 - center offset = half icon width and height
        var dp_latlng = new google.maps.LatLng(lat, lng);

        var myMarker = new google.maps.Marker({
            position: dp_latlng,
            icon: myIcon,
            draggable: true,
            map: map_obj
            });
        myMarker.setMap(map_obj);		// add marker with icon
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

        var myAddress = my_form.frm_street.value.trim() + ", " +my_form.frm_city.value.trim() + " "  +my_form.frm_state.value.trim();

        geocoder.geocode( { 'address': myAddress}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                pt_to_map (my_form, results[0].geometry.location.lat(), results[0].geometry.location.lng());
                var tick_lat = results[0].geometry.location.lat();	//	8/14/13
                var tick_lng = results[0].geometry.location.lng();	//	8/14/13
                }
            else 											{ alert("Geocode lookup failed: " + status);}
            });				// end geocoder.geocode()
        }				// end function loc_lkup()

    function find_warnings(tick_lat, tick_lng) {	//	9/10/13
        randomnumber=Math.floor(Math.random()*99999999);
        var theurl ="./ajax/loc_warn_list.php?version=" + randomnumber + "&lat=" + tick_lat + "&lng=" + tick_lng;
        theRequest(theurl, loc_w, "");
        function loc_w(req) {
            var the_warnings=JSON.decode(req.responseText);
            var the_count = the_warnings[0];
            if (the_count != 0) {
                alert("<?php print gettext('There is at least one location nearby with a warning registered for it.\nPlease view the Ticket to see the warnings');?>");
                document.add.submit();
                } else {
                document.add.submit();
                }
            }
        }

    var start_wl = false;
    function wl_win(the_Id) {				// 9/10/13
        if (start_wl) {return;}				// dbl-click proof
        start_wl = true;
        var url = "warnloc_popup.php?id=" + the_Id;
        newwindow_wl=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=600,width=750,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
        if (!(newwindow_wl)) {
            alert ("<?php print gettext('Locations warning operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.');?>");

            return;
            }
        newwindow_wl.focus();
        start_wl = false;
        }		// end function sv win()

    function theRequest(url,callback,postData) {
        var req = docreateXMLHTTPObject();
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

    var doXMLHttpFactories = [
        function () {return new XMLHttpRequest();	},
        function () {return new ActiveXObject("Msxml2.XMLHTTP");	},
        function () {return new ActiveXObject("Msxml3.XMLHTTP");	},
        function () {return new ActiveXObject("Microsoft.XMLHTTP");	}
        ];

    function docreateXMLHTTPObject() {
        var xmlhttp = false;
        for (var i=0;i<XMLHttpFactories.length;i++) {
            try { xmlhttp = doXMLHttpFactories[i](); }
            catch (e) { continue; }
            break;
            }

        return xmlhttp;
        }

<?php
    if ($gmaps) {		// 6/4/2013
?>

// **************************************************** Reverse Geocoder 10/13/09, 7/5/10

    var geocoder = new google.maps.Geocoder();
    var address;
    var rev_coding_on = '<?php print get_variable('reverse_geo');?>';		// 7/5/10
/**
 *
 * @param {type} lat
 * @param {type} lng
 * @returns {undefined}
 */
    function codeLatLng(lat, lng) {
        var latlng = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
        geocoder.geocode({'latLng': latlng}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                var addr_pieces = results[0].formatted_address.split(",");
                switch (addr_pieces.length) {
                    case 1:
                        document.add.frm_street.value = document.add.frm_city.value = "";				// state or country
                        document.add.frm_state.value=addr_pieces[0].substring(0,5).trim() ;			// state or country
                        break;

                    case 2:
                        document.add.frm_street.value = "";
                        document.add.frm_city.value=addr_pieces[0].substring(0,33).trim() ;			// city
                        var temp = addr_pieces[1].substring(0,5).trim().split(" ");					// zipcode
                        document.add.frm_state.value=temp[0].substring(0,5).trim() ;				// state or country
                        break;

                    case 3:
                        document.add.frm_street.value=addr_pieces[0].substring(0,97).trim() ;		// street
                        document.add.frm_city.value=addr_pieces[1].substring(0,33).trim() ;			// city
                        var temp = addr_pieces[2].substring(0,5).trim().split(" ");					// zipcode
                        document.add.frm_state.value=temp[0].substring(0,5).trim() ;				// state or country
                        break;

                    default:
                        document.add.frm_street.value=addr_pieces[0].substring(0,97).trim() ;							// street
                        document.add.frm_city.value=addr_pieces[(addr_pieces.length-3)].substring(0,33).trim() ;		// city
                        var temp = addr_pieces[(addr_pieces.length-2)].substring(0,5).trim().split(" ");				// zipcode
                        if (temp.length == 2) {
                            document.add.frm_city.value=addr_pieces[(addr_pieces.length-3)].substring(0,33).trim() ;	// city
                            document.add.frm_state.value=temp[0].substring(0,5).trim() ;								// US state
                            }
                        else {
                            var the_city = addr_pieces[(addr_pieces.length-3)] + ", " + addr_pieces[(addr_pieces.length-2)];
                            document.add.frm_city.value=the_city.substring(0,33).trim();								// city
                            document.add.frm_state.value=addr_pieces[(addr_pieces.length-1)].substring(0,5).trim();		// country
                            }
                    }				// end switch
                }
            } else { alert("<?php print gettext('Geocoder lookup failed due to');?>: " + status); }
        });
    }				// end function

/**
 *
 * @param {type} overlay
 * @param {type} latlng
 * @returns {undefined}
 */
    function getAddress(overlay, latlng) {		//7/5/10
//		if (rev_coding_on == 1) {
//			alert(1076);
            if (latlng != null) {

                geocoder.getLocations(latlng, function (response) {
                map.clearOverlays();
                if (response.Status.code != 200) {
                    alert("<?php print __LINE__;?>: <?php print gettext('address unavailable');?>");
                } else {

                    place = response.Placemark[0];
                    point = new google.maps.LatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
// 					locality = response.Placemark[0].AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality;   5/22/11
                    marker = new google.maps.Marker(point);
                    marker.setMap(map);
                    results = pars_goog_addr(place.address);
//																7/3/2013 - enforce size limits
                    document.add.frm_street.value = results[0].substring(0, 95);		// 7/22/10
                    document.add.frm_city.value = results[1].substring(0, 31) ;

                    document.add.frm_state.value = results[2].substring(0, 3);
                    document.add.frm_street.focus();		// 7/22/10

                    }
                });
                }
//			}
        }				// end function getAddress()

// *****************************************************************************
<?php
    }				// end if ($gmaps)  -  6/4/2013
?>

    var tbd_str = "TBD";									// 1/11/09
    var user_inc_name = false;							// 4/21/10
/**
 *
 * @param {type} str
 * @param {type} indx
 * @returns {undefined}
 */
    function do_inc_name(str, indx) {								// 10/4/08, 7/7/09
<?php
//	$inc_num_ary = unserialize (get_variable('_inc_num'));					// 2/27/11
    $temp = get_variable('_inc_num');										// 3/2/11
//	dump(strpos($temp, '{'));
    $inc_num_ary = (strpos($temp, '{')>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));

    if (intval($inc_num_ary[4])>0) {
?>
        if (document.add.frm_scope.value.trim()==tbd_str) {document.add.frm_scope.value= "";}
        ldg_sl_str = (document.add.frm_scope.value.trim().length>0) ? "/" : "";				// other input?
        document.add.frm_scope.value += ldg_sl_str + str + "/";								// 11/13/10
<?php
        }
?>
        if (protocols[indx]) {
//			$('proto_row').style.display = "block";
            $('proto_cell').innerHTML = protocols[indx];
            }
        else {
            $('proto_cell').innerHTML = "";
            }
        }			// end function do_inc_name()
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
<?php
    if (!(intval(get_variable('quick')==1))) {
?>
        if (theForm.frm_in_types_id.value == 0) {errmsg+= "\t<?php print gettext('Nature of Incident is required');?>\n";}			// 1/11/09
<?php
        }
?>
        if (theForm.frm_contact.value == "") {errmsg+= "\t<?php print gettext('Reported-by is required');?>\n";}
        if (theForm.frm_scope.value == "") {errmsg+= "\t<?php print gettext('Incident name is required');?>\n";}
//		if (theForm.frm_description.value == "") {errmsg+= "\t<?php print gettext('Synopsis is required');?>\n";}
//		theForm.frm_lat.disabled=false;														// 9/9/08
<?php
    if (($gmaps) && (!($in_win))) {
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
            var theAddr = theForm.frm_street.value + " " + theForm.frm_city.value + " " + theForm.frm_state.value;
<?php
        if (intval(get_variable('broadcast')==1)) {
?>																						/*	5/22/2013 */
            var theMessage = "New  <?php print get_text('Incident');?> (" + theForm.frm_scope.value + ") " + theAddr  + " by <?php echo $_SESSION['user'];?>";
            broadcast(theMessage ) ;
<?php
    }			// end if (broadcast)
?>
            find_warnings(theForm.frm_lat.value, theForm.frm_lng.value);	//	9/10/13

            return true;	//	11/18/13
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
        }				// end function do_fac_to_loc
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
        try {$("grid_link").style.textDecoration = "underline";	}						// 4/30/09
        catch (e) { }
        }

    function checkAll() {	//	9/10/13
        var theField = document.add.elements["frm_group[]"];
        for (i = 0; i < theField.length; i++) {
            theField[i].checked = true ;
            }
        }

    function uncheckAll() {	//	9/10/13
        var theField = document.add.elements["frm_group[]"];
        for (i = 0; i < theField.length; i++) {
            theField[i].checked = false ;
            }
        }

/**
 *
 * @returns {undefined}
 */
    function do_grid_to_ll() {						// grid to LL array			- 12/4/08
<?php
    switch (get_variable("locale")) { 				// 4/23/11
        case 0:										// US NG
?>
            tolatlng = new Array();
            USNGtoLL(document.add.frm_ngs.value, tolatlng);					// returns array ?
            var point = new google.maps.LatLng(tolatlng[0].toFixed(6) ,tolatlng[1].toFixed(6));
            map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);

            var marker = new google.maps.Marker(point);
            document.add.frm_lat.value = point.lat(); document.add.frm_lng.value = point.lng();
            do_lat (point.lat());
            do_lng (point.lng());
//			do_ngs_utm(document.add);
            do_grids(document.add);			// 12/13/10
            load(point.lat(), point.lng(), <?php echo get_variable('def_zoom'); ?>);			// show it
<?php
        break;

        default:		// ROW
            print "\n\t alert('" . gettext('Unavailable') . "');\n;";
        }			// end switch($locale)
?>
        }				// end function

    var protocols = new Array();		// 7/7/09
    var fac_lat = [];
    var fac_lng = [];
/**
 *
 * @returns {Boolean}
 */
    function ReadOnlyCheckBox() {
        alert("<?php print gettext('You can\'t change this value');?>");

        return false;
    }

<?php
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]';";	//	6/10/11
    $result = mysql_query($query);	// 4/13/11
    $al_groups = array();
    while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	//	6/10/11
        $al_groups[] = $row['group'];
        }

    if (isset($_SESSION['viewed_groups'])) {	//	6/10/11
        $curr_viewed= explode(",",$_SESSION['viewed_groups']);
        }

    if (!isset($curr_viewed)) {
        if (count($al_groups == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
            $where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
            } else {
            $x=0;	//	6/10/11
            $where2 = "WHERE (";	//	6/10/11
            foreach ($al_groups as $grp) {	//	6/10/11
                $where3 = (count($al_groups) > ($x+1)) ? " OR " : ")";
                $where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
                $where2 .= $where3;
                $x++;
                }
            $where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
            }
        } else {
        if (count($curr_viewed == 0)) {	//	catch for errors - no entries in allocates for the user.	//	6/24/13
            $where2 = "WHERE `$GLOBALS[mysql_prefix]allocates`.`type` = 3";
            } else {
            $x=0;	//	6/10/11
            $where2 = "WHERE (";	//	6/10/11
            foreach ($curr_viewed as $grp) {	//	6/10/11
                $where3 = (count($curr_viewed) > ($x+1)) ? " OR " : ")";
                $where2 .= "`$GLOBALS[mysql_prefix]allocates`.`group` = '{$grp}'";
                $where2 .= $where3;
                $x++;
                }
            $where2 .= "AND `$GLOBALS[mysql_prefix]allocates`.`type` = 3";	//	6/10/11
            }
        }

        // Pulldown menu for use of Incident set at Facility 9/22/09, 3/18/10 - 2/12/11
    $query_fc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
        LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
        $where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
    $result_fc = mysql_query($query_fc) or do_error($query_fc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $pulldown = '<option value=0 selected>' . gettext('Incident at Facility') . '</option>\n';	// 3/18/10
        while ($row_fc = mysql_fetch_array($result_fc, MYSQL_ASSOC)) {
            $pulldown .= "<option value=\"{$row_fc['fac_id']}\">" . shorten($row_fc['name'], 30) . "</option>\n";
            print "\tfac_lat[" . $row_fc['fac_id'] . "] = " . $row_fc['lat'] . " ;\n";
            print "\tfac_lng[" . $row_fc['fac_id'] . "] = " . $row_fc['lng'] . " ;\n";

            }

        // Pulldown menu for use of receiving Facility 10/6/09, 3/18/10
    $query_rfc = "SELECT *, `$GLOBALS[mysql_prefix]facilities`.`id` AS `fac_id` FROM `$GLOBALS[mysql_prefix]facilities`
        LEFT JOIN `$GLOBALS[mysql_prefix]allocates` ON ( `$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]allocates`.`resource_id` )
        $where2 GROUP BY `$GLOBALS[mysql_prefix]facilities`.`id` ORDER BY `name` ASC";
    $result_rfc = mysql_query($query_rfc) or do_error($query_rfc, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $pulldown2 = '<option value = 0 selected>' . gettext("Receiving facility") . '</option>\n'; 	// 3/18/10
        while ($row_rfc = mysql_fetch_array($result_rfc, MYSQL_ASSOC)) {
            $pulldown2 .= "<option value=\"{$row_rfc['fac_id']}\">" . shorten($row_rfc['name'], 30) . "</option>\n";
            print "\tfac_lat[" . $row_rfc['fac_id'] . "] = " . $row_rfc['lat'] . " ;\n";
            print "\tfac_lng[" . $row_rfc['fac_id'] . "] = " . $row_rfc['lng'] . " ;\n";

            }

        // Pulldown menu for portal user association 9/10/13
    $query_pu = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `level` = " . $GLOBALS['LEVEL_SERVICE_USER'] . " ORDER BY `name_l` ASC";
    $result_pu = mysql_query($query_pu) or do_error($query_pu, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    if (mysql_affected_rows() > 0) {
        $has_portal = 1;
        $portal_user_control = "<SELECT NAME='frm_portal_user'>\n";
        $portal_user_control .= "<OPTION VALUE = 0 SELECTED>" . gettext('Select User') . "</OPTION>\n";
            while ($row_pu = mysql_fetch_array($result_pu, MYSQL_ASSOC)) {
                $theName = $row_pu['name_f'] . " " . $row_pu['name_l'] . " (" . $row_pu['user'] . ")";
                $portal_user_control .= "<OPTION VALUE=" . $row_pu['id'] . ">" . $theName . "</OPTION>\n";
                }
        $portal_user_control .= "</SELECT>\n";
        } else {
        $has_portal = 0;
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
        if (severities[in_val]>0) {document.add.frm_severity.selectedIndex = severities[in_val];};
        }
/**
 *
 * @param {type} the_url
 * @returns {unresolved}
 */
    function do_act_window(the_url) {				// 5/6/11
        newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
        if (newwindow == null) {
            alert ("<?php print gettext('Station log operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        newwindow.focus();
        }
/**
 *
 * @param {type} the_url
 * @returns {unresolved}
 */
    function do_pat_window(the_url) {				// 5/6/11
        newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=720,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
        if (newwindow == null) {
            alert ("<?php print gettext('Station log operation requires popups to be enabled. Please adjust your browser options.');?>");

            return;
            }
        newwindow.focus();
        }

</SCRIPT>
<STYLE TYPE="text/css">
.box { background-color: transparent; border: 0px solid #000000; color: #000000; padding: 0px; position: absolute; z-index:1000; }
.bar { background-color: #DEE3E7; color: #000000; cursor: move; font-weight: bold; padding: 2px 1em 2px 1em;  z-index:1000; }
.content { padding: 1em; }
</STYLE>
<SCRIPT SRC="./js/misc_function.js" type="text/javascript"></SCRIPT></head>
<?php
    if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {
        require_once './incs/socket2me.inc.php';		// 5/22/2013
        }
?>
</HEAD>
<?php
$from_left = 500;
$from_top = 150;				// 11/22/2012
$cid_lat = isset($cid_lat) ? $cid_lat : ""; // 8/8/11
$cid_lng = isset($cid_llng) ? $cid_lng : ""; // 8/8/11
$onload_str = "load(" .  get_variable('def_lat') . ", " . get_variable('def_lng') . "," . get_variable('def_zoom') . ");";
$onload_str .= (is_float($cid_lat))? " pt_to_map( add, {$cid_lat} ,{$cid_lng});": "";
//dump(__LINE__);
//dump(is_float($cid_lat));
//dump($onload_str);
?>
<BODY onLoad="ck_frames(); do_lock_pe(document.add); document.add.frm_street.focus(); <?php echo $onload_str ;?>">  <!-- <?php echo __LINE__;?> -->		<!-- // 8/23/08 -->
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
<div id = 'outer' style = "position:fixed; z-index: 998;">				<!-- 2/19/11 -->
<div id="boxB" class="box" style="left:<?php print $from_left;?>px; top:<?php print $from_top;?>px;">
  <div class="bar" STYLE="width:12em; color:red; background-color : transparent;"
       onmousedown="dragStart(event, 'boxB');"><i>&nbsp;&nbsp;&nbsp;&nbsp;<?php print gettext('Drag me');?></i></div>
  <div class="content" style="width:auto;">
          <INPUT TYPE="button" VALUE="<?php print gettext('History');?>"  onClick="do_hist_win();" STYLE = 'margin-top:0;'/><BR />
          <INPUT TYPE="button" VALUE="<?php print get_text("Cancel"); ?>"  onClick="do_cancel(document.add);" STYLE = 'margin-top:4px;'/><BR />
          <INPUT TYPE="reset" VALUE="<?php print get_text("Reset"); ?>" onclick= "do_reset(this.form);"  STYLE = 'margin-top:4px;'/><BR />
          <INPUT TYPE="button" VALUE="<?php print get_text("Next"); ?>"  onClick="validate(document.add);" STYLE = 'margin-top:4px;'/><BR />
<?php if (!($in_win )) { ?>
          <INPUT TYPE="button" VALUE="<?php print get_text("Action"); ?>"  onClick="do_act_window('action_w.php?ticket_id=<?php echo $ticket_id;?>');" STYLE = 'margin-top:4px;'/> <BR />
          <INPUT TYPE="button" VALUE="<?php print get_text("Patient"); ?>"  onClick="do_pat_window('patient_w.php?ticket_id=<?php echo $ticket_id;?>');" STYLE = 'margin-top:4px;'/>  <!-- 6/23/11 -->
<?php } ?>
  </div>
</div>
</div>

<?php
require_once './incs/links.inc.php';

//$inc_num_ary = unserialize (get_variable('_inc_num'));											// 11/13/10
$temp = get_variable('_inc_num');										// 3/2/11
$inc_num_ary = (strpos($temp, "{")>0)?  unserialize ($temp) :  unserialize (base64_decode($temp));
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

if (!(mysql_table_exists("$GLOBALS[mysql_prefix]places"))) {$city_name_array_str="";}		// 2/21/11 - build array of city names for JS usage
else {
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]places` ORDER BY `id`";		// get all names
    $place_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    $num_places = mysql_num_rows($result);	//	3/17/11
    $city_name_array_str = $sep = "";
    while ($place_row = stripslashes_deep(mysql_fetch_assoc($place_result))) {
        $city_name_array_str .= "{$sep}'{$place_row['name']}'";
        $sep =",";
        }
    }
                        // apply cid data if available
$street = isset($cid_street) ?  $cid_street : $res_row['street'] ;

if (isset($cid_city)) {
    $city = $cid_city;
    }
else {
    $city = (!(empty($res_row['city'])))?	$res_row['city']  : get_variable('def_city');		// 11/5/10, 3/17/11
    }
if (isset($cid_state)) {
    $st = $cid_state;
    }
else {
    $st = (!(empty($res_row['state'])))?	$res_row['state'] : get_variable('def_st') ;
    }
$st_size = (get_variable("locale") ==0)?  2: 4;												// 11/23/10
$phone = (isset($cid_phone))? format_phone($cid_phone): get_variable('def_area_code');
$reported_by = (isset($cid_name))?	$cid_name: "TBD";
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]' ORDER BY `id` ASC;";	// 6/10/11
$result = mysql_query($query);	// 6/10/11
$al_groups = array();
$al_names = "";
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {	// 6/10/11
    $al_groups[] = $row['group'];
    $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]region` WHERE `id`= '$row[group]';";	// 6/10/11
    $result2 = mysql_query($query2);	// 6/10/11
    while ($row2 = stripslashes_deep(mysql_fetch_assoc($result2))) {	// 6/10/11
            $al_names .= $row2['group_name'] . ", ";
        }

    }
if (is_super()) {
    $al_names .= "&nbsp;&nbsp;" . gettext('Superadmin Level') . '"';
}

if (isset($_SESSION['viewed_groups'])) {	//	6/10/11
    $curr_viewed= explode(",",$_SESSION['viewed_groups']);
    } else {
    $curr_viewed = $al_groups;
    }

$curr_names="";	//	6/10/11
$z=0;	//	6/10/11
foreach ($curr_viewed as $grp_id) {	//	6/10/11
    $counter = (count($curr_viewed) > ($z+1)) ? ", " : "";
    $curr_names .= get_groupname($grp_id);
    $curr_names .= $counter;
    $z++;
    }

$heading = gettext("Add Ticket") . " - " . get_variable('map_caption');
if ((get_num_groups()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
    $regs_string = "<FONT SIZE='-1'>" . gettext('Allocated') . " " . get_text("Regions") . ":&nbsp;&nbsp;" . $al_names . "&nbsp;&nbsp;|&nbsp;&nbsp;" . gettext('Currently Viewing') . " " . get_text("Regions") . ":&nbsp;&nbsp;" . $curr_names . "</FONT>";	//	6/10/11
} else {
    $regs_string = "";
}
?>
<SCRIPT>
var obj_sugg;
/**
 *
 * @returns {undefined}
 */
 function createAutoComplete() {
// 	alert(<?php print __LINE__;?>);
    obj_sugg = new autoComplete(aNames,document.getElementById('my_txt'),document.getElementById('suggest'),50);
    }
 var aNames =[<?php print $city_name_array_str;?>];
/**
 *
 * @param {type} the_form
 * @returns {unresolved}
 */
function do_nearby(the_form) {		// 11/22/2012
    if (the_form.frm_lat.value.length == 0) {
        alert("<?php print gettext('Map Location is required for nearby Incident lookup.');?>");

        return;
        }
    var the_url = "nearby.php?tick_lat="+the_form.frm_lat.value+"&tick_lng="+the_form.frm_lng.value;
    newwindow=window.open(the_url, "new_window",  "titlebar, location=0, resizable=1, scrollbars, height=480,width=960,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
    if (newwindow == null) {
        alert ("<?php print gettext('Nearby operation requires popups to be enabled. Please adjust your browser options.');?>");

        return;
        }
    newwindow.focus();
    }		// end do_nearby()

</SCRIPT>
<DIV>
<TABLE BORDER="0" ID = "form_outer" >
<TR CLASS='header'><TD COLSPAN='99' ALIGN='center'><FONT CLASS='header' STYLE='background-color: inherit;'><?php print $heading; ?> </FONT></TD></TR>	<!-- 6/10/11 -->
<TR CLASS='spacer'><TD CLASS='spacer' COLSPAN='99' ALIGN='center'>&nbsp;</TD></TR>				<!-- 6/10/11 -->
<TR><TD>
<TABLE BORDER="0"><BR />
<TR><TD ALIGN='center' COLSPAN='3'><FONT CLASS='header'><FONT COLOR='green'><?php print gettext('New Call');?></FONT></FONT><BR />
    <FONT SIZE=-1>(<?php print gettext('mouseover caption for help information');?>)</FONT></FONT><BR /><BR /></TD>
    </TR>
<FORM NAME="add" METHOD="post" ENCTYPE="multipart/form-data" ACTION="<?php print basename(__FILE__);?>?add=true" onSubmit="return validate(document.add);">
<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_loca"];?>');"><?php print get_text("Location"); ?></A>:</TD>
    <TD></TD>
    <TD><INPUT NAME="frm_street" tabindex=1 SIZE="72" TYPE="text" VALUE="<?php print $street;?>" MAXLENGTH="96"></TD>
    </TR>
<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print gettext('About Address - for instance, round the back, building number etc.');?>');"><?php print get_text("Address About"); ?></A>:</TD>	<!-- 9/10/13 -->
    <TD></TD>
    <TD><INPUT NAME="frm_address_about" tabindex=1 SIZE="72" TYPE="text" VALUE="" MAXLENGTH="512"></TD>
    </TR>
<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_city"];?>');"><?php print get_text("City");?>:</TD>
    <TD ALIGN='center' ><BUTTON type="button" onClick="Javascript:loc_lkup(document.add);return false;"><img src="./markers/glasses.png" alt="<?php print gettext('Lookup location.');?>" /></BUTTON>&nbsp;&nbsp;</TD>
    <TD><INPUT ID="my_txt"  onFocus = "createAutoComplete();$('city_reset').visibility='visible';" NAME="frm_city" autocomplete="off" tabindex=2 SIZE="32" TYPE="text" VALUE="<?php print $city; ?>" MAXLENGTH="32" onChange = " $('city_reset').visibility='visible'; this.value=capWords(this.value);">
        <span id="suggest" onmousedown="$('suggest').style.display='none'; $('city_reset').style.visibility='visible';" style="visibility:hidden;border:#000000 1px solid;width:150px;right:400px;" /></span>
        <IMG ID = 'city_reset' SRC="./markers/reset.png" STYLE = "margin-left:20px; visibility:hidden;" onClick = "this.style.visibility='hidden'; document.add.frm_city.value=''; document.add.frm_city.focus(); obj_sugg = null; ">
<?php
    if ($gmaps) {		// 12/1/2012
?>
        <BUTTON type="button" onClick="Javascript:do_nearby(this.form);return false;"><?php print gettext('Nearby?');?></BUTTON> <!-- 11/22/2012 -->
<?php
    }
?>
        <SPAN CLASS="td_label" STYLE='margin-left:20px;' onmouseout="UnTip();" onmouseover="Tip('<?php print $titles['_state'];?>');"><?php print get_text("St"); ?></SPAN>:&nbsp;&nbsp;
        <INPUT NAME="frm_state" tabindex=3 SIZE="<?php print $st_size;?>" TYPE="text" VALUE="<?php print $st; ?>" MAXLENGTH="<?php print $st_size;?>">
        <DIV id='loc_warnings' style='z-index: 1000; display: none; height: 100px; width: 300px; font-size: 1.5em; font-weight: bold; border: 1px outset #707070;'></DIV>		<!-- 9/10/13 -->
        </TD>
    </TR>
<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_phone"];?>');"><?php print get_text("Phone");?></A>:</TD>
    <TD ALIGN='center' ><BUTTON type="button" onClick="Javascript:phone_lkup(document.add.frm_phone.value);"><img src="./markers/glasses.png" alt="<?php print gettext('Lookup phone number');?>" ></button>&nbsp;&nbsp;</TD>
    <TD><INPUT NAME="frm_phone"  tabindex=4 SIZE="16" TYPE="text" VALUE="<?php print $phone;?>"  MAXLENGTH="16">&nbsp;<SPAN ID='repeats'></SPAN></TD>
    </TR>
<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print gettext('To address - Not plotted on map, for information only');?>');"><?php print get_text("To address");?></A>:</TD>	<!-- 9/10/13 -->
    <TD></TD>
    <TD><INPUT NAME="frm_to_address" tabindex=4 SIZE="72" TYPE="text" VALUE=""  MAXLENGTH="1024"></TD>
    </TR>
<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_nature"];?>');"><?php print $nature;?>: <font color='red' size='-1'>*</font></TD>
    <TD></TD>
    <TD>
        <SELECT NAME="frm_in_types_id"  tabindex=5 onChange="do_set_severity (this.selectedIndex); do_inc_name(this.options[selectedIndex].text.trim(), this.options[selectedIndex].value.trim());">	<!--  10/4/08 -->
        <OPTION VALUE=0 SELECTED><?php print gettext('TBD');?></OPTION>				<!-- 1/11/09 -->
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
                print "\n<SCRIPT>\n\t protocols[{$temp_row['id']}] = \"{$temp}\";\n</SCRIPT>\n";		// 7/16/09, 5/6/10
                }
            $i++;
            }		// end while()
        print "\n</OPTGROUP>\n";
?>

    </SELECT>

    <SPAN CLASS="td_label" STYLE='margin-left:20px' onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_prio"];?>');"><?php print get_text("Priority");?></SPAN>:

    <SELECT NAME="frm_severity" tabindex=6>
    <OPTION VALUE="0" SELECTED><?php print get_severity($GLOBALS['SEVERITY_NORMAL']);?></OPTION>
    <OPTION VALUE="1"><?php print get_severity($GLOBALS['SEVERITY_MEDIUM']);?></OPTION>
    <OPTION VALUE="2"><?php print get_severity($GLOBALS['SEVERITY_HIGH']);?></OPTION>
    </SELECT>

    </TD>
    </TR>
<TR CLASS='odd'>	<!--  3/15/11 -->
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_proto"];?>');"><?php print get_text("Protocol");?></A>:</SPAN></TD>
    <TD></TD>
    <TD ID='proto_cell'></TD>
    </TR>
<?php
if (get_num_groups()) {
    if ((is_super()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {		//	6/10/11
?>
        <TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
        <TD CLASS="td_label" onmouseout='UnTip();' onmouseover="Tip('<?php print gettext('Sets groups that Incident is allocated to - click + to expand, - to collapse');?>');"><?php print get_text("Regions");?></A>: </TD>
        <TD>
        <SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
        <SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
        <TD>
            <DIV id='checkButts' style='display: none;'>
                <SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'><?php print gettext('Check All');?></SPAN>
                <SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'><?php print gettext('Uncheck All');?></SPAN>
            </DIV>
<?php
        $alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
        print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11
?>
        </TD></TR>
<?php
        } elseif ((is_admin()) && (COUNT(get_allocates(4, $_SESSION['user_id'])) > 1)) {	//	6/10/11
?>
        <TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
        <TD CLASS="td_label" onmouseout='UnTip();' onmouseover="Tip('<?php print gettext('Sets groups that Incident is allocated to - click + to expand, - to collapse');?>');"><?php print get_text("Regions");?></A>: </TD>
        <TD>
        <SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
        <SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
        <TD>
            <DIV id='checkButts' style='display: none;'>
                <SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'>Check All</SPAN>
                <SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'>Uncheck All</SPAN>
            </DIV>
<?php
        $alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
        print get_user_group_butts(($_SESSION['user_id']));	//	6/10/11
?>
        </TD></TR>
<?php
        } else {
?>
        <TR CLASS='even' VALIGN="top">	<!--  6/10/11 -->
        <TD CLASS="td_label" onmouseout='UnTip();' onmouseover="Tip('Sets groups that Incident is allocated to - click + to expand, - to collapse');"><?php print get_text("Regions");?></A>:
        </TD>
        <TD>
        <SPAN id='expand_gps' onClick="$('checkButts').style.display = 'inline-block'; $('groups_sh').style.display = 'inline-block'; $('expand_gps').style.display = 'none'; $('collapse_gps').style.display = 'inline-block';" style = 'display: inline-block; font-size: 16px; border: 1px solid;'><B>+</B></SPAN>
        <SPAN id='collapse_gps' onClick="$('checkButts').style.display = 'none'; $('groups_sh').style.display = 'none'; $('collapse_gps').style.display = 'none'; $('expand_gps').style.display = 'inline-block';" style = 'display: none; font-size: 16px; border: 1px solid;'><B>-</B></SPAN></TD>
        <TD>
            <DIV id='checkButts' style='display: none;'>
                <SPAN id='checkbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='checkAll();'><?php print gettext('Check All');?></SPAN>
                <SPAN id='uncheckbut' class='plain' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick='uncheckAll();'><?php print gettext('Uncheck All');?></SPAN>
            </DIV>
<?php
        $alloc_groups = implode(',', get_allocates(4, $_SESSION['user_id']));	//	6/10/11
        print get_user_group_butts_readonly($_SESSION['user_id']);	//	6/10/11
?>
        </TD></TR>
<?php
        }
    } else {
?>
        <INPUT TYPE="hidden" NAME="frm_group[]" VALUE="1"/>	 <!-- 6/10/11 -->
<?php
    }//	end if num of groups is greater than 1 (i.e. non multi-region system
?>
    <TR class='spacer'><TD class='spacer' COLSPAN=99>&nbsp;</TD></TR>
    <TR CLASS='odd' VALIGN="top">	<!--  3/15/11 -->
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_synop"];?>');"><?php print get_text("Synopsis");?></A>: </TD>
    <TD></TD>
    <TD><TEXTAREA NAME="frm_description"  tabindex=7 COLS="48" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
    </TR>
<?php				//2/11/11
        $query_sigs = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
        $result_sigs = mysql_query($query_sigs) or do_error($query_sigs, 'mysql query_sigs failed', mysql_error(),basename( __FILE__), __LINE__);

        if (mysql_num_rows($result_sigs)>0) {
?>

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
                    print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT>
            </TD>
    </TR>
<?php
        }		// end if (mysql_num_rows($result_sigs)>0)
?>

<TR CLASS='odd'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_911"];?>');"><?php print get_text("911 Contacted"); ?></A>:&nbsp;</TD>
    <TD></TD>
    <TD><INPUT NAME="frm_nine_one_one"  tabindex=8 SIZE="56" TYPE="text" VALUE="" MAXLENGTH="96" /></TD>
    </TR>

<TR CLASS='even'>
    <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_caller"];?>');"><?php print get_text("Reported by");?></A>:&nbsp;<FONT COLOR='RED' SIZE='-1'>*</FONT></TD>
    <TD></TD>
    <TD><INPUT NAME="frm_contact"  tabindex=9 SIZE="56" TYPE="text" VALUE="<?php print $reported_by; ?>" MAXLENGTH="48" onFocus ="Javascript: if (this.value.trim()=='TBD') {this.value='';}"></TD>
    </TR>
<?php
if ($has_portal == 1) {
?>
    <TR CLASS='even'>	<!-- 9/10/13 - associate Ticket with Portal User -->
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print gettext('Associate this ticket with a specific portal user so they can see it.');?>');"><?php print get_text("Portal User");?></A>:&nbsp;</TD>
        <TD></TD>
        <TD><?php print $portal_user_control;?></TD>
    </TR>
<?php
    } else {
?>
    <INPUT TYPE='hidden' NAME="frm_portal_user" VALUE=0/>
<?php
    }
?>
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
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_name"];?>');"><?php print get_text("Incident name");?></A>: <font color='red' size='-1'>*</font></TD>
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
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_booked"];?>');"><?php print get_text("Scheduled Date");?></A>:
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
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_facy"];?>');"><?php print gettext('Facility?');?></A>:&nbsp;&nbsp;&nbsp;&nbsp;</TD>	 <!-- 9/22/09 -->
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
        <TD><INPUT SIZE="48" TYPE="text" 	NAME="frm_affected" VALUE="" MAXLENGTH="48" /></TD>
    </TR>
-->
    <TR CLASS='even' VALIGN='bottom'>
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_start"];?>');"><?php print get_text("Run Start");?></A>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
        <TD ALIGN='center' ><img id='lock_s' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_ps(document.add);' /></TD>
        <TD>
<?php print generate_date_dropdown('problemstart',0,TRUE);?>
        <SPAN CLASS="td_label" STYLE='margin-left:12px' onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_status"];?>');"><?php print get_text("Status");?>:</SPAN>
        <SELECT NAME='frm_status'><OPTION VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' selected><?php print gettext('Open');?></OPTION>
        <OPTION VALUE='<?php print $GLOBALS['STATUS_CLOSED']; ?>'><?php print gettext('Closed');?></OPTION>
        <OPTION VALUE='<?php print $GLOBALS['STATUS_SCHEDULED']; ?>'><?php print gettext('Scheduled');?></OPTION></SELECT>

        </TD>
    </TR>
    <TR CLASS='odd' valign="middle">
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_end"];?>');"><?php print get_text("Run End");?></A>:
        </TD>
        <TD ALIGN='center' ><input type="radio" name="re_but" onClick ="do_end(this.form);" /></TD>
        <TD>
            <SPAN style = "visibility:hidden" ID = "runend1"><?php print generate_date_dropdown('problemend',0, TRUE);?></SPAN>
        </TD>
    </TR>
    <TR CLASS='even' VALIGN="top">
        <TD CLASS="td_label" onmouseout="UnTip();" onmouseover="Tip('<?php print $titles["_disp"];?>');"><?php print $disposition;?></A>:</TD>
        <TD></TD>
        <TD><TEXTAREA NAME="frm_comments" COLS="45" ROWS="2" WRAP="virtual"></TEXTAREA></TD>
        </TR>
<?php											// 2/8/11
    if (mysql_num_rows($result_sigs)>0) {
?>
    <TR VALIGN = 'TOP' CLASS='even'>		<!-- 11/15/10 -->
        <TD></TD>
        <TD></TD>
        <TD CLASS="td_label"><?php print gettext('Signal');?> &raquo;

            <SELECT NAME='signals' onChange = 'set_signal2(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
                <OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
//				$query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";		// 12/18/10
//				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result_sigs))) {
                    print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|" . shorten($row_sig['text'], 32) . "</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT>
            </TD>
    </TR>
<?php
        }				// end if (mysql_num_rows($result_sigs)>0)
    if (($gmaps) && (!($in_win))) {
?>

    <TR CLASS='odd'>
        <TD CLASS="td_label">
            <SPAN ID="pos" onClick = 'javascript: do_coords(document.add.frm_lat.value, document.add.frm_lng.value );'>
            <U><A HREF="#" TITLE="<?php print $titles["_coords"];?>"><?php print $incident;?> <?php print gettext('Lat/Lng');?></A></U></SPAN>:
                <font color='red' size='-1'>*</font>
        </TD>
        <TD ALIGN='center' ><img id='lock_p' border=0 src='./markers/unlock2.png' STYLE='vertical-align: middle' onClick = 'do_unlock_pos(document.add);'></TD>
        <TD><INPUT SIZE="11" TYPE="text" NAME="show_lat" VALUE="" />
            <INPUT SIZE="11" TYPE="text" NAME="show_lng" VALUE="" />&nbsp;&nbsp;
<?php
    $locale = get_variable('locale');						// 08/03/09
    $grid_types = array("USNG", "OSGB", "UTM");				// 4/23/11
    print "<B><SPAN ID = 'grid_link' onClick = 'do_grid_to_ll();'>{$grid_types[$locale]}:</SPAN></B>&nbsp;<INPUT SIZE='19' TYPE='text' NAME='frm_ngs' VALUE='' DISABLED ></TD></TR>";
    }		// end if ($gmaps)
?>
    <TR CLASS='even'>
        <TD COLSPAN="3" ALIGN="center"><br /><IMG SRC="glasses.png" BORDER="0"/>: <?php print gettext('Lookup');?> </TD>
    </TR>
    <TR class='spacer'>
        <TD COLSPAN='3' class='spacer'>&nbsp;</TD>
    </TR>
    <TR class='spacer'>
        <TD COLSPAN='3' class='spacer'>&nbsp;</TD>
    </TR>
    <TR class='heading'>
        <TD COLSPAN='3' class='heading' style='text-align: center;'><?php print gettext('File Upload');?></TD>
    </TR>
    <TR class='even'>
        <TD class='td_label' COLSPAN='2' style='text-align: left;'><?php print gettext('Choose a file to upload');?>:</TD>
        <TD class='td_data' style='text-align: left;'><INPUT NAME="frm_file" TYPE="file" /></TD>
    </TR>
    <TR class='odd'>
        <TD class='td_label' COLSPAN='2' style='text-align: left;'><?php print gettext('File Name');?></TD>
        <TD class='td_data' style='text-align: left;'><INPUT NAME="frm_file_title" TYPE="text" SIZE="48" MAXLENGTH="128" VALUE="" /></TD>
    </TR>
    <TR class='even'>
        <TD COLSPAN='3'>&nbsp;</TD>
    </TR>
        <INPUT TYPE="hidden" NAME="frm_lat" VALUE="" />				<!-- // 9/9/08 -->
        <INPUT TYPE="hidden" NAME="frm_lng" VALUE="" />
        <INPUT TYPE="hidden" NAME="ticket_id" VALUE="<?php print $ticket_id;?>" />	<!-- 1/25/09, 3/10/09 -->
        <INPUT TYPE='hidden' NAME="frm_do_scheduled" VALUE=0 />	<!-- 1/1/11 -->
    </FORM></TABLE>
    </TD>
<?php
        if (($gmaps) && (!($in_win))) {
?>

    <TD>
        <TABLE ID='four' border=0>
            <TR>
                <TD id='three' ALIGN='center'>
                    <div id='map_canvas' style='z-index:1; width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px'>
                    </div>
                    <BR /><CENTER><FONT CLASS='header'><?php echo get_variable('map_caption');?></FONT><BR /><BR />
                    <SPAN ID='do_grid' onclick = "toglGrid();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<U><?php print gettext('Grid');?></U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <SPAN ID='do_sv' onClick = "sv_win(document.add);" style='display:none'><U><?php print gettext('Street view');?></U></SPAN> <!-- 2/11/09 -->
                </TD>
            </TR>
        </TABLE>
    </TD>
<?php
    }
?>
    </TR>
    </TABLE></DIV>

<?php
//	dump($_SESSION['user_id']);
    } //end if/else
?>
<FORM NAME='can_Form' ACTION="main.php">
</FORM>
</BODY></HTML>
