<?php
/*
Calls module
3/31/2013 initial release
11/2/2013 added group by type
status_about 
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09 
error_reporting (E_ALL	^ E_DEPRECATED);
@session_start();
if (! array_key_exists('SP', $_SESSION)) {
	header("Location: index.php");
	}
require_once('../incs/functions.inc.php');		//7/28/10
require_once('incs/sp_functions.inc.php');		// 4/8/2013 
$me = $_SESSION['SP']['user_unit_id'] ;			// possibly empty
?>
<!DOCTYPE html> 
<html lang="en"> 
<head>
	<meta charset="utf-8" />
	<title>Tickets SP <?php echo get_text("Facilities");?></title>
	<link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1">-->
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<script src="./js/misc.js" type="text/javascript"></script> 

<?php
//		===============================		TOP		==========================================================
	if ( ( isset($_POST['id'] ) ) && (  strlen ( $_POST['id'] ) > 0 )  ) {
																		/*	show the one record	*/
		$id_array = explode (",", $_POST['id_str']);
		$the_id = $id_array[intval($_POST['id'])];		// nth entry is record id
		
		function get_sidelinks () {		// returns 2-element array of strings
			global $id_array;
			$out_arr = array("", "");
			if ( $_POST['id'] > 0 ) {		// if not at array origin then a prior one exists
				$query = "SELECT `handle` AS `left_one`	FROM `$GLOBALS[mysql_prefix]facilities` 
					WHERE `id` = {$id_array[($_POST['id']-1)]} LIMIT 1";
				$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				$in_row = stripslashes_deep(mysql_fetch_array($result));
				$out_arr[0] = $in_row['left_one'];
				}
			if ( $_POST['id'] < count ($id_array)-1 ) {		// then not at end
				$query = "SELECT `handle` AS `right_one` FROM `$GLOBALS[mysql_prefix]facilities` 
					WHERE `id` = {$id_array[($_POST['id']+1)]} LIMIT 1";
				$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
				$in_row = stripslashes_deep(mysql_fetch_array($result));
				$out_arr[1] = $in_row['right_one'];
				}
			return $out_arr;			
			}

?>
<script>
		
	DomReady.ready(function() {
		var id_array = document.navForm.id_str.value.split(",");		
		var timer = setInterval(function(){getLocation(<?php echo $me;?>)}, (60*1000)) ;		// get position one per minute		    	
		});
	
//	do_set_time("dispatched", {$the_id}, 0)
	function do_set_time (column, record_id, cell_id, function_id) {		// ajax call to set selected dispatch time
	
		function the_callback(req) {
			document.getElementById(cell_id).innerHTML=req.responseText;
			CngClass(cell_id, "bright");									// highlight for 2 seconds
			setTimeout ( function() {  CngClass(cell_id, "plain")}, 3000);
			}		// end function the_callback()
	
		var params = "the_column="+ column + "&record_id=" +record_id + "&function_id=" + function_id;		// 
		var url = "./ajax/set_disp_times.php";
		sendRequest(url,the_callback, params);		//  issue AJAX request
		}		// end function do set_time
	
	function call_map ( ) {
		document.navForm.action = 'sp_map_spec.php';	// 
		document.navForm.submit();
		}
</script>
<?php
if (intval(get_variable('broadcast'))==1) {	
//	require_once('./incs/sp_socket2me.inc.php');		//6/27/2013 
	}
?>		
		</head>
		<body>			<!-- <?php echo __LINE__; ?> -->
<?php
		
			function set_click_td ($rcd_id, $cell_id, $the_str) {
				$click_str = "onclick = \"do_set_time('{$the_str}', {$rcd_id}, '{$cell_id}', 0)\"";
				return "<td id = '{$cell_id}' {$click_str} class='click'><b>Set</b></td></tr>\n";
				}
				
			require_once('incs/header.php');	
		 	$id_array = explode (",", $_POST['id_str']);
		 	$link_arr = get_sidelinks ();
		 	
		 	$larrow = (intval($_POST['id'] == 0))? "" : "&laquo;&nbsp; <span style = 'font-size: 50%;'>{$link_arr[0]}</span>" ;					// suppress display if at origin
			$rarrow = (intval($_POST['id']) == count ($id_array) -1 )? "" : "<span style = 'font-size: 50%;'>{$link_arr[1]}</span>&nbsp;&raquo;" ;	// suppress display if at end

?>

		<div style='float:left; '>
			<div id = "left-side" onclick = 'navBack();' style = "position:fixed; left: 0px; top:125px; margin-left:10px; font-size: 4.0em; opacity:0.50;"><?php echo $larrow; ?></div>
		</div>
		<div style='float:right; '>
			<div id = "right-side" onclick = 'navFwd ();' style = "position:fixed; right: 25px; top:125px;font-size: 4.0em; opacity:0.5;"><?php echo $rarrow; ?></div>
		</div>
