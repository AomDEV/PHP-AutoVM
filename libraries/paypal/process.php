<?php
session_start();
if (session_status() == PHP_SESSION_NONE) { session_start(); } //PHP >= 5.4.0
include_once("../../applications/config.php");
include_once("../../modules/aom.database.php");
include_once('../../applications/AES.php');
include_once("paypal.class.php");
use aomFramework\database;
$db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
$genPayPalAccount = $db->select("a_billing_account")->find("account_type","=","paypal")->execute(true);
$checkPayPalAccount = $db->getNumber($genPayPalAccount,array());
if($checkPayPalAccount==1){
	$getPayPalAccount = $db->getRow($genPayPalAccount,array());
	$PayPalApiUsername = $getPayPalAccount["username"];
	$PayPalApiPassword = $getPayPalAccount["password"];
	$PayPalApiSignature = $getPayPalAccount["signature"];
	$PayPalAccountID = $getPayPalAccount["id"];

	# PAYPAL DATABASE CONFIG
	$userId = intval($_SESSION["user_info"]["uid"]);
	$accountTable = "account";
	$accountUIDColumn = "uid";
	$accountBalanceColumn = "balance";
	$allowSaveDatabaseLog = true;
	$paypalDatabaseLogTable = "a_billing_report";
	$paypalDatabaseLogValue = array("id"=>NULL,"user_id"=>$userId,"account_type"=>"paypal","account_id"=>$PayPalAccountID,"transaction_time"=>time(),"amount"=>$price,"transaction_id"=>$txid,"status"=>1,"last_report"=>time());
	# PAYPAL DATABASE CONFIG

} else{die("PayPal is not Available right now!");return false;}

$paypalmode = ($PayPalMode=='sandbox') ? '.sandbox' : '';

