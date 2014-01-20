<?php
/*
*/
//error_reporting(E_ALL);
require_once('../incs/functions.inc.php'); 
$now = mysql_format_date(time() - (get_variable('delta_mins')*60));	
function output_xml_field($col_name,$value) {
	$value = str_replace('&', '&amp;',	$value);
    $value = str_replace('<', '&lt;',	$value);
    $value = str_replace('>', '&gt;',	$value);
    $value = str_replace('"', '&quot;',	$value);
    return '<'.$col_name.'>'.$value.'</'.$col_name.'>';
	}

# format the keys to ensure they can be found in the database
$query = "SELECT 
	`r`.`id` AS `feed_id`,
	`r`.`_on` AS `as_of`,
	`c`.`_on` AS `c_on`,	
	`r`.`_from` AS `r_from`, 
	`c`.`_from` AS `c_from`, 
	`r`.`address` AS `address`,
	`r`.`lat` AS `lat`,	
	`r`.`lng` AS `lng`,	
	`r`.`_by` AS `updated_by`, 
	`c`.`_by` AS `c_by`,
	`r`.`username` AS `username`,	
	`r`.`id` AS `the_id`, 
	`c`.`id` AS `type_id`, 
	`r`.`title` AS `the_title`, 
	`c`.`title` AS `type`, 
	`r`.`description` AS `notes`, 
	`c`.`description` AS `the_description` 
	FROM `$GLOBALS[mysql_prefix]roadinfo` `r` 
	LEFT JOIN `$GLOBALS[mysql_prefix]conditions` `c` ON ( `r`.`conditions` = c.id )		
	ORDER BY `r`.`id`";
$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
$i = 1;
header("Content-type: text/xml");
$XML = "<?xml version=\"1.0\"?>";
$XML .= "<rss version=\"2.0\" xmlns:georss=\"http://www.georss.org/georss\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">";
$XML .= "\t<channel>";
$XML .= "\t\t<title>Road Conditions</title>\n\n";
$XML .= "\t\t<description><![CDATA[Road Conditions provided by Gloucestershire & Worcestershire 4x4 Response]]></description>\n";	
$XML .= "\t\t<link>http://gw4x4r.co.uk</link>\n";		
$XML .= "\t\t<pubDate>" . $now . "</pubDate>\n";
$XML .= "\t\t<lastBuildDate>" . $now . "</lastBuildDate>";
$XML .= "\t\t<language>en-us</language>";
$XML .= "\t\t<managingEditor>webmaster@gw4x4r.co.uk</managingEditor>";
$XML .= "\t\t<webMaster>webmaster@gw4x4r.co.uk</webMaster>";
$XML .= "\t\t<image>";
$XML .= "\t\t\t<url>http://www.gw4x4r.co.uk/gw4x4r_logo.jpg</url>";
$XML .= "\t\t\t<title><![CDATA[Gloucestershire & Worcestershire 4x4 Response]]></title>";
$XML .= "\t\t\t<link>http://www.gw4x4r.co.uk/</link>";
$XML .= "\t\t</image>";
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$XML .= "\t\t<item>\n";
	$XML .= "\t\t\t<title>" . $row['the_title'] . "</title>\n";
	$XML .= "\t\t\t<description><![CDATA[Road Condition: " . $row['notes'] . "<BR />\n";
	$XML .= "Reported: " . $row['as_of'] . "<BR />\n";
	$XML .= "Location: " . $row['address'] . "<BR />\n";
	$XML .= "Latitude: " . $row['lat'] . ", Longitude: " . $row['lng'] . "<BR />\n";
	$XML .= "<img src=\"http://www.gw4x4r.co.uk/gw4x4r_logo.jpg\" height='40'>]]></description>\n";
	$XML .= "\t\t\t<category><![CDATA[" . $row['type'] . "]]></category>\n";
	$XML .= "\t\t\t<link>http://gw4x4r.co.uk</link>\n";	
	$XML .= "\t\t<georss:point>" . $row['lat'] . " " . $row['lng'] . "</georss:point>\n";
	$XML .= "\t\t</item>\n";
	$i++;
	}
$XML .= "\t</channel>\n";
$XML .= "</rss>\n";
echo $XML;