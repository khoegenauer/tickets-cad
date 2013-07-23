<?php
/*
*/
error_reporting(E_ALL);	
	function dump ($variable) {
		echo "\n<PRE>";
		var_dump ($variable) ;
		echo "</PRE>\n";
		}
$instr = NULL;
dump (intval($instr));
dump (strval(intval($instr)));
dump (strval(intval($instr)) === $instr);
?>
