<?php
session_start();
// Include config file
include_once("../../modules/aom.database.php");
include_once("../../modules/aom.form.php");
include_once("../../applications/config.php");
include_once('../../applications/AES.php');
use aomFramework\database;
use aomFramework\form;
$ENABLE_SANDBOX = TRUE;

$allowCardType = array("visa","mastercard","discover","amex","jcb");
$allowInit = array("check_card","add_fund");
if(isset($_POST) and isset($_POST["initType"]) and in_array($_POST["initType"],$allowInit) and isset($_SESSION["user_info"])){

  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
  $user_id = $_SESSION["user_info"]["uid"];
  if(isset($_POST["card_type"]) and in_array(strtolower($_POST["card_type"]),$allowCardType)
   and isset($_POST["card_no"]) and preg_match("/^([0-9]{16})+$/i",$_POST["card_no"]) and is_numeric($_POST["card_no"])
   and isset($_POST["card_exp"]) and preg_match("/^([0-1]+[0-9]+\/+[0-9]{4})+$/i",$_POST["card_exp"])
   and isset($_POST["card_cvv2"]) and preg_match("/^([0-9]{3,4})+$/i",$_POST["card_cvv2"])
   and isset($_POST["first_name"]) and preg_match("/^([a-zA-Z.[:space:]]*)+$/i",$_POST["first_name"])
   and isset($_POST["last_name"]) and preg_match("/^([a-zA-Z.[:space:]]*)+$/i",$_POST["last_name"])
   and isset($_POST["address"]) and preg_match("/^([a-zA-Z0-9.[:space:]]*)+$/i",$_POST["address"])
   and isset($_POST["city"]) and preg_match("/^([a-zA-Z]*)+$/i",$_POST["city"])
   and isset($_POST["state"]) and preg_match("/^([a-zA-Z]*)+$/i",$_POST["state"])
   and isset($_POST["country"]) and preg_match("/^([A-Z]{2})+$/i",$_POST["country"])
   and isset($_POST["phone"]) and preg_match("/^((09|08|06)[0-9]{8})+$/i",$_POST["phone"]) and is_numeric($_POST["phone"])
   and isset($_POST["postalcode"]) and preg_match("/^([0-9]{5})+$/i",$_POST["postalcode"]) and is_numeric($_POST["postalcode"])){

  	 $card_type = filter_input(INPUT_POST,"card_type");
  	 $card_no = (filter_input(INPUT_POST,"card_no"));
  	 $card_exp = intval(str_replace("/","",filter_input(INPUT_POST,"card_exp")));
  	 $card_cvv2 = (filter_input(INPUT_POST,"card_cvv2"));
  	 $first_name = filter_input(INPUT_POST,"first_name");
  	 $last_name = filter_input(INPUT_POST,"last_name");
  	 $street = filter_input(INPUT_POST,"address");
  	 $city = filter_input(INPUT_POST,"city");
  	 $state = filter_input(INPUT_POST,"state");
  	 $country = filter_input(INPUT_POST,"country");
     $phone = filter_input(INPUT_POST,"phone");
  	 $postalcode = filter_input(INPUT_POST,"postalcode");
  	 $initType = filter_input(INPUT_POST,"initType");
     $amount = floatval(intval(rand(1,100)) / 100);

  } else{

    $decryptKey = "amount_key";
    if( !isset($_POST["amount"]) or !is_numeric($db->decryptText($_POST["amount"],$decryptKey)) or !isset($creditCardAmount[$db->decryptText($_POST["amount"],$decryptKey)]) ){
      echo json_encode(array("status"=>"wrong_amt"));
      return false;
    }
    $getCardInfo = $db->select("credit_card")->find("user_id","=",$user_id)->execute()[0];
    $card_type = $getCardInfo["card_type"];
    $card_cvv2 = $getCardInfo["card_cvv2"];
    $card_no = $db->decryptText($getCardInfo["card_no"],"CVV2|{$card_cvv2}");
    $card_exp = $db->decryptText($getCardInfo["card_exp"],"CVV2|{$card_cvv2}");
    $first_name = $getCardInfo["first_name"];
    $last_name = $getCardInfo["last_name"];
    $street = $getCardInfo["street"];
    $city = $getCardInfo["city"];
    $state = $getCardInfo["state"];
    $country = $getCardInfo["country_code"];
    $phone = $getCardInfo["phone"];
    $postalcode = $getCardInfo["zip_code"];
    $initType = filter_input(INPUT_POST,"initType");
    $amount = $creditCardAmount[$db->decryptText(filter_input(INPUT_POST,"amount"),$decryptKey)];

  }

   if(strlen($card_exp)==5){$cardExp = "0".$card_exp;}else{$cardExp = $card_exp;}
	$genPayPalAccount = $db->select("a_billing_account")->find("account_type","=","paypal.pro")->execute(true);
	$checkPayPalAccount = $db->getNumber($genPayPalAccount,array());
	if($checkPayPalAccount==1){
		$getPayPalAccount = $db->getRow($genPayPalAccount,array());
		$api_username = $getPayPalAccount["username"];
		$api_password = $getPayPalAccount["password"];
		$api_signature = $getPayPalAccount["signature"];
    $api_id = $getPayPalAccount["id"];
		$api_endpoint = $ENABLE_SANDBOX ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$request_params = array
							(
							'METHOD' => 'DoDirectPayment',
							'USER' => $api_username,
							'PWD' => $api_password,
							'SIGNATURE' => $api_signature,
							'VERSION' => '85.0',
							'PAYMENTACTION' => 'Sale',
							'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
							'CREDITCARDTYPE' => $card_type,
							'ACCT' => $card_no,
							'EXPDATE' => $cardExp,
							'CVV2' => $card_cvv2,
							'FIRSTNAME' => $first_name,
							'LASTNAME' => $last_name,
							'STREET' => $street,
							'CITY' => $city,
							'STATE' => $state,
							'COUNTRYCODE' => $country,
							'ZIP' => $postalcode,
							'AMT' => $amount,
							'CURRENCYCODE' => 'USD',
							'DESC' => '# PAYMENT TRANSACTION #'
							);

		// Loop through $request_params array to generate the NVP string.
		$nvp_string = '';
		foreach($request_params as $var=>$val)
		{
			$nvp_string .= '&'.$var.'='.urlencode($val);
		}

		// Send NVP string to PayPal and store response
		$curl = curl_init();
				curl_setopt($curl, CURLOPT_VERBOSE, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_URL, $api_endpoint);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $nvp_string);

		$result = curl_exec($curl);
		curl_close($curl);

		// Function to convert NTP string to an array
		function NVPToArray($NVPString)
		{
			$proArray = array();
			while(strlen($NVPString))
			{
				// name
				$keypos= strpos($NVPString,'=');
				$keyval = substr($NVPString,0,$keypos);
				// value
				$valuepos = strpos($NVPString,'&') ? strpos($NVPString,'&'): strlen($NVPString);
				$valval = substr($NVPString,$keypos+1,$valuepos-$keypos-1);
				// decoding the respose
				$proArray[$keyval] = urldecode($valval);
				$NVPString = substr($NVPString,$valuepos+1,strlen($NVPString));
			}
			return $proArray;
		}

		// Parse the API response
		$result_array = NVPToArray($result);

    # LOG
    $log_return = $db->encryptText(json_encode($result_array),"log_return");
    $lowerStatusACK = strtolower($result_array['ACK']);
    $db->insert("a_payment_log",array("log_id"=>NULL,"log_name"=>"billing:result.credit_card","log_action"=>"result:{$lowerStatusACK}","log_return"=>$log_return,"log_ip"=>$_SERVER["REMOTE_ADDR"],"log_time"=>time()));

    if(isset($result_array["ACK"]) and isset($result_array["AMT"]) and $result_array["ACK"]=="Success"){
      $eCardNo = $db->encryptText($card_no,"CVV2|".$card_cvv2);
      $eCardEXP = $db->encryptText($card_exp,"CVV2|".$card_cvv2);
      # TRANSACTION
      $form = new form();
      $account_id = $api_id;
      $transaction_time = time();
      $amount = $result_array["AMT"];
      $transaction_id = $form->generateTXID();
      # INSERT AND UPDATE USER CREDIT CARD
      $checkCardExist = $db->select("credit_card")->find("user_id","=",$user_id)->execute(true);
      $checkCardExistNum = $db->getNumber($checkCardExist,array());
      $columnCreditCard = array(
        "card_no"=>$eCardNo,
        "card_exp"=>$eCardEXP,
        "card_cvv2"=>$card_cvv2,
        "card_type"=>$card_type,
        "first_name"=>$first_name,
        "last_name"=>$last_name,
        "phone"=>$phone,
        "street"=>$street,
        "city"=>$city,
        "state"=>$state,
        "country_code"=>$country,
        "zip_code"=>$postalcode,
        "last_time"=>time(),
        "last_ip"=>$_SERVER["REMOTE_ADDR"]);

      if($initType=="add_fund"){
        $getAccount = $db->select("account")->find("uid","=",$user_id)->execute()[0];
        $totalBalance = floatval($getAccount["balance"]) + floatval($amount);
        $updateBalance = array("balance"=>$totalBalance);
        $db->update("account",$updateBalance,"uid","=",$user_id);
        $db->insert("a_billing_report",array("id"=>NULL,"user_id"=>$user_id,"account_type"=>"credit.card","account_id"=>$account_id,"transaction_time"=>$transaction_time,"amount"=>$amount,"transaction_id"=>$transaction_id,"status"=>1,"last_report"=>time()));
      }

      if($checkCardExistNum>=1){
        //UPDATE
        $db->update("credit_card",$columnCreditCard,"user_id","=",$user_id);
      } else{
        //INSERT
        $columnCreditCard["user_id"] = $user_id;
        $db->insert("credit_card",$columnCreditCard);
      }
      echo json_encode(array("status"=>"success","data"=>$result_array));
    } else if(isset($result_array["ACK"]) and isset($result_array["AMT"]) and $result_array["ACK"]=="SuccessWithWarning"){
      echo json_encode(array("status"=>"has_warning","message"=>$result_array["L_SHORTMESSAGE0"]));
    } else{
      echo json_encode(array("status"=>"failed","message"=>$result_array["L_SHORTMESSAGE0"]));
    }

	} else{
		echo json_encode(array("status"=>"disabled"));
	}
} else{
	echo json_encode(array("status"=>"request","request"=>$_POST));
}
