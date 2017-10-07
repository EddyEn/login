<?php
session_start();

//Include Google client library 
include_once 'src/Google_Client.php';
include_once 'src/contrib/Google_Oauth2Service.php';

/*
 * Configuration and setup Google API
 */
$clientId = '318179630554-07ft15h4eoqpi0kfs9ov5frinjjtm8u2.apps.googleusercontent.com'; //Google client ID
$clientSecret = 'ympr93J6jbqxN9nkltsSvg_p'; //Google client secret
$redirectURL = 'http://localhost/login_with_google_using_php/'; //Callback URL

//Call Google API
$gClient = new Google_Client();
$gClient->setApplicationName('Login to test');
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectURL);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>