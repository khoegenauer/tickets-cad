/*
4/12/2013 - initial release
4/22/2013 - sendRequest() added
4/24/2013 - function CngClass( ) added
7/1/2013 - function getLocation( ...  echo $_SESSION['user_unit_id'] ...) added
7/4/2013 - passed unit id value to getLocation()
*/
function $() {									// 12/20/08
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

String.prototype.trim = function () {
	return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};	

function do_unload () {
	try {
		clearInterval(timer)
	  	}
	catch(err) {}
	}				// end function

(function(){
    var DomReady = window.DomReady = {};
	// Everything that has to do with properly supporting our document ready event. Brought over from the most awesome jQuery. 
    var userAgent = navigator.userAgent.toLowerCase();
    // Figure out what browser is being used
    var browser = {
    	version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
    	safari: /webkit/.test(userAgent),
    	opera: /opera/.test(userAgent),
    	msie: (/msie/.test(userAgent)) && (!/opera/.test( userAgent )),
    	mozilla: (/mozilla/.test(userAgent)) && (!/(compatible|webkit)/.test(userAgent))
    };    
	var readyBound = false;	
	var isReady = false;
	var readyList = [];
	// Handle when the DOM is ready
	function domReady() {
		// Make sure that the DOM is not already loaded
		if(!isReady) {
			// Remember that the DOM is ready
			isReady = true;
        
	        if(readyList) {
	            for(var fn = 0; fn < readyList.length; fn++) {
	                readyList[fn].call(window, []);
	            }
            
	            readyList = [];
	        }
		}
	};
	// From Simon Willison. A safe way to fire onload w/o screwing up everyone else.
	function addLoadEvent(func) {
	  var oldonload = window.onload;
	  if (typeof window.onload != 'function') {
	    window.onload = func;
	  } else {
	    window.onload = function() {
	      if (oldonload) {
	        oldonload();
	      }
	      func();
	    }
	  }
	};
	// does the heavy work of working through the browsers idiosyncracies (let's call them that) to hook onload.
	function bindReady() {
		if(readyBound) {
		    return;
	    }
	
		readyBound = true;
		// Mozilla, Opera (see further below for it) and webkit nightlies currently support this event
		if (document.addEventListener && !browser.opera) {
			// Use the handy event callback
			document.addEventListener("DOMContentLoaded", domReady, false);
		}
		// If IE is used and is not in a frame
		// Continually check to see if the document is ready
		if (browser.msie && window == top) (function(){
			if (isReady) return;
			try {
				// If IE is used, use the trick by Diego Perini
				// http://javascript.nwbox.com/IEContentLoaded/
				document.documentElement.doScroll("left");
			} catch(error) {
				setTimeout(arguments.callee, 0);
				return;
			}
			// and execute any waiting functions
		    domReady();
		})();
		if(browser.opera) {
			document.addEventListener( "DOMContentLoaded", function () {
				if (isReady) return;
				for (var i = 0; i < document.styleSheets.length; i++)
					if (document.styleSheets[i].disabled) {
						setTimeout( arguments.callee, 0 );
						return;
					}
				// and execute any waiting functions
	            domReady();
			}, false);
		}
		if(browser.safari) {
		    var numStyles;
			(function(){
				if (isReady) return;
				if (document.readyState != "loaded" && document.readyState != "complete") {
					setTimeout( arguments.callee, 0 );
					return;
				}
				if (numStyles === undefined) {
	                var links = document.getElementsByTagName("link");
	                for (var i=0; i < links.length; i++) {
	                	if(links[i].getAttribute('rel') == 'stylesheet') {
	                	    numStyles++;
	                	}
	                }
	                var styles = document.getElementsByTagName("style");
	                numStyles += styles.length;
				}
				if (document.styleSheets.length != numStyles) {
					setTimeout( arguments.callee, 0 );
					return;
				}
			
				// and execute any waiting functions
				domReady();
			})();
		}
		// A fallback to window.onload, that will always work
	    addLoadEvent(domReady);
	};
	// This is the public function that people can use to hook up ready.
	DomReady.ready = function(fn, args) {
		// Attach the listeners
		bindReady();
    
		// If the DOM is already ready
		if (isReady) {
			// Execute the function immediately
			fn.call(window, []);
	    } else {
			// Add the function to the wait list
	        readyList.push( function() { return fn.call(window, []); } );
	    }
	};
    
	bindReady();
	
})();

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
		}	// end function sendRequest()
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try 		{ xmlhttp = XMLHttpFactories[i]();	}
			catch (e) 	{ continue; }
			break;
			}
		return xmlhttp;
		}			// end function createXMLHTTPObject()		

	function CngClass(obj, the_class){
		$(obj).className=the_class;
		return true;
		}

	function navFwd () {				// say 0-9, length 10
		var idArray=document.navForm.id_str.value.split(","); 
		if (document.navForm.id.value <  idArray.length-1) {
			document.navForm.id.value++;				// step to next
//			alert(document.navForm.id.value);
			document.navForm.submit();
			}	
		}
	function navBack () {
		if (document.navForm.id.value >0 ) {
			document.navForm.id.value--;				// step to prior
			document.navForm.submit();
			}	
		}
	function actnavFwd () {				// Actions/Patients navigation
		var idArray=document.navForm.act_id_str.value.split(","); 
		if (document.navForm.act_id.value <  idArray.length-1) {
			document.navForm.act_id.value++;				// step to next
			document.navForm.submit();
			}	
		}
	function actnavBack () {
		if (document.navForm.act_id.value > 0 ) {
			document.navForm.act_id.value--;				// back up to prior
			document.navForm.submit();
			}	
		}

	var unit_id;									// 7/4/2013
	
	function getLocation(unit_id_val) {				// 7/1/2013
		if (navigator.geolocation) {
			unit_id = unit_id_val;
			var array_pos = navigator.geolocation.getCurrentPosition(reportCurrentPosition);	// note callback
			return array_pos;
			}
		else { return false; } 
		}				// end function get Location() 
	
	function reportCurrentPosition(position) {
		function r_c_p_callback(req) {			// no callback role here
			}		// end function()
			
		var latitude = 	isNaN(position.coords.latitude)? 	null : 	position.coords.latitude ;
		var longitude = isNaN(position.coords.longitude)? 	null : 	position.coords.longitude ;
		var speed = 	isNaN(position.coords.speed)? 		null : 	position.coords.speed ;
		var heading = 	isNaN(position.coords.heading)? 	null : 	position.coords.heading ;
		var altitude = 	isNaN(position.coords.altitude)? 	null : 	position.coords.altitude ;
		
		var params = "latitude=" + latitude + 
			"&longitude=" + longitude + 
			"&speed=" + speed + 
			"&heading=" + heading + 
			"&altitude=" + altitude + 
			"&unit_id=" + unit_id;		// 

		var url = "./ajax/set_position.php";
		sendRequest( url, r_c_p_callback, params);		//  update position and track data
		}				// end function report Current Position

		