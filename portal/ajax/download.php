<?php
/**
 * 
 * 
 * @package download.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version string
 */
if(empty($_GET)) {
	exit();
	}
require_once('../../../incs/functions.inc.php');

$filename = "../../files/" . $_GET['filename'];
$properFilename = $_GET['origname'];
header("Content-Disposition: attachment; filename=\"$properFilename\";" );
header("Content-Transfer-Encoding: binary");
header("Pragma: public");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
readfile("$filename");
exit();
?>