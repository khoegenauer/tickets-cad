<?php
/*
8/7/08	initial release - replaces a Google search
1/18/09 POST replace GET
1/26/09 added functions.inc, get_variable for wp key 
10/1/09	revised return string to include match count as initial entry
3/13/10 constituents table handling added
4/30/10 accommodate add'l phone fields
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/6/10  Added test for internet available
9/2/10 corrected test for internet available
9/30/10 fix per JB email
*/

@session_start();
require_once('incs/functions.inc.php');		//7/28/10

$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]caller_id` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `call_str` varchar(256) NOT NULL,
		  `lookup_vals` varchar(1024) NOT NULL,
		  `status` int(2) NOT NULL,
		  `_by` int(7) NOT NULL DEFAULT '0',
		  `_from` varchar(16) DEFAULT NULL,
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);

// if ((isset($_REQUEST['id'])) && (!(strval(intval($_REQUEST['id']))===$_REQUEST['id']))) {	win_shut_down();}	// 5/28/11

$phone = (empty($_REQUEST))? "4108498721": $_REQUEST['phone'];

	function cid_lookup($phone )  {
		$aptStr = " Apt:";															
		function do_the_row($inRow) {		// for ticket or constituents data
			global $apartment, $misc;
			$outStr = $inRow['contact']	. ";";		// phone
			$outStr .= $inRow['phone']	. ";";			// phone
			$outStr .= $inRow['street'] . (stripos($inRow['street'], " Apt:"))? "" : $apartment;		// street and apartment - 3/13/10
			
			$outStr .= $inRow['street']	. $apartment . ";";			// street and apartment - 3/13/10
			$outStr .= $inRow['city']	. ";";			// city 
			$outStr .= $inRow['state']	. ";";			// state 	
			$outStr .= ";";								// frm_zip - unused 
			$outStr .=$inRow['lat']		. ";"; 
			$outStr .=$inRow['lng']		. ";"; 
			$outStr .=$misc			. ";"; 			// possibly empty - 3/13/10
			return 	$outStr;						// end function do_the_row()
			}
	
																// collect constituent data this phone no.
	
	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]constituents` WHERE `phone`= '{$phone}'
		OR `phone_2`= '{$phone}' OR `phone_3`= '{$phone}' OR `phone_4`= '{$phone}'	LIMIT 1";
	
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$cons_row = (mysql_num_rows($result)==1)	? stripslashes_deep(mysql_fetch_array($result)): NULL;
	$apartment = 	(is_null($cons_row))		? "" : $aptStr . $cons_row['apartment']; 						// note brackets
	$misc = 		(is_null($cons_row))		? "" : $cons_row['miscellaneous'];
	
	$query  = "SELECT  * FROM `$GLOBALS[mysql_prefix]ticket` WHERE `phone`= '{$phone}' ORDER BY `updated` DESC";			// 9/29/09
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$ret = mysql_num_rows($result) . ";";						// hits - common to each return
	if (mysql_num_rows($result)> 0) {							// build return string from newest incident data
		$row = stripslashes_deep(mysql_fetch_array($result));
		$ret .= do_the_row($row);
		$source = 0;										// incidents

		}
	
	 elseif (!(is_null($cons_row))) {						// 3/13/10
	 	$source = 1;										// constituents
		$ret .= do_the_row($cons_row);						// otherwise use constituents data
		}
	
	else {													// no priors or constituents - do WP
			$wp_key = get_variable("wp_key");				// 1/26/09
			$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($phone) . ";api_key=". $wp_key;
			if(isset($phone)) {								// wp phone lookup
				$url = "http://api.whitepages.com/reverse_phone/1.0/?phone=" . urlencode($phone) . ";api_key=". $wp_key;
				}
			$data = "";
			if (function_exists("curl_init")) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec ($ch);
				curl_close ($ch);
				}
			else {				// not CURL
				if ($fp = @fopen($url, "r")) {
					while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
					print "-error 1";		// @fopen fails
					}
				}
					
		//						target: "Arnold Shore;(410) 849-8721;1684 Anne Ct;  Annapolis; MD;21401;lattitude;longitude; miscellaneous"
			if  (!(((strpos ($data, "Invalid")>0)) || ((strpos ($data, "Missing")>0)))){

				$source = 2;				// White Pages
		
				$aryk[0] = "<wp:firstname>";
				$aryk[1] = "<wp:lastname>";
				$aryk[2] = "<wp:fullphone>";
				$aryk[3] = "<wp:fullstreet>";
				$aryk[4] = "<wp:city>";
				$aryk[5] = "<wp:state>";
				$aryk[6] = "<wp:zip>";
				$aryk[7] = "<wp:latitude>";
				$aryk[8] = "<wp:longitude>";
	//			dump($aryk);
				$aryv = array(9);				// values
			//	First Last;(123) 456-7890;1234 Name Ct,  Where, NY 12345"
				$arys[0] = " ";		// firstname
				$arys[1] = ";";		// lastname
				$arys[2] = ";";		// fullphone
				$arys[3] = ";";		// fullstreet
				$arys[4] = ";";		// city
				$arys[5] = ";";		// state
				$arys[6] = ";";		// zip
				$arys[7] = ";";		// latitude
				$arys[8] = ";";		// longitude
				
				$pos = 0;					//
				for ($i=0; $i< count($aryk); $i++) {
					$pos = strpos ( $data, $aryk[$i], $pos);
					if ($pos === false) {								// bad
						$arys="";
						break;
						}
					$lhe = $pos+strlen($aryk[$i]);
					$rhe = strpos ( $data, "<", $lhe);
					$aryv[$i] = substr ( $data, $lhe , $rhe-$lhe );		// substr ( string, start , length )
					}		// end for ($i...)
	//			dump($aryv);
		
				if (!(empty($arys))) {									// 11/11/09
					for ($i=0; $i< count($aryk); $i++) {				// append return string to match count
						$ret .= $aryv[$i].$arys[$i];					// value + separator
						}			// end for ()
					unset($result);
					}
				}
		}					// end no priors
	
	//dump($ret);
	$ret .= ";" . $source;	// add data source
