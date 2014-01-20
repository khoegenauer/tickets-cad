<?php
$_limit = 9999;
/*
9/24/2013 - rewrite to handle hidden units
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED', 8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);

@session_start();
if ( !array_key_exists('SP', $_SESSION) ) {
	header("Location: index.php");
	}

$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty

require_once('../incs/functions.inc.php');
require_once('./incs/sp_functions.inc.php');
$id_array = explode (",", $_POST['id_str']);
$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
$the_group = $_POST['group'];
								/* no target if it's 'me'  */
$target = ( ! ( $the_group == $GLOBALS['TABLE_RESPONDER'] && ( $the_id == $_SESSION["SP"]["user_unit_id"] ) ) ) ;

/*			defined in sp_functions 
$GLOBALS['TABLE_TICKET'] 			= 0;	
$GLOBALS['TABLE_RESPONDER'] 		= 1;
$GLOBALS['TABLE_FACILITY']  		= 2;
$GLOBALS['TABLE_ASSIGN']   			= 3;
$GLOBALS['TABLE_ROAD']   			= 4;
$GLOBALS['TABLE_CLOSED']   			= 5;
$GLOBALS['ME']   					= 6;
$GLOBALS['TABLE_RESPONDER_HIDE'] 	= 7;
*/

$layers = array();
$layers[$GLOBALS['TABLE_TICKET']] =  	"incidents";
$layers[$GLOBALS['TABLE_RESPONDER']] =	"units";
$layers[$GLOBALS['TABLE_FACILITY']] =	"facilities";
$layers[$GLOBALS['TABLE_ASSIGN']] =  	null;
$layers[$GLOBALS['TABLE_ROAD']] =    	"roadinfo";
$layers[$GLOBALS['TABLE_CLOSED']] =  	"nearby" ;
$layers[$GLOBALS['ME']] =    			"me";
$layers[$GLOBALS['TABLE_RESPONDER_HIDE']] =    			"hides";

$icons = array();
$icons[$GLOBALS['TABLE_TICKET']]=		"tickIcon";
$icons[$GLOBALS['TABLE_RESPONDER']]=	"unitIcon";
$icons[$GLOBALS['TABLE_FACILITY']]=		"facIcon";
$icons[$GLOBALS['TABLE_ASSIGN']] =  	null;
$icons[$GLOBALS['TABLE_ROAD']] =		"roadIcon";
$icons[$GLOBALS['TABLE_CLOSED']]=		"closedIcon";
$icons[$GLOBALS['ME']] = 				"meIcon";
$icons[$GLOBALS['TABLE_RESPONDER_HIDE']] =	"hideIcon";

$bigIcons = array();
$bigIcons[$GLOBALS['TABLE_TICKET']]=	"tickIcon";
$bigIcons[$GLOBALS['TABLE_RESPONDER']]=	"unitIcon";
$bigIcons[$GLOBALS['TABLE_FACILITY']]=	"facIcon";
$bigIcons[$GLOBALS['TABLE_ASSIGN']] =  	null;
$bigIcons[$GLOBALS['TABLE_ROAD']] =		"roadIcon";
$bigIcons[$GLOBALS['TABLE_CLOSED']]=	"closedIcon";
$bigIcons[$GLOBALS['ME']] = 			"meIcon";
$bigIcons[$GLOBALS['TABLE_RESPONDER_HIDE']] =	"hideIcon";

$count = array();
$count[$GLOBALS['TABLE_TICKET']] =
$count[$GLOBALS['TABLE_RESPONDER']] =
$count[$GLOBALS['TABLE_FACILITY']] =
$count[$GLOBALS['TABLE_ASSIGN']] =
$count[$GLOBALS['TABLE_ROAD']] =
$count[$GLOBALS['TABLE_CLOSED']] =
$count[$GLOBALS['ME']] = 
$count[$GLOBALS['TABLE_RESPONDER_HIDE']] = 0;

$ini_array = parse_ini_file("./incs/sp.ini");
$do_google = 	( array_key_exists ( "use_gmaps", $ini_array ) );
$do_together = 	( array_key_exists ( "use_togetherjs", $ini_array ) );

