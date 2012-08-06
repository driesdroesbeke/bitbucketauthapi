<?php
	
	include_once( 'config.php' );

	$bb = new BitBucket(BB_USERNAME, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	$repos = $bb->get_repositories();
	
?>
<html>
<head>
	<title>BitBucket API tests</title>
</head>
<body>
	<p>You have such repos:</p>
	<?php pa($repos); ?>
</body>
</html>