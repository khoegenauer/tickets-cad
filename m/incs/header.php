<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );
require_once('incs/sp_functions.inc.php');
if ( ! ( array_key_exists("css", $_SESSION ) ) ) { get_session_css ("Day"); }		// possible  session conflict
?>
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
<?php
echo "<div class = 'even' id='header' style = 'position:fixed; top:0; width:100%; background-color:{$_SESSION['css']['page_background']};  margin-top:0px; height:auto; text-align:center; vertical-align: middle;'>
	<span id = 'ht' class='logo' onClick = 'document/toTickets.submit();'> T </span>	<span class = 'butt-sep'>|</span>
	<span id = 'hc' class='head_butt'><b>" . shorten(get_variable('login_banner'), 26) . "</b></span>		
	<span class = 'butt-sep'>|</span>\n";
if (intval(get_variable('broadcast'))==1) {	
		echo "<span class = 'head_butt' onClick = 'do_broadcast();'>" . get_text('HAS') . "</span>\n";
		echo "<span class = 'butt-sep'>|</span>\n";
	}
echo "<span id = 'hs' class='head_butt'  onClick = 'sizer(1)' ><b>+</b></span>						
	<span class = 'butt-sep'>|</span>
	<span id = 'hs' class='head_butt'  onClick = 'sizer(-1)' ><b>-</b></span>						
	<span class = 'butt-sep'>|</span>
	<span id = 'hd' class='head_butt'  onClick = \"navTo ('sp_lout.php', null);\" ><i>Logout</i></span>
	<form name = 'toTickets' method = 'post' action = 'totickets.php'></form>	
	</div>\n
	<div id = 'container' style = 'width: 100%;  overflow: auto; margin-top:10px; height:" . $_SESSION['container_height'] . "px'>
	\n";
?>