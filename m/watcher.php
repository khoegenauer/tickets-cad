<?php
date_default_timezone_set("America/New_York");
header("Content-Type: text/event-stream\n\n");
set_time_limit(60);
$period = 3;

@session_start();	
while (1) { 		 // check every $period seconds	
	sleep( $period );
	if ( ! ( ( array_key_exists("SP", $_SESSION) ) && ( array_key_exists( "user_id", $_SESSION["SP"] ) ) ) ) {
		echo "event: pingx\n";
		echo "\n";
		ob_flush();
		flush();
		break;
		} 		// end if ()		
	}		// end while (1)
exit() ;


//	echo "event: pingx\n";
//	echo 'data: {"time": "' . $curDate . '"}' . "\n";					// works OK by itself
//	echo "\n";	
	
?>