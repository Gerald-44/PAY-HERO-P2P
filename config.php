<?php
$paymentConfig=[
    "channelId"=> 100,//Your payment or wallet channel ID
    "provider"=> "m-pesa",//Value can be m-pesa or sasapay
    "networkCode"=> "63902",// Network code for Safaricom
    "callbackUrl"=> "",//OPTIONAL: Callback URL to receive payment json payload on success
    "credentialId"=> null,//OPTIONAL: Your custom credential_id
    "basicAuthToken"=>"",//Your basic auth token
    "successURL"=>null,//OPTIONAL: URl to redirect user to if payment is success
    "failedURL"=>null//OPTIONAL: URl to redirect user to if payment fails
];