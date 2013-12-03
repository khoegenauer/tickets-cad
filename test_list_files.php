<?php
/**
 * @package test_list_files.php
 * @author John Doe <john.doe@example.com>
 * @since version
 * @version 2013-09-10
 */
/*
9/10/13 - new file, lists tickets that are assigned to the mobile user
*/
@session_start();
require_once('./incs/functions.inc.php');
print list_files(0, 0, 0, 0, 1)
?>