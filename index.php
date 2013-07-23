<?php 
error_reporting(E_ALL);		// 10/1/08
/*
8/21/10 - capts.inc.php added
8/25/10 utf8 collation set on table capts
8/27/10 setting added
8/30/10 captions data now handled as array
*/

if(!(file_exists("./incs/mysql.inc.php"))) {
	print "This appears to be a new Tickets installation; file 'mysql.inc.inc' absent. Please run <a href=\"install.php\">install.php</a> with valid database configuration information.";
	exit();
	}

@session_start();

require_once('./incs/functions.inc.php');
$version = "2.12 A beta - 10/8/10";	

/*
10/1/08 added error reporting
1/11/09 added call frame, 'auto_route' setting
1/17/09 "ALTER TABLE `assigns` CHANGE `in-quarters` `on_scene` DATETIME NULL DEFAULT NULL"
2/1/09 version  no.
2/2/09 un_status schemae changes, version no.
2/24 comment re terrain setting
3/25/09 schema update
4/1/09 new settings added
7/7/09 function do_setting added, smtp_acct, email_from, 'multi' to responders
7/7/09 added protocol to in_types, utc_stamp
7/14/09 auto-size CB frame
7/29/09 added gtrack url setting, LocateA, Gtrack, Glat and Handle fields to responder table
8/2/09 added maptype setting which controls google map type default display
8/3/09 added locale setting which controls USNG, OSGB and UTM display plus date format
8/5/09 added user defined function key settings (3 keys).
8/19/09	added circle attrib's to in_types
11/1/09 Added setting for reverse geocoding on or off when setting location of incident.
11/11/09 Version no. to  11B
11/23/09 Version no. to  11C
1/3/10 added 'by' field to ticket table, for multi-user operation
1/8/10 added fields to table 'user' to support multi-user operation
1/23/10 session housekeeping
2/4/10 added unit status and fac_status value coloring
3/3/10 removed session destroy()
3/12/10 table `constituents` added
3/21/10 pie chart settings added
3/24/10 tables `in_types`, `un_status` revised
4/5/10 tag closure, version no.
4/7/10 unit_status_chg setting added, 'mu_init.php' renamed to 'get_latest.php'
4/11/10 added table 'pin_control' for asterisk integration
4/30/10 added three add'l phone fields to consx table
5/4/10 added responder_id (for use with level = 'unit') to user table
5/11/10 added miscellaneous to table consx
5/19/10 version update test added
6/20/10 schema changes per KJ email
6/25/10 user dob to text type
6/26/10 added set_severity to table in_types
6/27/10 corrected 911 field for prefix
7/6/10 address elements to responder, facilities schema, by AH
7/21/10 setting 'unit_status_chg' removed
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/5/10 internet setting added
8/8/10 install required if mysql.inc.php absent
8/13/10 gettext table inserted
8/17/10 gettext table renamed to 'captions'
10/8/10 version number date change
*/

//snap(basename(__FILE__) . " " . __LINE__  , count($_SESSION));

$cb_per_line = 22;				// 6/5/09
$cb_fixed_part = 60;
$cb_min = 96;
$cb_max = 300;

