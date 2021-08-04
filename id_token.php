<?php
namespace TwitterOAuth2;

header("Content-Type: application/json");

// Read configuration
require_once __DIR__ . "/etc/Config.php";

$CLIENT_ID = Config::CLIENT_ID;
$CLIENT_SECRET = Config::CLIENT_SECRET;
$ISSUER = Config::ISSUER;

/* Check for valid authorization header */
if ($CLIENT_ID !== $_POST["client_id"] && $CLIENT_SECRET !== $_POST["client_secret"]){
	echo "Invalid request\n";
	exit();
}

/* Check if the code is valid */
require_once './lib/Member.php';
$member = new Member();
$memberData = $member->getUserByCode($_POST["code"])[0];
if (empty($memberData)){
	echo "Invalid code.";
	exit();
}

$accessToken = $member->getAccessToken($memberData["id"]);

$token = [
	"access_token" => $accessToken,
	"token_type" => "Bearer",
	"expires_in" => 3600,
];

/* Code is valid, invalidate it now */
$member->invalidateCode($_POST["code"]);

echo json_encode($token);
