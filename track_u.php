<?php
/**
 * @package track_u.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */
/*
original, converted from tracks.php
10/4/08	added auto-refresh
10/4/08	corrected to include all point into bounding box
10/4/08	added direction icons
3/18/09 'aprs_poll' to 'auto_poll'
4/8/09 correction to icon names, 'small text' added
7/29/09	Changed titlebar to show Name and Handle
8/2/09 Added code to get maptype variable and switch to change default maptype based on variable setting
7/16/10 detailmap.setCenter correction
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/13/10 map.setUIToDefault();
8/19/10 alternative source of lookup argument
3/15/11 changed stylesheet.php to stylesheet.php
*/

@session_start();
require_once($_SESSION['fip']);		//7/28/10
//do_login(basename(__FILE__));		// in a window

if (array_key_exists('unit_id', $_GET)) {	// 8/19/10
    $query = "SELECT  * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id` = {$_GET['unit_id']} LIMIT 1;";	//	8/19/10
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    $row = stripslashes_deep(mysql_fetch_array($result)) ;
    $source = $row['callsign'];
    }
else {
    extract($_GET);
    }

$api_key = get_variable('gmaps_api_key');

/**
 * list_tracks
 * Insert description here
 *
 * @param $addon
 * @param $start
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function list_tracks($addon = '', $start) {
    global $source, $evenodd;

?>
<SCRIPT>
    var direcs=new Array("north.png","north_east.png","east.png","south_east.png","south.png","south_west.png","west.png","north_west.png", "north.png");	// 4/8/09
    var colors = new Array ('odd', 'even');
	var gmarkers = [];
	var infoTabs = [];
	var which;
	var i = 0;			// sidebar/icon index, track point index
	var points = false;								// none
	
	function $() {									// 1/21/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
                    }
		return elements;
                    }

//								(point, html, node_type, heading)
/**
 *
 */
    function create_track_Marker(point, html, node_type, heading) {
        switch (node_type) {
            case 1:				// start node
				var marker = new google.maps.Marker({position: point, map: map, icon: starticon});
				google.maps.event.addListener(marker, "click", function() {		// 1811 - here for both side bar and icon click
					try  {open_iw.close()} catch (e) {;}
					map.setCenter(point, 8);
					var infowindow = new google.maps.InfoWindow({ content: html, maxWidth: 400});	 
					infowindow.open(map, marker);
					open_iw = infowindow;
					which = id;
                    });
                break;
            case 0:				// end node
				var marker = new google.maps.Marker({position: point, map: map, icon: endicon});
				google.maps.event.addListener(marker, "click", function() {		// 1811 - here for both side bar and icon click
					try  {open_iw.close()} catch (e) {;}
					map.setCenter(point, 8);
					var infowindow = new google.maps.InfoWindow({ content: html, maxWidth: 400});	 
					infowindow.open(map, marker);
					open_iw = infowindow;
					which = id;
                    });
                break;
            default : 			// in between nodes
				var iconurl = "./markers/" + direcs[heading];
				var infoicon = new google.maps.MarkerImage(iconurl);
				infoicon.iconSize = new google.maps.Size(15, 15);
				infoicon.iconAnchor = new google.maps.Point(4, 4);
				var marker = new google.maps.Marker({position: point, map: map, icon: infoicon});
				google.maps.event.addListener(marker, "click", function() {		// 1811 - here for both side bar and icon click
					try  {open_iw.close()} catch (e) {;}
					map.setCenter(point, 8);
					var infowindow = new google.maps.InfoWindow({ content: html, maxWidth: 400});	 
					infowindow.open(map, marker);
					open_iw = infowindow;
					which = id;
                    });
                }
		gmarkers[id] = marker;									// marker to array for side_bar click function
		infoTabs[id] = html;									// tabs to array
        return marker;
        }

	function do_sidebar (sidebar, id) {
		side_bar_html += "<TR CLASS='" + colors[(id)%2] +"'>";
		side_bar_html += "<TD CLASS='td_label'>" + sidebar +"</TD></TR>\n";
		}

	function myclick(id) {					// Responds to sidebar click, then triggers listener above -  note [i]
		google.maps.event.trigger(gmarkers[id], "click");
		}

	var starticon = new google.maps.MarkerImage("./markers/start.png");
	starticon.iconSize = new google.maps.Size(16, 16);
	starticon.iconAnchor = new google.maps.Point(8, 8);
	starticon.infoWindowAnchor = new google.maps.Point(9, 2);

	var endicon = new google.maps.MarkerImage("./markers/end.png");
	endicon.iconSize = new google.maps.Size(16, 16);
	endicon.iconAnchor = new google.maps.Point(8, 8);
	endicon.infoWindowAnchor = new google.maps.Point(9, 2);

    var map;
    var side_bar_html = "<TABLE border=0 CLASS='sidebar' ID='tbl_responders'>";
    side_bar_html +="<TR><TD ALIGN='center' COLSPAN=99><?php print gettext('Mouseover for details');?></TD></TR>";

	var myLatlng = new google.maps.LatLng(<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>);	
	var mapOptions = {
		zoom: <?php print get_variable('def_zoom');?>,
		center: myLatlng,
		panControl: true,
	    zoomControl: true,
	    scaleControl: true
		}	

	map = new google.maps.Map($('map_canvas'), mapOptions);				// 
