<?php

include'./incs/error_reporting.php';
$a = 1;

function x() {
    $b = 2;


    function y() {
        global $a;
        global $b;
        echo $a;
        echo $b;
        }	//  end y
    }		// end x
