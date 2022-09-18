<?php
    require(__DIR__ . "/includes/shared.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf8"/>
        <title>Outlook-Kontakte</title>
        <link rel="stylesheet" href="style.css">
        <style>
            button#submit {
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div id="page_container">
            <h1>
                Integration Microsoft-Kontakte<br/>
                für Cisco IP-Phones
            </h1>
<?php
    $key = get_key();    
    if($key === false) {
?>
            <form method="GET">
                <p>
                    Zur Ansicht und Einbindung der Outlook-Kontakte wird ein Schlüssel benötigt.
                    Dieser gibt an, die Kontaktliste welches der verknüpften Microsoft-Konten genutzt werden soll.
                </p>
                <p>
                    Nach der Verknüpfung eines Microsoft-Kontos wird der Schlüssel generiert und angezeigt.
                    Wenn Sie Ihren Schlüssel vergessen haben, 
                    <a href="mailto:<?php echo $_SERVER['SERVER_ADMIN']; ?>">wenden Sie sich bitte an den Webmaster.</a>
                    Sie können auch
                    <a href="<?php echo $url_oauth; ?>">ein weiteres Microsoft-Konto verknüpfen.</a>
                </p>
                <p>
                    <label for="key">Schlüssel:</label><br/>
                    <input id="key" type="text" name="key" required minlength="20" />
                </p>
                <p>
                    <button id="submit" type="submit">Übersicht anzeigen</button>
                </p>
            </form>

            <br/>

            <h2>Ohne Schlüssel nutzbare Funktionen</h2>
            <ul>
                <li> <a href="<?php echo $url_call; ?>">Anruf auf Telefon starten (Zugangsdaten des Telefons benötigt)</a> </li>
            </ul>

            <h3>URL für SEP&lt;MAC&gt;.cnf.xml</h3>
            <table>
                <tr>
                    <th>authenticationURL</th>
                    <td><?php echo $url_cisco_auth ?></td>
                </tr>
            </table>
<?php
    }else if(get_access_token($key) === false){
?>
            Unter diesem Schlüssel wurde kein Konto gefunden oder es muss neu verknüpft werden. Das können Sie
            <a href="<?php echo $url_oauth . "?key=" . $key; ?>">hier</a>
            tun.
<?php
    }else{
        if(isset($_GET['state']) && $_GET['state'] == "authorized") {
?>
            <p>Microsoft-Konto erfolgreich verknüpft!</p>
<?php
        }
?>
            <h2>Per Browser nutzbare Funktionen</h2>
            <ul>
                <li> <a href="<?php echo $url_call . "?key=" . $key; ?>">Anruf auf Telefon starten (Zugangsdaten des Telefons benötigt)</a> </li>
                <li> <a href="<?php echo $url_vcard . $key; ?>">vCard-Export der Kontakte</a> </li>
                <li> <a href="<?php echo $url_oauth . "?key=" . $key; ?>">dieses Microsoft-Konto neu verknüpfen</a> </li>
                <li> <a href="<?php echo $url_oauth; ?>">ein weiteres Microsoft-Konto verknüpfen</a> </li>
            </ul>

            <br/>

            <h2>URLs für SEP&lt;MAC&gt;.cnf.xml</h2>
            <table>
                <!-- TODO use secure<...>URL instead? -->
                <tr>
                    <th>directoryURL</th>
                    <td><?php echo $url_cisco_dir . $key; ?></td>
                </tr>
                
                <tr>
                    <th>authenticationURL</th>
                    <td><?php echo $url_cisco_auth ?></td>
                </tr>
            </table>
<?php
    }
?>
        </div>
    </body>
</html>