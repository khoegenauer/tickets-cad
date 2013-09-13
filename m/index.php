<?php
/*
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);

require_once('../incs/functions.inc.php');	
require_once('incs/sp_functions.inc.php');

/*
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
if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {
	$mobile_browser++;
	}
if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
	$mobile_browser = 0;
	}
if ($mobile_browser > 0) {
   // do something
	}
else {
   // do something else
	}
*/
/*
css_day
css_night

page_background
normal_text
row_light
*/

if (count($_POST) == 0) {									// new?
	session_start();	
	$_SESSION  = array();								// force empty 
	$_SESSION['token'] = sha1(uniqid(rand(), true));	// login token
	get_session_css ("Day");							// set initial css sub-directory 

	$button_height = 50;		// height in pixels
	$button_width = 160;		// width in pixels
	$button_spacing = 4;		// spacing in pixels

?>
<head>
	<meta charset="utf-8" />
	<title>Tickets Mobile</title>
<!--
  <link rel="stylesheet"  type="text/css" href="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.css" />
  <script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.js"></script>
-->  
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />

	<style>
	td				{ font-size:1em; font-weight: bold;  font-family: arial, 'trebuchet ms', helvetica, sans-serif;}
	input.my_button { margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; } 
	input.login		{ font-size:1em;}
	</style>
	<script src="./js/misc.js" type="application/javascript"></script>
	<script type="application/javascript">
		DomReady.ready(function() {
			document.login.userid.focus() ;
			document.login.scr_width.value=screen.width;			// 1/23/10
			document.login.scr_height.value=screen.height;			
			});
		function validate(theForm) {
			if ( ( theForm.userid.value.trim().length == 0 ) || ( theForm.password.value.trim().length == 0 ) ) {
				alert( "Please enter userID and password" );
				return false;
				}
			else {
				theForm.submit() ;
				}
			}		// end function validate()			

		function quick_in() {
//			document.login.userid.value = document.login.password.value = 'admin';
			document.login.userid.value = 'ashore';
			document.login.password.value = 'pug2skim';
			document.login.submit();
			}

	</script>
</head>
<body>
<?php
	echo "\n<center><h2>" . get_variable('login_banner') . "</h2></center>\n";
?>
  <form name = "login" action = "<?php echo basename(__FILE__)	;?>" method = "post" >
  <input type = hidden name = "theHash" value="" />
  <input type = hidden name = "scr_width" value= "" />
  <input type = hidden name = "scr_height" value= "" />

<table align=center cellpadding = 8 border = 1>
<?php
	if (array_key_exists('message', $_GET)) {
		echo "\n<tr id = 'message_tr'  class = 'odd' ><th colspan=3 align = center><i>{$_GET['message']}</i></th></tr>\n";
		unset($_GET['message']); 
		}
?>
<tr class = 'even' valign = middle><td rowspan=4 align='center'><img src="images/t.png" BORDER=0 style = "padding:25px 25px 25px 25px; " onclick = "quick_in();"/></td><td>User ID:</td>
	<td><input class='login' type = "text" id = "userid" name = "userid"  value = "" size = 20 /></td></tr>
	<td  class='login'>Password:</td>
	<td><input class='login' type = "password"  name = "password" value=""  size = 20  /></td></tr>
<tr class = 'odd' ><td class='login'>Colors:</td>
	<td><input type="radio" NAME="day_night" VALUE="Day" checked />Day<input style = "margin-left:60px;" type="radio" name="day_night" value="Night">Night</td></tr>	
<tr class = 'even' ><td  class='login' colspan=2 align="center">
	<input type = "button" class = "my_button" value = " Log in" onclick = "validate(this.form);" />
	<input type = "button" class = "my_button" value = " Reset " onclick = "this.form.reset()" style = "margin-left:40px;" />
	</td></tr>
<tr class = 'odd' ><td colspan=3 align="center"><br /><br /><h2>Tickets SP Login</h2></td></tr>	
</table>
  </form>  <!-- login form -->

<?php
	}				// end if (empty($_POST))
// ==============================================	empty($_POST)	=====================================================
else {		// process $_POST
	$query 	= "SELECT * FROM `$GLOBALS[mysql_prefix]user` WHERE
				`user`  = '" . 		addslashes(trim($_POST['userid'])) . 	"' AND
				`passwd` = '" . 	md5(addslashes(trim($_POST['password']))) . 	"'		
				LIMIT 1";				
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);	
	@session_start();	

	if (mysql_num_rows($result)==1)  {
		$row = mysql_fetch_assoc ( $result );

		$token = md5($row['user'] . $row['passwd'] . $_SESSION['token']);		

		do_log($GLOBALS['LOG_SIGN_IN'], 0, 0, $row['id']);			// log it													

		$sid = session_id();							
		$browser = substr (checkBrowser(FALSE), 0, 40);		// field size limit
		$expiry = mysql_format_date(expires()) ;
		$now =  mysql_format_date(now());					// to string;
		$query = "UPDATE `$GLOBALS[mysql_prefix]user` SET 
			`sid` = '{$sid}', 
			`expires`= '{$expiry}', 
			`login` = '{$now}', 
			`_from`= '{$_SERVER['REMOTE_ADDR']}', 
			`browser` = '{$browser}'  
			WHERE `id` = {$row['id']} LIMIT 1";

		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		
		$_SESSION['id'] = 			$sid;
		$_SESSION['login_at'] = 	$now; 
		$_SESSION['expires'] = 		$expiry;
		$_SESSION['user_id'] = 		$row['id'];
		$_SESSION['user'] = 		$row['user'];				
		$_SESSION['level'] = 		$row['level']; 
		$_SESSION['user_unit_id'] = $row['responder_id'];		/* GT 0 if user has unit id */
		$_SESSION['scr_width'] = 	$_POST['scr_width'];		/* device dimensions */
		$_SESSION['scr_height'] = 	$_POST['scr_height'];		
		$_SESSION['day_night']	=	$_POST['day_night'];

		$_SESSION['header_height']	=	60;
		$_SESSION['footer_height']	=	60;
		$_SESSION['container_height'] = $_POST['scr_height'] - $_SESSION['header_height'] - $_SESSION['footer_height'];
					
		get_session_css ($_POST['day_night']);							// set css sub-directory 
		$_SESSION['next_pos_update'] =  ( intval($_SESSION['user_unit_id'] ) > 0 ) ?  
			mysql_format_date(now()-1) : 
			mysql_format_date(now() + (60*1000));		// do position update
		$_SESSION['internet'] = TRUE;                        		

		$_POST  = array();			// force empty 
		header("Location: ./sp_map.php");		 /* OK - redirect to map page */
		}
	else {
		$url = basename(__FILE__);
		$message = urlencode("Password-userid fails - retry?");
		header("Location: {$url}?message={$message}"); 			/* Redirect */
		exit;													/* force script termination. */
		}
	}
?>
</body>
</html>