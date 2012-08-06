<?php

require_once( dirname(__FILE__).'/class.OAuth.php' );
require_once( dirname(__FILE__).'/class.BitBucketAuth.php' );

/**
 *	class BitBucket
 *	contains only API functions.
 *	extends from BitBucketAuth, which have code to Auth with OAuth 1.0a
 */
class BitBucket extends BitBucketAuth{
	
	
	/**
	 *	get full list of repositories
	 */
	public function get_repositories(){
		
		$repos = array();
		$res = $this->request_api('GET', 'user/repositories', $params);
		if( !empty($res['response']) ){
			$repos = json_decode($res['response']);
		}
		
		//pa($repos,1);
		return $repos;
	}
	


}


?>