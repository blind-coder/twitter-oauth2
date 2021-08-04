<?php
namespace TwitterOAuth2;

header("Content-Type: application/json");

/* Retrieve Authorization header, nginx and Apache variants */
$auth = null;
if (array_key_exists("HTTP_AUTHORIZATION", $_SERVER)){
	$auth = $_SERVER["HTTP_AUTHORIZATION"];
}
if (empty($auth)){
	$headers = apache_request_headers();
	if (array_key_exists("Authorization", $headers)){
		$auth = $headers["Authorization"];
	}
}

/* No Authorization header */
if (empty($auth)){
	http_response_code(400);
	echo json_encode(["error" => "invalid request"]);
	exit();
}

$auth = explode(" ", $auth);
if (strtolower($auth[0]) !== "bearer"){
	http_response_code(400);
	echo json_encode(["error" => "invalid request"]);
	exit();
}

$auth = $auth[1];
/* Check if the token is valid */
require_once __DIR__ . '/lib/Member.php';
$member = new Member();
$memberData = $member->getUserByAuthToken($auth)[0];
if (empty($memberData)){
	http_response_code(400);
	echo json_encode(["error" => "invalid request"]);
	exit();
}

/* Token is valid, get userinfo token and return it */
require_once __DIR__ . "/lib/OIDC.php";
$OIDC = new OIDC();
$token = $OIDC->getUserInfo($memberData);
echo json_encode($token);
