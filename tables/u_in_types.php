	<FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>"/>
	<INPUT TYPE="hidden" NAME="tablename"	VALUE="<?php print $tablename;?>"/>
	<INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
	<INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
	<INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
	<INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
	<INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>  <!-- process update -->
	<INPUT TYPE="hidden" NAME="frm_set_severity"	VALUE="<?php print $row['set_severity'] ;?>"/>&nbsp;&nbsp;&nbsp;&nbsp;

	<TABLE BORDER="0" ALIGN="center">
	<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER"><FONT SIZE="+1"><?php print gettext("Table 'Incident types' - Update/Delete Entry");?></FONT></TD></TR>
	<TR><TD>&nbsp;</TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Id');?>:</TD><TD><INPUT MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_id" VALUE="<?php print $row['id'] ;?>" onChange = "this.value=JSfnTrim(this.value);" disabled/> <SPAN class='warn' ><?php print gettext('numeric');?></SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Type');?>:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_type" VALUE="<?php print $row['type'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='warn' ><?php print gettext('text');?></SPAN></TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Description');?>:</TD>
		<TD><INPUT MAXLENGTH="60" SIZE="60" type="text" NAME="frm_description" VALUE="<?php print $row['description'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='warn' ><?php print gettext('text');?></SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Protocol');?>:</TD>
		<TD><TEXTAREA NAME="frm_protocol" COLS="90" ROWS = "1"><?php print $row['protocol'] ;?></TEXTAREA> <SPAN class='opt' ><?php print gettext('text');?></SPAN></TD></TR>

<?php
	$temp = array("", "", "");
	$temp[$row['set_severity']] = " checked ";
?>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Set severity');?>:</TD>
		<TD>
			<SPAN STYLE = "margin-left:20px;"><?php print gettext('Normal');?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '0' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[0];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;"><?php print gettext('Medium');?> &raquo; <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '1' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[1];?>/></SPAN>
			<SPAN STYLE = "margin-left:20px;"><?php print gettext('High');?> &raquo; 	 <INPUT TYPE = 'radio' NAME ='dum_severity'  VALUE = '2' onClick = "this.form.frm_set_severity.value=this.value;" <?php print $temp[2];?>/></SPAN>		
		</TD></TR>

	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Group');?>:</TD>
		<TD><INPUT MAXLENGTH="20" SIZE="20" type="text" NAME="frm_group" VALUE="<?php print $row['group'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Sort');?>:</TD><TD><INPUT MAXLENGTH=11 SIZE=11 TYPE= "text" NAME="frm_sort" VALUE="<?php print $row['sort'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' ><?php print gettext('numeric');?></SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Radius');?>:</TD><TD><INPUT MAXLENGTH=4 SIZE=4 TYPE= "text" NAME="frm_radius" VALUE="<?php print $row['radius'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' ><?php print gettext('numeric');?></SPAN></TD></TR>

	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Color');?>:</TD>
		<TD><INPUT MAXLENGTH="8" SIZE="8" type="text" NAME="frm_color" VALUE="<?php print $row['color'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' >text</SPAN></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Opacity');?>:</TD><TD><INPUT MAXLENGTH=3 SIZE=3 TYPE= "text" NAME="frm_opacity" VALUE="<?php print $row['opacity'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' ><?php print gettext('numeric');?></SPAN></TD></TR>
<?php
	$mg_select = "<SELECT NAME='frm_notify_mailgroup'>";
	$mg_select .= "<OPTION VALUE=0 SELECTED>" . gettext('Select Mail List') . "</OPTION>";
	$query_mg = "SELECT * FROM `$GLOBALS[mysql_prefix]mailgroup` ORDER BY `id` ASC";
	$result_mg = mysql_query($query_mg) or do_error($query_mg, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row_mg = stripslashes_deep(mysql_fetch_assoc($result_mg))) {
		$sel = ($row['notify_mailgroup'] == $row_mg['id']) ? "SELECTED" : "";
		$mg_select .= "\t<OPTION {$sel} VALUE='{$row_mg['id']}'>{$row_mg['name']} </OPTION>\n";
		}
	$mg_select .= "</SELECT>";
?>
	<TR VALIGN="baseline" CLASS="odd"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Notify Mailgroup');?>:</TD><TD><?php print $mg_select;?></TD></TR>
	<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php print gettext('Notify Email');?>:</TD><TD><INPUT MAXLENGTH=256 SIZE=60 TYPE= "text" NAME="frm_notify_email" VALUE="<?php print $row['notify_email'] ;?>" onChange = "this.value=JSfnTrim(this.value);"/> <SPAN class='opt' ><?php print gettext('text');?></SPAN></TD></TR>
	<TR><TD COLSPAN="99" ALIGN="center">
	<BR />
	<INPUT TYPE="button" 	VALUE="<?php print gettext('Cancel');?>" onClick = "Javascript: document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;

	<INPUT TYPE="reset" 	VALUE="<?php print gettext('Reset');?>"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="sub_but" VALUE="               <?php print gettext('Submit');?>                " onclick="this.disabled=true; JSfnCheckInput(this.form, this);"/>&nbsp;&nbsp;&nbsp;&nbsp;
	<INPUT TYPE="button" 	NAME="del_but" VALUE="<?php print gettext('Delete this entry');?>" onclick="if (confirm('Please confirm DELETE action')) {this.form.func.value='d'; this.form.submit();}"/></TD></TR>
	</FORM>
	</TD></TR></TABLE>
<?php	
