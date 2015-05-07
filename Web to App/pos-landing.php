<?php
/* Landing page - used to handle the result from the performed transaction

   @link	pos-landing.php 
   @author	Created by Adyen - Payments Made Easy

   =============================================================================================================================
   ========================================= Prerequisites  ====================================================================
   =============================================================================================================================
   	
   pos-payment.php initiate a launch of the Adyen App to create a payment, in the callback parameter you should refer to this page. 

   We assume that you use own implementation of a database that keeps the results from the performed transaction.

   =============================================================================================================================
   ========================================= Steps   ===========================================================================
   =============================================================================================================================
   
   1) Retrieve parameters from callback url: sessid, merchantAccount, merchantReference, checksum, result
      Retrieve session parameters: merchantAccount, merchantReference

   2) Add security check to detect possible intruder between the Adyen App and your URL-launch web page (pos-payment.php): 
      	  - Compare session parameters with callback parameters (merchantAccount, merchantReference, sessid) 
	  - validate the checksum

   3) Handle the result from the transaction. You may use own mechanisms to keep information about the status of the transaction.
*/
	session_id($_GET['sessid']);
	session_start();
	
	if ($_SESSION['merchantAccount'] != $_GET['merchantAccount'] || $_SESSION['merchantReference'] != $_GET['merchantReference']){ 
		//TODO: Possible security risk
	}
	
	if (isset($_GET['checksum'])){
		$cs = $_GET['checksum'];
	}elseif(isset($_GET['cs'])){
		$cs = $_GET['cs'];
	}else{		
		$cs = 0;		
	}
	
	$status = "UNKNOWN";
		
	$authResult = $_GET['result'];
	if (empty($authResult) || $authResult == ""){
		//TODO: Handle the error
	}	
	
	if ($authResult == "CANCELLED" || $authResult == "DECLINED" || $authResult == "ERROR" || $authResult == "(null)"){
		$status = $authResult;	
		print "Cancelled";	
	}
	elseif($authResult == "APPROVED"){		
		$amount = $_GET['amountValue'];
		$currency = $_GET['amountCurrency'];
		$sessionid = $_GET['sessionId'];		
		
		if (validatechecksum($amount,$currency,$authResult,$sessionid,$cs,DEBUG) ==  false){
			//TODO: Possible security risk
		}
		$status = "APPROVED";	
		//TODO: Update the transaction status in your database			
	}

	
function validatechecksum($amount,$currency,$result,$sessionid,$cs,$debugcs=false){

	$amountdigits = str_split($amount);
	$currencychars = str_split($currency);
	$resultchars = str_split($result);
	$sessiondigits = str_split($sessionid);
	
	$checksum = 0;
	
	foreach ($amountdigits as $value){
		$checksum += Ascii2Int($value); 
	} 	

	
	foreach ($currencychars as $value){
		$checksum += Ascii2Int($value);
	}
	
	foreach ($resultchars as $value){
		$checksum += Ascii2Int($value);
	}
	
	$multiplier = 0;
	foreach ($sessiondigits as $value){
		$multiplier += Ascii2Int($value);
	}
	
	if ($multiplier == 0){
		$checksum = $checksum % 100;
	}else{
		$checksum = ($checksum * $multiplier) % 100;
	}
	
	if ( $cs != $checksum ) {
		return false;
	}
	
	return true;
}

function Ascii2Int($ascii){
	if (is_numeric($ascii)){
		$int = ord($ascii) - 48; 
	} else {
		$int = ord($ascii) - 64; 
	}
	return $int;
}

function getOS() {
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (stristr($useragent, "iphone") === false && stristr($useragent, "ipad") === false){
		$os = "android";
	}elseif (stristr($useragent, "iphone") >= 0 || stristr($useragent, "ipad") >= 0){
		$os = "ios";
	}
	return $os;
}

/* =============================================================================================================================
   ========================================= Close the landing page   ==========================================================
   =============================================================================================================================
   The landing page is closed after several seconds, so the focus goes back to the initiating page.
*/
?>

<script language="javaScript">
	function closeWindow() { 
		window.open('', '_self', ''); 
		window.close(); 
	}
	setTimeout(closeWindow, 500);
</script>


