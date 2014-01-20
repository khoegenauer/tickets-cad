<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		
error_reporting ( E_ALL ^ E_DEPRECATED );
/*
7/20/2013 - included HAS functions 
12/25/2013 - added location.reload()
*/
require_once('incs/sp_functions.inc.php');
if ( ! ( array_key_exists("css", $_SESSION['SP'] ) ) ) { get_session_css ("Day"); }		// possible  session conflict
?>
<script>

function sizer(multiplier) {							// work is done at callback
	var the_size;
	function GF_callback (req) {						// gets fontsize session variable
		the_size = (multiplier == "-") ? ( parseFloat(req.responseText) - parseFloat(0.25) ) : (parseFloat(req.responseText) + parseFloat(0.25)) ;		//
		if (the_size < 0.5 ) the_size = 0.5;		// enforce minimum

//	 	document.getElementsByTagName("table")[0].style.fontSize = new String( Math.round ( (the_size*10) / 10 ) ) + "em";	

//		try   		{ document.getElementsByTagName("table")[1].style.fontSize = new String( Math.round ( (the_size*10) / 10 ) ) + "em"; }
//		catch(err)	{ }
	 	
		var params="font_size=" + the_size;				// save it as session variable
		var url = "./ajax/set_font_size.php";
		sendRequest( url, SF_callback, params);			//
		}			// end function GF_callback() 

	function SF_callback (req) {						//
		document.navForm.submit();						// show font re-size
		}			// end function SF_callback ()

	var url = "./ajax/get_font_size.php";		// no params
	sendRequest( url, GF_callback, "");			// GF_callback() does the work	with retrieved font size
	}		// end function sizer()

function do_stop_cycle() {
	try   		{ parent.frames['top'].stop_cycle();  }
	catch(err)	{}
	}
	
</script>
<div id= 'header' style = 'position:fixed; top:0px; '>
	<span id = 'ht' class='logo' onClick = 'document.toTickets.submit();'> T </span>	<span class = 'butt-sep'>|</span>
	<span id = 'hc' class='head_butt'><b><?php echo  shorten(get_variable('login_banner'), 26);?></b></span>		
	<span class = 'butt-sep'>|</span>
<?php	
/*
	if (intval(get_variable('broadcast'))==1) {	
		echo "<span class = 'head_butt' onClick = 'do_broadcast();'>" . get_text('HAS') . "</span>\n";
		echo "<span class = 'butt-sep'>|</span>\n";
		}
*/
?>	
	<span id = 'hs' class='head_butt'  onClick = 'sizer("+")' >&nbsp;<b>+&nbsp;</b>&nbsp;</span>						
	<span class = 'butt-sep'>|</span>
	<span id = 'hs' class='head_butt'  onClick = 'sizer("-")' >&nbsp;<b>-</b>&nbsp;</span>						
	<span class = 'butt-sep'>|</span>
	<span id = 'hd' class='head_butt'  onClick = "do_stop_cycle(); navTo ('sp_lout.php', null);" ><i>Logout</i></span>

	<form name="reloader" >
	<form name = 'toTickets' method = 'post' action = 'totickets.php'></form>	
	</div>
<div id= 'has_in' style = 'display:none;'>		<!-- HAS input form -->
	<span id = 'has_span' >
	<form name = 'has_form' METHOD = post ACTION = 'javascript: void(0)'>
	<br/>
	<input type = 'text' NAME = 'has_text' ID = 'has_text' CLASS = '' size=90 value = '' STYLE = 'margin-left:6px;' placeholder='enter your broadcast message' />
	<button value='Send' 	onclick = 'has_check ( this.form.has_text.value.trim() )' STYLE = 'margin-left:16px;'>Send</BUTTON>
	<button value='Cancel' 	onclick = 'has_to_normal ();' STYLE = 'margin-left:24px;'>Cancel</BUTTON>
	</form>
	</span>			
	</div>

<div id = 'has_message' style = 'display: none;'>			<!-- HAS nessage display -->
	<span id = 'has_message_text' STYLE = 'margin-left:50px;'></span>
	<button onclick = 'end_message_show();' style = 'margin-left:20px'>OK</button>
	</div>
<p>	

<!-- <div id = 'container' style = 'width: 100%;  overflow: auto; margin-top:10px; height:auto'> -->
