<?php
//$me = 6;		// responder id
/*
Calls module
3/31/2013 initial release
*/
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}		// 11/8/09
error_reporting (E_ALL	^ E_DEPRECATED);
require_once '../incs/functions.inc.php';		//7/28/10
require_once 'incs/sp_functions.inc.php';		// 4/8/2013

@session_start();
if (! array_key_exists('SP', $_SESSION)) {
    header("Location: index.php");
    }
$me = $_SESSION['SP']['user_unit_id'] ;		// possibly empty
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo get_text("Calls");?></title>
    <link rel="stylesheet"  type="text/css" href="./css_default.php?rand=<?php echo time();?>" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1">-->
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <script src="./js/misc.js" type="text/javascript"></script>

<div style = "font-size: 1.0em">
<?php
dump($_SESSION['SP']);
?>
</div>
<?php
require_once 'incs/footer.php';
$idVal = ( array_key_exists("id", $_POST) )? $_POST['id'] : "" ;

?>
<form name = "navForm" method = post 	action = "<?php echo basename(__FILE__);?>">
<input type = hidden name = "id" 		value = "<?php echo $idVal;?>" />			<!-- array index of target record -->
<input type = hidden name = "id_str" 	value = "<?php echo $_POST['id_str'];?>" />
</form>

<form name = "respForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "resp_id" 	value = "" />
</form>

<form name = "tickForm" method = post 	action = "sp_tick.php?rand=<?php echo time();?>">
<input type = hidden name = "ticket_id" 	value = "" />
</form>

<script>
    function navTo(url, id) {
        var ts = Math.round((new Date()).getTime() / 1000);
        document.navForm.action = url +"?rand=" + ts;
        document.navForm.id.value = (id == null)? "": id;
        document.navForm.submit();
        }				// end function navTo ()
</script>
</body>
</html>
