package com.adyen.pos_payment_advanced;

import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.content.pm.ResolveInfo;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.util.Base64;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Toast;
import java.text.DecimalFormat;

import java.util.List;


/**
 Create Payment with Android Intent launch for the Adyen App & Terminal

 The Android Intent launch for the Adyen App & Terminal provide a flexible, secure and easy way to allow merchants to set up
 Point of Sale payments.

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
 Select the device corresponding to the serial number of your Shuttle. A passkey screen should appear on  your Android device,
 click ‘Pair’. The Adyen App identifies the device, registers the terminal, loads the device configuration, verifies the terminal
 configuration and shows information 'Device boarded'.
 For details on these steps please consult the Shuttle Quick Start Guide:
 https://www.adyen.com/home/support/manuals#S-T.


 =============================================================================================================================
 ========================================= Parameters  =======================================================================
 =============================================================================================================================
 The Android Intent launch requires certain variables to be posted in order to create a payment possibility through the
 Adyen App & Terminal. The variables that you can post to the Android Intent launch are the following:

 FLAG_ACTIVITY_CLEAR_WHEN_TASK_RESET (FLAG_ACTIVITY_NEW_DOCUMENT for API 21): clears the called app from the activity stack so
 users arrive in the expected place next time this application is restarted.

 amount         		: Amount specified in minor units EUR 1,00 = 100
 currency       		: The three-letter capitalised ISO currency code to pay in i.e. EUR
 merchantReference		: The merchant reference is your reference for the payment. Also, to be printed on the receipt.

 Additional options:

 startImmediately		: Skip user input on App (default is 1/true)
 callbackAutomatic	    : Return automatically to Cash Register, skip receipt handling via App (default is 1/true)

 shopperEmail		    : The e-mailaddress of the shopper (optional)
 shopperReference		: The shopper reference, i.e. the shopper ID (optional)
 recurringContract	    : Can be "ONECLICK","RECURRING" or "ONECLICK,RECURRING", this allows you to store the payment details as
                          a ONECLICK and/or RECURRING contract. Please note that if you supply recurringContract, shopperEmail and
                          shopperReference become mandatory.

 tenderOptions 		    : Specifies additional processing options for the payment. Alphanumerical, comma-separated (urlencoded).
 GetAdditionalData	    : Mandatory for DCC, Loyalty, Recurring
 ReceiptHandler		    :
 AskGratuity			: Gratuity amount is requested on the Terminal
 ManualKeyedEntry		: Allows for Manual Entry of the Card Details. The terminal has to be configured for this option

 */


public class MainActivity extends ActionBarActivity {

    String merchantReference	    = "TEST-PAYMENT-" + android.text.format.DateFormat.format("yyyy-MM-dd", new java.util.Date());
    int paymentAmount 	    	    = 10100;
    String currencyCode 		    = "EUR";

    Boolean startImmediately 		= true;
    Boolean callbackAutomatic 		= true;

    String shopperEmail 		    = "ShopperEmailAddress";
    String shopperReference 		= "ShopperReference";
    String recurringContract 		= "RECURRING"; // "ONECLICK","RECURRING" or "ONECLICK,RECURRING"

    Boolean askGratuity 			= false;
    Boolean bypassPin 	    		= false;
    Boolean manualKeyedEntry 		= false;
    Boolean receiptHandler 		    = true;

