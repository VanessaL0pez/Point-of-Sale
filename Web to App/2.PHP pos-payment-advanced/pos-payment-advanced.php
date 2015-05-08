<?php
/**
   Create Payment with URL-initiated launch for the Adyen App & Terminal
   
   The URL-initiated launch for the Adyen App & Terminal provide a flexible, secure and easy way to allow merchants to set up  
   Point of Sale payments. This code example will show simple request containing the required parameters to configure our Adyen 
   App & Terminal.

   @author	Created by Adyen - Payments Made Easy
 
   =============================================================================================================================
   ========================================= Prerequisites  ====================================================================
   =============================================================================================================================
   1) The Adyen App needs to be installed on your Android device. In Playstore: search for Adyen.
   2) Board the Adyen App: Enter merchant account, user name and password on the start of the Adyen App
   3) Board the Payment device: In the Adyen App > Payment device > Add new device > Choose Wifi or Bluetooth
    - For wifi:
 	Enter the IP address and click Add device > the Adyen App identifies the device, registers the terminal, loads the device
	configuration, verifies the terminal configuration and shows information 'Device boarded'.
	For details on these steps please consult the POS manual.
    - For Bluetooth:
	Pair your Shuttle with your Android phone/ tablet:
	On your tablet/phone: Make sure Bluetooth is enabled (consult your smartphone/ tablet manual).
	On the Shuttle device: press the ‘0’ key for at least 5 seconds to make your Shuttle discoverable over Bluetooth.
	Select the device corresponding to the serial number of your Shuttle. A passkey screen should appear on  your Android 
	device, click ‘Pair’. The Adyen App identifies the device, registers the terminal, loads the device configuration, 
	verifies the terminal configuration and shows information 'Device boarded'.
	For details on these steps please consult the Shuttle Quick Start Guide:https://www.adyen.com/home/support/manuals#S-T

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

   Additional options:
   startImmediately		: Skip user input on App (default is 1/true)
   callbackAutomatic	 	: Return automatically to Cash Register, skip receipt handling via App (default is 1/true)
   shopperEmail		    	: The e-mailaddress of the shopper (optional)
   shopperReference		: The shopper reference, i.e. the shopper ID (optional)
   recurringContract	    	: Can be "ONECLICK","RECURRING" or "ONECLICK,RECURRING", this allows you to store the payment 
				  details as a ONECLICK and/or RECURRING contract. Please note that if you supply recurringContract, 
				  shopperEmail and shopperReference become mandatory.
   tenderOptions 		: Specifies additional processing options for the payment. Alphanumerical, comma-separated (urlencoded).
   GetAdditionalData	        : Mandatory for DCC, Loyalty, Recurring
   ReceiptHandler		: 
   AskGratuity			: Gratuity amount is requested on the Terminal
   ManualKeyedEntry		: Allows for Manual Entry of the Card Details. The terminal has to be configured for this option
*/

	session_start();

  	$merchantAccount   = "YourMerchantAccount";
	$merchantReference = "TEST-PAYMENT-" . date("Y-m-d-H:i:s");
  	$paymentAmount 	   = "10100"; 	
  	$currencyCode 	   = "EUR"; 	

	$shopperEmail 	   = "ShopperEmail";
	$shopperReference  = "ShopperReference"; 
	$recurringContract = "RECURRING";

	$startImmediately  = true;
	$callbackAutomatic = true;

	$receiptHandler    = false;
	$askGratuity 	   = false; 
	$manualKeyedEntry  = false;
  	$callbackurl 	   = "https://merchantdomain.com/payments/processpaymentresult.php"; 
  
  	$os = getOS();
	 
  	$terminalLauncher  = "adyen://payment/";
  	if ($os == "android" && getBrowser() == "firefox") $terminalLauncher = "http://www.adyen.com/android-app/";

  	$sessionid = date("U")."ABCD";		
  	$terminalLauncher .= "?sessionId=".$sessionid;	
  	$terminalLauncher .= "&merchantReference=".urlencode($merchantReference);
  	$terminalLauncher .= "&amount=".$paymentAmount;
  	$terminalLauncher .= "&currency=".$currencyCode;

	$terminalLauncher .= "&startImmediately=".$startImmediately;
	$terminalLauncher .= "&callbackAutomatic=".$callbackAutomatic;

	if ($os == "android")$terminalLauncher .= "&fullScreen="."true";

	$tenderOptions 	   		 	 = "GetAdditionalData";		
	if ($askGratuity) 	$tenderOptions	.= ",AskGratuity"; 	
	if ($receiptHandler) 	$tenderOptions	.= ",ReceiptHandler"; 
	if ($manualKeyedEntry)	$tenderOptions  .= ",ManualKeyedEntry";
	$terminalLauncher 		      	.= "&tenderOptions=".urlencode($tenderOptions);		
		
	if ($shopperEmail != "" && $shopperReference != ""){
		$terminalLauncher .= "&shopperEmail=".urlencode($shopperEmail);
		$terminalLauncher .= "&shopperReference=".urlencode($shopperReference);
		if ($recurringContract != "") 
			$terminalLauncher .= "&recurringContract=".urlencode($recurringContract);
	}
	
	$receiptOrderLines = base64_encode(getReceiptOrderLines("",$currencyCode,$paymentAmount));
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

