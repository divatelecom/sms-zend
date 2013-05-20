DivaTxt – Zend library V1 May 2013

The DivaTxt library implements the DivaTxt API to allow simple SMS message sending from any Zend application.
 


Installation

The recommended installation location is in the Zend library folder e.g.
library
   |---Zend
    `--- Diva

Then amend the application.ini file to include an autoloader location e.g.
autoloaderNamespaces[] = "Diva_"



Usage:

First you need to obtain an  API username / Password from your DivaTxt account. Log into the portal and in the ‘Settings’ menu, select  ‘API’. Create a new user , and note down the username/password.
Please note the username will have been given a suffix which is an underscore followed by a number – this must be included.



To create a new instance

$divaTxt = new Diva_Txt(‘apiusername_1’,’api_password’);



Query your account balance

echo "Credit balance is :  ".$divaTxt->getBalance() . "<hr/>";

If your account is pre-pay, your balance will be positive, if your balance drops to zero you will not be able to send until you top up your account. Post-pay customer will see a negative balance, which will be reset to zero at the start of each month after you have been invoiced for the previous months usage.



Send a new message

The function takes the following arguments:

·	Mobile – this can be a comma separated string, a string, or an array of strings. 
·	Message – Up to 608 characters, UTF-8 encoding is expected. 
·	Sender – Who the message will come from when viewed by the recipient (11 chars alpha numeric)
·	Param1 – Optional Argument, to allow you to specify a message ID of your own
·	Param2 – Optional Argument, additional user provided message tracking ID


Simplest form of send:

$batch = $divaTxt->sendMessage("07772260651","message","sender");

Send to two mobiles with some of your own message tracking parameters:

$batch = $divaTxt->sendMessage("07775767845,07772260651","message","sender","001","myapp");

The batch number can be used to identify the message referred to in delivery reports, or to poll for status. 



Delivery Reports

To be notified of successful delivery, or otherwise, you need to build a controller that will receive a HTTP POST request from our servers. It needs to accept the following parameters:

·	batch_id  - This is the Batch ID returned when you submitted the messages
·	mobile - The mobile number of the recipient. Please note this may have been reformatted to international format.
·	report - This will either be success, pending or fail.
·	code - A numeric delivery code for more detailed investigation of delivery problems
·	param1 - Your reference, as set when submitting the message.
·	param2  -Your second reference field.

Once you have built this, you can specify the URL by calling the setHandler function below before sending any requests.

$divaTxt->setHandler(“http://www.example.com/test_handler”);




Query Message Status

If your site is an internal application, and so you are unable to accept delivery reports via the public internet, you can also poll the message status. Please note that if a delivery is delayed because a phone if powered off for example, you may have to poll every few hours to receive the final status.
If excessive poll requests are detected by our systems, your requests may be throttled.

You need to provide the batch number obtained from the sendMessage call :

print_r($divaTxt->queryMessage($batch));

The function returns and array of Report objects, one per number in the original request. There is a second form of the request that can filter on mobile as well:

print_r($divaTxt->queryMessage($batch,’0777555555’));



An example response is below:
Array
(
    [0] => Diva_Report Object
        (
            [batch] => 465868
            [mobile] => 447775767845
            [param1] => p1
            [param2] => p2
            [code] => 2
            [time] => 0000-00-00 00:00:00
            [credits] => 0
            [status] => pending
        )

    [1] => Diva_Report Object
        (
            [batch] => 465868
            [mobile] => 447772260651
            [param1] => p1
            [param2] => p2
            [code] => 2
            [time] => 0000-00-00 00:00:00
            [credits] => 0
            [status] => pending
        )

)




Message Report/Status:

The overall status of the message is summarised with the status field, this can be one of three: 
1.	success
2.	pending
3.	fail

The code field, gives more detail on failure reason

Common codes:


 
Code	Description	   
0	Successful delivery	   
2	Pending, usually only seen while attempting delivery	   
5	Invalid mobile number	   
6 	Unknown – generic failure	   
16	Absent Subscriber handset out of coverage, phone switch off, etc	   
21	Validity expired, network has been unable to deliver within the maximum time allowed to attempt, normally within 24 hours	   
		 



