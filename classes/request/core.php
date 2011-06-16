<?php defined('SYSPATH') or die('No direct script access.');

class Request_Core extends Kohana_Request {
	
	public static function factory($uri = TRUE, Cache $cache = NULL, $injected_routes = array())
	{
		$request = parent::factory($uri, $cache, $injected_routes);
		
		$request->client_ip = IP_Address::factory($request->client_ip);
		
		return $request;
	}
}