<?php	
			$the_count =  count ($id_array);			
			$the_nth = intval($_POST['id']) + 1;
			echo "\n<br/><br/><center><h2>Selected " . get_text("Facility") . " (# {$the_nth} of {$the_count} )</h2>\n";
			$div_height = $_SESSION['SP']['scr_height'] - 120;								// nav bars				
			$div_width = floor($_SESSION['SP']['scr_width'] * .6) ;							// allow for nav arrows		
			echo "<div style = 'height:{$div_height}px; width:auto; overflow: auto; width:{$div_width}px;'>";	

			echo "<table border=1>\n";
		
			$query = get_fac_sql ($the_id);
					
			$result = mysql_query($query) or do_error($query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
			$row = stripslashes_deep(mysql_fetch_array($result)) ;
//			dump($row);
			$hides = array("id", "Status_id", "lat", "lng", "icon");								// hide these columns
			for ($i=0; $i< mysql_num_fields($result); $i++) {						// each field
				if (!(substr(mysql_field_name($result, $i ), 0, 1) == "_")) {  		// meta-data?			
					if (!in_array(mysql_field_name($result, $i), $hides)) {				
						if ( ! ( empty($row[$i] ) ) ) {			
							$fn = get_text ( ucfirst(mysql_field_name($result, $i ) ) );
							echo "<tr><td>{$fn}:</td><td>";
	
							echo $row[$i];
								
							echo "</td></tr>\n";
	
							}
						}
					}				// end meta-data?
				}		// end for ($i...) each row element


			echo "</table></div>\n";
			
			$id_array = explode (",", $_POST['id_str']);
			}
