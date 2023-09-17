<?php
    // Prevent directory from being browsable on web servers disregarding .htaccess
    http_response_code(403);
    echo "Forbidden";
?>