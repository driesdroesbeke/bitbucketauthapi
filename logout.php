<?php
	include_once( 'config.php' );
	
	$bb = new BitBucket(BB_USERNAME, BB_PASSWORD, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	$bb->reset_tokens();
	
	// redirect back to index
	header("Location: index.php");
	exit();
?>