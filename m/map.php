  <!DOCTYPE html>
<html>
<head>
	<title>TicketsSP - sp_map.php</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=1377903649" />
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

<!--	https://gist.github.com/2197042 -->	
		
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
<body>	<!-- 75 -->
	<div id="map"></div>

	<script>
		var markers_work;
		var markers_ary = [];
		var id_array;

		var latest_position = new L.LatLng( 39.015049, -76.544774);
		var my_bounds = new L.LatLngBounds([39.015049, -76.544774], 
			[39.015049, -76.544774]);		

		DomReady.ready(function() {			//set initial bounds at map center	
			parent.frames["top"].document.getElementById("the_user").innerHTML = 		'admin';
			parent.frames["top"].document.getElementById("the_unit_id").innerHTML = 	'4';
			parent.frames["top"].document.getElementById("the_user_id").innerHTML = 	'1';
			parent.frames["top"].start_cycle();				

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
		var me_is_onscr = 	false;
	
	    markers_work = L.marker([39.103338, -76.706187], {icon: unitIcon}).bindLabel('!', { noHide: true }).addTo(units).showLabel();
		markers_work.addEventListener('click', function(e) { on_Click (0, 1, 53);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
		my_bounds.extend(markers_work.getLatLng());											// to bounding box
	
	    markers_work = L.marker([47.5427152, -94.8463287], {icon: facIcon}).bindLabel('!', { noHide: true }).addTo(facilities).showLabel();
		markers_work.addEventListener('click', function(e) { on_Click (1, 2, 47);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
		my_bounds.extend(markers_work.getLatLng());											// to bounding box
	
	    markers_work = L.marker([34.189086, -82.139282], {icon: tickIcon}).bindLabel('!', { noHide: true }).addTo(incidents).showLabel();
		markers_work.addEventListener('click', function(e) { on_Click (2, 0, 121);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
//		my_bounds.extend(markers_work.getLatLng());											// to bounding box
	
	    markers_work = L.marker([39.00838, -76.535311], {icon: closedIcon}).bindLabel('!', { noHide: true }).addTo(nearby).showLabel();
		markers_work.addEventListener('click', function(e) { on_Click (3, 0, 35);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
//		my_bounds.extend(markers_work.getLatLng());											// to bounding box
	
	    markers_work = L.marker([39.7674614, -94.8955521], {icon: roadIcon}).bindLabel('!', { noHide: true }).addTo(roadinfo).showLabel();
		markers_work.addEventListener('click', function(e) { on_Click (4, 4, 1);});			// click handler	    
		markers_ary.push(markers_work);														// indexed by $side_bar_index
//		my_bounds.extend(markers_work.getLatLng());											// to bounding box


		var my_Path = "http://127.0.0.1/_osm/";
		var in_local_bool = false;
		var osmUrl = (in_local_bool)?
			"../_osm/tiles/{z}/{x}/{y}.png":
			"http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";

	    var cmAttr = 'Map data &copy; 2011 OpenStreetMap contributors, Imagery &copy; 2011 CloudMade',
			cmUrl = osmUrl;

	    var OSM   = 		L.tileLayer(cmUrl);
		var gglr = 			new L.Google('ROADMAP');
		var full_scr = 		false;

		var map = L.map('map', {
			center: [34.852618, -82.394010],
			zoom: 8,
			layers: [ gglr, me, incidents, units],
			fullscreenControl: full_scr
			});

		var baseLayers = {
			"G_roads": 			gglr
			};

		var overlays = {
			"Me" : 				me,
			"Units (1)": 				units,
			"Incidents (1)": 			incidents,
			"Nearby (1)": 			nearby,
			"RoadInfo (1)": 			roadinfo,
			"Facilities (1)": 		facilities
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
			alert(403);
			my_circle.setLatLng([lat_in, lng_in]);						// the circle
			map.panTo(new L.LatLng(lat_in, lng_in ));					// map center on new position - ????
			markers_ary[my_marker_index].setLatLng([lat_in, lng_in]);	// the marker
			}			

		function getMapRadius () {		// returns distance map center to NE in meters
		    var mapBoundNorthEast = map.getBounds().getNorthEast();
		    return mapBoundNorthEast.distanceTo(map.getCenter());
			}
		if (!me_is_onscr) {
			my_bounds.extend( [39.015049, -76.544774]);											// to bounding box
			}


		var t=setTimeout(function(){								// delay for map rendering
			radius = Math.round(getMapRadius() * 0.05);				// arbitrary
			my_circle = L.circle( [39.015049, -76.544774 ], radius, { color: 'red', fill: false}).addTo(map);		// center circle on my position
			L.control.layers(baseLayers, overlays).addTo(map);

			map.fitBounds(my_bounds);								// show the centered map					
			
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
</script>

<form name = "navForm" method = post action = "sp_map.php">
<input type = hidden name = "id" 		value = ""/>			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = ""/>
<input type = hidden name = "group" 	value = "" />
</form>

<form name = 'toTickets' method = 'post' action = 'totickets.php'></form>	

<table id="context" cellpadding = 4 style="position: fixed; top: 83px; left: 367px; display: none; ">
<tr><td class = 'my_hover' onclick = 'do_stop_cycle(); navTo("sp_lout.php", "")'>	Logout</td></tr>
<tr><td class = 'my_hover' onclick = 'canContext();'>				Cancel</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_calls.php", "")'>	Calls</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_tick.php", "")'>	Incidents</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_resp.php", "")'>	Responders</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_fac.php", "")'>		Facilities</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_mail.php", "")'>	Email</td></tr>
<tr><td class = 'my_hover' onclick = 'navTo("sp_has.php", "")'>		HAS</td></tr>
<tr><td class = 'my_hover' onclick = 'to_tickets ();'>				to Tickets</td></tr>
<tr><td class = 'my_hover' onclick = 'location.reload();'>			Map refresh</td></tr>
<tr><td class = 'my_hover' onclick = 'window.history.back();'>		Back</td></tr>
</table>

</body>
</html>
