<?php
$config = array(

"title"=>"9Cloud", //Website Name
"web_title"=>"9Cloud - Open your new world with us!", //Title Website
"host"=>"localhost", //Database Host
"user"=>"root", //Database Username
"pass"=>"MySQL", //Database Password
"db_name"=>"autovm", //Database Name
"xen_ip"=>"10.210.100.48", //XEN Server IP
"xen_user"=>"root", //XEN Server User
"xen_pass"=>"Lomer123", //XEN Server Pass
"urlPath"=>"http://localhost/autovm", //URL Path

);

$_CREATE_SERVER_PAGE_TEMPLATE = "sbs.create.php";

$RenewMonthList = array(1,2,3,6,12,24,48);
$paypalAmount = array(150,300,450,600,900,1750,3500); //THB
$creditCardAmount = array(50,100,150,300,500,700,900,1000); //THB
$ReInstallVMFee = 10.00; //Re-Install Fee

$SubfixCurrency       = '฿';
$SubfixFullCurrency   = 'THB';
$PayPalMode           = 'live'; // sandbox or live
$PayPalCurrencyCode   = $SubfixFullCurrency; //Paypal Currency Code
$PayPalReturnURL      = $config["urlPath"]."/libraries/paypal/process.php"; //Point to process.php page
$PayPalCancelURL      = $config["urlPath"]."/?page=canceled&application=paypal&time=".time(); //Cancel URL if user clicks cancel
$PayPalSuccessURL     = $config["urlPath"]."/?page=succeed&application=paypal&time=".time();
$PayPalWebLogo        = ''; //Your PayPal Website Logo
$PayPalTaxAmount      = 3.00;

$StartAtRegisterMoney = 0.00;


// Email Configuration
#######################################################################################
############################ DO NOT EDIT CONFIG BELOW #################################
#######################################################################################
$SendMailToUserWhenRegistered = false;
# Email Configuration
$EnableEmailConfirm   = false;
# Closed function because It's very slow process!
$EmailHost            = 'smtp.gmail.com'; # Set the hostname of the mail server
$EmailUsername        = 'siriwat576@gmail.com'; # Email username
$EmailPassword        = 'atm3907atm'; # Email password
$EmailPort            = 587; # Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$EmailSecure          = 'tls'; # Set the encryption system to use - ssl (deprecated) or tls
$EmailAuth            = true; # Whether to use SMTP authentication
$EmailSendFromName    = 'Aom'; # Set who the message is to be sent from
$EmailReplyToEmail    = ''; # Set an alternative reply-to address
$EmailReplyToName     = ''; # Set an alternative reply-to address
#######################################################################################
# Email Configuration
?>
