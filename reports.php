<?php

include'./incs/error_reporting.php';
$asof = "3/24/10";

@session_start();
require_once './incs/functions.inc.php';		//7/28/10
do_login(basename(__FILE__));
require_once './incs/log_codes.inc.php'; 				// 3/25/10
$img_width  = round(.8*$_SESSION['scr_width']/3);		//8/9/08

if ((($istest)) && (!empty($_GET))) {dump ($_GET);}
if ((($istest)) && (!empty($_POST))) {dump ($_POST);}

extract($_GET);
extract($_POST);

if(($_SESSION['level'] == $GLOBALS['LEVEL_UNIT']) && (intval(get_variable('restrict_units')) == 1)) {
	print "Not Authorized";
	exit();
	}

$locale = get_variable('locale');

$nature = get_text("Nature");
$disposition = get_text("Disposition");
$patient = get_text("Patient");
$incident = get_text("Incident");
$incidents = get_text("Incidents");

$full_w = (@$_POST['frm_full_w']==1)? 100: 1;

$width_factors = array( (float) .01, (float) .013, (float) .016, (float) .032);			// 4/14/11
$w_tiny = (int) floor($_SESSION['scr_width'] * $width_factors[0] * $full_w);
$w_small = (int) floor($_SESSION['scr_width'] * $width_factors[1] * $full_w);
$w_medium = (int) floor($_SESSION['scr_width'] * $width_factors[2] * $full_w);
$w_large = (int) floor($_SESSION['scr_width'] * $width_factors[3] * $full_w);


$evenodd = array ("even", "odd");	// CLASS names for alternating tbl row colors
// ================ report-specific variables ===============================================
// IM
    $tick_array = array();
    $deltas = array();
    $counts = array();
    $severities = array ();
    $units_str = $today = $today_ref = "";

// ================ end report-specific variables ===============================================
if (empty($_POST)) {				// default to today

    switch ($locale) {
        case "0":
        $frm_date = date('m,d,Y');
        $full_date_fmt = date('n/j/y G:i');
        break;

        case "1":
        case "2":				// 11/29/10

        $frm_date = date('m,d,Y');
        $full_date_fmt = date('j/n/y G:i');
        break;

//		case "2":								// 8/10/09
//		$frm_date = date('m,d,Y');
//		$full_date_fmt = date('j/n/y G:i');
//		break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";

        }				// end switch
    $frm_func = "dr";				// single day report
    $group = "u";
    }
