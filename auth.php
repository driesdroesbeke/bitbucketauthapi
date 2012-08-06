<?php

	include_once( 'config.php' );
	
	$bb = new BitBucket(BB_USERNAME, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	$bb->request_access_token($_GET['oauth_verifier'], $_GET['oauth_token']);
	
	// redirect back to index
	header("Location: index.php");
	exit();
?>