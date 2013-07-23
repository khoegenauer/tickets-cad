<?php
/*
8/21/10 - initial release
8/24/10 - glat hyphen logic removed
11/21/10 - update times only on location change
4/8/11 - relocated JSON error suppression
4/9/11 - corrects double json decode 
4/23/11 - no JSON decode for instamapper, include NULL in UPDATE test
4/24/11 - aprs error suppress added
4/25/11 - glat - check position or time change, sane() added
5/22/11 - corrections to aprs sql
*/

function sane($in_lat, $in_lng, $in_time) {			// applies sanity check to input values
	if (!(is_float($in_lat)) && (is_float($in_lng)) && (!(is_int($in_time))))	return FALSE;
	if (($in_lat> 90.0) || ($in_lat> 90.0)) 									return FALSE;
	if (($in_lat== 0.0) || ($in_lng== 0.0))										return FALSE;
	if (( $in_lng> 180.0) || ( $in_lng<-180.0))									return FALSE;
	
	$ts_threshold_p = mysql2timestamp(strtotime('now - 24 hour'));	// integer time value
	$ts_threshold_f = mysql2timestamp(strtotime('now + 24 hour'));	
	return ($in_time>$ts_threshold_p)&& ($in_time>$ts_threshold_f);	
	}				// end function same()

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
	$ts_threshold = strtotime('now - 24 hour');				// discard inputs older than this - 4/25/11
	
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
	global $ts_threshold;					// 4/25/11
// 	http://www.instamapper.com/api?action=getPositions&key=4899336036773934943
	
//	$from_utc = ($row_tr)?  "&from_ts=" . $row_tr['utc_stamp']: "";		// 3/26/09
	$from_utc = "";											// reconsider for tracking
	
	$the_url = "http://www.instamapper.com/api?action=getPositions&key={$key_val}{$from_utc}";

	$data=get_remote($the_url, FALSE);		// no JSON decode - 4/23/11
	
	$ary_data = explode ("\n", $data);

	if (count($ary_data) > 1) {							// any data?
		for ($i=1; $i<count($ary_data)-1; $i++) {		// 11/25/10
			$the_position = explode (",", $ary_data[$i]);

			if (count($the_position)==8) {				// sanity check
			
				list($device, $user, $when, $lat, $lng, $course, $speed, $altitude ) = $the_position;								
//						0		1		2	  3		4		5		6		7
				$updated = mysql_format_date($when);
																		// update iff position change - 4/23/11
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat`= '{$lat}' ,
					`lng`= '{$lng}',
					`updated` = '{$updated}',
					`user_id` = 0
					WHERE ((`callsign` = '{$device}')
					AND  (`instam` = 1)		
					AND  ((`lat` != '{$lat}' OR `lat` IS NULL )))";
				$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);

				if (mysql_affected_rows()> 0 ) {												// if any movement
					$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source`= " . quote_smart(trim($user)) . " AND `updated` < (NOW() - INTERVAL 7 DAY)"; 	// remove prior track this device  3/20/09
					$result = mysql_query($query);				// 7/28/10
												// 11/25/10
					$query  = sprintf("INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh`(`source`,`utc_stamp`,`latitude`,`longitude`,`course`,`speed`,`altitude`,`updated`,`from`)
										VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
											quote_smart($device),
											quote_smart($when),
											quote_smart($lat),
											quote_smart($lng),
											quote_smart($altitude),
											round($speed),
											quote_smart($course),
											quote_smart(mysql_format_date($when)),
											quote_smart($speed)) ;
					$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);					
					unset($result);
					}				// end if (mysql_affected_rows>0)
					
				}		// end if (count())
			}		// end for ()
		}		// end if (count())
	
	}		// end function do_instam()

function do_gtrack() {			//7/29/09
	global $ts_threshold;		// 4/25/11

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

				$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
				$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				unset($resultd);
																			// 11/21/10
				$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
					`lat` = '$lat', `lng` ='$lng', updated	= '$updated' 
					WHERE `gtrack` = 1 
					AND  (`lat` != '{$lat}' OR `lng` != '{$lng}' ) 
					AND  `callsign` = '$user_id'";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

				$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE source = '$user_id'";	// remove prior track this device
				$result = mysql_query($query);				// 7/28/10
	
				$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
				$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}	//end if
			}	//end if
		}	//end if
	}	// end while
}	// end function do_gtrack()

