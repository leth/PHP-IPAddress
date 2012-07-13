<?php defined('SYSPATH') OR die('No direct script access.');

class Request_Core extends Kohana_Request {
	
	public static function factory($uri = TRUE, HTTP_Cache $cache = NULL, $injected_routes = array())
	{
		$request = parent::factory($uri, $cache, $injected_routes);
		
		Request::$client_ip = IP_Address::factory(Request::$client_ip);
		
		return $request;
	}
}
