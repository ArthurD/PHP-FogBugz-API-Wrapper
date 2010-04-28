<?php
// Created by Arthur D'Antonio III   (http://www.dantonio.info)

Class curl {
	
	// Truncates the COOKIEFILE to 0 so that you're starting this with no cookies
	public function __construct() { 
		$truncate_cookie = fopen(COOKIEFILE, 'w+');
		fclose($truncate_cookie);
		$this->verbose = 0;
	}

	public function SendRequest($url, $var_array=null, $http_auth=false) { 
		// Init curl
		$ch = curl_init();
		
		//Set Misc. CURL Options
		curl_setopt($ch, CURLOPT_PORT, $port);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEFILE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEFILE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		//For HTTP Auth
		if($http_auth !== false) { 
			curl_setopt($ch, CURLOPT_USERPWD, $http_auth);
		}
				
		// For POST Requests
		if($var_array != null) {
			$varstring = self::postVar2String($var_array);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $varstring);
		}
		
		//Send Request and save $page as the result
		$page = curl_exec($ch);
		curl_close($ch);
		
		//Return the $page string
		return $page;
	}

	public function postVar2String($var_array, $encode_keys = true) {
		if(!is_array($var_array)) { 
			return '';
		}
		foreach($var_array as $key=>$value) {
	 		if($key && $value) {
	 			if($encode_keys === true) { 
	  				$varstring .= urlencode($key) . "=" . urlencode($value) . "&";
	 			} else { 
	 				$varstring .= $key . "=" . urlencode($value) . "&";
	 			}
	  		}
	  	}
	  	return $varstring;
	}
	
	private function __destruct() { 
	    // Remove CookieFile
	    unlink(COOKIEFILE);
	}
}
?>