<?php
$maptype = get_variable('maptype');	// 08/02/09

    switch ($maptype) {
        case "1":
        break;

        case "2":?>
        map.setMapType(G_SATELLITE_MAP);<?php
        break;

        case "3":?>
        map.setMapType(G_PHYSICAL_MAP);<?php
        break;

        case "4":?>
        map.setMapType(G_HYBRID_MAP);<?php
        break;

        default:
        print "ERROR in " . basename(__FILE__) . " " . __LINE__ . "<BR />";
    }
?>

	var bounds = new google.maps.LatLngBounds();		// Initialize bounds for the map
	var listIcon = new google.maps.MarkerImage("./markers/yellow.png");
    listIcon.shadow = "./markers/sm_shadow.png";
	listIcon.iconSize = new google.maps.Size(20, 34);
	listIcon.shadowSize = new google.maps.Size(37, 34);
	listIcon.iconAnchor = new google.maps.Point(8, 28);
	listIcon.infoWindowAnchor = new google.maps.Point(9, 2);
	listIcon.infoShadowAnchor = new google.maps.Point(18, 25);
	
	var newIcon = new google.maps.MarkerImage("./markers/white.png");
	newIcon.shadow = "./markers/sm_shadow.png";
	newIcon.iconSize = new google.maps.Size(20, 34);
	newIcon.shadowSize = new google.maps.Size(37, 34);
	newIcon.iconAnchor = new google.maps.Point(8, 28);
	newIcon.infoWindowAnchor = new google.maps.Point(9, 2);
	newIcon.infoShadowAnchor = new google.maps.Point(18, 25);

	google.maps.event.addListener(map, "infowindowclose", function() {		// re-center after  move/zoom
		var zoomfactor = -2;	//	3/15/11
		var newzoom = currzoom + zoomfactor;
		base_zoom = map.getZoom();
		if (currzoom > (base_zoom - zoomfactor)) {	//	3/15/11
			map.setCenter(center, newzoom);
		} else {
			map.setCenter(center, currzoom);
		}
		gmarkers[which].setMap(map);		
        });
<?php

