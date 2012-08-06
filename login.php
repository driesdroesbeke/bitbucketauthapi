<?php
	
	include_once( 'config.php' );
	
	$bb = new BitBucket(BB_USERNAME, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	$bb->set_callback_url('http://custom.me/bitbucket/auth.php');
	$login_url = $bb->get_bitbucket_auth_url();
	
?>
<html>
<head>
	<title>BitBucket API tests</title>
</head>
<body>
	<p>To get your access token you need to login on bitbucket site:</p>
	<p><a href="<?php echo $login_url; ?>" target="_blank">login to bitbucket.org: <?php echo $login_url; ?></a></p>
</body>
</html>