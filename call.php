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
    $key = get_key();
    if($key !== false) {
        $access_token = get_access_token($key);
        if($access_token === false) { // request access
?>
            <p> <a href="<?php echo $url_oauth; ?>">
                Bitte Microsoft-Konto neu verbinden für Abruf der Kontaktliste!
            </a> </p>
<?php
        }
    }
?>
            <form method="POST" onsubmit="submit(this)">
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
    if($key !== false && $access_token !== false) {
        query_contacts($access_token, function($json, $last_page) {    
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
?>
                        <option value="<?php echo $number; ?>">
                            <?php echo $name . ' (' . $label . ') - ' . preg_replace("/[^+\d]/", "", $number); ?>
                        </option>
<?php
                        }
                    }
                }
            }
        });
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
            function submit(form) {
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