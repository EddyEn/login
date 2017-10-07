<?php
// Include FB config file && User class
require_once 'User.php';
// Include the autoloader provided in the SDK
require_once __DIR__ . '/facebook-php-sdk/autoload.php';

// Include required libraries
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

//Wired!?. Cannot make this session start into a function
if (!session_id() or session_status() == PHP_SESSION_NONE) {
    //The FacebookRedirectLoginHelper makes use of sessions to store a CSRF value
    session_start();
}
        
class facebookLogin
{
    private $fb;
    private $accessToken;
    private $helper;
    private $loginURL;
    private $fbPermissions;

    public function __construct($appID, $appKey)
    {
        $this->fbPermissions = array('email');  //Optional permissions

        $this->fb = new Facebook(array(
            'app_id' => $appID,
            'app_secret' => $appKey,
            'default_graph_version' => 'v2.2',
        ));

        $this->helper = $this->fb->getRedirectLoginHelper();
    }

    //If a user has not been logged in, the function will return null.
    private function getAccessToken()
    {
        if ($this::isLoggedIn()) {
            return $_SESSION['facebook_access_token'];
        }else{
            // Try to get access token
            try {
                return $this->accessToken = $this->helper->getAccessToken();
            } catch (FacebookResponseException $e) {
                //echo 'Graph returned an error: ' . $e->getMessage();
                //  exit;
                return NULL;
            } catch (FacebookSDKException $e) {
                //echo 'Facebook SDK returned an error: ' . $e->getMessage();
                // exit;
                return NULL;
            }
        }
    }
    
    // this is stateless call on this function
    public static function isLoggedIn(){  
        if (isset($_SESSION['facebook_access_token']) and !is_null($_SESSION['facebook_access_token'])) {
            return true;
        }else{
            return false;
        }
    }
    
    public function login(){
        if(!is_null($this->getAccessToken())){
            $this->storeAccessToken();
            $this->storeUserData();
        }
    }
    
    public static function logout(){
        // Remove access token from session
        unset($_SESSION['facebook_access_token']);

        // Remove user data from session
        unset($_SESSION['facebook_userData']);
    }
    
    private function storeAccessToken()
    {
        if (isset($this->accessToken)) {
            if (isset($_SESSION['facebook_access_token'])) {
                $this->fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            } else {
                // Put short-lived access token in session
                $_SESSION['facebook_access_token'] = (string)$this->accessToken;

                // OAuth 2.0 client handler helps to manage access tokens
                $oAuth2Client = $this->fb->getOAuth2Client();

                // Exchanges a short-lived access token for a long-lived one
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
                $_SESSION['facebook_access_token'] = (string)$longLivedAccessToken;

                // Set default access token to be used in script
                $this->fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
            }
            return true;
        }else{
            return false;
        }

    }

    private function retrieveUserData()
    {
        // Getting user facebook profile info
        try {
            $profileRequest = $this->fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
            return $profileRequest->getGraphNode()->asArray();
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            session_destroy();
            // Redirect user back to app login page
            header("Location: ./");
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
    
    private function storeUserData(){
        $fbUserProfile=$this->retrieveUserData();
          $_SESSION['facebook_userData'] = array(
            'oauth_provider' => 'facebook',
            'oauth_uid' => $fbUserProfile['id'],
            'first_name' => $fbUserProfile['first_name'],
            'last_name' => $fbUserProfile['last_name'],
            'email' => $fbUserProfile['email'],
            'gender' => $fbUserProfile['gender'],
            'locale' => $fbUserProfile['locale'],
            'picture' => $fbUserProfile['picture']['url'],
            'link' => $fbUserProfile['link']
        );
    }
    
    public static function getUserData(){
        if(isset($_SESSION['facebook_userData'])){
            return $_SESSION['facebook_userData'];
        }else{
            return NULL;
        }
    }

    public function logOutUrl($callBackLogOutUrl)
    {
        if ($this::isLoggedIn()) {
            // Get logout url
            return $this->helper->getLogoutUrl($this->getAccessToken(), $callBackLogOutUrl);
         }else{
             return '';
         }
    }
    
     public function logInUrl($callBackLogInUrl)
    {
        $this->loginURL = $this->helper->getLoginUrl($callBackLogInUrl, $this->fbPermissions);
        //to solve cross reference forgery error
        if (isset($_GET['state'])) {
            $_SESSION['FBRLH_state'] = $_GET['state'];
        }
        return $this->loginURL;
    }
}