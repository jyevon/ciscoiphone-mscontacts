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

    // --- Misc ---
    const DEBUG = false;
?>