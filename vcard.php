<?php
    require(__DIR__ . "/includes/shared.php");

    $key = get_key();
    if($key === false) { // request key
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf8"/>
        <title>Outlook-Kontakte</title>
    </head>
    <body>
        <a href="<?php echo pageroot(true); ?>">
            Bitte Schlüssel in URL angeben! Auf der Übersicht kann die korekte URL nachgelesen werden.
        </a>
    </body>
</html>
<?php

    }

    $access_token = get_access_token($key);
    if($access_token === false) { // request access
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf8"/>
        <title>Outlook-Kontakte</title>
    </head>
    <body>
        <a href="<?php echo $url_oauth . "?key=" . $key; ?>">
            Bitte Microsoft-Konto neu verbinden!
        </a>
    </body>
</html>
<?php
    }else{
        header("Content-type: text/x-vcard; charset=utf-8");
        header("Content-Disposition: attachment; filename=outlook.vcf");
        
        query_contacts($access_token, function($json, $last_page) {
            foreach((isset($json->value) ? $json->value : array($json)) as $contact) {
                echo "BEGIN:VCARD\n";
                echo "VERSION:3.0\n";

                echo "N:$contact->surname;$contact->givenName;$contact->middleName;$contact->title;$contact->generation\n";

                $name = $contact->displayName;
                if($name === "")  $name = $contact->companyName;
                echo "FN:$name\n";

                if(!empty($contact->nickName) && $contact->nickName !== " ") {
                    echo "NICKNAME:$contact->nickName\n";
                }

                if(!empty($contact->jobTitle) && $contact->jobTitle !== " ") {
                    echo "TITLE:$contact->jobTitle\n";
                }

                if((!empty($contact->companyName) && $contact->companyName !== " ")
                 || (!empty($contact->department) && $contact->department !== " ")) {
                    
                    echo "ORG:$contact->companyName;$contact->department\n";
                }

                if(!empty($contact->profession) && $contact->profession !== " ") {
                    echo "ROLE:$contact->profession\n";
                    echo "X-KADDRESSBOOK-X-Profession:$contact->profession\n";
                }

                if(!empty($contact->officeLocation) && $contact->officeLocation !== " ") {
                    echo "X-KADDRESSBOOK-X-Office:$contact->officeLocation\n";
                }

                if(!empty($contact->assistantName) && $contact->assistantName !== " ") {
                    // TODO FORMAT EXCHANGE NECESSARY?
                    echo "AGENT:$contact->assistantName\n";
                    echo "X-ASSISTANT:$contact->assistantName\n";
                    echo "X-EVOLUTION-ASSISTANT:$contact->assistantName\n";
                    echo "X-KADDRESSBOOK-X-AssistantsName:$contact->assistantName\n";
                }

                if(!empty($contact->manager) && $contact->manager !== " ") {
                    echo "X-MANAGER:$contact->manager\n";
                    echo "X-EVOLUTION-MANAGER:$contact->manager\n";
                    echo "X-KADDRESSBOOK-X-ManagersName:$contact->manager\n";
                }

                if(!empty($contact->spouseName) && $contact->spouseName !== " ") {
                    echo "X-SPOUSE:$contact->spouseName\n";
                    echo "X-EVOLUTION-SPOUSE:$contact->spouseName\n";
                    echo "X-KADDRESSBOOK-X-SpouseName:$contact->spouseName\n";
                }

                // TODO $contact->children

                foreach($contact->homePhones as $phone) {
                    echo "TEL;TYPE=VOICE,HOME:$phone\n";
                }
                if(!empty($contact->mobilePhone) && $contact->mobilePhone !== " ") {
                    echo "TEL;TYPE=VOICE,CELL:$contact->mobilePhone\n";
                }
                foreach($contact->businessPhones as $phone) {
                    echo "TEL;TYPE=VOICE,WORK:$phone\n";
                }

                if(count($contact->categories) > 0) {
                    echo "CATEGORIES:";

                    for($i = 0; $i < count($contact->categories); $i++) {
                        if($i != 0)  echo ',';
                        echo $contact->categories[$i];
                    }

                    echo "\n";
                }

                $primary_email = true;
                foreach($contact->emailAddresses as $email) {
                    if(!empty($email->address) && $email->address !== " ") {
                        echo "EMAIL;TYPE=";
                        if($primary_email) {
                            echo "PREF,";
                            $primary_email = false;
                        }
                        echo "INTERNET:" . $email->address . "\n";
                    }
                }

                foreach($contact->imAddresses as $impp) {
                    // TODO FORMAT CONVERSION NECESSARY?
                    if(!empty($impp))  echo "IMPP:$impp\n";
                }

                if(!empty($contact->businessHomePage) && $contact->businessHomePage !== " ") {
                    echo "URL;TYPE=WORK:$contact->businessHomePage\n";
                }

                if(
                    property_exists($contact->homeAddress, "city")
                    || property_exists($contact->homeAddress, "state")
                    || property_exists($contact->homeAddress, "postalCode")
                    || property_exists($contact->homeAddress, "countryOrRegion")
                ) {
                    echo "ADR;TYPE=HOME:;;"; // 1st part = post box

                    $i = @strrpos($contact->homeAddress->street, "\n");
                    if($i !== false) {
                        echo @substr($contact->homeAddress->street, $i + 1) . ';';
                    }else{
                        echo @$contact->homeAddress->street . ';';
                    }

                    echo @$contact->homeAddress->city . ';';
                    echo @$contact->homeAddress->state . ';';
                    echo @$contact->homeAddress->postalCode . ';';
                    echo @$contact->homeAddress->countryOrRegion . "\n";
                }

                if(
                    property_exists($contact->businessAddress, "city")
                    || property_exists($contact->businessAddress, "state")
                    || property_exists($contact->businessAddress, "postalCode")
                    || property_exists($contact->businessAddress, "countryOrRegion")
                    || !empty($contact->officeLocation)
                ) {
                    echo "ADR;TYPE=WORK:;"; // 1st part = post box
                    echo $contact->officeLocation . ';';

                    $i = @strrpos($contact->businessAddress->street, "\n");
                    if($i !== false) {
                        echo @substr($contact->businessAddress->street, $i + 1) . ';';
                    }else{
                        echo @$contact->businessAddress->street . ';';
                    }

                    echo @$contact->businessAddress->city . ';';
                    echo @$contact->businessAddress->state . ';';
                    echo @$contact->businessAddress->postalCode . ';';
                    echo @$contact->businessAddress->countryOrRegion . "\n";
                }

                if(
                    property_exists($contact->otherAddress, "city")
                    || property_exists($contact->otherAddress, "state")
                    || property_exists($contact->otherAddress, "postalCode")
                    || property_exists($contact->otherAddress, "countryOrRegion")
                ) {  
                    echo "ADR:;;"; // 1st part = post box

                    $i = @strrpos($contact->otherAddress->street, "\n");
                    if($i !== false) {
                        echo @substr($contact->otherAddress->street, $i + 1) . ';';
                    }else{
                        echo @$contact->otherAddress->street . ';';
                    }

                    echo @$contact->otherAddress->city . ';';
                    echo @$contact->otherAddress->state . ';';
                    echo @$contact->otherAddress->postalCode . ';';
                    echo @$contact->otherAddress->countryOrRegion . "\n";
                }

                if(!empty($contact->birthday)) {
                    echo "BDAY:" . substr($contact->birthday, 0, strrpos($contact->birthday, 'T')) . "\n";
                }

                if(!empty($contact->personalNotes) && $contact->personalNotes !== " ") {
                    echo "NOTE:" . str_replace("\n", "\\n", str_replace("\r", "", $contact->personalNotes)) . "\n";
                }

                echo "REV:$contact->lastModifiedDateTime\n";
                echo "END:VCARD\n";
            }
        });
    }
?>