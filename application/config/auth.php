<?php

$google_auth_token = explode(':', $_SERVER['GOOGLE_AUTH_CLIENT']);
$twitter_auth_token = explode(':', $_SERVER['TWITTER_AUTH_CLIENT']);
$facebook_auth_token = explode(':', $_SERVER['FACEBOOK_AUTH_CLIENT']);

return [
		'google' => [
				'name'		=> 'Google',
				'button'	=> 'http://api.con-troll.org/images/auth/google/btn_google_signin_dark_normal_web.png',
				'type'		=> 'OpenIDConnect',
				'id'		=> $google_auth_token[0],
				'secret'	=> $google_auth_token[1],
//				'endpoint'	=> 'https://accounts.google.com/o/oauth2/auth',
				'endpoint'	=> 'https://accounts.google.com',
		],
		
		'twitter' => [
				'name'		=> 'Twitter',
				'button'	=> 'http://api.con-troll.org/images/auth/twitter/twitter_login.png',
				'type'		=> 'LeagueOAuth1',
				'id'		=> $twitter_auth_token[0],
				'secret'	=> $twitter_auth_token[1],
				'provider'	=> 'Twitter',
		],
		
		'facebook' => [
				'name'		=> 'Facebook',
				'button'	=> 'http://api.con-troll.org/images/auth/facebook/facebook-login-with.png',
				'type'		=> 'LeagueOAuth2',
				'id'		=> $facebook_auth_token[0],
				'secret'	=> $facebook_auth_token[1],
				'provider'	=> 'Facebook',
				'config'	=> [ 'graphApiVersion' => 'v2.4' ],
		],
		
		'email' => [
				'name'		=> 'Login with an E-Mail address',
				'type'		=> 'email',
				'button'		=> 'http://api.con-troll.org/images/auth/email/login.png',
		],
];
