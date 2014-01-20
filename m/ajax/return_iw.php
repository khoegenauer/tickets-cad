<?php
/*
4/18/2013 - initial release
6/23/2013 - added roadinfo handling
12/6/2013 - corrected to handle 'me'
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting ( E_ALL ^ E_DEPRECATED );

error_reporting(0);

require_once '../../incs/functions.inc.php';
require_once '../incs/sp_functions.inc.php';


extract ($_POST);

$the_id = intval ($_POST['record_id']);
switch ($table_id) {

    case $GLOBALS['TABLE_TICKET']:
    case $GLOBALS['TABLE_CLOSED']:
        $query = get_tick_sql ($the_id);

        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        if (!(mysql_num_rows ( $result )) == 1 ) {
            report_error ( basename(__FILE__) . __LINE__);
            }
        else {
            $row = stripslashes_deep(mysql_fetch_array($result)) ;
            $hides = array("status", "lat", "lng", "_by", "in_types_id", "group", "id", "set_severity", "sort", "color" , "date", "street", "city", "state", "map", "nearby");		// hide these columns
            $the_status = get_status_str($row['status']);
            echo "\n<center><h2>{$the_status} ". get_text("Incident") . ": " . "{$row['incident/type']} </h2>\n";
            $ret_str =  "<table id='infowin' border=0>";
            for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
                if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?
                    if ( ! ( empty($row[$i] ) ) ) {
                        $fn = get_text(ucfirst(mysql_field_name($result, $i)));

                        switch (mysql_field_name($result, $i) ) {
                            case "severity":
                                $the_severity_class = get_severity_class($row[$i]);
                                $ret_str .= "<tr><td>{$fn}:</td><td class = '{$the_severity_class}'>" . get_severity($row[$i]) . "</td></tr>";		// get_status_str
                                break;
                            case "start":
                                $elapsed_str = my_date_diff ( now_ts(), $row[$i]);
                                $datestr = format_date(strval(strtotime($row[$i])));
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$datestr} <i>($elapsed_str)</i></td></tr>\n";
                                break;
                            case "as of":
                                $datestr = format_date(strval(strtotime($row[$i])));
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
                                break;
                            case "responding":
                                $ret_str .= "<tr><td>{$fn}:</td><td>" . get_response($row[$i]) . "</td></tr>";		// string of handles
                                break;
                            case "status":
                                $the_status = get_status_str($row[$i]);
                                $the_diff = my_date_diff ( $row["start"], mysql_format_date(now())) ;
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$the_status} ({$the_diff})</td></tr>";
                                break;

                            default:
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>";
                            };				// end switch ()

//						$ret_str .=  "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
                        }
                    }
                }
            $ret_str .=  "</table>\n";
            }
        $ret_str .=  sp_show_actions ($the_id);
        break;

    case $GLOBALS['TABLE_RESPONDER']:
    case $GLOBALS['ME']:				// 12/6/2013
//			unit_status    
		$query = get_resp_sql ($the_id);		
//		snap ( __LINE__, $query);

        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        if ( ! (mysql_num_rows ( $result ) ) == 1 ) {
            report_error ( basename(__FILE__) . __LINE__);
            }
        else {
            $row = stripslashes_deep(mysql_fetch_array($result)) ;
            $unit_text = get_text("Units");
            echo "\n<center><h2>" . get_text("Responder") ." " . "{$row[$unit_text]}</h2>\n";
            $hides = array("id","description", "un_status_id", "lat", "lng", "icon_str", "hide", "group", "bg_color", "text_color", "icon", "user_id", "type", "sort", "_on", "_from", "_by", "map", "callsign");

            $ret_str = "<table id='infowin'  border=0>";
            for ($i=0; $i< mysql_num_fields($result); $i++) {					// each field
                if (!in_array(mysql_field_name($result, $i), $hides) ) {		// filter hides
                    if ( ! (empty(  $row[$i]) ) ) {
                        $fn = do_colname ( get_text ( ucfirst(mysql_field_name($result, $i ) ) ) );
                        switch ( strtolower ( mysql_field_name($result, $i) ) ) {

                            case "unit_status":
                                $temp = get_unit_status ($the_id);
                                switch ( count ($temp) ) {
                                    case 1 :	$unit_status = $temp[0];					break;
                                    case 2 :	$unit_status =  "<b>{$temp[0]}</b> (<i>{$temp[1]}</i>)";	break;
                                    default:	$unit_status =  "error " . __LINE__;
                                    }		// end switch()
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$unit_status}</td></tr>\n";
                                break;

                            case "nearby" :
                            case "aprs" :
                            case "instamapper" :
                            case "open gts" :
                            case "locatea" :
                            case "g tracker" :
                            case "t tracker" :
                            case "g latitude" :
                                $callsign = $row['callsign'];
                                $ret_str .= "";
                                break;

                            case "mobile":
                                $temp = get_tracking_type ($row);
                                $mobile_str =  ( is_null( $temp ) ) ? "" : $temp[0] . " (<i>" . get_mobile_time ($row). "</i>)" ;
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$mobile_str}</td></tr>\n";
                                break;

                            case "as of":
                                $datestr = format_date(strval(strtotime($row[$i])));
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
                                break;

                            case "contact via":
                                if (is_email($row[$i]) ) {
                                    $onclick_str = "onclick = \"do_mail ('{$row[$i]}')\"";		// address string
                                    $ret_str .= "<tr {$onclick_str}><td>{$fn}:</td><td>{$row[$i]} <img style = 'margin-left:32px;' src = './images/go-right.png'/></td></tr>\n";
                                    }
                                else {
                                    $ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
                                    }
                                break;

                            case "dist":
                                $measure =  ( trim(get_variable("locale") ) == 0 ) ? "mi" : "km";
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]} {$measure}</td></tr>\n";
                                break;

                            default:
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
                            }				// end switch
                        }				// end if ( ! (empty(... ) ) )
                    }				// end filter hides
                }				// end for ($i ... )

            $ret_str .= "</table>\n";
            }				// end if/else error
        break;			// end case $GLOBALS['TABLE_RESPONDER']

    case $GLOBALS['TABLE_FACILITY']:
        $query = get_fac_sql ($the_id) ;
        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        if (!(mysql_num_rows ( $result )) == 1 ) {
            report_error ( basename(__FILE__) . __LINE__);
            }
        else {
            $row = stripslashes_deep(mysql_fetch_array($result)) ;
            $hides = array("lat", "lng", "type", "icon", "id", "_by", "_from", "_on", "user" , "map" );			// hide these columns
            echo "\n<center><h2>" . get_text("Facility") ." " . "{$row['facility']}</h2>\n";

            $ret_str = "<table id='infowin'  border=0>";

            for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
                if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?
                    if ( ! (empty($row[$i]) ) ) {
                        $fn = get_text(ucfirst(mysql_field_name($result, $i)));
                        switch ( strtolower ( mysql_field_name($result, $i) ) ) {
                            case "as of":
                                $datestr = format_date(strval(strtotime($row[$i])));
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
                                break;
                            default:
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
                            }				// end switch
                        }
                    }
                }
            $ret_str .= "</table>\n";
            }
        break;

    case $GLOBALS['TABLE_ROAD']:				// 6/23/2013

        $query = "SELECT
            `r`.`title`,
            `r`.`description`,
            `r`.`date` AS `as of`,
            `r`.`lat`,
            `r`.`lng`,
            `u`.`user` AS `by`
            FROM `$GLOBALS[mysql_prefix]roadinfo` `r`
            LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`u`.`id` = `r`.`_by`)
            WHERE `r`.`id` = {$the_id} LIMIT 1";

        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        if (!(mysql_num_rows ( $result )) == 1 ) {
            report_error ( basename(__FILE__) . __LINE__);
            }
        else {
            $row = stripslashes_deep(mysql_fetch_array($result)) ;
            $hides = array("id", "lat", "lng", "", "_from", "_on", "icon" );						// hide these columns
            echo "\n<center><h2>" . get_text("Road information") . " {$row['title']}</h2>\n";
            $ret_str = "<table id='infowin'  border=0>";

            for ($i=0; $i< mysql_num_fields($result); $i++) {				// each field
                if (!in_array(mysql_field_name($result, $i), $hides)) {		// filter hides ?
                    if ( ! (empty($row[$i]) ) ) {
                        $fn = get_text(ucfirst(mysql_field_name($result, $i)));
                        switch ( mysql_field_name ( $result, $i ) ) {
                            case "as of":
                                $datestr = format_date(strval(strtotime($row[$i])));
                                $ret_str .= "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
                                break;
                            default:
                                $ret_str .=  "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";
                            }				// end switch ()
                        }
                    }
                }
            $ret_str .= "</table>\n";
            }
        break;

    default:
        report_error ( basename(__FILE__) . __LINE__);
        return "error " . __LINE__;
    }				// end switch()

    echo $ret_str;
    exit();