<?php
include'./incs/error_reporting.php';

session_start();
//require_once($_SESSION['fip']);				// 7/28/10
require_once './incs/functions.inc.php';		// 9/29/10
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);					// 9/29/10
if ($istest) {
    dump ($_POST);
    dump ($_GET);
    }

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

$evenodd = array ("even", "odd");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD><TITLE><?php print gettext('Tickets - Search Module');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>	<!-- 3/15/11 -->
<?php
$api_key = trim(get_variable('gmaps_api_key'));
$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
	$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	} else {
	$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
	}
?>
<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT>

<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT> 	<!-- 4/23/13 -->
<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>	<!-- 4/23/13 -->
<SCRIPT TYPE="text/javascript" SRC="./js/misc_function.js"></SCRIPT>	<!-- 5/3/11 -->
<SCRIPT>
/**
 *
 * @returns {undefined}
 */
function ck_frames() {		//  onLoad = "ck_frames()"
    if (self.location.href==parent.location.href) {
        self.location.href = 'index.php';
        }
    else {
        parent.upper.show_butts();										// 1/21/09
        }
    }

try {
    parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
    parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
    parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
    }
catch(e) {
    }
/**
 *
 * @returns {undefined}
 */
function get_new_colors() {								// 4/5/11
    window.location.href = '<?php print basename(__FILE__);?>';
    }

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
 * @param {type} theForm
 * @returns {Boolean}
 */
function validate(theForm) {
    function TrimString(sInString) {
        sInString = sInString.replace( /^\s+/g, "" );// strip leading

        return sInString.replace( /\s+$/g, "" );// strip trailing
        }
    theForm.frm_query.value = TrimString(document.queryForm.frm_query.value);

    return true;
    }				// end function validate(theForm)
</SCRIPT>
</HEAD>