    DecimalFormat form = new DecimalFormat("0.00");
    int decimalPaymentAmount = paymentAmount/100;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
    }

    public static boolean isIntentAvailable(Context context, String action) {
        final PackageManager packageManager = context.getPackageManager();
        final Intent intent = new Intent(action);
        List<ResolveInfo> list =
                packageManager.queryIntentActivities(intent,
                        PackageManager.MATCH_DEFAULT_ONLY);
        return list.size() > 0;
    }

    public void buttonOnClick(View v){

        String intentString = "com.adyen.posregister.payment";
        if (isIntentAvailable(this,intentString)) {
            Intent intent = new Intent(intentString);

            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_DOCUMENT);
            intent.putExtra("amount", "" + paymentAmount);
            intent.putExtra("currency", currencyCode);
            intent.putExtra("merchantReference", merchantReference);

            intent.putExtra("startImmediately", startImmediately);
            intent.putExtra("callbackAutomatic", callbackAutomatic);

            intent.putExtra("shopperEmail", shopperEmail);
            intent.putExtra("shopperReference", shopperReference);
            intent.putExtra("recurringContract", recurringContract);

            //Before Header (BH)
            //add elements before the receipt header elements
            String receipt =  "BH|Adyen Omni Shop||CB\n"
                              + "BH|wherever people pay||CB\n"
                              + "BH|||\n";

            //HEADER:
            //header configured from the Adyen CA will be placed here by the Adyen App

            //After Header (AH):
            //add order details: after header, before the payment details (default)
            receipt = receipt + "---||C\n"
                              + "Served by: Friendly Ghost John||CB\n"
                              + "Table: 149|" + android.text.format.DateFormat.format("EEEE, MMMM dd, yyyy", new java.util.Date()) + "|B\n"
                              + "---||C\n"
                              + "||C\n"
                              + "---||C\n"
                              + "====== YOUR ORDER DETAILS ======||CB\n"
                              + "---||C\n"
                              + " No. Description |$/Piece  Subtotal|\n"
                              + "  2  Coffee Grande| " + currencyCode + " 0.05  "  + currencyCode + " "+ form.format(decimalPaymentAmount) + "|\n"
                              + "|--------|\n"
                              + "|Order Total:  "  + currencyCode + " " + form.format(decimalPaymentAmount) + "|B\n"
                              + "|Gross amount:  " + currencyCode + " " + form.format(decimalPaymentAmount) + "|\n"
                              + "|Sales tax 6%:  " + currencyCode + " " + form.format((6 * decimalPaymentAmount)/100) + "|\n"
                              + "|Net amount:  "   + currencyCode + " " + form.format(decimalPaymentAmount - ((6 * decimalPaymentAmount)/100)) + "|\n"
                              + "||C\n";

            receipt = receipt + "---||C\n"
                              + "====== YOUR PAYMENT DETAILS ======||CB\n"
                              + "---||C\n";

            //MAIN RECEIPT CONTENT:
            //payment details generated by the Adyen App

            //Before Footer (BF):
            //add elements before the receipt footer elements (after payment details)
            receipt = receipt + ""
                              + "BF|Store Contact Info||C\n"
                              + "BF|Web:|myfavoritecoffeestore.com/contact|\n"
                              + "BF|Mail:|email@coffeestore.com|\n"
                              + "BF|Phone:|+1 234 555 6789|\n"
                              + "BF|||\n"
                              + "BF|Follow us on facebook: AdyenPayments||C\n"
                              + "BF|||\n";

            //FOOTER:
            //the configured footer from the Adyen CA and the generated PspReference, appear here.

            //After Footer (AF):
            //add some elements after the receipt footer
            receipt = receipt + "AF|||\n"
                              + "AF|We'd love to see you again!||CB\n"
                              + "AF|||\n";

            intent.putExtra("receiptOrderLines", Base64.encodeToString(receipt.getBytes(), Base64.DEFAULT));


            String tenderOptions 	   			  = "GetAdditionalData";
            if (askGratuity) 	    tenderOptions = tenderOptions + ",AskGratuity";
            if (receiptHandler) 	tenderOptions = tenderOptions + ",ReceiptHandler";
            if (bypassPin)		    tenderOptions = tenderOptions + ",BypassPin";
            if (manualKeyedEntry)	tenderOptions = tenderOptions + ",ManualKeyedEntry";

            intent.putExtra("tenderOptions",tenderOptions );

            startActivityForResult(intent, 1);

        } else {
            Context context = getApplicationContext();
            CharSequence text = "Adyen App not installed";
            int duration = Toast.LENGTH_SHORT;
            Toast toast = Toast.makeText(context, text, duration);
            toast.show();
        }
    }


    public void onActivityResult(int requestCode, int resultCode, Intent intent) {
        if (requestCode == 1) {
            if (resultCode == 1 && "APPROVED".equals(intent.getExtras().getString("result"))) {
                Context context = getApplicationContext();
                CharSequence text = "Payment Accepted";
                int duration = Toast.LENGTH_SHORT;
                Toast toast = Toast.makeText(context, text, duration);
                toast.show();

            } else {
                Context context = getApplicationContext();
                CharSequence text = "Payment Not Accepted: " + intent.getExtras().getString("result");
                int duration = Toast.LENGTH_SHORT;
                Toast toast = Toast.makeText(context, text, duration);
                toast.show();

            }
        }
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
