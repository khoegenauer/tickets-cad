<?php
/*
4/18/2013 - initial release
6/23/2013 - roadinfo added
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);

@session_start();

if (empty($_SESSION)) {
	header("Location: index.php");
	}
$me = $_SESSION['user_unit_id'] ;		// possibly empty

require_once('../incs/functions.inc.php');
require_once('./incs/sp_functions.inc.php');

$GLOBALS['TABLE_TICKET'] 	= 0;	
$GLOBALS['TABLE_RESPONDER'] = 1;
$GLOBALS['TABLE_FACILITY']  = 2;
$GLOBALS['TABLE_ASSIGN']   	= 3;
$GLOBALS['TABLE_ROAD']   	= 4;

//		WARNING - WARNING - WARNING - match any changes to JS definitions below - WARNING - WARNING - WARNING - WARNING 
$layers = 		array($GLOBALS['TABLE_TICKET']  => "incidents", 	$GLOBALS['TABLE_RESPONDER'] => "units", 	$GLOBALS['TABLE_FACILITY'] => "facilities", 	$GLOBALS['TABLE_ROAD'] => "roadinfo");
$icons = 		array($GLOBALS['TABLE_TICKET']  => "tickIcon", 		$GLOBALS['TABLE_RESPONDER'] => "unitIcon", 	$GLOBALS['TABLE_FACILITY'] => "facIcon", 		$GLOBALS['TABLE_ROAD'] => "roadIcon");

?>
<!DOCTYPE html>
<html>
<head>
	<title>TicketsSP - <?php echo basename(__FILE__);?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="./dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href="./dist/leaflet.ie.css" /><![endif]-->
	<script src="./dist/leaflet.js"></script>
	<script src="./dist/leaflet.label.js></script>	<!-- 6/27/2013 -->
	<script src="./js/Control.FullScreen.js"></script>
	<script src="./js/misc.js" type="text/javascript"></script>
<?php
if (intval(get_variable('broadcast'))==1) {	
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
</head>
<body>
<?php
	require_once('incs/header.php');	
?>
<!--
	<div style = 'width:100%;text-align:center'>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_red.png'/>Incidents</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_black.png'/>Responders</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_yellow.png'/>Facilities</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_white.png'/>Road conditions</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/crosshair.png'/>me</span>
	</div>
-->
	<center><div id="map" style="margin-top:40px; width: <?php echo ($_SESSION['scr_width']-20);?>px; height: <?php echo ($_SESSION['scr_height'] - 260);?>px; "></div>	<!-- % fails here -->

	<script>
		var markers_work;
<?php	
/*

	    markers_work = L.marker([48.857289, -95.449305], {icon: unitIcon}).addTo(units);
		markers_work.addEventListener('click', function() { on_Click (9, 1, 16);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
		my_bounds.extend(markers_work.getLatLng());											// to bounding box
		
			L.marker([-37.785, 175.263])
			    .bindLabel('A sweet static label!', { noHide: true })
			    .addTo(map)
			    .showLabel();

*/

	function do_marker ($lat, $lng, $layer, $icon, $table, $id, $sb_index) {
?>	
	    markers_work = L.marker([<?php echo $lat;?>, <?php echo $lng;?>], {icon: <?php echo $icon;?>}).addTo(<?php echo $layer;?>);
		markers_work.addEventListener('click', function() { on_Click (<?php echo $sb_index;?>, <?php echo $table;?>, <?php echo $id;?>);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
		my_bounds.extend(markers_work.getLatLng());											// to bounding box
<?php
	}		// end function do_marker()
?>
		var markers_ary = [];
		var my_bounds = new L.LatLngBounds([<?php echo  get_variable('def_lat');?>, <?php echo get_variable('def_lng');?>], [<?php echo get_variable('def_lat');?>, <?php echo get_variable('def_lng');?>]);		
		var id_array;

		DomReady.ready(function() {			//set initial bounds at map center			
			id_array = document.navForm.id_str.value.split(",");
			});		
	
		var baseIcon = L.Icon.extend({options: {shadowUrl: './images/sm_shadow.png',
			iconSize: [12, 20],	shadowSize: [22, 20], iconAnchor: [0, 0],	shadowAnchor: [0, 0], popupAnchor: [6, -5]
			}
			});

		var theLayers = new Object();
		theLayers[<?php echo $GLOBALS['TABLE_TICKET'];?>]	=  ["incidents", 	"tickIcon",	<?php echo $GLOBALS['TABLE_TICKET'];?>];		// layer, icon, db table
		theLayers[<?php echo $GLOBALS['TABLE_RESPONDER'];?>] =  ["units", 		"unitIcon",	<?php echo $GLOBALS['TABLE_RESPONDER'];?>];
		theLayers[<?php echo $GLOBALS['TABLE_FACILITY'];?>]	=  	["facilities", 	"facIcon", 	<?php echo $GLOBALS['TABLE_FACILITY'];?>];
		theLayers[<?php echo $GLOBALS['TABLE_ROAD'];?>]	=  		["roadinfo", 	"roadIcon", <?php echo $GLOBALS['TABLE_ROAD'];?>];

		var tickIcon = 		new baseIcon({iconUrl: 'images/sm_red.png'}),
		unitIcon = 			new baseIcon({iconUrl: 'images/sm_black.png'}),
		facIcon = 			new baseIcon({iconUrl: 'images/sm_yellow.png'}),
		roadIcon = 			new baseIcon({iconUrl: 'images/sm_white.png'}),
		
		pos_unknown_icon =	new baseIcon({iconUrl: 'images/question1.png'}),
		meIcon =			new baseIcon({iconUrl: 'images/crosshair.png', iconSize: [32, 32], iconAnchor: [16, 16]});		//	crosshair_128 reticlebm7

		function on_Click (array_id, table_id, record_id) {		// 1: here on marker click - 2: issue request - 3: IW data returned via callback
			function iw_callback(req) {
				markers_ary[array_id].bindPopup(req.responseText).openPopup();		// - array_id?
				}		// end function my_callback()
			var params = "table_id="+ table_id + "&record_id=" +record_id;		// 
			var url = "./ajax/return_iw.php";
			sendRequest(url,iw_callback, params);		//  issue AJAX request for infowindow contents
			}		// end function on Click ()		
		
		var incidents = new L.LayerGroup();
		var units = new L.LayerGroup();
		var facilities = new L.LayerGroup();
		var roadinfo = new L.LayerGroup();

<?php	
		$side_bar_index = 0;		// used only with sidebar page
		
//							 generate UNITS  data

		$query = "SELECT `r`.`id` AS `unit_id`, `icon_str`, `handle`, `lat`, `lng`, `type`, `un_status_id`, `s`.`dispatch`,
					SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
					`r`.`description` AS `unit_descr`, 
					`t`.`description` AS `unit_type`, 
					`s`.`description` AS `unit_status`
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 	
				ORDER BY `handle` ASC
				";			

		$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_unit))) {		
			if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
				do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_RESPONDER']], $icons[$GLOBALS['TABLE_RESPONDER']], $GLOBALS['TABLE_RESPONDER'], $in_row['unit_id'], $side_bar_index);
				}
			else {				// invalid position data
				do_marker (get_variable('def_lat'), get_variable('def_lng'), 	$layers[$GLOBALS['TABLE_RESPONDER']], "pos_unknown_icon", $GLOBALS['TABLE_RESPONDER'], $in_row['unit_id'], $side_bar_index);			
				}
			$side_bar_index++;
			}
