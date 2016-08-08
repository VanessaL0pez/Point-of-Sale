# Point-of-Sale
Adyen POS Integration
==============
The code examples in this reposity help you integrate with the Adyen Point of Sale platform. 
Please go through the code examples and read the documentation in the files itself. 
The code examples require you to change some parameters to connect to your Adyen account 
such as merchant account, user name and password. Also, it shows a possible way how to integrate
your system with the results from the performed transactions. 

## Code structure
```
Web to App
	1.PHP: pos-payment
	
	URL-initiated  launch of the Adyen App:
  	- pos-payment.php	:Simple link with the required parameters to the Adyen App
  	- pos-landing.php	:Landing page which handles the result from the performed transaction

	2.PHP: pos-payment-advanced
	URL-initiated launch of the Adyen App with an extended set of parameters
	- pos-payment-advanced.php:Advanced link that provides set of parameters to the Adyen App
  	- pos-landing-advanced.php:Landing page which handles the result from the performed transaction and the information recieved in the parameters

Android Intent 

  	1. pos-payment		:Simple App to App integration, implementing the required set of parameters
  	2. pos-payment-advanced	:App to App integration providing extensive set of parameters
```
## Manuals
The code examples are based on our POS Quick Integration manual and the POS manual which provides rich information on how our platform works. 
Please find our manuals on the Developers section at www.adyen.com. 

## Support
If you do have any suggestions or questions regarding the code examples please send an e-mail to possupport@adyen.com.
