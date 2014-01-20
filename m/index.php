<?php
/*
alert(<?php echo __LINE__;?>);

3/31/2013 initial release
8/9/2013 screen dimensions changed
12/18/2013 added target dependence on any open calls
12/24/2013 screen layout chnages per droid sp
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}	
error_reporting (E_ALL  ^ E_DEPRECATED);

$must_reach = '../incs/functions.inc.php';

if ( ! ( file_exists($must_reach) ) ) {  die  ("\n\n\n SP must be installed in a Tickets CAD sub-directory!");	}						// 7/5/2013

require_once('../incs/functions.inc.php');	
require_once('incs/sp_functions.inc.php');
@session_start();	

$mobile_browser = '0';
if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
	$mobile_browser++;
	}
if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
	$mobile_browser++;
	}
$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
$mobile_agents = array(
	'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
	'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
	'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
	'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
	'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
	'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
	'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
	'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
	'wapr','webc','winw','winw','xda ','xda-');
if (in_array($mobile_ua,$mobile_agents)) {
	$mobile_browser++;
	}
if (@strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {
	$mobile_browser++;
	}
if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
	$mobile_browser = 0;
	}

if ($mobile_browser > 0) {
//	echo $mobile_browser . "<br />\n";
   // do something
	}
else {
   // do something else
	}


if (count($_POST) == 0) {									// new?
	$_SESSION['SP']  = array();								// force empty 
	$_SESSION['SP']['token'] = sha1(uniqid(rand(), true));	// login token
	get_session_css ("Day");							// set initial css sub-directory 

	$button_height = 50;		// height in pixels
	$button_width = 160;		// width in pixels
	$button_spacing = 4;		// spacing in pixels

?>
<head>
	<meta charset="utf-8" />
	<title>Tickets SmartPhone Login</title>
<!--
  <link rel="stylesheet"  type="text/css" href="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.css" />
  <script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.js"></script>
-->  
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />

	<style>
	td				{ font-size:<?php echo $GLOBALS['FONT_SIZE'];?>em; font-weight: bold;  font-family: arial, 'trebuchet ms', helvetica, sans-serif;}
	input.my_button { margin-top: <?php echo $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; } 
	input.login		{ font-size:<?php echo $GLOBALS['FONT_SIZE'];?>em;}
	</style>
	<script src="./js/misc.js" type="application/javascript"></script>
	<script type="application/javascript">

// ------------------------- 	position functions		------------------------------
     
		var gcpTimer;							// global
		var latest_position = new Array(0, 0);

		var cycle_seconds = 30000;	// 30 seconds

		var options = {
			enableHighAccuracy: false,
			timeout: 15000,				
			maximumAge: 10000					// use cache max ten seconds
			};

		function do_getCurrentPosition () {
			console.log("do_getCurrentPosition @ 98");
			// alert("do_getCurrentPosition @ 99");
			navigator.geolocation.getCurrentPosition(locationSuccess, locationFail, options);
			}		
		
		function start_cycle() {				//
			console.log("start_cycle() @104");     
			// alert("start_cycle() @105");     
			try 		{ stop_cycle(); }
			catch(err)	{ alert("catch(err) @ 107") };			
			
			if (!gcpTimer) {					// cycling?
				gcpTimer = setInterval(do_getCurrentPosition, cycle_seconds);		// start cycle update
				do_getCurrentPosition ();		// initial one
				}
			}				// end function		

		function stop_cycle() {
			console.log("stop_cycle() @ 115");     
			// alert("stop_cycle() @ 116");     
			clearInterval(gcpTimer);
			gcpTimer = null;
			}

		var report_fail = true;											// report initial failure
		var report_succ = true;											// either way
		
		function locationSuccess(position) {     
	     	if (report_succ) {
				console.log("Geo-location succeeds! @ 127");     
				// alert("Geo-location succeeds! @ 128");     
				report_fail = true;
				report_succ = false;
				}		

			var this_position = new Array ( position.coords.latitude, position.coords.longitude );			
			if ( JSON.stringify(this_position ) == JSON.stringify(latest_position ) ) { return; } 		// if position unchanged
			else {									// we have a  movement!
				console.log("we have a  movement! @ 136");
				document.login.latitude.value =   position.coords.latitude.toFixed(6);
				document.login.longitude.value =  position.coords.longitude.toFixed(6);
				}
			}				// end function locationSuccess()
	     
	     function locationFail() {     
	     	if (report_fail) {
				console.log("Geo-location failed! 177");     
				// alert("Geo-location failed! 178 ");     
				report_fail= false;
				report_succ = true;
				}
			do_getCurrentPosition ();			// try again
			}

// ----------------------------------------------------------------------
		function validate (theForm) {
			if ( ( theForm.userid.value.trim().length == 0 ) || ( theForm.password.value.trim().length == 0 ) ) {
				alert( "Please enter userID and password" );
				return false;
				}
			else {
				stop_cycle();
				document.login.submit();
				}
			}		// end function validate()			

		function quick_in() {
			document.login.userid.value = 'admin';			// ashore
			document.login.password.value = 'admin';		// pug2skim
			$("login_v").style.display = 'none';
			$("login_b").style.display = 'inline';
			stop_cycle();
			document.login.submit();
			}

		DomReady.ready(function() {
			if (window.screen.width > window.screen.height ) {			// accommodate iphones, etc.
				document.login.scr_width.value=window.screen.width;	
				document.login.scr_height.value=window.screen.height;	
				}
			else {
				document.login.scr_width.value=window.screen.height;	
				document.login.scr_height.value=window.screen.width;	
				}

			document.getElementById("xwidth").innerHTML = 	document.login.scr_width.value;
			document.getElementById("xheight").innerHTML = 	document.login.scr_height.value;

			var elem = document.getElementById('header'),				// pull header and footer height values for 
			    properties = window.getComputedStyle(elem, null),		//	content box height determination
			    height = properties.height,
			    width = properties.width;
				document.login.header_height.value = Math.ceil(parseFloat(height));	

			var elem = document.getElementById('footer'),
			    properties = window.getComputedStyle(elem, null),
			    height = properties.height,
			    width = properties.width;
				document.login.footer_height.value = Math.ceil(parseFloat(height));	

			document.login.userid.focus() ;			
			});


	</script>
<?php
$ini_array = parse_ini_file("./incs/sp.ini");
$do_together = 	( array_key_exists ( "use_togetherjs", $ini_array ) );
$use_login_togetherjs = 	( array_key_exists ( "use_login_togetherjs", $ini_array ) );

	if ($do_together) {
?>
	<script src= "https://togetherjs.com/togetherjs-min.js"></script>
<?php
	}
?>
</head>
<!--
<body onload = "console.log(typeof (parent.frames['top'].name));">
-->
<body>
<div id = 'invis_header' style = 'visibility:hidden;'>
<?php
	require_once('incs/header.php');	// used only to measure height

?>
</div>
<center>
  <span class='head_butt' ><h1><?php echo shorten(get_variable('login_banner'), 36);?></h1></span>		
  <form name = "login" action = "<?php echo basename(__FILE__)	;?>" method = "post" >
  <input type = hidden name = "theHash" value="" />
  <input type = hidden name = "scr_width" value= "" />
  <input type = hidden name = "scr_height" value= "" />
  <input type = hidden name = "header_height" value= "" />
  <input type = hidden name = "footer_height" value= "" />

  <input type = hidden name = "latitude" 	id = "latitude" 	value = "" />
  <input type = hidden name = "longitude" 	id = "longitude" 	value = "" />
  <input type = hidden name = "altitude" 	id = "altitude" 	value = "" />
  <input type = hidden name = "heading" 	id = "heading" 		value = "" />
  <input type = hidden name = "speed" 		id = "speed" 		value = "" />
  <input type = hidden name = "timestamp" 	id = "timestamp" 	value = "" />

<table align=center cellpadding = 8 border = 1>
<?php
	if (array_key_exists('message', $_GET)) {
		echo "\n<tr id = 'message_tr'  class = 'odd' ><th colspan=3 align = center><i>{$_GET['message']}</i></th></tr>\n";
		unset($_GET['message']); 
		}
?>
<tr class = 'even' valign = middle><td rowspan=5 align='center'>
		<img src="images/t.png" BORDER=0 style = "padding:25px 25px 25px 25px; " onclick = "quick_in();"/>
		</td>
		<td>User ID:</td>
	<td><input class='login' type = "text" id = "userid" name = "userid"  value = "" size = 20 /></td></tr>
	<td  class='login'>Password:</td>
	<td><input class='login' type = "password"  name = "password" value=""  size = 20  /></td></tr>
<tr class = 'odd' ><td class='login'>Colors:</td>
	<td><input type="radio" NAME="day_night" VALUE="Day" checked />Day<input style = "margin-left:60px;" type="radio" name="day_night" value="Night">Night</td></tr>	
<tr class = 'even' ><td class='login'>Maps:</td>
	<td><input type="radio" NAME="do_map" VALUE="y" checked />Yes<input style = "margin-left:60px;" type="radio" name="do_map" value="n">No</td></tr>	
<tr class = 'odd' style = 'height:60px;vertical-align:middle;'><td  class='login' colspan=2 align="center" style = 'width:420px;'>
	<span id = 'login_v' style = 'display:inline; white-space:nowrap;'>
	<input type = "button" class = "my_button" value = " Log in" onclick = "validate(this.form);" />
	<input type = "button" class = "my_button" value = " Reset " onclick = "this.form.reset()" style = "margin-left:40px;" />
	</span><span id = 'login_b' style = 'display:none;'></span>
	</td></tr>
<tr class = 'even' ><td colspan=3 align="center"><br/><h2>Tickets SP Login</span>			
			</h2><span class='tiny'>12/24/2013</span></td></tr>	
			
<?php						// 12/16/2013
	if ($use_login_togetherjs) {
?>
<tr class = 'even' ><td colspan=3 align="center"><input type = "button" class = "my_button" value = "Together" onclick = "TogetherJS(this);" /></td></tr>
<?php
	}
?>			
			
<tr class = 'odd' ><td colspan=3 align = center  >width:<span id = "xwidth"></span> height:<span id = "xheight"></span></td></tr>			
</table>
  </form>  <!-- login form -->

<?php
	}				// end if (empty($_POST))
// ==============================================	populated ($_POST)	=====================================================
else {		// process $_POST

	$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE
				`user`  = '" . 		addslashes(trim($_POST['userid'])) . 	"' AND
				`passwd` = '" . 	md5(addslashes(trim($_POST['password']))) . 	"'		
				LIMIT 1";				
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	

	if (mysql_num_rows($result)==1)  {
		$row = mysql_fetch_assoc ( $result );

		$token = md5($row['user'] . $row['passwd'] . $_SESSION['SP']['token']);		

		sp_do_log($GLOBALS['LOG_SIGN_IN'], 0, 0, $row['id']);			// log it													

		$sid = session_id();							
		$browser = substr (checkBrowser(FALSE), 0, 40);		// field size limit
		$expiry = mysql_format_date(expires()) ;			// now() + $GLOBALS['SESSION_TIME_LIMIT']
		$now =  mysql_format_date(now());					// to string;
		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET 
			`sid` = '{$sid}', 
			`expires`= '{$expiry}', 
			`login` = '{$now}', 
			`_from`= '{$_SERVER['REMOTE_ADDR']}', 
			`browser` = '{$browser}'  
			WHERE `id` = {$row['id']} LIMIT 1";

		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$_SESSION['SP']['latitude'] = 	get_variable('def_lat');	// pre-set default position values
		$_SESSION['SP']['longitude'] =	get_variable('def_lng');			
		
		if (intval($row['responder_id']) > 0 ) {		//  responder_id ?
			$query_unit = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `id`  = '{$row['responder_id']}' LIMIT 1";				
			$result_unit = mysql_query($query_unit) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			if (mysql_num_rows($result_unit)==1)  {		
				$row_unit = mysql_fetch_assoc ( $result_unit );
				if ( is_ok_position ( $row_unit['lat'] , $row_unit['lng'] ) ) {		// obtain from responder data
					$_SESSION['SP']['latitude'] = 	$row_unit['lat'];		
					$_SESSION['SP']['longitude'] =	$row_unit['lng'];
					}
				}
			}

		$_SESSION['SP']['id'] = 		$sid;
		$_SESSION['SP']['login_at'] = 	$now; 
		$_SESSION['SP']['expires'] = 	$expiry;
		$_SESSION['SP']['user_id'] = 	$row['id'];
		$_SESSION['SP']['user'] = 		$row['user'];				
		$_SESSION['SP']['level'] = 		$row['level']; 
		$_SESSION['SP']['user_unit_id'] = $row['responder_id'];		/* default 0, >0 if user has unit id */
		$_SESSION['SP']['scr_width'] = 	$_POST['scr_width'];		/* device dimensions */
		$_SESSION['SP']['scr_height'] = $_POST['scr_height'];		

