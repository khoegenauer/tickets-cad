<?php

include'./incs/error_reporting.php';

@session_start();
require_once './incs/functions.inc.php';		//7/28/10
if ($istest) {
//	dump(basename(__FILE__));
    print "GET<br />\n";
    dump($_GET);
    print "POST<br />\n";
    dump($_POST);
    }
$disposition = get_text("Disposition");				// 12/1/10
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print gettext('Add Note to Existing Incident');?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT>
/**
 *
 * @returns {unresolved}
 */
    String.prototype.trim = function () {				// 3/16/10

        return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
        };
/**
 *
 * @returns {Boolean}
 */
function validate() {
    if (document.frm_note.frm_text.value.trim().length==0) {
        alert("<?php print gettext('Enter text - or Cancel');?>");

        return false;
        }
    else {
        document.frm_note.submit();
        }
    }
</SCRIPT>
</HEAD>
<?php
if (empty($_POST)) {
?>
<BODY onLoad = "document.frm_note.frm_text.focus();">
<CENTER>
<H4><?php print gettext('Enter note text');?></H4>
<FORM NAME='frm_note' METHOD='post' ACTION = '<?php print basename(__FILE__);?>'>
<TEXTAREA NAME='frm_text' COLS=60 ROWS = 3></TEXTAREA>
<BR />
<SCRIPT>
/**
 *
 * @param {type} inval
 * @returns {undefined}
 */
    function set_signal(inval) {
        var temp_ary = inval.split("|", 2);		// inserted separator
        document.frm_note.frm_text.value+=" " + temp_ary[1] + ' ';
        document.frm_note.frm_text.focus();
        }		// end function set_signal()
</SCRIPT>

Signal &raquo;
<SELECT NAME='signals' onChange = 'set_signal(this.options[this.selectedIndex].text); this.options[0].selected=true;'>	<!--  11/17/10 -->
<OPTION VALUE=0 SELECTED><?php print gettext('Select');?></OPTION>
<?php
                $query = "SELECT * FROM `$GLOBALS[mysql_prefix]codes` ORDER BY `sort` ASC, `code` ASC";
                $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
                while ($row_sig = stripslashes_deep(mysql_fetch_assoc($result))) {
                    print "\t<OPTION VALUE='{$row_sig['code']}'>{$row_sig['code']}|{$row_sig['text']}</OPTION>\n";		// pipe separator
                    }
?>
            </SELECT><BR /><BR />

<B><?php print gettext('Apply to');?></B>&nbsp;:&nbsp;&nbsp;
<?php print gettext('Description');?> &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='0' CHECKED />&nbsp;&nbsp;&nbsp;&nbsp;
<?php print $disposition;?> &raquo; <INPUT TYPE = 'radio' NAME='frm_add_to' value='1' /><BR /><BR />
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Cancel');?>' onClick = 'window.close();' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Reset');?>' onClick = 'this.form.reset();' />&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Next');?>' onClick = 'validate();' />

<!-- <INPUT TYPE = 'button' VALUE = '<?php print gettext('Next');?>' onClick = 'this.form.submit();' /> -->
<INPUT TYPE = 'hidden' NAME = 'frm_ticket_id' VALUE='<?php print $_GET['ticket_id']; ?>' />
</FORM>
<?php
        }		// end if (empty($_POST))
    else {
        $field_name = array('description', 'comments');
		$frm_ticket_id=(int)$_POST['frm_ticket_id'];	//	4/4/14
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$frm_ticket_id} LIMIT 1";	//	4/4/14

//		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `id` = {$_POST['frm_ticket_id']} LIMIT 1";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
        $row = stripslashes_deep(mysql_fetch_assoc($result));
        $now = (time() - (get_variable('delta_mins')*60));
        $format = get_variable('date_format');
        $the_date = date($format, $now);
        $the_in_str = ($_POST['frm_add_to']=="0")? $row['description'] : $row['comments'] ;
        @session_start();
        $the_text = "{$the_in_str} [{$_SESSION['user']}:{$the_date}]" . strip_tags(trim($_POST['frm_text'])) . "\n";		// 1/7/2013

        $query = "UPDATE `$GLOBALS[mysql_prefix]ticket` SET `{$field_name[$_POST['frm_add_to']]}`= " . quote_smart($the_text) . " WHERE `id` = " . quote_smart($_POST['frm_ticket_id'])  ." LIMIT 1";
        $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//		dump ($query);
        $quick = (intval(get_variable('quick'))==1);				// 12/16/09
        if ($quick) {
?>
    <BODY onLoad = "opener.location.reload(true); opener.parent.frames['upper'].show_msg ('Note added!'); window.close();">
    </BODY></HTML>

<?php
            }				// end if ($quick)
        else {
?>
<BODY onLoad = "opener.location.reload(true);"><CENTER>
<BR /><BR />
<H3><?php print gettext('Note added to Incident');?> '<?php print $row['scope'];?>'</H3><BR /><BR />
<INPUT TYPE = 'button' VALUE = '<?php print gettext('Finished');?>' onClick = 'window.close();'/>
</CENTER>
</BODY>
</HTML>
<?php
        unset($result);
        }		// end if/else (quick)
    }		// end if/else (empty())
?>