function do_locatea() {				//7/29/09
	global $ts_threshold;					// 4/25/11
	
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `locatea`= 1 AND `callsign` <> ''";  // work each call/license, 8/10/09
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(),basename( __FILE__), __LINE__);
	while ($row = @mysql_fetch_assoc($result)) {		// for each responder/account
	$tracking_id = ($row['callsign']);
	$db_lat = ($row['lat']);
	$db_lng = ($row['lng']);
	$db_updated = ($row['updated']);
	$update_error = strtotime('now - 4 hours');

		$request_url = "http://www.locatea.net/data.php?userid=$tracking_id";
//		snap(__LINE__, $request_url);
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
			$updated = $date . " " . $time;				// updated datetime, e.g., 2009-09-22 13:40:20
//			snap(__LINE__, $updated);
			}

		if (sane($lat, $lng, mysql2timestamp($updated))) {
			$mph = $xml->marker['mph'];
			$kph = $xml->marker['kph'];
			$heading = $xml->marker['heading'];
	
			$queryd	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
			$resultd = mysql_query($queryd) or do_error($queryd, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			unset($resultd);
																		// 4/25/11
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
				`lat` = '{$lat}', `lng` ='{$lng}', updated	= '{$updated}' 
				WHERE ((`locatea` = 1)
				AND ( callsign = '{$user_id}'))";
				
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` = '$user_id'";		// remove prior track this device
			$result = mysql_query($query);		// 7/28/10
	
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (source, latitude, longitude, speed, altitude, updated) VALUES ('$user_id', '$lat', '$lng', round({$mph}), '$alt', '$updated')";		// 6/24/10
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (source, latitude, longitude, speed, altitude, packet_date, updated) VALUES ('$user_id', '$lat', '$lng', '$mph', '$alt', '$updated', '$updated')";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}	//end if sane()
	}	// end while
}	// end function do_locatea()

function do_glat() {			//7/29/09
	global $ts_threshold;					// 4/25/11
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
		error_reporting(0);										// 4/8/11
		$json = get_remote($the_url);							// arrives decoded 4/9/11
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
								$ret_val[1] = $value3;		// integer time UTC?
//								snap(__LINE__, $value3);
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
											
		$lat = $ret_val[3];
		$lng = $ret_val[2];
		$glat_id = $ret_val[0];									// 8/24/10
		$timestamp = $ret_val[1];
		$updated = mysql_format_date($timestamp);				// to datetime format
		
		if ( sane( $lat, $lng, $timestamp )) {					// discard if invalid or stale
		
			$queryd	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE packet_date < (NOW() - INTERVAL 14 DAY)"; // remove ALL expired track records 
			$resultd = mysql_query($queryd) or do_error($queryd, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			unset($resultd);
																			// 4/25/11
			$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
				`lat` = '{$lat}', `lng` ='{$lng}', `updated`	= '{$updated}' 
				WHERE ((`glat` = 1)
				AND  (`callsign` LIKE '%{$glat_id}'))";	
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$query = "DELETE FROM `$GLOBALS[mysql_prefix]tracks_hh` WHERE `source` LIKE '%{$glat_id}'";		// remove prior track this device  
			$result = mysql_query($query);	// 7/28/10
			
			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks_hh` (`source`, `latitude`, `longitude`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated')";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (`source`, `latitude`, `longitude`,`packet_date`, `updated`) VALUES ('$glat_id', '$lat', '$lng', '$updated', '$updated')";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

			}			// end if (sane())
		}			// end while()

	}		// end function do_glat();


function aprs_date_ok ($indate) {	// checks for date/time within 48 hours
	return (abs(time() - mysql2timestamp($indate)) < 2*24*60*60); 
	}

