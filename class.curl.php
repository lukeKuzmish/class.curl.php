<?php

class Curl {

    // private properties
    private $_ch            =   null;   // the meat 'n' potatoes
    private $debug          =   false;  // sets curl to verbose
    private $retries        =   3;      // number of attempts to retry a URL
    

    // public properties
    public $url             =   null;
    public $cookiesFile     =   null;
    public $userAgent       =   "PHPCurlWrapper v0.3";


    // public methods
    public function __construct($url = null, $cookiesLoc = null) {

        $this->_ch = curl_init();
        
        if ($url) {
            $this->url = $url;
            curl_setopt($this->_ch, CURLOPT_URL, $url);
        }
        
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $this->userAgent);

        $this->setDebug(false);        

        if ($cookiesLoc != null) {
            $this->cookiesFile = $cookiesLoc;
            curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $this->cookiesFile);
            curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $this->cookiesFile);
        }

    } // __construct


    public function setDebug($newval = true) {
      
        $this->debug = $newval;
        if ($this->debug) {
            curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
        }
        else {
            curl_setopt($this->_ch, CURLOPT_VERBOSE, false);
        }

    } // setDebug

    
    public function setUserAgent($userAgent = "") {
      
        $this->userAgent = $userAgent;
    
    } // setUserAgent
    
    
    public function getCH() {
      
        return $this->_ch;
        
    } // getCH
    
    
    // XmlHttpRequest methods
    public function getXHR($url = null) {
    
        $this->setXHR();
        return $this->getRequest($url);

    } // getXHR
    
    
    public function postXHR($payload, $url = null) {
    
        $this->setXHR();
        return $this->postRequest($payload, $url);
        
    } // postXHR
    
    
    public function getRequest($url = null) {
    
        curl_setopt($this->_ch, CURLOPT_HTTPGET, true);
        return $this->exec($url);

    } // getRequest

    
    public function getDOMDocument($url = null) {
    
      // decodes HTML as utf-8
        $html = $this->getRequest($url);
        if (!$html) {
            // might have returned false
            return false;
        }
        
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        return $dom;
        
    } // getDOMDocument

    
    public function postRequest($payload, $url = null) {
    
        if (is_array($payload)) {
          $payload = http_build_query($payload, '', '&');
        }
        
        curl_setopt($this->_ch, CURLOPT_POST, true);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->_ch, CURLOPT_POSTREDIR, 2);
        
        if ($this->debug) {
            echo "http query: $payload\n";
        }        
        
        return $this->exec($url);

    }    
    
    
    // private methods
    private function setXHR() {
      
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));
    
    }
    
    
    private function exec($url = null) {
    
        // if neither URL is set, return false
        if (!$url AND !$this->url) {
            return false;
        }
        
        // if user has passed in URL, update the object's property
        if ($url != null) {
            $this->url = $url;
        }
        
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->_ch, CURLOPT_URL, $this->url);
        
        // retry until HTTP status is 200 or we've met $Curl->retries
        $attempts = 1;
        do {
            $result = curl_exec($this->_ch);
        
            $statusCode = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
        } while (($attempts++ < $this->retries) and ($statusCode != 200));

        // if there is a redirect, update object's url property 
        $this->url = curl_getinfo($this->_ch, CURLINFO_EFFECTIVE_URL);
        
        
        return $result;

    }
    

} // Curl
