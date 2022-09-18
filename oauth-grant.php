<?php
    require(__DIR__ . "/includes/shared.php");

    // https://learn.microsoft.com/en-us/graph/auth-v2-user#2-get-authorization
    
    if(isset($_POST['code']) && isset($_POST['state'])) { // access granted, request token
        $response = send_post("https://login.microsoftonline.com/consumers/oauth2/v2.0/token", array(
            'client_id' => MSGRAPH_CLIENT_ID,
            'scope' => MSGRAPH_SCOPE,
            'code' => $_POST['code'],
            'redirect_uri' => current_url(),
            'grant_type' => "authorization_code",
            'client_secret' => MSGRAPH_CLIENT_SECRET)
        );

        if(DEBUG)  var_dump($response[0]);
        $response = json_decode($response[1]);
        if(DEBUG)  var_dump($response);

        if(!isset($_GET['key']) || preg_match("/[\da-f]{20,}/", $_GET['key']) !== 1) {
            // generate new key
            $_GET['key'] = substr(sha1(microtime() . random_int(0, 1000)), 0, 20);
        }

        $storage->set($_GET['key'] . "_access_token", $response->access_token);
        $storage->set($_GET['key'] . "_access_token_expiry", $response->expires_in + time());
        $storage->set($_GET['key'] . "_refresh_token", $response->refresh_token);

        header("Location: " . pageroot(true) . "?key=" . $_GET['key'] . "&state=authorized");

    }else{ // request access
        header(
            "Location: https://login.microsoftonline.com/consumers/oauth2/v2.0/authorize"
            . "?client_id=" . MSGRAPH_CLIENT_ID
            . "&response_type=code"
            . "&redirect_uri=" . urlencode(current_url())
            . "&response_mode=form_post"
            . "&scope=" . urlencode(MSGRAPH_SCOPE)
            . "&state=" . rand(1, 99999) // TODO check this in the response!
        );
    }
?>