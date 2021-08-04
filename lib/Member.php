<?php
namespace TwitterOAuth2;

class Member {

		private $db;
		private $userTbl;

		/**
		 * return a pseudo-unique pseudo-uuid string
		 *
		 * not 100% unique and certainly not cryptographically strong, but good enough
		 *
		 * @param none
		 * @return a uuid-like string, hopefully unique
		 */
		function getUniqueId(){
			$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
			$tmpl = "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX";
			$retVal = "";
			for ($i = 0; $i < strlen($tmpl); $i++){
				if ($tmpl[$i] !== "X"){
					$retVal .= $tmpl[$i];
					continue;
				}
				$retVal .= $chars[rand(0,strlen($chars)-1)];
			}
			return $retVal;
		}

		function __construct()
		{
				require_once __DIR__ . '/DataSource.php';
				$this->db = new DataSource();
		}

		/**
		 * gets an existing account from the database
		 *
		 * @param $twitterOauthId an oauth id returned by twitter
		 * @return an array of accounts, ideally with only one element
		 */
		function isExists($twitterOauthId)
		{
				$query = "SELECT * FROM tbl_member WHERE oauth_id = ?";
				$paramType = "s";
				$paramArray = array(
						$twitterOauthId
				);
				$result = $this->db->select($query, $paramType, $paramArray);
				return $result;
		}

		/**
		 * insert a twitter handle into the database
		 *
		 * @param $oauthId oauth ID returned by twitter
		 * @param $fullName full name returned by twitter
		 * @param $screenName twitter handle
		 * @param $email email returned by twitter
		 */
		function insertMember($oauthId, $fullName, $screenName, $email)
		{
				$query = "INSERT INTO tbl_member (oauth_id, oauth_provider, full_name, screen_name, email) values (?,?,?,?,?)";
				$paramType = "sssss";
				$paramArray = array(
						$oauthId,
						'twitter',
						$fullName,
						$screenName,
						$email
				);
				$this->db->insert($query, $paramType, $paramArray);
		}

		/**
		 * creates and returns a short-lived OIDC code for an account
		 *
		 * @param $id database id (primary key) of the account
		 * @param $nonce optional nonce sent by the OIDC relying party
		 * @return the OIDC code
		 */
		function getCode($id, $nonce){
			$code = $this->getUniqueId();
			$query = "INSERT INTO tbl_code (member_id, code, nonce, code_exp) values (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
			$paramType="iss";
			$paramArray = array(
				$id,
				$code,
				$nonce
			);
			$this->db->execute($query, $paramType, $paramArray);
			return $code;
		}

		/**
		 * creates and returns a longer-lived access token for an account
		 *
		 * @param $id database id (primary key) of the account
		 * @return the access token
		 */
		function getAccessToken($id){
			$token = $this->getUniqueId();
			$query = "INSERT INTO tbl_token (member_id, token, token_exp) values (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
			$paramType="is";
			$paramArray = array(
				$id,
				$token,
			);
			$this->db->execute($query, $paramType, $paramArray);
			return $token;
		}

		/**
		 * fetches account information by id (primary key)
		 *
		 * @param $id the id (primary key) of the account
		 * @return an array of accounts, ideally with only one element
		 */
		function getUserById($id)
		{
				$query = "SELECT * FROM tbl_member WHERE id = ?";
				$paramType = "i";
				$paramArray = array(
						$id
				);
				$result = $this->db->select($query, $paramType, $paramArray);
				return $result;
		}

		/**
		 * fetches account information by OIDC code
		 *
		 * @param $id the code provided by the relying party
		 * @return an array of accounts, ideally with only one element
		 */
		function getUserByCode($id)
		{
			$query = "SELECT tbl_member.*, tbl_code.code, tbl_code.code_exp, tbl_code.nonce FROM tbl_member
				LEFT JOIN tbl_code on tbl_code.member_id = tbl_member.id WHERE code = ? AND code_exp >= NOW()";
			$paramType = "s";
			$paramArray = array(
				$id
			);
			$result = $this->db->select($query, $paramType, $paramArray);
			return $result;
		}

		/**
		 * fetches account information by authorization token
		 *
		 * @param $id the auth token provided by the relying party
		 * @return an array of accounts, ideally with only one element
		 */
		function getUserByAuthToken($token)
		{
			$query = "SELECT tbl_member.*, tbl_token.token, tbl_token.token_exp FROM tbl_member
				LEFT JOIN tbl_token on tbl_token.member_id = tbl_member.id WHERE token = ? AND token_exp >= NOW()";
			$paramType = "s";
			$paramArray = array(
				$token
			);
			$result = $this->db->select($query, $paramType, $paramArray);
			return $result;
		}

		/**
		 * invalidates a code
		 *
		 * codes are single-use tokens that can be exchanged for an access token,
		 * so they must be invalidated after successful use.
		 *
		 * @param $code the code to invalidate
		 */
		function invalidateCode($code)
		{
				$query = "DELETE FROM tbl_code WHERE code = ? LIMIT 1";
				$paramType = "s";
				$paramArray = array(
					$code
				);
				$result = $this->db->execute($query, $paramType, $paramArray);
		}

		/**
		 * invalidates an auth token
		 *
		 * authorization tokens are longer lived, but can be invalidated as well
		 *
		 * @param $token the token to invalidate
		 */
		function invalidateToken($token)
		{
				$query = "DELETE FROM tbl_token WHERE token = ? LIMIT 1";
				$paramType = "s";
				$paramArray = array(
					$token
				);
				$result = $this->db->execute($query, $paramType, $paramArray);
		}
}
