<?php

function do_insert_day_colors($name,$value) {			//	3/15/11
    $query = "INSERT INTO `$GLOBALS[mysql_prefix]css_day` (name,value) VALUES('$name','$value')";
    $result = mysql_query($query) or die("DO_INSERT_DAY_COLORS($name,$value) " . gettext('failed, execution halted'));
    }

function do_insert_night_colors($name,$value) {			//	3/15/11
    $query = "INSERT INTO `$GLOBALS[mysql_prefix]css_night` (name,value) VALUES('$name','$value')";
    $result = mysql_query($query) or die("DO_INSERT_NIGHT_COLORS($name,$value) " . gettext('failed, execution halted'));
    }

if (!mysql_table_exists("css_day")) {			//	3/15/11
    $query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_day` (`id` bigint(8) NOT NULL auto_increment,`name` tinytext,`value` tinytext, PRIMARY KEY  (`id`),UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

    do_insert_day_colors('page_background', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('normal_text', '000000');			//	3/15/11
    do_insert_day_colors('header_text', '000000');			//	3/15/11
    do_insert_day_colors('header_background', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('titlebar_text', '000000');			//	3/15/11
    do_insert_day_colors('links', '000099');			//	3/15/11
    do_insert_day_colors('other_text', '000000');			//	3/15/11
    do_insert_day_colors('legend', '000000');			//	3/15/11
    do_insert_day_colors('row_light', 'DEE3E7');			//	3/15/11
    do_insert_day_colors('row_light_text', '000000');			//	3/15/11
    do_insert_day_colors('row_dark', 'EFEFEF');			//	3/15/11
    do_insert_day_colors('row_dark_text', '000000');			//	3/15/11
    do_insert_day_colors('row_plain', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('row_plain_text', '000000');			//	3/15/11
    do_insert_day_colors('row_heading_background', '707070');			//	3/15/11
    do_insert_day_colors('row_heading_text', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('row_spacer', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('form_input_background', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('form_input_text', '000000');			//	3/15/11
    do_insert_day_colors('select_menu_background', 'FFFFFF');			//	3/15/11
    do_insert_day_colors('select_menu_text', '000000');			//	3/15/11
    do_insert_day_colors('label_text', '000000');			//	3/15/11
} // end if !table_exists css_day

if (!mysql_table_exists("css_night")) {			//	3/15/11
    $query = "CREATE TABLE `$GLOBALS[mysql_prefix]css_night` (`id` bigint(8) NOT NULL auto_increment,`name` tinytext,`value` tinytext,PRIMARY KEY  (`id`),UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    $result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

    do_insert_night_colors('page_background', '121212');			//	3/15/11
    do_insert_night_colors('normal_text', 'DAEDE2');			//	3/15/11
    do_insert_night_colors('header_text', 'DAEDE2');			//	3/15/11
    do_insert_night_colors('header_background', '2B2B2B');			//	3/15/11
    do_insert_night_colors('titlebar_text', 'FFFFFF');			//	3/15/11
    do_insert_night_colors('links', '3F23F7');			//	3/15/11
    do_insert_night_colors('other_text', 'FFFFFF');			//	3/15/11
    do_insert_night_colors('legend', 'ECFC05');			//	3/15/11
    do_insert_night_colors('row_light', 'BEC3C7');			//	3/15/11
    do_insert_night_colors('row_light_text', '04043D');			//	3/15/11
    do_insert_night_colors('row_dark', '9E9E9E');			//	3/15/11
    do_insert_night_colors('row_dark_text', '000000');			//	3/15/11
    do_insert_night_colors('row_plain', 'A3A3A3');			//	3/15/11
    do_insert_night_colors('row_plain_text', '000000');			//	3/15/11
    do_insert_night_colors('row_heading_background', '262626');			//	3/15/11
    do_insert_night_colors('row_heading_text', 'F0F0F0');			//	3/15/11
    do_insert_night_colors('row_spacer', 'F2E3F2');			//	3/15/11
    do_insert_night_colors('form_input_background', 'B5B5B5');			//	3/15/11
    do_insert_night_colors('form_input_text', '212422');			//	3/15/11
    do_insert_night_colors('select_menu_background', 'B5B5B5');			//	3/15/11
    do_insert_night_colors('select_menu_text', '151716');			//	3/15/11
    do_insert_night_colors('label_text', '000000');			//	3/15/11
} // end if !table_exists css_night




if (!mysql_table_exists("region"))
{
  $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region` (`id` bigint(8) NOT NULL AUTO_INCREMENT,`group_name` varchar(60) NOT NULL,`category` int(2) DEFAULT NULL,`description` varchar(60) DEFAULT NULL,`owner` int(2) NOT NULL DEFAULT '1',`def_area_code` varchar(4) DEFAULT NULL,`def_city` varchar(20) DEFAULT NULL,`def_lat` double DEFAULT NULL,`def_lng` double DEFAULT NULL,`def_st` varchar(20) DEFAULT NULL,`def_zoom` int(2) NOT NULL DEFAULT '10',`boundary` int(4) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
  $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
  $query = "INSERT INTO `$GLOBALS[mysql_prefix]region` (`id`, `group_name`, `category`, `description`, `owner`, `def_area_code`, `def_city`, `def_lat`, `def_lng`, `def_st`, `def_zoom`, `boundary`) VALUES (0, 'General', 4, 'General - group 0', 1, '', '', NULL, NULL, '10', 10, 0);";
  $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
}

if (!mysql_table_exists("region_type")) {	//	6/10/11
        $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]region_type` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(16) NOT NULL,
            `description` varchar(48) NOT NULL,
            `_on` datetime NOT NULL,
            `_from` varchar(16) NOT NULL,
            `_by` int(7) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11

        $query = "INSERT INTO `$GLOBALS[mysql_prefix]region_type` (`id`, `name`, `description`, `_on`, `_from`, `_by`) VALUES
            (1, 'EMS', 'Medical Services', '2011-06-17 14:21:39', '127.0.0.1', 1),
            (2, 'Security', 'Security Services', '2011-06-17 14:21:55', '127.0.0.1', 1),
            (3, 'Fire', 'Fire Services', '2011-06-17 14:22:10', '127.0.0.1', 1),
            (4, 'General', 'General Use', '2011-06-17 14:22:10', '127.0.0.1', 1);";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);		//	6/10/11
}

if (!mysql_table_exists("allocates")) {	//	6/10/11
        $query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]allocates` (
            `id` bigint(8) NOT NULL auto_increment,
            `group` int(4) NOT NULL default '1',
            `type` tinyint(1) NOT NULL default '1',
            `al_as_of` datetime default NULL,
            `al_status` int(4) default NULL,
            `resource_id` int(4) default NULL,
            `sys_comments` varchar(64) default NULL,
            `user_id` int(4) NOT NULL default  '0',
            PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        $result = mysql_query($query) or do_error($query , 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

        $now = mysql_format_date(time() - (intval(get_variable('delta_mins')*60)));
        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]ticket`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];
            $tick_stat = $row['status'];
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 1, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];	// 4/13/11
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 2, '$now', $tick_stat, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]facilities`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];	// 4/13/11
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 3, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";	// 4/13/11
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }

        $query_insert = "SELECT * FROM `$GLOBALS[mysql_prefix]user`;";
        $result_insert = mysql_query($query_insert);
        while ($row = stripslashes_deep(mysql_fetch_assoc($result_insert))) {
            $id = $row['id'];
            $query_a  = "INSERT INTO `$GLOBALS[mysql_prefix]allocates` (`group` , `type`, `al_as_of` , `al_status` , `resource_id` , `sys_comments` , `user_id`) VALUES
                    (1 , 4, '$now', 0, $id, 'Updated to Regional capability by upgrade routine' , 0)";
            $result_a = mysql_query($query_a) or do_error($query_a, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);
            }
}	//	End if "Allocates does not exist"

?>
