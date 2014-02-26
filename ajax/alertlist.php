<?php
/*
1/3/14 - new file, lists road condition alerts for plotting on situation screen map
*/
@session_start();
require_once('../incs/functions.inc.php');

$ret_arr = array();
$ret_arr[0][0] = 0;

$query = "SELECT *,
		`r`.`id` AS `cond_id`,
		`c`.`id` AS `type_id`,
		`r`.`description` AS `r_description`,
		`c`.`description` AS `type_description`,
		`r`.`title` AS `r_title`,
		`c`.`title` AS `type_title`,
		`c`.`icon`AS `icon_url`,
		`r`.`_on` AS `updated`
		FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
		LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON `r`.`conditions`=`c`.`id` 
		WHERE `r`.`_on` >= (NOW() - INTERVAL 2 DAY) ORDER BY `cond_id`";
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$z=0;
while ($row = stripslashes_deep(mysql_fetch_assoc($result))){
	$ret_arr[$z][0] = $row['cond_id'];
	$ret_arr[$z][1] = $row['r_title'];	
	$ret_arr[$z][2] = $row['type_title'];	
	$ret_arr[$z][3] = stripslashes_deep($row['address']);
	$ret_arr[$z][4] = stripslashes_deep($row['r_description']);
	$ret_arr[$z][5] = stripslashes_deep($row['icon_url']);
	$ret_arr[$z][6] = format_date_2(strtotime($row['updated']));
	$ret_arr[$z][7] = $row['lat'];
	$ret_arr[$z][8] = $row['lng'];		
	$z++;
	} // end while

print json_encode($ret_arr);
exit();
?>