function getReceiptOrderLines($orderData,$currencyCode,$paymentAmount){

	$myReceiptOrderLines = "";
	
	$formattedAmountValue = formatAmount($paymentAmount);
	
	
	//Before Header (BH)
        //add elements before the receipt header elements
	$myReceiptOrderLines = "BH|Adyen Omni Shop||CB\n".
			"BH|||\n";
	
	//HEADER:
        //header configured from the Adyen CA will be placed here by the Adyen App

        //After Header (AH):
        //add order details: after header, before the payment details (default)
	$myReceiptOrderLines .= "---||C\n".
			"Served by: Friendly Ghost John||CB\n".
			"Table: 149|".date("l j M Y")."|B\n".
			"---||C\n".
			"||C\n".
			"---||C\n".
			"====== YOUR ORDER DETAILS ======||CB\n".
			"---||C\n".
			" No. Description |$/Piece  Subtotal|\n".
			"  2  Coffee Grande| ".$currencyCode." 0.05  ".$currencyCode." ".$formattedAmountValue."|\n".
			"|--------|\n".
			"|Order Total:  ".$currencyCode." ".$formattedAmountValue."|B\n".
			"|Gross amount:  ".$currencyCode." ".$formattedAmountValue."|\n".
			"|Sales tax 6%:  ".$currencyCode." ".getFormattedSalesTaxAmount(6,$paymentAmount)."|\n".
			"|Net amount:  ".$currencyCode." ".getFormattedNetAmount(6,$paymentAmount)."|\n".
			"||C\n";

	$myReceiptOrderLines .= "---||C\n".
			"====== YOUR PAYMENT DETAILS ======||CB\n".
			"---||C\n";
	
	//MAIN RECEIPT CONTENT:
        //payment details generated by the Adyen App

        //Before Footer (BF):
        //add elements before the receipt footer elements (after payment details)
	$myReceiptOrderLines .= "".
			"BF|Store Contact Info||C\n".
			"BF|Web:|myfavoritecoffeestore.com/contact|\n".
			"BF|Mail:|email@coffeestore.com|\n".
			"BF|Phone:|+1 234 555 6789|\n".
			"BF|||\n".
			"BF|Follow us on facebook: AdyenPayments||C\n".
			"BF|||\n";
	
	//FOOTER:
        //the configured footer from the Adyen CA and the generated PspReference, appear here.

        //After Footer (AF):
        //add some elements after the receipt footer
	$myReceiptOrderLines .= "BF|---||C\n".
			"BF|Adyen adds PspReference directly below footer:||C\n".
			"BF|---||C\n";	

	$myReceiptOrderLines .= "AF|||\n".
			"AF|We'd love to see you again!||CB\n".
			"AF|||\n";
	
	return $myReceiptOrderLines;
}


function formatAmount($amountValue){
	return number_format($amountValue/100,2);
}

function getFormattedSalesTaxAmount($salesTaxPercentage,$amount){
	return formatAmount(getSalesTaxAmount($salesTaxPercentage,$amount));
}

function getFormattedNetAmount($salesTaxPercentage,$amount){
	return formatAmount($amount - getSalesTaxAmount($salesTaxPercentage,$amount));
}

function getSalesTaxAmount($salesTaxPercentage,$amount){
	return ($salesTaxPercentage * $amount)/100;
}


/* =============================================================================================================================
   ========================================= Result inquiry  ===================================================================
   =============================================================================================================================
   The landing page recieves the result from the performed transaction and saves it in your database. The landing page is closed 
   and the focus is back in the initating page, where the result can be presented. 
   One way to handle this is to implement a form (resultInquiry) which will be posted when this page is active again (after the 
   Adyen App calls back the landing page, and it is closed). You have to present the result that you have saved in your database.
*/

if (isset($_REQUEST['merchantAccount']) && isset($_REQUEST['merchantReference'])) {
	//TO DO: Check transaction result in your database and present it here
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



