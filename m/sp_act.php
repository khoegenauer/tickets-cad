<?php
/*
action/patients module
6/15/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL	^ E_DEPRECATED);
require_once '../incs/functions.inc.php';		//7/28/10
require_once 'incs/sp_functions.inc.php';		// 4/8/2013

@session_start();
if (! array_key_exists('SP', $_SESSION)) {
    header("Location: index.php");
    }
$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tickets SP <?php echo get_text("Patients");?>/<?php echo get_text("Actions");?></title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="./js/misc.js" type="text/javascript"></script>

<?php
    function get_act_response($in_list) {				// returns string of comma-sep'd handles
        $id_list =  str_replace ( " ", ",", $in_list);
        $query = "SELECT `r`.`handle`, `id` FROM `$GLOBALS[mysql_prefix]responder`  `r`
                    WHERE (`r`.`id` IN ({$id_list}) )
                    ORDER BY `handle` ASC";

        $result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        $handle_str = $sep = "";
        while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {		// each data row
            $handle_str .= $sep . $in_row['handle'];
             $sep = ", ";
            }

        return $handle_str;
        }


    if ( empty ($_POST['act_id_str'] ) ) {				//if no list build it
        $temp = explode (",", $_POST['id_str'] );

        $ticket_id = intval($temp [$_POST['id']]);						// precaution

        $query = "
            (SELECT 0 AS `which`,`a`.`id`, `a`.`ticket_id`
                FROM `$GLOBALS[mysql_prefix]action` `a`
                WHERE `a`.`ticket_id` = {$ticket_id} )
            UNION DISTINCT
            (SELECT 1 AS `which`,`p`.`id`, `p`.`ticket_id`
                FROM `$GLOBALS[mysql_prefix]patient` `p`
                WHERE `P`.`ticket_id` = {$ticket_id} )
            ORDER BY `ticket_id` ASC";

        $result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);

        $sep = $_POST['act_id_str'] = "";
        while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {			// to comma-sep'd string of pairs
             $_POST['act_id_str'] .= "{$sep}{$in_row['which']}/{$in_row['id']}";		// type slash id
             $sep = ",";
             }
        $_POST['act_id'] = "0";						// point to origin
        unset($result);
        }		// end if ( empty )
    else {			// ========================================		MIDDLE		=============================================
        $temp = explode ("," , $_POST['act_id_str']);
        $temp1 = $temp [$_POST['act_id']];
        }
?>
        <script>
        DomReady.ready(function () {
            var act_id_array = document.navForm.act_id_str.value.split(",");
            var timer = setInterval(function () {getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
            });
    </script>
<?php
if (intval(get_variable('broadcast'))==1) {
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013
    }
?>
    </head>
    <body>				<!-- <?php echo __LINE__; ?> -->
    <center>
<?php
    require_once 'incs/header.php';
     $act_id_array = explode (",", $_POST['act_id_str']);
     $larrow = (intval($_POST['act_id'] == 0))? 							"" : "&laquo;&nbsp;" ;	// suppress left-pointer if at origin
    $rarrow = (intval($_POST['act_id']) == count ($act_id_array) -1 )? 	"" : "&nbsp;&raquo;" ;	// suppress right-pointer if at end
?>
        <div style='float:left; '>
            <div id = "left-side" onclick = 'actnavBack();' style = "position:fixed; left: 0px; top:125px; margin-left:10px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
        </div>
        <div style='float:right; '>
            <div id = "right-side" onclick = 'actnavFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
        </div>
<?php
        $div_height = $_SESSION['SP']['scr_height'] - 120;								// nav bars
        $div_width = floor($_SESSION['SP']['scr_width'] * .6) ;							// allow for nav arrows
        echo "<center><div style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'><br />";

        $act_id_array = explode (",", $_POST['act_id_str']);
        $the_count =  count ($act_id_array);
		$the_nth = intval($_POST['id']) + 1;
        $the_pair = $act_id_array[intval($_POST['act_id'])];		// pull nth entry
        $the_array = explode ("/", $the_pair);
        $the_type = ($the_array[0] == 0 )? get_text("Action") : get_text("Patient");
        echo "<br/><br/><div style = 'text-align:center; margin-top:60px;'><h2>{$the_type} Record </h2></div>\n";
        $the_id = $the_array[1];

        if ($the_array[0] == 0) {				// action record?
            $query = "SELECT `a`.`description`,
                `a`.`responder`,
                `u`.`user`,
                `t`.`scope` AS `ticket`,
                `a`.`date`,
                `a`.`updated` AS `as of`
                FROM `$GLOBALS[mysql_prefix]action` `a`
                LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`u`.`id` = `a`.`user`)
                LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` ON (`t`.`id` = `a`.`ticket_id`)
                WHERE `a`.`id` = {$the_id} LIMIT 1";
            }
        else {									// patient record
            $query = "SELECT
                `p`.`description`,
                `p`.`name`,
                `p`.`fullname` AS `full name`,
                `p`.`dob`,
                `p`.`gender`,
                `p`.`insurance_id`,
                `t`.`scope` AS `ticket`,
                `f`.`name` AS `facility`,
                `p`.`facility_contact` AS `contact`,
                `p`.`date`,
                `p`.`updated` AS `as of`,
                `u`.`user` AS `by`
                FROM `$GLOBALS[mysql_prefix]patient` `p`
                LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` 			ON (`u`.`id` = `p`.`user`)
                LEFT JOIN `$GLOBALS[mysql_prefix]ticket` `t` 		ON (`t`.`id` = `p`.`ticket_id`)
                LEFT JOIN `$GLOBALS[mysql_prefix]facilities` `f` 	ON (`f`.`id` = `p`.`facility_id`)
                WHERE `p`.`id`  = {$the_id} LIMIT 1";
            }

//		dump ($query);
        $result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), basename( __FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_array($result)) ;

        $hides = array("action_type");								// hide these columns
        echo "\n<table border=1>\n";
        for ($i=0; $i< mysql_num_fields($result); $i++) {						// each field
            if (!(substr(mysql_field_name($result, $i ), 0, 1) == "_")) {  		// meta-data?
                if ( ! ( empty($row[$i] ) ) ) {
                    $fn = get_text ( ucfirst(mysql_field_name($result, $i ) ) );
                    switch (mysql_field_name($result, $i) ) {
                        case "gender":
                            $gender_ary = array("", "M", "F", "T", "U", "?", "?", "?", "?");
                            echo "<tr><td>{$fn}:</td><td> {$gender_ary [intval($row[$i])]}</td></tr>\n";
                            break;

                        case "responder":
                            $temp = get_act_response($row[$i]);
                            echo "<tr><td>{$fn}:</td><td>{$temp}</td></tr>\n";
                            break;

                        case "ticket":
                            $the_rand_str = "?rand=" . strval(time());
                            $the_onclick_str = "document.navForm.action = \"sp_tick.php{$the_rand_str}\";";
                            $the_onclick_str .= "document.navForm.submit();";
                            echo "<tr onclick = '{$the_onclick_str}'><td>{$fn}:</td>
                                <td><span>{$row[$i]}</span>
                                    <span style = 'margin-left:20px; font-weight:bold;'><img src = './images/go-right.png'/></span></td>
                                </tr>\n";
                            break;

                        case "date":
                        case "as of":
                            $datestr = format_date(strval(strtotime($row[$i])));
                            echo "<tr><td>{$fn}:</td><td>{$datestr}</td></tr>\n";
                            break;
                        default:
                            echo "<tr><td>{$fn}:</td><td>{$row[$i]}</td></tr>\n";

                        };				// end switch ()

                    echo "</td></tr>\n";

                    }
                }				// end meta-data?
            }		// end for ($i...) each row element
        echo "\n</table></div>\n";

        $act_id_array = explode (",", $_POST['act_id_str']);

    require_once 'incs/footer.php';
    $idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />

<input type = hidden name = "act_id" 		value = "<?php echo $_POST['act_id'];?>" />		<!-- array index of target record -->
<input type = hidden name = "act_id_str" 	value = "<?php echo $_POST['act_id_str'];?>" />
<input type = hidden name = "ticket_id" value = "<?php echo $ticket_id;?>" />
</form>
<script>
    function navTo(url, id) {
        var ts = Math.round((new Date()).getTime() / 1000);
        document.navForm.action = url +"?rand=" + ts;
        document.navForm.id.value = (id == null)? "": id;
        document.navForm.submit();
        }				// end function navTo ()
</script>
</body>
</html>
