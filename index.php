<?php
    require_once(__DIR__ . "/shared.php");
    
    if(isset($_GET['code']) && isset($_GET['state'])) { // access granted, request token
        $response = send_post("https://login.microsoftonline.com/consumers/oauth2/v2.0/token", array(
            'client_id' => CLIENT_ID,
            'scope' => $scope,
            'code' => $_GET['code'],
            'redirect_uri' => $redirect_uri,
            'grant_type' => "authorization_code",
            'client_secret' => CLIENT_SECRET));

        if(isset($_GET['debug'])) {
            var_dump($response[0]);
        }
        $response = json_decode($response[1]);
        if(isset($_GET['debug'])) {
            var_dump($response);
        }

        $storage->set("access_token", $response->access_token);
        $storage->set("access_token_expiry", $response->expires_in + time());
        $storage->set("refresh_token", $response->refresh_token);

        echo "Vielen Dank!";
    }else{ // request access
        header("Location: https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize"
         . "?client_id=" . CLIENT_ID
         . "&response_type=code"
         . "&redirect_uri=" . urlencode($redirect_uri)
         . "&response_mode=query" // USE POST INSTEAD?
         . "&scope=" . urlencode($scope)
         . "&state=" . rand(1, 99999));
    }
?>