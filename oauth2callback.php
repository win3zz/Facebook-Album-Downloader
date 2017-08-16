<?php
require_once 'libs/SDK/google/vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_id.json');
$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/fb/oauth2callback.php');
$client->addScope(Google_Service_Drive::DRIVE);

if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  echo '<script type="text/javascript">window.close();</script>';
}
?>