/*
if ($istest) {											// 12/13/09
	$query = "CREATE TABLE IF NOT EXISTS `{$snap_table}` (
		`id` int(4) NOT NULL AUTO_INCREMENT,
		`source` text,
		`stuff` text,
		`when` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);	
	}

SET @@global.sql_mode= '';
sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
*/
function do_setting ($which, $what) {				// 7/7/09
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]settings` WHERE `name`= '$which' LIMIT 1";		// 5/25/09
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	if (mysql_affected_rows()==0) {
		$query = "INSERT INTO `$GLOBALS[mysql_prefix]settings` ( `id` , `name` , `value` ) VALUES (NULL , '$which', '$what');";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		}
	unset ($result);
	return TRUE;
	}				// end function do_setting ()
	
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}

$old_version = get_variable('_version');

if (!($version == $old_version)) {		// current? - 5/19/10

	do_setting ('smtp_acct','');			// 7/7/09  
	do_setting ('email_from','');			// 7/7/09
	do_setting ('gtrack_url','');			// 7/7/09
	do_setting ('maptype','1');				// 8/2/09
	do_setting ('locale','0');				// 8/3/09
	do_setting ('func_key1','http://openises.sourceforge.net/,Open ISES');				// 8/5/09
	do_setting ('func_key2','');				// 8/5/09
	do_setting ('func_key3','');				// 8/5/09
	do_setting ('reverse_geo','0');				// 11/1/09
	do_setting ('logo','t.png');				// 11/1/09
	do_setting ('pie_charts','300/450/300');	// 3/21/10
//	do_setting ('unit_status_chg','0');			// 7/21/10
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]facilities` (`id` bigint(8) NOT NULL auto_increment, `name` text, `direcs` tinyint(2) NOT NULL default '1' COMMENT '0=>no directions, 1=> yes', `description` text NOT NULL, `capab` varchar(255) default NULL COMMENT 'Capability', `status_id` int(4) NOT NULL default '0', `other` varchar(96) default NULL, `handle` varchar(24) default NULL, `contact_name` varchar(64) default NULL, `contact_email` varchar(64) default NULL, `contact_phone` varchar(15) default NULL, `security_contact` varchar(64) default NULL, `security_email` varchar(64) default NULL, `security_phone` varchar(15) default NULL, `opening_hours` mediumtext, `access_rules` mediumtext, `security_reqs` mediumtext, `pager_p` varchar(64) default NULL, `pager_s` varchar(64) default NULL, `send_no` varchar(64) default NULL, `lat` double default NULL, `lng` double default NULL, `type` tinyint(1) default NULL, `updated` datetime default NULL, `user_id` int(4) default NULL, `callsign` varchar(24) default NULL, `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` datetime NOT NULL, PRIMARY KEY  (`id`), UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1 AUTO_INCREMENT=43;";
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);			// 7/7/09
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_types` (`id` int(11) NOT NULL auto_increment, `name` varchar(16) NOT NULL, `description` varchar(48) NOT NULL, `icon` int(3) NOT NULL default '0', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL COMMENT 'ip', `_on` datetime NOT NULL, PRIMARY KEY  (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 COMMENT='Allows for variable facility types' AUTO_INCREMENT=19;";
	$result = mysql_query($query);		// 7/7/09
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]fac_status` (`id` bigint(4) NOT NULL auto_increment, `status_val` varchar(20) NOT NULL, `description` varchar(60) NOT NULL, `group` varchar(20) default NULL, `sort` int(11) NOT NULL default '0', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` datetime NOT NULL,  PRIMARY KEY  (`id`), UNIQUE KEY `ID` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=4;";
	$result = mysql_query($query);			// 7/7/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `facility` INT( 4 ) NULL DEFAULT NULL AFTER `phone`;";
	$result = mysql_query($query);		// 8/1/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `rec_facility` INT( 4 ) NULL DEFAULT NULL AFTER `facility`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `booked_date` DATETIME NULL DEFAULT NULL AFTER `updated`;";
	$result = mysql_query($query);			// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `facility` INT(7) NULL DEFAULT NULL AFTER `info`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `rec_facility` INT(7) NULL DEFAULT NULL AFTER `facility`;";
	$result = mysql_query($query);			// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]log` ADD `mileage` INT(8) NULL DEFAULT NULL AFTER `rec_facility`;";
	$result = mysql_query($query);			// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `facility_id` INT(8) NULL DEFAULT NULL AFTER `on_scene`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `rec_facility_id` INT(8) NULL DEFAULT NULL AFTER `facility_id`;";
	$result = mysql_query($query);			// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` CHANGE `comments` TEXT;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2fenr` DATETIME NULL DEFAULT NULL AFTER `rec_facility_id`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `u2farr` DATETIME NULL DEFAULT NULL AFTER `u2fenr`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `start_miles` INT(8) NULL DEFAULT NULL AFTER `comments`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]assigns` ADD `end_miles` INT(8) NULL DEFAULT NULL AFTER `start_miles`;";
	$result = mysql_query($query);		// 10/6/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `multi` INT( 1 ) NOT NULL DEFAULT 0 COMMENT 'if 1, allow multiple call assigns' AFTER `direcs`;";
	$result = mysql_query($query);			// 7/7/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `protocol` VARCHAR( 255 ) NULL AFTER `description` ;";
	$result = mysql_query($query);			// 7/7/09
	
	$query	= "ALTER TABLE `$GLOBALS[mysql_prefix]tracks_hh` ADD `utc_stamp` BIGINT( 12 ) NOT NULL DEFAULT 0 COMMENT 'Position timestamp in UTC' AFTER `altitude` ;";
	$result = mysql_query($query);
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `locatea` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses LocateA tracking - required to set callsign' AFTER `instam`;";
	$result = mysql_query($query);		// 7/29/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `gtrack` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Gtrack tracking - required to set callsign' AFTER `locatea`;";
	$result = mysql_query($query);		// 7/29/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `glat` TINYINT( 2 ) NOT NULL DEFAULT 0 COMMENT 'if 1 unit uses Google Latitude tracking - required to set callsign' AFTER `gtrack`;";
	$result = mysql_query($query);			// 7/29/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `handle` VARCHAR( 24 ) NULL DEFAULT NULL COMMENT 'Unit Handle' AFTER `callsign`;";
	$result = mysql_query($query);		// 7/29/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `radius` INT( 4 ) NOT NULL DEFAULT 0 COMMENT 'enclosing circle',
				ADD `color` VARCHAR( 8 ) NULL DEFAULT NULL ,
				ADD `opacity` INT( 3 ) NOT NULL DEFAULT '0';";
	$result = mysql_query($query);			// 8/19/09
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]chat_invites` ( `id` int(7) NOT NULL AUTO_INCREMENT, `to` varchar(64) NOT NULL COMMENT 'comma sep''d, 0 = all', `_by` int(7) NOT NULL, `_from` varchar(16) NOT NULL, `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1;"; // 12/23/09
	$result = mysql_query($query);		// 10/21/09
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `hide` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `description` ;";
	$result = mysql_query($query);		// 10/21/09
				
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `_by` INT( 7 ) NOT  NULL DEFAULT '0' COMMENT 'Call taker id' ";
	$result = mysql_query($query);		//1/3/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `expires` TIMESTAMP NULL  DEFAULT NULL COMMENT 'session start time';";
	$result = mysql_query($query);		// 1/8/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `sid` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'php session id';";
	$result = mysql_query($query);		// 1/8/10
		
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `login` TIMESTAMP NULL COMMENT 'last login';";
	$result = mysql_query($query);		// 1/23/10
		
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `_from` VARCHAR( 24 ) NULL COMMENT 'IP addr';";
	$result = mysql_query($query);		// 1/23/10
		
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user`  ADD `browser` VARCHAR( 40 ) NULL COMMENT 'used at last login';";
	$result = mysql_query($query);		// 1/23/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' COMMENT 'background color',
		ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' COMMENT 'text color'";
	$result = mysql_query($query);		// 2/4/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]fac_status` ADD `bg_color` VARCHAR( 16 ) NOT NULL DEFAULT 'transparent' AFTER `sort` ,
		ADD `text_color` VARCHAR( 16 ) NOT NULL DEFAULT '#000000' AFTER `bg_color`";
	$result = mysql_query($query);		// 2/4/10
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]constituents` (
		`id` bigint(8) NOT NULL AUTO_INCREMENT,
		`contact` varchar(48) NOT NULL,
		`street` varchar(48) DEFAULT NULL,
		`apartment` varchar(48) DEFAULT NULL,
		`city` varchar(48) DEFAULT NULL,
		`state` char(2) DEFAULT NULL,
		`miscellaneous` varchar(80) DEFAULT NULL,
		`phone` varchar(16) NOT NULL,
		`email` varchar(48) DEFAULT NULL,
		`lat` double DEFAULT NULL,
		`lng` double DEFAULT NULL,
		`updated` varchar(16) DEFAULT NULL,
		`_by` int(7) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10
	
	//			// 3/24/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` 
		CHANGE `type` `type` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `description` `description` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `sort` `sort` INT( 11 ) NULL DEFAULT NULL ,
		CHANGE `radius` `radius` INT( 4 ) NULL DEFAULT NULL ,
		CHANGE `opacity` `opacity` INT( 3 ) NULL DEFAULT NULL";	
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` CHANGE `description` `description` VARCHAR( 60 ) 
		CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
		CHANGE `sort` `sort` INT( 11 ) NOT NULL DEFAULT '0'";
	$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/24/10
	
	$query = "CREATE TABLE IF NOT EXISTS `$GLOBALS[mysql_prefix]pin_ctrl` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `responder_id` int(7) NOT NULL DEFAULT '0' COMMENT 'link to responder record',
		  `pin` varchar(4) NOT NULL COMMENT 'login authentication ',
		  `_by` int(7) NOT NULL COMMENT 'user creating/updating this entry',
		  `_from` varchar(30) DEFAULT NULL COMMENT 'IP address',
		  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$result = mysql_query($query);		// 4/11/10
											// 4/30/10
	/* query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_2` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone` ,
		ADD `phone_3` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone_2` ,
		ADD `phone_4` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `phone_3` ,
		ADD INDEX ( `phone_2` , `phone_3` , `phone_4` ) ";
	$result = mysql_query($query);
	*/
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` CHANGE `id` `id` BIGINT( 7 ) NOT NULL AUTO_INCREMENT ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `contact` 		VARCHAR(48) NULL DEFAULT NULL AFTER `id` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `street` 		VARCHAR(48) NULL DEFAULT NULL AFTER `contact` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `apartment` 		VARCHAR(48) NULL DEFAULT NULL AFTER `street` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `city` 			VARCHAR(48) NULL DEFAULT NULL AFTER `apartment` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `state` 			char(2) 	NULL DEFAULT NULL AFTER `city` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `miscellaneous` 	VARCHAR(80) NULL DEFAULT NULL AFTER `state` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone` 			VARCHAR(16) NULL DEFAULT NULL AFTER `miscellaneous` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_2` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_3` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone_2` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `phone_4` 		VARCHAR(16) NULL DEFAULT NULL AFTER `phone_3` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `email` 			VARCHAR(48) NULL DEFAULT NULL AFTER `phone_4` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lat` 			double 		NULL DEFAULT NULL AFTER `email` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `lng` 			double 		NULL DEFAULT NULL AFTER `lat` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `updated` 		VARCHAR(16) NULL DEFAULT NULL AFTER `lng` ";
	$result = mysql_query($query);		// 5/11/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]constituents` ADD `_by` 			int(7) 		NULL DEFAULT NULL AFTER `updated` ";
	$result = mysql_query($query);		// 5/11/10
	
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` ADD `responder_id` INT( 7 ) NOT NULL DEFAULT '0' COMMENT 'For level = unit' AFTER `level` ";		// 5/4/10
	$result = mysql_query($query);			// 10/6/09
//snap (__LINE__ , microtime_float());

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]chat_messages` CHANGE `message` `message` VARCHAR( 2048 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ";
	$result = mysql_query($query);		// 5/29/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]un_status` ADD `dispatch` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0 - can dispatch, 1- no - inform, 2 - enforce' AFTER `description`";
	$result = mysql_query($query);		// 5/30/10

	do_setting ('sound_wav','aooga.wav');			// 6/12/10
	do_setting ('sound_mp3','phonesring.mp3');		// 6/12/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` 
		CHANGE `facility` `facility` INT( 4 ) NULL DEFAULT '0',
		CHANGE `rec_facility` `rec_facility` INT( 4 ) NULL DEFAULT '0'";
	$result = mysql_query($query);		// 6/20/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL";
	$result = mysql_query($query);				// 6/20/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` 
		CHANGE `_by` `_by` INT( 7 ) NULL DEFAULT NULL ,
		CHANGE `_from` `_from` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
		CHANGE `_on` `_on` DATETIME NULL DEFAULT NULL" ;		// 6/20/10
	$result = mysql_query($query);		// 2/4/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]user` CHANGE `dob` `dob` TEXT NULL DEFAULT NULL ";		// 6/25/10
	$result = mysql_query($query);	
															// 6/26/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
	$result = mysql_query($query);	
															// 6/27/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]ticket` ADD `nine_one_one` VARCHAR( 96 ) NULL DEFAULT NULL COMMENT 'comments re 911' AFTER `comments` ";
	$result = mysql_query($query);	

// AH 7/6/10
	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name` ";
	$result = mysql_query($query);

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
	$result = mysql_query($query);		// 7/5/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
	$result = mysql_query($query);		// 7/5/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]responder` ADD `phone` VARCHAR( 16 ) NULL DEFAULT NULL AFTER `state`;";
	$result = mysql_query($query);		// 7/5/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `street` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `name`;";
	$result = mysql_query($query);		// 7/5/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `city` VARCHAR( 28 ) NULL DEFAULT NULL AFTER `street`;";
	$result = mysql_query($query);		// 7/5/10

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]facilities` ADD `state` CHAR( 2 ) NULL DEFAULT NULL AFTER `city`;";
	$result = mysql_query($query);		// 7/5/10

// AH

	$query = "DELETE FROM `$GLOBALS[mysql_prefix]settings` WHERE `settings`.`name` = 'unit_status_chg' LIMIT 1;";
	$result = mysql_query($query);		// 7/21/10

	$query = "UPDATE `$GLOBALS[mysql_prefix]settings` SET `value`=". quote_smart($version)." WHERE `name`='_version' LIMIT 1";	// 5/28/08
	$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);

	$the_table = "$GLOBALS[mysql_prefix]captions";

	if (!(mysql_table_exists($the_table))) {					// 8/13/10, 8/25/10
						
		$query = "CREATE TABLE IF NOT EXISTS `{$the_table}` (
			  `id` int(7) NOT NULL AUTO_INCREMENT,
			  `capt` varchar(36) NOT NULL,
			  `repl` varchar(36) NOT NULL,
			  `_by` int(7) NOT NULL DEFAULT '0',
			  `_from` varchar(16) NOT NULL DEFAULT '''''',
			  `_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;";
		$result = mysql_query($query) or do_error("", 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__); 		// 3/12/10	

		require_once ("./incs/capts.inc.php");		// get_text captions as an array 8/17/10
													//
		for ($i=0; $i< count($capts); $i++) {		// 8/29/10
			$temp = quote_smart($capts[$i]);
	
			$query = "INSERT INTO `{$the_table}` (`capt`, `repl`) VALUES ($temp, $temp);";
//			dump($query);
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			}
		
		unset ($result);

		}	// end if (!(mysql_table_exists($the_table))
	}		// end (!($version ==...)

