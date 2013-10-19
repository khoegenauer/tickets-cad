<?php
/*
9/10/13 - New file - popup window to vie details of Location warnings
*/
error_reporting(E_ALL);

@session_start();
require_once($_SESSION['fip']);
do_login(basename(__FILE__));
require_once($_SESSION['fmp']);
$api_key = get_variable('gmaps_api_key');		// empty($_GET)
$in_win = array_key_exists ("mode", $_GET);		// in

if ((!empty($_GET))&& ((isset($_GET['logout'])) && ($_GET['logout'] == 'true'))) {
	do_logout();
	exit();
	}
else {
	do_login(basename(__FILE__));
	}
if ($istest) {
	print "GET<BR/>\n";
	if (!empty($_GET)) {
		dump ($_GET);
		}
	print "POST<BR/>\n";
	if (!empty($_POST)) {
		dump ($_POST);
		}
	}

$id =	(array_key_exists('id', ($_GET)))?	$_GET['id']  :	NULL;

$result = mysql_query("SELECT * FROM `$GLOBALS[mysql_prefix]warnings` WHERE id='$id'");
$row = mysql_fetch_assoc($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Location Warning Details</TITLE>
	<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">
	<STYLE type="text/css">
	.hover 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; background-color: #DEE3E7; font-weight: bolder;}
	.plain 	{ text-align: center; margin-left: 4px; float: none; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
  				  padding: 4px 0.5em;text-decoration: none; background-color: #EFEFEF; font-weight: bolder;}
	.wrap_data { width: 200px; background-color: inherit;	font-size: 12px; color: #000000; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
	.wrap_label { width: 100px; background-color: #707070; font-size: 12px; color: #FFFFFF; font-weight: bold; font-style: normal; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
	.tab_row { border: 1px solid #CECECE; width: 300px; }
  	</STYLE>	
	<SCRIPT TYPE="text/javascript" src="http://maps.google.com/maps/api/js?<?php echo $api_key;?>&libraries=geometry&sensor=false"></SCRIPT>
	<SCRIPT TYPE="text/javascript" src="./js/elabel_v3.js"></SCRIPT>
	<SCRIPT TYPE="text/javascript" SRC="./js/gmaps_v3_init.js"></script>
	<SCRIPT SRC="./js/graticule_V3.js" type="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/usng.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC='./js/jscoord.js' TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/lat_lng.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/osgb.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/misc_function.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT SRC="./js/suggest.js" TYPE="text/javascript"></SCRIPT>
	<SCRIPT>
	function ck_frames() {		// onLoad = "ck_frames()"
		}		// end function ck_frames()

	function $() {
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

	function do_hover (the_id) {
		CngClass(the_id, 'hover');
		return true;
		}

	function do_plain (the_id) {
		CngClass(the_id, 'plain');
		return true;
		}

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	var baseIcon;
	var cross;
	var icon_file = "./markers/sm_red.png";
	var map_obj = null;				// the map object - note GLOBAL
	var myMarker;					// the marker object
	
	function do_marker(lat, lng, zoom) {
		var point = new google.maps.LatLng(lat, lng);
		myMarker = new google.maps.Marker({position: point, map: map_obj, icon: icon_file});
		map_obj.setCenter(point, zoom);
		myMarker.setMap(map_obj);
		}

	function load(the_lat, the_lng, the_zoom) {
		function call_back (in_obj){				// callback function - from gmaps_v3_init()
			}			// end callback function

		map_obj = gmaps_v3_init(call_back, 'map_canvas', 
			<?php echo get_variable('def_lat');?>, 
			<?php echo get_variable('def_lng');?>, 
			the_zoom, 
			icon_file, 
			<?php echo get_variable('maptype');?>, 
			false);	

		do_marker(the_lat, the_lng, the_zoom);
		}			// end function load()
	</SCRIPT>
</HEAD>
<BODY onLoad = 'load(<?php print $row['lat'];?>, <?php print $row['lng'];?>, 15); ck_frames();'>
<TABLE ALIGN = 'center'><TR><TD>

<CENTER><BR /><BR clear=all/><BR /></CENTER>
<TABLE style='width: 680px;'>
	<TR>
		<TD style='width: 300px;'>
			<TABLE style='width: 300px; border: 1px solid #000000;'>
				<TR class='tab_row'>
					<TD class='wrap_label'>Title</TD><TD class='wrap_data'><?php print $row['title'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Street</TD><TD class='wrap_data'><?php print $row['street'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>City</TD><TD class='wrap_data'><?php print $row['city'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>State</TD><TD class='wrap_data'><?php print $row['state'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Latitude</TD><TD class='wrap_data'><?php print $row['lat'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Longitude</TD><TD class='wrap_data'><?php print $row['lng'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Description</TD><TD class='wrap_data'><?php print $row['description'];?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Reported By</TD><TD class='wrap_data'><?php print get_owner($row['_by']);?></TD>
				</TR>
				<TR class='tab_row'>
					<TD class='wrap_label'>Date Reported</TD><TD class='wrap_data'><?php print $row['_on'];?></TD>
				</TR>
			</TABLE>
		</TD>
		<TD style='width: 380px;'>
			<DIV id='map_canvas' style='z-index:1; width: 380px; height: 380px'></DIV>
		</TD>
	</TR>
</TABLE>
<BR /><BR /><BR />
<CENTER><SPAN id='fin_button' class='plain' style='text-align: center;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'window.close();'>Finished</SPAN></CENTER>
<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>'>
</FORM>
<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
<INPUT TYPE='hidden' NAME='status' VALUE=''>
</FORM>
</TD></TR></TABLE>
</BODY></HTML>