function randomFloat() {
	return floatval ( .5 - (mt_rand(0, 10000) ) / 10000);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>TicketsSP - <?php echo basename(__FILE__);?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta charset="utf-8" />
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

<?php						// 12/16/2013
	if ($do_together) {
?>
	<script src=									"https://togetherjs.com/togetherjs-min.js"></script>
<?php
	}
?>

<!--	https://gist.github.com/2197042 -->	
<?php
if (intval(get_variable('broadcast'))==1) {	
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
<script>
	function do_stop_cycle() {
		try   		{ parent.frames['top'].stop_cycle();  }
		catch(err)	{}
		}
</script>
	<style>
		body { padding: 0; margin: 0; }
		html, body, #map {height: 100%; }
	</style>

</head>
<body>	<!-- <?php echo __LINE__;?> -->
	<div id="map"></div>
<!-- $("map").style.backgroundImage = "url(./markers/loading.jpg)"; -->

<?php
	if ( is_ok_position ( $_SESSION["SP"]['latitude'] , $_SESSION["SP"]['longitude'] ) ) {
		$my_position_arr = array ($_SESSION["SP"]['latitude'] , $_SESSION["SP"]['longitude'] ) ;
		$my_position = TRUE;
		}
	else {
		$my_position_arr = array ( get_variable('def_lat'), get_variable('def_lng') ) ;
		$my_position = FALSE;
		}
?>

	<script>
		var markers_work;
		var markers_ary = [];
		var id_array;

		var latest_position = new L.LatLng( <?php echo $my_position_arr[0];?>, <?php echo $my_position_arr[1];?>);
		var my_bounds = new L.LatLngBounds([<?php echo $my_position_arr[0];?>, <?php echo $my_position_arr[1];?>], 
			[<?php echo $my_position_arr[0];?>, <?php echo $my_position_arr[1];?>]);		

<?php	
	function do_marker ($lat, $lng, $layer, $icon, $table, $id, $sb_index) {
		$label_val = ".bindLabel('XxXxXxXXxXxXxXXxXxXxXXxXxXxX!',{ noHide: true })";	
		$label_act = ".showLabel()";				
?>	
	    markers_work = L.marker([<?php echo $lat;?>,<?php echo $lng;?>],{icon:<?php echo $icon;?>})<?php echo $label_val;?>.addTo(<?php echo $layer;?>)<?php echo $label_act;?>;
		markers_work.addEventListener('click', function(e) { on_Click (<?php echo $sb_index;?>, <?php echo $table;?>, <?php echo $id;?>);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
		my_bounds.extend(markers_work.getLatLng());											// to bounding box
<?php
		}		// end function do_marker()

	function do_prelim_marker ($the_lat, $the_lng, $the_layer , $the_icon, $the_table, $the_id, $side_bar_index) {
		global $icons, $layers;
		if ( is_ok_position ( $the_lat , $the_lng ) ) {
			do_marker ($the_lat, $the_lng, 	$layers[$the_table], $icons[$the_table], $the_table, $the_id, $side_bar_index);	
			}
		else	{
			do_marker ( ( get_variable('def_lat') + randomFloat() ), ( get_variable('def_lng') + randomFloat() ) , 	$layers[$the_table], "pos_unknown_icon", $the_table, $the_id, $side_bar_index);			
			}
		}				//end function do prelim_marker()

?>
		var li_cycle_seconds = 5000;				// 5 seconds
		var ck_li_url = "./ajax/logged_in.php";		// issue AJAX call ...
		function ck_li () {							// to obtain logged-in status
			function ck_li_callback(req) {
				if ( req.responseText==0 ) { 		// zero if no longer logged-in
					clearInterval(gcpTimer);
					clearInterval(ck_loged_in_timer);
					$("map").style.opacity=0.5;				// fade
					setTimeout(function(){window.close();},3000);					
					}				// end if (responseText==0)
				}				// end function ck_li_callback(req)
				
			sendRequest( ck_li_url, ck_li_callback );										// for logged-in status
			}		// end function ck_li ()
		

		DomReady.ready(function() {			//set initial bounds at map center	
			id_array = document.navForm.id_str.value.split(",");
			ck_loged_in_timer = setInterval(ck_li, li_cycle_seconds);		// start logged-in check

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
		roadIcon = 			new baseIcon({iconUrl: 'images/red_x.png', 		iconSize: [12, 20], iconAnchor: [6, 10]}),
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
		var hides = 		new L.LayerGroup();
		var facilities = 	new L.LayerGroup();
		var roadinfo = 		new L.LayerGroup();
		var nearby = 		new L.LayerGroup();
		var me   = 			new L.LayerGroup();
	    
		var me_is_onscr = 	false;
<?php	
		if ( $target ) {
		    echo "\n\t\tvar target   = 		new L.LayerGroup();\n";		// define the overlay layer
	 	   }
		$side_bar_index = 0;		// used only with sidebar page
// ==============================================================================================

	function x_get_resp_sql ($where_cl) {
		global $_limit;
		return "SELECT `r`.`id` AS `unit_id`, `icon_str`, `handle`, `lat`, `lng`, `type`, `un_status_id`, `dispatch`,
					SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
					`r`.`description` AS `unit_descr`, 
					`t`.`description` AS `unit_type`, 
					`s`.`description` AS `unit_status`
				FROM `$GLOBALS[mysql_prefix]responder` `r` 
				LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `t` ON ( `r`.`type` = `t`.`id` )	
				LEFT JOIN `$GLOBALS[mysql_prefix]un_status` `s` ON ( `r`.`un_status_id` = `s`.`id` ) 	
				{$where_cl}
				ORDER BY `handle` ASC LIMIT {$_limit}
				";			
		}			// end function x_get_resp_sql		

	function get_facy_sql ($where_cl) {
		global $_limit;
		return "SELECT `f`.`id` AS `facy_id`, `icon_str`, `handle`, `type`, `lat`, `lng`, `status_id`, 
				SUBSTRING(CAST(`updated` AS CHAR),9,8 ) AS `sb_updated`,
				`f`.`description` AS `facy_descr`, 
				`t`.`description` AS `facy_type`, 
				`s`.`description` AS `facy_status`
				
				FROM `$GLOBALS[mysql_prefix]facilities` `f` 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `t` ON `f`.type = `t`.id 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
				{$where_cl}
				ORDER BY `handle` ASC  LIMIT {$_limit}
				";
				}		// end function get facy_sql


	function get_local_tick_sql ($where_cl) {
		global $_limit;

		return "SELECT `t`.`id`  AS `tick_id`,
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
				{$where_cl}
				ORDER BY `severity` DESC, `problemstart` ASC  LIMIT {$_limit}
				";
			}		// end function get tick_sql ( ... )

// ==============================================================================================


				$where_cl_tick_1 = $where_cl_facy_1 = $where_cl_resp_1 = $where_cl_resp_h_1 = "";
				switch ($the_group) {
					case $GLOBALS['TABLE_TICKET']:					
						$where_cl_tick_1 = " AND ( `t`.`id` <> '{$the_id}' ) ";
						break;
						
					case $GLOBALS['TABLE_RESPONDER']:
						$where_cl_resp_1 = " WHERE `unit_id` <> '{$the_id}'";
						$where_cl_resp_h_1 =" AND (`unit_id` <> '{$the_id}')";
						break;
						
					case $GLOBALS['TABLE_FACILITY']:
						$where_cl_facy_1 = " WHERE `f`.`id` <> '{$the_id}'";						
						break;
												
					default:
						dump (__LINE__);
					    echo "error - error - error - error - error - ";
					}				// end switch ($the_group)


//							 generate UNITS  data

		$not_me_str = ( intval ( $me ) > 0 )? " AND `r`.`id` != {$me} " : "";		// do the visible units
		$_where_str = "WHERE (`hide` = 'n' {$not_me_str})";
		$query = x_get_resp_sql ($_where_str);

		$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$count[$GLOBALS['TABLE_RESPONDER']] = mysql_num_rows($result_unit);

		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_unit))) {		
			do_prelim_marker ($in_row['lat'], $in_row['lng'], $layers[$GLOBALS['TABLE_RESPONDER']], $icons[$GLOBALS['TABLE_RESPONDER']], $GLOBALS['TABLE_RESPONDER'], $in_row['unit_id'], $side_bar_index);
			$side_bar_index++;
			}

		$_where_str = "WHERE (`hide` != 'n' {$not_me_str})";					// do the hidden units
		$query = x_get_resp_sql ($_where_str);
		$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$count[$GLOBALS['TABLE_RESPONDER_HIDE']] = mysql_num_rows($result_unit);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_unit))) {	
			do_prelim_marker ($in_row['lat'], $in_row['lng'], $layers[$GLOBALS['TABLE_RESPONDER_HIDE']], $icons[$GLOBALS['TABLE_RESPONDER_HIDE']], $GLOBALS['TABLE_RESPONDER_HIDE'], $in_row['unit_id'], $side_bar_index);
			$side_bar_index++;
			}

		if (intval ( $me ) > 0 ) {												// do 'me'
			$_where_str = "WHERE `r`.`id` = {$me}";	
			$query = x_get_resp_sql ($_where_str);
			$result_unit = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$count[$GLOBALS['ME']] = mysql_num_rows($result_unit);
			echo "\n\t var my_marker_index = {$side_bar_index};\n";		
			$in_row = stripslashes_deep ( mysql_fetch_assoc($result_unit) );
			$my_handle = $in_row['handle'];				// used on layers control
			do_prelim_marker ($in_row['lat'], $in_row['lng'], $layers[$GLOBALS['ME']], $icons[$GLOBALS['ME']], $GLOBALS['ME'], $in_row['unit_id'], $side_bar_index);
			$side_bar_index++;
			}			
