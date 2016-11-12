<?php
// Set sandbox (test mode) to true/false.
$sandbox = TRUE;

// Set PayPal API version and credentials.
$api_version = '85.0';
$api_endpoint = $sandbox ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
$api_username = $sandbox ? 'payment_api1.ninepay.net' : 'LIVE_USERNAME_GOES_HERE';
$api_password = $sandbox ? 'FUJEJ9YECLW3WKVQ' : 'LIVE_PASSWORD_GOES_HERE';
$api_signature = $sandbox ? 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AJig-QrED8OIZ-.EHyMWXfi0Q6Dk' : 'LIVE_SIGNATURE_GOES_HERE';
