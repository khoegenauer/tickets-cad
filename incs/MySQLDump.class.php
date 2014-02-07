<?php
/**
 * MySQL Dump PHP class by CubeScripts, www.cubescripts.com
 * @package MySQLDump.class.php
 * @author Cubescripts <john.doe@example.com>
*/

/**
 * MySQLDump
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class MySQLDump {

    var $tables = array();
    var $connected = false;
    var $output;
    var $droptableifexists = false;
    var $mysql_error;

/**
 * connect
 * Insert description here
 *
 * @param $host
 * @param $user
 * @param $pass
 * @param $db
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function connect($host,$user,$pass,$db) {
    $return = true;
    $conn = @mysql_connect($host,$user,$pass);
    if (!$conn) { $this->mysql_error = mysql_error(); $return = false; 	}
    $seldb = @mysql_select_db($db);
    if (!$conn) { $this->mysql_error = mysql_error();  $return = false; 	}
    $this->connected = $return;

    return $return;
    }

/**
 * list_tables
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function list_tables() {
    $return = true;
    if (!$this->connected) { $return = false; 	}
    $this->tables = array();
    $sql = mysql_query("SHOW TABLES");
    while ($row = mysql_fetch_array($sql)) {
        array_push($this->tables,$row[0]);
        }

    return $return;
    }

/**
 * list_values
 * Insert description here
 *
 * @param $tablename
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function list_values($tablename) {
    $sql = mysql_query("SELECT * FROM $tablename");
    $this->output .= "\n\n-- Dumping data for table: $tablename\n\n";
    while ($row = mysql_fetch_array($sql)) {
        $broj_polja = count($row) / 2;
        $this->output .= "INSERT INTO `$tablename` VALUES(";
        $buffer = '';
        for ($i=0;$i < $broj_polja;$i++) {
            $vrednost = $row[$i];
            if (!is_integer($vrednost)) { $vrednost = "'".addslashes($vrednost)."'"; 	}
            $buffer .= $vrednost.', ';
            }
        $buffer = substr($buffer,0,count($buffer)-3);
        $this->output .= $buffer . ");\n";
        }
    }

/**
 * dump_table
 * Insert description here
 *
 * @param $tablename
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function dump_table($tablename) {
    $this->output = "";
    $this->get_table_structure($tablename);
    $this->list_values($tablename);
    }

/**
 * get_table_structure
 * Insert description here
 *
 * @param $tablename
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function get_table_structure($tablename) {
//	snap(__LINE__, $tablename);
    $arr = array("NO" => "NOT NULL", "YES" => "NULL");

    $this->output .= "\n\n-- Dumping structure for table: $tablename\n\n";
    if ($this->droptableifexists) { $this->output .= "DROP TABLE IF EXISTS `$tablename`;\nCREATE TABLE `$tablename` (\n"; 	}
        else { $this->output .= "CREATE TABLE `$tablename` (\n"; 	}
    $sql = mysql_query("DESCRIBE $tablename");		// returns resource(22) of type (mysql result)
//	dump($tablename . ": " . gettype($sql));
    $this->fields = array();
    while ($row = mysql_fetch_array($sql)) {
        $name = $row[0]; //name
        $type = $row[1]; //type
        $null = $arr[strtoupper(trim($row[2]))]; //null?

        $key = $row[3]; //(primary) key - PRI za primary
        if ($key == "PRI") { $primary = $name; 	}
        $default = $row[4]; //default
        $extra = $row[5];
        if ($extra !== "") { $extra .= ' '; 	} //makeup
        $this->output .= "  `$name` $type $null $extra,\n";
        }
    $this->output .= "  PRIMARY KEY  (`$primary`)\n);\n";
    }

    }
?>
