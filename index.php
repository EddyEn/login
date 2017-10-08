<?php

include_once 'facebook_login/facebookLogin.php';
include_once 'google_login/googleLogin.php';


$loginCallBackUrl = 'http://localhost/oauth/';
$logoutCallBackUrl = 'http://localhost/oauth/logout.php';
$output='';

$facebookEntity = new facebookLogin('1062398260563802','2fb41963fc59c8c67736077e4dae1206');
$facebookLoginUrl = $facebookEntity->logInUrl($loginCallBackUrl);
$facebookEntity->login();

$googleEntity = new googleLogin('318179630554-07ft15h4eoqpi0kfs9ov5frinjjtm8u2.apps.googleusercontent.com', 'ympr93J6jbqxN9nkltsSvg_p', 'test', $loginCallBackUrl);
$googleLoginUrl= $googleEntity->logInUrl($loginCallBackUrl);
$googleEntity->login();

//Rendering an output.
if(facebookLogin::isloggedIn()){
    $logoutURL = $facebookEntity->logOutUrl($logoutCallBackUrl);
    $output = detailProfileHtml( facebookLogin::getUserData(),$logoutURL);
}elseif(googleLogin::isLoggedIn()){
    $logoutURL = $googleEntity->logOutUrl($logoutCallBackUrl);
    $output = detailProfileHtml( googleLogin::getUserData(),$logoutURL);
}else{ // for non login
    $output = '<a href="'.htmlspecialchars($facebookLoginUrl).'" onClick="clickFacebookLogin()"><img src="images/fblogin-btn.png"></a>';
    $output .= '<a href="'.htmlspecialchars($googleLoginUrl).'"><img src="images/glogin.png"></a>';
}
    

function detailProfileHtml($userData,$logoutURL){
    // Render facebook profile data
    if(!empty($userData)) {
        $output = '<h1>User Profile Details </h1>';
        $output .= '<img src="' . $userData['picture'] . '">';
        $output .= '<br/>ID : ' . $userData['oauth_uid'];
        $output .= '<br/>Name : ' . $userData['first_name'] . ' ' . $userData['last_name'];
        $output .= '<br/>Email : ' . $userData['email'];
        $output .= '<br/>Gender : ' . $userData['gender'];
        $output .= '<br/>Locale : ' . $userData['locale'];
        $output .= '<br/>';
        $output .= '<br/><a href="' . $userData['link'] . '" target="_blank">Click to Visit the accout page</a>';
        $output .= '<br/>Logout from <a href="' . $logoutURL . '">Log out</a>';
    }else{
        $output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
    }
    return $output;
}
?>

<html>
<head>
<title>Login with Oauth using PHP </title>
<style type="text/css">
	h1{font-family:Arial, Helvetica, sans-serif;color:#999999;}
</style>
</head>
<body>
	<!-- Display login button / Facebook profile information -->
	<div><?php echo $output; ?></div>
</body>
</html>