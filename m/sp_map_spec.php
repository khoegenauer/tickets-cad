<?php
/*
alert("<?php echo __LINE__;?> ");

4/18/2013 - initial release
6/23/2013 - roadinfo added

array(3) {
  ["id"]=> 		string(1) "0"
  ["id_str"]=>	string(50) "47,50,48,49,51,52,54,55,53,56,57,58,59,60,63,62,61"
  ["group"]=>	string(1) "2"
}
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);

@session_start();
if (! array_key_exists('SP', $_SESSION)) {
	header("Location: index.php");
	}
$_limit = 9999;

$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty
require_once('../incs/functions.inc.php');
require_once('./incs/sp_functions.inc.php');
$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
$the_group = $_POST['group'];
$target = true;								// target layer

//dump ($_SESSION['SP']);

		function unit_query ($id_selector) {		//				returns UNITS  SQL
			global $_limit;
			return "SELECT `r`.`id` AS `unit_id`, `icon_str`, `handle`, `lat`, `lng`, `type`, `un_status_id`, `s`.`dispatch`,
					SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
					`r`.`description` AS `unit_descr`, 
					`t`.`description` AS `unit_type`, 
					`s`.`description` AS `unit_status`
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = t.id )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = s.id ) 
				{$id_selector}
				ORDER BY `handle` ASC LIMIT {$_limit} ";		
				}		// end function ()

		function facy_query ($id_selector) {			//		returns FACILITIES SQL
			global $_limit;
			return "SELECT `f`.`id` AS `facy_id`, `icon_str`, `handle`, `type`, `lat`, `lng`, `status_id`, 
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
				`f`.`description` AS `facy_descr`, 
				`t`.`description` AS `facy_type`, 
				`s`.`description` AS `facy_status`				
				FROM `$GLOBALS[mysql_prefix]facilities` `f` 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
				{$id_selector}
				ORDER BY `handle` ASC  LIMIT {$_limit} ";
				}		// end function ()

		function tick_query ($id_selector) {			//		returns INCIDENT SQL 
			global $_limit;
			$hide_limit = get_variable('hide_booked');		// 5/26/2013
			return "SELECT `t`.`id`  AS `tick_id`,
				CONCAT_WS(' ',`street`,`city`) AS `addr`,
				 `status`, `lat`, `lng`, `scope` , `t`.`description` AS `description` ,
				 `severity` , `comments` ,
				( SELECT COUNT( * ) FROM `$GLOBALS[mysql_prefix]assigns`
					WHERE (`$GLOBALS[mysql_prefix]assigns`.`ticket_id` = `t`.`id`
					AND ( ( `clear` IS NULL ) OR ( DATE_FORMAT( `clear` , '%y' ) = '00') ) ) ) 
					AS `u`, 
				( SELECT COUNT( * )	FROM `$GLOBALS[mysql_prefix]action`
					WHERE `$GLOBALS[mysql_prefix]action`.`ticket_id` = `t`.`id` ) 
					AS `a`,
				( SELECT COUNT( * ) FROM `$GLOBALS[mysql_prefix]patient`
					WHERE `$GLOBALS[mysql_prefix]patient`.`ticket_id` = `t`.`id` ) 
					AS `p`,
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `as of`				
				FROM `$GLOBALS[mysql_prefix]ticket` `t` 
				WHERE ( (`t`.`status`='{$GLOBALS['STATUS_OPEN']}' ) OR
					(`booked_date` <=  (NOW() + INTERVAL {$hide_limit} HOUR) )  		
					{$id_selector} ) 
				ORDER BY `severity` DESC, `problemstart` ASC  LIMIT {$_limit} ";
				}		// end function ()

$GLOBALS['TABLE_TICKET'] 	= 0;	
$GLOBALS['TABLE_RESPONDER'] = 1;
$GLOBALS['TABLE_FACILITY']  = 2;
$GLOBALS['TABLE_ASSIGN']   	= 3;
$GLOBALS['TABLE_ROAD']   	= 4;
$GLOBALS['TABLE_CLOSED']   	= 5;
$GLOBALS['ME']   			= 6;

//		WARNING - WARNING - WARNING - match any changes to JS definitions below - WARNING - WARNING - WARNING - WARNING 
$layers =	array($GLOBALS['TABLE_TICKET']  => "incidents", 	$GLOBALS['TABLE_RESPONDER'] => "units", 		$GLOBALS['TABLE_FACILITY'] => "facilities", 	$GLOBALS['TABLE_ROAD'] => "roadinfo", 	$GLOBALS['TABLE_CLOSED'] => "nearby" , 		$GLOBALS['ME'] => "me" );
$icons =	array($GLOBALS['TABLE_TICKET']  => "tickIcon", 		$GLOBALS['TABLE_RESPONDER'] => "unitIcon", 		$GLOBALS['TABLE_FACILITY'] => "facIcon", 		$GLOBALS['TABLE_ROAD'] => "roadIcon", 	$GLOBALS['TABLE_CLOSED'] => "closedIcon", 	$GLOBALS['ME'] => "meIcon" );
$bigIcons =	array($GLOBALS['TABLE_TICKET']  => "big_tickIcon", 	$GLOBALS['TABLE_RESPONDER'] => "big_unitIconn", $GLOBALS['TABLE_FACILITY'] => "big_facIcon", 	$GLOBALS['TABLE_ROAD'] => "roadIcon", 	$GLOBALS['TABLE_CLOSED'] => "closedIcon", 	$GLOBALS['ME'] => "meIcon" );

function randomFloat() {
	return floatval ( .5 - (mt_rand(0, 10000) ) / 10000);
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>TicketsSP - <?php echo basename(__FILE__);?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href=					"./dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href=	"./dist/leaflet.ie.css" /><![endif]-->
	<script src=									"./dist/leaflet.js"></script>
	<link rel="stylesheet" href=					"./dist/leaflet.fullscreen.css"/>
	<script src=									"./dist/Leaflet.fullscreen.js"></script>	
	<link rel="stylesheet" href=					"./dist/leaflet.label.css" />
	<script src=									"./dist/leaflet.label.js"></script>
	<script src=									"./dist/Google.js"></script>
	<script src=									"./js/misc.js"></script>
	<script src=									"http://maps.google.com/maps/api/js?sensor=false&amp;v=3.2"></script>

<!--	https://gist.github.com/2197042 -->	
<?php
if (intval(get_variable('broadcast'))==1) {	
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()

	function do_stop_cycle() {
		try   		{ parent.frames['top'].stop_cycle();  }
		catch(err)	{}
		}

</script>
	<style>
		body 		{ padding: 0; margin: 0; }
		html, body	{ height: 100%; }
	</style>

</head>
<body>	<!-- <?php echo __LINE__;?> -->
<?php
//	require_once('incs/header.php');	
?>
<!--	legend
	<div style =  'width:100%; text-align:center'>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_red.png'/>Incidents</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_black.png'/>Responders</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_yellow.png'/>Facilities</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_white.png'/>Road conditions</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/crosshair.png'/>me</span>
	</div>
-->
	<center><div id="map"  style=" height: <?php echo ($_SESSION['SP']['scr_height'] - 200);?>px; "></div>

	<script>
		var markers_work;
		var markers_ary = [];
		var id_array;
		var latest_position = new L.LatLng( 0, 0);			// <?php echo __LINE__ . "\n";?>
		
		var my_bounds = new L.LatLngBounds([<?php echo  $_SESSION['SP']['latitude'];?>, <?php echo $_SESSION['SP']['longitude'];?>], [<?php echo $_SESSION['SP']['latitude'];?>, <?php echo $_SESSION['SP']['longitude'];?>]);		

<?php	
	function do_marker ($lat, $lng, $layer, $icon, $table, $id, $sb_index) {
		$label_val = ".bindLabel('!', { noHide: true })";	
		$label_act = ".showLabel()";				
?>	
	    markers_work = L.marker([<?php echo $lat;?>, <?php echo $lng;?>], {icon: <?php echo $icon;?>})<?php echo $label_val;?>.addTo(<?php echo $layer;?>)<?php echo $label_act;?>;
		markers_work.addEventListener('click', function(e) { on_Click (<?php echo $sb_index;?>, <?php echo $table;?>, <?php echo $id;?>);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
//		my_bounds.extend(markers_work.getLatLng());											// to bounding box
<?php
	}		// end function do_marker()
?>
		DomReady.ready(function() {			//set initial bounds at map center	
			var t=setTimeout ( function(){parent.frames["top"].start_cycle()}, 1000 );		// wait
			id_array = document.navForm.id_str.value.split(",");
			});		
	
		var baseIcon = L.Icon.extend({options: {shadowUrl: './images/sm_shadow.png',
			iconSize: [12, 20],	shadowSize: [22, 20], iconAnchor: [0, 0],	shadowAnchor: [5, 10], popupAnchor: [6, -5]
			}
			});

		var big_tickIcon =	new baseIcon({iconUrl: 'images/red.png', 		iconSize: [20, 34], iconAnchor: [10, 17]}),
		tickIcon = 			new baseIcon({iconUrl: 'images/sm_red.png', 	iconSize: [12, 20], iconAnchor: [6, 10]}),
		big_unitIconn =		new baseIcon({iconUrl: 'images/green.png', 		iconSize: [20, 34], iconAnchor: [10, 17]}),
		unitIcon = 			new baseIcon({iconUrl: 'images/sm_green.png', 	iconSize: [12, 20], iconAnchor: [6, 10]}),
		big_facIcon =		new baseIcon({iconUrl: 'images/yellow.png', 	iconSize: [20, 34], iconAnchor: [10, 17]}),
		facIcon = 			new baseIcon({iconUrl: 'images/sm_yellow.png',  iconSize: [12, 20], iconAnchor: [6, 10]}),
		roadIcon = 			new baseIcon({iconUrl: 'images/sm_white.png', 	iconSize: [12, 20], iconAnchor: [6, 10]}),
		closedIcon = 		new baseIcon({iconUrl: 'images/sm_black.png', 	iconSize: [12, 20], iconAnchor: [6, 10]}),		
		pos_unknown_icon =	new baseIcon({iconUrl: 'images/question1.png',  iconSize: [14, 30], iconAnchor: [7, 15]}),
		meIcon =			new baseIcon({iconUrl: 'images/crosshair.png',  iconSize: [32, 32], iconAnchor: [16, 16]});		//	crosshair_128 reticlebm7

		function on_Click (array_id, table_id, record_id) {							// 1: here on marker click - 2: issue request - 3: IW data returned via callback
			function iw_callback(req) {
				markers_ary[array_id].bindPopup(req.responseText).openPopup();		// - array_id?
				}		// end function my_callback()
			var params = "table_id="+ table_id + "&record_id=" +record_id;			// 
			var url = "./ajax/return_iw.php";										//  issue AJAX call ...
			sendRequest( url, iw_callback, params );								//    for infowindow contents
			}		// end function on Click ()		
		
		var incidents = 	new L.LayerGroup();
		var units = 		new L.LayerGroup();
		var facilities = 	new L.LayerGroup();
		var roadinfo = 		new L.LayerGroup();
		var nearby = 		new L.LayerGroup();
	    var me   = 			new L.LayerGroup();
	    var target   = 		new L.LayerGroup();

<?php	
		$side_bar_index = 0;		// used only with sidebar page
		
//							 generate UNITS  data
		$id_selector = ( $the_group == $GLOBALS['TABLE_RESPONDER'] ) ? " WHERE `r`.`id` <> {$the_id} " : "" ;	// possibly 'me'
		$query = unit_query ($id_selector) ;
		$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$ct_u = mysql_num_rows($result_unit);

		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_unit))) {	
			if ( intval ( $in_row['unit_id'] ) == intval ( $me ) ) 	{
				$the_layer = 	$layers[$GLOBALS['ME']];
				$the_icon = 	$icons[$GLOBALS['ME']];
				$my_lat = 		$in_row['lat']; 
				$my_lng = 		$in_row['lng']; 
				$my_handle = 	$in_row['handle'];
				snap(__LINE__, $my_handle);
?>
		var my_marker_index = <?php echo $side_bar_index;?>;					// save for marker motion
		my_bounds.extend([<?php echo $my_lat;?>, <?php echo $my_lng;?>]);		// my position to bounding box - <?php echo __LINE__;?>
<?php
				}
			else 	 {
				$the_layer = 	$layers[$GLOBALS['TABLE_RESPONDER']];
				$the_icon = 	$icons[$GLOBALS['TABLE_RESPONDER']];
				}
			if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
		
				if ( ( $in_row ['unit_id'] == $the_id ) && ( $the_group == $GLOBALS['TABLE_RESPONDER'] ) ) {		// if at target
					$my_lat = $in_row['lat']; $my_lng = $in_row['lng']; 
					$the_icon = $bigIcons[$GLOBALS['TABLE_RESPONDER']];
					}			
				do_marker ($in_row['lat'], $in_row['lng'], 	$the_layer , $the_icon, $GLOBALS['TABLE_RESPONDER'], $in_row['unit_id'], $side_bar_index);
				}
			else {				// invalid position data
				do_marker ( ( get_variable('def_lat') + randomFloat() ) , ( get_variable('def_lng') + randomFloat() ), 	$the_layer, "pos_unknown_icon", $GLOBALS['TABLE_RESPONDER'], $in_row['unit_id'], $side_bar_index);			
				}
			$side_bar_index++;
			}
//							 generate FACILITIES data
		$id_selector = ($the_group == $GLOBALS['TABLE_FACILITY'] ) ? " WHERE `f`.`id`  <> {$the_id} " : "" ;
		$query = facy_query ($id_selector);
		$result_facy = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$ct_f = mysql_num_rows($result_facy);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_facy))) {			

			$my_icon = $icons[$GLOBALS['TABLE_FACILITY']];
			if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
				do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_FACILITY']], $my_icon, $GLOBALS['TABLE_FACILITY'], $in_row['facy_id'], $side_bar_index);
				}
			else {				// invalid position data
				do_marker ( ( get_variable('def_lat') + randomFloat() ), ( get_variable('def_lng') + randomFloat() ) , 	$layers[$GLOBALS['TABLE_FACILITY']], "pos_unknown_icon", $GLOBALS['TABLE_FACILITY'], $in_row['facy_id'], $side_bar_index);			
				}

			$side_bar_index++;
			}
//							 generate INCIDENT data - sidebar data unused here

		$id_selector = ($the_group == $GLOBALS['TABLE_TICKET'] ) ? " AND ( `t`.`id` <> {$the_id} ) " : "" ;
		$query = tick_query ($id_selector);
		$result_tick = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$ct_t = mysql_num_rows($result_tick);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
			if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {

				$my_icon = $icons[$GLOBALS['TABLE_TICKET']];
				do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_TICKET']], $my_icon, $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);
				}
			else {				// invalid position data
				do_marker ( ( get_variable('def_lat') + randomFloat() ), ( get_variable('def_lng') + randomFloat() ), 	$layers[$GLOBALS['TABLE_TICKET']], "pos_unknown_icon", $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);			
				}
	
			$side_bar_index++;
			}
		$nearby_ok = ( ( array_key_exists("longitude", $_SESSION['SP'] ) ) && ( is_ok_position ( $_SESSION['SP'] ['latitude'] , $_SESSION['SP'] ['longitude'] ) ) ) ;

		if ( $nearby_ok ) { 	

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

				( 6371 * acos ( 
				cos ( radians ( {$_SESSION['SP']['latitude']} ) ) *
				cos ( radians ( `lat` ) ) *
				cos ( radians ( `lng` ) - radians ( {$_SESSION['SP']['longitude']} ) ) +
				sin ( radians ( {$_SESSION['SP']['latitude']} ) ) *
				sin ( radians ( `lat` ) ) ) ) 				AS `km_from`,
				
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `as of`				
				FROM `$GLOBALS[mysql_prefix]ticket` `t` 
				WHERE ( ( `status` = {$GLOBALS['STATUS_CLOSED']} ) AND ( `lat` <> {$GLOBALS['NM_LAT_VAL']} ) )
				ORDER BY `km_from` ASC, `t`.`updated` DESC LIMIT {$_limit}
				";			//	STATUS_CLOSED	STATUS_OPEN

			$result_tick = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$ct_n = mysql_num_rows($result_tick);
			if ($result_tick ) {
				while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
					if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
//								  ( $lat, 			$lng, 			$layer, 						   $icon, 							 $table, 					$id, 				$sb_index) 					
						do_marker ( $in_row['lat'], $in_row['lng'], $layers[$GLOBALS['TABLE_CLOSED']], $icons[$GLOBALS['TABLE_CLOSED']], $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);
						}
					else {				// invalid position data
						do_marker ( ( get_variable('def_lat') + randomFloat() ), ( get_variable('def_lng') + randomFloat() ), 	$layers[$GLOBALS['TABLE_TICKET']], "pos_unknown_icon", $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);			
						}
		
					$side_bar_index++;
					}
				}
			}

//							   generate ROADINFO data 
		$roadinfo_ok = mysql_table_exists("$GLOBALS[mysql_prefix]roadinfo") ;		// possibly not defined
		if ($roadinfo_ok) { 

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo`  LIMIT {$_limit}";
 			$result_road = mysql_query($query);			
			$ct_r = mysql_num_rows($result_road);
 			if ( $result_road ) {
	 			$the_icon = $icons[$GLOBALS['TABLE_ROAD']];		
	 			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_road))) {			
					if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
						do_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_ROAD']], $icons[$GLOBALS['TABLE_ROAD']], $GLOBALS['TABLE_ROAD'], $in_row['id'], $side_bar_index);
						}
					else {				// invalid position data
						do_marker ( (get_variable('def_lat') + randomFloat() ) , ( get_variable('def_lng') + randomFloat() ), 	$layers[$GLOBALS['TABLE_ROAD']], "pos_unknown_icon", $GLOBALS['TABLE_ROAD'], $in_row['id'], $side_bar_index);			
						}
	
	 				$side_bar_index++;
	 				}
 				}		// end if ( $result_road ) 
 			}		// end if ($roadinfo_ok)

//		---------------------------------- end icon generation --------------------------------------------
 				
 			if ( ! ( isset($my_handle) ) ) $my_handle = "Me";
			
			if ($target) {
				switch ($the_group) {
					case $GLOBALS['TABLE_TICKET']:
						$query = tick_query (" AND ( `t`.`id` = {$the_id} ) ");
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$in_row = stripslashes_deep(mysql_fetch_assoc($result));
						$target_handle = $in_row['scope'];
						break;
						
					case $GLOBALS['TABLE_RESPONDER']:
						$query = unit_query (" WHERE `r`.`id` = {$the_id} ");
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$in_row = stripslashes_deep(mysql_fetch_assoc($result));
						$target_handle = $in_row['handle'];
						break;
						
					case $GLOBALS['TABLE_FACILITY']:
						$query = facy_query (" WHERE `f`.`id`  = {$the_id} ");
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$in_row = stripslashes_deep(mysql_fetch_assoc($result));
						$target_handle = $in_row['handle'];
						break;	
					default:
						dump ($_POST);
					    echo "error - error - error - error - error - ";
					}				// end switch ($the_group)

				if ( is_ok_position ( $in_row['lat'] , $in_row['lng'] ) ) {
					$the_icon = $bigIcons[$the_group];
					do_marker ($in_row['lat'], $in_row['lng'], "target", $the_icon, $the_group, $the_id, $side_bar_index);
					}
				else {				// invalid position data
					do_marker ( ( get_variable('def_lat') + randomFloat() ), ( get_variable('def_lng') + randomFloat() ) , 	"target", "pos_unknown_icon", $the_group, $the_id, $side_bar_index);			
					}
				echo "\t\tmy_bounds.extend([{$in_row['lat']}, {$in_row['lng']}]);\t\t//" . __LINE__ ."\n";		
	
				$side_bar_index++;
				}				// end if ($target)

?>
//							end icon generation 

		var my_Path = "http://127.0.0.1/_osm/";
		var in_local_bool = false;
		var osmUrl = (in_local_bool)?
			"../_osm/tiles/{z}/{x}/{y}.png":
			"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

	    var cmUrl = osmUrl;

//	    var OSM   = 		L.tileLayer(cmUrl);
		var gglr = 			new L.Google('ROADMAP');
		var gglh = 			new L.Google('HYBRID');
		var ggls = 			new L.Google('SATELLITE');
		var gglt = 			new L.Google('TERRAIN');

<?php
	$target_on = ($target)?  ", target" : ""; 
	$target_overlay =  ($target)?  ", \n\t\t\t'{$target_handle}':\t target\n" : ""; 

	switch ( intval ( get_variable ('maptype' ) ) ) {
			case (2): $the_type= "SATELLITE"; 	break;
			case (3): $the_type= "TERRAIN"; 	break;
			case (4): $the_type= "HYBRID"; 		break;
			default:  $the_type= "ROADMAP";
			}		// end switch

?>
		var goog = new L.Google('<?php echo $the_type;?>');

		var map = L.map('map', {
			center: [<?php echo get_variable('def_lat') ; ?>, <?php echo get_variable('def_lng') ; ?>],
			zoom: <?php echo get_variable('def_zoom') ; ?>,
//			layers: [OSM, goog, me <?php echo $target_on;?>],
			layers: [goog, me <?php echo $target_on;?>],
			fullscreenControl: false
			});

		map.fitBounds(my_bounds);								// show the map		- <?php echo __LINE__;?>

		var baseLayers = {
			"Google": 			goog
//			"OSM": 				OSM
			};

		var overlays = {
			"<?php echo $my_handle;?>" : 				me,
			"Units (<?php echo $ct_u;?>)": 				units,
			"Incidents (<?php echo $ct_t;?>)": 			incidents,
<?php	if ($nearby_ok ) {	?>
			"Nearby (<?php echo $ct_n;?>)": 			nearby,
<?php			} ?>
<?php	if ($roadinfo_ok) { ?>
			"RoadInfo (<?php echo $ct_r;?>)": 			roadinfo,
<?php		}	?>
			"Facilities (<?php echo $ct_f;?>)": 		facilities
			};

		function onMapClick(e) {
			$("context").style.display = "block";			
			}

		function canContext() {
			$("context").style.display = "none";			
			}

		map.on('contextmenu', onMapClick);
		
		var my_circle, radius;

		function move_circle ( lat_in, lng_in ) {						// called from parent.frames["top"] on detecting motion
			my_circle.setLatLng([lat_in, lng_in]);						// the circle
//			map.panTo(new L.LatLng(lat_in, lng_in ));					// map center on new position - ????
			markers_ary[my_marker_index].setLatLng([lat_in, lng_in]);	// the marker
			}			

		function getMapRadius () {								// returns distance map center to NE in meters
		    var mapBoundNorthEast = map.getBounds().getNorthEast();
		    return mapBoundNorthEast.distanceTo(map.getCenter());
			}

		map.fitBounds(my_bounds);								// show the map		

		var t=setTimeout(function(){
			radius = Math.round(getMapRadius() * 0.05);				// arbitrary
			my_circle = L.circle( [<?php echo $my_lat;?>, <?php echo $my_lng;?> ], radius, { color: 'red', fill: false}).addTo(map);		// center circle on my position
			L.control.layers(baseLayers, overlays).addTo(map);
			},1000) ;												// wait for rendering

//		map.panTo(new L.LatLng(<?php echo $my_lat;?>, <?php echo $my_lng ;?>));		// to the target

		map.on('enterFullscreen', function(){		// detect fullscreen toggling
			if(window.console) window.console.log('enterFullscreen');
			});
		map.on('exitFullscreen', function(){
			if(window.console) window.console.log('exitFullscreen');
			});
		

		function do_mail (in_addr) {
			document.mailform.mail_addr.value = in_addr;
			document.mailform.submit();
			}
	</script>

<form name = "mailform" method = post 	action = "sp_mail.php?rand=<?php echo time();?>">
<input type = hidden name = "mail_addr" value = "" />			
</form>
<!--
	<div style = 'width:100%;text-align:center'>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_red.png'/>Incidents</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_black.png'/>Responders</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_yellow.png'/>Facilities</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/sm_white.png'/>Road conditions</span><br/>
	<span style = 'width:20%; margin-left:40px;'><img src = 'images/crosshair.png'/>me</span>
	</div>
-->

<form name = "navForm" method = post action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $_POST['id'];?>"/>			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>"/>
<input type = hidden name = "group" 	value = "<?php echo $_POST['group'];?>" />
</form>

<table id="context" cellpadding = 4 style="position: fixed; top: <?php echo round ( .1 * $_SESSION['SP']['scr_height'] );?>px; left: <?php echo round (.25 * $_SESSION['SP']['scr_width'] );?>px; display: none; ">
<tr><td class = 'my_hover' onclick = 'do_stop_cycle(); navTo("sp_lout.php", "")'>	<?php echo get_text("Logout");?></td></tr>
<tr><td class = 'my_hover' onclick = 'canContext();'>					Cancel</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_calls.php", "")'>		<?php echo get_text("Calls");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_tick.php", "")'>		<?php echo get_text("Incidents");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_resp.php", "")'>		<?php echo get_text("Responders");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_fac.php", "")'>			<?php echo get_text("Facilities");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_mail.php", "")'>		<?php echo get_text("Email");?></td></tr>
<tr><td class = 'my_hover' onclick = 'to_tickets ();'>					to Tickets</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("<?php echo basename(__FILE__);?>", "")'>Map refresh</td></tr>
</table>
</body>
</html>