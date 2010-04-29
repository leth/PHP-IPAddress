<?php
/**
 * An abstract representation of an IP Address in a given network
 *
 * @package default
 * @author Marcus Cobden
 */
abstract class IPNetworkAddress
{
	/**
	 * The IP Address
	 *
	 * @var IPAddress
	 */
	protected $address;
	
	/**
	 * The CIDR number
	 *
	 * @var int
	 */
	protected $cidr;
	
	/**
	 * Generates the subnet mask for a given CIDR
	 *
	 * @param int $cidr The CIDR number
	 * @return IPAddress An IP address representing the mask.
	 */
	public static abstract function generateSubnetMask($cidr);
	
	/**
	 * Gets the Global subnet mask for this IP Protocol
	 *
	 * @return IPAddress An IP Address representing the mask.
	 * @author Marcus Cobden
	 */
	public static abstract function getGlobalNetmask();
	

	/**
	 * Creates an IPNetworkAddress for the supplied string
	 *
	 * @param string $address IP Network Address string.
	 * @param string $cidr Optional CIDR number. If not supplied It is assumed to be part of the address string
	 * @return IPNetworkAddress 
	 */
	public static function factory($address, $cidr = NULL)
	{
		if ($cidr === NULL)
		{
			$parts = explode('/', $string, 2);
			
			if (count($parts) != 2)
				throw new InvalidArgumentException("Missing CIDR notation on '$string'.");
			
			list($address, $cidr) = $parts;
		}

		if (is_string($cidr))
		{
			if (!ctype_digit($cidr))
				throw new InvalidArgumentException("Malformed CIDR suffix '$cidr'.");
		
			$cidr = intval($cidr);
		}
		
		$ip = IPAddress::factory($address);
		
		if ($ip instanceof IPv4Address)
			return new IPv4NetworkAddress($ip, $cidr);
		elseif ($ip instanceof IPv6Address)
			return new IPv6NetworkAddress($ip, $cidr);
		else
			throw new InvalidArgumentException('Unsupported IPAddress type \'' . get_class($ip) . '\'.');
	}
	
	/**
	 * Compare 2 IP Network Address objects.
	 * 
	 * This method is a wrapper for the compareTo method and is useful in callback situations, e.g.
	 * usort($addresses, array('IPNetworkAddress', 'compare'));
	 *
	 * @param IPAddress $a The left hand side of the comparison.
	 * @param IPAddress $b The right hand side of the comparison.
	 * @return int The result of the comparison.
	 */
	public static function compare(IPNetworkAddress $a, IPNetworkAddress $b)
	{
		return $a->compareTo($b);
	}
	
	/**
	 * Construct an IPNetworkAddress.
	 *
	 * @param IPAddress $address The IP Address of the host
	 * @param string $cidr The CIDR size of the network
	 */
	function __construct(IPAddress $address, $cidr)
	{
		$classname = get_class($this);
		if (!is_int($cidr) || $cidr < 0 || $cidr > $classname::max_subnet)
			throw new InvalidArgumentException("Invalid CIDR '$cidr'. Invalid type or out of range for class $classname.");
		
		$this->address = $address;
		$this->cidr = $cidr;
	}
	
	/**
	 * Calculates the first address in this subnet.
	 *
	 * @return IPv4Address
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
	
	/**
	 * Calculates the number of address in this subnet.
	 *
	 * @return integer
	 */
	public function getNetworkAddressCount()
	{
		$class_name = get_class($this);
		return pow(2, $class_name::max_subnet - $this->cidr);
	}
	
	/**
	 * Checks whether this is a Network Identifier
	 *
	 * @return boolean
	 */
	public function isNetworkIdentifier()
	{
		return $this->address->compareTo($this-getNetworkStart()) == 0;
	}
	
	/**
	 * Get the Network Identifier for the network this address is in.
	 *
	 * @return IPNetworkAddress
	 */
	public function getNetworkIdentifier()
	{
		$classname = get_class($this);
		return new $classname($this-getNetworkStart(), $this->cidr);
	}
	
	/**
	 * Get the subnet mask for this network
	 *
	 * @return IPAddress
	 */
	public function getSubnetMask()
	{
		$class_name = get_class($this);
		return $class_name::generateSubnetMask($this->cidr);
	}
	
	/**
	 * Calculates whether two subnets share any portion of their address space.
	 *
	 * @param IPAddress $other The other subnet to compare to.
	 * @return void
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
	 */
	public function enclosesSubnet(IPNetworkAddress $other)
	{
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
	 */
	function enclosesAddress(IPAddress $ip)
	{
		$this->checkIPVersion($ip);
		
		return 
			$this->getNetworkStart()->compareTo($ip) <= 0 &&
			$this->getNetworkEnd()  ->compareTo($ip) >= 0;
	}
	
	/**
	 * Check that this and the argument are of the same type.
	 *
	 * @param IPNetworkAddress $other The object to check.
	 * @return void
	 * @throws IllegalArgumentException If they are not of the same type.
	 */
	protected function checkTypes($other)
	{
		if (get_class($this) != get_class($other))
			throw new IllegalArgumentException('Incompatible types.');
	}
	
	/**
	 * Check that this and the argument are of the same IP protocol version
	 *
	 * @param IPAddress $other 
	 * @return void
	 * @throws IllegalArgumentException If they are not of the same type.
	 */
	protected function checkIPVersion(IPAddress $other)
	{
		$this_class = get_class($this);
		$other_class = get_class($other);
		
		if (str_replace('Network','', $this_class) != $other_class)
			throw new IllegalArgumentException("Incompatible types ('$this_class' and '$other_class').");
	}
	
	/**
	 * Compare this instance to another IPNetworkAddress
	 *
	 * @param IPNetworkAddress $other The instance to compare to
	 * @return integer
	 */
	public function compareTo(IPNetworkAddress $other)
	{
		$cmp = $this->address->compareTo($other->address);
		
		if ($cmp == 0)
			$cmp = $this->cidr - $other->cidr;
		
		return $cmp;
	}
	
	/**
	 * Provides a string representation of this object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->address . '/' . $this->cidr;
	}
}
