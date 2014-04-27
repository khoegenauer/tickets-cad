<?php
/**
 * @package u_places.php
 * @author John Doe <john.doe@example.com>
 * @since
 * @version
 */
?>
<!--
3/18/11 initial release - AS
3/26/2014 - updated to include information re buildinga
-->
    <SCRIPT src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php print get_variable('gmaps_api_key');?>"
            type="text/javascript"></SCRIPT>
    <SCRIPT type="text/javascript">

    var geocoder;		// note GLOBAL!
    var map;
/**
 *
 * @returns {undefined}
 */
    function initialize() {
      if (GBrowserIsCompatible()) {
        geocoder = new GClientGeocoder();
        map = new GMap2(document.getElementById("map_canvas"));
        map.setUIToDefault();										// 8/13/10

        map.addControl(new GMapTypeControl());
<?php print (get_variable('terrain') == 1)? "\t\tmap.addMapType(G_PHYSICAL_MAP);\n" : "";?>
        map.addControl(new GOverviewMapControl());
        map.enableScrollWheelZoom();

        var center = new GLatLng(<?php echo $row['lat']; ?>, <?php echo $row['lon']; ?>);
        map.setCenter(center, <?php echo get_variable('def_zoom'); ?>);

        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(map, "click", function (marker, point) {
            if (point) {
//				alert("point");
                document.u.frm_lat.value = point.lat().toFixed(6);
                document.u.frm_lon.value = point.lng().toFixed(6);
                map.clearOverlays();
                map.addOverlay(new GMarker(point));										// to center
                var center = new GLatLng( point.lat(),point.lng());
                map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels
                }
                });

/*
        GEvent.addListener(marker, "dragstart", function () {
            map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function () {
            marker.openInfoWindowHtml("Just bouncing along...");
        });
*/
        map.addOverlay(marker);
      }
    }			// end function initialize()
/**
 *
 * @param {type} theForm
 * @returns {undefined}
 */
    function addrlkup(theForm) {		   //
        var address = theForm.the_city.value + " "  + theForm.the_st.value;
        if (geocoder) {								// defined in function initialize()
            geocoder.getLatLng(
                address,
                function (point) {
                    if (!point) {
                        alert(address + " not found");
                        }
                    else {
                        map.setCenter(point, <?php echo get_variable('def_zoom'); ?>);
                        var marker = new GMarker(point);
                        document.u.frm_lat.value = point.lat().toFixed(6);
                        document.u.frm_lon.value = point.lng().toFixed(6);
                        map.clearOverlays();
                        map.addOverlay(new GMarker(point));										// to center
                        var center = new GLatLng( point.lat(),point.lng());
                        map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels
                        }
                    }
                );
            }
        }				// end function addrlkup()

/**
 *
 * @param {type} str
 * @returns {RegExp}
 */
    function is_float(str) {
        return /^[-+]?\d+(\.\d+)?$/.test(str);
        }

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function validate(theForm) {	//
        var errmsg="";
        if (theForm.frm_name.value == "") {errmsg+= "\t<?php print gettext('Place Name is required');?>\n";}
        if (theForm.frm_lat.value == "") {errmsg+= "\t<?php print gettext('Latitude value is required');?>\n";}
        else if (!
            (is_float(theForm.frm_lat.value) &&
            (theForm.frm_lat.value <=90.0) &&
            (theForm.frm_lat.value >= -90.0)
            )) 									{errmsg+= "\t<?php print gettext('Valid latitude is required');?>\n";}
        if (theForm.frm_lon.value == "") {errmsg+= "\t<?php print gettext('Longitude value is required');?>\n";}
        else if (!
            (is_float(JSfnTrim(theForm.frm_lon.value)) &&
            (theForm.frm_lon.value <=180.0) &&
            (theForm.frm_lon.value >= -180.0)
            )) 									{errmsg+= "\t<?php print gettext('Valid longitude is required');?>\n";}
//					 3/26/2014
		if (theForm.frm_apply_to.value == "bldg") {	
			if (theForm.frm_street.value.trim() == "") 	{errmsg+= "\tValid street addr is required\n";}
			if (theForm.frm_city.value.trim() == "") 	{errmsg+= "\tValid City is required\n";}
			if (theForm.frm_state.value.trim() == "") 	{errmsg+= "\tValid State is required\n";}		
			} 

        if (errmsg!="") {
            alert ("<?php print gettext('Please correct the following and re-submit');?>:\n\n" + errmsg);

            return false;
            }
        else {
            theForm.submit();
            }
        }				// end function validate(theForm)

