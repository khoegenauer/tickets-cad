<?php
/*
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL	^ E_DEPRECATED);
require_once '../incs/functions.inc.php';		//7/28/10
require_once 'incs/sp_functions.inc.php';		// 4/8/2013 get_resp_list_sql
@session_start();
if (! array_key_exists('SP', $_SESSION)) {
    header("Location: index.php");
    }

function get_resp_list_sql($me_param) {				// responder list sql -

    $unit_text = get_text("Units");
    $radius =  ( trim(get_variable("locale") ) ==0 ) ?  3959: 6371;
    $pos_array = ( ( is_ok_position ( $_SESSION['SP'] ['latitude'] , $_SESSION['SP'] ['longitude'] ) ) ) ?
        array ( $_SESSION['SP'] ['latitude'], $_SESSION['SP'] ['longitude']):
        array ( get_variable('def_lat'), get_variable('def_lng') ) ;

    $query_core = "
        `r`.`id`										AS `id`,
        CONCAT_WS(' / ', `r`.`handle`, `y`.`name`) 		AS `{$unit_text}`,
        1												AS `unit_status`,
        `r`.`mobile` 									AS `unit_tr`,

        `r`.`aprs`										AS `APRS`,
        `r`.`instam`									AS `instamapper`,
        `r`.`ogts`										AS `open GTS`,
        `r`.`locatea`									AS `locateA`,
        `r`.`gtrack`									AS `g tracker`,
        `r`.`t_tracker`									AS `t tracker`,
        `r`.`glat`										AS `g Latitude`,

        CONCAT_WS(' ',`street`,`city`, `state`) 		AS `loc`,
        `r`.`capab`										AS `capabilities`,
        `r`.`handle`									AS `handle`,

        ( ROUND ( {$radius} * acos (
            cos(radians(  {$pos_array[0]} ) ) *
            cos(radians(`lat`) ) *
            cos(radians(`lng`) - radians( {$pos_array[1]} ) ) +
            sin(radians( {$pos_array[0]} ) ) *
            sin(radians(`lat`) ) ) , 1 ) )				AS `dist`,
        SUBSTRING(CAST(`updated` AS CHAR),9,8 ) 		AS `as of`
        FROM `$GLOBALS[mysql_prefix]responder` `r`
        LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` 	ON (`r`.`un_status_id` = `s`.`id`)
        LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` 	ON (`r`.`type` = `y`.`id`)
        ";

    return "
        ( SELECT `r`.`id`, 1 AS `mine`, {$query_core} WHERE (`r`.`id` =   {$me_param} ) )
        UNION
        ( SELECT `r`.`id`, 0 AS `mine`, {$query_core} WHERE (`r`.`id` <>  {$me_param} ) )
        ORDER BY `mine` DESC, `handle` ASC
        ";
    }					// end function get_resp_list_sql

$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tickets SP <?php echo get_text("Calls");?></title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1">-->
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <script src="./js/misc.js" type="text/javascript"></script>

<?php

    function set_post_vals($id) {
        $query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]responder` ORDER BY `id` ASC";
        $result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        $sep = $_POST['id_str'] = "";
        $ctr = 0;																// build $_POST['id_str'] as
        while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// comma sep'd string of id's
             $_POST['id_str'] .= $sep . $in_row['id'];
             if ($in_row['id'] == $id) { $_POST['id'] = $ctr; }
             $sep = ",";
            $ctr++;
             }
        }		// end function set_post_vals ()

    if ( array_key_exists("responder_id", $_POST ) ) { set_post_vals ( $_POST['responder_id'] ) ; }		// requested id

//		====================================	TOP		=========================================

    if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 )  ) {									//	show the one record

        $id_array = explode (",", $_POST['id_str']);
        $the_id = $id_array[intval($_POST['id'])];		// nth entry is record id

        function get_sidelinks() {		// returns 2-element array of strings
            global $id_array;
            $out_arr = array("", "");
            if ($_POST['id'] > 0) {		// if not at array origin then a prior one exists
                $query = "SELECT `handle` AS `left_one`	FROM `$GLOBALS[mysql_prefix]responder`
                    WHERE `id` = {$id_array[($_POST['id']-1)]} LIMIT 1";
                $result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
                $in_row = stripslashes_deep(mysql_fetch_array($result));
                $out_arr[0] = $in_row['left_one'];
                }
            if ( $_POST['id'] < count ($id_array)-1 ) {		// then not at end
                $query = "SELECT `handle` AS `right_one` FROM `$GLOBALS[mysql_prefix]responder`
                    WHERE `id` = {$id_array[($_POST['id']+1)]} LIMIT 1";
                $result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
                $in_row = stripslashes_deep(mysql_fetch_array($result));
                $out_arr[1] = $in_row['right_one'];
                }

            return $out_arr;
            }
?>
            <script>

            DomReady.ready(function () {
                var id_array = document.navForm.id_str.value.split(",");
//				var timer = setInterval(function () {getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
                });

//			do_set_time("dispatched", {$the_id}, 0)
            function do_set_time(column, record_id, cell_id, function_id) {		// ajax call to set selected dispatch time

                function the_callback(req) {
                    document.getElementById(cell_id).innerHTML=req.responseText;
                    CngClass(cell_id, "bright");									// highlight for 2 seconds
                    setTimeout ( function () {  CngClass(cell_id, "plain")}, 3000);
                    }		// end function the_callback()

                var params = "the_column="+ column + "&record_id=" +record_id + "&function_id=" + function_id;		//
                var url = "./ajax/set_disp_times.php";
                 sendRequest(url,the_callback, params);		//  issue AJAX request
                 }		// end function do set_time

            function call_map() {
                document.navForm.action = 'sp_map_spec.php';	//
                document.navForm.submit();
                }

            function call_status(id_val) {
                navTo ('sp_unit_stat_update.php', <?php echo  $_POST['id'];?>) ;			// navTo (url, id)
                }

        </script>
<?php
if (intval(get_variable('broadcast'))==1) {
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013
    }
?>
        </head>
        <body>		<!-- <?php echo __LINE__; ?> -->
<?php
            function set_click_td($rcd_id, $cell_id, $the_str) {
                $click_str = "onclick = \"do_set_time('{$the_str}', {$rcd_id}, '{$cell_id}', 0)\"";

                return "<td id = '{$cell_id}' {$click_str} class='click'><b>Set</b></td></tr>\n";
                }

            require_once 'incs/header.php';
             $id_array = explode (",", $_POST['id_str']);
             $link_arr = get_sidelinks ();

             $larrow = (intval($_POST['id'] == 0))? "" : "&laquo;&nbsp; <span style = 'font-size: 50%;'>{$link_arr[0]}</span>" ;					// suppress display if at origin
            $rarrow = (intval($_POST['id']) == count ($id_array) -1 )? "" : "<span style = 'font-size: 50%;'>{$link_arr[1]}</span>&nbsp;&raquo;" ;	// suppress display if at end
?>
        <div style='float:left; '>
            <div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 0px; top:125px; margin-left:10px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
        </div>
        <div style='float:right; '>
            <div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
        </div>
<?php
            $the_count =  count ($id_array);
            echo "\n<br/><br/><center><h2>Selected " . get_text("Responder") . " (of {$the_count} )</h2>\n";

            $div_height = $_SESSION['SP']['scr_height'] - 120;								// nav bars
            $div_width = floor($_SESSION['SP']['scr_width'] * .6) ;							// allow for nav arrows
            echo "<div id = 'tbd' style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'><br />\n";

            echo "<table border=1 id = 'table' class='tablesorter table-striped' >\n";
            $query = get_resp_sql ($the_id);
//			snap(__LINE__, $query);

            $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
            $hides = array( "id", "lat", "lng", "dispatched", "callsign", "APRS", "Instamapper", "OGTS", "LocateA", "Gtrack", "t tracker", "g Latitude", "bg_color", "text_color" );

            $row = stripslashes_deep(mysql_fetch_array($result)) ;
            for ($i=0; $i< mysql_num_fields($result); $i++) {						// each field
                if ( ( !empty($row[$i] ) ) && ( !in_array(mysql_field_name($result, $i), $hides ) ) ) {	// suppress metadata and hides
                    if ( ( !empty($row[$i] ) ) &&  ( !is_null ( $row[$i] ) ) ) {

                        $fn = get_text ( do_colname (mysql_field_name($result, $i ) ) );
//						echo "<tr><td>{$fn}:</td><td>";

                        switch ( strtolower(mysql_field_name($result, $i) ) ) {
                            case "contact via":
                                if (is_email($row[$i]) ) {
                                    $onclick_str = "onclick = \"do_mail ('{$row[$i]}')\"";
                                    echo "<tr {$onclick_str}><td>{$fn}:</td><td><u>{$row[$i]}</u><img style = 'margin-left:32px;' src = './images/go-right.png'/></td></tr>";
                                    }
                                else {
                                    echo "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>";
                                    }
                                break;

                            case "unit_status":
                                $bg_color = $row['bg_color'];
                                $text_color = $row['text_color'];
                                $style_str = "style = 'color:{$text_color}; background-color:{$bg_color};'";
                                $temp = get_unit_status ($the_id);
                                switch ( count ($temp) ) {
                                    case 1 :	$the_val = $temp[0];									break;
                                    case 2 :	$the_val = "<b>{$temp[0]}</b> (to <i>{$temp[1]}</i>)";	break;	// dispatch/call status
                                    default:	echo "error " . __LINE__;
                                    }		// end switch()
                                $the_click_str = (  sp_is_guest() ||  sp_is_member() ) ?
                                    "" :
                                    " onclick = 'call_status ( {$the_id} ) '";
                                echo "<tr {$the_click_str}><td>{$fn}:</td><td>{$the_val}<img style = 'margin-left:20px;' src = './images/go-right.png'/</td></tr>";

                                break;

                            case "as of":
                                $the_val = format_date ( strval ( strtotime($row['as of'] ) ) );
                                echo "<tr><td>{$fn}:</td><td>{$the_val}</td></tr>";
                                break;

                            case "mobile":
                                $temp = get_tracking_type ($row);
                                $the_val =  ( is_null( $temp ) ) ? "" : $temp[0] . " (<i>" . get_mobile_time ($row). "</i>)" ;
                                echo "<tr><td>{$fn}:</td><td>{$the_val}</td></tr>";

                                break;

                            case "map":
                //				$span_str = "<span onclick = 'call_map ( ) ' style = 'margin-left:20px;'><img src = './images/go-right.png'/></span>";
                //				echo $span_str;
                                echo "<tr onclick = 'call_map ( ) '><td>{$fn}:</td><td align='center'><img src = './images/go-right.png'/></td></tr>";
                                break;

                            default:
                                echo "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>";
                            };				// end switch ()

                        echo "</td></tr>\n";
                        }
                    }				// end meta-data?
                }		// end for ($i...) each row element
            echo "</table><BR/><BR/><BR/><BR/>\n";

            $id_array = explode (",", $_POST['id_str']);
            }
//	==============================================	LIST	================================================
else {
//	require_once('incs/sql.inc.php');				// 9/2/2013
    $query = get_resp_list_sql ($me);
//	dump ($query);
    $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

    $sep = $_POST['id_str'] = "";											// build $_POST['id_str']
    while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// as comma sep'd string of id's
         $_POST['id_str'] .= $sep . $in_row['id'];
         $sep = ",";
         }
    @mysql_data_seek($result, 0) ;		// reset for pass 2

?>
        <script>
            DomReady.ready(function () {
                try { parent.frames['top'].start_cycle();}
                catch(err)	{ console.log("267") };

                document.getElementById("fr").style.display = "none";
                });				// end  DomReady.ready(
        </script>
        </head>
        <body>		<!-- <?php echo __LINE__; ?> -->
        <center>
<?php
            require_once 'incs/header.php';
            if ( mysql_num_rows($result) == 0 ) {
                echo "<div style = 'text-align:center; margin-top:200px;'><h2>No " . get_text("Responders") . " in database</h2></div>\n";
                }
            else {
//				$hides = array("the_group", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
                $hides = array("mine", "handle", "the_group", "as_of", "assign_id", "id", "lat", "lng", "APRS", "instamapper", "open GTS", "locateA", "g tracker", "t tracker", "g Latitude");		// hide these columns

//				echo "<br/><br/><br/><br/><br/>";
                $top_row = get_text("Responders") . " - <i>click/tap for details</i>";

                echo sp_show_list($result, $hides, basename(__FILE__), $top_row ) . "\n" ;	// do the list

                echo "<br/><br/>";			// show bottom rows
                }
            } 		// end else {} ==================================================

    require_once 'incs/footer.php';
    $idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "group" 	value = "<?php echo $GLOBALS['TABLE_RESPONDER'];?>" />
</form>
<script>
    function navTo(url, id) {
        var ts = Math.round((new Date()).getTime() / 1000);
        document.navForm.action = url +"?rand=" + ts;
        document.navForm.id.value = (id == null)? "": id;
        document.navForm.submit();
        }				// end function navTo ()

//	EventSource's response has a MIME type ("text/html") that is not "text/event-stream". Aborting the connection.


var evtSource = new EventSource("db_monitor.php");
source.addEventListener('open', function (event) {
    alert('Connection  opened');
    updateConnectionStatus('Connected', true);
    }, false);

evtSource.onmessage = function (e) {
    alert ("message: " + e.data);
    }

evtSource.onerror = function (e) {
    alert("EventSource failed@326.");
    };

</script>
</body>
</html>
