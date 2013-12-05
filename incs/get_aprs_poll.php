<?php
/**
 *
 *
 * @package get_aprs_poll.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */

/*
3/18/09 'aprs_poll' to 'auto_poll'
*/

require_once($fip);		//7/28/10
$aprs_poll = @get_variable('auto_poll');		//  possibly not set
$aprs_poll = (is_null ($aprs_poll))? 0 : $aprs_poll ;
print $aprs_poll;
//print "55";
