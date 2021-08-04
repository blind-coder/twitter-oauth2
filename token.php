<?php
namespace TwitterOAuth2;

header("Content-Type: application/json");

require_once __DIR__ . "/lib/OIDC.php";
$OIDC = new OIDC();

/* Check for valid authorization header */
if (!(array_key_exists("client_id", $_POST) && array_key_exists("client_secret", $_POST))){
	http_response_code(400);
	echo json_encode(["error" => "invalid_request"]);
	exit();
}

if (!$OIDC->verifyClient($_POST["client_id"], $_POST["client_secret"])){
	http_response_code(400);
	echo json_encode(["error" => "invalid_request"]);
	exit();
}

/* Check if the code is valid */
require_once './lib/Member.php';
$member = new Member();
$memberData = $member->getUserByCode($_POST["code"])[0];
if (empty($memberData)){
	http_response_code(400);
	echo json_encode(["error" => "invalid or expired code"]);
	exit();
}

/* Get a new access token and return it */
$accessToken = $member->getAccessToken($memberData["id"]);

$token = [
	"access_token" => $accessToken,
	"token_type" => "Bearer",
	"expires_in" => 3600,
];

/* Code is used up, invalidate it now */
$member->invalidateCode($_POST["code"]);

echo json_encode($token);
