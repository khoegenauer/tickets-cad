<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tickets SP Calls</title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=1372849093" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="./js/misc.js" type="text/javascript"></script>

            <script>

            DomReady.ready(function () {
                var id_array = document.navForm.id_str.value.split(",");
//				var timer = setInterval(function () {getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute
                });

//			do_set_time("dispatched", {$the_id}, 0)
            function do_set_time(column, record_id, cell_id, function_id) {		// ajax call to set selected dispatch time

                function the_callback(req) {
                    document.getElementById(cell_id).innerHTML=req.responseText;
                    CngClass(cell_id, "bright");									// highlight for 2 seconds
                    setTimeout ( function () {  CngClass(cell_id, "plain")}, 3000);
                    }		// end function the_callback()

                var params = "the_column="+ column + "&record_id=" +record_id + "&function_id=" + function_id;		//
                var url = "./ajax/set_disp_times.php";
                 sendRequest(url,the_callback, params);		//  issue AJAX request
                 }		// end function do set_time

        </script>

        </head>
        <body onunload="do_unload ();">
AAAAAAAAA
<script>
var tdTaglist = document.getElementsByTagName("td");		// limit to td's - global

function sizer(multiplier) {
    if (document.body.style.fontSize == "") {
        document.body.style.fontSize = "1.0em";
        }
//	document.body.style.fontSize = parseFloat(document.body.style.fontSize) + (multiplier * 0.2) + "em";
    for (i=0; i< tdTaglist.length; i++) {
        document.getElementsByTagName("td").tdTaglist[i].style.fontSize = parseFloat(document.getElementsByTagName("td").tdTaglist[i].style.fontSize) + (multiplier * 0.2) + "em";
        }
    }			// end function sizer()

</script>
<div class = 'even' id='header' style = 'position:fixed; top:0; width:100%; background-color:#EFEFEF;  margin-top:0px; height:auto; text-align:center; vertical-align: middle;'>
    <span id = 'ht' class='logo' onClick = 'document/toTickets.submit();'> T </span>	<span class = 'butt-sep'>|</span>
    <span id = 'hc' class='head_butt'><b>The Minnesota DNR Comm..</b></span>
    <span class = 'butt-sep'>|</span>
<span id = 'hs' class='head_butt'  onClick = 'sizer(1)' ><b>+</b></span>
    <span class = 'butt-sep'>|</span>
    <span id = 'hs' class='head_butt'  onClick = 'sizer(-1)' ><b>-</b></span>
    <span class = 'butt-sep'>|</span>
    <span id = 'hd' class='head_butt'  onClick = "navTo ('sp_lout.php', null);" ><i>Logout</i></span>
    <form name = 'toTickets' method = 'post' action = 'totickets.php'></form>
    </div>

    <div id = 'container' style = 'width: 100%;  overflow: auto; margin-top:60px; height:705px'>

        <div style='float:left; '>
            <div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 50px; top:125px; margin-left:100px; font-size: 4.0em; opacity:0.50;"></div>
        </div>
        <div style='float:right; '>
            <div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;">&nbsp;&raquo;</div>
        </div>

<br/><br/><br/><br/><center><h2>Selected Responder (of 16 )</h2>
<div style = 'height:705px; width:auto; overflow: auto; width:880px;'><br /><table border=1>
<tr><td>Handle:</td><td>111-D4</td></tr>
<tr><td>Name:</td><td>111-Bemidji-D4-111</td></tr>
<tr><td>Type/status:</td><td>TP4 / available</td></tr>
<tr><td>Location:</td><td>2220 Bemidji Ave Bemidji MN</td></tr>
<tr><td>Description:</td><td>Make: Caterpillar
Model: D-4
VIN:
License:</td></tr>
<tr><td>Capabilities:</td><td>6 way blade with plow, enclosed cab.</td></tr>
</table>
</div><br/>BBBBBBBB</div> <!-- bottom of container -->
<script>
    function do_show() {
        $("ts").style.display = "none";
        $("header").style.display = "inline-block";
        $("th").style.display = "inline-block";
        }

    function do_hide() {
        $("th").style.display = "none";
        $("header").style.display = "none";
        $("ts").style.display = "inline-block";
        }

</script>
<div id = 'footer' style = 'width:100%; position:fixed; bottom :0; height:24px; text-align:center; vertical-align: baseline; background-color:#EFEFEF; }'>
<!--
     <span id = 'ts' class='head_butt' style = 'display: none;'			onclick = 'do_show ()'>Show top</span>
     <span id = 'th' class='head_butt' style = 'display: inline-block;'	onclick = 'do_hide ()'>Hide top</span><span class = 'butt-sep'>|</span>
-->
     <span id = 'fm' class='head_butt' onclick = 'navTo("sp_map.php", "")'>Map</span>			<span class = 'butt-sep'>|</span>
     <span id = 'fc' class='head_butt' onclick = 'navTo("sp_calls.php", "")'>Calls</span> 		<span class = 'butt-sep'>|</span>
     <span id = 'fi' class='head_butt' onclick = 'navTo("sp_tick.php", "")'>Incidents</span> 	<span class = 'butt-sep'>|</span>
     <span id = 'fr' class='head_butt' onclick = 'navTo("sp_resp.php", "")'>Responders</span>	<span class = 'butt-sep'>|</span>
     <span id = 'ff' class='head_butt' onclick = 'navTo("sp_fac.php", "")'>Facilities</span>	<span class = 'butt-sep'>|</span>
    </div>

<form name = "navForm" method = post 	action = "sp_resp.php">
<input type = hidden name = "id" 		value = "0" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "13,17,7,8,9,15,14,10,11,16,12,6,5,19,20,18" />
</form>
<script>
    function navTo(url, id) {
        var ts = Math.round((new Date()).getTime() / 1000);
        document.navForm.action = url +"?rand=" + ts;
        document.navForm.id.value = (id == null)? "": id;
        document.navForm.submit();
        }				// end function navTo ()
</script>
</body>
</html>
