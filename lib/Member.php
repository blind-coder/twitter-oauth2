<?php
namespace TwitterOAuth2;

class Member {

    private $db;
    private $userTbl;

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
			$code = uniqid("code_", true);
			$query = "UPDATE tbl_member SET code = ?, nonce = ?, code_exp = NOW()+300 WHERE id = ? LIMIT 1";
			$paramType="sss";
			$paramArray = array(
				$code,
				$nonce,
				$id
			);
			$this->db->execute($query, $paramType, $paramArray);
			return $code;
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
        $query = "SELECT * FROM tbl_member WHERE code = ? AND code_exp >= NOW()";
        $paramType = "s";
        $paramArray = array(
            $id
        );
        $result = $this->db->select($query, $paramType, $paramArray);
        return $result;
    }

    function invalidateCode($id)
    {
        $query = "UPDATE tbl_member SET code_exp = NOW()-300 WHERE id = ? LIMIT 1";
        $paramType = "i";
				$paramArray = array(
					$id
				);
        $result = $this->db->execute($query, $paramType, $paramArray);
    }
}
