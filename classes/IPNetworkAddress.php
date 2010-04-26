<?php

abstract class IPNetworkAddress
{
	
	protected $address;
	protected $cidr;
	
	public static abstract function generateSubnetMask($cidr);
	
	public static function factory($address, $cidr = NULL)
	{
		if ($cidr === NULL)
		{
			$parts = explode('/', $string, 2);
			
			if (count($parts) != 2)
				throw new Exception("Missing CIDR notation on '$string'.");
			
			list($address, $cidr) = $parts;
		}

		if (is_string($cidr))
		{
			if (!ctype_digit($cidr))
				throw new Exception("Malformed CIDR suffix '$cidr'.");
		
			$cidr = intval($cidr);
		}
		
		$ip = IPAddress::factory($address);
		
		if ($ip instanceof IPv4Address)
			return new IPv4NetworkAddress($ip, $cidr);
		elseif ($ip instanceof IPv6Address)
			return new IPv6NetworkAddress($ip, $cidr);
		else
			throw new Exception('Unsupported IPAddress type \'' . get_class($ip) . '\'.');
	}
	
	function __construct(IPAddress $address, $cidr)
	{
		$classname = get_class($this);
		if (!is_int($cidr) || $cidr < 0 || $cidr > $classname::max_subnet)
			throw new Exception("Invalid CIDR '$cidr'. Invalid type or out of range for class $classname.");
		
		$this->address = $address;
		$this->cidr = $cidr;
	}
	
	public function getGlobalNetmask()
	{
		$classname = get_class($this);
		return $this->generateSubnetMask($classname::max_subnet);
	}

	/**
	 * Calculates the first address in this subnet.
	 *
	 * @return IPv4Address
	 * @author Marcus Cobden
	 */
	public function getNetworkStart()
	{
		return $this->address->bitwiseAND($this->getSubnetMask());
	}
	
	/**
	 * Calculates the last address in this subnet.
	 *
	 * @return IPv4Address
	 */
	public function getNetworkEnd()
	{
		$classname = get_class($this);
		return $this->getSubnetMask()->bitwiseXOR($classname::getGlobalNetmask())->bitwiseOR($this->address);
	}
	
	public function getSubnetMask()
	{
		return $this->generateSubnetMask($this->cidr);
	}
	
	/**
	 * Calculates whether two subnets share any portion of their address space.
	 *
	 * @param IPAddress $other The other subnet to compare to.
	 * @return void
	 * @author Marcus Cobden
	 */
	public function sharesSubnetSpace(IPNetworkAddress $other)
	{
		$this->checkTypes($other);
		
		$this_start  = $this ->getNetworkStart();
		$other_start = $other->getNetworkStart();
		$this_end    = $this ->getNetworkEnd();
		$other_end   = $other->getNetworkEnd();
		
		return 
			($this_start->compareTo($other_start) >= 0 && 
			 $this_start->compareTo($other_end  ) <= 0) ||
			($this_end  ->compareTo($other_start) >= 0 &&
			 $this_end  ->compareTo($other_end  ) <= 0);
	}
	
	/**
	 * Checks whether this subnet encloses the supplied subnet.
	 *
	 * @param IPAddress $other Subnet to test against.
	 * @return boolean
	 * @author Marcus Cobden
	 */
	public function enclosesSubnet(IPNetworkAddress $other) {
		$this->checkTypes($other);
		
		if($this->cidr > $other->cidr)
			throw new Exception("Invalid Usage: $this is smaller than $other");

		return 
			($this->getNetworkStart()->compareTo($other->getNetworkStart()) <= 0) && 
			($this->getNetworkEnd()  ->compareTo($other->getNetworkEnd()  ) >= 0);
	}
	
	/**
	 * Checks whether the supplied IP fits within this subnet.
	 *
	 * @param IPAddress $ip IP to test against.
	 * @return boolean
	 * @author Marcus Cobden
	 */
	function enclosesAddress(IPAddress $ip) {
		$this->checkIPVersion($ip);
		
		return 
			$this->getNetworkStart()->compareTo($ip) <= 0 &&
			$this->getNetworkEnd()  ->compareTo($ip) >= 0;
	}
	
	protected function checkTypes($other)
	{
		if (get_class($this) != get_class($other))
			throw new Exception('Incompatible types.');
	}
	
	protected function checkIPVersion(IPAddress $other)
	{
		$this_class = get_class($this);
		$other_class = get_class($other);
		
		if (str_replace('Network','', $this_class) != $other_class)
			throw new Exception("Incompatible types ('$this_class' and '$other_class').");
	}
	
	public static function compare(IPNetworkAddress $a, IPNetworkAddress $b)
	{
		return $a->compareTo($b);
	}
	
	public function compareTo(IPNetworkAddress $other)
	{
		$cmp = $this->address->compareTo($other->address);
		
		if ($cmp == 0)
			$cmp = $this->cidr - $other->cidr;
		
		return $cmp;
	}
	
	public function __toString()
	{
		return $this->address . '/' . $this->cidr;
	}
}
