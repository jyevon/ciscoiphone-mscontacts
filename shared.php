<?php
    require_once(__DIR__ . "/config.php");

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
            $file = $this->dir . ($this->hash ? md5($key) : str_replace('/', '_', $key));

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

    $storage = new Storage(__DIR__ . "/storage/", false);

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
    function current_url(bool $force_ssl = false) {
        $force_ssl = $force_ssl ? true : is_ssl();

        return ($force_ssl ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
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
    
    $redirect_uri = current_url();
    $query = strpos($redirect_uri, "?");
    if($query !== false) {
        $redirect_uri = substr($redirect_uri, 0, $query);
    }
    $scope = "offline_access user.read contacts.read";

    // https://docs.microsoft.com/en-us/graph/auth-v2-user
?>