do_setting ('internet','1');						// 8/5/10 - just in case
do_setting ('disp_stat','D/R/O/FE/FA/Clear');		// 8/29/10 - dispatch status tags
do_setting ('oper_can_edit','0');					// 8/27/10  
	
$temp = explode(" ", get_variable('_version'));	
$disp_version = $temp[0];	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<HEAD>
	<META NAME="ROBOTS" CONTENT="INDEX,FOLLOW" />
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<META HTTP-EQUIV="Script-date" CONTENT="<?php print date("n/j/y G:i", filemtime(basename(__FILE__)));?>" /> <!-- 7/7/09 -->
	<TITLE>Tickets <?php print $disp_version;?></TITLE>
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
	<link rel="shortcut icon" href="favicon.ico" />
</HEAD>

<?php			// 7/14/09
$buster = strval(rand());			//  cache buster
if (get_variable('call_board') == 2) {
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, 0 ,*" BORDER="<?php print get_variable('frameborder');?>" BORDERCOLOR="#ff0000">
	<FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC='board.php?stuff=<?php print $buster;?>' ID = 'what' NAME='calls' SCROLLING='AUTO' />	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
<?php 
	}
else  {
?>
	<FRAMESET ID = 'the_frames' ROWS="<?php print (get_variable('framesize') + 25);?>, *" BORDER="<?php print get_variable('frameborder');?>">
	<FRAME SRC="top.php?stuff=<?php print $buster;?>" NAME="upper" SCROLLING="no" />
	<FRAME SRC="main.php?stuff=<?php print $buster;?>" NAME="main" />
<?php
	}
?>
	<NOFRAMES>
	<BODY>
		Tickets requires a frames-capable browser.
	</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
<?php

	$query = "ALTER TABLE `$GLOBALS[mysql_prefix]in_types` ADD `set_severity` INT( 1 ) NOT NULL DEFAULT '0' COMMENT 'sets incident severity' AFTER `protocol`";
	$result = mysql_query($query);	
?>	