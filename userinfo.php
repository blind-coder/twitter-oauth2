<?php
namespace TwitterOAuth2;

function base64url_encode($data) {
	$b64 = base64_encode($data);

	if ($b64 === false) {
		return false;
	}

	// Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
	$url = strtr($b64, '+/', '-_');

	// Remove padding character from the end of line and return the Base64URL result
	return rtrim($url, '=');
}


header("Content-Type: application/json");

// Read configuration
require_once __DIR__ . "/etc/Config.php";
$CLIENT_ID = Config::CLIENT_ID;
$ISSUER = Config::ISSUER;
$KEYFILE = Config::KEYFILE;

/* Retrieve Authorization header, nginx and Apache variants */
$auth = null;
if (array_key_exists("HTTP_AUTHORIZATION", $_SERVER)){
	$auth = $_SERVER["HTTP_AUTHORIZATION"];
}
if (empty($auth)){
	$headers = apache_request_headers();
	error_log(var_export($headers, true));
	if (array_key_exists("Authorization", $headers)){
		$auth = $headers["Authorization"];
	}
}

/* No Authorization header */
if (empty($auth)){
	echo "Invalid request";
	exit();
}

$auth = explode(" ", $auth);
if (strtolower($auth[0]) !== "bearer"){
	echo "Invalid request";
	exit();
}

$auth = $auth[1];

/* Check if the token is valid */
require_once './lib/Member.php';
$member = new Member();
$memberData = $member->getUserByAuthToken($auth)[0];
if (empty($memberData)){
	echo "Invalid code.";
	exit();
}

/* Static JWT header */
$headers = ['alg' => 'RS256', 'typ' => 'JWT'];
$headers_encoded = base64url_encode(json_encode($headers));

/* Construct JWT payload */
$payload = [
	'sub' => $memberData["email"],
	'exp' => time()+300,
	'iss' => $ISSUER,
	'aud' => $CLIENT_ID,
	'iat' => time(),
	'kid' => 0,
	'nonce' => $memberData["nonce"],
];
$payload_encoded = base64url_encode(json_encode($payload));

/* Read private key */
$key = file_get_contents($KEYFILE);

/* Sign JWT */
openssl_sign("$headers_encoded.$payload_encoded", $signature, openssl_get_privatekey($key), 'sha256WithRSAEncryption'); 
$signature_encoded = base64url_encode($signature);

/* Create and echo JWT */
$token = "$headers_encoded.$payload_encoded.$signature_encoded";

/* Construct userinfo token */
$token = [
	'sub' => $memberData["email"],
	'iss' => $ISSUER,
	'aud' => $CLIENT_ID,
	'email' => $memberData["email"],
	'id_token' => $token
];

echo json_encode($token);
error_log(json_encode($token));
