<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL  ^ E_DEPRECATED);
@session_start();
require '../incs/functions.inc.php';
$user = $_SESSION['user_id'];
 
function get_facilityname($value) {
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities` WHERE `id` = " . $value . " LIMIT 1";		 
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
	$row = stripslashes_deep(mysql_fetch_assoc($result));
	return $row['name'];
	}
 
function exportMysqlToCsv($user,$filename = 'requests.csv'){
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
	
	$where = (isset($user)) ? "WHERE `requester` = " . $user: "";

	$order = "ORDER BY `request_date`";
	$order2 = "ASC";


	$query = "SELECT 
			`r`.`id` AS `id`,
			`r`.`street` AS `street`,
			`r`.`city` AS `city`,
			`r`.`state` AS `state`,
			`r`.`the_name` AS `customer`,
			`r`.`phone` AS `phone`,
			`r`.`to_address` AS `to_address`,
			`r`.`rec_facility` AS `rec_facility`,
			`r`.`scope` AS `title`,
			`r`.`description` AS `description`,
			`r`.`comments` AS `comments`,
			`r`.`status` AS `status`,
			`r`.`id` AS `request_id`,
			`a`.`id` AS `assigns_id`,
			`a`.`start_miles` AS `start_miles`,
			`a`.`end_miles` AS `end_miles`,
			`r`.`request_date` AS `request_date`,
			`r`.`accepted_date` AS `accepted_date`,
			`r`.`declined_date` AS `declined_date`,		
			`r`.`resourced_date` AS `resourced_date`,
			`r`.`completed_date` AS `completed_date`,	
			`r`.`closed` AS `closed_date`,
			`r`.`cancelled` AS `cancelled_date`,
			`r`.`_on` AS `_on`,
			`a`.`dispatched` AS `dispatched`,
			`a`.`clear` AS `clear`		
			FROM `$GLOBALS[mysql_prefix]requests` `r`
			LEFT JOIN `$GLOBALS[mysql_prefix]assigns` `a` ON `a`.`ticket_id`=`r`.`ticket_id` 			
			{$where} GROUP BY `r`.`id` {$order} {$order2}";
	$result = mysql_query($query) or do_error('', 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
    $fields_cnt = mysql_num_fields($result);
	
	$output = array();
	$z=0;
    while ($row = mysql_fetch_array($result)){
		$output[$z][] = $row['customer'];
		$output[$z][] = $row['street'];		
		$output[$z][] = $row['city'];
		$output[$z][] = $row['state'];		
		$output[$z][] = $row['phone'];
		$theToAddress = explode(',',$row['to_address']);
		if($theToAddress[0] == "") {
			$output[$z][] = "";
			} else {
			$output[$z][] = $row['to_address'];
			}
		$output[$z][] = ($row['rec_facility'] != 0) ? get_facilityname($row['rec_facility']): "Not Set";	
		$output[$z][] = $row['title'];
		$output[$z][] = $row['description'];	
		$output[$z][] = $row['comments'];	
		$output[$z][] = $row['status'];	
		$output[$z][] = ($row['request_date'] != NULL) ? format_date_2(strtotime($row['request_date'])): "";		
		$output[$z][] = ($row['accepted_date'] != NULL) ? format_date_2(strtotime($row['accepted_date'])): "";	
		$output[$z][] = ($row['declined_date'] != NULL) ? format_date_2(strtotime($row['declined_date'])): "";	
		$output[$z][] = ($row['resourced_date'] != NULL) ? format_date_2(strtotime($row['resourced_date'])): "";	
		$output[$z][] = ($row['completed_date'] != NULL) ? format_date_2(strtotime($row['completed_date'])): "";
		$output[$z][] = ($row['closed_date'] != NULL) ? format_date_2(strtotime($row['closed_date'])): "";
		$output[$z][] = ($row['cancelled_date'] != NULL) ? format_date_2(strtotime($row['cancelled_date'])): "";
		$z++;
		}
	$fields_cnt = count($output[1]);
	$rows_cnt = count($output);
	
	$headers = array('Customer','Street','City','State','Phone','To Address','Receiving Facility','Title','Description','Comments','Status','Request Date','Accepted Date','Declined Date','Resourced Date','Completed Date','Closed Date','Cancelled Date');
	
	$headers_cnt = count($headers);
 
    $schema_insert = '';
 
    for ($i = 0; $i < $headers_cnt; $i++) {
		$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, stripslashes($headers[$i])) . $csv_enclosed;
		if($i < $headers_cnt - 1) {
			$schema_insert .= $csv_separator;
			}
		} // end for
 
    $out = $schema_insert;
    $out .= $csv_terminated;
    // Format the data
	for ($k = 0; $k < $rows_cnt; $k++) {
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            if (($output[$k][$j] == '0') || ($output[$k][$j] != ''))
            {
 
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $output[$k][$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
					str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $output[$k][$j]) . $csv_enclosed;
                }
            } 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
		

		
    } // end for
	
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Length: " . strlen($out));
    // Output to browser with appropriate mime type, you choose ;)
	header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=$filename");
    echo $out;
    exit;
	} 

exportMysqlToCsv($user);
 
?> 
