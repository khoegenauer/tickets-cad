<?php

function count_responders() {	//	5/11/12 For quick start.
    $query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder`";
    $result = mysql_query($query);
    $count_responders = mysql_num_rows($result);

    return $count_responders;
    }

?>
