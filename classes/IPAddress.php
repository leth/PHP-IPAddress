<?php

abstract class IPAddress
{
	protected $address;
	
	public static function factory($address)
	{
		if (strpos($address, '.') !== FALSE)
			return new IPv4Address($address);
		else if (strpos($address, ':') !== FALSE)
			return new IPv6Address($address);
		else
			throw new Exception('Unable to guess IP address type.');
	}
	
	public static function compare(IPAddress $a, IPAddress $b)
	{
		return $a->compareTo($b);
	}
	
	function __construct($address)
	{
		$this->address = $address;
	}
	
	public abstract function bitwiseAND(IPAddress $other);
	public abstract function bitwiseOR(IPAddress $other);
	public abstract function bitwiseXOR(IPAddress $other);
	public abstract function bitwiseNOT();
	public abstract function compareTo(IPAddress $other);
	public abstract function __toString();
	
	protected function checkTypes($other)
	{
		if (get_class($this) != get_class($other))
			throw new Exception('Incompatible types.');
	}
}
