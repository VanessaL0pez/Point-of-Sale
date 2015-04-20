<?php
/**
   Create Payment with URL-initiated launch for the Adyen App & Terminal
   
   The URL-initiated launch for the Adyen App & Terminal provide a flexible, secure and easy way to allow merchants to set up  
   Point of Sale payments. This code example will show simple request containing the required parameters to configure our Adyen 
   App & Terminal.
   
   @link	pos-payment.php 
   @author	Created by Adyen - Payments Made Easy
 
   =============================================================================================================================
   ========================================= Prerequisites  ====================================================================
   =============================================================================================================================
   1) The Adyen App needs to be installed on your iOS/Android device. 
	- iOS:		Apple app store: https://itunes.apple.com/app/adyen/id585207510
	- Android: 	Playstore: search for Adyen.
   2) Board the Adyen App: Enter merchant account, user name and password on the start of the Adyen App
   3) Board the Payment device: In the Adyen App > Payment device > Add new device
   For details on this steps please consult the POS manual.

   =============================================================================================================================
   ========================================= Parameters  =======================================================================
   =============================================================================================================================
   The URL-initiated launch requires certain variables to be posted in order to create a payment possibility through the 
   Adyen App & Terminal. The variables that you have to post to the URL-initiated launch are the following:
 
  
   $sessionID 			: Used to correlate the callback URL with the URL that initiated the payment.	
   $merchantAccount		: The merchant account you want to process this payment with.	
   $merchantReference		: The merchant reference is your reference for the payment. Unique identifier of the payment in 
				  your database. Also, to be printed on the receipt.
   $paymentAmount		: Amount specified in minor units EUR 1,00 = 100
   $currencyCode		: The three-letter capitalised ISO currency code to pay in i.e. EUR   
   $callbackurl			: Specifies a URL to be called by the Adyen App when the payment is complete.
*/

   session_start();

   $merchantAccount   = "PME_POS";
   $merchantReference = "TEST-PAYMENT-" . date("Y-m-d-H:i:s");
   $paymentAmount     = "2"; 	
   $currencyCode      = "USD"; 	
   $callbackurl       = "https://merchantdomain.com/payments/pos-landing.php";

   $os = getOS();
	 
   $terminalLauncher  = "adyen://payment/";
   if ($os == "android" && getBrowser() == "firefox") $terminalLauncher = "http://www.adyen.com/android-app/";

   $sessionid = date("U")."ABCD";		
   $terminalLauncher .= "?sessionId=".$sessionid;	
   $terminalLauncher .= "&merchantReference=".urlencode($merchantReference);
   $terminalLauncher .= "&amount=".$paymentAmount;
   $terminalLauncher .= "&currency=".$currencyCode;
   $receiptOrderLines = base64_encode("BH|Adyen Omni Shop||CB\n");
   $terminalLauncher .= "&receiptOrderLines=".urlencode($receiptOrderLines);	

   if ($os == "android")$terminalLauncher .= "&fullScreen="."true";

   $sessid = session_id();		
   $terminalLauncher .= "&callback=".urlencode($callbackurl."/?sessid=".$sessid."&merchantAccount=".$merchantAccount);

   $_SESSION["merchantAccount"]   = $merchantAccount;
   $_SESSION["merchantReference"] = $merchantReference;
	

function getOS($requestSource="") {
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (stristr($useragent, "iphone") === false && stristr($useragent, "ipad") === false){
		$os = "android";
	}elseif (stristr($useragent, "iphone") >= 0 || stristr($useragent, "ipad") >= 0){
		$os = "ios";
	}
	return $os;
}

function getBrowser(){
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (stripos($useragent, "FireFox") !== false){
		$browser = "firefox";
	}elseif (stripos($useragent, "Chrome") !== false){
		$browser = "chrome";
	}else{
		$browser = "undetermined";
	}
	return $browser;
}

/* =============================================================================================================================
   ========================================= Result inquiry  ===================================================================
   =============================================================================================================================
   The landing page recieves the result from the performed transaction and saves it in your database. The landing page is closed 
   and the focus is back in the initating page, where the result can be presented. 
   One way to handle this is to implement a form (resultInquiry) which will be posted when this page (pos-payment.php) is active 
   again (after the Adyen App calls back the landing page, and the landing page is closed). You have to present the result that 
   you have saved in your database.
*/

if (isset($_REQUEST['merchantAccount']) && isset($_REQUEST['merchantReference'])) {
	//TODO: Check transaction result in your database and present it here
	print "The transaction is finished with result: Get the result from your database <br>"; 	
}

?>

<script language="JavaScript"> 

	var eventName = "visibilitychange";
  	
	// Listener for event that will fire call to checkResult()
	document.addEventListener(eventName, visibilityChanged, false);

	function visibilityChanged() {
		  	if (document.webkitHidden || document.mozHidden || document.msHidden || document.hidden) {
			  	//The initiating page was hidden as a result of the Adyen App taking control
		  	}else{
			  	//The initiating page is in control again after Adyen App finished and the landing page was closed
				if (document.getElementById("resultInquiry")){
					document.getElementById("resultInquiry").submit();
				}
		  	}
	}
</script>


<a id='launchlink' href="<?php echo $terminalLauncher; ?>">Pay!</a>

<?php
print <<<EOD
	<form id="resultInquiry" action="" method="post">
		<input type="hidden" id="merchantAccount" name="merchantAccount" value="{$_SESSION["merchantAccount"]}">
		<input type="hidden" id="merchantReference" name="merchantReference" value="{$_SESSION["merchantReference"]}">		
	</form>
EOD;
?>