//							 generate FACILITIES data

		$query = "SELECT `f`.`id` AS `facy_id`, `icon_str`, `handle`, `type`, `lat`, `lng`, `status_id`, 
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
				`f`.`description` AS `facy_descr`, 
				`t`.`description` AS `facy_type`, 
				`s`.`description` AS `facy_status`
				
				FROM `$GLOBALS[mysql_prefix]facilities` `f` 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
				ORDER BY `handle` ASC 
				";			
		$result_facy = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_facy))) {			

			if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
				do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_FACILITY']], $icons[$GLOBALS['TABLE_FACILITY']], $GLOBALS['TABLE_FACILITY'], $in_row['facy_id'], $side_bar_index);
				}
			else {				// invalid position data
				do_marker (get_variable('def_lat'), get_variable('def_lng'), 	$layers[$GLOBALS['TABLE_FACILITY']], "pos_unknown_icon", $GLOBALS['TABLE_FACILITY'], $in_row['facy_id'], $side_bar_index);			
				}

			$side_bar_index++;
			}
//							 generate INCIDENT data - sidebar data unused here

		$query = "SELECT `t`.`id`  AS `tick_id`,
				CONCAT_WS(' ',`street`,`city`) AS `addr`,
				 `status`, `lat`, `lng`, `scope` , `t`.`description` AS `description` ,
				 `severity` , `comments` ,
				(SELECT COUNT( * )
					FROM `$GLOBALS[mysql_prefix]assigns`
					WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`
					AND `clear` IS NULL
					OR DATE_FORMAT( `clear` , '%y' ) = '00')
					) AS `u`, (
					SELECT COUNT( * )
						FROM `$GLOBALS[mysql_prefix]action`
						WHERE `$GLOBALS[mysql_prefix]action`.`ticket_id` = `t`.`id`
						) AS `a`,(						
					SELECT COUNT( * )
						FROM `$GLOBALS[mysql_prefix]patient`
						WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id`
						) AS `p`,
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `as of`				
				FROM `$GLOBALS[mysql_prefix]ticket` `t` 
				WHERE (`t`.`status`='{$GLOBALS['STATUS_OPEN']}')  
				ORDER BY `severity` DESC, `problemstart` ASC 
				";
	
			$result_tick = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