//	$bulls = array(0 =>"",1 =>"red",2 =>"green",3 =>"white",4 =>"black");
    $toedit = "";

	$eols = array ("\r\n", "\n", "\r");		// all flavors of eol
	
    $query = "SELECT DISTINCT `source`, `latitude`, `longitude` ,`course` ,`speed` ,`altitude` ,`closest_city` ,
        `status` , `packet_date`,
        UNIX_TIMESTAMP(updated) AS `updated`
        FROM `$GLOBALS[mysql_prefix]tracks`
        WHERE `source` = '" .$source . "'
        ORDER BY `packet_date`";	//	6/16/08
    $result_tr = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
    $sidebar_line = "<TABLE border=0>\n";
    if (mysql_affected_rows()> 1 ) {
?>
        var j=0;				// point counter this unit
        var ender = <?php print mysql_affected_rows(); ?> ;
<?php
        $last = $day = "";
		$i=1;

        while ($row_tr = stripslashes_deep(mysql_fetch_array($result_tr))) {
            if (substr($row_tr['packet_date'] ,  0,  10) != $day) {
                $day = substr ($row_tr['packet_date'] ,  0,  10);
                $sidebar_line .="<TR CLASS='" . $evenodd[$i%2] . "'><TD COLSPAN=99><U>" . $day . "</U></TD></TR>\n";
				} else {
				$sidebar_line = "<TABLE border=0>\n";			
				$sidebar_line .="<TR CLASS='" . $evenodd[$i%2] . "' onClick='myclick(" . $i . ");'>";		// 4/8/09
            $sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['packet_date'] . "'>&nbsp;" .  	substr ($row_tr['packet_date'] , 11, 5) ." </TD>\n";
            $sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['latitude']. ", ". $row_tr['longitude'] . "'>&nbsp;" . shorten($row_tr['latitude'], 8) ."</TD>\n";
            $sidebar_line .= "<TD CLASS = 'text_small'>&nbsp;" . $row_tr['speed']."@" . $row_tr['course'] . "</TD>\n";
            $sidebar_line .= "<TD CLASS = 'text_small' TITLE='" . $row_tr['closest_city'] . "'>&nbsp;" .  	shorten($row_tr['closest_city'], 16) ."</TD>\n";
            $sidebar_line .="</TR>\n";
				$sidebar_line .="</TABLE>\n";
				
?>
            j++;
				do_sidebar ("<?php print str_replace($eols, "", $sidebar_line); ?>", j);		// as single string
				var point = new google.maps.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>);
            var html = "<b><?php print $row_tr['source'];?></b><br /><br /><?php print format_date($row_tr['updated']);?>";
            var heading = Math.round(<?php print intval($row_tr['course']);?>/45);		// 10/4/08

            if (j== ender) {node_type=0;}														// signifies last node 10/4/08
            else 			{node_type=j;};														// other than last
				var marker = create_track_Marker(point, html, node_type, heading, j);
				marker.setMap(map);
				bounds.extend(new google.maps.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>));	// 10/4/08  all points to bounding box
<?php
            if (!empty($last)) {
?>
					var polyline = new google.maps.Polygon([
						new google.maps.LatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>),		// prior point
						new google.maps.LatLng(<?php print $row_tr['latitude'];?>, <?php print $row_tr['longitude'];?>)	// current point
                    ], "#FF0000", 2);
					polyline.setMap(map);
                points++;
<?php
                }		// end if (!empty($last))
            $last = $row_tr;										// either way
            $i++;
				}
            }		// end while ($row_tr...)

            $mode = ($last['speed'] == 0)? 1: 2 ;
            if ($last['speed'] >= 50) { $mode = 3;}
?>
			var point = new google.maps.LatLng(<?php print $last['latitude'];?>, <?php print $last['longitude'];?>);	// mobile position
<?php
            }				// end (mysql_affected_rows()> 1 )


?>
    if (!points) {		// any?
		map.setCenter(new google.maps.LatLng(<?php echo get_variable('def_lat'); ?>, <?php echo get_variable('def_lng'); ?>), <?php echo get_variable('def_zoom'); ?>);
        }
    else {
		map.fitBounds(bounds);
        }
<?php
    if (!empty($addon)) {
        print "\n\tside_bar_html +=\"" . $addon . "\"\n";
        }
?>
    side_bar_html +="</TABLE>\n";