//		==============  list =================	MIDDLE 		================= list ==========================
else {								// list

		function sp_show_facy_list($res, $hides, $proc, $top_row_txt = "" ) {					// returns table for display - 10/26/2013
			$ini_array = parse_ini_file("./incs/sp.ini");
			$types_lim = ( array_key_exists ( "facy_count", $ini_array ) ) ? intval ( $ini_array['facy_count'] ) : "15" ;
			$do_types = ( mysql_num_rows ( $res ) > $types_lim );
		
			$div_height = $_SESSION['SP']['scr_height'] - 200;									// nav bars			
			$out_str = "<div id = 'sp_show_list' style = 'border:5px solid blue; height:{$div_height}px; width:auto; overflow: auto; text-align:center; '><!-- " . basename(__FILE__) . __LINE__ . " -->\n";
		
			mysql_data_seek($res, 0) ;										// reset for data iteration
			
			$out_str = "<br/><br/><br/>\n<table id = 'table' class='list table-striped' border = 2  align='center' style = 'width:98%;'>\n";
			$out_str .= "<thead>\n<tr class = 'even' valign = 'bottom'><th colspan = 99 align = 'center'><b>{$top_row_txt}</b></th></tr>\n";
			$out_str .= "<tr class = 'odd'>";
			for ($i=1; $i< mysql_num_fields($res); $i++) {				// header row - skip id column
				if (!in_array(mysql_field_name($res, $i), $hides)) {
					$out_str .= "<th>" . do_colname ( mysql_field_name($res, $i)) . "</th>\n";		// field name
					}
				}
			$out_str .= "</tr>\n</thead>\n<tbody>\n";		//			$disp_val = ( $i == 0 ) ) ? "''"  : "'none'";
		
			mysql_data_seek($res, 0) ;												// reset for data iteration
			$counts = array();
			while ($in_row = stripslashes_deep(mysql_fetch_assoc($res))) {			// counts by type
				if ( ! ( array_key_exists($in_row["facy_type"], $counts ) ) )  { $counts[$in_row["facy_type"]] = 0 ;}
				$counts[$in_row["facy_type"]]++;
				}
			$i = 0;				// row index
			$this_type = "";
		
			mysql_data_seek($res, 0) ;										//start at 1st row
		 	while ($in_row = stripslashes_deep(mysql_fetch_array($res))) {					// 
				$this_class = $in_row['facy_type'];
				$this_style = ( $do_types )?  "display: none;" : "display: '';"; 
	
			 	if ( ( ($do_types) && $in_row['facy_type'] != $this_type ) ) {				// we have a type change - do button		
			 		$out_str .=  "\n<tr class = 'perm' onclick = \"changeRows('table', '{$this_class}');\">
				 			<td colspan=9 align = 'center'><button type='button'><b>{$in_row['facy_type']}</b></button> ({$counts[$in_row['facy_type']]})</td></tr>\n\n";
				 	$this_type = $in_row['facy_type'];
			 		}
		
				$url = "navTo('{$proc}', {$i})";												// set for row click
		
				$out_str .= "<tr class = '{$this_class}' style = \"{$this_style}\" onclick = \"{$url};\">\n";	
				for ($j=1; $j< mysql_num_fields($res); $j++) {
					if (!in_array(mysql_field_name($res, $j), $hides)) {					// hides?			
						$display_val = htmlentities(shorten($in_row[$j], COL_WIDTH));					
						$out_str .= "\t<td>{$display_val}</td>\n";							// field value
						}
					}
				$out_str .= "</tr>\n";
				$i++;		
				}						// end while ($in_row ... )
		
				$total =  mysql_num_rows($res) ;
				if ( $do_types ) { 
					$out_str .=  "\n<tr class = 'perm' onclick = \"allRows( '' );\">
						<td colspan=9 align = 'center'><button type='button'><b>All</b></button> ({$total})</td></tr>\n\n";
					}

			$out_str .= "</tbody>\n</table><!-- " . basename(__FILE__) . __LINE__ . " -->\n";
			return $out_str;
			}		// end function sp_show_facy_list()

												// beds in use?
		$query = "SELECT `f`.`beds_a`, `f`.`beds_o`, `f`.`beds_info` FROM `$GLOBALS[mysql_prefix]facilities` `f` 
			WHERE ( TRIM(`f`.`beds_a` <> '') OR TRIM(`f`.`beds_o`) <> '' OR TRIM(`f`.`beds_info`) <> '' )
			LIMIT 1";
		$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		
		$do_beds = (mysql_num_rows ($result) > 0 ) ? 
			"CONCAT_WS('/',`beds_a`,`beds_o`) AS `beds A/O`,`beds_info` AS `beds info`,"  :
			"" ;		
		
		$query = "SELECT `f`.`id`, 
					`handle` 										AS `facility`, 
					`y`.`name` 										AS `facy_type`, 
					{$do_beds}
					`f`.`description` 								AS `description`, 
					`s`.`status_val` 								AS `status`,
					CONCAT_WS(' ',`street`,`city`) AS `location`,
					SUBSTRING(CAST(`f`.`updated` AS CHAR),9,8 ) 	AS `as of`
				FROM `$GLOBALS[mysql_prefix]facilities` `f` 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_types` `y` ON `f`.type = `y`.id 
				LEFT JOIN `$GLOBALS[mysql_prefix]fac_status` `s` ON `f`.status_id = `s`.id 
				ORDER BY `facy_type` ASC, `facility` ASC";					
		
		$result = mysql_query($query) or do_error($as_query,'mysql_query() failed',mysql_error(), basename( __FILE__), __LINE__);
		snap(__LINE__, $query);
		$sep = $_POST['id_str'] = "";
		while ($in_row = stripslashes_deep(mysql_fetch_array($result))) {		// to comma sep'd string of id's
		 	$_POST['id_str'] .= $sep . $in_row['id'];
		 	$sep = ",";
		 	}
		@mysql_data_seek($result, 0) ;		// reset for pass 2
		
?>
		<script>
		    DomReady.ready(function() {
		    	document.getElementById("ff").style.display = "none";
				});

		function allRows(styleval) {
			tr=document.getElementsByTagName('tr');
			for (i=0;i<tr.length;i++){
				tr[i].style.display = styleval;
				}
			}			// end function allRows()
		
		function changeRows(theTableID, theClass) {				// applies 'show' to theClass rows in table theTableID
			var table = document.getElementById(theTableID);
			for (var i = 3; i<table.rows.length; i++) {
				if (table.rows[i].className != 'perm') {
					if ( table.rows[i].className == theClass)	{ table.rows[i].style.display = '';}		// show
					else  										{ table.rows[i].style.display = 'none';}	// no
					}
				}  
			}				// end function changeRows()
	</script>
	</head>
<body>				<!-- <?php echo __LINE__; ?> -->
	<center>
<?php
			require_once('incs/header.php');	
			if ( mysql_num_rows($result) == 0 ) {
				echo "<div style = 'text-align:center; margin-top:200px;'><h2>No " . get_text("Facilities") . " in database</h2></div>\n";		
				}
			else {
//				echo "<div style = 'text-align:center; margin-top:60px;'><h2>" . get_text("Facilities") . " - <i>click/tap for details</i></h2></div>\n";
				$hides = array("the_group", "as_of", "assign_id", "id", "lat", "lng");		// hide these columns
				$top_row = get_text("Facilities") . " - <i>click/tap for details</i>";
//				echo sp_show_list($result, $hides, basename(__FILE__) , $top_row) . "\n" ;	
				echo sp_show_facy_list($result, $hides, basename(__FILE__) , $top_row) . "\n" ;	
				echo "<br/><br/>";			// show bottom rows
				}
			} 		// end else {}	===========================		BOTTOM		=====================================

	require_once('incs/footer.php');	
	$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
<input type = hidden name = "group" 	value = "<?php echo $GLOBALS['TABLE_FACILITY'];?>" />
</form>
<script>
	function navTo (url, id) {
		var ts = Math.round((new Date()).getTime() / 1000);
		document.navForm.action = url +"?rand=" + ts;
		document.navForm.id.value = (id == null)? "": id;
		document.navForm.submit();
		}				// end function navTo ()
</script>
</body>
</html>
