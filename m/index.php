<?php
/*		interesting snippets
<div id="twitter">
	<a class="twitter-timeline" href="https://twitter.com/search?q=%23roadskill" data-widget-id="362253821021401088">Tweets about "#roadskill"</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</div>

xhr = $.ajax({							// creating our request
	url: 'ajax/progress.ftl',
	success: function(data) {    }	//do something    	
	});

xhr.abort();    // aborting the request
	
*/
?>
<!DOCTYPE html>
<html>
<frameset rows="0, *">
	<frame src="location.php?rand=<?php echo time();?>" 	name="top" 	frameborder="0" border="0" framespacing="0" noresize="noresize" style="margin-top:0px;">
	<frame src="login.php?rand=<?php echo time();?>" 		name="main" frameborder="0" border="0" framespacing="0" noresize="noresize" style="margin-top:0px;">
    <noframes>
	<b>A browser with frames capability is required for this application</b>
	</noframes>
</frameset>
</html>