<?php
/**
 * @package u_unit_types.php
 * @author John Doe <john.doe@example.com>
 * @since
 * @version
 */
?>
<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" /><!-- 1/21/09 - APRS moved to responder schema  -->
        <INPUT TYPE="hidden" NAME="func" 		VALUE="pu" />
        <INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>" />
        <INPUT TYPE="hidden" NAME="indexname" 	VALUE="id" />
        <INPUT TYPE="hidden" NAME="sortby" 		VALUE="id" />
        <INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
        <INPUT TYPE="hidden" NAME="frm__by" 	VALUE="<?php print $_SESSION['user_id']; ?>" />
        <INPUT TYPE="hidden" NAME="frm__from" 	VALUE="<?php print $_SERVER['REMOTE_ADDR']; ?>" />
        <INPUT TYPE="hidden" NAME="frm__on" 	VALUE="<?php print mysql_format_date(time() - (get_variable('delta_mins')*60));?>" />
        <INPUT TYPE="hidden" NAME="frm_icon" 	VALUE="<?php print $row['icon'];?>" />
        <INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />

        <TABLE BORDER="0" ALIGN="center">
        <TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1"><?php print gettext("Table 'unit_types' - Update Entry");?></FONT></TD></TR>
        <TR><TD>&nbsp;</TD></TR>
    <TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Type name');?>:</TD>
        <TD><INPUT  ID="ID1" CLASS="dirty" MAXLENGTH="16" SIZE="16" type="text" NAME="frm_name" VALUE="<?php print $row['name'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value);"> <SPAN class='warn' ><?php print gettext('text');?></SPAN></TD></TR>
    <TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Description');?>:</TD>
        <TD><INPUT  ID="ID2" CLASS="dirty" MAXLENGTH="48" SIZE="48" type="text" NAME="frm_description" VALUE="<?php print $row['description'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value);"> <SPAN class='warn' ><?php print gettext('text');?></SPAN></TD></TR>
    <TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Icon');?>:</TD>
        <TD><IMG ID='ID3' SRC="<?php print './our_icons/' . $sm_icons[$row['icon']];?>"/></TD></TR>
    <TR CLASS="even"><TD></TD><TD ALIGN='center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<SCRIPT>
            for (i=0; i<icons.length-1; i++) {						// generate icons display
                document.write(gen_img_str(i)+"&nbsp;&nbsp;\n");
                }
</SCRIPT>
            &laquo; <SPAN class='warn'><?php print gettext('click to change icon');?> </SPAN> &nbsp;
        </TD></TR>
        <TR><TD COLSPAN="99" ALIGN="center">
        <BR />
    <INPUT TYPE="button"	VALUE="<?php print gettext('Cancel');?>" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="button"	VALUE="<?php print gettext('Reset');?>" onClick = "Javascript: document.u.reset();icon_to_form('<?php print $row['icon'];?>'); "/>&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="button" NAME="sub_but" VALUE="               <?php print gettext('Submit');?>                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/>

        </TD></TR>
        </FORM>
        </td></tr></table>

<?php