if(isset($_POST) and isset($_POST["price"])) //Post Data received from product list page.
{
	//Mainly we need 4 variables from product page Item Name, Item Price, Item Number and Item Quantity.

	$_SESSION["transaction"]["last"]=substr(strtoupper(uniqid(md5(date("dmYHis")))),0,14);

	//Please Note : People can manipulate hidden field amounts in form,
	//In practical world you must fetch actual price from database using item id. Eg:
	//$ItemPrice = $mysqli->query("SELECT item_price FROM products WHERE id = Product_Number");

	$ItemName 		= "TOPUP #".$_SESSION["transaction"]["last"]; //filter_input(INPUT_POST,"itemname"); //Item Name
	$ItemPrice 		= $paypalAmount[intval($db->decryptText($_POST["price"],"!paypal_amount"))]; //filter_input(INPUT_POST,"itemprice"); //Item Price
	//$ItemNumber 	= filter_input(INPUT_POST,"itemnumber"); //Item Number
	//$ItemDesc 		= filter_input(INPUT_POST,"itemdesc"); //Item Number
	//$ItemQty 		= filter_input(INPUT_POST,"itemQty"); // Item Quantity

	$ItemTotalPrice = ($ItemPrice); //(Item Price x Quantity = Total) Get total amount of product;

	//Other important variables like tax, shipping cost
	$TotalTaxAmount 	= $PayPalTaxAmount;  //Sum of tax for all items in this order.
	$HandalingCost 		= 0.00;  //Handling cost for this order.
	$InsuranceCost 		= 0.00;  //shipping insurance cost for this order.
	$ShippinDiscount 	= 0.00; //Shipping discount for this order. Specify this as negative number.
	$ShippinCost 		= 0.00; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.

	//Grand total including all tax, insurance, shipping cost and discount
	$GrandTotal = ($ItemTotalPrice + $TotalTaxAmount + $HandalingCost + $InsuranceCost + $ShippinCost + $ShippinDiscount);

	//Parameters for SetExpressCheckout, which will be sent to PayPal
	$padata = 	'&METHOD=SetExpressCheckout'.
				'&RETURNURL='.urlencode($PayPalReturnURL ).
				'&CANCELURL='.urlencode($PayPalCancelURL).
				'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").

				'&L_PAYMENTREQUEST_0_NAME0='.urlencode($ItemName).
				'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($_SESSION["transaction"]["last"]).
				//'&L_PAYMENTREQUEST_0_DESC0='.urlencode($ItemDesc).
				'&L_PAYMENTREQUEST_0_AMT0='.urlencode($ItemPrice).
				'&L_PAYMENTREQUEST_0_QTY0=1'.

				/*
				//Additional products (L_PAYMENTREQUEST_0_NAME0 becomes L_PAYMENTREQUEST_0_NAME1 and so on)
				'&L_PAYMENTREQUEST_0_NAME1='.urlencode($ItemName2).
				'&L_PAYMENTREQUEST_0_NUMBER1='.urlencode($ItemNumber2).
				'&L_PAYMENTREQUEST_0_DESC1='.urlencode($ItemDesc2).
				'&L_PAYMENTREQUEST_0_AMT1='.urlencode($ItemPrice2).
				'&L_PAYMENTREQUEST_0_QTY1='. urlencode($ItemQty2).
				*/

				/*
				//Override the buyer's shipping address stored on PayPal, The buyer cannot edit the overridden address.
				'&ADDROVERRIDE=1'.
				'&PAYMENTREQUEST_0_SHIPTONAME=J Smith'.
				'&PAYMENTREQUEST_0_SHIPTOSTREET=1 Main St'.
				'&PAYMENTREQUEST_0_SHIPTOCITY=San Jose'.
				'&PAYMENTREQUEST_0_SHIPTOSTATE=CA'.
				'&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=US'.
				'&PAYMENTREQUEST_0_SHIPTOZIP=95131'.
				'&PAYMENTREQUEST_0_SHIPTOPHONENUM=408-967-4444'.
				*/

				'&NOSHIPPING=0'. //set 1 to hide buyer's shipping address, in-case products that does not require shipping

				'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice).
				'&PAYMENTREQUEST_0_TAXAMT='.urlencode($TotalTaxAmount).
				'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($ShippinCost).
				'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($HandalingCost).
				'&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($ShippinDiscount).
				'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($InsuranceCost).
				'&PAYMENTREQUEST_0_AMT='.urlencode($GrandTotal).
				'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&LOCALECODE=TH'. //PayPal pages to match the language on your website.
				'&LOGOIMG='.$PayPalWebLogo. //site logo
				'&CARTBORDERCOLOR=FFFFFF'. //border color of cart
				'&ALLOWNOTE=1';

				############# set session variable we need later for "DoExpressCheckoutPayment" #######
				$_SESSION["payment"]["paypal"]['ItemName'] 			=  $ItemName; //Item Name
				$_SESSION["payment"]["paypal"]['ItemPrice'] 			=  $ItemPrice; //Item Price
				$_SESSION["payment"]["paypal"]['ItemNumber'] 		=  $_SESSION["transaction"]["last"]; //Item Number
				//$_SESSION["PayPal"]['ItemDesc'] 			=  $ItemDesc; //Item Number
				$_SESSION["payment"]["paypal"]['ItemQty'] 			=  1; // Item Quantity
				$_SESSION["payment"]["paypal"]['ItemTotalPrice'] 	=  $ItemTotalPrice; //(Item Price x Quantity = Total) Get total amount of product;
				$_SESSION["payment"]["paypal"]['TotalTaxAmount'] 	=  $TotalTaxAmount;  //Sum of tax for all items in this order.
				$_SESSION["payment"]["paypal"]['HandalingCost'] 		=  $HandalingCost;  //Handling cost for this order.
				$_SESSION["payment"]["paypal"]['InsuranceCost'] 		=  $InsuranceCost;  //shipping insurance cost for this order.
				$_SESSION["payment"]["paypal"]['ShippinDiscount'] 	=  $ShippinDiscount; //Shipping discount for this order. Specify this as negative number.
				$_SESSION["payment"]["paypal"]['ShippinCost'] 		=   $ShippinCost; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
				$_SESSION["payment"]["paypal"]['GrandTotal'] 		=  $GrandTotal;

				//echo $padata;

		//We need to execute the "SetExpressCheckOut" method to obtain paypal token
		$paypal= new MyPayPal();
		$httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

		//Respond according to message we receive from Paypal
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{

				//Redirect user to PayPal store with Token received.
			 	//$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
			 	$paypalurl ='https://www.paypal.com/checkoutnow?token='.$httpParsedResponseAr["TOKEN"].'';
				header('Location: '.$paypalurl);

		}else{
			//Show error message
			echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
		}

}

