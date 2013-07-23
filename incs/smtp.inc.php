<?php
function real_smtp ($my_to, $my_subject, $my_message, $my_params, $my_from) { // 7/5/10 - per Kurt Jack
	require_once 'lib/swift_required.php';
	
	// $params = "outgoing.verizon.net/587/none/ashore4/********/ashore4@verizon.net";
	//                       0         1    2      3      4           5
	
	$conn_ary = explode ("/",  $my_params);   
	// dump($conn_ary) ;
	$transport = Swift_SmtpTransport::newInstance($conn_ary[0] , $conn_ary[1] , $conn_ary[2])
	  ->setUsername($conn_ary[3])
	  ->setPassword($conn_ary[4])
	  ;
	
	$mailer = Swift_Mailer::newInstance($transport); // instantiate using  created Transport
	$temp_ar = explode("@", $my_to); // extract name portion - 7/8/09
	$the_from = (isset($conn_ary[5]))? $conn_ary[5]: $my_from;
	$the_from_ar = explode("@", $my_from);	// to extract user portion
											// Create a message
	$message = Swift_Message::newInstance($my_subject)
	  ->setFrom(array($the_from => $the_from_ar[0]))
	  ->setTo(array($my_to , $my_to => trim($temp_ar[0])))
	  ->setBody($my_message)
	  ;
	//    ->setTo(array('receiver@domain.org', 'other@domain.org' => 'Names'))
	$result = $mailer->send($message); //Send the message
	
	} // end function real_smtp

?>