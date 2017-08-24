<?php
session_start();
ini_set('max_execution_time', 300);
/**
 * it sets the application id and application secret id
 *
 */
$fb_app_id = '1575668789121612';//Please specify app id
$fb_secret_id = 'ce4476d96c08d20e93bbeb3064acfaac';//Please specify app secret

$fb_login_url = 'http://www.harshad75.ga/';

require_once ('libs/Facebook/autoload.php');
/**
 *
 * define the namespace alies
 * for the use of facebook namespace
 * easy to use
 */

		use Facebook\FacebookSession;
		use Facebook\FacebookRedirectLoginHelper;
		use Facebook\FacebookRequest;

/*setting application configuration
 and session
  */
FacebookSession::setDefaultApplication($fb_app_id, $fb_secret_id);
$helper = new FacebookRedirectLoginHelper($fb_login_url);

if (isset($_SESSION) && isset($_SESSION['facebook_access_token'])) {
	$session = new FacebookSession($_SESSION['facebook_access_token']);
	try {
		if (!$session -> validate()) {
			$session = null;
		}
	} catch ( Exception $e ) {
		$session = null;
	}
}
if (!isset($session) || $session === null) {
	try {
		$session = $helper -> getSessionFromRedirect();

	} catch( FacebookRequestException $ex ) {
		print_r($ex);
	} catch( Exception $ex ) {
		print_r($ex);
	}
}
function datafromfacebook($url){
       $session = new FacebookSession($_SESSION['facebook_access_token']);
       $request = new FacebookRequest($session, 'GET', $url);
	$response = $request -> execute();
	$user = $response -> getGraphObject() -> asArray();

	return $user;
   }
   
?>