//							 generate FACILITIES data
		$query = get_facy_sql($where_cl_facy_1);			
//		snap (__LINE__, $query );
		$result_facy = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$count[$GLOBALS['TABLE_FACILITY']] = mysql_num_rows($result_facy);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_facy))) {		
			do_prelim_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_FACILITY']], $icons[$GLOBALS['TABLE_FACILITY']], $GLOBALS['TABLE_FACILITY'], $in_row['facy_id'], $side_bar_index);
			$side_bar_index++;
			}

//							 generate INCIDENT data - sidebar data unused here
		$hide_limit = INTVAL ( get_variable('hide_booked') );		// hours embargoed

		$tick_where_cl_str = " WHERE ( ( (`t`.`status`='{$GLOBALS['STATUS_OPEN']}') 
							OR ( (`t`.`status`='{$GLOBALS['STATUS_SCHEDULED']}') AND 
							(`booked_date` <=  (NOW() + INTERVAL {$hide_limit} HOUR) ) ) ) {$where_cl_tick_1} )  
							";

		$query = get_local_tick_sql($tick_where_cl_str);
		$result_tick = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$count[$GLOBALS['TABLE_TICKET']] = mysql_num_rows($result_tick);
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
			do_prelim_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_TICKET']], $icons[$GLOBALS['TABLE_TICKET']], $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);
			$side_bar_index++;
			}

