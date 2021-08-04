<?php
namespace TwitterOAuth2;

class Member {

		private $db;
		private $userTbl;

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

		function insertMember($oauthId, $fullName, $screenName, $email)
		{
				$query = "INSERT INTO tbl_member (oauth_id, oauth_provider, full_name, screen_name, email) values (?,?,?,?,?)";
				$paramType = "ssssss";
				$paramArray = array(
						$oauthId,
						'twitter',
						$fullName,
						$screenName,
						$email
				);
				$this->db->insert($query, $paramType, $paramArray);
		}

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

		function invalidateCode($code)
		{
				$query = "DELETE FROM tbl_code WHERE code = ? LIMIT 1";
				$paramType = "s";
				$paramArray = array(
					$code
				);
				$result = $this->db->execute($query, $paramType, $paramArray);
		}

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
