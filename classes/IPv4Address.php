<?php

class IPv4Address extends IPAddress
{
	
	public static function factory($address)
	{
		return new IPv4Address($address);
	}
	
	function __construct($address)
	{
		if(!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			throw new InvalidArgumentException("'$address' is not a valid IPv4 Address");
		
		parent::__construct(ip2long($address));
	}
	
	/**
	  * Calculates the Bitwise & (AND) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseAND(IPAddress $other)
	{
		return $this->bitwiseOperation('&', $other);
	}
	
	/**
	  * Calculates the Bitwise | (OR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseOR(IPAddress $other)
	{
		return $this->bitwiseOperation('|', $other);
	}
	
	/**
	  * Calculates the Bitwise ^ (XOR) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseXOR(IPAddress $other)
	{
		return $this->bitwiseOperation('^', $other);
	}
	
	/**
	  * Calculates the Bitwise ~ (NOT) of a given IP address.
	  * @param IPv4Address $other is the ip to be compared against
	  * @returns IPAddress
	  */
	public function bitwiseNOT()
	{
		return $this->bitwiseOperation('~');
	}
	
	protected function bitwiseOperation($operation)
	{
		$args = func_get_args();
		$operation = array_shift($args);
		array_unshift($args, $this);
		
		$addr[0] = $args[0]->address;
		if ($operation != '~')
		{
			$this->checkTypes($args[1]);
			$addr[1] = $args[1]->address;
		}
		
		switch ($operation) {
			case '&':
				$res = $addr[1] & $addr[0];
				break;
			case '|':
				$res = $addr[1] | $addr[0];
				break;
			case '^':
				$res = $addr[1] ^ $addr[0];
				break;
			case '~':
				$res = ~ $addr[0];
				break;
			
			default:
				throw new Exception('Unknown Operation.');
				break;
		}

		return new IPv4Address(long2ip($res));
	}
	
	public function asIPv6Address()
	{
		$address = str_replace('.',':','0000:0000:0000:ffff:' . $this);
		
		return new IPv6Address($address);
	}
	
	public function compareTo(IPAddress $other)
	{
		$this->checkTypes($other);
		
		return $this->address - $other->address;
	}
	
	public function __toString()
	{
		return long2ip($this->address);
	}
}