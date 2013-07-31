<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE><?php print gettext('Sounds');?></TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript">
<STYLE>
BODY { BACKGROUND-COLOR: #EFEFEF; MARGIN:12PX; FONT-WEIGHT: normal; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; line-height:125% }
h2	{FONT-SIZE: 16px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; }
h3	{MARGIN:12PX; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none; }
div {MARGIN:12PX; TEXT-DECORATION: underline; }
</STYLE>
<SCRIPT>
/**
 * 
 * @param {type} n
 * @returns {undefined}
 */  
	function playit(n) {	// 6/12/10
		try 		{document.getElementsByTagName('audio')[n].play();}
		catch (e) 	{alert('Not supported');}		// ignore
		}				// end function playit()

</SCRIPT>
</HEAD>
<BODY>
<audio src="./sounds/aooga.wav" preload></audio>
<audio src="./sounds/applause.wav" preload></audio>
<audio src="./sounds/divalarm.wav" preload></audio>
<audio src="./sounds/door-squeak-1.wav" preload></audio>
<audio src="./sounds/flushtoilet.wav" preload></audio>
<audio src="./sounds/sonar.wav" preload></audio>
<audio src="./sounds/taps.wav" preload></audio>


<audio src="./sounds/phonesring.mp3" preload></audio>
<audio src="./sounds/4bells.mp3" preload></audio>
<audio src="./sounds/bass_1.mp3" preload></audio>
<audio src="./sounds/firetrucksiren.mp3" preload></audio>
<audio src="./sounds/missle.mp3" preload></audio>
<audio src="./sounds/shockperson.mp3" preload></audio>
<audio src="./sounds/weatherwarning.mp3" preload></audio>
<audio src="./sounds/whistlewolf.mp3" preload></audio>

<h2><?php print gettext('Available Sound Effects For Notification');?> - <u><?php print gettext('click below to try');?></u></h2>
<h3><?php print gettext('wav format sound files');?> (Firefox 3.5+ and Safari 5.0+ )</h3>
<DIV onClick = 'playit(0);'><?php print gettext('Aooga');?> (aooga.wav)</DIV>
<DIV onClick = 'playit(1);'><?php print gettext('Applause');?> (applause.wav)</DIV>
<DIV onClick = 'playit(2);'><?php print gettext('Dive alarm');?> (divalarm.wav)</DIV>
<DIV onClick = 'playit(3);'><?php print gettext('Door squeak');?> (door-squeak-1.wav)</DIV>
<DIV onClick = 'playit(4);'><?php print gettext('Flush toilet');?> (flushtoilet.wav)</DIV>
<DIV onClick = 'playit(5);'><?php print gettext('Sonar');?> (sonar.wav)</DIV>
<DIV onClick = 'playit(6);'><?php print gettext('Taps');?> (taps.wav)</DIV>
<br><br><h3><?php print gettext('mp3 format sound files');?> (Chrome 5.0+ and Safari 5.0+ )</h3>

<DIV onClick = 'playit(7);'><?php print gettext('Phone ring');?> (phonesring.mp3)</DIV>
<!-- <DIV onClick = 'playit(8);'><?php print gettext('4bells.wav');?> (4bells.wav)</DIV>
<DIV onClick = 'playit(9);'>bass_1.mid ()</DIV> -->
<DIV onClick = 'playit(10);'><?php print gettext('Fire truck siren');?> (firetrucksiren.mp3)</DIV>
<DIV onClick = 'playit(11);'><?php print gettext('Missile');?> (missle.mp3)</DIV>
<DIV onClick = 'playit(12);'><?php print gettext('Electric shock');?> (shockperson.mp3)</DIV>
<DIV onClick = 'playit(13);'><?php print gettext('Weather warning');?> (weatherwarning.mp3)</DIV>
<DIV onClick = 'playit(14);'><?php print gettext('Wolf whistle');?> (whistlewolf.mp3)</DIV>
<BR />
<SPAN onClick = 'window.close()'><u><?php print gettext('Finished');?></u></SPAN>
</BODY>
</HTML>