//							 generate NEARBY CLOSED INCIDENT data - sidebar data unused here
		$nearby_ok = ( ( array_key_exists("longitude", $_SESSION['SP'] ) ) && ( is_ok_position ( $_SESSION['SP'] ['latitude'] , $_SESSION['SP'] ['longitude'] ) ) ) ;

		if ( $nearby_ok ) { 	
				$where_cl_str = " WHERE ( ( ( `status` = {$GLOBALS['STATUS_CLOSED']} ) AND ( `lat` <> {$GLOBALS['NM_LAT_VAL'] } ) ) {$where_cl_tick_1} )";
				$query = get_local_tick_sql ($where_cl_str);
				snap(__LINE__, $query);
				$result_tick = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				if ($result_tick ) {
					$range = $ini_array['range'];				// distance in meters for 'nearby'
					while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_tick))) {
						$dist = my_gcd ( $in_row['lat'] , $in_row['lng'] , $_SESSION['SP']['latitude'], $_SESSION['SP']['longitude']);		// meters?
//						snap (__LINE__, $dist);			// 
						if ( abs ( $dist ) < $range ) { 
							do_prelim_marker ( $in_row['lat'], $in_row['lng'], $layers[$GLOBALS['TABLE_CLOSED']], $icons[$GLOBALS['TABLE_CLOSED']], $GLOBALS['TABLE_TICKET'], $in_row['tick_id'], $side_bar_index);			
							$count[$GLOBALS['TABLE_CLOSED']]++;
							}					
						$side_bar_index++;
						}
					}
				}