//	dump(explode(";", $ret));
	return $ret;			// semicolon-separated string
	}			// end function cid_lookup() 


$lookup_str =  cid_lookup($phone);
$query = "INSERT INTO `$GLOBALS[mysql_prefix]caller_id` (`call_str`, `lookup_vals`, `status`)  VALUES ( " . quote_smart(trim($phone)) . ", " .  quote_smart(addslashes(trim($lookup_str))) . ", 0);";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
$retval =  (explode(";", $lookup_str)) ;
$received = format_date_time(mysql_format_date(now()));
$sources = array("prior incidents", "Constituents data", "White pages");
$extra = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}{$extra}/";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD><TITLE>Tickets - Caller ID Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<META HTTP-EQUIV="Expires" CONTENT="0">
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>"> <!-- 7/7/09 -->
	<LINK REL=StyleSheet HREF="stylesheet.php" TYPE="text/css">		<!-- 3/15/11 -->
	</HEAD>
	<BODY>

<TABLE ALIGN="center" cellpadding = 2 cellspacing = 2 BORDER=0 STYLE = "margin-top:40px" >
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TH COLSPAN=2 ALIGN = 'center'>Caller ID information Saved</TH>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Name</TD>
	<TD><?php echo $retval[1];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>	<!--  $new_string = ereg_replace("[^0-9]", "", $string);  -->
	<TD>Phone no.</TD>
	<TD><?php echo format_phone (ereg_replace("[^0-9]", "", $retval[2]));?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Address</TD>
	<TD><?php echo $retval[3];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>City</TD>
	<TD><?php echo $retval[4];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>State</TD>
	<TD><?php echo $retval[5];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD></TD>
	<TD><?php echo $retval[6];?></TD>			<!-- wp returns zip -->
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Latitude</TD>
	<TD><?php echo $retval[7];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>Longitude</TD>
	<TD><?php echo $retval[8];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>tbd</TD>
	<TD><?php echo $retval[9];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>tbd</TD>
	<TD><?php echo $retval[10];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Call received</TD>
	<TD><?php echo $received;?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD>Information source</TD>
	<TD><?php echo $sources[$retval[10]];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='odd'>
	<TD>Prior calls this number</TD>
	<TD><?php echo $retval[0];?></TD>
	</TR>
<TR ALIGN="left" VALIGN="baseline" CLASS='even'>
	<TD colspan = 2 align= 'center'><BR />
	<A HREF= "<?php echo $url;?>"><U>to Tickets</U><BR /><BR /></A>
	</TD>
	</TR>
</TABLE>


</BODY></HTML>
