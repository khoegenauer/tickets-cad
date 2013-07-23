<?php
/*
8/21/10 - initial release
8/24/10 - glat hyphen logic removed
*/
function get_current() {		// 3/16/09, 7/25/09
	$delay = 1;			// minimum time in minutes between  queries - 7/25/09
	$when = get_variable('_aprs_time');				// misnomer acknowledged
	if(time() < $when) { 
		return;
		} 
	else {
		$next = time() + $delay*60;
		$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`='$next' WHERE `name`='_aprs_time'";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}

	$aprs = $instam = $locatea = $gtrack = $glat = FALSE;	// 3/22/09
	
	$query = "SELECT `id`, `aprs`, `instam`, `locatea`, `gtrack`, `glat` FROM `$GLOBALS[mysql_prefix]responder`WHERE ((`aprs` = 1) OR (`instam` = 1) OR (`locatea` = 1) OR (`gtrack` = 1) OR (`glat` = 1))";	
	$result = mysql_query($query) or do_error($query, ' mysql error=', mysql_error(), basename( __FILE__), __LINE__);
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
		if ($row['aprs'] == 1) 	{ $aprs = TRUE;}
		if ($row['instam'] == 1) { $instam = TRUE;}
		if ($row['locatea'] == 1) { $locatea = TRUE;}		//7/29/09
		if ($row['gtrack'] == 1) { $gtrack = TRUE;}		//7/29/09
		if ($row['glat'] == 1) { $glat = TRUE;}			//7/29/09
		}		// end while ()
	unset($result);
	if ($aprs) 		{do_aprs();}
	if ($instam) {	
		$temp = get_variable("instam_key");
		$instam = ($temp=="")? FALSE: $temp;
		if ($instam )	{do_instam($temp);}
		}

	if ($locatea) 	{do_locatea();}					//7/29/09
	if ($gtrack) 	{do_gtrack();}					//7/29/09
	if ($glat) 		{do_glat();}					//7/29/09
	return array("aprs" => $aprs, "instam" => $instam, "locatea" => $locatea, "gtrack" => $gtrack, "glat" => $glat);		//7/29/09
	
	}		// end get_current() 

function do_instam($key_val) {				// 3/17/09
	// http://www.instamapper.com/api?action=getPositions&key=4899336036773934943
	// housekeep 

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `instam`= 1 AND `callsign` <> ''";  				// work each call/license, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$query	= "SELECT `id`,`utc_stamp` FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '{$row['callsign']}' ORDER BY `utc_stamp` DESC LIMIT 1";		// work each call/license
		$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
		$row_tr = (mysql_affected_rows()>0)? mysql_fetch_assoc($result): FALSE;
		
//		$from_utc = ($row_tr)?  "&from_ts=" . $row_tr['utc_stamp']: "";		// 3/26/09
		$from_utc = "";											// reconsider for tracking
		
		$url = "http://www.instamapper.com/api?action=getPositions&key={$key_val}{$from_utc}";
		$data="";
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
				print "";		// @fopen fails
				}
			}
				
	/*
	InstaMapper API v1.00
	1263013328977,bold,1236239763,34.07413,-118.34940,25.0,0.0,335
	1088203381874,CABOLD,1236255869,34.07701,-118.35262,27.0,0.4,72
	*/
	
	$ary_data = explode ("\n", $data);
	if (count($ary_data) > 1) {
		for ($i=1; $i<count($ary_data)-2; $i++) {
		
			$str_pos = explode (",", $ary_data[$i]);
			if (count($str_pos)==8) {

				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat`=		" . quote_smart(trim($str_pos[3])) . ",
					`lng`=		" . quote_smart(trim($str_pos[4])) . ",
					`updated` = " .	quote_smart(mysql_format_date(trim($str_pos[2]))) . "
					WHERE `instam` = 1 and `callsign` = " . quote_smart(trim($str_pos[0]));		// 7/25/09

				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
																									// 3/19/09
				$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source`= " . quote_smart(trim($str_pos[1]));		// remove prior track this device  3/20/09
				$result = mysql_query($query);				// 7/28/10
											// 
				$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
									VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
										quote_smart($str_pos[1]),
										quote_smart($str_pos[2]),
										quote_smart($str_pos[3]),
										quote_smart($str_pos[4]),
										quote_smart($str_pos[7]),
										round($str_pos[6]),
										quote_smart($str_pos[5]),
										quote_smart(mysql_format_date($str_pos[2])),
										quote_smart($str_pos[6])) ;
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);					
				unset($result);
					
				}		// end if (count())


			}		// end for ()
		}		// end if (count())
	
		}		// end while
	}		// end function do_instam()

function do_gtrack() {			//7/29/09
	$gtrack_url = get_variable('gtrack_url');
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `gtrack`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
	$tracking_id = ($row['callsign']);
	$db_lat = ($row['lat']);
	$db_lng = ($row['lng']);
	$db_updated = ($row['updated']);
	$update_error = strtotime('now - 1 hour');

		$request_url = $gtrack_url . "/data.php?userid=$tracking_id";		//gtrack_url set by entry in settings table
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error " . __LINE__;		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day 8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;	// create updated datetime
		}
		$mph = $xml->marker['mph'];
		$kph = $xml->marker['kph'];
		$heading = $xml->marker['heading'];

		if (!empty($lat) && !empty($lng)) {		//check not NULL
	
			if ($db_lat<>$lat && $db_lng<>$lng) {	// check for change in position

				if(($db_updated == $updated) && ($update_error > $updated)) {
				} else {

				$query	= "DELETE FROM $GLOBALS[mysql_prefix]tracks WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
	
				$query = "UPDATE $GLOBALS[mysql_prefix]responder SET lat = '$lat', lng ='$lng', updated	= '$updated' WHERE callsign = '$user_id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query = "DELETE FROM $GLOBALS[mysql_prefix]tracks_hh WHERE source = '$user_id'";	// remove prior track this device
				$result = mysql_query($query);				// 7/28/10
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks_hh (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
	}	// end while
}	// end function do_gtrack()

function do_locatea() {				//7/29/09
	
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `locatea`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
	$tracking_id = ($row['callsign']);
	$db_lat = ($row['lat']);
	$db_lng = ($row['lng']);
	$db_updated = ($row['updated']);
	$update_error = strtotime('now - 4 hours');

		$request_url = "http://www.locatea.net/data.php?userid=$tracking_id";
		$data="";
		if (function_exists("curl_init")) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			}
		else {				// not CURL
			if ($fp = @fopen($request_url, "r")) {
				while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
				fclose($fp);
				}		
			else {
				print "-error " . __LINE__;		// @fopen fails
				}
			}

		$xml = new SimpleXMLElement($data);

		$user_id = $xml->marker['userid'];
		$lat = $xml->marker['lat'];
		$lng = $xml->marker['lng'];
		$alt = $xml->marker['alt'];
		$date = $xml->marker['local_date'];
		if ($date != "") {
			list($day, $month, $year) = explode("/", $date); // expand date string to year, month and day	8/3/09
			$date = $year . "-" . $month . "-" . $day;  // format date as mySQL date
			$time = $xml->marker['local_time'];
			$time = date("H:i:s", strtotime($time));	// format as mySQL time
			$updated = $date . " " . $time;	// create updated datetime
			}
		$mph = $xml->marker['mph'];
		$kph = $xml->marker['kph'];
		$heading = $xml->marker['heading'];

		if (!empty($lat) && !empty($lng)) {		//check not NULL
	
			if ($db_lat<>$lat && $db_lng<>$lng) {	// check for change in position

				if(($db_updated == $updated) && ($update_error > $updated)) {
				} else {
	
				$query	= "DELETE FROM $GLOBALS[mysql_prefix]tracks WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
	
				$query = "UPDATE $GLOBALS[mysql_prefix]responder SET lat = '$lat', lng ='$lng', updated	= '$updated' WHERE callsign = '$user_id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query = "DELETE FROM $GLOBALS[mysql_prefix]tracks_hh WHERE source = '$user_id'";		// remove prior track this device
				$result = mysql_query($query);		// 7/28/10
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks_hh (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
				$query = "INSERT INTO $GLOBALS[mysql_prefix]tracks (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
	}	// end while
}	// end function do_locatea()

function do_glat() {			//7/29/09

	function get_remote($url) {				// 8/9/09
		
			$data="";
			if (function_exists("curl_init")) {
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$data = curl_exec($ch);
				curl_close($ch);
				return ($data)?  json_decode($data): FALSE;			// FALSE if fails
				}
			else {				// no CURL
				if ($fp = @fopen($url, "r")) {
					while (!feof($fp) && (strlen($data)<9000)) $data .= fgets($fp, 128);
					fclose($fp);
					}		
				else {
					return FALSE;		// @fopen fails
					}
				}
		return json_decode($data);
	
		}	// end function get remote()

	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `glat`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
		$user = $row['callsign'];
		$db_lat = ($row['lat']);
		$db_lng = ($row['lng']);
		$db_updated = ($row['updated']);
		$update_error = strtotime('now - 1 hour');
	
		$ret_val = array("", "", "", "");
		$the_url = "http://www.google.com/latitude/apps/badge/api?user={$user}&type=json";
		$json = get_remote($the_url);
		error_reporting(0);
		foreach ($json as $key => $value) {				// foreach 1
		    $temp = $value;
			foreach ($temp as $key1 => $value1) {			// foreach 2
			    $temp = $value1;
				foreach ($temp as $key2 => $value2) {			// foreach 3
					$temp = $value2;
					foreach ($temp as $key3 => $value3) {			// foreach 4
						switch (strtolower($key3)) {
							case "id":
								$ret_val[0] = $value3;
							    break;
							case "timestamp":
								$ret_val[1] = $value3;
							    break;
							case "coordinates":
								$ret_val[2] = $value3[0];
								$ret_val[3] = $value3[1];
							    break;
							}		// end switch()
						}		// end for each()
			    	}		// end for each()
				}		// end for each()
			}		// end foreach 1
		error_reporting(E_ALL);
	
		if ((empty($ret_val[0])) || ((empty($ret_val[1])))  || (!(my_is_float($ret_val[2] ))) || (!(my_is_float($ret_val[3])))) {
			break;
			}
		else {							// valid glat data
			$lat = $ret_val[3];
			$lng = $ret_val[2];
			$temp = $ret_val[0];
//			$glat_id = str_replace  ( "-", "" ,$ret_val[0]);		// drop ldg hyphen - 12/13/09 - 
			$glat_id = $ret_val[0];									// 8/24/10
			$timestamp = $ret_val[1];
			$updated = date('Y-m-d H:i:s', $timestamp);
		
			if (!empty($lat) && !empty($lng)) {		//check not NULL
				if ($db_lat<>$lat && $db_lng<>$lng) {	// check for change in position
		
					if(($db_updated == $updated) && ($update_error > $updated)) {
						} 
					else {		
						$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
						$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						unset($resultd);
				
						$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET `lat` = '$lat', `lng` ='$lng', `updated`	= '$updated' WHERE `callsign` LIKE '%{$glat_id}'";		// 12/13/09
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

						$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` LIKE '%{$glat_id}'";		// remove prior track this device  
						$result = mysql_query($query);	// 7/28/10
				
						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

						$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated', '$updated')";
						$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
						}			//end if/else
					}			//end if
				}			//end if
			}			// end if/else()
		}			// end while()

	}		// end function do_glat();


