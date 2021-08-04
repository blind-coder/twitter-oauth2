<?php
namespace TwitterOAuth2;

header("Content-Type: text/plain");
// Read configuration
require_once __DIR__ . "/etc/Config.php";

$CLIENT_ID = Config::CLIENT_ID;
$CLIENT_SECRET = Config::CLIENT_SECRET;
$REDIRECT_URI = Config::REDIRECT_URI;

// Correct client ID?
if ($_GET["client_id"] !== $CLIENT_ID){
	echo "Invalid request";
	exit();
}

// Acceptable response_type?
if ($_GET["response_type"] !== "code"){
	echo "Invalid request";
	exit();
}

/* Collect stateful data in the session */
session_start();
$_SESSION["state"] = $_GET["state"];
$_SESSION["client_id"] = $_GET["client_id"];
$_SESSION["scope"] = $_GET["scope"];
$_SESSION["response_type"] = $_GET["response_type"];
$_SESSION["nonce"] = $_GET["nonce"];
session_write_close();

/* Grab user id from SESSION, if it exists */
if (array_key_exists("id", $_SESSION)){
	$memberId = $_SESSION["id"];
}

if (empty($memberId)){
	/* No user session, request authentication from Twitter */
	require_once __DIR__ . '/lib/TwitterOauthService.php';
	$twitterOauthService = new TwitterOauthService();
	$redirectUrl = $twitterOauthService->getOauthVerifier();
	header("Location: " . $redirectUrl);
	exit();
} else {
	/* User session exist, collect user information */
	require_once './lib/Member.php';
	$member = new Member();
	$memberData = $member->getUserById($_SESSION["id"])[0];

	/* Invalid user ID in _SESSION, destroy data */
	if (empty($memberData)) {
		foreach(array_keys($_SESSION) as $key => $value){
			unset($_SESSION["$key"]);
		}
		session_destroy();
		echo "Unknown user";
		exit();
	}

	/* Valid user, create a code and redirect */
	$memberId = $memberData["id"];
	$code = $member->getCode($memberId, $_SESSION["nonce"]);
	$redirect_uri = str_replace("__CODE__", $code, $REDIRECT_URI);
	$redirect_uri = str_replace("__STATE__", $_SESSION['state'], $redirect_uri);
	header("Location: $redirect_uri");
	exit();
}

echo "Unknown error";
