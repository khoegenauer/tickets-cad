<?php
/*
5/21/2013 initial release - useage: inside the page <head> "require_once('./incs/socket2me.inc.php');"
5/27/2013 removed user_id prepend
6/3/2013 revised js source per AH email
*/

if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
@session_start();
$user_id = ( ( array_key_exists ( 'SP' , $_SESSION ) ) && (array_key_exists('user_id', $_SESSION['SP'] ) ) )? $_SESSION['SP']['user_id'] : "";

if ( ( intval ( get_variable ('broadcast')==1 ) ) &&  ( intval ( get_variable ('internet')==1 ) ) ) {	// 8/20/2013

?>
	<script src="easyWebSocket.min.js"></script>	<!-- 6/3/2013 -->
	<script>		// 8/10/2013

	var socket = new EasyWebSocket('ws://<?php echo "{$host}{$uri}"?>/');		// instantiate

	function get_user_id() {
		return parent.frames['top'].$("the_user").innerHTML;
		}				// end function get_user_id()	
	    
	 socket.onmessage = function(event) {					// on incoming
	 	var ourArr = event.data.split("/");
	 	var temp = get_user_id();
	 	if (ourArr[0] != temp ) {							// is this mine?
	 		var payload = ourArr.slice(1);					// no, drop user_id segment before showing it
	 		payload = payload.join ("/");					// array back to string

			if ( (window.opener) && (window.opener.parent.frames["upper"] ) ) 			// in call board?
				{ window.opener.parent.frames["upper"].show_has_message(payload); }	// call the function() there
			else {
				if ( parent.frames["upper"])	{ parent.frames["upper"].show_has_message(payload); }						
				else						{ show_has_message(payload); }
				}		// end else		

			do_audio();										// invoke audio function in top
			}				// end mine?
		}				// end incoming

	function broadcast( theMessage ) {
	    	var temp = get_user_id();
			var outStr = temp + "/" + theMessage;
	    	socket.send(outStr);		
	    	}		// end function broadcast ()

		function do_audio()	{
			if (typeof(do_audible) == "function") {do_audible();}					// if in top
			else if ( (window.opener) && ( window.opener.parent.frames["upper"] ) )
			  	{ window.opener.parent.frames["upper"].do_audible(); }				// if in lower frame
			else	{ parent.frames["upper"].do_audible();	}						// if in board 
			}		// end function do_audio()
	</script>
<?php
	}
?>	