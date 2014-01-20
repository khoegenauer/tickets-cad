<?php
/*
6/19/2013
12/16/2013 if do_map added	
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);

@session_start();	
?>
<script>
    function do_mail(in_addr) {
        document.mailform.mail_addr.value = in_addr;
        document.mailform.submit();
        }

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
<!-- </div> --> <!-- container bottom -->
</p>
<br/><br/>
<div id = 'footer' style = 'position:fixed; bottom:0px; white-space:nowrap; '>
<table  class= 'footer' align=center border=0 cellpadding=0 cellspacing=0 width=100%><tr><td>
<?php
	if ( intval ( $_SESSION['SP']['do_map'] ) == 1 ) {		// 12/16/2013
?>
	 <span id = 'fm' class='head_butt' onclick = 'navTo("sp_map.php", "")'>Map<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>			
<?php
		}
$ini_arr = parse_ini_file ("incs/sp.ini");
?>		
     <span id = 'fc' class='head_butt' onclick = 'navTo("sp_calls.php", "")'>Calls<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span> 		
	 <span id = 'fi' class='head_butt' onclick = 'navTo("sp_tick.php", "")'>Incidents<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span> 	
	 <span id = 'fr' class='head_butt' onclick = 'navTo("sp_resp.php", "")'>Responders<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>	
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_fac.php", "")'>Facilities<span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_mail.php", "")'>Mail</span><span class = 'butt-sep'>&nbsp;&nbsp;|</span></span>
	 <span id = 'ff' class='head_butt' onclick = 'navTo("sp_log.php", "")'>Logs</span>
<?php
if	( array_key_exists ( "debug", $ini_arr ) ) {
?>
	 <span class = 'butt-sep'>&nbsp;&nbsp;|</span><span id = 'du' class='head_butt' onclick = 'navTo("dump.php", "")'>Dump</span>
<?php
	}
?>	

</td></tr></table>
</div>

<form name = "mailform" method = post 	action = "sp_mail.php?rand=<?php echo time();?>">
<input type = hidden name = "mail_addr" value = "" />
</form>
