<?php
/* 
4/28/2013 - initial release
*/
@session_start();	
header("Content-type: text/css"); 
?>
body { background-color: <?php echo $_SESSION['css']['page_background'];?>; margin:0; font-weight: normal; font-size: 12px; color: <?php echo $_SESSION['css']['normal_text'];?>; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
table				{ border-collapse:collapse; border-spacing:4px; margin-top:0px; }
tr 					{ vertical-align: top; }
tr.even 			{ background: <?php echo $_SESSION['css']['row_light'];?>; }
tr.odd 				{ background: <?php echo $_SESSION['css']['row_dark'];?>; }

td 					{ font-size: 1.0em; padding:4px;}

tr:nth-child(even) 	{ background: <?php echo $_SESSION['css']['row_dark'];?>; }
tr:nth-child(odd) 	{ background: <?php echo $_SESSION['css']['row_light'];?>;}

.logo {  display: inline; font-family: "Times New Roman", Times, serif; font-size: 4.0em; font-weight:900; color:#0000ff; }

.center				{ position:static; margin:auto;}

.foot_butt 	{ display: inline; font-family: Arial, Verdana, sans-serif; font-size: 1.25em; margin:2px 8px 2px 8px; font-weight:bold; } 		/* top right bottom left  */
.head_butt 	{ display: inline; font-family: Arial, Verdana, sans-serif; font-size: 1.25em; margin:2px 8px 2px 8px; font-weight:bold; }		/* top right bottom left  */
.butt-sep 	{ display: inline; font-size: 1.0em; opacity: 0.2; color: <?php echo $_SESSION['css']['normal_text'];?>; }

.severity_high { FONT-WEIGHT: bold;     COLOR: #C00000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.severity_medium { FONT-WEIGHT: bold;   COLOR: #008000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.severity_normal { FONT-WEIGHT: bold;   COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; }

.bright		{ text-align:center; 	font-weight:bold; 	background-color:gray;		color:white;	font-weight:bold;}
.brightleft	{ text-align:left; 		font-weight:bold; 	background-color:gray;		color:white;	font-weight:bold;}
.plain		{ text-align:left; 		font-weight:normal;	background-color:inherit;	color:inherit;	font-weight:inherit;}
.click		{ text-align:center; 	font-weight:bold;	background-color:inherit;	color:inherit;	font-weight:inherit;}

td.nav { width:64px; text-align: center; font-size: 1.0em; opacity: 0.25; }

#footer{ position:fixed; bottom:0px; height:auto; text-align: center; width:100%; background:#999;}
/* Für ie6*/
* html #footer{ position:absolute; top:expression((0-(footer.offsetHeight)+(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)+(ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop))+'px');	}
	
.leaflet-control-zoom-fullscreen { background-image: url(icon-fullscreen.png); }
.leaflet-container:-webkit-full-screen { width: 100% !important; height: 100% !important; }
.leaflet-control-zoom-fullscreen { background-image: url(./js/icon-fullscreen.png); }

#mapxyz { 
	border-radius:.125em;
	border:2px solid #1978cf;
	box-shadow: 0 0 8px #999;
	width:auto;
	height:auto;	
	overflow:auto;	
	margin-top: 60px ; 
	margin-left: 0 auto; 
	margin-right: 0 auto; 
}