//							   generate ROADINFO data 
		$roadinfo_ok = mysql_table_exists("$GLOBALS[mysql_prefix]roadinfo") ;
		if ($roadinfo_ok) { 

			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]roadinfo`  LIMIT {$_limit}";
 			$result_road = mysql_query($query);					// possibly not defined
			$ct_r = mysql_num_rows($result_road);
 			if ( $result_road ) {
	 			$the_icon = $icons[$GLOBALS['TABLE_ROAD']];		
	 			while ($in_row = stripslashes_deep(mysql_fetch_assoc($result_road))) {			
					do_prelim_marker ($in_row['lat'], $in_row['lng'], 	$layers[$GLOBALS['TABLE_ROAD']], $icons[$GLOBALS['TABLE_ROAD']], $GLOBALS['TABLE_ROAD'], $in_row['id'], $side_bar_index);	
					$count[$GLOBALS['TABLE_ROAD']]++;
	 				$side_bar_index++;
	 				}
 				}		// end if ( $result_road ) 
 			}		// end if ($roadinfo_ok)
 			
			if ($target) {
				switch ($the_group) {
					case $GLOBALS['TABLE_TICKET']:
						$query = get_local_tick_sql (" WHERE ( `t`.`id` = {$the_id} ) ");
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$in_row = stripslashes_deep(mysql_fetch_assoc($result));
						$target_handle = $in_row['scope'];
						break;
						
					case $GLOBALS['TABLE_RESPONDER']:
						$query = x_get_resp_sql (" WHERE `r`.`id` = {$the_id} ");
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						$in_row = stripslashes_deep(mysql_fetch_assoc($result));
						$target_handle = $in_row['handle'];
						break;
						
					case $GLOBALS['TABLE_FACILITY']:
						$query = get_facy_sql (" WHERE `f`.`id`  = {$the_id} ");
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

//							end icon generation 

	if ( ! ( isset($my_handle) ) ) $my_handle = "Me";

	if ( $do_google ) {
		$gmap_types_arr = array ("", "ROADMAP", "SATELLITE", "TERRAIN", "HYBRID");
	
		$temp = intval (get_variable('maptype') );
		$type_str = ( ($temp > 0 ) && ( $temp <= 4 ) )?  $gmap_types_arr [$temp]:  $gmap_types_arr [4];

		$baselayer_call = "gglr";	
?>
		var gglr = new L.Google('<?php echo $type_str;?>');

<?php
		}		// end if ( do google )
	else {		// do OSM
		$baselayer_call = "OSM";
?>
		var in_local_bool = false;
		
		var osmUrl = (in_local_bool)?
			"../_osm/tiles/{z}/{x}/{y}.png":
			"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

	    var OSM   = 		L.tileLayer(osmUrl);
<?php
		$ini_array = parse_ini_file("./incs/sp.ini");
		$tiles_dir = ( ( array_key_exists ( "tiles_dir", $ini_array ) ) ) ? $ini_array ['tiles_dir'] : "../_osm/tiles/"; // 
	
		if ( file_exists( $tiles_dir ) ) {
		
			$minZoom = 18; $maxZoom = 0;			// set minZoom, maxZoom based on tiles directory contents
			$files = scandir($tiles_dir);
			for ($i=0; $i< count( $files ); $i++) {
				if ( ( intval ( $files[$i] ) > 0 ) && ( intval ( $files[$i] < 18 ) ) ) {
					$maxZoom = max (intval ( $files[$i]	) , intval ( $maxZoom ) );
					$minZoom = min (intval ( $files[$i] ) , intval ( $minZoom ) );
					}
				}
			}		// end if ($tiles_dir)
	
		}		// end 		// do OSM

	$targ_overlay = ($target)?  ", target" : "" ; 
?>
		var full_scr = 		false;

		var map = L.map('map', {
			center: [<?php echo get_variable('def_lat') ; ?>, <?php echo get_variable('def_lng') ; ?>],
			zoom: <?php echo get_variable('def_zoom') ; ?>,
//			layers: [OSM, gglr, gglh, ggls, gglt, me, incidents, units],
			layers: [ <?php echo $baselayer_call;?>, me <?php echo $targ_overlay;?>, roadinfo],
			fullscreenControl: full_scr
			});

		var baseLayers = {
<?php
	if ( array_key_exists ( "use_gmaps", $ini_array ) ) {
?>		
//			"G_hybrid": 		gglh,
//			"G_satellite": 		ggls,			
//			"G_terrain": 		gglt,			

			"Google": 			gglr
<?php
		}
	else {
?>
			"OSM": 				OSM
<?php
			}		// end else
?>			
			};		// end var baseLayers = ...

		var overlays = {
<?php
	echo "\t\t\t\"{$my_handle}\" : 				me";
	if ($target) 													{echo ",\n\t\t\t\"{$target_handle} \": target";}
	if (intval ($count[$GLOBALS['TABLE_RESPONDER']] ) > 0 ) 		{echo ",\n\t\t\t\"Units ({$count[$GLOBALS['TABLE_RESPONDER']]})\": units";}
	if (intval ($count[$GLOBALS['TABLE_RESPONDER_HIDE']] ) > 0 ) 	{echo ",\n\t\t\t\"Hidden ({$count[$GLOBALS['TABLE_RESPONDER_HIDE']] })\": hides";}
	if (intval ($count[$GLOBALS['TABLE_TICKET']] ) > 0 ) 			{echo ",\n\t\t\t\"Incidents ({$count[$GLOBALS['TABLE_TICKET']] })\": incidents";}
	if (intval ($count[$GLOBALS['TABLE_FACILITY']] ) > 0 ) 			{echo ",\n\t\t\t\"Facilities ({$count[$GLOBALS['TABLE_FACILITY']] })\": facilities";}
	if (intval ($count[$GLOBALS['TABLE_ROAD']] ) > 0 ) 				{echo ",\n\t\t\t\"RoadInfo ({$count[$GLOBALS['TABLE_ROAD']] })\": roadinfo";}
	if (intval ($count[$GLOBALS['TABLE_CLOSED']] ) > 0 ) 			{echo ",\n\t\t\t\"Nearby ({$count[$GLOBALS['TABLE_CLOSED']] })\": nearby";}
?>

			};
//		var m = L.marker([34.852618, -82.394010], {draggable:true}).	bindLabel('A sweet static label!', { noHide: true }).addTo(map).showLabel();

		function onMapClick(e) {
			$("map_menu").style.display = "block";			
			}

		function can_map_menu() {
			$("map_menu").style.display = "none";			
			$("map_menu_link").style.display = "block";			
			}

		map.on('contextmenu', onMapClick);

		var my_circle, radius;
/*
 	if (parent.frames["main"].move_circle()) {	}
*/
		function move_circle ( lat_in, lng_in ) {						// called from parent.frames["top"] on detecting motion
			console.log("function move_circle @ 42");
			my_circle.setLatLng([lat_in, lng_in]);						// the circle
			map.panTo(new L.LatLng(lat_in, lng_in ));					// map center on new position - ????
			markers_ary[my_marker_index].setLatLng([lat_in, lng_in]);	// the marker
			}			

		function getMapRadius () {		// returns distance map center to NE in meters
		    var mapBoundNorthEast = map.getBounds().getNorthEast();
		    return mapBoundNorthEast.distanceTo(map.getCenter());
			}
		if (!me_is_onscr) {
//			alert(414);
			my_bounds.extend( [<?php echo $my_position_arr[0];?>, <?php echo $my_position_arr[1];?>]);											// to bounding box
			}


		var t=setTimeout(function(){								// delay for map rendering
//			alert(421);
			L.control.layers(baseLayers, overlays).addTo(map);

			map.fitBounds(my_bounds);								// show the centered map					

			radius = Math.round(getMapRadius() * 0.20);				// arbitrary
			my_circle = L.circle( [<?php echo $my_position_arr[0];?>, <?php echo $my_position_arr[1];?> ], radius, { color: 'red', fill: false}).addTo(map);		// center circle on my position
			
			},1000) ;												// wait for rendering


		map.on('enterFullscreen', function(){		// detect fullscreen toggling
			if(window.console) window.console.log('enterFullscreen');
		});
		map.on('exitFullscreen', function(){
			if(window.console) window.console.log('exitFullscreen');
		});		

	function to_tickets () {
		if (typeof(parent.frames["top"].stop_cycle) === 'function') { parent.frames["top"].stop_cycle(); }
		document.toTickets.submit();
		}	

//	alert(569);
//	var m = L.marker([34.852618, -82.394010], {draggable:true}).	bindLabel('A sweet static label!', { noHide: true }).addTo(facilities).showLabel();

	</script>
<center>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()

	function allowDrop(ev) {
		ev.preventDefault();
		}
	
	function drag(ev){
		ev.dataTransfer.setData("Text",ev.target.id);
		}
	
	function drop(ev){
		ev.preventDefault();
		var data=ev.dataTransfer.getData("Text");
		ev.target.appendChild(document.getElementById(data));
		}

	function do_mail (in_addr) {
		document.mailform.mail_addr.value = in_addr;
		document.mailform.submit();
		}
</script>


<form name = "mailform" method = post 	action = "sp_mail.php?rand=<?php echo time();?>">
<input type = hidden name = "mail_addr" value = "" />			
</form>

<form name = "navForm" method = post action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = ""/>			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = ""/>
<input type = hidden name = "group" 	value = "" />
</form>

<form name = 'toTickets' method = 'post' action = 'totickets.php'></form>	

<table id="map_menu_link" width = auto cellpadding = 4 style="position: fixed; top: <?php echo round ( .012 * $_SESSION['SP']['scr_height'] );?>px; left: <?php echo round (.45 * $_SESSION['SP']['scr_width'] );?>px; display: block; ">
<tr><td  width = 50px align = center onclick = "$('map_menu_link').style.display = 'none'; $('map_menu').style.display = 'block'; "> Menu </td>
</tr></table>


<table id="map_menu" cellpadding = 4 style="position: fixed; top: <?php echo round ( .1 * $_SESSION['SP']['scr_height'] );?>px; left: <?php echo round (.25 * $_SESSION['SP']['scr_width'] );?>px; display: none; ">
<tr><td class = 'my_hover' onclick = 'do_stop_cycle(); navTo("sp_lout.php", "")'>	<?php echo get_text("Logout");?></td></tr>
<tr><td class = 'my_hover' onclick = 'can_map_menu();'>				Cancel</td></tr>
<tr><td class = 'my_hover' onclick = 'location.reload();'>			Map refresh</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_calls.php", "")'>	<?php echo get_text("Calls");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_tick.php", "")'>	<?php echo get_text("Incidents");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_resp.php", "")'>	<?php echo get_text("Responders");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_fac.php", "")'>		<?php echo get_text("Facilities");?></td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_mail.php", "")'>	<?php echo get_text("Email");?></td></tr>
<tr><td class = 'my_hover' onclick = 'to_tickets ();'>				to Tickets</td></tr>
<?php						// 12/16/2013
	if ($do_together) {
?>
<tr><td class = 'my_hover' onclick = 'TogetherJS(this); return false;'>	Together</td></tr>
<?php
	}
?>
</table>

</body>
</html>
