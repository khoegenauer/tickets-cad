1.	Tickets SP is an adjunct to standard Tickets CAD, a no-muss no-fuss implementation intended for use by 
	mobile responders utilizing small-screen devices such as smart-phones (hence SP) while on dispatch.  
	Required: an installed Tickets CAD application.  (Download from www.ticketscad.org)

2.	While it works on conventional desktop machines, users will see some waste of screen real-estate.  It's 
	been tested and shows satisfactory operation on the android-powered Nexus 4, iPhone and iPad, with Chrome 
	and Safari browsers.  Given the variety of mobile devices out there, Your Mileage Will Vary!  Let us know.

3.	What's it do?  Well, this  version (9/6/2013) has capabilities targeted at mobile incident response, which 
	is well short of the gamut of a full-fledged Tickets operation.
	a.	SP uses yr existing live Tickets database.
	b.  The map is Google's;  While we also operate with OSM, the combined downloads overwhelmed smaller devices 
	during early testing.  We'll get it working for a next release.
	c.  SP  provides useful information on existing calls, incidents, responders, facilities, and - new! - road 
	information if yr database has any.
	d.  On any detail page where an arrow icon shows, clicking on that row takes you to some useful page, depending
	on the page context.
	e.	The smallish control in the map's upper right provides hide/show by layer in order to reduce map clutter.
	e.	SP does attempt do locate you while mobile.  The ability to do so is controlled by  browser settings.

4.	Installing SP: Simply unzip it into its own directory underneath your Tickets root.  (There's no 
	installation process.)  You shd see the SP directory along with the 20-odd of Tickets proper.

5.	Accessing SP:  If your Tickets root is at, say, 'my_server/my_cad' then its URL is 'my_server/my_cad/sp' . 
	There, log-in using yr familiar Tickets login credentials.

6.	Navigate via the nav bar at each page bottom.  With lists displayed, click on the line/item of interest 
	to bring up its details.  There, wherever you see an arrow icon, clicking on that row will take you to a 
	page that provides some display or update functionality, depending on the context.

7.	Futures:  TBD, and influenced by your responses.  (To an extent, this is an exercise to see what the minimal 
	amount of code can accomplish.)  But look for another mobile-oriented version by Andy Harvey, with a 
	somewhat different concept to its design and implementation.  It will include a tighter integration with 
	Tickets proper.  
	
Stay toon'd!
AS
September, 2013