/*
			L.marker([-37.785, 175.263])
			    .bindLabel('A sweet static label!', { noHide: true })
			    .addTo(map)
			    .showLabel();
*/
				if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
//					dump(__LINE__);
					do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_TICKET']], $icons[$GLOBALS['TABLE_TICKET']], $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);
					}
				else {				// invalid position data
//					dump(__LINE__);
					do_marker (get_variable('def_lat'), get_variable('def_lng'), 	$layers[$GLOBALS['TABLE_TICKET']], "pos_unknown_icon", $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);			
					}
	
				$side_bar_index++;
				}

//							   generate ROADINFO data 
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo`";
 			$result_road = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
 			$the_icon = $icons[$GLOBALS['TABLE_ROAD']];		
 			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_road))) {			
				if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
					do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_ROAD']], $icons[$GLOBALS['TABLE_ROAD']], $GLOBALS['TABLE_ROAD'], $in_row['id'], $side_bar_index);
					}
				else {				// invalid position data
					do_marker (get_variable('def_lat'), get_variable('def_lng'), 	$layers[$GLOBALS['TABLE_ROAD']], "pos_unknown_icon", $GLOBALS['TABLE_ROAD'], $in_row['id'], $side_bar_index);			
					}

 				$side_bar_index++;
 				}
?>
		var my_Path = "http://127.0.0.1/_osm/";
		var in_local_bool = false;
		var osmUrl = (in_local_bool)?
			"../_osm/tiles/{z}/{x}/{y}.png":
			"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

	    var cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade',
			cmUrl = osmUrl;

	    var minimal   = L.tileLayer(cmUrl, {attribution: cmAttr}),
		    midnight  = L.tileLayer(cmUrl, {attribution: cmAttr}),
		    motorways = L.tileLayer(cmUrl, {attribution: cmAttr});

		var map = L.map('map', {
			center: [0.0, 0.0],
			zoom: 10,
			layers: [minimal, motorways, incidents, facilities, units, roadinfo]
			});

		var baseLayers = {
			"Minimal": minimal,
			"Night View": midnight
			};

		var overlays = {
			"Units": units,
			"Facilities": facilities,
			"Incidents": incidents,
			"RoadInfo": roadinfo
			};

		L.control.layers(baseLayers, overlays).addTo(map);

		function send_position(in_point) {
			function sp_callback(req) {
//				alert(<?php echo __LINE__;?>);					// disregard return value
				}		// end function()
			var params = "lat=" + in_point.lat + "&lng=" + in_point.lng + "&unit_id=" + <?php echo $_SESSION['user_unit_id'];?>;		// 
			var url = "./ajax/set_position.php";
			sendRequest( url, sp_callback, params);		//  update position data and track
			}

		function onLocationFound(e) {		// meIcon isValid() 
			var radius = e.accuracy / 2;
			L.marker(e.latlng, {icon: meIcon}).addTo(map);
			if (!my_bounds) {my_bounds = new L.LatLngBounds(markers_work.getLatLng(), markers_work.getLatLng());}	// set corners
			my_bounds.extend(e.latlng);									// to bounding box
			map.fitBounds(my_bounds);									// show the map		
			radius = Math.round(getMapRadius() * 0.2);					// 20% - arbitrary
			L.circle(e.latlng, radius, { color: 'red', fill: false}).addTo(map);		// center circle on my position
//			send_position(e.latlng)
			}				// end function

		function getMapRadius () {		// returns distance map center to NE in meters
		    var mapBoundNorthEast = map.getBounds().getNorthEast();
		    return mapBoundNorthEast.distanceTo(map.getCenter());
			}

		function onLocationError(e) {
			alert(e.message);
			}

		map.on('locationfound', onLocationFound);
		map.on('locationerror', onLocationError);

		map.locate({setView: true, maxZoom: 16});		

		map.on('enterFullscreen', function(){		// detect fullscreen toggling
			if(window.console) window.console.log('enterFullscreen');
		});
		map.on('exitFullscreen', function(){
			if(window.console) window.console.log('exitFullscreen');
		});
		
	</script>
<!--
	<div style = 'width:100%;text-align:center'>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_red.png'/>Incidents</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_black.png'/>Responders</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_yellow.png'/>Facilities</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_white.png'/>Road conditions</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/crosshair.png'/>me</span>
	</div>

<script>
function getLocation() {
	if (navigator.geolocation) {
		var array_pos = navigator.geolocation.getCurrentPosition(showPosition);
		}
	else { return false; } 
function showPosition(position) {
	return [ position.coords.latitude,  position.coords.longitude ];
	}
</script>
-->

<?php
require_once('incs/footer.php');	
?>
<form name = "navForm" method = post action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" value = ""/>			<!-- array index of target record -->
<input type = hidden name = "id_str" value = ""/>
</form>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</body>
</html>