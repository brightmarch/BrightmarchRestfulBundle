<?php

namespace Brightmarch\Bundle\RestfulBundle\Utility;

class CurlClient {
    
    public $response = "";
    public $is_connected = false;
    public $conn = null;
    
    public $headers = array();
    
    public function __destruct() {
        if ($this->is_connected) {
            $this->close();
        }
    }
    
    public function connect($url) {
        $this->conn = curl_init($url);
        $this->is_connected = true;
        
        $this->setOption(CURLOPT_RETURNTRANSFER, 1)
            ->setOption(CURLOPT_HEADER, 0);
        return($this);
    }
    
    public function close() {
        if ($this->is_connected) {
            curl_close($this->conn);
            $this->is_connected = false;
        }
        return($this);
    }
    
    public function setOption($name, $value) {
        if ($this->is_connected) {
            curl_setopt($this->conn, $name, $value);
        }
        return($this);
    }
    
    public function setOptions($options) {
        if ($this->is_connected) {
            if (is_array($options) && count($options) > 0) {
                return(curl_setopt_array($this->conn, $options));
            }
        }
        return($this);
    }
    
    public function addHeader($header) {
        $this->headers[] = $header;
        return($this->setOption(CURLOPT_HTTPHEADER, $this->headers));
    }
    
    // The beginning of our "DSL" (Really, it's a DSA - domain specific API)
    public function accepting($accept) {
        $header = sprintf("Accept: %s", $accept);
        return($this->addHeader($header));
    }
    
    public function authorizedBy($username, $password) {
        if (!empty($username) && !empty($password)) {
            $this->setOption(CURLOPT_USERPWD, sprintf("%s:%s", $username, $password));
        }
        return($this);
    }
    
    public function parameters(array $parameters) {
        if (count($parameters) > 0) {
            $this->setOption(CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        return($this);
    }
    
    public function referedBy($referer) {
        return($this->setOption(CURLOPT_REFERER, $referer));
    }

    public function using($user_agent) {
        return($this->setOption(CURLOPT_USERAGENT, $user_agent));
    }

    public function with($method) {
        return($this->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method)));
    }
    // The end of our "DSL" (DSA)
    
    // Handling responses
    public function send() {
        if ($this->is_connected) {
            $this->response = curl_exec($this->conn);
            return(true);
        }
        return(false);
    }
    
    public function response() {
        return($this->response);
    }
    
    public function statusCode() {
        if ($this->is_connected) {
            return((int)curl_getinfo($this->conn, CURLINFO_HTTP_CODE));
        }
        return(-1);
    }

    public function redirect() {
        if ($this->is_connected) {
            $info = curl_getinfo($this->conn);
            if (array_key_exists('redirect_url', $info)) {
                return($info['redirect_url']);
            }
        }
        return('');
    }
    
    public function error() {
        if ($this->is_connected) {
            return(curl_error($this->conn));
        }
        return(false);
    }
    
    // Helper methods
    public function isConnected() {
        return($this->is_connected);
    }
    
    public function getConnection() {
        return($this->conn);
    }

}
