<?php
/**
 * @package get_tiles.php
 * @author John Doe <john.doe@example.com>
 * @since 2013-09-10
 * @version 2013-09-10
 */
/*
9/10/13 - New file - gets local copies of map tiles from OSM
*/
require_once './incs/functions.inc.php';
@session_start();

do_login(basename(__FILE__));
include'./incs/error_reporting.php';
set_time_limit(0);

$local = getcwd() . "/_osm/tiles/";

function directory_empty($path) {
    if (($files = @scandir($path)) && (count($files) > 2)) {
        return FALSE;
        } else {
        return TRUE;
        }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php print gettext('Tickets Map Configuration');?></title>
<link rel="stylesheet" href="./js/leaflet/leaflet.css" />
<!--[if lte IE 8]>
     <link rel="stylesheet" href="./js/leaflet/leaflet.ie.css" />
<![endif]-->
<LINK REL="StyleSheet" HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css"/>
<style type="text/css">
    html, body { margin: 0; padding: 0; font-size: 75%;}
    .hover 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: inset; border-color: #FFFFFF;
                    padding: 4px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
    .plain 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
                    padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder; cursor: pointer; }
    .plain_centered 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
                    padding: 4px 0.5em;text-decoration: none; color: black;background-color: #EFEFEF;font-weight: bolder;}
    .hover_lo 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#FF0000; border-width: 1px; border-STYLE: outset; border-color: #FFFFFF;
                    padding: 1px 0.5em;text-decoration: none; color: black;background-color: #DEE3E7;font-weight: bolder; cursor: pointer; }
    .plain_lo 	{  margin-left: 4px; font: normal 12px Arial, Helvetica, sans-serif; color:#000000;  border-width: 3px; border-STYLE: hidden; border-color: #FFFFFF;}
    .data 	{ margin-left: 4px;  font: normal 12px Arial, Helvetica, sans-serif; color:#000000;
                    padding: 4px 0.5em;text-decoration: none;float: left;color: black;background-color: yellow;font-weight: bolder;}
    .message { FONT-WEIGHT: bold; FONT-SIZE: 20px; COLOR: #0000FF; FONT-STYLE: normal; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
</style>
<script src="./js/misc_function.js" type="text/javascript"></script>
<script src="./js/leaflet/leaflet.js"></script>
<SCRIPT>
/**
 *
 * @returns {undefined}
 */
    window.onload = function () {
        if ($('map_canvas')) { initialise(); }
        };
/**
 *
 * @param {type} angle
 * @returns {Number}
 */
    function deg2rad(angle) {
        return angle * .017453292519943295;
        }
/**
 *
 * @param {type} lon
 * @param {type} zoom1
 * @returns {@exp;Math@call;floor}
 */
    function long2tile(lon,zoom1) {
        tt = Number(lon);

        return (Math.floor((tt+180)/360*Math.pow(2,zoom1)));
        }
/**
 *
 * @param {type} lat
 * @param {type} zoom2
 * @returns {@exp;Math@call;floor}
 */
    function lat2tile(lat,zoom2) {
        return (Math.floor((1-Math.log(Math.tan(lat*Math.PI/180) + 1/Math.cos(lat*Math.PI/180))/Math.PI)/2 *Math.pow(2,zoom2)));
        }
/**
 *
 * @param {type} zoom
 * @param {type} lat
 * @param {type} lon
 * @returns {Array}
 */
    function calc_tile_name(zoom, lat, lon) {
        var xtile = long2tile(lon,zoom);
        var ytile = lat2tile(lat,zoom);
        var ret_arr = new Array(2);
        ret_arr[0] = xtile;
        ret_arr[1] = ytile;

        return ret_arr;
        }				// end function calc_tile_name ()
/**
 *
 * @returns {undefined}
 */
    function get_tiles_required() {
        $('help4').innerHTML = "<BR /><BR /><BR /><BR />";
        $('file_list_header').style.display='block';
        $('file_list').style.display='block';
        $('the_box').style.display='block';
        $('waiting').style.display='block';
        $('waiting').innerHTML = "Please Wait, Downloading Tiles<BR /><IMG style='vertical-align: middle;' src='./images/progressbar3.gif'/>";
        var zoom_top = document.map_tiles_form.zoom_top.value;
        var zoom_btm = document.map_tiles_form.zoom_bot.value;
        var top_left_lat = document.map_tiles_form.tl_lat.value;
        var top_left_lon = document.map_tiles_form.tl_lon.value;
        var btm_rt_lat = document.map_tiles_form.br_lat.value;
        var btm_rt_lon = document.map_tiles_form.br_lon.value;
        for (var zoom = zoom_top; zoom<=zoom_btm;  zoom++) {
            var temp = calc_tile_name (zoom, top_left_lat, top_left_lon);		// get tile names for each zoom level
            var col_first = temp[0];
            var row_first = temp[1];
            var temp2 = calc_tile_name (zoom, btm_rt_lat, btm_rt_lon);
            col_last = temp2[0];
            row_last = temp2[1];
            for (var col = col_first; col<col_last;  col++) {
                for (var row = row_first; row<row_last;  row++) {
                    if ((zoom==zoom_btm) && (col==col_last-1) && (row==row_last-1)) { lastfile="yes"; } else { lastfile = "no"; }
                    var url = "./ajax/gettiles.php?dir=" + zoom + "&subdir=" + col + "&file=" + row + "&lastfile=" + lastfile;
//					alert(url);
                    sendRequest (url ,tile_cb, "");
                    }
                }
            }
        }
/**
 *
 * @param {type} req
 * @returns {undefined}
 */
    function tile_cb(req) {
        var the_ret_file=JSON.decode(req.responseText);
        if (the_ret_file[0] == "Completed") {
            $('file_list').innerHTML += the_ret_file[1];
            $('file_list').innerHTML += "<BR />";
            $('file_list').scrollTop = $('file_list').scrollHeight;
            } else {
            alert("Failed");
            }
        var finish_but = "<SPAN id='b6' class = 'plain' style='display: none; z-index: 999; float: none; width: 120px;' onMouseOver='do_hover(this.id);' onMouseOut='do_plain(this.id);' onClick = 'document.forms[\"to_config_Form\"].submit();'>Back to Config</SPAN>";
        if (the_ret_file[2] == "yes") { $('waiting').style.display='block'; $('waiting').innerHTML = "<CENTER>Complete<BR /><BR />" + finish_but + "</CENTER>"; $('b6').style.display='block'; $('b6').style.zindex = 999;}
        }				// end function tile_cb()
/**
 *
 * @returns {undefined}
 */
    function get_bounds() {
        var theBounds = map.getBounds();
        document.map_tiles_form.tl_lon.value = theBounds.getNorthWest().lng;
        document.map_tiles_form.tl_lat.value = theBounds.getNorthWest().lat;
        document.map_tiles_form.br_lon.value = theBounds.getSouthEast().lng;
        document.map_tiles_form.br_lat.value = theBounds.getSouthEast().lat;
        }
/**
 *
 * @returns {undefined}
 */
    function get_zoom_max() {
        document.map_tiles_form.zoom_bot.value = map.getZoom();
        }
/**
 *
 * @returns {undefined}
 */
    function get_zoom_min() {
        document.map_tiles_form.zoom_top.value = map.getZoom();
        }
/**
 *
 * @param {type} obj
 * @param {type} the_class
 * @returns {Boolean}
 */
    function CngClass(obj, the_class) {
        $(obj).className=the_class;

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_hover(the_id) {
        CngClass(the_id, 'hover');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_hover_centered(the_id) {
        CngClass(the_id, 'hover_centered');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_lo_hover(the_id) {
        CngClass(the_id, 'lo_hover');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_plain(the_id) {				// 8/21/10
        CngClass(the_id, 'plain');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_plain_centered(the_id) {				// 8/21/10
        CngClass(the_id, 'plain_centered');

        return true;
        }
/**
 *
 * @param {type} the_id
 * @returns {Boolean}
 */
    function do_lo_plain(the_id) {
        CngClass(the_id, 'lo_plain');

        return true;
        }
/**
 *
 * @returns {Array}
 */
    function $() {															// 12/20/08
        var elements = new Array();
        for (var i = 0; i < arguments.length; i++) {
            var element = arguments[i];
            if (typeof element == 'string')
                element = document.getElementById(element);
            if (arguments.length == 1)
                return element;
            elements.push(element);
            }

        return elements;
        }
/**
 *
 * @returns {undefined}
 */
    function del_tiles() {
        $('waiting').innerHTML = "<?php print gettext('Please Wait, Deleting existing tiles');?><BR /><IMG style='vertical-align: middle;' src='./images/progressbar3.gif'/>";
        var url = "./ajax/deltiles.php?deltiles=yes";
        sendRequest (url ,del_cb, "");
            function del_cb(req) {
                $('waiting').style.display='block';
                $('waiting').innerHTML = "<BR /><BR /><?php print gettext('Deleting existing tiles - please wait.');?>";
                var the_ret_arr=JSON.decode(req.responseText);
                if (the_ret_arr == "Completed") {
                    $('waiting').innerHTML = "<BR /><BR />Complete";
                    $('deltiles').style.display='none';
                    $('keeptiles').style.display='none';
                    $('go_but').style.display='inline-block';
                }
                if (the_ret_arr == "Failed") {
                    alert("Failed");
                    }
            }				// end function del_cb()
        }				// end function del_tiles()
/**
 *
 * @returns {undefined}
 */
    function go_toit() {
        document.go_Form.submit();
        }
/**
 *
 * @param {type} url
 * @param {type} callback
 * @param {type} postData
 * @returns {unresolved}
 */
    function sendRequest(url,callback,postData) {
        var req = createXMLHTTPObject();
        if (!req) return;
        var method = (postData) ? "POST" : "GET";
        req.open(method,url,true);
        req.setRequestHeader('User-Agent','XMLHTTP/1.0');
        if (postData)
            req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        req.onreadystatechange = function () {
            if (req.readyState != 4) return;
            if (req.status != 200 && req.status != 304) {
                return;
                }
            callback(req);
            }
        if (req.readyState == 4) return;
        req.send(postData);
        }

    var XMLHttpFactories = [
        function () {return new XMLHttpRequest();	},
        function () {return new ActiveXObject("Msxml2.XMLHTTP");	},
        function () {return new ActiveXObject("Msxml3.XMLHTTP");	},
        function () {return new ActiveXObject("Microsoft.XMLHTTP");	}
        ];
/**
 *
 * @returns {Boolean}
 */
    function createXMLHTTPObject() {
        var xmlhttp = false;
        for (var i=0;i<XMLHttpFactories.length;i++) {
            try {
                xmlhttp = XMLHttpFactories[i]();
                }
            catch (e) {
                continue;
                }
            break;
            }

        return xmlhttp;
        }
/**
 *
 * @param {type} strURL
 * @returns {@exp;AJAX@pro;responseText|Boolean}
 */
    function syncAjax(strURL) {							// synchronous ajax function
        if (window.XMLHttpRequest) {
            AJAX=new XMLHttpRequest();
            }
        else {
            AJAX=new ActiveXObject("Microsoft.XMLHTTP");
            }
        if (AJAX) {
            AJAX.open("GET", strURL, false);
            AJAX.send(null);							// form name

            return AJAX.responseText;
            }
        else {
            alert ("201: failed");

            return false;
            }
        }		// end function sync Ajax()

</SCRIPT>
</HEAD>
<BODY>
<DIV id='outer' style='position: absolute; top: 0px; left: 0px; width: 100%;'>
<?php
if ((!directory_empty($local)) && (!isset($_GET['getgo']))) {

?>
    <DIV id='title' style='position: absolute; top: 0px; width: 100%; height: 30px; background: #707070; color: #FFFFFF; font-size: 24px; font-weight: bold; text-align: center;'>Get Local Map Tiles</DIV><BR /><BR />
    <DIV id='title2' style='position: relative; top: 40px; width: 100%; height: 30px; color: #000000; font-size: 18px; font-weight: bold; text-align: center;'>Tiles already exist</DIV>
    <DIV id='directory_check' style='position: relative; top: 100px; width: 100%; text-align: center;'>
        <SPAN id='deltiles' class = 'plain' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "del_tiles();">Delete Existing Tiles</SPAN>
        <SPAN id='go_but' class = 'plain' style='display: none; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "go_toit();">Continue</SPAN>
        <SPAN id='keeptiles' class = 'plain' style='display: inline-block; float: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "go_toit();">Keep Existing Tiles</SPAN>
    </DIV>
<?php
    } else {
?>
    <DIV id='banner' style='position: absolute; top: 0px; left: 10%; width: 80%; height: 30px; background: #707070; color: #FFFFFF; font-size: 24px; font-weight: bold; text-align: center;'>Get Local Map Tiles</DIV>
    <BR />
    <BR />
    <DIV ID='map_canvas' style="position: relative; left: 50px; top: 20px; width: 500px; height: 500px; border: 2px outset #CECECE; display: block;"></DIV>
    <DIV id='leftcol' style='width: 20%; position: relative; top: 20px; left: 50px; background: #CECECE;'>
        <DIV id='menubar' style='width: 100%;'>
            <SPAN id='b5' class = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "document.forms['reset_Form'].submit();">Reset</SPAN>
            <SPAN id='b1' class = 'plain' style='display: inline;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_bounds(); $('b2').style.display='block'; $('b1').style.display='none'; $('help1').style.display='none'; $('help2').style.display='block'; $('b5').style.display='block';">Get Bounds</SPAN>
            <SPAN id='b2' class = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_zoom_min(); $('b3').style.display='block'; $('b2').style.display='none'; $('help2').style.display='none'; $('help3').style.display='block';">Get Zoom Out</SPAN>
            <SPAN id='b3' class = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "get_zoom_max(); $('b4').style.display='block'; $('b3').style.display='none'; $('help3').style.display='none'; $('help4').style.display='block';">Get Zoom In</SPAN>
            <SPAN id='b4' class = 'plain' style='display: none;' onMouseOver="do_hover(this.id);" onMouseOut="do_plain(this.id);" onClick = "$('b4').style.display='none'; get_tiles_required();">Next</SPAN>
        </DIV>
        <DIV style='width: 100%;'>
            <FORM METHOD="POST" NAME= "map_tiles_form" ACTION="get_tiles.php?func=get_tiles">
                <TABLE style='width: 100%;'>
                    <TR>
                        <TD class='td_label'><?php print gettext('Top Left (lon)');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='tl_lon'/></TD>
                    </TR>
                    <TR>
                        <TD class='td_label'><?php print gettext('Top Left (lat)');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='tl_lat'/></TD>
                    </TR>
                    <TR>
                        <TD class='td_label'><?php print gettext('Bottom Right (lon)');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='br_lon'/></TD>
                    </TR>
                    <TR>
                        <TD class='td_label'><?php print gettext('Bottom Right (lat)');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='br_lat'/></TD>
                    </TR>
                    <TR>
                        <TD class='td_label'><?php print gettext('Max-Zoom Out');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='zoom_top'/></TD>
                    </TR>
                    <TR>
                        <TD class='td_label'><?php print gettext('Max-Zoom In');?></TD><TD class='td_data'><INPUT TYPE='text' SIZE='10' MAXLENGTH='10' NAME='zoom_bot'/></TD>
                    </TR>
                </TABLE>
            </FORM>
        </DIV><BR /><BR />
    </DIV>
    <DIV id='rightcol' style='width: 40%; position: absolute; top: 100px; right: 10%; background: #CECECE; height: 60%; font-size: 16px; font-weight: bold; border: 1px outset #707070; padding: 30px;'>
        <DIV id='help1' style='display: block;'><CENTER><?php print gettext('Help.');?></CENTER><BR /><BR />
        <?php print gettext('This page allows you to collect local Open Streetmap tiles so that mapping will work whether on-line or off.<BR /><BR />
        First, on the map zoom and move the map to the area you want local maps for. Once you have done this click the "Get Bounds" button.');?><BR />
        </DIV>
        <DIV id='help2' style='display: none;'><CENTER><?php print gettext('Help.');?></CENTER><BR /><BR />
        <?php print gettext('Next zoom in or out to the minimum amount of detail you will need - i.e. the most you would zoom out on the map.<BR /><BR />
        Once you have got this as you want click on the "Get Zoom Out" button');?><BR />
        </DIV>
        <DIV id='help3' style='display: none;'><CENTER><?php print gettext('Help.');?></CENTER><BR /><BR />
        <?php print gettext('Next zoom in or out to the maximum amount of detail you will need - i.e. the most you would zoom in on the map.<BR /><BR />
        Once you have got this as you want click on the "Get Zoom In" button');?><BR />
        </DIV>
        <DIV id='help4' style='display: none;'><CENTER><?php print gettext('Help.');?></CENTER><BR /><BR />
        <?php print gettext('Now click the "Next" button and the system will go away and collect the tiles appropriate for the settings you have provided.<BR />
        Please note that this could take a considerable time. Do not navigate away from this page until the system alerts you that<BR />
        the collection of tiles is complete.');?><BR />
        </DIV><BR /><BR />
        <DIV id='file_list_header' class='heading' style='position: relative; left: 40%; width: 60%; text-align: center; display: none;'>Downloaded Tiles</DIV>
        <DIV id='file_list' style='border: 1px solid #707070; position: relative; left: 40%; width: 60%; height: 200px; overflow-y: scroll; display: none; font-weight: normal; font-size: .8em;'></DIV>
        <SCRIPT>
        var map;
/**
 *
 * @returns {undefined}
 */
        function initialise() {
            map = L.map('map_canvas').setView([<?php print get_variable('def_lat');?>, <?php print get_variable('def_lng');?>], 1);
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery � <a href="http://cloudmade.com">CloudMade</a>[�]',
            maxZoom: 18
            }).addTo(map);
            }
        </SCRIPT>
    </DIV>
<?php
    }
?>
</DIV>
<DIV id='the_box' style='z-index: 99; position: absolute; left: 40%; top: 20%; width: 20%; height: 20%; display: none;'>
    <DIV id='waiting' style='text-align: center; vertical-align: middle; border: 4px outset #707070; font-weight: bold; font-size: 20px; background: yellow; color: black; z-index: 99; display: none; width: 100%; height: 100%;'>
    </DIV>
</DIV>
<FORM NAME='to_config_Form' METHOD="post" ACTION = "config.php"></FORM>
<FORM NAME='reset_Form' METHOD="post" ACTION = "get_tiles.php"></FORM>
<FORM NAME='go_Form' METHOD="post" ACTION = "get_tiles.php?getgo=yes"></FORM>

</BODY>
</HTML>
