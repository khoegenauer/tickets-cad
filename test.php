<?php

$x = "asasas(123123123123123123123123123123_99)";
$allowed = "/[^\d]/";
echo  preg_replace($allowed,"",$x);
?>