<BODY onLoad = "ck_frames();">
<?php
    $post_frm_query = (array_key_exists('frm_query', $_POST)) ? strip_tags($_POST['frm_query']) : FALSE ;		// 1/6/2013

    if ($post_frm_query) {


//		$query_str = quote_smart(trim($_POST['frm_query']));		// 7/20/10

        print "<BR /><SPAN STYLE = 'margin-left:80px;'><FONT CLASS='header'>" . gettext('Search results for') . " '$_POST[frm_query]'</FONT></SPAN><BR /><BR />\n";
        $_POST['frm_query'] = ereg_replace(' ', '|', $_POST['frm_query']);
        $query_str = quote_smart(trim(ereg_replace(' ', '|', $_POST['frm_query'])));
        if ($_POST['frm_search_in']) {								//what field are we searching?
            $search_fields = "{$_POST['frm_search_in']} REGEXP '$_POST[frm_query]'";	//
            }
        else {							//list fields and form the query to search all of them
            $result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]ticket`");
            $search_fields = "";
            $ok_types = array("string", "blob");
            for ($i = 0; $i < mysql_num_fields($result); $i++) {
                if (in_array (mysql_field_type($result, $i), $ok_types )) {
                    $search_fields .= mysql_field_name($result, $i) ." REGEXP {$query_str} OR ";
                    }
                }
            $search_fields = substr($search_fields,0,strlen($search_fields) - 4);		// drop trailing OR
            }
        if (get_variable('restrict_user_tickets') && !(is_administrator()))		//is user restricted to his/her own tickets?
            $restrict_ticket = "AND owner='{$_SESSION['user_id']}'";
        else{
            $restrict_ticket = "";
            }
        $desc = isset($_POST['frm_order_desc'])? $_POST['frm_order_desc'] :  "";		// 9/19/08

// ___________________________________  NEW STUFF __________________	9/30/10
        $id_stack= array();
        $query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]ticket`  WHERE `status` <> {$GLOBALS['STATUS_RESERVED']} AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . " AND " . $search_fields . " " . $restrict_ticket . " ORDER BY `" . $_POST['frm_ordertype'] . "` " . $desc;		// 9/19/08
        $result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

        $tick_hits = mysql_affected_rows();
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            array_push($id_stack, $row['id']);
            }
        $query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]patient`
            WHERE `description` REGEXP " . quote_smart($_POST['frm_query']) . " OR `name` REGEXP " . quote_smart($_POST['frm_query']) ;
        $result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);
        $per_hits = mysql_affected_rows();
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            array_push($id_stack, $row['ticket_id']);
            }

        $query = "SELECT `ticket_id` FROM `$GLOBALS[mysql_prefix]action`
            WHERE `description` REGEXP " . quote_smart($_POST['frm_query']);		// 9/19/08
        $result = mysql_query($query) or do_error('','', mysql_error(),basename( __FILE__), __LINE__);
        $act_hits = mysql_affected_rows();

        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            array_push($id_stack, $row['ticket_id']);
            }

        if (empty($id_stack )) {
            print "<SPAN STYLE = 'margin-left:80px'><B>" . gettext('No matches found') . "</B></SPAN><BR /><BR />";
            }
        else {
            $id_stack = array_unique($id_stack);		// at least one

            $in_str = $sep = "";
            for ($i=0; $i< count($id_stack); $i++) {
                $in_str .= "{$sep}'{$id_stack[$i]}'";
                $sep = ", ";
                }

            $query = "SELECT `id`, UNIX_TIMESTAMP(`problemstart`) AS `problemstart`, UNIX_TIMESTAMP(`updated`) AS `updated`, `scope`, `status`, `severity`,
                CONCAT_WS(' ',`street`,`city`,`state`) AS `addr`
                FROM `$GLOBALS[mysql_prefix]ticket`
                WHERE `status` <> {$GLOBALS['STATUS_RESERVED']}
                AND `id` IN ({$in_str})
                AND `status` LIKE " . quote_smart($_POST['frm_querytype']) . "
                ORDER BY `severity` DESC, `problemstart` ASC";
    //		dump ($query);

            $result = mysql_query($query) or do_error($query,'', mysql_error(),basename( __FILE__), __LINE__);

    // ___________________________________  END NEW STUFF __________________
//			dump(mysql_num_rows($result));

            if (mysql_num_rows($result) == 1) {	//	revised to redirect to main.php rather than show ticket in search.php	4/29/13
                $row = stripslashes_deep(mysql_fetch_assoc($result));
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', FALSE);
                header('Pragma: no-cache');

                $host  = $_SERVER['HTTP_HOST'];
                $url = "main.php?id=" . $row['id'];
                redir($url);
                exit();

                }
            elseif (mysql_num_rows($result) == 0) {
                print "<SPAN STYLE = 'margin-left:80px'><B>" . gettext('No matches found') . "</B></SPAN><BR /><BR />";

                }
            else {		//  more than one, list them
                print "<SPAN STYLE = 'margin-left:80px'><B>" . gettext('Matches') . "</B>: " . gettext('tickets') . " {$tick_hits}, " . gettext('actions') . " {$act_hits}, " . gettext('persons') . " {$per_hits}</SPAN><BR /><BR />";

                print "<TABLE BORDER='0'><TR CLASS='even'>
                    <TD CLASS='td_header'><SPAN STYLE = 'margin-left:2px;'>" . gettext('Ticket') . "</SPAN></TD>
                    <TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>" . gettext('Opened') . "</SPAN></TD>
                    <TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>" . gettext('Description') . "</SPAN></TD>
                    <TD CLASS='td_header'><SPAN STYLE = 'margin-left:20px;'>" . gettext('Location') . "</SPAN></TD></TR>";
                $counter = 0;

                while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {				// 8/28/08
                    if ($row['status']== $GLOBALS['STATUS_CLOSED']) {
                        $strike = "<strike>"; $strikend = "</strike>";
                        }
                    else { $strike = $strikend = "";}
                    switch ($row['severity']) {		//color tickets by severity
                         case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='severity_medium'; break;
                        case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='severity_high'; break;
                        default: 				$severityclass='severity_normal'; break;
                        }

                    print "<TR CLASS='{$evenodd[$counter%2]}' onClick = \"Javascript: self.location.href = 'main.php?id={$row['id']}';\">
                        <TD CLASS='$severityclass'>#{$row['id']}</TD>
                        <TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>" . format_date($row['problemstart'])."</SPAN></TD>
                        <TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['scope']), 120) . "{$strikend}</SPAN></TD>
                        <TD CLASS='$severityclass'><SPAN STYLE = 'margin-left:10px;'>{$strike}" . shorten(highlight($_POST['frm_query'], $row['addr']), 120) . "{$strikend}</SPAN></TD>
                        </TR>\n";				// 2/25/09
                    $counter++;
                    }
                print '</TABLE><BR /><BR />';
                }			// end if/else
            }			// end if/else (empty($id_stack ))
        }				// end if ($_POST['frm_query'])
    else {
        print "<SPAN STYLE = 'margin-left:86px'><FONT CLASS='header'>" . gettext('Search') . "</FONT></SPAN>";
        }
?>
<BR /><BR />
<FORM METHOD="post" NAME="queryForm" ACTION="search.php" onSubmit="return validate(document.queryForm);">
<TABLE CELLPADDING="2" BORDER="0" STYLE = 'margin-left:80px;'>
<TR CLASS = "even"><TD VALIGN="top" CLASS="td_label"><?php print gettext('Query');?>: &nbsp;</TD><TD><INPUT TYPE="text" SIZE="40" MAXLENGTH="255" VALUE="<?php print $post_frm_query;?>" NAME="frm_query"></TD></TR>
<TR CLASS = "odd"><TD VALIGN="top" CLASS="td_label"><?php print gettext('Search in');?>: &nbsp;</TD><TD>
<SELECT NAME="frm_search_in">
<OPTION VALUE="" checked><?php print gettext('All');?></OPTION>
<OPTION VALUE="contact"><?php print gettext('Reported by');?></OPTION>
<OPTION VALUE="street"><?php print gettext('Address');?></OPTION>
<OPTION VALUE="city"><?php print gettext('City');?></OPTION>
<OPTION VALUE="state"><?php print gettext('State');?></OPTION>
<OPTION VALUE="description"><?php print gettext('Description');?></OPTION>
<OPTION VALUE="comments"><?php print gettext('Comments');?></OPTION>
<OPTION VALUE="owner"><?php print gettext('Owner');?></OPTION>
<OPTION VALUE="date"><?php print gettext('Issue Date');?></OPTION>
<OPTION VALUE="problemstart"><?php print gettext('Problem Starts');?></OPTION>
<OPTION VALUE="problemend"><?php print gettext('Problem Ends');?></OPTION>
</SELECT></TD></TR>
<TR CLASS = "even"><TD VALIGN="top" CLASS="td_label"><?php print gettext('Order By');?>: &nbsp;</TD><TD>
<SELECT NAME="frm_ordertype">
<OPTION VALUE="date"><?php print gettext('Issue Date');?></OPTION>
<OPTION VALUE="problemstart"><?php print gettext('Problem Starts');?></OPTION>
<OPTION VALUE="problemend"><?php print gettext('Problem Ends');?></OPTION>
<OPTION VALUE="affected"><?php print gettext('Affected');?></OPTION>
<OPTION VALUE="scope"><?php print gettext('Incident');?></OPTION>
<OPTION VALUE="owner"><?php print gettext('Owner');?></OPTION>
</SELECT>&nbsp;<?php print gettext('Descending');?>: <INPUT TYPE="checkbox" NAME="frm_order_desc" VALUE="DESC" CHECKED></TD></TR>
<TR CLASS = "odd"><TD VALIGN="top" CLASS="td_label"><?php print gettext('Status');?>: &nbsp;</TD><TD>
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="%" CHECKED> <?php print gettext('All');?><BR />
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_OPEN;?>"> <?php print gettext('Open');?><BR />
<INPUT TYPE="radio" NAME="frm_querytype" VALUE="<?php print $STATUS_CLOSED;?>"> <?php print gettext('Closed');?><BR />
</TD></TR>
<TR CLASS = "even"><TD></TD><TD ALIGN = "left"><INPUT TYPE="button" VALUE="<?php print gettext('Cancel');?>"  onClick="history.back();" /><INPUT TYPE="reset" VALUE="<?php print gettext('Reset');?>" STYLE ="margin-left:20px" /><INPUT TYPE="submit" VALUE="<?php print gettext('Next');?>"  STYLE ="margin-left:20px" /></TD></TR>
</TABLE></FORM>
</BODY></HTML>
