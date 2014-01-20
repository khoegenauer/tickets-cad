<?php
/*
Add note page - adapted from Tickets
6/30/2013	initial release
*/
if ( !defined ( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL	^ E_DEPRECATED);
require_once '../incs/functions.inc.php';
require_once 'incs/sp_functions.inc.php';
@session_start();
if (empty($_SESSION['SP'])) {
    header("Location: index.php");
    }
$me = $_SESSION['SP']['user_unit_id'] ;				// possibly empty
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tickets SP <?php echo get_text("Units");?> Status Update</title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="./js/misc.js" type="text/javascript"></script>
<?php
if (intval(get_variable('broadcast'))==1) {
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013
    }

if ( ! ( array_key_exists ( "update", $_POST ) ) ) { 	// ========================	TOP	==========================

//											first collect the input
    $temp = explode (",", $_POST['id_str'] );
    $the_unit_id = intval($temp [$_POST['id']]);						// precaution

    $query = "SELECT `r`.`id`, `type`, `handle`, `un_status_id`,`type`, `y`.`name` AS `the_type`
        FROM `$GLOBALS[mysql_prefix]responder` `r`
        LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `y` ON (`r`.`type` = `y`.`id`)
        WHERE `r`.`id` = {$the_unit_id} LIMIT 1;";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $row = stripslashes_deep(mysql_fetch_array($result));
?>

<script>
    DomReady.ready(function () {		// 42
        var id_array = document.navForm.id_str.value.split(",");
        });
</script>
</head>
<body>		<!-- <?php echo __LINE__;?> -->
<center>
<?php
    require_once 'incs/header.php';

$disabled = ( ( sp_is_guest() ) || ( sp_is_member() ) ) ? "disabled" : "" ;		//
?>
<FORM name='frm_status' METHOD='post' ACTION = '<?php echo basename(__FILE__);?>?rand=<?php echo time();?>'>
<table border = 0 style = 'margin-top: 80px;'>
<tr><td colspan = 2 align = center>
    <H4>Change status for <?php echo $row['the_type'];?> <?php echo $row['handle'];?></H4>
    </td></tr>
<tr><td colspan = 2 align = center>
<?php
    echo sp_get_status_sel($row['id'], $row['un_status_id'], "u");

?>
    </td></tr>
<tr><td colspan=2 align='center'>

<tr><td colspan = 2 align = 'center'>
        <input type = 'button' value = 'Cancel' 	onClick = 'navTo ("sp_resp.php", <?php echo $_POST['id'];?>)' />&nbsp;&nbsp;&nbsp;&nbsp;
        <input type = 'button' value = 'Reset' 		onClick = 'this.form.reset()' <?php echo $disabled;?>/>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type = 'button' value = 'Next' 		onClick = 'document.frm_status.submit();' <?php echo $disabled;?>/>
    </td></tr></table>

<input type = hidden name = 'update' 		value='1' />									<!-- signifies 'do update' -->
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "unit_id" 		value = "<?php echo $row['id'];?>" />
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_RESPONDER'];?>" />
</form>

<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_RESPONDER'];?>" />
</form>
<script>
    function navTo(url, id) {
        document.navForm.action = url + "?rand=<?php echo time();?>";
        document.navForm.id.value = (id == null)? "": id;
        document.navForm.submit();
        }				// end function navTo ()
</script>

<?php
        }		// end if ( ! ( array_key_exists ( "update") ) )
//				================================================	MIDDLE	======================================
    else {				// do the deed


        $query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET
            `un_status_id`= 	" . quote_smart($_POST['frm_status_id']) . ",
            `updated` = 		" . quote_smart(now_ts()) . ",
            `user_id` = 		" . $_SESSION['SP']['user_id'] . "
            WHERE `id` = 		" . quote_smart($_POST['unit_id']) ." LIMIT 1";

        $result	= mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
        do_log($GLOBALS['LOG_UNIT_CHANGE'], $_POST['unit_id']);
?>
<script>
    DomReady.ready(function () {				// go to ????
        setTimeout("document.navForm.submit();", 2000);
        });
</script>
</head>
<body>	<!-- <?php echo __LINE__;?> -->
<center>
<div style = "margin-top:100px; text-align: center;">
<h2>Status update applied</h2>
</div>
<div style = 'margin-top:100px;'></div>		<!-- spacer -->
<form name = "navForm" method = post 		action = 'sp_resp.php?rand=<?php echo time();?>'>
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_RESPONDER'];?>" />
</form>
<?php
        unset($result);
    }		// end if/else (empty())		====================	BOTTOM	====================

    require_once 'incs/footer.php';
?>
</body>
</html>
