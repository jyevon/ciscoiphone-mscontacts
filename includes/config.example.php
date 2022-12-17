<?php
    // --- Microsoft Azure App ---
    const MSGRAPH_CLIENT_ID = "11111111-1111-1111-1111-111111111111";
    const MSGRAPH_CLIENT_SECRET = "example_secret~nQBqc7Dkz1LJc7agb0spwK";
    // Don't forget to add redirect uri(s) at the Azure Portal
    
    // --- Cisco IP Phones ---
    // used by authenticate.php & call.php
    const PHONES = array(
        array(
            "label" => "Example Phone 1",
            "devicename" => "SEPXXXXXXXXXXXX",
            "host" => "192.168.0.41",
            "username" => "alice",
            "password" => "3x4mpl3_p45w0rd-1+-F2vF~"
        ),
        array(
            "label" => "Example Phone 2",
            "devicename" => "SEPYYYYYYYYYYYY",
            "host" => "SEPYYYYYYYYYYYY.bobsdnssuffix.net",
            "username" => "bob",
            "password" => "3x4mpl3_p45w0rd-2.WbD'5]"
        )
    );

    // --- fritzco ---
    const FRITZCO_URL = "https://example.com/fritzco";
    const FRITZCO_PHONEBOOKS = array(
        // open genkey.php in browser to get a random key
        "changeme868d490d4e99" => array(
            "bookid" => "0"
            // 'telefonbuch' number from fritzco's directory.config.inc.php
            // (https://github.com/SkyhawkXava/fritzco/blob/master/config/directory.config.inc.php)
        ),
        "random20charhex23159" => array(
            "url" => "https://another.example.com/fritzco", // if different than FRITZCO_URL
            "bookid" => "240"
            // phone books synced with your FRITZ!Box from an external source usually start at 240
        )
    );

    // --- Misc ---
    const CALL_DEFAULT_KEY = ""; // Key(s) of addressbooks loaded when none provided in URL
    const DEBUG = false;
?>