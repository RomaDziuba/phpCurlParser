<?php

class Curl extends EventDispatcher
{
    protected $resource;
    protected $options;
    protected $lists;
    protected $lastIndex = 0;
    protected $master;


    public function __construct($options = array())
    {
        if (!extension_loaded('curl')) {
            throw new Exception(_('cURL extension is not available on your server'));
        }

        $this->options = $options;

    }

    public function getUrl($url, $pos_params = false, $proxy = false, $proxy_pswd = false, $isIgnoreErrors = false)
    {
    	$this->resource = curl_init();

        $this->options[CURLOPT_URL] = $url;

        if ($proxy) {

        	if (is_array($proxy)) {
        		$this->options[CURLOPT_PROXYTYPE] = $proxy[0];
        		$this->options[CURLOPT_PROXY] = $proxy[1];
        	} else {
        		$this->options[CURLOPT_PROXY] = $proxy;
        	}
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

    public function addUrl($url, $externalOptions = array(), $pos_params = false, $proxy = false, $proxy_pswd = false, $isIgnoreErrors = false)
    {
    	$index = $this->lastIndex;

    	$resource = &$this->resource[$this->lastIndex];
    	$resource = curl_init();

    	$options = $this->options;
    	foreach ($externalOptions as $key => $value) {
    		$options[$key] = $value;
    	}

    	$options[CURLOPT_URL] = $url;

    	if ($proxy) {
    		if (is_array($proxy)) {
    			$options[CURLOPT_PROXYTYPE] = $proxy[0];
    			$options[CURLOPT_PROXY] = $proxy[1];
    		} else {
    			$options[CURLOPT_PROXY] = $proxy;
    		}
    	}

    	if($proxy_pswd) {
    		$options[CURLOPT_PROXYUSERPWD] = $proxy_pswd;
    	}

    	if($pos_params) {
    		$options[CURLOPT_POST] = true;
    		$options[CURLOPT_POSTFIELDS] = is_array($pos_params) ?  join("&", $pos_params) : $pos_params;
    	}

    	curl_setopt_array($resource, $options);

    	$this->lastIndex++;

    	return $index;
    } // end addUrl

    public function start()
    {
    	$this->master = curl_multi_init();

    	foreach ($this->resource as $index => $resource) {
    		curl_multi_add_handle($this->master, $resource);
    	}

    	do {
    		//usleep(10000);
    		$mrc = curl_multi_exec($this->master, $running);
    	} while ($running > 0);

    	$result = array();

    	foreach ($this->resource as $index => $resource) {
    		$result[$index] = curl_multi_getcontent($resource);
    	}

    	foreach ($this->resource as $index => $resource) {
    		curl_multi_remove_handle($this->master, $resource);
    	}

    	curl_multi_close($this->master);

    	return $result;
    }


}
?>