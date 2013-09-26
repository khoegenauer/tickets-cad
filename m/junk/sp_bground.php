<?php
/*
		document.login.latitude.value = 	position.coords.latitude;     
		document.login.longitude.value = 	position.coords.longitude;     
		document.login.altitude.value = 	position.coords.altitude;     
		document.login.heading.value = 		position.coords.heading;     
		document.login.speed.value = 		position.coords.speed;     
		document.login.timestamp.value =	position.coords.timestamp;     
*/
/*
*/
error_reporting(E_ALL);	
require_once('../incs/functions.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php echo basename(__FILE__);?></TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<script src="./js/misc.js" type="application/javascript"></script>
<script type="application/javascript">
     
	function locationSuccess(position) {     
			alert(<?php echo __LINE__;?>);					// disregard return value
			function sp_callback(req) {
				alert(<?php echo __LINE__;?>);					// disregard return value
				}		// end function()
			var params="latitude=" + position.coords.latitude + "&longitude=" + position.coords.longitude + "&altitude=" + position.coords.altitude + "&heading=" + position.coords.heading + "&speed=" + position.coords.speed + "&timestamp=" + position.coords.timestamp;
			alert(<?php echo __LINE__;?>);
			alert("<?php echo __LINE__;?> " + params);
			var url="./ajax/set_position.php";
			sendRequest( url, sp_callback, params);		//  update position data and track
			}		// end function locationSuccess()
     
     function locationFail() {     
		alert("Geo-location failure!");     
		}

	function do_getCurrentPosition () {
		alert(<?php echo __LINE__;?>);					// disregard return value
		navigator.geolocation.getCurrentPosition(locationSuccess, locationFail);
		}		

	var gcpTimer;

	navigator.geolocation.getCurrentPosition(locationSuccess, locationFail);
		
	DomReady.ready(function() {
		do_getCurrentPosition () ;
		gcpTimer = setInterval(do_getCurrentPosition, 60000);		// once per minute		
		});

</script>
</HEAD>
<BODY>

</BODY>
</HTML>