</SCRIPT>
        <FORM NAME="u" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
        <INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
        <INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
        <INPUT TYPE="hidden" NAME="id" 			VALUE="<?php print $row['id'];?>" />
        <INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
        <INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
        <INPUT TYPE="hidden" NAME="func" 		VALUE="pu"/>
        <INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
		<INPUT TYPE="hidden" NAME="frm_apply_to" VALUE="<?php print $row['apply_to'];?>" /> <!-- db update value; initially the default; revised onclick -->

<?php
$label = ($row['apply_to'] == "bldg") ? "Building" : "Place" ;
?>
        <TABLE BORDER=0 ID='outer' ALIGN= 'center'>
        <TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header"><?php echo get_variable('map_caption');?></FONT><BR /><BR /></TD></TR>
        <TR><TD>
            <TABLE BORDER="0" ALIGN="center">
			<TR CLASS="even" VALIGN="top"><TD COLSPAN="2" ALIGN="CENTER" CLASS="td_label" ><FONT SIZE="+1">Update '<?php echo $label;?>' Data</FONT></TD></TR>
			<TR><TD><P />&nbsp;</TD></TR>
			<TR CLASS = "even"><TD></TD>
				<TD COLSPAN=3>&nbsp;&nbsp;<?php print get_text("City");?>:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="the_city" VALUE="<?php echo $row['city'];?>" />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php print get_text("St");?>:&nbsp;<INPUT MAXLENGTH="4" SIZE="2" TYPE="text" NAME="the_st" VALUE="<?php echo $row['state'];?>" /><button type="button" style = "margin-left:40px;" onClick="addrlkup(this.form)">
				<img src="./markers/glasses.png" alt="Lookup location."  />&nbsp;&nbsp;Lookup</TD></TR>
			<TR><TD>&nbsp;</TD></TR>

		<TR VALIGN="baseline" CLASS="even"><TD CLASS="td_label" ALIGN="right"><?php echo $label;?> name:</TD>
			<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_name" VALUE="<?php echo $row['name'];?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>

			<TR><TD>&nbsp;</TD></TR>
<!-- new -->
			<TR VALIGN="baseline" CLASS="even" ID = 'brow1' >
				<TD CLASS="td_label" ALIGN="right">Street:</TD>
				<TD><INPUT ID="ID3" CLASS="dirty" MAXLENGTH="96" SIZE="64" type="text" NAME="frm_street" VALUE="<?php echo $row['street']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD>
				</TR>
			<TR VALIGN="baseline" CLASS="odd" ID = 'brow2' >
				<TD CLASS="td_label" ALIGN="right">City:</TD>
				<TD><INPUT ID="ID4" CLASS="dirty" MAXLENGTH="32" SIZE="32" type="text" NAME="frm_city" VALUE="<?php echo $row['city']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> 
				<span CLASS="td_label" style = "margin-left:20px;"> St: <INPUT ID="ID5" CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_state" VALUE="<?php echo $row['state']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)" ></span>
				</TD>
				</TR>

			<TR VALIGN="baseline" CLASS="even" ID = 'brow4' >
				<TD CLASS="td_label" ALIGN="right"><?php echo $label;?> information:</TD>
				<TD><TEXTAREA ID='ID6' CLASS='dirty' NAME='frm_information' COLS='64' ROWS = '1' onFocus="JSfnChangeClass(this.id, 'dirty');" STYLE='vertical-align:text-top;'><?php echo $row['information']; ?></TEXTAREA> </TD>
				</TR>
<!-- /new -->
            <TR><TD>&nbsp;</TD></TR>

		<TR VALIGN="baseline" CLASS="even" STYLE = "opacity:.2;"><TD CLASS="td_label" ALIGN="right">Lat:</TD>
			<TD><INPUT ID="ID2" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lat" VALUE="<?php echo $row['lat']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/>
			<SPAN CLASS="td_label" STYLE ="margin-left:20px;">Lon:&nbsp;&nbsp;&nbsp;
			<INPUT ID="ID3" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lon" VALUE="<?php echo $row['lon']; ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/></SPAN>
             </TD></TR>
                <TR><TD COLSPAN="99" ALIGN="center">
            <BR /><BR />
      <INPUT TYPE="button"	VALUE="<?php print gettext('Cancel');?>" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
      <INPUT TYPE="reset"		VALUE="<?php print gettext('Reset');?>"/>&nbsp;&nbsp;&nbsp;&nbsp;
      <INPUT TYPE="button" NAME="sub_but" VALUE="<?php print gettext('Submit');?>" onclick="validate(this.form);"/>

            </TD></TR>
            </FORM>
            </TD></TR></TABLE>
        </TD><TD>
            <div id="map_canvas" style="width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px; margin-left:20px;"></div>
        </TD></TR>
        </TABLE>
<SCRIPT>
initialize();
</SCRIPT>
<?php
