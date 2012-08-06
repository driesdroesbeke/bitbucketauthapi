<?php

	// place your credentials here
	define('BB_USERNAME', 'your username'); 
	define('BB_OAUTH_KEY', 'your consumer key');
	define('BB_OAUTH_SECRET', 'your consumer secret');

	define('ABS_PATH', dirname(__FILE__));
	
	include_once(ABS_PATH.'/functions.php');
	include_once(ABS_PATH.'/lib/class.BitBucket.php');
	
?>