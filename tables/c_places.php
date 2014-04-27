<?php
/**
 * @package c_places.php
 * @author John Doe <john.doe@example.com>
 * @since
 * @version
 */
?>

<!--
3/18/11 initial release - AS
3/25/2014 - expanded to handle buildings
-->
<?php
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]places` ADD `apply_to` ENUM( 'city', 'bldg' ) NOT NULL DEFAULT 'city' AFTER `name` ,
	ADD `street` VARCHAR( 96 ) NULL DEFAULT NULL AFTER `apply_to` ,
	ADD `city` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `street` ,
	ADD `state` VARCHAR( 4 ) NULL DEFAULT NULL AFTER `city` ,
	ADD `information` VARCHAR( 1024 ) NULL DEFAULT NULL AFTER `state` ";
	$result = @mysql_query($query) ;		// note STFU
	
?>
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

        var center = new GLatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>);
        map.setCenter(center, <?php echo get_variable('def_zoom'); ?>);

        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(map, "click", function (marker, point) {
            if (point) {
                document.c.frm_lat.value = point.lat().toFixed(6);
                document.c.frm_lon.value = point.lng().toFixed(6);
                map.clearOverlays();
                map.addOverlay(new GMarker(point));										// to center
                var center = new GLatLng( point.lat(),point.lng());
                map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels
                }
                });

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
                        document.c.frm_lat.value = point.lat().toFixed(6);
                        document.c.frm_lon.value = point.lng().toFixed(6);
                        map.clearOverlays();
                        map.addOverlay(new GMarker(point));										// to center
                        var center = new GLatLng( point.lat(),point.lng());
                        map.setCenter(center, (<?php echo get_variable('def_zoom'); ?>+2));		// zoom in 2 levels
                        }
                    }
                );
            }
        }				// end function addrlkup()


		function $() {									// 2/11/09
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

	var sv_starting;
	function sv_win(theForm) {				// 4/1/2014
		sv_starting = false;							// test
		if(sv_starting) {return;}				// dbl-click proof
		sv_starting = true;					

		var thelat = theForm.frm_lat.value;
		var thelng = theForm.frm_lon.value;
		var url = "street_view.php?thelat=" + thelat + "&thelng=" + thelng;
		newwindow_sv=window.open(url, "sta_log",  "titlebar=no, location=0, resizable=1, scrollbars, height=450,width=640,status=0,toolbar=0,menubar=0,location=0, left=100,top=300,screenX=100,screenY=300");
		if (!(newwindow_sv)) {
			alert ("Street view operation requires popups to be enabled. Please adjust your browser options - or else turn off the Call Board option.");
			return;
			}
		newwindow_sv.focus();
		starting = false;
		}		// end function sv win()

/**
 *
 * @param {type} theForm
 * @returns {Boolean}
 */
    function validate(theForm) {	//
        var errmsg="";
        if (theForm.frm_name.value == "") {errmsg+= "\t<?php print gettext('Place name is required');?>\n";}
        if (theForm.frm_lat.value == "") {errmsg+= "\t<?php print gettext('Latitude value is required');?>\n";}
        else if (!
            (is_float(theForm.frm_lat.value) &&
            (theForm.frm_lat.value <=90.0) &&
            (theForm.frm_lat.value >= -90.0)
            )) 											{errmsg+= "\t<?php print gettext('Valid latitude is required');?>\n";}
        if (theForm.frm_lon.value == "") {errmsg+= "\t<?php print gettext('Longitude value is required');?>\n";}
        else if (!
            (is_float(JSfnTrim(theForm.frm_lon.value)) &&
            (theForm.frm_lon.value <=180.0) &&
            (theForm.frm_lon.value >= -180.0)
            )) 											{errmsg+= "\t<?php print gettext('Valid longitude is required');?>\n";}
				// 3/26/2014
		if (theForm.frm_apply_to.value == "bldg") {	
			if (theForm.frm_street.value.trim() == "") 	{errmsg+= "\tBuilding street addr is required\n";}
			if (theForm.frm_city.value.trim() == "") 	{errmsg+= "\tBuilding city is required\n";}
			if (theForm.frm_state.value.trim() == "") 	{errmsg+= "\tBuilding state is required\n";}		
			} 

        if (errmsg!="") {
            alert ("<?php print gettext('Please correct the following and re-submit');?>:\n\n" + errmsg);

            return false;
            }
        else {
            theForm.submit();
            }
        }				// end function validate(theForm)
	function do_reset() {
		document.c.reset();
		$('but1').style.opacity = $('but2').style.opacity = $('row1').style.opacity = $('row2').style.opacity = $('row3').style.opacity = $('row4').style.opacity = $('row5').style.opacity = 0.2;		
		document.c.apply_to_c.checked = document.c.apply_to_b.checked = false;	
		}		// end function

	function fn_check_borc(inval) {
		$('but1').style.opacity = $('but2').style.opacity = $('row1').style.opacity = $('row2').style.opacity = $('row3').style.opacity = $('row4').style.opacity = $('row5').style.opacity = 1.0;		

		document.c.frm_apply_to.value = inval;				// set as 'db apply' value	
		if ( (inval == 'city') && (!($('ID3').readOnly)) ) {
			$('ID3').value = $('ID4').value = $('ID5').value = '';			
			}
		var opacity =  (inval=='city')?  0.2 : 1.0;
		$('row4').style.opacity = $('row5').style.opacity = opacity;		
		$('ID3').readOnly = $('ID4').readOnly = $('ID5').readOnly = (inval=='city');		

		}		// end function fn_check_borc()


</SCRIPT>
        <FORM NAME="c" METHOD="post" ACTION="<?php print $_SERVER['PHP_SELF']; ?>" />
        <INPUT TYPE="hidden" NAME="tablename" 	VALUE="<?php print $tablename;?>"/>
        <INPUT TYPE="hidden" NAME="indexname" 	VALUE="id"/>
        <INPUT TYPE="hidden" NAME="sortby" 		VALUE="id"/>
        <INPUT TYPE="hidden" NAME="sortdir"		VALUE=0 />
        <INPUT TYPE="hidden" NAME="func" 		VALUE="pc"/>
        <INPUT TYPE="hidden" NAME="srch_str"  	VALUE=""/> <!-- 9/12/10 -->
		<INPUT TYPE="hidden" NAME="frm_apply_to" VALUE="city" /> <!-- db update value; initially the default; revised onclick -->

        <TABLE BORDER=0 ID='outer' ALIGN= 'center'>
		<TR><TD COLSPAN=2 ALIGN='center'><FONT CLASS="header"><i>Add New City/Building Entry</i></FONT><BR /><BR /></TD></TR>
        <TR><TD>
            <TABLE BORDER="0" ALIGN="center">
            <TR><TD>&nbsp;</TD></TR>
			
			<TR VALIGN="baseline" CLASS="odd" ><TD ALIGN="right" CLASS="header" >Select:</TD>
				<TD ALIGN = 'center' VALIGN='baseline' CLASS="header">&nbsp;&nbsp;<B>city &nbsp;&raquo;&nbsp;
					<INPUT TYPE='radio' onclick = "fn_check_borc(this.value);" NAME="apply_to_c" VALUE= "city" STYLE='vertical-align:baseline;'/>
					<span style = "margin-left:50px;">&nbsp;bldg&nbsp;&raquo;&nbsp;
					<INPUT TYPE='radio' onclick = "fn_check_borc(this.value);" NAME="apply_to_b" VALUE= "bldg" STYLE='vertical-align:baseline; '/></span></B></TD>
				</TR>
			<TR><TD><br /></TD></TR>

			<TR id = 'row1' CLASS = "even" style = "opacity:0.2" ><TD></TD>
                <TD COLSPAN=3>&nbsp;&nbsp;<?php print get_text("City");?>:&nbsp;<INPUT MAXLENGTH="24" SIZE="24" TYPE="text" NAME="the_city" VALUE="" />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php print get_text("St");?>:&nbsp;<INPUT MAXLENGTH="4" SIZE="2" TYPE="text" NAME="the_st" VALUE="" /><button type="button" style = "margin-left:40px;" onClick="addrlkup(this.form)">
				<img src="./markers/glasses.png" alt="Lookup location." />&nbsp;&nbsp;Lookup</TD></TR>

			<TR><TD>&nbsp;</TD></TR>
			<TR><TD CLASS="td_label" colspan=2 align=center><SPAN ID='do_sv' onClick = "sv_win(document.c)" style='display:block'><u>Street view</U></SPAN></TD></TR>
			<TR><TD>&nbsp;</TD></TR>

			<TR id = 'row2' VALIGN="baseline" CLASS="even" style = "opacity:0.2" ><TD CLASS="td_label" ALIGN="right">Name:</TD>
				<TD><INPUT ID="ID1" CLASS="dirty" MAXLENGTH="64" SIZE="64" type="text" NAME="frm_name" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD></TR>
			<TR><TD>&nbsp;</TD></TR>
<!-- new -->
			<TR id = 'row3' VALIGN="baseline" CLASS="even" ID = 'brow4' style = "opacity:0.2"  >
				<TD CLASS="td_label" ALIGN="right">Information:</TD>
				<TD><TEXTAREA ID='ID6' CLASS='dirty' NAME='frm_information' COLS='64' ROWS = '1' onFocus="JSfnChangeClass(this.id, 'dirty');" STYLE='vertical-align:text-top;'></TEXTAREA> </TD>
				</TR>

			<TR><TD><br /></TD></TR>

			<TR ID = 'row4' VALIGN="baseline" CLASS="even" style = "opacity:0.2">
				<TD CLASS="td_label" ALIGN="right" >Bldg addr:</TD>
				<TD><INPUT ID="ID3" readOnly CLASS="dirty" MAXLENGTH="96" SIZE="64" type="text" NAME="frm_street" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> </TD>
				</TR>

			<TR ID = 'row5' VALIGN="baseline" CLASS="odd"  style = "opacity:0.2" >
				<TD CLASS="td_label" ALIGN="right">Bldg city:</TD>
				<TD><INPUT ID="ID4" readOnly CLASS="dirty" MAXLENGTH="32" SIZE="32" type="text" NAME="frm_city" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"> 
				<span CLASS="td_label" style = "margin-left:20px;"> St: <INPUT ID="ID5" readOnly CLASS="dirty" MAXLENGTH="4" SIZE="4" type="text" NAME="frm_state" VALUE="" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)" ></span>
				</TD>
				</TR>

<!-- /new -->
		<TR><TD>&nbsp;</TD></TR>

		<TR ID = 'row6' VALIGN="baseline" CLASS="odd" STYLE = "opacity:.2;"><TD CLASS="td_label" ALIGN="right">Lat:</TD>
			<TD><INPUT ID="ID2" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lat" VALUE="<?php echo get_variable('def_lat'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/>
			<SPAN CLASS="td_label" STYLE ="margin-left:20px;">Lon:&nbsp;&nbsp;&nbsp;
			<INPUT ID="ID3" MAXLENGTH=12 SIZE=12 TYPE=text NAME="frm_lon" VALUE="<?php echo get_variable('def_lng'); ?>" onFocus="JSfnChangeClass(this.id, 'dirty');" onChange = "this.value=JSfnTrim(this.value)"/></SPAN>
             </TD></TR>
                <TR><TD COLSPAN="99" ALIGN="center">
            <BR /><BR />
      <INPUT TYPE="button"	VALUE="<?php print gettext('Cancel');?>" onClick = "Javascript: document.retform.func.value='r';document.retform.submit();"/>&nbsp;&nbsp;&nbsp;&nbsp;
      <INPUT ID = 'but1' style = "opacity:0.2"  TYPE="button"	VALUE="Reset" onclick = "do_reset();"/>&nbsp;&nbsp;&nbsp;&nbsp;
      <INPUT ID = 'but2' style = "opacity:0.2"  TYPE="button" VALUE="Submit" NAME="sub_but" onclick="validate(this.form)"/>	

            </TD></TR>
            </FORM>
            </TD></TR></TABLE> <!-- /inner -->
        </TD><TD>
            <div id="map_canvas" style="width:<?php print get_variable('map_width');?>px; height:<?php print get_variable('map_height');?>px; margin-left:20px;"></div>
        </TD></TR>
        </TABLE>
<SCRIPT>
initialize();
</SCRIPT>
<?php
