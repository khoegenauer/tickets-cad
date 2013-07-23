/*
do_sel_update - functions_major.inc.php
function to_server - functions_major.inc.php
function syncAjax - - functions_major.inc.php
7/26/10 CngClass(), do_time() added
9/27/10 added Canada reverse geocoding.
*/
	function do_sel_update (in_unit, in_val) {							// 12/17/09
		to_server(in_unit, in_val);
		}


	function to_server(the_unit, the_status) {							// write unit status data via ajax xfer
		var querystr = "frm_responder_id=" + the_unit;
		querystr += "&frm_status_id=" + the_status;
	
		var url = "as_up_un_status.php?" + querystr;			// 
		var payload = syncAjax(url);						// 
		if (payload.substring(0,1)=="-") {	
			alert ("<php print __LINE__;?>: msg failed ");
			return false;
			}
		else {
			parent.frames['upper'].show_msg ('Unit status update applied!')
			return true;
			}				// end if/else (payload.substring(... )
		}		// end function to_server()
	
	function syncAjax(strURL) {							// synchronous ajax function
		if (window.XMLHttpRequest) {						 
			AJAX=new XMLHttpRequest();						 
			} 
		else {																 
			AJAX=new ActiveXObject("Microsoft.XMLHTTP");
			}
		if (AJAX) {
//			alert("257 " + strURL);
			AJAX.open("GET", strURL, false);														 
			AJAX.send(null);							// e
			return AJAX.responseText;																				 
			} 
		else {
			alert ("<?php print __LINE__; ?>: failed");
			return false;
			}																						 
		}		// end function sync Ajax(strURL)

/*
Slippers Pl,  [0]
Camberwell,  [0]
Greater London SE16 2  [1]
UK [2]

Arniston,  [0]
Midlothian EH23 4,  [1]
UK  [2]

39-55 Cheviot Rd, 
South Tyneside NE32 5, 
UK	 [2]

*/

	function pars_goog_addr(addr_str) {
		var addr = "";
		var city = "";
		var st = "";

		var addr_ar = addr_str.split(",", 5);
		switch (addr_ar[(addr_ar.length-1)].trim()) {
		
			case "USA":	// frm_street frm_city frm_state - string.substring(from, to)
			case "Canada":					// 9/27/10
				switch (addr_ar.length) {
					case 3:					
						addr = "";
						city = addr_ar[0].trim();
						st = addr_ar[1].trim().substring(0, 2);					
						break;
				
					case 4:
						addr = addr_ar[0].trim();
						city = addr_ar[1].trim();
						st = addr_ar[2].trim().substring(0, 2);					
						break;
					default:
						alert ("<?php print __LINE__; ?> err: " + addr_ar.length);
					}			
				break;
		
			case "UK":
				switch (addr_ar.length) {
					case 3:
						addr = addr_ar[0].trim();
						city = addr_ar[1].trim();
						st = addr_ar[2].trim();					
						break;
				
					case 4:
						addr = addr_ar[0].trim() + ", " + addr_ar[1].trim() ;
						city = addr_ar[2].trim();
						st = addr_ar[3].trim();					
						break;
					default:
						alert ("<?php print __LINE__; ?> err: " + addr_ar.length);
					}			
				break;		
		
			default:
				alert ( "<?php print __LINE__; ?> error");
			}		// end switch
		
		var return_ar = new Array(addr, city, st);
		return return_ar;
		}		// end function pars_goog_addr(addr_str) 

	function CngClass(obj, the_class){		// 7/26/10
		$(obj).className=the_class;
		return true;
		}

	function do_time() {							//7/26/10
		var today=new Date();
		today.setDate(today.getSeconds()+7.5);		// half-adjust
		var hours = today.getHours();
		var h=(hours < 10)?  "0" + hours : hours ;
		var mins = today.getMinutes();
		var m=(mins < 10)?  "0" + mins : mins ;
		return h+":"+m;
		}

