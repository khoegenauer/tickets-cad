<?php

include'./incs/error_reporting.php';

@session_start();
require_once($_SESSION['fip']);		//7/28/10
echo (string) get_cb_height ();
