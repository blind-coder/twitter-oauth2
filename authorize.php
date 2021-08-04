<?php
namespace TwitterOAuth2;

header("Content-Type: text/plain");

require_once __DIR__ . "/lib/OIDC.php";
$OIDC = new OIDC();

// Check if the client id is correct
if (!(array_key_exists("client_id", $_GET) && $OIDC->verifyClient($_GET["client_id"]))){
	http_response_code(400);
	echo json_encode(["error" => "invalid_request"]);
	exit();
}

// Acceptable response_type?
if (!(array_key_exists("response_type", $_GET) && $OIDC->verifyResponseType($_GET["response_type"]))){
	http_response_code(400);
	echo json_encode(["error" => "invalid_request"]);
	exit();
}

/* Collect stateful data in the session */
session_start();
$_SESSION["state"] = $_GET["state"];
$_SESSION["client_id"] = $_GET["client_id"];
$_SESSION["scope"] = $_GET["scope"];
$_SESSION["response_type"] = $_GET["response_type"];
if (array_key_exists("nonce", $_GET)){
	$_SESSION["nonce"] = $_GET["nonce"];
}
session_write_close();

/* Grab user id from SESSION, if it exists */
if (array_key_exists("id", $_SESSION)){
	$memberId = $_SESSION["id"];
}

require_once __DIR__ . '/lib/TwitterOauthService.php';
$twitterOauthService = new TwitterOauthService();
$redirectUrl = $twitterOauthService->getOauthVerifier();

if (empty($memberId)){
	/* No user session, request authentication from Twitter */
	header("Location: " . $redirectUrl);
	exit();
} else {
	/* User session exists, collect user information */
	require_once './lib/Member.php';
	$member = new Member();
	$memberData = $member->getUserById($_SESSION["id"])[0];

	/* Invalid user ID in _SESSION, request authentication */
	if (empty($memberData)) {
		session_start();
		unset($_SESSION["id"]);
		session_write_close();
		header("Location: " . $redirectUrl);
		exit();
	}

	/* Valid user, create a code and redirect */
	$memberId = $memberData["id"];
	$code = $member->getCode($memberId, $_SESSION["nonce"]);

	$redirect_uri = $OIDC->getCodeRedirectUri($code, $_SESSION["state"]);
	header("Location: $redirect_uri");
	exit();
}

http_response_code(400);
echo json_encode(["error" => "Unknown error"]);
