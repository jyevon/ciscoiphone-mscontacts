<?php
    require(__DIR__ . "/includes/shared.php");
    
    header("Content-Type: text/plain; charset=utf-8");
    echo gen_key();
?>