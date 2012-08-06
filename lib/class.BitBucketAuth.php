<?php

/**
 *	Custom Exception class
 */
class BitBucketException extends Exception{}

/**
 *	BitBucketAuth class
 *	contain code to process OAuth 1.0a and methods to process api calls
 */
class BitBucketAuth {
	
	/**
	 *	these values are given by bitbucket
	 */
	protected $username;
	protected $key;
	protected $secret;
	
	protected $callback_url; // used to process bitbucket authentification
	
	/**
	 *	define where we save our tokens. you can use 'file' and 'cookie'.
	 *	you should give this folder write permissions if you want use "file"!
	 */
	protected $token_storage = 'cookie';
	
	/**
	 *	basic URLs to send requests to.
	 */
	protected $oauth_urls = array(
		'unauth_token' => 'https://bitbucket.org/!api/1.0/oauth/request_token/',
		'login' => 'https://bitbucket.org/!api/1.0/oauth/authenticate/',
		'access_token' => 'https://bitbucket.org/!api/1.0/oauth/access_token/',
	);
	protected $api_url = 'https://api.bitbucket.org/1.0';
	
	/**
	 *	helpers
	 */
	protected $consumer;
	protected $signature_method;

	protected $token;
	protected $unauth_token;
	protected $token_files = array(
		'unauth_token' => '.bbunauthtoken',
		'access_token' => '.bbaccesstoken',
		);
	
	
	/**
	 *	class constructor
	 *	@param  string  $username   BitBucket username
	 *	@param  string  $key      BitBucket consumer key
	 *	@param  string  $secret   BitBucket consumer secret
	 */
	public function __construct( $username, $key, $secret ){
		// set main vars
		$this->username = $username;
		$this->key = $key;
		$this->secret = $secret;
		
		// create consumer token and signature methods
		$this->consumer = new OAuthConsumer($this->key, $this->secret, NULL);
		$this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		// set token files full path
		$current_dir = dirname(__FILE__).'/';
		foreach($this->token_files as $key => $filename){
			$this->token_files[$key] = $current_dir . $filename;
		}
		
		// init tokens we have
		$this->token = $this->get_token( 'access_token' );
		$this->unauth_token = $this->get_token( 'unauth_token' );
	}
	
	/**
	 *	set callback url
	 *	@param  string  $callback_url  url to ping after BitBucket auth form is submitted.
	 */
	public function set_callback_url($callback_url){
		$this->callback_url = $callback_url;
	}
	
	/**
	 *	return callback url value
	 */
	public function get_callback_url(){
		return $this->callback_url;
	}
	
	/**
	 *	set token storage type
	 *	@param  string  $type   type of storage. only 2 values available: 'file' and 'cookie'
	 */
	public function set_token_storage_type( $type = 'file' ){
		$this->token_storage = $type;
	}
	
	/**
	 *	return token_storage value
	 */
	public function get_token_storage_type(){
		return $this->token_storage;
	}
	
	/**
	 *	get unauth token from storage
	 *	@param    string      $type   type of token to get: unauth_token / access_token
	 *	@return   OAuthToken  $token or NULL
	 */
	public function get_token( $type = 'access_token' ){
		$token = NULL;
		if( $this->token_storage == 'file' ){
			if( is_file($this->token_files[$type]) ){
				$_token = OAuthToken::from_string( file_get_contents($this->token_files[$type]) );
				if( !empty($_token->key) ){
					$token = $_token;
				}
			}
		}
		else{
			// add method to get token from cookie
			if( !empty($_COOKIE[$type]) ){
				$_token = OAuthToken::from_string( $_COOKIE[$type] );
				if( !empty($_token->key) ){
					$token = $_token;
				}
			}
		}
		
		return $token;
	}
	
	/**
	 *	set unauth token
	 *	@param  OAuthToken  $token   token to be saved
	 *	@param  string      $type    type of token to get: unauth_token / access_token
	 *	@return   null
	 */
	public function set_token( $token, $type = 'access_token' ){
		if( $this->token_storage == 'file' ){
			file_put_contents($this->token_files[$type], $token->to_string());
		}
		else{
			// add method to set token to cookie
			setcookie($type, $token->to_string(), time() + 3600*24*30, '/'); // set cookie for 30 days
		}
		
		$this->$type = $token;
	}
	
	/**
	 *	clean tokens 
	 */
	public function reset_tokens(){
		if( $this->token_storage == 'file' ){
			foreach($this->token_files as $file){
				if( is_file($file) ) unlink($file);
			}
		}
		else{
			// clean cookies
			foreach($this->token_files as $type => $file){
				setcookie($type, '', 0, '/'); // set cookie for 30 days
			}
			
		}
	}
	
