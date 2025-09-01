<?php

require_once 'inc/mysql.php';

//session_start();
$modDB = new modDB();
if (!empty($_GET['error'])) {
    die($_GET['error_description']);
    exit;
}
//var_dump($_SESSION);
//retrieve session data from database
$sessionData = $modDB->QuerySingle('SELECT * FROM tblAuthSessions WHERE txtSessionKey=\'' . $modDB->Escape($_SESSION['sessionkey']) . '\'');


if ($sessionData) {
    // Request token from Azure AD
    $oauthRequest = 'grant_type=authorization_code&client_id=' . _OAUTH_CLIENTID . '&redirect_uri=' . urlencode(_URL . '/sso_auth.php') . '&code=' . $_GET['code'] . '&client_secret=' . urlencode(_OAUTH_SECRET) . '&code_verifier=' . $sessionData['txtCodeVerifier'];
    $ch = curl_init(_OAUTH_SERVER . 'token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $oauthRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($cError = curl_error($ch)) {
        die($cError);
    }
    curl_close($ch);
    // Decode response from Azure AD. Extract JWT data from supplied access_token and id_token and update database.
    $reply = json_decode($response);
    if (!empty($reply->error)) {
        die($reply->error_description);
    }

    $idToken = base64_decode(explode('.', $reply->id_token)[1]);
    $modDB->Update('tblAuthSessions', array('txtToken' => $reply->access_token, 'txtRefreshToken' => $reply->refresh_token, 'txtIDToken' => $idToken, 'txtRedir' => '', 'dtExpires' => date('Y-m-d H:i:s', strtotime('+' . $reply->expires_in . ' seconds'))), array('intAuthID' => $sessionData['intAuthID']));
    // Redirect user back to where they came from.
    header('Location: ' . $sessionData['txtRedir']);
//echo "2";
} else {
    header('Location: /sso_auth.php');
//echo "1";
}
?>
