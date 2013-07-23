<?php
/*
3/25/10 initial release
*/
		$types = array();
	
			$types[$GLOBALS['LOG_SIGN_IN']]							="Sign in";
			$types[$GLOBALS['LOG_SIGN_OUT']]						="Sign out";
			$types[$GLOBALS['LOG_COMMENT']]							="Comment";
			$types[$GLOBALS['LOG_INCIDENT_OPEN']]					="Incident opened";
			$types[$GLOBALS['LOG_INCIDENT_CLOSE']]					="Incident closed";
			$types[$GLOBALS['LOG_INCIDENT_CHANGE']]					="Incident updated";
			$types[$GLOBALS['LOG_ACTION_ADD']]						="Action added";
			$types[$GLOBALS['LOG_PATIENT_ADD']]						="Patient data added";
			$types[$GLOBALS['LOG_INCIDENT_DELETE']]					="Incident deleted";
			$types[$GLOBALS['LOG_ACTION_DELETE']]					="Action deleted";
			$types[$GLOBALS['LOG_PATIENT_DELETE']]					="Patient data deleted";
			$types[$GLOBALS['LOG_UNIT_STATUS']]						="Unit status change";
			$types[$GLOBALS['LOG_UNIT_COMPLETE']]					="Unit completed";
			$types[$GLOBALS['LOG_UNIT_CHANGE']]						="Unit updated";
			
			$types[$GLOBALS['LOG_CALL_DISP']]						="Unit dispatched";
			$types[$GLOBALS['LOG_CALL_RESP']]						="Unit responding";
			$types[$GLOBALS['LOG_CALL_ONSCN']]						="Unit on scene";
			$types[$GLOBALS['LOG_CALL_CLR']]						="Unit clear";
			$types[$GLOBALS['LOG_CALL_RESET']]						="Call reset";
			
			$types[$GLOBALS['LOG_CALL_REC_FAC_SET']]				="Call rcv fac'y set";
			$types[$GLOBALS['LOG_CALL_REC_FAC_CHANGE']]				="Call rcv fac'y changed";
			$types[$GLOBALS['LOG_CALL_REC_FAC_UNSET']]				="Call rcv fac'y unset";
			$types[$GLOBALS['LOG_CALL_REC_FAC_CLEAR']]				="Call rcv fac'y cleared";
			
			$types[$GLOBALS['LOG_FACILITY_ADD']]					="Facility added";
			$types[$GLOBALS['LOG_FACILITY_CHANGE']]					="Facility changed";
			
			$types[$GLOBALS['LOG_FACILITY_INCIDENT_OPEN']]			="Facility incident opened";
			$types[$GLOBALS['LOG_FACILITY_INCIDENT_CLOSE']]			="Facility incident closed";
			$types[$GLOBALS['LOG_FACILITY_INCIDENT_CHANGE']]		="Facility incident changed";
			
			$types[$GLOBALS['LOG_CALL_U2FENR']]						="Call unit to fac'y enroute";
			$types[$GLOBALS['LOG_CALL_U2FARR']]						="Call unit to fac'y arrived";
			
			$types[$GLOBALS['LOG_FACILITY_DISP']]					="Facility dispatched";
			$types[$GLOBALS['LOG_FACILITY_RESP']]					="Facility responding";
			$types[$GLOBALS['LOG_FACILITY_ONSCN']]					="Facility on scene";
			$types[$GLOBALS['LOG_FACILITY_CLR']]					="Facility cleared";
			$types[$GLOBALS['LOG_FACILITY_RESET']]					="Facility reset";
?>