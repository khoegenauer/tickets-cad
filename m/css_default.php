<?php
/* 
4/28/2013 - initial release
*/
$def_font_size = "1.0";			// start-up value
@session_start();	
header("Content-type: text/css"); 							/* set arbitrary display defaults	*/
$scr_width =	( ( array_key_exists('SP', $_SESSION ) ) && 
					array_key_exists('scr_width', $_SESSION['SP'] ) ) ? $_SESSION['SP']['scr_width']	: 1200;
$row_light =	(array_key_exists('SP', $_SESSION)) ? $_SESSION['SP']['css']['row_light']				: "#EFEFEF";
$row_dark =		(array_key_exists('SP', $_SESSION)) ? $_SESSION['SP']['css']['row_dark']				: "#DEE3E7";
$background =	(array_key_exists('SP', $_SESSION)) ? $_SESSION['SP']['css']['page_background']			: $row_light;
$text_color =	(array_key_exists('SP', $_SESSION)) ? $_SESSION['SP']['css']['normal_text']				: "#000000";

$ini_arr = parse_ini_file ("incs/sp.ini");

$font_size = 	(array_key_exists('SP', $_SESSION)) ? $_SESSION['SP']['font_size']						:  $def_font_size;	// EM units string
?>
body { background-color: <?php echo $background;?>; margin:0; font-weight: normal; font-size: <?php echo $ini_arr['def_fontsize'];?>em; color: <?php echo $text_color;?>; font-family: Verdana, Arial, Helvetica, sans-serif; text-decoration: none; }
table				{ border-collapse:collapse; border-spacing:0; margin-top:0px;  
						max-width:<?php echo round (.6 * $scr_width) ;?>px;
						min-width:<?php echo round (.3 * $scr_width );?>px;
						font-size: <?php echo $ini_arr['def_fontsize'];?>em;
						
						border-radius:.125em;
						border:2px solid #1978cf;
						box-shadow: 0 0 8px #999;
						overflow:auto;	
						margin-top: 0px ; 
						margin-left: 0 auto; 
						margin-right: 0 auto; 
											}
									
table.tablesorter	{ border-collapse:collapse; border-spacing:0; margin-top:0px;  
						width:auto;
						font-size: <?php echo $ini_arr['def_fontsize'];?>em;						
						border-radius:.125em;
						border:2px solid #1978cf;
						box-shadow: 0 0 8px #999;
						overflow:auto;	
						margin-top: 0px ; 
						margin-left: 0 auto; 
						margin-right: 0 auto; 						
											}
tr 					{ vertical-align: top; }
tr.even 			{ background: <?php echo $row_light;?>; }
tr.odd 				{ background: <?php echo $row_dark ;?>; }


th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;
	}

th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
	}

th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
	}

td 					{ font-size: <?php echo $font_size;?>em; padding:4px;}
td.my_hover:hover	{ text-decoration:underline; font-style:italic; }
tr:nth-child(even) 	{ background: <?php echo $row_light;?>; }
tr:nth-child(odd) 	{ background: <?php echo $row_dark;?>;}

.logo {  display: inline; font-family: "Times New Roman", Times, serif; font-size: 2.0em; font-weight:900; color:#0000ff; }

.center				{ position:static; margin:auto;}


.foot_butt 		{ display: inline; font-family: Arial, Verdana, sans-serif; font-size: 1.25em; margin:2px 8px 2px 8px; font-weight:bold; } 		/* top right bottom left  */
.head_butt 		{ display: inline; font-family: Arial, Verdana, sans-serif; font-size: 1.0em; margin:2px 8px 2px 8px; font-weight:bold; }		/* top right bottom left  */
.head_butt_font	{ display: inline; font-family: Arial, Verdana, sans-serif; font-size: 2.0em; margin:2px 8px 2px 8px; font-weight:bolder; }		/* top right bottom left  */
.butt-sep 		{ display: inline; font-size: 1.0em; opacity: 0.2; color: <?php echo $_SESSION['SP']['css']['normal_text'];?>; }

.severity_high { FONT-WEIGHT: bold;     COLOR: #C00000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.severity_medium { FONT-WEIGHT: bold;   COLOR: #008000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none }
.severity_normal { FONT-WEIGHT: bold;   COLOR: #0000FF; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; }

.bright		{ text-align:center; 	font-weight:bold; 	background-color:gray;		color:white;	font-weight:bold;}
.brightleft	{ text-align:left; 		font-weight:bold; 	background-color:gray;		color:white;	font-weight:bold;}
.plain		{ text-align:left; 		font-weight:normal;	background-color:inherit;	color:inherit;	font-weight:inherit;}
.click		{ text-align:center; 	font-weight:bold;	background-color:inherit;	color:inherit;	font-weight:inherit;}

td.nav 	{ width:64px; text-align: center; font-size: 1.0em; opacity: 0.25; }
.tiny 	{ font-size: 0.25em; opacity: 0.25; }
.link 	{ text-decoration:underline; }
#context	{   min-width:100px; font-family: Arial, Verdana, sans-serif; font-size: 1.0em; font-weight:bold; text-align:left;} 		
#infowin	{   min-width:100px; max-width:280px; font-family: Arial, Verdana, sans-serif; font-size: 1.0em; font-weight:bold; text-align:left;} 		
#header		{ font-family: Arial, Verdana, sans-serif; font-size: 1.5em; font-weight:bold; height:auto; text-align: center; width:100%; background-color: <?php echo "{$_SESSION['SP']['css']['page_background']};"?>;}
#footer		{ font-family: Arial, Verdana, sans-serif; font-size: 1.5em; font-weight:bold; height:auto; text-align: center; width:100%; background-color: <?php echo "{$_SESSION['SP']['css']['page_background']};"?>;}
/* Für ie6*/
* html #footer{ position:absolute; top:expression((0-(footer.offsetHeight)+(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)+(ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop))+'px');	}
	
.leaflet-control-zoom-fullscreen { background-image: url(icon-fullscreen.png); }
.leaflet-container:-webkit-full-screen { width: 100% !important; height: 100% !important; }
.leaflet-control-zoom-fullscreen { background-image: url(./js/icon-fullscreen.png); }

#map { 
	border-radius:.125em;
	border:2px solid #1978cf;
	box-shadow: 0 0 8px #999;
	width:auto;
	height:auto;	
	overflow:auto;	
	margin-top: 0px ; 
	margin-left: 0 auto; 
	margin-right: 0 auto; 
	}

