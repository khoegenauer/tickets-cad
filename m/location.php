<?php	/* SP_KS */
/*
// alert("<?php echo __LINE__;?> ");

4/18/2013 - initial release
6/23/2013 - roadinfo added
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		//
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once('../incs/functions.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<head>
	<meta charset="utf-8" />
	<title><?php echo basename(__FILE__);?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 
	<script>
		function test() {
			alert("test 23 OK");
			}
	
		function show_msg() {
			// alert (27);
			}

		var gcpTimer;							// global
		var latest_position = new Array(0, 0);

		var cycle_seconds = 30000;	// 30 seconds
		
		var options = {
			enableHighAccuracy: false,
			timeout: 15000,				
			maximumAge: 10000					// use cache max ten seconds
			};

		function do_getCurrentPosition () {
			console.log("do_getCurrentPosition @ 42");
			// alert("do_getCurrentPosition @ 43");
			navigator.geolocation.getCurrentPosition(locationSuccess, locationFail, options);
			}		
		
		function start_cycle() {				//set initial bounds at map center
			console.log("start_cycle() @48");     
			// alert("start_cycle() @49");     
			try 		{ stop_cycle(); }
			catch(err)	{ alert("catch(err) @ 51") };			
			
			if (!gcpTimer) {					// cycling?
				gcpTimer = setInterval(do_getCurrentPosition, cycle_seconds);		// start cycle update
				do_getCurrentPosition ();		// initial one
				}
			}				// end function		

		function stop_cycle() {											// called from other frame
			console.log("stop_cycle() @ 60");     
			// alert("stop_cycle() @ 61");     
			clearInterval(gcpTimer);
			gcpTimer = null;
			}

		var report_fail = true;											// report initial failure
		var report_succ = true;											// either way
		
		function locationSuccess(position) {     
	     	if (report_succ) {
				console.log("Geo-location succeeds! @ 71");     
				// alert("Geo-location succeeds! @ 72");     
				report_fail = true;
				report_succ = false;
				}		
			function sp_LS_callback(req) {
				console.log("sp_LS_callback" +  req.responseText);			
				// alert("sp_LS_callback 74: " +  req.responseText);			
				}		// end function()

			var this_position = new Array ( position.coords.latitude, position.coords.longitude );			
			if ( JSON.stringify(this_position ) == JSON.stringify(latest_position ) ) { return; } 		// if position unchanged
			else {									// we have a  movement!
				console.log("we have a  movement! @ 84");
				// alert("we have a  movement! @ 85");
													// do we have a map?
				if (typeof(parent.frames["main"].move_circle) === 'function') { 
					console.log("88");
					// alert("89");

					try 		{ parent.frames["main"].move_circle ( position.coords.latitude, position.coords.longitude); } 
					catch(err)	{ alert("catch(err) @ 92") };

					latest_position = this_position;	
//					latest_position = new Array("","");
					var params="latitude=" + position.coords.latitude + "&longitude=" + position.coords.longitude + "&altitude=" + position.coords.altitude + "&heading=" + position.coords.heading + "&speed=" + position.coords.speed + "&timestamp=" + position.coords.timestamp;
					var url = "./ajax/set_position.php";
					console.log("ajax: " + params);
					// alert("ajax - 99: " + params);
					
					sendRequest( url, sp_LS_callback, params);					// update position data and track	
					}
				}
			}				// end function locationSuccess()
	     
	     function locationFail() {     
	     	if (report_fail) {
				console.log("Geo-location failed! 108");     
				// alert("Geo-location failed! 109 ");     
				report_fail= false;
				report_succ = true;
				}
			do_getCurrentPosition ();			// try again
			}

<?php
	$width = 800;
	$height = 600;
@session_start();
//	dump ($_SESSION);
if (array_key_exists('scr_width', $_SESSION['SP'])) {
	$width = round ( .75 * $_SESSION['SP']['scr_width'] );
	$height = round( .75 * $_SESSION['SP']['scr_height'] );
	}
?>
//		var map_2;
		function open_map_win() {
			alert(128);
			if (map_2) {map_2.focus();}
			else {map_2 = window.open("sp_map.php","Map 2", 
				width=<?php echo $width;?>, height=<?php echo $height;?>);
				}
			}		// end function open_map_win()
		

		DomReady.ready(function() {
			console.log("location.php DomReady");
			start_cycle();
			// alert("location.php DomReady - 138");
			});

</script>
</head>
<body style = 'margin-top: 0px;'>
<div id = 'the_unit_id' style = 'display:none;'></div> 	<!-- filled at login time : $("the_unit_id").innerHTML = tbd;  -->
<div id = 'the_user_id' style = 'display:none;'></div> 	<!-- filled at login time : $("the_user_id").innerHTML = tbd;  -->
<div id = 'the_user' style = 'display:none;'></div> 	<!-- filled at login time : $("the_user").innerHTML = tbd;  -->
</body>
</html>