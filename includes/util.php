<?php
    // ========= Storage =========
    class Storage {
        private $dir;
        private $hash;
        
        public function __construct($dir, $hash = true) {
            if(!is_dir($dir)) {
                mkdir($dir);
            }
            
            $this->dir = $dir;
            $this->hash = $hash;
        }

        private function file($key) {
            $file = $this->dir . ($this->hash ? sha1($key) : urlencode($key));

            return $file;
        }

        public function set($key, $value) {
            $key = $this->file($key);

            if(!file_exists($key)) {
                fclose(fopen($key, "w"));
            }
            
            return file_put_contents($key, serialize($value));
        }

        public function get($key) {
            $key = $this->file($key);

            if(file_exists($key)) {
                return unserialize(file_get_contents($key));
            }else{
                return false;
            }
        }

        public function remove($key) {
            return unlink(file($key));
        }
    }

    // ========= URLs =========
    /**
     * Checks whether the user is connected over ssl
     * 
     * @return bool
     */
    function is_ssl() { // stolen from wordpress
        if(isset( $_SERVER['HTTPS'])) {
            // Check https info
            if (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1') {
                return true;
            }
        }else if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            // Try to guess by port
            return true;
        }

        return false;
    }

    /**
     * Gets the path to the root directory (if this file is in it)
     * 
     * @param boolean	$absolute	Whether the path should be absolute, otherwise relative to the queried file
     * @param boolean	$force_ssl	Whether the protocol should be changed to ssl
     * 
     * @return string the path to the root directory
     */
    function pageroot(bool $absolute = false, bool $force_ssl = false) {
        // Subtract path of querried file from basedir
        $pageroot = substr(realpath($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']), strlen(realpath(__DIR__ . "/../")));

        // Count all seperators ('/' or '\')
        $slashes = substr_count(str_replace("\\", '/', $pageroot), '/');
        
        if($absolute) {
            // Absolute

            // Get current URL
            $pageroot = current_url($force_ssl);

            // Remove as many parts as are in difference
            for($i = 0; $i < $slashes; $i++) {
                $pageroot = substr($pageroot, 0, strrpos($pageroot, "/"));
            }

            return $pageroot . "/";
        }else{
            // Relative
            if($slashes != 1) {
                $pageroot = "";

                // Go up as many directories as parts are in difference
                for($i = 1; $i < $slashes; $i++) {
                    $pageroot .= "../";
                }
            }else{
                // Select the current directory
                $pageroot = "./";
            }

            return $pageroot;
        }
    }

    /**
     * Gets the queried url
     * 
     * @param bool	$force_ssl Whether the protocol should be replaced by https
     * 
     * @return string	the url
     */
    function current_url(bool $force_ssl = false) {
        $force_ssl = $force_ssl ? true : is_ssl();

        return ($force_ssl ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    // ========= HTTP requests =========
    function send_post(string $url, $data, string $header = "") {
        $options = array(
            'http' => array(
                'ignore_errors' => true,
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . $header,
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        return array($http_response_header, $result);
    }

    function send_get(string $url, string $header = "") {
        $options = array(
            'http' => array(
                'ignore_errors' => true,
                'header'  => $header,
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        return array($http_response_header, $result);
    }
?>