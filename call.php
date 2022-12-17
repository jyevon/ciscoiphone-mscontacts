<?php
    require(__DIR__ . "/includes/shared.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf8"/>
        <title>Cisco IP Phone Anruf</title>
        <link rel="stylesheet" href="style.css">
        <style>
            div#page_container {
                width: 500px;
            }
            
            p#btns {
                display: flex;
                justify-content: space-between;
            }
        </style>
    </head>
    <body>
        <div id="page_container">
            <h1>Anruf starten</h1>
<?php
    $keys = array();
    if(empty($_GET['key']))  $_GET['key'] = CALL_DEFAULT_KEY;
    if(!empty($_GET['key'])) {
        $keys = explode(",", $_GET['key']);
        foreach ($keys as $key) {
            if(array_key_exists($key, FRITZCO_PHONEBOOKS))  continue;

            $access_token = get_access_token($key);
            if($access_token === false) { // request access
?>
            <p> <a href="<?php echo $url_oauth; ?>">
                Bitte Microsoft-Konto neu verbinden für Abruf der Kontaktliste!
            </a> </p>
<?php
            }
        }
    }
?>
            <form method="POST" onsubmit="submitXML(this)">
                <p>
                    <label for="target">Telefon:</label><br/>
                    <select id="target" required autofocus >
                        <option selected disabled></option>
<?php
    foreach(PHONES as $phone) {
        $host = $phone["host"] ?? $phone["devicename"];
?>
                        <option value="<?php echo $host; ?>" <?php if(@$_GET['devicename'] == $phone["devicename"])  echo " selected";  ?>>
                            <?php echo $phone["label"]; ?> (<?php echo $phone["devicename"]; ?>)
                        </option>
<?php
    }

    $ssl = (!isset($_GET['ssl']) || $_GET['ssl'] == ""
        || filter_var($_GET['ssl'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false
    );
?>
                    </select>
                </p>
                <p>
                    <input id="https" type="checkbox"<?php if($ssl)  echo " checked"; ?> />
                    <label for="https">HTTPS verwenden?</label>
                </p>
                <p>
                    <label for="target">Telefonnummer / Suche in Kontaktliste:</label><br/>
                    <input id="tel" type="tel" required list="contacts" value="<?php echo @$_GET['num']; ?>" />
                    <datalist id="contacts">
<?php 
    $suggestions = array();
    
    foreach ($keys as $key) {
        if(array_key_exists($key, FRITZCO_PHONEBOOKS)) {
            if(!empty(FRITZCO_PHONEBOOKS[$key]["url"]))  $url = FRITZCO_PHONEBOOKS[$key]["url"];
            else  $url = FRITZCO_URL;

            $book = @file_get_contents($url . "/books/" . FRITZCO_PHONEBOOKS[$key]["bookid"] . ".xml");
            if(DEBUG)  var_dump($book);

            if($book === false)  continue;

            $xml = simplexml_load_string($book);
            if(DEBUG)  var_dump($xml);

            foreach($xml->phonebook->contact as $contact) {
                $name = $contact->person->realName;

                foreach($contact->telephony->number as $number) {
                    $num = preg_replace("/[^+*#\d]/", "", $number);
                    if(in_array($num, $suggestions))  continue;
                    
                    $label = $number->attributes()["type"];
                    if($label == "home")  $label = "Privat";
                    elseif($label == "work")  $label = "Geschäftlich";
                    elseif($label == "mobile")  $label = "Mobil";
                    elseif($label == "fax_work")  $label = "Fax Geschäftlich";

?>
                        <option value="<?php echo $number; ?>">
                            <?php echo $name . ' (' . $label . ') - ' . $num; ?>
                        </option>
<?php
                }
            }

            continue;
        }

        $access_token = get_access_token($key);
        if($access_token !== false) {
            query_contacts($access_token, function($json, $last_page) {    
                global $suggestions;

                if(isset($json->value)) { // multiple results / list
                    foreach($json->value as $contact) {
                        $numbers = array();

                        if(count($contact->homePhones) == 1) {
                            if(!empty($contact->homePhones[0]))  $numbers['Privat'] = $contact->homePhones[0];
                        }else{
                            for($i = 0; $i < count($contact->homePhones); $i++) {
                                if(empty($contact->homePhones[$i]))  continue;
                                $numbers['Privat ' . ($i + 1)] = $contact->homePhones[$i];
                            }
                        }
                        
                        if(count($contact->businessPhones) == 1) {
                            if(!empty($contact->businessPhones[0]))  $numbers['Geschäftlich'] = $contact->businessPhones[0];
                        }else{
                            for($i = 0; $i < count($contact->businessPhones); $i++) {
                                if(empty($contact->businessPhones[$i]))  continue;
                                $numbers['Geschäftlich ' . ($i + 1)] = $contact->businessPhones[$i];
                            }
                        }

                        if(!empty($contact->mobilePhone)) {
                            $numbers['Mobil'] = $contact->mobilePhone;
                        }
            
                        if(count($numbers) > 0) {
                            $name = $contact->displayName;
                            if($name === "")  $name = $contact->companyName;
                            
                            foreach($numbers as $label => $number) {
                                $num = preg_replace("/[^+*#\d]/", "", $number);
                                array_push($suggestions, $num);
?>
                        <option value="<?php echo $number; ?>">
                            <?php echo $name . ' (' . $label . ') - ' . $num; ?>
                        </option>
<?php
                            }
                        }
                    }
                }
            });
        }
    }
?>
                    </datalist>
                </p>
                <p id="btns">
                    <button type="submit">📞 Mit Telefon anrufen</button>
                    <button type="button" onclick="external()">🎧 Mit PC anrufen</button>
                </p>

                <input id="xml" name="XML" type="hidden" />
            </form>
        </div>

        <script type="text/javascript">
            function submitXML(form) {
                var target = document.getElementById("target").value;
                var https = document.getElementById("https").checked;
                var tel = document.getElementById("tel").value;

                if(tel != "") {
                    document.getElementById("xml").value = '<\?xml version="1.0" encoding="utf-8"?>\n'
                        + '<CiscoIPPhoneExecute>'
                        + '<ExecuteItem Priority="0" URL="Dial:' + tel + '"/>'
                        + '</CiscoIPPhoneExecute>';

                    form.action = (https ? "https://" : "http://") + target + "/CGI/Execute";
                }
            }

            function external() {
                var tel = document.getElementById("tel").value.replace(" ", "");
                window.location.replace("tel:" + tel);
            }
        </script>
    </body>
</html>