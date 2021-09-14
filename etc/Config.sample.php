<?php
namespace TwitterOAuth2;

class Config
{
	const WEB_ROOT = "https://example/twitter";

	// Database Configuration
	const DB_HOST = "mysql.example.com";
	const DB_USERNAME = "twitter-oauth";
	const DB_PASSWORD = "secret123";
	const DB_NAME = "twitter_oauth";

	// Twitter API configuration
	const TW_CONSUMER_KEY = 'xxx';
	const TW_CONSUMER_SECRET = 'xxx';
	const TW_CALLBACK_URL = Config::WEB_ROOT . '/signin.php';

	// My Client ID and Secret
	// These next two can be anything, really, but should be sufficiently long, random strings.
	// Client ID creation example:     strings /dev/urandom | head -n 256 | md5sum
	// Client Secret creation example: strings /dev/urandom | head -n 256 | sha256sum
	const CLIENT_ID = "xxx";
	const CLIENT_SECRET = "xxx";

	// Issuer will be part of the JWT
	const ISSUER = "Twitter / OAuth2.0 Gateway";

	// Keyfile is the secret key to use. Must not be password protected. Must be readable by PHP / Webserver
	// !!! MUST ABSOLUTELY NOT BE ACCESSIBLE BY A WEB BROWSER !!!
	const KEYFILE = "certs/gateway.nopass.pem";
	// KEYPHRASE is the passphrase for the private. If it is null or the empty string, it's not used
	const KEYPHRASE = null;

	// Upstream redirect URI
	// You will get this from your OAuth2 Client
	const REDIRECT_URI = "https://localhost/callback?code=__CODE__&state=__STATE__";
}
