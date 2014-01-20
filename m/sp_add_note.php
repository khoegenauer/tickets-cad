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
if (! array_key_exists('SP', $_SESSION)) {
    header("Location: index.php");
    }
$me = $_SESSION['SP']['user_unit_id'] ;				// possibly empty
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tickets SP <?php echo get_text("Add note");?></title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="./js/misc.js" type="text/javascript"></script>
<?php
if (intval(get_variable('broadcast'))==1) {
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013
    }

if ( ! ( array_key_exists ( "update", $_POST ) ) ) { 	// ========================	TOP	==========================
    $the_ticket_id = quote_smart($_POST['ticket']);
    $query = "SELECT `description`, `comments`, `scope` FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$the_ticket_id} LIMIT 1;";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
    $row = stripslashes_deep(mysql_fetch_array($result));

    $addl = "";
?>
<style>
blockquote {
    font: .9em normal helvetica, sans-serif; text-align:left;
    background-color: #faebbc;
    width:40%;
    }
</style>

<script>
        DomReady.ready(function () {		// 53
            var id_array = document.navForm.id_str.value.split(",");
            document.frm_note.frm_text.focus();
            });

        function validate() {
            if (document.frm_note.frm_text.value.trim().length==0) {
                alert("Enter text - or Cancel");
                document.frm_note.frm_text.focus();

                return false;
                }
            else {
                document.frm_note.submit();
                }
            }
</script>
</head>
<body>		<!-- <?php echo __LINE__;?> -->
<center>
<?php
    require_once 'incs/header.php';
    echo "<h3>" . get_text("Incident") . " '{$row['scope']}'</h3>";

    if ( strlen ( trim( $row['description'] ) ) > 0 ) {
        echo "\n<blockquote>{$row['description']}</blockquote>";
        $addl = "additional";
        }
    if ( strlen ( trim ( $row['comments'] ) ) > 0 ) {
        echo "\n<blockquote>{$row['comments']}</blockquote> <br/>";
        $addl = "additional";
        }
$disabled = ( ( sp_is_guest() ) || ( sp_is_member() ) ) ? "disabled" : "" ;		//
?>
<script>
    function set_signal(inval) {
        var temp_ary = inval.split("|", 2);		// inserted separator
        document.frm_note.frm_text.value+=" " + temp_ary[1] + ' ';
        document.frm_note.frm_text.focus();
        }		// end function set_signal()
</script>
<FORM name='frm_note' METHOD='post' ACTION = '<?php echo basename(__FILE__);?>?rand=<?php echo time();?>'>
<table border = 0>
<tr><td colspan = 2 align = center>
    <H4>Enter <?php echo $addl;?> note text</H4>
    </td></tr>
    <tr><td><td>
    <TEXTAREA name='frm_text' COLS=40 ROWS = 2 placeholder="here ..." <?php echo $disabled;?>></TEXTAREA>
    </td></tr>
<tr valign = 'top'><td>
    <B>Signal</B> &raquo;
    </td><td>
        <SELECT name='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;' <?php echo $disabled;?>>	<!--  11/17/10 -->
        <OPTION value=0 SELECTED>Select</OPTION>
<?php											// signals list
                $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
                $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
                    echo "\t<OPTION value='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT><BR />
</td></tr>
<tr><td colspan=2 align='center'>
    <B>Apply to:</B>&nbsp;&nbsp;&nbsp;
        <?php echo get_text("Description"); ?>	 &raquo; <input type = 'radio' name='frm_add_to' value='0' CHECKED <?php echo $disabled;?> />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php echo get_text("Disposition"); ?>&raquo; <input type = 'radio' name='frm_add_to' value='1' <?php echo $disabled;?>/><br/><br/>
    </td></tr>
    <tr><td colspan = 2 align = 'center'>
        <input type = 'button' value = 'Cancel' 	onClick = 'navTo ("sp_tick.php", <?php echo $_POST['id'];?>)' />
        <input type = 'button' value = 'Reset' 		onClick = 'this.form.reset()' <?php echo $disabled;?>  STYLE = 'margin-left:40px' />
        <input type = 'button' value = 'Next' 		onClick = 'validate()' <?php echo $disabled;?>  STYLE = 'margin-left:40px' />
    </td></tr></table>

<input type = hidden name = 'update' 		value='1' />									<!-- signifies 'do update' -->
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "<?php echo $_POST['ticket'];?>" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_TICKET'];?>" />
</form>

<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "<?php echo $_POST['ticket'];?>" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_TICKET'];?>" />
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
        $field_name = array('description', 'comments');
        $the_in_str = quote_smart ( strip_tags ( trim ( addslashes ( $_POST['frm_text'] ) ) ) ) ;

        @session_start();
        $the_date = format_date(strval(now()));
        $the_text = quote_smart ("[{$_SESSION['SP']['user']}:{$the_date}]" . addslashes ( strip_tags ( trim ( $_POST['frm_text'] ) ) ) . "\n");;

        $query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`=
            concat ( `{$field_name[$_POST['frm_add_to']]}` , {$the_text} )
            WHERE `id` = " . quote_smart($_POST['ticket'])  . " LIMIT 1";

        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
?>
<script>
    DomReady.ready(function () {				// go to sp_tick - 159
        document.navForm.submit();
        });
</script>
</head>
<body>	<!-- <?php echo __LINE__;?> -->
<center>
<form name = "navForm" method = post action = 'sp_tick.php?rand=<?php echo time();?>'>
<input type = hidden name = "id" 			value = "<?php echo $_POST['id'];?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 		value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "ticket" 		value = "<?php echo $_POST['ticket'];?>" />
<input type = hidden name = "act_id" 		value = "" />				<!-- secondary navigation -->
<input type = hidden name = "act_id_str" 	value = "" />				<!-- 		 "			  -->
<input type = hidden name = "group" 		value = "<?php echo $GLOBALS['TABLE_TICKET'];?>" />
</form>
<?php
        unset($result);
    }		// end if/else (empty())		====================	BOTTOM	====================

    require_once 'incs/footer.php';
?>
</body>
</html>
