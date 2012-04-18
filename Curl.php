<?php 

class Curl extends EventDispatcher
{
    protected $resource;
    protected $options;
    
    
    public function __construct($options = array())
    {
        if (!extension_loaded('curl')) {
            throw new Exception(_('cURL extension is not available on your server'));
        }
        
        $this->options = $options;
        
        $this->resource = curl_init();
    }
    
    public function getUrl($url, $pos_params = false, $proxy = false, $proxy_pswd = false, $isIgnoreErrors = false) 
    {
        
        $this->options[CURLOPT_URL] = $url;
        
        if($proxy) {
            $this->options[CURLOPT_PROXY] = $proxy;
        }
        
        if($proxy_pswd) {
            $this->options[CURLOPT_PROXYUSERPWD] = $proxy_pswd;
        }
        
        if($pos_params) {
            $this->options[CURLOPT_POST] = true;
            $this->options[CURLOPT_POSTFIELDS] = is_array($pos_params) ?  join("&", $pos_params) : $pos_params;
        }
        
        curl_setopt_array($this->resource, $this->options);
        
        $res = curl_exec($this->resource);
        $code = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);
        
        if($code != 200 && !$isIgnoreErrors) {
            return false;
        }
    
        return  $res;
    } // end getUrl
    
}
?>