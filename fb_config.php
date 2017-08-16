<?php
require_once __DIR__ . '/libs/SDK/facebook/vendor/autoload.php';
$fb = new Facebook\Facebook([
	'app_id' => 'xxxxxxxx',
	'app_secret' => 'xxxxxxxxxxxxxx',
	'default_graph_version' => 'v2.10',
]);
?>