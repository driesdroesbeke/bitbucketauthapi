# BitBucket + OAuth class.

contain code to login to bitbucket using OAuth 1.0a and example of how to use.
you can extend your class and write your own API calls which already use authentification.

#### Example usage

You can also find here examples how to use this code. To do that - you need to edit config.php and add your credentials there.

* **index.php**: contain code to check if you're logged in
* **login.php**: contain code to call class method to get login URL
* **auth.php**:  is a callback url. it's needed to get access token when you return back from BitBucket login process
* **test-oauth.php**: simple test - show your repos, connected to your account

**Note:** OAuth lib was taken from BitBucket OAuth libs links ( [simple OAuth library by Cal Henderson](https://svn.iamcal.com/public/php/lib_oauth/lib_oauth.php) ). But this lib was modified a bit.