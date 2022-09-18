<?php
    require(__DIR__ . "/../includes/shared.php");
    
    header("Content-type: text/xml; charset=utf-8");
?>
<?xml version="1.0" encoding="utf-8"?>
<?php
    
    $key = get_key();
    if($key === false) { // request key
?>
<CiscoIPPhoneText>
    <Title>Outlook-Kontakte</Title>
    <Prompt>Bitte Schlüssel in URL angeben!</Prompt>
    <Text><?php echo pageroot(true); ?></Text>
</CiscoIPPhoneText>
<?php
        exit;
    }

    $access_token = get_access_token($key);
    if($access_token === false) { // request access
?>
<CiscoIPPhoneText>
    <Title>Outlook-Kontakte</Title>
    <Prompt>Bitte MS-Konto neu verbinden!</Prompt>
    <Text><?php echo $url_oauth . "?key=" . $key; ?></Text>
</CiscoIPPhoneText>
<?php

    }else{
        $new_url = htmlspecialchars(current_url());
        $new_url .= (strrpos($new_url, '?') !== false) ? '&amp;' : '?'; // BUG?? htmlspecialchars should never contain ?
        $new_url .=  "id=";

        $started = false;
        $results = 0;
        query_contacts($access_token, function($json, $last_page) {    
            global $new_url;
            global $started;
            global $results;

            if(isset($json->value)) { // multiple results / list
                if(!$started) {
                    $started = true;
?>
<CiscoIPPhoneMenu>
    <Title>Outlook-Kontakte</Title>
<?php
                }
                foreach($json->value as $contact) {
                    if(
                        (count($contact->homePhones) > 0 && !empty($contact->homePhones[0]))
                        || (count($contact->businessPhones) > 0 && !empty($contact->businessPhones[0]))
                        || !empty($contact->mobilePhone)
                    ) {
                        $name = $contact->displayName;
                        if($name === "")  $name = $contact->companyName;
?>
    <MenuItem>
        <Name><?php echo htmlspecialchars($name); ?></Name>
        <URL><?php echo $new_url . urlencode($contact->id); ?></URL>
    </MenuItem>
<?php
                        // "IP phones allow a maximum of 100 MenuItems"
                        // TODO IMPLEMENT MULTIPLE RESULT PAGES NAVIGATED USING SOFTKEYS
                        $results++;                        
                        if($results == 100) {
                            $last_page = true;
                            break;
                        }
                    }
                }

                if($last_page) {
?>
</CiscoIPPhoneMenu>
<?php
                    return false; // don't fetch next result page(s) (if any)
                }
            }else{ // single result
                $name = $json->displayName;
                if($name === "")  $name = $json->companyName;

                if(!$started) {
                    $started = true;
?>
<CiscoIPPhoneDirectory>
    <Title>Outlook-Kontakt</Title>
    <Prompt><?php echo htmlspecialchars(substr($name, 0, 33/* =max length*/)); ?></Prompt>
<?php
                }

                $numbers = array();
                
                if(count($json->homePhones) == 1) {
                    if(!empty($json->homePhones[0]))  $numbers['Privat'] = $json->homePhones[0];
                }else{
                    for($i = 0; $i < count($json->homePhones); $i++) {
                        if(empty($json->homePhones[$i]))  continue;
                        $numbers['Privat ' . ($i + 1)] = $json->homePhones[$i];
                    }
                }
                
                if(count($json->businessPhones) == 1) {
                    if(!empty($json->businessPhones[0]))  $numbers['Geschäftlich'] = $json->businessPhones[0];
                }else{
                    for($i = 0; $i < count($json->businessPhones); $i++) {
                        if(empty($json->businessPhones[$i]))  continue;
                        $numbers['Geschäftlich ' . ($i + 1)] = $json->businessPhones[$i];
                    }
                }

                if(!empty($json->mobilePhone)) {
                    $numbers['Mobil'] = $json->mobilePhone;
                }
    
                foreach ($numbers as $label => $number) {
?>
    <DirectoryEntry>
        <Name><?php echo $label; ?></Name>
        <Telephone><?php echo $number; ?></Telephone>
    </DirectoryEntry>
<?php
                }

                if($last_page) {
?>
</CiscoIPPhoneDirectory>
<?php
                }
            }
        });
    }
?>