function aprs_date_ok ($indate) {	// checks for date/time within 48 hours
	return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60); 
	}

function do_aprs() {				// populates the APRS tracks table 
									// major surgery by Randy Hammock, August 07
									// Note:	This function assumes the structure/format of APRS data as of Aug 30,2007.
									//			Contact developer with solid information regarding any change in that format.
									// rev 8/17/08 to toss data further than 500 mi fm defult center - to prevent data pollution
									//
	global $istest;
	$dist_chk = ($istest)? 2500000.0 : 250000.0 ;		// 3/18/09

	$pkt_ids = array();				// 6/17/08
	$speeds = array();				// 10/2/08
	$sources = array();
																	// 10/4/08
	$query = "SELECT `callsign`, `mobile` FROM `$GLOBALS[mysql_prefix]responder` WHERE `aprs`= 1 AND `callsign` <> ''";  // 1/23/09, 8/10/09
	$result1 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
	while ($row1 = mysql_fetch_assoc($result1)) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source`= '{$row1['callsign']}' ORDER BY `packet_date` DESC LIMIT 1";	// possibly none
		$result2 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		while ($row2 = mysql_fetch_assoc($result2)) {
			$pkt_ids[trim($row2['packet_id'])] = TRUE;					// index is packet_id
			$sources[trim($row2['source'])] = TRUE;						// index is callsign
			$speeds[trim($row2['source'])] = $row2['speed'];			// index is callsign 10/2/08
			}
		}


	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `packet_date`< (NOW() - INTERVAL 7 DAY)"; // remove ALL expired track records 
	$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($resultd);
	
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

	if (mysql_affected_rows() > 0) {			// 1/23/09
	
		while ($row = @mysql_fetch_assoc($result)) {	
			$lat= (empty($row['lat'])) ? $row['lat']: get_variable('def_lat');
			$lng= (empty($row['lng'])) ? $row['lng']: get_variable('def_lng');
	
			$url = "http://db.aprsworld.net/datamart/csv.php?call=". $row['callsign'];	
			$raw="";		
			if ($fp = @fopen($url, r)) {		
				while (!feof($fp)) $raw .= fgets($fp, 128);		
					fclose($fp);					
					}
			$raw = str_replace("\r",'',$raw);								// Strip Carriage Returns
			$data = explode ("\n",  $raw , 50 );							// Break each line
			if (count($data) > 1) {
	
				$data[1] = str_replace("\",\"", '|', $data[1]); 			// Convert to pipe delimited
				$data[1] = str_replace("\"", '', $data[1]);	  				// Strip remaining quotes
				$fields = explode ("|",  $data[1]);				 			// Break out the fields

				$fields = mysql_real_escape_string_deep($fields);			// 

				if ((count($fields) == 14) && (aprs_date_ok ($fields[13])))  {	// APRS data sanity check
	
					$packet_id = trim($fields[1]) . trim($fields[13]); 		// source, date - unique
					$temp = (isset($pkt_ids[$packet_id]))? "true" : "false";

					if(!(isset($pkt_ids[$packet_id]))) {					// 6/17/08 - avoid duplicate reports						
						$dist = pow($lat-$fields[2],2) + pow($lng-$fields[3],2);		// 8/17/08

						if ($dist < $dist_chk) {							// 3/18/09, 8/26/08	- 10/2/08  planar distance from center < 500 mi?
						
							$query  = "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source` = '$fields[1]' AND  `packet_date`< (NOW() - INTERVAL 7 DAY)"; // remove expired track records this source
							$temp = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
							if(!array_key_exists($fields[1], $sources)) {	// 		new, populate 10/2/08
								$speeds[$fields[1]] = 999;					//
								}
							$error = FALSE;
																					// don't store if duplicate packet_id or invalid floats
							if ((!array_key_exists($packet_id, $pkt_ids)) && 		// 1/23/09
								(intval($fields[5])>0) || 
								(isFloat($fields[2])) &&
								(isFloat($fields[3])) && 
								(intval($speeds[$fields[1]])>0)) {					// 10/2/08
								$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`packet_id`,
																		`source`,`latitude`,`longitude`,`course`,
																		`speed`,`altitude`,`symbol_table`,`symbol_code`,
																		`status`,`closest_city`,`mapserver_url_street`,
																		`mapserver_url_regional`,`packet_date`,`updated`)
													VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
																		NOW() + INTERVAL 1 MINUTE)",
														quote_smart($packet_id),
														quote_smart($fields[1]),
														quote_smart(floatval($fields[2])),
														quote_smart(floatval($fields[3])),
														quote_smart(intval($fields[4])),
														quote_smart(intval($fields[5])),
														quote_smart(intval($fields[6])),
														quote_smart($fields[7]),
														quote_smart($fields[8]),
														quote_smart($fields[9]),
														quote_smart($fields[10]),
														quote_smart($fields[11]),
														quote_smart($fields[12]),
														quote_smart($fields[13]));
			
								$result_tr = mysql_query($query) or $error = TRUE ;
								}
								
							else {													// 1/23/09
								$a = (array_key_exists($packet_id, $pkt_ids));
								$b = (intval($fields[5])>0);
								$c = (intval($speeds[$fields[1]])>0);
								}
								
							$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
							$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
								`lat`= " . 	quote_smart(trim($fields[2])) . ",
								`lng`= " . 	quote_smart(trim($fields[3])) . ",
								`updated`=	'$now'
								WHERE `callsign`= " . quote_smart(trim($fields[1])) . " LIMIT 1";				// 10/2/08, 8/26/08  -- needs USNG computation
							

							$result_tr = mysql_query($query);
							unset($result_tr);
							$lat = $fields[2];										// 8/26/08
							$lng = $fields[3];
							}	
						}			// end if(!(isset(...)
					}				// end count($fields) == 14) && ...		
				}		// end for ($i...)		
		
			}		// end while ($row =...)
		}		// 1/23/09

	}		// end function do_aprs() 
/*
require_once('./incs/functions.inc.php');
17817.qHuXjJwAqjs7mNtl
http://api.aprs.fi/api/get?name=micon-1&what=loc&apikey=17817.qHuXjJwAqjs7mNtl&format=json

$x = '{"command":"get","result":"ok","what":"loc","found":1,"entries":[{"name":"MICON-1","type":"l","time":"1286309771","lasttime":"1286309883","lat":"42.79817","lng":"-83.36817","altitude":322,"course":79,"speed":0,"symbol":"\/>","srccall":"MICON-1","dstcall":"T2TW8Y","comment":"146.840MHzN8ZSA","path":"N8ZSA-1*,W8FSM-3*,WIDE2-1,qAR,W8FSM-5"}]}';

$y = json_decode($x, true);
dump($y);
dump($y['result']);
dump($y['found']);
for ($i=0;$i<$y['found'];$i++){
	dump($y['entries'][$i]['name']);
	dump($y['entries'][$i]['type']);
	dump($y['entries'][$i]['time']);
	dump($y['entries'][$i]['lat']);
	dump($y['entries'][$i]['lng']);
	}
*/

?>