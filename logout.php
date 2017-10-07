<?php

include_once 'facebook_login_with_php/facebookLogin.php';
include_once 'login_with_google_using_php/googleLogin.php';

facebookLogin::logout();
$googleEn = new googleLogin('318179630554-07ft15h4eoqpi0kfs9ov5frinjjtm8u2.apps.googleusercontent.com', 'ympr93J6jbqxN9nkltsSvg_p', 'test', $loginCallBackUrl);
$googleEn->logout();
// Redirect to the homepage
header("Location:index.php");
?>