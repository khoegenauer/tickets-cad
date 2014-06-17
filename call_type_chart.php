

<?php
require_once './incs/functions.inc.php';
?>

<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
<?php
$query = "SELECT `type`, COUNT(*) AS `nr`
    FROM `$GLOBALS[mysql_prefix]ticket` `t`
    LEFT JOIN `$GLOBALS[mysql_prefix]in_types` `y` ON `t`.`in_types_id` = `y`.`id`
    GROUP BY `type`
    ORDER BY `nr` DESC";


$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), __FILE__, __LINE__);

if (mysql_num_rows($result) > 0) {


  $string = "['Call Type', '# Calls'],";
    while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {			//
        $row ['type']  = ( @strlen ( $row ['type'] ) > 0 ) ? $row ['type'] : "Other" ;						// possible null/empty

        $string = $string . "['".$row ['type']."',".$row ['nr']."],";

        }
        $string = rtrim($string, ',');
        echo $string;
        }
else {		// a WTF situation?
    $err_arg = basename(__FILE__) . "/" . __LINE__;
    do_log ($GLOBALS['LOG_ERROR'], 0, 0, $err_arg);		// logs supplied error message
    }

?>
        ]);

        var options = {
          title: 'Calls by Type',
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="piechart_3d" style="width: 1200px; height: 700px;"></div>
  </body>
</html>