	/**
	 *	get link to bitbucket auth form.
	 *	for this we need generate unauth access token and generate link using oauth class
	 */
	public function get_bitbucket_auth_url(){
		if( empty($this->unauth_token) ){
			$this->request_unauth_token();
		}
		
		$auth_req = OAuthRequest::from_consumer_and_token($this->consumer, $this->unauth_token, "GET", $this->oauth_urls['login']);
		$auth_req->sign_request($this->signature_method, $this->consumer, $this->unauth_token);
		return $auth_req->to_url();
	}
	
	/**
	 *	send request to 
	 */
	public function request_unauth_token(){
		$unauth_request = OAuthRequest::from_consumer_and_token($this->consumer, NULL, "GET", $this->oauth_urls['unauth_token']);
		$unauth_request->set_parameter('oauth_callback', $this->callback_url, false);
		$unauth_request->sign_request($this->signature_method, $this->consumer, NULL);
	
		$unauth_response = $this->curl( $unauth_request->to_url() );
		
		$unauth_token = OAuthToken::from_string($unauth_response['response']);
		
		$this->set_token($unauth_token, 'unauth_token');
	}
	
	/**
	 *	send request for access token
	 *	@param  string  $oauth_verifier  verify string / you get it from BitBucket after auth
	 *	@param  string  $oauth_token     token key  / you get it from BitBucket after auth
	 */
	public function request_access_token( $oauth_verifier, $oauth_token ){
		// check that values given is not empty
		if( empty($oauth_token) || empty($oauth_verifier) ){
			throw new BitBucketException("Error getting access token: empty verifier and token");
			return;
		}
		
		// check that token is the same as we have
		if( empty($this->unauth_token) || strcmp($this->unauth_token->key, $oauth_token) != 0 ){
			// we have bad tokens. clean all and generate error
			$this->reset_tokens();
			
			throw new BitBucketException("Error getting access token: wrong unregistered access token.");
			return;
		}
		
		// request access token
		$access_token_req = OAuthRequest::from_consumer_and_token($this->consumer, $this->unauth_token, "GET", $this->oauth_urls['access_token']);
		$access_token_req->set_parameter('oauth_verifier', $oauth_verifier);
		$access_token_req->sign_request($this->signature_method, $this->consumer, $this->unauth_token);
		//pa($access_token_req,1);
		$access_token_response = $this->curl( $access_token_req->to_url() );
		if( $access_token_response['status'] != 200 ){
			throw new BitBucketException("BitBucket OAuth: ".$access_token_response['response']);
			return;
		}
	
		$token = OAuthToken::from_string( $access_token_response['response'] );
		
		$this->set_token( $token );
	}
	
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
	
	/**
	 *	prepare url and params, send request
	 */
	public function request_api($type, $uri, $params = array()){
		
		// prepare request url
		$request_url = $this->api_url.'/'.$uri;

		// prepare access token header
		$access_token_req = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, "GET", $request_url);
		$access_token_req->sign_request($this->signature_method, $this->consumer, $this->token);
		
		// curl lib require header opt be set as array, so prepare it:
		$curl_opts = array(
			'httpheader' => array( $access_token_req->to_header() ),
		);
		
		if( $type == 'GET' ){
			if( !empty($params) ){
				$query_string = http_build_query($params);
				$request_url .= '?' . $query_string;
			}
			$result = $this->curl($request_url, NULL, $curl_opts);
		}
		else{
			$result = $this->curl($request_url, $params, $curl_opts);
		}
		
		return $result;
	}
	
	/**
	*	curl
	*/	 	
	protected function curl($url, $params = array(), $curl_options = array())
	{
		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		$ch = curl_init();
		
		if(!empty($params))
		{
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
		}
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,7);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		
		// this line makes it work under https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
		
		foreach($curl_options as $option => $value)
		{
			if(defined('CURLOPT_'.strtoupper($option)))
			{
				curl_setopt($ch, constant('CURLOPT_'.strtoupper($option)) ,$value);
			}	
		}
	
		$_result = curl_exec ($ch);
		$_info = curl_getinfo($ch);
		
		$result = array(
			'response' => $_result,
			'error' => curl_error($ch),
			#'errno' => curl_errno($ch),
			#'info' => $_info,
		);
		$result['status'] = $_info['http_code'];
		
		return $result;
	}

}


?>