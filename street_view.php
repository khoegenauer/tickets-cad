<?php
include'./incs/error_reporting.php';
@session_start();
require_once($_SESSION['fip']);		//7/28/10
$api_key = get_variable('gmaps_api_key');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title>Streetview for Tickets</title>
	<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=<?php print $api_key; ?>"
			type="text/javascript"></script>
	<script type="text/javascript">

	function $() {									// 1/19/09
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


	var myPano;
	var lat = <?php print $_GET['thelat'];?>;
	var lng =  <?php print $_GET['thelng'];?>;
	function do_the_view() {
		var the_loc = new GLatLng(lat,lng);
		panoramaOptions = { latlng:the_loc 	};
		myPano = new GStreetviewPanorama(document.getElementById("pano"), panoramaOptions);
		GEvent.addListener(myPano, "error", handleNoFlash);
		}

	function handleNoFlash(errorCode) {				// 2/11/09
		if (errorCode == "600") {
			alert("No StreetView this location")
			self.close();
			}
		else {
			alert ("error code " + errorCode);
			self.close();
			}
		}			// end function handleNoFlash()

	</script>
	</head>
	<body onLoad = "do_the_view()" onUnload="GUnload()">
	<div name="pano" id="pano" style="width: 600px; height: 400px"></div>
	</body>
</html>
