<?php
namespace TwitterOAuth2;

class OIDC {

		private $db;
		private $CLIENT_ID;
		private $CLIENT_SECRET;
		private $REDIRECT_URI;
		private $ISSUER;
		private $KEYFILE;

		function __construct() {
			require_once __DIR__ . '/../etc/Config.php';
			$this->CLIENT_ID = Config::CLIENT_ID;
			$this->CLIENT_SECRET = Config::CLIENT_SECRET;
			$this->REDIRECT_URI = Config::REDIRECT_URI;
			$this->ISSUER = Config::ISSUER;
			$this->KEYFILE = Config::KEYFILE;
		}

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

		function verifyClient($client_id, $client_secret = NULL){
			if ($client_id !== $this->CLIENT_ID){
				return false;
			}
			if (!empty($client_secret)){
				if ($client_secret !== $this->CLIENT_SECRET){
					return false;
				}
			}
			return true;
		}

		function verifyResponseType($responseType){
			return $responseType === "code";
		}

		function getCodeRedirectUri($code, $state){
			$redirect_uri = str_replace("__CODE__", $code, $this->REDIRECT_URI);
			$redirect_uri = str_replace("__STATE__", $state, $redirect_uri);
			return $redirect_uri;
		}

		function getUserInfo($memberData){
			/* Static JWT header */
			$headers = ['alg' => 'RS256', 'typ' => 'JWT'];
			$headers_encoded = $this->base64url_encode(json_encode($headers));

			/* Construct JWT payload */
			$payload = [
				'sub' => $memberData["email"],
				'exp' => time()+300,
				'iss' => $this->ISSUER,
				'aud' => $this->CLIENT_ID,
				'iat' => time(),
				'firstName' => $firstname,
				'lastName' => $lastname,
				'displayName' => $memberData["full_name"],
				'twitter_handle' => $memberData["screen_name"],
				'email' => $memberData["email"],
				'kid' => 0,
			];
			if (array_key_exists("nonce", $memberData) && !empty($memberData["nonce"])){
				$payload['nonce'] = $memberData["nonce"];
			}
			$payload_encoded = $this->base64url_encode(json_encode($payload));

			/* Read private key */
			$key = file_get_contents($this->KEYFILE);

			/* Sign JWT */
			openssl_sign("$headers_encoded.$payload_encoded", $signature, openssl_get_privatekey($key), 'sha256WithRSAEncryption'); 
			$signature_encoded = $this->base64url_encode($signature);

			/* Create and return JWT */
			$token = "$headers_encoded.$payload_encoded.$signature_encoded";

			/* Construct userinfo token */
			$name = explode(" ", $memberData["full_name"]);
			$lastname = array_pop($name);
			$firstname = implode(" ", $name);
			$token = [
				'sub' => $memberData["email"],
				'iss' => $this->ISSUER,
				'aud' => $this->CLIENT_ID,
				'firstName' => $firstname,
				'lastName' => $lastname,
				'displayName' => $memberData["full_name"],
				'twitter_handle' => $memberData["screen_name"],
				'email' => $memberData["email"],
				'id_token' => $token
			];

			return $token;
		}
}
