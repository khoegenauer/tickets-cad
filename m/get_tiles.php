<?php
/*
dump(__LINE__);
dump($_POST);
is_writable ( string $filename )
file_exists ()
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');

//$tiles_dir = "..//_osm/tiles/";	

$ini_array = parse_ini_file("./incs/sp.ini");
$tiles_dir = ( array_key_exists ( "tiles_dir", $ini_array ) ) ? $ini_array ['tiles_dir'] : "../_osm/tiles/"; 
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo basename(__FILE__);?></title>
	<meta name="Description" CONTENT="OSM Tile Downloader">	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=1381887089" />
	<meta charset="utf-8" />
	<link rel="stylesheet" href=					"./dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href=	"./dist/leaflet.ie.css" /><![endif]-->
<?php
$err_msg = "";
if ( ! ( array_key_exists( "top_left_lat", $_POST ) ) ) {

if ( ! ( file_exists ($tiles_dir) ) ) {$err_msg = "Directory error - '_osm/tiles' absent from directory structure";}

else {
	if ( ! ( is_writable ($tiles_dir) ) ) {$err_msg = "Directory write permissions required";}
	}
if ( ( strlen ( trim ( $err_msg ) ) > 0 ) ) {
	require_once('incs/header.php');	
	echo "</head>\n<body>\n<center><h1 style = 'margin-top:200px;'>{$err_msg}</h1>\n<br /><br /><h3>Please correct and retry.</h3></body>\n</html>";
	}		// end if ( ! ( empty ($err_msg ) ) ) 
else {
?>
	<script src=									"./dist/leaflet.js"></script>
<!--
	<link rel="stylesheet" href=					"./dist/leaflet.fullscreen.css"/>
	<script src=									"./dist/Leaflet.fullscreen.js"></script>	
-->
	<link rel="stylesheet" href=					"./dist/leaflet.label.css" />
	<script src=									"./dist/leaflet.label.js"></script>
	<script src=									"./dist/Google.js"></script>
	<script src=									"./js/misc.js"></script>
	<script src=									"http://maps.google.com/maps/api/js?sensor=false&amp;v=3.2"></script>

<!--	https://gist.github.com/2197042 -->	
		
<script>
	function do_stop_cycle() {
		try   		{ parent.frames['top'].stop_cycle();  }
		catch(err)	{}
		}

	function chk1() {
		var msgp1 = "Note: map diagonal distance is ";
		var msgp2 = "  mi\n\nClick OK to proceed, or Cancel to revise";
		var r=confirm(msgp1 + getMapDiagMi () + msgp2);
		if (r)	{
			document.map_form.top_left_lat.value = map.getBounds().getNorthWest().lat.toFixed(6);
			document.map_form.top_left_lon.value = map.getBounds().getNorthWest().lng.toFixed(6);
			document.map_form.btm_rt_lat.value = map.getBounds().getSouthEast().lat.toFixed(6);
			document.map_form.btm_rt_lon.value = map.getBounds().getSouthEast().lng.toFixed(6);
			document.map_form.zoom_top.value = map.getZoom();					// e.g., 4
			$('one').style.display = "none";
			$('two').style.display = "block";
			}
		else	{return false;}
		}
		
	function chk2() {
		if (document.map_form.zoom_top.value >  map.getZoom() ) {
			alert("Invalid zoom levels require correction!");
			return false;
			}
		document.map_form.zoom_btm.value = map.getZoom();					// e.g., 12
		document.map_form.submit();		
		}

	function back() {
		$('one').style.display = "block";
		$('two').style.display = "none";
		}
		

	var map = L.map('map');

	function getMapDiagMix () {								// returns miles
		var getNorthWest = map.getBounds().getNorthWest();
		var mapDistance = getNorthWest.distanceTo(map.getBounds().getSouthEast());
		return Math.round(mapDistance * 0.000621371);		// 
		}
	</script>
<style>
	body { padding: 0; margin: 0; }
	html, body, #map {height: 100%; }
	</style>

</head>
<body>	<!-- <?php echo __LINE__ ;?> -->
<p>
<?php
	$text = (is_dir($tiles_dir) ) ? "add to tiles already downloaded" : "download tiles to your server";
?>
<div id = 'zero' style = "margin-left:50px; display:block;"> <h3>This page will <?php echo $text; ?> for <em>server-stored maps</em>. </h3>
</div>
<br />
<div id = 'one' style = "margin-left:50px; display:block;">Step 1:  <b>Zoom and pan map until <u>your area of interest</u> is centered within the map.  
	Then click &raquo; </b><input type = 'button' value = "here" onclick = "chk1();" />
</div>
<br />
<div id = 'two' style = "margin-left:50px; display:none;">
Step 2: <b><u>Zoom in</u> to the level that's practical for your operation.  
<p style = "margin-left: 40px;">(<i>Note that extra-close zoom can waste storage space - as well as make navigation inconvenient.</i>)</p>
<br /><span style = "margin-left:0px;">Then click &raquo;</b> <input type = 'button' value = "here" onclick = "chk2();" /></span>

<span style = "margin-left:60px">Or, <input type = 'button' value = "Back" onclick = "back ();" /></span>
</div>
</p>
<div id="map" style="margin-left:150px; overflow: none; width: <?php echo get_variable('map_width');?>px; height: <?php echo get_variable('map_height');?>px"></div>
	<script>
		var markers_work;
		var markers_ary = [];
		var id_array;

		DomReady.ready(function() {			//set initial bounds at map center	
			id_array = document.navForm.id_str.value.split(",");
			});		
	
//		var my_Path = "http://127.0.0.1/_osm/";
		var in_local_bool = false;
		var osmUrl = (in_local_bool)?
			"{$tiles_dir}{z}/{x}/{y}.png":
			"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

		var cmUrl = osmUrl;

	    var OSM   = 		L.tileLayer(cmUrl);		//
		
		map = L.map('map', {
			center: [<?php echo get_variable('def_lat');?>, <?php echo get_variable('def_lng');?>],
			zoom: <?php echo round(get_variable('def_zoom')/2);?>,
			layers: [OSM]
			});

		function onMapClick(e) {		// context click
			$("map_menu").style.display = "block";			
			}

		function can_map_menu() {
			$("map_menu").style.display = "none";			
			}

//		map.on('contextmenu', onMapClick);

		function getMapDiagMi () {								// returns miles
			var getNorthWest = map.getBounds().getNorthWest();
			var mapDistance = getNorthWest.distanceTo(map.getBounds().getSouthEast());
			return Math.round(mapDistance * 0.000621371);		// 
			}
		
		function onMapClick(e) {
		    alert("You clicked the map at " + e.latlng);
			}

		map.on('click', onMapClick);


	function to_tickets () {
		if (typeof(parent.frames["top"].stop_cycle) === 'function') { parent.frames["top"].stop_cycle(); }
		document.toTickets.submit();
		}	

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


<form name = "mailform" method = post 	action = "sp_mail.php?rand=1381887089">
<input type = hidden name = "mail_addr" value = "" />			
</form>

<form name = "navForm" method = post action = "sp_map.php">
<input type = hidden name = "id" 		value = ""/>			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = ""/>
<input type = hidden name = "group" 	value = "" />
</form>

<form name = 'toTickets' method = 'post' action = 'totickets.php'></form>	

<table id="map_menu" cellpadding = 4 style="position: fixed; top: 90px; left: 400px; display: none; ">
<tr><td class = 'my_hover' onclick = 'do_stop_cycle(); navTo("sp_lout.php", "")'>	Logout</td></tr>
<tr><td class = 'my_hover' onclick = 'can_map_menu();'>				Cancel</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_calls.php", "")'>	Calls</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_tick.php", "")'>	Incidents</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_resp.php", "")'>	Responders</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_fac.php", "")'>		Facilities</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_mail.php", "")'>	Email</td></tr>
<tr><td class = 'my_hover' onclick = 'to_tickets ();'>				to Tickets</td></tr>
<tr><td class = 'my_hover' onclick = 'location.reload();'>			Map refresh</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("get_tiles.php", "")'>	Tiles</td></tr>
</table>

<form name = "map_form" method = "post" action = "<?php echo basename(__FILE__);?>" >
<input type = "hidden" name = "top_left_lat" value = "" />
<input type = "hidden" name = "top_left_lon" value = "" />
<input type = "hidden" name = "btm_rt_lat" value = "" />
<input type = "hidden" name = "btm_rt_lon" value = "" />
<input type = "hidden" name = "zoom_top" value = "" />
<input type = "hidden" name = "zoom_btm" value = "" />
</form>
<?php
			}
		}		// end if ( ! ( array_key_exists( "top_left_lat", $_POST ) ) )
else {
//	dump ($_POST);
	error_reporting(E_ALL);	
	set_time_limit(1*60*60*24);		// one day

	extract ($_POST);

	$got_curl = function_exists("curl_init");	
	
	$base = "http://tile.openstreetmap.org";
//	$local = getcwd() . "/_osm/tiles/";
	$local =  $tiles_dir;
	$dir_ct = $file_ct = 0;
	
	function calc_tile_name ($zoom, $lat, $lon) {
		$xtile = floor((($lon + 180) / 360) * pow(2, $zoom));
		$ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
		return array($xtile, $ytile);
		}				// end function calc_tile_name ()
	
	function do_file ($dir, $subdir, $file) {					// ($zoom, $col, $row)
		global $got_curl, $base, $local, $dir_ct, $file_ct ;
		$my_addr = "{$local}/{$dir}/{$subdir}/{$file}.png";
		if (!(file_exists($my_addr))) {							// check for pre-existence
			$temp = rand ( 0, 2 );
			if ($temp == 0) {sleep(1);}
			$dirname = (string) "{$local}/{$dir}";
			if (!(file_exists($dirname))) {						// zoom directory
				$dir_ct++;
				mkdir($dirname) OR die(__LINE__);
				}
			$dirname = (string) "{$local}/{$dir}/{$subdir}";
			if (!(file_exists($dirname))) {		
				$dir_ct++;
				mkdir($dirname) OR die(__LINE__);
				}
		
			$url = "{$base}/{$dir}/{$subdir}/{$file}.png";
			if ($got_curl) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$the_tile = curl_exec ($ch);
				curl_close ($ch);
				}
			else {				// not CURL
				$the_tile = file_get_contents($url);
				}
		
			if ($fp = fopen($my_addr, 'wb')) {
				$file_ct++;
				fwrite ($fp, $the_tile);
				fclose ($fp);
				}		
			else {
				echo "error " . __LINE__ . "<br />";		// @fopen fails
				}
			}		// end if ()	
		}		// end function do_file ()	
	
	echo "<span style = 'margin-left:100px;margin-top 60px;'> Working . ";
	for ($zoom = $zoom_top; $zoom<=$zoom_btm;  $zoom++) {
		echo ". ";
		$temp = calc_tile_name ($zoom, $top_left_lat, $top_left_lon) ;		// get tile names for each zoom level
		$col_first = $temp[0];		// dir name
		$row_first = $temp[1];		// file name
		$temp = calc_tile_name ($zoom, $btm_rt_lat, $btm_rt_lon) ;
		$col_last = $temp[0];
		$row_last = $temp[1];		// file name
		for ($col = $col_first; $col<$col_last;  $col++) {	
			for ($row = $row_first; $row<$row_last;  $row++) {	
				do_file ($zoom, $col, $row);
//				echo "{$zoom} {$col} {$row}<br />";
				}		// end for ($row = ... )
			}		// end for ($col = ... )
		}		// end for ($zoom = ... )
		
	echo "<div style = 'margin-top:200px; margin-left:300px;'>";
	echo "<br />\n{$dir_ct} Directories created";
	echo "<br />\n{$file_ct} Files written";
	echo "<br />\n<br />\n<b>Finished!</b></div>";
	}		

require_once('incs/footer.php');	
$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>

<form name = "respForm" method = post 	action = "sp_resp.php?rand=<?php echo time();?>">
<input type = hidden name = "responder_id" 	value = "" />			
</form>

<form name = "tickForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "ticket_id" 	value = "" />			
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
