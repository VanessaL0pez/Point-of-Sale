package com.example.adyen.pos_payment;

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
import android.widget.Button;
import android.widget.Toast;

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
 merchantReference		: The merchant reference is your unique reference for the payment. Also, to be printed on the receipt.

 */


public class MainActivity extends ActionBarActivity {
    String merchantReference	    = "TEST-PAYMENT-" + android.text.format.DateFormat.format("yyyy-MM-dd", new java.util.Date());
    int paymentAmount 	    	    = 100;
    String currencyCode 		    = "EUR";

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