$_SESSION["activeOrderResultPage"]=true;
//Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
if(isset($_GET["token"]) && isset($_GET["PayerID"]))
{
	//we will be using these two variables to execute the "DoExpressCheckoutPayment"
	//Note: we haven't received any payment yet.

	$token = $_GET["token"];
	$payer_id = $_GET["PayerID"];

	//get session variables
	$ItemName 			= $_SESSION["payment"]["paypal"]['ItemName']; //Item Name
	$ItemPrice 			= $_SESSION["payment"]["paypal"]['ItemPrice'] ; //Item Price
	$ItemNumber 		= $_SESSION["payment"]["paypal"]['ItemNumber']; //Item Number
	//$ItemDesc 			= $_SESSION["payment"]["paypal"]['ItemDesc']; //Item Number
	$ItemQty 			= $_SESSION["payment"]["paypal"]['ItemQty']; // Item Quantity
	$ItemTotalPrice 	= $_SESSION["payment"]["paypal"]['ItemTotalPrice']; //(Item Price x Quantity = Total) Get total amount of product;
	$TotalTaxAmount 	= $_SESSION["payment"]["paypal"]['TotalTaxAmount'] ;  //Sum of tax for all items in this order.
	$HandalingCost 		= $_SESSION["payment"]["paypal"]['HandalingCost'];  //Handling cost for this order.
	$InsuranceCost 		= $_SESSION["payment"]["paypal"]['InsuranceCost'];  //shipping insurance cost for this order.
	$ShippinDiscount 	= $_SESSION["payment"]["paypal"]['ShippinDiscount']; //Shipping discount for this order. Specify this as negative number.
	$ShippinCost 		= $_SESSION["payment"]["paypal"]['ShippinCost']; //Although you may change the value later, try to pass in a shipping amount that is reasonably accurate.
	$GrandTotal 		= $_SESSION["payment"]["paypal"]['GrandTotal'];

	$padata = 	'&TOKEN='.urlencode($token).
				'&PAYERID='.urlencode($payer_id).
				'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").

				//set item info here, otherwise we won't see product details later
				'&L_PAYMENTREQUEST_0_NAME0='.urlencode($ItemName).
				'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($ItemNumber).
				//'&L_PAYMENTREQUEST_0_DESC0='.urlencode($ItemDesc).
				'&L_PAYMENTREQUEST_0_AMT0='.urlencode($ItemPrice).
				'&L_PAYMENTREQUEST_0_QTY0='. urlencode($ItemQty).

				/*
				//Additional products (L_PAYMENTREQUEST_0_NAME0 becomes L_PAYMENTREQUEST_0_NAME1 and so on)
				'&L_PAYMENTREQUEST_0_NAME1='.urlencode($ItemName2).
				'&L_PAYMENTREQUEST_0_NUMBER1='.urlencode($ItemNumber2).
				'&L_PAYMENTREQUEST_0_DESC1=Description text'.
				'&L_PAYMENTREQUEST_0_AMT1='.urlencode($ItemPrice2).
				'&L_PAYMENTREQUEST_0_QTY1='. urlencode($ItemQty2).
				*/

				'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice).
				'&PAYMENTREQUEST_0_TAXAMT='.urlencode($TotalTaxAmount).
				'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($ShippinCost).
				'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($HandalingCost).
				'&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($ShippinDiscount).
				'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($InsuranceCost).
				'&PAYMENTREQUEST_0_AMT='.urlencode($GrandTotal).
				'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode);

	//We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
	$paypal= new MyPayPal();
	$httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

	//Check if everything went ok..
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
	{

			echo '<h2>Success</h2>';
			echo 'Your Transaction ID : '.urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);
			$_SESSION["transaction"]["paypal"]=urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);

				/*
				//Sometimes Payment are kept pending even when transaction is complete.
				//hence we need to notify user about it and ask him manually approve the transiction
				*/

				if('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
				{
					echo '<div style="color:green">Payment Received! Your product will be sent to you very soon!</div>';
				}
				elseif('Pending' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
				{
					echo '<div style="color:red">Transaction Complete, but payment is still pending! '.
					'You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
				}

				// we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
				// GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
				$padata = 	'&TOKEN='.urlencode($token);
				$paypal= new MyPayPal();
				$httpParsedResponseAr = $paypal->PPHttpPost('GetExpressCheckoutDetails', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

				#LOG
				$lowerStatusACK = strtolower($httpParsedResponseAr["ACK"]);
				$log_return = $db->encryptText(json_encode($httpParsedResponseAr),"log_return");
				$loggerPayment = array("log_id"=>NULL,"log_name"=>"billing:result.paypal","log_action"=>"result:{$lowerStatusACK}","log_return"=>$log_return,"log_ip"=>$_SERVER["REMOTE_ADDR"],"log_time"=>time());

				$db->insert("a_payment_log",$loggerPayment);
				#LOG

				if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
				{

					echo '<br /><b>Stuff to store in database :</b><br /><pre>';

					$buyerName = urldecode($httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"]);
					$buyerEmail = urldecode($httpParsedResponseAr["EMAIL"]);

					$price = intval($ItemPrice);
					$user = $db->select($accountTable)->find($accountUIDColumn,"=",$userId)->execute()[0];
					$total = $user[$accountBalanceColumn]+($price);
					$txid = $_SESSION["transaction"]["paypal"];
					if($allowSaveDatabaseLog==true){$db->insert($paypalDatabaseLogTable,$paypalDatabaseLogValue);}
					$db->update($accountTable,array($accountBalanceColumn=>$total,$accountUIDColumn,"=",$userId));

					echo '<pre>';
					print_r($httpParsedResponseAr);
					echo '</pre>';
					header("Location: {$PayPalSuccessURL}");
				} else  {
					echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
					echo '<pre>';
					print_r($httpParsedResponseAr);
					echo '</pre>';
					header("Location: {$PayPalCancelURL}");
				}

	}else{
			echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
			header("Location: {$PayPalCancelURL}");
	}
}
?>
