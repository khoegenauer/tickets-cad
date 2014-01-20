<?php
date_default_timezone_set("America/New_York");
header("Content-Type: text/event-stream\n\n");
set_time_limit(30);

$ctr = 0;
while (1) { 		 // Every second, sent a "ping" event.	
	$ctr++;
	$curDate = date(DATE_ISO8601);
//	echo "event: pingy\n";
//	echo "event: pingx\n";
	echo 'data: {"time": "' . $curDate . '"}' . "\n";					// works OK by itself
	echo "\n";
	
	
	ob_flush();
	flush();
	sleep(3);
	}
?>