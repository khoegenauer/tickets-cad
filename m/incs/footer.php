<?php
//	6/19/2013
	@session_start();											//
?>
</div> <!-- bottom of container -->
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
<div id = 'footer' style = 'width:100%; position:fixed; bottom :0; height:24px; text-align:center; vertical-align: baseline; background-color:<?php echo "{$_SESSION['css']['page_background']}; }"?>'>
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

