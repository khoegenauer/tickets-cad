<?php
/*
9/12/2013 corrected notifies sql
*/
if ( !defined ( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL	^ E_DEPRECATED);
require_once('../incs/functions.inc.php');
require_once('incs/sp_functions.inc.php');
/*
array(5) {
  ["frm_add_str"]=>
  string(37) "1221102@gmail.com|adsocmvhf@gmail.com"
  ["cb{(0+1)}"]=>
  string(17) "1221102@gmail.com"
  ["cb{(1+1)}"]=>
  string(19) "adsocmvhf@gmail.com"
  ["frm_subj"]=>
  string(3) "bbb"
  ["frm_text"]=>
  string(3) "vvv"
}
dump($_POST);
*/

@session_start();
if (empty($_SESSION['SP'])) {
	header("Location: index.php");
	}
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP Mail</title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="./js/misc.js" type="text/javascript"></script> 
<?php
require_once('incs/header.php');

if ( ! ( array_key_exists ( "frm_add_str", $_POST ) ) ) { 	// ========================	TOP	==========================

	$query = "
		(SELECT 'u' AS `source`, `user`, `email`, `email_s` 
			FROM `$GLOBALS[mysql_prefix]user` WHERE 
		(`email` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') OR 
		(`email_s` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') )
	UNION 
		(SELECT 'r' AS `source`,`handle` AS `user`, `contact_via` AS `email`, `other` AS `email_s`   
			FROM `$GLOBALS[mysql_prefix]responder` 
		WHERE (`contact_via` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') )
	UNION 
		(SELECT 'n' AS `source`,`u`.`user` AS `user`, `n`.`email_address` AS `email`, NULL AS `email_s`   
			FROM `$GLOBALS[mysql_prefix]notify` `n`
			LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON `n`.`user` = `u`.`id`
		WHERE (`email_address` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') )		
	UNION 
		(SELECT 'c' AS `source`,`name` AS `user`, `email` AS `email`, `other` AS `email_s`   
			FROM `$GLOBALS[mysql_prefix]contacts` WHERE 
		(`email` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') OR 
		(`other` 	REGEXP '^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$') )
		
		ORDER BY `source` ASC, `user` ASC" ;

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$addresses = array();		//

	while($row = stripslashes_deep(mysql_fetch_assoc($result), MYSQL_ASSOC)){
	
		$temp = filter_var ($row['email'], FILTER_SANITIZE_EMAIL )  ;
		if ( $temp ) {
			$addresses[$temp] = $row['user'];		// given addr gets name
			}	
		$temp = filter_var ($row['email_s'], FILTER_SANITIZE_EMAIL )  ;
		if ( $temp ) {
			$addresses[$temp] = $row['user'];		// given addr gets name
			}	
		}				// end while( $row... )

	ksort  ( $addresses );							// order by address		

?>
<SCRIPT> 

	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
		};

	function do_submit() {
		if (document.mail_form.frm_text.value.trim()=="") {
			alert ("Message text is required");
			document.mail_form.frm_text.focus();
			return false;
			}
		var sep = "";
		var j = 0;
		for (i=0;i<document.mail_form.elements.length; i++) {
			if((document.mail_form.elements[i].type =='checkbox') && (document.mail_form.elements[i].checked)){		// 
				document.mail_form.frm_add_str.value += sep + document.mail_form.elements[i].value;
				j++;
				sep = "|";
				}
			}
		if (document.mail_form.frm_add_str.value.trim()=="") {
			alert ("Addressees required");
			return false;
			}
		else {		
			var temp = document.mail_form.incidents.options.selectedIndex;
			if (temp > 0 ) {	//	"http://maps.google.com/?q=loc:latitude,longitude\n";	
				var map_url = "http://maps.google.com/?q=loc:" + document.mail_form.incidents.options[temp].value;		// append lat/lmg
				document.mail_form.frm_text.value += "\nMap: " + map_url + "\n";
//				alert(document.mail_form.frm_text.value);
				}		
			document.mail_form.submit();	
			}				// end else
		}
	
	function do_clear(){
		for (i=0;i<document.mail_form.elements.length; i++) {
			if(document.mail_form.elements[i].type =='checkbox'){
				document.mail_form.elements[i].checked = false;
				}
			}		// end for ()
		$('clr_spn').style.display = "none";
		$('chk_spn').style.display = "inline-block";
		}		// end function do_clear

	function do_check(){
		for (i=0;i<document.mail_form.elements.length; i++) {
			if(document.mail_form.elements[i].type =='checkbox'){
				document.mail_form.elements[i].checked = true;
				}
			}		// end for ()
		$('clr_spn').style.display = "inline-block";
		$('chk_spn').style.display = "none";
		}		// end function do_clear

	</SCRIPT>
	</HEAD>

<?php		
	if(count($addresses)>0) {
?>
	<BODY>
	<CENTER>
	<p><br/><br/><br/>

		<FORM NAME='mail_form' onsubmit = 'do_submit(); return false;' METHOD='post' ACTION='<?php echo basename(__FILE__); ?>'>
		<INPUT TYPE='hidden' NAME='frm_add_str' VALUE=''>	<!-- for pipe-delim'd addr string -->
<?php		
		echo "<TABLE ALIGN = 'center' BORDER=0><tr>\n";
		if(count($addresses)> 4) {
			echo "<tr valign = bottom><td colspan = 4 align = 'center'><SPAN ID='clr_spn' STYLE = 'display:inline-block' onClick = 'do_clear()'>&raquo; <U>Un-check all</U></SPAN>
				<SPAN ID='chk_spn' STYLE = 'display:none'  onClick = 'do_check()'>&raquo; <U>Check all</U></SPAN></td></tr>\n";
			}

		$i=0;
		foreach ($addresses as $key => $val) {

			if  ( array_key_exists ( 'mail_addr', $_POST ) ) {
				$checked = ($_POST['mail_addr'] == $key ) ? "CHECKED" : "";		// singleton?
				}
			else {
				$checked = "CHECKED";
				}
			
			echo "<TD ALIGN='right'><INPUT TYPE='checkbox' {$checked} NAME='cb{($i+1)}' VALUE='{$key}'> </TD>
				<TD>&nbsp;&nbsp;{$key}&nbsp; (<I>" . shorten ($val, 12) . "</I>)</TD>";
			$i++;
			if(!($i&1)) {echo "</TR>\n\t<TR>";}		// if even
			}		// end foreach()
		echo "</TR>";
?>	
		<TR><TD><b>Subject</b>: </TD><TD COLSPAN=4>	<INPUT TYPE = 'text' NAME = 'frm_subj' SIZE = 60></TD></TR>
		<TR><TD><b>Send map of</b>: </TD><TD COLSPAN=4>
<?php		
	$query = get_tick_list_sql ();

//	dump($query);				
	$result = mysql_query($query) or do_error($query, ' failed', mysql_error(), basename( __FILE__), __LINE__);
	
	echo "<SELECT name = 'incidents' >\n";
	echo "\n<option value = ''>Select</option>\n";	

	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		$tick_ident = substr("{$row['addr']}/{$row['scope']}/{$row['description']}", 0, 48);
		echo "\n<option value = '{$row['lat']},{$row['lng']}'>{$tick_ident}</option>\n";	
		}		// end while()
	echo "\n</SELECT>\n";

?>		
		</TD></TR>
		<TR><TD><b>Message</b>:</TD><TD COLSPAN=4> <TEXTAREA NAME='frm_text' COLS=72 ROWS=1></TEXTAREA></TD></TR>
		<TR'><TD ALIGN='center' COLSPAN=4><BR /><BR />
			<INPUT TYPE='button' 	VALUE='Next' onClick = "do_submit()">&nbsp;&nbsp;&nbsp;&nbsp;
			<INPUT TYPE='reset' 	VALUE='Reset'>&nbsp;&nbsp;&nbsp;&nbsp;
<!--		<INPUT TYPE='button' 	VALUE='Cancel' onClick = 'window.close();'>	-->
			</TD></TR>
			</TABLE></FORM>
			<BR/><BR/><BR/><BR/>

<?php
			}		// end if(count($addresses)>0)
		else {
?>
	<BODY onLoad = "reSizeScr(2)"><CENTER>		<!-- 1/12/09 -->
	<CENTER><H3>Mail to Users</H3>
	<BR /><BR />
	<H3>No addresses available!</H3><BR /><BR />
	<INPUT TYPE='button' VALUE='Cancel' onClick = 'window.history.back();'>
	<BR /><BR />

<?php
			}
		}		// end if (empty($_POST)) {

	else {
			do_send ($_POST['frm_add_str'], "", $_POST['frm_subj'], $_POST['frm_text'], 0, 0);	// ($to_str, $subject_str, $text_str )
			$count = count ( explode ("|", $_POST["frm_add_str"] ) ) ;
?>
	<BODY><CENTER>		<!-- 1/12/09 -->
	<CENTER><BR /><BR /><BR /><H3>Mail sent (<?php echo $count;?>)</H3>
<?php
	}		// end else

	require_once('incs/footer.php');	
	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "group" 	value = "<?php echo $GLOBALS['TABLE_RESPONDER'];?>" />
</form>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</body>
</html>
