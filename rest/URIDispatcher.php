<?php

/**
* Class URIDispatcher
*
* Class to map requests URIs to function callbacks.
*
* This class is a singleton and the expected use is to:
* 1. Get the instance and call to map(...) method assign callback
* functions to specific url patterns and http method.
* 2. Call the dispatchRequest(...) method in order to process
* the current request by finding a mathing mapping previously provided.
*
* @author lipido <lipido@gmail.com>
*/
class URIDispatcher {

	// singleton
	private static $uri_dispatcher_singleton = NULL;
	public static function getInstance() {
		if (self::$uri_dispatcher_singleton == null) {
			self::$uri_dispatcher_singleton = new URIDispatcher();
		}
		return self::$uri_dispatcher_singleton;
	}

	public function __construct() {
		$this->cors = false;
	}
	/**
	* Reference to an array of mapping specifications
	*
	* @var array
	*/
	private $mappings = array();

	/**
	* Adds a mapping for an HTTP request to a function callback.
	*
	* The $url_pattern will be compared with the path of the requested uri.
	* This pattern can also contain special $<number> tokens, it will be useful
	* to capture path elements and pass them as parameters to the callback.
	*
	* For example, the request to:
	* /user/alice/posts/1 matched against: /user/$2/posts/$1 will invoke a call
	* to callback_function(1, "alice").
	*
	* In addition, request bodies of Content-Type: application/json will be parsed
	* and passed as the last parameter to the callback. You can disable this with
	* the $parse_json_input parameter.
	*
	* @param string $http_method The required HTTP method
	* @param string $url_pattern The pattern to match the current request against
	* @param array $matched_parameters The $<number> matched values
	* @param callback $callback The $<number> matched values
	* @param boolean $parse_json_input whether a request body of type json should be parsed
	* @return boolean true if hte current request matches. False otherwise
	*/
	public function map($http_method, $url_pattern, $callback,
	$parse_json_input = true) {

		array_push($this->mappings, array(
			"http_method" => $http_method,
			"url_pattern" => $url_pattern,
			"callback" => $callback,
			"parse_json_input" => $parse_json_input
		));
		return $this;
	}

	/**
	* Dispatch the current request by finding a suitable mapping previously
	* provided by calling the map method.
	*
	* @return boolean True if a mapping could be found and invoked. False, otherwise
	*/
	public function dispatchRequest() {
		$dispatchAsCORS = false;
		$allowedMethods = array();
		foreach($this->mappings as $mapping) {
			$parameters = array();
			if ($this->match_request( $mapping["http_method"],
			$mapping["url_pattern"],
			$parameters)) {

				if ($this->cors == true && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
					$dispatchAsCORS = true;
					array_push($allowedMethods,strtoupper($mapping["http_method"]));
					
				} else {
					// if request content-type is "application/json" we will parse it
					// and add it as a final parameter
					if ($mapping["parse_json_input"] &&
					isset($_SERVER['CONTENT_TYPE']) &&
					strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {

						array_push($parameters, json_decode(file_get_contents("php://input")));
					}
					
					if ($this->cors == true) {
						header('Access-Control-Allow-Origin: '.$this->allowedOrigin);
					}
					call_user_func_array($mapping["callback"], $parameters);
					// we have dispatched this request
					return true;
				}
			}
		}
		
		if ($dispatchAsCORS) {
			header('Access-Control-Allow-Origin: '.$this->allowedOrigin);
			header('Access-Control-Allow-Headers:'.$this->allowedRequestHeaders);
			header('Access-Control-Allow-Methods: '.implode(',', $allowedMethods).',OPTIONS');
			return true;
		}
		
		return false;
	}

	public function enableCORS($allowedOrigin, $allowedRequestHeaders) {
		$this->cors = true;
		$this->allowedOrigin = $allowedOrigin;
		$this->allowedRequestHeaders = $allowedRequestHeaders;
	}
	/**
	* Matches the current HTTP request. If the current request matches it will
	* return true, otherwise, false.
	*
	* In addition, if the url_pattern parameter contains $<number> tokens, the
	* $matched_parameters will contain $matched_parameters[<number>] entries
	* containing the request URI corresponding path elements.
	*
	* If you have enabled CORS, OPTIONS will be also allowed, independently if
	* you have created a map for this method specifically.
	*
	* For example, the request to:
	* /user/alice/posts/1 matched against the "/user/$2/posts/$1" pattern, will
	* return the array: array( [1] => "1", [2] => "alice")
	*
	* @param string $http_method The required HTTP method
	* @param string $url_pattern The expression to match the current request
	* @param array $matched_parameters The $<number> matched values
	* @return boolean true if hte current request matches. False otherwise
	*/
	private function match_request($http_method, $url_pattern, &$matched_parameters = array()) {
		$path = substr($_SERVER['REQUEST_URI'],
		strlen($_SERVER['PHP_SELF']) - strlen(basename($_SERVER['PHP_SELF'])) - 1);
		$path = parse_url($path)['path'];
		if ($_SERVER['REQUEST_METHOD'] != strtoupper($http_method) &&
				($this->cors == false || $_SERVER['REQUEST_METHOD'] != 'OPTIONS')) {
			return false;
		}
		$pathTokens = explode("/", $path);
		$patternTokens = explode("/", $url_pattern);

		if (sizeof($pathTokens) != sizeof($patternTokens)) {
			return false;
		}

		$i = 0;
		$matched_parameters = array();
		for ($i = 0; $i < sizeof($pathTokens); $i++) {
			if ($pathTokens[$i] != $patternTokens[$i]) {
				if (preg_match('/\$([0-9]+?)/', $patternTokens[$i], $matches)==1) {
					$matched_parameters[$matches[1]] = $pathTokens[$i];
				} else {
					return false;
				}
			}
		}

		if (sizeof($matched_parameters) > 0) {
			ksort($matched_parameters);
		}
		return true;
	}
}
