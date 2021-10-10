<CiscoIPPhoneMenu>
    <Title>Outlook-Kontakte</Title>
<?php
    require_once(__DIR__ . "/shared.php");
    
    if($storage->get("refresh_token") === false) { // request access
?>
    <MenuItem>
		<Name>BITTE NEU AUTHENTIFIZIEREN</Name>
		<URL></URL>
	</MenuItem>
<?php
    }else{
        if($storage->get("access_token_expiry") <= time() || $storage->get("access_token") === false) {
            $response = send_post("https://login.microsoftonline.com/consumers/oauth2/v2.0/token", array(
                'client_id' => CLIENT_ID,
                'scope' => $scope,
                'refresh_token' => $storage->get("refresh_token"),
                'redirect_uri' => $redirect_uri,
                'grant_type' => "refresh_token",
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
        }

        $query = "https://graph.microsoft.com/v1.0/me/contacts";
        $value = array();
        while($query !== null) {
            $response = send_get($query, "Authorization: Bearer " . $storage->get("access_token"));
    
            if(isset($_GET['debug'])) {
                var_dump($response[0]);
            }
            $json = json_decode($response[1]);
            if(isset($_GET['debug'])) {
                var_dump($json);
            }
    
            $j = 0;
            foreach($json->value as $contact) {
                
                $numbers = array();
                
                $name = $contact->displayName;
                if($name === "") {
                    $name = $contact->companyName;
                }
                
                if(count($contact->homePhones) == 1) {
                    $numbers[$name . ' (privat)'] = $contact->homePhones[0];
                }else{
                    for($i = 0; $i < count($contact->homePhones); $i++) {
                        $numbers[$name . ' (privat ' . ($i + 1) . ')'] = $contact->homePhones[$i];
                    }
                }
                
                if(count($contact->businessPhones) == 1) {
                    $numbers[$name . ' (geschäftlich)'] = $contact->businessPhones[0];
                }else{
                    for($i = 0; $i < count($contact->businessPhones); $i++) {
                        $numbers[$name . ' (geschäftlich ' . ($i + 1) . ')'] = $contact->businessPhones[$i];
                    }
                }

                if($contact->mobilePhone !== null) {
                    $numbers[$name . ' (Mobil)'] = $contact->mobilePhone;
                }
    
                foreach ($numbers as $k => $v) {
?>
    <MenuItem>
        <Name><?php echo $k; ?></Name>
        <URL>Dial:<?php echo $v; ?></URL>
    </MenuItem>
<?php
                }

                $j++;
                if($j >= 5/*6*/) { // DEBUG: TEXT MAX SIZE PHONEBOOK - 160 entries max? 7821 - max between 92-160
                    break;
                }
            }
    
            @$query = $json->{'@odata.nextLink'};
        }
    }// IMPLEMENT MULTIPLE LOOKUPS BY INTRODUCING USER-ID
?>
</CiscoIPPhoneMenu>