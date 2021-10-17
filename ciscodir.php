<?php
    require_once(__DIR__ . "/shared.php");
    
    header("Content-type: text/xml; charset=utf-8");
    
    if($storage->get("refresh_token") === false) { // request access
?>
<CiscoIPPhoneText>
    <Title>Outlook-Kontakte</Title>
    <Prompt>Bitte an PC neu authentifizieren unter</Prompt>
    <Text><?php echo str_replace("\\", '/', "https://" . $_SERVER['HTTP_HOST'] . substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))); ?></Text>
</CiscoIPPhoneText>
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
        if(isset($_GET['id'])) {
            $query .= '/' . $_GET['id'];
        }
        
        $new_url = htmlspecialchars(current_url());
        $new_url .= (strrpos($new_url, '?') !== false) ? '&amp;' : '?';

        $value = array();
        $started = false;
        $results = 0;
        while($query !== null) {
            $response = send_get($query, "Authorization: Bearer " . $storage->get("access_token"));
    
            if(isset($_GET['debug'])) {
                var_dump($response[0]);
            }
            $json = json_decode($response[1]);
            if(isset($_GET['debug'])) {
                var_dump($json);
            }
    
            @$query = $json->{'@odata.nextLink'};
    
            if(isset($json->value)) { // multiple results / list
                if(!$started) {
                    $started = true;
?>
<CiscoIPPhoneMenu>
    <Title>Outlook-Kontakte</Title>
<?php
                }
                foreach($json->value as $contact) {
                    $numbers = array();
                    
                    $name = $contact->displayName; // SORT ALPHABETICALLY?
                    if($name === "") {
                        $name = $contact->companyName;
                    }
                    
                    if(count($contact->homePhones) > 0 || count($contact->businessPhones) > 0 || $contact->mobilePhone !== null) {
?>
    <MenuItem>
        <Name><?php echo htmlspecialchars($name); ?></Name>
        <URL><?php echo $new_url . "id=" . urlencode($contact->id); ?></URL>
    </MenuItem>
<?php
                        $results++;
                        
                        if($results == 100) { // "IP phones allow a maximum of 100 MenuItems" - IMPLEMENT MULTIPLE RESULT PAGES NAVIGATED USING SOFTKEYS
                            $query = null;
                            break;
                        }
                    }
                }

                if($query == null) {
?>
</CiscoIPPhoneMenu>
<?php
                }
            }else{ // single result
                $numbers = array();
                
                $name = $json->displayName;
                if($name === "") {
                    $name = $json->companyName;
                }

                if(!$started) {
                    $started = true;
?>
<CiscoIPPhoneDirectory>
    <Title>Outlook-Kontakt</Title>
    <Prompt><?php echo htmlspecialchars(substr($name, 0, 33/* =max length*/)); ?></Prompt>
<?php
                }
                
                if(count($json->homePhones) == 1) {
                    $numbers['Privat'] = $json->homePhones[0];
                }else{
                    for($i = 0; $i < count($json->homePhones); $i++) {
                        $numbers['Privat ' . ($i + 1)] = $json->homePhones[$i];
                    }
                }
                
                if(count($json->businessPhones) == 1) {
                    $numbers['Geschäftlich'] = $json->businessPhones[0];
                }else{
                    for($i = 0; $i < count($json->businessPhones); $i++) {
                        $numbers['Geschäftlich ' . ($i + 1)] = $json->businessPhones[$i];
                    }
                }

                if($json->mobilePhone !== null) {
                    $numbers['Mobil'] = $json->mobilePhone;
                }
    
                foreach ($numbers as $k => $v) {
?>
    <DirectoryEntry>
		<Name><?php echo $k; ?></Name>
		<Telephone><?php echo $v; ?></Telephone>
	</DirectoryEntry>
<?php
                }

                if($query == null) {
?>
</CiscoIPPhoneDirectory>
<?php
                }
            }
        }
    }// IMPLEMENT MULTIPLE LOOKUPS BY INTRODUCING USER-ID
?>