else {
    switch ($locale) { 	//	3/23/11
        case "0":
        $frm_date = array_key_exists('frm_date', ($_POST))? $_POST['frm_date']: date('m,d,Y');
        $full_date_fmt = date('n/j/y G:i');
        break;

        case "1":
        case "2":				// 11/29/10

        $frm_date = date('m,d,Y');
        $full_date_fmt = date('j/n/y G:i');
        break;

//		case "2":								// 8/10/09
//		$frm_date = date('m,d,Y');
//		$full_date_fmt = date('j/n/y G:i');
//		break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";

        }				// end switch($locale)

    $frm_func = (array_key_exists( 'frm_func', $_POST))? $_POST['frm_func']: "dr";		//	4/21/11
    $group = $_POST['frm_group'];
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <HEAD><TITLE><?php print gettext('Tickets - Reports Module');?></TITLE>
    <META HTTP-EQUIV="Content-Type" 		CONTENT="text/html; charset=UTF-8">
    <META HTTP-EQUIV="Expires" 				CONTENT="0">
    <META HTTP-EQUIV="Cache-Control" 		CONTENT="NO-CACHE">
    <META HTTP-EQUIV="Pragma" 				CONTENT="NO-CACHE">
    <META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
    <META HTTP-EQUIV="Script-date" CONTENT="<?php print date($full_date_fmt, filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
    <LINK REL="StyleSheet" HREF="stylesheet.php" TYPE="text/css"/>	<!-- 3/15/11 -->
<style type="text/css">
.hovermenu ul{font:bold 13px arial;padding-left:0;margin-left:0;height:20px;}
.hovermenu ul li{ list-style:none; display:inline;}
.hovermenu ul li { padding:2px 0.5em; float:left; color:black; background-color:#DEE3E7; border:2px solid #EFEFEF; width:81px;text-align: center}
.hovermenu ul li:hover{ background-color:#DEE3E7; border-style:outset;text-decoration: underline }
.hovermenu2 ul{font:bold 13px arial;padding-left:0;margin-left:0;height:20px;}
.hovermenu2 ul li{ list-style:none; display:inline;}
.hovermenu2 ul li { padding:2px 0.5em; float:left; color:black; background-color:#DEE3E7; border:2px solid #EFEFEF; width:179px;text-align: center}
.hovermenu2 ul li:hover{ background-color:#DEE3E7; border-style:outset;text-decoration: underline }
th {font-family: Verdana, Arial, Helvetica, sans-serif;color:#000000;font-weight: bold; font-size: 11px;}
.typical	{font-family: Verdana, Arial, Helvetica, sans-serif;color:#000000;font-weight: normal; font-size: 11px;}
.high		{font-family: Verdana, Arial, Helvetica, sans-serif;color:#347C17;font-weight: bold; font-size: 11px;}
.highest	{font-family: Verdana, Arial, Helvetica, sans-serif;color:#FF0000;font-weight: bold; font-size: 11px;}
p.page { page-break-after: always; }
</style>

<SCRIPT>
<?php
    print "//  {$asof}  \n";
?>
    try {
        parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
        parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
        parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
        }
    catch(e) {
        }

    var which='<?php print $group;?>';					// global - which report default
/**
 *
 * @returns {Array}
 */
    function $() {										// 2/24/09
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


    String.prototype.trim = function () {					// 3/24/10

        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };

    function get_new_colors() {								// 4/5/11
        window.location.href = '<?php print basename(__FILE__);?>';
        }

    function do_full_w(the_form) {							// 4/24/11
        var the_val = (the_form.full.checked)? 1:0;
        document.sel_form.frm_full_w.value=document.udr_form.frm_full_w.value=document.ugr_form.frm_full_w.value=document.log_form.frm_full_w.value=the_val;
        }

    function viewT(id) {			// view ticket

        return;

        }

    function viewU(id) {			// view unit

        return;

        }

    function toUDRnav(date_in) {					// daily report
        document.udr_form.frm_date.value=date_in;	// set date params
        document.udr_form.frm_group.value=which;
        document.udr_form.frm_resp_sel.value=document.sel_form.frm_unit_id.options[document.sel_form.frm_unit_id.selectedIndex].value;
        document.udr_form.frm_tick_sel.value=document.sel_form.frm_ticket_id.options[document.sel_form.frm_ticket_id.selectedIndex].value;
        document.udr_form.submit();
        }

    function do_ugr(instr) {						// select for generic
        document.ugr_form.frm_func.value=instr;
        document.ugr_form.frm_group.value=which;
        document.ugr_form.frm_resp_sel.value=document.sel_form.frm_unit_id.options[document.sel_form.frm_unit_id.selectedIndex].value;	// 2/8/09
        document.ugr_form.frm_tick_sel.value=document.sel_form.frm_ticket_id.options[document.sel_form.frm_ticket_id.selectedIndex].value;
        document.ugr_form.submit();
        }

    function ck_frames() {		// ck_frames()
        if (self.location.href==parent.location.href) {
            self.location.href = 'index.php';
            }
        else {
            parent.upper.show_butts();										// 1/21/09
            }
        }

    function open_tick_window(id) {				// 4/14/11
        var url = "single.php?ticket_id="+ id;
        var tickWindow = window.open(url, 'mailWindow', 'resizable=1, scrollbars, height=600, width=720, left=100,top=100,screenX=100,screenY=100');
        tickWindow.focus();
        }

    </SCRIPT>

    </HEAD>
<BODY onLoad = "ck_frames();">
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT> <!-- 10/2/10 -->

<A NAME="top" />
<DIV ID='to_bottom' style="position:fixed; top:6px; left:10px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';">
<IMG SRC="markers/down.png" BORDER=0 /></DIV>

<?php

/**
 * date_range
 * Insert description here
 *
 * @param $dr_date_in
 * @param $dr_func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function date_range($dr_date_in, $dr_func_in) {			// returns array of MySQL-formatted dates
        $temp = explode(",", $dr_date_in);					// into m, d, y
        $range = array();				// mktime ($hour, $minute, $second, $month, $day, $year)$temp[0] $temp[1] $temp[2]
        switch ($dr_func_in) {
            case "dr":
                $range[0] = mysql_format_date(mktime(0,0,0,$temp[0],$temp[1],$temp[2]));		// m, d, y -- date ('D, M j',
                $range[1] = mysql_format_date(mktime(0,0,0,$temp[0],$temp[1]+1,$temp[2]));
                $range[2] = date ('D, M j',mktime(0,0,0,$temp[0],$temp[1],$temp[2]));
                $range[3] = date ('D, M j',mktime(0,0,0,$temp[0],$temp[1]+1,$temp[2]));

                return $range;
                break;

            case "cm" :		// current month
                $range[0] = mysql_format_date(mktime(0,0,0,$temp[0],1,$temp[2]));			// m, d, y
                $range[1] = mysql_format_date(mktime(23,59,59,$temp[0],$temp[1],$temp[2]));	// from day 1 of this month m
                $range[2] = date ('D, M j', mktime(0,0,0,$temp[0],1,$temp[2]));				// m, d, y
                $range[3] = date ('D, M j', mktime(23,59,59,$temp[0],$temp[1],$temp[2]));	// from day 1 of this month m

                return $range;
                break;

            case "cw" :		// current week
                for ($i=0;$i<7;$i++) {												// find last Monday
                    $monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
                    if (date('w', $monday) == 1) {
                        break;
                        }
                    }
                $range[0] = mysql_format_date(mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));	// midnight sun/mon
                $range[1] = mysql_format_date(mktime(23,59,59,date('m'),date('d'),date('Y')));								// today
                $range[2] = date ('D, M j', mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));		// midnight sun/mon
                $range[3] = date ('D, M j', mktime(23,59,59,date('m'),date('d'),date('Y')));								// today

                return $range;
                break;

            case "lw" :		// last week
                for ($i=0;$i<7;$i++) {												// find last Monday
                    $monday = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
                    if (date('w', $monday) == 1) {
                        break;
                        }
                    }
                $prior_monday = $monday - (7*24*60*60);	// back seven days
                $range[0] = mysql_format_date(mktime(0,0,0,date('m', $prior_monday), date('d', $prior_monday), date('Y', $prior_monday)));	// midnight sun/mon
                $range[1] = mysql_format_date(mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday)));					// midnight sun/mon
                $range[2] = date ('D, M j', mktime(0,0,0,date('m', $prior_monday), date('d', $prior_monday), date('Y', $prior_monday)));	// midnight sun/mon
                $range[3] = date ('D, M j', mktime(0,0,0,date('m', $monday), date('d', $monday), date('Y', $monday))-1);						// midnight sun/mon

                return $range;
                break;

            case "lm" :		// last month
                $prior1st = mktime(0, 0, 0, date("m")-1, 1, date("Y"));
                $this1st = mktime(0, 0, 0, date("m"), 1, date("Y"));

                $range[0] = mysql_format_date(mktime(0,0,0,date('m', $prior1st), date('d', $prior1st), date('Y', $prior1st)));	// midnight on prior 1st
                $range[1] = mysql_format_date(mktime(0,0,0,date('m', $this1st), date('d', $this1st), date('Y', $this1st)));		// midnight on this month's 1st
                $range[2] = date ('D, M j', mktime(0,0,0,date('m', $prior1st), date('d', $prior1st), date('Y', $prior1st)));	// midnight on prior 1st
                $range[3] = date ('D, M j', mktime(0,0,0,date('m', $this1st), date('d', $this1st), date('Y', $this1st))-1);		// midnight on this month's 1st

                return $range;
                break;

            case "cy" :		// current year
                $range[0] = mysql_format_date(mktime(0,0,0,1,1,date("Y")));							// from Jan 1 of this year
                $range[1] = mysql_format_date(mktime(23,59,59, date('m'),date('d'),date("Y")));		// to today
                $range[2] = date ('D, M j', mktime(0,0,0,1,1,date("Y")));
                $range[3] = date ('D, M j', mktime(23,59,59,date('m'),date('d'),date("Y")));

                return $range;
                break;

            case "ly" :		// last year
                $range[0] = mysql_format_date(mktime(0,0,0,1,1,date("Y")-1));				// from Jan 1 of last year
                $range[1] = mysql_format_date(mktime(23,59,59,12,31,date("Y")-1));			// to Dec 31 of that year
                $range[2] = date ('D, M j', mktime(0,0,0,1,1,date("Y")-1));					//
                $range[3] = date ('D, M j', mktime(23,59,59,12,31,date("Y")-1));			//

                return $range;
                break;

            default:
                echo " error - error - error " . $dr_func_in;
            }		// end switch ()
        }				// end function date range()

    function date_part($in_date) {						// return date part of date/time string
        $temp = explode (" ", $in_date);

        return $temp[0];
        }		// end function date_part()

    function time_part($in_date) {						// "2007-12-02 21:07:30"
        $temp = explode (" ", $in_date);

        return substr($temp[1], 0, 5);
        }		// end function time_part()

// =================================================== DISPATCH LOG =========================================	1/31/09

    function do_dispreport($date_in, $func_in) {				// $frm_date, $mode as params - 6/8/11
        global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
        global $evenodd, $types;
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

/**
 * the_time
 * Insert description here
 *
 * @param $in_val
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function the_time($in_val) {
            return date("j H:i", (int) $in_val);
            }

/**
 * do_cells
 * Insert description here
 *
 * @param $in_1
 * @param $in_2
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function do_cells($in_1, $in_2) {
            global $row, $out_row_1, $out_row_2;
            if (is_date($row['in_1'])) {
                $out_val1 = format_date_2($row['in_2']);
                $out_val2 = my_date_diff($row['problemstart_i'], $row['in_2']);
                }
            else {$out_val1 = $out_val2 = "";}
            $out_row_1 .= "<TD>{$out_val1}</TD>";
            $out_row_2 .= "<TD>{$out_val2}</TD>";
            }

/**
 * do_cell
 * Insert description here
 *
 * @param $in_1
 * @param $in_2
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
        function do_cell($in_1, $in_2) {
            return (is_date($in_2))? format_date_2($in_1) : "";
            }

        $from_to = date_range($date_in,$func_in);	// get date range as array

        $titles = array ();
        $titles['dr'] = "Dispatch Log Daily Report - ";
        $titles['cm'] = "Dispatch Log Current Month-to-date - ";
        $titles['lm'] = "Dispatch Log Last Month - ";
        $titles['cw'] = "Dispatch Log Current Week-to-date - ";
        $titles['lw'] = "Dispatch Log Last Week - ";
        $titles['cy'] = "Dispatch Log Current Year-to-date - ";
        $titles['ly'] = "Dispatch Log Last Year - ";
        $to_str = ($func_in=="dr")? "": " to " . $from_to[3];

        $to_str = ($func_in=="dr")? "": " to " . $from_to[3];
        print "\n<TABLE ALIGN='left' BORDER = 0 >\n<TR CLASS='even' style='height: 24px'>\n";
        print "<TH COLSPAN=99 ALIGN = 'center' border=1>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";

        $where = " WHERE  `a`.`dispatched` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
                    OR  `a`.`responding` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
                    OR  `a`.`on_scene` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
                    OR  `a`.`u2fenr` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
                    OR  `a`.`u2farr` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'
                    OR  `a`.`clear` BETWEEN '{$from_to[0]}' AND '{$from_to[1] }'";

        $which_inc = ($_POST['frm_tick_sel']==0)? 	"" : " AND `ticket_id` = " . 	quote_smart($_POST['frm_tick_sel']);				// 2/7/09
        $which_unit = ($_POST['frm_resp_sel']==0)? 	"" : " AND `responder_id` = " . quote_smart($_POST['frm_resp_sel']);

        $query = "SELECT *,
            `dispatched` AS dispatched_i,
            `responding` AS responding_i,
            `on_scene` AS on_scene_i,
            `u2fenr` AS u2fenr_i,
            `u2farr` AS u2farr_i,
            `clear` AS clear_i, `r`.`handle`,
            `t`.`problemstart` AS `problemstart_i`,
            `r`.`handle`
            FROM `$GLOBALS[mysql_prefix]assigns` `a`
            LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 	ON (`t`.`id` = `a`.`ticket_id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`r`.`id` = `a`.`responder_id`)
            {$where} {$which_inc} {$which_unit}
            ORDER BY `t`.`severity` DESC, `a`.`id` ASC" ;

//		dump($query);

        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        if (mysql_affected_rows()>0) {										// main loop - top
            $temp = explode("/", get_variable('disp_stat'));				// 1/7/2013
            if (count($temp)< 6) {$temp = 	explode("/", "D/R/O/FE/FA/Clear");}
            $header= "<TR CLASS = '{$evenodd[1]} {highest}'>
                <TH ALIGN='center'>" . get_text("Unit") . "&nbsp;</TH>
                <TH ALIGN='center'>" . get_text("Incident") . "&nbsp;</TH>
                <TH ALIGN='center'>" . gettext('Start') . "</TH>
                <TH ALIGN='center'>{$temp[0]}&nbsp;</TH>
                <TH ALIGN='center'>{$temp[1]}&nbsp;</TH>
                <TH ALIGN='center'>{$temp[2]}&nbsp;</TH>
                <TH ALIGN='center'>{$temp[3]}&nbsp;</TH>
                <TH ALIGN='center'>{$temp[4]}&nbsp;</TH>
                <TH ALIGN='center'>{$temp[5]}&nbsp;</TH>
                </TR>\n";
            echo $header;
            $i = 0;
            while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// main loop - top
                switch ($row['severity']) {		//style row by severity
                     case $GLOBALS['SEVERITY_MEDIUM']: 	$severityclass='high'; break;
                    case $GLOBALS['SEVERITY_HIGH']: 	$severityclass='highest'; break;
                    default: 							$severityclass='typical'; break;
                    }

                $row_tr = "<TR CLASS = '{$evenodd[$i%2]} {$severityclass}'>";
                $row_tr .= "<TD>&nbsp;{$row['handle']}</TD>\n";
                $row_tr .= "<TD>&nbsp;{$row['scope']}</TD>\n";		//
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['problemstart_i'],	$row['problemstart']) .	"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['dispatched_i'],		$row['dispatched']) .	"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['responding_i'],		$row['responding']) .	"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['on_scene_i'],		$row['on_scene']) .		"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['u2fenr_i'],			$row['u2fenr']) .		"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['u2farr_i'],			$row['u2farr']) .		"</TD>\n";
                $row_tr .= "<TD>&nbsp;" . do_cell ($row['clear_i'],			$row['clear']) .		"</TD>\n";
                $row_tr .= "</TR>\n";
                echo $row_tr;
                $i++;
                }
            }		// end if (mysql_affected_rows()>0)
        else {
            echo "<TR><TH><BR/>" . gettext('No data this period.') . "<BR/></TH></TR>";
            }
        echo "</TABLE><BR/><BR/>";
        }		// end function do_dispreport()

// =================================================== UNIT LOG =========================================

/**
 * do_unitreport
 * Insert description here
 *
 * @param $date_in
 * @param $func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_unitreport($date_in, $func_in) {				// $frm_date, $mode as params
        global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
        global $evenodd, $types;
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

        $from_to = date_range($date_in,$func_in);	// get date range as array

        $incidents = $severity = $unit_names = $status_vals = $users = $unit_status_ids = array();

        $query = "SELECT `id`, `scope`, `severity` FROM `$GLOBALS[mysql_prefix]ticket`";
        $temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $incidents[0]="";

        while ($temp_row = mysql_fetch_assoc($temp_result)) {
            $incidents[$temp_row['id']]=$temp_row['scope'];
            $severity[$temp_row['id']]=$temp_row['severity'];
            }

        $query = "SELECT `id`, `name`, `un_status_id` FROM `$GLOBALS[mysql_prefix]responder`";
        $temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $unit_names[0]="TBD";
        while ($temp_row = mysql_fetch_assoc($temp_result)) {
            $unit_names[$temp_row['id']]=$temp_row['name'];
            $unit_status_ids[$temp_row['id']]=$temp_row['un_status_id'];
            }

        $query = "SELECT `id`, `status_val` FROM `$GLOBALS[mysql_prefix]un_status`";
        $temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $status_vals[0]="??";										// 2/2/09
        while ($temp_row = mysql_fetch_assoc($temp_result)) {
            $status_vals[$temp_row['id']]=$temp_row['status_val'];
            }

        $query = "SELECT `id`, `user` FROM `$GLOBALS[mysql_prefix]user`";
        $temp_result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $users[0]="TBD";
        while ($temp_row = mysql_fetch_assoc($temp_result)) {
            $users[$temp_row['id']]=$temp_row['user'];
            }
        $priorities = array("text_black","text_blue","text_red" );
        $titles = array ();
        $titles['dr'] = get_text("Units") . " - " . gettext('Daily Report') . " - ";
        $titles['cm'] = get_text("Units") . " - " . gettext('Current Month-to-date') . " - ";
        $titles['lm'] = get_text("Units") . " - " . gettext('Last Month') . " - ";
        $titles['cw'] = get_text("Units") . " - " . gettext('Current Week-to-date') . " - ";
        $titles['lw'] = get_text("Units") . " - " . gettext('Last Week') . " - ";
        $titles['cy'] = get_text("Units") . " - " . gettext('Current Year-to-date') . " - ";
        $titles['ly'] = get_text("Units") . " - " . gettext('Last Year') . " - ";
        $to_str = ($func_in=="dr")? "": " to " . $from_to[3];
        print "\n<TABLE ALIGN='left' BORDER = 0 WIDTH='800px'>\n<TR CLASS='even' style='height: 24px'>\n";
        print "<TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";

        $i = 1;

//		collect status values in use
        $query = "SELECT DISTINCT `info` FROM `$GLOBALS[mysql_prefix]log` WHERE `code` = " . $GLOBALS['LOG_UNIT_STATUS'] . " ORDER BY `info` ASC";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $i++;

        $caption =  "<TR CLASS = 'odd'><TD COLSPAN=2>&nbsp;&nbsp;&nbsp;<B>" . get_text("Unit") . "</B></TD>";
        $curr_unit = "";
        $statuses = array();
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			// build header row
            if (!empty($row['info'])) {
                $statuses[$row['info']] = "";										// define the entry
                $query = "SELECT `status_val` FROM `$GLOBALS[mysql_prefix]un_status` WHERE `id` = " . $row['info'] . " LIMIT 1" ;// status type
                $result_val= mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
                $row_val = stripslashes_deep(mysql_fetch_assoc($result_val));
                $the_status = (empty($row_val))? "??": shorten($row_val['status_val'], 12); 		// 2/2/09

                $caption .= "\t<TD ALIGN='CENTER'>&nbsp;&nbsp;" . shorten($the_status, 12) . "&nbsp;&nbsp;</TD>\n";
                }
            }
        $caption .=  "<TD ALIGN='center'><U>{$incident}</U></TD>";
        $caption .=  "<TD ALIGN='left'><U>" . gettext('Comment') . "</U></TD></TR>\n";	//	9/10/13
        $blank = $statuses;

        $where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
//		$which_unit = ($_POST['frm_resp_sel']==0)? "" : " AND `responder_id` = " .$_POST['frm_resp_sel'];
        $which_unit = ((!isset($_POST['frm_resp_sel']) || ($_POST['frm_resp_sel']==0)))? "" : " AND `responder_id` = " .$_POST['frm_resp_sel'];
                                                                                                                                            // 3/23/09
        $query = "SELECT *,
            `when` AS `when_num`,
            `responder_id` AS `unit`,
            `info` AS `status`,
            `ticket_id` AS `incident`
            FROM `$GLOBALS[mysql_prefix]log`
            LEFT JOIN `$GLOBALS[mysql_prefix]responder` r ON (`$GLOBALS[mysql_prefix]log`.responder_id = r.id) ".
            $where . $which_unit. " AND ((`code` = " . $GLOBALS['LOG_UNIT_STATUS'] . ") OR (`code` = " . $GLOBALS['LOG_COMMENT'] . ")) ORDER BY `name` ASC, `incident` ASC, `status` ASC, `when` ASC" ;	//	9/10/13
//		dump($query);
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
        $i = 0;
        if (mysql_affected_rows()>0) {				// main loop - top
            print $caption;
            while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
                if (empty($curr_unit)) {
                    $curr_unit = $row['unit'];
                    $curr_inc = $row['incident'];
                    $curr_date_test = date ('z', strtotime($row['when_num']));			// day of year as test value
                    $do_date=$row['when_num'];
                    }								// populate break item
                if (($row['unit'] == $curr_unit) && ($row['incident'] == $curr_inc ) && (date ('z', strtotime($row['when_num'])) == $curr_date_test )) {	// same unit and incident, date?
                    $statuses[$row['status']] = time_part($row['when']);		// yes, populate the row
                    $theIncident_id = $row['incident'];
                    }
                else {														// no, flush, initialize and populate
                    if ($row['code'] != $GLOBALS['LOG_COMMENT']) {	//	9/10/13
                        print "<TR CLASS='" . $evenodd[$i%2] . "'>";
                        $theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
                        print (array_key_exists($curr_unit, $unit_names))? "<TD onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>":	"<TD>[#" . $curr_unit . "]</TD>";
                        if (!empty($do_date)) {
                            print "<TD>" . date ('D, M j', strtotime($do_date)) . "</TD>";
                            $do_date = "";
                            }
                        else {
                            print "<TD></TD>";
                            }
                        if (((date ('z', strtotime($row['when_num']))) != $curr_date_test)) {		// date change?
                            $do_date=$row['when_num'];
                            $curr_date_test = date ('z', strtotime($row['when_num']));
                            }
                        $theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;

                        foreach ($statuses as $key => $val) {
                            print "<TD ALIGN='center'> $val </TD>";
                            }
                        if ($row['incident']>0) {				// 6/6/08
                            $theIncidentName = (array_key_exists($row['incident'], $incidents))? $incidents[$row['incident']]: "#" . $row['incident'] ;
                            $theSeverity = (array_key_exists($row['incident'], $severity))? $severity[$row['incident']]: 0;
                            print (array_key_exists($row['incident'], $incidents))?	"<TD CLASS='" . $priorities[$theSeverity] . "' onClick = 'viewT(" . $row['incident'] . ")'><B>" . shorten($theIncidentName, 20) . "</B></TD>":	"<TD>#" . $row['incident']. " ??</TD>";
                            }
                        else {
                            print "<TD></TD>";
                            }
                        print "<TD></TD>";
                        $statuses = $blank;															// initalize
                        $statuses[$row['status']] = date('H:i', strtotime($row['when_num']));					// MySQL format
                        $curr_unit = $row['unit'];
                        $curr_inc = $row['incident'];
                        $i++;
                        $theIncident_id = $row['incident'];
                        } else {	//	9/10/13
                        print "<TR CLASS='" . $evenodd[$i%2] . "'>";
                        $theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16): "#" . $curr_unit ;
                        print (array_key_exists($curr_unit, $unit_names))? "<TD onClick = 'viewU(" .$curr_unit . ");'><B>" . $theUnitName . "</B></TD>":	"<TD>[#" . $curr_unit . "]</TD>";
                        if (!empty($do_date)) {
                            print "<TD>" . date ('D, M j', strtotime($do_date)) . "</TD>";
                            $do_date = "";
                            } else {
                            print "<TD></TD>";
                            }
                        foreach ($statuses as $key => $val) {
                            print "<TD ALIGN='center'>&nbsp;</TD>";
                            }
                        print "<TD></TD>";
                        print "<TD ALIGN='left'>" . $row['info'] . "</TD>";
                        $i++;
                        }
                    }
                print "</TR>\n";
                }		// end while($row...)		 main loop - bottom

            print "\n<TR CLASS='" . $evenodd[$i%2] . "'>";
            $theUnitName = (array_key_exists($curr_unit, $unit_names))? shorten($unit_names[$curr_unit], 16):  "#" . $curr_unit ;
            print "<TD onClick = 'viewU(" .$curr_unit . ")'><B>" . $theUnitName . "</B></TD>";		// flush tail-end Charlie

//			dump($do_date);
            $work_date = (!empty($do_date))? date ('D, M j', strtotime($do_date)) : "" ; // 1/7/2013
            print "<TD>{$work_date}</TD>";

            foreach ($statuses as $key => $val) {
                print "<TD ALIGN='center'> $val </TD>";
                }
            if ($theIncident_id>0) {
                $theIncidentName = (array_key_exists($theIncident_id, $incidents))? $incidents[$theIncident_id]: "#" . $theIncident_id ;
                $theSeverity = (array_key_exists($theIncident_id, $severity))? $severity[$theIncident_id]: 0;

//				print "<TD CLASS='" . $priorities[$severity[$theIncident_id]] . "' onClick = 'viewT(" . $theIncident_id . ");'><B>" . shorten($incidents[$theIncident_id],20) . "</B></TD>";
                print "<TD CLASS='" . $priorities[$theSeverity] . "' onClick = 'viewT(" . $theIncident_id . ");'>" . shorten($theIncidentName,20) . "</TD>";
                }
            else {
                print "<TD></TD>";
                }
            print "<TD></TD></TR>\n";
            }		// end if (mysql_affected_rows()>0)
        else {
            print "\n<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><br /><I>" . gettext('No') . " " . get_text("Unit") . " " . gettext('data for this period') . "</I><BR /></TD></TR>\n";
            }
        print "<TR><TD ALIGN='center' COLSPAN=99>";
        $m = date("m"); $d = date("d"); $y = date("Y");

        print "</TD></TR>";
        $i++;
        print "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
        print "</TABLE>\n";
        }		// end function do_unitreport()

// =============================================== STATION LOG  ===========================================

/**
 * do_sta_report
 * Insert description here
 *
 * @param $date_in
 * @param $func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_sta_report($date_in, $func_in) {				// $frm_date, $mode as params
        global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
        global $evenodd, $istest, $types;
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

        $from_to = date_range($date_in,$func_in);	// get date range as array
//		dump ($from_to);

        $types[$GLOBALS['LOG_ERRONEOUS']]			= gettext("Bad Log entry");	//	3/15/11
        $where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
                                                                                // 1/14/10, 5/29/12
        $query = "
            SELECT `when`, `l`.`id` AS `logid`,`l`.`info` AS `loginfo`,  t.scope AS `tickname`, `r`.`name` AS `unitname`, `s`.`status_val` AS `theinfo`, `u`.`user` AS `thename`,
             `l`.`code`,  `l`.`ticket_id`,  `u`.`user`, `l`.`from`, `r`.`handle`
            FROM `$GLOBALS[mysql_prefix]log` `l`
            LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`l`.`ticket_id` = `t`.`id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON (`l`.`responder_id` = `r`.`id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON (`l`.info = `s`.`id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`l`.`who` = `u`.`id`)
             $where ORDER BY `when` ASC
            ";
//		dump($query);
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

        $titles = array ();
        $titles['dr'] = gettext("Station Daily Report") . " - ";
        $titles['cm'] = gettext("Station Report") . " - " . gettext("Current Month-to-date") . " - ";
        $titles['lm'] = gettext("Station Report") . " - " . gettext("Last Month") . " - ";
        $titles['cy'] = gettext("Station Report") . " - " . gettext("Current Year-to-date") . " - ";
        $titles['ly'] = gettext("Station Report") . " - " . gettext("Last Year") . " - ";
        $titles['cw'] = gettext("Station Report") . " - " . gettext("Current Week-to-date") . " - ";
        $titles['lw'] = gettext("Station Report") . " - " . gettext("Last Week") . " - ";

        $i = 0;
        $curr_date="";
        print "\n<TABLE ALIGN='left' WIDTH='800px' BORDER = 0><TR CLASS='even'>\n";
        $to_str = ($func_in=="dr")? "": " to " . $from_to[3];
        print "<TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";

//		print "<TR CLASS='even'><TH COLSPAN=99 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . " to " . $from_to[3] . "</TH></TR>\n";
        if (mysql_affected_rows()>0) {
                print "<TR CLASS='odd'>";
                print "<TH ALIGN='left'>" . gettext('Date') . "</TH>";		// 4/4/10
                print "<TH ALIGN='left'>" . gettext('Time') . "</TH>";
                print "<TH ALIGN='left'>" . gettext('Code') . "</TH>";
                print "<TH ALIGN='left'>" . gettext('Call') . "</TH>";
                print "<TH ALIGN='left'>" . get_text("Unit") . "</TH>";
                print "<TH ALIGN='left'>" . gettext('Info') . "</TH>";
                print "<TH ALIGN='left'>" . gettext('User') . "</TH>";
                print "<TH ALIGN='left'>" . gettext('From') . "</TH>";
                if ($istest) {print "<TH ALIGN='left'>" . gettext('ID') . "</TH>";}
                print "</TR>\n";

			$of_interest = array($GLOBALS['LOG_ERROR'], $GLOBALS['LOG_INTRUSION'], $GLOBALS['LOG_ICS_MESSAGE_SEND']);
            while ($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)) {			// main loop - top
//				dump(mysql_format_date(mysql2timestamp($row['when'])));
//				dump(mysql_format_date($row['when']));
//              if (($row['code']<20) || ($row['code'] == $GLOBALS['LOG_ERROR']) || ($row['code'] == $GLOBALS['LOG_INTRUSION'])  ) {

				if (($row['code']<20) || in_array( $row['code'], $of_interest) ) {		// 4/7/2014
                    print "<TR CLASS='" . $evenodd[$i%2] . "'>";

                    if (!(date("z", mysql2timestamp($row['when'])) == $curr_date)) {								// date change?
                        print "<TD>" . date ('D, M j', mysql2timestamp($row['when'])) ."</TD>";
                        $curr_date = date("z", mysql2timestamp($row['when']));
                        }
                    else {print "<TD></TD>";}
//					$the_ticket = (empty($row['tickname']))? "[#" . $row['ticket_id']. "]" : $row['tickname'] ;

                    if (empty($row['tickname'])) {
                        $the_ticket = ($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";
                        }
                    else {
                        $the_ticket =$row['tickname'] ;
                        }
//			$action = (empty($_POST['action'])) ? ( isset( $defaultString ) ? $defaultString : 'default' ) : $_POST['action'];
//			$the_ticket = (empty($row['tickname']))? (($row['ticket_id']>0 )? "[#" . $row['ticket_id']. "]" :"";) : $row['tickname'] ;
//					dump(mysql_format_date($row['when']));
                    print "<TD>" . date('H:i', mysql2timestamp($row['when'])) . "</TD>";
                    print "<TD>" . $types[$row['code']] . "</TD>";
//					print "<TD>" . $row['tickname'] . "</TD>";
                    print "<TD>" . $the_ticket . "</TD>";
                    print "<TD>" . $row['handle'] . "</TD>";			// 5/29/12
//					print "<TD>" . $row['info'] . "</TD>";
                    print "<TD>" . $row['loginfo'] . "</TD>";			// 1/21/09
                    print "<TD>" . $row['user'] . "</TD>";
                    print "<TD>" . $row['from'] . "</TD>";
                    if ($istest) {print "<TD>" . $row['logid'] . "</TD>";}
                    print "</TR>\n";
                    $i++;
                    }
                }		// end while($row = ...)
            }		// end if (mysql_affected_rows() ...
        else {
            print "<TR CLASS='odd'><TD COLSPAN='99' ALIGN='center'><br /><I>" . gettext('No data for this period') . "</I><BR /></TD></TR>\n";
            }
        print "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
        print "</TABLE>\n";

        }		// end function do_sta_report()

// ================================================== INCIDENT SUMMARY =========================================

/**
 * do_inc_report
 * Insert description here
 *
 * @param $date_in
 * @param $func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_inc_report($date_in, $func_in) {				// Incidents summary report - $frm_date, $mode as params
        global $evenodd, $img_width, $types ;
        global $nature, $disposition, $patient, $incident, $incidents;	// 12/3/10
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

        $from_to = date_range($date_in,$func_in);	// get date range as array
//		dump ($from_to);
        $priorities = array("text_black","text_blue","text_red" );

        $types = array();
        $types[$GLOBALS['LOG_INCIDENT_OPEN']]		="{$incident} open";
        $types[$GLOBALS['LOG_INCIDENT_CLOSE']]		="{$incident} close";
        $types[$GLOBALS['LOG_INCIDENT_CHANGE']]		="{$incident} change";

        $where = " WHERE `when` >= '" . $from_to[0] . "' AND `when` < '" . $from_to[1] . "'";
        $which_inc = ($_POST['frm_tick_sel'] ==0)? "" : " AND `ticket_id` = " . $_POST['frm_tick_sel'];				// 2/7/09

        $query = "
            SELECT *,
            `when` AS `when`,
            t.id AS `tick_id`,t.scope AS `tick_name`,
            t.severity AS `tick_severity`,
            `u`.`user` AS `user_name`
            FROM `$GLOBALS[mysql_prefix]log`
            LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON (`$GLOBALS[mysql_prefix]log`.ticket_id = t.id)
            LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (`$GLOBALS[mysql_prefix]log`.who = u.id)
            ". $where . $which_inc . " AND `code` >= '" . $GLOBALS['LOG_INCIDENT_OPEN'] ."'  AND `code` <= '" . $GLOBALS['LOG_INCIDENT_CLOSE'] . "'
             ORDER BY `when` ASC
            ";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		dump ($query);

        $titles = array ();
        $titles['dr'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Daily Report') . " - ";
        $titles['cm'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Current Month-to-date') . " - ";
        $titles['lm'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Last Month') . " - ";
        $titles['cy'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Current Year-to-date') . " - ";
        $titles['ly'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Last Year') . " - ";
        $titles['cw'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Current Week-to-date') . " - ";
        $titles['lw'] = "<B>" . gettext('Incidents') . "</B> " . gettext('Report - Last Week') . " - ";

        $i = 0;
        print "\n<TABLE ALIGN='left' BORDER = 0 width=800>\n";
        $to_str = ($func_in=="dr")? "": " to " . $from_to[3];
        print "<TR CLASS='even'><TH COLSPAN=6 ALIGN = 'center'>" . $titles[$func_in] . $from_to[2] . $to_str . "</TH></TR>\n";
        $curr_date="";
        if (mysql_affected_rows()>0) {

            print "<TR CLASS='odd'>";
            print "<TH>" . gettext('Date') . "</TH>";
            print "<TH>" . gettext('Time') . "</TH>";
            print "<TH>" . gettext('Code') . "</TH>";
            print "<TH>{$incident}</TH>";
            print "<TH>" . gettext('User') . "</TH>";
            print "<TH>" . gettext('From') . "</TH>";
            print "</TR>\n";
            $inc_types = array();

            while ($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)) {
                if ($row['code']<20) {
                    if (array_key_exists($row['in_types_id'], $inc_types)) {
                        $inc_types[$row['in_types_id']]++;
                        }
                    else {
                        $inc_types[$row['in_types_id']] = 1;
                        }
                    print "<TR CLASS='" . $evenodd[$i%2] . "'>";
                    if (!(date("z", strtotime($row['when'])) == $curr_date)) {
                        print "<TD>" . date ('D, M j', strtotime($row['when'])) ."</TD>";
                        $curr_date = date("z", strtotime($row['when']));
                        }
                    else {print "<TD></TD>";}
                    print "<TD>" . date('H:i',strtotime($row['when'])) . "</TD>";
                    print "<TD>" . $types[$row['code']] . "</TD>";
                    if ($row['ticket_id']>0) {
                        $the_ticket = (empty($row['tick_name']))? "[#" . $row['ticket_id'] . "]" : shorten($row['tick_name'],20);	// 8/15/08 -1
                        $severity_class = empty($row['tick_severity'])? $priorities[0]: $priorities[$row['tick_severity']];			// accommodate null
                        print "<TD TITLE = '" .
                        $row['ticket_id'] . "' CLASS='" .
                        $severity_class . "' onClick = 'viewT(" .
                        $row['ticket_id'] . ");'>" .
                        $the_ticket . "</TD>";
                        }
                    print "<TD>" . $row['user_name'] . "</TD>";
                    print "<TD>" . $row['from'] . "</TD>";
                    print "</TR>\n";
                    $i++;
                    }
                }


        $query2 = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE id IN (" . $query . ")";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
            while ($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)) {			//
//				dump ($row['id']);
                }
//		graphics date range in db format and calculated img width - when` < '2013-06-01 23:59:59&p3=391'  AND `code` = '10'
$s_urlstr =  "sever_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";	//8/9/08
$t_urlstr =  "inc_types_graph.php?p1=" . 	urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";
$c_urlstr =  "city_graph.php?p1=" . 		urlencode($from_to[0]) . "&p2=" . urlencode($from_to[1]) . "&p3={$img_width}";

?>
</TABLE>
<BR CLEAR='left' />
<TABLE>
<TR><TD COLSPAN=3 ALIGN='center'><br><HR SIZE=1 COLOR='blue' WIDTH='50%'></TD></TR>
<TR VALIGN='bottom'><TD ALIGN='center'>
    <img src="<?php print $s_urlstr;?>" border=0 ID = "sev_img" />
    </TD>

    <TD ALIGN='center'>
    <img src="<?php print $t_urlstr;?>" border=0 ID = "typ_img" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </TD>

    <TD ALIGN='center'>
    <img src="<?php print $c_urlstr;?>" border=0 ID = "cit_img" />
    </TD>
    </TR>
<?php
            }
        else {
            print "\n<TR CLASS='odd'><TH COLSPAN='99' ALIGN='center'><br /><I>" . gettext('No data for this period!') . "</I><BR /><BR /></TH></TR>\n";
            }
        echo "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
        print "</TABLE>\n";

        }		// end function do_inc_report()

// ================================================== INCIDENT LOG REPORT =========================================

/**
 * do_inc_log_report
 * Insert description here
 *
 * @param $the_ticket_id
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_inc_log_report($the_ticket_id) {			// 3/18/10
        global $types;
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11
        global $nature, $disposition, $patient, $incident, $incidents;	// 4/21/11

        $tickets = $actions = $patients = $unit_names = $un_status = $unit_types = $users = $facilities = $fac_status = $fac_types = array();

        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]ticket`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
//		$str_lgth_max = 10;
//		$tick_str = ((strlen($tickets[$row['id']])) > $str_lgth_max) ? substr($row['street'], 0, $str_lgth_max). " ..." : $tickets[$row['id']] ;
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $tickets[$row['id']] = substr($row['scope'], 0, 10) . "/" . substr($row['street'], 0, 10);
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]action`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $actions[$row['id']] = substr($row['description'], 0, 20);
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]patient`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $patients[$row['id']] = substr($row['description'], 0, 20);
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]responder`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $unit_names[$row['id']] = $row['name'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]un_status`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $un_status[$row['id']] = $row['status_val'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]unit_types`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $unit_types[$row['id']] = $row['name'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]user`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $users[$row['id']] = $row['user'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]facilities`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $facilities[$row['id']] = $row['name'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]fac_status`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $fac_status[$row['id']] = $row['status_val'];
            }
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]fac_types`";
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $fac_types[$row['id']] = $row['name'];
            }
// ______________________________________________________________________________

        $query = "SELECT *,
            `problemstart` AS `problemstart`,
            `problemend` AS `problemend`,
            `booked_date` AS `booked_date`,
            `date` AS `date`,
            `$GLOBALS[mysql_prefix]ticket`.`updated` AS updated,
             `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
             `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
             `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
             `$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
             `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
             `rf`.`name` AS `rec_fac_name`,
             `rf`.`lat` AS `rf_lat`,
             `rf`.`lng` AS `rf_lng`,
             `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
             `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`
            LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
            LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`)
            WHERE `$GLOBALS[mysql_prefix]ticket`.`id`= '{$the_ticket_id}' LIMIT 1";			// 7/24/09 10/16/08 Incident location 10/06/09 Multi point routing

        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);

        $theRow = stripslashes_deep(mysql_fetch_array($result));
        $tickno = (get_variable('serial_no_ap')==0)?  "&nbsp;&nbsp;<I>(#" . $theRow['id'] . ")</I>" : "";			// 1/25/09

        switch ($theRow['severity']) {		//color tickets by severity
             case $GLOBALS['SEVERITY_MEDIUM']: $severityclass='severity_medium'; break;
            case $GLOBALS['SEVERITY_HIGH']: $severityclass='severity_high'; break;
            default: $severityclass='severity_normal'; break;
            }
        $print = "<TABLE BORDER='0' STYLE = 'width:800px'>\n";		//
        $print .= "<TR CLASS='even'><TD ALIGN='left' CLASS='td_data' COLSPAN=2 ALIGN='center'><B>{$incident}: <I>{$theRow['scope']}</B>{$tickno}</TD></TR>\n";
        $print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . gettext('Priority') . ":</TD> <TD ALIGN='left' CLASS='" . $severityclass . "'>" . get_severity($theRow['severity']);
        $print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$nature}:&nbsp;&nbsp;" . get_type($theRow['in_types_id']);
        $print .= "</TD></TR>\n";

        $print .= "<TR CLASS='even' ><TD ALIGN='left'>" . gettext('Protocol') . ":</TD> <TD ALIGN='left' CLASS='{$severityclass}'>{$theRow['protocol']}</TD></TR>\n";		// 7/16/09
        $print .= "<TR CLASS='odd' ><TD ALIGN='left'>" . gettext('Address') . ":</TD>		<TD ALIGN='left'>{$theRow['street']}";
        $print .= "&nbsp;&nbsp;{$theRow['city']}&nbsp;&nbsp;{$theRow['state']}</TD></TR>\n";
        $print .= "<TR CLASS='even'  VALIGN='top'><TD ALIGN='left'>" . gettext('Description') . ":</TD>	<TD ALIGN='left'>" .  nl2br($theRow['tick_descr']) . "</TD></TR>\n";	//	8/12/09
        $end_date = (intval($theRow['problemend'])> 1)? $theRow['problemend']:  (time() - (get_variable('delta_mins')*60));
        $elapsed = my_date_diff($theRow['problemstart'], $end_date);		// 5/13/10
        $print .= "<TR CLASS='odd'><TD ALIGN='left'>" . gettext('Status') . ":</TD>		<TD ALIGN='left'>" . get_status($theRow['status']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;({$elapsed})</TD></TR>\n";
        $print .= "<TR CLASS='even'><TD ALIGN='left'>" . gettext('Reported by') . ":</TD>	<TD ALIGN='left'>{$theRow['contact']}";
        $print .= "&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('Phone') . ":&nbsp;&nbsp;" . format_phone ($theRow['phone']) . "</TD></TR>\n";
        $by_str = ($theRow['call_taker'] ==0)?	"" : "&nbsp;&nbsp;by " . get_owner($theRow['call_taker']) . "&nbsp;&nbsp;";		// 1/7/10
        $print .= "<TR CLASS='odd'><TD ALIGN='left'>" . gettext('Written') . ":</TD>		<TD ALIGN='left'>" . format_date_2($theRow['date']) . $by_str;
        $print .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('Updated') . ":&nbsp;&nbsp;" . format_date_2($theRow['updated']) . "</TD></TR>\n";
        $print .= (empty($theRow['booked_date']))? "" : "<TR CLASS='odd'><TD ALIGN='left'>" . gettext('Scheduled date') . ":</TD>		<TD ALIGN='left'>" . format_date_2($theRow['booked_date']) . "</TD></TR>\n";	// 10/6/09
        $print .= (!(is_int($theRow['facility'])))? 		"" : "<TR CLASS='odd' ><TD ALIGN='left'>{$incident} " . gettext('at Facility') . ":</TD>		<TD ALIGN='left'>{$theRow['fac_name']}</TD></TR>\n";	// 8/1/09
        $print .= (!(is_int($theRow['rec_facility'])))? 	"" : "<TR CLASS='even' ><TD ALIGN='left'>" . gettext('Receiving Facility') . ":</TD>		<TD ALIGN='left'>{$theRow['rec_fac_name']}</TD></TR>\n";	// 10/6/09

        $print .= (empty($theRow['comments']))? "" : "<TR CLASS='odd'  VALIGN='top'><TD ALIGN='left'>{$disposition}:</TD>	<TD ALIGN='left'>" . nl2br($theRow['comments']) . "</TD></TR>\n";

        $print .= "<TR CLASS='even' ><TD ALIGN='left'>" . gettext('Run Start') . ":</TD><TD ALIGN='left'>" . format_date_2($theRow['problemstart']);
        $print .= 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('End') . ":&nbsp;&nbsp;" . format_date_2($theRow['problemend']) . "&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('Elapsed') . ":&nbsp;&nbsp;{$elapsed}
            </TD></TR>\n";

        $locale = get_variable('locale');	// 08/03/09
        switch ($locale) {
            case "0":
            $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('USNG') . "&nbsp;&nbsp;" . LLtoUSNG($theRow['lat'], $theRow['lng']);
            break;

            case "1":
            $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('OSGB') . "&nbsp;&nbsp;" . LLtoOSGB($theRow['lat'], $theRow['lng']);	// 8/23/08, 10/15/08, 8/3/09
            break;

            case "2":
            $coords =  $theRow['lat'] . "," . $theRow['lng'];									// 8/12/09
            $grid_type = "&nbsp;&nbsp;&nbsp;&nbsp;" . gettext('UTM') . "&nbsp;&nbsp;" . toUTM($coords);	// 8/23/08, 10/15/08, 8/3/09
            break;

            default:
            print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
            }
        $print .= "<TR CLASS='odd'><TD ALIGN='left'>" . gettext('Position') . ": </TD><TD ALIGN='left'>" .
                get_lat($theRow['lat']) . "&nbsp;&nbsp;&nbsp;" . get_lng($theRow['lng']) . $grid_type .
                "</TD></TR>\n";																				// 9/13/08
        $print .= "<TR><TD>&nbsp;</TD></TR></TABLE>\n";

        print $print;

        print show_actions ($the_ticket_id, "date" , FALSE, TRUE); // ($the_id, $theSort="date", $links, $display)
        $query = "
            SELECT *, `u`.`user` AS `thename` ,
                `l`.`info` AS `log_info` ,
                `l`.`id` AS `log_id` ,
                `l`.`responder_id` AS `the_unit_id`,
                `l`.`when`  AS `when`
            FROM `$GLOBALS[mysql_prefix]log` `l`
            LEFT JOIN `$GLOBALS[mysql_prefix]ticket` t ON ( `t`.`id` = `l`.`ticket_id` )
            LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON ( `l`.`who` = `u`.`id` )
            LEFT JOIN `$GLOBALS[mysql_prefix]assigns` a ON ( `a`.`ticket_id` = `t`.`id` )
            LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `r`.`id` = `a`.`responder_id` )
            WHERE `code` >= '{$GLOBALS['LOG_INCIDENT_OPEN']}'
            AND `l`.`ticket_id` ={$the_ticket_id}
            ORDER BY `log_id` ASC";

//		dump($query);
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        $evenodd = array ("even", "odd");
        $i = 0;
        echo "<TABLE ALIGN='left' CELLSPACING = 1 border=0  STYLE = 'width:800px'>";
        $do_hdr = TRUE;
        $day_part="";
        $last_id = "";
        while ($row = stripslashes_deep(mysql_fetch_assoc($result)) ) {
            if ($row['log_id'] <> $last_id) {
                $last_id = $row['log_id'] ;			// dupe preventer
                if ($do_hdr) {
                    echo "<TR CLASS='odd'><TD>&nbsp;</TD></TR>";
                    echo "<TR CLASS='even'><TH COLSPAN=99> {$incident} " . gettext('Log') . "</TH></TR>";
                    echo "<TR CLASS='odd'>
                        <TD></TD>
                        <TD ALIGN='left'><b>&nbsp;" . gettext('Time') . "</b></TD>
                        <TD ALIGN='left'><b>&nbsp;" . gettext('Log code') . "</b></TD>
                        <TD ALIGN='left'><b>&nbsp;" . get_text("Unit") . "/" . gettext('Facility') . "</b></TD>
                        <TD ALIGN='left'><b>&nbsp;" . gettext('Data') . "</b></TD>
                        <TD ALIGN='left'><b>&nbsp;" . gettext('By') . "</b></TD>
                        <TD ALIGN='left'><b>&nbsp;" . gettext('From') . "</b></TD>
                        </TR>";
                    $do_hdr = FALSE;
                    }
            $temp = explode (" ", format_date_2($row['when']));
            $show_day = ($temp[0] == $day_part)? "" : $temp[0] ;
            $day_part = $temp[0];
            echo "<TR CLASS = '{$evenodd[($i%2)]}'>
                <TD ALIGN='left'>{$show_day}</TD>
                <TD ALIGN='left'>&nbsp;{$temp[1]}&nbsp;</TD>
                <TD><b>&nbsp;{$types[$row['code']]}&nbsp;</b></TD>";

                switch ($row['code']) {

                    case $GLOBALS['LOG_INCIDENT_OPEN'] :
                    case $GLOBALS['LOG_INCIDENT_CLOSE'] :
                    case $GLOBALS['LOG_INCIDENT_CHANGE'] :
                    case $GLOBALS['LOG_INCIDENT_DELETE'] :
                        print "<TD></TD><TD></TD>";
                        break;

                    case $GLOBALS['LOG_ACTION_ADD'] :
                    case $GLOBALS['LOG_ACTION_DELETE'] :
                        $act_str = (array_key_exists($row['log_info'], $actions))? $actions[$row['log_info']] : "[{$row['log_info']}]";
                        print "<TD></TD><TD>&nbsp;{$act_str}&nbsp;</TD>";
                        break;

                    case $GLOBALS['LOG_PATIENT_ADD'] :
                    case $GLOBALS['LOG_PATIENT_DELETE'] :
                        $pat_str = (array_key_exists($row['log_info'], $patients))? $patients[$row['log_info']] : "[{$row['log_info']}]";
                        print "<TD></TD><TD>&nbsp;{$pat_str}&nbsp;</TD>";
                        break;

                    case $GLOBALS['LOG_UNIT_STATUS'] :
                    case $GLOBALS['LOG_UNIT_COMPLETE'] :
                    case $GLOBALS['LOG_UNIT_CHANGE'] :
                        $the_unit = array_key_exists($row['the_unit_id'], $unit_names) ? $unit_names[$row['the_unit_id']] : "?? {$row['the_unit_id']}" ;
                        $the_status = array_key_exists($row['log_info'], $un_status) ? $un_status[$row['log_info']] : "?? {$row['the_unit_id']}" ;
                        print "<TD>&nbsp;{$the_unit}&nbsp;</TD><TD>{$the_status}</TD>";
                        break;

                    case $GLOBALS['LOG_CALL_DISP'] :
                    case $GLOBALS['LOG_CALL_RESP'] :
                    case $GLOBALS['LOG_CALL_ONSCN'] :
                    case $GLOBALS['LOG_CALL_CLR'] :
                    case $GLOBALS['LOG_CALL_RESET'] :
//						dump($row);
                        $the_unit = array_key_exists($row['the_unit_id'], $unit_names) ? $unit_names[$row['the_unit_id']] : "?? {$row['the_unit_id']}" ;
                        print "<TD>&nbsp;{$the_unit}&nbsp;</TD><TD></TD>";
                        break;

                    case $GLOBALS['LOG_CALL_REC_FAC_SET'] :
                    case $GLOBALS['LOG_CALL_REC_FAC_CHANGE'] :
                    case $GLOBALS['LOG_CALL_REC_FAC_UNSET'] :
                    case $GLOBALS['LOG_CALL_REC_FAC_CLEAR'] :
                    case $GLOBALS['LOG_FACILITY_INCIDENT_OPEN'] :
                    case $GLOBALS['LOG_FACILITY_INCIDENT_CLOSE'] :
                    case $GLOBALS['LOG_FACILITY_INCIDENT_CHANGE'] :
                    case $GLOBALS['LOG_FACILITY_DISP'] :
                    case $GLOBALS['LOG_FACILITY_RESP'] :
                    case $GLOBALS['LOG_FACILITY_ONSCN'] :
                    case $GLOBALS['LOG_FACILITY_CLR'] :
                    case $GLOBALS['LOG_FACILITY_RESET'] :
                        $the_facy = array_key_exists($row['facility'], $facilities) ? $facilities[$row['facility']] : "?? {$row['facility']}" ;

                        print "<TD>$the_facy</TD><TD></TD>";
                        break;

                    default:
                        print "<TD>ERROR {$row['code']} : {$row['log_id']} </TD";
                    }		// end switch()
                echo "
                    <TD>&nbsp;{$row['thename']}&nbsp;</TD>
                    <TD>&nbsp;{$row['from']}&nbsp;</TD>";
                echo "</TR>\n";
                $i++;
                }
        }					// end while()
        echo "<TR><TD COLSPAN=99 ALIGN='center'><HR STYLE = 'color: blue; size: 1; width: 50%'></TD></TR>";
        echo "</TABLE>";
    } 					// end function do_inc_log_report()

// ================================================== AFTER-ACTION REPORT =========================================

/**
 * do_aa_report
 * Insert description here
 *
 * @param $date_in
 * @param $func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_aa_report($date_in, $func_in) {				// after action report $frm_date, $mode as params - 9/27/10
        global $types, $incident, $disposition;				// 12/7/10
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

        $the_width = 600;
        require_once './incs/functions_major.inc.php';		// 7/28/10

        $from_to = date_range($date_in,$func_in);			// get date range as array
        $where = " WHERE `problemstart` >= '{$from_to[0]}' AND `problemstart` < '{$from_to[1]}'";

        $query = "SELECT *,
            `problemstart` AS `problemstart`,
            `problemend` AS `problemend`,
            `booked_date` AS `booked_date`,
            `date` AS `date`,
            `$GLOBALS[mysql_prefix]ticket`.`updated` AS updated,
            `$GLOBALS[mysql_prefix]ticket`.`description` AS `tick_descr`,
            `$GLOBALS[mysql_prefix]ticket`.`lat` AS `lat`,
            `$GLOBALS[mysql_prefix]ticket`.`lng` AS `lng`,
            `$GLOBALS[mysql_prefix]ticket`.`_by` AS `call_taker`,
            `$GLOBALS[mysql_prefix]ticket`.`street` AS `tick_street`,
            `$GLOBALS[mysql_prefix]ticket`.`city` AS `tick_city`,
            `$GLOBALS[mysql_prefix]ticket`.`state` AS `tick_state`,
            `$GLOBALS[mysql_prefix]facilities`.`name` AS `fac_name`,
            `rf`.`name` AS `rec_fac_name`,
            `rf`.`lat` AS `rf_lat`,
            `rf`.`lng` AS `rf_lng`,
            `$GLOBALS[mysql_prefix]facilities`.`lat` AS `fac_lat`,
            `$GLOBALS[mysql_prefix]facilities`.`lng` AS `fac_lng` FROM `$GLOBALS[mysql_prefix]ticket`
            LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `ty` ON (`$GLOBALS[mysql_prefix]ticket`.`in_types_id` = `ty`.`id`)
            LEFT JOIN `$GLOBALS[mysql_prefix]facilities` ON (`$GLOBALS[mysql_prefix]facilities`.`id` = `$GLOBALS[mysql_prefix]ticket`.`facility`)
            LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `rf` ON (`rf`.`id` = `$GLOBALS[mysql_prefix]ticket`.`rec_facility`)
            {$where} ORDER BY `SEVERITY` ASC, `problemstart` ASC";

        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        if (mysql_affected_rows()==0) {
            print "<BR /><BR /><SPAN STYLE='margin-left:300px;'><B>" . gettext('No incident data for this period') . "</B></SPAN>";
            }
        else {
            $numrows = mysql_num_rows($result);
            $to_str = ($func_in=="dr")? "": " to {$from_to[3]} " . substr($from_to[1] ,0 , 4) ;
            print "<BR /><SPAN STYLE='margin-left:160px;'><B>" . mysql_affected_rows() . " " . gettext('Incidents') . ": " . $from_to[2] . $to_str .  "</B></SPAN><BR /><BR />";
            $page_num = 1;
//			print "<TABLE ALIGN='left' CELLSPACING = 2 CELLPADDING = 2  BORDER=0 width='800px'><TR><TD>";
            while ($row_ticket = stripslashes_deep(mysql_fetch_array($result))) {
                print do_ticket_wm($row_ticket, $the_width, FALSE, FALSE);	//	2/4/13
        //		print "<TR><TD ALIGN='center'><HR COLOR='blue'><BR /></TD></TR>";
                print "<BR />";
                print "<p class='page'>" . gettext('Page {$page_num} of {$numrows}') . "</p>";
                $page_num++;
                }			// end while ()
//			print "</TD></TR></TABLE>";
            }		// end if/else

        }			// end function

// ================================= INCIDENT MANAGEMENT REPORT ================================= 10/4/10
/**
 * my_stripslashes_deep
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
function my_stripslashes_deep($value) {
       if (is_array($value)) {$value = array_map('my_stripslashes_deep', $value);}
       else 					{$value = stripslashes($value); }

       return str_replace ( "'", "&#39;", $value  );
       }

    $logs = array();

/**
 * do_im_report
 * Insert description here
 *
 * @param $date_in
 * @param $func_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
    function do_im_report($date_in, $func_in) {				// incident mgmt report $frm_date, $mode as params - 9/27/10
        global $types, $tick_array,$deltas, $counts, $severities, $units_str, $evenodd, $logs, $types ;
        global $types, $incident, $disposition;				// 12/7/10
        global $w_tiny, $w_small, $w_medium, $w_large;		// 4/14/11

        $tick_array = array(0);
        $deltas = array(0, 0, 0, 0);		// normal, medium, high, total
        $counts = array(0, 0, 0, 0);		//
        $severities = array ("", "M", "H");	// severity symbols

        $from_to = date_range($date_in,$func_in);			// get date range as array
        $where = " WHERE `problemstart` >= '{$from_to[0]}' AND `problemstart` < '{$from_to[1]}'";
/**
 * do_print
 * Insert description here
 *
 * @param $row_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
            function do_print($row_in) {
                global $today, $today_ref, $line_ctr, $units_str, $severities, $evenodd;
                global $w_tiny, $w_small, $w_medium, $w_large;
//																			5/31/2013
                    if (empty($today)) {
                        $today_ref = date("z", strtotime($row_in['problemstart']));
                        $today = substr( format_date_2($row_in['problemstart']), 0, 5);
                        }
                    else {
                        if (!($today_ref == (date("z", strtotime($row_in['problemstart']))))) {				// date change?
                            $today_ref = date("z", strtotime($row_in['problemstart']));
                            $today = substr( format_date_2($row_in['problemstart']), 0, 5);
                            }
                        }

                $def_city = get_variable('def_city');
                $def_st = get_variable('def_st');

                print "<TR CLASS = '{$evenodd[$line_ctr%2]}'  onClick = 'open_tick_window(" . $row_in['tick_id'] . ");' >\n";
                print "<TD>{$today}</TD>\n";							//		Date -

                $problemstart = format_date_2($row_in['problemstart']);
                $problemstart_sh = short_ts($problemstart);
                print "<TD onMouseover=\"Tip('{$problemstart}');\" onmouseout='UnTip();'>{$problemstart_sh}</TD>\n";						//		start

                $problemend = format_date_2($row_in['problemend']);
                $problemend_sh = short_ts($problemend);
                print "<TD onMouseover=\"Tip('{$problemend}');\" onmouseout='UnTip();'>{$problemend_sh}</TD>\n";						//		end

                $elapsed =(((intval( $row_in['problemstart'])>0) && (intval ($row_in['problemend'])>0)))? my_date_diff($row_in['problemstart'], $row_in['problemend']) : "na";
                print "<TD>{$elapsed}</TD>\n";							//		Ending time

                print "<TD ALIGN='center'>{$severities[$row_in['severity']]}</TD>\n";

                $scope = $row_in['tick_scope'];
                $scope_sh = shorten($row_in['tick_scope'], $w_medium);
                print "<TD onMouseover=\"Tip('{$scope}');\" onmouseout='UnTip();'>{$scope_sh}</TD>\n";					//		Call type

                $comment = $row_in['comments'];
                $short_comment = shorten ( $row_in['comments'] , $w_large);
                print "<TD onMouseover=\"Tip('{$comment}');\" onMouseout='UnTip();'>{$short_comment}</TD>\n";			//		Comments/Disposition

                $facility = $row_in['facy_name'];
                $facility_sh = shorten($row_in['facy_name'], $w_small);
                print "<TD onMouseover=\"Tip('{$facility}');\" onmouseout='UnTip();'>{$facility_sh}</TD>\n";			//		Facility

                $city = ($row_in['tick_city']==$def_city)? 	"": ", {$row_in['tick_city']}" ;
                $st = ($row_in['tick_state']==$def_st)? 	"": ", {$row_in['tick_state']}";
                $addr = "{$row_in['tick_street']}{$city}{$st}";
                $addr_sh = shorten($row_in['tick_street'] . $city . $st, $w_medium);

                print "<TD onMouseover=\"Tip('{$addr}');\" onMouseout='UnTip();'>{$addr_sh}</TD>\n";					//		Street addr
                print "<TD>{$units_str}</TD>\n";						//		Units responding
                print "</TR>\n\n";
                $line_ctr++;
                }		// end function do print()

/**
 * do_stats
 * Insert description here
 *
 * @param $in_row
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
            function do_stats($in_row) {		//
                global $deltas, $counts;
                if ((intval( $in_row['problemstart'])>0) && (intval ($in_row['problemend'])>0)) {
                    $deltas[$in_row['severity']]+= ($in_row['problemend'] - $in_row['problemstart']);
                    $deltas[3] 					+= ($in_row['problemend'] - $in_row['problemstart']);
                    }
                $counts[$in_row['severity']]++;
                $counts[3]++;
                }		// end function do stats()
                                                                                    // 12/7/10
/**
 * do_print_log
 * Insert description here
 *
 * @param $ary_in
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
            function do_print_log($ary_in) {		//     ["code"]=> string(1) "3" ["info"]=>  string(14) "test test test"  ["when"]=>   string(10) "1302117158"
                global $today, $today_ref, $line_ctr,$evenodd, $types ;
                global $w_tiny, $w_small, $w_medium, $w_large;

                print "<TR CLASS = '{$evenodd[$line_ctr%2]}'>\n";
                print "<TD>{$today}</TD>\n";							//		Date -

                $when = format_date_2($ary_in['when']);
                $when_sh = short_ts($when);
                print "<TD onMouseover=\"Tip('{$when}');\" onmouseout='UnTip();'>{$when_sh}</TD>\n";						//		start
                print "<TD  COLSPAN=3></TD>\n";							//		end	Ending time
                print "<TD><I>" . gettext('Log entry') . ":</I></TD>\n";					//		Call type
                $info = $ary_in['info'];
                $sh_info = shorten ( $ary_in['info'] , $w_large);
                print "<TD onMouseover=\"Tip('{$info}');\" onMouseout='UnTip();'>{$sh_info}</TD>\n";			//		Comments/Disposition

                print "<TD>{$ary_in['user']}</TD>\n";			//		Facility
                print "<TD COLSPAN=2></TD>\n";					//		Street addr, Units responding
                print "</TR>\n\n";
                $line_ctr++;
                }		// end function do print_log()

                                                                            // populate global logs array
            $where_l = str_replace ("problemstart",  "when", $where);		// log version - 7/22/11
            $query = "SELECT `l`.`code`,
                `l`.`info` AS `info`,
                `l`.`when` AS `when`,
                `u`.`user`, `u`.`info` AS `user_info`
                FROM `$GLOBALS[mysql_prefix]log` `l`
                LEFT JOIN `$GLOBALS[mysql_prefix]user` u ON (`l`.who = u.id)
                {$where_l}
                AND (`code` = {$GLOBALS['LOG_COMMENT']})
                ORDER BY `when` ASC";
            $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
            while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
                array_push($logs, $row);
                }
            unset ($result);

/**
 * check_logs
 * Insert description here
 *
 * @param $in_time
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
            function check_logs($in_time) {						//  prints qualifying log entries
                global $logs;
                while ((!(empty($logs))) && ($logs[0]['when']<= $in_time )) {
                    do_print_log ($logs[0]);
                    array_shift ($logs);		// remove 1st entry
                    }
                }		// end function check_logs()

            $query = "SELECT *,
                `problemstart` AS `problemstart`,
                `problemend` AS `problemend`,
                `a`.`id` AS `assign_id` ,
                `a`.`comments` AS `assign_comments`,
                `u`.`user` AS `theuser`, `t`.`scope` AS `tick_scope`,
                `t`.`id` AS `tick_id`,
                `t`.`description` AS `tick_descr`,
                `t`.`status` AS `tick_status`,
                `t`.`street` AS `tick_street`,
                `t`.`city` AS `tick_city`,
                `t`.`state` AS `tick_state`,
                `r`.`id` AS `unit_id`,
                `r`.`name` AS `unit_name` ,
                `r`.`type` AS `unit_type` ,
                `f`.`name` AS `facy_name` ,
                `a`.`as_of` AS `assign_as_of`
                FROM `$GLOBALS[mysql_prefix]assigns` `a`
                LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`a`.`ticket_id` = `t`.`id`)
                LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`a`.`user_id` = `u`.`id`)
                LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
                LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`a`.`facility_id` = `f`.`id`)
                {$where}
                AND `t`.`status` <> '{$GLOBALS['STATUS_RESERVED']}'
                ORDER BY `problemstart` ASC";

            $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
//dump($query);
            print "<TABLE BORDER=0 ALIGN='center' cellspacing = 1 CELLPADDING = 4  ID='IM' STYLE='display:block'>";
            $to_str = ($func_in=="dr")? "": " to {$from_to[3]} " . substr($from_to[1] ,0 , 4) ;
            print "<TR CLASS='even'><TH COLSPAN=99 ALIGN = 'center'>" . "{$incident} " . gettext('Management Report') . " - " . $from_to[2] . $to_str . "</TH></TR>\n";

            print "<TR CLASS='odd'>
                    <TD><B>" . gettext('Date') . "</B></TD>
                    <TD><B>" . gettext('Opened') . "</B></TD>
                    <TD><B>" . gettext('Closed') . "</B></TD>
                    <TD><B>" . gettext('Elapsed') . "</B></TD>
                    <TD><B>" . gettext('Severity') . "</B></TD>
                    <TD><B>" . gettext('Call type') . "</B></TD>
                    <TD><B>" . gettext('Comments') . "/{$disposition}</B></TD>
                    <TD><B>" . gettext('Facility') . "</B></TD>
                    <TD><B>" . gettext('Address') . "</B></TD>
                    <TD><B>" .  get_text("Unit") . " " . gettext('responding') . "</B></TD>
                    </TR>";

            if (mysql_num_rows ($result) == 0) {												// empty?
                print "<TR CLASS = 'even'><TH COLSPAN=99>" . gettext('none') . "</TH></TR>\n";
                print "<TR CLASS = 'odd'><TD COLSPAN=99><BR /><BR /></TD></TR>\n";
                }
            else {
                $units_str = "";
                $i=0;
                $today = $today_ref = "";
                $buffer = "";
                $sep = ", ";

                while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {					// major while ()
                    array_push ($tick_array, $row['tick_id']);									// stack them up

                    if (empty($buffer)) {											// first time
                        $buffer = $row;
                        $units_str = $row['unit_name'];
                        }
                    else {		// not first time
                        if ($row['tick_id'] == $buffer['tick_id']) {
                            $units_str .= $sep . $row['unit_name'] ;		// no change, collect unit names
        //					$buffer = $row;
                            }
                        else {
                            check_logs($buffer['problemstart']) ;				// problemstart integer
                            do_print($buffer);		// print from buffer
                            do_stats($buffer);

                            $buffer = $row;
                            $units_str = $row['unit_name'];
                            }
                        }		// end if/else
                    }		// end while(

                check_logs(time()) ;				// everything remaining
                do_print($buffer);					// print from buffer
                do_stats($buffer);
                }		// end else{}

            $tick_array2 = array_unique ($tick_array );		// delete dupes
            $tick_array3 = array_values ($tick_array2 );	// compress result
            $sep = $tick_str = "";
            for ($i=0; $i< count($tick_array3); $i++ ) {
                $tick_str .= $sep . $tick_array3[$i];
                $sep = ",";
                }

            $query = "SELECT *,
                `problemstart` AS `problemstart`,
                `problemend` AS `problemend`,
                `u`.`user` AS `theuser`,
                NULL AS `unit_name`,
                `t`.`scope` AS `tick_scope`,
                `t`.`id` AS `tick_id`,
                `t`.`description` AS `tick_descr`,
                `t`.`status` AS `tick_status`,
                `t`.`street` AS `tick_street`,
                `t`.`city` AS `tick_city`,
                `t`.`state` AS `tick_state`,
                `f`.`name` AS `facy_name`
                FROM `$GLOBALS[mysql_prefix]ticket`			 `t`
                LEFT JOIN `$GLOBALS[mysql_prefix]user`		 `u` ON (`t`.`_by` = `u`.`id`)
                LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` ON (`t`.`facility` = `f`.`id`)
                {$where}
                AND `t`.`id` NOT IN ({$tick_str})
                AND `t`.`status` <> '{$GLOBALS['STATUS_RESERVED']}'
                ORDER BY `problemstart` ASC";
//		dump($query);
            $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
            print "<TR><TD COLSPAN=99 ALIGN='center'><B>" . gettext('Not dispatched') . "</B></TD></TR>";
            if (mysql_num_rows($result)==0) {
                print "<TR CLASS='even'><TD COLSPAN=99 ALIGN='center'><B>" . gettext('none') . "</B></TD></TR>";
                }
            else {
                $units_str = "";
                while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {		// incidents not dispatched
                    do_print($row);
                    do_stats($row) ;
                    }
                }

            if ($counts[3]>0) {						// any stats?
                print "<TR><TD COLSPAN=99 ALIGN='center'><B><BR />" . gettext('Mean incident close times by severity') . ":&nbsp;&nbsp;&nbsp;";
                for ($i = 0; $i<3; $i++) {					// each severity level
                    if ($counts[$i]>0) {
                        $mean = round($deltas[$i] / $counts[$i]);
                        print "<B>" . ucfirst(get_severity($i)) ."</B> ({$counts[$i]}): ". my_date_diff(0, $mean) . ",&nbsp;&nbsp;&nbsp;&nbsp;";
                        }
                    }

                    $mean = round($deltas[3] / $counts[3]);		// overall
                    print "<B>Overall</B>  ({$counts[3]}): ". my_date_diff(0, $mean);
                print "</B></TD></TR>";
                }
            print "</TABLE>";

            return;
            }		// end function do_im_report()

// ================================= end incident management report =================================

    $theDate = 	isset($frm_date)? $frm_date :  		date('m,d,Y');		// set defaults
    $theFunc= 	isset($frm_func)? $frm_func :  		"dr";				// daily
    $frm_group = isset($frm_group)? $frm_group: 	"u";				// unit reports

    switch ($frm_group) {
        case "m":								// 10/2/10 -->
            do_im_report ($theDate, $theFunc) ;
            break;
        case "a":								// 9/27/10 -->
            do_aa_report ($theDate, $theFunc) ;
            break;
        case "d":								// 1/27/09 -->
            do_dispreport ($theDate, $theFunc) ;
            break;
        case "u":
            do_unitreport ($theDate, $theFunc) ;
            break;
        case "s":
            do_sta_report ($theDate, $theFunc);
            break;
        case "i":
            do_inc_report ($theDate, $theFunc);		// incidents summary
            break;
        case "l":
            do_inc_log_report ($_POST['frm_tick_sel']);		// incident log report - 3/18/10
            break;
        default:
            echo "error error error " . __LINE__ . "<BR />";
            break;
        }

    $i=1;
    $checked = array("a" => "", "u" => "", "d" => "", "s" => "", "i" => ""); // 8/3/09 added d option to array to allow default to unit report correctly
    $temp = (empty($_POST))? "u":  $_POST['frm_group']; 		// set selector fm last, default is unit
    $checked [$temp] = " CHECKED ";								// copy fm last

?>
    <BR CLEAR='left' /><BR />
    <TABLE ALIGN='left' CELLSPACING = 2 CELLPADDING = 2  BORDER=0 width='800px'>
    <TR CLASS='even'><TH COLSPAN=99><?php print gettext('Other Reports');?></TH></TR>
    <TR CLASS='odd'><TD>&nbsp;</TD></TR>
    <TR><TD COLSPAN=99 ALIGN='center'>
    <FORM NAME='sel_form' METHOD='post' ACTION = ''><!-- dummy  -->
<?php
        $unit_types = array();											// 3/23/10, 4/11/09
        $query = "SELECT *FROM `$GLOBALS[mysql_prefix]unit_types`";		// build array of type names
        $result = mysql_query($query) or do_error($query, $query, mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $unit_types[$row['id']] = $row['name'];
            }

        print "Select " . get_text("Unit") . ": <SELECT NAME='frm_unit_id'>\n\t<OPTION VALUE=0 SELECTED>" . gettext('All') . "</OPTION>\n";
        $query = "SELECT * , COUNT( `responder_id` ) FROM `$GLOBALS[mysql_prefix]log`
            LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `$GLOBALS[mysql_prefix]log`.responder_id = r.id )
            GROUP BY `responder_id` HAVING COUNT( `responder_id` ) >=1
            ORDER BY `r`.`type`";

        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

        $do_optgroup = $set_type = TRUE;
        $curr_type = "";
        $optgroup_close = "";

        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $the_name = explode ("/", $row['name']);
            if (array_key_exists($row['type'], $unit_types)) {
                if (!($curr_type == $row['type'])) {
                    $curr_type = $row['type'];
                    $type_label = $unit_types[$row['type']];
                    $do_optgroup = TRUE;
                    }
                if ($do_optgroup) {
                    print "{$optgroup_close}\n<OPTGROUP LABEL='{$type_label}'>";
                    $optgroup_close = "</OPTGROUP>";
                    $do_optgroup = FALSE;
                    }
                if (!(empty($row['name']))) {print "<OPTION VALUE={$row['responder_id']}>{$the_name[0]}</OPTION>\n";}
                }
            else {
                if (!(empty($row['name']))) {print "<OPTION VALUE={$row['responder_id']}>{$the_name[0]}</OPTION>\n";}
                }
            }				// end while ()
        print "</OPTGROUP></SELECT>\n";

        $query = "SELECT *, COUNT(`scope`) FROM `$GLOBALS[mysql_prefix]ticket` GROUP BY `scope` HAVING COUNT(`scope`)>=1  AND status > 0";  // build assoc array of all tickets
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            $tickets[$row['id']] = $row['scope'];
            }

        print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select {$incident}:
            <SELECT NAME='frm_ticket_id'  onChange = \" $('inc_log_btn').style.display = ''; document.log_form.frm_tick_sel.value = this.value.trim(); \">\n\t" ;
        print "<OPTION VALUE=0 SELECTED>" . gettext('All') . "</OPTION>\n";
        $query = "SELECT *, COUNT(`ticket_id`) FROM `$GLOBALS[mysql_prefix]log` `l`
            LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`t`.`id` = `l`.`ticket_id`)
            GROUP BY `ticket_id` HAVING COUNT(`ticket_id`)>=1
            ORDER BY `t`.`status` DESC, `t`.`id` ASC";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

        $do_optgroup = $set_status = TRUE;
        $curr_status = "";
        $status_vals = array ('err', 'closed', 'open', 'scheduled');	//	4/1/11
        $optgroup_close = "";
        while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
            if (array_key_exists($row['ticket_id'], $tickets)) {
                if (!($curr_status == $row['status'])) {
                    $curr_status = $row['status'];
                    $stat_label = $status_vals[$row['status']];
                    $do_optgroup = TRUE;
                    }
                if ($do_optgroup) {
                    print "{$optgroup_close}\n<OPTGROUP LABEL='{$stat_label}'>";
                    $optgroup_close = "</OPTGROUP>";
                    $do_optgroup = FALSE;
                    }
                print "<OPTION VALUE='{$row['ticket_id']}'>{$tickets[$row['ticket_id']]}</OPTION>\n";
                }
            }
        print "\n</OPTGROUP></SELECT>\n";

//		print "&nbsp;&nbsp;<INPUT ID = 'inc_log_btn' TYPE = 'button' value = '{$incident} Log' onClick = 'document.log_form.submit();' STYLE = 'display: none'>";
?>
    <INPUT TYPE='hidden' NAME='frm_full_w' VALUE=0 />
    </B></TD></TR>

    <TR CLASS='odd'><TD COLSPAN=8 ALIGN='center'><B>
        <SPAN STYLE='margin-left:10px;'><?php print get_text("Unit"); ?> <?php print gettext('Log');?> <INPUT TYPE='radio' <?php print $checked['u']; ?> NAME= 'frm_which' onClick ="Javascript: which='u';"></SPAN>
        <SPAN STYLE='margin-left:10px;'><?php print gettext('Dispatch Log');?> <INPUT TYPE='radio' <?php print $checked['d']; ?> NAME= 'frm_which' onClick ="Javascript: which='d';"></SPAN> <!-- 1/29/09, 8/3/09 fixed default changed $checked to ['d'] -->
        <SPAN STYLE='margin-left:10px;'><?php print gettext('Station Log');?> <INPUT TYPE='radio' <?php print $checked['s']; ?> NAME= 'frm_which' onClick ="Javascript: which = 's';"></SPAN>
        <SPAN STYLE='margin-left:10px;'><?php print $incident;?> <?php print gettext('Summary');?> <INPUT TYPE='radio' <?php print $checked['i']; ?> NAME= 'frm_which' onClick ="Javascript: which = 'i';"></SPAN>
        <SPAN STYLE='margin-left:10px;'><?php print gettext('After-action Report');?> <INPUT TYPE='radio' <?php print $checked['a']; ?> NAME= 'frm_which' onClick ="Javascript: which = 'a';">
        <SPAN STYLE='margin-left:10px;'><?php print $incident;?><?php print gettext(' mgmt Report');?> <INPUT TYPE='radio' <?php print $checked['a']; ?> NAME= 'frm_which' onClick ="Javascript: which = 'm';">
        <SPAN ID = "inc_log_btn"  STYLE = 'margin-left: 20px; display: none'><?php print $incident;?> <?php print gettext('Log');?> <INPUT TYPE = 'radio' onClick = 'document.log_form.submit();'></SPAN>
        </B></TD>
        </TR>

    <TR CLASS='odd'><TD>&nbsp;</TD></TR>

    <TR CLASS='even'>
    <TD COLSPAN=99 ALIGN='center'>
    <SPAN STYLE='WIDTH:100px; FLOAT:left;'>&nbsp;</SPAN>
    <FONT SIZE="-1"><I><?php print gettext('Mouseover for buttons');?></I><SPAN STYLE='WIDTH:100px; FLOAT:right;'>
        <INPUT TYPE='checkbox' NAME='full' onclick = 'do_full_w(this.form);' /><?php print gettext('full width');?></SPAN></FONT><BR />

<?php

    print "\n<span class='hovermenu' style='background-color:#DEE3E7'><ul>\n";
    print "<nobr><li onClick= \"do_ugr('lw');\">" . gettext('Last Week') . "</li>";
    for ($j = -13; $j < 1; $j++) {
        $temp = mktime(0,0,0,date('m'), date('d')+$j, date('Y'));
        print "<LI onClick = \"toUDRnav('" . date ('m,d,Y', $temp) . "');\">";

$locale = get_variable('locale');	// 08/03/09
    switch ($locale) {
        case "0":
        print date ("m/d", $temp);
        print "</LI>\n";
        break;

        case "1":
        case "2":				// 11/29/10

        print date ("d/m", $temp);
        print "</LI>\n";
        break;

//		case "2":									// 8/10/09
//		print date ("d/m", $temp);
//		print "</LI>\n";
//		break;

        default:
            print gettext("ERROR in") . " " . basename(__FILE__) . " " . __LINE__ . "<BR />";
    }

        if ($j== -7) {
            print "<BR /><BR /><nobr><li onClick= \"do_ugr('cw');\">" . gettext('This Week') . "</li><nobr>";
            $i++;
            }
        }				// end for ($j...)
        print "</UL></nobr></SPAN>";
?>
    </TD></TR>
    </FORM>

    <FORM NAME='udr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'><!-- daily -->
    <TR CLASS='even' width='100%'><TD ALIGN='center' colspan=99>
    <span class="hovermenu2"><nobr>
    <ul>
    <li onClick= "do_ugr('lm');"><?php print date("M `y", mktime(0, 0, 0, date("m")-1, 15,   date("Y")));?></li>
    <li onClick= "do_ugr('cm');"><?php print date("M `y");?></li>
    <li onClick= "do_ugr('ly');"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")-1));?></li>
    <li onClick= "do_ugr('cy');"><?php print date("Y", mktime(0, 0, 0, 1, 1,  date("Y")));?></li>
    </ul>
    </nobr>
    </span>
    </TD></TR>

    </TABLE>
    <INPUT TYPE='hidden' NAME='frm_func' VALUE='dr'/>
    <INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'/>
    <INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'/>
    <INPUT TYPE='hidden' NAME='frm_resp_sel' VALUE=''/>									<!-- 2/8/09 -->
    <INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''/>
    <INPUT TYPE='hidden' NAME='frm_full_w' VALUE=0/>
    </FORM>
    <FORM NAME='ugr_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'>	<!-- generic, date-driven -->
    <INPUT TYPE='hidden' NAME='frm_func' VALUE='w'/>
    <INPUT TYPE='hidden' NAME='frm_date' VALUE='<?php print date('m,d,Y'); ?>'/>
    <INPUT TYPE='hidden' NAME='frm_group' VALUE='<?php print $group;?>'/>
    <INPUT TYPE='hidden' NAME='frm_resp_sel' VALUE=''/>
    <INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''/>
    <INPUT TYPE='hidden' NAME='frm_full_w' VALUE=0/>
    </FORM>

     <FORM NAME='log_form' METHOD='post' ACTION = '<?php print basename(__FILE__); ?>'>
     <INPUT TYPE='hidden' NAME='frm_group' VALUE='l'/><!-- incident log -->
     <INPUT TYPE='hidden' NAME='frm_tick_sel' VALUE=''/>
   <INPUT TYPE='hidden' NAME='frm_full_w' VALUE=0/>
     </FORM>

    <FORM NAME='T_nav_form' METHOD='get' TARGET = 'main' ACTION = "main.php">
    <INPUT TYPE='hidden' NAME='id' VALUE=''/>
    </FORM>

    <FORM NAME='U_nav_form' METHOD='get' TARGET = 'main' ACTION = "units.php">
    <INPUT TYPE='hidden' 	NAME='id' VALUE=''/>
    <INPUT TYPE='hidden' 	NAME='func' VALUE='responder'/>
    <INPUT TYPE='hidden' 	NAME='view' VALUE='true'/>
    </FORM>

    <FORM NAME='can_Form' METHOD="post" ACTION = "<?php print basename(__FILE__); ?>"></FORM>
<BR CLEAR = 'left' /><IMG SRC="markers/up.png" BORDER=0  onclick = "location.href = '#top';" STYLE = 'margin-left: 20px' />
<A NAME="bottom" />
</BODY></HTML>

<?php  /*
$GLOBALS['LOG_SIGN_IN']				= 1;
$GLOBALS['LOG_SIGN_OUT']			= 2;
$GLOBALS['LOG_COMMENT']				= 3;		// misc comment
$GLOBALS['LOG_INCIDENT_OPEN']		=10;
$GLOBALS['LOG_INCIDENT_CLOSE']		=11;
$GLOBALS['LOG_INCIDENT_CHANGE']		=12;
$GLOBALS['LOG_ACTION_ADD']			=13;
$GLOBALS['LOG_PATIENT_ADD']			=14;
$GLOBALS['LOG_INCIDENT_DELETE']		=15;		// added 6/4/08
$GLOBALS['LOG_UNIT_STATUS']			=20;
$GLOBALS['LOG_UNIT_COMPLETE']		=21;		// 	run complete
$GLOBALS['LOG_UNIT_CHANGE']			=22;

$GLOBALS['LOG_CALL_DISP']			=30;		// 1/20/09
$GLOBALS['LOG_CALL_RESP']			=31;
$GLOBALS['LOG_CALL_ONSCN']			=32;
$GLOBALS['LOG_CALL_CLR']			=33;

*/
