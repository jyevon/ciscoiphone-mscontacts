<?php
    require(__DIR__ . "/../includes/shared.php");
    
    foreach(PHONES as $phone) {
        if($phone["devicename"] == @$_GET['devicename']) {
            if($phone["username"] == $_GET['UserID'] && $phone["password"] == $_GET['Password']) {
                die("AUTHORIZED");
            }

            break;
        }
    }

    echo "UNAUTHORIZED";
?>