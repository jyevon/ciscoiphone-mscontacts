<?php
    // Prevent directory from beeing browsable on webservers disregarding .htaccess
    http_response_code(403);
    echo "Forbidden";
?>