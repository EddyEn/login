<?php
//Include GP config file && User class
//Include Google client library 
include_once 'src/Google_Client.php';
include_once 'src/contrib/Google_Oauth2Service.php';

if (!session_id() or session_status() == PHP_SESSION_NONE) {
    session_start();
}

class googleLogin
{
    private $gClient;
    private $google_oauthV2;
    public function __construct($appID, $appKey, $appName, $loginCallbackUrl)
    {
        //Call Google API
        $this->gClient = new Google_Client();
        $this->gClient->setApplicationName($appName);
        $this->gClient->setClientId($appID);
        $this->gClient->setClientSecret($appKey);
        $this->gClient->setRedirectUri($loginCallbackUrl);
        $this->google_oauthV2 = new Google_Oauth2Service($this->gClient);
    }
    
    private function retrieveUserData()
    {
        return $this->google_oauthV2->userinfo->get();
    }
    
    public static function isLoggedIn(){  
        if (isset($_SESSION['google_access_token']) and !is_null($_SESSION['google_access_token'])) {
            return true;
        }else{
            return false;
        }
    }
    
    public function logInUrl($loginCallbackUrl){
        $this->gClient->setRedirectUri($loginCallbackUrl);
        $authUrl = $this->gClient->createAuthUrl();
	return filter_var($authUrl, FILTER_SANITIZE_URL);
    }
    
    public function logOutUrl($loginCallbackUrl){
        return $loginCallbackUrl;
    }
    
    public function login(){
        if($this::isLoggedIn()){
            return true;
        }
        
        try{
            if(isset($_GET['code'])){
                $this->gClient->authenticate($_GET['code']);
                $_SESSION['google_access_token'] = $this->gClient->getAccessToken();
                if (isset($_SESSION['google_access_token'])) {
                    $this->gClient->setAccessToken($_SESSION['google_access_token']);
                    $this->storeUserData();
                    return true;
                }
            }
        }catch(Exception $e){
            //echo $e;
            return false;
        }
        
        return false;
    }
    
    public function logout(){
        //Unset token and user data from session
        unset($_SESSION['google_access_token']);
        unset($_SESSION['google_userData']);

        //Reset OAuth access token
        $this->gClient->revokeToken();

        //Destroy entire session
        session_destroy();
    }
    
    public function storeUserData(){
        $gpUserProfile = $this->retrieveUserData();
        $_SESSION['google_userData'] = array(
            'oauth_provider'=> 'google',
            'oauth_uid'     => $gpUserProfile['id'],
            'first_name'    => $gpUserProfile['given_name'],
            'last_name'     => $gpUserProfile['family_name'],
            'email'         => $gpUserProfile['email'],
            'gender'        => $gpUserProfile['gender'],
            'locale'        => $gpUserProfile['locale'],
            'picture'       => $gpUserProfile['picture'],
            'link'          => $gpUserProfile['link']
        );
    
    }
    
    public static function getUserData(){
        if(isset($_SESSION['google_userData'])){
            return $_SESSION['google_userData'];
        }else {
            return NULL;
        }
    }
    
} 

