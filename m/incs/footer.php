<?php
//	6/19/2013
	@session_start();											//
?>
<script>
	function do_mail (in_addr) {
		document.mailform.mail_addr.value = in_addr;
		document.mailform.submit();
		}
		
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
<!-- </div> --> <!-- container bottom -->
</p>
<br/><br/>
<div id= 'footer' style = 'position:fixed; bottom:0px; left:0px; white-space:nowrap; '>
<table align=center border=0 cellpadding=0 cellspacing=0 width=100%><tr><td>
<!--
	 <span id = 'ts' class='head_butt' style = 'display: none;'			onclick = 'do_show ()'>Show top</span>
	 <span id = 'th' class='head_butt' style = 'display: inline-block;'	onclick = 'do_hide ()'>Hide top</span><span class = 'butt-sep'>|</span>
-->	 
	 <span id = 'fm' class='head_butt' onclick = 'navTo("sp_map.php", "")'>Map<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>			
     <span id = 'fc' class='head_butt' onclick = 'navTo("sp_calls.php", "")'>Calls<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span> 		
	 <span id = 'fi' class='head_butt' onclick = 'navTo("sp_tick.php", "")'>Incidents<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span> 	
	 <span id = 'fr' class='head_butt' onclick = 'navTo("sp_resp.php", "")'>Responders<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>	
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_fac.php", "")'>Facilities<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_mail.php", "")'>Mail</span>
<!--
	 <span id = 'du' class='head_butt' onclick = 'navTo("dump.php", "")'>Dump</span>
-->	 	 
</td></tr></table>
</div>

<form name = "mailform" method = post 	action = "sp_mail.php?rand=<?php echo time();?>">
<input type = hidden name = "mail_addr" value = "" />			
</form>
