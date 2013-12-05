<?php
/**
 *
 *
 * @package insert_ticket.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */
require_once '../../incs/functions.inc.php';
@session_start();
$by = $_SESSION['user_id'];
$now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
$regions = array();
/**
 *
 * @param type $the_id
 * @return type
 */
function get_requester_details($the_id) {
    $the_ret = array();
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE `id` = " . $the_id . " LIMIT 1";
    $result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) == 1) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        if ($row['email'] == "") {
            if ($row['email_s'] == "") {
                $the_ret[0] = "";
                } else {
                $the_ret[0] = $row['email_s'];
                }
            } else {
                $the_ret[0] = $row['email'];
            }
        } else {
        $the_ret[0] = "";
        }
        $the_ret[1] = $row['user'];

    return $the_ret;
    }
/**
 *
 * @param type $id
 * @return string
 */
function get_facname($id) {
    $the_ret = array();
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $id . " LIMIT 1";
    $result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    if (mysql_num_rows($result) == 1) {
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        $the_ret[0] = ($row['name'] != "") ? $row['name'] : "NA";
        $street = ($row['street'] != "") ? $row['street'] : "";
        $the_ret[1] = ($street != "") ? $street . ", " . $row['city'] . ", " . $row['state']: "";
        $the_ret[2] = "Phone: " . $row['contact_phone'];
        } else {
        $the_ret[0] = "";
        $the_ret[1] = "";
        $the_ret[2] = "";
        }

    return $the_ret;
    }

$query = "SELECT * FROM `$GLOBALS[mysql_prefix]allocates` WHERE `type`= 4 AND `resource_id` = '$_SESSION[user_id]'";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
    $regions[] = $row['group'];
    }

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `status` = 'Accepted', `accepted_date` = '" .$now . "' WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
$query = "SELECT * FROM `$GLOBALS[mysql_prefix]requests` WHERE `id` = " . strip_tags($_GET['id']) . " LIMIT 1";
$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
$row = stripslashes_deep(mysql_fetch_assoc($result));
$theLat = ($row['lat'] == NULL) ? 0.999999 : $row['lat'];
$theLng = ($row['lng'] == NULL) ? 0.999999 : $row['lng'];
$theDetails = get_requester_details($row['requester']);
$the_email = $theDetails[0];
$the_requester = strip_tags($theDetails[1]);
$description = (($row['description'] == "") && ($row['comments'] == "")) ? "New Ticket from Portal - Accepted " . $now : $row['description'] . $row['comments'];
$ret_arr = array();
$query = "INSERT INTO `$GLOBALS[mysql_prefix]ticket` (
                `in_types_id`,
                `org`,
                `contact`,
                `street`,
                `city`,
                `state`,
                `phone`,
                `to_address`,
                `facility`,
                `rec_facility`,
                `lat`,
                `lng`,
                `booked_date`,
                `problemstart`,
                `scope`,
                `description`,
                `status`,
                `owner`,
                `severity`,
                `updated`,
                `_by`
            ) VALUES (
                0,
                0,
                '" . $row['the_name'] . "',
                '" . $row['street'] . "',
                '" . $row['city'] . "',
                '" . $row['state'] . "',
                '" . $row['phone'] . "',
                '" . $row['to_address'] . "',
                " . $row['orig_facility'] . ",
                " . $row['rec_facility'] . ",
                " . $theLat . ",
                " . $theLng . ",
                '" . $row['request_date'] . "',
                 " . quote_smart(trim($now)) . ",
                '" . $row['scope'] . "',
                '" . $description . "',
                2,
                " . $by . ",
                0,
                 " . quote_smart(trim($now)) . ",
                " . $by . ")";

$result	= mysql_query($query) or do_error($query,'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
if ($result) {
    $last_id = mysql_insert_id();
    } else {
    $last_id = 0;
    }

$query = "UPDATE `$GLOBALS[mysql_prefix]requests` SET `ticket_id` = " . $last_id . " WHERE `id` = " . strip_tags($_GET['id']);
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

foreach ($regions as $grp_val) {
        $query  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                ($grp_val, 1, '$now', 2, $last_id, 'Allocated to Group' , $by)";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    }

do_log($GLOBALS['LOG_INCIDENT_OPEN'], $last_id);
if ($last_id != 0) {
    $ret_arr[0] = $last_id;
    $the_summary = "Request from " . $the_requester . "\r\n";
    $the_summary .= get_text('Scope') . ": " . $row['scope'] . "\r\n\r\n";
    $the_summary .= get_text('Patient') . " name: " . $row['the_name'] . "\r\n";
    $the_summary .= get_text('Street') . ": " . $row['street'] . ", ";
    $the_summary .= get_text('City') . ": " . $row['city'] . ", ";
    $the_summary .= get_text('State') . ": " . $row['state'] . "\r\n";
    $the_summary .= get_text('Contact Phone') . ": " . $row['phone'] . "\r\n";
    $orig_Fac = ($row['orig_facility'] != "0") ? get_facname($row['orig_facility']) : "";
    $rec_Fac =  ($row['rec_facility'] != "0") ? get_facname($row['rec_facility']) : "";
    $the_summary .= ((is_array($orig_Fac)) && ($orig_Fac[0] != "")) ? "Originating Facility " . $orig_Fac[0] . "\nAddress: " . $orig_Fac[1] . "\nPhone " . $orig_Fac[2] . "\r\n" : "";
    $the_summary .= ((is_array($rec_Fac)) && ($rec_Fac[0] != "")) ? "Receiving Facility " . $rec_Fac[0] . "\nAddress: " . $rec_Fac[1] . "\nPhone " . $rec_Fac[2] . "\r\n" : "";
    $the_summary .= get_text('Description') . "\r\n" . $description . "\r\n";
    $the_summary .= get_text('Comments') . "\r\n" . $row['comments'] . "\r\n";
    $the_summary .= get_text('Request Date') . ": " . format_date_2(strtotime($row['request_date'])) . "\r\n";

    if ($the_email != "") {				// any addresses?
        $to_str = $the_email;
        $smsg_to_str = "";
        $subject_str = "Your request " . $row['scope'] . " has been accepted";
        $text_str = "Your Request " . $row['scope'] . " accepted\r\n";
        $text_str .= "Please check on the portal for further status updates\r\n";
        $text_str .= "Request Summary\n\n" . $the_summary;
        do_send ($to_str, $smsg_to_str, $subject_str, $text_str, 0, 0);
        }				// end if/else ($the_email)
    } else {
    $ret_arr[0] = 999;
    }

print json_encode($ret_arr);
