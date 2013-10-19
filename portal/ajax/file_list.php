<?php
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
@session_start();
require_once('../../incs/functions.inc.php');
function br2nl($input) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
	}
$return = "";
if(empty($_GET)) {
	exit;
	} else {
	$user_id = $_GET['id'];
	}
	
$where = "WHERE (`user_id` = " . $user_id . " OR `user_id` = 0) AND `type` = 2";

$query = "SELECT *,
		`fx`.`id` AS `x_id`,
		`f`.`id` AS `f_id`
		FROM `$GLOBALS[mysql_prefix]files_x` `fx`
		LEFT JOIN `$GLOBALS[mysql_prefix]files` `f` ON `f`.`id` = `fx`.`file_id` 
		{$where} ORDER BY `f`.`id` ASC"; 
$bgcolor = '#EEEEEE';
$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
if (mysql_affected_rows() == 0) { 
	$return .= "<TABLE style='width: 100%;'><TR style='width: 100%; font-size: 1em;'><TD style='width: 100%; font-size: 1em; text-align: center;'>No Files</TD></TR></TABLE>";
	} else {
	$return .= "<TABLE style='width: 100%;'>";	
	$return .= "<TR class='heading' style='width: 100%; color: #FFFFFF; font-size: 1.1em;'><TD style='font-size: 1em;; color: #FFFFFF'>File Name</TD><TD style='font-size: 1em;; color: #FFFFFF'>Uploaded By</TD><TD style='font-size: 1em;; color: #FFFFFF'>Date</TD></TR>";		
	while ($row = stripslashes_deep(mysql_fetch_assoc($result))){	
		$return .= "<TR style='font-size: 1em; background-color: " . $bgcolor . "; font-size: 1em;'>";
		$filename = $row['filename'];
		$return .= "<TD style='font-size: 1em;'><A HREF='./ajax/download.php?filename={$filename}&origname={$row['orig_filename']}'>{$row['title']}</A></TD>";
		$return .= "<TD style='font-size: 1em;'>" . get_owner($row['_by']) . "</TD>";
		$return .= "<TD style='font-size: 1em;'>" . format_date_2(strtotime($row['_on'])) . "</TD>";		
		$return .= "</TR>";
		$bgcolor = ($bgcolor == '#EEEEEE') ? '#FEFEFE' : '#EEEEEE';
		}				// end while
		$return .= "</TABLE>";
	}	//	end else
	
print $return;
?>