//	alert(side_bar_html);
    document.getElementById("side_bar").innerHTML += side_bar_html;	// append the assembled side_bar_html contents to the side_bar div

<?php
    do_kml();		// generate KML JS - added 5/23/08
?>
	</SCRIPT>
<?php
    }				// end function list_tracks() ===========================================================

$interval = intval(get_variable('auto_poll'));
$refresh = ($interval>0)? "\t<META HTTP-EQUIV='REFRESH' CONTENT='" . intval($interval*60) . "'>": "";	//10/4/08

$query_callsign	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `callsign`='{$source}'";				// 7/29/09
$result_callsign = mysql_query($query_callsign) or do_error($query_callsign, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);		// 7/29/09
$row_callsign	= mysql_fetch_assoc($result_callsign);				// 7/29/09
$handle = ($row_callsign['handle']);				// 7/29/09
$name = ($row_callsign['name']);				// 7/29/09
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <HEAD><TITLE>Tickets - <?php print $name; ?> : <?php print $handle; ?> <?php print gettext('Tracks');?></TITLE>

<?php print $refresh; ?>	<!-- 10/4/08 -->

    <LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>	<!-- 3/15/11 -->
<?php
	$key_str = (strlen($api_key) == 39)?  "key={$api_key}&" : "";
	if((array_key_exists('HTTPS', $_SERVER)) && ($_SERVER['HTTPS'] == 'on')) {
		$gmaps_url =  "https://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
		} else {
		$gmaps_url =  "http://maps.google.com/maps/api/js?" . $key_str . "libraries=geometry,weather&sensor=false";
		}
?>
	<SCRIPT TYPE="text/javascript" src="<?php print $gmaps_url;?>"></SCRIPT>
	<SCRIPT>
    function ck_frames() {		// ck_frames()
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
        }		// end function ck_frames()
    </SCRIPT>
    </HEAD>
	<BODY>
	<A NAME='top'>
		<TABLE ID='outer'><TR CLASS='even'><TD ALIGN='center' colspan=2><B><FONT SIZE='+1'>Mobile Unit <?php print $handle;?> : <?php print $name;?> - Tracks</FONT></B></TD></TR><TR><TD>
            <DIV ID='side_bar'></DIV>
            </TD><TD ALIGN='center'>
			<DIV ID='map_canvas' style='width: <?php print get_variable('map_width');?>px; height: <?php print get_variable('map_height');?>px; border-style: outset'></DIV>
            <BR><BR>
            <CENTER><SPAN onClick = 'self.close();'><B><U><?php print gettext('Close');?></U></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="javascript:location.reload(true);"><B><U><?php print gettext('Refresh');?></U>
            </TD></TR>
            </TABLE><!-- end outer -->

            <FORM NAME='view_form' METHOD='get' ACTION='units.php'>
            <INPUT TYPE='hidden' NAME='func' VALUE='responder' />
            <INPUT TYPE='hidden' NAME='view' VALUE='true' />
            <INPUT TYPE='hidden' NAME='id' VALUE='' />
            </FORM>

            <FORM NAME='to_add_form' METHOD='get' ACTION='units.php'>
            <INPUT TYPE='hidden' NAME='func' VALUE='responder' />
            <INPUT TYPE='hidden' NAME='add' VALUE='true' />
            </FORM>

            <FORM NAME='can_Form' METHOD="post" ACTION = "units.php?func=responder"></FORM>
                        <!-- END RESPONDER LIST and ADD -->
<?php
        print list_tracks("", 0);
        $alt_urlstr =  "./incs/alt_graph.php?p1=" . urlencode($source) ;		// 7/18/08  Call sign for altitude graph
?>

<BR /><HR ALIGN='center' SIZE=1 COLOR='blue' WIDTH='75%'><BR />
<CENTER><img src="<?php print $alt_urlstr;?>" border=0 />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br><br>
</CENTER><A HREF='#top'><U><?php print gettext('to top');?></U></A>
</BODY></HTML>
