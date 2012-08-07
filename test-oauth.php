<?php
	
	include_once( 'config.php' );

	$bb = new BitBucket(BB_USERNAME, BB_PASSWORD, BB_OAUTH_KEY, BB_OAUTH_SECRET);
	
	$my_repos = $bb->get_my_repositories();
	$my_rep = current($my_repos);
	//pa($my_rep);
?>
<html>
<head>
	<title>BitBucket API tests</title>
</head>
<body>
	<p>To clone repo "<?php echo $my_rep->name; ?>" you need to run:<br/>
		<code>git clone <?php echo $bb->get_repository_clone_url($my_rep->slug); ?></code>
	</p>

	<p>Commit history for "<?php echo $my_rep->name; ?>":</p>
	<?php pa( $bb->get_commit_history($my_rep->slug) ); ?>
	
	<p>You have such repos:</p>
	<?php pa( $my_repos ); ?>

	<p>You have access to such repos:</p>
	<?php pa( $bb->get_all_repositories()); ?>
</body>
</html>