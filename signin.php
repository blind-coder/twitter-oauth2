<?php
namespace TwitterOAuth2;

require_once './lib/TwitterOauthService.php';
$TwitterOauthService = new TwitterOauthService();

session_start();
$oauthTokenSecret = $_SESSION["oauth_token_secret"];

if (! empty($_GET["oauth_verifier"]) && ! empty($_GET["oauth_token"])) {
    $userData = $TwitterOauthService->getUserData($_GET["oauth_verifier"], $_GET["oauth_token"], $oauthTokenSecret);
		error_log(var_export($userData, true));
    $userData = json_decode($userData, true);
    if (! empty($userData)) {
        $oauthId = $userData["id"];
        $fullName = $userData["name"];
        $screenName = $userData["screen_name"];
        $email = $userData["email"];

        require_once './lib/Member.php';
        $member = new Member();
        $isMemberExists = $member->isExists($oauthId);
        if (empty($isMemberExists)) {
            $memberId = $member->insertMember($oauthId, $fullName, $screenName, $email);
						$isMemberExists = $member->isExists($oauthId);
        }
				error_log(var_Export($isMemberExists, true));
				$memberId = $isMemberExists[0]["id"];
        if (! empty($memberId)) {
            unset($_SESSION["oauth_token"]);
            unset($_SESSION["oauth_token_secret"]);
            $_SESSION["id"] = $memberId;
            $_SESSION["email"] = $email;
            $_SESSION["fullName"] = $fullName;
            $_SESSION["screenName"] = $screenName;
            header("Location: authorize.php?client_id=".$_SESSION["client_id"]."&state=".$_SESSION["state"]."&response_type=".$_SESSION["response_type"]."&scope=".$_SESSION["scope"]);
        }
    }
} else {
    ?>
<HTML>
<head>
<title>Twitter Authentication</title>
</head>
<body>
Sorry, something went wrong.
</body>
</HTML>
<?php
}
session_write_close();
exit();