function do_aprs() {				// 3/15/11 - populates the APRS tracks table and updates responder position data
	global $ts_threshold;					// 4/25/11
	global $istest;
	$the_key = trim(get_variable('aprs_fi_key'));
	if (empty($the_key)) {
		do_log($GLOBALS['LOG_ERROR'], 0, 0, "APRS_FI key required for APRS operation" );
		return FALSE;
		}
	
	$dist_chk = ($istest)? 2500000.0 : 250000.0 ;		

	$pkt_ids = array();				// 6/17/08
	$speeds = array();				// 10/2/08
	$sources = array();
																	// 4/25/11
	$query = "SELECT `callsign`, `mobile` FROM `$GLOBALS[mysql_prefix]responder`
		WHERE (	( `mobile`= 1 )
		AND  	(`aprs`= 1 )
		AND 	(`callsign` <> ''))";  	

	$result1 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
//	snap(__LINE__, $query);																	// remove old track records 		
	while ($row1 = mysql_fetch_assoc($result1)) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]tracks` WHERE `source`= '{$row1['callsign']}' ORDER BY `packet_date` DESC LIMIT 1";	// possibly none
		$result2 = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);
		while ($row2 = mysql_fetch_assoc($result2)) {
			$pkt_ids[trim($row2['packet_id'])] = TRUE;					// index is packet_id
			$sources[trim($row2['source'])] = TRUE;						// index is callsign
			$speeds[trim($row2['source'])] = $row2['speed'];			// index is callsign 10/2/08
			}
		}
	$query	= "DELETE FROM `$GLOBALS[mysql_prefix]tracks` WHERE `updated`< (NOW() - INTERVAL 7 DAY)"; 
	$resultd = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	unset($resultd);
	
	$query	= "SELECT * FROM `$GLOBALS[mysql_prefix]responder` WHERE `mobile`= 1 AND `aprs`= 1 AND `callsign` <> ''";  // work each call sign, 8/10/09
	$result	= mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
	$positions = array();	

	if (mysql_affected_rows() > 0) {			// 
		$call_str = $sep = "";
		while ($row = @mysql_fetch_assoc($result)) {	
			$lat = (!(empty($row['lat']))) ? $row['lat']: get_variable('def_lat');
			$lng = (!(empty($row['lng']))) ? $row['lng']: get_variable('def_lng');
			$positions[$row['callsign']] = array($lat, $lng);
			$call_str .= $sep . $row['callsign'];
			$sep = ",";
			}
		$the_url = "http://api.aprs.fi/api/get?name={$call_str}&what=loc&apikey={$the_key}&format=json";
//		dump($the_url);
		
		$data=get_remote($the_url);				// returns JSON-decoded values
		$temp = $data->result;
//		snap(__LINE__, $temp);
		
		if (strtoupper($temp) == "OK"){
			$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
		
			for ($i=0; $i< ($data->found ) ; $i++) {		// extract fields from each entry
				$entry = (object) $data->entries[$i];
//				dump($entry);
				$callsign_in = $entry->name;
				
				$lat = $entry->lat;
				$lng = $entry->lng;
				$updated =  $entry->time;
//				snap(__LINE__, $updated);
				@($course = $entry->course);				// 4/24/11
				@($mph = $entry->speed);
				@($alt = @$entry->altitude);
				$packet_date = $entry->lasttime;
				$p_d_timestamp = mysql_format_date($packet_date);		// datetime format				
																		// 4/25/11, 5/22/11
				if (sane($lat, $lng, $updated )){																
					$query = "UPDATE `$GLOBALS[mysql_prefix]responder` SET 
						`lat` = '$lat', `lng` ='$lng', `updated` = '{$p_d_timestamp}' 
						WHERE ((`aprs` = 1)
						AND  (`updated` <> '{$p_d_timestamp}')
						AND  (`callsign` = '{$callsign_in}'))";
	
//					snap(__LINE__, $p_d_timestamp);
					$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
					
					$our_hash = $callsign_in . (string) (abs($lat) + abs($lng)) ;				// a hash - for dupe prevention
	
					$query = "INSERT INTO `$GLOBALS[mysql_prefix]tracks` (
						packet_id, source, latitude, longitude, speed, course, altitude, packet_date, updated) VALUES (
						'{$our_hash}', '{$callsign_in}', '{$lat}', '{$lng}', '{$mph}', '{$course}', '{$alt}', '{$p_d_timestamp}', '{$now}')";
						
					$result = mysql_query($query);				// ignore duplicate/errors
					$result_tr = mysql_query($query) or $error = TRUE ;
					}
	
				}		// end for ($i...)	

			}				// end ( JSON data OK)
		}		// end (mysql_affected_rows() > 0) - any APRS units?
	}		// end function do_aprs() 


?>