//		$_SESSION['SP']['browser'] = $_POST['mobile_browser'];		
		
//		$_SESSION['SP']['latitude'] = 	$_POST['latitude'];		
//		$_SESSION['SP']['longitude'] =	$_POST['longitude'];
		$_SESSION['SP']['altitude'] =	$_POST['altitude'];
		$_SESSION['SP']['heading'] =	$_POST['heading'];
		$_SESSION['SP']['speed'] =		$_POST['speed'];
		$_SESSION['SP']['timestamp'] =	$_POST['timestamp'];
		
		$_SESSION['SP']['day_night'] =	$_POST['day_night'];

		$_SESSION['SP']['header_height']	= $_POST['header_height'];
		$_SESSION['SP']['footer_height']	= $_POST['footer_height'];
		$_SESSION['SP']['container_height'] = $_POST['scr_height'] - 150 - $_SESSION['SP']['header_height'] - $_SESSION['SP']['footer_height'];
		
	if ( $_SESSION['SP']['container_height'] < $_SESSION['SP']['footer_height'] ) {				// ?? 12/24/2013
		$_SESSION['SP']['header_height']	=  round ( ( .2 * $_POST['scr_height'] ), 0);
		$_SESSION['SP']['footer_height']	=  round ( ( .3 * $_POST['scr_height'] ), 0);
		$_SESSION['SP']['footer_height']	= $_POST['footer_height'];
		$_SESSION['SP']['container_height'] = $_POST['scr_height'] - $_SESSION['SP']['header_height'] - $_SESSION['SP']['footer_height'];
		}				
					
		get_session_css ($_POST['day_night']);							// set css sub-directory 
		$_SESSION['SP']['next_pos_update'] =  ( intval($_SESSION['SP']['user_unit_id'] ) > 0 ) ?  // is user associated with response unit?
			mysql_format_date(now()-1) : 
			mysql_format_date(now() + (60*1000));		// do position update
		$_SESSION['SP']['internet'] = TRUE;                        		
		$_SESSION['SP']['map_type'] = 0;				// default is OSM
		$_SESSION['SP']['do_map']	= ( $_POST['do_map'] == "y" ) ?  1 : 0 ;
		$_SESSION['SP']['tickets_version'] = get_variable('_version');
		
														// 9/8/2013
		$query = "SELECT MAX(`id`) AS `max_id` FROM `$GLOBALS[mysql_prefix]ticket` WHERE status != '$GLOBALS[STATUS_RESERVED]'";
		$result_max = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows($result_max)==1)  {		
			$row_tick = mysql_fetch_assoc ( $result_max );
			$_SESSION['SP']['last_ticket'] = $row_tick['max_id'];
			}
		else {$_SESSION['SP']['last_ticket'] = 0;}
		
		$query = "SELECT MAX(`when`) AS `last_disp` FROM `$GLOBALS[mysql_prefix]log` WHERE (`code` IN 
						('{$GLOBALS['LOG_CALL_DISP']}', '{$GLOBALS['LOG_CALL_RESP']}', '{$GLOBALS['LOG_CALL_ONSCN']}', 
						'{$GLOBALS['LOG_CALL_U2FENR']}', '{$GLOBALS['LOG_CALL_U2FARR']}')
						)";

		$result_max = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		if (mysql_num_rows($result_max)==1)  {		
				$row_disp = mysql_fetch_assoc ( $result_max );
				$_SESSION['SP']['last_disp'] = $row_disp['last_disp'];		// note ts time format
				}
		else {$_SESSION['SP']['last_dispatch'] = 0;}		

		$ini_arr = parse_ini_file ("incs/sp.ini");		
		$_SESSION['SP']['font_size'] = $ini_arr['def_fontsize'];;	// EM units string from ini

		$_POST  = array();			// force empty 

		$query = "SELECT `id` FROM `$GLOBALS[mysql_prefix]assigns` `a`
			WHERE	(`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00' )
			LIMIT 1";
		$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		$target = (  mysql_num_rows($result)  == 1 ) ? 			// any open calls? - 12/18/2013
			"./sp_calls.php?map=1&rand=" . time() :				// calls
			"./sp_resp.php?map=1&rand=" . time();				// responders

		header("Location: {$target}");				 /* OK - redirect to 1st page */
		}
	else {
		$message = urlencode("Password-userid fails - retry?");
		$url = basename(__FILE__);
		header("Location: {$url}?message={$message}"); 			/* Redirect */
		exit;													/* force script termination. */
		}
	}
?>
<div id = 'invis_footer' style = "visibility:hidden;">
<?php
	require_once('incs/footer.php');		// used only to measure height
?>

</div>
</body>
<!-- screen.availWidth and screen.availHeight 1600/900  -->
</html>