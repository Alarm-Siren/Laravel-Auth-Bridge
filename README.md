BridgeBB for Laravel 4.2 and PhpBB 3.1.3
===========

Allows phpBB3 to use the Laravel Auth driver to create/authenticate accounts.

#Installation:

##In Laravel :
#####Add to your composer.json
```
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/mathieutu/Laravel-Auth-Bridge"
  }
],
"require": {
    "webcode/bridgebb": "dev-master"
}
```

#####Run composer update
```
$ composer update
```

#####Register the BridgeBB Service Provider by adding it to your project's providers array in app.php
```
'providers' => array(
    'Webcode\BridgeBB\BridgeBBServiceProvider'
);
```

#####Create a secret api key in \vendor\webcode\bridgebb\src\config\api.php
```
'bridgebb-apikey' => 'secretkey'
```

#####Update the column names used for the Laravel Auth driver config/webcode/bridgebb/api.php
```
'username-column' => 'username',
'password-column' => 'password'
```
##In PhpBB
#####Copy all files from the /vendor/webcode/bridgebb/phpbb_root directory to your phpBB root.

#####Edit the file located at {PHPBB-ROOT}/ext/laravel/bridgebb/auth/provider/bridgebb.php
```
define('LARAVEL_URL', 'http://www.example.com/'); //your laravel application's url
define('BRIDGEBB_API_KEY', "secretkey"); //the same key you created earlier
```
#USAGE:
#####Login to the phpBB admin panel, go to `Customize` tab then `Extensions Management` and enable `BridgeBB Laravel Authentication`
#####Go to `General` tab then `CLIENT COMMUNICATION` > `Authentication` and set `bridgebb` as the authentication module.

Now all logins will be checked just with the Laravel Auth driver.
(the DB logins are ignored, be careful with your admin user : YOU MUST HAVE AN USER WITH THE SAME LOGIN IN LARAVEL !)

If the user is validated by the Laravel Auth driver phpBB will check if the account exists in its own database. 
If the user is validated but the account does not exist in the phpBB database the login information will be duplicated in the database.
It permits to have the option to switch to the default phpBB auth driver as all the logins will already exist (as they were at the users' first connection) .
