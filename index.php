<?php
	
	include_once( 'config.php' );
	
	$bb = new BitBucket(BB_USERNAME, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	$access_token = $bb->get_token();
	
?>
<html>
<head>
	<title>BitBucket API tests</title>
</head>
<body>
	<p>This is BitBucket API test page.</p>
	<?php if( empty($access_token) ) : ?>
		<p><a href="/bitbucket/login.php">login</a></p>
	<?php else : ?>
		<p><a href="/bitbucket/test-oauth.php">test oauth</a></p>
	<?php endif; ?>
</body>
</html>