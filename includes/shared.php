<?php
    require(__DIR__ . "/config.php");
    require(__DIR__ . "/util.php");

    const MSGRAPH_SCOPE = "offline_access user.read contacts.read";

    $storage = new Storage(__DIR__ . "/../storage/", false);

    $url_oauth = pageroot(true, true) . "oauth-grant.php";
    $url_vcard = pageroot() . "vcard.php?key=";
    $url_call = pageroot() . "call.php";
    $url_cisco_dir = pageroot(true) . "cisco/directory.php?key=";
    $url_cisco_auth = pageroot(true) . "cisco/authenticate.php";
    

    function gen_key() {
        return substr(sha1(microtime() . random_int(0, 1000)), 0, 20);
    }    
    function get_key() {
        if(empty($_GET['key']))  return false;
        return $_GET['key'];
    }

    function get_access_token($key) {
        // https://learn.microsoft.com/en-us/graph/auth-v2-user#3-get-a-token

        global $storage;
        global $url_oauth;
        
        // check if user needs to (re-)authorize
        if($storage->get($key . "_refresh_token") === false)  return false;

        // renew or reqest access token if necessary        
        if($storage->get($key . "_access_token_expiry") <= time() || $storage->get($key . "_access_token") === false) {
            $response = send_post("https://login.microsoftonline.com/consumers/oauth2/v2.0/token", array(
                'client_id' => MSGRAPH_CLIENT_ID,
                'scope' => MSGRAPH_SCOPE,
                'refresh_token' => $storage->get($key . "_refresh_token"),
                'redirect_uri' => $url_oauth,
                'grant_type' => "refresh_token",
                'client_secret' => MSGRAPH_CLIENT_SECRET)
            );
    
            if(DEBUG)  var_dump($response[0]);
            $response = json_decode($response[1]);
            if(DEBUG)  var_dump($response);
    
            $storage->set($key . "_access_token", $response->access_token);
            $storage->set($key . "_access_token_expiry", $response->expires_in + time());
            $storage->set($key . "_refresh_token", $response->refresh_token);
        }

        // return access token
        return $storage->get($key . "_access_token");
    }

    function query_contacts($access_token, $foreach_resultpage) {
        // https://learn.microsoft.com/de-de/graph/api/user-list-contacts

        $query = "https://graph.microsoft.com/v1.0/me/contacts";
        if(isset($_GET['id'])) {
            $query .= '/' . urlencode($_GET['id']);
        }else if(isset($_GET['search'])){
            // BUG search with > 1 result pages throwing error on 2nd page:
            // "The following parameters are not supported with change tracking over the 'Contacts' resource: '$orderby, $filter, $select, $expand, $search, $top'."
            $query  .= '?$search="' . urlencode($_GET['search']) . '"';
        }else{
            $query .= '?$orderby=displayName%20asc';
        }

        while($query !== null) {
            $response = send_get($query, "Authorization: Bearer " . $access_token);
    
            if(DEBUG)  var_dump($response[0]);
            $json = json_decode($response[1]);
            if(DEBUG)  var_dump($json);
    
            @$query = $json->{'@odata.nextLink'};

            if($foreach_resultpage($json, ($query == null)) === false)  break;
        }
    }
?>