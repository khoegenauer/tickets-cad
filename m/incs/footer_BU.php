<?php
//	6/19/2013
	@session_start();											//
?>
<script>
	function do_show () {
		$("ts").style.display = "none";
		$("header").style.display = "inline-block"; 
		$("th").style.display = "inline-block";
		}
		
	function do_hide () {
		$("th").style.display = "none";
		$("header").style.display = "none"; 
		$("ts").style.display = "inline-block";
		}
	
</script>
</div> <!-- container bottom -->
<table border=0 cellspacing=0 cellpadding=0><tr><td align=center>
<div id = 'footer' style = 'text-align: center; position:fixed; bottom:0px;'>	<!-- see css default -->
<!--
	 <span id = 'ts' class='head_butt' style = 'display: none;'			onclick = 'do_show ()'>Show top</span>
	 <span id = 'th' class='head_butt' style = 'display: inline-block;'	onclick = 'do_hide ()'>Hide top</span><span class = 'butt-sep'>|</span>
-->	 
	 <span id = 'fm' class='head_butt' onclick = 'navTo("sp_map.php", "")'>Map</span>			<span class = 'butt-sep'>|</span>
     <span id = 'fc' class='head_butt' onclick = 'navTo("sp_calls.php", "")'>Calls</span> 		<span class = 'butt-sep'>|</span>
	 <span id = 'fi' class='head_butt' onclick = 'navTo("sp_tick.php", "")'>Incidents</span> 	<span class = 'butt-sep'>|</span>
	 <span id = 'fr' class='head_butt' onclick = 'navTo("sp_resp.php", "")'>Responders</span>	<span class = 'butt-sep'>|</span>
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_fac.php", "")'>Facilities</span>
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_mail.php", "")'>Mail</span>
	</div>
	</td></tr>
